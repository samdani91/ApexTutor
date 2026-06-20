<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE tuition_job_applications SET status = 'not_selected' WHERE status = 'removed'");
        DB::statement("ALTER TABLE tuition_job_applications MODIFY COLUMN status ENUM('applied','shortlisted','appointed','connected','not_selected') NOT NULL DEFAULT 'applied'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tuition_job_applications SET status = 'removed' WHERE status = 'not_selected'");
        DB::statement("ALTER TABLE tuition_job_applications MODIFY COLUMN status ENUM('applied','shortlisted','appointed','connected','removed') NOT NULL DEFAULT 'applied'");
    }
};
