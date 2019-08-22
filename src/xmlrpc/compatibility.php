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
include_once __DIR__ . '/../objects/alias.php';

include_once __DIR__ . '/../misc/utilities.php';

// args = [identity]
function xmlrpc_compatibility_check_account_validated($method, $args) {
	// Return 1 if account is validated, else return 0

	list($login, $domain) = explode("@", $args[0]);
	if (startswith($login, "sip:")) {
		list($sip, $login) = explode(":", $login);
	}

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $login;
	$account->domain = $domain;

	if ($account->getOne()) {
		return is_activated($account->activated) ? '1' : '0';
	}

	return 0;
}

// args = [identity]
function xmlrpc_compatibility_check_account($method, $args) {
	// Return 1 if login is already used, else return 0

	list($login, $domain) = explode("@", $args[0]);
	if (startswith($login, "sip:")) {
		list($sip, $login) = explode(":", $login);
	}

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $login;
	$account->domain = $domain;

	if ($account->getOne()) {
		return 1;
	}

	return 0;
}

// args = [identity, password, email, useragent]
function xmlrpc_compatibility_create_account_with_useragent($method, $args) {
	$newargs = array($args[0], $args[1], $args[2], 0, $args[3]);
	return xmlrpc_compatibility_create_account($method, $newargs);
}

// args = [identity, password, email, newsletter, useragent?]
function xmlrpc_compatibility_create_account($method, $args) {
   	// Return 0 if account successfully created, else return -1

	list($login, $domain) = explode("@", $args[0]);
	if (startswith($login, "sip:")) {
		list($sip, $login) = explode(":", $login);
	}
	
	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $login;
	$account->domain = $domain;

	if ($account->getOne()) {
		return -1;
	}
	
	$account->email = $args[2];
	$account->confirmation_key = uniqid();
	$account->ip_address = getIp();
	$account->$user_agent = 'linphone-wizard';
	$account->activated = AUTO_ACTIVATE_ACCOUNT ? "1" : "0";
	if (count($args) == 5) {
		$account->$user_agent = $args[4];
	}
	$account->create();

	$crypted_password = hash_password($login, $args[1], $domain, "MD5");
	$password = new Password($db);
	$password->account_id = $account->id;
	$password->password = $crypted_password;
	$password->algorithm = "MD5";
	$password->create();
	
	if (SEND_ACTIVATION_EMAIL && EMAIL_ENABLED) {
		send_email_with_activation_link($account->email, $account->confirmation_key, $account->username);
	}
	
	return 0;
}

function xmlrpc_compatibility_register_methods($server) {
	xmlrpc_server_register_method($server, 'check_account', 'xmlrpc_compatibility_check_account');
	xmlrpc_server_register_method($server, 'create_account', 'xmlrpc_compatibility_create_account');
	xmlrpc_server_register_method($server, 'check_account_validated', 'xmlrpc_compatibility_check_account_validated');
	xmlrpc_server_register_method($server, 'create_account_with_useragent', 'xmlrpc_compatibility_create_account_with_useragent');
}

?>