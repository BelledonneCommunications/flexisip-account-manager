<?php


// args = [identity]
function xmlrpc_compatibility_check_account_validated($method, $args) {
	// Return 1 if account is validated, else return 0

	list($login, $domain) = explode("@", $args[0]);
	if (startswith($login, "sip:")) {
		list($sip, $login) = explode(":", $login);
	}

	if (db_account_is_activated($login, $domain)) {
		return 1;
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

	if (db_account_is_existing($login, $domain)) {
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
	
	if (db_account_is_existing($login, $domain)) {
		return -1;
	}
	
	$password = $args[1];
	$email = $args[2];
  	$confirmation_key = uniqid();
	$ip = getIp();
	$crypted_password = hash_password($login, $password, $domain);
	$useragent = 'linphone-wizard';
	if (count($args) == 5) {
		$useragent = $args[4];
	}
	
	db_account_create($login, $crypted_password, $domain, $email, $confirmation_key, $ip, $useragent);
	send_email_with_activation_link($email, $confirmation_key);
	return 0;
}

function xmlrpc_compatibility_register_methods($server) {
	xmlrpc_server_register_method($server, 'check_account', 'xmlrpc_compatibility_check_account');
	xmlrpc_server_register_method($server, 'create_account', 'xmlrpc_compatibility_create_account');
	xmlrpc_server_register_method($server, 'check_account_validated', 'xmlrpc_compatibility_check_account_validated');
	xmlrpc_server_register_method($server, 'create_account_with_useragent', 'xmlrpc_compatibility_create_account_with_useragent');
}

?>