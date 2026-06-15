<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tuition_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuition_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['applied', 'shortlisted', 'appointed', 'connected', 'removed'])
                  ->default('applied');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->unique(['tuition_job_id', 'tutor_profile_id'], 'unique_job_application');
            $table->index(['tutor_profile_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('tuition_job_applications'); }
};
