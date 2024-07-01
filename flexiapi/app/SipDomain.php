<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SipDomain extends Model
{
    use HasFactory;

    protected $hidden = ['id'];
    protected $casts = [
        'super' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'domain', 'domain');
    }
}
