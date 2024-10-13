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

    public function saveUser() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $roleId = filter_input(INPUT_POST, 'roleId', FILTER_VALIDATE_INT);
        $projectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
        $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

        if (!$username || !$roleId || !$name) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $user = $userId ? User::findById($userId) : new User();
        $user->Username = $username;
        $user->RoleID = $roleId;
        $user->ProjectId = $projectId ?: null;
        if ($password) {
            $user->Password = password_hash($password, PASSWORD_DEFAULT);
        }
        $user->Name = $name;

        try {
            $user->save();
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteUser() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }

        try {
            $user = User::findById($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            // Remove user from assigned bugs
            Bug::unassignUserFromBugs($userId);
            
            // Delete the user
            $user->delete();
            
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveProject() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $projectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
        $projectName = filter_input(INPUT_POST, 'projectName', FILTER_SANITIZE_STRING);

        if (!$projectName) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Project name is required']);
            return;
        }

        $project = $projectId ? Project::findById($projectId) : new Project();
        $project->Project = $projectName;

        try {
            $project->save();
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveBug() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $bugId = filter_input(INPUT_POST, 'bugId', FILTER_VALIDATE_INT);
        $projectId = filter_input(INPUT_POST, 'bugProjectId', FILTER_VALIDATE_INT);
        $summary = filter_input(INPUT_POST, 'summary', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $assignedToId = filter_input(INPUT_POST, 'assignedToId', FILTER_VALIDATE_INT);
        $statusId = filter_input(INPUT_POST, 'statusId', FILTER_VALIDATE_INT);
        $priorityId = filter_input(INPUT_POST, 'priorityId', FILTER_VALIDATE_INT);
        $targetDate = filter_input(INPUT_POST, 'targetDate', FILTER_SANITIZE_STRING);

        if (!$projectId || !$summary || !$description || !$statusId || !$priorityId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $bug = $bugId ? Bug::findById($bugId) : new Bug();
        $bug->projectId = $projectId;
        $bug->ownerId = SessionManager::get('user_id');
        $bug->assignedToId = $assignedToId ?: null;
        $bug->statusId = $statusId;
        $bug->priorityId = $priorityId;
        $bug->summary = $summary;
        $bug->description = $description;
        $bug->targetDate = $targetDate ?: null;

        if (!$bugId) {
            $bug->dateRaised = date('Y-m-d H:i:s');
        }

        try {
            $bug->save();
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}