<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teaching_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('file_path', 500)->nullable();
            $table->string('thumbnail_path', 500)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->enum('review_status', ['pending','approved','rejected'])->default('pending');
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('teaching_videos'); }
};
