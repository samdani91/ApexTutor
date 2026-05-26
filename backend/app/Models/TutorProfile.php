<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id','tutor_id','public_id','profile_completion_percent','is_verified','verification_status',
        'verified_at','verified_by','rejection_reason','status','rating',
        'review_count','profile_view_count','bio',
    ];

    protected static function booted(): void
    {
        static::creating(function (TutorProfile $profile) {
            if (!$profile->public_id) {
                $profile->public_id = \Illuminate\Support\Str::uuid()->toString();
            }
        });

        static::created(function (TutorProfile $profile) {
            $profile->updateQuietly([
                'tutor_id' => 'TUT-' . str_pad($profile->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'rating' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function educationEntries(): HasMany { return $this->hasMany(EducationEntry::class)->orderBy('sort_order'); }
    public function tuitionPreference(): HasOne { return $this->hasOne(TuitionPreference::class); }
    public function personalInfo(): HasOne { return $this->hasOne(TutorPersonalInfo::class); }
    public function emergencyContact(): HasOne { return $this->hasOne(TutorEmergencyContact::class); }
    public function documents(): HasMany { return $this->hasMany(TutorDocument::class); }
    public function teachingVideo(): HasOne { return $this->hasOne(TeachingVideo::class)->latestOfMany(); }
    public function teachingVideos(): HasMany { return $this->hasMany(TeachingVideo::class)->latest(); }
    public function travelAvailabilities(): HasMany { return $this->hasMany(TravelAvailability::class); }
    public function activeTravelAvailabilities(): HasMany {
        return $this->hasMany(TravelAvailability::class)
            ->where('open_to_tuitions', true)
            ->where('from_date', '<=', now())
            ->where('to_date', '>=', now());
    }
    public function reviews(): HasMany {
        return $this->hasMany(Review::class)->where('moderation_status', 'approved');
    }
    public function connectionRequests(): HasMany { return $this->hasMany(ConnectionRequest::class); }
}
