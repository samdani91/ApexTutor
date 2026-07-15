<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsJob;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Notifications\TuitionJobApplicationStatusNotification;
use App\Notifications\TuitionJobGuardianNotification;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminTuitionJobController extends Controller
{
    use LogsAdminActivity;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'   => 'nullable|in:open,closed',
            'q'        => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = TuitionJob::with([
            'district:id,name',
            'area:id,name',
            'subjects:id,name',
            'guardianProfile.user:id,name,email',
        ])
        ->withCount('applications')
        ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->q) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sq) use ($q) {
                $sq->where('title', 'like', $q)
                   ->orWhere('public_id', 'like', $q);
            });
        }

        $jobs = $query->paginate($request->integer('per_page', 15));

        return response()->json(['success' => true, 'data' => $jobs]);
    }

    public function show(string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)
            ->with([
                'district:id,name',
                'area:id,name',
                'subjects:id,name',
                'guardianProfile.user:id,name,email',
            ])
            ->withCount('applications')
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $job]);
    }

    public function applications(string $publicId, Request $request): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();

        $query = TuitionJobApplication::where('tuition_job_id', $job->id)
            ->with([
                'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count',
                'tutorProfile.user:id,name,email,avatar',
                'tutorProfile.personalInfo:id,tutor_profile_id,gender',
                'tutorProfile.tuitionPreference:id,tutor_profile_id,expected_salary_min,expected_salary_max',
            ])
            ->orderBy('applied_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function shortlist(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->getApplicationWithJob($publicId, $applicationId);
        abort_if($app->tuitionJob->status !== 'open', 422, 'You can only manage applicants while the job is open.');
        abort_if($app->status !== 'applied', 422, 'Only applied applicants can be shortlisted.');

        $app->update(['status' => 'shortlisted']);

        $this->notifyTutor($app, 'shortlisted');

        $this->logActivity($request, 'tuition_job_applicant_shortlisted', 'TuitionJobApplication', $app->id,
            "Shortlisted tutor '{$app->tutorProfile->user->name}' for job '{$app->tuitionJob->title}' ({$app->tuitionJob->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Tutor shortlisted.']);
    }

    public function appoint(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->getApplicationWithJob($publicId, $applicationId);
        abort_if($app->tuitionJob->status !== 'open', 422, 'You can only manage applicants while the job is open.');
        abort_if(!in_array($app->status, ['shortlisted', 'demo_requested'], true), 422, 'Only shortlisted or demo-requested applicants can be appointed.');

        $app->update(['status' => 'appointed']);

        $this->notifyTutor($app, 'appointed');
        $this->notifyGuardian($app->tuitionJob, 'appointed', $app->tutorProfile->user->name ?? 'A tutor');
        $this->smsTutor($app, 'appointed');

        $this->logActivity($request, 'tuition_job_applicant_appointed', 'TuitionJobApplication', $app->id,
            "Appointed tutor '{$app->tutorProfile->user->name}' for demo class on job '{$app->tuitionJob->title}' ({$app->tuitionJob->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Tutor appointed for demo class.']);
    }

    public function confirm(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        $app = TuitionJobApplication::where('id', $applicationId)
            ->where('tuition_job_id', $job->id)
            ->with(['tutorProfile.user', 'tuitionJob'])
            ->firstOrFail();

        abort_if($job->status === 'closed', 422, 'This job is already closed.');
        abort_if(!in_array($app->status, ['appointed', 'confirm_requested'], true), 422, 'Only appointed or confirm-requested applicants can be confirmed.');

        $othersIds = DB::transaction(function () use ($job, $app) {
            $app->update(['status' => 'connected']);
            $job->update(['status' => 'closed']);

            $othersIds = TuitionJobApplication::where('tuition_job_id', $job->id)
                ->where('id', '!=', $app->id)
                ->whereNotIn('status', ['connected'])
                ->pluck('id');

            TuitionJobApplication::whereIn('id', $othersIds)->update(['status' => 'not_selected']);

            return $othersIds;
        });

        // Notifications and SMS run after the transaction commits
        TuitionJobApplication::whereIn('id', $othersIds)
            ->with('tutorProfile.user')
            ->get()
            ->each(function ($other) use ($job) {
                $this->notifyTutor($other, 'not_selected', $job);
            });

        $this->notifyTutor($app, 'connected', $job);
        $this->notifyGuardian($job, 'confirmed', $app->tutorProfile->user->name ?? 'A tutor');
        $this->smsTutor($app, 'confirmed', $job);

        $this->logActivity($request, 'tuition_job_applicant_confirmed', 'TuitionJobApplication', $app->id,
            "Confirmed tutor '{$app->tutorProfile->user->name}' for job '{$job->title}' ({$job->public_id}). Job closed."
        );

        return response()->json(['success' => true, 'message' => 'Tutor confirmed. Connection request created.']);
    }

    public function remove(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $app = $this->getApplicationWithJob($publicId, $applicationId);
        abort_if($app->tuitionJob->status !== 'open', 422, 'You can only manage applicants while the job is open.');
        abort_if($app->status === 'connected', 422, 'Cannot remove a confirmed tutor.');

        $app->update(['status' => 'not_selected']);

        $this->notifyTutor($app, 'not_selected');

        $this->logActivity($request, 'tuition_job_applicant_not_selected', 'TuitionJobApplication', $app->id,
            "Marked tutor '{$app->tutorProfile->user->name}' as not selected for job '{$app->tuitionJob->title}' ({$app->tuitionJob->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Applicant not selected.']);
    }

    public function changeStatus(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:applied,shortlisted,demo_requested,appointed,confirm_requested',
        ]);

        $app = $this->getApplicationWithJob($publicId, $applicationId);
        abort_if($app->tuitionJob->status !== 'open', 422, 'You can only change applicant statuses while the job is open.');
        abort_if($app->status === 'connected', 422, 'Cannot change the status of a confirmed tutor.');

        $target = $data['status'];

        if ($target === $app->status) {
            return response()->json(['success' => true, 'message' => 'No change made.']);
        }

        $app->update(['status' => $target]);

        // Notify the tutor for meaningful states (no notification for resetting to plain "applied" or request statuses)
        if (in_array($target, ['shortlisted', 'appointed'], true)) {
            $this->notifyTutor($app, $target);
        }

        $this->logActivity($request, 'tuition_job_applicant_status_changed', 'TuitionJobApplication', $app->id,
            "Changed status of tutor '{$app->tutorProfile->user->name}' to '{$target}' for job '{$app->tuitionJob->title}' ({$app->tuitionJob->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Applicant status updated.']);
    }

    public function close(Request $request, string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        $job->update(['status' => 'closed']);

        $this->logActivity($request, 'tuition_job_closed', 'TuitionJob', $job->id,
            "Closed tuition job '{$job->title}' ({$job->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Job closed.']);
    }

    public function reopen(Request $request, string $publicId): JsonResponse
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        $job->update(['status' => 'open']);

        $this->logActivity($request, 'tuition_job_reopened', 'TuitionJob', $job->id,
            "Reopened tuition job '{$job->title}' ({$job->public_id})"
        );

        return response()->json(['success' => true, 'message' => 'Job reopened.']);
    }

    private function notifyGuardian(TuitionJob $job, string $event, string $tutorName): void
    {
        try {
            if (!$job->relationLoaded('guardianProfile') || !$job->guardianProfile?->relationLoaded('user')) {
                $job->load('guardianProfile.user');
            }
            $guardianUser = $job->guardianProfile?->user;
            if (!$guardianUser) return;

            $guardianUser->notify(new TuitionJobGuardianNotification(
                event:        $event,
                jobTitle:     $job->title,
                jobPublicId:  $job->public_id,
                tutorName:    $tutorName,
            ));
        } catch (\Exception $e) {
            Log::error('TuitionJob guardian notification failed', [
                'error'  => $e->getMessage(),
                'job_id' => $job->id,
                'event'  => $event,
            ]);
        }
    }

    private function notifyTutor(TuitionJobApplication $app, string $status, ?TuitionJob $job = null): void
    {
        try {
            $job     = $job ?? $app->tuitionJob;
            $tutorUser = $app->tutorProfile?->user;
            if (!$tutorUser || !$job) return;

            $tutorUser->notify(new TuitionJobApplicationStatusNotification(
                status:    $status,
                jobTitle:  $job->title,
                jobPublicId: $job->public_id,
            ));
        } catch (\Exception $e) {
            Log::error('TuitionJob applicant notification failed', [
                'error'          => $e->getMessage(),
                'application_id' => $app->id,
                'status'         => $status,
            ]);
        }
    }

    private function smsTutor(TuitionJobApplication $app, string $event, ?TuitionJob $job = null): void
    {
        try {
            $tutorPhone = $app->tutorProfile?->user?->phone;
            if (!$tutorPhone) return;

            $job ??= $app->tuitionJob;
            if (!$job) return;

            if ($event === 'appointed') {
                $job->loadMissing(['guardianProfile.user', 'area:id,name', 'district:id,name']);

                $guardianName  = $job->guardianProfile?->user?->name ?? 'Guardian';
                $guardianPhone = $job->guardianProfile?->user?->phone;
                $phonePart     = $guardianPhone ? " ({$guardianPhone})" : '';

                $addressParts = array_filter([
                    $job->address_details,
                    $job->area?->name,
                    $job->district?->name,
                ]);
                $addressPart = $addressParts ? ', ' . implode(', ', $addressParts) : '';

                $message = "You've got selected by {$guardianName}{$phonePart} for Job ID {$job->public_id}{$addressPart}. Contact him/her within 30 minutes.";
            } elseif ($event === 'confirmed') {
                $message = "Congratulations! Your hiring has been confirmed by the guardian/student for Job ID: {$job->public_id}.";
            } else {
                return;
            }

            SendSmsJob::dispatch($tutorPhone, $message);
        } catch (\Exception $e) {
            Log::error('TuitionJob tutor SMS dispatch failed', [
                'error'          => $e->getMessage(),
                'application_id' => $app->id,
                'event'          => $event,
            ]);
        }
    }

    private function getApplicationWithJob(string $publicId, int $applicationId): TuitionJobApplication
    {
        $job = TuitionJob::where('public_id', $publicId)->firstOrFail();
        return TuitionJobApplication::where('id', $applicationId)
            ->where('tuition_job_id', $job->id)
            ->with(['tutorProfile.user', 'tuitionJob'])
            ->firstOrFail();
    }
}
