<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public $id;
    public $username;
    public $roleId;
    public $projectId;
    public $password;
    public $name;

    public static function findById($id)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM user_details WHERE id = ?", [$id], self::class);
    }

    public static function findByUsername($username)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM user_details WHERE username = ?", [$username], self::class);
    }

    public function save()
    {
        $db = Database::getInstance();
        if ($this->id) {
            // Update existing user
            $db->query("UPDATE user_details SET username = ?, roleId = ?, projectId = ?, password = ?, name = ? WHERE id = ?",
                [$this->username, $this->roleId, $this->projectId, $this->password, $this->name, $this->id]);
        } else {
            // Insert new user
            $db->query("INSERT INTO user_details (username, roleId, projectId, password, name) VALUES (?, ?, ?, ?, ?)",
                [$this->username, $this->roleId, $this->projectId, $this->password, $this->name]);
            $this->id = $db->getConnection()->lastInsertId();
        }
    }
}