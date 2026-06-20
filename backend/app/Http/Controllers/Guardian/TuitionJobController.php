<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTuitionJobRequest;
use App\Models\Subject;
use App\Models\TuitionJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status   = $request->query('status', 'all');
        $guardian = $request->user()->guardianProfile;

        $query = TuitionJob::where('guardian_profile_id', $guardian->id)
            ->withCount([
                'applications',
                'applications as applied_count'     => fn($q) => $q->where('status', 'applied'),
                'applications as shortlisted_count'  => fn($q) => $q->where('status', 'shortlisted'),
                'applications as appointed_count'    => fn($q) => $q->where('status', 'appointed'),
                'applications as connected_count'    => fn($q) => $q->where('status', 'connected'),
            ])
            ->with(['district:id,name', 'area:id,name', 'subjects:id,name'])
            ->latest();

        if (in_array($status, ['open', 'closed'])) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(StoreTuitionJobRequest $request): JsonResponse
    {
        $data     = $request->validated();
        $guardian = $request->user()->guardianProfile;

        $subjects = Subject::whereIn('id', $data['subject_ids'])->pluck('name')->all();
        $title    = TuitionJob::buildTitle($data['class_level'], $subjects, $data['tuition_type'], $data['tutoring_days_per_week'] ?? null);

        $job = TuitionJob::create([
            'guardian_profile_id'    => $guardian->id,
            'title'                  => $title,
            'tuition_type'           => $data['tuition_type'],
            'medium'                 => $data['medium'] ?? null,
            'tutoring_style'         => $data['tutoring_style'] ?? null,
            'district_id'            => $data['district_id'],
            'area_id'                => $data['area_id'] ?? null,
            'address_details'        => $data['address_details'] ?? null,
            'class_level'            => $data['class_level'],
            'student_gender'         => $data['student_gender'],
            'num_students'           => $data['num_students'],
            'tutor_gender_pref'      => $data['tutor_gender_pref'],
            'offered_salary'         => $data['offered_salary'],
            'hire_date'              => $data['hire_date'] ?? null,
            'tutoring_time'          => $data['tutoring_time'] ?? null,
            'tutoring_days_per_week' => $data['tutoring_days_per_week'] ?? null,
            'student_institute'      => $data['student_institute'] ?? null,
            'extra_requirements'     => $data['extra_requirements'] ?? null,
        ]);

        $job->subjects()->sync($data['subject_ids']);

        $admins = \App\Models\User::where('role', 'super_admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AdminTuitionJobPostedNotification(
                $request->user()->name,
                $job->title,
                $job->public_id,
            ));
        }

        return response()->json(['success' => true, 'data' => $job, 'message' => 'Job posted successfully.'], 201);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $job = $this->ownedJob($request, $publicId);
        $job->load(['district:id,name', 'area:id,name', 'subjects:id,name'])
            ->loadCount([
                'applications',
                'applications as applied_count'     => fn($q) => $q->where('status', 'applied'),
                'applications as shortlisted_count'  => fn($q) => $q->where('status', 'shortlisted'),
                'applications as appointed_count'    => fn($q) => $q->where('status', 'appointed'),
                'applications as connected_count'    => fn($q) => $q->where('status', 'connected'),
            ]);
        return response()->json(['success' => true, 'data' => $job]);
    }

    public function update(StoreTuitionJobRequest $request, string $publicId): JsonResponse
    {
        $job = $this->ownedJob($request, $publicId);

        abort_if($job->status === 'closed', 403, 'Closed jobs cannot be edited.');

        $data     = $request->validated();
        $subjects = Subject::whereIn('id', $data['subject_ids'])->pluck('name')->all();
        $title    = TuitionJob::buildTitle($data['class_level'], $subjects, $data['tuition_type'], $data['tutoring_days_per_week'] ?? null);

        $job->update(array_merge($data, ['title' => $title]));
        $job->subjects()->sync($data['subject_ids']);

        return response()->json(['success' => true, 'data' => $job->fresh(), 'message' => 'Job updated.']);
    }

    public function close(Request $request, string $publicId): JsonResponse
    {
        $job = $this->ownedJob($request, $publicId);
        $job->update(['status' => 'closed']);
        return response()->json(['success' => true, 'message' => 'Job closed.']);
    }

    public function reopen(Request $request, string $publicId): JsonResponse
    {
        $job = $this->ownedJob($request, $publicId);
        $job->update(['status' => 'open']);
        return response()->json(['success' => true, 'message' => 'Job reopened.']);
    }

    public function dashboardSummary(Request $request): JsonResponse
    {
        $guardian = $request->user()->guardianProfile;

        $open   = TuitionJob::where('guardian_profile_id', $guardian->id)->where('status', 'open')->count();
        $closed = TuitionJob::where('guardian_profile_id', $guardian->id)->where('status', 'closed')->count();
        $total  = TuitionJob::where('guardian_profile_id', $guardian->id)->count();

        $totalApplicants = \App\Models\TuitionJobApplication::whereHas(
            'tuitionJob', fn($q) => $q->where('guardian_profile_id', $guardian->id)
        )->count();

        return response()->json(['success' => true, 'data' => compact('open', 'closed', 'total', 'totalApplicants')]);
    }

    private function ownedJob(Request $request, string $publicId): TuitionJob
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        abort_if($job->guardian_profile_id !== $request->user()->guardianProfile->id, 403);
        return $job;
    }
}
