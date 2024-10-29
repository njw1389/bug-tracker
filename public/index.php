<?php

/**
* Application Entry Point
* Handles routing, autoloading, and initial application setup
* 
* Features:
* - Class autoloading
* - Route registration
* - Session management
* - Error handling
*/

// Load core dependencies
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/BugController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../localEnvSet.php';

/**
* PSR-4 Autoloader Implementation
* Automatically loads classes based on namespace
* 
* Convention:
* - Namespace 'App' maps to /app directory
* - Uses PSR-4 naming standards
* - Converts namespace separators to directory separators
*/
spl_autoload_register(function ($class) {
   // Project-specific namespace prefix
   $prefix = 'App\\';
   
   // Base directory for namespace
   $base_dir = __DIR__ . '/../app/';
   
   // Verify class uses our namespace
   $len = strlen($prefix);
   if (strncmp($prefix, $class, $len) !== 0) {
       return; // Class not in our namespace
   }
   
   // Get relative class path
   $relative_class = substr($class, $len);
   
   // Convert namespace to file path
   $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
   
   // Load file if it exists
   if (file_exists($file)) {
       require $file;
   }
});

use App\Core\SessionManager;

// Initialize session management
SessionManager::start();
SessionManager::refreshSession();

/**
* Route Registration
* Defines all application routes and their handlers
* 
* Format: $router->addRoute(path, controller, action)
* 
* Security considerations:
* - Routes are mapped to specific controller actions
* - No direct file access
* - Role-based access controlled in controllers
*/
$router = new Router();

// Public routes
$router->addRoute('/', 'HomeController', 'index');
$router->addRoute('login', 'AuthController', 'login');
$router->addRoute('logout', 'AuthController', 'logout');
$router->addRoute('refresh-session', 'AuthController', 'refreshSession');

// Bug management routes
$router->addRoute('bug', 'BugController', 'index');
$router->addRoute('bug/saveBug', 'BugController', 'saveBug');

// Admin routes
$router->addRoute('admin', 'AdminController', 'index');
$router->addRoute('admin/saveUser', 'AdminController', 'saveUser');
$router->addRoute('admin/deleteUser', 'AdminController', 'deleteUser');
$router->addRoute('admin/saveProject', 'AdminController', 'saveProject');
$router->addRoute('admin/saveBug', 'AdminController', 'saveBug');
$router->addRoute('admin/deleteBug', 'AdminController', 'deleteBug');
$router->addRoute('admin/exportData', 'AdminController', 'exportData');
$router->addRoute('admin/updateUserProject', 'AdminController', 'updateUserProject');

/**
* Request Handling
* Processes incoming request and routes to appropriate controller
* 
* Security features:
* - Query string removal
* - Exception handling
* - 404 handling
*/
try {
   // Get clean URL without query string
   $url = strtok($_SERVER['REQUEST_URI'], '?');
   
   // Dispatch request to appropriate controller
   $router->dispatch($url);
} catch (Exception $e) {
   // Handle 404 and other errors
   error_log('Router Error: ' . $e->getMessage());
   echo "404 Not Found: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

/**
* Application Architecture Notes:
* 
* 1. Routing:
*    - Clean URLs without query strings
*    - Controller/Action pattern
*    - Centralized route definition
* 
* 2. Security:
*    - Session management
*    - Input sanitization
*    - Error handling
*    - Controlled file access
* 
* 3. Structure:
*    - PSR-4 autoloading
*    - MVC architecture
*    - Separation of concerns
* 
* 4. Dependencies:
*    - Core framework files
*    - Controllers
*    - Environment configuration
* 
* 5. Error Handling:
*    - Try-catch blocks
*    - Error logging
*    - User-friendly messages
*/