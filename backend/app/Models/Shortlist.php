<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shortlist extends Model
{
    public $timestamps = false;
    protected $fillable = ['guardian_profile_id','tutor_profile_id'];
    public function guardianProfile(): BelongsTo { return $this->belongsTo(GuardianProfile::class); }
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
