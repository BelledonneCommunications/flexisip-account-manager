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

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/../misc/results_values.php';

// args = [phone, [domain]]
function xmlrpc_is_alias_used($method, $args)
{
    $phone = $args[0];
    $domain = get_domain($args[1]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_is_alias_used(" . $phone . ", " . $domain . ")");

    if (!check_parameter($phone, "phone")) {
        return MISSING_PHONE_PARAM;
    } elseif (!startswith($phone, "+")) {
        return PHONE_NOT_E164;
    }

    $database = new Database();
    $db = $database->getConnection();
    $alias = new Alias($db);
    $alias->alias = $phone;
    $alias->domain = $domain;

    if (!$alias->getOne()) {
        return ALIAS_NOT_FOUND;
    }

    return OK;
}

// args = [phone, account, [domain], [lang]]
function xmlrpc_link_phone_number_with_account($method, $args)
{
    $phone = $args[0];
    $user = $args[1];
    $domain = get_domain($args[2]);
    $lang = get_lang($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_link_phone_number_with_account(" . $user . ", " . $domain . ", " . $phone . ", " . $lang . ")");

    if (!check_parameter($phone)) {
        return MISSING_PHONE_PARAM;
    } elseif (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!startswith($phone, "+")) {
        return PHONE_NOT_E164;
        /*} else if (db_alias_is_in_use($phone, $domain)) {
            return PHONE_TAKEN;*/
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    if (SEND_ACTIVATION_SMS) {
        if (!SMS_API_ENABLED) {
            // This is a hack to allow testing without sending SMS
            return OK;
        }
        $account->confirmation_key = generate_4_digits_code();
        $account->update();
        $ok = send_sms($phone, $account->confirmation_key, $lang);
        return $ok;
    }

    return SMS_DISABLED;
}

// args = [phone, user, key, ha1, [domain], [algo]]
function xmlrpc_activate_phone_number_link($method, $args)
{
    $phone = $args[0];
    $user = $args[1];
    $key = $args[2];
    $ha1 = $args[3];
    $domain = get_domain($args[4]);
    $algo = get_algo($args[5]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_activate_phone_number_link(" . $user . ", " . $domain . ", " . $phone . ", " . $key . ", " . $algo . ")");

    if (!check_parameter($phone, "phone")) {
        return MISSING_PHONE_PARAM;
    } elseif (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!startswith($phone, "+")) {
        return PHONE_NOT_E164;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }
    if (!is_activated($account->activated)) {
        return ACCOUNT_NOT_YET_ACTIVATED;
    }

    if (!is_key_matching($key, $account)) {
        return KEY_DOESNT_MATCH;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }
    if (!password_match($password->password, $ha1)) {
        return PASSWORD_DOESNT_MATCH;
    }

    $alias = new Alias($db);
    $alias->alias = $phone;
    $alias->domain = $domain;

    if ($alias->getOne()) {
        $alias->account_id = $account->id;
        $alias->update();
    } else {
        $alias->account_id = $account->id;
        $alias->create();
    }

    return OK;
}

// args = [phone, [domain]]
function xmlrpc_get_alias($method, $args)
{
    $phone = $args[0];
    $domain = get_domain($args[1]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_get_alias(" . $phone . ", " . $domain . ")");

    if (!check_parameter($phone, "phone")) {
        return MISSING_PHONE_PARAM;
    } elseif (!startswith($phone, "+")) {
        return PHONE_NOT_E164;
    }

    $database = new Database();
    $db = $database->getConnection();
    $alias = new Alias($db);
    $alias->alias = $phone;
    $alias->domain = $domain;

    if (!$alias->getOne()) {
        return ALIAS_NOT_FOUND;
    }

    $account = new Account($db);
    $account->id = $alias->account_id;
    if ($account->getOne()) {
        return $account->username;
    }

    return ACCOUNT_NOT_FOUND;
}

function xmlrpc_aliases_register_methods($server)
{
    xmlrpc_server_register_method($server, 'is_alias_used', 'xmlrpc_is_alias_used');// args = [phone, [domain]], return OK
    xmlrpc_server_register_method($server, 'link_phone_number_with_account', 'xmlrpc_link_phone_number_with_account');// args = [phone, account, [domain], [lang]], return OK
    xmlrpc_server_register_method($server, 'activate_phone_number_link', 'xmlrpc_activate_phone_number_link');// args = [phone, user, key, ha1, [domain], [algo]], return OK
    xmlrpc_server_register_method($server, 'get_alias', 'xmlrpc_get_alias');// args = [phone, [domain]], return username
}
