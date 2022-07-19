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

// The following can't be put inside a function...
// So comment it out if not using the OVH SMS API
require __DIR__ . '/../vendor/autoload.php';
use \Ovh\Sms\SmsApi;

include_once __DIR__ . '/../database/database.php';
include_once __DIR__ . '/../objects/sms.php';
include_once __DIR__ . '/../misc/logging.php';
include_once __DIR__ . '/results_values.php';
include_once __DIR__ . '/utilities.php';

// Internationalization

function get_sms_string_for_lang($lang)
{
    global $SMS_OVH_TEMPLATE;
    if (isset($SMS_OVH_TEMPLATE[$lang])) {
        return $SMS_OVH_TEMPLATE[$lang];
    }
    Logger::getInstance()->warning("SMS template not found for lang " . $lang . ", using US template");
    return SMS_OVH_US_TEMPLATE;
}

// SMS API

function send_sms_ovh($phone, $key, $lang)
{
    if (!SMS_API_ENABLED) {
        Logger::getInstance()->warning("[SMS] SMS API disabled");
        return SMS_DISABLED;
    }

    $sms = new SmsApi(SMS_OVH_API_KEY, SMS_OVH_API_SECRET, SMS_OVH_ENDPOINT, SMS_OVH_CONSUMER_KEY);
    $accounts = $sms->getAccounts();
    $sms->setAccount($accounts[0]);
    if (SMS_USE_SENDER) {
        $senders = $sms->getSenders();

        /* The account must be validated in the OVH interface and by OVH itself */
        if (count($senders) == 0) {
            Logger::getInstance()->warning("[SMS] No sender found, creating one " . SMS_OVH_SENDER . " / " . SMS_OVH_REASON . " : " . SMS_OVH_DESC);
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
                    Logger::getInstance()->message("[SMS] Found valid sender " . $sender . ", using it");
                    $message->setSender($sender);
                    break;
                } else {
                    Logger::getInstance()->error("[SMS] Found sender " . $sender . " but it is not valid");
                }
            }
        }
    }
    $message->addReceiver($phone);
    $message->setIsMarketing(false);

    $text = get_sms_string_for_lang($lang);
    $text = str_replace("#CODE#", $key, $text);
    $result = $message->send($text);

    $credits_removed = $result['totalCreditsRemoved'];
    Logger::getInstance()->message("[SMS] " . $credits_removed . " credit removed");
    $invalid_receiver = $result['invalidReceivers'];
    $valid_receiver = $result['validReceivers'];
    if (count($invalid_receiver) > 0) {
        Logger::getInstance()->error("[SMS] phone number " . $phone . " seems invalid");
    } elseif (count($valid_receiver) > 0) {
        Logger::getInstance()->message("[SMS] " . $text . " sent to " . $phone);
    } else {
        Logger::getInstance()->warning("[SMS] Both valid and invalid receiver lists are empty...");
    }
}

function send_sms_legacy($phone, $password)
{
    if (!SMS_API_ENABLED) {
        Logger::getInstance()->warning("[SMS] SMS API disabled");
        return SMS_DISABLED;
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
    Logger::getInstance()->message("[SMS] SMS confirmation sent to " . $phone . " using password " . $password . ", request result is " . $result);
    curl_close($ch);
}

function send_sms($phone, $key, $lang)
{
    if (!SMS_API_ENABLED) {
        Logger::getInstance()->warning("[SMS] SMS API disabled");
        return SMS_DISABLED;
    }

    if (startswith($phone, TESTS_PHONE_PREFIX)) {
        Logger::getInstance()->error("[SMS] Not sending sms to fake number used for tests purposes: " . $phone);
        return TEST_ACCOUNTS_DISABLED;
    }

    $now_date = new DateTime('now');
    $now = $now_date->getTimestamp() * 1000;

    $database = new Database();
    $db = $database->getConnection();
    $sms = new SMS($db);
    $sms->phone = $phone;

    if ($sms->getOne()) {
        $diff = $now - $sms->last_sms;
        if ($sms->count >= SMS_COUNT_LIMIT_IN_PERIOD and $diff < SMS_TIME_PERIOD) {
            Logger::getInstance()->error("[SMS] Last sms was sent at " . $sms->last_sms . ", time elapsed since then is " . $diff . "ms which is less than the configured time period " . SMS_TIME_PERIOD);
            return MAX_SMS_ALLOWED_EXCEEDED;
        } elseif ($diff >= SMS_TIME_PERIOD) {
            $sms->last_sms = $now;
            $sms->count = 1;
            $sms->update();
        } else {
            $sms->count = $sms->count + 1;
            $sms->update();
        }
    } else {
        $sms->last_sms = $now;
        $sms->count = 1;
        $sms->create();
    }

    if (SMS_OVH_API_KEY != null && SMS_OVH_API_KEY != "" && SMS_OVH_API_SECRET != null && SMS_OVH_API_SECRET != "" && SMS_OVH_CONSUMER_KEY != null && SMS_OVH_CONSUMER_KEY != "" && SMS_OVH_ENDPOINT != null && SMS_OVH_ENDPOINT != "") {
        try {
            send_sms_ovh($phone, $key, $lang);
            return OK;
        } catch (Exception $e) {
            Logger::getInstance()->error("[OVH-SMS] Exception: " . $e->getMessage());
        }
    } elseif (SMS_API_URL != null && SMS_API_URL != "" && SMS_API_USERNAME != null && SMS_API_USERNAME != "" && SMS_API_PASSWORD != null && SMS_API_PASSWORD != "") {
        send_sms_legacy($phone, $key);
        return OK;
    } else {
        Logger::getInstance()->error("[SMS] No SMS API configured, discarding sms...");
        return OK;
    }
    return SMS_API_FAILURE;
}
