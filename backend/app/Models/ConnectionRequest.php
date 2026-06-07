<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class ConnectionRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'guardian_profile_id','tutor_profile_id',
        'status','guardian_message','admin_notes','confirmed_at',
    ];
    protected $casts = ['confirmed_at' => 'datetime'];
    public function guardianProfile(): BelongsTo { return $this->belongsTo(GuardianProfile::class); }
    public function tutorProfile(): BelongsTo { return $this->belongsTo(TutorProfile::class); }
    public function review(): HasOne { return $this->hasOne(Review::class, 'connection_request_id'); }
}
