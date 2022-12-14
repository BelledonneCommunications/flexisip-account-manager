<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
    use HasFactory;

    public static $expirationMinutes = 5;

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    public function scopeValid($query)
    {
        return $query->where('created_at', '>', Carbon::now()->subMinutes(self::$expirationMinutes));
    }
}
