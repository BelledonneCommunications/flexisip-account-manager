<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivationExpiration extends Model
{
    use HasFactory;

    protected $casts = [
        'expires' => 'datetime:Y-m-d H:i:s',
    ];

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    public function isExpired()
    {
        $now = Carbon::now();
        return $this->expires->lessThan($now);
    }
}
