<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ConnectionRequest;
use App\Models\District;
use App\Models\Subject;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorSearchController extends Controller
{
    public function landingStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'verified_tutors' => TutorProfile::where('status', 'active')
                    ->where('is_verified', true)
                    ->count(),
                'districts' => District::count(),
                'student_matches' => ConnectionRequest::where('status', 'confirmed')->count(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'medium'       => 'nullable|in:bangla_medium,english_medium,english_version',
            'class_level'  => 'nullable|string',
            'subject_ids'  => 'nullable|string',
            'district_id'  => 'nullable|integer',
            'area_id'      => 'nullable|integer|exists:areas,id',
            'tutor_gender' => 'nullable|in:male,female,no_preference',
            'days_per_week'        => 'nullable|integer|min:1|max:7',
            'hours_per_day'        => 'nullable|numeric',
            'place_of_tutoring'    => 'nullable|array',
            'place_of_tutoring.*'  => 'in:student_home,tutor_home,online',
            'tutoring_styles'      => 'nullable|array',
            'tutoring_styles.*'    => 'in:one_to_one,group,online',
            'salary_max'           => 'nullable|integer',
            'min_rating'   => 'nullable|numeric|min:1|max:5',
            'verified_only'=> 'nullable|boolean',
            'sort'         => 'nullable|in:relevance,rating,newest,salary_asc,salary_desc',
            'per_page'     => 'nullable|integer|min:1|max:24',
            'page'         => 'nullable|integer|min:1',
        ]);

        if (!empty($filters['subject_ids'])) {
            $filters['subject_ids'] = array_map('intval', explode(',', $filters['subject_ids']));
        }

        $query = TutorProfile::query()
            ->with([
                'user:id,name,avatar',
                'tuitionPreference:id,tutor_profile_id,district_id,preferred_curricula,preferred_classes,expected_salary_min,expected_salary_max,tutoring_methods,tutoring_styles,total_experience_years,place_of_tutoring',
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.locations.area:id,name',
                'activeTravelAvailabilities.district:id,name',
                'personalInfo:id,tutor_profile_id,gender',
            ])
            ->where('status', 'active')
            ->where('is_verified', true);

        if (!empty($filters['medium'])) {
            $query->whereHas('tuitionPreference', fn($q) => $q->whereJsonContains('preferred_curricula', $filters['medium']));
        }
        if (!empty($filters['class_level'])) {
            $query->whereHas('tuitionPreference', fn($q) => $q->whereJsonContains('preferred_classes', $filters['class_level']));
        }
        if (!empty($filters['subject_ids'])) {
            $ids = $filters['subject_ids'];
            $query->whereHas('tuitionPreference.subjects', fn($q) => $q->whereIn('subjects.id', $ids));
        }
        if (!empty($filters['district_id'])) {
            $did = $filters['district_id'];
            $query->where(function ($q) use ($did) {
                $q->whereHas('tuitionPreference', fn($q) => $q->where('district_id', $did))
                  ->orWhereHas('activeTravelAvailabilities', fn($q) => $q->where('district_id', $did));
            });
        }
        if (!empty($filters['area_id'])) {
            $query->whereHas('tuitionPreference.locations', fn($q) => $q->where('area_id', $filters['area_id']));
        }
        if (!empty($filters['tutor_gender']) && $filters['tutor_gender'] !== 'no_preference') {
            $query->whereHas('personalInfo', fn($q) => $q->where('gender', $filters['tutor_gender']));
        }
        if (!empty($filters['days_per_week'])) {
            $query->whereHas('tuitionPreference', fn($q) => $q->where('days_per_week', $filters['days_per_week']));
        }
        if (!empty($filters['hours_per_day'])) {
            $query->whereHas('tuitionPreference', fn($q) => $q->where('hours_per_day', (float)$filters['hours_per_day']));
        }
        if (!empty($filters['place_of_tutoring'])) {
            $places = $filters['place_of_tutoring'];
            $query->whereHas('tuitionPreference', function ($q) use ($places) {
                $q->where(function ($inner) use ($places) {
                    foreach ($places as $place) {
                        $inner->orWhereJsonContains('place_of_tutoring', $place);
                    }
                });
            });
        }
        if (!empty($filters['tutoring_styles'])) {
            $styles = $filters['tutoring_styles'];
            $query->whereHas('tuitionPreference', function ($q) use ($styles) {
                $q->where(function ($inner) use ($styles) {
                    foreach ($styles as $style) {
                        $inner->orWhereJsonContains('tutoring_styles', $style);
                    }
                });
            });
        }
        if (!empty($filters['salary_max'])) {
            $query->whereHas('tuitionPreference', fn($q) => $q->where('expected_salary_min', '<=', (int)$filters['salary_max']));
        }
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', (float)$filters['min_rating']);
        }
        if (!empty($filters['verified_only'])) {
            $query->where('is_verified', true);
        }

        $sort = $filters['sort'] ?? 'relevance';
        match($sort) {
            'rating'      => $query->orderByDesc('rating'),
            'newest'      => $query->latest(),
            'salary_asc'  => $query->orderByRaw('(SELECT expected_salary_min FROM tuition_preferences WHERE tutor_profile_id = tutor_profiles.id LIMIT 1) ASC'),
            'salary_desc' => $query->orderByRaw('(SELECT expected_salary_max FROM tuition_preferences WHERE tutor_profile_id = tutor_profiles.id LIMIT 1) DESC'),
            default       => $query->orderByRaw('(profile_completion_percent/100*4)+(is_verified*3)+(rating/5*2)+(LEAST(review_count,50)/50)+(LEAST(profile_view_count,1000)/1000) DESC'),
        };

        $results = $query->paginate($filters['per_page'] ?? 12);
        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * Resolve a free-text search term into structured search filters.
     * Recognises medium, class level, subject, area and district so a typed
     * query like "physics" or "dhanmondi" auto-selects the matching filters.
     */
    public function resolve(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $term = trim((string) $request->query('q', ''));
        if ($term === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Padded with spaces so phrase removal leaves clean word boundaries.
        $remaining = ' ' . mb_strtolower($term) . ' ';
        $filters   = [];

        // 1. Medium — requires an explicit "medium"/"version" word so a bare
        //    "english" stays available as a subject match below.
        $mediumAliases = [
            'english_version' => ['english version'],
            'english_medium'  => ['english medium'],
            'bangla_medium'   => ['bangla medium', 'bengali medium'],
        ];
        foreach ($mediumAliases as $value => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($remaining, $alias)) {
                    $filters['medium'] = $value;
                    $remaining = str_replace($alias, ' ', $remaining);
                    break 2;
                }
            }
        }

        // 2. Class level — higher/longer aliases first so "class 1" never
        //    swallows "class 10".
        $classAliases = [
            'hsc'      => ['hsc', 'higher secondary', 'intermediate'],
            'ssc'      => ['ssc'],
            'a_level'  => ['a level', 'a-level', 'alevel'],
            'o_level'  => ['o level', 'o-level', 'olevel'],
            'class_10' => ['class 10', 'class-10', 'grade 10'],
            'class_9'  => ['class 9', 'class-9', 'grade 9'],
            'class_8'  => ['class 8', 'grade 8'],
            'class_7'  => ['class 7', 'grade 7'],
            'class_6'  => ['class 6', 'grade 6'],
            'class_5'  => ['class 5', 'grade 5'],
            'class_4'  => ['class 4', 'grade 4'],
            'class_3'  => ['class 3', 'grade 3'],
            'class_2'  => ['class 2', 'grade 2'],
            'class_1'  => ['class 1', 'grade 1'],
        ];
        foreach ($classAliases as $value => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($remaining, $alias)) {
                    $filters['class_level'] = $value;
                    $remaining = str_replace($alias, ' ', $remaining);
                    break 2;
                }
            }
        }

        $remaining = trim(preg_replace('/\s+/', ' ', $remaining));

        if ($remaining !== '') {
            // 3. Subject — restricted to the per-class taxonomy the filter UI
            //    understands (the keys above), so legacy range rows like
            //    "9-12" or "University" are never adopted as a class_level.
            $subjectQuery = Subject::query()
                ->whereIn('class_level', array_keys($classAliases));
            if (!empty($filters['class_level'])) {
                $subjectQuery->where('class_level', $filters['class_level']);
            }
            $subject = (clone $subjectQuery)->whereRaw('LOWER(name) = ?', [$remaining])->first()
                ?? $subjectQuery
                    ->where(fn($q) => $q->where('name', 'like', "%{$remaining}%")
                        ->orWhere('name_bn', 'like', "%{$remaining}%"))
                    ->orderByRaw('LENGTH(name)')
                    ->first();

            if ($subject) {
                $filters['subject_ids'] = (string) $subject->id;
                if (empty($filters['class_level'])) {
                    $filters['class_level'] = $subject->class_level;
                }
            }

            // 4. Area (also fixes the parent district) then district fallback.
            $area = Area::where('name', 'like', "%{$remaining}%")
                ->orderByRaw('LENGTH(name)')
                ->first();
            if ($area) {
                $filters['area_id']     = $area->id;
                $filters['district_id'] = $area->district_id;
            } elseif (empty($filters['district_id'])) {
                $district = District::where('name', 'like', "%{$remaining}%")
                    ->orderByRaw('LENGTH(name)')
                    ->first();
                if ($district) {
                    $filters['district_id'] = $district->id;
                }
            }
        }

        return response()->json(['success' => true, 'data' => $filters]);
    }

    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::when($request->class_level, fn($q) => $q->where('class_level', $request->class_level))
            ->orderBy('name')
            ->get();
        return response()->json(['success' => true, 'data' => $subjects]);
    }

    public function districts(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => District::all()]);
    }

    public function areas(Request $request): JsonResponse
    {
        $request->validate(['district_id' => 'required|exists:districts,id']);
        $areas = Area::where('district_id', $request->district_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json(['success' => true, 'data' => $areas]);
    }
}
