<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand enum to include both values, migrate data, then drop old value
        DB::statement("ALTER TABLE connection_requests MODIFY COLUMN status ENUM('pending','admin_reviewing','tutor_contacted','connected','confirmed','declined','closed') DEFAULT 'pending'");
        DB::statement("UPDATE connection_requests SET status = 'confirmed' WHERE status = 'connected'");
        DB::statement("ALTER TABLE connection_requests MODIFY COLUMN status ENUM('pending','admin_reviewing','tutor_contacted','confirmed','declined','closed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE connection_requests RENAME COLUMN connected_at TO confirmed_at");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE connection_requests RENAME COLUMN confirmed_at TO connected_at");
        DB::statement("ALTER TABLE connection_requests MODIFY COLUMN status ENUM('pending','admin_reviewing','tutor_contacted','confirmed','connected','declined','closed') DEFAULT 'pending'");
        DB::statement("UPDATE connection_requests SET status = 'connected' WHERE status = 'confirmed'");
        DB::statement("ALTER TABLE connection_requests MODIFY COLUMN status ENUM('pending','admin_reviewing','tutor_contacted','connected','declined','closed') DEFAULT 'pending'");
    }
};
