<?php

namespace App\Models;

use App\Core\Database;

/**
* User Model represents and manages user accounts in the system
* Handles user authentication, CRUD operations, and role management
* 
* Properties:
* @property int|null $Id User's unique identifier
* @property string $Username User's login name
* @property int $RoleID User's role level (1=Admin, 2=Manager, 3=User)
* @property int|null $ProjectId User's assigned project
* @property string $Password User's hashed password
* @property string $Name User's display name
*/
class User
{
   /** @var int|null */
   public $Id;

   /** @var string */
   public $Username;

   /** @var int */
   public $RoleID;

   /** @var int|null */
   public $ProjectId;

   /** @var string */
   public $Password;

   /** @var string */
   public $Name;

   /** @var array Valid role IDs and their names */
   private const ROLES = [
       1 => 'Admin',
       2 => 'Manager',
       3 => 'User'
   ];

   /**
    * Retrieves all users from database
    * 
    * @return array Array of User objects
    * @throws \PDOException If query fails
    */
   public static function findAll()
   {
       $db = Database::getInstance();
       return $db->fetchAll("SELECT * FROM user_details", [], self::class);
   }

   /**
    * Finds user by ID
    * 
    * @param int $Id User ID to find
    * @return User|null User object if found, null otherwise
    * @throws \PDOException If query fails
    */
   public static function findById($Id)
   {
       $db = Database::getInstance();
       return $db->fetch(
           "SELECT * FROM user_details WHERE Id = ?",
           [$Id],
           self::class
       );
   }

   /**
    * Finds all users with specific role
    * 
    * @param int $roleId Role ID to search for
    * @return array Array of User objects with specified role
    * @throws \PDOException If query fails
    */
   public static function findByRole($roleId)
   {
       $db = Database::getInstance();
       return $db->fetchAll(
           "SELECT * FROM user_details WHERE roleId = ?",
           [$roleId],
           self::class
       );
   }

   /**
    * Finds user by username
    * 
    * @param string $Username Username to search for
    * @return User|null User object if found, null otherwise
    * @throws \PDOException If query fails
    */
   public static function findByUsername($Username)
   {
       $db = Database::getInstance();
       return $db->fetch(
           "SELECT * FROM user_details WHERE Username = ?",
           [$Username],
           self::class
       );
   }

   /**
    * Saves or updates user in database
    * Handles both new user creation and existing user updates
    * 
    * Validation:
    * - Username uniqueness
    * - Password requirements for new users
    * - Role validation
    * - Input sanitization
    * 
    * @return bool True on success
    * @throws \InvalidArgumentException If validation fails
    * @throws \Exception If user already exists or save fails
    */
   public function save()
   {
       $db = Database::getInstance();

       // Validate and sanitize all inputs
       $this->validateAndSanitize();

       try {
           // Check username uniqueness
           if ($this->usernameExists()) {
               throw new \Exception("Username already exists");
           }

           if ($this->Id) {
               $this->updateExisting($db);
           } else {
               $this->validateNewUser();
               $this->insertNew($db);
           }

           return true;
       } catch (\PDOException $e) {
           error_log("Database error: " . $e->getMessage());
           throw new \Exception("An error occurred while saving the user");
       }
   }

   /**
    * Deletes user from database
    * 
    * @throws \PDOException If deletion fails
    */
   public function delete()
   {
       $db = Database::getInstance();
       $db->query("DELETE FROM user_details WHERE Id = ?", [$this->Id]);
   }

   /**
    * Validates and sanitizes user properties
    * 
    * @throws \InvalidArgumentException If validation fails
    */
   private function validateAndSanitize()
   {
       // Sanitize string inputs
       $this->Username = htmlspecialchars($this->Username, ENT_QUOTES, 'UTF-8');
       $this->Name = htmlspecialchars($this->Name, ENT_QUOTES, 'UTF-8');

       // Validate integers
       $this->RoleID = filter_var($this->RoleID, FILTER_VALIDATE_INT);
       $this->ProjectId = $this->ProjectId ? 
           filter_var($this->ProjectId, FILTER_VALIDATE_INT) : null;

       // Validate role
       if (!array_key_exists($this->RoleID, self::ROLES)) {
           throw new \InvalidArgumentException("Invalid role ID");
       }

       // Validate string lengths
       if (strlen($this->Username) > 255 || strlen($this->Name) > 255) {
           throw new \InvalidArgumentException("Username and Name must be less than 255 characters");
       }
   }

   /**
    * Checks if username already exists
    * 
    * @return bool True if username exists for different user
    */
   private function usernameExists()
   {
       $existingUser = self::findByUsername($this->Username);
       return $existingUser && $existingUser->Id !== $this->Id;
   }

   /**
    * Validates requirements for new user
    * 
    * @throws \InvalidArgumentException If validation fails
    */
   private function validateNewUser()
   {
       if (empty($this->Password)) {
           throw new \InvalidArgumentException("Password is required for new users");
       }
   }

   /**
    * Updates existing user in database
    * 
    * @param Database $db Database instance
    * @throws \PDOException If update fails
    */
   private function updateExisting($db)
   {
       $query = "UPDATE user_details SET username = ?, roleId = ?, projectId = ?, name = ?";
       $params = [$this->Username, $this->RoleID, $this->ProjectId, $this->Name];

       if ($this->Password) {
           $query .= ", password = ?";
           $params[] = password_hash($this->Password, PASSWORD_DEFAULT);
       }

       $query .= " WHERE id = ?";
       $params[] = $this->Id;

       $db->query($query, $params);
   }

   /**
    * Inserts new user into database
    * 
    * @param Database $db Database instance
    * @throws \PDOException If insert fails
    */
   private function insertNew($db)
   {
       $query = "INSERT INTO user_details (username, roleId, projectId, password, name) 
                VALUES (?, ?, ?, ?, ?)";
       
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