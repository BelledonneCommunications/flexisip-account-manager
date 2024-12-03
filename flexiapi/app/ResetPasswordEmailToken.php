<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetPasswordEmailToken extends Consommable
{
    use HasFactory;
    protected ?string $configExpirationMinutesKey = 'reset_password_email_token_expiration_minutes';

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
}
