<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecoveryCode extends Consommable
{
    use HasFactory;

    public const MAX_ATTEMPTS = 3;

    protected ?string $configExpirationMinutesKey = 'recovery_code_expiration_minutes';

    public function attemptsLeft(): int
    {
        return self::MAX_ATTEMPTS - $this->attempts;
    }
}
