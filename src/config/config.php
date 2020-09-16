<?php

/*
	Flexisip Account Manager is a set of tools to manage SIP accounts.
	Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

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

$path = isset($_SERVER['FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG']) ? $_SERVER['FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG'] : "/etc/flexisip-account-manager";
define("FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG", $path);

include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/accounts.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/auth.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/db.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/emails.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/inapp.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/logs.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/provisioning.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/sms.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/tests.conf';

// these two might include some of the scripts from the installation, so keep them last
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/hooks.conf';
include_once FLEXISIP_ACCOUNT_MANAGER_PATH_TO_CONFIG . '/overloads.conf';

?>
