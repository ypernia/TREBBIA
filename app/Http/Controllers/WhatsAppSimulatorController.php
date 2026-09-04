<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ConversationManager;
use App\Services\WhatsAppBookingConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WhatsAppSimulatorController extends Controller
{
    public function __construct(
        private ConversationManager $conversations,
        private WhatsAppBookingConversationService $bookingConversation,
    ) {}

    public function index(Request $request): View
    {
        $business = app('activeBusiness');
        $conversation = $this->selectedConversation($request);
        $settings = $business->settings()->firstOrCreate([]);
        $whatsappSettings = $this->whatsappSettings($business);

        return view('whatsapp-simulator.index', [
            'business' => $business,
            'whatsappSettings' => $whatsappSettings,
            'configuredPhone' => $this->normalizePhone($whatsappSettings['phone'] ?? ''),
            'conversations' => $business->conversations()
                ->with(['whatsappContact', 'state'])
                ->latest('last_message_at')
                ->latest()
                ->take(12)
                ->get(),
            'conversation' => $conversation,
            'messages' => $conversation
                ? $conversation->messages()->oldest()->get()
                : collect(),
            'state' => $conversation?->state,
            'channelStatus' => ($settings->whatsapp_settings['status'] ?? 'not_configured'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = app('activeBusiness');
        $attributes = $request->validate([
            'phone' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $contact = $this->conversations->contactForWhatsapp(
            $business,
            $this->normalizePhone($attributes['phone']),
            $attributes['name'] ?: null,
            metadata: ['simulated' => true],
        );
        $conversation = $this->conversations->openConversation($business, $contact);

        $this->conversations->recordIncoming($conversation, [
            'external_message_id' => 'sim-'.Str::uuid(),
            'body' => $attributes['body'],
            'payload' => ['source' => 'internal_simulator'],
            'received_at' => now(),
        ]);

        $this->conversations->recordOutgoing($conversation, $this->bookingConversation->replyFor($business, $conversation->fresh(['state', 'whatsappContact']), $attributes['body']));

        return redirect()->route('whatsapp-simulator.index', ['conversation' => $conversation->id])
            ->with('status', 'Mensaje simulado procesado.');
    }

    public function reset(Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->business_id === app('activeBusiness')->id, 404);

        $conversation->state()->delete();
        $conversation->update([
            'intent' => null,
            'current_step' => 'idle',
            'appointment_id' => null,
        ]);

        $this->conversations->recordOutgoing($conversation, 'Conversacion reiniciada. Escribe "quiero agendar" para comenzar de nuevo.');

        return redirect()->route('whatsapp-simulator.index', ['conversation' => $conversation->id]);
    }

    private function selectedConversation(Request $request): ?Conversation
    {
        $business = app('activeBusiness');
        $conversationId = $request->integer('conversation');

        return $conversationId
            ? $business->conversations()->with(['whatsappContact', 'state'])->findOrFail($conversationId)
            : $business->conversations()->with(['whatsappContact', 'state'])->latest('last_message_at')->latest()->first();
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: $value;
    }

    private function whatsappSettings($business): array
    {
        return array_merge([
            'enabled' => false,
            'phone' => $business->phone ?? '',
            'display_name' => $business->name,
            'entry_message' => 'Hola, quiero agendar una cita',
            'welcome_message' => 'Hola, soy el asistente de reservas de '.$business->name.'. Te ayudo a encontrar un horario disponible.',
            'unavailable_message' => 'No encontre horarios disponibles para esa opcion. Probemos con otra fecha u horario.',
            'confirmation_message' => 'Listo, tu cita quedo registrada. Te esperamos.',
            'appointment_status' => 'scheduled',
        ], $business->settings()->firstOrCreate([])->whatsapp_settings ?? []);
    }
}
