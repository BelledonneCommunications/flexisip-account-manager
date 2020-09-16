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

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Device extends Model
{
    public function fromContact(\stdClass $contact)
    {
        $this->uuid = \substr($contact->line_value_copy, 2, -2);
        $this->expires_at = Carbon::createFromTimestamp($contact->expires_at);
        $this->update_time = Carbon::createFromTimestamp($contact->update_time);
        $this->user_agent = $contact->user_agent;
    }
}
