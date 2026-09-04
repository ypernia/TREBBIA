<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_number_id')->unique();
            $table->string('waba_id')->nullable()->index();
            $table->text('access_token')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('last_webhook_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'phone_number_id'], 'wa_accounts_business_phone_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
