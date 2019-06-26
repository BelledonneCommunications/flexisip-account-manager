<?php

function db_drop_accounts_table() {
	$conn = linphonedb_connect();
	$create_req = "DROP TABLE IF EXISTS " . ACCOUNTS_DB_TABLE;
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_create_accounts_table() {
	$conn = linphonedb_connect();
	$create_req = "CREATE TABLE IF NOT EXISTS " . ACCOUNTS_DB_TABLE . " (
	id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	login VARCHAR(64) NOT NULL,
	domain VARCHAR(64) NOT NULL,
	email VARCHAR(64),
	activated VARCHAR(1) NOT NULL DEFAULT '0',
	confirmation_key VARCHAR(14) DEFAULT NULL,
	ip_address VARCHAR(39) NOT NULL,
	user_agent VARCHAR(256) NOT NULL,
	expire DATETIME,
	PRIMARY KEY (id), UNIQUE KEY login (login, domain))";
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_drop_accounts_algo_table() {
	$conn = linphonedb_connect();
	$create_req = "DROP TABLE IF EXISTS " . ACCOUNTS_ALGO_DB_TABLE;
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_create_accounts_algo_table($hash=true) {
	$conn = linphonedb_connect();
	$create_req = "CREATE TABLE IF NOT EXISTS " . ACCOUNTS_ALGO_DB_TABLE . " (
	id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	account_id INTEGER(11) UNSIGNED NOT NULL,
	password VARCHAR(255) default '',
	algorithm VARCHAR(10) default '',
	PRIMARY KEY (id), UNIQUE KEY login (account_id, algorithm)))";
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	if ($hash) {
		$result = linphonedb_query(db_migrate_accounts_algo_table(), $conn);
		linphonedb_clean($result);
	}
	linphonedb_close($conn);
}

function db_migrate_accounts_algo_table() {
	return "UPDATE " . ACCOUNTS_ALGO_DB_TABLE . " AS a, " . ACCOUNTS_ALGO_DB_TABLE . " AS b SET a.password=b.password, a.algorithm=b.algorithm WHERE a.id=b.id;";
}

