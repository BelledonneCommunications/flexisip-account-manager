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

// Google/Android specifics

// Get an access token to access Google APIs
function get_google_access_token()
{
    $ch = curl_init(GOOGLE_API_OAUTH_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'client_id' => GOOGLE_PROJECT_ID,
            'client_secret' => GOOGLE_PROJECT_PASSWORD,
            'refresh_token' => GOOGLE_PROJECT_REFRESH_TOKEN,
            'grant_type' => "refresh_token",
    )));
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    $token = $json["access_token"];
    mylog("[GOOGLE] Access token is " . $token);
    return $token;
}

// Query Google for the expiration time given the transaction token as described here: https://developers.google.com/android-publisher/api-ref/purchases/subscriptions/get
function get_expiration_for_android_token_and_subscription($token, $subscription)
{
    $google_access_token = get_google_access_token();
    $url = "https://www.googleapis.com/androidpublisher/v2/applications/" . ANDROID_PACKAGE . "/purchases/subscriptions/" . $subscription . "/tokens/" . $token . "?access_token=" . $google_access_token;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    $expiration = $json["expiryTimeMillis"];
    mylog("[GOOGLE] expire timestamp for token = " . $token . " and product id = " . $subscription . " is " . $expiration);
    return $expiration . "";
}

// Returns 1 if the payload/signature has been issued by Google.
function check_google_signature($payload, $signature)
{
    $certFile = fopen(ANDROID_PUB_KEY_PATH, "r");
    $cert = fread($certFile, 8192);
    fclose($certFile);
    $pubKeyId = openssl_get_publickey($cert);

    $ok = openssl_verify($payload, base64_decode($signature), $pubKeyId, OPENSSL_ALGO_SHA1);
    mylog("[GOOGLE] signature verification result is " . $ok);
    return $ok;
}

// End of Google/Android specifics

// Apple/iOS specifics

function get_apple_receipt($payload)
{
    $ch = curl_init(APPLE_URL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('receipt-data' => $payload, 'password' => APPLE_SECRET)));
    $result = curl_exec($ch);
    curl_close($ch);

    mylog("[APPLE] decoded receipt is " . $result);
    $json = json_decode($result, true);

    $status_code = $json["status"];
    if ($status_code == 21007) {
        mylog("[APPLE] Error 21007 found, sending receipt to sandbox instead of production");
        $ch = curl_init(APPLE_SANDBOX_URL);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('receipt-data' => $payload, 'password' => APPLE_SECRET)));
        $result = curl_exec($ch);
        curl_close($ch);

        mylog("[APPLE] decoded receipt is " . $result);
        $json = json_decode($result, true);
    }

    return $json;
}

// Returns 1 if the payload/signature has been signed by Apple, else will return the error code as described here: https://developer.apple.com/library/ios/releasenotes/General/ValidateAppStoreReceipt/Chapters/ValidateRemotely.html#//apple_ref/doc/uid/TP40010573-CH104-SW1
function check_apple_signature($payload)
{
    $status = -1;

    $status = $payload["status"];
    mylog("[APPLE] Status in apple receipt is " . $status);
    if ($status == 0) {
        return 1;
    }

    return $status;
}

function parse_apple_receipt_get_expiration($user, $domain, $json)
{
    $last_used = db_inapp_get_last_used_field($user, $domain);

    $days = 0;
    $receipt = $json["receipt"];
    $in_app = $receipt["in_app"];
    foreach ($in_app as $item => $value) {
        if (array_key_exists("original_purchase_date_ms", $value) and array_key_exists("product_id", $value)) {
            $purchase_date = $value["original_purchase_date_ms"];
            $product_id = $value["product_id"];

            if ($purchase_date > $last_used) {
                $days_bought = 0;
                if (endswith($product_id, "1_month")) {
                    $days_bought = 30;
                } elseif (endswith($product_id, "1_year")) {
                    $days_bought = 365;
                } else {
                    mylog("[ERROR] Unknown duration for product ID " . $product_id);
                    continue;
                }

                if (startswith($product_id, "test.")) {
                    mylog("[APPLE] Test mode detected, time accelerated (1 month => 1 minute)");
                    $days_bought /= 43200;
                }

                if ($days_bought > 0) {
                    $days = $days + $days_bought;
                    db_inapp_update_last_used_field($user, $domain, $purchase_date);
                }
            }
        }
    }

    if ($days <= 0) {
        mylog("[WARN] [APPLE] Either no receipt or all receipts have already been consumed");
        return 0;
    }
    $millis = 86400000 * $days;

    $now = get_trial_expiration_date();
    $expiration = db_inapp_get_expiration_date($user, $domain);

    $max = max($now, $expiration);
    $expiration_date = $max + $millis;

    mylog("[APPLE] Adding " . $days . " days to current expiration date (= " . $millis . " ms). New expiration date is " . $expiration_date);

    return $expiration_date;
}

