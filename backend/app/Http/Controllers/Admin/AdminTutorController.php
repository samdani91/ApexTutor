<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTutorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TutorProfile::with('user:id,name,email,phone,avatar')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->verification_status, fn($q) => $q->where('verification_status', $request->verification_status))
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(int $id): JsonResponse
    {
        $tutor = TutorProfile::with([
            'user',
            'educationEntries',
            'tuitionPreference.subjects',
            'tuitionPreference.days',
            'tuitionPreference.locations.area',
            'personalInfo',
            'emergencyContact',
            'documents',
            'teachingVideos',
            'connectionRequests' => fn($q) => $q->with('guardianProfile.user:id,name,email')->latest()->take(20),
            'reviews' => fn($q) => $q->with('guardianProfile.user:id,name')->latest()->take(10),
        ])->findOrFail($id);

        $tutor->documents->each(function ($doc) {
            $doc->file_url = \Illuminate\Support\Facades\Storage::disk('public')->url($doc->file_path);
        });
        foreach ($tutor->teachingVideos as $video) {
            if ($video->file_path) {
                $video->file_url = \Illuminate\Support\Facades\Storage::disk('public')->url($video->file_path);
            }
        }

        return response()->json(['success' => true, 'data' => $tutor]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tutor = TutorProfile::with(['user','tuitionPreference','personalInfo','emergencyContact'])->findOrFail($id);

        $request->validate([
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

        DB::transaction(function () use ($request, $tutor) {
            if ($userData = $request->input('user')) {
                $tutor->user->update(array_filter($userData, fn($v) => $v !== null || in_array($v, ['phone','address'])));
            }

            if ($profileData = $request->input('profile')) {
                $update = array_filter($profileData, fn($v) => $v !== null);
                $update['pending_changes'] = null;
                $update['pending_note']    = null;
                if (!empty($update)) $tutor->update($update);
            } else {
                // Always clear pending changes on any admin edit
                $tutor->update(['pending_changes' => null, 'pending_note' => null]);
            }

            if ($prefData = $request->input('preference')) {
                $prefData = array_filter($prefData, fn($v) => $v !== null);
                if (!empty($prefData)) {
                    $tutor->tuitionPreference
                        ? $tutor->tuitionPreference->update($prefData)
                        : $tutor->tuitionPreference()->create(array_merge(['tutor_profile_id' => $tutor->id], $prefData));
                }
            }

            if ($piData = $request->input('personal_info')) {
                $piData = array_filter($piData, fn($v) => $v !== null);
                if (!empty($piData)) {
                    $tutor->personalInfo
                        ? $tutor->personalInfo->update($piData)
                        : $tutor->personalInfo()->create(array_merge(['tutor_profile_id' => $tutor->id], $piData));
                }
            }

            if ($ecData = $request->input('emergency_contact')) {
                $ecData = array_filter($ecData, fn($v) => $v !== null);
                if (!empty($ecData)) {
                    $tutor->emergencyContact
                        ? $tutor->emergencyContact->update($ecData)
                        : $tutor->emergencyContact()->create(array_merge(['tutor_profile_id' => $tutor->id], $ecData));
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Tutor profile updated.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:active,inactive,suspended']);
        $tutor = TutorProfile::findOrFail($id);
        $tutor->update($data);
        $isSuspended = $data['status'] === 'suspended';
        $tutor->user->update(['is_active' => !$isSuspended]);
        if ($isSuspended) {
            // Immediately invalidate all active sessions
            $tutor->user->tokens()->delete();
        }
        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}
