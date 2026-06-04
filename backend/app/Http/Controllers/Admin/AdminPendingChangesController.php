<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendingProfileChangeResource;
use App\Models\TutorProfile;
use App\Notifications\PendingChangeApprovedNotification;
use App\Notifications\PendingChangeRejectedNotification;
use App\Services\PendingProfileChangeApplier;
use App\Services\PendingProfileChangePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPendingChangesController extends Controller
{
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

        $presented = $this->presenter->presentMany($profiles);
        $resources = array_map(fn($item) => (new PendingProfileChangeResource($item))->toArray(request()), $presented);

        return response()->json(['success' => true, 'data' => $resources]);
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

        return response()->json(['success' => true, 'message' => 'Changes approved and applied to profile.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data    = $request->validate(['note' => 'nullable|string|max:500']);
        $profile = TutorProfile::findOrFail($id);
        $pending = $profile->pending_changes ?? [];

        $sections  = array_keys(collect($pending)->except('submitted_at')->toArray());
        $submitted = $this->presenter->buildRejectionSummary($pending);

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

        return response()->json(['success' => true, 'message' => 'Changes rejected.']);
    }

}
