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
include_once __DIR__ . '/../objects/user-info.php';

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/results_values.php';

// args = [username, ha1, [domain], [algo]]
function xmlrpc_get_email_account($method, $args) {
	$username = $args[0];
	$ha1 = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

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

	$userinfo = new UserInfo($db);
	$userinfo->account_id = $account->id;

	if (!$userinfo->getOne()) {
		return USERINFO_NOT_FOUND;
	}

	$result = array(
        "id" => $account->id,
        "username" => $account->username,
		"domain" => $account->domain,
		"email" => $account->email,
		"alias" => $account->alias,
		"activated" => $account->activated,
		"firstname" => $userinfo->firstname,
		"lastname" => $userinfo->lastname,
		"gender" => $userinfo->gender,
		"subscribe" => $userinfo->subscribe
	);
	
	return $result;
}

// args = [tel, ha1, [domain], [algo]]
function xmlrpc_get_phone_account($method, $args) {
	$phone = $args[0];
	$ha1 = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

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

	$userinfo = new UserInfo($db);
	$userinfo->account_id = $account->id;

	if (!$userinfo->getOne()) {
		return USERINFO_NOT_FOUND;
	}

	$result = array(
        "id" => $account->id,
        "username" => $account->username,
		"domain" => $account->domain,
		"email" => $account->email,
		"alias" => $account->alias,
		"activated" => $account->activated,
		"firstname" => $userinfo->firstname,
		"lastname" => $userinfo->lastname,
		"gender" => $userinfo->gender,
		"subscribe" => $userinfo->subscribe
	);
	
	return $result;
}

function xmlrpc_user_info_register_methods($server) {
	xmlrpc_server_register_method($server, 'get_email_account', 'xmlrpc_get_email_account'); // args = [username, ha1, [domain], [algo]]
	xmlrpc_server_register_method($server, 'get_phone_account', 'xmlrpc_get_phone_account'); // args = [tel, ha1, [domain], [algo]]
}

?>