<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['university_id','nid','passport','birth_certificate','ssc_marksheet','ssc_certificate','hsc_marksheet','hsc_certificate','o_level_marksheet','a_level_marksheet','additional']);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->enum('review_status', ['pending','approved','rejected'])->default('pending');
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index('review_status');
        });
    }
    public function down(): void { Schema::dropIfExists('tutor_documents'); }
};
