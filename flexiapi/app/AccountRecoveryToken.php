<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountRecoveryToken extends Consommable
{
    use HasFactory;

    protected $hidden = ['id', 'updated_at', 'created_at'];
    protected $appends = ['expire_at'];
    protected ?string $configExpirationMinutesKey = 'account_recovery_token_expiration_minutes';

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function consume()
    {
        $this->used = true;
        $this->save();
    }

    public function consumed(): bool
    {
        return $this->used == true;
    }

    public function toLog()
    {
        return [
            'token' => $this->token,
            'pn_param' => $this->pn_param,
            'used' => $this->used,
            'account_id' => $this->account_id,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
        ];
    }
}
