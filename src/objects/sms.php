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

class SMS
{
    private $conn;

    public $id;
    public $phone;
    public $last_sms;
    public $count;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function __toString()
    {
        $to_string = "SMS: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->phone)) {
            $to_string = $to_string . "phone=" . $this->phone . ", ";
        }
        if (!empty($this->last_sms)) {
            $to_string = $to_string . "last_sms=" . $this->last_sms . ", ";
        }
        if (!empty($this->count)) {
            $to_string = $to_string . "count=" . $this->count . ", ";
        }
        return substr($to_string, 0, -2);
    }

    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS " . SMS_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . SMS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . SMS_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            phone VARCHAR(64),
            last_sms BIGINT(15) UNSIGNED DEFAULT 0,
            count TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id), UNIQUE KEY phone (phone))";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . SMS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . SMS_DB_TABLE . " WHERE id = ?";

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
        $query = "INSERT INTO " . SMS_DB_TABLE . " SET phone=:phone, last_sms=:last_sms, count=:count";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->last_sms = htmlspecialchars(strip_tags($this->last_sms));
        $this->count = htmlspecialchars(strip_tags($this->count));

        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":last_sms", $this->last_sms);
        $stmt->bindParam(":count", $this->count);

        Logger::getInstance()->debug("Creating " . (string)$this);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . SMS_DB_TABLE . " SET phone=:phone, last_sms=:last_sms, count=:count WHERE id=:id";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->last_sms = htmlspecialchars(strip_tags($this->last_sms));
        $this->count = htmlspecialchars(strip_tags($this->count));

        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":last_sms", $this->last_sms);
        $stmt->bindParam(":count", $this->count);
        $stmt->bindParam(":id", $this->id);

        Logger::getInstance()->debug("Updating " . (string)$this);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function getAll()
    {
        $query = "SELECT id, phone, last_sms, count FROM " . SMS_DB_TABLE;
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();
        return $stmt;
    }

    public function getOne()
    {
        $query = "SELECT id, phone, last_sms, count FROM " . SMS_DB_TABLE . " WHERE phone = ?";
        $stmt = $this->conn->prepare($query);
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $stmt->bindParam(1, $this->phone);

        Logger::getInstance()->debug("GetOne " . (string)$this);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row == null) {
                Logger::getInstance()->message("Couldn't find SMS matching " . (string)$this);
                return false;
            }

            $this->id = $row['id'];
            $this->phone = $row['phone'];
            $this->last_sms = $row['last_sms'];
            $this->count = $row['count'];
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }
}