function db_account_delete($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("DELETE FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "')", $conn);
	linphonedb_clean($result);
	$result = linphonedb_query("DELETE FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_create($user, $pwd, $domain, $email, $key, $ip, $ua, $algo) {
	$conn = linphonedb_connect();
        if (ALLOW_TEST_ACCOUNTS) {
                $query = "INSERT INTO " . ACCOUNTS_DB_TABLE . "(login, activated, domain, email, confirmation_key, ip_address, user_agent, expire) VALUES('" . linphonedb_escape($conn, $user) . "','0','" . linphonedb_escape($conn, $domain) . "','" . linphonedb_escape($conn, $email) . "', '" . linphonedb_escape($conn, $key) . "','" . linphonedb_escape($conn, $ip) . "','" . linphonedb_escape($conn, $ua) . "', DATE_ADD(NOW(), INTERVAL " . EXPIRATION_DELAY . " MINUTE))";
                mylog("[DEBUG] create account query = " . $query);
                $result = linphonedb_query($query, $conn);
        } else {
                $result = linphonedb_query("INSERT INTO " . ACCOUNTS_DB_TABLE . "(login, activated, domain, email, confirmation_key, ip_address, user_agent) VALUES('" . linphonedb_escape($conn, $user) . "','0','" . linphonedb_escape($conn, $domain) . "','" . linphonedb_escape($conn, $email) . "', '" . linphonedb_escape($conn, $key) . "','" . linphonedb_escape($conn, $ip) . "','" . linphonedb_escape($conn, $ua) . "')", $conn);
        }
	linphonedb_clean($result);
	if (!strcmp($algo,""))
		$algo = "MD5";
	$result = linphonedb_query("INSERT INTO " . ACCOUNTS_ALGO_DB_TABLE . " (account_id, password, algorithm) VALUES((SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'), '" . linphonedb_escape($conn, $pwd) . "', '" . linphonedb_escape($conn, $algo) . "')", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_activate($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . ACCOUNTS_DB_TABLE . " SET activated='1' WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_super_activate($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . ACCOUNTS_DB_TABLE . " SET activated='2' WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_update_confirmation_key($user, $domain, $key) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . ACCOUNTS_DB_TABLE . " SET confirmation_key='" . linphonedb_escape($conn, $key) . "' WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_update_email($user, $domain, $new_email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . ACCOUNTS_DB_TABLE . " SET email='" . linphonedb_escape($conn, $new_email) . "' WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_update_password($user, $domain, $hashed_new_password, $algo) {
	$conn = linphonedb_connect();
	if(!strcmp($algo,""))
		$algo = "MD5";
	$result = "UPDATE " . ACCOUNTS_ALGO_DB_TABLE . " SET password='" . linphonedb_escape($conn, $hashed_new_password) . "' WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "') AND algorithm='" . linphonedb_escape($conn, $algo) . "'";
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_account_is_activated($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT activated FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	$row = linphonedb_fetch($result);
	$is_activated = $row[0] == '1';
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $is_activated;
}

function db_account_is_existing($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count(*) FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "')", $conn);
	$row = linphonedb_fetch($result);
	$is_existing = $row[0] >= 1;
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $is_existing;
}

function db_account_is_email_in_use($email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count(*) FROM " . ACCOUNTS_DB_TABLE . " WHERE email='" . linphonedb_escape($conn, $email) . "'", $conn);
	$row = linphonedb_fetch($result);
	$is_in_use = $row[0] >= 1;
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $is_in_use;
}

function db_account_is_email_or_login_in_use($user_or_email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count(*) FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user_or_email) . "' OR email='" . linphonedb_escape($conn, $user_or_email) . "'", $conn);
	$row = linphonedb_fetch($result);
	$is_in_use = $row[0] >= 1;
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $is_in_use;
}

function db_account_get_confirmation_key($user, $domain) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT confirmation_key FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "'", $conn);
	$row = linphonedb_fetch($result);
	$key = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $key;
}

function db_account_get_password($user, $domain, $algo) {
	$conn = linphonedb_connect();
	if(!strcmp($algo,""))
		$algo = "MD5";
	$result = linphonedb_query("SELECT password FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user) . "' AND domain='" . linphonedb_escape($conn, $domain) . "') AND algorithm='" . linphonedb_escape($conn, $algo) . "'", $conn);
	$row = linphonedb_fetch($result);
	$password = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $password;
}

function db_account_get_login_from_login_or_email($user_or_email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT login FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user_or_email) . "' OR email='" . linphonedb_escape($conn, $user_or_email) . "'", $conn);
	$row = linphonedb_fetch($result);
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $row[0];
}

function db_account_get_email_from_login_or_email($user_or_email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT email FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $user_or_email) . "' OR email='" . linphonedb_escape($conn, $user_or_email) . "'", $conn);
	$row = linphonedb_fetch($result);
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $row[0];
}

function db_account_get_logins_from_email($email) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT login FROM " . ACCOUNTS_DB_TABLE . " WHERE email='" . linphonedb_escape($conn, $email) . "'", $conn);
	$usernames = array();
	$i = 0;
	while ($row = linphonedb_fetch($result)) {
		$usernames[$i] = $row[0];
		$i = $i + 1;
	}
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $usernames;
}

function db_get_accounts() {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT login, domain, activated, confirmation_key, ip_address, user_agent FROM " . ACCOUNTS_DB_TABLE, $conn);
	$accounts = array();
	$i = 0;
	while ($row = linphonedb_fetch($result)) {
		$account = array();
		$account['username'] = $row[0];
		$account['domain'] = $row[1];
		$account['activated'] = $row[2];
		$account['activation_code'] = $row[3];
		$account['ip_address'] = $row[4];
		$account['user_agent'] = $row[5];
		$accounts[$i] = $account;
		$i = $i + 1;
	}
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $accounts;
}

