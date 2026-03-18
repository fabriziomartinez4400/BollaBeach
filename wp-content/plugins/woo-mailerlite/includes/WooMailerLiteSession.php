<?php

class WooMailerLiteSession {

    public static function get()
    {
        return WC()->session;
    }

    public static function set($key, $value)
    {
        WC()->session->set($key, $value);
    }

    public static function cart()
    {
        return json_encode(WC()->cart->get_cart());
    }

    public static function customer()
    {
        return WC()->get_customer();
    }

    public static function billingEmail()
    {
        return WC()->cart->get_customer()->get_billing_email();
    }

    public static function getMLCustomer()
    {
        return WC()->session->get('woo_mailerlite_customer_data');
    }

    public static function getMLCartHash()
    {
        if (WC()->session) {
            return WC()->session->get('woo_mailerlite_cart_hash');
        }
        return null;
    }

    public static function getMlCheckbox()
    {
        return WC()->session->get('woo_mailerlite_checkbox') ?? false;
    }
}