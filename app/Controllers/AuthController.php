<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\User;

class AuthController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8');
            $password = $_POST['password'] ?? '';

            if (!$username || !$password) {
                $error = "Username and password are required";
                require_once __DIR__ . '/../views/home.php';
                return;
            }

            $user = User::findByUsername($username);

            if ($user && password_verify($password, $user->Password)) {
                SessionManager::login($user->Id, $user->RoleID);
                SessionManager::start();
                error_log("User logged in. ID: " . $user->Id . ", Role: " . $user->RoleID);
                
                if ($user->RoleID == 1 || $user->RoleID == 2) { // Admin or Manager
                    header('Location: /admin');
                } else {
                    header('Location: /bug');
                }
                exit;
            } else {
                $error = "Invalid username or password";
                require_once __DIR__ . '/../views/home.php';
            }
        } else {
            header('Location: /');
        }
    }

    public function logout()
    {
        SessionManager::logout();
        header('Location: /');
        exit;
    }

    public function refreshSession()
    {
        SessionManager::start();
        if (SessionManager::isLoggedIn()) {
            SessionManager::refreshSession();
            $newExpirationTime = SessionManager::getSessionExpirationTime();
            echo json_encode(['success' => true, 'newExpirationTime' => $newExpirationTime]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}