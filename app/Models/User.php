<?php

namespace App\Models;

use App\Core\Database;
use App\Core\FileCache as Cache;

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
        $cacheKey = "all_users";
        $cachedUsers = Cache::get($cacheKey);
        
        if ($cachedUsers !== false) {
            return $cachedUsers;
        }

        $db = Database::getInstance();
        $users = $db->fetchAll("SELECT * FROM user_details", [], self::class);
        
        Cache::set($cacheKey, $users);

        return $users;
    }

    public static function findById($Id)
    {
        $cacheKey = "user_$Id";
        $cachedUser = Cache::get($cacheKey);
        
        if ($cachedUser !== false) {
            return $cachedUser;
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM user_details WHERE Id = ?", [$Id], self::class);
        
        if ($user) {
            Cache::set($cacheKey, $user);
        }

        return $user;
    }

    public static function findByUsername($Username)
    {
        $cacheKey = "user_username_$Username";
        $cachedUser = Cache::get($cacheKey);
        
        if ($cachedUser !== false) {
            return $cachedUser;
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM user_details WHERE Username = ?", [$Username], self::class);
        
        if ($user) {
            Cache::set($cacheKey, $user);
        }

        return $user;
    }

    public function save()
    {
        $db = Database::getInstance();

        // Sanitize and validate inputs
        $this->Username = htmlspecialchars($this->Username, ENT_QUOTES, 'UTF-8');
        $this->RoleID = filter_var($this->RoleID, FILTER_VALIDATE_INT);
        $this->ProjectId = $this->ProjectId ? filter_var($this->ProjectId, FILTER_VALIDATE_INT) : null;
        $this->Name = htmlspecialchars($this->Name, ENT_QUOTES, 'UTF-8');

        try {
            // Check for existing username
            $existingUser = self::findByUsername($this->Username);
            if ($existingUser && $existingUser->Id !== $this->Id) {
                throw new \Exception("Username already exists");
            }

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

            // Clear relevant caches
            Cache::delete("user_" . $this->Id);
            Cache::delete("user_username_" . $this->Username);
            Cache::delete("all_users");

            return true;
        } catch (\PDOException $e) {
            // Log the error and throw a generic exception
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while saving the user");
        }
    }

    public function delete() 
    {
        $db = Database::getInstance();
        $db->query("DELETE FROM user_details WHERE Id = ?", [$this->Id]);

        // Clear relevant caches
        Cache::delete("user_" . $this->Id);
        Cache::delete("user_username_" . $this->Username);
        Cache::delete("all_users");
    }
}