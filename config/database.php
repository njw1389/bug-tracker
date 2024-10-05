<?php

$requiredEnvVars = ['DB_SERVER', 'DB_USER', 'DB', 'DB_PASSWORD'];

foreach ($requiredEnvVars as $var) {
    if (!getenv($var)) {
        throw new Exception("Environment variable $var is not set.");
    }
}

return [
    'host' => getenv('DB_SERVER'),
    'database' => getenv('DB'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
];