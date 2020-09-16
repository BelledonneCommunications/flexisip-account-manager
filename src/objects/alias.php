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

class Alias
{
    private $conn;

    public $id;
    public $account_id;
    public $alias;
    public $domain;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function __toString()
    {
        $to_string = "Alias: ";
        if (!empty($this->id)) {
            $to_string = $to_string . "id=" . $this->id . ", ";
        }
        if (!empty($this->account_id)) {
            $to_string = $to_string . "account_id=" . $this->account_id . ", ";
        }
        if (!empty($this->alias)) {
            $to_string = $to_string . "alias=" . $this->alias . ", ";
        }
        if (!empty($this->domain)) {
            $to_string = $to_string . "domain=" . $this->domain . ", ";
        }
        return substr($to_string, 0, -2);
    }

    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS " . ALIAS_DB_TABLE;
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Dropping table " . ALIAS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function createTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . ALIAS_DB_TABLE . " (
            id INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id INTEGER(11) UNSIGNED NOT NULL,
            alias VARCHAR(64) NOT NULL,
            domain VARCHAR(64) NOT NULL,
            PRIMARY KEY (id), UNIQUE KEY identity (alias, domain))";
        $stmt = $this->conn->prepare($query);

        Logger::getInstance()->debug("Creating table " . ALIAS_DB_TABLE);
        if ($stmt->execute()) {
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . ALIAS_DB_TABLE;

        if (!empty($this->id)) {
            $query = $query . " WHERE id = ?";
            $this->id = htmlspecialchars(strip_tags($this->id));
        } elseif (!empty($this->account_id)) {
            $query = $query . " WHERE account_id = ?";
            $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        } elseif (!empty($this->alias)) {
            $query = $query . " WHERE alias = ?";
            $this->alias = htmlspecialchars(strip_tags($this->alias));
            if (!empty($this->domain)) {
                $query = $query . " AND domain = ?";
                $this->domain = htmlspecialchars(strip_tags($this->domain));
            }
        } else {
            return false;
        }


        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));

        if (!empty($this->id)) {
            $stmt->bindParam(1, $this->id);
        } elseif (!empty($this->account_id)) {
            $stmt->bindParam(1, $this->account_id);
        } elseif (!empty($this->alias)) {
            $stmt->bindParam(1, $this->alias);
            if (!empty($this->domain)) {
                $stmt->bindParam(2, $this->domain);
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
        $query = "INSERT INTO " . ALIAS_DB_TABLE . " SET account_id=:account_id, alias=:alias, domain=:domain";

        $stmt = $this->conn->prepare($query);
        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->alias = htmlspecialchars(strip_tags($this->alias));
        $this->domain = htmlspecialchars(strip_tags($this->domain));

        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":alias", $this->alias);
        $stmt->bindParam(":domain", $this->domain);

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
        $query = "UPDATE " . ALIAS_DB_TABLE . " SET account_id=:account_id, alias=:alias, domain=:domain WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->alias = htmlspecialchars(strip_tags($this->alias));
        $this->domain = htmlspecialchars(strip_tags($this->domain));

        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":alias", $this->alias);
        $stmt->bindParam(":domain", $this->domain);
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
        $query = "SELECT id, account_id, alias, domain FROM " . ALIAS_DB_TABLE;

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);
        Logger::getInstance()->debug("GetAll " . (string)$this);
        $stmt->execute();

        return $stmt;
    }

    public function getOne()
    {
        $query = "SELECT id, account_id, alias, domain FROM " . ALIAS_DB_TABLE;

        if (!empty($this->id)) {
            $query = $query . " WHERE id = ?";
            $this->id = htmlspecialchars(strip_tags($this->id));
        } elseif (!empty($this->account_id)) {
            $query = $query . " WHERE account_id = ?";
            $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        } elseif (!empty($this->alias)) {
            $query = $query . " WHERE alias = ?";
            $this->alias = htmlspecialchars(strip_tags($this->alias));
            if (!empty($this->domain)) {
                $query = $query . " AND domain = ?";
                $this->domain = htmlspecialchars(strip_tags($this->domain));
            }
        } else {
            return false;
        }

        $query = $query . " LIMIT 0,1";

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $this->conn->prepare($query);

        if (!empty($this->id)) {
            $stmt->bindParam(1, $this->id);
        } elseif (!empty($this->account_id)) {
            $stmt->bindParam(1, $this->account_id);
        } elseif (!empty($this->alias)) {
            $stmt->bindParam(1, $this->alias);
            if (!empty($this->domain)) {
                $stmt->bindParam(2, $this->domain);
            }
        }

        Logger::getInstance()->debug("GetOne " . (string)$this);
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row == null) {
                Logger::getInstance()->message("Couldn't find alias matching " . (string)$this);
                return false;
            }

            $this->id = $row['id'];
            $this->account_id = $row['account_id'];
            $this->alias = $row['alias'];
            $this->domain = $row['domain'];
            return true;
        }
        Logger::getInstance()->error($stmt->errorInfo());
        return false;
    }
}
