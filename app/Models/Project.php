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
        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM project WHERE Id = ?", [$Id], self::class);
        
        return $project;
    }

    public static function findAll()
    {
        $db = Database::getInstance();
        $projects = $db->fetchAll("SELECT * FROM project", [], self::class);

        return $projects;
    }

    public function save()
    {
        $db = Database::getInstance();

        // Sanitize input
        $this->Project = htmlspecialchars($this->Project, ENT_QUOTES, 'UTF-8');

        if (empty($this->Project) || strlen($this->Project) > 255) {
            throw new \InvalidArgumentException("Invalid project name");
        }
        
        if ($this->Id) {
            // Update existing project
            $db->query("UPDATE project SET Project = ? WHERE Id = ?", [$this->Project, $this->Id]);
        } else {
            // Insert new project
            $db->query("INSERT INTO project (Project) VALUES (?)", [$this->Project]);
            $this->Id = $db->getConnection()->lastInsertId();
        }
    }
}