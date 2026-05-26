<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TuitionRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'guardian_profile_id','student_name','medium','class_level','district_id','city','area',
        'preferred_tutor_gender','days_per_week','preferred_days','hours_per_day',
        'preferred_time_from','preferred_time_to','salary_min','salary_max',
        'place_of_tutoring','special_notes','status',
    ];
    protected $casts = ['preferred_days' => 'array', 'place_of_tutoring' => 'array'];
    public function guardianProfile(): BelongsTo { return $this->belongsTo(GuardianProfile::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'tuition_requirement_subjects'); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
}
