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

            // Only include keys that are actually set — null keys must be absent so
            // buildDiff() can use `=== undefined` to detect "not pending" correctly.
            'pending' => $schema->toArray(),

            'live' => $this->resource['live'],
        ];
    }
}
