<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalAccount extends Model
{
    use HasFactory;

    public const PROTOCOLS = ['UDP', 'TCP','TLS'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getIdentifierAttribute(): string
    {
        return $this->attributes['username'] . '@' . $this->attributes['domain'];
    }
}