// XMLRPC methods

// args == [email]
function xmlrpc_recover_usernames_from_email($method, $args) {
	$email = $args[0];
	mylog("[XMLRPC] xmlrpc_recover_usernames_from_email(" . $email . ")");

	if (!check_parameter($email, "email")) {
		return "ERROR_EMAIL_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_email_in_use($email)) {
		$usernames = db_account_get_logins_from_email($email);
		//TODO: send email
		return "OK";
	} else {
		mylog("[ERROR] email not found in database");
		return "ERROR_EMAIL_DOESNT_EXIST";
	}
}

// args = [username or email]
function xmlrpc_send_reset_password_email($method, $args) {
	$user_or_email = $args[0];
	mylog("[XMLRPC] xmlrpc_send_reset_password_email(" . $user_or_email . ")");

	if (!check_parameter($user_or_email, "username or email")) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_email_or_login_in_use($user_or_email)) {
		$user = db_account_get_login_from_login_or_email($user_or_email);
		$email = db_account_get_email_from_login_or_email($user_or_email);
		//TODO: send email
		return "OK";
	} else {
		mylog("[ERROR] username or email not found in database");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}
}

function xmlrpc_send_activation_email($method, $args) {
	$user_or_email = $args[0];
	mylog("[XMLRPC]xmlrpc_send_activation_email(" . $user_or_email . ")" );

	if (!check_parameter($user_or_email, "username or email")) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_email_or_login_in_use($user_or_email)) {
		$user  = db_account_get_login_from_login_or_email($user_or_email);
		$email = db_account_get_email_from_login_or_email($user_or_email);
		$hash  = get_hash_from_email($email);
		delete_link_in_database($hash);
		//TODO: send email
		return "OK";
	} else {
		mylog("[ERROR] username or email not found in database");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}
}

// args = [username, old hash, new hash, [domain], [algo]]
function xmlrpc_update_hash($method, $args) {
	$user = $args[0];
	$hashed_old_password = $args[1];
	$hashed_new_password = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	mylog("[XMLRPC] xmlrpc_update_hash(" . $user . ", " . $domain . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}
	
	if (db_account_is_existing($user, $domain)) {
		$db_hashed_password = db_account_get_password($user, $domain, $algo);
		if (strcmp($db_hashed_password, $hashed_old_password) != 0) {
			mylog("[ERROR] old password doesn't match");
			return "ERROR_PASSWORD_DOESNT_MATCH";
		} else {
			db_account_update_password($user, $domain, $hashed_new_password, $algo);
			mylog("Password updated successfully");
			return "OK";
		}
	} else {
		mylog("[ERROR] username not found in database");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}
}

// args = [username, old password, new password, [domain], [algo]]
function xmlrpc_update_password($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[3]);
	$algo = get_algo($algo[4]);

	mylog("[XMLRPC] xmlrpc_update_password(" . $user . ", " . $domain . ")");

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	$args[1] = hash_password($args[0], $args[1], $algo);
	$args[2] = hash_password($args[0], $args[2], $algo);

	return xmlrpc_update_hash("xmlrpc_update_password", $args);
}

