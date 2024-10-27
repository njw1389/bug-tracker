<?php

namespace App\Controllers;

require_once __DIR__ . '/../Core/Database.php';

/**
* HomeController handles the display of the application's home/landing page
* This serves as the entry point for users before authentication
*/
class HomeController {

   /**
    * Displays the home page of the application
    * Renders the login form for unauthenticated users
    * 
    * Purpose:
    * - Serves as the landing page for the application
    * - Provides the login interface for users
    * - Acts as the default redirect point for unauthenticated access attempts
    * 
    * Security considerations:
    * - This page is publicly accessible
    * - No sensitive data is displayed
    * - Login form handles credential validation separately
    * 
    * View considerations:
    * - Loads home.php view which contains the login form
    * - View handles proper HTML escaping of any displayed data
    * - Provides user feedback for failed login attempts
    * 
    * @return void Displays the home page
    */
   public function index() {
       // Load and display the home page view
       require_once __DIR__ . '/../views/home.php';
   }
}