<?php
namespace Database\Seeders;

use App\Models\GuardianProfile;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Password@123';

        $tutors = [
            ['name' => 'Arif Hossain',    'email' => 'arif.tutor@example.com',    'phone' => '01711000001'],
            ['name' => 'Nusrat Jahan',    'email' => 'nusrat.tutor@example.com',  'phone' => '01711000002'],
            ['name' => 'Tanvir Ahmed',    'email' => 'tanvir.tutor@example.com',  'phone' => '01711000003'],
            ['name' => 'Sadia Islam',     'email' => 'sadia.tutor@example.com',   'phone' => '01711000004'],
            ['name' => 'Mahmudul Hasan',  'email' => 'mahmudul.tutor@example.com','phone' => '01711000005'],
            ['name' => 'Farida Khanam',   'email' => 'farida.tutor@example.com',  'phone' => '01711000006'],
            ['name' => 'Rasel Miah',      'email' => 'rasel.tutor@example.com',   'phone' => '01711000007'],
            ['name' => 'Sumaiya Akter',   'email' => 'sumaiya.tutor@example.com', 'phone' => '01711000008'],
            ['name' => 'Imran Hossain',   'email' => 'imran.tutor@example.com',   'phone' => '01711000009'],
            ['name' => 'Sabbir Rahman',   'email' => 'sabbir.tutor@example.com',  'phone' => '01711000010'],
            ['name' => 'Tahmina Sultana', 'email' => 'tahmina.tutor@example.com', 'phone' => '01711000011'],
            ['name' => 'Mizanur Rahman',  'email' => 'mizan.tutor@example.com',   'phone' => '01711000012'],
            ['name' => 'Sharmin Akter',   'email' => 'sharmin.tutor@example.com', 'phone' => '01711000013'],
            ['name' => 'Nazmul Huda',     'email' => 'nazmul.tutor@example.com',  'phone' => '01711000014'],
            ['name' => 'Parveen Akter',   'email' => 'parveen.tutor@example.com', 'phone' => '01711000015'],
        ];

        $guardians = [
            ['name' => 'Karim Abdullah',  'email' => 'karim.guardian@example.com',  'phone' => '01822000001'],
            ['name' => 'Rokeya Begum',    'email' => 'rokeya.guardian@example.com', 'phone' => '01822000002'],
            ['name' => 'Shafiqul Islam',  'email' => 'shafiq.guardian@example.com', 'phone' => '01822000003'],
            ['name' => 'Nasrin Sultana',  'email' => 'nasrin.guardian@example.com', 'phone' => '01822000004'],
            ['name' => 'Anwar Hossain',   'email' => 'anwar.guardian@example.com',  'phone' => '01822000005'],
            ['name' => 'Dilruba Akter',   'email' => 'dilruba.guardian@example.com','phone' => '01822000006'],
            ['name' => 'Zahir Uddin',     'email' => 'zahir.guardian@example.com',  'phone' => '01822000007'],
            ['name' => 'Meher Nigar',     'email' => 'meher.guardian@example.com',  'phone' => '01822000008'],
            ['name' => 'Rezaul Karim',    'email' => 'rezaul.guardian@example.com', 'phone' => '01822000009'],
            ['name' => 'Farhana Yasmin',  'email' => 'farhana.guardian@example.com','phone' => '01822000010'],
            ['name' => 'Motiur Rahman',   'email' => 'motiur.guardian@example.com', 'phone' => '01822000011'],
            ['name' => 'Shahnaz Parvin',  'email' => 'shahnaz.guardian@example.com','phone' => '01822000012'],
            ['name' => 'Belal Hossain',   'email' => 'belal.guardian@example.com',  'phone' => '01822000013'],
            ['name' => 'Ruma Khatun',     'email' => 'ruma.guardian@example.com',   'phone' => '01822000014'],
            ['name' => 'Jamal Uddin',     'email' => 'jamal.guardian@example.com',  'phone' => '01822000015'],
        ];

        foreach ($tutors as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->name              = $data['name'];
            $user->phone             = $data['phone'];
            $user->password          = Hash::make($password);
            $user->role              = 'tutor';
            $user->is_active         = true;
            $user->email_verified_at = now();
            $user->phone_verified_at = now();
            $user->save();
            TutorProfile::firstOrCreate(['user_id' => $user->id]);
        }

        foreach ($guardians as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->name              = $data['name'];
            $user->phone             = $data['phone'];
            $user->password          = Hash::make($password);
            $user->role              = 'guardian';
            $user->is_active         = true;
            $user->email_verified_at = now();
            $user->phone_verified_at = now();
            $user->save();
            GuardianProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['account_type' => 'guardian']
            );
        }
    }
}
