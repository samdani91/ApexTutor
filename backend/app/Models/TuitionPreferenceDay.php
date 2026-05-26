<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionPreferenceDay extends Model
{
    public $timestamps = false;
    protected $fillable = ['tuition_preference_id','day','time_from','time_to'];
    public function preference(): BelongsTo { return $this->belongsTo(TuitionPreference::class, 'tuition_preference_id'); }
}
