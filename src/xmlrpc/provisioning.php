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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/xml; charset=UTF-8");

include_once __DIR__ . '/../misc/utilities.php';

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml = $xml . '<config xmlns="http://www.linphone.org/xsds/lpconfig.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.linphone.org/xsds/lpconfig.xsd lpconfig.xsd">';

$proxy_config_index = 0;
$auth_info_index = 0;

if (file_exists(REMOTE_PROVISIONING_DEFAULT_CONFIG)) {
    $rc_array = parse_ini_file(REMOTE_PROVISIONING_DEFAULT_CONFIG, true);
    foreach ($rc_array as $section => $values) {
        $xml = $xml . '<section name="' . $section . '"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>';
        if (startswith($section, "proxy_config_")) {
            $proxy_config_index += 1;
        } else if (startswith($section, "auth_info_")) {
            $auth_info_index += 1;
        }

        foreach ($values as $key => $value) {
            // We need to replace any < or > by &lt; and &gt; or the xml won't be valid !
            $value = str_replace("<", "&lt;", $value);
            $value = str_replace(">", "&gt;", $value);
            
            $xml = $xml . '<entry name="' . $key . '">' . $value . '</entry>';
        }
        $xml = $xml . '</section>';
    }
}

$username = isset($_GET['username']) ? $_GET['username'] : null;
$domain = isset($_GET['domain']) ? $_GET['domain'] : SIP_DOMAIN;
$transport = isset($_GET['transport']) ? $_GET['transport'] : REMOTE_PROVISIONING_DEFAULT_TRANSPORT;

if (!empty($username)) {
    $xml = $xml . '<section name="proxy_config_' . $proxy_config_index . '"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>';
    $xml = $xml . '<entry name="reg_identity">&lt;sip:' . $username . '@' . $domain . '&gt;</entry>';
    $xml = $xml . '<entry name="reg_proxy">&lt;sip:' . $domain . ';transport=' . $transport . '&gt;</entry>';
    $xml = $xml . '<entry name="reg_route">&lt;sip:' . $domain . ';transport=' . $transport . '&gt;</entry>';
    $xml = $xml . '</section>';

    $ha1 = isset($_GET['ha1']) ? $_GET['ha1'] : null;
    $algo = isset($_GET['algorithm']) ? $_GET['algorithm'] : "MD5";

    if (!empty($ha1)) {
        $xml = $xml . '<section name="auth_info_' . $auth_info_index . '"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>';
        $xml = $xml . '<entry name="username">' . $username . '</entry>';
        $xml = $xml . '<entry name="ha1">' . $ha1 . '</entry>';
        $xml = $xml . '<entry name="domain">' . $domain . '</entry>';
        $xml = $xml . '<entry name="algorithm">' . $algo . '</entry>';
        $xml = $xml . '</section>';
    }
}

$xml = $xml . '</config>';

http_response_code(200);
echo $xml;

?>