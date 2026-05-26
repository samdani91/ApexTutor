<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->after('id');
            $table->string('phone', 20)->nullable()->change();
        });

        // Extend the purpose column to support new values
        DB::statement("ALTER TABLE otp_codes MODIFY COLUMN purpose VARCHAR(30) NOT NULL");
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->string('phone', 20)->nullable(false)->change();
        });
        DB::statement("ALTER TABLE otp_codes MODIFY COLUMN purpose ENUM('registration','login','password_reset') NOT NULL");
    }
};
