<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('role')->index();
            $table->json('permissions')->nullable();
            $table->string('token')->unique();
            $table->string('status')->default('pending')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_invitations');
    }
};
