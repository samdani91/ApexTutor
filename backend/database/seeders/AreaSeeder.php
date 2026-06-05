<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        // Keyed by district_id (matches DistrictSeeder insertion order).
        $seed = [
            1  => [ // Dhaka
                'Adabor','Azimpur','Badda','Banani','Baridhara','Bashundhara R/A',
                'Cantonment','Demra','Dhanmondi','Farmgate','Gulshan','Jatrabari',
                'Kadamtali','Kafrul','Khilgaon','Khilkhet','Lalbagh','Malibagh',
                'Mirpur-1','Mirpur-2','Mirpur-6','Mirpur-10','Mirpur-11','Mirpur-12',
                'Mohammadpur','Motijheel','Mugda','Pallabi','Rampura','Rayer Bazar',
                'Shyamoli','Tejgaon','Uttara','Wari',
                // Additional Dhaka areas
                'Aftabnagar','Agargaon','Aminbazar','Banasree','Basabo',
                'Chawkbazar','Donia','Eskaton','Green Road','Hatirjheel',
                'Hazaribagh','Jigatola','Kalabagan','Kamalapur','Karwan Bazar',
                'Lalmatia','Manda','Matuail','Mirpur-13','Mirpur-14',
                'New Market','Niketan','Panthapath','Sabujbagh','Shantinagar',
                'Shegunbagicha','South Bishwa Road','Sutrapur','Vatara','Zinzira',
            ],
            2  => [ // Chittagong
                'Agrabad','Bakalia','Chawkbazar','CDA Avenue','Dampara',
                'Double Mooring','GEC Circle','Halishahar','Khulshi','Lalkhan Bazar',
                'Muradpur','Nasirabad','Pahartali','Panchlaish','Patenga','Sholoshahar',
            ],
            3  => ['Boalia','Motihar','Rajpara','Shah Makhdum','Upashahar'], // Rajshahi
            4  => ['Daulatpur','Khalishpur','Khan Jahan Ali','Khulna Sadar','Sonadanga'], // Khulna
            5  => ['Barisal Sadar','Bandh Road','Natullabad','Rupatali'], // Barisal
            6  => ['Jalalabad','Moglabazar','North Surma','Shahporan','Sylhet Sadar','Zindabazar'], // Sylhet
            7  => ['Kaunia','Mithapukur','Pirganj','Rangpur Sadar','Tajhat'], // Rangpur
            8  => ['Muktagacha','Mymensingh Sadar','Trishal','Valuka'], // Mymensingh
            9  => ['Board Bazar','Joydebpur','Kaliakoir','Kapasia','Sreepur','Tongi'], // Gazipur
            10 => ['Araihazar','Fatullah','Narayanganj Sadar','Rupganj','Siddhirganj'], // Narayanganj
            11 => ['Narsingdi Sadar','Palash','Raipura','Shibpur'], // Narsingdi
            12 => ['Manikganj Sadar','Saturia','Singair'], // Manikganj
            13 => ['Munshiganj Sadar','Sreenagar','Tongibari'], // Munshiganj
            14 => ['Bhuapur','Tangail Sadar','Mirzapur'], // Tangail
            15 => ['Faridpur Sadar','Bhanga','Nagarkanda'], // Faridpur
            16 => ['Comilla Sadar','Brahmanpara','Chandina','Laksam'], // Comilla
            17 => ["Cox's Bazar Sadar",'Chakaria','Ramu','Ukhiya'], // Cox's Bazar
            18 => ['Feni Sadar','Chhagalnaiya','Sonagazi'], // Feni
            19 => ['Noakhali Sadar','Begumganj','Chowmuhani','Maijdee'], // Noakhali
            20 => ['Chandpur Sadar','Faridganj','Haimchar'], // Chandpur
            21 => ['Bogura Sadar','Gabtali','Sariakandi','Sherpur'], // Bogura
            22 => ['Pabna Sadar','Bera','Ishwardi'], // Pabna
            23 => ['Natore Sadar','Baraigram','Lalpur'], // Natore
            24 => ['Sirajganj Sadar','Belkuchi','Kazipur'], // Sirajganj
            25 => ['Jessore Sadar','Abhaynagar','Benapole','Jhikargachha'], // Jessore
            26 => ['Satkhira Sadar','Kalaroa','Tala'], // Satkhira
            27 => ['Bagerhat Sadar','Fakirhat','Morrelganj'], // Bagerhat
            28 => ['Habiganj Sadar','Bahubal','Chunarughat'], // Habiganj
            29 => ['Moulvibazar Sadar','Barlekha','Kulaura','Sreemangal'], // Moulvibazar
            30 => ['Sunamganj Sadar','Chhatak','Jagannathpur'], // Sunamganj
            31 => ['Dinajpur Sadar','Birampur','Chirirbandar'], // Dinajpur
        ];

        $rows = [];
        foreach ($seed as $districtId => $names) {
            foreach (array_unique($names) as $name) {
                $rows[] = ['district_id' => $districtId, 'name' => $name];
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('areas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('areas')->insert($rows);
    }
}
