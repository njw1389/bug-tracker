<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

/**
* BugController handles all bug tracking functionality for users
* Manages bug viewing, creation, and modification based on user roles and permissions
*/
class BugController {
   
   /**
    * Displays the bug management dashboard
    * Shows bugs filtered by user role and project assignment
    * 
    * Security:
    * - Requires authenticated user
    * - Filters bugs based on user role/project
    * - Redirects unauthenticated users
    * 
    * @return void Displays bug management interface
    */
   public function index() {
       SessionManager::start();
       // Verify authentication
       if (!SessionManager::isLoggedIn()) {
           header('Location: ' . BASE_PATH . '/');
           exit;
       }

       // Get user context
       $userId = SessionManager::get('user_id');
       $userRole = SessionManager::get('role');
       $user = User::findById($userId);

       // Load required data
       $projects = Project::findAll();
       $users = User::findAll();
       // Get bugs based on user role and project
       $bugs = $this->getBugsForUser($userId, $userRole, $user->ProjectId);
       $allBugs = $this->getAllBugs($user->ProjectId);

       // Filter bugs by status
       $openBugs = $this->filterBugs($bugs, function($bug) {
           return $bug->statusId != 3; // Filter out closed bugs
       });

       // Filter overdue bugs
       $overdueBugs = $this->filterBugs($bugs, function($bug) {
           // Validate target date before checking if overdue
           if (!empty($bug->targetDate)) {
               $targetTimestamp = strtotime($bug->targetDate);
               return $targetTimestamp !== false && 
                      $targetTimestamp < time() && 
                      $bug->statusId != 3;
           }
           return false;
       });

       // Create project lookup array
       $projectsById = [];
       foreach ($projects as $project) {
           $projectsById[$project->Id] = $project;
       }

       $WelcomeUser = User::findById(SessionManager::get('user_id'));

       require_once __DIR__ . '/../Views/bug.php';
   }

   /**
    * Retrieves bugs based on user role and project assignment
    * Admins/Managers see all bugs, regular users see only their project's bugs
    * 
    * @param int $userId User's ID
    * @param int $userRole User's role level
    * @param int $userProjectId User's assigned project ID
    * @return array Array of Bug objects
    */
   private function getBugsForUser($userId, $userRole, $userProjectId) {
       if ($userRole <= 2) {
           // Admins and Managers see all bugs
           return Bug::findAll();
       } else {
           // Regular users see only bugs assigned to them in their project
           return Bug::findByAssignedUser($userId, $userProjectId);
       }
   }

   /**
    * Retrieves all bugs for a specific project
    * 
    * @param int $userProjectId Project ID to fetch bugs for
    * @return array Array of Bug objects for the project
    */
   private function getAllBugs($userProjectId) {
       return Bug::findByProject($userProjectId);
   }

   /**
    * Filters bugs array based on provided callback function
    * 
    * @param array $bugs Array of Bug objects to filter
    * @param callable $filterFunction Callback function for filtering
    * @return array Filtered array of Bug objects
    */
   private function filterBugs($bugs, $filterFunction) {
       return array_filter($bugs, $filterFunction);
   }

   /**
    * Saves or updates a bug
    * Handles creation of new bugs and modification of existing ones
    * Enforces role-based permissions and validation
    * 
    * Security:
    * - Requires authenticated user
    * - Validates user permissions
    * - Sanitizes all input
    * - Enforces project-based access
    * 
    * @return void JSON response indicating success/failure
    */
   public function saveBug() {
       SessionManager::start();
       // Verify authentication
       if (!SessionManager::isLoggedIn()) {
           $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
           return;
       }

       // Validate and sanitize input
       $bugId = filter_input(INPUT_POST, 'bugId', FILTER_VALIDATE_INT);
       $projectId = filter_input(INPUT_POST, 'bugProjectId', FILTER_VALIDATE_INT);
       $summary = htmlspecialchars($_POST['summary'] ?? '', ENT_QUOTES, 'UTF-8');
       $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
       $assignedToId = filter_input(INPUT_POST, 'assignedToId', FILTER_VALIDATE_INT);
       $statusId = filter_input(INPUT_POST, 'statusId', FILTER_VALIDATE_INT);
       $priorityId = filter_input(INPUT_POST, 'priorityId', FILTER_VALIDATE_INT);
       $targetDate = htmlspecialchars($_POST['targetDate'] ?? '', ENT_QUOTES, 'UTF-8');

       // Get user context
       $userId = SessionManager::get('user_id');
       $userRole = SessionManager::get('role');
       $user = User::findById($userId);

       // Validate required fields and field lengths
       if (!$projectId || !$summary || !$description || !$statusId || !$priorityId || 
           strlen($summary) > 255 || strlen($description) > 1000) {
           $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields']);
           return;
       }

       // Enforce role-based permissions
       if ($userRole > 2) {
           // Regular users can only assign to themselves
           if ($assignedToId && $assignedToId != $userId) {
               $this->sendJsonResponse(['success' => false, 'message' => 'You can only assign bugs to yourself']);
               return;
           }
           // Regular users can only work with their project's bugs
           if ($projectId != $user->ProjectId) {
               $this->sendJsonResponse(['success' => false, 'message' => 'You can only create/edit bugs for your own project']);
               return;
           }
       }

       // Get existing bug or create new one
       $bug = $bugId ? Bug::findById($bugId) : new Bug();

       // Verify edit permissions
       if ($bugId && $userRole > 2 && $bug->assignedToId != $userId && $bug->ownerId != $userId) {
           $this->sendJsonResponse(['success' => false, 'message' => 'You do not have permission to edit this bug']);
           return;
       }

       // Update bug properties
       $bug->projectId = $projectId;
       $bug->ownerId = $bugId ? $bug->ownerId : $userId; // Preserve original owner
       $bug->assignedToId = $assignedToId ?: null;
       $bug->statusId = $statusId;
       $bug->priorityId = $priorityId;
       $bug->summary = $summary;
       $bug->description = $description;
       $bug->targetDate = $targetDate ?: null;

       // Set creation date for new bugs
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

   /**
    * Sends JSON response to client
    * 
    * @param array $data Data to be JSON encoded
    * @return void Outputs JSON response
    */
   private function sendJsonResponse($data) {
       header('Content-Type: application/json');
       echo json_encode($data);
   }
}