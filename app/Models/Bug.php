<?php

namespace App\Models;

use App\Core\Database;

/**
 * Bug Model represents and manages bug tracking entries in the system
 * Handles CRUD operations and bug-related business logic
 */
class Bug
{
    /** @var int|null */
    public $id;
    
    /** @var int Project the bug belongs to */
    public $projectId;
    
    /** @var int User who created the bug */
    public $ownerId;
    
    /** @var int|null User assigned to fix the bug */
    public $assignedToId;
    
    /** @var int Current status (1=Unassigned, 2=Assigned, 3=Closed) */
    public $statusId;
    
    /** @var int Priority level (1=Low to 4=Urgent) */
    public $priorityId;
    
    /** @var string Brief description of the bug */
    public $summary;
    
    /** @var string Detailed description of the bug */
    public $description;
    
    /** @var string|null Description of how the bug was fixed */
    public $fixDescription;
    
    /** @var string Date the bug was reported */
    public $dateRaised;
    
    /** @var string|null Target date for resolution */
    public $targetDate;
    
    /** @var string|null Date the bug was closed */
    public $dateClosed;

    /**
     * Finds a bug by its ID
     * 
     * @param int $id Bug ID to find
     * @return Bug|null Bug object if found, null otherwise
     */
    public static function findById($id)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM bugs WHERE id = ?", [$id], self::class);
    }

    /**
     * Finds all bugs for a specific project
     * 
     * @param int $projectId Project ID to find bugs for
     * @return array Array of Bug objects
     */
    public static function findByProject($projectId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM bugs WHERE projectId = ?", 
            [$projectId], 
            self::class
        );
    }

    /**
     * Finds bugs assigned to a specific user in a specific project
     * 
     * @param int $userId User ID of assignee
     * @param int $projectId Project ID to filter by
     * @return array Array of Bug objects
     */
    public static function findByAssignedUser($userId, $projectId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM bugs WHERE assignedToId = ? AND projectId = ?",
            [$userId, $projectId],
            self::class
        );
    }

    /**
     * Finds bugs by project and assigned user combination
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @return array Array of Bug objects
     */
    public static function findByProjectAndAssignedUser($projectId, $userId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM bugs WHERE projectId = ? AND assignedToId = ?",
            [$projectId, $userId],
            self::class
        );
    }

    /**
     * Unassigns all bugs from a specific user
     * Sets status to unassigned for affected bugs
     * 
     * @param int $userId User ID to unassign bugs from
     * @return void
     */
    public static function unassignUserFromBugs($userId)
    {
        $db = Database::getInstance();
        $unassignedStatusId = 1;
        
        $db->query(
            "UPDATE bugs SET assignedToId = NULL, statusId = ? WHERE assignedToId = ?",
            [$unassignedStatusId, $userId]
        );
    }

    /**
     * Reassigns bugs from a deleted user to the default manager
     * Used when deleting users to maintain bug ownership
     * 
     * @param int $userId ID of user being deleted
     * @return void
     */
    public static function reassignBugsToManager($userId)
    {
        $db = Database::getInstance();
        
        // Find default manager
        $managerQuery = "SELECT Id FROM user_details WHERE Username = 'manager' AND RoleID = 2 LIMIT 1";
        $manager = $db->fetch($managerQuery);
        
        if (!$manager) {
            error_log("No manager found with username 'manager'. Bugs will remain assigned to deleted user.");
            return;
        }
        
        // Reassign bugs to manager
        $db->query(
            "UPDATE bugs SET ownerId = ? WHERE ownerId = ?",
            [$manager['Id'], $userId]
        );
    }

    /**
     * Deletes this bug from the database
     * 
     * @return bool True on success
     * @throws \Exception If deletion fails
     */
    public function delete()
    {
        $db = Database::getInstance();

        try {
            $db->query("DELETE FROM bugs WHERE id = ?", [$this->id]);
            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while deleting the bug");
        }
    }

    /**
     * Retrieves all bugs from the database
     * 
     * @return array Array of all Bug objects
     */
    public static function findAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM bugs", [], self::class);
    }

    /**
     * Saves or updates bug in the database
     * Handles both new bug creation and existing bug updates
     * 
     * Validation:
     * - Required fields presence
     * - Field length limits
     * - Data type validation
     * - Date format validation
     * 
     * @return bool True on success
     * @throws \InvalidArgumentException If validation fails
     * @throws \Exception If save operation fails
     */
    public function save()
    {
        $db = Database::getInstance();

        // Validate and sanitize all inputs
        $this->validateAndSanitizeFields();

        try {
            if ($this->id) {
                $this->updateExistingBug($db);
            } else {
                $this->insertNewBug($db);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while saving the bug");
        }
    }

    /**
     * Validates and formats a date string
     * 
     * @param string|null $date Date string to validate
     * @return string|null Formatted date or null
     * @throws \InvalidArgumentException If date format is invalid
     */
    private function validateDate($date)
    {
        if (!$date) {
            return null;
        }
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid date format");
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Validates and sanitizes all bug fields
     * 
     * @throws \InvalidArgumentException If validation fails
     */
    private function validateAndSanitizeFields()
    {
        $this->projectId = filter_var($this->projectId, FILTER_VALIDATE_INT);
        $this->ownerId = filter_var($this->ownerId, FILTER_VALIDATE_INT);
        $this->assignedToId = $this->assignedToId ? filter_var($this->assignedToId, FILTER_VALIDATE_INT) : null;
        $this->statusId = filter_var($this->statusId, FILTER_VALIDATE_INT);
        $this->priorityId = filter_var($this->priorityId, FILTER_VALIDATE_INT);
        $this->summary = htmlspecialchars($this->summary, ENT_QUOTES, 'UTF-8');
        $this->description = htmlspecialchars($this->description, ENT_QUOTES, 'UTF-8');
        $this->fixDescription = $this->fixDescription ? htmlspecialchars($this->fixDescription, ENT_QUOTES, 'UTF-8') : null;
        $this->dateRaised = $this->validateDate($this->dateRaised);
        $this->targetDate = !empty($this->targetDate) ? $this->validateDate($this->targetDate) : null;
        $this->dateClosed = $this->validateDate($this->dateClosed);

        if (!$this->validateRequiredFields()) {
            throw new \InvalidArgumentException("Invalid input data");
        }
    }

    /**
     * Validates presence and format of required fields
     * 
     * @return bool True if all required fields are valid
     */
    private function validateRequiredFields()
    {
        return $this->projectId && 
               $this->ownerId && 
               $this->statusId && 
               $this->priorityId && 
               $this->summary && 
               $this->description && 
               strlen($this->summary) <= 255 && 
               strlen($this->description) <= 1000;
    }

    /**
     * Updates an existing bug in the database
     * 
     * @param Database $db Database instance
     * @return void
     */
    private function updateExistingBug($db)
    {
        $query = "UPDATE bugs SET 
            projectId = ?, ownerId = ?, assignedToId = ?, statusId = ?, priorityId = ?,
            summary = ?, description = ?, fixDescription = ?, dateRaised = ?, targetDate = ?, 
            dateClosed = ? WHERE id = ?";
        
        $params = [
            $this->projectId, $this->ownerId, $this->assignedToId, $this->statusId,
            $this->priorityId, $this->summary, $this->description, $this->fixDescription,
            $this->dateRaised, $this->targetDate, $this->dateClosed, $this->id
        ];
        
        $db->query($query, $params);
    }

    /**
     * Inserts a new bug into the database
     * 
     * @param Database $db Database instance
     * @return void
     */
    private function insertNewBug($db)
    {
        $query = "INSERT INTO bugs (
            projectId, ownerId, assignedToId, statusId, priorityId, summary,
            description, fixDescription, dateRaised, targetDate, dateClosed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $this->projectId, $this->ownerId, $this->assignedToId, $this->statusId,
            $this->priorityId, $this->summary, $this->description, $this->fixDescription,
            $this->dateRaised, $this->targetDate, $this->dateClosed
        ];
        
        $db->query($query, $params);
        $this->id = $db->getConnection()->lastInsertId();
    }
}