<?php

class WooMailerLiteCache {

    protected static $cacheKey = "woo_mailerlite_";

    public static function set($key, $data, $seconds)
    {
        set_transient(self::$cacheKey . $key, $data, $seconds);
    }

    public static function get($key, $default = null)
    {
        $data = get_transient(self::$cacheKey . $key);
        if ($data) {
            return $data;
        }
        return $default;
    }

    public static function delete($key)
    {
        return delete_transient(self::$cacheKey . $key);
    }

    public static function pull($key, $default = null)
    {
        $data = self::get(self::$cacheKey . $key, $default);
        self::delete(self::$cacheKey . $key);
        return $data;
    }
}