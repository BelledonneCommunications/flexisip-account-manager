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
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

use App\ApiKey;
use App\Password;
use App\EmailChanged;
use App\Mail\ChangingEmail;
use Carbon\Carbon;

class Account extends Authenticatable
{
    use HasFactory;

    protected $with = ['passwords', 'admin', 'emailChanged', 'alias', 'activationExpiration', 'types', 'actions'];
    protected $hidden = ['alias', 'expire_time', 'confirmation_key', 'provisioning_token', 'pivot'];
    protected $dateTimes = ['creation_time'];
    protected $appends = ['realm', 'phone', 'confirmation_key_expires'];
    protected $casts = [
        'activated' => 'boolean',
    ];
    public $timestamps = false;

    public static $dtmfProtocols = ['sipinfo' => 'SIPInfo', 'rfc2833' => 'RFC2833', 'sipmessage' => 'SIP Message'];

    /**
     * Scopes
     */
    protected static function booted()
    {
        static::addGlobalScope('domain', function (Builder $builder) {
            if (Auth::hasUser()) {
                $user = Auth::user();
                if (!$user->admin || !config('app.admins_manage_multi_domains')) {
                    $builder->where('domain', config('app.sip_domain'));
                }

                return;
            }

            $builder->where('domain', config('app.sip_domain'));
        });

        /**
         * External account handling
         */
        static::creating(function ($account) {
            if (config('app.consume_external_account_on_create') && !getAvailableExternalAccount()) {
                abort(403, 'Accounts cannot be created on the server');
            }
        });

        static::created(function ($account) {
            if (config('app.consume_external_account_on_create')) {
                $account->attachExternalAccount();
            }
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
        return $this->hasMany('App\AccountAction')->whereIn('account_id', function ($query) {
            $query->select('id')
                ->from('accounts')
                ->whereNotNull('dtmf_protocol');
        });
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

    public function externalAccount()
    {
        return $this->hasOne('App\ExternalAccount');
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

    public function authTokens()
    {
        return $this->hasMany('App\AuthToken');
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
        return $this->attributes['username'] . '@' . $this->attributes['domain'];
    }

    public function getFullIdentifierAttribute()
    {
        $displayName = $this->attributes['display_name']
                    ? '"' . $this->attributes['display_name'] . '" '
                    : '';

        return $displayName . '<sip:' . $this->getIdentifierAttribute() . '>';
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

    public static function dtmfProtocolsRule()
    {
        return implode(',', array_keys(self::$dtmfProtocols));
    }

    public function getResolvedDtmfProtocolAttribute()
    {
        return self::$dtmfProtocols[$this->attributes['dtmf_protocol']];
    }

    /**
     * Utils
     */
    public function activationExpired(): bool
    {
        return ($this->activationExpiration && $this->activationExpiration->isExpired());
    }

    public function attachExternalAccount(): bool
    {
        $externalAccount = getAvailableExternalAccount();

        if (!$externalAccount) abort(403, 'No External Account left');

        $externalAccount->account_id = $this->id;
        $externalAccount->used = true;
        return $externalAccount->save();
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

    public function generateApiKey(): ApiKey
    {
        $this->apiKey()->delete();

        $apiKey = new ApiKey;
        $apiKey->account_id = $this->id;
        $apiKey->last_used_at = Carbon::now();
        $apiKey->key = Str::random(40);
        $apiKey->save();

        return $apiKey;
    }

    public function generateAuthToken(): AuthToken
    {
        // Clean the expired and previous ones
        AuthToken::where(
            'created_at',
            '<',
            Carbon::now()->subMinutes(AuthToken::$expirationMinutes)
        )->orWhere('account_id', $this->id)
            ->delete();

        $authToken = new AuthToken;
        $authToken->account_id = $this->id;
        $authToken->token = Str::random(32);
        $authToken->save();

        return $authToken;
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
        $password->password = bchash($this->username, $this->resolvedRealm, $newPassword, $algorithm);
        $password->algorithm = $algorithm;
        $password->save();
    }

    public function toVcard4()
    {
        $vcard = 'BEGIN:VCARD
VERSION:4.0
KIND:individual
IMPP:sip:' . $this->getIdentifierAttribute();

        $vcard .= '
FN:' . !empty($this->attributes['display_name'])
            ? $this->attributes['display_name']
            : $this->getIdentifierAttribute();

        if ($this->dtmf_protocol) {
            $vcard .= '
X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:' . $this->dtmf_protocol;
        }

        foreach ($this->types as $type) {
            $vcard .= '
X-LINPHONE-ACCOUNT-TYPE:' . $type->key;
        }

        foreach ($this->actions as $action) {
            $vcard .= '
X-LINPHONE-ACCOUNT-ACTION:' . $action->key . ';' . $action->code;
        }

        return $vcard . '
END:VCARD';
    }
}
