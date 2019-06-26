<?php

define("PATH_TO_CONFIG", "/etc/flexisip-account-manager/");
include PATH_TO_CONFIG . "xmlrpc.conf";

$rc_array = parse_ini_file("default.rc", true);

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml = $xml . '<config xmlns="http://www.linphone.org/xsds/lpconfig.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.linphone.org/xsds/lpconfig.xsd lpconfig.xsd">';
foreach ($rc_array as $section => $values) {
    $xml = $xml . '<section name="' . $section . '">';
    foreach ($values as $key => $value) {
		if (REMOTE_PROVISIONING_OVERWRITE_ALL) {
			$xml = $xml . '<entry name="' . $key . '" overwrite="true">' . $value . '</entry>';
		} else {
			$xml = $xml . '<entry name="' . $key . '">' . $value . '</entry>';
		}
    }
    $xml = $xml . '</section>';
}
$xml = $xml . '</config>';

header('Content-type: text/xml');
echo $xml;

?>
