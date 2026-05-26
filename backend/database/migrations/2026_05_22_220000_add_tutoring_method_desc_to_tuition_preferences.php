<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->text('tutoring_method_description')->nullable()->after('hours_per_day');
        });
    }
    public function down(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->dropColumn('tutoring_method_description');
        });
    }
};
