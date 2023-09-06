<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\AccountCreationToken;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use Illuminate\Support\Carbon;

class AccountCreationTokenFactory extends Factory
{
    protected $model = AccountCreationToken::class;

    public function definition()
    {
        return [
            'pn_provider' => $this->faker->uuid,
            'pn_param' => $this->faker->uuid,
            'pn_prid' => $this->faker->uuid,
            'token' => Str::random(WebAuthenticateController::$emailCodeSize),
            'used' => false,
            'created_at' => Carbon::now()
        ];
    }
}
