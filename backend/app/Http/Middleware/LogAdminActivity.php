<?php
namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use App\Models\Area;
use App\Models\ConnectionRequest;
use App\Models\District;
use App\Models\GuardianProfile;
use App\Models\PlatformFeedback;
use App\Models\Review;
use App\Models\Subject;
use App\Models\SupportTicket;
use App\Models\TutorProfile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Automatically records every successful admin write action (POST/PUT/PATCH/DELETE)
 * into the admin_activity_logs table.
 *
 * This is the single source of truth for the audit log — controllers no longer
 * need to call logActivity() manually. Any new admin write endpoint is captured
 * automatically; add an entry to self::DESCRIPTORS to give it a friendly label.
 */
class LogAdminActivity
{
    /**
     * Map of "ControllerShortName@method" → [action slug, target type, description template].
     * The description may contain :label which is replaced with a human-readable identifier.
     */
    private const DESCRIPTORS = [
        'AdminUserController@store'             => ['create_admin',        'user',           'Created a new admin account: :label'],
        'AdminUserController@update'            => ['update_admin',        'user',           'Updated admin account: :label'],

        'AdminTutorController@update'           => ['update_tutor',        'tutor_profile',  'Edited tutor profile: :label'],
        'AdminTutorController@updateStatus'     => ['update_tutor_status', 'tutor_profile',  'Changed tutor status: :label'],
        'AdminTutorController@uploadDocument'   => ['upload_tutor_document','tutor_profile', 'Uploaded a document for tutor :label'],
        'AdminTutorController@deleteDocument'   => ['delete_tutor_document','tutor_profile', 'Deleted a document from tutor :label'],
        'AdminTutorController@updateVideo'      => ['update_tutor_video',  'tutor_profile',  'Updated a teaching video for tutor :label'],
        'AdminTutorController@deleteVideo'      => ['delete_tutor_video',  'tutor_profile',  'Deleted a teaching video from tutor :label'],
        'AdminTutorController@reviewVideo'      => ['review_tutor_video',  'tutor_profile',  'Reviewed a teaching video for tutor :label'],

        'AdminGuardianController@update'        => ['update_guardian',        'guardian_profile', 'Edited guardian profile: :label'],
        'AdminGuardianController@updateStatus'  => ['update_guardian_status', 'guardian_profile', 'Changed guardian account status: :label'],
        'AdminGuardianController@uploadNid'     => ['upload_guardian_nid',    'guardian_profile', 'Uploaded NID document for guardian :label'],
        'AdminGuardianController@deleteNid'     => ['delete_guardian_nid',    'guardian_profile', 'Deleted NID document for guardian :label'],

        'AdminUserAvatarController@replace' => ['replace_user_avatar', 'user', 'Replaced avatar for :label'],
        'AdminUserAvatarController@remove'  => ['remove_user_avatar',  'user', 'Removed avatar for :label'],

        'AdminVerificationController@approve'   => ['approve_verification', 'tutor_profile', 'Approved verification for tutor :label'],
        'AdminVerificationController@reject'    => ['reject_verification',  'tutor_profile', 'Rejected verification for tutor :label'],

        'AdminPendingChangesController@approve' => ['approve_pending_changes', 'tutor_profile', 'Approved pending profile changes for tutor :label'],
        'AdminPendingChangesController@reject'  => ['reject_pending_changes',  'tutor_profile', 'Rejected pending profile changes for tutor :label'],

        'AdminConnectionController@updateStatus'=> ['update_connection_status', 'connection_request', 'Updated connection status: :label'],
        'AdminConnectionController@addNotes'    => ['add_connection_notes',     'connection_request', 'Added notes to connection :label'],

        'AdminReviewController@approve'         => ['approve_review', 'review', 'Approved review :label'],
        'AdminReviewController@reject'          => ['reject_review',  'review', 'Rejected review :label'],

        'AdminPlatformFeedbackController@approve' => ['approve_feedback', 'platform_feedback', 'Approved platform feedback from :label'],
        'AdminPlatformFeedbackController@reject'  => ['reject_feedback',  'platform_feedback', 'Rejected platform feedback from :label'],

        'AdminReferenceDataController@storeSubject'    => ['create_subject',  'subject',  'Created subject: :label'],
        'AdminReferenceDataController@updateSubject'   => ['update_subject',  'subject',  'Updated subject: :label'],
        'AdminReferenceDataController@destroySubject'  => ['delete_subject',  'subject',  'Deleted subject: :label'],
        'AdminReferenceDataController@storeDistrict'   => ['create_district', 'district', 'Created district: :label'],
        'AdminReferenceDataController@updateDistrict'  => ['update_district', 'district', 'Updated district: :label'],
        'AdminReferenceDataController@destroyDistrict' => ['delete_district', 'district', 'Deleted district: :label'],
        'AdminReferenceDataController@storeArea'       => ['create_area',     'area',     'Created area: :label'],
        'AdminReferenceDataController@updateArea'      => ['update_area',     'area',     'Updated area: :label'],
        'AdminReferenceDataController@destroyArea'     => ['delete_area',     'area',     'Deleted area: :label'],

        'AdminTicketController@updateStatus'           => ['update_ticket_status', 'support_ticket', 'Updated ticket status: :label'],
        'AdminTicketController@reply'                  => ['reply_ticket',         'support_ticket', 'Replied to ticket :label'],
        'AdminTicketController@claim'                  => ['claim_ticket',         'support_ticket', 'Claimed ticket :label'],
        'AdminTicketController@unclaim'                => ['unclaim_ticket',       'support_ticket', 'Unclaimed ticket :label'],

        'AdminSmsController@send'      => ['send_sms',       'user', 'Sent SMS to :label'],
        'AdminSmsController@broadcast' => ['broadcast_sms',  'user', 'Sent broadcast SMS to :label'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Reference-data hard-deletes remove the record before we can look it up,
        // so we must fetch the name before the controller runs.
        $preLabel = $this->prefetchDeleteLabel($request);

        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->record($request, $response, $preLabel);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }
        if ($response->getStatusCode() >= 400) {
            return false;
        }
        if (!$request->user()) {
            return false;
        }

        $key = $this->routeKey($request);

        return isset(self::DESCRIPTORS[$key]);
    }

