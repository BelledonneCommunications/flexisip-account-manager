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
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

use App\ApiKey;
use App\Password;
use App\EmailChanged;
use App\Helpers\Utils;
use App\Events\AccountDeleting;
use App\Mail\ChangingEmail;
use App\Mail\ChangedEmail;

class Account extends Authenticatable
{
    use HasFactory;

    protected $connection = 'external';
    protected $with = ['passwords', 'admin', 'emailChanged'];
    protected $dateTimes = ['creation_time'];
    protected $appends = ['realm'];
    protected $casts = [
        'activated' => 'boolean',
    ];
    public $timestamps = false;

    protected $dispatchesEvents = [
        // Remove all the related data, accross multiple database
        // and without foreign-keys (sic)
        'deleting' => AccountDeleting::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope('domain', function (Builder $builder) {
            $builder->where('domain', config('app.sip_domain'));
        });
    }

    public function scopeSip($query, string $sip)
    {
        if (\str_contains($sip, '@')) {
            list($usernane, $domain) = explode('@', $sip);

            return $query->where('username', $usernane)
                         ->where('domain', $domain);
        };

        return $query->where('id', '<', 0);
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

    public function getRealmAttribute()
    {
        return config('app.realm');
    }

    public function requestEmailUpdate(string $newEmail)
    {
        // Remove all the old requests
        $this->emailChanged()->delete();

        // Create a new one
        $emailChanged = new EmailChanged;
        $emailChanged->new_email = $newEmail;
        $emailChanged->hash = Str::random(16);
        $emailChanged->account_id = $this->id;
        $emailChanged->save();

        $this->refresh();

        Mail::to($this)->send(new ChangingEmail($this));
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

    public function updatePassword($newPassword, $algorithm)
    {
        $this->passwords()->delete();

        $password = new Password;
        $password->account_id = $this->id;
        $password->password = Utils::bchash($this->username, $this->domain, $newPassword, $algorithm);
        $password->algorithm = $algorithm;
        $password->save();
    }
}
