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

include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../misc/logging.php';

function send_email($email, $subject, $text, $html)
{
    $site = EMAIL_SITE;
    $from = EMAIL_FROM_ADDR;
    $name = EMAIL_FROM_NAME;
    $to = $email;
    $from = $name." <".$from.">";

    $limite = "_----------=_parties_".md5(uniqid(rand()));

    $headers  = "Reply-to: ".$from."\n";
    $headers .= "From: ".$from."\n";
    $headers .= "Return-Path: ".$from."\n";
    $headers .= "X-Sender: <".$site.">\n";
    $headers .= "X-Mailer: PHP\n";
    $headers .= "X-auth-smtp-user: ".$from." \n";
    $headers .= "X-abuse-contact: ".$from." \n";
    $headers .= "X-auth-smtp-user: ".$from." \n";
    $headers .= "X-abuse-contact: ".$from." \n";
    $headers .= "Date: ".date("D, j M Y G:i:s O")."\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"".$limite."\"";

    $message = "";

    $message .= "--".$limite."\n";
    $message .= "Content-Type: text/plain; charset=\"utf-8\"\n";
    $message .= "Content-Transfer-Encoding: 8bit\n\n";
    $message .= $text;

    $message .= "\n\n--".$limite."\n";
    $message .= "Content-Type: text/html; charset=\"utf-8\"\n";
    $message .= "Content-Transfer-Encoding: 8bit;\n\n";
    $message .= $html;

    $message .= "\n--".$limite."--";

    $params = "-f" . EMAIL_FROM_ADDR . " -O DeliveryMode=b";
    $result = mail($email, $subject, $message, $headers, $params);
    if (!$result) {
        Logger::getInstance()->error("[EMAIL] Email delivery declined !");
    }
}

function send_email_with_activation_link($email, $key, $username, $domain, $algo)
{
    if (!EMAIL_ENABLED) {
        Logger::getInstance()->warning("[EMAIL] Emails are disabled");
        return "WARNING_EMAILS_DISABLED";
    }

    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    $link = $pageURL . EMAIL_ACTIVATION_LINK;
    $link = str_replace("%key%", $key, $link);
    $link = str_replace("%username%", $username, $link);
    $link = str_replace("%domain%", $domain, $link);
    $link = str_replace("%algo%", $algo, $link);
    Logger::getInstance()->debug("[EMAIL] Activation link is " . $link);

    $body = str_replace("%link%", $link, EMAIL_ACTIVATION_BODY);
    Logger::getInstance()->debug("[EMAIL] Activation body is " . $body);
    $body_html = str_replace("%link%", $link, EMAIL_ACTIVATION_BODY_HTML);
    Logger::getInstance()->debug("[EMAIL] Activation html body is " . $body_html);

    send_email($email, EMAIL_ACTIVATION_SUBJECT, $body, $body_html);
    Logger::getInstance()->message("[EMAIL] Email sent to email " . $email . " to activate the account");
}

function send_email_with_recover_key($email, $key)
{
    if (!EMAIL_ENABLED) {
        Logger::getInstance()->warning("[EMAIL] Emails are disabled");
        return "WARNING_EMAILS_DISABLED";
    }

    $body = str_replace("%key%", $key, EMAIL_RECOVERY_BODY);
    Logger::getInstance()->debug("[EMAIL] Recovery body is " . $body);
    $body_html = str_replace("%key%", $key, EMAIL_RECOVERY_BODY_HTML);
    Logger::getInstance()->debug("[EMAIL] Recovery html body is " . $body_html);

    send_email($email, EMAIL_RECOVERY_SUBJECT, $body, $body_html);
    Logger::getInstance()->message("[EMAIL] Email sent to email " . $email . " to recover the account");
}
