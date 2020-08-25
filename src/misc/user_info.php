<?php

include_once __DIR__ . '/../database/database.php';

include_once __DIR__ . '/../objects/account.php';
include_once __DIR__ . '/../objects/password.php';
include_once __DIR__ . '/../objects/alias.php';
include_once __DIR__ . '/../objects/user_info.php';

include_once __DIR__ . '/utilities.php';
include_once __DIR__ . '/geoloc.php';

include_once __DIR__ . '/results_values.php';

// args = [username, ha1, firstname, lastname, gender, subscribe, [domain], [algo]]
function update_account_user_info($username, $ha1, $firstname, $lastname, $gender, $subscribe, $domain, $algo)
{
    Logger::getInstance()->message("update_account_user_info(" . $username . ", " . $domain . " : " . $firstname . ", " . $lastname . ", " . $gender . ", " . $subscribe . ")");

    $database = new Database();
    $db = $database->getConnection();

    $account = new Account($db);
    $account->username = $username;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }
    Logger::getInstance()->debug("userInfo : Account after get one " . $account);

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if (!password_match($ha1, $password->password)) {
        return PASSWORD_DOESNT_MATCH;
    }

    $user_info = new UserInfo($db);
    $user_info->account_id = $account->id;

    if (get_config_value(ENABLE_NEW_ACCOUNTS_GEOLOC, FALSE)) {
        Logger::getInstance()->debug("userInfo : Account ip after enable geoloc if " . $account->ip_address);
        $country_infos = Geoloc::getGeolocInfosFromIp($account->ip_address);
        if ($country_infos) {
            $user_info->country_code = $country_infos->country_code;
            $user_info->country_name = $country_infos->country_name;
        }
        //error message is displayed from geoloc method.
        else {
            return GEOLOC_FAILED;
        }
        Logger::getInstance()->debug("Getting geoloc infos : country_code=".
            $country_infos->country_code . ' country_name=' . $country_infos->country_name);
    }

    $update = $user_info->getOne();

    $user_info->firstname = $firstname;
    $user_info->lastname = $lastname;
    $user_info->gender = $gender;
    $user_info->subscribe = $subscribe;

    if ($update) {
        $user_info->update();
    } else {
        $user_info->create();
    }

    return OK;
}
