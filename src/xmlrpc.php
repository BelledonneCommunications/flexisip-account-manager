<?php

define("PATH_TO_CONFIG", "/etc/flexisip-account-manager/");
include PATH_TO_CONFIG . "xmlrpc.conf";

include "logging.php";
include "utilities.php";
include "hooks.php";
include "authentication.php";
include "mysqli-db.php";

if (SMS_API_ENABLED) {
	include "xmlrpc-sms.php";
}

include "xmlrpc-accounts.php";
include "xmlrpc-aliases.php";
include "xmlrpc-inapp.php";
include "xmlrpc-devices.php";
include "xmlrpc-compatibility.php";

date_default_timezone_set(DEFAULT_TIMEZONE);
mylog("[DEBUG] Timezone set to " . DEFAULT_TIMEZONE);

$request = file_get_contents("php://input");
if (empty($request)) mylog("[DEBUG] request is empty");

$server = xmlrpc_server_create();
if (!$server) die("Couldn't create server");

if (!$request) {
	if ($argc >= 2) {
		$arg1 = $argv[1];
		if (strcmp($arg1, "create_tables") == 0) {
			db_create_inapp_table();
			db_create_alias_table();
			db_create_accounts_table();
			db_create_accounts_algo_table();
			db_create_devices_table();
			db_create_sms_table();
			echo "Tables have been created!\r\n";
		} else if (strcmp($arg1, "delete_tables") == 0) {
			db_drop_inapp_table();
			db_drop_alias_table();
			db_drop_accounts_table();
			db_drop_accounts_algo_table();
			db_drop_devices_table();
			db_drop_sms_table();
			echo "Tables have been deleted!\r\n";
		} else if (strcmp($arg1, "create_algo_table") == 0) {
			db_create_accounts_algo_table();
			echo "Algo table has been created!\r\n";
		} else if (strcmp($arg1, "drop_algo_table") == 0) {
			db_drop_accounts_algo_table();
			echo "Algo table has been deleted!\r\n";
		}
		exit;
	}
	mylog("[DEBUG] No request found");
	exit;
}

// XMLRPC requests that do not require authentication
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
);

$headers = getallheaders();
$xml = simplexml_load_string($request);
$request_type = $xml->methodName;

// Get authentication header if there is one
if (!empty($headers['Auth-Digest'])) {
	mylog("Auth-Digest = " . $headers['Auth-Digest']);
	$authorization = $headers['Auth-Digest'];
} elseif (!empty($headers['Authorization'])) {
	mylog("Authorization = " . $headers['Authorization']);
	$authorization = $headers['Authorization'];
}

// Authentication
if (in_array($request_type, $unauthenticated_requests) == FALSE) {
	if (!empty($authorization)) {
		$authentication_status = authenticate(AUTH_REALM);

		if ($authentication_status == TRUE) {
			mylog("[DEBUG] Authentication successful for " . $headers['From']);
		} else {
			mylog("[DEBUG] Authentication failed for " . $headers['From']);
			request_authentication(AUTH_REALM);
		}
	} else {
		mylog("[DEBUG] No authentication header for " . $headers['From']);
		request_authentication(AUTH_REALM);
	}
}

xmlrpc_accounts_register_methods($server);
xmlrpc_aliases_register_methods($server);
xmlrpc_inapp_register_methods($server);
xmlrpc_misc_register_methods($server);
xmlrpc_compatibility_register_methods($server);

if ($request) {
	$options = array('output_type' => 'xml', 'version' => 'auto');
	echo xmlrpc_server_call_method($server, $request, null, $options);
}

?>
