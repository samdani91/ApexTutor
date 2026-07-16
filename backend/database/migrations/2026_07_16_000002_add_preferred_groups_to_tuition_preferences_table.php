<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            // Academic groups the tutor teaches (science / business_studies /
            // humanities) — mirrors preferred_curricula / preferred_classes.
            $table->json('preferred_groups')->nullable()->after('preferred_classes');
        });
    }

    public function down(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->dropColumn('preferred_groups');
        });
    }
};
