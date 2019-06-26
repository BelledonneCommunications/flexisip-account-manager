<?php

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

// Internationalization

function get_lang($param) {
	if ($param == NULL || $param == "") {
		mylog("[WARN] lang parameter wasn't found, use US");
		return 'US';
	} else if (strlen($param) > 2) {
		$param = substr($param, 0, 2);
	}
	return strtoupper($param);
}

function get_sms_string_for_lang($lang) {
	global $SMS_OVH_TEMPLATE;
	if (isset($SMS_OVH_TEMPLATE[$lang])) {
		return $SMS_OVH_TEMPLATE[$lang];
	}
	mylog("[WARN] SMS template not found for lang " . $lang . ", using US template");
	return SMS_OVH_US_TEMPLATE;
}

// Password
	
function hash_password($user, $password, $domain, $algo) {
	if(strcmp($algo,"")==0 || strcmp($algo,"MD5")==0) $hashed_password = hash("md5", $user . ":" . $domain . ":" . $password);
	if(strcmp($algo,"SHA-256")==0) $hashed_password = hash("sha256", $user . ":" . $domain . ":" . $password);
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
	
function get_trial_expiration_date() {
	$expiration_date = new DateTime('now +' . TRIAL_DURATION_DAYS . ' days');
	$expiration = $expiration_date->getTimestamp() * 1000;
	return $expiration;
}
	
function check_parameter($param, $param_name = "username") {
	if ($param == NULL || $param == "") {
		mylog("[WARN] " . $param_name . " is missing");
		return false;
	}
	return true;
}
	
function check_algo($algo) {
	if (strcmp($algo,"")==0 || strcmp($algo,"MD5")==0 || strcmp($algo,"SHA-256")==0 || strcmp($algo,"clrtxt")==0){
		return true;
	}
	mylog("[ERROR] Algo " . $algo . " is not supported");
	return false;
}

function get_domain($param) {
	if ($param == NULL || $param == "") {
		mylog("[WARN] domain parameter wasn't found, use " . SIP_DOMAIN);
		$param = SIP_DOMAIN;
	}
	return $param;
}

// Email

function send_email($email, $subject, $text, $html) {
	$site = EMAIL_SITE;
	$from = EMAIL_FROM_ADDR;
	$name = EMAIL_FROM_NAME;
	$to = $email;
	$from = $name." <".$from.">";
 
	$limite = "_----------=_parties_".md5(uniqid (rand()));

	$headers  = "Reply-to: ".$from."\n";
	$headers .= "From: ".$from."\n";
	$headers .= "Return-Path: ".$from."\n";
	$headers .= "X-Sender: <".$site.">\n";
	$headers .= "X-Mailer: PHP\n";
	$headers .= "X-auth-smtp-user: ".$from." \n";
	$headers .= "X-abuse-contact: ".$from." \n";
	$headers .= "X-auth-smtp-user: ".$from." \n";
	$headers .= "X-abuse-contact: ".$from." \n";
	$headers .= "Date: ".date("D, j M Y G:i:s O")."\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: multipart/alternative; boundary=\"".$limite."\"";

	$message = "";

	$message .= "--".$limite."\n";
	$message .= "Content-Type: text/plain; charset=\"utf-8\"\n";
	$message .= "Content-Transfer-Encoding: 8bit\n\n";
	$message .= $text;

	$message .= "\n\n--".$limite."\n";
	$message .= "Content-Type: text/html; charset=\"utf-8\"\n";
	$message .= "Content-Transfer-Encoding: 8bit;\n\n";
	$message .= $html;

	$message .= "\n--".$limite."--";

	$params = "-f" . EMAIL_FROM_ADDR . " -O DeliveryMode=b";
	$result = mail($email, $subject, $message, $headers, $params);
	if (!$result) {
		mylog("[ERROR][EMAIL] Email delivery declined !");
	}
}

function send_email_with_activation_link($email, $key) {
	if( !EMAIL_ENABLED ){
		mylog("[WARN] [EMAIL] Emails are disabled");
		return "WARNING_EMAILS_DISABLED";
	}

	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	
	$link = $pageURL . EMAIL_ACTIVATION_LINK;
	$link = str_replace("%key%", $key, $link);
	mylog("[EMAIL] Activation link is " . $link);
	
	$body = str_replace("%link%", $link, EMAIL_ACTIVATION_BODY);
	mylog("[EMAIL] Activation body is " . $body);
	$body_html = str_replace("%link%", $link, EMAIL_ACTIVATION_BODY_HTML);
	mylog("[EMAIL] Activation html body is " . $body_html);
	
	send_email($email, EMAIL_ACTIVATION_SUBJECT, $body, $body_html);
	mylog("[EMAIL] Email sent to email " . $email . " to activate the account");
}

?>
