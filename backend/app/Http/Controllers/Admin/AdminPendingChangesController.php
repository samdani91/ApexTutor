<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendingProfileChangeResource;
use App\Models\TutorProfile;
use App\Models\User;
use App\Notifications\PendingChangeApprovedNotification;
use App\Notifications\PendingChangeRejectedNotification;
use App\Services\PendingProfileChangeApplier;
use App\Services\PendingProfileChangePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                'user:id,name,email,avatar,pending_avatar',
                // Full model on purpose: a column list here silently drops any
                // newly added preference field from the admin diff (bit us with
                // preferred_groups — the third allowlist bug of this kind).
                'tuitionPreference',
                'tuitionPreference.subjects:id,name',
                'tuitionPreference.district:id,name',
                'tuitionPreference.days',
                'tuitionPreference.locations.area:id,name',
                'educationEntries',
                'documents',
                'personalInfo:id,tutor_profile_id,gender,date_of_birth,religion,nationality,present_address,permanent_address,additional_phone,national_id,fathers_name,fathers_phone,mothers_name,mothers_phone',
                'emergencyContact:id,tutor_profile_id,name,relation,phone,address',
            ])
            ->get();

        $presented = $this->presenter->presentMany($profiles);
        $resources = array_map(fn($item) => (new PendingProfileChangeResource($item))->toArray(request()), $presented);

        // Standalone pending avatars: guardians, students, and any tutor whose
        // pending_changes either is null or doesn't have an 'avatar' key yet
        // (verified tutors that staged avatar via stageAvatar() show up in $profiles above;
        // this catches any that fell through, e.g. from before the feature was in place)
        $pendingAvatars = User::whereNotNull('pending_avatar')
            ->where(function ($q) {
                $q->whereIn('role', ['guardian', 'student'])
                  ->orWhere(function ($q2) {
                      $q2->where('role', 'tutor')
                         ->whereHas('tutorProfile', fn($tp) =>
                             $tp->whereNull('pending_changes')
                                ->orWhereRaw("JSON_EXTRACT(pending_changes, '$.avatar') IS NULL")
                         );
                  });
            })
            ->with([
                'tutorProfile:id,user_id,tutor_id',
                'guardianProfile:id,user_id,guardian_id',
            ])
            ->get()
            ->map(fn(User $u) => [
                'id'                 => $u->id,
                'name'               => $u->name,
                'email'              => $u->email,
                'role'               => $u->role,
                'avatar_url'         => $u->avatar_url,
                'pending_avatar_url' => $u->pending_avatar_url,
                'profile_id'         => $u->tutorProfile?->tutor_id ?? $u->guardianProfile?->guardian_id,
            ])
            ->values()
            ->all();

        return response()->json([
            'success'         => true,
            'data'            => $resources,
            'pending_avatars' => $pendingAvatars,
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $profile = TutorProfile::findOrFail($id);

        if (!$profile->pending_changes) {
            return response()->json(['success' => false, 'message' => 'No pending changes to approve.'], 422);
        }

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

        // Discard pending avatar file if one was staged
        if (isset($pending['avatar']['path'])) {
            $user = $profile->user;
            try { Storage::disk('public')->delete($pending['avatar']['path']); } catch (\Exception $e) {
                Log::warning('Could not delete pending avatar on reject', ['path' => $pending['avatar']['path']]);
            }
            $user->pending_avatar = null;
            $user->save();
        }

        // Discard pending name change if one was staged
        if (isset($pending['name'])) {
            $user = $profile->user;
            $user->pending_name = null;
            $user->save();
        }

        // Discard pending document files that were uploaded but never applied
        foreach ($pending['documents']['upsert'] ?? [] as $docData) {
            if (!empty($docData['file_path'])) {
                try { Storage::disk('public')->delete($docData['file_path']); } catch (\Exception $e) {
                    Log::warning('Could not delete pending document on reject', ['path' => $docData['file_path']]);
                }
            }
        }

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
