<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_profile_id')->constrained()->cascadeOnDelete();
            $table->string('student_name', 150);
            $table->enum('medium', ['bangla_medium','english_medium','english_version']);
            $table->string('class_level', 50);
            $table->unsignedSmallInteger('district_id')->nullable();
            $table->string('city', 150)->nullable();
            $table->string('area', 255)->nullable();
            $table->enum('preferred_tutor_gender', ['male','female','no_preference'])->default('no_preference');
            $table->unsignedTinyInteger('days_per_week')->nullable();
            $table->json('preferred_days')->nullable();
            $table->decimal('hours_per_day', 3, 1)->nullable();
            $table->time('preferred_time_from')->nullable();
            $table->time('preferred_time_to')->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->json('place_of_tutoring')->nullable();
            $table->text('special_notes')->nullable();
            $table->enum('status', ['open','in_progress','connected','closed'])->default('open');
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->index('medium');
            $table->index('class_level');
            $table->index('district_id');
            $table->index('days_per_week');
        });
    }
    public function down(): void { Schema::dropIfExists('tuition_requirements'); }
};
