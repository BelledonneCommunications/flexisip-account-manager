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
include_once __DIR__ . '/../misc/utilities.php';

class Database
{
    public $conn;
    public function getConnection()
    {
        $this->conn = null;

        try {
            if(!empty(get_config_value("DB_ENABLE_SSL","")) && !empty(get_config_value("ROOT_CA_PATH",""))){
              if(!file_exists (ROOT_CA_PATH)){
                Logger::getInstance()->error("MySQL connection error: the provided ROOT_CA_PATH does not exists!");
              }
              else{

                $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD, array (
                    PDO::MYSQL_ATTR_SSL_CA => ROOT_CA_PATH,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                ));
              }
            }
            else{
              $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            }

            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            Logger::getInstance()->error("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
