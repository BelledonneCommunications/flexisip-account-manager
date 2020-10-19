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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

use App\ApiKey;

class Account extends Authenticatable
{
    use HasFactory;

    protected $connection = 'external';
    protected $with = ['passwords', 'admin', 'emailChanged'];
    protected $dates = ['creation_time'];
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope('domain', function (Builder $builder) {
            $builder->where('domain', config('app.sip_domain'));
        });
    }

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

    public function admin()
    {
        return $this->hasOne('App\Admin');
    }

    public function apiKey()
    {
        return $this->hasOne('App\ApiKey');
    }

    public function emailChanged()
    {
        return $this->hasOne('App\EmailChanged');
    }

    public function getIdentifierAttribute()
    {
        return $this->attributes['username'].'@'.$this->attributes['domain'];
    }

    public function generateApiKey()
    {
        $this->apiKey()->delete();

        $apiKey = new ApiKey;
        $apiKey->account_id = $this->id;
        $apiKey->key = Str::random(40);
        $apiKey->save();
    }

    public function isAdmin()
    {
        return ($this->admin);
    }
}
