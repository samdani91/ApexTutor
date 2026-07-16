<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Shapes the public-facing tutor profile (search results + profile page).
 * Strips internal fields (pending_changes, pending_note, rejection_reason, etc.)
 * and ensures consistent structure regardless of which relations are loaded.
 */
class PublicTutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'public_id'  => $this->public_id,
            'tutor_id'   => $this->tutor_id,
            'is_verified'=> $this->is_verified,
            'status'     => $this->status,
            'bio'        => $this->bio,
            'rating'     => $this->rating ? round((float) $this->rating, 2) : null,
            'review_count'      => $this->review_count,
            'profile_view_count'=> $this->profile_view_count,

            'user' => $this->when($this->relationLoaded('user'), fn() => [
                'id'         => $this->user->id,
                'name'       => $this->user->name,
                'avatar_url' => $this->user->avatar_url,
            ]),

            'tuition_preference' => $this->when(
                $this->relationLoaded('tuitionPreference') && $this->tuitionPreference,
                fn() => [
                    'district'          => $this->tuitionPreference->district,
                    'subjects'          => $this->tuitionPreference->subjects ?? [],
                    'locations'         => $this->tuitionPreference->locations ?? [],
                    'days'              => $this->tuitionPreference->days ?? [],
                    'expected_salary_min'      => $this->tuitionPreference->expected_salary_min,
                    'expected_salary_max'      => $this->tuitionPreference->expected_salary_max,
                    'total_experience_years'   => $this->tuitionPreference->total_experience_years,
                    'days_per_week'            => $this->tuitionPreference->days_per_week,
                    'hours_per_day'            => $this->tuitionPreference->hours_per_day,
                    'preferred_classes'        => $this->tuitionPreference->preferred_classes,
                    'preferred_time'           => $this->tuitionPreference->preferred_time,
                    'place_of_tutoring'        => $this->tuitionPreference->place_of_tutoring,
                    'tutoring_methods'         => $this->tuitionPreference->tutoring_methods,
                    'tutoring_styles'          => $this->tuitionPreference->tutoring_styles,
                    'preferred_curricula'      => $this->tuitionPreference->preferred_curricula,
                    'preferred_groups'         => $this->tuitionPreference->preferred_groups,
                    'experience_details'       => $this->tuitionPreference->experience_details,
                    'tutoring_method_description' => $this->tuitionPreference->tutoring_method_description,
                ]
            ),

            'education' => $this->when(
                $this->relationLoaded('educationEntries'),
                fn() => $this->educationEntries
            ),

            'documents' => $this->when(
                $this->relationLoaded('documents'),
                fn() => TutorDocumentResource::collection($this->documents)
            ),

            'teaching_video' => $this->when(
                $this->relationLoaded('teachingVideo'),
                fn() => $this->teachingVideo
            ),

            'travel_availabilities' => $this->when(
                $this->relationLoaded('travelAvailabilities'),
                fn() => $this->travelAvailabilities
            ),

            'reviews' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => $this->reviews
            ),
        ];
    }
}
