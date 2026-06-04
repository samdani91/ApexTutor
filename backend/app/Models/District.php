<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public $timestamps = false;
    protected $fillable = ['name','name_bn','division'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class)->orderBy('name');
    }
}
