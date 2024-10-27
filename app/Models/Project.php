<?php

namespace App\Models;

use App\Core\Database;

/**
* Project Model represents and manages projects in the bug tracking system
* Handles CRUD operations and validation for projects
* 
* Properties:
* @property int|null $Id Project's unique identifier 
* @property string $Project Project name
*/
class Project
{
   /** @var int|null Project's ID in database */
   public $Id;

   /** @var string Project name/title */
   public $Project;

   /**
    * Finds a project by its ID
    * 
    * @param int $Id Project ID to find
    * @return Project|null Project object if found, null otherwise
    * @throws \PDOException If database query fails
    */
   public static function findById(int $Id): ?Project
   {
       $db = Database::getInstance();
       return $db->fetch(
           "SELECT * FROM project WHERE Id = ?", 
           [$Id], 
           self::class
       );
   }

   /**
    * Retrieves all projects from database
    * 
    * @return array Array of Project objects
    * @throws \PDOException If database query fails
    */
   public static function findAll(): array
   {
       $db = Database::getInstance();
       return $db->fetchAll(
           "SELECT * FROM project", 
           [], 
           self::class
       );
   }

   /**
    * Saves or updates project in database
    * Handles both new project creation and existing project updates
    * 
    * Validation:
    * - Project name cannot be empty
    * - Project name limited to 255 characters
    * - Input sanitization for XSS prevention
    * 
    * @return void
    * @throws \InvalidArgumentException If validation fails
    * @throws \PDOException If database operation fails
    */
   public function save(): void
   {
       $db = Database::getInstance();

       // Validate and sanitize project name
       $this->validateAndSanitize();

       try {
           if ($this->Id) {
               $this->updateExisting($db);
           } else {
               $this->insertNew($db);
           }
       } catch (\PDOException $e) {
           error_log("Database error in Project save: " . $e->getMessage());
           throw new \PDOException("Failed to save project: " . $e->getMessage());
       }
   }

   /**
    * Validates and sanitizes project properties
    * 
    * @throws \InvalidArgumentException If validation fails
    */
   private function validateAndSanitize(): void
   {
       // Sanitize project name
       $this->Project = htmlspecialchars($this->Project, ENT_QUOTES, 'UTF-8');

       // Validate project name
       if (empty($this->Project) || strlen($this->Project) > 255) {
           throw new \InvalidArgumentException("Project name must not be empty and must be less than 255 characters");
       }
   }

   /**
    * Updates an existing project
    * 
    * @param Database $db Database instance
    * @return void
    * @throws \PDOException If update fails
    */
   private function updateExisting(Database $db): void
   {
       $db->query(
           "UPDATE project SET Project = ? WHERE Id = ?",
           [$this->Project, $this->Id]
       );
   }

   /**
    * Inserts a new project
    * 
    * @param Database $db Database instance
    * @return void
    * @throws \PDOException If insert fails
    */
   private function insertNew(Database $db): void
   {
       $db->query(
           "INSERT INTO project (Project) VALUES (?)",
           [$this->Project]
       );
       $this->Id = $db->getConnection()->lastInsertId();
   }
}