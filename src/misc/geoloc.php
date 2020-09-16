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

include_once __DIR__ . '/../config/config.php';

class Geoloc
{
    public static function getGeolocInfosFromIp($ip_address)
    {
        if ($ip_address == "::1" || $ip_address == "127.0.0.1" || $ip_address == "localhost") {
            $service_url = 'https://ipecho.net/plain';
            $curl = curl_init($service_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $curl_response = curl_exec($curl);
            if ($curl_response === false) {
                $info = curl_getinfo($curl);
                curl_close($curl);
                Logger::getInstance()->error('Error occured during curl exec (getting public ip of server). Additionnal info: ' . var_export($info));
                return false;
            } else {
                Logger::getInstance()->debug("Getting external public ip from ipecho.net= " . $curl_response);
                $ip_address = $curl_response;
            }
            curl_close($curl);
        }
        $service_url = 'http://api.ipapi.com/' . $ip_address .'?access_key='. GEOLOC_ACCESS_KEY .'&fields=country_code,country_name';
        Logger::getInstance()->debug("Getting geoloc infos for ip after parse if=" . $ip_address);
        Logger::getInstance()->debug("Geoloc url = " . $service_url);
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            Logger::getInstance()->error('Error occured during curl exec (geoloc). Additionnal info: ' . var_export($info));
            return false;
        }
        curl_close($curl);
        Logger::getInstance()->debug('geoloc, curl response : ' . $curl_response);
        $decoded = json_decode($curl_response);

        return $decoded;
    }
}
