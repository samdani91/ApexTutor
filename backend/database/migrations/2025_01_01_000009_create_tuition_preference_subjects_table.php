<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_preference_subjects', function (Blueprint $table) {
            $table->foreignId('tuition_preference_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('subject_id');
            $table->primary(['tuition_preference_id', 'subject_id']);
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_preference_subjects'); }
};
