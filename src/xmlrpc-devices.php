<?php

function db_drop_devices_table() {
	$conn = linphonedb_connect();
	$create_req = "DROP TABLE IF EXISTS " . DEVICES_DB_TABLE;
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_create_devices_table() {
	$conn = linphonedb_connect();
	$create_req = "CREATE TABLE IF NOT EXISTS " . DEVICES_DB_TABLE . " (
					id INTEGER(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
					manufacturer VARCHAR(64) NOT NULL,
					model VARCHAR(34) NOT NULL,
					status VARCHAR(34) NOT NULL,
					delay INTEGER(4) NOT NULL DEFAULT 0,
					hardware_echo_canceller TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (id))";
	$result = linphonedb_query($create_req, $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

function db_device_create($manufacturer, $model, $status, $delay, $hec) {
	$conn = linphonedb_connect();
	$result = linphonedb_query("INSERT INTO " . DEVICES_DB_TABLE . "(manufacturer, model, status, delay, hardware_echo_canceller) VALUES('" . linphonedb_escape($conn, $manufacturer) . "','" . linphonedb_escape($conn, $model) . "','" . linphonedb_escape($conn, $status) . "','" . linphonedb_escape($conn, $delay) . "','" . linphonedb_escape($conn, $hec) . "')", $conn);
	linphonedb_clean($result);
	linphonedb_close($conn);
}

// args = [manufacturer, model, status, delay, hasHEC]
function xmlrpc_add_ec_calibration_result($method, $args) {
	$manufacturer = $args[0];
	$model = $args[1];
	$status = $args[2];
	$delay = $args[3];
	$hasHEC = 0;
	if (count($args) == 5) {
		$hasHEC = (int)$args[4];
	}

	db_device_create($manufacturer, $model, $status, $delay, $hasHEC);
	return "OK";
}

function xmlrpc_misc_register_methods($server) {
	xmlrpc_server_register_method($server, 'add_ec_calibration_result', 'xmlrpc_add_ec_calibration_result');// args = [manufacturer, model, status, delay, hasHEC]
}

?>