    private function record(Request $request, Response $response, ?string $preLabel): void
    {
        try {
            $key = $this->routeKey($request);
            [$action, $targetType, $template] = self::DESCRIPTORS[$key];

            $targetId    = $this->resolveTargetId($request, $response);
            $description = $this->describe($key, $template, $targetId, $request, $response, $preLabel);

            AdminActivityLog::create([
                'admin_id'    => $request->user()->id,
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'description' => $description,
                'ip_address'  => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the actual request
            Log::error('Admin activity logging failed', ['error' => $e->getMessage(), 'url' => $request->fullUrl()]);
        }
    }

    private function routeKey(Request $request): string
    {
        $action = $request->route()?->getActionName() ?? '';
        // "App\Http\Controllers\Admin\AdminTutorController@update" → "AdminTutorController@update"
        $action = ltrim(strrchr($action, '\\') ?: $action, '\\');
        return $action;
    }

    private function resolveTargetId(Request $request, Response $response): int
    {
        // Numeric route param 'id' covers most actions
        $id = $request->route('id');
        if ($id !== null) {
            return (int) $id;
        }

        // Named public-id params (string slugs like TUT-123456 / GRD-123456)
        // — pull the numeric DB id from the JSON response body when possible
        foreach (['tutorId', 'guardianId', 'videoId'] as $param) {
            if ($request->route($param) !== null) {
                $payload = json_decode($response->getContent(), true);
                return (int) ($payload['data']['id'] ?? $payload['data']['data']['id'] ?? 0);
            }
        }

        // Create actions — pull the new record's id from the JSON response
        $payload = json_decode($response->getContent(), true);
        return (int) ($payload['data']['id'] ?? $payload['data']['data']['id'] ?? 0);
    }

    /**
     * For hard-delete actions, load the name before the controller removes the record.
     */
    private function prefetchDeleteLabel(Request $request): ?string
    {
        if ($request->method() !== 'DELETE') {
            return null;
        }
        $key = $this->routeKey($request);
        if (!isset(self::DESCRIPTORS[$key])) {
            return null;
        }
        $id = (int) $request->route('id');
        if (!$id) {
            return null;
        }

        return match ($key) {
            'AdminReferenceDataController@destroySubject'  => Subject::find($id)?->name,
            'AdminReferenceDataController@destroyDistrict' => District::find($id)?->name,
            'AdminReferenceDataController@destroyArea'     => Area::find($id)?->name,
            default                                        => null,
        };
    }

    /**
     * Build a human-readable description by substituting :label in the template.
     */
    private function describe(
        string $key,
        string $template,
        int $targetId,
        Request $request,
        Response $response,
        ?string $preLabel
    ): string {
        $label = $preLabel ?? $this->resolveLabel($key, $request, $response, $targetId);
        $base  = str_replace(':label', $label, $template);

        return match ($key) {
            'AdminTutorController@updateStatus'      => $base . ': ' . $request->input('status', ''),
            'AdminGuardianController@updateStatus'   => $base . ': ' . ($request->boolean('is_active') ? 'activated' : 'suspended'),
            'AdminConnectionController@updateStatus' => $base . ': ' . $request->input('status', ''),
            'AdminVerificationController@reject'     => $base . ($request->filled('rejection_reason') ? ' — ' . $request->input('rejection_reason') : ''),
            'AdminPendingChangesController@reject'   => $base . ($request->filled('note') ? ' — ' . $request->input('note') : ''),
            'AdminReviewController@reject'           => $base . ($request->filled('moderation_note') ? ' — ' . $request->input('moderation_note') : ''),
            'AdminSmsController@send'                => $base . ' — ' . mb_substr($request->input('message', ''), 0, 60),
            'AdminSmsController@broadcast'           => $base . ' — ' . mb_substr($request->input('message', ''), 0, 60),
            default                                  => $base,
        };
    }

    /**
     * Resolve a human-readable label for the affected resource.
     * Returns an empty string when no label can be determined — the template still reads naturally.
     */
    private function resolveLabel(string $key, Request $request, Response $response, int $targetId): string
    {
        // Tutor routes carry {tutorId} = "TUT-123456" directly in the URL
        $tutorRouteId = $request->route('tutorId');
        if ($tutorRouteId !== null) {
            $profile = TutorProfile::with('user:id,name')->where('tutor_id', $tutorRouteId)->first();
            $name    = $profile?->user?->name ?? '';
            return $name ? "{$name} ({$tutorRouteId})" : $tutorRouteId;
        }

        // Guardian routes carry {guardianId} = "GRD-123456" directly in the URL
        $guardianRouteId = $request->route('guardianId');
        if ($guardianRouteId !== null) {
            $profile = GuardianProfile::with('user:id,name')->where('guardian_id', $guardianRouteId)->first();
            $name    = $profile?->user?->name ?? '';
            return $name ? "{$name} ({$guardianRouteId})" : $guardianRouteId;
        }

        // Verification & PendingChanges — {id} is the tutor_profile primary key
        if (str_starts_with($key, 'AdminVerification') || str_starts_with($key, 'AdminPendingChanges')) {
            $profile = TutorProfile::with('user:id,name')->find($targetId);
            if ($profile) {
                $name = $profile->user?->name ?? '';
                $tid  = $profile->tutor_id   ?? '';
                if ($name && $tid) return "{$name} ({$tid})";
                return $name ?: $tid;
            }
            return '';
        }

        // Connection actions — {id} is the connection_request primary key
        if (str_starts_with($key, 'AdminConnection')) {
            $conn = ConnectionRequest::with([
                'tutorProfile.user:id,name',
                'guardianProfile.user:id,name',
            ])->find($targetId);
            if ($conn) {
                $tutor    = $conn->tutorProfile?->user?->name   ?? 'Tutor';
                $guardian = $conn->guardianProfile?->user?->name ?? 'Guardian';
                return "{$tutor} — {$guardian}";
            }
            return '';
        }

        // Review actions — {id} is the review primary key
        if (str_starts_with($key, 'AdminReview')) {
            $review = Review::with([
                'tutorProfile.user:id,name',
                'guardianProfile.user:id,name',
            ])->find($targetId);
            if ($review) {
                $by  = $review->guardianProfile?->user?->name ?? 'Guardian';
                $for = $review->tutorProfile?->user?->name    ?? 'Tutor';
                return "by {$by} for {$for}";
            }
            return '';
        }

        // Ticket actions — {id} is the support_ticket primary key
        if (str_starts_with($key, 'AdminTicket')) {
            $ticket = SupportTicket::find($targetId);
            if ($ticket) {
                $ref     = $ticket->ticket_number ?? '';
                $subject = $ticket->subject ? mb_substr($ticket->subject, 0, 50) : '';
                if ($ref && $subject) return "{$ref} — {$subject}";
                return $ref ?: $subject;
            }
            return '';
        }

        // Platform feedback — {id} is the feedback primary key
        if (str_starts_with($key, 'AdminPlatformFeedback')) {
            $feedback = PlatformFeedback::with('user:id,name')->find($targetId);
            return $feedback?->user?->name ?? '';
        }

        // Admin user management — {id} is the user primary key (or new user from response for store)
        if (str_starts_with($key, 'AdminUserController')) {
            $user = User::find($targetId);
            return $user?->name ?? '';
        }

        // Avatar actions — {id} is the user primary key
        if (str_starts_with($key, 'AdminUserAvatar')) {
            $user = User::find($targetId);
            return $user?->name ?? '';
        }

        // Reference data — name is in the request body for creates and updates;
        // deletes are handled before the controller runs via prefetchDeleteLabel()
        if (str_starts_with($key, 'AdminReferenceData')) {
            return (string) $request->input('name', '');
        }

        // SMS single send — user_id in request body
        if ($key === 'AdminSmsController@send') {
            $userId = (int) $request->input('user_id');
            $user   = $userId ? User::find($userId) : null;
            if ($user) {
                $pid = TutorProfile::where('user_id', $userId)->value('tutor_id')
                    ?? GuardianProfile::where('user_id', $userId)->value('guardian_id');
                return $pid ? "{$user->name} ({$pid})" : $user->name;
            }
            return '';
        }

        // SMS broadcast — recipient count from response body
        if ($key === 'AdminSmsController@broadcast') {
            $payload = json_decode($response->getContent(), true);
            $count   = (int) ($payload['data']['id'] ?? 0);
            return $count ? "{$count} users" : 'all users';
        }

        return '';
    }
}
