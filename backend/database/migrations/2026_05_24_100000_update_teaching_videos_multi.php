<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('teaching_videos', function (Blueprint $table) {
            // Must drop FK before dropping the unique index it depends on
            $table->dropForeign(['tutor_profile_id']);
            $table->dropUnique(['tutor_profile_id']);
            // Re-add FK without unique constraint
            $table->foreign('tutor_profile_id')->references('id')->on('tutor_profiles')->cascadeOnDelete();
            // Metadata columns
            $table->string('title', 200)->nullable()->after('tutor_profile_id');
            $table->string('subject', 100)->nullable()->after('title');
            $table->string('class_level', 100)->nullable()->after('subject');
            $table->string('medium', 50)->nullable()->after('class_level');
        });
    }

    public function down(): void {
        Schema::table('teaching_videos', function (Blueprint $table) {
            $table->dropColumn(['title', 'subject', 'class_level', 'medium']);
            $table->dropForeign(['tutor_profile_id']);
            $table->unique('tutor_profile_id');
            $table->foreign('tutor_profile_id')->references('id')->on('tutor_profiles')->cascadeOnDelete();
        });
    }
};
