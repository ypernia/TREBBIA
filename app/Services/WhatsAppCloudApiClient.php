<?php

namespace App\Services;

use App\Models\ConversationMessage;
use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\Http;

class WhatsAppCloudApiClient
{
    public function sendText(WhatsAppAccount $account, string $to, string $body): array
    {
        if (! $account->access_token) {
            return [
                'ok' => false,
                'error' => 'missing_access_token',
            ];
        }

        $response = Http::withToken($account->access_token)
            ->acceptJson()
            ->post($this->endpoint($account), [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $body,
                ],
            ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'json' => $response->json(),
        ];
    }

    public function markMessageResult(ConversationMessage $message, array $result): void
    {
        $message->update([
            'status' => $result['ok'] ? 'sent' : 'failed',
            'processed_at' => now(),
            'payload' => array_merge($message->payload ?? [], ['cloud_api_result' => $result]),
        ]);
    }

    private function endpoint(WhatsAppAccount $account): string
    {
        $version = config('services.whatsapp.graph_version', 'v24.0');

        return "https://graph.facebook.com/{$version}/{$account->phone_number_id}/messages";
    }
}
