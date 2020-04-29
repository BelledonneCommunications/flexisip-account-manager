<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $connection = 'local';
    protected $table = 'admins';

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
