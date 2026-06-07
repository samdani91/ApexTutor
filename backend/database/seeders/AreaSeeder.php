<?php
namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        // Keyed by district name — IDs are resolved at runtime so the seeder
        // stays correct regardless of insertion order in DistrictSeeder.
        $seed = [
            // ── Barishal Division ─────────────────────────────────────────
            'Barguna' => [
                'Barguna Sadar', 'Amtali', 'Betagi', 'Pathorghata', 'Taltali', 'Bamna',
            ],
            'Barisal' => [
                'Barisal Sadar', 'Babuganj', 'Bakerganj', 'Banaripara', 'Agailjhara',
                'Gournadi', 'Hizla', 'Mehendiganj', 'Muladi', 'Wazirpur',
            ],
            'Bhola' => [
                'Bhola Sadar', 'Charfesson', 'Doulatkhan', 'Lalmohan', 'Monpura', 'Tazumuddin',
            ],
            'Jhalokati' => [
                'Jhalakathi Sadar', 'Kathalia', 'Nalchity', 'Rajapur',
            ],
            'Patuakhali' => [
                'Patuakhali Sadar', 'Bauphal', 'Dashmina', 'Dumki', 'Galachipa',
                'Kalapara', 'Mirzaganj', 'Rangabali',
            ],
            'Pirojpur' => [
                'Pirojpur Sadar', 'Bhandaria', 'Indurkani', 'Kawkhali',
                'Mathbaria', 'Nazirpur', 'Nesarabad',
            ],

            // ── Chittagong Division ───────────────────────────────────────
            'Bandarban' => [
                'Bandarban Sadar', 'Alikadam', 'Lama', 'Naikhongchhari',
                'Rowangchhari', 'Ruma', 'Thanchi',
            ],
            'Brahmanbaria' => [
                'Brahmanbaria Sadar', 'Akhaura', 'Ashuganj', 'Bancharampur',
                'Kasba', 'Nabinagar', 'Nasirnagar', 'Sarail',
            ],
            'Chandpur' => [
                'Chandpur Sadar', 'Faridgonj', 'Haimchar', 'Hajiganj',
                'Kachua', 'Matlab North', 'Matlab South', 'Shahrasti',
            ],
            'Chittagong' => [
                'Anwara', 'Banshkhali', 'Boalkhali', 'Chandanaish', 'Fatikchhari',
                'Hathazari', 'Karnafuli', 'Lohagara', 'Mirsharai', 'Patiya',
                'Rangunia', 'Raozan', 'Sandwip', 'Satkania', 'Sitakunda',
            ],
            'Comilla' => [
                'Comilla Sadar', 'Barura', 'Brahmanpara', 'Burichang', 'Chandina',
                'Chauddagram', 'Daudkandi', 'Debidwar', 'Homna', 'Laksam',
                'Lalmai', 'Meghna', 'Monohargonj', 'Muradnagar', 'Nangalkot',
                'Sadarsouth', 'Titas',
            ],
            "Cox's Bazar" => [
                "Cox's Bazar Sadar", 'Chakaria', 'Eidgaon', 'Kutubdia',
                'Moheshkhali', 'Pekua', 'Ramu', 'Teknaf', 'Ukhiya',
            ],
            'Feni' => [
                'Feni Sadar', 'Chhagalnaiya', 'Daganbhuiyan', 'Fulgazi', 'Parshuram', 'Sonagazi',
            ],
            'Khagrachhari' => [
                'Khagrachhari Sadar', 'Dighinala', 'Guimara', 'Laxmichhari',
                'Manikchari', 'Matiranga', 'Mohalchari', 'Panchari', 'Ramgarh',
            ],
            'Lakshmipur' => [
                'Lakshmipur Sadar', 'Kamalnagar', 'Raipur', 'Ramganj', 'Ramgati',
            ],
            'Noakhali' => [
                'Noakhali Sadar', 'Begumganj', 'Chatkhil', 'Companiganj',
                'Hatia', 'Kabirhat', 'Senbug', 'Sonaimori', 'Subarnachar',
            ],
            'Rangamati' => [
                'Rangamati Sadar', 'Baghaichari', 'Barkal', 'Belaichari',
                'Juraichari', 'Kaptai', 'Kawkhali', 'Langadu', 'Naniarchar', 'Rajasthali',
            ],

            // ── Dhaka Division ────────────────────────────────────────────
            // Includes API upazilas + city neighbourhoods useful for tutor search
            'Dhaka' => [
                // API upazilas (outside city)
                'Dhamrai', 'Dohar', 'Keraniganj', 'Nawabganj', 'Savar',
                // City neighbourhoods
                'Adabor', 'Aftabnagar', 'Agargaon', 'Aminbazar', 'Azimpur',
                'Badda', 'Banani', 'Banasree', 'Baridhara', 'Bashundhara R/A',
                'Basabo', 'Cantonment', 'Chawkbazar', 'Demra', 'Dhanmondi',
                'Donia', 'Eskaton', 'Farmgate', 'Green Road', 'Gulshan',
                'Hatirjheel', 'Hazaribagh', 'Jatrabari', 'Jigatola', 'Kadamtali',
                'Kafrul', 'Kalabagan', 'Kamalapur', 'Karwan Bazar', 'Khilgaon',
                'Khilkhet', 'Lalbagh', 'Lalmatia', 'Malibagh', 'Manda',
                'Matuail', 'Mirpur-1', 'Mirpur-2', 'Mirpur-6', 'Mirpur-10',
                'Mirpur-11', 'Mirpur-12', 'Mirpur-13', 'Mirpur-14',
                'Mohammadpur', 'Motijheel', 'Mugda', 'New Market', 'Niketan',
                'Pallabi', 'Panthapath', 'Rampura', 'Rayer Bazar', 'Sabujbagh',
                'Shantinagar', 'Shegunbagicha', 'Shyamoli', 'Tejgaon', 'Uttara',
                'Vatara', 'Wari', 'Zinzira',
            ],
            'Faridpur' => [
                'Faridpur Sadar', 'Alfadanga', 'Bhanga', 'Boalmari', 'Charbhadrasan',
                'Madhukhali', 'Nagarkanda', 'Sadarpur', 'Saltha',
            ],
            'Gazipur' => [
                'Gazipur Sadar', 'Kaliakair', 'Kaliganj', 'Kapasia', 'Sreepur',
            ],
            'Gopalganj' => [
                'Gopalganj Sadar', 'Kashiani', 'Kotalipara', 'Muksudpur', 'Tungipara',
            ],
            'Kishoreganj' => [
                'Kishoreganj Sadar', 'Austagram', 'Bajitpur', 'Bhairab', 'Hossainpur',
                'Itna', 'Karimgonj', 'Katiadi', 'Kuliarchar', 'Mithamoin',
                'Nikli', 'Pakundia', 'Tarail',
            ],
            'Madaripur' => [
                'Madaripur Sadar', 'Dasar', 'Kalkini', 'Rajoir', 'Shibchar',
            ],
            'Manikganj' => [
                'Manikganj Sadar', 'Doulatpur', 'Gior', 'Harirampur',
                'Saturia', 'Shibaloy', 'Singiar',
            ],
            'Munshiganj' => [
                'Munshiganj Sadar', 'Gajaria', 'Louhajanj', 'Sirajdikhan',
                'Sreenagar', 'Tongibari',
            ],
            'Narayanganj' => [
                'Narayanganj Sadar', 'Araihazar', 'Bandar', 'Rupganj', 'Sonargaon',
            ],
            'Narsingdi' => [
                'Narsingdi Sadar', 'Belabo', 'Monohardi', 'Palash', 'Raipura', 'Shibpur',
            ],
            'Rajbari' => [
                'Rajbari Sadar', 'Baliakandi', 'Goalanda', 'Kalukhali', 'Pangsa',
            ],
            'Shariatpur' => [
                'Shariatpur Sadar', 'Bhedarganj', 'Damudya', 'Gosairhat', 'Naria', 'Zajira',
            ],
            'Tangail' => [
                'Tangail Sadar', 'Basail', 'Bhuapur', 'Delduar', 'Dhanbari',
                'Ghatail', 'Gopalpur', 'Kalihati', 'Madhupur', 'Mirzapur',
                'Nagarpur', 'Sakhipur',
            ],

            // ── Mymensingh Division ───────────────────────────────────────
            'Jamalpur' => [
                'Jamalpur Sadar', 'Bokshiganj', 'Dewangonj', 'Islampur',
                'Madarganj', 'Melandah', 'Sarishabari',
            ],
            'Mymensingh' => [
                'Mymensingh Sadar', 'Bhaluka', 'Dhobaura', 'Fulbaria', 'Gafargaon',
                'Gouripur', 'Haluaghat', 'Iswarganj', 'Muktagacha', 'Nandail',
                'Phulpur', 'Tarakanda', 'Trishal',
            ],
            'Netrakona' => [
                'Netrakona Sadar', 'Atpara', 'Barhatta', 'Durgapur', 'Kalmakanda',
                'Kendua', 'Khaliajuri', 'Madan', 'Mohongonj', 'Purbadhala',
            ],
            'Sherpur' => [
                'Sherpur Sadar', 'Jhenaigati', 'Nalitabari', 'Nokla', 'Sreebordi',
            ],

            // ── Khulna Division ───────────────────────────────────────────
            'Bagerhat' => [
                'Bagerhat Sadar', 'Chitalmari', 'Fakirhat', 'Kachua', 'Mollahat',
                'Mongla', 'Morrelganj', 'Rampal', 'Sarankhola',
            ],
            'Chuadanga' => [
                'Chuadanga Sadar', 'Alamdanga', 'Damurhuda', 'Jibannagar',
            ],
            'Jessore' => [
                'Jessore Sadar', 'Abhaynagar', 'Bagherpara', 'Chougachha',
                'Jhikargacha', 'Keshabpur', 'Manirampur', 'Sharsha',
            ],
            'Jhenaidah' => [
                'Jhenaidah Sadar', 'Harinakundu', 'Kaliganj', 'Kotchandpur',
                'Moheshpur', 'Shailkupa',
            ],
            'Khulna' => [
                'Khulna Sadar', 'Botiaghata', 'Dakop', 'Digholia', 'Dumuria',
                'Fultola', 'Koyra', 'Paikgasa', 'Rupsha', 'Terokhada',
            ],
            'Kushtia' => [
                'Kushtia Sadar', 'Bheramara', 'Daulatpur', 'Khoksa', 'Kumarkhali', 'Mirpur',
            ],
            'Magura' => [
                'Magura Sadar', 'Mohammadpur', 'Shalikha', 'Sreepur',
            ],
            'Meherpur' => [
                'Meherpur Sadar', 'Gangni', 'Mujibnagar',
            ],
            'Narail' => [
                'Narail Sadar', 'Kalia', 'Lohagara',
            ],
            'Satkhira' => [
                'Satkhira Sadar', 'Assasuni', 'Debhata', 'Kalaroa',
                'Kaliganj', 'Shyamnagar', 'Tala',
            ],

            // ── Rajshahi Division ─────────────────────────────────────────
            'Bogura' => [
                'Bogura Sadar', 'Adamdighi', 'Dhunot', 'Gabtali', 'Kahaloo',
                'Nondigram', 'Shariakandi', 'Sherpur', 'Shibganj', 'Sonatala',
            ],
            'Joypurhat' => [
                'Joypurhat Sadar', 'Akkelpur', 'Kalai', 'Khetlal', 'Panchbibi',
            ],
            'Naogaon' => [
                'Naogaon Sadar', 'Atrai', 'Badalgachi', 'Dhamoirhat', 'Manda',
                'Mohadevpur', 'Niamatpur', 'Patnitala', 'Porsha', 'Raninagar', 'Sapahar',
            ],
            'Natore' => [
                'Natore Sadar', 'Bagatipara', 'Baraigram', 'Gurudaspur', 'Lalpur',
                'Naldanga', 'Singra',
            ],
            'Chapai Nawabganj' => [
                'Chapai Nawabganj Sadar', 'Bholahat', 'Gomostapur', 'Nachol', 'Shibganj',
            ],
            'Pabna' => [
                'Pabna Sadar', 'Atghoria', 'Bera', 'Bhangura', 'Chatmohar',
                'Faridpur', 'Ishurdi', 'Santhia', 'Sujanagar',
            ],
            'Rajshahi' => [
                'Rajshahi Sadar', 'Bagha', 'Bagmara', 'Charghat', 'Durgapur',
                'Godagari', 'Mohonpur', 'Paba', 'Puthia', 'Tanore',
            ],
            'Sirajganj' => [
                'Sirajganj Sadar', 'Belkuchi', 'Chauhali', 'Kamarkhand',
                'Kazipur', 'Raigonj', 'Shahjadpur', 'Tarash', 'Ullapara',
            ],

            // ── Rangpur Division ──────────────────────────────────────────
            'Dinajpur' => [
                'Dinajpur Sadar', 'Birampur', 'Birganj', 'Birol', 'Bochaganj',
                'Chirirbandar', 'Fulbari', 'Ghoraghat', 'Hakimpur', 'Kaharol',
                'Khansama', 'Nawabganj', 'Parbatipur',
            ],
            'Gaibandha' => [
                'Gaibandha Sadar', 'Gobindaganj', 'Palashbari', 'Phulchari',
                'Sadullapur', 'Saghata', 'Sundarganj',
            ],
            'Kurigram' => [
                'Kurigram Sadar', 'Bhurungamari', 'Charrajibpur', 'Chilmari',
                'Nageshwari', 'Phulbari', 'Rajarhat', 'Rowmari', 'Ulipur',
            ],
            'Lalmonirhat' => [
                'Lalmonirhat Sadar', 'Aditmari', 'Hatibandha', 'Kaliganj', 'Patgram',
            ],
            'Nilphamari' => [
                'Nilphamari Sadar', 'Dimla', 'Domar', 'Jaldhaka', 'Kishorganj', 'Syedpur',
            ],
            'Panchagarh' => [
                'Panchagarh Sadar', 'Atwari', 'Boda', 'Debiganj', 'Tetulia',
            ],
            'Rangpur' => [
                'Rangpur Sadar', 'Badargonj', 'Gangachara', 'Kaunia', 'Mithapukur',
                'Pirgacha', 'Pirgonj',
            ],
            'Thakurgaon' => [
                'Thakurgaon Sadar', 'Baliadangi', 'Haripur', 'Pirganj',
                'Ranisankail', 'Taragonj',
            ],

            // ── Sylhet Division ───────────────────────────────────────────
            'Habiganj' => [
                'Habiganj Sadar', 'Ajmiriganj', 'Baniachong', 'Bahubal', 'Chunarughat',
                'Lakhai', 'Madhabpur', 'Nabiganj', 'Shaistaganj',
            ],
            'Maulvibazar' => [
                'Maulvibazar Sadar', 'Barlekha', 'Juri', 'Kamolganj',
                'Kulaura', 'Rajnagar', 'Sreemangal',
            ],
            'Sunamganj' => [
                'Sunamganj Sadar', 'Bishwambarpur', 'Chhatak', 'Derai',
                'Dharmapasha', 'Dowarabazar', 'Jagannathpur', 'Jamalganj',
                'Madhyanagar', 'Shantiganj', 'Shalla', 'Tahirpur',
            ],
            'Sylhet' => [
                'Sylhet Sadar', 'Balaganj', 'Bishwanath', 'Companiganj',
                'Dakshinsurma', 'Fenchuganj', 'Golapganj', 'Gowainghat',
                'Jaintiapur', 'Kanaighat', 'Osmaninagar', 'Zakiganj',
            ],
        ];

        $districtMap = District::pluck('id', 'name')->all();

        $rows = [];
        foreach ($seed as $districtName => $areas) {
            $districtId = $districtMap[$districtName] ?? null;
            if (!$districtId) continue;
            foreach (array_unique($areas) as $area) {
                $rows[] = ['district_id' => $districtId, 'name' => $area];
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('areas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('areas')->insert($rows);
    }
}
