<?php

require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/FileCache.php';
require_once __DIR__ . '/app/Models/Bug.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Project.php';
require_once __DIR__ . '/localEnvSet.php';

use App\Core\FileCache as Cache;
use App\Models\Bug;
use App\Models\User;
use App\Models\Project;

// Initialize the cache
Cache::init(__DIR__ . '/cache');
Cache::setDefaultTtl(60); // Set a short TTL for testing

function testCache() {
    echo "Testing Bug caching:\n";
    $start = microtime(true);
    $bug = Bug::findById(1); // This should hit the database
    $end = microtime(true);
    echo "First fetch took " . ($end - $start) . " seconds\n";

    $start = microtime(true);
    $bug = Bug::findById(1); // This should hit the cache
    $end = microtime(true);
    echo "Second fetch took " . ($end - $start) . " seconds\n";

    echo "\nTesting User caching:\n";
    $start = microtime(true);
    $user = User::findById(1); // This should hit the database
    $end = microtime(true);
    echo "First fetch took " . ($end - $start) . " seconds\n";

    $start = microtime(true);
    $user = User::findById(1); // This should hit the cache
    $end = microtime(true);
    echo "Second fetch took " . ($end - $start) . " seconds\n";

    echo "\nTesting Project caching:\n";
    $start = microtime(true);
    $project = Project::findById(1); // This should hit the database
    $end = microtime(true);
    echo "First fetch took " . ($end - $start) . " seconds\n";

    $start = microtime(true);
    $project = Project::findById(1); // This should hit the cache
    $end = microtime(true);
    echo "Second fetch took " . ($end - $start) . " seconds\n";

    // Test cache clearing
    echo "\nTesting cache clearing:\n";
    $bug->summary = "Updated summary";
    $bug->save();
    
    $start = microtime(true);
    $updatedBug = Bug::findById(1); // This should hit the database again
    $end = microtime(true);
    echo "Fetch after update took " . ($end - $start) . " seconds\n";
    echo "Updated summary: " . $updatedBug->summary . "\n";
}

testCache();