<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel')->default('manual')->index();
            $table->string('trigger')->default('appointment_reminder')->index();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['business_id', 'is_active']);
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('manual')->index();
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->text('message_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('notification_templates');
    }
};
