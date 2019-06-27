#! /bin/php
<?php 

define("PATH_TO_CONFIG", "/etc/flexisip-account-manager/");
include PATH_TO_CONFIG . "xmlrpc.conf";

include "mysqli-db.php";
include "logging.php";
include "utilities.php";
include "xmlrpc-accounts.php";
include "xmlrpc-aliases.php";
include "xmlrpc-sms.php";

date_default_timezone_set(DEFAULT_TIMEZONE);
mylog("[DEBUG] Timezone set to " . DEFAULT_TIMEZONE);

if ($argc >= 2) {
    $action = $argv[1];
    if ($action == "list_accounts") {
        $accounts = db_get_accounts();
        foreach ($accounts as $account) {
            echo $account['username'] . '@' . $account['domain'] . ' activation status is ' . $account['activated'] . " (activation code is " . $account['activation_code'] . "): IP " . $account['ip_address'] . ", user-agent " . $account['user_agent'] . "\r\n";
        }
    } else if ($action == "delete_account") {
        if ($argc >= 3) {
            $login = $argv[2];
            $domain = SIP_DOMAIN;
            if ($argc >= 4) {
                $domain = $argv[3];
            }
            if (!db_account_is_existing($login, $domain)) {
                echo "Error: account " . $login . " on domain " . $domain . " doesn't exist." . "\r\n";
                exit;
            }
            db_alias_delete($login, $domain);                                                                                                                                                
            db_account_delete($login, $domain);                                                                                                                                              
            if (startswith($login, "+")) {                                                                                                                                                   
                db_delete_sms($login);                                                                                                                                                       
            }
            echo "Account " . $login . " successfuly deleted." . "\r\n";                                                                                                                                                                                
        } else {                                                                                                                                                                             
            echo "Proper way to use is php admin.php delete_account <login> [domain]" . "\r\n";                                                                                            
        }
    } else if ($action == "activate_account") {
        if ($argc >= 3) {
            $login = $argv[2];
            $domain = SIP_DOMAIN;
            if ($argc >= 4) {
                $domain = $argv[3];
            }
            if (!db_account_is_existing($login, $domain)) {
                echo "Error: account " . $login . " on domain " . $domain . " doesn't exist." . "\r\n";
                exit;
            }
            db_account_super_activate($login, $domain);
            echo "Account " . $login . " succesfuly super activated." . "\r\n";
        } else {
            echo "Proper way to use is php admin.php activate_account <login> [domain]" . "\r\n";
        }                                                                                                                                                                                    
    } else if ($action == "help") {                                                                                                                                                          
        echo "Possible commands are:" . "\r\n";                                                                                                                                              
        echo "help" . "\r\n";                                                                                                                                                                
        echo "list_accounts" . "\r\n";                                                                                                                                                       
        echo "activate_account" . "\r\n";
        echo "delete_account <login> [domain]" . "\r\n";                                                                                                                                     
    }                                                                                                                                                                                        
} else {                                                                                                                                                                                     
    echo "Proper way to use is php admin.php action [params]" . "\r\n";                                                                                                                      
    echo "Try php admin.php help to see all possible actions." . "\r\n";
        exit;
}

?>
