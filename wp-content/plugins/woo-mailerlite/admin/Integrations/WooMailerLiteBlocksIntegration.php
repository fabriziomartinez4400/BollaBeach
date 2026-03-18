<?php

class WooMailerLiteBlocksIntegration implements Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface
{
    public function get_name()
    {
        return 'woo-mailerlite';
    }

    public function initialize()
    {
        $this->register_mailerlite_block_frontend_scripts();
        $this->register_mailerlite_block_editor_scripts();
    }

    public function get_script_handles()
    {
        return ['mailerlite-block-woo-mailerlite-block-frontend'];
    }

    public function get_editor_script_handles()
    {
        return ['mailerlite-block-woo-mailerlite-block-editor'];
    }

    public function get_script_data()
    {
        $mailerLiteSettings = WooMailerLiteOptions::get('settings');
        if (!isset($mailerLiteSettings['subscribeOnCheckout'])) {
            WooMailerLiteOptions::update('settings', $mailerLiteSettings);
            return true;
        } elseif (!$mailerLiteSettings['subscribeOnCheckout']) {
            return true;
        }
        return [
            'MailerLiteWooActive'    => true,
            'MailerLiteWooLabel'     => $mailerLiteSettings['checkoutLabel'],
            'MailerLiteWooPreselect' => $mailerLiteSettings['checkoutPreselect'],
            'MailerLiteWooHidden'    => $mailerLiteSettings['checkoutHidden'],
            'MailerLiteNonce'        => wp_create_nonce('ml-block-settings-update'),
            'MailerLiteAdminURL'     => admin_url('admin.php?page=wc-settings&tab=integration&section=mailerlite'),
        ];
    }

    public function register_mailerlite_block_editor_scripts()
    {

        $script_path       = '../assets/js/blocks-integration/woo-mailerlite-admin-block.js';
        $script_asset_path = dirname(__FILE__) . $script_path;
        $script_url        = plugins_url($script_path, __FILE__);

        $script_asset = [
            'dependencies' => [
                'wc-blocks-checkout',
                'wc-settings',
                'wp-block-editor',
                'wp-blocks',
                'wp-components',
                'wp-element',
                'wp-i18n',
            ],
            'version'      => $this->get_file_version($script_asset_path),
        ];

        wp_register_script(
            'mailerlite-block-woo-mailerlite-block-editor',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }

    public function register_mailerlite_block_frontend_scripts()
    {
        $script_path       = '../../public/js/blocks-integration/woo-mailerlite-blocks.js';
        $script_url        = plugins_url($script_path, __FILE__);
        $script_asset_path = dirname(__FILE__) . $script_path;

        $script_asset = [
            'dependencies' => [
                'wc-blocks-checkout',
                'wc-settings',
            ],
            'version'      => $this->get_file_version($script_asset_path),
        ];

        $result = wp_register_script(
            'mailerlite-block-woo-mailerlite-block-frontend',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        if ( ! $result) {

            return false;
        }

        wp_localize_script(
            'mailerlite-block-woo-mailerlite-block-frontend',
            'wooMailerLiteBlockData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('woo_mailerlite_cart_nonce'),
            ]
        );

        return true;
    }

    protected function get_file_version($file)
    {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG && file_exists($file)) {
            return filemtime($file);
        }

        return WOO_MAILERLITE_VERSION;
    }
}
