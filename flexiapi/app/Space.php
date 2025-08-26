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
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Space extends Model
{
    use HasFactory;

    protected $with = ['emailServer', 'carddavServers'];

    public const FORBIDDEN_KEYS = [
        'account_proxy_registrar_address',
        'account_realm',
        'assistant_disable_qr_code',
        'assistant_hide_create_account',
        'assistant_hide_third_party_account',
        'copyright_text',
        'disable_broadcast_feature',
        'disable_call_recordings_feature',
        'disable_chat_feature',
        'disable_meetings_feature',
        'hide_account_settings',
        'hide_settings',
        'intro_registration_text',
        'max_account',
        'newsletter_registration_address',
        'only_display_sip_uri_username',
    ];

    protected $hidden = ['id'];
    protected $casts = [
        'assistant_disable_qr_code' => 'boolean',
        'assistant_hide_create_account' => 'boolean',
        'assistant_hide_third_party_account' => 'boolean',
        'carddav_user_credentials' => 'boolean',
        'disable_broadcast_feature' => 'boolean',
        'disable_call_recordings_feature' => 'boolean',
        'disable_chat_feature' => 'boolean',
        'disable_meetings_feature' => 'boolean',
        'expire_at' => 'date',
        'hide_account_settings' => 'boolean',
        'hide_settings' => 'boolean',
        'only_display_sip_uri_username' => 'boolean',
        'super' => 'boolean',
    ];

    public const HOST_REGEX = '[\w\-]+';
    public const DOMAIN_REGEX = '(?=^.{4,253}$)(^((?!-)[a-z0-9-]{1,63}(?<!-)\.)+[a-z]{2,63}$)';

    protected static function booted()
    {
        static::addGlobalScope('domain', function (Builder $builder) {
            if (!Auth::hasUser()) return;

            if (Auth::hasUser() || Auth::user()->superAdmin) {
                return;
            }

            $builder->where('domain', Auth::user()->domain);
        });
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'domain', 'domain');
    }

    public function admins()
    {
        return $this->accounts()->where('admin', true);
    }

    public function emailServer()
    {
        return $this->hasOne(SpaceEmailServer::class);
    }

    public function carddavServers()
    {
        return $this->hasMany(SpaceCardDavServer::class);
    }

    public function scopeNotFull(Builder $query)
    {
        return $query->where('max_accounts', 0)
                     ->orWhereRaw('max_accounts > (select count(*) from accounts where domain = spaces.domain)');
    }

    public function getAccountsPercentageAttribute(): int
    {
        if ($this->max_accounts != null) {
            return (int)($this->accounts()->count() / $this->max_accounts * 100);
        }

        return Command::SUCCESS;
    }

    public function isFull(): bool
    {
        return $this->max_accounts > 0 && ($this->accounts()->count() >= $this->max_accounts);
    }

    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    public function isRoot(): bool
    {
        return $this->host == config('app.root_host');
    }

    public function getAccountsPercentageClassAttribute(): string
    {
        if ($this->getAccountsPercentageAttribute() >= 80) {
            return 'orange';
        }

        if ($this->getAccountsPercentageAttribute() >= 60) {
            return 'yellow';
        }

        return 'green';
    }

    public function getDaysLeftAttribute(): ?int
    {
        if ($this->expire_at != null) {
            return (int)$this->expire_at->diffInDays(Carbon::now()) + 1;
        }

        return null;
    }
}
