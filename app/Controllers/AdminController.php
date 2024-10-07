<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

class AdminController {
    public function index() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            header('Location: /');
            exit;
        }

        $userRole = SessionManager::get('role');

        $users = ($userRole == 1) ? User::findAll() : [];
        $projects = Project::findAll();
        $bugs = Bug::findAll();

        // Convert projects array to associative array with ID as key
        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->Id] = $project;
        }

        $openBugs = array_filter($bugs, function($bug) {
            return $bug->statusId != 3; // Assuming 3 is the 'Closed' status
        });

        $overdueBugs = array_filter($bugs, function($bug) {
            return strtotime($bug->targetDate) < time() && $bug->statusId != 3;
        });

        $unassignedBugs = array_filter($bugs, function($bug) {
            return $bug->assignedToId === null;
        });

        require_once __DIR__ . '/../views/admin.php';
    }

    public function addUser() {
        // Implementation for adding a new user (Admin only)
    }

    public function deleteUser() {
        // Implementation for deleting a user (Admin only)
    }

    public function addProject() {
        // Implementation for adding a new project (Admin and Manager)
    }

    public function updateProject() {
        // Implementation for updating a project (Admin and Manager)
    }
}