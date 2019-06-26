<?php

function request_authentication($realm = "sip.example.org") {
	header('HTTP/1.1 401 Unauthorized');
	header('WWW-Authenticate: Digest realm="' . $realm.
		'",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
	exit();
}

function authenticate($auth_digest, $realm = "sip.example.org") {
	// Parse the client authentication data
	$default = array('nounce', 'nc', 'cnounce', 'qop', 'username', 'uri', 'response');
	preg_match_all('~(\w+)="?([^",]+)"?~', $auth_digest, $matches); # $_SERVER['PHP_AUTH_DIGEST']
	$data = array_combine($matches[1] + $default, $matches[2]);

	// Get the password/hash from database
	$conn = linphonedb_connect();
	$query = "SELECT password FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $data['username']) . "');";
	$result = linphonedb_query($query, $conn);
	$row = linphonedb_fetch($result);
	$password = $row[0];

	// Get the corresponding algorithm if specified
	$query = "SELECT algorithm FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id=(SELECT id FROM " . ACCOUNTS_DB_TABLE . " WHERE login='" . linphonedb_escape($conn, $data['username']) . "');";
	$result = linphonedb_query($query, $conn);
	$row = linphonedb_fetch($result);
	$algorithm = $row[0];

	if (is_null($algorithm)) {
		$algorithm = 'MD5';
	}

	// Close db connection
	linphonedb_clean($result);
	linphonedb_close($conn);

	// Generate the valid response
	switch ($algorithm) {
		case 'CLRTXT':
			$A1 = md5($data['username'].':'.$data['realm'].':'.$password);
			$A2 = md5(getenv('REQUEST_METHOD').':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			break;
		case 'MD5':
			$A1 = $password; // username:realm:password
			$A2 = md5(getenv('REQUEST_METHOD').':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			break;
		case 'SHA256':
			$A1 = $password; // username:realm:password
			$A2 = hash('sha256', getenv('REQUEST_METHOD').':'.$data['uri']);
			$valid_response = hash('sha256', $A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			break;
	}

	// Compare with the client response
	if($data['response'] === $valid_response) {
		return TRUE;
	} else {
		return FALSE;
	}
}

?>
