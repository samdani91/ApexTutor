<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TutorEmergencyContact;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorEmergencyContactController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

    public function show(Request $request): JsonResponse
    {
        $contact = $request->user()->tutorProfile->emergencyContact;
        return response()->json(['success' => true, 'data' => $contact]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'relation' => 'required|in:father,mother,sibling,spouse,friend,relative,other',
            'phone'    => 'required|string|max:20',
            'address'  => 'nullable|string|max:500',
        ]);

        $profile = $request->user()->tutorProfile;

        if ($profile->is_verified) {
            $pending = $profile->pending_changes ?? [];
            $pending['emergency_contact'] = $data;
            $pending['submitted_at'] = now()->toISOString();
            $profile->update(['pending_changes' => $pending, 'pending_note' => null]);

            return response()->json([
                'success' => true,
                'pending' => true,
                'message' => 'Emergency contact saved -- pending admin review.',
            ]);
        }

        $contact = TutorEmergencyContact::updateOrCreate(
            ['tutor_profile_id' => $profile->id],
            $data
        );

        $profile->touch();

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Emergency contact saved.',
        ]);
    }
}
