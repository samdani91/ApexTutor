<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create areas table ──────────────────────────────────────────────
        Schema::create('areas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('district_id');
            $table->string('name', 100);
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->index('district_id');
        });

        // ── 2. Seed areas ──────────────────────────────────────────────────────
        $seed = [
            1  => [ // Dhaka
                'Adabor','Azimpur','Badda','Banani','Baridhara','Bashundhara R/A',
                'Cantonment','Demra','Dhanmondi','Farmgate','Gulshan','Jatrabari',
                'Kadamtali','Kafrul','Khilgaon','Khilkhet','Lalbagh','Malibagh',
                'Mirpur-1','Mirpur-2','Mirpur-6','Mirpur-10','Mirpur-11','Mirpur-12',
                'Mohammadpur','Motijheel','Mugda','Pallabi','Rampura','Rayer Bazar',
                'Shyamoli','Tejgaon','Uttara','Wari',
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
            foreach ($names as $name) {
                $rows[] = ['district_id' => $districtId, 'name' => $name];
            }
        }
        DB::table('areas')->insert($rows);

        // ── 3. Update tuition_preference_locations ─────────────────────────────
        // Clear existing free-text data (can't map to IDs)
        DB::table('tuition_preference_locations')->truncate();

        Schema::table('tuition_preference_locations', function (Blueprint $table) {
            $table->dropColumn('area_name');
            $table->unsignedSmallInteger('area_id')->after('tuition_preference_id');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tuition_preference_locations', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->string('area_name', 150)->after('tuition_preference_id');
        });
        Schema::dropIfExists('areas');
    }
};
