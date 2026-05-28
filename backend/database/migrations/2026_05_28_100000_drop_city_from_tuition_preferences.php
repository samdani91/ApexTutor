<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }

    public function down(): void
    {
        Schema::table('tuition_preferences', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('tutor_profile_id');
        });
    }
};
