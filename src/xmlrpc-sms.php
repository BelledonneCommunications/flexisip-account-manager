<?php

// The following can't be put inside a function...
// So comment it out if not using the OVH SMS API
require __DIR__ . '/vendor/autoload.php';
use \Ovh\Sms\SmsApi;

function db_drop_sms_table() {
	$conn = linphonedb_connect();
	$create_req = "DROP TABLE IF EXISTS " . SMS_DB_TABLE;
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_create_sms_table() {
	$conn = linphonedb_connect();
	$create_req = "CREATE TABLE IF NOT EXISTS " . SMS_DB_TABLE . " (
					id INTEGER(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
					phone VARCHAR(64),
					last_sms BIGINT(15) UNSIGNED DEFAULT 0,
					count TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (id), UNIQUE KEY login (phone))";
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_has_sms_already_been_sent_to($phone) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count(*) FROM " . SMS_DB_TABLE . " WHERE phone='" . linphonedb_escape($conn, $phone) . "'", $conn);
	$row = linphonedb_fetch($result);
	$already_sent = $row[0] >= 1;
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $already_sent;
}

function db_insert_sms($phone, $time) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("INSERT INTO " . SMS_DB_TABLE . "(phone, last_sms, count) VALUES('" . linphonedb_escape($conn, $phone) . "', " . linphonedb_escape($conn, $time) . ", 1)", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_get_sms_count($phone) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT count FROM " . SMS_DB_TABLE . " WHERE phone='" . linphonedb_escape($conn, $phone) . "'", $conn);
	$row = linphonedb_fetch($result);
	$sms_count = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $sms_count;
}

function db_get_last_sms($phone) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("SELECT last_sms FROM " . SMS_DB_TABLE . " WHERE phone='" . linphonedb_escape($conn, $phone) . "'", $conn);
	$row = linphonedb_fetch($result);
	$last_sms = $row[0];
	linphonedb_clean($result);
	linphonedb_close($conn);
	return $last_sms;
}

function db_update_sms($phone, $time, $count) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("UPDATE " . SMS_DB_TABLE . " SET last_sms=" . linphonedb_escape($conn, $time)  . ", count=" . linphonedb_escape($conn, $count) . " WHERE phone='" . linphonedb_escape($conn, $phone) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_delete_sms($phone) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("DELETE FROM " . SMS_DB_TABLE . " WHERE phone='" . linphonedb_escape($conn, $phone) . "'", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function send_sms_ovh($phone, $key, $lang) {
	if (!SMS_API_ENABLED) {
		mylog("[WARN][SMS] SMS API disabled");
		return "WARNING_SMS_API_DISABLED";
	}
	
	$sms = new SmsApi(SMS_OVH_API_KEY, SMS_OVH_API_SECRET, SMS_OVH_ENDPOINT, SMS_OVH_CONSUMER_KEY);
	$accounts = $sms->getAccounts();
	$sms->setAccount($accounts[0]);
	if (SMS_USE_SENDER) {
		$senders = $sms->getSenders();
		
		/* The account must be validated in the OVH interface and by OVH itself */
		if (count($senders) == 0) {
			mylog("[WARN][SMS] No sender found, creating one " . SMS_OVH_SENDER . " / " . SMS_OVH_REASON . " : " . SMS_OVH_DESC);
			$sms->addSender(SMS_OVH_SENDER, SMS_OVH_REASON, SMS_OVH_DESC);
			$senders = $sms->getSenders();
		}
	}

	$message = $sms->createMessage();
	if (SMS_USE_SENDER && count($senders) > 0) {
		foreach ($senders as $sender) {
			if ($sender == SMS_OVH_SENDER) {
				if ($sms->checkSender($sender)) {
					// Check if sender exists and is valid, otherwise it will create an exception and sms won't be sent
					mylog("[SMS] Found valid sender " . $sender . ", using it");
					$message->setSender($sender);
					break;
				} else {
					mylog("[ERROR][SMS] Found sender " . $sender . " but it is not valid");
				}
			}
		}
	}
	$message->addReceiver($phone);
	$message->setIsMarketing(FALSE);

	$text = get_sms_string_for_lang($lang);
	$text = str_replace("#CODE#", $key, $text);
	$result = $message->send($text);
	
	$credits_removed = $result['totalCreditsRemoved'];
	mylog("[SMS] " . $credits_removed . " credit removed");
	$invalid_receiver = $result['invalidReceivers'];
	$valid_receiver = $result['validReceivers'];
	if (count($invalid_receiver) > 0) {
		mylog("[ERROR][SMS] phone number " . $phone . " seems invalid");
	} else if (count($valid_receiver) > 0) {
		mylog("[SMS] " . $text . " sent to " . $phone);
	} else {
		mylog("[WARN][SMS] Both valid and invalid receiver lists are empty...");
	}
}

function send_sms_legacy($phone, $password) {
	if (!SMS_API_ENABLED) {
		mylog("[WARN][SMS] SMS API disabled");
		return "WARNING_SMS_API_DISABLED";
	}

	$url = SMS_API_URL;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_USERPWD, SMS_API_USERNAME . ":" . SMS_API_PASSWORD);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
			'mobile' => $phone,
			'password' => $password,
	)));
	$result = curl_exec($ch);
	mylog("[SMS] SMS confirmation sent to " . $phone . " using password " . $password . ", request result is " . $result);
	curl_close($ch);
}

