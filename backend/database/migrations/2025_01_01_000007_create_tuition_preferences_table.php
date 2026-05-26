<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('tutoring_methods')->nullable();
            $table->json('place_of_tutoring')->nullable();
            $table->json('tutoring_styles')->nullable();
            $table->json('preferred_curricula')->nullable();
            $table->json('preferred_classes')->nullable();
            $table->string('city', 100)->nullable();
            $table->unsignedSmallInteger('district_id')->nullable();
            $table->unsignedInteger('expected_salary_min')->nullable();
            $table->unsignedInteger('expected_salary_max')->nullable();
            $table->unsignedTinyInteger('total_experience_years')->default(0);
            $table->text('experience_details')->nullable();
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_preferences'); }
};
