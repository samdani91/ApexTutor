<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_profile_id','guardian_profile_id','connection_request_id',
        'rating','review_text','moderation_status','moderation_note',
        'moderated_by','moderated_at',
    ];
    protected $casts = ['moderated_at' => 'datetime'];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
    public function guardianProfile(): BelongsTo { return $this->belongsTo(GuardianProfile::class); }
    public function connectionRequest(): BelongsTo { return $this->belongsTo(ConnectionRequest::class, 'connection_request_id'); }
}
