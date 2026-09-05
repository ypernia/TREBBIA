<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'description')) {
                $table->text('description')->nullable()->after('code');
            }

            if (! Schema::hasColumn('plans', 'currency')) {
                $table->string('currency', 3)->default('COP')->after('description');
            }

            if (! Schema::hasColumn('plans', 'annual_price_cents')) {
                $table->unsignedInteger('annual_price_cents')->nullable()->after('monthly_price_cents');
            }

            if (! Schema::hasColumn('plans', 'entitlements')) {
                $table->json('entitlements')->nullable()->after('limits');
            }

            if (! Schema::hasColumn('plans', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('features');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'trial_started_at')) {
                $table->timestamp('trial_started_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('subscriptions', 'current_period_started_at')) {
                $table->timestamp('current_period_started_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('current_period_ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('cancelled_at');
            }
        });

        if (! Schema::hasTable('subscription_events')) {
            Schema::create('subscription_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status')->index();
                $table->foreignId('from_plan_id')->nullable()->constrained('plans')->nullOnDelete();
                $table->foreignId('to_plan_id')->nullable()->constrained('plans')->nullOnDelete();
                $table->string('reason')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['business_id', 'created_at']);
            });
        }

        DB::table('plans')
            ->where(function ($query): void {
                $query->where('monthly_price_cents', 0)
                    ->orWhereIn('code', ['starter', 'free']);
            })
            ->update(['is_active' => false]);

        DB::table('subscriptions')
            ->whereNull('trial_started_at')
            ->whereNotNull('trial_ends_at')
            ->update(['trial_started_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
    }
};
