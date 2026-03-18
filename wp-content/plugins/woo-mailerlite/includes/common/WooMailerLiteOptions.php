<?php

class WooMailerLiteOptions
{
    private static $key = 'woo_mailerlite_options';
    private static $apiKey = 'apiKey';
    protected static $toJson = false;

    public static function all()
    {
        return get_option(self::$key, []);
    }

    public static function get($key, $default = false)
    {
        try {
            $options = get_option(self::$key, []);
            if (!isset($options['settings'])) {
                $options = self::update('settings', [
                    'subscribeOnCheckout' => false
                ]);
            }
            if (strpos($key, '.') !== false) {
                $needle = explode('.', $key);

                if (is_string($options[$needle[0]])) {
                    $cleanJson = stripslashes($options[$needle[0]]);
                    $options[$needle[0]] = json_decode($cleanJson, true);
                }
                if (isset($options[$needle[0]][$needle[1]])) {
                    return $options[$needle[0]][$needle[1]];
                } else {
                    return $default;
                }
            }

            if (isset($options[$key])) {
                if (is_string($options[$key])) {
                    $cleanJson = stripslashes($options[$key]);
                    if (is_array(json_decode($cleanJson, true))) {
                        $options[$key] = json_decode($cleanJson, true);
                    }
                }
                if (self::$toJson) {
                    return json_encode($options[$key]);
                }

                if ($key === self::$apiKey && !empty($options[$key])) {
                    $decrypted = WooMailerLiteEncryption::instance()->decrypt($options[$key]);
                    if ($decrypted !== false) {
                        return $decrypted;
                    } else {
                        self::update($key, WooMailerLiteEncryption::instance()->encrypt($options[$key]));
                    }
                }
                return $options[$key];
            }
            return $default;
        } catch(Exception $e) {
            return null;
        }
    }

    public static function update($key, $value)
    {
        $options =  get_option(self::$key, []);
        $options[$key] = $value;
        return update_option(self::$key, $options);
    }

    public static function updateMultiple($data)
    {
        if (isset($data[self::$apiKey]) && !empty($data[self::$apiKey])) {
            $data[self::$apiKey] = WooMailerLiteEncryption::instance()->encrypt($data[self::$apiKey]);
        }
        $options =  get_option(self::$key, []);
        $options = array_merge($options, $data);
        return update_option(self::$key, $options);
    }

    public static function delete($key)
    {
        $options =  get_option(self::$key, []);
        if (isset($options[$key])) {
            unset($options[$key]);
        }
        return update_option(self::$key, $options);
    }

    public static function deleteAll()
    {
        return delete_option(self::$key);
    }

    public static function toJson()
    {
        self::$toJson = true;
        return new self;
    }
}
