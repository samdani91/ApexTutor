<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorPersonalInfo extends Model
{
    use HasFactory;
    protected $table = 'tutor_personal_infos';
    protected $fillable = [
        'tutor_profile_id','additional_phone','present_address','permanent_address',
        'gender','date_of_birth','religion','national_id','nationality',
        'facebook_url','linkedin_url','fathers_name','fathers_phone','mothers_name','mothers_phone',
    ];
    protected $casts = ['date_of_birth' => 'date'];
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
}
