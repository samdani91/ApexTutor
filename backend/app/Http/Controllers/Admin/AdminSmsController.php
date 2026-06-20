<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianProfile;
use App\Models\TutorProfile;
use App\Models\User;
use App\Services\BulkSmsBdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSmsController extends Controller
{
    public function searchUsers(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::whereIn('users.role', ['tutor', 'guardian', 'student'])
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '')
            ->leftJoin('tutor_profiles',    'tutor_profiles.user_id',    '=', 'users.id')
            ->leftJoin('guardian_profiles', 'guardian_profiles.user_id', '=', 'users.id')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('users.name',                   'like', "%{$q}%")
                          ->orWhere('users.phone',                'like', "%{$q}%")
                          ->orWhere('users.email',                'like', "%{$q}%")
                          ->orWhere('tutor_profiles.tutor_id',    'like', "%{$q}%")
                          ->orWhere('guardian_profiles.guardian_id', 'like', "%{$q}%");
                });
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.role',
                'tutor_profiles.tutor_id',
                'guardian_profiles.guardian_id'
            )
            ->orderBy('users.name')
            ->limit(20)
            ->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'phone'       => $u->phone,
                'role'        => $u->role,
                'platform_id' => $u->tutor_id ?? $u->guardian_id ?? null,
            ]);

        return response()->json(['success' => true, 'data' => $users]);
    }

    /** Returns how many users have phone numbers (for broadcast preview). */
    public function broadcastPreview(): JsonResponse
    {
        $count = User::whereIn('role', ['tutor', 'guardian', 'student'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        return response()->json(['success' => true, 'data' => ['recipients' => $count]]);
    }

    public function send(Request $request, BulkSmsBdService $sms): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|min:3|max:1000',
        ]);

        $user = User::findOrFail($data['user_id']);

        if (empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'This user does not have a phone number on record.',
            ], 422);
        }

        $result = $sms->send($user->phone, $data['message']);

        if (!$result['success']) {
            Log::warning('Admin manual SMS failed', [
                'admin_id' => $request->user()->id,
                'user_id'  => $user->id,
                'response' => $result['response'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'SMS could not be delivered. Please try again or check the SMS balance.',
            ], 502);
        }

        // data.id lets LogAdminActivity resolve the target_id as the recipient user
        return response()->json([
            'success' => true,
            'data'    => ['id' => $user->id],
            'message' => "SMS sent successfully to {$user->name} ({$user->phone}).",
        ]);
    }

    public function broadcast(Request $request, BulkSmsBdService $sms): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|min:3|max:1000',
        ]);

        $phones = User::whereIn('role', ['tutor', 'guardian', 'student'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();

        if (empty($phones)) {
            return response()->json([
                'success' => false,
                'message' => 'No users with phone numbers found.',
            ], 422);
        }

        $result = $sms->broadcast($phones, $data['message']);

        if (!$result['success']) {
            Log::warning('Admin broadcast SMS failed', [
                'admin_id'   => $request->user()->id,
                'recipients' => count($phones),
                'response'   => $result['response'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Broadcast SMS failed. Please check the SMS balance and try again.',
            ], 502);
        }

        $count = count($phones);

        // data.id carries recipient count so LogAdminActivity can record it
        return response()->json([
            'success' => true,
            'data'    => ['id' => $count],
            'message' => "Broadcast SMS sent to {$count} users.",
        ]);
    }
}
