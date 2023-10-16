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
use Illuminate\Database\Eloquent\Model;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;

class StatisticsCall extends Model
{
    use HasFactory;
    use Compoships;

    public $incrementing = false;
    protected $casts = ['initiated_at' => 'datetime', 'ended_at' => 'datetime'];
    protected $keyType = 'string';

    public function accountFrom()
    {
        return $this->belongsTo(Account::class, ['username', 'domain'], ['to_username', 'to_domain']);
    }

    public function accountTo()
    {
        return $this->belongsTo(Account::class, ['username', 'domain'], ['to_username', 'to_domain']);
    }

    public function getFromAttribute()
    {
        return $this->attributes['from_username'] . '@' . $this->attributes['from_domain'];
    }

    public function getToAttribute()
    {
        return $this->attributes['to_username'] . '@' . $this->attributes['to_domain'];
    }

    public function scopeToByContactsList(Builder $query, int $contactsListId)
    {
        return $query->whereIn('to_domain', function ($query) use ($contactsListId) {
            Account::subByContactsList($query, $contactsListId)
                ->select('domain');
        })->whereIn('to_username', function ($query) use ($contactsListId) {
            Account::subByContactsList($query, $contactsListId)
                ->select('username');
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
