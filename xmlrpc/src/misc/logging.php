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

class Logger
{
    private static $instance = null;

    private $log_file;

    public function __construct()
    {
        if (!LOGS_ENABLED) {
            return;
        }
        if (USE_ONE_LOG_FILE) {
            $this->log_file = LOG_FILE;
        } else {
            if (!file_exists(LOG_DIR)) {
                mkdir(LOG_DIR, 0777, true);
            }
            $this->log_file = LOG_DIR . "/" . date('d-M-Y') . '.logs';
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    private function mylog($level, $message)
    {
        if (!LOGS_ENABLED) {
            return;
        }

        if (is_array($message)) {
            $message = implode(" ", $message);
        }

        $now = getdate();
        $month = sprintf("%02d", $now["mon"]);
        $day = sprintf("%02d", $now["mday"]);
        $hours = sprintf("%02d", $now["hours"]);
        $minutes = sprintf("%02d", $now["minutes"]);
        $seconds = sprintf("%02d", $now["seconds"]);
        $log_msg = "[" . $day . "/" .  $month . "/" . $now["year"] . " " . $hours . ":" . $minutes . ":" . $seconds . "] [" . $level . "] " . $message . "\r\n";

        file_put_contents($this->log_file, $log_msg, FILE_APPEND);
    }

    public function error($message)
    {
        $this->mylog("Error", $message);
    }

    public function warning($message)
    {
        $this->mylog("Warning", $message);
    }

    public function message($message)
    {
        $this->mylog("Message", $message);
    }

    public function debug($message)
    {
        $this->mylog("Debug", $message);
    }
}
