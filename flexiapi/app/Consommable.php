<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Consommable extends Model
{
    protected string $consommableAttribute = 'code';

    public function consume()
    {
        $this->{$this->consommableAttribute} = null;
        $this->save();
    }

    public function fillRequestInfo(Request $request)
    {
        $this->ip = $request->ip();
        $this->user_agent = $request->userAgent();
    }

    public function consumed(): bool
    {
        return $this->{$this->consommableAttribute} == null;
    }
}
