<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\User;
use App\Notifications\ConnectionRequestedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConnectionRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = $request->user()->guardianProfile->connectionRequests()
            ->with([
                'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count,bio',
                'tutorProfile.user:id,name,avatar',
            ])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'data' => $connections]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutor_profile_id' => 'required|exists:tutor_profiles,id',
            'requirement_id'   => 'nullable|exists:tuition_requirements,id',
            'guardian_message' => 'nullable|string|max:1000',
        ]);
        $guardianProfile = $request->user()->guardianProfile;
        $connection = $guardianProfile->connectionRequests()->create($data);

        try {
            $tutor    = \App\Models\TutorProfile::with('user:id,name')->find($data['tutor_profile_id']);
            $admins   = User::whereIn('role', ['admin', 'super_admin'])->get();
            $notification = new ConnectionRequestedNotification(
                guardianName:    $request->user()->name,
                tutorName:       $tutor?->user?->name ?? 'a tutor',
                guardianMessage: $data['guardian_message'] ?? null,
            );
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        } catch (\Exception $e) {
            Log::error('Connection request notification failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'data' => $connection, 'message' => 'Connection request sent.'], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $connection = $request->user()->guardianProfile->connectionRequests()->with(['tutorProfile','requirement'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $connection]);
    }

    public function confirmed(Request $request): JsonResponse
    {
        $tuitions = $request->user()->guardianProfile->connectionRequests()
            ->where('status', 'confirmed')
            ->with([
                'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count,bio',
                'tutorProfile.user:id,name,avatar,phone',
                'tutorProfile.tuitionPreference:id,tutor_profile_id,district_id,expected_salary_min,expected_salary_max',
                'tutorProfile.tuitionPreference.district:id,name',
            ])
            ->latest('confirmed_at')
            ->get();
        return response()->json(['success' => true, 'data' => $tuitions]);
    }
}
