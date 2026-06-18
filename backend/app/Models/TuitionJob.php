<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuitionJob extends Model
{
    protected $fillable = [
        'public_id', 'guardian_profile_id', 'title', 'tuition_type', 'medium', 'tutoring_style',
        'district_id', 'area_id', 'address_details', 'class_level',
        'student_gender', 'num_students', 'tutor_gender_pref', 'offered_salary',
        'hire_date', 'tutoring_time', 'tutoring_days_per_week',
        'student_institute', 'extra_requirements', 'status',
    ];

    protected $casts = [
        'hire_date'              => 'date',
        'num_students'           => 'integer',
        'offered_salary'         => 'integer',
        'tutoring_days_per_week' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (TuitionJob $job) {
            if (!$job->public_id) {
                do {
                    $id = (string) random_int(100000, 999999);
                } while (self::where('public_id', $id)->exists());
                $job->public_id = $id;
            }
        });
    }

    public function guardianProfile(): BelongsTo { return $this->belongsTo(GuardianProfile::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'tuition_job_subjects'); }
    public function applications(): HasMany { return $this->hasMany(TuitionJobApplication::class); }

    public static function buildTitle(string $classLevel, array $subjectNames, string $tuitionType, ?int $daysPerWeek = null): string
    {
        $classLabel  = self::classLabel($classLevel);
        $subjectPart = !empty($subjectNames)
            ? implode(' & ', array_slice($subjectNames, 0, 2)) . ' '
            : '';
        $daysPart = $daysPerWeek ? " - {$daysPerWeek} Days/Week" : '';
        return "Need {$subjectPart}Tutor For {$classLabel} Student{$daysPart}";
    }

    public static function classLabel(string $level): string
    {
        return match ($level) {
            'class_1'        => 'Class 1',
            'class_2'        => 'Class 2',
            'class_3'        => 'Class 3',
            'class_4'        => 'Class 4',
            'class_5'        => 'Class 5',
            'class_6'        => 'Class 6',
            'class_7'        => 'Class 7',
            'class_8'        => 'Class 8',
            'class_9'        => 'Class 9',
            'class_10'       => 'Class 10',
            'ssc'            => 'SSC',
            'hsc'            => 'HSC',
            'o_level'        => 'O Level',
            'a_level'        => 'A Level',
            'admission_test' => 'Admission Test',
            default          => ucfirst(str_replace('_', ' ', $level)),
        };
    }
}
