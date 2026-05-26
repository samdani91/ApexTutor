<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    public $timestamps = false;
    protected $fillable = ['email','phone','code','purpose','expires_at','used_at'];
    protected $casts = ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    protected $table = 'otp_codes';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}
