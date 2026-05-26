<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('education_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['phd','masters','bachelor','hsc','ssc','o_level','a_level','other']);
            $table->string('institute_name', 255);
            $table->string('degree_title', 150);
            $table->string('major_group', 150)->nullable();
            $table->string('id_card_number', 100)->nullable();
            $table->string('result', 100)->nullable();
            $table->string('curriculum', 100)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->year('year_of_passing')->nullable();
            $table->boolean('is_current')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('education_entries'); }
};
