<?php

require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/BugController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../localEnvSet.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\SessionManager;

SessionManager::start();
SessionManager::refreshSession();

$router = new Router();

$router->addRoute('/', 'HomeController', 'index');
$router->addRoute('admin', 'AdminController', 'index');
$router->addRoute('bug', 'BugController', 'index');
$router->addRoute('login', 'AuthController', 'login');
$router->addRoute('logout', 'AuthController', 'logout');
$router->addRoute('admin/saveUser', 'AdminController', 'saveUser');
$router->addRoute('admin/deleteUser', 'AdminController', 'deleteUser');
$router->addRoute('admin/saveProject', 'AdminController', 'saveProject');
$router->addRoute('admin/deleteProject', 'AdminController', 'deleteProject');
$router->addRoute('admin/saveBug', 'AdminController', 'saveBug');
$router->addRoute('admin/deleteBug', 'AdminController', 'deleteBug');
$router->addRoute('bug/saveBug', 'BugController', 'saveBug');
$router->addRoute('refresh-session', 'AuthController', 'refreshSession');
$router->addRoute('admin/exportData', 'AdminController', 'exportData');

$url = $_SERVER['REQUEST_URI'];
$url = strtok($url, '?'); // Remove query string if present

try {
    $router->dispatch($url);
} catch (Exception $e) {
    echo "404 Not Found: " . $e->getMessage();
}