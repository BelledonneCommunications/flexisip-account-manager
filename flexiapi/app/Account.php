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
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

use App\ApiKey;
use App\Password;
use App\EmailChanged;
use App\Helpers\Utils;
use App\Mail\ChangingEmail;

class Account extends Authenticatable
{
    use HasFactory;

    protected $with = ['passwords', 'admin', 'emailChanged', 'alias', 'activationExpiration', 'types', 'actions'];
    protected $hidden = ['alias', 'expire_time', 'confirmation_key', 'pivot'];
    protected $dateTimes = ['creation_time'];
    protected $appends = ['realm', 'phone', 'confirmation_key_expires'];
    protected $casts = [
        'activated' => 'boolean',
    ];
    public $timestamps = false;

    /**
     * Scopes
     */
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

    /**
     * Relations
     */
    public function actions()
    {
        return $this->hasMany('App\AccountAction');
    }

    public function activationExpiration()
    {
        return $this->hasOne('App\ActivationExpiration');
    }

    public function admin()
    {
        return $this->hasOne('App\Admin');
    }

    public function alias()
    {
        return $this->hasOne('App\Alias');
    }

    public function apiKey()
    {
        return $this->hasOne('App\ApiKey');
    }

    public function contacts()
    {
        return $this->belongsToMany('App\Account', 'contacts', 'account_id', 'contact_id');
    }

    public function emailChanged()
    {
        return $this->hasOne('App\EmailChanged');
    }

    public function nonces()
    {
        return $this->hasMany('App\DigestNonce');
    }

    public function passwords()
    {
        return $this->hasMany('App\Password');
    }

    public function phoneChangeCode()
    {
        return $this->hasOne('App\PhoneChangeCode');
    }

    public function types()
    {
        return $this->belongsToMany('App\AccountType');
    }

    /**
     * Attributes
     */
    public function getIdentifierAttribute()
    {
        return $this->attributes['username'].'@'.$this->attributes['domain'];
    }

    public function getRealmAttribute()
    {
        return config('app.realm');
    }

    public function getResolvedRealmAttribute()
    {
        return config('app.realm') ?? $this->domain;
    }

    public function getPhoneAttribute()
    {
        if ($this->alias) {
            return $this->alias->alias;
        }

        return null;
    }

    public function getConfirmationKeyExpiresAttribute()
    {
        if ($this->activationExpiration) {
            return $this->activationExpiration->expires->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function getSha256PasswordAttribute()
    {
        return $this->passwords()->where('algorithm', 'SHA-256')->exists();
    }

    /**
     * Utils
     */
    public function activationExpired(): bool
    {
        return ($this->activationExpiration && $this->activationExpiration->isExpired());
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

        // Set it temporary to try to send the validation email
        $this->email = $newEmail;

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

    public function hasTombstone()
    {
        return AccountTombstone::where('username', $this->attributes['username'])
                               ->where('domain', $this->attributes['domain'])
                               ->exists();
    }

    public function updatePassword($newPassword, $algorithm)
    {
        $this->passwords()->delete();

        $password = new Password;
        $password->account_id = $this->id;
        $password->password = Utils::bchash($this->username, $this->resolvedRealm, $newPassword, $algorithm);
        $password->algorithm = $algorithm;
        $password->save();
    }

    public function toVcard4()
    {
        $vcard = 'BEGIN:VCARD
VERSION:4.0
KIND:individual
IMPP:sip:'.$this->getIdentifierAttribute();

        if (!empty($this->attributes['display_name'])) {
            $vcard . '
FN:'.$this->attributes['display_name'];
        }

        if ($this->types) {
            $vcard .= '
X-LINPHONE-ACCOUNT-TYPE:'.$this->types->implode('key', ',');
        }

        foreach ($this->actions as $action) {
            $vcard .= '
X-LINPHONE-ACCOUNT-ACTION:'.$action->key.';'.$action->code.';'.$action->protocol;
        }

        return $vcard . '
END:VCARD';
    }
}
