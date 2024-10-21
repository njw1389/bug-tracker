<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use ZipArchive;

class AdminController {
    public function index() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            header('Location: /');
            exit;
        }

        $userRole = SessionManager::get('role');

        $users = ($userRole == 1 || $userRole == 2) ? User::findAll() : [];
        
        // Sort users by role
        usort($users, function($a, $b) {
            if ($a->RoleID == $b->RoleID) {
                return strcmp($a->Username, $b->Username);
            }
            return $a->RoleID - $b->RoleID;
        });

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
            return $bug->targetDate !== null && strtotime($bug->targetDate) < time() && $bug->statusId != 3;
        });

        $unassignedBugs = array_filter($bugs, function($bug) {
            return $bug->assignedToId === null;
        });

        $WelcomeUser = User::findById(SessionManager::get('user_id'));

        require_once __DIR__ . '/../views/admin.php';
    }

    public function updateUserProject()
    {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 2) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);
        $projectId = filter_var($data['projectId'] ?? null, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }

        try {
            $user = User::findById($userId);
            if (!$user || $user->RoleID != 3) {
                throw new \Exception('Invalid user');
            }

            // Unassign user from all bugs in their current project
            if ($user->ProjectId) {
                Bug::unassignUserFromBugs($userId);
            }

            // Update user's project
            $user->ProjectId = $projectId ?: null;
            $user->save();

            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveUser() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
    
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        $username = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8');
        $roleId = filter_input(INPUT_POST, 'roleId', FILTER_VALIDATE_INT);
        $newProjectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
        $password = $_POST['password'] ?? '';
        $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    
        if (!$username || !$roleId || !$name || strlen($username) > 255 || strlen($name) > 255) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields OR Invalid input data']);
            return;
        }
    
        $user = $userId ? User::findById($userId) : new User();
        $oldProjectId = $user->ProjectId;
    
        $user->Username = $username;
        $user->RoleID = $roleId;
        $user->ProjectId = $newProjectId ?: null;
        if ($password) {
            $user->Password = $password;
        }
        $user->Name = $name;
    
        try {
            $user->save();
    
            // If the project has changed, update the bugs
            if ($oldProjectId !== $newProjectId) {
                $this->updateBugsForUserProjectChange($userId, $oldProjectId);
            }
    
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function updateBugsForUserProjectChange($userId, $oldProjectId) {
        if ($oldProjectId) {
            $bugs = Bug::findByProjectAndAssignedUser($oldProjectId, $userId);
            foreach ($bugs as $bug) {
                $bug->assignedToId = null;
                $bug->statusId = 1; // Assuming 1 is the 'Unassigned' status
                $bug->save();
            }
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
        $isSelf = $data['isSelf'] ?? false;

        if (!$userId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }

        try {
            $user = User::findById($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            // Check if this is the last admin or manager
            $admins = User::findByRole(1);
            $managers = User::findByRole(2);
            
            if ($user->RoleID == 1 && count($admins) <= 1) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Cannot delete the last admin. Create a new admin before deleting this user.']);
                return;
            }
            
            if ($user->RoleID == 2 && count($managers) <= 1) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Cannot delete the last manager. Create a new manager before deleting this user.']);
                return;
            }
            
            // Remove user from assigned bugs
            Bug::unassignUserFromBugs($userId);
            
            // Delete the user
            $user->delete();
            
            if ($isSelf) {
                // If the admin is deleting themselves, destroy the session
                SessionManager::destroy();
            }
            
            $this->sendJsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteBug() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') != 1) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

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
            
            // Delete the bug
            $bug->delete();
            
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
        $projectName = htmlspecialchars($_POST['projectName'] ?? '', ENT_QUOTES, 'UTF-8');

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
        $summary = htmlspecialchars($_POST['summary'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $assignedToId = filter_input(INPUT_POST, 'assignedToId', FILTER_VALIDATE_INT);
        $statusId = filter_input(INPUT_POST, 'statusId', FILTER_VALIDATE_INT);
        $priorityId = filter_input(INPUT_POST, 'priorityId', FILTER_VALIDATE_INT);
        $targetDate = htmlspecialchars($_POST['targetDate'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!$projectId || !$summary || !$description || !$statusId || !$priorityId || strlen($summary) > 255 || strlen($description) > 1000) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Missing required fields OR Invalid input data']);
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

    public function exportData() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            header('HTTP/1.0 403 Forbidden');
            echo "Unauthorized access";
            return;
        }

        $userRole = SessionManager::get('role');

        $exportUsers = isset($_POST['exportUsers']) && $userRole == 1;
        $exportProjects = isset($_POST['exportProjects']);
        $exportBugs = isset($_POST['exportBugs']);

        $tempDir = sys_get_temp_dir() . '/export_' . time();
        mkdir($tempDir);

        if ($exportUsers) {
            $this->exportUsers($tempDir);
        }

        if ($exportProjects) {
            $this->exportProjects($tempDir);
        }

        if ($exportBugs) {
            $this->exportBugs($tempDir);
        }

        $zipFile = $tempDir . '/exported_data.zip';
        $this->createZipArchive($tempDir, $zipFile);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="exported_data.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        // Clean up
        $this->removeDirectory($tempDir);
    }

    private function exportUsers($dir) {
        $users = User::findAll();
        $file = fopen($dir . '/users.csv', 'w');
        fputcsv($file, ['ID', 'Username', 'Role', 'Project ID', 'Name']);
        foreach ($users as $user) {
            fputcsv($file, [$user->Id, $user->Username, $user->RoleID, $user->ProjectId, $user->Name]);
        }
        fclose($file);
    }

    private function exportProjects($dir) {
        $projects = Project::findAll();
        $file = fopen($dir . '/projects.csv', 'w');
        fputcsv($file, ['ID', 'Project Name']);
        foreach ($projects as $project) {
            fputcsv($file, [$project->Id, $project->Project]);
        }
        fclose($file);
    }

    private function exportBugs($dir) {
        $bugs = Bug::findAll();
        $file = fopen($dir . '/bugs.csv', 'w');
        fputcsv($file, ['ID', 'Project ID', 'Owner ID', 'Assigned To ID', 'Status ID', 'Priority ID', 'Summary', 'Description', 'Fix Description', 'Date Raised', 'Target Date', 'Date Closed']);
        foreach ($bugs as $bug) {
            fputcsv($file, [
                $bug->id, $bug->projectId, $bug->ownerId, $bug->assignedToId,
                $bug->statusId, $bug->priorityId, $bug->summary, $bug->description,
                $bug->fixDescription, $bug->dateRaised, $bug->targetDate, $bug->dateClosed
            ]);
        }
        fclose($file);
    }

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