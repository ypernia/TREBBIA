<?php

use App\Models\ManualPayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manual_payments')) {
            return;
        }

        Schema::create('manual_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default(ManualPayment::STATUS_CONFIRMED)->index();
            $table->string('currency', 3)->default('COP');
            $table->unsignedInteger('amount_cents');
            $table->unsignedTinyInteger('period_months')->default(1);
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'paid_at']);
            $table->index(['subscription_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
    }
};
