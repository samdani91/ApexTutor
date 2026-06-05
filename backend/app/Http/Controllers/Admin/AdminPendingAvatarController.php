<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AvatarApprovedNotification;
use App\Notifications\AvatarRejectedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminPendingAvatarController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::whereNotNull('pending_avatar')
            ->whereIn('role', ['tutor', 'guardian', 'student'])
            ->with([
                'tutorProfile:id,user_id,tutor_id',
                'guardianProfile:id,user_id,guardian_id',
            ])
            ->get()
            ->map(fn(User $user) => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'role'               => $user->role,
                'avatar_url'         => $user->avatar_url,
                'pending_avatar_url' => $user->pending_avatar_url,
                'profile_id'         => $user->tutorProfile?->tutor_id
                                     ?? $user->guardianProfile?->guardian_id,
            ]);

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$user->pending_avatar) {
            return response()->json(['success' => false, 'message' => 'No pending avatar.'], 422);
        }

        $oldAvatar           = $user->avatar;
        $user->avatar        = $user->pending_avatar;
        $user->pending_avatar = null;
        $user->save();

        if ($oldAvatar) {
            try {
                Storage::disk('public')->delete($oldAvatar);
            } catch (\Exception $e) {
                Log::warning('Could not delete old avatar', ['path' => $oldAvatar, 'error' => $e->getMessage()]);
            }
        }

        try {
            $user->notify(new AvatarApprovedNotification());
        } catch (\Exception $e) {
            Log::error('Avatar approved notification failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Avatar approved and applied.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $user = User::findOrFail($id);

        if (!$user->pending_avatar) {
            return response()->json(['success' => false, 'message' => 'No pending avatar.'], 422);
        }

        try {
            Storage::disk('public')->delete($user->pending_avatar);
        } catch (\Exception $e) {
            Log::warning('Could not delete pending avatar', ['path' => $user->pending_avatar, 'error' => $e->getMessage()]);
        }

        $user->pending_avatar = null;
        $user->save();

        try {
            $user->notify(new AvatarRejectedNotification(note: $request->input('note')));
        } catch (\Exception $e) {
            Log::error('Avatar rejected notification failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Avatar rejected.']);
    }
}
