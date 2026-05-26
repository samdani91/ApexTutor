<?php
namespace App\Http\Middleware;

use App\Models\ProfileChangeRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow reads, change-request CRUD, and video/document uploads
        if ($request->isMethod('GET')
            || $request->is('api/tutor/change-request*')
            || $request->is('api/tutor/videos*')
            || $request->is('api/tutor/documents*')
        ) {
            return $next($request);
        }

        $profile = $request->user()?->tutorProfile;
        if (!$profile) {
            return $next($request);
        }

        // Locked: profile is verified
        if ($profile->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Your profile is verified and locked. Submit a change request to make edits.',
                'locked'  => true,
            ], 403);
        }

        // Locked: tutor clicked "Done editing" — admin is reviewing
        $underReview = ProfileChangeRequest::where('tutor_profile_id', $profile->id)
            ->where('status', 'review_pending')
            ->exists();

        if ($underReview) {
            return response()->json([
                'success' => false,
                'message' => 'Your profile is submitted for re-review. You cannot make further edits until the admin approves.',
                'locked'  => true,
            ], 403);
        }

        return $next($request);
    }
}
