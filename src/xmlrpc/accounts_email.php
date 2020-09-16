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
include_once __DIR__ . '/../misc/user_info.php';
include_once __DIR__ . '/../misc/results_values.php';

// args = [username, email, [hash], useragent, [domain], [algo]]
function xmlrpc_create_email_account($method, $args)
{
    $user = $args[0];
    $email = $args[1];
    $hashed_password = $args[2];
    $user_agent = $args[3];
    $domain = get_domain($args[4]);
    $algo = get_algo($args[5]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_create_email_account(" . $user . ", " . $domain . ", " . $email . ", " . $algo . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!check_parameter($email, "email")) {
        return MISSING_EMAIL_PARAM;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if ($account->getOne()) {
        return USERNAME_TAKEN;
    }

    if (!ALLOW_SAME_EMAILS_ON_MULTILPLE_ACCOUNTS) {
        $email_account = new Account($db);
        $email_account->email = $email;
        if ($email_account->getOne()) {
            return EMAIL_TAKEN;
        }
    }

    if (GENERATE_PASSWORD_ENABLED) {
        $hashed_password = hash_password($user, generate_password(), $domain, $algo);
    }

    $account->confirmation_key = uniqid();
    $account->email = $email;
    $account->user_agent = $user_agent;
    $account->ip_address = getIp();

    $account->activated = AUTO_ACTIVATE_ACCOUNT ? "1" : "0";
    $account->create();

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->password = $hashed_password;
    $password->algorithm = $algo;
    $password->create();

    if (CUSTOM_HOOKS) {
        hook_on_account_created($account);
    }

    if (SEND_ACTIVATION_EMAIL && EMAIL_ENABLED) {
        send_email_with_activation_link($email, $account->confirmation_key, $account->username, $account->domain, $algo);
    } elseif (AUTO_ACTIVATE_ACCOUNT) {
        //TODO
        /*if (USE_IN_APP_PURCHASES) {
            $expiration = get_trial_expiration_date();
            db_inapp_add_account($user, $domain, $expiration);
        }*/
    }

    // args = [username, email, [hash], useragent, [domain], [algo]]
    // args needed = [username, ha1, firstname, lastname, gender, subscribe, [domain], [algo]]
    //need  username + domain

    //We call this function to set the geoloc if enabled
    if (get_config_value(ENABLE_NEW_ACCOUNTS_GEOLOC, FALSE)) {
        return update_account_user_info($account->username, $hashed_password, null, null, "unknown", '0', $account->domain, $algo);
    }

    return OK;
}

// args = [username, email, md5_hash, sha256_hash, useragent, [domain]], return OK
function xmlrpc_create_email_md5_sha256_account($method, $args)
{
    $user = $args[0];
    $email = $args[1];
    $md5_hash = $args[2];
    $sha256_hash = $args[3];
    $user_agent = $args[4];
    $domain = get_domain($args[5]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_create_email_md5_sha256_account(" . $user . ", " . $domain . ", " . $email . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!check_parameter($email, "email")) {
        return MISSING_EMAIL_PARAM;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if ($account->getOne()) {
        return USERNAME_TAKEN;
    }

    if (!ALLOW_SAME_EMAILS_ON_MULTILPLE_ACCOUNTS) {
        $email_account = new Account($db);
        $email_account->email = $email;
        if ($email_account->getOne()) {
            return EMAIL_TAKEN;
        }
    }

    if (GENERATE_PASSWORD_ENABLED) {
        $pwd = generate_password();
        $md5_hash = hash_password($user, $pwd, $domain, MD5);
        $sha256_hash = hash_password($user, $pwd, $domain, SHA256);
    }

    $account->confirmation_key = uniqid();
    $account->email = $email;
    $account->user_agent = $user_agent;
    $account->ip_address = getIp();
    $account->activated = AUTO_ACTIVATE_ACCOUNT ? "1" : "0";
    $account->create();

    $md5_password = new Password($db);
    $md5_password->account_id = $account->id;
    $md5_password->password = $md5_hash;
    $md5_password->algorithm = MD5;
    $md5_password->create();

    $sha256_password = new Password($db);
    $sha256_password->account_id = $account->id;
    $sha256_password->password = $sha256_hash;
    $sha256_password->algorithm = SHA256;
    $sha256_password->create();

    if (CUSTOM_HOOKS) {
        hook_on_account_created($account);
    }

    if (SEND_ACTIVATION_EMAIL && EMAIL_ENABLED) {
        send_email_with_activation_link($email, $account->confirmation_key, $account->username, $account->domain, SHA256);
    } elseif (AUTO_ACTIVATE_ACCOUNT) {
        //TODO
        /*if (USE_IN_APP_PURCHASES) {
            $expiration = get_trial_expiration_date();
            db_inapp_add_account($user, $domain, $expiration);
        }*/
    }

    //We call this function to set the geoloc if enabled
    // args needed = [username, ha1, firstname, lastname, gender, subscribe, [domain], [algo]]
    //need  username + domain
    if (get_config_value(ENABLE_NEW_ACCOUNTS_GEOLOC, FALSE)) {
        return update_account_user_info($account->username, $md5_hash, null, null, "unknown", '0', $account->domain, MD5);
    }

    return OK;
}

// args = [username, key, [domain], [algo]]
function xmlrpc_activate_email_account($method, $args)
{
    $user = $args[0];
    $key = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_activate_account(" . $user . ", " . $domain . ", " . $key . ", " . $algo . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    } elseif ($account->activated != "0") {
        return ACCOUNT_ALREADY_ACTIVATED;
    }

    if (!is_key_matching($key, $account)) {
        return KEY_DOESNT_MATCH;
    }

    $account->activated = "1";
    $account->update();

    $expiration = null;
    // TODO
    /*if (USE_IN_APP_PURCHASES) {
        $expiration = get_trial_expiration_date();
        db_inapp_add_account($user, $domain, $expiration);
    }*/

    if (CUSTOM_HOOKS) {
        hook_on_account_activated($account);
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if ($password->getOne()) {
        return $password->password;
    }

    return PASSWORD_NOT_FOUND;
}

// args = [username, email, [domain]]
function xmlrpc_recover_email_account($method, $args)
{
    $username = $args[0];
    $email = $args[1];
    $domain = get_domain($args[2]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_recover_email_account(" . $username . ", " . $email . ", " . $domain . ")");

    $database = new Database();
    $db = $database->getConnection();

    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    if (strcasecmp($email, $account->email) != 0) { // Email case insensitive compare
        return EMAIL_DOESNT_MATCH;
    }

    $account->confirmation_key = uniqid();
    $account->update();

    if (SEND_ACTIVATION_EMAIL && EMAIL_ENABLED) {
        send_email_with_recover_key($email, $account->confirmation_key);
    }

    return OK;
}

// args = [username, password, new email, [domain], [algo]]
function xmlrpc_update_email($method, $args)
{
    $user = $args[0];
    $pwd = $args[1];
    $new_email = $args[2];
    $domain = get_domain($args[3]);
    $algo = get_algo($args[4]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_update_email(" . $user . ", " . $domain . ", " . $new_email . ", " . $algo . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
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

    $hashed_old_password = hash_password($user, $pwd, $domain, $algo);
    if (!password_match($password->password, $hashed_old_password)) {
        return PASSWORD_DOESNT_MATCH;
    }

    if ($account->email == $new_email) {
        Logger::getInstance()->warning("New email same as previous one");
        return EMAIL_UNCHANGED;
    }

    if (!ALLOW_SAME_EMAILS_ON_MULTILPLE_ACCOUNTS) {
        $email_account = new Account($db);
        $email_account->email = $email;
        if ($email_account->getOne()) {
            return EMAIL_TAKEN;
        }
    }

    $account->email = $new_email;
    if ($account->update()) {
        Logger::getInstance()->message("Email updated successfully");
        return OK;
    }

    return NOK;
}

// args = [username, email, ha1, [domain], [algo]]
function xmlrpc_delete_email_account($method, $args)
{
    $username = $args[0];
    $email = $args[1];
    $ha1 = $args[2];
    $domain = get_domain($args[3]);
    $algo = get_algo($args[4]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_delete_email_account(" . $username . ", " . $email . ", " . $domain . ", " . $algo . ")");

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    if ($email != $account->email) {
        return EMAIL_DOESNT_MATCH;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if ($ha1 != $password->password) {
        return PASSWORD_DOESNT_MATCH;
    }

    if ($account->delete()) {
        if ($password->delete()) {
            $alias = new Alias($db);
            $alias->account_id = $account->id;
            $alias->delete();

            $userinfo = new UserInfo($db);
            $userinfo->account_id = $account->id;
            $userinfo->delete();

            return OK;
        }
    }

    return NOK;
}

function xmlrpc_accounts_email_register_methods($server)
{
    xmlrpc_server_register_method($server, 'create_email_account', 'xmlrpc_create_email_account');// args = [username, email, [hash], useragent, [domain], [algo]], return OK
    xmlrpc_server_register_method($server, 'create_email_md5_sha256_account', 'xmlrpc_create_email_md5_sha256_account');// args = [username, email, md5_hash, sha256_hash, useragent, [domain]], return OK
    xmlrpc_server_register_method($server, 'activate_email_account', 'xmlrpc_activate_email_account');// args = [username, key, [domain], [algo]], return ha1_password
    xmlrpc_server_register_method($server, 'recover_email_account', 'xmlrpc_recover_email_account');// args = [username, email, [domain]], return OK
    xmlrpc_server_register_method($server, 'update_email', 'xmlrpc_update_email');// args = [username, password, new email, [domain], [algo]], return OK
    xmlrpc_server_register_method($server, 'delete_email_account', 'xmlrpc_delete_email_account');// args = [username, email, ha1, [domain], [algo]]
}
