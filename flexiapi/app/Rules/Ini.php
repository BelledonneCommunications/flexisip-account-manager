<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2022 Belledonne Communications SARL, All rights reserved.

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

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Ini implements Rule
{
    private $forbiddenKeys = [];
    private $forbiddenKeysFound = [];

    public function __construct($forbiddenKeys)
    {
        $this->forbiddenKeys = $forbiddenKeys;
    }

    public function passes($attribute, $value): bool
    {
        $parsed = parse_ini_string($value);

        if ($parsed == false) {
            return false;
        }

        foreach (array_keys($parsed) as $key) {
            if (in_array($key, $this->forbiddenKeys)) {
                array_push($this->forbiddenKeysFound, $key);
            }
        }

        return empty($this->forbiddenKeysFound);
    }

    public function message()
    {
        $message = 'Invalid ini format.';

        if (!empty($this->forbiddenKeysFound)) {
            $message .= ' The following settings cannot be set, they are already handled by the platform: ' . implode(', ', $this->forbiddenKeysFound);
        }

        return $message;
    }
}
