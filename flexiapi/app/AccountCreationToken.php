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

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountCreationToken extends Consommable
{
    use HasFactory;

    protected $hidden = ['id', 'updated_at', 'created_at'];
    protected $appends = ['expire_at'];
    protected ?string $configExpirationMinutesKey = 'account_creation_token_expiration_minutes';

    public function accountCreationRequestToken()
    {
        return $this->hasOne(AccountCreationRequestToken::class, 'acc_creation_token_id');
    }

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
