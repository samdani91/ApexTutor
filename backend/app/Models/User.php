<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'pending_name', 'email', 'pending_email', 'pending_email_token', 'phone', 'address', 'password', 'role', 'is_active', 'avatar', 'pending_avatar', 'referred_by'];
    protected $hidden   = ['password', 'remember_token', 'pending_email_token'];
    protected $appends  = ['avatar_url', 'pending_avatar_url'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) return null;
        return Storage::disk('public')->url($this->avatar);
    }

    public function getPendingAvatarUrlAttribute(): ?string
    {
        if (!$this->pending_avatar) return null;
        return Storage::disk('public')->url($this->pending_avatar);
    }

    public function getReferralCodeAttribute(): ?string
    {
        $code = $this->tutorProfile?->tutor_id ?? $this->guardianProfile?->guardian_id;
        return $code ? \Illuminate\Support\Str::after($code, '-') : null;
    }

    public function tutorProfile(): HasOne { return $this->hasOne(TutorProfile::class); }
    public function guardianProfile(): HasOne { return $this->hasOne(GuardianProfile::class); }
    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referred_by'); }
    public function referrals(): HasMany { return $this->hasMany(User::class, 'referred_by'); }
    public function referralEarnings(): HasMany { return $this->hasMany(ReferralEarning::class, 'referrer_id'); }
    public function isAdmin(): bool { return $this->role === 'super_admin'; }
    public function isTutor(): bool { return $this->role === 'tutor'; }
    public function isGuardian(): bool { return in_array($this->role, ['guardian', 'student']); }
}
