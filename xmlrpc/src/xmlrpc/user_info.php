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

include_once __DIR__ . '/../database/database.php';

include_once __DIR__ . '/../objects/account.php';
include_once __DIR__ . '/../objects/password.php';
include_once __DIR__ . '/../objects/alias.php';
include_once __DIR__ . '/../objects/user_info.php';

include_once __DIR__ . '/../misc/utilities.php';
include_once __DIR__ . '/../misc/geoloc.php';

include_once __DIR__ . '/../misc/results_values.php';

include_once __DIR__ . '/../misc/user_info.php';

// args = [username, ha1, [domain], [algo]]
function xmlrpc_get_email_account($method, $args)
{
    $username = $args[0];
    $ha1 = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_get_email_account(" . $username . ")");

    $database = new Database();
    $db = $database->getConnection();

    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if (!password_match($ha1, $password->password)) {
        return PASSWORD_DOESNT_MATCH;
    }

    $user_info = new UserInfo($db);
    $user_info->account_id = $account->id;
    $user_info->getOne();

    $result = array(
        "id" => $account->id,
        "username" => $account->username,
        "domain" => $account->domain,
        "email" => $account->email,
        "alias" => $account->alias,
        "activated" => $account->activated,
        "firstname" => $user_info->firstname,
        "lastname" => $user_info->lastname,
        "gender" => $user_info->gender,
        "subscribe" => $user_info->subscribe
    );

    return $result;
}

// args = [tel, ha1, [domain], [algo]]
function xmlrpc_get_phone_account($method, $args)
{
    $phone = $args[0];
    $ha1 = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_get_phone_account(" . $phone . ")");

    $database = new Database();
    $db = $database->getConnection();

    $alias = new Alias($db);
    $alias->alias = $phone;
    $alias->domain = $domain;

    $account = new Account($db);

    if (!$alias->getOne()) {
        $account->username = $phone;
        $account->domain = $domain;
    } else {
        $account->id = $alias->account_id;
    }

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if (!password_match($ha1, $password->password)) {
        return PASSWORD_DOESNT_MATCH;
    }

    $user_info = new UserInfo($db);
    $user_info->account_id = $account->id;
    $user_info->getOne();

    $result = array(
        "id" => $account->id,
        "username" => $account->username,
        "domain" => $account->domain,
        "email" => $account->email,
        "alias" => $account->alias,
        "activated" => $account->activated,
        "firstname" => $user_info->firstname,
        "lastname" => $user_info->lastname,
        "gender" => $user_info->gender,
        "subscribe" => $user_info->subscribe
    );

    return $result;
}

// args = [username, ha1, firstname, lastname, gender, subscribe, [domain], [algo]]
function xmlrpc_update_account_user_info($method, $args)
{
    $username = $args[0];
    $ha1 = $args[1];
    $firstname = $args[2];
    $lastname = $args[3];
    $gender = $args[4];
    $subscribe = $args[5];
    $domain = get_domain($args[6]);
    $algo = get_algo($args[7]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_update_account_user_info(" . $username . ", " . $domain . " : " . $firstname . ", " . $lastname . ", " . $gender . ", " . $subscribe . ")");
    return update_account_user_info($username, $ha1, $firstname, $lastname, $gender, $subscribe, $domain, $algo);
}

function xmlrpc_user_info_register_methods($server)
{
    xmlrpc_server_register_method($server, 'get_email_account', 'xmlrpc_get_email_account'); // args = [username, ha1, [domain], [algo]]
    xmlrpc_server_register_method($server, 'get_phone_account', 'xmlrpc_get_phone_account'); // args = [tel, ha1, [domain], [algo]]
    xmlrpc_server_register_method($server, 'update_account_user_info', 'xmlrpc_update_account_user_info'); // args = [username, ha1, firstname, lastname, gender, subscribe, [domain], [algo]]
}
