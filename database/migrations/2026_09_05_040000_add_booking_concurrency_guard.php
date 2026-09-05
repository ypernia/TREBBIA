<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slot_locks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('lock_key')->unique();
            $table->date('lock_date')->index();
            $table->string('scope')->index();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'lock_date']);
            $table->index(['business_id', 'scope', 'scope_id'], 'appt_locks_business_scope_idx');
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('idempotency_key')->nullable()->after('source_reference');
            $table->unique(['business_id', 'idempotency_key'], 'appointments_business_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropUnique('appointments_business_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::dropIfExists('appointment_slot_locks');
    }
};
