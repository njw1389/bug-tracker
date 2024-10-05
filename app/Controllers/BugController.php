<?php

namespace App\Controllers;

use App\Core\SessionManager;

class BugController {
    public function index() {
        SessionManager::start();
        if (!SessionManager::isLoggedIn()) {
            header('Location: /');
            exit;
        }
        require_once __DIR__ . '/../views/bug.php';
    }
}