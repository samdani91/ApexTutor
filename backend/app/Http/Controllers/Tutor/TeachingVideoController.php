<?php
namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TeachingVideo;
use App\Models\User;
use App\Notifications\AdminPendingVideoNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class TeachingVideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $videos = $request->user()->tutorProfile
            ->teachingVideos()
            ->get()
            ->map(fn($v) => $this->withUrl($v));

        return response()->json(['success' => true, 'data' => $videos]);
    }

    public function store(Request $request): JsonResponse
    {
        $profile = $request->user()->tutorProfile;

        if ($profile->teachingVideos()->count() >= 4) {
            return response()->json(['success' => false, 'message' => 'Maximum 4 videos allowed.'], 422);
        }

        $request->validate([
            'video'       => 'required|file|max:153600|mimes:mp4,webm,mov',
            'title'       => 'required|string|max:200',
            'subject'     => 'required|string|max:100',
            'class_level' => 'required|string|max:100',
            'medium'      => 'required|string|max:50',
        ]);

        $file = $request->file('video');
        $path = $file->store('teaching_videos', 'public');

        $video = $profile->teachingVideos()->create([
            'title'         => $request->title,
            'subject'       => $request->subject,
            'class_level'   => $request->class_level,
            'medium'        => $request->medium,
            'file_path'     => $path,
            'file_size'     => $file->getSize(),
            'review_status' => 'pending',
        ]);

        try {
            $admins = User::where('role', 'super_admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminPendingVideoNotification(
                    tutorName:      $request->user()->name,
                    tutorProfileId: $profile->id,
                    videoTitle:     $request->title,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Admin video notification failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'data' => $this->withUrl($video), 'message' => 'Video uploaded.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        $video   = $profile->teachingVideos()->findOrFail($id);

        $request->validate([
            'title'       => 'sometimes|string|max:200',
            'subject'     => 'sometimes|string|max:100',
            'class_level' => 'sometimes|string|max:100',
            'medium'      => 'sometimes|string|max:50',
        ]);

        $video->update($request->only(['title', 'subject', 'class_level', 'medium']));

        return response()->json(['success' => true, 'data' => $this->withUrl($video)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->tutorProfile;
        $video   = $profile->teachingVideos()->findOrFail($id);

        Storage::disk('public')->delete($video->file_path);
        $video->delete();

        return response()->json(['success' => true, 'message' => 'Video deleted.']);
    }

    private function withUrl(TeachingVideo $video): TeachingVideo
    {
        if ($video->file_path) {
            $video->file_url = Storage::disk('public')->url($video->file_path);
        }
        return $video;
    }
}
