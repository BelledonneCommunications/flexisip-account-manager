<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneCountry extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $visible = ['code', 'country_code', 'activated'];
    protected $casts = [
        'activated' => 'boolean',
    ];

    public function getNameAttribute(): ?string
    {
        return getCountryCodes()[$this->attributes['code']];
    }
}
