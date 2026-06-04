<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DistrictSeeder::class,
            AreaSeeder::class,
            SubjectSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
