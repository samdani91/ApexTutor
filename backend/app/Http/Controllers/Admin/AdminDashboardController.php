<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\GuardianProfile;
use App\Models\Review;
use App\Models\TeachingVideo;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [
            'total_tutors'            => TutorProfile::count(),
            'verified_tutors'         => TutorProfile::where('is_verified', true)->count(),
            'pending_verifications'   => TutorProfile::where('verification_status', 'pending')->count(),
            'active_connections'      => ConnectionRequest::where('status', 'confirmed')->count(),
            'pending_connections'     => ConnectionRequest::where('status', 'pending')->count(),
            'total_users'             => User::count(),
            'total_guardians'         => GuardianProfile::count(),
            'pending_profile_changes' => TutorProfile::whereNotNull('pending_changes')->count(),
            'pending_reviews'         => Review::where('moderation_status', 'pending')->count(),
            'pending_videos'          => TeachingVideo::where('review_status', 'pending')->count(),
        ]]);
    }

    public function analytics(): JsonResponse
    {
        $months = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month'       => $date->format('M Y'),
                'connections' => ConnectionRequest::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'confirmed'   => ConnectionRequest::where('status', 'confirmed')
                    ->whereYear('confirmed_at', $date->year)
                    ->whereMonth('confirmed_at', $date->month)->count(),
            ];
        });

        return response()->json(['success' => true, 'data' => [
            'monthly_connections' => $months,
            'connection_statuses' => ConnectionRequest::selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status'),
            'top_tutors' => TutorProfile::with('user:id,name')
                ->where('is_verified', true)
                ->orderByDesc('review_count')
                ->take(5)
                ->get(['id', 'user_id', 'rating', 'review_count']),
        ]]);
    }
}
