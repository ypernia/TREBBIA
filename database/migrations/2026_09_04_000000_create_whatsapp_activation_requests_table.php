<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_activation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('submitted')->index();
            $table->string('commercial_name');
            $table->string('legal_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('country', 80);
            $table->string('city', 120)->nullable();
            $table->string('address')->nullable();
            $table->string('industry')->nullable();
            $table->string('website_or_instagram')->nullable();
            $table->string('public_email')->nullable();
            $table->string('public_phone')->nullable();
            $table->string('responsible_name');
            $table->string('responsible_role')->nullable();
            $table->string('responsible_email');
            $table->string('responsible_phone');
            $table->string('whatsapp_number');
            $table->string('verification_method')->default('sms');
            $table->boolean('uses_whatsapp_business')->default(false);
            $table->string('whatsapp_display_name');
            $table->text('whatsapp_description')->nullable();
            $table->string('whatsapp_category')->nullable();
            $table->text('business_hours')->nullable();
            $table->boolean('number_owner_confirmed')->default(false);
            $table->boolean('managed_activation_accepted')->default(false);
            $table->boolean('messaging_costs_accepted')->default(false);
            $table->text('client_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_activation_requests');
    }
};
