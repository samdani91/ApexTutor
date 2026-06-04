<?php
namespace App\Http\Resources;

use App\DTO\PendingChangesSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes the admin pending-changes review payload.
 *
 * $this->resource is the array built by PendingProfileChangePresenter::present(),
 * which has keys: id, tutor_id, user, submitted_at, pending, live.
 */
class PendingProfileChangeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $schema = PendingChangesSchema::from($this->resource['pending'] ?? []);

        return [
            'id'           => $this->resource['id'],
            'tutor_id'     => $this->resource['tutor_id'],
            'user'         => $this->resource['user'],
            'submitted_at' => $this->resource['submitted_at'],

            // Typed pending sections — frontend can rely on these keys always existing
            'pending' => [
                'bio'               => $schema->bio,
                'status'            => $schema->status,
                'submitted_at'      => $schema->submittedAt,
                'preferences'       => $schema->preferences,
                'personal_info'     => $schema->personalInfo,
                'emergency_contact' => $schema->emergencyContact,
                'education'         => $schema->education,
                'documents'         => $schema->documents,
            ],

            'live' => $this->resource['live'],
        ];
    }
}
