<?php
namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = array_merge(
            $this->primarySubjects(),
            $this->middleSchoolSubjects(),
            $this->sscSubjects(),
            $this->hscSubjects(),
            $this->admissionTestSubjects(),
            $this->olevelSubjects(),
            $this->alevelSubjects(),
            $this->universitySubjects(),
        );
        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                [
                    'name' => $subject['name'],
                    'class_level' => $subject['class_level'],
                    'medium' => $subject['medium'] ?? null,
                ],
                ['name_bn' => $subject['name_bn'] ?? null]
            );
        }
    }

    private function sscSubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'ICT',
            'Bangladesh & Global Studies',
            'Science',
            'Religion (Islam)',
            'Religion (Hindu)',
            'Religion (Christian)',
            'Religion (Buddhist)',
            'Physical Education, Health & Sports',
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            'Accounting',
            'Business Entrepreneurship',
            'Finance & Banking',
            'Economics',
            'History of Bangladesh & World Civilization',
            'Geography & Environment',
            'Civics & Citizenship',
            'Home Science',
            'Agriculture',
            'Arabic',
            'Sanskrit',
        ];

        return collect(['class_9', 'class_10', 'ssc'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => null,
            ]))
            ->values()
            ->all();
    }

    private function primarySubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'Bangladesh and Global Studies',
            'Religion (Islam)',
            'Religion (Hindu)',
            'Religion (Christian)',
            'Religion (Buddhist)',
            'Science',
        ];

        return collect(['class_1', 'class_2', 'class_3', 'class_4', 'class_5'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => null,
            ]))
            ->values()
            ->all();
    }

    private function middleSchoolSubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'Science',
            'Bangladesh and Global Studies',
            'ICT',
            'Physical Education',
            'Work and Life',
            'Agriculture Studies',
            'Home Science',
            'Arts and Crafts',
            'Songit',
            'Islamic Studies',
            'Hindu Religion',
            'Christian Religion',
            'Buddhist Religion',
            'Arabic Studies',
            'Songskrito',
            'Pali',
            'Minority Language and Culture',
        ];

        return collect(['class_6', 'class_7', 'class_8'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => null,
            ]))
            ->values()
            ->all();
    }

    private function hscSubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            'ICT',
            'Statistics',
            'Economics',
            'Civics & Good Governance',
            'History',
            'Logic',
            'Sociology',
            'Geography',
            'Social Work',
            'Islamic History & Culture',
            'Accounting',
            'Business Organization & Management',
            'Finance, Banking & Insurance',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'hsc',
            'medium' => null,
        ])->values()->all();
    }

    private function admissionTestSubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            'ICT',
            'Statistics',
            'Economics',
            'Civics & Good Governance',
            'History',
            'Logic',
            'Sociology',
            'Geography',
            'Social Work',
            'Islamic History & Culture',
            'Accounting',
            'Business Organization & Management',
            'Finance, Banking & Insurance',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'admission_test',
            'medium' => null,
        ])->values()->all();
    }

    private function olevelSubjects(): array
    {
        $subjects = [
            'Bengali',
            'English Language',
            'Mathematics (Syllabus D)',
            'Physics',
            'Chemistry',
            'Biology',
            'Additional Mathematics',
            'Business Studies',
            'Commerce',
            'Economics',
            'Principles of Accounting',
            'Bangladesh Studies',
            'Islamic Studies',
            'Art & Design',
            'Computer Science',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'o_level',
            'medium' => null,
        ])->values()->all();
    }

    private function alevelSubjects(): array
    {
        $subjects = [
            'Physics',
            'Chemistry',
            'Biology',
            'Mathematics',
            'Further Mathematics',
            'Accounting',
            'Business',
            'Economics',
            'Computer Science',
            'Information Technology',
            'English Language',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'a_level',
            'medium' => null,
        ])->values()->all();
    }

    private function universitySubjects(): array
    {
        $subjects = [
            'IELTS',
            'University Math',
            'Admission Test Prep',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'university',
            'medium' => null,
        ])->values()->all();
    }
}
