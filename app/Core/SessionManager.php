<?php

namespace App\Core;

class SessionManager
{
    const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && self::checkSessionTimeout();
    }

    public static function checkSessionTimeout() 
    {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function refreshSession()
    {
        if (self::isLoggedIn()) {
            self::set('last_activity', time());
        }
    }

    public static function login($userId, $role)
    {
        self::set('user_id', $userId);
        self::set('role', $role);
        self::set('last_activity', time());
    }

    public static function logout()
    {
        self::destroy();
    }
}