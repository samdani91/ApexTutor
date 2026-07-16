<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeachingVideo;
use App\Models\TutorDocument;
use App\Models\TutorProfile;
use App\Models\University;
use App\Notifications\AccountReactivatedNotification;
use App\Notifications\AccountSuspendedNotification;
use App\Notifications\TutorProfileEditedByAdminNotification;
use App\Notifications\TutorVideoReviewedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminTutorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TutorProfile::with('user:id,name,email,phone,avatar')
            ->when($request->search, function ($q, $search) use ($request) {
                $by = $request->get('search_by', 'name');
                $q->where(function ($inner) use ($search, $by) {
                    if ($by === 'id') {
                        $inner->where('tutor_id', 'like', "%{$search}%");
                    } elseif ($by === 'email') {
                        $inner->whereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%"));
                    } else {
                        $inner->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                    }
                });
            })
            ->when($request->verification_status, fn($q) => $q->where('verification_status', $request->verification_status))
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(string $tutorId): JsonResponse
    {
        $tutor = TutorProfile::with([
            'user',
            'educationEntries.university:id,name,short_name,logo',
            'tuitionPreference.subjects',
            'tuitionPreference.days',
            'tuitionPreference.locations.area',
            'personalInfo',
            'emergencyContact',
            'documents',
            'teachingVideos',
            'connectionRequests' => fn($q) => $q->with('guardianProfile.user:id,name,email')->latest()->take(20),
            'reviews' => fn($q) => $q->with('guardianProfile.user:id,name')->latest()->take(10),
        ])->where('tutor_id', $tutorId)->firstOrFail();

        $tutor->documents->each(function ($doc) {
            $doc->file_url = rtrim(config('app.url'), '/') . '/serve.php?f=' . rtrim(strtr(base64_encode($doc->file_path), '+/', '-_'), '=');
        });
        foreach ($tutor->teachingVideos as $video) {
            if ($video->file_path) {
                $video->file_url = \Illuminate\Support\Facades\Storage::disk('public')->url($video->file_path);
            }
        }

        $tutor->makeVisible(['user_id', 'verified_by', 'pending_changes', 'pending_note', 'rejection_reason']);

        return response()->json(['success' => true, 'data' => $tutor]);
    }

    public function update(Request $request, string $tutorId): JsonResponse
    {
        $tutor = TutorProfile::with(['user','tuitionPreference','personalInfo','emergencyContact'])->where('tutor_id', $tutorId)->firstOrFail();

        $validated = $request->validate([
            'user.name'    => 'sometimes|string|max:100',
            'user.email'   => 'sometimes|email|unique:users,email,' . $tutor->user_id,
            'user.phone'   => 'nullable|string|max:20',
            'user.address' => 'nullable|string|max:255',

            'profile.bio'    => 'nullable|string|max:2000',
            'profile.status' => 'sometimes|in:active,inactive,suspended',

            'preference.expected_salary_min'         => 'nullable|integer|min:0',
            'preference.expected_salary_max'         => 'nullable|integer|min:0',
            'preference.total_experience_years'      => 'nullable|integer|min:0',
            'preference.experience_details'          => 'nullable|string|max:2000',
            'preference.days_per_week'               => 'nullable|integer|min:1|max:7',
            'preference.hours_per_day'               => 'nullable|numeric|min:0.5|max:12',
            'preference.place_of_tutoring'           => 'nullable|array',
            'preference.tutoring_methods'            => 'nullable|array',
            'preference.tutoring_styles'             => 'nullable|array',
            'preference.preferred_classes'           => 'nullable|array',
            'preference.preferred_curricula'         => 'nullable|array',
            'preference.preferred_groups'            => 'nullable|array',
            'preference.preferred_groups.*'          => 'in:science,business_studies,humanities',
            'preference.preferred_time'              => 'nullable|array',
            'preference.tutoring_method_description' => 'nullable|string|max:1000',
            'preference.district_id'                 => 'nullable|exists:districts,id',

            'personal_info.gender'           => 'nullable|in:male,female,other',
            'personal_info.date_of_birth'    => 'nullable|date',
            'personal_info.nationality'      => 'nullable|string|max:50',
            'personal_info.religion'         => 'nullable|string|max:50',
            'personal_info.national_id'      => 'nullable|string|max:50',
            'personal_info.additional_phone' => 'nullable|string|max:20',
            'personal_info.present_address'  => 'nullable|string|max:255',
            'personal_info.permanent_address'=> 'nullable|string|max:255',
            'personal_info.facebook_url'     => 'nullable|url|max:255',
            'personal_info.linkedin_url'     => 'nullable|url|max:255',
            'personal_info.fathers_name'     => 'nullable|string|max:100',
            'personal_info.fathers_phone'    => 'nullable|string|max:20',
            'personal_info.mothers_name'     => 'nullable|string|max:100',
            'personal_info.mothers_phone'    => 'nullable|string|max:20',

            'emergency_contact.name'     => 'nullable|string|max:100',
            'emergency_contact.relation' => 'nullable|string|max:50',
            'emergency_contact.phone'    => 'nullable|string|max:20',
            'emergency_contact.address'  => 'nullable|string|max:255',
        ]);

        // If the tutor had a staged avatar in pending_changes, also discard pending_avatar
        // on the user record (and the file). Admin editing the profile directly supersedes
        // any pending changes — the avatar is no longer in a reviewable state.
        $pendingAvatarPath = $tutor->pending_changes['avatar']['path'] ?? null;

        // Track whether the edit actually mutated anything so a no-op "Save" doesn't
        // fire a profile-edited notification or claim changes were made.
        $changed = false;

        DB::transaction(function () use ($validated, $tutor, &$changed) {
            if ($userData = $validated['user'] ?? null) {
                $tutor->user->fill(array_filter($userData, fn($v, $k) => $v !== null || in_array($k, ['phone', 'address']), ARRAY_FILTER_USE_BOTH));
                if ($tutor->user->isDirty()) {
                    $tutor->user->save();
                    $changed = true;
                }
            }

            if ($profileData = $validated['profile'] ?? null) {
                $tutor->fill(array_filter($profileData, fn($v) => $v !== null));
            }
            // Admin edit supersedes any pending review — clear staged changes.
            $tutor->pending_changes = null;
            $tutor->pending_note    = null;
            if ($tutor->isDirty()) {
                $tutor->save();
                $changed = true;
            }

            // Clear dangling pending_avatar when pending changes are wiped
            if ($tutor->user->pending_avatar) {
                $tutor->user->pending_avatar = null;
                $tutor->user->save();
                $changed = true;
            }

            if ($prefData = $validated['preference'] ?? null) {
                $prefData = array_filter($prefData, fn($v) => $v !== null);
                if (!empty($prefData)) {
                    if ($tutor->tuitionPreference) {
                        $tutor->tuitionPreference->fill($prefData);
                        if ($tutor->tuitionPreference->isDirty()) {
                            $tutor->tuitionPreference->save();
                            $changed = true;
                        }
                    } else {
                        $tutor->tuitionPreference()->create(array_merge(['tutor_profile_id' => $tutor->id], $prefData));
                        $changed = true;
                    }
                }
            }

            if ($piData = $validated['personal_info'] ?? null) {
                $piData = array_filter($piData, fn($v) => $v !== null);
                if (!empty($piData)) {
                    if ($tutor->personalInfo) {
                        $tutor->personalInfo->fill($piData);
                        if ($tutor->personalInfo->isDirty()) {
                            $tutor->personalInfo->save();
                            $changed = true;
                        }
                    } else {
                        $tutor->personalInfo()->create(array_merge(['tutor_profile_id' => $tutor->id], $piData));
                        $changed = true;
                    }
                }
            }

            if ($ecData = $validated['emergency_contact'] ?? null) {
                $ecData = array_filter($ecData, fn($v) => $v !== null);
                if (!empty($ecData)) {
                    if ($tutor->emergencyContact) {
                        $tutor->emergencyContact->fill($ecData);
                        if ($tutor->emergencyContact->isDirty()) {
                            $tutor->emergencyContact->save();
                            $changed = true;
                        }
                    } else {
                        $tutor->emergencyContact()->create(array_merge(['tutor_profile_id' => $tutor->id], $ecData));
                        $changed = true;
                    }
                }
            }
        });

        if (!$changed) {
            return response()->json(['success' => true, 'changed' => false, 'message' => 'No changes to save.']);
        }

        // Delete the staged avatar file outside the transaction so a storage failure
        // cannot roll back the profile update.
        if ($pendingAvatarPath) {
            try {
                Storage::disk('public')->delete($pendingAvatarPath);
            } catch (\Exception $e) {
                Log::warning('Could not delete staged avatar on admin edit', ['path' => $pendingAvatarPath]);
            }
        }

        $this->notifyProfileEdited($tutor);

        return response()->json(['success' => true, 'changed' => true, 'message' => 'Tutor profile updated.']);
    }

    /** Notify the tutor (platform + email) that an admin edited their profile. */
    private function notifyProfileEdited(TutorProfile $tutor): void
    {
        try {
            if ($tutor->user) {
                $tutor->user->notify(new TutorProfileEditedByAdminNotification());
            }
        } catch (\Exception $e) {
            Log::error('Admin profile edit notification failed', ['error' => $e->getMessage(), 'tutor' => $tutor->tutor_id]);
        }
    }

    // ── Document management ───────────────────────────────────────────────────

    public function uploadDocument(Request $request, string $tutorId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();

        $request->validate([
            'type' => 'required|in:nid,ssc_marksheet,hsc_marksheet,emergency_contact_nid',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $file     = $request->file('file');
        $realMime = mime_content_type($file->getRealPath());
        $allowed  = ['application/pdf', 'image/jpeg', 'image/png'];

        if (!in_array($realMime, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, and PNG are accepted.'], 422);
        }

        $path    = $file->store('documents', 'public');
        $payload = [
            'type'      => $request->type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $realMime,
            'review_status' => 'approved',
        ];

        // Remove any existing document of this type
        foreach ($tutor->documents()->where('type', $request->type)->get() as $old) {
            Storage::disk('public')->delete($old->file_path);
            $old->delete();
        }

        $doc = $tutor->documents()->create($payload);
        $doc->file_url = rtrim(config('app.url'), '/') . '/serve.php?f=' . rtrim(strtr(base64_encode($doc->file_path), '+/', '-_'), '=');

        return response()->json(['success' => true, 'data' => $doc, 'message' => 'Document uploaded.'], 201);
    }

    public function deleteDocument(string $tutorId, int $docId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $doc   = $tutor->documents()->findOrFail($docId);

        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    // ── Education management ──────────────────────────────────────────────────

    public function storeEducation(Request $request, string $tutorId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();

        $data = $this->validateEducation($request, required: true);
        $data = $this->resolveInstituteName($data);

        $entry = $tutor->educationEntries()->create($data);
        $entry->load('university:id,name,short_name,logo');

        $this->notifyProfileEdited($tutor);

        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Education entry added.'], 201);
    }

    public function updateEducation(Request $request, string $tutorId, int $educationId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $entry = $tutor->educationEntries()->findOrFail($educationId);

        $data = $this->validateEducation($request, required: false);
        $data = $this->resolveInstituteName($data);

        $entry->update($data);
        $entry->load('university:id,name,short_name,logo');

        $this->notifyProfileEdited($tutor);

        return response()->json(['success' => true, 'data' => $entry, 'message' => 'Education entry updated.']);
    }

    public function deleteEducation(string $tutorId, int $educationId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $tutor->educationEntries()->findOrFail($educationId)->delete();

        $this->notifyProfileEdited($tutor);

        return response()->json(['success' => true, 'message' => 'Education entry deleted.']);
    }

    private function validateEducation(Request $request, bool $required): array
    {
        return $request->validate([
            'level'           => ($required ? 'required' : 'sometimes') . '|in:phd,masters,bachelor,hsc,ssc,o_level,a_level,other',
            'university_id'   => 'nullable|integer|exists:universities,id',
            'institute_name'  => 'nullable|string|max:255',
            'degree_title'    => 'nullable|string|max:150',
            'major_group'     => 'nullable|string|max:150',
            'result'          => 'nullable|string|max:100',
            'year_of_passing' => 'nullable|integer|min:1970|max:' . date('Y'),
        ]);
    }

    /** Fill institute_name from the selected university when it's blank. */
    private function resolveInstituteName(array $data): array
    {
        if (!empty($data['university_id']) && empty($data['institute_name'])) {
            $data['institute_name'] = University::find($data['university_id'])?->name;
        }
        return $data;
    }

    // ── Teaching video management ─────────────────────────────────────────────

    public function updateVideo(Request $request, string $tutorId, int $videoId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $video = $tutor->teachingVideos()->findOrFail($videoId);

        $data = $request->validate([
            'title'       => 'sometimes|string|max:200',
            'subject'     => 'sometimes|string|max:100',
            'class_level' => 'sometimes|string|max:100',
            'medium'      => 'sometimes|string|max:50',
        ]);

        $video->update($data);
        if ($video->file_path) {
            $video->file_url = Storage::disk('public')->url($video->file_path);
        }

        return response()->json(['success' => true, 'data' => $video, 'message' => 'Video updated.']);
    }

    public function reviewVideo(Request $request, string $tutorId, int $videoId): JsonResponse
    {
        $data  = $request->validate([
            'action'      => 'required|in:approve,reject',
            'review_note' => 'nullable|string|max:500',
        ]);

        $tutor = TutorProfile::with('user')->where('tutor_id', $tutorId)->firstOrFail();
        $video = $tutor->teachingVideos()->findOrFail($videoId);

        $reviewStatus = $data['action'] === 'approve' ? 'approved' : 'rejected';

        $video->update([
            'review_status' => $reviewStatus,
            'review_note'   => $data['review_note'] ?? null,
            'reviewed_by'   => $request->user()->id,
            'reviewed_at'   => now(),
        ]);

        try {
            if ($tutor->user) {
                $tutor->user->notify(new TutorVideoReviewedNotification(
                    videoTitle:  $video->title,
                    action:      $reviewStatus,
                    reviewNote:  $data['review_note'] ?? null,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Tutor video review notification failed', ['error' => $e->getMessage(), 'video' => $video->id]);
        }

        $message = $data['action'] === 'approve' ? 'Video approved.' : 'Video rejected.';
        return response()->json(['success' => true, 'data' => $video, 'message' => $message]);
    }

    public function deleteVideo(string $tutorId, int $videoId): JsonResponse
    {
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $video = $tutor->teachingVideos()->findOrFail($videoId);

        Storage::disk('public')->delete($video->file_path);
        $video->delete();

        return response()->json(['success' => true, 'message' => 'Video deleted.']);
    }

    public function updateStatus(Request $request, string $tutorId): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:active,inactive,suspended']);
        $tutor = TutorProfile::where('tutor_id', $tutorId)->firstOrFail();
        $oldStatus   = $tutor->status;
        $isSuspended = $data['status'] === 'suspended';
        $tutor->update($data);
        $tutor->user->update(['is_active' => !$isSuspended]);
        if ($isSuspended) {
            // Immediately invalidate all active sessions
            $tutor->user->tokens()->delete();
        }

        // Notify the tutor when their account is suspended or brought back from suspension
        try {
            if ($isSuspended) {
                $tutor->user->notify(new AccountSuspendedNotification());
            } elseif ($oldStatus === 'suspended' && in_array($data['status'], ['active', 'inactive'])) {
                $tutor->user->notify(new AccountReactivatedNotification());
            }
        } catch (\Exception $e) {
            Log::error('Account status notification failed', ['error' => $e->getMessage(), 'tutor' => $tutorId]);
        }

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}
