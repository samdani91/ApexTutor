<?php
namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\TuitionJobApplication;
use App\Models\User;
use App\Notifications\ConnectionRequestedNotification;
use App\Notifications\ConnectionRequestedTutorNotification;
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
            ->limit(500)
            ->get();
        return response()->json(['success' => true, 'data' => $connections]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tutor_profile_id' => 'required|exists:tutor_profiles,id',
            'guardian_message' => 'nullable|string|max:1000',
        ]);
        $guardianProfile = $request->user()->guardianProfile;

        $active = $guardianProfile->connectionRequests()
            ->where('tutor_profile_id', $data['tutor_profile_id'])
            ->whereNotIn('status', ['declined', 'closed'])
            ->exists();

        abort_if($active, 422, 'You already have an active connection request with this tutor.');

        $connection = $guardianProfile->connectionRequests()->create($data);

        try {
            $tutor    = \App\Models\TutorProfile::with('user:id,name')->find($data['tutor_profile_id']);
            $admins   = User::where('role', 'super_admin')->get();
            $adminNotification = new ConnectionRequestedNotification(
                guardianName:    $request->user()->name,
                tutorName:       $tutor?->user?->name ?? 'a tutor',
                guardianMessage: $data['guardian_message'] ?? null,
            );
            foreach ($admins as $admin) {
                $admin->notify($adminNotification);
            }
            if ($tutor?->user) {
                $tutor->user->notify(new ConnectionRequestedTutorNotification(
                    guardianName:    $request->user()->name,
                    connectionId:    $connection->id,
                    guardianMessage: $data['guardian_message'] ?? null,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Connection request notification failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'data' => $connection, 'message' => 'Connection request sent.'], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $connection = $request->user()->guardianProfile->connectionRequests()->with(['tutorProfile'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $connection]);
    }

    public function confirmed(Request $request): JsonResponse
    {
        $guardianProfile = $request->user()->guardianProfile;

        $tutorWith = [
            'tutorProfile:id,user_id,tutor_id,public_id,is_verified,rating,review_count,bio',
            'tutorProfile.user:id,name,avatar,phone',
            'tutorProfile.tuitionPreference:id,tutor_profile_id,district_id,expected_salary_min,expected_salary_max',
            'tutorProfile.tuitionPreference.district:id,name',
        ];

        // Flow 1: guardian found tutor themselves → ConnectionRequest confirmed by admin
        $fromConnections = $guardianProfile->connectionRequests()
            ->where('status', 'confirmed')
            ->with($tutorWith)
            ->latest('confirmed_at')
            ->get()
            ->map(fn($c) => [
                'id'           => 'cr_' . $c->id,
                'source'       => 'connection',
                'tutor_profile' => $c->tutorProfile,
                'confirmed_at' => $c->confirmed_at,
            ]);

        // Flow 2: guardian posted a job → admin confirmed a tutor application
        $fromJobs = TuitionJobApplication::where('status', 'connected')
            ->whereHas('tuitionJob', fn($q) => $q->where('guardian_profile_id', $guardianProfile->id))
            ->with([
                ...$tutorWith,
                'tuitionJob:id,public_id,title',
            ])
            ->latest('updated_at')
            ->get()
            ->map(fn($a) => [
                'id'            => 'ja_' . $a->id,
                'source'        => 'job',
                'tutor_profile' => $a->tutorProfile,
                'confirmed_at'  => $a->updated_at,
                'job_title'     => $a->tuitionJob?->title,
                'job_public_id' => $a->tuitionJob?->public_id,
            ]);

        $tuitions = $fromConnections->concat($fromJobs)
            ->sortByDesc('confirmed_at')
            ->values();

        return response()->json(['success' => true, 'data' => $tuitions]);
    }
}
