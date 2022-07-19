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

include_once __DIR__ . '/../misc/results_values.php';

// args = [username, old hash, new hash, [domain], [algo]]
function xmlrpc_update_password($method, $args)
{
    $username = $args[0];
    $hashed_old_password = $args[1];
    $hashed_new_password = $args[2];
    $domain = get_domain($args[3]);
    $algo = get_algo($args[4]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_update_password(" . $username . ", " . $domain . ", " . $algo . ")");

    if (!check_parameter($username)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!check_parameter($hashed_old_password, "old password")) {
        return MISSING_OLD_HASH;
    } elseif (!check_parameter($hashed_new_password, "md5 password")) {
        return MISSING_NEW_HASH;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

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
    $password->password = $hashed_old_password;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_DOESNT_MATCH;
    }

    $password->password = $hashed_new_password;
    if ($password->update()) {
        Logger::getInstance()->message("Password updated successfully");
        return OK;
    }

    return NOK;
}

// args = [username, old hash, md5_hash, sha256_hash, [domain]]
function xmlrpc_update_passwords($method, $args)
{
    $username = $args[0];
    $hashed_password = $args[1];
    $md5_hashed_password = $args[2];
    $sha256_hashed_password = $args[3];
    $domain = get_domain($args[4]);

    if (!check_parameter($username)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!check_parameter($hashed_password, "old password")) {
        return MISSING_OLD_HASH;
    } elseif (!check_parameter($md5_hashed_password, "md5 password")) {
        return MISSING_MD5_HASH;
    } elseif (!check_parameter($sha256_hashed_password, "sha256 password")) {
        return MISSING_SHA256_HASH;
    }

    Logger::getInstance()->message("[XMLRPC] xmlrpc_update_passwords(" . $username . ", " . $domain . ")");

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
    $password->password = $hashed_password;

    if (!$password->getOne()) {
        return PASSWORD_DOESNT_MATCH;
    }

    // Old password is OK, now let's hash the new password for both MD5 and SHA-256

    $md5_password = new Password($db);
    $md5_password->account_id = $account->id;
    $md5_password->algorithm = MD5;
    $md5_exists = $md5_password->GetOne();
    $md5_password->password = $md5_hashed_password;
    if ($md5_exists) {
        $md5_password->update();
    } else {
        $md5_password->create();
    }

    $sha256_password = new Password($db);
    $sha256_password->account_id = $account->id;
    $sha256_password->algorithm = SHA256;
    $sha256_exists = $sha256_password->GetOne();
    $sha256_password->password = $sha256_hashed_password;
    if ($sha256_exists) {
        $sha256_password->update();
    } else {
        $sha256_password->create();
    }

    return OK;
}

// args = [username, old md5 hash, sha256 hash, [domain]]
function xmlrpc_upgrade_password($method, $args)
{
    $username = $args[0];
    $md5_hash = $args[1];
    $sha256_hash = $args[2];
    $domain = get_domain($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_upgrade_password(" . $username . ", " . $domain . ")");

    if (!check_parameter($username)) {
        return MISSING_USERNAME_PARAM;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $sha256_password = new Password($db);
    $sha256_password->account_id = $account->id;
    $sha256_password->algorithm = SHA256;

    // There is already a SHA-256 password for this account, abort upgrade
    if ($sha256_password->getOne()) {
        return SHA256_PASSWORD_ALREADY_EXISTS;
    }

    $md5_password = new Password($db);
    $md5_password->account_id = $account->id;
    $md5_password->password = $md5_hash;
    $md5_password->algorithm = MD5;

    // No MD5 or wrong hash, abort
    if (!$md5_password->getOne()) {
        return PASSWORD_DOESNT_MATCH;
    }

    // Upgrade MD5 to SHA-256
    $md5_password->password = $sha256_hash;
    $md5_password->algorithm = SHA256;
    if ($md5_password->update()) {
        Logger::getInstance()->message("Password upgraded successfully");
        return OK;
    }

    return NOK;
}

// args = [username, hash, [domain]]
function xmlrpc_check_authentication($method, $args)
{
    $username = $args[0];
    $hashed_password = $args[1];
    $domain = get_domain($args[2]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_check_authentication(" . $username . ", " . $domain . ")");

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
    $password->password = $hashed_password;

    if (!$password->getOne()) {
        return PASSWORD_DOESNT_MATCH;
    }

    return OK;
}

// args = [username, md5_hash, sha256_hash, [domain]]
function xmlrpc_check_authentication_and_upgrade_password($method, $args)
{
    $username = $args[0];
    $md5_hashed_password = $args[1];
    $sha256_hashed_password = $args[2];
    $domain = get_domain($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_check_authentication_and_upgrade_password(" . $username . ", " . $domain . ")");

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $sha256_password = new Password($db);
    $sha256_password->account_id = $account->id;
    $sha256_password->password = $sha256_hashed_password;
    $sha256_password->algorithm = SHA256;

    if (!$sha256_password->getOne()) {
        // SHA-256 doesn't exists or doesn't match, let's try MD5
        $md5_password = new Password($db);
        $md5_password->account_id = $account->id;
        $md5_password->password = $md5_hashed_password;
        $md5_password->algorithm = MD5;

        if (!$md5_password->getOne()) {
            return PASSWORD_DOESNT_MATCH;
        }

        if ($sha256_password->id > 0) {
            // SHA-256 exists, let's update it
            $sha256_password->update();
        } else {
            $sha256_password->create();
        }
    }

    return OK;
}

function xmlrpc_passwords_register_methods($server)
{
    // The below two methods are the same but with different names, update_hash was the previous one and is kept here for the time being for compatibility purposes
    xmlrpc_server_register_method($server, 'update_hash', 'xmlrpc_update_password');// args = [username, old hash, new hash, [domain], [algo]], return OK
    xmlrpc_server_register_method($server, 'update_password', 'xmlrpc_update_password');// args = [username, old hash, new hash, [domain], [algo]], return OK
    xmlrpc_server_register_method($server, 'update_passwords', 'xmlrpc_update_passwords');// args = [username, old hash, md5_hash, sha256_hash, [domain]]
    xmlrpc_server_register_method($server, 'upgrade_password', 'xmlrpc_upgrade_password');// args = [username, old md5 hash, sha256 hash, [domain]]

    xmlrpc_server_register_method($server, 'check_authentication', 'xmlrpc_check_authentication');// args = [username, hash, [domain]]
    xmlrpc_server_register_method($server, 'check_authentication_and_upgrade_password', 'xmlrpc_check_authentication_and_upgrade_password');// args = [username, md5_hash, sha256_hash, [domain]]
}
