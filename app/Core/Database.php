<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $host = getenv('DB_SERVER');
        $db   = getenv('DB');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = [], $class = null)
    {
        $stmt = $this->query($sql, $params);
        if ($class) {
            return $stmt->fetchAll(PDO::FETCH_CLASS, $class);
        }
        return $stmt->fetchAll();
    }

    public function fetch($sql, $params = [], $class = null)
    {
        $stmt = $this->query($sql, $params);
        if ($class) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
            return $stmt->fetch();
        }
        return $stmt->fetch();
    }
}