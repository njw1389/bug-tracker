<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

class BugController {
    public function index() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        $userId = SessionManager::get('user_id');
        $userRole = SessionManager::get('role');
        $user = User::findById($userId);

        $projects = ($userRole <= 2) ? Project::findAll() : [Project::findById($user->ProjectId)];
        $bugs = $this->getBugsForUser($userId, $userRole, $user->ProjectId);

        $openBugs = $this->filterBugs($bugs, function($bug) {
            return $bug->statusId != 3; // Assuming 3 is the 'Closed' status
        });

        $overdueBugs = $this->filterBugs($bugs, function($bug) {
            // Check if targetDate is not null and not empty before using strtotime
            if (!empty($bug->targetDate)) {
                $targetTimestamp = strtotime($bug->targetDate);
                return $targetTimestamp !== false && $targetTimestamp < time() && $bug->statusId != 3;
            }
            return false; // If no target date, it's not overdue
        });

        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->Id] = $project;
        }

        $users = ($userRole <= 2) ? User::findAll() : [$user];

        require_once __DIR__ . '/../views/bug.php';
    }

    private function getBugsForUser($userId, $userRole, $userProjectId) {
        if ($userRole <= 2) {
            return Bug::findAll();
        } else {
            return Bug::findByAssignedUser($userId, $userProjectId);
        }
    }

    private function filterBugs($bugs, $filterFunction) {
        return array_filter($bugs, $filterFunction);
    }

    public function saveBug() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $bugId = filter_input(INPUT_POST, 'bugId', FILTER_VALIDATE_INT);
        $projectId = filter_input(INPUT_POST, 'bugProjectId', FILTER_VALIDATE_INT);
        $summary = htmlspecialchars($_POST['summary'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $assignedToId = filter_input(INPUT_POST, 'assignedToId', FILTER_VALIDATE_INT);
        $statusId = filter_input(INPUT_POST, 'statusId', FILTER_VALIDATE_INT);
        $priorityId = filter_input(INPUT_POST, 'priorityId', FILTER_VALIDATE_INT);
        $targetDate = htmlspecialchars($_POST['targetDate'] ?? '', ENT_QUOTES, 'UTF-8');


        $userId = SessionManager::get('user_id');
        $userRole = SessionManager::get('role');

        if (!$projectId || !$summary || !$description || !$statusId || !$priorityId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $bug = $bugId ? Bug::findById($bugId) : new Bug();

        // Check if the user has permission to edit this bug
        if ($bugId && $userRole > 2 && $bug->assignedToId != $userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'You do not have permission to edit this bug']);
            return;
        }

        $bug->projectId = $projectId;
        $bug->ownerId = $bugId ? $bug->ownerId : $userId;
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