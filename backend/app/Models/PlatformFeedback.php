<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformFeedback extends Model
{
    protected $fillable = ['user_id', 'quote', 'display_label', 'moderation_status', 'show_on_landing'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
