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

include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/logging.php';
if (EMAIL_ENABLED) {
	include_once __DIR__ . '/email.php';
}
if (SMS_API_ENABLED) {
	include_once __DIR__ . '/sms.php';
}

function startswith($hay, $needle) {
	return substr($hay, 0, strlen($needle)) === $needle;
}

function endswith($hay, $needle) {
	return $needle === "" || (($temp = strlen($hay) - strlen($needle)) >= 0 and strpos($hay, $needle, $temp) !== FALSE);
}

function getIp() {
	$ip = $_SERVER['REMOTE_ADDR'];
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	return $ip;
}
	
function get_trial_expiration_date() {
	$expiration_date = new DateTime('now +' . TRIAL_DURATION_DAYS . ' days');
	$expiration = $expiration_date->getTimestamp() * 1000;
	return $expiration;
}

function is_activated($activated) {
	return $activated == "1" || $activated == 1 || $activated;
}

// XMLRPC parameters
	
function check_parameter($param, $param_name = "username") {
	if ($param == NULL || $param == "") {
		Logger::getInstance()->warning("Parameter " . $param_name . " is missing");
		return false;
	}
	return true;
}
	
function get_algo($algo) {
	if ($algo == NULL || $algo == "") {
		Logger::getInstance()->warning("Algo parameter wasn't found, assume MD5");
		return "MD5";
	}
	if ($algo == "MD5" || $algo == "SHA-256" || $algo == "clrtxt") {
		return $algo;
	}
	Logger::getInstance()->error("Algo " . $algo . " is not supported");
	return NULL;
}

function get_domain($param) {
	if ($param == NULL || $param == "") {
		Logger::getInstance()->warning("Domain parameter wasn't found, assume " . SIP_DOMAIN);
		$param = SIP_DOMAIN;
	}
	return $param;
}

function get_lang($param) {
	if ($param == NULL || $param == "") {
		Logger::getInstance()->warning("lang parameter wasn't found, use US");
		return 'US';
	} else if (strlen($param) > 2) {
		$param = substr($param, 0, 2);
	}
	return strtoupper($param);
}

// Password
	
function hash_password($user, $password, $domain, $algo) {
	$hashed_password = $password;
	if ($algo == "" || $algo == "MD5") $hashed_password = hash("md5", $user . ":" . $domain . ":" . $password);
	if ($algo == "SHA-256") $hashed_password = hash("sha256", $user . ":" . $domain . ":" . $password);
	return $hashed_password;
}
	
function generate_password() {
	$generated_password = substr(str_shuffle(GENERATED_PASSWORD_CHARACTERS), 0, GENERATED_PASSWORD_LENGTH);
	return $generated_password;
}
	
function generate_4_digits_code() {
	$generated_password = substr(str_shuffle("0123456789"), 0, 4);
	return $generated_password;
}

function password_match($pwd1, $pwd2) {
	if ($pwd1 != $pwd2) {
		Logger::getInstance()->error("Password doesn't match");
		return false;
	}
	return true;
}

?>
