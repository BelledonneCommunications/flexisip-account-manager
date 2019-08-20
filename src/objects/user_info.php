<?php

/*
	Flexisip Account Manager is a set of tools to manage SIP accounts.
	Copyright (C) 2019 Belledonne Communications SARL, All rights reserved.

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

class UserInfo {
    private $conn;

    public $id;
    public $account_id;
    public $firstname;
    public $lastname;
    public $gender;
    public $subscribe;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function __toString() {
        $to_string = "UserInfo: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->account_id)) {
            $to_string = $to_string . "account_id=" . $this->account_id . ", ";
        }
        if (!empty($this->firstname)) {
            $to_string = $to_string . "firstname=" . $this->firstname . ", ";
        }
        if (!empty($this->lastname)) {
            $to_string = $to_string . "lastname=" . $this->lastname . ", ";
        }
        if (!empty($this->gender)) {
            $to_string = $to_string . "gender=" . $this->gender . ", ";
        }
        if (!empty($this->subscribe)) {
            $to_string = $to_string . "subscribe=" . $this->subscribe . ", ";
        }
        return substr($to_string, 0, -2);
    }

    function dropTable() {
        $query = "DROP TABLE IF EXISTS " . USER_INFO_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . USER_INFO_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . USER_INFO_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id INTEGER(11) UNSIGNED NOT NULL,
            firstname VARCHAR(128) NOT NULL,
            lastname VARCHAR(128) NOT NULL,
            gender enum('male','female') NOT NULL,
            subscribe enum('0','1') NOT NULL DEFAULT '0',
            PRIMARY KEY (id))";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . USER_INFO_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . USER_INFO_DB_TABLE . " WHERE id = ?";

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

    function create() {
        $query = "INSERT INTO " . USER_INFO_DB_TABLE . " SET account_id=:account_id, firstname=:firstname, lastname=:lastname, gender=:gender, subscribe=:subscribe";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->subscribe = htmlspecialchars(strip_tags($this->subscribe));

        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":subscribe", $this->subscribe);

        Logger::getInstance()->debug("Creating " . (string)$this);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    function update() {
        $query = "UPDATE " . USER_INFO_DB_TABLE . " SET firstname=:firstname, lastname=:lastname, subscribe=:subscribe";

        $query = $query . " WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->subscribe = htmlspecialchars(strip_tags($this->subscribe));

        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":subscribe", $this->subscribe);
        $stmt->bindParam(":id", $this->id);

        Logger::getInstance()->debug("Updating " . (string)$this);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    function getAll() {
        $query = "SELECT id, account_id, firstname, lastname, gender, subscribe FROM " . USER_INFO_DB_TABLE;
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();
        return $stmt;
    }

    function getOne() {
        $query = "SELECT id, account_id, firstname, lastname, gender, subscribe FROM " . USER_INFO_DB_TABLE;

        if (!empty($this->id)) {
            $query = $query . " WHERE id = ?";
            $this->id = htmlspecialchars(strip_tags($this->id));
        } else if (!empty($this->account_id)) {
            $query = $query . " WHERE account_id = ?";
            $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        } else if (!empty($this->lastname)) {
            $query = $query . " WHERE lastname = ?";
            $this->lastname = htmlspecialchars(strip_tags($this->lastname));
            if (!empty($this->firstname)) {
                $query = $query . " AND firstname = ?";
                $this->firstname = htmlspecialchars(strip_tags($this->firstname));
            }
        } else {
            return false;
        }

        $query = $query . " LIMIT 0,1";
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        if (!empty($this->id)) {
            $stmt->bindParam(1, $this->id);
        }  else if (!empty($this->account_id)) {
            $stmt->bindParam(1, $this->account_id);
        } else if (!empty($this->lastname)) {
            $stmt->bindParam(1, $this->lastname);
            if (!empty($this->firstname)) {
                $stmt->bindParam(2, $this->firstname);
            }
        }

        Logger::getInstance()->debug("GetOne " . (string)$this);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row == null) {
                Logger::getInstance()->message("Couldn't find account matching " . (string)$this);
                return false;
            }

            $this->id = $row['id'];
            $this->account_id = $row['account_id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->gender = $row['gender'];
            $this->subscribe = $row['subscribe'];
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }
}

?>