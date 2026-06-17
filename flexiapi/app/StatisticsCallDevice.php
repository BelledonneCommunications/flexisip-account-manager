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

enum InviteTerminatedState: string
{
    case Accepted = 'accepted';
    case AcceptedElsewhere = 'accepted_elsewhere';
    case Canceled = 'canceled';
    case Declined = 'declined';
    case DeclinedElsewhere = 'declined_elsewhere';
    case Error = 'error';

    public static function values(): array
    {
        return array_column(InviteTerminatedState::cases(), 'value');
    }

    public function icon(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => 'phone',
            self::Canceled => 'phone-x',
            self::Declined, self::DeclinedElsewhere => 'phone-disconnect',
            self::Error => 'phone-slash',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => __('Accepted'),
            self::Canceled => __('Canceled'),
            self::Declined, self::DeclinedElsewhere => __('Declined'),
            self::Error => __('Error'),
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => 'color green',
            self::Canceled => 'color orange',
            self::Error => 'color red',
            default => ''
        };
    }
}

class StatisticsCallDevice extends Model
{
    use HasFactory;

    protected $fillable = ['call_id', 'device_id', 'rang_at', 'invite_terminated_at', 'invite_terminated_state', 'call_id'];
    protected $casts = [
        'rang_at' => 'datetime',
        'invite_terminated_state' => InviteTerminatedState::class
    ];

    public function call()
    {
        return $this->belongsTo(StatisticsCall::class, 'id', 'call_id');
    }
}
