<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create areas table ──────────────────────────────────────────────
        Schema::create('areas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('district_id');
            $table->string('name', 100);
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->index('district_id');
        });

        // Area reference data is seeded by AreaSeeder (runs after DistrictSeeder),
        // since the rows depend on districts which are seeded, not migrated.

        // ── 2. Update tuition_preference_locations ─────────────────────────────
        // Clear existing free-text data (can't map to IDs)
        DB::table('tuition_preference_locations')->truncate();

        Schema::table('tuition_preference_locations', function (Blueprint $table) {
            $table->dropColumn('area_name');
            $table->unsignedSmallInteger('area_id')->after('tuition_preference_id');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tuition_preference_locations', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->string('area_name', 150)->after('tuition_preference_id');
        });
        Schema::dropIfExists('areas');
    }
};
