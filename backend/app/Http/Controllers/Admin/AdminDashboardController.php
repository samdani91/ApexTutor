<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\GuardianProfile;
use App\Models\PlatformFeedback;
use App\Models\Review;
use App\Models\SupportTicket;
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
            'pending_feedbacks'       => PlatformFeedback::where('moderation_status', 'pending')->count(),
            'pending_videos'          => TeachingVideo::where('review_status', 'pending')->count(),
            // Only count standalone avatar items — tutors whose avatar is already in
            // pending_changes are already included in pending_profile_changes above.
            'pending_avatars'         => User::whereNotNull('pending_avatar')
                ->where(function ($q) {
                    $q->whereIn('role', ['guardian', 'student'])
                      ->orWhere(function ($q2) {
                          $q2->where('role', 'tutor')
                             ->whereHas('tutorProfile', fn($tp) =>
                                 $tp->whereNull('pending_changes')
                                    ->orWhereRaw("JSON_EXTRACT(pending_changes, '$.avatar') IS NULL")
                             );
                      });
                })->count(),
            'open_tickets'            => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
        ]]);
    }

    public function analytics(\Illuminate\Http\Request $request): JsonResponse
    {
        $from = $request->filled('from')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->from)->startOfMonth()
            : now()->subMonths(11)->startOfMonth();

        $to = $request->filled('to')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->to)->endOfMonth()
            : now()->endOfMonth();

        // Never go into the future
        if ($to->isAfter(now())) {
            $to = now()->endOfMonth();
        }
        // Enforce a sensible maximum range (60 months) to avoid huge queries
        if ($from->diffInMonths($to) > 59) {
            $from = $to->copy()->subMonths(59)->startOfMonth();
        }

        $cursor  = $from->copy();
        $months  = collect();
        while ($cursor->lte($to)) {
            $months->push([
                'month'       => $cursor->format('M Y'),
                'month_key'   => $cursor->format('Y-m'),
                'connections' => ConnectionRequest::whereYear('created_at', $cursor->year)
                    ->whereMonth('created_at', $cursor->month)->count(),
                'confirmed'   => ConnectionRequest::where('status', 'confirmed')
                    ->whereYear('confirmed_at', $cursor->year)
                    ->whereMonth('confirmed_at', $cursor->month)->count(),
            ]);
            $cursor->addMonth();
        }

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
