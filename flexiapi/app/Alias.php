<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    protected $table = 'aliases';
    protected $connection = 'external';
    public $timestamps = false;

    public function account()
    {
        return $this->belongsTo('App\Account');
    }
}
