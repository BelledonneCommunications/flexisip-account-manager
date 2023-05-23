<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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
use Illuminate\Database\Eloquent\Model;

class AccountCreationRequestToken extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'updated_at', 'created_at'];
    protected $appends = ['validation_url'];

    public function accountCreationToken()
    {
        return $this->belongsTo(AccountCreationToken::class, 'acc_creation_token_id');
    }

    public function getValidationUrlAttribute(): ?string
    {
        return $this->validated_at == null
            ? route('account.creation_request_token.check', $this->token)
            : null;
    }
}
