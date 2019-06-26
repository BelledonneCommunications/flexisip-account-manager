<?php

function db_drop_alias_table() {
	$conn = linphonedb_connect();
	$create_req = "DROP TABLE IF EXISTS " . ALIAS_DB_TABLE;
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_create_alias_table() {
	$conn = linphonedb_connect();
	$create_req = "CREATE TABLE IF NOT EXISTS " . ALIAS_DB_TABLE . " (
					id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					account_id INTEGER(11) UNSIGNED NOT NULL,
					alias VARCHAR(64),
					domain VARCHAR(64) NOT NULL,
					PRIMARY KEY (id), UNIQUE KEY login (alias, domain))";
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_alias_delete($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("DELETE FROM " . ALIAS_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "')", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_alias_add($alias, $user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("INSERT INTO " . ALIAS_DB_TABLE . "(account_id, alias, domain) VALUES((SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'),'" . linphonedb_escape($conn, $alias) . "','" . linphonedb_escape($conn, $domain) . "')", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_alias_update($alias, $user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . ALIAS_DB_TABLE . " SET account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "') WHERE alias='" . linphonedb_escape($conn, $alias) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_alias_is_in_use($alias, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count(*) FROM " . ALIAS_DB_TABLE . " WHERE alias='" . linphonedb_escape($conn, $alias) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	$row = linphonedb_fetch($result);
	$is_in_use = $row[0] == 1;
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $is_in_use;
}

function db_alias_get($alias, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT login FROM " . ACCOUNTS_DB_TABLE . " WHERE id=(SELECT account_id FROM " . ALIAS_DB_TABLE . " WHERE alias='" . linphonedb_escape($conn, $alias) . "' AND domain='" . linphonedb_escape($conn, $domain) . "')", $conn);
	$row = linphonedb_fetch($result);
	$login = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $login;
}

function db_alias_get_reverse($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT alias FROM " . ALIAS_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "')", $conn);
	$row = linphonedb_fetch($result);
	$reverse = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $reverse;
}

// XMLRPC methods

// args = [phone, [domain]]
function xmlrpc_is_alias_used($method, $args) {
	$phone = $args[0];
	$domain = get_domain($args[1]);
	mylog("[XMLRPC] xmlrpc_is_alias_used(" . $user . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	} else if (!db_alias_is_in_use($phone, $domain)) {
		return "ERROR_ALIAS_DOESNT_EXIST";
	}

	return "OK";
}

// args = [phone, account, [domain], [lang]]
function xmlrpc_link_phone_number_with_account($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$domain = get_domain($args[2]);
	$lang = get_lang($args[3]);

	mylog("[XMLRPC] xmlrpc_link_phone_number_with_account(" . $user . ", " . $domain . ", " . $phone . ")");

	if (!check_parameter($phone)) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	/*} else if (db_alias_is_in_use($phone, $domain)) {
		return "ERROR_ALIAS_ALREADY_IN_USE";*/
	} else if (!db_account_is_existing($user, $domain)) {
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}

	if (SEND_ACTIVATION_SMS) {
		if (!SMS_API_ENABLED) {
			// This is a hack to allow testing without sending SMS
			return "OK";
		}
		$key = generate_4_digits_code();
		db_account_update_confirmation_key($user, $domain, $key);
		$ok = send_sms($phone, $key, $lang);
		return $ok;
	}
	return "ERROR_SMS_API_DISABLED";
}

// args = [phone, user, key, ha1, [domain], [algo]]
function xmlrpc_activate_phone_number_link($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$key = $args[2];
	$ha1 = $args[3];
	$domain = get_domain($args[4]);
		
	mylog("[XMLRPC] xmlrpc_activate_phone_number_link(" . $user . ", " . $domain . ", " . $phone . ", " . $key . ")");
		
	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	}
		
	if (!db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " doesn't exist");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	} else if (!db_account_is_activated($user, $domain)) {
		mylog("[ERROR] User account " . $user . " is not activated");
		return "ERROR_ACCOUNT_NOT_ACTIVATED";
	}
		
	$key_db = db_account_get_confirmation_key($user, $domain);
	if (strcmp($key, $key_db) != 0) {
		mylog("[ERROR] Key doesn't match");
		return "ERROR_KEY_DOESNT_MATCH";
	}
	$db_hashed_password = db_account_get_password($user, $domain, $args[5]);
	if (strcmp($db_hashed_password, $ha1) != 0) {
		mylog("[ERROR] old password doesn't match");
		return "ERROR_PASSWORD_DOESNT_MATCH";
	}
		
	if (db_alias_is_in_use($phone, $domain)) {
		db_alias_update($phone, $user, $domain);
	} else {
		db_alias_add($phone, $user, $domain);
	}
	return "OK";
}


// args = [phone, [domain]]
function xmlrpc_get_alias($method, $args) {
	$phone = $args[0];
	$domain = get_domain($args[1]);

	mylog("[XMLRPC] xmlrpc_get_alias(" . $phone . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	} else if (!db_alias_is_in_use($phone, $domain)) {
		return "ERROR_ALIAS_DOESNT_EXIST";
	}

	return db_alias_get($phone);
}

function xmlrpc_aliases_register_methods($server) {
	xmlrpc_server_register_method($server, 'is_alias_used', 'xmlrpc_is_alias_used');// args = [phone], return "OK"
	xmlrpc_server_register_method($server, 'link_phone_number_with_account', 'xmlrpc_link_phone_number_with_account');// args = [phone, account, [domain], [lang]], return "OK"
	xmlrpc_server_register_method($server, 'activate_phone_number_link', 'xmlrpc_activate_phone_number_link');// args = [phone, user, key, ha1, [domain]], return "OK"
	xmlrpc_server_register_method($server, 'get_alias', 'xmlrpc_get_alias');// args = [phone], return username
}

?>
