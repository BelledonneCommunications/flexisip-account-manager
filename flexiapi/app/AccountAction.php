<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountAction extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at', 'account_id'];

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
