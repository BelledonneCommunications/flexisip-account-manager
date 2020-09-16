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

define("OK", "OK");
define("NOK", "NOK");
define("OK_ALIAS", "OK_ALIAS");
define("OK_ACCOUNT", "OK_ACCOUNT");

/* Parameter related */

define("MISSING_PHONE_PARAM", "ERROR_PHONE_PARAMETER_NOT_FOUND");
define("MISSING_USERNAME_PARAM", "ERROR_USERNAME_PARAMETER_NOT_FOUND");
define("MISSING_EMAIL_PARAM", "ERROR_EMAIL_PARAMETER_NOT_FOUND");
define("MISSING_OLD_HASH", "ERROR_OLD_HASH_NOT_FOUND");
define("MISSING_NEW_HASH", "ERROR_NEW_HASH_NOT_FOUND");
define("MISSING_MD5_HASH", "ERROR_MD5_HASH_NOT_FOUND");
define("MISSING_SHA256_HASH", "ERROR_SHA256_HASH_NOT_FOUND");
define("EMAIL_UNCHANGED", "ERROR_EMAIL_NEW_SAME_AS_OLD");

/* Parameter not available because already in use */

define("PHONE_TAKEN", "ERROR_ALIAS_ALREADY_IN_USE");
define("USERNAME_TAKEN", "ERROR_ACCOUNT_ALREADY_IN_USE");
define("EMAIL_TAKEN", "ERROR_EMAIL_ALREADY_IN_USE");

/* Object not found */

define("ALIAS_NOT_FOUND", "ERROR_ALIAS_DOESNT_EXIST");
define("ACCOUNT_NOT_FOUND", "ERROR_ACCOUNT_DOESNT_EXIST");
define("PASSWORD_NOT_FOUND", "ERROR_PASSWORD_NOT_FOUND");
define("USERINFO_NOT_FOUND", "ERROR_USERINFO_NOT_FOUND");

/* Equality check failure */

define("KEY_DOESNT_MATCH", "ERROR_KEY_DOESNT_MATCH");
define("PASSWORD_DOESNT_MATCH", "ERROR_PASSWORD_DOESNT_MATCH");
define("EMAIL_DOESNT_MATCH", "ERROR_EMAIL_DOESNT_MATCH");
define("ALIAS_DOESNT_MATCH", "ERROR_ALIAS_DOESNT_MATCH");

/* Disabled features */

define("TEST_ACCOUNTS_DISABLED", "ERROR_NON_TEST_ACCOUNTS_UNAUTHORIZED");
define("SMS_DISABLED", "ERROR_SMS_API_DISABLED");
define("ALGO_NOT_SUPPORTED", "ERROR_ALGO_NOT_SUPPORTED");

/* Unexpected state */

define("ACCOUNT_ALREADY_ACTIVATED", "ERROR_ACCOUNT_ALREADY_ACTIVATED");
define("ACCOUNT_NOT_YET_ACTIVATED", "ERROR_ACCOUNT_NOT_ACTIVATED");
define("ACCOUNT_RECOVERY_IMPOSSIBLE", "ERROR_CANT_RECOVER_ACCOUNT");

/* Format error */

define("PHONE_NOT_E164", "ERROR_PHONE_ISNT_E164");

/* SMS error */

define("MAX_SMS_ALLOWED_EXCEEDED", "ERROR_MAX_SMS_EXCEEDED");
define("SMS_API_FAILURE", "ERROR_CANT_SEND_SMS");

/* Geoloc error */

define("GEOLOC_FAILED", "ERROR_GEOLOC_FAILED");

/* Other error */

define('SHA256_PASSWORD_ALREADY_EXISTS', 'ERROR_SHA256_PASSWORD_ALREADY_EXISTS');
