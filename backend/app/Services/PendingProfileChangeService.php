<?php
namespace App\Services;

use App\Models\TutorProfile;
use App\Models\User;
use App\Notifications\AdminPendingProfileChangeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Centralises all "merge into pending_changes" logic so every tutor controller
 * calls a single well-typed method instead of copy-pasting the JSON merge pattern.
 */
class PendingProfileChangeService
{
    public function requiresPendingFlow(TutorProfile $profile): bool
    {
        return (bool) $profile->is_verified;
    }

    // ── Simple section replacement ────────────────────────────────────────────

    /** Replace an entire section (preferences, emergency_contact, etc.) */
    public function merge(TutorProfile $profile, string $section, mixed $data): void
    {
        $pending = $profile->pending_changes ?? [];
        $pending[$section] = $data;
        $this->save($profile, $pending);
    }

    /**
     * Merge multiple top-level fields in a single DB write.
     * Used for bio/status which are not nested under a section key.
     */
    public function mergeTopLevel(TutorProfile $profile, array $fields): void
    {
        $pending = $profile->pending_changes ?? [];
        foreach ($fields as $key => $value) {
            $pending[$key] = $value;
        }
        $this->save($profile, $pending);
    }

    /** Shallow-merge new keys into an existing section (personal_info partial updates) */
    public function shallowMerge(TutorProfile $profile, string $section, array $data): void
    {
        $pending = $profile->pending_changes ?? [];
        $pending[$section] = array_merge($pending[$section] ?? [], $data);
        $this->save($profile, $pending);
    }

    // ── Education changes (create / update / delete actions) ─────────────────

    public function queueEducationChange(
        TutorProfile $profile,
        string       $action,
        ?int         $id,
        array        $data
    ): void {
        $pending = $profile->pending_changes ?? [];
        $pending['education']['changes'] ??= [];

        $key = $id ? "existing:{$id}" : 'new:' . uniqid('', true);

        // If we're deleting an entry that was previously queued as a create/update,
        // remove those earlier changes to avoid phantom entries.
        if ($action === 'delete' && $id) {
            foreach ($pending['education']['changes'] as $k => $change) {
                if ($k !== $key && ($change['id'] ?? null) === $id) {
                    unset($pending['education']['changes'][$k]);
                }
            }
        }

        $pending['education']['changes'][$key] = compact('action', 'id', 'data');
        $this->save($profile, $pending);
    }

    // ── Document staging ──────────────────────────────────────────────────────

    public function stageDocumentUpsert(TutorProfile $profile, string $type, array $payload): void
    {
        $pending = $profile->pending_changes ?? [];
        $pending['documents']['upsert'][$type] = $payload;
        $this->save($profile, $pending);
    }

    public function stageDocumentDelete(TutorProfile $profile, int $docId): void
    {
        $pending = $profile->pending_changes ?? [];
        $ids = $pending['documents']['delete'] ?? [];
        $ids[] = $docId;
        $pending['documents']['delete'] = array_values(array_unique($ids));
        $this->save($profile, $pending);
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    private function save(TutorProfile $profile, array $pending): void
    {
        // Only notify on the first submission in a new review cycle.
        // If pending_changes is already set the admin was already notified.
        $isNewSubmission = is_null($profile->pending_changes);

        $pending['submitted_at'] = now()->toISOString();
        $profile->update(['pending_changes' => $pending, 'pending_note' => null]);

        if ($isNewSubmission) {
            try {
                $admins    = User::where('role', 'super_admin')->get();
                $tutorName = $profile->user->name ?? 'A tutor';
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new AdminPendingProfileChangeNotification($tutorName, $profile->id));
                }
            } catch (\Exception $e) {
                Log::error('Admin pending-change notification failed', ['error' => $e->getMessage(), 'profile' => $profile->id]);
            }
        }
    }
}
