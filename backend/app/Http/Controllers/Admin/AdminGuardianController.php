<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianProfile;
use App\Notifications\AccountReactivatedNotification;
use App\Notifications\AccountSuspendedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminGuardianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GuardianProfile::with('user:id,name,email,phone,avatar')
            ->when($request->search, function ($q, $search) use ($request) {
                $by = $request->get('search_by', 'name');
                $q->where(function ($inner) use ($search, $by) {
                    if ($by === 'id') {
                        $inner->where('guardian_id', 'like', "%{$search}%");
                    } elseif ($by === 'email') {
                        $inner->whereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%"));
                    } else {
                        $inner->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                    }
                });
            })
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(string $guardianId): JsonResponse
    {
        $guardian = GuardianProfile::with([
            'user',
            'connectionRequests' => fn($q) => $q->with([
                'tutorProfile:id,user_id,tutor_id,verification_status',
                'tutorProfile.user:id,name,email',
            ])->latest()->take(30),
            'shortlists' => fn($q) => $q->with([
                'tutorProfile:id,user_id,tutor_id',
                'tutorProfile.user:id,name',
            ]),
        ])->where('guardian_id', $guardianId)->firstOrFail();

        return response()->json(['success' => true, 'data' => $guardian]);
    }

    public function update(Request $request, string $guardianId): JsonResponse
    {
        $guardian = GuardianProfile::with('user')->where('guardian_id', $guardianId)->firstOrFail();

        $validated = $request->validate([
            'user.name'    => 'sometimes|string|max:100',
            'user.email'   => 'sometimes|email|unique:users,email,' . $guardian->user_id,
            'user.phone'   => 'nullable|string|max:20',
            'user.address' => 'nullable|string|max:255',

            'profile.occupation'             => 'nullable|string|max:100',
            'profile.relationship_to_student'=> 'nullable|string|max:50',
            'profile.nid_number'             => 'nullable|string|max:30',
            'profile.account_type'           => 'nullable|in:guardian,student',
        ]);

        DB::transaction(function () use ($validated, $guardian) {
            if ($userData = $validated['user'] ?? null) {
                $guardian->user->update(array_filter($userData, fn($v) => $v !== null));
            }
            if ($profileData = $validated['profile'] ?? null) {
                $filtered = array_filter($profileData, fn($v) => $v !== null);
                if (!empty($filtered)) $guardian->update($filtered);
            }
        });

        return response()->json(['success' => true, 'message' => 'Guardian profile updated.']);
    }

    public function updateStatus(Request $request, string $guardianId): JsonResponse
    {
        $data = $request->validate([
            'is_active' => 'required|boolean',
            'reason'    => 'nullable|string|max:500',
        ]);

        $guardian    = GuardianProfile::with('user')->where('guardian_id', $guardianId)->firstOrFail();
        $wasActive   = (bool) $guardian->user->is_active;
        $guardian->user->update(['is_active' => $data['is_active']]);

        if (!$data['is_active']) {
            $guardian->user->tokens()->delete();
        }

        // Notify the guardian when their account is suspended or reactivated
        try {
            if (!$data['is_active']) {
                $guardian->user->notify(new AccountSuspendedNotification(reason: $data['reason'] ?? null));
            } elseif (!$wasActive) {
                $guardian->user->notify(new AccountReactivatedNotification());
            }
        } catch (\Exception $e) {
            Log::error('Guardian account status notification failed', ['error' => $e->getMessage(), 'guardian' => $guardianId]);
        }

        return response()->json(['success' => true, 'message' => $data['is_active'] ? 'Guardian activated.' : 'Guardian suspended.']);
    }

    public function uploadNid(Request $request, string $guardianId): JsonResponse
    {
        $request->validate(['nid_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096']);

        $file     = $request->file('nid_document');
        $realMime = mime_content_type($file->getRealPath());

        if (!in_array($realMime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type.'], 422);
        }

        $guardian = GuardianProfile::with('user')->where('guardian_id', $guardianId)->firstOrFail();

        if ($guardian->nid_document) {
            Storage::disk('public')->delete($guardian->nid_document);
        }

        $path = $file->store('nid_documents/' . $guardian->user_id, 'public');
        $guardian->update(['nid_document' => $path]);

        return response()->json([
            'success' => true,
            'data'    => ['nid_document_url' => rtrim(config('app.url'), '/') . '/private-storage/' . rtrim(strtr(base64_encode($path), '+/', '-_'), '=')],
            'message' => 'NID document updated.',
        ]);
    }

    public function deleteNid(string $guardianId): JsonResponse
    {
        $guardian = GuardianProfile::where('guardian_id', $guardianId)->firstOrFail();

        if ($guardian->nid_document) {
            Storage::disk('public')->delete($guardian->nid_document);
            $guardian->update(['nid_document' => null]);
        }

        return response()->json(['success' => true, 'message' => 'NID document removed.']);
    }
}
