<?php

namespace App\Core;

use PDO;
use PDOException;

/**
* Database class implements a singleton pattern for database connection management
* Provides secure database access with prepared statements and object mapping
* 
* Features:
* - Singleton design pattern
* - PDO connection management
* - Prepared statement support
* - Object mapping capabilities
* - Secure query execution
*/
class Database
{
   /** @var Database|null Singleton instance */
   private static $instance = null;
   
   /** @var PDO Active database connection */
   private $connection;

   /**
    * Private constructor to prevent direct instantiation
    * Establishes database connection with secure configuration
    * 
    * Security features:
    * - Uses environment variables for credentials
    * - UTF-8 character encoding
    * - Strict error reporting
    * - Prepared statement enforcement
    * 
    * @throws PDOException If connection fails
    */
   private function __construct()
   {
       // Get database configuration from environment
       $host = getenv('DB_SERVER');
       $db   = getenv('DB');
       $user = getenv('DB_USER');
       $pass = getenv('DB_PASSWORD');

       // Configure database connection
       $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
       
       // Set PDO options for security and functionality
       $options = [
           PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Throw exceptions for errors
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return arrays indexed by column name
           PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
       ];

       try {
           // Establish connection
           $this->connection = new PDO($dsn, $user, $pass, $options);
       } catch (PDOException $e) {
           // Rethrow with connection details sanitized
           throw new PDOException($e->getMessage(), (int)$e->getCode());
       }
   }

   /**
    * Gets the singleton instance of the database connection
    * Creates new instance if none exists
    * 
    * @return Database Singleton instance
    */
   public static function getInstance()
   {
       if (self::$instance === null) {
           self::$instance = new self();
       }
       return self::$instance;
   }

   /**
    * Gets the active PDO connection
    * 
    * @return PDO Active database connection
    */
   public function getConnection()
   {
       return $this->connection;
   }

   /**
    * Executes a prepared SQL query with parameters
    * 
    * Security:
    * - Uses prepared statements
    * - Parameterized queries
    * - SQL injection prevention
    * 
    * @param string $sql SQL query with placeholders
    * @param array $params Array of parameters to bind
    * @return \PDOStatement Executed statement
    * @throws PDOException If query fails
    */
   public function query($sql, $params = [])
   {
       $stmt = $this->connection->prepare($sql);
       $stmt->execute($params);
       return $stmt;
   }

   /**
    * Fetches all rows from a query result
    * Optionally maps results to a specified class
    * 
    * @param string $sql SQL query with placeholders
    * @param array $params Array of parameters to bind
    * @param string|null $class Optional class name for object mapping
    * @return array Array of results as associative arrays or objects
    * @throws PDOException If query fails
    */
   public function fetchAll($sql, $params = [], $class = null)
   {
       $stmt = $this->query($sql, $params);
       if ($class) {
           // Map results to specified class objects
           return $stmt->fetchAll(PDO::FETCH_CLASS, $class);
       }
       // Return as associative arrays
       return $stmt->fetchAll();
   }

   /**
    * Fetches a single row from a query result
    * Optionally maps result to a specified class
    * 
    * @param string $sql SQL query with placeholders
    * @param array $params Array of parameters to bind
    * @param string|null $class Optional class name for object mapping
    * @return mixed Single result as associative array or object
    * @throws PDOException If query fails
    */
   public function fetch($sql, $params = [], $class = null)
   {
       $stmt = $this->query($sql, $params);
       if ($class) {
           // Map result to specified class object
           $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
           return $stmt->fetch();
       }
       // Return as associative array
       return $stmt->fetch();
   }

   /**
    * Prevent cloning of singleton instance
    * 
    * @return void
    * @throws \Exception If clone is attempted
    */
   private function __clone()
   {
       throw new \Exception('Cannot clone singleton database instance');
   }

   /**
    * Prevent unserialization of singleton instance
    * 
    * @return void
    * @throws \Exception If unserialization is attempted
    */
   public function __wakeup()
   {
       throw new \Exception('Cannot unserialize singleton database instance');
   }
}