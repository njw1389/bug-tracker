<?php

namespace App\Core;

class FileCache
{
    private static $cacheDir;
    private static $prefix = 'bug_tracker_';
    private static $defaultTtl = 3600; // 1 hour

    public static function init($cacheDir)
    {
        self::$cacheDir = $cacheDir;
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }

    public static function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? self::$defaultTtl;
        $filename = self::getFilename($key);
        $data = [
            'value' => $value,
            'expiry' => time() + $ttl
        ];
        return file_put_contents($filename, serialize($data)) !== false;
    }

    public static function get($key)
    {
        $filename = self::getFilename($key);
        if (!file_exists($filename)) {
            return false;
        }
        $data = unserialize(file_get_contents($filename));
        if ($data['expiry'] < time()) {
            unlink($filename);
            return false;
        }
        return $data['value'];
    }

    public static function delete($key)
    {
        $filename = self::getFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    public static function clear()
    {
        $files = glob(self::$cacheDir . '/' . self::$prefix . '*');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public static function exists($key)
    {
        return self::get($key) !== false;
    }

    public static function increment($key, $step = 1)
    {
        $value = self::get($key);
        if ($value === false) {
            return false;
        }
        $value += $step;
        self::set($key, $value);
        return $value;
    }

    public static function decrement($key, $step = 1)
    {
        return self::increment($key, -$step);
    }

    public static function setMultiple(array $values, $ttl = null)
    {
        $result = true;
        foreach ($values as $key => $value) {
            $result = $result && self::set($key, $value, $ttl);
        }
        return $result;
    }

    public static function getMultiple(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }
        return $result;
    }

    public static function deleteMultiple(array $keys)
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $result && self::delete($key);
        }
        return $result;
    }

    public static function setDefaultTtl($ttl)
    {
        self::$defaultTtl = $ttl;
    }

    private static function getFilename($key)
    {
        return self::$cacheDir . '/' . self::$prefix . md5($key);
    }
}