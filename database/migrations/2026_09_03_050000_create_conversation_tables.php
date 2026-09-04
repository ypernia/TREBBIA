<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_contacts')) {
            Schema::create('whatsapp_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name')->nullable();
                $table->string('phone');
                $table->string('external_contact_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['business_id', 'phone'], 'wa_contacts_business_phone_unique');
                $table->unique(['business_id', 'external_contact_id'], 'wa_contacts_business_external_unique');
            });
        }

        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('whatsapp_contact_id')->constrained()->cascadeOnDelete();
                $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
                $table->string('channel')->default('whatsapp')->index();
                $table->string('status')->default('open')->index();
                $table->string('intent')->nullable()->index();
                $table->string('current_step')->default('idle')->index();
                $table->timestamp('last_message_at')->nullable()->index();
                $table->timestamps();
                $table->index(['business_id', 'status', 'last_message_at'], 'conversations_business_status_last_idx');
            });
        }

        if (! Schema::hasTable('conversation_messages')) {
            Schema::create('conversation_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->string('channel')->default('whatsapp')->index();
                $table->string('direction')->index();
                $table->string('external_message_id')->nullable();
                $table->string('message_type')->default('text');
                $table->string('status')->default('received')->index();
                $table->text('body')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->unique(['business_id', 'channel', 'external_message_id'], 'conv_messages_external_unique');
                $table->index(['conversation_id', 'created_at'], 'conv_messages_conversation_created_idx');
            });
        } else {
            $this->addIndexIfMissing(fn () => Schema::table('conversation_messages', function (Blueprint $table) {
                $table->unique(['business_id', 'channel', 'external_message_id'], 'conv_messages_external_unique');
            }));

            $this->addIndexIfMissing(fn () => Schema::table('conversation_messages', function (Blueprint $table) {
                $table->index(['conversation_id', 'created_at'], 'conv_messages_conversation_created_idx');
            }));
        }

        if (! Schema::hasTable('conversation_states')) {
            Schema::create('conversation_states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->string('state')->default('idle')->index();
                $table->json('data')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
                $table->unique('conversation_id', 'conversation_states_conversation_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_states');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('whatsapp_contacts');
    }

    private function addIndexIfMissing(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            if (! str_contains($exception->getMessage(), 'Duplicate key name')) {
                throw $exception;
            }
        }
    }
};
