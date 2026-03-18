<?php


class WooMailerLiteEncryption {

    private $key;

    private $salt;

    protected static $instance = null;

    public function __construct() {
        $this->key  = $this->getDefaultKey();
        $this->salt = $this->getDefaultSalt();
    }

    public static function instance()
    {
        if (!empty(static::$instance)) {
            return static::$instance;
        }
        static::$instance = new static();
        return static::$instance;
    }

    public function encrypt( $value ) {
        if ( ! extension_loaded( 'openssl' ) ) {
            return $value;
        }

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = openssl_random_pseudo_bytes( $ivlen );

        $raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
        if ( ! $raw_value ) {
            return false;
        }

        return base64_encode( $iv . $raw_value );
    }

    public function decrypt( $raw_value ) {
        if ( ! extension_loaded( 'openssl' ) ) {
            return $raw_value;
        }

        $raw_value = base64_decode( $raw_value, true );

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = substr( $raw_value, 0, $ivlen );

        $raw_value = substr( $raw_value, $ivlen );

        $value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
        if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
            return false;
        }

        return substr( $value, 0, - strlen( $this->salt ) );
    }

    protected function getDefaultKey()
    {
        if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
            return LOGGED_IN_KEY;
        }

        return 'this-is-a-fallback-key-but-not-secure';
    }

    protected function getDefaultSalt()
    {
        if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
            return LOGGED_IN_SALT;
        }
        return 'this-is-a-fallback-salt-but-not-secure';
    }

}
