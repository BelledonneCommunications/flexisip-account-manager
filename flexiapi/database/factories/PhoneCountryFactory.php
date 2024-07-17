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

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use libphonenumber\PhoneNumberUtil;

class PhoneCountryFactory extends Factory
{
    public function definition()
    {
        $phoneNumberUtils = PhoneNumberUtil::getInstance();

        $codes = ['AF', 'AX', 'AL', 'DZ', 'AS', 'AD'];
        $code = $codes[array_rand($codes)];

        return [
            'code' => $code,
            'country_code' => $phoneNumberUtils->getMetadataForRegion($code)->getCountryCode(),
            'activated' => false,
        ];
    }

    public function france()
    {
        $code = 'FR';
        $phoneNumberUtils = PhoneNumberUtil::getInstance();

        return $this->state(fn (array $attributes) => [
            'code' => $code,
            'country_code' => $phoneNumberUtils->getMetadataForRegion($code)->getCountryCode()
        ]);
    }

    public function netherlands()
    {
        $code = 'NL';
        $phoneNumberUtils = PhoneNumberUtil::getInstance();

        return $this->state(fn (array $attributes) => [
            'code' => $code,
            'country_code' => $phoneNumberUtils->getMetadataForRegion($code)->getCountryCode()
        ]);
    }

    public function activated()
    {
        return $this->state(fn (array $attributes) => [
            'activated' => true
        ]);
    }
}
