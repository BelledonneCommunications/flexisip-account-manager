<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalAccount extends Model
{
    use HasFactory;

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    public function getIdentifierAttribute()
    {
        return $this->attributes['username'].'@'.$this->attributes['domain'];
    }

    public function getResolvedRealmAttribute()
    {
        return $this->attributes['domain'];
    }
}
