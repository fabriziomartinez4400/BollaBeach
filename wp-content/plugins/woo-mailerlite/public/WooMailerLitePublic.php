<?php

class WooMailerLitePublic {


    /**
     * The single instance of the class.
     * @var null $instance
     */
    protected static $instance = null;

    /**
     * @return self
     */
    public static function instance()
    {
        if (!empty(static::$instance)) {
            return static::$instance;
        }
        static::$instance = new static();
        return static::$instance;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {
        if (function_exists('is_checkout') && is_checkout()) {
            wp_register_script('woo-mailerlite-public', plugin_dir_url( __FILE__ ) . 'js/woo-mailerlite-public.js', array(), WOO_MAILERLITE_VERSION);
            wp_localize_script('woo-mailerlite-public', 'wooMailerLitePublicData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('woo_mailerlite_cart_nonce'),
                'language' => get_locale(),
                'checkboxSettings' => [
                    'enabled' => (bool) WooMailerLiteOptions::get("settings.subscribeOnCheckout"),
                    'label' => WooMailerLiteOptions::get("settings.checkoutLabel"),
                    'preselect' => (bool) WooMailerLiteOptions::get('settings.checkoutPreselect'),
                    'hidden' => (bool) WooMailerLiteOptions::get('settings.checkoutHidden'),
                ]
            ));
            wp_enqueue_script('woo-mailerlite-public', '', array(), WOO_MAILERLITE_VERSION, true);
        }

        if (!WooMailerLiteOptions::get('enabled')) {
            return true;
        }
        if (is_plugin_active("official-mailerlite-sign-up-forms/mailerlite.php")) {
            return true;
        }
        if (WooMailerLiteApi::client()->isRewrite()) {
            wp_enqueue_script(
                'mailerlite-rewrite-universal',
                plugin_dir_url(__FILE__) . 'js/universal/rewrite-universal.js',
                array(),
                null,
                true
            );
            wp_localize_script('mailerlite-rewrite-universal', 'mailerliteData', array(
                'account' => (int) WooMailerLiteOptions::get('accountId'),
                'enablePopups' => (bool) WooMailerLiteOptions::get('popupsEnabled'),
            ));
        } else {
            $shopUrl = home_url();
            $shopUrl = str_replace('http://', '', $shopUrl);
            $shopUrl = str_replace('https://', '', $shopUrl);
            wp_enqueue_script(
                'mailerlite-classic-universal',
                plugin_dir_url(__FILE__) . 'js/universal/classic-universal.js',
                array(),
                null,
                true
            );
            wp_localize_script('mailerlite-classic-universal', 'mailerliteData', array(
                'account' => (int) WooMailerLiteOptions::get('accountId'),
                'accountSubdomain' => WooMailerLiteOptions::get('accountSubdomain'),
                'shopUrl' => $shopUrl,
                'enablePopups' => (bool) WooMailerLiteOptions::get('popupsEnabled'),
            ));
        }
        return true;
    }

    public function removeOptionalFromMlCheckbox($field)
    {

        if (is_checkout() && ! is_wc_endpoint_url() && strpos($field,
                'woo_ml_subscribe') !== false && WooMailerLiteOptions::get('enabled')) {

            $optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
            $field    = str_replace($optional, '', $field);
        }

        return $field;
    }
}
