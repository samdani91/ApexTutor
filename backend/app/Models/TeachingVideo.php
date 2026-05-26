<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingVideo extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_profile_id','title','subject','class_level','medium',
        'file_path','thumbnail_path','duration_seconds',
        'file_size','review_status','review_note','reviewed_by','reviewed_at',
    ];
    protected $casts = ['reviewed_at' => 'datetime'];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
