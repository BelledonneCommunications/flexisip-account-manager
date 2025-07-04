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
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Awobaz\Compoships\Compoships;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use stdClass;

class Account extends Authenticatable
{
    use HasFactory;
    use Compoships;

    protected $with = ['passwords', 'emailChangeCode', 'types', 'actions', 'dictionaryEntries'];
    protected $hidden = ['expire_time', 'pivot', 'currentProvisioningToken', 'currentRecoveryCode', 'dictionaryEntries'];
    protected $appends = ['realm', 'provisioning_token', 'provisioning_token_expire_at', 'dictionary'];
    protected $casts = [
        'activated' => 'boolean',
    ];
    protected $fillable = ['username', 'domain', 'email'];

    public static $dtmfProtocols = ['sipinfo' => 'SIPInfo', 'rfc2833' => 'RFC2833', 'sipmessage' => 'SIP Message'];

    public static function boot()
    {
        parent::boot();

        static::deleted(function (Account $account) {
            StatisticsMessage::where('from_username', $account->username)
                ->where('from_domain', $account->domain)
                ->delete();

            StatisticsCall::where('from_username', $account->username)
                ->where('from_domain', $account->domain)
                ->delete();
        });

        static::created(function (Account $account) {
            $account->provision();
            $account->refresh();
        });
    }

    protected static function booted()
    {
        static::addGlobalScope('domain', function (Builder $builder) {
            if (Auth::hasUser() && Auth::user()->superAdmin) {
                return;
            }

            $builder->where('domain', config('app.sip_domain'));
        });
    }

    public function scopeSip($query, string $sip)
    {
        if (\str_contains($sip, '@')) {
            list($username, $domain) = explode('@', $sip);

            return $query->where('username', $username)
                ->where('domain', $domain);
        };

        return $query->where('id', '<', 0);
    }

