<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@tutorkhujo.com',
            'phone'    => '01700000000',
            'password' => Hash::make('Admin@123'),
            'role'     => 'super_admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }
}
