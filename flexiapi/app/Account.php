<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2019 Belledonne Communications SARL, All rights reserved.

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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    protected $connection = 'external';
    protected $with = ['passwords'];
    protected $dates = ['creation_time'];
    public $timestamps = false;

    public function passwords()
    {
        return $this->hasMany('App\Password');
    }

    public function alias()
    {
        return $this->hasOne('App\Alias');
    }

    public function nonces()
    {
        return $this->hasMany('App\DigestNonce');
    }

    public function getIdentifierAttribute()
    {
        return $this->attributes['username'].'@'.$this->attributes['domain'];
    }
}
