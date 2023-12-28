<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDictionaryEntry extends Model
{
    use HasFactory;

    protected $visible = ['value'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
