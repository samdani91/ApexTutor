<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionJobApplication extends Model
{
    const CREATED_AT = 'applied_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = ['tuition_job_id', 'tutor_profile_id', 'status'];

    protected $casts = ['applied_at' => 'datetime'];

    public function tuitionJob(): BelongsTo { return $this->belongsTo(TuitionJob::class); }
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