// args = [username, password, new email, [domain], [algo]]
function xmlrpc_update_email($method, $args) {
	$user = $args[0];
	$password = $args[1];
	$new_email = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	mylog("[XMLRPC] xmlrpc_update_email(" . $user . ", " . $domain . ", " . $new_email . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	if (db_account_is_existing($user, $domain)) {
		$db_hashed_password = db_account_get_password($user, $domain, $algo);
		$hashed_old_password = hash_password($user, $password, $domain, $algo);
		if (strcmp($db_hashed_password, $hashed_old_password) != 0 and strcmp($db_hashed_password, $password) != 0) {
			mylog("[ERROR] old password doesn't match");
			return "ERROR_PASSWORD_DOESNT_MATCH";
		} else {
			$old_email = db_account_get_email_from_login_or_email($user);
			if (strcmp($old_email, $new_email) == 0) {
				mylog("New email same as previous one");
				return "ERROR_EMAIL_NEW_SAME_AS_OLD";
			} else {
				if (db_account_is_email_in_use($new_email) && !ALLOW_SAME_EMAILS_ON_MULTILPLE_ACCOUNTS) {
					return "ERROR_EMAIL_ALREADY_IN_USE";
				} else {
					db_account_update_email($user, $domain, $new_email);
					mylog("Email updated successfully");
					return "OK";
				}
			}
		}
	} else {
		mylog("[ERROR] username not found in database");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}
}

// args = [username, [domain]]
function xmlrpc_is_account_activated($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[1]);
	mylog("[XMLRPC] xmlrpc_is_account_activated(" . $user . ", " . $domain . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_activated($user, $domain)) {
		mylog("User account " . $user . " / " . $domain . " is activated");
		return "OK";
	} else {
		if (db_account_is_existing($user, $domain)) {
			return "NOK";
		} else {
			mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
			return "ERROR_ACCOUNT_DOESNT_EXIST";
		}
	}
}

// args = [username, [domain]]
function xmlrpc_is_account_used($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[1]);
	mylog("[XMLRPC] xmlrpc_is_account_used(" . $user . ", " . $domain . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " exists");
		return "OK";
	} else {
		mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
		return "NOK";
	}
}

// args = [email]
function xmlrpc_is_email_used($method, $args) {
	$email = $args[0];
	mylog("[XMLRPC] xmlrpc_is_email_used(" . $email . ")");

	if (!check_parameter($email, "email")) {
		return "ERROR_EMAIL_PARAMETER_NOT_FOUND";
	}

	if (db_account_is_email_in_use($email)) {
		mylog("[ERROR] User email " . $email . " exists");
		return "OK";
	} else {
		mylog("[ERROR] User email " . $email . " doesn't exist");
		return "ERROR_EMAIL_DOESNT_EXIST";
	}
}

// args = [username, key, [domain], [algo]]
function xmlrpc_activate_email_account($method, $args) {
	$user = $args[0];
	$key = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	mylog("[XMLRPC] xmlrpc_activate_account(" . $user . ", " . $domain . ", " . $key . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	if (!db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	} else if (db_account_is_activated($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " is already activated");
		return "ERROR_ACCOUNT_ALREADY_ACTIVATED";
	}

	$key_db = db_account_get_confirmation_key($user, $domain);
	if (strcmp($key, "ERROR") == 0 or strcmp($key, $key_db) != 0) {
		if (strcmp($key_db, "ERROR") != 0) {
			db_account_update_confirmation_key($user, $domain, "ERROR");
		}
		mylog("[ERROR] Key doesn't match");
		return "ERROR_KEY_DOESNT_MATCH";
	}

	$expiration = NULL;
	db_account_activate($user, $domain);
	if (USE_IN_APP_PURCHASES) {
		$expiration = get_trial_expiration_date();
		db_inapp_add_account($user, $domain, $expiration);
	}

	if (CUSTOM_HOOKS) {
		hook_on_account_activated($user, $domain, $expiration);
	}

	$ha1 = db_account_get_password($user, $domain, $algo);
	return $ha1;
}

// args = [phone, username, key, [domain], [algo]]
function xmlrpc_activate_phone_account($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$key = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	mylog("[XMLRPC] xmlrpc_activate_phone_account(" . $user . ", " . $domain . ", " . $key . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	}
	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}
	if (!db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}

	$key_db = db_account_get_confirmation_key($user, $domain);
	if (strcmp($key, "ERROR") == 0 or strcmp($key, $key_db) != 0) {
		if (strcmp($key_db, "ERROR") != 0) {
			db_account_update_confirmation_key($user, $domain, "ERROR");
		}
		mylog("[ERROR] Key doesn't match");
		return "ERROR_KEY_DOESNT_MATCH";
	}

	// If this is a recovery, account is already activated, don't go through the following again
	if (!db_account_is_activated($user, $domain)) {
		$expiration = NULL;
		db_account_activate($user, $domain);
		db_alias_add($phone, $user, $domain);
		if (USE_IN_APP_PURCHASES) {
			$expiration = get_trial_expiration_date();
			db_inapp_add_account($user, $domain, $expiration);
		}

		if (CUSTOM_HOOKS) {
			hook_on_account_activated($user, $domain, $expiration);
		}
	}

	$ha1 = db_account_get_password($user, $domain, $algo);
	return $ha1;
}

