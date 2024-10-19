<?php

namespace App\Models;

use App\Core\Database;
use App\Core\FileCache as Cache;

class Project
{
    public $Id;
    public $Project;

    public static function findById($Id)
    {
        $cacheKey = "project_$Id";
        $cachedProject = Cache::get($cacheKey);
        
        if ($cachedProject !== false) {
            return $cachedProject;
        }

        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM project WHERE Id = ?", [$Id], self::class);
        
        if ($project) {
            Cache::set($cacheKey, $project);
        }

        return $project;
    }

    public static function findAll()
    {
        $cacheKey = "all_projects";
        $cachedProjects = Cache::get($cacheKey);
        
        if ($cachedProjects !== false) {
            return $cachedProjects;
        }

        $db = Database::getInstance();
        $projects = $db->fetchAll("SELECT * FROM project", [], self::class);
        
        Cache::set($cacheKey, $projects);

        return $projects;
    }

    public function delete()
    {
        $db = Database::getInstance();

        try {
            // Delete all bugs associated with this project
            Bug::deleteByProject($this->Id);

            // Delete the project
            $db->query("DELETE FROM project WHERE Id = ?", [$this->Id]);

            // Clear relevant caches
            Cache::delete("project_" . $this->Id);
            Cache::delete("all_projects");

            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new \Exception("An error occurred while deleting the project");
        }
    }

    public function save()
    {
        $db = Database::getInstance();

        // Sanitize input
        $this->Project = htmlspecialchars($this->Project, ENT_QUOTES, 'UTF-8');
        
        if ($this->Id) {
            // Update existing project
            $db->query("UPDATE project SET Project = ? WHERE Id = ?", [$this->Project, $this->Id]);
        } else {
            // Insert new project
            $db->query("INSERT INTO project (Project) VALUES (?)", [$this->Project]);
            $this->Id = $db->getConnection()->lastInsertId();
        }

        // Clear relevant caches
        Cache::delete("project_" . $this->Id);
        Cache::delete("all_projects");
    }
}