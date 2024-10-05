<?php

namespace App\Models;

use App\Core\Database;

class Project
{
    public $id;
    public $project;

    public static function findById($id)
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM project WHERE id = ?", [$id], self::class);
    }

    public static function findAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM project", [], self::class);
    }

    public function save()
    {
        $db = Database::getInstance();
        if ($this->id) {
            // Update existing project
            $db->query("UPDATE project SET project = ? WHERE id = ?", [$this->project, $this->id]);
        } else {
            // Insert new project
            $db->query("INSERT INTO project (project) VALUES (?)", [$this->project]);
            $this->id = $db->getConnection()->lastInsertId();
        }
    }
}