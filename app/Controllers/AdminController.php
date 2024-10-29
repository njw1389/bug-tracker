<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use ZipArchive;

/**
 * AdminController handles all administrative functions of the bug tracking system
 * including user management, project management, and bug management for admin & manager users.
 */
class AdminController {
    /**
     * Displays the admin dashboard with user, project, and bug management interfaces
     * Only accessible by users with admin (role 1) or manager (role 2) privileges
     */
    public function index() {
        SessionManager::start();
        // Verify user is logged in and has appropriate role
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            header('Location: /');
            exit;
        }

        $userRole = SessionManager::get('role');

        // Only admins and managers can view user list
        $users = ($userRole == 1 || $userRole == 2) ? User::findAll() : [];
        
        // Sort users first by role, then alphabetically by username within each role
        usort($users, function($a, $b) {
            if ($a->RoleID == $b->RoleID) {
                return strcmp($a->Username, $b->Username);
            }
            return $a->RoleID - $b->RoleID;
        });

        $projects = Project::findAll();
        $bugs = Bug::findAll();

        // Create lookup array for quick project access
        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->Id] = $project;
        }

        // Filter bugs by different criteria
        $openBugs = array_filter($bugs, function($bug) {
            return $bug->statusId != 3; // Status 3 = Closed
        });

        $overdueBugs = array_filter($bugs, function($bug) {
            return $bug->targetDate !== null && strtotime($bug->targetDate) < time() && $bug->statusId != 3;
        });

        $unassignedBugs = array_filter($bugs, function($bug) {
            return $bug->assignedToId === null;
        });

        $WelcomeUser = User::findById(SessionManager::get('user_id'));

        require_once __DIR__ . '/../views/admin.php';
    }

    /**
     * Updates a user's project assignment (Manager only function)
     * When changing projects, unassigns user from all bugs in their current project
     * 
     * @return void JSON response indicating success/failure
     */
    public function updateUserProject() {
        SessionManager::start();
        // Verify user is logged in and is a manager
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Validate input data
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);
        $projectId = filter_var($data['projectId'] ?? null, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }

        try {
            // Verify user exists and is a regular user (role 3)
            $user = User::findById($userId);
            if (!$user || $user->RoleID != 3) {
                throw new \Exception('Invalid user');
            }

            // Unassign user from current project's bugs if they have one
            if ($user->ProjectId) {
                Bug::unassignUserFromBugs($userId);
            }

            // Update user's project assignment
            $user->ProjectId = $projectId ?: null;
            $user->save();

            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Saves or updates a user's information (Admin only function)
     * Handles both new user creation and existing user updates
     * 
     * @return void JSON response indicating success/failure
     */
    public function saveUser() {
        SessionManager::start();
        // Verify user is logged in and is an admin
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
    
        // Validate and sanitize input data
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        $username = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8');
        $roleId = filter_input(INPUT_POST, 'roleId', FILTER_VALIDATE_INT);
        $newProjectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
        $password = $_POST['password'] ?? '';
        $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    
        // Validate required fields and field lengths
        if (!$username || !$roleId || !$name || strlen($username) > 255 || strlen($name) > 255) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields OR Invalid input data']);
            return;
        }
    
        // Get existing user or create new one
        $user = $userId ? User::findById($userId) : new User();
        $oldProjectId = $user->ProjectId;
    
        // Update user properties
        $user->Username = $username;
        $user->RoleID = $roleId;
        $user->ProjectId = $newProjectId ?: null;
        if ($password) {
            $user->Password = $password;
        }
        $user->Name = $name;
    
        try {
            $user->save();
    
            // Handle bug reassignment if user's project changed
            if ($oldProjectId !== $newProjectId) {
                $this->updateBugsForUserProjectChange($userId, $oldProjectId);
            }
    
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Updates bug assignments when a user's project changes
     * Unassigns the user from bugs in their old project and sets them to unassigned status
     * 
     * @param int $userId The ID of the user whose project is changing
     * @param int $oldProjectId The ID of the user's previous project
     * @return void
     */
    private function updateBugsForUserProjectChange($userId, $oldProjectId) {
        if ($oldProjectId) {
            $bugs = Bug::findByProjectAndAssignedUser($oldProjectId, $userId);
            foreach ($bugs as $bug) {
                $bug->assignedToId = null;
                $bug->statusId = 1; // Set to 'Unassigned' status
                $bug->save();
            }
        }
    }

    /**
     * Deletes a user from the system (Admin only function)
     * Handles reassignment of their bugs and project assignments
     * Prevents deletion of last admin/manager to maintain system integrity
     * 
     * @return void JSON response indicating success/failure
     */
    public function deleteUser() {
        SessionManager::start();
        // Verify user is logged in and is an admin
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Validate input data
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);
        $isSelf = $data['isSelf'] ?? false; // Flag indicating if user is deleting themselves

        if (!$userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }

        try {
            $user = User::findById($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            // Prevent deletion of last admin or manager
            $admins = User::findByRole(1);
            $managers = User::findByRole(2);
            
            if ($user->RoleID == 1 && count($admins) <= 1) {
                $this->sendJsonResponse([
                    'success' => false, 
                    'message' => 'Cannot delete the last admin. Create a new admin before deleting this user.'
                ]);
                return;
            }
            
            if ($user->RoleID == 2 && count($managers) <= 1) {
                $this->sendJsonResponse([
                    'success' => false, 
                    'message' => 'Cannot delete the last manager. Create a new manager before deleting this user.'
                ]);
                return;
            }
            
            // Clean up user's bug assignments
            Bug::unassignUserFromBugs($userId);
            Bug::reassignBugsToManager($userId);
            
            // Delete the user
            $user->delete();
            
            // Handle self-deletion
            if ($isSelf) {
                SessionManager::destroy();
            }
            
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Deletes a bug from the system (Admin only function)
     * 
     * @return void JSON response indicating success/failure
     */
    public function deleteBug() {
        SessionManager::start();
        // Verify user is logged in and is an admin
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Validate input data
        $data = json_decode(file_get_contents('php://input'), true);
        $bugId = filter_var($data['bugId'] ?? null, FILTER_VALIDATE_INT);

        if (!$bugId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid bug ID']);
            return;
        }

        try {
            $bug = Bug::findById($bugId);
            if (!$bug) {
                throw new \Exception('Bug not found');
            }
            
            $bug->delete();
            
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Saves or updates a project (Admin/Manager function)
     * Handles both new project creation and existing project updates
     * 
     * @return void JSON response indicating success/failure
     */
    public function saveProject() {
        SessionManager::start();
        // Verify user is logged in and has appropriate role
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Validate and sanitize input data
        $projectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
        $projectName = htmlspecialchars($_POST['projectName'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!$projectName) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Project name is required']);
            return;
        }

        // Get existing project or create new one
        $project = $projectId ? Project::findById($projectId) : new Project();
        $project->Project = $projectName;

        try {
            $project->save();
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Saves or updates a bug (Admin/Manager function)
     * Handles both new bug creation and existing bug updates
     * Maintains ownership history and handles status changes
     * 
     * @return void JSON response indicating success/failure
     */
    public function saveBug() {
        SessionManager::start();
        // Verify user is logged in and has appropriate role
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Validate and sanitize input data
        $bugId = filter_input(INPUT_POST, 'bugId', FILTER_VALIDATE_INT);
        $projectId = filter_input(INPUT_POST, 'bugProjectId', FILTER_VALIDATE_INT);
        $summary = htmlspecialchars($_POST['summary'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $assignedToId = filter_input(INPUT_POST, 'assignedToId', FILTER_VALIDATE_INT);
        $statusId = filter_input(INPUT_POST, 'statusId', FILTER_VALIDATE_INT);
        $priorityId = filter_input(INPUT_POST, 'priorityId', FILTER_VALIDATE_INT);
        $targetDate = htmlspecialchars($_POST['targetDate'] ?? '', ENT_QUOTES, 'UTF-8');

        // Validate required fields and field lengths
        if (!$projectId || !$summary || !$description || !$statusId || !$priorityId || strlen($summary) > 255 || strlen($description) > 1000) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields OR Invalid input data']);
            return;
        }

        // Get existing bug or create new one
        $bug = $bugId ? Bug::findById($bugId) : new Bug();
        
        // Set owner for new bugs only
        if (!$bugId) {
            $bug->ownerId = SessionManager::get('user_id');
            $bug->dateRaised = date('Y-m-d H:i:s');
        }

        // Update bug properties
        $bug->projectId = $projectId;
        $bug->assignedToId = $assignedToId ?: null;
        $bug->statusId = $statusId;
        $bug->priorityId = $priorityId;
        $bug->summary = $summary;
        $bug->description = $description;
        $bug->targetDate = $targetDate ?: null;

        try {
            $bug->save();
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Sends a JSON response to the client
     * 
     * @param array $data The data to be JSON encoded and sent
     * @return void
     */
    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Exports selected data types to CSV files and creates a ZIP archive
     * Available for Admin and Manager roles
     * Admins can export user data, both roles can export projects and bugs
     * 
     * @return void Outputs ZIP file for download
     */
    public function exportData() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            header('HTTP/1.0 403 Forbidden');
            echo "Unauthorized access";
            return;
        }

        $userRole = SessionManager::get('role');

        // Determine what to export based on role and request
        $exportUsers = isset($_POST['exportUsers']) && $userRole == 1;
        $exportProjects = isset($_POST['exportProjects']);
        $exportBugs = isset($_POST['exportBugs']);

        // Create temporary directory for export files
        $tempDir = sys_get_temp_dir() . '/export_' . time();
        mkdir($tempDir);

        // Export selected data types
        if ($exportUsers) {
            $this->exportUsers($tempDir);
        }
        if ($exportProjects) {
            $this->exportProjects($tempDir);
        }
        if ($exportBugs) {
            $this->exportBugs($tempDir);
        }

        // Create and send ZIP file
        $zipFile = $tempDir . '/exported_data.zip';
        $this->createZipArchive($tempDir, $zipFile);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="exported_data.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        // Clean up temporary files
        $this->removeDirectory($tempDir);
    }

    /**
     * Exports user data to CSV
     * 
     * @param string $dir Directory to save the CSV file
     * @return void
     */
    private function exportUsers($dir) {
        $users = User::findAll();
        $file = fopen($dir . '/users.csv', 'w');
        fputcsv($file, ['ID', 'Username', 'Role', 'Project ID', 'Name']);
        foreach ($users as $user) {
            fputcsv($file, [
                $user->Id, 
                $user->Username, 
                $user->RoleID, 
                $user->ProjectId, 
                $user->Name
            ]);
        }
        fclose($file);
    }

    /**
     * Exports project data to CSV
     * 
     * @param string $dir Directory to save the CSV file
     * @return void
     */
    private function exportProjects($dir) {
        $projects = Project::findAll();
        $file = fopen($dir . '/projects.csv', 'w');
        fputcsv($file, ['ID', 'Project Name']);
        foreach ($projects as $project) {
            fputcsv($file, [$project->Id, $project->Project]);
        }
        fclose($file);
    }

    /**
     * Exports bug data to CSV
     * 
     * @param string $dir Directory to save the CSV file
     * @return void
     */
    private function exportBugs($dir) {
        $bugs = Bug::findAll();
        $file = fopen($dir . '/bugs.csv', 'w');
        fputcsv($file, [
            'ID', 'Project ID', 'Owner ID', 'Assigned To ID', 'Status ID', 
            'Priority ID', 'Summary', 'Description', 'Fix Description', 
            'Date Raised', 'Target Date', 'Date Closed'
        ]);
        foreach ($bugs as $bug) {
            fputcsv($file, [
                $bug->id, $bug->projectId, $bug->ownerId, $bug->assignedToId,
                $bug->statusId, $bug->priorityId, $bug->summary, $bug->description,
                $bug->fixDescription, $bug->dateRaised, $bug->targetDate, $bug->dateClosed
            ]);
        }
        fclose($file);
    }

    /**
     * Creates a ZIP archive from a directory
     * 
     * @param string $sourceDir Source directory containing files to zip
     * @param string $outZipPath Output path for the ZIP file
     * @return void
     */
    private function createZipArchive($sourceDir, $outZipPath) {
        $zip = new ZipArchive();
        if ($zip->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($sourceDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }
    }

    /**
     * Recursively removes a directory and its contents
     * 
     * @param string $dir Directory to remove
     * @return void
     */
    private function removeDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        $this->removeDirectory($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }
}