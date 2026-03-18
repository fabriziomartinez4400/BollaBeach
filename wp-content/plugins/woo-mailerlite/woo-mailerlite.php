<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           woo-mailerlite
 *
 * @wordpress-plugin
 * Plugin Name:       MailerLite - WooCommerce integration
 * Plugin URI:        https://mailerlite.com
 * Description:       Official MailerLite integration for WooCommerce. Track sales and campaign ROI, import products details, automate emails based on purchases and seamlessly add your customers to your email marketing lists via WooCommerce's checkout process.
 * Version:           3.1.11
 * Author:            MailerLite
 * Author URI:        https://mailerlite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins:  woocommerce
 * Text Domain:       woo-mailerlite
 * Domain Path:       /languages
 */


if ( ! defined( 'WPINC' ) ) {
    die;
}

if (!isset($woo_mailerlite_autoload) || $woo_mailerlite_autoload === false) {
    include_once __DIR__ . "/bootstrap.php";
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Update when you release new versions.
 */
define( 'WOO_MAILERLITE_VERSION', '3.1.11' );

define('WOO_MAILERLITE_ASYNC_JOBS', false);

define('WOO_MAILERLITE_DIR', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-mailerlite-activator.php
 */
register_activation_hook( __FILE__, 'activate_woo_mailerlite');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-mailerlite-deactivator.php
 */
register_deactivation_hook( __FILE__, 'deactivate_woo_mailerlite' );

/**
 * The main function responsible for returning the one true WooMailerLite
 * instance to functions everywhere
 * @return      WooMailerLite The one true WooMailerLite
 *
 * @since       1.0.0
 */
add_action('plugins_loaded', 'run_woo_mailerlite', 12);
add_action('woocommerce_blocks_loaded', function () {
        if (class_exists('\Automattic\WooCommerce\Blocks\Package') &&
            interface_exists('\Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface')) {
            add_action(
                'woocommerce_blocks_checkout_block_registration',
                function ($integration_registry) {
                    try {
                        $integration_registry->register(new WooMailerLiteBlocksIntegration());
                    } catch (Exception $e) {
                        WooMailerLiteLog()->error('woocommerce_blocks_loaded', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    }
                }
            );

            add_filter(
                '__experimental_woocommerce_blocks_add_data_attributes_to_block',
                function ($allowed_blocks) {
                    $allowed_blocks[] = 'mailerlite-block/woo-mailerlite';

                    return $allowed_blocks;
                },
            );
    }
});
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

add_filter('auto_update_plugin', function ($update, $item) {
    if (isset($item->plugin) && $item->plugin === 'woo-mailerlite/woo-mailerlite.php') {
        return WooMailerLiteOptions::get('settings.autoUpdatePlugin');
    }
    return $update;
}, 10, 2);
