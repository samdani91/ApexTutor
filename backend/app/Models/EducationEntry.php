<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_profile_id','level','university_id','institute_name','degree_title','major_group',
        'id_card_number','result','curriculum','from_date','to_date',
        'year_of_passing','is_current','sort_order',
    ];
    protected $casts = ['is_current' => 'boolean', 'from_date' => 'date', 'to_date' => 'date'];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
    public function university(): BelongsTo  { return $this->belongsTo(University::class); }
}
