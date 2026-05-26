<?php
namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'name_bn' => 'গণিত', 'class_level' => '1-10', 'medium' => null],
            ['name' => 'Higher Math', 'name_bn' => 'উচ্চতর গণিত', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Physics', 'name_bn' => 'পদার্থবিজ্ঞান', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Chemistry', 'name_bn' => 'রসায়ন', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Biology', 'name_bn' => 'জীববিজ্ঞান', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'English', 'name_bn' => 'ইংরেজি', 'class_level' => '1-12', 'medium' => null],
            ['name' => 'Bangla', 'name_bn' => 'বাংলা', 'class_level' => '1-12', 'medium' => null],
            ['name' => 'ICT', 'name_bn' => 'আইসিটি', 'class_level' => '6-12', 'medium' => null],
            ['name' => 'Accounting', 'name_bn' => 'হিসাববিজ্ঞান', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Economics', 'name_bn' => 'অর্থনীতি', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Geography', 'name_bn' => 'ভূগোল', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'History', 'name_bn' => 'ইতিহাস', 'class_level' => '9-12', 'medium' => null],
            ['name' => 'Religion (Islam)', 'name_bn' => 'ইসলাম ধর্ম', 'class_level' => '1-10', 'medium' => null],
            ['name' => 'IELTS', 'name_bn' => 'IELTS', 'class_level' => 'University', 'medium' => null],
            ['name' => 'University Math', 'name_bn' => 'বিশ্ববিদ্যালয় গণিত', 'class_level' => 'University', 'medium' => null],
            ['name' => 'Admission Test Prep', 'name_bn' => 'ভর্তি পরীক্ষা প্রস্তুতি', 'class_level' => 'HSC', 'medium' => null],
            ['name' => 'O Level Math', 'name_bn' => 'O Level Math', 'class_level' => 'O Level', 'medium' => 'english_medium'],
            ['name' => 'A Level Physics', 'name_bn' => 'A Level Physics', 'class_level' => 'A Level', 'medium' => 'english_medium'],
        ];
        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
