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
include_once __DIR__ . '/../objects/user_info.php';

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/results_values.php';

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_get_confirmation_key($method, $args) {
	$user = $args[0];
	$pwd = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_get_confirmation_key(" . $user . ", " . $domain . ", " . $algo . ")");

	if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if (!ALLOW_TEST_ACCOUNTS) {
		Logger::getInstance()->error ("Non test account unauthorized");
		return TEST_ACCOUNTS_DISABLED;
	} else if ($algo == NULL) {
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

	if ($algo == "clrtxt") {
		$hashed_password = $pwd;
	} else {
		$hashed_password = hash_password($user, $pwd, $domain, $algo);
	}

	if (!password_match($hashed_password, $password->password) 
		&& !password_match($pwd, $password->password)) { // This condition is specific for liblinphone tester....
		return PASSWORD_DOESNT_MATCH;
	}

	$key = $account->confirmation_key;
	Logger::getInstance()->debug("[XMLRPC] returning key = " . $key);
	return $key;
}

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_delete_account($method, $args) {
	$user = $args[0];
	$pwd = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_delete_account(" . $user . ", " . $domain . ", " . $algo . ")");

	if ($algo == NULL) {
		return ALGO_NOT_SUPPORTED;
	} else if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if (!ALLOW_TEST_ACCOUNTS) {
		return TEST_ACCOUNTS_DISABLED;
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

	if ($algo == "clrtxt") {
		$hashed_password = $pwd;
	} else {
		$hashed_password = hash_password($user, $pwd, $domain, $algo);
	}
	if (!password_match($hashed_password, $password->password) 
		&& !password_match($pwd, $password->password)) { // This condition is specific for liblinphone tester....
		return PASSWORD_DOESNT_MATCH;
	}

	$alias = new Alias($db);
	$alias->account_id = $account->id;

	$account->delete();
	$password->delete();
	$alias->delete();

	return OK;
}

// args = [username, [domain]]
function xmlrpc_is_account_used($method, $args) {
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

	return NOK;
}

// args = [username, [domain]]
function xmlrpc_is_account_activated($method, $args) {
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
		return ACCOUNT_NOT_FOUND;
	}

	Logger::getInstance()->message("Account activation status is " . $account->activated);
	if (is_activated($account->activated)) {
		return OK;
	}

	return NOK;
}

// args = [phone, [domain]]
function xmlrpc_is_phone_number_used($method, $args) {
	$phone = $args[0];
	$domain = get_domain($args[1]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_is_phone_number_used(" . $phone . ", " . $domain . ")");

	if (!check_parameter($phone, "phone")) {
		return MISSING_PHONE_PARAM;
	} else if (!startswith($phone, "+")) {
		return PHONE_NOT_E164;
	}

	$database = new Database();
	$db = $database->getConnection();

	$alias = new Alias($db);
	$alias->alias = $phone;
	$alias->domain = $domain;
	
	if ($alias->getOne()) {
		return OK_ALIAS;
	}
	
	$account = new Account($db);
	$account->username = $phone;
	$account->domain = $domain;

	if ($account->getOne()) {
		return OK_ACCOUNT;
	}

	return NOK;
}

// args = [phone, username, key, [domain], [algo]]
function xmlrpc_activate_phone_account($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$key = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_activate_phone_account(" . $user . ", " . $domain . ", " . $phone . ", " . $key . ", " . $algo . ")");

	if (!check_parameter($phone, "phone")) {
		return MISSING_PHONE_PARAM;
	} else if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if (!startswith($phone, "+")) {
		Logger::getInstance()->error("Phone doesn't start by +");
		return PHONE_NOT_E164;
	} else if ($algo == NULL) {
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

	if (!is_key_matching($key, $account)) {
		return KEY_DOESNT_MATCH;
	}

	// If this is a recovery, account is already activated, don't go through the following again
	if (!is_activated($account->activated)) {
		$expiration = NULL;
		$account->activated = "1";
		$account->update();

		$alias = new Alias($db);
		$alias->account_id = $account->id;
		$alias->alias = $phone;
		$alias->domain = $account->domain;
		$alias->create();

		if (USE_IN_APP_PURCHASES) {
			$expiration = get_trial_expiration_date();
			//db_inapp_add_account($user, $domain, $expiration);
			//TODO
		}

		if (CUSTOM_HOOKS) {
			hook_on_account_activated($account);
		}
	}

	$password = new Password($db);
	$password->account_id = $account->id;
	$password->algorithm = $algo;

	if ($password->getOne()) {
		return $password->password;
	}

	return PASSWORD_NOT_FOUND;
}

// args = [phone, [username], [password], useragent, [domain], [lang], [algo]]
function xmlrpc_create_phone_account($method, $args) {
	$phone = $args[0];
	$user = $args[1];
	$hashed_password = $args[2];
	$user_agent = $args[3];
	$domain = get_domain($args[4]);
	$lang = get_lang($args[5]);
	$algo = get_algo($args[6]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_create_phone_account(" . $user . ", " . $domain . ", " . $phone . ", " . $lang . ", " . $algo . ")");

	if (!check_parameter($phone, "phone")) {
		return MISSING_PHONE_PARAM;
	} else if (!startswith($phone, "+")) {
		mylog("[ERROR] Phone doesn't start by +");
		return PHONE_NOT_E164;
	} else if ($algo == NULL) {
		return ALGO_NOT_SUPPORTED;
	}

	if (!check_parameter($user)) {
		$user = $phone;
	}

	$recover_params  = array(
		0 => $phone,
		1 => $domain,
		2 => $lang,
	);

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $user;
	$account->domain = $domain;

	$alias = new Alias($db);
	$alias->alias = $phone;
	$alias->domain = $domain;

	if ($account->getOne()) {
		if (RECOVER_ACCOUNT_IF_EXISTS) {
			$recovered_user = xmlrpc_recover_phone_account($method, $recover_params);
			if ($recovered_user == $user) {
				return OK;
			}

			return ACCOUNT_RECOVERY_IMPOSSIBLE;
		}

		return USERNAME_TAKEN;
	} else if ($alias->getOne()) {
		if (RECOVER_ACCOUNT_IF_EXISTS) {
			$recovered_user = xmlrpc_recover_phone_account($method, $recover_params);
			if ($recovered_user == $user) {
				return OK;
			}

			return ACCOUNT_RECOVERY_IMPOSSIBLE;
		}

		return PHONE_TAKEN;
	}

	$pwd = $hashed_password;
	if (!check_parameter($hashed_password, "hashed password")) {
		$pwd = generate_password();
		$hashed_password = hash_password($user, $pwd, $domain, $algo);
	}

	$account->confirmation_key = generate_4_digits_code();
	$account->user_agent = $user_agent;
	$account->ip_address = getIp();
    $account->activated = AUTO_ACTIVATE_ACCOUNT ? "1" : "0";
	$account->create();

	$password = new Password($db);
	$password->account_id = $account->id;
	$password->password = $hashed_password;
	$password->algorithm = $algo;
	$password->create();

	if ($user != $phone) {
		$alias->account_id = $account->id;
		$alias->create();
	}

	if (CUSTOM_HOOKS) {
		hook_on_account_created($account);
	}

	if (SEND_ACTIVATION_SMS) {
		if (!SMS_API_ENABLED) {
			// This is a hack to allow testing without sending SMS
			return OK;
		}
		$ok = send_sms($phone, $account->confirmation_key, $lang);
		return $ok;
	} else if (AUTO_ACTIVATE_ACCOUNT) {
		if (USE_IN_APP_PURCHASES) {
			//TODO
			/*$expiration = get_trial_expiration_date();
			db_inapp_add_account($user, $domain, $expiration);*/
		}
	}

	return OK;
}

// args = [username, key, [domain], [algo]]
function xmlrpc_activate_email_account($method, $args) {
	$user = $args[0];
	$key = $args[1];
	$domain = get_domain($args[2]);
	$algo = get_algo($args[3]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_activate_account(" . $user . ", " . $domain . ", " . $key . ", " . $algo . ")");

	if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if ($algo == NULL) {
		return ALGO_NOT_SUPPORTED;
	}

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $user;
	$account->domain = $domain;

	if (!$account->getOne()) {
		return ACCOUNT_NOT_FOUND;
	} else if ($account->activated != "0") {
		return ACCOUNT_ALREADY_ACTIVATED;
	}

	if (!is_key_matching($key, $account)) {
		return KEY_DOESNT_MATCH;
	}

	$account->activated = "1";
	$account->update();
	
	$expiration = NULL;
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

// args = [username, email, [hash], useragent, [domain], [algo]]
function xmlrpc_create_email_account($method, $args) {
	$user = $args[0];
	$email = $args[1];
	$hashed_password = $args[2];
	$user_agent = $args[3];
	$domain = get_domain($args[4]);
	$algo = get_algo($args[5]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_create_email_account(" . $user . ", " . $domain . ", " . $email . ", " . $algo . ")");

	if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if (!check_parameter($email, "email")) {
		return MISSING_EMAIL_PARAM;
	} else if ($algo == NULL) {
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
		send_email_with_activation_link($email, $account->confirmation_key);
	} else if (AUTO_ACTIVATE_ACCOUNT) {
		//TODO
		/*if (USE_IN_APP_PURCHASES) {
			$expiration = get_trial_expiration_date();
			db_inapp_add_account($user, $domain, $expiration);
		}*/
	}

	return OK;
}

// args = [username, [domain]]
function xmlrpc_get_phone_number_for_account($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[1]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_get_phone_number_for_account(" . $user . ")");

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
			return $user;
		}
		
		return ACCOUNT_NOT_FOUND;
	}
	
	$phone = $account->alias;
	if ($phone == NULL) {
		return ALIAS_NOT_FOUND;
	}

	if (RECOVER_ACCOUNT_IF_EXISTS) {
		return ACCOUNT_NOT_FOUND;
	}

	return $phone;
}

// args = [phone, [domain], [lang]]
function xmlrpc_recover_phone_account($method, $args) {

	// Is this function overloaded
	if (XMLRPC_RECOVER_PHONE_ACCOUNT_OVERLOAD === TRUE) {
		return xmlrpc_recover_phone_account_overload($method, $args);
	}

	$phone = $args[0];
	$domain = get_domain($args[1]);
	$lang = get_lang($args[2]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_recover_phone_account(" . $phone . ", " . $domain . ", " . $lang . ")");

	if (!check_parameter($phone, "phone")) {
		return MISSING_PHONE_PARAM;
	} else if (!startswith($phone, "+")) {
		return PHONE_NOT_E164;
	}

	$database = new Database();
	$db = $database->getConnection();

	$account = new Account($db);
	$account->username = $phone;
	$account->domain = $domain;

	$alias = new Alias($db);
	$alias->alias = $phone;
	$alias->domain = $domain;

	if (!$account->getOne()) {
		if ($alias->getOne()) {
			$account->id = $alias->account_id;
			// This time the search will be done on the id instead of couple username / domain
			if (!$account->getOne()) {
				return ACCOUNT_NOT_FOUND;
			}
		} else {
			return ACCOUNT_NOT_FOUND;
		}
	}

	if (SEND_ACTIVATION_SMS) {
		$account->confirmation_key = generate_4_digits_code();
		$account->update();

		if (!SMS_API_ENABLED) {
			// This is a hack to allow testing without sending SMS
			return $account->username;
		}
		$ok = send_sms($phone, $account->confirmation_key, $lang);
		if ($ok != OK) {
			return $ok;
		}
	}

	return $account->username;
}

// args = [username, email, [domain]]
function xmlrpc_recover_email_account($method, $args) {
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

// args = [username, key, [domain], [algo]]
function xmlrpc_recover_account_from_confirmation_key($method, $args) {
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

	$password = new Password($db);
	$password->account_id = $account->id;
	$password->algorithm = $algo;

	if ($password->getOne()) {
		return $password->password;
	}

	return PASSWORD_NOT_FOUND;
}

// args = [username, old password, new password, [domain], [algo]]
function xmlrpc_update_password($method, $args) {
	$user = $args[0];
	$domain = get_domain($args[3]);
	$algo = get_algo($algo[4]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_update_password(" . $user . ", " . $domain . ", " . $algo . ")");

	if ($algo == NULL) {
		return ALGO_NOT_SUPPORTED;
	}

	$args[1] = hash_password($args[0], $args[1], $algo);
	$args[2] = hash_password($args[0], $args[2], $algo);

	return xmlrpc_update_hash("xmlrpc_update_password", $args);
}

// args = [username, old hash, new hash, [domain], [algo]]
function xmlrpc_update_hash($method, $args) {
	$user = $args[0];
	$hashed_old_password = $args[1];
	$hashed_new_password = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_update_hash(" . $user . ", " . $domain . ", " . $algo . ")");

	if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if ($algo == NULL) {
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

	$db_hashed_password = $password->password;
	if (!password_match($db_hashed_password, $hashed_old_password)) {
		return PASSWORD_DOESNT_MATCH;
	}

	$password->password = $hashed_new_password;
	if ($password->update()) {
		Logger::getInstance()->message("Password updated successfully");
		return OK;
	}
	
	return NOK;
}

// args = [username, password, new email, [domain], [algo]]
function xmlrpc_update_email($method, $args) {
	$user = $args[0];
	$pwd = $args[1];
	$new_email = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_update_email(" . $user . ", " . $domain . ", " . $new_email . ", " . $algo . ")");

	if (!check_parameter($user)) {
		return MISSING_USERNAME_PARAM;
	} else if ($algo == NULL) {
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

// args = [username, phone, ha1, [domain], [algo]]
function xmlrpc_delete_phone_account($method, $args) {
	$username = $args[0];
	$phone = $args[1];
	$ha1 = $args[2];
	$domain = get_domain($args[3]);
	$algo = get_algo($args[4]);

	Logger::getInstance()->message("[XMLRPC] xmlrpc_delete_phone_account(" . $username . ", " . $phone . ", " . $domain . ", " . $algo . ")");

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	$account->username = $username;
	$account->domain = $domain;
	
	if (!$account->getOne()) {
		return ACCOUNT_NOT_FOUND;
	}

	if ($phone != $username && $phone != $account->alias) {
		return ALIAS_DOESNT_MATCH;
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

// args = [username, email, ha1, [domain], [algo]]
function xmlrpc_delete_email_account($method, $args) {
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

// args = []
function xmlrpc_get_accounts_count($method, $args) {
	Logger::getInstance()->message("[XMLRPC] xmlrpc_get_accounts_count()");

	$database = new Database();
	$db = $database->getConnection();
	$account = new Account($db);
	return $account->getCount();
}

function xmlrpc_accounts_register_methods($server) {
	if (ALLOW_TEST_ACCOUNTS) {
		// /!\ This methods must be used for tests purposes only /!\
		xmlrpc_server_register_method($server, 'get_confirmation_key', 'xmlrpc_get_confirmation_key');// args = [user, pwd, [domain], [algo]], return confirmation_key
		xmlrpc_server_register_method($server, 'delete_account', 'xmlrpc_delete_account');// args = [user, pwd, [domain], [algo]]
	}

 	xmlrpc_server_register_method($server, 'is_account_used', 'xmlrpc_is_account_used');// args = [username, [domain]], return OK or NOK
	xmlrpc_server_register_method($server, 'is_account_activated', 'xmlrpc_is_account_activated');// args = [username, [domain]], return OK or NOK
	xmlrpc_server_register_method($server, 'is_phone_number_used', 'xmlrpc_is_phone_number_used');// args = [phone], return OK_ACCOUNT, OK_ALIAS or NOK
	xmlrpc_server_register_method($server, 'get_phone_number_for_account', 'xmlrpc_get_phone_number_for_account');// args = [username, [domain]], return a phone number or an error

	xmlrpc_server_register_method($server, 'activate_phone_account', 'xmlrpc_activate_phone_account');// args = [phone, username, key, [domain], [algo]], return ha1_password
	xmlrpc_server_register_method($server, 'create_phone_account', 'xmlrpc_create_phone_account');// args = [phone, [username], [password], useragent, [domain], [lang], [algo]], return OK
	xmlrpc_server_register_method($server, 'activate_email_account', 'xmlrpc_activate_email_account');// args = [username, key, [domain], [algo]], return ha1_password
	xmlrpc_server_register_method($server, 'create_email_account', 'xmlrpc_create_email_account');// args = [username, email, [hash], useragent, [domain], [algo]], return OK

	xmlrpc_server_register_method($server, 'recover_phone_account', 'xmlrpc_recover_phone_account');// args = [phone, [domain], [lang]], return username
	xmlrpc_server_register_method($server, 'recover_email_account', 'xmlrpc_recover_email_account');// args = [username, email, [domain]], return OK
	xmlrpc_server_register_method($server, 'recover_account_from_confirmation_key', 'xmlrpc_recover_account_from_confirmation_key');// args = [username, key, [domain], [algo]]

	xmlrpc_server_register_method($server, 'delete_phone_account', 'xmlrpc_delete_phone_account');// args = [username, phone, ha1, [domain], [algo]]
	xmlrpc_server_register_method($server, 'delete_email_account', 'xmlrpc_delete_email_account');// args = [username, email, ha1, [domain], [algo]]

	xmlrpc_server_register_method($server, 'update_password', 'xmlrpc_update_password');// args = [username, old password, new password, [domain], [algo]], return OK
	xmlrpc_server_register_method($server, 'update_hash', 'xmlrpc_update_hash');// args = [username, old hash, new hash, [domain], [algo]], return OK
	xmlrpc_server_register_method($server, 'update_email', 'xmlrpc_update_email');// args = [username, password, new email, [domain], [algo]], return OK

	xmlrpc_server_register_method($server, 'get_accounts_count', 'xmlrpc_get_accounts_count');//args = []
}

?>
