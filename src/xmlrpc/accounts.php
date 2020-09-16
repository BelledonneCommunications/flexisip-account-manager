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

include_once __DIR__ . '/accounts_email.php';
include_once __DIR__ . '/accounts_phone.php';

include_once __DIR__ . '/../misc/results_values.php';

// args = [username, [domain]]
function xmlrpc_is_account_used($method, $args)
{
    $user = $args[0];
    $domain = get_domain($args[1]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_is_account_used(" . $user . ", " . $domain . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if ($account->getOne()) {
        return OK;
    }

    $alias = new Alias($db);
    $alias->alias = $user;
    $alias->domain = $domain;

    if ($alias->getOne()) {
        return OK;
    }

    return NOK;
}

// args = [username, [domain]]
function xmlrpc_is_account_activated($method, $args)
{
    $user = $args[0];
    $domain = get_domain($args[1]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_is_account_activated(" . $user . ", " . $domain . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        $alias = new Alias($db);
        $alias->alias = $user;
        $alias->domain = $domain;

        if ($alias->getOne()) {
            $account->id = $alias->account_id;
            if (!$account->getOne()) {
                return ACCOUNT_NOT_FOUND;
            }
        } else {
            return ACCOUNT_NOT_FOUND;
        }
    }

    Logger::getInstance()->message("Account activation status is " . $account->activated);
    if (is_activated($account->activated)) {
        return OK;
    }

    return NOK;
}

// args = [username, key, [domain], [algo]]
function xmlrpc_recover_account_from_confirmation_key($method, $args)
{
    $username = $args[0];
    $key = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_recover_account_from_confirmation_key(" . $username . ", " . $domain . ", " . $key . ", " . $algo . ")");

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    if (!is_key_matching($key, $account)) {
        return KEY_DOESNT_MATCH;
    }

    if (!is_activated($account)) {
        $account->activated = "1";
        $account->update();
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if ($password->getOne()) {
        $result = array(
            "password" => $password->password,
            "algorithm" => $password->algorithm
        );
        return $result;
    }

    // If not found, try without algo
    $password2 = new Password($db);
    $password2->account_id = $account->id;

    if ($password2->getOne()) {
        $result = array(
            "password" => $password2->password,
            "algorithm" => $password2->algorithm
        );
        return $result;
    }

    return PASSWORD_NOT_FOUND;
}

// args = []
function xmlrpc_get_accounts_count($method, $args)
{
    Logger::getInstance()->message("[XMLRPC] xmlrpc_get_accounts_count()");

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    return $account->getCount();
}

// args = [user_agent, [domain], [algo]]
function xmlrpc_create_push_account($method, $args)
{
    $user_agent = $args[0];
    $domain = get_domain($args[1]);
    $algo = get_algo($args[2]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_create_push_account(" . $domain . ", " . $algo . ")");

    if ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->domain = $domain;

    do {
        $user = generate_username();
        $account->username = $user;
    } while ($account->getOne());

    Logger::getInstance()->message("[XMLRPC] Push account generated username is: " . $user);

    $hashed_password = hash_password($user, generate_password(), $domain, $algo);

    $account->user_agent = $user_agent;
    $account->ip_address = getIp();
    $account->activated = "1";
    $account->create();

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->password = $hashed_password;
    $password->algorithm = $algo;
    $password->create();

    if (CUSTOM_HOOKS) {
        hook_on_account_created($account);
    }

    $result = array(
        "username" => $account->username,
        "domain" => $account->domain,
        "password" => $password->password,
        "algorithm" => $password->algorithm
    );

    return $result;
}


function xmlrpc_accounts_register_methods($server)
{
    xmlrpc_server_register_method($server, 'is_account_used', 'xmlrpc_is_account_used');// args = [username, [domain]], return OK or NOK
    xmlrpc_server_register_method($server, 'is_account_activated', 'xmlrpc_is_account_activated');// args = [username, [domain]], return OK or NOK
    xmlrpc_server_register_method($server, 'recover_account_from_confirmation_key', 'xmlrpc_recover_account_from_confirmation_key');// args = [username, key, [domain], [algo]]

    xmlrpc_server_register_method($server, 'get_accounts_count', 'xmlrpc_get_accounts_count');// args = []

    xmlrpc_server_register_method($server, 'create_push_account', 'xmlrpc_create_push_account');// args = [user_agent, [domain], [algo]]

    xmlrpc_accounts_email_register_methods($server);
    xmlrpc_accounts_phone_register_methods($server);
}
