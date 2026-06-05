<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Re-generate tutor_ids with random 6-digit numbers
        $usedTutor = [];
        DB::table('tutor_profiles')->orderBy('id')->each(function ($row) use (&$usedTutor) {
            do {
                $num = random_int(100000, 999999);
            } while (isset($usedTutor[$num]));
            $usedTutor[$num] = true;
            DB::table('tutor_profiles')
                ->where('id', $row->id)
                ->update(['tutor_id' => 'TUT-' . $num]);
        });

        // Re-generate guardian_ids with random 6-digit numbers
        $usedGuardian = [];
        DB::table('guardian_profiles')->orderBy('id')->each(function ($row) use (&$usedGuardian) {
            do {
                $num = random_int(100000, 999999);
            } while (isset($usedGuardian[$num]));
            $usedGuardian[$num] = true;
            DB::table('guardian_profiles')
                ->where('id', $row->id)
                ->update(['guardian_id' => 'GRD-' . $num]);
        });
    }

    public function down(): void
    {
        // Restore sequential IDs
        DB::table('tutor_profiles')->orderBy('id')->each(function ($row) {
            DB::table('tutor_profiles')
                ->where('id', $row->id)
                ->update(['tutor_id' => 'TUT-' . str_pad($row->id, 6, '0', STR_PAD_LEFT)]);
        });

        DB::table('guardian_profiles')->orderBy('id')->each(function ($row) {
            DB::table('guardian_profiles')
                ->where('id', $row->id)
                ->update(['guardian_id' => 'GRD-' . str_pad($row->id, 6, '0', STR_PAD_LEFT)]);
        });
    }
};
