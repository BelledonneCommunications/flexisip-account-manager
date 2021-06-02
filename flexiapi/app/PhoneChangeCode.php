<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneChangeCode extends Model
{
    use HasFactory;

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
