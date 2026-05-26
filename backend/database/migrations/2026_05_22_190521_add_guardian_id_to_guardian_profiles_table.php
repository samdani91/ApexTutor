<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardian_profiles', function (Blueprint $table) {
            $table->string('guardian_id', 20)->nullable()->unique()->after('id');
        });

        DB::table('guardian_profiles')->orderBy('id')->each(function ($row) {
            DB::table('guardian_profiles')
                ->where('id', $row->id)
                ->update(['guardian_id' => 'GRD-' . str_pad($row->id, 6, '0', STR_PAD_LEFT)]);
        });
    }

    public function down(): void
    {
        Schema::table('guardian_profiles', function (Blueprint $table) {
            $table->dropColumn('guardian_id');
        });
    }
};
