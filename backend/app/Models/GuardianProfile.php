<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GuardianProfile extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id', 'guardian_id', 'account_type',
        'occupation', 'relationship_to_student', 'nid_number', 'nid_document',
    ];

    protected $appends = ['nid_document_url'];

    public function getNidDocumentUrlAttribute(): ?string
    {
        if (!$this->nid_document) return null;
        return Storage::disk('public')->url($this->nid_document);
    }

    protected static function booted(): void
    {
        static::created(function (GuardianProfile $profile) {
            $profile->updateQuietly([
                'guardian_id' => 'GRD-' . str_pad($profile->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function tuitionRequirements(): HasMany { return $this->hasMany(TuitionRequirement::class); }
    public function connectionRequests(): HasMany { return $this->hasMany(ConnectionRequest::class); }
    public function shortlists(): HasMany { return $this->hasMany(Shortlist::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
}
