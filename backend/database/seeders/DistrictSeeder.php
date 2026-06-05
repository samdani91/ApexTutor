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
            ['name' => 'Dhaka', 'name_bn' => 'ঢাকা', 'division' => 'Dhaka'],
            ['name' => 'Chittagong', 'name_bn' => 'চট্টগ্রাম', 'division' => 'Chittagong'],
            ['name' => 'Rajshahi', 'name_bn' => 'রাজশাহী', 'division' => 'Rajshahi'],
            ['name' => 'Khulna', 'name_bn' => 'খুলনা', 'division' => 'Khulna'],
            ['name' => 'Barisal', 'name_bn' => 'বরিশাল', 'division' => 'Barisal'],
            ['name' => 'Sylhet', 'name_bn' => 'সিলেট', 'division' => 'Sylhet'],
            ['name' => 'Rangpur', 'name_bn' => 'রংপুর', 'division' => 'Rangpur'],
            ['name' => 'Mymensingh', 'name_bn' => 'ময়মনসিংহ', 'division' => 'Mymensingh'],
            ['name' => 'Gazipur', 'name_bn' => 'গাজীপুর', 'division' => 'Dhaka'],
            ['name' => 'Narayanganj', 'name_bn' => 'নারায়ণগঞ্জ', 'division' => 'Dhaka'],
            ['name' => 'Narsingdi', 'name_bn' => 'নরসিংদী', 'division' => 'Dhaka'],
            ['name' => 'Manikganj', 'name_bn' => 'মানিকগঞ্জ', 'division' => 'Dhaka'],
            ['name' => 'Munshiganj', 'name_bn' => 'মুন্সিগঞ্জ', 'division' => 'Dhaka'],
            ['name' => 'Tangail', 'name_bn' => 'টাঙ্গাইল', 'division' => 'Dhaka'],
            ['name' => 'Faridpur', 'name_bn' => 'ফরিদপুর', 'division' => 'Dhaka'],
            ['name' => 'Comilla', 'name_bn' => 'কুমিল্লা', 'division' => 'Chittagong'],
            ['name' => "Cox's Bazar", 'name_bn' => "কক্সবাজার", 'division' => 'Chittagong'],
            ['name' => 'Feni', 'name_bn' => 'ফেনী', 'division' => 'Chittagong'],
            ['name' => 'Noakhali', 'name_bn' => 'নোয়াখালী', 'division' => 'Chittagong'],
            ['name' => 'Chandpur', 'name_bn' => 'চাঁদপুর', 'division' => 'Chittagong'],
            ['name' => 'Bogura', 'name_bn' => 'বগুড়া', 'division' => 'Rajshahi'],
            ['name' => 'Pabna', 'name_bn' => 'পাবনা', 'division' => 'Rajshahi'],
            ['name' => 'Natore', 'name_bn' => 'নাটোর', 'division' => 'Rajshahi'],
            ['name' => 'Sirajganj', 'name_bn' => 'সিরাজগঞ্জ', 'division' => 'Rajshahi'],
            ['name' => 'Jessore', 'name_bn' => 'যশোর', 'division' => 'Khulna'],
            ['name' => 'Satkhira', 'name_bn' => 'সাতক্ষীরা', 'division' => 'Khulna'],
            ['name' => 'Bagerhat', 'name_bn' => 'বাগেরহাট', 'division' => 'Khulna'],
            ['name' => 'Habiganj', 'name_bn' => 'হবিগঞ্জ', 'division' => 'Sylhet'],
            ['name' => 'Moulvibazar', 'name_bn' => 'মৌলভীবাজার', 'division' => 'Sylhet'],
            ['name' => 'Sunamganj', 'name_bn' => 'সুনামগঞ্জ', 'division' => 'Sylhet'],
            ['name' => 'Dinajpur', 'name_bn' => 'দিনাজপুর', 'division' => 'Rangpur'],
            ['name' => 'Gaibandha', 'name_bn' => 'গাইবান্ধা', 'division' => 'Rangpur'],
            ['name' => 'Kurigram', 'name_bn' => 'কুড়িগ্রাম', 'division' => 'Rangpur'],
            ['name' => 'Lalmonirhat', 'name_bn' => 'লালমনিরহাট', 'division' => 'Rangpur'],
            ['name' => 'Nilphamari', 'name_bn' => 'নীলফামারী', 'division' => 'Rangpur'],
            ['name' => 'Panchagarh', 'name_bn' => 'পঞ্চগড়', 'division' => 'Rangpur'],
            ['name' => 'Thakurgaon', 'name_bn' => 'ঠাকুরগাঁও', 'division' => 'Rangpur'],
            ['name' => 'Jamalpur', 'name_bn' => 'জামালপুর', 'division' => 'Mymensingh'],
            ['name' => 'Kishoreganj', 'name_bn' => 'কিশোরগঞ্জ', 'division' => 'Mymensingh'],
            ['name' => 'Netrokona', 'name_bn' => 'নেত্রকোণা', 'division' => 'Mymensingh'],
            ['name' => 'Sherpur', 'name_bn' => 'শেরপুর', 'division' => 'Mymensingh'],
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
