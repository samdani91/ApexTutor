<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
