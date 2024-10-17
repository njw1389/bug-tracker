<?php

namespace App\Models;

use App\Core\Database;

class Project
{
    public $Id;
    public $Project;

    public static function findById($Id)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM project WHERE Id = ?", [$Id], self::class);
    }

    public static function findAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM project", [], self::class);
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
            $this->id = $db->getConnection()->lastInsertId();
        }
    }
}