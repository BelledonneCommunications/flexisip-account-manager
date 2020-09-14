<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailChanged extends Model
{
    protected $connection = 'local';
    protected $table = 'email_changed';

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
