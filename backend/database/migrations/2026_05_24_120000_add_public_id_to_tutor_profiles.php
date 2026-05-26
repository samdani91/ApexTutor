<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('tutor_profiles', 'public_id')) {
            Schema::table('tutor_profiles', function (Blueprint $table) {
                $table->uuid('public_id')->nullable()->unique()->after('id');
            });
        }

        // Backfill existing rows
        DB::table('tutor_profiles')->whereNull('public_id')->orderBy('id')->each(function ($row) {
            DB::table('tutor_profiles')
                ->where('id', $row->id)
                ->update(['public_id' => Str::uuid()->toString()]);
        });

        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->uuid('public_id')->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
