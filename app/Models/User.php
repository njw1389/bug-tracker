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

        // Sanitize and validate inputs
        $this->Username = htmlspecialchars($this->Username, ENT_QUOTES, 'UTF-8');
        $this->RoleID = filter_var($this->RoleID, FILTER_VALIDATE_INT);
        $this->ProjectId = $this->ProjectId ? filter_var($this->ProjectId, FILTER_VALIDATE_INT) : null;
        $this->Name = htmlspecialchars($this->Name, ENT_QUOTES, 'UTF-8');

        if ($this->Id) {
            // Update existing user
            $query = "UPDATE user_details SET username = ?, roleId = ?, projectId = ?, name = ?";
            $params = [$this->Username, $this->RoleID, $this->ProjectId, $this->Name];

            if ($this->Password) {
                $query .= ", password = ?";
                $params[] = password_hash($this->Password, PASSWORD_DEFAULT);
            }

            $query .= " WHERE id = ?";
            $params[] = $this->Id;

            $db->query($query, $params);
        } else {
            // Insert new user
            $query = "INSERT INTO user_details (username, roleId, projectId, password, name) VALUES (?, ?, ?, ?, ?)";
            $params = [
                $this->Username,
                $this->RoleID,
                $this->ProjectId,
                password_hash($this->Password, PASSWORD_DEFAULT),
                $this->Name
            ];

            $db->query($query, $params);
            $this->Id = $db->getConnection()->lastInsertId();
        }
    }

    public function delete() {
        $db = Database::getInstance();
        $db->query("DELETE FROM user_details WHERE Id = ?", [$this->Id]);
    }
}