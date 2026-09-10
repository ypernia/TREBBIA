<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (! Schema::hasColumn('services', 'price_type')) {
                $table->string('price_type', 20)->default('fixed')->after('duration_minutes');
            }

            if (! Schema::hasColumn('services', 'price_max_cents')) {
                $table->unsignedInteger('price_max_cents')->nullable()->after('price_cents');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (Schema::hasColumn('services', 'price_max_cents')) {
                $table->dropColumn('price_max_cents');
            }

            if (Schema::hasColumn('services', 'price_type')) {
                $table->dropColumn('price_type');
            }
        });
    }
};
