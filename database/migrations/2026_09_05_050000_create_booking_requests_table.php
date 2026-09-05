<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->string('status')->default('pending')->index();
            $table->string('source_channel')->default('public_booking')->index();
            $table->string('source_reference')->nullable()->index();
            $table->string('idempotency_key')->nullable();
            $table->json('source_metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->nullable()->index();
            $table->timestamp('decided_at')->nullable()->index();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'idempotency_key'], 'booking_requests_business_idempotency_unique');
            $table->index(['business_id', 'status', 'starts_at'], 'booking_requests_business_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
