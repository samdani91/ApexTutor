<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_requirement_subjects', function (Blueprint $table) {
            $table->foreignId('tuition_requirement_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('subject_id');
            $table->primary(['tuition_requirement_id', 'subject_id']);
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_requirement_subjects'); }
};
