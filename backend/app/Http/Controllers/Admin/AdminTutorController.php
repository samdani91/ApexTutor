<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