// End of Apple/iOS specifics

// XMLRPC methods

// Returns 1 if the payload/signature has been signed by either Google or Apple, depending on $os.
function check_signature($os, $payload, $signature)
{
    if (strcmp($os, "google") == 0) {
        return check_google_signature($payload, $signature);
    } elseif (strcmp($os, "apple") == 0) {
        return check_apple_signature($payload);
    }
    return -2;
}

// args = [username, ha1, [domain]]
function xmlrpc_is_account_trial($method, $args)
{
    $user = $args[0];
    $password = $args[1];
    $domain = get_domain($args[2]);

    mylog("[XMLRPC] xmlrpc_is_account_trial(" . $user . ", " . $domain . ")");

    if (!check_parameter($user)) {
        return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
    }
    if (!db_account_is_existing($user, $domain)) {
        mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
        return "ERROR_ACCOUNT_DOESNT_EXIST";
    } else {
        $hashed_password = hash_password($user, $password, $domain);
        $db_hashed_password = db_account_get_password($user, $domain);
        if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
            mylog("[ERROR] Password doesn't match");
            return "ERROR_PASSWORD_DOESNT_MATCH";
        }
    }

    if (!USE_IN_APP_PURCHASES || !db_inapp_is_account($user, $domain)) {
        return "ERROR_NO_EXPIRATION";
    }

    if (db_inapp_is_account_trial($user, $domain)) {
        return "OK";
    } else {
        return "NOK";
    }
}

// args = [username, ha1, [domain]]
function xmlrpc_is_account_expired($method, $args)
{
    $user = $args[0];
    $password = $args[1];
    $domain = get_domain($args[2]);

    mylog("[XMLRPC] xmlrpc_is_account_expired(" . $user . ", " . $domain . ")");

    if (!check_parameter($user)) {
        return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
    }
    if (!db_account_is_existing($user, $domain)) {
        mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
        return "ERROR_ACCOUNT_DOESNT_EXIST";
    } else {
        $hashed_password = hash_password($user, $password, $domain);
        $db_hashed_password = db_account_get_password($user, $domain);
        if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
            mylog("[ERROR] Password doesn't match");
            return "ERROR_PASSWORD_DOESNT_MATCH";
        }
    }

    if (!USE_IN_APP_PURCHASES || !db_inapp_is_account($user, $domain)) {
        return "ERROR_NO_EXPIRATION";
    }

    $expiration = db_inapp_get_expiration_date($user, $domain);
    $now_date = new DateTime('now');
    $now = $now_date->getTimestamp() * 1000;
    if ($now > $expiration) {
        return "OK";
    } else {
        return "NOK";
    }
}

// args = [payload, signature]
function xmlrpc_check_payload_signature($method, $args)
{
    $payload = $args[0];
    $signature = $args[1];

    mylog("[XMLRPC] xmlrpc_check_payload_signature(payload, signature)");

    $result = 0;
    $os = "google";
    $payloadJson = $payload;
    if ($signature == "") {
        $payloadJson = get_apple_receipt($payload);
        $os = "apple";
    }

    $result = check_signature($os, $payloadJson, $signature);

    if ($result == 1) {
        return "OK";
    }
    return "NOK";
}

