<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL');
        $phone    = env('ADMIN_PHONE');
        $password = env('ADMIN_PASSWORD');

        if (!$email || !$phone || !$password) {
            $this->command->error('AdminSeeder: ADMIN_EMAIL, ADMIN_PHONE, and ADMIN_PASSWORD must be set in .env');
            return;
        }

        User::create([
            'name'              => env('ADMIN_NAME', 'Super Admin'),
            'email'             => $email,
            'phone'             => $phone,
            'password'          => Hash::make($password),
            'role'              => 'super_admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }
}
