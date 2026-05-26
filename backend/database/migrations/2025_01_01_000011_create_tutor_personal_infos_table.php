<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutor_personal_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('additional_phone', 20)->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('religion', ['islam','hinduism','christianity','buddhism','other'])->nullable();
            $table->string('national_id', 30)->nullable();
            $table->string('nationality', 100)->default('Bangladeshi');
            $table->string('facebook_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('fathers_name', 150)->nullable();
            $table->string('fathers_phone', 20)->nullable();
            $table->string('mothers_name', 150)->nullable();
            $table->string('mothers_phone', 20)->nullable();
            $table->timestamps();
        });
        \DB::statement('ALTER TABLE tutor_personal_infos ADD FULLTEXT INDEX ft_address (present_address)');
    }
    public function down(): void { Schema::dropIfExists('tutor_personal_infos'); }
};
