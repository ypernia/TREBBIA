<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('source_channel')->default('internal')->after('status')->index();
            $table->string('source_reference')->nullable()->after('source_channel')->index();
            $table->json('source_metadata')->nullable()->after('source_reference');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['source_channel', 'source_reference', 'source_metadata']);
        });
    }
};