// args = [username, ha1, [domain], payload, signature=""]
function xmlrpc_update_expiration_date($method, $args)
{
    $user = $args[0];
    $password = $args[1];
    $payload = $args[3];
    $signature = $args[4];
    $domain = get_domain($args[2]);

    mylog("[XMLRPC] xmlrpc_update_expiration_date(" . $user . ", " . $domain . ", payload, signature)");

    if (!check_parameter($user)) {
        return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
    }
    if (!db_account_is_existing($user, $domain)) {
        mylog("[ERROR] User account " . $user . " / " . $domain . " doesn't exist");
        return "ERROR_ACCOUNT_DOESNT_EXIST";
    } elseif (!db_account_is_activated($user, $domain)) {
        mylog("[ERROR] User account " . $user . " / " . $domain . " isn't activated");
        return "ERROR_ACCOUNT_NOT_ACTIVATED";
    } else {
        $hashed_password = hash_password($user, $password, $domain);
        $db_hashed_password = db_account_get_password($user, $domain);
        if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
            mylog("[ERROR] Password doesn't match");
            return "ERROR_PASSWORD_DOESNT_MATCH";
        }
    }

    if (!USE_IN_APP_PURCHASES || !db_inapp_is_account($user, $domain)) {
        return "ERROR_NO_EXPIRATION";
    }

    $result = 0;
    $os = "google";
    $payloadJson = $payload;
    if ($signature == "") {
        $payloadJson = get_apple_receipt($payload);
        $os = "apple";
    }
    $result = check_signature($os, $payloadJson, $signature);

    if ($result == 1) {
        $expiration_date = 0;
        if (strcmp($os, "google") == 0) {
            $json = json_decode($payload, true);
            $token = $json["purchaseToken"];
            $subscription = $json["productId"];
            $expiration_date = get_expiration_for_android_token_and_subscription($token, $subscription);
        } elseif (strcmp($os, "apple") == 0) {
            $expiration_date = parse_apple_receipt_get_expiration($user, $domain, $payloadJson);
        }

        if ($expiration_date >= 0) {
            if ($expiration_date > 0) {
                db_inapp_update_trial($user, $domain, 0);
                db_inapp_update_expiration_date($user, $domain, $expiration_date);

                /*if (CUSTOM_HOOKS) {
                    hook_on_expiration_date_updated($user, $domain, $expiration_date, $payloadJson, $os);
                }*/
                return $expiration_date . "";
            } else {
                return db_inapp_get_expiration_date($user, $domain) . "";
            }
        } else {
            mylog("[ERROR] Expiration is " . $expiration_date);
        }
    }
    mylog("[ERROR] Couldn't verify signature of payload...");
    return "ERROR_SIGNATURE_VERIFICATION_FAILED";
}

// args = [username, ha1, [domain]]
function xmlrpc_get_account_expiration($method, $args)
{
    $user = $args[0];
    $password = $args[1];
    $domain = get_domain($args[2]);

    mylog("[XMLRPC] xmlrpc_get_account_expiration(" . $user . ")");

    if (!check_parameter($user)) {
        return "ERROR_USERNAME_PARAMETER_NOT_FOUND";
    }

    if (!db_account_is_existing($user, $domain)) {
        mylog("[ERROR] User account " . $user . " doesn't exist");
        return "ERROR_ACCOUNT_DOESNT_EXIST";
    } elseif (!db_account_is_activated($user, $domain)) {
        mylog("[ERROR] User account " . $user . " isn't activated");
        return "ERROR_ACCOUNT_NOT_ACTIVATED";
    } else {
        $hashed_password = hash_password($user, $password, $domain);
        $db_hashed_password = db_account_get_password($user, $domain);
        if (strcmp($hashed_password, $db_hashed_password) != 0 and strcmp($password, $db_hashed_password) != 0) {
            mylog("[ERROR] Password doesn't match");
            return "ERROR_PASSWORD_DOESNT_MATCH";
        }
    }

    if (!USE_IN_APP_PURCHASES || !db_inapp_is_account($user, $domain)) {
        return "ERROR_NO_EXPIRATION";
    }

    $expiration = db_inapp_get_expiration_date($user, $domain);
    return $expiration . "";
}

function xmlrpc_inapp_register_methods($server)
{
    xmlrpc_server_register_method($server, 'is_account_trial', 'xmlrpc_is_account_trial');// args = [username, ha1, [domain]]
    xmlrpc_server_register_method($server, 'is_account_expired', 'xmlrpc_is_account_expired');// args = [username, ha1, [domain]]
    xmlrpc_server_register_method($server, 'get_account_expiration', 'xmlrpc_get_account_expiration');// args = [username, ha1, [domain]]
    xmlrpc_server_register_method($server, 'update_expiration_date', 'xmlrpc_update_expiration_date');// args = [username, ha1, [domain], payload, [signature]]
    xmlrpc_server_register_method($server, 'check_payload_signature', 'xmlrpc_check_payload_signature');// args = [payload, signature]
}
