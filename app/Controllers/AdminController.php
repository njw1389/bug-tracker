<?php

namespace App\Controllers;

use App\Core\SessionManager;

class AdminController {
    public function index() {
        SessionManager::start();
        error_log("User logged in: " . (SessionManager::isLoggedIn() ? "Yes" : "No"));
        error_log("User role: " . SessionManager::get('role'));
        
        if (!SessionManager::isLoggedIn() || SessionManager::get('role') > 2) {
            error_log("Redirecting to home. Not logged in or role > 2");
            header('Location: /');
            exit;
        }
        require_once __DIR__ . '/../views/admin.php';
    }
}