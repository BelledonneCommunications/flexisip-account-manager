<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecoveryCode extends Consommable
{
    use HasFactory;

    protected ?string $configExpirationMinutesKey = 'recovery_code_expiration_minutes';
}
