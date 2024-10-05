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

    public function save()
    {
        $db = Database::getInstance();
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