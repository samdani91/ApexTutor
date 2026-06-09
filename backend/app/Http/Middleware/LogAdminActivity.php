<?php
namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
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
     * The description may contain :id and :name placeholders, or be refined in describe().
     */
    private const DESCRIPTORS = [
        'AdminUserController@store'             => ['create_admin',        'user',           'Created a new admin account'],
        'AdminUserController@update'            => ['update_admin',        'user',           'Updated admin account #:id'],

        'AdminTutorController@update'           => ['update_tutor',        'tutor_profile',  'Edited tutor profile #:id'],
        'AdminTutorController@updateStatus'     => ['update_tutor_status', 'tutor_profile',  'Changed tutor #:id status'],
        'AdminTutorController@uploadDocument'   => ['upload_tutor_document','tutor_profile', 'Uploaded a document for tutor #:id'],
        'AdminTutorController@deleteDocument'   => ['delete_tutor_document','tutor_profile', 'Deleted a document from tutor #:id'],
        'AdminTutorController@updateVideo'      => ['update_tutor_video',  'tutor_profile',  'Updated a teaching video for tutor #:id'],
        'AdminTutorController@deleteVideo'      => ['delete_tutor_video',  'tutor_profile',  'Deleted a teaching video from tutor #:id'],
        'AdminTutorController@reviewVideo'      => ['review_tutor_video',  'tutor_profile',  'Reviewed a teaching video for tutor #:id'],

        'AdminGuardianController@update'        => ['update_guardian',        'guardian_profile', 'Edited guardian profile #:id'],
        'AdminGuardianController@updateStatus'  => ['update_guardian_status', 'guardian_profile', 'Changed guardian #:id account status'],
        'AdminGuardianController@uploadNid'     => ['upload_guardian_nid',    'guardian_profile', 'Uploaded NID document for guardian #:id'],
        'AdminGuardianController@deleteNid'     => ['delete_guardian_nid',    'guardian_profile', 'Deleted NID document for guardian #:id'],

        'AdminUserAvatarController@replace' => ['replace_user_avatar', 'user', 'Replaced avatar for user #:id'],
        'AdminUserAvatarController@remove'  => ['remove_user_avatar',  'user', 'Removed avatar for user #:id'],

        'AdminVerificationController@approve'   => ['approve_verification', 'tutor_profile', 'Approved verification for tutor #:id'],
        'AdminVerificationController@reject'    => ['reject_verification',  'tutor_profile', 'Rejected verification for tutor #:id'],

        'AdminPendingChangesController@approve' => ['approve_pending_changes', 'tutor_profile', 'Approved pending profile changes for tutor #:id'],
        'AdminPendingChangesController@reject'  => ['reject_pending_changes',  'tutor_profile', 'Rejected pending profile changes for tutor #:id'],

        'AdminConnectionController@updateStatus'=> ['update_connection_status', 'connection_request', 'Updated connection #:id status'],
        'AdminConnectionController@addNotes'    => ['add_connection_notes',     'connection_request', 'Added notes to connection #:id'],

        'AdminReviewController@approve'         => ['approve_review', 'review', 'Approved review #:id'],
        'AdminReviewController@reject'          => ['reject_review',  'review', 'Rejected review #:id'],

        'AdminReferenceDataController@storeSubject'    => ['create_subject',  'subject',  'Created a subject'],
        'AdminReferenceDataController@updateSubject'   => ['update_subject',  'subject',  'Updated subject #:id'],
        'AdminReferenceDataController@destroySubject'  => ['delete_subject',  'subject',  'Deleted subject #:id'],
        'AdminReferenceDataController@storeDistrict'   => ['create_district', 'district', 'Created a district'],
        'AdminReferenceDataController@updateDistrict'  => ['update_district', 'district', 'Updated district #:id'],
        'AdminReferenceDataController@destroyDistrict' => ['delete_district', 'district', 'Deleted district #:id'],
        'AdminReferenceDataController@storeArea'       => ['create_area',     'area',     'Created an area'],
        'AdminReferenceDataController@updateArea'      => ['update_area',     'area',     'Updated area #:id'],
        'AdminReferenceDataController@destroyArea'     => ['delete_area',     'area',     'Deleted area #:id'],

        'AdminTicketController@updateStatus'           => ['update_ticket_status', 'support_ticket', 'Updated ticket #:id status'],
        'AdminTicketController@reply'                  => ['reply_ticket',         'support_ticket', 'Replied to ticket #:id'],
        'AdminTicketController@claim'                  => ['claim_ticket',         'support_ticket', 'Claimed ticket #:id'],
        'AdminTicketController@unclaim'                => ['unclaim_ticket',       'support_ticket', 'Unclaimed ticket #:id'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->record($request, $response);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        // Only successful write requests
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

        // Only log mapped actions — keeps noise (e.g. notification read-marking) out
        return isset(self::DESCRIPTORS[$key]);
    }

    private function record(Request $request, Response $response): void
    {
        try {
            $key = $this->routeKey($request);
            [$action, $targetType, $template] = self::DESCRIPTORS[$key];

            $targetId    = $this->resolveTargetId($request, $response);
            $description = $this->describe($key, $template, $targetId, $request);

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
     * Enrich the static template with dynamic request context where useful.
     */
    private function describe(string $key, string $template, int $targetId, Request $request): string
    {
        $base = str_replace(':id', (string) $targetId, $template);

        return match ($key) {
            'AdminTutorController@updateStatus'      => $base . ': ' . $request->input('status', ''),
            'AdminGuardianController@updateStatus'   => $base . ': ' . ($request->boolean('is_active') ? 'activated' : 'suspended'),
            'AdminConnectionController@updateStatus' => $base . ': ' . $request->input('status', ''),
            'AdminVerificationController@reject'     => $base . ($request->filled('rejection_reason') ? ': ' . $request->input('rejection_reason') : ''),
            'AdminPendingChangesController@reject'   => $base . ($request->filled('note') ? ': ' . $request->input('note') : ''),
            'AdminReviewController@reject'           => $base . ($request->filled('moderation_note') ? ': ' . $request->input('moderation_note') : ''),
            default                                  => $base,
        };
    }
}
