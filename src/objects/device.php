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

class Device
{
    private $conn;

    public $id;
    public $manufacturer;
    public $model;
    public $status;
    public $delay;
    public $hardware_echo_canceller;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function __toString()
    {
        $to_string = "Device: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->manufacturer)) {
            $to_string = $to_string . "manufacturer=" . $this->manufacturer . ", ";
        }
        if (!empty($this->model)) {
            $to_string = $to_string . "model=" . $this->model . ", ";
        }
        if (!empty($this->status)) {
            $to_string = $to_string . "status=" . $this->status . ", ";
        }
        if (!empty($this->delay)) {
            $to_string = $to_string . "delay=" . $this->delay . ", ";
        }
        if (!empty($this->hardware_echo_canceller)) {
            $to_string = $to_string . "hardware_echo_canceller=" . $this->hardware_echo_canceller . ", ";
        }
        return substr($to_string, 0, -2);
    }

    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS " . DEVICES_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . DEVICES_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . DEVICES_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            manufacturer VARCHAR(64) NOT NULL,
            model VARCHAR(34) NOT NULL,
            status VARCHAR(34) NOT NULL,
            delay INTEGER(4) NOT NULL DEFAULT 0,
            hardware_echo_canceller TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id))";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . DEVICES_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . DEVICES_DB_TABLE . " WHERE id = ?";

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
        $query = "INSERT INTO " . DEVICES_DB_TABLE . " SET manufacturer=:manufacturer, model=:model, status=:status,
            delay=:delay, hardware_echo_canceller=:hardware_echo_canceller";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->delay = htmlspecialchars(strip_tags($this->delay));
        $this->hardware_echo_canceller = htmlspecialchars(strip_tags($this->hardware_echo_canceller));

        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":delay", $this->delay);
        $stmt->bindParam(":hardware_echo_canceller", $this->hardware_echo_canceller);

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
        $query = "UPDATE " . DEVICES_DB_TABLE . " SET manufacturer=:manufacturer, model=:model, status=:status,
            delay=:delay, hardware_echo_canceller=:hardware_echo_canceller WHERE id=:id";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->delay = htmlspecialchars(strip_tags($this->delay));
        $this->hardware_echo_canceller = htmlspecialchars(strip_tags($this->hardware_echo_canceller));

        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":delay", $this->delay);
        $stmt->bindParam(":hardware_echo_canceller", $this->hardware_echo_canceller);
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
        $query = "SELECT id, manufacturer, model, status, delay, hardware_echo_canceller FROM " . DEVICES_DB_TABLE;
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();
        return $stmt;
    }
}
