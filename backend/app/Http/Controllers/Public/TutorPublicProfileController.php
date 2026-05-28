<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TutorPublicProfileController extends Controller
{
    public function show(string $publicId): JsonResponse
    {
        $profile = TutorProfile::with([
            'user:id,name,avatar',
            'educationEntries',
            'tuitionPreference.subjects',
            'tuitionPreference.days',
            'tuitionPreference.district:id,name',
            'tuitionPreference.locations.area:id,name',
            'personalInfo:id,tutor_profile_id,gender,religion,nationality,fathers_name,mothers_name',
            'teachingVideos',
            'reviews' => fn($q) => $q->with('guardianProfile.user:id,name,avatar')->latest()->take(10),
        ])
        ->where('status', 'active')
        ->where('is_verified', true)
        ->where('public_id', $publicId)
        ->firstOrFail();

        foreach ($profile->teachingVideos as $video) {
            if ($video->file_path) {
                $video->file_url = Storage::disk('public')->url($video->file_path);
            }
        }

        $visitor = auth('sanctum')->user();
        if ($visitor && in_array($visitor->role, ['guardian', 'student'])) {
            $profile->increment('profile_view_count');
        }

        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function reviews(string $publicId): JsonResponse
    {
        $profile = TutorProfile::where('public_id', $publicId)->firstOrFail();
        $reviews = $profile->reviews()->with('guardianProfile.user:id,name,avatar')->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $reviews]);
    }
}