    public static function subByContactsList($query, int $contactsListId)
    {
        return $query->from('accounts')
            ->whereIn('id', function ($query) use ($contactsListId) {
                $query->select('contact_id')
                    ->from('contacts_list_contact')
                    ->where('contacts_list_id', $contactsListId);
            });
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

    public function apiKey()
    {
        return $this->hasOne(ApiKey::class)->whereNull('expires_after_last_used_minutes');
    }

    public function adminApiKeys()
    {
        return $this->hasMany(ApiKey::class)->whereNotNull('expires_after_last_used_minutes');
    }

    public function external()
    {
        return $this->hasOne(ExternalAccount::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Account::class, 'contacts', 'account_id', 'contact_id');
    }

    public function vcardsStorage()
    {
        return $this->hasMany(VcardStorage::class);
    }

    public function contactsLists()
    {
        return $this->belongsToMany(ContactsList::class, 'account_contacts_list', 'account_id', 'contacts_list_id');
    }

    public function dictionaryEntries()
    {
        return $this->hasMany(AccountDictionaryEntry::class);
    }

    public function getDictionaryAttribute()
    {
        if ($this->dictionaryEntries->isEmpty()) return new stdClass;

        return $this->dictionaryEntries->keyBy('key')->map(function ($entry) {
            return $entry->value;
        });
    }

    public function setDictionaryEntry(string $key, string $value): AccountDictionaryEntry
    {
        $this->dictionaryEntries()->where('key', $key)->delete();
        $entry = new AccountDictionaryEntry;

        $entry->account_id = $this->id;
        $entry->key = $key;
        $entry->value = $value;
        $entry->save();

        return $entry;
    }

    public function nonces()
    {
        return $this->hasMany(DigestNonce::class);
    }

    public function passwords()
    {
        return $this->hasMany(Password::class);
    }

    public function types()
    {
        return $this->belongsToMany(AccountType::class);
    }

    public function space()
    {
        return $this->hasOne(Space::class, 'domain', 'domain');
    }

    public function statisticsFromCalls()
    {
        return $this->hasMany(StatisticsCall::class, ['from_username', 'from_domain'], ['username', 'domain']);
    }

    public function statisticsToCalls()
    {
        return $this->hasMany(StatisticsCall::class, ['to_username', 'to_domain'], ['username', 'domain']);
    }

    public function statisticsFromMessages()
    {
        return $this->hasMany(StatisticsMessage::class, ['from_username', 'from_domain'], ['username', 'domain']);
    }

    public function statisticsToMessageDevices()
    {
        return $this->hasMany(StatisticsMessageDevice::class, ['to_username', 'to_domain'], ['username', 'domain']);
    }

    /**
     * Tokens and codes
     */
    public function currentRecoveryCode()
    {
        return $this->hasOne(RecoveryCode::class)->whereNotNull('code')->latestOfMany();
    }

    public function recoveryCodes()
    {
        return $this->hasMany(RecoveryCode::class)->latest();
    }

    public function phoneChangeCode()
    {
        return $this->hasOne(PhoneChangeCode::class)->whereNotNull('code')->latestOfMany();
    }

    public function phoneChangeCodes()
    {
        return $this->hasMany(PhoneChangeCode::class)->latest();
    }

    public function emailChangeCode()
    {
        return $this->hasOne(EmailChangeCode::class)->whereNotNull('code')->latestOfMany();
    }

    public function emailChangeCodes()
    {
        return $this->hasMany(EmailChangeCode::class)->latest();
    }

    public function currentProvisioningToken()
    {
        return $this->hasOne(ProvisioningToken::class)->where('used', false)->latestOfMany();
    }

    public function provisioningTokens()
    {
        return $this->hasMany(ProvisioningToken::class)->latest();
    }

    public function accountCreationToken()
    {
        return $this->hasOne(AccountCreationToken::class);
    }

    public function accountRecoveryTokens()
    {
        return $this->hasMany(AccountRecoveryToken::class);
    }

    public function authTokens()
    {
        return $this->hasMany(AuthToken::class);
    }

    public function currentResetPasswordEmailToken()
    {
        return $this->hasOne(ResetPasswordEmailToken::class)->where('used', false)->latestOfMany();
    }

    public function resetPasswordEmailTokens()
    {
        return $this->hasMany(ResetPasswordEmailToken::class)->latest();
    }

    /**
     * Attributes
     */
    public function getRecoveryCodeAttribute(): ?string
    {
        if ($this->currentRecoveryCode) {
            return $this->currentRecoveryCode->code;
        }

        return null;
    }

    public function getProvisioningTokenAttribute(): ?string
    {
        if ($this->currentProvisioningToken) {
            return $this->currentProvisioningToken->token;
        }

        return null;
    }

    public function getProvisioningTokenExpireAtAttribute(): ?string
    {
        if ($this->currentProvisioningToken) {
            return $this->currentProvisioningToken->expire_at;
        }

        return null;
    }

    public function getIdentifierAttribute(): string
    {
        return $this->attributes['username'] . '@' . $this->attributes['domain'];
    }

    public function getFullIdentifierAttribute(): string
    {
        $displayName = $this->attributes['display_name']
            ? '"' . $this->attributes['display_name'] . '" '
            : '';

        return $displayName . '<sip:' . $this->getIdentifierAttribute() . '>';
    }

    public function getRealmAttribute()
    {
        return $this->space->account_realm;
    }

    public function getResolvedRealmAttribute()
    {
        return $this->space->account_realm ?? $this->domain;
    }

    public function getConfirmationKeyExpiresAttribute()
    {
        if ($this->activationExpiration) {
            return $this->activationExpiration->expires->format('Y-m-d H:i:s');
        }

        return null;
    }

    public static function dtmfProtocolsRule()
    {
        return implode(',', array_keys(self::$dtmfProtocols));
    }

    public function getResolvedDtmfProtocolAttribute()
    {
        return self::$dtmfProtocols[$this->attributes['dtmf_protocol']];
    }

    public function getSuperAdminAttribute(): bool
    {
        return Space::where('domain', $this->domain)->where('super', true)->exists() && $this->admin;
    }

    /**
     * Provisioning
     */

    public function getProvisioningUrlAttribute(): string
    {
        return replaceHost(
            route('provisioning.provision', $this->getProvisioningTokenAttribute()),
            $this->space->host
        );
    }

    public function getProvisioningQrcodeUrlAttribute(): string
    {
        return replaceHost(
            route('provisioning.qrcode', $this->getProvisioningTokenAttribute()),
            $this->space->host
        );
    }

    public function getProvisioningWizardUrlAttribute(): string
    {
        return replaceHost(
            route('provisioning.wizard', $this->getProvisioningTokenAttribute()),
            $this->space->host
        );
    }

    /**
     * Utils
     */

    public function generateUserApiKey(?string $ip = null): ApiKey
    {
        $this->apiKey()->delete();

        $apiKey = new ApiKey;
        $apiKey->account_id = $this->id;
        $apiKey->last_used_at = Carbon::now();
        $apiKey->key = Str::random(40);
        $apiKey->ip = $ip;
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

    public function recover(?string $code = null, ?string $phone = null, ?string $email = null): string
    {
        $recoveryCode = new RecoveryCode;
        $recoveryCode->code = $code ?? generatePin();
        $recoveryCode->phone = $phone;
        $recoveryCode->email = $email;
        $recoveryCode->account_id = $this->id;

        if (request()) {
            $recoveryCode->fillRequestInfo(request());
        }

        $recoveryCode->save();

        return $recoveryCode->code;
    }

    public function provision(?string $token = null): string
    {
        $provisioningToken = new ProvisioningToken;
        $provisioningToken->token = $token ?? Str::random(WebAuthenticateController::$emailCodeSize);
        $provisioningToken->account_id = $this->id;

        if (request()) {
            $provisioningToken->fillRequestInfo(request());
        }

        $provisioningToken->save();

        return $provisioningToken->token;
    }

    public function setRole(string $role)
    {
        if ($role == 'end_user') {
            $this->admin = false;
        }

        if ($role == 'admin') {
            $this->admin = true;
        }

        $this->save();
    }

    public function hasTombstone(): bool
    {
        return AccountTombstone::where('username', $this->attributes['username'])
            ->where('domain', $this->attributes['domain'])
            ->exists();
    }

    public function createTombstone(): bool
    {
        if (!$this->hasTombstone()) {
            $tombstone = new AccountTombstone();
            $tombstone->username = $this->attributes['username'];
            $tombstone->domain = $this->attributes['domain'];
            $tombstone->save();

            return true;
        }

        return false;
    }

    public function failedRecentRecovery(): bool
    {
        $oneHourAgo = Carbon::now()->subHour();
        return !empty($this->recovery_code) && $this->updated_at->greaterThan($oneHourAgo);
    }

    public function updatePassword(string $newPassword, ?string $algorithm = null)
    {
        $algorithm = $algorithm ?? config('app.account_default_password_algorithm');

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
FN:';
        $vcard .= !empty($this->display_name)
            ? $this->display_name
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
