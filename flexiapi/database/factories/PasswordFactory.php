<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2019 Belledonne Communications SARL, All rights reserved.

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

use App\Password;
use App\Account;
use Faker\Generator as Faker;

$factory->define(Password::class, function (Faker $faker) use ($factory) {
    $account = $factory->create(App\Account::class);

    return [
        'password'   => hash('md5', $account->username.':'.$account->domain.':testtest'),
        'account_id' => $account->id,
        'algorithm'  => 'MD5',
    ];
});

$factory->state(Password::class, 'sha256', function (Faker $faker) use ($factory) {
    $account = $factory->create(App\Account::class);

    return [
        'password'   => hash('sha256', $account->username.':'.$account->domain.':testtest'),
        'account_id' => $account->id,
        'algorithm'  => 'SHA-256',
    ];
});

$factory->state(Password::class, 'clrtxt', function (Faker $faker) use ($factory) {
    $account = $factory->create(App\Account::class);

    return [
        'password'   => 'testtest',
        'account_id' => $account->id,
        'algorithm'  => 'CLRTXT',
    ];
});