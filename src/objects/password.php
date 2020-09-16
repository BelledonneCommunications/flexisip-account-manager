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

class Password
{
    private $conn;

    public $id;
    public $account_id;
    public $password;
    public $algorithm;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function __toString()
    {
        $to_string = "Password: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->account_id)) {
            $to_string = $to_string . "account_id=" . $this->account_id . ", ";
        }
        if (!empty($this->password)) {
            $to_string = $to_string . "password=********"/* . $this->password*/ . ", ";
        }
        if (!empty($this->algorithm)) {
            $to_string = $to_string . "algorithm=" . $this->algorithm . ", ";
        }
        return substr($to_string, 0, -2);
    }

    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS " . ACCOUNTS_ALGO_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . ACCOUNTS_ALGO_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . ACCOUNTS_ALGO_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id INTEGER(11) UNSIGNED NOT NULL,
            password VARCHAR(255) NOT NULL,
            algorithm VARCHAR(10) NOT NULL DEFAULT 'MD5',
            PRIMARY KEY (id), UNIQUE KEY account (account_id, algorithm))";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . ACCOUNTS_ALGO_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . ACCOUNTS_ALGO_DB_TABLE;

        if (!empty($this->id)) {
            $query = $query . " WHERE id = ?";
            $this->id = htmlspecialchars(strip_tags($this->id));
        } elseif (!empty($this->account_id)) {
            $query = $query . " WHERE account_id = ?";
            $this->account_id = htmlspecialchars(strip_tags($this->account_id));
            if (!empty($this->algorithm)) {
                $query = $query . " AND algorithm = ?";
                $this->algorithm = htmlspecialchars(strip_tags($this->algorithm));
            }
        } else {
            return false;
        }

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        if (!empty($this->id)) {
            $stmt->bindParam(1, $this->id);
        } elseif (!empty($this->account_id)) {
            $stmt->bindParam(1, $this->account_id);
            if (!empty($this->algorithm)) {
                $stmt->bindParam(2, $this->algorithm);
            }
        }

        Logger::getInstance()->debug("Deleting " . (string)$this);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function create()
    {
        $query = "INSERT INTO " . ACCOUNTS_ALGO_DB_TABLE . " SET account_id=:account_id, password=:password, algorithm=:algorithm";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->algorithm = htmlspecialchars(strip_tags($this->algorithm));

        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":algorithm", $this->algorithm);

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
        $query = "UPDATE " . ACCOUNTS_ALGO_DB_TABLE . " SET account_id=:account_id, password=:password, algorithm=:algorithm WHERE id=:id";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->algorithm = htmlspecialchars(strip_tags($this->algorithm));

        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":algorithm", $this->algorithm);
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
        $query = "SELECT id, password, algorithm FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id = ?";

        $stmt = $this->conn->prepare($query);
        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $stmt->bindParam(1, $this->account_id);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();

        return $stmt;
    }

    public function getOne()
    {
        $query = "SELECT id, password, algorithm FROM " . ACCOUNTS_ALGO_DB_TABLE . " WHERE account_id = ?";

        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        if (!empty($this->algorithm)) {
            $query = $query . " AND algorithm = ?";
            $this->algorithm = htmlspecialchars(strip_tags($this->algorithm));
            if (!empty($this->password)) {
                $query = $query . " AND password = ?";
                $this->password = htmlspecialchars(strip_tags($this->password));
            }
        } elseif (!empty($this->password)) {
            $query = $query . " AND password = ?";
            $this->password = htmlspecialchars(strip_tags($this->password));
        }

        $query = $query . " LIMIT 0,1";
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->account_id);
        if (!empty($this->algorithm)) {
            $stmt->bindParam(2, $this->algorithm);
            if (!empty($this->password)) {
                $stmt->bindParam(3, $this->password);
            }
        } elseif (!empty($this->password)) {
            $stmt->bindParam(2, $this->password);
        }

        Logger::getInstance()->debug("GetOne " . (string)$this);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row == null) {
                Logger::getInstance()->message("Couldn't find password matching " . (string)$this);
                return false;
            }

            $this->id = $row['id'];
            $this->password = $row['password'];
            $this->algorithm = $row['algorithm'];
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }
}
