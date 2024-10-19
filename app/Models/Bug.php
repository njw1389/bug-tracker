<?php

namespace App\Models;

use App\Core\Database;
use App\Core\FileCache as Cache;

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
        $cacheKey = "bug_$id";
        $cachedBug = Cache::get($cacheKey);
        
        if ($cachedBug !== false) {
            return $cachedBug;
        }

        $db = Database::getInstance();
        $bug = $db->fetch("SELECT * FROM bugs WHERE id = ?", [$id], self::class);
        
        if ($bug) {
            Cache::set($cacheKey, $bug);
        }

        return $bug;
    }

    public static function findByProject($projectId)
    {
        $cacheKey = "project_bugs_$projectId";
        $cachedBugs = Cache::get($cacheKey);
        
        if ($cachedBugs !== false) {
            return $cachedBugs;
        }

        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs WHERE projectId = ?", [$projectId], self::class);
        
        Cache::set($cacheKey, $bugs);

        return $bugs;
    }

    public static function findByAssignedUser($userId, $projectId)
    {
        $cacheKey = "user_bugs_{$userId}_{$projectId}";
        $cachedBugs = Cache::get($cacheKey);
        
        if ($cachedBugs !== false) {
            return $cachedBugs;
        }

        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs WHERE assignedToId = ? AND projectId = ?", [$userId, $projectId], self::class);
        
        Cache::set($cacheKey, $bugs);

        return $bugs;
    }

    public static function unassignUserFromBugs($userId) {
        $db = Database::getInstance();
        $db->query("UPDATE bugs SET assignedToId = NULL WHERE assignedToId = ?", [$userId]);

        // Clear relevant caches
        $allProjects = Project::findAll(); // Assuming you have a Project model with findAll method
        foreach ($allProjects as $project) {
            Cache::delete("user_bugs_{$userId}_{$project->Id}");
        }
        Cache::delete("all_bugs");

        // Clear project-specific bug caches
        $affectedBugs = $db->fetchAll("SELECT DISTINCT projectId FROM bugs WHERE assignedToId = ?", [$userId]);
        foreach ($affectedBugs as $bug) {
            Cache::delete("project_bugs_" . $bug['projectId']);
        }
    }

    public function delete()
    {
        $db = Database::getInstance();

        try {
            $db->query("DELETE FROM bugs WHERE id = ?", [$this->id]);

            // Clear relevant caches
            Cache::delete("bug_" . $this->id);
            Cache::delete("project_bugs_" . $this->projectId);
            Cache::delete("user_bugs_{$this->assignedToId}_{$this->projectId}");
            Cache::delete("all_bugs");

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

            // Clear relevant caches
            Cache::delete("project_bugs_" . $projectId);
            Cache::delete("all_bugs");

            // Clear user-specific bug caches
            $users = User::findAll();
            foreach ($users as $user) {
                Cache::delete("user_bugs_{$user->Id}_{$projectId}");
            }

            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while deleting bugs for the project");
        }
    }


    public static function findAll()
    {
        $cacheKey = "all_bugs";
        $cachedBugs = Cache::get($cacheKey);
        
        if ($cachedBugs !== false) {
            return $cachedBugs;
        }

        $db = Database::getInstance();
        $bugs = $db->fetchAll("SELECT * FROM bugs", [], self::class);
        
        Cache::set($cacheKey, $bugs);

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

            // Clear relevant caches
            Cache::delete("bug_" . $this->id);
            Cache::delete("project_bugs_" . $this->projectId);
            Cache::delete("user_bugs_{$this->assignedToId}_{$this->projectId}");
            Cache::delete("all_bugs");

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