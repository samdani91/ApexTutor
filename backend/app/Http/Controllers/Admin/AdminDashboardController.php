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

        if ($to->isAfter(now())) $to = now()->endOfMonth();
        if ($from->diffInMonths($to) > 59) {
            $from = $to->copy()->subMonths(59)->startOfMonth();
        }

        // ── bulk queries — one per metric, not one per month ─────────────────

        $connByMonth = ConnectionRequest::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as mk, COUNT(*) as n")
            ->groupBy('mk')->pluck('n', 'mk');

        $connConfByMonth = ConnectionRequest::where('status', 'confirmed')
            ->whereBetween('confirmed_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(confirmed_at,'%Y-%m') as mk, COUNT(*) as n")
            ->groupBy('mk')->pluck('n', 'mk');

        $regByMonth = User::whereIn('role', ['tutor', 'guardian', 'student'])
            ->whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as mk, role, COUNT(*) as n")
            ->groupByRaw('mk, role')
            ->get()->groupBy('mk');

        $jobsByMonth = \App\Models\TuitionJob::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as mk, COUNT(*) as n")
            ->groupBy('mk')->pluck('n', 'mk');

        $jobAppsByMonth = \App\Models\TuitionJobApplication::whereBetween('applied_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(applied_at,'%Y-%m') as mk, COUNT(*) as n")
            ->groupBy('mk')->pluck('n', 'mk');

        $jobConfByMonth = \App\Models\TuitionJobApplication::where('status', 'connected')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(updated_at,'%Y-%m') as mk, COUNT(*) as n")
            ->groupBy('mk')->pluck('n', 'mk');

        // ── build month rows ──────────────────────────────────────────────────
        $cursor = $from->copy();
        $months = collect();
        while ($cursor->lte($to)) {
            $mk  = $cursor->format('Y-m');
            $reg = $regByMonth->get($mk);
            $months->push([
                'month'                => $cursor->format('M Y'),
                'month_key'            => $mk,
                'connections'          => (int) ($connByMonth[$mk] ?? 0),
                'confirmed'            => (int) ($connConfByMonth[$mk] ?? 0),
                'tutors_registered'    => $reg ? (int) $reg->where('role', 'tutor')->sum('n') : 0,
                'guardians_registered' => $reg ? (int) $reg->whereIn('role', ['guardian', 'student'])->sum('n') : 0,
                'jobs_posted'          => (int) ($jobsByMonth[$mk] ?? 0),
                'job_applications'     => (int) ($jobAppsByMonth[$mk] ?? 0),
                'jobs_confirmed'       => (int) ($jobConfByMonth[$mk] ?? 0),
            ]);
            $cursor->addMonth();
        }

        return response()->json(['success' => true, 'data' => [
            'monthly'              => $months,
            'monthly_connections'  => $months, // backward compat

            'connection_statuses'  => ConnectionRequest::selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status'),

            'job_statuses'         => \App\Models\TuitionJob::selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status'),

            'application_statuses' => \App\Models\TuitionJobApplication::selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status'),

            'top_tutors'           => TutorProfile::with('user:id,name')
                ->where('is_verified', true)
                ->orderByDesc('review_count')
                ->take(5)
                ->get(['id', 'user_id', 'rating', 'review_count']),
        ]]);
    }
}
