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
        // reopen() has no guards, so a closed-by-confirm job can come back open
        // with its connected row intact — without this, confirming again here
        // would produce a second connected tutor on the same job.
        abort_if(
            TuitionJobApplication::where('tuition_job_id', $job->id)->where('status', 'connected')->exists(),
            422, 'Another tutor is already confirmed for this job. Un-confirm them first.'
        );

        $othersIds = DB::transaction(function () use ($job, $app) {
            $app->update(['status' => 'connected', 'status_before_confirm' => null]);
            $job->update(['status' => 'closed']);

            // Skip rows already not_selected: they were rejected for their own
            // reasons, shouldn't be resurrected by an un-confirm, and re-updating
            // them would send a duplicate rejection email below.
            $othersIds = TuitionJobApplication::where('tuition_job_id', $job->id)
                ->where('id', '!=', $app->id)
                ->whereNotIn('status', ['connected', 'not_selected'])
                ->pluck('id');

            // status_before_confirm must be assigned before status: MySQL applies
            // SET clauses left to right, so it captures the pre-overwrite value.
            TuitionJobApplication::whereIn('id', $othersIds)->update([
                'status_before_confirm' => DB::raw('status'),
                'status'                => 'not_selected',
            ]);

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

    /**
     * Full admin override: any of the 7 statuses, at any time, on any job state.
     * Maintains the job invariant — setting `connected` closes the job and
     * rejects the others (remembering their statuses); moving a tutor off
     * `connected` reopens the job and restores everyone exactly.
     */
    public function changeStatus(Request $request, string $publicId, int $applicationId): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:applied,shortlisted,demo_requested,appointed,confirm_requested,connected,not_selected',
        ]);

        $app      = $this->getApplicationWithJob($publicId, $applicationId);
        $job      = $app->tuitionJob;
        $target   = $data['status'];
        $previous = $app->status;

        if ($target === $previous) {
            return response()->json(['success' => true, 'message' => 'No change made.']);
        }

        // Only one confirmed tutor per job — nothing in the DB enforces it.
        if ($target === 'connected') {
            abort_if(
                TuitionJobApplication::where('tuition_job_id', $job->id)
                    ->where('id', '!=', $app->id)
                    ->where('status', 'connected')
                    ->exists(),
                422, 'Another tutor is already confirmed for this job. Un-confirm them first.'
            );
        }

        $rejectedIds = DB::transaction(function () use ($app, $job, $target, $previous) {
            if ($target === 'connected') {
                // Same effects as confirm(): close the job, reject the rest,
                // remembering each one's current status for a later un-confirm.
                $app->update(['status' => 'connected', 'status_before_confirm' => null]);
                $job->update(['status' => 'closed']);

                $rejectedIds = TuitionJobApplication::where('tuition_job_id', $job->id)
                    ->where('id', '!=', $app->id)
                    ->whereNotIn('status', ['connected', 'not_selected'])
                    ->pluck('id');

                // SET order matters: status_before_confirm captures the value
                // before the same statement overwrites it (MySQL, left to right).
                TuitionJobApplication::whereIn('id', $rejectedIds)->update([
                    'status_before_confirm' => DB::raw('status'),
                    'status'                => 'not_selected',
                ]);

                return $rejectedIds;
            }

            $app->update(['status' => $target, 'status_before_confirm' => null]);

            if ($previous === 'connected') {
                // Un-confirm: reopen the job and put everyone the confirm
                // rejected back to exactly where they were.
                $job->update(['status' => 'open']);

                TuitionJobApplication::where('tuition_job_id', $job->id)
                    ->whereNotNull('status_before_confirm')
                    ->update([
                        'status'                => DB::raw('status_before_confirm'),
                        'status_before_confirm' => null,
                    ]);
            }

            return collect();
        });

        // ── Notifications (after commit) ──────────────────────────────────────
        // Strict allowlist: the notification template only knows shortlisted /
        // appointed / connected / not_selected — any other value silently falls
        // back to the REJECTION email, so the rest must stay silent.
        if ($target === 'connected') {
            TuitionJobApplication::whereIn('id', $rejectedIds)
                ->with('tutorProfile.user')
                ->get()
                ->each(fn ($other) => $this->notifyTutor($other, 'not_selected', $job));

            $this->notifyTutor($app, 'connected', $job);
            $this->notifyGuardian($job, 'confirmed', $app->tutorProfile->user->name ?? 'A tutor');
            $this->smsTutor($app, 'confirmed', $job);
        } else {
            if (in_array($target, ['shortlisted', 'appointed', 'not_selected'], true)) {
                $this->notifyTutor($app, $target, $job);
            }
            if ($previous === 'connected') {
                // Restored applicants are deliberately not notified — their
                // statuses include values the template cannot express.
                $this->notifyGuardian($job, 'unconfirmed', $app->tutorProfile->user->name ?? 'A tutor');
            }
        }

        $jobNote   = $target === 'connected' ? ' Job closed.' : ($previous === 'connected' ? ' Job reopened, other applicants restored.' : '');
        $fromLabel = TuitionJobApplication::statusLabel($previous);
        $toLabel   = TuitionJobApplication::statusLabel($target);
        $this->logActivity($request, 'tuition_job_applicant_status_changed', 'TuitionJobApplication', $app->id,
            "Changed status of tutor '{$app->tutorProfile->user->name}' from '{$fromLabel}' to '{$toLabel}' for job '{$job->title}' ({$job->public_id}).{$jobNote}"
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
