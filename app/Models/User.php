<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public $Id;
    public $Username;
    public $RoleID;
    public $ProjectId;
    public $Password;
    public $Name;

    public static function findAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM user_details", [], self::class);
    }

    public static function findById($Id)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM user_details WHERE Id = ?", [$Id], self::class);
    }

    public static function findByUsername($Username)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM user_details WHERE Username = ?", [$Username], self::class);
    }

    public function save()
    {
        $db = Database::getInstance();
        if ($this->Id) {
            // Update existing user
            $db->query("UPDATE user_details SET username = ?, roleId = ?, projectId = ?, password = ?, name = ? WHERE id = ?",
                [$this->Username, $this->RoleID, $this->ProjectId, $this->Password, $this->Name, $this->Id]);
        } else {
            // Insert new user
            $db->query("INSERT INTO user_details (username, roleId, projectId, password, name) VALUES (?, ?, ?, ?, ?)",
                [$this->Username, $this->RoleID, $this->ProjectId, $this->Password, $this->Name]);
            $this->id = $db->getConnection()->lastInsertId();
        }
    }

    public function delete() {
        $db = Database::getInstance();
        $db->query("DELETE FROM user_details WHERE Id = ?", [$this->Id]);
    }
}