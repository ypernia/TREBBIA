<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppAccount;
use App\Services\ConversationManager;
use App\Services\WhatsAppBookingConversationService;
use App\Services\WhatsAppCloudApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class MetaWhatsAppWebhookController extends Controller
{
    public function __construct(
        private ConversationManager $conversations,
        private WhatsAppBookingConversationService $bookingConversation,
        private WhatsAppCloudApiClient $cloudApi,
    ) {}

    public function verify(Request $request): Response
    {
        $verifyToken = config('services.whatsapp.verify_token');

        abort_unless($verifyToken && $request->query('hub_verify_token') === $verifyToken, 403);
        abort_unless($request->query('hub_mode') === 'subscribe', 403);

        return response($request->query('hub_challenge', ''), 200);
    }

    public function receive(Request $request): Response
    {
        $this->verifySignature($request);

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $this->processChange($change);
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processChange(array $change): void
    {
        $value = $change['value'] ?? [];
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        if (! $phoneNumberId || empty($value['messages'])) {
            return;
        }

        $account = WhatsAppAccount::query()
            ->with('business')
            ->where('phone_number_id', $phoneNumberId)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return;
        }

        $account->update([
            'last_webhook_at' => now(),
            'status' => 'connected',
            'metadata' => array_merge($account->metadata ?? [], ['last_metadata' => $value['metadata'] ?? []]),
        ]);

        foreach ($value['messages'] as $message) {
            $this->processMessage($account, $message, $value['contacts'][0] ?? []);
        }
    }

    private function processMessage(WhatsAppAccount $account, array $message, array $contactPayload): void
    {
        $business = $account->business;
        $from = $message['from'] ?? null;

        if (! $business || ! $from) {
            return;
        }

        $contact = $this->conversations->contactForWhatsapp(
            $business,
            $from,
            $contactPayload['profile']['name'] ?? null,
            $contactPayload['wa_id'] ?? $from,
            ['cloud_api_contact' => $contactPayload],
        );
        $conversation = $this->conversations->openConversation($business, $contact);
        $body = $message['text']['body'] ?? '['.($message['type'] ?? 'mensaje').']';
        $incoming = $this->conversations->recordIncoming($conversation, [
            'external_message_id' => $message['id'] ?? null,
            'message_type' => $message['type'] ?? 'unknown',
            'body' => $body,
            'payload' => $message,
            'received_at' => isset($message['timestamp'])
                ? Carbon::createFromTimestamp((int) $message['timestamp'], $business->timezone)
                : now(),
        ]);

        if ($incoming->wasRecentlyCreated === false) {
            return;
        }

        $reply = $this->bookingConversation->replyFor($business, $conversation->fresh(['state', 'whatsappContact']), $body);
        $outgoing = $this->conversations->recordOutgoing($conversation->fresh(), $reply, ['provider' => 'meta_cloud_api']);
        $result = $this->cloudApi->sendText($account, $from, $reply);

        $this->cloudApi->markMessageResult($outgoing, $result);
    }

    private function verifySignature(Request $request): void
    {
        $appSecret = config('services.whatsapp.app_secret');

        if (! $appSecret) {
            return;
        }

        $signature = $request->header('X-Hub-Signature-256');
        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

        abort_unless($signature && hash_equals($expected, $signature), 403);
    }
}
