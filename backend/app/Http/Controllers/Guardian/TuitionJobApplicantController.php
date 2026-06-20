<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuitionJobApplicantController extends Controller
{
    public function index(Request $request, string $publicId): JsonResponse
    {
        $job    = $this->ownedJob($request, $publicId);
        $status = $request->query('status');

        $query = TuitionJobApplication::where('tuition_job_id', $job->id)
            ->with([
                'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count',
                'tutorProfile.user:id,name,avatar',
                'tutorProfile.personalInfo:id,tutor_profile_id,gender',
                'tutorProfile.tuitionPreference:id,tutor_profile_id,expected_salary_min,expected_salary_max',
            ])
            ->orderBy('applied_at');

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function shortlist(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->ownedApplication($request, $publicId, $applicationId);
        abort_if($app->status !== 'applied', 422, 'Only applied applicants can be shortlisted.');
        $app->update(['status' => 'shortlisted']);
        return response()->json(['success' => true, 'message' => 'Tutor shortlisted.']);
    }

    public function appoint(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->ownedApplication($request, $publicId, $applicationId);
        abort_if($app->status !== 'shortlisted', 422, 'Only shortlisted applicants can be appointed.');
        $app->update(['status' => 'appointed']);
        return response()->json(['success' => true, 'message' => 'Tutor set for demo class.']);
    }

    public function confirm(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $job = $this->ownedJob($request, $publicId);
        $app = TuitionJobApplication::where('id', $applicationId)
            ->where('tuition_job_id', $job->id)
            ->firstOrFail();

        abort_if($job->status === 'closed', 422, 'This job is already closed.');
        abort_if($app->status !== 'appointed', 422, 'Only appointed applicants can be confirmed.');

        \Illuminate\Support\Facades\DB::transaction(function () use ($job, $app) {
            $app->update(['status' => 'connected']);
            $job->update(['status' => 'closed']);
            TuitionJobApplication::where('tuition_job_id', $job->id)
                ->where('id', '!=', $app->id)
                ->whereNotIn('status', ['connected'])
                ->update(['status' => 'not_selected']);
        });

        return response()->json(['success' => true, 'message' => 'Tutor confirmed.']);
    }

    public function remove(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->ownedApplication($request, $publicId, $applicationId);
        abort_if($app->status === 'connected', 422, 'Cannot remove a confirmed tutor.');
        $app->update(['status' => 'not_selected']);
        return response()->json(['success' => true, 'message' => 'Applicant not selected.']);
    }

    private function ownedJob(Request $request, string $publicId): TuitionJob
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        abort_if($job->guardian_profile_id !== $request->user()->guardianProfile->id, 403);
        return $job;
    }

    private function ownedApplication(Request $request, string $publicId, int $applicationId): TuitionJobApplication
    {
        $job = $this->ownedJob($request, $publicId);
        return TuitionJobApplication::where('id', $applicationId)
            ->where('tuition_job_id', $job->id)
            ->firstOrFail();
    }
}
