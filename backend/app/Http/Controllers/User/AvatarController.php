<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminPendingAvatarNotification;
use App\Services\PendingProfileChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function __construct(private readonly PendingProfileChangeService $pending) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $user = $request->user();

        // Admins update their avatar immediately — no approval needed.
        if ($user->isAdmin()) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success'    => true,
                'avatar_url' => Storage::disk('public')->url($path),
                'message'    => 'Profile picture updated.',
            ]);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $url  = Storage::disk('public')->url($path);

        // Verified tutors: stage via the existing pending-changes pipeline so the avatar
        // diff appears alongside other profile changes in the admin review queue.
        if ($user->isTutor()) {
            $profile = $user->tutorProfile;
            if ($profile && $this->pending->requiresPendingFlow($profile)) {
                $this->pending->stageAvatar($profile, $path, $url);
                return response()->json([
                    'success'            => true,
                    'pending'            => true,
                    'pending_avatar_url' => $url,
                    'message'            => 'Profile picture submitted — pending admin approval.',
                ]);
            }
        }

        // Guardians and unverified tutors: write pending_avatar on the user only.
        if ($user->pending_avatar) {
            Storage::disk('public')->delete($user->pending_avatar);
        }
        $user->pending_avatar = $path;
        $user->save();

        $this->notifyAdmins($user);

        return response()->json([
            'success'            => true,
            'pending'            => true,
            'pending_avatar_url' => $url,
            'message'            => 'Profile picture submitted — pending admin approval.',
        ]);
    }

    private function notifyAdmins(User $user): void
    {
        try {
            $admins = User::where('role', 'super_admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminPendingAvatarNotification(
                    userName: $user->name,
                    userRole: $user->role,
                    userId:   $user->id,
                ));
            }
        } catch (\Exception $e) {
            Log::error('Pending avatar notification failed', [
                'error'   => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}
