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
            $this->dakhilSubjects(),
            $this->alimSubjects(),
            $this->englishMediumPrimarySubjects(),
            $this->madrashaBasicSubjects(),
        );
        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                [
                    'name' => $subject['name'],
                    'class_level' => $subject['class_level'],
                    'medium' => $subject['medium'] ?? null,
                ],
                [
                    'name_bn' => $subject['name_bn'] ?? null,
                    'group'   => $subject['group'] ?? null,
                ]
            );
        }
    }

    private function sscSubjects(): array
    {
        // group NULL = compulsory or cross-group (shown under every group).
        // Needs a domain check before production; admins can fix from Reference Data.
        $subjects = [
            'Bangla'                                     => null,
            'English'                                    => null,
            'Mathematics'                                => null,
            'ICT'                                        => null,
            'Bangladesh & Global Studies'                => null,
            'Science'                                    => null,
            'Religion (Islam)'                           => null,
            'Religion (Hindu)'                           => null,
            'Religion (Christian)'                       => null,
            'Religion (Buddhist)'                        => null,
            'Physical Education, Health & Sports'        => null,
            'Physics'                                    => 'science',
            'Chemistry'                                  => 'science',
            'Biology'                                    => 'science',
            'Higher Mathematics'                         => 'science',
            'Accounting'                                 => 'business_studies',
            'Business Entrepreneurship'                  => 'business_studies',
            'Finance & Banking'                          => 'business_studies',
            'Economics'                                  => null, // cross business/humanities
            'History of Bangladesh & World Civilization' => 'humanities',
            'Geography & Environment'                    => 'humanities',
            'Civics & Citizenship'                       => 'humanities',
            'Home Science'                               => null,
            'Agriculture'                                => null,
            'Arabic'                                     => null,
            'Sanskrit'                                   => null,
        ];

        return collect(['class_9', 'class_10', 'ssc'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($group, $subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => null,
                'group' => $group,
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
        // See sscSubjects() note on NULL groups / domain check.
        $subjects = [
            'Bangla'                             => null,
            'English'                            => null,
            'ICT'                                => null,
            'Economics'                          => null, // cross business/humanities
            'Physics'                            => 'science',
            'Chemistry'                          => 'science',
            'Biology'                            => 'science',
            'Higher Mathematics'                 => 'science',
            'Statistics'                         => 'science',
            'Accounting'                         => 'business_studies',
            'Business Organization & Management' => 'business_studies',
            'Finance, Banking & Insurance'       => 'business_studies',
            'Civics & Good Governance'           => 'humanities',
            'History'                            => 'humanities',
            'Logic'                              => 'humanities',
            'Sociology'                          => 'humanities',
            'Geography'                          => 'humanities',
            'Social Work'                        => 'humanities',
            'Islamic History & Culture'          => 'humanities',
        ];

        return collect($subjects)->map(fn ($group, $subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'hsc',
            'medium' => null,
            'group' => $group,
        ])->values()->all();
    }

    private function admissionTestSubjects(): array
    {
        // See sscSubjects() note on NULL groups / domain check.
        $subjects = [
            'Bangla'                             => null,
            'English'                            => null,
            'ICT'                                => null,
            'Economics'                          => null, // cross business/humanities
            'General Knowledge'                  => null,
            'Analytical Ability'                 => null,
            'Physics'                            => 'science',
            'Chemistry'                          => 'science',
            'Biology'                            => 'science',
            'Higher Mathematics'                 => 'science',
            'Statistics'                         => 'science',
            'Accounting'                         => 'business_studies',
            'Business Organization & Management' => 'business_studies',
            'Finance, Banking & Insurance'       => 'business_studies',
        ];

        return collect($subjects)->map(fn ($group, $subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'admission_test',
            'medium' => null,
            'group' => $group,
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

    /**
     * Dakhil — Madrasah Education Board's SSC equivalent (grades 6-10).
     *
     * Names deliberately mirror sscSubjects() — plain subject names, no 1st/2nd
     * paper split — so the same subject reads identically across class levels.
     *
     * NOTE: needs a domain check before it ships. Assembled from public sources
     * that disagreed with each other; the Islamic core and the three group names
     * are corroborated, the optional subjects least so.
     */
    private function dakhilSubjects(): array
    {
        $subjects = [
            // Islamic core
            'Quran Majeed & Tajweed',
            'Hadith Sharif',
            'Aqaid & Fiqh',
            'Arabic',
            'Islamic History',
            // General compulsory
            'Bangla',
            'English',
            'Mathematics',
            'Science',
            'Bangladesh & Global Studies',
            'ICT',
            'Physical Education, Health & Sports',
            // Science group
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            // General group
            'Civics & Citizenship',
            'Economics',
            'Geography & Environment',
            // Muzabbid (business) group
            'Accounting',
            'Business Entrepreneurship',
            'Finance & Banking',
            // Optional
            'Agriculture',
            'Home Science',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'dakhil',
            'medium' => null,
        ])->values()->all();
    }

    /**
     * Alim — Madrasah Education Board's HSC equivalent.
     *
     * Names mirror hscSubjects(). Same caveat as dakhilSubjects() — needs a
     * domain check before it ships.
     */
    private function alimSubjects(): array
    {
        $subjects = [
            // Islamic core
            'Quran Majeed',
            'Hadith Sharif',
            'Al Fiqh',
            'Arabic',
            'Balagat & Mantiq',
            'Islamic History & Culture',
            // General compulsory
            'Bangla',
            'English',
            'ICT',
            // Science group
            'Physics',
            'Chemistry',
            'Biology',
            'Higher Mathematics',
            // General group
            'Civics & Good Governance',
            'Economics',
            'Logic',
            // Optional
            'Urdu',
            'Persian',
        ];

        return collect($subjects)->map(fn ($subject) => [
            'name' => $subject,
            'name_bn' => null,
            'class_level' => 'alim',
            'medium' => null,
        ])->values()->all();
    }

    /**
     * English Medium primary years (Class 1–7). Class 8–10 fall under O Level and
     * 11–12 under A Level, so they are not seeded here.
     *
     * Tagged medium='english_medium' so they sit alongside — not on top of — the
     * national-curriculum rows that share these same class levels. A starter set;
     * admins can edit or extend it from Reference Data.
     */
    private function englishMediumPrimarySubjects(): array
    {
        $subjects = [
            'English Language',
            'English Literature',
            'Mathematics',
            'Science',
            'Bengali',
            'History/Geography',
            'Computer Science / ICT',
            'Religious Studies',
            'Art',
            'Music',
            'Dance',
            'Physical Education',
        ];

        return collect(['class_1', 'class_2', 'class_3', 'class_4', 'class_5', 'class_6', 'class_7'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => 'english_medium',
            ]))
            ->values()
            ->all();
    }

    /**
     * Madrasha primary years (Class 1–8). Dakhil/Alim (the board-exam levels)
     * are seeded separately above. Only the core subjects are seeded for now;
     * admins add the Islamic/optional ones from Reference Data.
     */
    private function madrashaBasicSubjects(): array
    {
        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'Science',
        ];

        return collect(['class_1', 'class_2', 'class_3', 'class_4', 'class_5', 'class_6', 'class_7', 'class_8'])
            ->flatMap(fn ($classLevel) => collect($subjects)->map(fn ($subject) => [
                'name' => $subject,
                'name_bn' => null,
                'class_level' => $classLevel,
                'medium' => 'madrasha',
            ]))
            ->values()
            ->all();
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