// args = [username, email, [hash], useragent, [domain], [algo]]
function xmlrpc_create_email_account($method, $args) {
	$user = $args[0];
	$email = $args[1];
	$domain = get_domain($args[4]);
	$algo = get_algo($args[5]);

	mylog("[XMLRPC] xmlrpc_create_account(" . $user . ", " . $domain . ", " . $email . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (db_account_is_existing($user, $domain)) {
		mylog("[XMLRPC] account already in use");
		return "ERROR_ACCOUNT_ALREADY_IN_USE";
	}

	if (!check_parameter($email, "email")) {
		return "ERROR_EMAIL_PARAMETER_NOT_FOUND";
	} else if (db_account_is_email_in_use($email) && !ALLOW_SAME_EMAILS_ON_MULTILPLE_ACCOUNTS) {
		mylog("[XMLRPC] email already in use");
		return "ERROR_EMAIL_ALREADY_IN_USE";
	}
	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	if (GENERATE_PASSWORD_ENABLED) {
		$hashed_password = hash_password($user, generate_password(), $domain, $algo);
	} else {
		$hashed_password = $args[2];
	}

	$user_agent = $args[3];
	$key = uniqid();

	mylog("[XMLRPC] Create account " . $user);
	db_account_create($user, $hashed_password, $domain, $email, $key, getIp(), $user_agent, $algo);

	if (CUSTOM_HOOKS) {
		hook_on_account_created($user, $domain, $hashed_password, $email);
	}

	if (SEND_ACTIVATION_EMAIL) {
		send_email_with_activation_link($email, $key);
	} else if (AUTO_ACTIVATE_ACCOUNT) {
		db_account_activate($user, $domain);
		if (USE_IN_APP_PURCHASES) {
			$expiration = get_trial_expiration_date();
			db_inapp_add_account($user, $domain, $expiration);
		}
	}

	return "OK";
}

// args = [phone, [username], [password], useragent, [domain], [lang], [algo]]
function xmlrpc_create_phone_account($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$hashed_password = $args[2];
	$domain = get_domain($args[4]);
	$lang = get_lang($args[5]);
	$algo = get_algo($args[6]);

	mylog("[XMLRPC] xmlrpc_create_phone_account(" . $phone . ", " . $domain . ", " . $user . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return "ERROR_PHONE_ISNT_E164";
	}

	if (!check_parameter($user)) {
		$user = $phone;
	}

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	$recover_params  = array(
		0 => $phone,
		1 => $domain,
		2 => $lang,
	);
	if (db_account_is_existing($user, $domain)) {
		if (RECOVER_ACCOUNT_IF_EXISTS) {
			$recovered_user = xmlrpc_recover_phone_account($method, $recover_params);
			if ($recovered_user == $user) return "OK";
			return "ERROR_CANT_RECOVER_ACCOUNT";
		} else {
			return "ERROR_ACCOUNT_ALREADY_IN_USE";
		}
	} else if (db_alias_is_in_use($phone, $domain)) {
		if (RECOVER_ACCOUNT_IF_EXISTS) {
			$recovered_user = xmlrpc_recover_phone_account($method, $recover_params);
			if ($recovered_user == $user) return "OK";
			return "ERROR_CANT_RECOVER_ACCOUNT";
		} else {
			return "ERROR_ALIAS_ALREADY_IN_USE";
		}
	}

	$password = $hashed_password;
	if (!check_parameter($hashed_password, "hashed password")) {
		$password = generate_password();
		$hashed_password = hash_password($user, $password, $domain, $algo);
	}

	$user_agent = $args[3];
	$key = generate_4_digits_code();

	db_account_create($user, $hashed_password, $domain, NULL, $key, getIp(), $user_agent, $algo);

	if (CUSTOM_HOOKS) {
		hook_on_account_created($user, $domain, $hashed_password, NULL);
	}

	if (SEND_ACTIVATION_SMS) {
		if (!SMS_API_ENABLED) {
			// This is a hack to allow testing without sending SMS
			return "OK";
		}
		$ok = send_sms($phone, $key, $lang);
		return $ok;
	} else if (AUTO_ACTIVATE_ACCOUNT) {
		db_account_activate($user, $domain);
		if (USE_IN_APP_PURCHASES) {
			$expiration = get_trial_expiration_date();
			db_inapp_add_account($user, $domain, $expiration);
		}
	}

	return "OK";
}

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_get_confirmation_key($method, $args) {
	$user = $args[0];
	$password = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	mylog("[XMLRPC] xmlrpc_get_confirmation_key(" . $user . ", " . $domain . ")");

	if (!check_parameter($user)) {
		mylog("[ERROR] Username parameter not found");
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (!ALLOW_TEST_ACCOUNTS) {
		mylog("[ERROR] Non test account unauthorized");
		return "ERROR_NON_TEST_ACCOUNTS_UNAUTHORIZED";
	}

	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	if (!db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	} else {
		$hashed_password = hash_password($user, $password, $domain, $algo);
		$db_hashed_password = db_account_get_password($user, $domain, $algo);
		if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
			mylog("[ERROR] Password doesn't match");
			return "ERROR_PASSWORD_DOESNT_MATCH";
		}
	}

	$key = db_account_get_confirmation_key($user, $domain);
	mylog("[XMLRPC] returning key = " . $key);
	return $key;
}

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_delete_account($method, $args) {
	$user = $args[0];
	$password = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	mylog("[XMLRPC] xmlrpc_delete_account(" . $user . ", " . $domain . ")");
	if ($algo == NULL) {
		return "ERROR_ALGO_NOT_SUPPORTED";
	}

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	} else if (!ALLOW_TEST_ACCOUNTS) {
		return "ERROR_NON_TEST_ACCOUNTS_UNAUTHORIZED";
	}

	if (!db_account_is_existing($user, $domain)) {
		mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	} else {
		$hashed_password = hash_password($user, $password, $domain, $algo);
		$db_hashed_password = db_account_get_password($user, $domain, $algo);
		if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
			mylog("[ERROR] Password doesn't match");
			return "ERROR_PASSWORD_DOESNT_MATCH";
		}
	}
	db_alias_delete($user, $domain);
	db_account_delete($user, $domain);
	return "OK";
}

