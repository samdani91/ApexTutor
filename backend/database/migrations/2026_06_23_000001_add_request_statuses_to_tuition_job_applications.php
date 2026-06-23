<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE tuition_job_applications MODIFY COLUMN status ENUM('applied','shortlisted','demo_requested','appointed','confirm_requested','connected','not_selected') NOT NULL DEFAULT 'applied'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tuition_job_applications MODIFY COLUMN status ENUM('applied','shortlisted','appointed','connected','not_selected') NOT NULL DEFAULT 'applied'");
    }
};
