<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WooMailerlite
 */
class WooMailerLiteActivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        WooMailerLiteMigration::migrate();
        WooMailerLiteOptions::update('initial_sync', false);
    }

    public static function deactivate()
    {
        update_option('woo_mailerlite', ['activated' => false]);
    }
}
