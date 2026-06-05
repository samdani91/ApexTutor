<?php
namespace App\Http\Controllers\Admin;

use App\DTO\PendingChangesSchema;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminUserAvatarController extends Controller
{
    public function replace(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $user = User::findOrFail($id);

        // Delete both the live and pending avatars (admin is setting the definitive one)
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        if ($user->pending_avatar) {
            try { Storage::disk('public')->delete($user->pending_avatar); } catch (\Exception) {}
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar         = $path;
        $user->pending_avatar = null;
        $user->save();

        // If this user is a verified tutor with a pending_changes['avatar'], clear it
        if ($user->isTutor() && $user->tutorProfile) {
            $profile = $user->tutorProfile;
            if (!empty($profile->pending_changes['avatar'])) {
                $changes = $profile->pending_changes;
                unset($changes['avatar']);
                $profile->update(['pending_changes' => PendingChangesSchema::from($changes)->isEmpty() ? null : $changes]);
            }
        }

        return response()->json([
            'success'    => true,
            'avatar_url' => Storage::disk('public')->url($path),
            'message'    => 'Avatar updated.',
        ]);
    }

    public function remove(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->avatar) {
            try { Storage::disk('public')->delete($user->avatar); } catch (\Exception) {}
        }
        if ($user->pending_avatar) {
            try { Storage::disk('public')->delete($user->pending_avatar); } catch (\Exception) {}
        }

        $user->avatar         = null;
        $user->pending_avatar = null;
        $user->save();

        // Clear avatar from tutor's pending_changes if present
        if ($user->isTutor() && $user->tutorProfile) {
            $profile = $user->tutorProfile;
            if (!empty($profile->pending_changes['avatar'])) {
                $changes = $profile->pending_changes;
                unset($changes['avatar']);
                $profile->update(['pending_changes' => PendingChangesSchema::from($changes)->isEmpty() ? null : $changes]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Avatar removed.']);
    }
}
