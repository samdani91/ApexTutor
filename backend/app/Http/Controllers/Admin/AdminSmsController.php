<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastSmsJob;
use App\Models\GuardianProfile;
use App\Models\TutorProfile;
use App\Models\University;
use App\Models\User;
use App\Services\BulkSmsBdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    $inner->where('users.name',                      'like', "%{$q}%")
                          ->orWhere('users.phone',                   'like', "%{$q}%")
                          ->orWhere('users.email',                   'like', "%{$q}%")
                          ->orWhere('tutor_profiles.tutor_id',       'like', "%{$q}%")
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

    /** Universities list for the university-target picker. */
    public function getUniversities(): JsonResponse
    {
        $universities = University::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        return response()->json(['success' => true, 'data' => $universities]);
    }

    /**
     * Returns how many users match the given broadcast target.
     * ?target=all|tutors|guardians|university  &university_id=<id>
     */
    public function broadcastPreview(Request $request): JsonResponse
    {
        $target       = $request->get('target', 'all');
        $universityId = $request->integer('university_id') ?: null;

        $count = $this->recipientQuery($target, $universityId)->count();

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

        return response()->json([
            'success' => true,
            'data'    => ['id' => $user->id],
            'message' => "SMS sent successfully to {$user->name} ({$user->phone}).",
        ]);
    }

    public function broadcast(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'       => 'required|string|min:3|max:1000',
            'target'        => 'required|in:all,tutors,guardians,university',
            'university_id' => 'required_if:target,university|nullable|integer|exists:universities,id',
        ]);

        $target       = $data['target'];
        $universityId = isset($data['university_id']) ? (int) $data['university_id'] : null;

        $phones = $this->recipientQuery($target, $universityId)
            ->pluck('users.phone')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($phones)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients with phone numbers found for the selected target.',
            ], 422);
        }

        // Queued rather than sent inline: a large broadcast is chunked into many
        // sequential BulkSMSBD calls and would otherwise block the admin's request
        // past PHP's max_execution_time. Delivery failures surface in the log and
        // the failed_jobs table rather than in this response.
        BroadcastSmsJob::dispatch($phones, $data['message']);

        $count = count($phones);
        $label = $this->targetLabel($target, $universityId);

        return response()->json([
            'success' => true,
            'data'    => ['id' => $count],
            'message' => "Broadcast queued for {$count} {$label}. Delivery may take a few minutes.",
        ]);
    }

    /** Build a query that returns rows from `users` for the given broadcast target. */
    private function recipientQuery(string $target, ?int $universityId): \Illuminate\Database\Query\Builder
    {
        $base = DB::table('users')
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '');

        return match ($target) {
            'tutors'     => $base->where('users.role', 'tutor'),
            'guardians'  => $base->whereIn('users.role', ['guardian', 'student']),
            'university' => $base
                ->where('users.role', 'tutor')
                ->join('tutor_profiles', 'tutor_profiles.user_id', '=', 'users.id')
                ->join('education_entries', function ($j) use ($universityId) {
                    $j->on('education_entries.tutor_profile_id', '=', 'tutor_profiles.id')
                      ->where('education_entries.university_id', '=', $universityId);
                })
                ->distinct(),
            default      => $base->whereIn('users.role', ['tutor', 'guardian', 'student']),
        };
    }

    private function targetLabel(string $target, ?int $universityId): string
    {
        return match ($target) {
            'tutors'     => 'tutors',
            'guardians'  => 'guardians & students',
            'university' => ($universityId
                ? (University::find($universityId)?->name ?? 'university') . ' tutors'
                : 'university tutors'),
            default      => 'users',
        };
    }
}
