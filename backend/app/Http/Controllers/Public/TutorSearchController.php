<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\District;
use App\Models\Subject;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'medium'       => 'nullable|in:bangla_medium,english_medium,english_version',
            'class_level'  => 'nullable|string',
            'subject_ids'  => 'nullable|string',
            'district_id'  => 'nullable|integer',
            'area_id'      => 'nullable|integer|exists:areas,id',
            'tutor_gender' => 'nullable|in:male,female,no_preference',
            'days_per_week'=> 'nullable|integer|min:1|max:7',
            'hours_per_day'=> 'nullable|numeric',
            'salary_max'   => 'nullable|integer',
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
            'salary_asc'  => $query->orderBy('tuition_preferences.expected_salary_min'),
            'salary_desc' => $query->orderByDesc('tuition_preferences.expected_salary_max'),
            default       => $query->orderByRaw('(is_verified*3)+(profile_completion_percent/100*2)+(rating/5*2)+(LEAST(review_count,50)/50)+(LEAST(profile_view_count,1000)/1000) DESC'),
        };

        $results = $query->paginate($filters['per_page'] ?? 12);
        return response()->json(['success' => true, 'data' => $results]);
    }

    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::when($request->class_level, fn($q) => $q->where('class_level', $request->class_level))->get();
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