function send_sms($phone, $key, $lang, $password) {
	if (!SMS_API_ENABLED) {
		mylog("[WARN][SMS] SMS API disabled");
		return "WARNING_SMS_API_DISABLED";
	}
	
	if (startswith($phone, TESTS_PHONE_PREFIX)) {
		mylog("[ERROR][SMS] Not sending sms to fake number used for tests purposes: " . $phone);
		return "ERROR_NON_TEST_ACCOUNTS_UNAUTHORIZED";
	}
	
	$now_date = new DateTime('now');
	$now = $now_date->getTimestamp() * 1000;
	
	if (db_has_sms_already_been_sent_to($phone)) {
		$count = db_get_sms_count($phone);
		$time = db_get_last_sms($phone);
		$diff = $now - $time;
		if ($count >= SMS_COUNT_LIMIT_IN_PERIOD and $diff < SMS_TIME_PERIOD) {
			mylog("[ERROR][SMS] Last sms was sent at " . $time . ", time elapsed since then is " . $diff . "ms which is less than the configured time period " . SMS_TIME_PERIOD);
			return "ERROR_MAX_SMS_EXCEEDED";
		} else if ($diff >= SMS_TIME_PERIOD) {
			db_update_sms($phone, $now, 1);
		} else {
			$count = $count + 1;
			db_update_sms($phone, $now, $count);
		}
	} else {
		db_insert_sms($phone, $now);
	}
	
	
	if (SMS_OVH_API_KEY != NULL && SMS_OVH_API_KEY != "" && SMS_OVH_API_SECRET != NULL && SMS_OVH_API_SECRET != "" && SMS_OVH_CONSUMER_KEY != NULL && SMS_OVH_CONSUMER_KEY != "" && SMS_OVH_ENDPOINT != NULL && SMS_OVH_ENDPOINT != "") {
		try {
			send_sms_ovh($phone, $key, $lang);
			return "OK";
		} catch (Exception $e) {
			mylog("[ERROR][OVH-SMS] Exception: " . $e->getMessage());
		}
	} else if (SMS_API_URL != NULL && SMS_API_URL != "" && SMS_API_USERNAME != NULL && SMS_API_USERNAME != "" && SMS_API_PASSWORD != NULL && SMS_API_PASSWORD != "") {
		send_sms_legacy($phone, $password);
		return "OK";
	} else {
		mylog("[ERROR][SMS] No SMS API configured, discarding sms...");
		return "OK";
	}
	return "ERROR_CANT_SEND_SMS";
}

?>
