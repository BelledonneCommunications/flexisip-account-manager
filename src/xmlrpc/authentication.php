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

include_once __DIR__ . '/../database/database.php';

include_once __DIR__ . '/../objects/account.php';
include_once __DIR__ . '/../objects/password.php';

include_once __DIR__ . '/../misc/utilities.php';

// Nonce are one-time usage, in order to avoid storing them in a table
// The nonce is built using:
// - timestamp : nonce is valid for MIN_NONCE_VALIDITY_PERIOD seconds at minimum and twice it at maximum (our goal is one time usage anyway, typical value shall be 10 )
// - request content : the response uses only the URI, enforce the content to be the same so the nonce is actually a one time usage
// a replay is not devastating (it would just be an actual replay, not a different command to server)
// - secret key : avoid an attacker to be able to generate a valid nonce
function auth_get_valid_nonces()
{
    $request = file_get_contents('php://input');
    $time = time();
    $time -= $time%MIN_NONCE_VALIDITY_PERIOD; // our nonce will be valid at leat MIN_NONCE_VALIDITY_PERIOD seconds and max twice it, so floor the timestamp
    return array(
        hash_hmac("sha256", $time.':'.$request, AUTH_NONCE_KEY),
        hash_hmac("sha256", $time-MIN_NONCE_VALIDITY_PERIOD.':'.$request, AUTH_NONCE_KEY));
}

function request_authentication($realm = "sip.example.org")
{
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm.
        '",qop="auth",nonce="' . auth_get_valid_nonces()[0] . '",opaque="' . md5($realm) . '"');
    exit();
}

function authenticate($auth_digest, $realm = "sip.example.org")
{
    Logger::getInstance()->debug("Authenticate : Digest ".(print_r($auth_digest, true))." realm " . $realm);
    // Parse the client authentication data
    $default = array('nounce', 'nc', 'cnounce', 'qop', 'username', 'uri', 'response');
    preg_match_all('~(\w+)="?([^",]+)"?~', $auth_digest, $matches); # $_SERVER['PHP_AUTH_DIGEST']
    $data = array_combine($matches[1] + $default, $matches[2]);

    // Get the password/hash from database
    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $data['username'];
    $account->domain = empty($data['domain']) ? SIP_DOMAIN : $data['domain'];

    if (!$account->getOne()) {
        Logger::getInstance()->error("Couldn't find account " . (string)$account);
        return false;
    }
    $pwd = new Password($db);
    $pwd->account_id = $account->id;

    $stmt = $pwd->getAll();
    $num = $stmt->rowCount();
    if ($num <= 0) {
        Logger::getInstance()->error("Couldn't find password " . (string)$pwd);
        return false;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        // Generate the valid response
        switch ($algorithm) {
            case 'CLRTXT':
                $A1 = md5($data['username'].':'.$data['realm'].':'.$password);
                $A2 = md5(getenv('REQUEST_METHOD').':'.$data['uri']);
                $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
                break;
            case 'MD5':
                $A1 = $password; // username:realm:password
                $A2 = md5(getenv('REQUEST_METHOD').':'.$data['uri']);
                $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
                break;
            case 'SHA256':
                $A1 = $password; // username:realm:password
                $A2 = hash('sha256', getenv('REQUEST_METHOD').':'.$data['uri']);
                $valid_response = hash('sha256', $A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
                break;
        }

        // Compare with the client response
        if ($data['response'] === $valid_response) {
            return true;
        }
    }

    Logger::getInstance()->error("Failed to authenticate request");
    return false;
}
