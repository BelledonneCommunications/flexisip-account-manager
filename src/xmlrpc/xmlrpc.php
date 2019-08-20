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

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/authentication.php';
include_once __DIR__ . '/accounts.php';
include_once __DIR__ . '/aliases.php';
include_once __DIR__ . '/devices.php';
include_once __DIR__ . '/user_info.php';
include_once __DIR__ . '/compatibility.php';

$request = file_get_contents("php://input");
if (empty($request)) Logger::getInstance()->error("Request is empty");

$server = xmlrpc_server_create();
if (!$server) die("Couldn't create server");

if (USE_DIGEST_AUTH) {
	$headers = getallheaders();
	$xml = simplexml_load_string($request);
	$request_type = $xml->methodName;

	$unauthenticated_requests = array(
		// account
		0 => 'create_email_account',
		1 => 'create_phone_account',
		2 => 'get_confirmation_key',
		3 => 'activate_email_account',
		4 => 'activate_phone_account',
		5 => 'recover_phone_account',
		6 => 'get_phone_number_for_account',
		7 => 'is_account_activated',
	
		// aliases
		8 => 'is_alias_used',
	
		// inapp
		9 => 'check_payload_signature',
	
		// misc
		10 => 'add_ec_calibration_result',
	
		// compatibility
		11 => 'create_account',
		12 => 'create_account_with_useragent',

		// user_info
		13 => 'get_account_by_confirmation_key',
	);

	// Get authentication header if there is one
	if (!empty($headers['Auth-Digest'])) {
		Logger::getInstance()->debug("Auth-Digest = " . $headers['Auth-Digest']);
		$authorization = $headers['Auth-Digest'];
	} elseif (!empty($headers['Authorization'])) {
		Logger::getInstance()->debug("Authorization = " . $headers['Authorization']);
		$authorization = $headers['Authorization'];
	}

	// Authentication
	if (in_array($request_type, $unauthenticated_requests) == FALSE) {
		if (!empty($authorization)) {
			$authentication_status = authenticate(AUTH_REALM);

			if ($authentication_status == TRUE) {
				Logger::getInstance()->debug("Authentication successful for " . $headers['From']);
			} else {
				Logger::getInstance()->debug("Authentication failed for " . $headers['From']);
				request_authentication(AUTH_REALM);
			}
		} else {
			Logger::getInstance()->debug("No authentication header for " . $headers['From']);
			request_authentication(AUTH_REALM);
		}
	}
}

xmlrpc_accounts_register_methods($server);
xmlrpc_aliases_register_methods($server);
xmlrpc_devices_register_methods($server);
if (USE_IN_APP_PURCHASES) {
	xmlrpc_inapp_register_methods($server);
}
xmlrpc_user_info_register_methods($server);
xmlrpc_compatibility_register_methods($server);

if ($request) {
	$options = array('output_type' => 'xml', 'version' => 'auto');
	echo xmlrpc_server_call_method($server, $request, null, $options);
}

?>