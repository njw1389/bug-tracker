<?php

require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/BugController.php';
require_once __DIR__ . '/../localEnvSet.php';

$router = new Router();

$router->addRoute('/', 'HomeController', 'index');
$router->addRoute('admin', 'AdminController', 'index');
$router->addRoute('bug', 'BugController', 'index');

$url = $_SERVER['REQUEST_URI'];
$url = strtok($url, '?'); // Remove query string if present

try {
    $router->dispatch($url);
} catch (Exception $e) {
    echo "404 Not Found: " . $e->getMessage();
}