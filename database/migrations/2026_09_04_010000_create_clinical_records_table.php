<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained()->nullOnDelete();
            $table->date('record_date')->index();
            $table->string('reason_for_visit')->nullable();
            $table->text('diagnosis')->nullable();
            $table->unsignedTinyInteger('pain_scale')->nullable();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('evolution')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('next_steps')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();

            $table->index(['business_id', 'client_id', 'record_date'], 'clinical_records_business_client_date_idx');
            $table->index(['business_id', 'professional_id', 'record_date'], 'clinical_records_business_prof_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_records');
    }
};
