<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionJobApplication extends Model
{
    const CREATED_AT = 'applied_at';
    const UPDATED_AT = 'updated_at';

    // Human-readable status names for anything user-facing (audit log
    // descriptions, messages) — raw enum values must not reach the screen.
    public const STATUS_LABELS = [
        'applied'           => 'Applied',
        'shortlisted'       => 'Shortlisted',
        'demo_requested'    => 'Demo Requested',
        'appointed'         => 'Appointed',
        'confirm_requested' => 'Confirm Requested',
        'connected'         => 'Confirmed',
        'not_selected'      => 'Not Selected',
    ];

    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    protected $fillable = ['tuition_job_id', 'tutor_profile_id', 'status', 'status_before_confirm'];

    protected $casts = ['applied_at' => 'datetime'];

    public function tuitionJob(): BelongsTo { return $this->belongsTo(TuitionJob::class); }
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
