<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tutorProfile = $request->user()?->tutorProfile;

        $query = TuitionJob::where('status', 'open')
            ->with(['district:id,name', 'area:id,name', 'subjects:id,name'])
            ->withExists([
                'applications as already_applied' => fn($q) => $q->where('tutor_profile_id', $tutorProfile?->id ?? 0),
            ])
            ->latest();

        if ($request->filled('district_id')) {
            $query->where('district_id', (int) $request->district_id);
        }
        if ($request->filled('area_ids')) {
            $ids = array_map('intval', (array) $request->area_ids);
            $query->whereIn('area_id', $ids);
        }
        if ($request->filled('class_level')) {
            $query->where('class_level', $request->class_level);
        }
        if ($request->filled('subject_id')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.id', (int) $request->subject_id));
        }
        if ($request->filled('tuition_type')) {
            $query->where('tuition_type', $request->tuition_type);
        }
        if ($request->filled('medium')) {
            $query->where('medium', $request->medium);
        }
        if ($request->filled('tutor_gender_pref')) {
            $query->where('tutor_gender_pref', $request->tutor_gender_pref);
        }
        if ($request->filled('salary_min')) {
            $query->where('offered_salary', '>=', (int) $request->salary_min);
        }
        if ($request->filled('salary_max')) {
            $query->where('offered_salary', '<=', (int) $request->salary_max);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($q) => $q->where('title', 'like', $term)
                ->orWhere('extra_requirements', 'like', $term));
        }

        $jobs = $query->paginate($request->integer('per_page', 12));

        return response()->json(['success' => true, 'data' => $jobs]);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $tutorProfile = $request->user()?->tutorProfile;

        $job = TuitionJob::where('public_id', $publicId)
            ->where('status', 'open')
            ->with(['district:id,name', 'area:id,name', 'subjects:id,name'])
            ->firstOrFail();

        $myApplication = $tutorProfile
            ? TuitionJobApplication::where('tuition_job_id', $job->id)
                ->where('tutor_profile_id', $tutorProfile->id)
                ->first(['id', 'status', 'applied_at'])
            : null;

        return response()->json([
            'success' => true,
            'data'    => array_merge($job->toArray(), ['my_application' => $myApplication]),
        ]);
    }

    public function apply(Request $request, string $publicId): JsonResponse
    {
        $tutorProfile = $request->user()->tutorProfile;

        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();

        abort_if($job->status === 'closed', 422, 'This job is no longer accepting applications.');

        $exists = TuitionJobApplication::where('tuition_job_id', $job->id)
            ->where('tutor_profile_id', $tutorProfile->id)
            ->exists();

        abort_if($exists, 422, 'You have already applied to this job.');

        TuitionJobApplication::create([
            'tuition_job_id'  => $job->id,
            'tutor_profile_id'=> $tutorProfile->id,
            'status'          => 'applied',
        ]);

        return response()->json(['success' => true, 'message' => 'Application submitted.'], 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $tutorProfile = $request->user()->tutorProfile;
        $status       = $request->query('status');
        $jobStatus    = $request->query('job_status');

        $query = TuitionJobApplication::where('tutor_profile_id', $tutorProfile->id)
            ->with([
                'tuitionJob:id,public_id,title,offered_salary,district_id,area_id,status,created_at',
                'tuitionJob.district:id,name',
                'tuitionJob.area:id,name',
            ])
            ->orderByDesc('applied_at');

        if ($status) {
            $query->where('status', $status);
        }
        if ($jobStatus) {
            $query->whereHas('tuitionJob', fn($q) => $q->where('status', $jobStatus));
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function dashboardSummary(Request $request): JsonResponse
    {
        $tutorProfile = $request->user()->tutorProfile;

        $counts = TuitionJobApplication::where('tutor_profile_id', $tutorProfile->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'applied')     as applied,
                SUM(status = 'shortlisted') as shortlisted,
                SUM(status = 'appointed')   as appointed,
                SUM(status = 'connected')   as connected
            ")
            ->first();

        return response()->json(['success' => true, 'data' => $counts]);
    }
}
