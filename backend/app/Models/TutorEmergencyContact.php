<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorEmergencyContact extends Model
{
    use HasFactory;
    protected $fillable = ['tutor_profile_id','name','relation','phone','address'];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
