<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('profile_completion_percent')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->enum('verification_status', ['pending','under_review','approved','rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('status', ['active','inactive','suspended'])->default('inactive');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('profile_view_count')->default(0);
            $table->text('bio')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('verification_status');
            $table->index('status');
            $table->index('rating');
        });
        // Full-text index on bio
        \DB::statement('ALTER TABLE tutor_profiles ADD FULLTEXT INDEX ft_bio (bio)');
    }
    public function down(): void { Schema::dropIfExists('tutor_profiles'); }
};
