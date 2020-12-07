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

header("Access-Control-Allow-Origin: *");

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/../objects/account.php';
include_once __DIR__ . '/../objects/password.php';

include_once __DIR__ . '/authentication.php';

$logger = Logger::getInstance();

$username = isset($_GET['username']) ? $_GET['username'] : null;
$domain = isset($_GET['domain']) ? $_GET['domain'] : SIP_DOMAIN;
$realm = isset($_GET['domain']) ? $_GET['domain'] : AUTH_REALM;

if (REMOTE_PROVISIONING_USE_DIGEST_AUTH) {
    $headers = getallheaders();
    // From is the GRUU('sip:username@AUTH_REALM;gr=*;), we need to extract the username from it:
    // from position 4(skip 'sip:') until the first occurence of @
    // pass it through rawurldecode has GRUU may contain escaped characters
    $from = rawurldecode(substr($headers['From'],4,strpos($headers['From'], '@')-4));
    if (empty($from)) {
        $from = $username;
        $logger->debug("Empty From, using username = " . $username);
    }
    $authorization = null;

    // Get authentication header if there is one
    if (!empty($headers['Auth-Digest'])) {
        $logger->debug("Auth-Digest = " . $headers['Auth-Digest']);
        $authorization = $headers['Auth-Digest'];
    } elseif (!empty($headers['Authorization'])) {
        $logger->debug("Authorization = " . $headers['Authorization']);
        $authorization = $headers['Authorization'];
    }

    if (!empty($authorization)) {
        $authentication_status = authenticate($authorization, $realm);

        if ($authentication_status != null) {
            Logger::getInstance()->debug("Authentication successful");
        } else {
            Logger::getInstance()->debug("Authentication failed");
            request_authentication($realm, $from);
        }
    } else {
        Logger::getInstance()->debug("No authentication header");
        request_authentication($realm, $from);
    }
}

if (isset($_GET['qrcode']) && $_GET['qrcode'] == 1) {
    $query = $_GET;
    $query['qrcode'] = 0;
    $query_result = http_build_query($query);
    $data = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?" . $query_result;

    // https://github.com/tecnickcom/tc-lib-barcode GPLv3
    require __DIR__ . '/../vendor/tecnickcom/tc-lib-barcode/resources/autoload.php';
    $barcode = new \Com\Tecnick\Barcode\Barcode();
    $bobj = $barcode->getBarcodeObj(
        'QRCODE,H',                     // barcode type and additional comma-separated parameters
        $data,                          // data string to encode
        -4,                             // bar width (use absolute or negative value as multiplication factor)
        -4,                             // bar height (use absolute or negative value as multiplication factor)
        'black',                        // foreground color
        array(-2, -2, -2, -2)           // padding (use absolute or negative values as multiplication factors)
    )->setBackgroundColor('white');     // background color

    header("Content-Type: text/html; charset=UTF-8");
    echo $bobj->getHtmlDiv();
    return;
}

header("Content-Type: application/xml; charset=UTF-8");

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= '<config xmlns="http://www.linphone.org/xsds/lpconfig.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.linphone.org/xsds/lpconfig.xsd lpconfig.xsd">';

$proxy_config_index = 0;
$auth_info_index = 0;

if (file_exists(REMOTE_PROVISIONING_DEFAULT_CONFIG)) {
    $rc_array = parse_ini_file(REMOTE_PROVISIONING_DEFAULT_CONFIG, true);
    foreach ($rc_array as $section => $values) {
        $xml .= '<section name="' . $section . '">';
        if (startswith($section, "proxy_config_")) {
            $proxy_config_index += 1;
        } elseif (startswith($section, "auth_info_")) {
            $auth_info_index += 1;
        }

        foreach ($values as $key => $value) {
            // We need to replace any < or > by &lt; and &gt; or the xml won't be valid !
            $value = str_replace("<", "&lt;", $value);
            $value = str_replace(">", "&gt;", $value);

            $xml .= '<entry name="' . $key . '"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>' . $value . '</entry>';
        }
        $xml .= '</section>';
    }
}


$transport = isset($_GET['transport']) ? $_GET['transport'] : REMOTE_PROVISIONING_DEFAULT_TRANSPORT;

$request_params = array(
    "username" => $username,
    "domain" => $domain,
    "transport" => $transport,
    "ha1" => null,
    "algo" => DEFAULT_ALGORITHM,
);

if (!empty($username)) {
    if (REMOTE_PROVISIONING_USE_DIGEST_AUTH) {
        if ($username != $authentication_status) {
            $logger->error("User " . $authentication_status . " is trying to impersonate another user: " . $username);
            header('HTTP/1.1 403 Forbidden: Authenticated username and provisioning username mismatch');
            exit();
        }
    }

    $ha1 = isset($_GET['ha1']) ? $_GET['ha1'] : null;
    $algo = isset($_GET['algorithm']) ? $_GET['algorithm'] : DEFAULT_ALGORITHM;

    if (REMOTE_PROVISIONING_ONE_TIME_PASSWORD) {
        $database = new Database();
        $db = $database->getConnection();
        $account = new Account($db);
        $account->username = $username;
        $account->domain = $domain;

        if ($account->getOne()) {
            if (!is_activated($account->activated)) {
                $password = new Password($db);
                $password->account_id = $account->id;
                $password->algorithm = $algo;

                if ($password->getOne()) {
                    $new_password = generate_password();
                    $ha1 = hash_password($username, $new_password, $domain, $algo);
                    $password->password = $ha1;
                    if (!$password->update()) {
                        $logger->error("Failed to update password for account id " . $account->id);
                    }
                } else {
                    $logger->error("Password not found for account id " . $account->id);
                }

                $account->activated = "1";
                if (!$account->update()) {
                    $logger->error("Failed to activate account id " . $account->id);
                }
            } else {
                $logger->message("Account id " . $account->id . " is already activated");
            }
        }
    }

    $request_params["ha1"] = $ha1;
    $request_params["algo"] = $algo;

    $xml .= '<section name="proxy_' . $proxy_config_index . '">';
    $xml .= '<entry name="reg_identity"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>&lt;sip:' . $username . '@' . $domain . '&gt;</entry>';
    $xml .= '<entry name="reg_sendregister"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>1</entry>';
    $xml .= '<entry name="refkey"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') .     '>push_notification</entry>';
    if (get_config_value(CUSTOM_HOOKS, FALSE)) {
        provisioning_hook_on_proxy_config($xml, $request_params);
    }
    $xml .= '</section>';

    if (!empty($ha1)) {
        $xml .= '<section name="auth_info_' . $auth_info_index . '">';
        $xml .= '<entry name="username"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>' . $username . '</entry>';
        $xml .= '<entry name="ha1"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>' . $ha1 . '</entry>';
        $xml .= '<entry name="realm"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>' . $domain . '</entry>';
        $xml .= '<entry name="algorithm"' . (REMOTE_PROVISIONING_OVERWRITE_ALL ? ' overwrite="true"' : '') . '>' . $algo . '</entry>';
        if (get_config_value(CUSTOM_HOOKS, FALSE)) {
            provisioning_hook_on_auth_info($xml, $request_params);
        }
        $xml .= '</section>';
    }
}

if (get_config_value(CUSTOM_HOOKS, FALSE)) {
    provisioning_hook_on_additional_section($xml, $request_params);
}

$xml .= '</config>';

http_response_code(200);
echo $xml;
