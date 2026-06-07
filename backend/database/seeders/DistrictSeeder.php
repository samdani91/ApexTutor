<?php
namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            // Barishal Division
            ['name' => 'Barguna',    'name_bn' => 'বরগুনা',       'division' => 'Barishal'],
            ['name' => 'Barisal',    'name_bn' => 'বরিশাল',       'division' => 'Barishal'],
            ['name' => 'Bhola',      'name_bn' => 'ভোলা',         'division' => 'Barishal'],
            ['name' => 'Jhalokati',  'name_bn' => 'ঝালকাঠি',      'division' => 'Barishal'],
            ['name' => 'Patuakhali', 'name_bn' => 'পটুয়াখালী',   'division' => 'Barishal'],
            ['name' => 'Pirojpur',   'name_bn' => 'পিরোজপুর',     'division' => 'Barishal'],

            // Chittagong Division
            ['name' => 'Bandarban',    'name_bn' => 'বান্দরবান',        'division' => 'Chittagong'],
            ['name' => 'Brahmanbaria', 'name_bn' => 'ব্রাহ্মণবাড়িয়া', 'division' => 'Chittagong'],
            ['name' => 'Chandpur',     'name_bn' => 'চাঁদপুর',          'division' => 'Chittagong'],
            ['name' => 'Chittagong',   'name_bn' => 'চট্টগ্রাম',        'division' => 'Chittagong'],
            ['name' => 'Comilla',      'name_bn' => 'কুমিল্লা',         'division' => 'Chittagong'],
            ['name' => "Cox's Bazar",  'name_bn' => 'কক্সবাজার',        'division' => 'Chittagong'],
            ['name' => 'Feni',         'name_bn' => 'ফেনী',             'division' => 'Chittagong'],
            ['name' => 'Khagrachhari', 'name_bn' => 'খাগড়াছড়ি',       'division' => 'Chittagong'],
            ['name' => 'Lakshmipur',   'name_bn' => 'লক্ষ্মীপুর',       'division' => 'Chittagong'],
            ['name' => 'Noakhali',     'name_bn' => 'নোয়াখালী',        'division' => 'Chittagong'],
            ['name' => 'Rangamati',    'name_bn' => 'রাঙ্গামাটি',       'division' => 'Chittagong'],

            // Dhaka Division
            ['name' => 'Dhaka',       'name_bn' => 'ঢাকা',          'division' => 'Dhaka'],
            ['name' => 'Faridpur',    'name_bn' => 'ফরিদপুর',       'division' => 'Dhaka'],
            ['name' => 'Gazipur',     'name_bn' => 'গাজীপুর',       'division' => 'Dhaka'],
            ['name' => 'Gopalganj',   'name_bn' => 'গোপালগঞ্জ',     'division' => 'Dhaka'],
            ['name' => 'Kishoreganj', 'name_bn' => 'কিশোরগঞ্জ',    'division' => 'Dhaka'],
            ['name' => 'Madaripur',   'name_bn' => 'মাদারীপুর',     'division' => 'Dhaka'],
            ['name' => 'Manikganj',   'name_bn' => 'মানিকগঞ্জ',     'division' => 'Dhaka'],
            ['name' => 'Munshiganj',  'name_bn' => 'মুন্সিগঞ্জ',    'division' => 'Dhaka'],
            ['name' => 'Narayanganj', 'name_bn' => 'নারায়ণগঞ্জ',   'division' => 'Dhaka'],
            ['name' => 'Narsingdi',   'name_bn' => 'নরসিংদী',       'division' => 'Dhaka'],
            ['name' => 'Rajbari',     'name_bn' => 'রাজবাড়ী',      'division' => 'Dhaka'],
            ['name' => 'Shariatpur',  'name_bn' => 'শরীয়তপুর',     'division' => 'Dhaka'],
            ['name' => 'Tangail',     'name_bn' => 'টাঙ্গাইল',      'division' => 'Dhaka'],

            // Mymensingh Division
            ['name' => 'Jamalpur',   'name_bn' => 'জামালপুর',   'division' => 'Mymensingh'],
            ['name' => 'Mymensingh', 'name_bn' => 'ময়মনসিংহ',  'division' => 'Mymensingh'],
            ['name' => 'Netrakona',  'name_bn' => 'নেত্রকোণা',  'division' => 'Mymensingh'],
            ['name' => 'Sherpur',    'name_bn' => 'শেরপুর',     'division' => 'Mymensingh'],

            // Khulna Division
            ['name' => 'Bagerhat',  'name_bn' => 'বাগেরহাট',   'division' => 'Khulna'],
            ['name' => 'Chuadanga', 'name_bn' => 'চুয়াডাঙ্গা', 'division' => 'Khulna'],
            ['name' => 'Jessore',   'name_bn' => 'যশোর',        'division' => 'Khulna'],
            ['name' => 'Jhenaidah', 'name_bn' => 'ঝিনাইদহ',    'division' => 'Khulna'],
            ['name' => 'Khulna',    'name_bn' => 'খুলনা',       'division' => 'Khulna'],
            ['name' => 'Kushtia',   'name_bn' => 'কুষ্টিয়া',   'division' => 'Khulna'],
            ['name' => 'Magura',    'name_bn' => 'মাগুরা',      'division' => 'Khulna'],
            ['name' => 'Meherpur',  'name_bn' => 'মেহেরপুর',   'division' => 'Khulna'],
            ['name' => 'Narail',    'name_bn' => 'নড়াইল',      'division' => 'Khulna'],
            ['name' => 'Satkhira',  'name_bn' => 'সাতক্ষীরা',  'division' => 'Khulna'],

            // Rajshahi Division
            ['name' => 'Bogura',           'name_bn' => 'বগুড়া',           'division' => 'Rajshahi'],
            ['name' => 'Joypurhat',        'name_bn' => 'জয়পুরহাট',        'division' => 'Rajshahi'],
            ['name' => 'Naogaon',          'name_bn' => 'নওগাঁ',            'division' => 'Rajshahi'],
            ['name' => 'Natore',           'name_bn' => 'নাটোর',            'division' => 'Rajshahi'],
            ['name' => 'Chapai Nawabganj', 'name_bn' => 'চাঁপাইনবাবগঞ্জ',  'division' => 'Rajshahi'],
            ['name' => 'Pabna',            'name_bn' => 'পাবনা',            'division' => 'Rajshahi'],
            ['name' => 'Rajshahi',         'name_bn' => 'রাজশাহী',          'division' => 'Rajshahi'],
            ['name' => 'Sirajganj',        'name_bn' => 'সিরাজগঞ্জ',        'division' => 'Rajshahi'],

            // Rangpur Division
            ['name' => 'Dinajpur',    'name_bn' => 'দিনাজপুর',    'division' => 'Rangpur'],
            ['name' => 'Gaibandha',   'name_bn' => 'গাইবান্ধা',   'division' => 'Rangpur'],
            ['name' => 'Kurigram',    'name_bn' => 'কুড়িগ্রাম',   'division' => 'Rangpur'],
            ['name' => 'Lalmonirhat', 'name_bn' => 'লালমনিরহাট',  'division' => 'Rangpur'],
            ['name' => 'Nilphamari',  'name_bn' => 'নীলফামারী',   'division' => 'Rangpur'],
            ['name' => 'Panchagarh',  'name_bn' => 'পঞ্চগড়',     'division' => 'Rangpur'],
            ['name' => 'Rangpur',     'name_bn' => 'রংপুর',       'division' => 'Rangpur'],
            ['name' => 'Thakurgaon',  'name_bn' => 'ঠাকুরগাঁও',   'division' => 'Rangpur'],

            // Sylhet Division
            ['name' => 'Habiganj',    'name_bn' => 'হবিগঞ্জ',     'division' => 'Sylhet'],
            ['name' => 'Maulvibazar', 'name_bn' => 'মৌলভীবাজার',  'division' => 'Sylhet'],
            ['name' => 'Sunamganj',   'name_bn' => 'সুনামগঞ্জ',   'division' => 'Sylhet'],
            ['name' => 'Sylhet',      'name_bn' => 'সিলেট',        'division' => 'Sylhet'],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('areas')->truncate();
        District::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        foreach ($districts as $district) {
            District::create($district);
        }
    }
}
