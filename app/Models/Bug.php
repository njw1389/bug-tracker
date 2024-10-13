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
        return $db->fetch("SELECT * FROM bugs WHERE id = ?", [$id], self::class);
    }

    public static function findByProject($projectId)
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM bugs WHERE projectId = ?", [$projectId], self::class);
    }

    public static function unassignUserFromBugs($userId) {
        $db = Database::getInstance();
        $db->query("UPDATE bugs SET assignedToId = NULL WHERE assignedToId = ?", [$userId]);
    }

    public static function findAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM bugs", [], self::class);
    }

    public function save()
    {
        $db = Database::getInstance();

        // Validate and sanitize inputs
        $this->projectId = filter_var($this->projectId, FILTER_VALIDATE_INT);
        $this->ownerId = filter_var($this->ownerId, FILTER_VALIDATE_INT);
        $this->assignedToId = filter_var($this->assignedToId, FILTER_VALIDATE_INT);
        $this->statusId = filter_var($this->statusId, FILTER_VALIDATE_INT);
        $this->priorityId = filter_var($this->priorityId, FILTER_VALIDATE_INT);
        $this->summary = filter_var($this->summary, FILTER_SANITIZE_STRING);
        $this->description = filter_var($this->description, FILTER_SANITIZE_STRING);
        $this->fixDescription = filter_var($this->fixDescription, FILTER_SANITIZE_STRING);
        $this->dateRaised = filter_var($this->dateRaised, FILTER_SANITIZE_STRING);
        $this->targetDate = filter_var($this->targetDate, FILTER_SANITIZE_STRING);
        $this->dateClosed = filter_var($this->dateClosed, FILTER_SANITIZE_STRING);

        if ($this->id) {
            // Update existing bug
            $db->query("UPDATE bugs SET projectId = ?, ownerId = ?, assignedToId = ?, statusId = ?, priorityId = ?, 
                        summary = ?, description = ?, fixDescription = ?, dateRaised = ?, targetDate = ?, dateClosed = ? 
                        WHERE id = ?",
                [$this->projectId, $this->ownerId, $this->assignedToId, $this->statusId, $this->priorityId,
                 $this->summary, $this->description, $this->fixDescription, $this->dateRaised, $this->targetDate,
                 $this->dateClosed, $this->id]);
        } else {
            // Insert new bug
            $db->query("INSERT INTO bugs (projectId, ownerId, assignedToId, statusId, priorityId, summary, description, 
                        fixDescription, dateRaised, targetDate, dateClosed) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$this->projectId, $this->ownerId, $this->assignedToId, $this->statusId, $this->priorityId,
                 $this->summary, $this->description, $this->fixDescription, $this->dateRaised, $this->targetDate,
                 $this->dateClosed]);
            $this->id = $db->getConnection()->lastInsertId();
        }
    }
}