// args = [phone, [domain]]
function xmlrpc_is_phone_number_used($method, $args) {
	$phone = $args[0];
	$domain = get_domain($args[1]);

	mylog("[XMLRPC] xmlrpc_is_phone_number_used(" . $phone . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		return "ERROR_PHONE_ISNT_E164";
	}

	if (db_account_is_existing($phone, $domain)) {
		return "OK_ACCOUNT";
	} else if (db_alias_is_in_use($phone, $domain)) {
		return "OK_ALIAS";
	}

	return "NOK";
}

// args = [username, [domain]]
function xmlrpc_get_phone_number_for_account($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[1]);

	mylog("[XMLRPC] xmlrpc_get_phone_number_for_account(" . $user . ")");

	if (!check_parameter($user)) {
		return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
	}

	if (!db_account_is_existing($user, $domain)) {
		if (db_alias_is_in_use($user, $domain)) {
			return $user;
		}
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}
	
	$phone = db_alias_get_reverse($user, $domain);
	if ($phone == NULL) {
		return "ERROR_ALIAS_DOESNT_EXIST";
	}

	if (RECOVER_ACCOUNT_IF_EXISTS) {
		return "ERROR_ACCOUNT_DOESNT_EXIST";
	}

	return $phone;
}

// args = [phone, [domain], [lang]]
function xmlrpc_recover_phone_account($method, $args) {
	$phone = $args[0];
	$domain = get_domain($args[1]);
	$lang = get_lang($args[2]);

	mylog("[XMLRPC] xmlrpc_recover_phone_account(" . $phone . ")");

	if (!check_parameter($phone, "phone")) {
		return "ERROR_PHONE_PARAMETER_NOT_FOUND";
	} else if (!startswith($phone, "+")) {
		return "ERROR_PHONE_ISNT_E164";
	}

	$user = NULL;
	if (db_alias_is_in_use($phone, $domain)) {
		$user = db_alias_get($phone, $domain);
	}
	if ($user != NULL || db_account_is_existing($phone, $domain)) {
		if ($user == NULL) {
			$user = $phone;
		}
		if (SEND_ACTIVATION_SMS) {
			$key = generate_4_digits_code();
			db_account_update_confirmation_key($user, $domain, $key);

			$ok = send_sms($phone, $key, $lang);
			if ($ok != "OK") {
				return $ok;
			}
		}
		return $user;
	}
	return "ERROR_ACCOUNT_DOESNT_EXIST";
}

