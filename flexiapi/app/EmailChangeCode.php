<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailChangeCode extends Consommable
{
    use HasFactory;

    protected ?string $configExpirationMinutesKey = 'email_change_code_expiration_minutes';
    protected $hidden = ['id', 'account_id', 'code'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function validate(int $code): bool
    {
        return ($this->code == $code);
    }

    public function getObfuscatedEmailAttribute()
    {
        $stars = 4; // Min Stars to use
        $at = strpos($this->attributes['email'], '@');
        if ($at - 2 > $stars) $stars = $at - 2;
        return substr($this->attributes['email'], 0, 1) . str_repeat('*', $stars) . substr($this->attributes['email'], $at - 1);
    }
}
