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

use Awobaz\Compoships\Database\Eloquent\Model;

class StatisticsMessageDevice extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'to_username', 'to_domain', 'device_id', 'last_status', 'received_at'];
    protected $casts = ['received_at' => 'datetime'];

    public function message()
    {
        return $this->hasOne(StatisticsMessage::class, 'id', 'message_id');
    }

    public function accountTo()
    {
        return $this->belongsTo(Account::class, ['username', 'domain'], ['to_username', 'to_domain']);
    }
}
