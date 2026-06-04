<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use App\Notifications\PendingChangeApprovedNotification;
use App\Notifications\PendingChangeRejectedNotification;
use App\Services\PendingProfileChangeApplier;
use App\Services\PendingProfileChangePresenter;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPendingChangesController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly PendingProfileChangePresenter $presenter,
        private readonly PendingProfileChangeApplier   $applier,
    ) {}

    public function index(): JsonResponse
    {
        $profiles = TutorProfile::whereNotNull('pending_changes')
            ->with([
                'user:id,name,email',
                'tuitionPreference:id,tutor_profile_id,district_id,expected_salary_min,expected_salary_max,total_experience_years,days_per_week,hours_per_day,tutoring_methods,preferred_classes,preferred_time,place_of_tutoring',
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.district:id,name',
                'tuitionPreference.locations.area:id,name',
                'educationEntries',
                'documents',
                'personalInfo:id,tutor_profile_id,gender,date_of_birth,religion,nationality,present_address,permanent_address,additional_phone,national_id,fathers_name,fathers_phone,mothers_name,mothers_phone',
                'emergencyContact:id,tutor_profile_id,name,relation,phone,address',
            ])
            ->get();

        return response()->json(['success' => true, 'data' => $this->presenter->presentMany($profiles)]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $profile = TutorProfile::findOrFail($id);

        $this->applier->apply($profile);

        try {
            $profile->user->notify(new PendingChangeApprovedNotification());
        } catch (\Exception $e) {
            Log::error('Notification failed (approve)', ['error' => $e->getMessage(), 'profile' => $id]);
        }

        $this->logActivity($request, 'approve_pending_changes', 'tutor_profile', $id,
            "Approved pending profile changes for tutor #{$id}");

        return response()->json(['success' => true, 'message' => 'Changes approved and applied to profile.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data    = $request->validate(['note' => 'nullable|string|max:500']);
        $profile = TutorProfile::findOrFail($id);
        $pending = $profile->pending_changes ?? [];

        $sections  = array_keys(collect($pending)->except('submitted_at')->toArray());
        $submitted = $this->buildSubmittedSummary($pending);

        $profile->update([
            'pending_changes' => null,
            'pending_note'    => $data['note'] ?? null,
        ]);

        try {
            $profile->user->notify(new PendingChangeRejectedNotification(
                note:      $data['note'] ?? null,
                sections:  $sections,
                submitted: $submitted,
            ));
        } catch (\Exception $e) {
            Log::error('Notification failed (reject)', ['error' => $e->getMessage(), 'profile' => $id]);
        }

        $this->logActivity($request, 'reject_pending_changes', 'tutor_profile', $id,
            "Rejected pending profile changes for tutor #{$id}" . ($data['note'] ? ": {$data['note']}" : ''));

        return response()->json(['success' => true, 'message' => 'Changes rejected.']);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildSubmittedSummary(array $pending): array
    {
        $labels = [
            'bio' => 'Bio', 'status' => 'Status',
            'additional_phone' => 'Additional Phone', 'present_address' => 'Present Address',
            'permanent_address' => 'Permanent Address', 'national_id' => 'National ID',
            'fathers_name' => "Father's Name", 'fathers_phone' => "Father's Phone",
            'mothers_name' => "Mother's Name", 'mothers_phone' => "Mother's Phone",
            'gender' => 'Gender', 'date_of_birth' => 'Date of Birth',
            'religion' => 'Religion', 'nationality' => 'Nationality',
            'facebook_url' => 'Facebook URL', 'linkedin_url' => 'LinkedIn URL',
            'name' => 'Name', 'relation' => 'Relation', 'phone' => 'Phone', 'address' => 'Address',
        ];

        $submitted = [];

        foreach (['bio', 'status'] as $f) {
            if (!empty($pending[$f])) {
                $submitted[] = ['field' => $labels[$f], 'value' => (string) $pending[$f]];
            }
        }
        foreach ($pending['personal_info'] ?? [] as $f => $v) {
            if ($v !== null && $v !== '') {
                $submitted[] = ['field' => $labels[$f] ?? ucwords(str_replace('_', ' ', $f)), 'value' => (string) $v];
            }
        }
        foreach ($pending['emergency_contact'] ?? [] as $f => $v) {
            if ($v !== null && $v !== '') {
                $submitted[] = ['field' => $labels[$f] ?? ucwords(str_replace('_', ' ', $f)), 'value' => (string) $v];
            }
        }

        return $submitted;
    }
}
