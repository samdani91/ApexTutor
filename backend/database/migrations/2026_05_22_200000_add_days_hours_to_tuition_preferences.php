<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->unsignedTinyInteger('days_per_week')->nullable()->after('experience_details');
            $table->decimal('hours_per_day', 3, 1)->nullable()->after('days_per_week');
        });
    }
    public function down(): void {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->dropColumn(['days_per_week', 'hours_per_day']);
        });
    }
};
