<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tuition_job_subjects', function (Blueprint $table) {
            $table->foreignId('tuition_job_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->primary(['tuition_job_id', 'subject_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('tuition_job_subjects'); }
};
