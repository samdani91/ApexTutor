<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $extra = [
            // Dhaka (district_id = 1)
            'Aftabnagar', 'Agargaon', 'Aminbazar', 'Banasree', 'Basabo',
            'Chawkbazar', 'Donia', 'Eskaton', 'Green Road', 'Hatirjheel',
            'Hazaribagh', 'Jigatola', 'Kalabagan', 'Kamalapur', 'Karwan Bazar',
            'Lalmatia', 'Manda', 'Matuail', 'Mirpur-13', 'Mirpur-14',
            'New Market', 'Niketan', 'Panthapath', 'Sabujbagh', 'Shantinagar',
            'Shegunbagicha', 'South Bishwa Road', 'Sutrapur', 'Vatara',
            'Zinzira',
        ];

        // Only insert names not already present for Dhaka
        $existing = DB::table('areas')
            ->where('district_id', 1)
            ->pluck('name')
            ->map(fn($n) => strtolower($n))
            ->toArray();

        $rows = collect($extra)
            ->filter(fn($n) => !in_array(strtolower($n), $existing))
            ->map(fn($n) => ['district_id' => 1, 'name' => $n])
            ->values()
            ->toArray();

        if ($rows) {
            DB::table('areas')->insert($rows);
        }
    }

    public function down(): void
    {
        $names = [
            'Aftabnagar','Agargaon','Aminbazar','Banasree','Basabo',
            'Chawkbazar','Donia','Eskaton','Green Road','Hatirjheel',
            'Hazaribagh','Jigatola','Kalabagan','Kamalapur','Karwan Bazar',
            'Lalmatia','Manda','Matuail','Mirpur-13','Mirpur-14',
            'New Market','Niketan','Panthapath','Sabujbagh','Shantinagar',
            'Shegunbagicha','South Bishwa Road','Sutrapur','Vatara','Zinzira',
        ];
        DB::table('areas')->where('district_id', 1)->whereIn('name', $names)->delete();
    }
};
