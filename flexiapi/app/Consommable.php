<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Consommable extends Model
{
    protected string $consommableAttribute = 'code';
    protected ?string $configExpirationMinutesKey = null;
    protected $casts = [
        'expire_at' => 'datetime'
    ];

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

    public function getExpireAtAttribute(): ?string
    {
        if ($this->isExpirable()) {
            return $this->created_at->addMinutes(config('app.' . $this->configExpirationMinutesKey))->toJSON();
        }

        return null;
    }

    public function expired(): bool
    {
        return ($this->isExpirable()
            && Carbon::now()->subMinutes(config('app.' . $this->configExpirationMinutesKey))->isAfter($this->created_at));
    }

    private function isExpirable(): bool
    {
        return $this->configExpirationMinutesKey != null
            && config('app.' . $this->configExpirationMinutesKey) != null
            && config('app.' . $this->configExpirationMinutesKey) > 0;
    }
}
