<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tuition_job_applications', function (Blueprint $table) {
            // Stamped on applicants mass-rejected when a tutor is confirmed, so
            // un-confirming can restore each one to exactly the status they held.
            $table->string('status_before_confirm', 30)->nullable()->after('status');
        });
    }

    public function down(): void {
        Schema::table('tuition_job_applications', function (Blueprint $table) {
            $table->dropColumn('status_before_confirm');
        });
    }
};
