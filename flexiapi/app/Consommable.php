<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class Consommable extends Model
{
    protected string $consommableAttribute = 'code';

    public function consume()
    {
        $this->{$this->consommableAttribute} = null;
        $this->save();
    }
}
