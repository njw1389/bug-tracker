<?php

namespace App\Controllers;

use App\Core\SessionManager;
use App\Models\User;

/**
* AuthController handles all authentication-related functionality including
* user login, logout, and session management.
*/
class AuthController
{
   /**
    * Handles user login authentication and redirection
    * Validates credentials, creates session, and routes users based on role
    * 
    * @return void Redirects to appropriate page or displays login form with error
    */
   public function login()
   {
       // Only process POST requests for login
       if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           // Extract and validate login credentials
           $username = $_POST['username'] ?? '';
           $password = $_POST['password'] ?? '';

           // Validate credential length and presence
           if (!$username || !$password || strlen($username) > 255 || strlen($password) > 255) {
               $error = "Username and password are required";
               require_once __DIR__ . '/../views/home.php';
               return;
           }

           // Attempt to find user and verify credentials
           $user = User::findByUsername($username);

           // Verify credentials with secure password hashing
           if ($user && $user->Username === $username && password_verify($password, $user->Password)) {
               // Initialize session with user data
               SessionManager::login($user->Id, $user->RoleID);
               SessionManager::start();
               
               // Log successful login for audit purposes
               error_log("User logged in. ID: " . $user->Id . ", Role: " . $user->RoleID);
               
               // Route users based on role
               if ($user->RoleID == 1 || $user->RoleID == 2) { // Admin or Manager roles
                   header('Location: /admin');
               } else {
                   header('Location: /bug');
               }
               exit;
           } else {
               // Invalid credentials - show error but don't specify which field was wrong
               // (security best practice)
               $error = "Invalid username or password";
               require_once __DIR__ . '/../views/home.php';
           }
       } else {
           // Non-POST requests redirect to home page
           header('Location: /');
       }
   }

   /**
    * Handles user logout
    * Destroys session and redirects to home page
    * 
    * @return void Redirects to home page after logout
    */
   public function logout()
   {
       // Destroy session and clear all session data
       SessionManager::logout();
       
       // Redirect to home page
       header('Location: /');
       exit;
   }

   /**
    * Refreshes user session to prevent timeout
    * Updates session expiration time if user is logged in
    * 
    * @return void JSON response with new expiration time or failure status
    */
   public function refreshSession()
   {
       SessionManager::start();
       
       // Only refresh if user is currently logged in
       if (SessionManager::isLoggedIn()) {
           SessionManager::refreshSession();
           $newExpirationTime = SessionManager::getSessionExpirationTime();
           
           // Return new expiration time to client
           echo json_encode([
               'success' => true, 
               'newExpirationTime' => $newExpirationTime
           ]);
       } else {
           // Return failure if user is not logged in
           echo json_encode(['success' => false]);
       }
   }
}