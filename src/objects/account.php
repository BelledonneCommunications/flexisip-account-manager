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

class Account
{
    private $conn;

    public $id;
    public $username;
    public $domain;
    public $email;
    public $activated;
    public $confirmation_key;
    public $ip_address;
    public $user_agent;
    public $creation_time;
    public $expire_time;
    public $alias;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function __toString()
    {
        $to_string = "Account: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->username)) {
            $to_string = $to_string . "username=" . $this->username . ", ";
        }
        if (!empty($this->domain)) {
            $to_string = $to_string . "domain=" . $this->domain . ", ";
        }
        if (!empty($this->email)) {
            $to_string = $to_string . "email=" . $this->email . ", ";
        }
        if (!empty($this->activated)) {
            $to_string = $to_string . "activated=" . $this->activated . ", ";
        }
        if (!empty($this->ip_address)) {
            $to_string = $to_string . "ip_address=" . $this->ip_address . ", ";
        }
        if (!empty($this->confirmation_key)) {
            $to_string = $to_string . "confirmation_key=" . $this->confirmation_key . ", ";
        }
        if (!empty($this->alias)) {
            $to_string = $to_string . "alias=" . $this->alias . ", ";
        }
        return substr($to_string, 0, -2);
    }

    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS " . ACCOUNTS_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . ACCOUNTS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . ACCOUNTS_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(64) NOT NULL,
            domain VARCHAR(64) NOT NULL,
            email VARCHAR(64) DEFAULT NULL,
            activated VARCHAR(1) NOT NULL DEFAULT '0',
            confirmation_key VARCHAR(14) DEFAULT NULL,
            ip_address VARCHAR(39) NOT NULL,
            user_agent VARCHAR(256) NOT NULL,
            creation_time DATETIME NOT NULL,
            expire_time DATETIME,
            PRIMARY KEY (id), UNIQUE KEY identity (username, domain))";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . ACCOUNTS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . ACCOUNTS_DB_TABLE . " WHERE id = ?";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        Logger::getInstance()->debug("Deleting " . (string)$this);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function create()
    {
        $query = "INSERT INTO " . ACCOUNTS_DB_TABLE . " SET username=:username, domain=:domain, email=:email, activated=:activated,
            confirmation_key=:confirmation_key, ip_address=:ip_address, user_agent=:user_agent, creation_time=:creation_time";

        if (USE_IN_APP_PURCHASES) {
            $query = $query . ", expire_time=DATE_ADD(NOW(), INTERVAL " . EXPIRATION_DELAY . " MINUTE)";
            $this->expire_time = htmlspecialchars(strip_tags($this->expire_time));
        }

        if (empty($this->creation_time)) {
            $this->creation_time = date('Y-m-d H:i:s');
        }

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->domain = htmlspecialchars(strip_tags($this->domain));
        $this->activated = htmlspecialchars(strip_tags($this->activated));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->confirmation_key = htmlspecialchars(strip_tags($this->confirmation_key));
        $this->ip_address = htmlspecialchars(strip_tags($this->ip_address));
        $this->user_agent = htmlspecialchars(strip_tags($this->user_agent));
        $this->creation_time = htmlspecialchars(strip_tags($this->creation_time));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":domain", $this->domain);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":activated", $this->activated);
        $stmt->bindParam(":confirmation_key", $this->confirmation_key);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        $stmt->bindParam(":creation_time", $this->creation_time);

        Logger::getInstance()->debug("Creating " . (string)$this);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function activate()
    {
        $query = "UPDATE " . ACCOUNTS_DB_TABLE . " SET activated=1 WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $stmt->bindParam(":id", $this->id);

        Logger::getInstance()->debug("Activating " . (string)$this);
        if ($stmt->execute()) {
            $this->activated = "1";
            return true;
        }
        
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . ACCOUNTS_DB_TABLE . " SET username=:username, domain=:domain, activated=:activated";

        if (!empty($this->email)) {
            $query = $query . ", email=:email";
        }
        if (!empty($this->confirmation_key)) {
            $query = $query . ", confirmation_key=:confirmation_key";
        }
        if (USE_IN_APP_PURCHASES) {
            $query = $query . ", expire_time=:expire_time";
        }

        $query = $query . " WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->domain = htmlspecialchars(strip_tags($this->domain));
        $this->activated = htmlspecialchars(strip_tags($this->activated));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":domain", $this->domain);
        $stmt->bindParam(":activated", $this->activated);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->email)) {
            $this->email = htmlspecialchars(strip_tags($this->email));
            $stmt->bindParam(":email", $this->email);
        }
        if (!empty($this->confirmation_key)) {
            $this->confirmation_key = htmlspecialchars(strip_tags($this->confirmation_key));
            $stmt->bindParam(":confirmation_key", $this->confirmation_key);
        }
        if (USE_IN_APP_PURCHASES) {
            $this->expire_time = htmlspecialchars(strip_tags($this->expire_time));
            $stmt->bindParam(":expire_time", $this->expire_time);
        }

        Logger::getInstance()->debug("Updating " . (string)$this);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function getCount()
    {
        $query = "SELECT count(*) FROM " . ACCOUNTS_DB_TABLE;
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetCount " . (string)$this);
        if ($stmt->execute()) {
            $number_of_rows = $stmt->fetchColumn();
            return $number_of_rows;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return -1;
    }

    public function getAll()
    {
        $query = "SELECT ac.id, ac.username, ac.domain, ac.activated, ac.confirmation_key, ac.email, al.alias FROM " . ACCOUNTS_DB_TABLE .
            " ac LEFT JOIN " . ALIAS_DB_TABLE . " al ON ac.id = al.account_id";
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();
        return $stmt;
    }

    public function getOne()
    {
        $query = "SELECT ac.id, ac.username, ac.domain, ac.activated, ac.confirmation_key, ac.email, ac.ip_address, al.alias FROM " . ACCOUNTS_DB_TABLE .
            " ac LEFT JOIN " . ALIAS_DB_TABLE . " al ON ac.id = al.account_id";

        if (!empty($this->id)) {
            $query = $query . " WHERE ac.id = ?";
            $this->id = htmlspecialchars(strip_tags($this->id));
        } elseif (!empty($this->username)) {
            $query = $query . " WHERE ac.username = ?";
            $this->username = htmlspecialchars(strip_tags($this->username));
            if (!empty($this->domain)) {
                $query = $query . " AND ac.domain = ?";
                $this->domain = htmlspecialchars(strip_tags($this->domain));
            }
        } elseif (!empty($this->email)) {
            $query = $query . " WHERE ac.email = ?";
            $this->email = htmlspecialchars(strip_tags($this->email));
        } elseif (!empty($this->confirmation_key)) {
            $query = $query . " WHERE ac.confirmation_key = ?";
            $this->confirmation_key = htmlspecialchars(strip_tags($this->confirmation_key));
        } else {
            return false;
        }

        $query = $query . " LIMIT 0,1";
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        if (!empty($this->id)) {
            $stmt->bindParam(1, $this->id);
        } elseif (!empty($this->username)) {
            $stmt->bindParam(1, $this->username);
            if (!empty($this->domain)) {
                $stmt->bindParam(2, $this->domain);
            }
        } elseif (!empty($this->email)) {
            $stmt->bindParam(1, $this->email);
        } elseif (!empty($this->confirmation_key)) {
            $stmt->bindParam(1, $this->confirmation_key);
        }

        Logger::getInstance()->debug("GetOne " . (string)$this);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row == null) {
                Logger::getInstance()->message("Couldn't find account matching " . (string)$this);
                return false;
            }

            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->domain = $row['domain'];
            $this->email = $row['email'];
            $this->activated = strval($row['activated']);
            $this->confirmation_key = $row['confirmation_key'];
            $this->ip_address = $row['ip_address'];
            $this->alias = $row['alias'];

            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }
}
