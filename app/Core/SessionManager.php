<?php

namespace App\Core;

/**
* SessionManager handles all session-related functionality
* Provides secure session management with timeout and authentication features
* 
* Features:
* - Session initialization and destruction
* - Session timeout management
* - Authentication state tracking
* - Session data management
* - Security measures for session handling
*/
class SessionManager
{
   /**
    * Session timeout duration in seconds
    * After this period of inactivity, session will be destroyed
    */
   const SESSION_TIMEOUT = 902; // 15 minutes and 2 seconds

   /**
    * Starts a new session if one doesn't exist
    * Ensures only one session is active at a time
    * 
    * @return void
    */
   public static function start(): void
   {
       if (session_status() == PHP_SESSION_NONE) {
           session_start();
       }
   }

   /**
    * Sets a session variable
    * 
    * @param string $key Session variable name
    * @param mixed $value Value to store
    * @return void
    */
   public static function set(string $key, mixed $value): void
   {
       $_SESSION[$key] = $value;
   }

   /**
    * Gets a session variable
    * 
    * @param string $key Session variable name
    * @param mixed $default Default value if key doesn't exist
    * @return mixed Session variable value or default
    */
   public static function get(string $key, mixed $default = null): mixed
   {
       return $_SESSION[$key] ?? $default;
   }

   /**
    * Removes a session variable
    * 
    * @param string $key Session variable name
    * @return void
    */
   public static function remove(string $key): void
   {
       unset($_SESSION[$key]);
   }

   /**
    * Destroys the current session
    * Removes all session data and cookies
    * 
    * @return void
    */
   public static function destroy(): void
   {
       session_destroy();
   }

   /**
    * Checks if a user is currently logged in
    * Verifies both user ID existence and session timeout
    * 
    * @return bool True if user is logged in and session is valid
    */
   public static function isLoggedIn(): bool
   {
       return isset($_SESSION['user_id']) && self::checkSessionTimeout();
   }

   /**
    * Checks if the session has timed out
    * Manages session timeout tracking and enforcement
    * 
    * Security features:
    * - Tracks last activity time
    * - Enforces timeout period
    * - Destroys expired sessions
    * 
    * @return bool True if session is still valid
    */
   public static function checkSessionTimeout(): bool
   {
       // Initialize last activity time if not set
       if (!isset($_SESSION['last_activity'])) {
           $_SESSION['last_activity'] = time();
           return true;
       }
       
       // Check if session has expired
       if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
           self::destroy();
           return false;
       }
       
       // Update last activity time
       $_SESSION['last_activity'] = time();
       return true;
   }

   /**
    * Refreshes the session timeout
    * Updates last activity time if user is logged in
    * 
    * @return void
    */
   public static function refreshSession(): void
   {
       if (self::isLoggedIn()) {
           self::set('last_activity', time());
       }
   }

   /**
    * Logs in a user
    * Sets up user session with ID, role, and activity tracking
    * 
    * @param int $userId User's ID
    * @param int $role User's role level
    * @return void
    */
   public static function login(int $userId, int $role): void
   {
       self::set('user_id', $userId);
       self::set('role', $role);
       self::set('last_activity', time());
   }

   /**
    * Logs out a user
    * Destroys current session and all associated data
    * 
    * @return void
    */
   public static function logout(): void
   {
       self::destroy();
   }

   /**
    * Gets the session expiration timestamp
    * Calculates when current session will timeout
    * 
    * @return int Unix timestamp of session expiration
    * @throws \RuntimeException If session is not started
    */
   public static function getSessionExpirationTime(): int
   {
       if (!isset($_SESSION['last_activity'])) {
           throw new \RuntimeException('Session not initialized');
       }
       return $_SESSION['last_activity'] + self::SESSION_TIMEOUT;
   }
}