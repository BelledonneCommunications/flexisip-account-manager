<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthToken extends Consommable
{
    use HasFactory;

    public static $expirationMinutes = 5;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeValid($query)
    {
        return $query->where('created_at', '>', Carbon::now()->subMinutes(self::$expirationMinutes));
    }
}
