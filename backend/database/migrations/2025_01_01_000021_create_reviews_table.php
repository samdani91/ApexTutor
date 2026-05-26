<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('connection_request_id')->unique()->constrained();
            $table->unsignedTinyInteger('rating');
            $table->text('review_text')->nullable();
            $table->enum('moderation_status', ['pending','approved','rejected'])->default('pending');
            $table->text('moderation_note')->nullable();
            $table->unsignedBigInteger('moderated_by')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reviews'); }
};
