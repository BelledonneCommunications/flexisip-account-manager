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

namespace App\Helpers;

use Illuminate\Support\Str;

use App\Account;
use App\DigestNonce;

class Utils
{
    public static function generateNonce(): string
    {
        return Str::random(32);
    }

    public static function generateValidNonce(Account $account): string
    {
        $nonce = new DigestNonce;
        $nonce->account_id = $account->id;
        $nonce->nonce = Utils::generateNonce();
        $nonce->save();

        return $nonce->nonce;
    }

    public static function bchash(string $username, string $domain, string $password, string $algorithm = 'MD5')
    {
        $algos = ['MD5' => 'md5', 'SHA-256' => 'sha256'];

        return hash($algos[$algorithm], $username.':'.$domain.':'.$password);
    }

    public static function generatePin()
    {
        return mt_rand(1000, 9999);
    }
}
