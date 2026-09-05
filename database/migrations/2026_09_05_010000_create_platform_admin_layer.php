<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'platform_role')) {
                $table->string('platform_role')->nullable()->after('password')->index();
            }

            if (! Schema::hasColumn('users', 'platform_permissions')) {
                $table->json('platform_permissions')->nullable()->after('platform_role');
            }

            if (! Schema::hasColumn('users', 'platform_access_enabled_at')) {
                $table->timestamp('platform_access_enabled_at')->nullable()->after('platform_permissions');
            }
        });

        if (! Schema::hasTable('platform_audit_logs')) {
            Schema::create('platform_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action')->index();
                $table->string('auditable_type')->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();
                $table->index(['auditable_type', 'auditable_id']);
                $table->index(['business_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};
