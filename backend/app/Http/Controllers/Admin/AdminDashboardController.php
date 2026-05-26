<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\ProfileChangeRequest;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [
            'total_tutors'            => TutorProfile::count(),
            'pending_verifications'   => TutorProfile::where('verification_status', 'pending')->count(),
            'active_connections'      => ConnectionRequest::where('status', 'connected')->count(),
            'total_users'             => User::count(),
            'pending_change_requests' => ProfileChangeRequest::where('status', 'pending')->count(),
        ]]);
    }
}
