<?php

define("CUSTOM_HOOKS", True);

function hook_on_account_created($user, $domain, $hashed_password, $email) {
	mylog("[HOOK] Account created");
}

function hook_on_account_activated($user, $domain, $expiration_date) {
	mylog("[HOOK] Account activated");
}

function hook_on_expiration_date_updated($user, $domain, $expiration_date, $payloadJson, $os) {
	mylog("[HOOK] Expiration updated");
}

?>