function xmlrpc_accounts_register_methods($server) {
	//TODO FIXME remove this methods later as it's a security risk
	// /!\ This methods must be used for tests purposes only /!\
	xmlrpc_server_register_method($server, 'get_confirmation_key', 'xmlrpc_get_confirmation_key');// args = [user, pwd, [domain]], return confirmation_key
	xmlrpc_server_register_method($server, 'delete_account', 'xmlrpc_delete_account');// args = [user, pwd, [domain]]

 	xmlrpc_server_register_method($server, 'is_account_used', 'xmlrpc_is_account_used');// args = [username, [domain]], return "OK" or "NOK"
	xmlrpc_server_register_method($server, 'is_account_activated', 'xmlrpc_is_account_activated');// args = [username, [domain]], return "OK" or "NOK"
	xmlrpc_server_register_method($server, 'is_phone_number_used', 'xmlrpc_is_phone_number_used');// args = [phone], return "OK_ACCOUNT", "OK_ALIAS" or "NOK"
	xmlrpc_server_register_method($server, 'activate_phone_account', 'xmlrpc_activate_phone_account');// args = [phone, username, key, [domain]], return ha1_password
	xmlrpc_server_register_method($server, 'create_phone_account', 'xmlrpc_create_phone_account');// args = [phone, [username], [password], useragent, [domain], [lang]], return "OK"
	xmlrpc_server_register_method($server, 'activate_email_account', 'xmlrpc_activate_email_account');// args = [username, key, [domain]], return ha1_password
	xmlrpc_server_register_method($server, 'create_email_account', 'xmlrpc_create_email_account');// args = [username, email, [hash], useragent, [domain]], return "OK"
	xmlrpc_server_register_method($server, 'get_phone_number_for_account', 'xmlrpc_get_phone_number_for_account');// args = [username, [domain]], return a phone number or an error
	xmlrpc_server_register_method($server, 'recover_phone_account', 'xmlrpc_recover_phone_account');// args = [phone, [domain], [lang]], return username

	xmlrpc_server_register_method($server, 'update_password', 'xmlrpc_update_password');// args = [username, old password, new password, [domain]], return "OK"
	xmlrpc_server_register_method($server, 'update_hash', 'xmlrpc_update_hash');// args = [username, old hash, new hash, [domain]], return "OK"
	xmlrpc_server_register_method($server, 'update_email', 'xmlrpc_update_email');// args = [username, password, new email, [domain]], return "OK"
}

?>
