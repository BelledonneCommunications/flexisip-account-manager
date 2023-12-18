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

    public function consumed(): bool
    {
        return $this->{$this->consommableAttribute} == null;
    }
}
