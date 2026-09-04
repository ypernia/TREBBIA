<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationState;
use App\Models\WhatsAppContact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ConversationManager
{
    public function contactForWhatsapp(Business $business, string $phone, ?string $name = null, ?string $externalContactId = null, array $metadata = []): WhatsAppContact
    {
        return WhatsAppContact::updateOrCreate(
            ['business_id' => $business->id, 'phone' => trim($phone)],
            [
                'name' => $name,
                'external_contact_id' => $externalContactId,
                'metadata' => $metadata ?: null,
            ],
        );
    }

    public function openConversation(Business $business, WhatsAppContact $contact): Conversation
    {
        abort_unless($contact->business_id === $business->id, 404);

        return Conversation::firstOrCreate(
            [
                'business_id' => $business->id,
                'whatsapp_contact_id' => $contact->id,
                'channel' => Conversation::CHANNEL_WHATSAPP,
                'status' => Conversation::STATUS_OPEN,
            ],
            ['current_step' => 'idle'],
        );
    }

    public function recordIncoming(Conversation $conversation, array $attributes): ConversationMessage
    {
        return DB::transaction(function () use ($conversation, $attributes): ConversationMessage {
            $externalMessageId = $attributes['external_message_id'] ?? null;

            if ($externalMessageId) {
                $existingMessage = ConversationMessage::query()
                    ->where('business_id', $conversation->business_id)
                    ->where('channel', $conversation->channel)
                    ->where('external_message_id', $externalMessageId)
                    ->first();

                if ($existingMessage) {
                    return $existingMessage;
                }
            }

            $receivedAt = $attributes['received_at'] ?? now();
            $message = $conversation->messages()->create([
                'business_id' => $conversation->business_id,
                'channel' => $conversation->channel,
                'direction' => ConversationMessage::DIRECTION_INBOUND,
                'external_message_id' => $externalMessageId,
                'message_type' => $attributes['message_type'] ?? 'text',
                'status' => $attributes['status'] ?? 'received',
                'body' => $attributes['body'] ?? null,
                'payload' => $attributes['payload'] ?? null,
                'received_at' => $receivedAt,
            ]);

            $conversation->update(['last_message_at' => $receivedAt]);

            return $message;
        });
    }

    public function recordOutgoing(Conversation $conversation, string $body, array $payload = []): ConversationMessage
    {
        return DB::transaction(function () use ($conversation, $body, $payload): ConversationMessage {
            $sentAt = now();
            $message = $conversation->messages()->create([
                'business_id' => $conversation->business_id,
                'channel' => $conversation->channel,
                'direction' => ConversationMessage::DIRECTION_OUTBOUND,
                'message_type' => 'text',
                'status' => 'queued',
                'body' => $body,
                'payload' => $payload ?: null,
                'sent_at' => $sentAt,
            ]);

            $conversation->update(['last_message_at' => $sentAt]);

            return $message;
        });
    }

    public function updateState(Conversation $conversation, string $state, array $data = [], ?Carbon $expiresAt = null): ConversationState
    {
        return ConversationState::updateOrCreate(
            ['conversation_id' => $conversation->id],
            [
                'business_id' => $conversation->business_id,
                'state' => $state,
                'data' => $data ?: null,
                'expires_at' => $expiresAt,
            ],
        );
    }
}
