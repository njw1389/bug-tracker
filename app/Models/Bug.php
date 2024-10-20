<?php

namespace App\Models;

use App\Core\Database;

class Bug
{
    public $id;
    public $projectId;
    public $ownerId;
    public $assignedToId;
    public $statusId;
    public $priorityId;
    public $summary;
    public $description;
    public $fixDescription;
    public $dateRaised;
    public $targetDate;
    public $dateClosed;

    public static function findById($id)
    {
        $db = Database::getInstance();
        $bug = $db->fetch("SELECT * FROM bugs WHERE id = ?", [$id], self::class);
        return $bug;
    }

    public static function findByProject($projectId)
    {
        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs WHERE projectId = ?", [$projectId], self::class);
        return $bugs;
    }

    public static function findByAssignedUser($userId, $projectId)
    {
        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs WHERE assignedToId = ? AND projectId = ?", [$userId, $projectId], self::class);
        return $bugs;
    }

    public static function unassignUserFromBugs($userId) {
        $db = Database::getInstance();
        $db->query("UPDATE bugs SET assignedToId = NULL WHERE assignedToId = ?", [$userId]);
    }

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

    public static function deleteByProject($projectId)
    {
        $db = Database::getInstance();

        try {
            $db->query("DELETE FROM bugs WHERE projectId = ?", [$projectId]);
            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while deleting bugs for the project");
        }
    }


    public static function findAll()
    {
        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs", [], self::class);
        return $bugs;
    }

    public function save()
    {
        $db = Database::getInstance();

        // Validate and sanitize inputs
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

        // Validate required fields
        if (!$this->projectId || !$this->ownerId || !$this->statusId || !$this->priorityId || !$this->summary || !$this->description) {
            throw new \InvalidArgumentException("Missing required fields");
        }

        try {
            if ($this->id) {
                // Update existing bug
                $query = "UPDATE bugs SET 
                    projectId = ?, ownerId = ?, assignedToId = ?, statusId = ?, priorityId = ?,
                    summary = ?, description = ?, fixDescription = ?, dateRaised = ?, targetDate = ?, dateClosed = ?
                    WHERE id = ?";
                $params = [
                    $this->projectId, $this->ownerId, $this->assignedToId, $this->statusId, $this->priorityId,
                    $this->summary, $this->description, $this->fixDescription, $this->dateRaised, $this->targetDate,
                    $this->dateClosed, $this->id
                ];
                $db->query($query, $params);
            } else {
                // Insert new bug
                $query = "INSERT INTO bugs 
                    (projectId, ownerId, assignedToId, statusId, priorityId, summary, description,
                    fixDescription, dateRaised, targetDate, dateClosed)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $this->projectId, $this->ownerId, $this->assignedToId, $this->statusId, $this->priorityId,
                    $this->summary, $this->description, $this->fixDescription, $this->dateRaised, $this->targetDate,
                    $this->dateClosed
                ];
                $db->query($query, $params);
                $this->id = $db->getConnection()->lastInsertId();
            }

            return true;
        } catch (\PDOException $e) {
            // Log the error and throw a generic exception
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while saving the bug");
        }
    }

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
}