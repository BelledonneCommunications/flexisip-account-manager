<?php

define("PATH_TO_CONFIG", "/etc/flexisip-account-manager/");
include PATH_TO_CONFIG . "xmlrpc.conf";

include "mysqli-db.php";
include "xmlrpc-sms.php";
include "xmlrpc-accounts.php";
include "xmlrpc-aliases.php";
include "xmlrpc-inapp.php";
include "xmlrpc-devices.php";
include "xmlrpc-compatibility.php";
include "utilities.php";
include "logging.php";
include "hooks.php";

if ($argc == 3) {
	$phone = $argv[1];
	$msg = $argv[2];
	send_sms_ovh($phone, $msg, NULL);
} else {
	echo "php ./sms_sender.php <phone number> <message>\r\n";
}

?>
