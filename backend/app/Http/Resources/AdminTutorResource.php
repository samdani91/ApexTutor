<?php
namespace App\Http\Resources;

use App\DTO\PendingChangesSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin-facing tutor detail view.
 * Includes internal fields (pending_changes, verification_status, etc.)
 * that are not appropriate for the public profile.
 */
class AdminTutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schema = PendingChangesSchema::from($this->pending_changes);

        return [
            'id'                       => $this->id,
            'public_id'                => $this->public_id,
            'tutor_id'                 => $this->tutor_id,
            'is_verified'              => $this->is_verified,
            'verification_status'      => $this->verification_status,
            'status'                   => $this->status,
            'bio'                      => $this->bio,
            'rating'                   => $this->rating,
            'review_count'             => $this->review_count,
            'profile_completion_percent' => $this->profile_completion_percent,
            'profile_view_count'       => $this->profile_view_count,
            'verified_at'              => $this->verified_at,
            'rejection_reason'         => $this->rejection_reason,
            'pending_note'             => $this->pending_note,
            'has_pending_changes'      => !$schema->isEmpty(),

            // Pending changes typed via schema DTO
            'pending_changes' => $this->when(!$schema->isEmpty(), fn() => [
                'bio'               => $schema->bio,
                'status'            => $schema->status,
                'submitted_at'      => $schema->submittedAt,
                'avatar'            => $schema->avatar,
                'preferences'       => $schema->preferences,
                'personal_info'     => $schema->personalInfo,
                'emergency_contact' => $schema->emergencyContact,
                'education'         => $schema->education,
                'documents'         => $schema->documents,
            ]),

            'user' => $this->when($this->relationLoaded('user'), fn() => $this->user),

            'tuition_preference' => $this->when(
                $this->relationLoaded('tuitionPreference'),
                fn() => $this->tuitionPreference
            ),

            'education' => $this->when(
                $this->relationLoaded('educationEntries'),
                fn() => $this->educationEntries
            ),

            'documents' => $this->when(
                $this->relationLoaded('documents'),
                fn() => TutorDocumentResource::collection($this->documents)
            ),

            'personal_info' => $this->when(
                $this->relationLoaded('personalInfo'),
                fn() => $this->personalInfo
            ),

            'emergency_contact' => $this->when(
                $this->relationLoaded('emergencyContact'),
                fn() => $this->emergencyContact
            ),
        ];
    }
}
