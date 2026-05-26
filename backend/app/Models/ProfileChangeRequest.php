<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileChangeRequest extends Model
{
    protected $fillable = ['tutor_profile_id', 'reason', 'status', 'admin_note', 'reviewed_by', 'reviewed_at'];
    protected $casts = ['reviewed_at' => 'datetime'];

    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
