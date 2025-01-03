<?php
$isLocal = false;

// Set the base path depending on environment
if ($isLocal) {
    define('BASE_PATH', '/');
} else {
    // Remove 'public' from the path for production
    define('BASE_PATH', '/~njw1389/ISTE341/Projects/bug-tracker/');
}

// Set the public path
define('PUBLIC_PATH', BASE_PATH . 'public/');

// Helper function for assets
function asset($path) {
    return PUBLIC_PATH . $path;
}

// Helper function for URLs
function url($path = '') {
    return BASE_PATH . ltrim($path, '/');
}

// Define application paths
define('APP_PATH', __DIR__ . '/../app/');
define('VIEW_PATH', APP_PATH . 'Views/');
define('CONTROLLER_PATH', APP_PATH . 'Controllers/');