<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelAvailability extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_profile_id','district_id','from_date','to_date',
        'open_to_tuitions','notes','is_expired',
    ];
    protected $casts = [
        'from_date' => 'date', 'to_date' => 'date',
        'open_to_tuitions' => 'boolean', 'is_expired' => 'boolean',
    ];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
}
