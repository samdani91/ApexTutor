<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGuardianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GuardianProfile::with('user:id,name,email,phone,avatar')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->sort === 'id_asc', fn($q) => $q->orderBy('id'), fn($q) => $q->orderByDesc('id'));

        return response()->json(['success' => true, 'data' => $query->paginate(10)]);
    }

    public function show(int $id): JsonResponse
    {
        $guardian = GuardianProfile::with([
            'user',
            'tuitionRequirements.subjects',
            'connectionRequests' => fn($q) => $q->with([
                'tutorProfile:id,user_id,tutor_id,verification_status',
                'tutorProfile.user:id,name,email',
            ])->latest()->take(30),
            'shortlists' => fn($q) => $q->with([
                'tutorProfile:id,user_id,tutor_id',
                'tutorProfile.user:id,name',
            ]),
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $guardian]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $guardian = GuardianProfile::with('user')->findOrFail($id);

        $request->validate([
            'user.name'    => 'sometimes|string|max:100',
            'user.email'   => 'sometimes|email|unique:users,email,' . $guardian->user_id,
            'user.phone'   => 'nullable|string|max:20',
            'user.address' => 'nullable|string|max:255',

            'profile.occupation'             => 'nullable|string|max:100',
            'profile.relationship_to_student'=> 'nullable|string|max:50',
            'profile.nid_number'             => 'nullable|string|max:30',
            'profile.account_type'           => 'nullable|in:guardian,student',
        ]);

        DB::transaction(function () use ($request, $guardian) {
            if ($userData = $request->input('user')) {
                $guardian->user->update(array_filter($userData, fn($v) => $v !== null));
            }
            if ($profileData = $request->input('profile')) {
                $filtered = array_filter($profileData, fn($v) => $v !== null);
                if (!empty($filtered)) $guardian->update($filtered);
            }
        });

        return response()->json(['success' => true, 'message' => 'Guardian profile updated.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'is_active' => 'required|boolean',
            'reason'    => 'nullable|string|max:500',
        ]);

        $guardian = GuardianProfile::with('user')->findOrFail($id);
        $guardian->user->update(['is_active' => $data['is_active']]);

        if (!$data['is_active']) {
            $guardian->user->tokens()->delete();
        }

        return response()->json(['success' => true, 'message' => $data['is_active'] ? 'Guardian activated.' : 'Guardian suspended.']);
    }
}
