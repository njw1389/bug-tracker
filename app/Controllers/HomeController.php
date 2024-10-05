<?php

namespace App\Controllers;

require_once __DIR__ . '/../Core/Database.php';

class HomeController {
    public function index() {
        require_once __DIR__ . '/../views/home.php';
    }
}