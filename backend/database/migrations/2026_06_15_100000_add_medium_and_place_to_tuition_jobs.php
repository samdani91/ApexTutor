<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Convert enum to varchar so we can store new place_of_tutoring values
        DB::statement("ALTER TABLE tuition_jobs MODIFY COLUMN tuition_type VARCHAR(50) NOT NULL");

        Schema::table('tuition_jobs', function (Blueprint $table) {
            $table->string('medium', 50)->nullable()->after('tuition_type');
            $table->string('tutoring_style', 50)->nullable()->after('medium');
        });

        // Migrate old enum values to new place-of-tutoring vocabulary
        DB::table('tuition_jobs')->where('tuition_type', 'home')->update(['tuition_type' => 'student_home']);
        DB::table('tuition_jobs')->where('tuition_type', 'home_and_online')->update(['tuition_type' => 'student_home']);
        DB::table('tuition_jobs')->where('tuition_type', 'group')->update(['tuition_type' => 'student_home']);
        // 'online' stays as 'online'
    }

    public function down(): void
    {
        Schema::table('tuition_jobs', function (Blueprint $table) {
            $table->dropColumn(['medium', 'tutoring_style']);
        });
        DB::statement("ALTER TABLE tuition_jobs MODIFY COLUMN tuition_type ENUM('home','online','group','home_and_online') NOT NULL");
    }
};
