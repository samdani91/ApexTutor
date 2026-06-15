<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tuition_jobs', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 6)->unique();
            $table->foreignId('guardian_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('tuition_type', ['home', 'online', 'group', 'home_and_online']);
            $table->unsignedSmallInteger('district_id');
            $table->unsignedSmallInteger('area_id')->nullable();
            $table->text('address_details')->nullable();
            $table->string('class_level');
            $table->enum('student_gender', ['male', 'female', 'any'])->default('any');
            $table->tinyInteger('num_students')->unsigned()->default(1);
            $table->enum('tutor_gender_pref', ['male', 'female', 'any'])->default('any');
            $table->unsignedInteger('offered_salary');
            $table->date('hire_date')->nullable();
            $table->time('tutoring_time')->nullable();
            $table->tinyInteger('tutoring_days_per_week')->unsigned()->nullable();
            $table->string('student_institute')->nullable();
            $table->text('extra_requirements')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
            $table->index(['status', 'created_at']);
            $table->index('guardian_profile_id');
        });
    }

    public function down(): void { Schema::dropIfExists('tuition_jobs'); }
};
