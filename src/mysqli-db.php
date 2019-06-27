<?php

function linphonedb_connect() {
	if (USE_PERSISTENT_CONNECTIONS) {
		$conn = mysqli_connect('p:' . DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	} else {
		$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}
	if ($conn->connect_errno) {
 		mylog("[ERROR][DB] Connection failed: " . $conn->connect_errno . " - " . $conn->connect_error);
	}
	return $conn;
}

function linphonedb_escape($conn, $param) {
	return mysqli_real_escape_string($conn, $param);
}

function linphonedb_query($queryStr, $conn) {
	$result = mysqli_query($conn, $queryStr);
	if (! $result) {
 		mylog("[ERROR][DB] Invalid query: " . $conn->connect_errno . " - " . $conn->connect_error);
		return "";
	}
	// cannot log result because fetch needed
	// 	mylog("[DB] Query: " . $queryStr);
	return $result;
}

function linphonedb_fetch($result) {
	if ($result->num_rows === 0) {
 		mylog("[ERROR][DB] Result is empty...");
	}
	$row = mysqli_fetch_array($result, MYSQLI_NUM);
	return $row;
}

function linphonedb_clean($result) {
	if (!is_bool($result)) { // some query may return a boolean, in that case we must not call free
		mysqli_free_result($result);
	}
}

function linphonedb_close($conn) {
	mysqli_close($conn);
}

?>
