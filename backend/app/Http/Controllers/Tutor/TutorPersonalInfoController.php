<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TutorPersonalInfo;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorPersonalInfoController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

    public function show(Request $request): JsonResponse
    {
        $info = $request->user()->tutorProfile->personalInfo;
        return response()->json(['success' => true, 'data' => $info]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gender'            => 'nullable|in:male,female,other',
            'date_of_birth'     => 'nullable|date',
            'religion'          => 'nullable|in:islam,hinduism,christianity,buddhism,other',
            'nationality'       => 'nullable|string|max:100',
            'present_address'   => 'nullable|string|max:500',
            'permanent_address' => 'nullable|string|max:500',
            'additional_phone'  => 'nullable|string|max:20',
            'national_id'       => 'nullable|string|max:30',
            'facebook_url'      => 'nullable|url|max:500',
            'linkedin_url'      => 'nullable|url|max:500',
            'fathers_name'      => 'nullable|string|max:150',
            'fathers_phone'     => 'nullable|string|max:20',
            'mothers_name'      => 'nullable|string|max:150',
            'mothers_phone'     => 'nullable|string|max:20',
        ]);

        $profile = $request->user()->tutorProfile;

        if ($this->pending->requiresPendingFlow($profile)) {
            $this->pending->shallowMerge($profile, 'personal_info', $data);
            return response()->json(['success' => true, 'pending' => true, 'message' => 'Personal information saved — pending admin review.']);
        }

        $info = TutorPersonalInfo::updateOrCreate(
            ['tutor_profile_id' => $profile->id],
            $data
        );

        return response()->json(['success' => true, 'data' => $info, 'message' => 'Personal information saved.']);
    }
}
