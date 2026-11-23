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
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StatisticsMessage extends Model
{
    use HasFactory;
    use Compoships;

    public $incrementing = false;
    protected $casts = ['sent_at' => 'datetime'];
    protected $keyType = 'string';

    public function accountFrom()
    {
        return $this->belongsTo(Account::class, ['username', 'domain'], ['to_username', 'to_domain']);
    }

    public function scopeInvolvingDomain(Builder $query, string $domain)
    {
        // Count each message once, even if only one of its two endpoints belongs to this domain —
        // it is not counted twice when both do.
        // The to endpoint of a message lives in the statistics_message_devices rows.
        // The where() grouping is required so that this scope composes correctly with any other constraint.
        return $query->where(function (Builder $query) use ($domain) {
            $query->where('from_domain', $domain)
                ->orWhere(function (Builder $query) use ($domain) {
                    $query->toDomain($domain);
                });
        });
    }

    public function scopeFromUsernameDomain(Builder $query, string $username, string $domain)
    {
        return $query->where('from_username', $username)->where('from_domain', $domain);
    }

    public function scopeToUsernameDomain(Builder $query, string $username, string $domain)
    {
        return $query->whereIn('id', function ($query) use ($username, $domain) {
            $query->select('message_id')
                ->from('statistics_message_devices')
                ->where('to_username', $username)
                ->where('to_domain', $domain);
        });
    }

    public function scopeToDomain(Builder $query, ?string $domain)
    {
        return $query->whereIn('id', function ($query) use ($domain) {
            $query->select('message_id')
                ->from('statistics_message_devices')
                ->where('to_domain', $domain);
        });
    }
    public function scopeToByContactsList(Builder $query, int $contactsListId)
    {
        return $query->whereIn('id', function ($query) use ($contactsListId) {
            $query->select('message_id')
                ->from('statistics_message_devices')
                ->whereIn('to_domain', function ($query) use ($contactsListId) {
                    Account::subByContactsList($query, $contactsListId)
                        ->select('domain');
                })->whereIn('to_username', function ($query) use ($contactsListId) {
                    Account::subByContactsList($query, $contactsListId)
                        ->select('username');
                });
        });
    }

    public function scopeFromByContactsList(Builder $query, int $contactsListId)
    {
        return $query->whereIn('from_domain', function ($query) use ($contactsListId) {
            Account::subByContactsList($query, $contactsListId)
                ->select('domain');
        })->whereIn('from_username', function ($query) use ($contactsListId) {
            Account::subByContactsList($query, $contactsListId)
                ->select('username');
        });
    }
}
