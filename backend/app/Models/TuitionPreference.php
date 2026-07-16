<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuitionPreference extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_profile_id','tutoring_methods','place_of_tutoring','tutoring_styles',
        'preferred_curricula','preferred_classes','preferred_groups','district_id',
        'expected_salary_min','expected_salary_max','total_experience_years','experience_details',
        'days_per_week','hours_per_day','preferred_time','tutoring_method_description',
    ];
    protected $casts = [
        'tutoring_methods' => 'array',
        'place_of_tutoring' => 'array',
        'tutoring_styles' => 'array',
        'preferred_curricula' => 'array',
        'preferred_classes' => 'array',
        'preferred_groups' => 'array',
        'hours_per_day'  => 'float',
        'preferred_time' => 'array',
    ];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
    public function days(): HasMany { return $this->hasMany(TuitionPreferenceDay::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'tuition_preference_subjects'); }
    public function locations(): HasMany { return $this->hasMany(TuitionPreferenceLocation::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
}
