<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wcu_fs' ) ) {
  wcu_fs()->add_action('after_uninstall', 'wcu_fs_uninstall_cleanup');
}

/**
 * Runs on uninstall to cleanup plugin data, if option is enabled.
 *
 */
if( !function_exists( 'wcu_fs_uninstall_cleanup' ) ) {
  function wcu_fs_uninstall_cleanup() {

    $options = get_option( 'wcusage_options' );
    if(isset($options['wcusage_field_deactivate_delete'])) {
      $wcusage_field_deactivate_delete = $options['wcusage_field_deactivate_delete'];
    } else {
      $wcusage_field_deactivate_delete = 0;
    }

    // Return if either plugin "woo-coupon-usage" or "woo-coupon-usage-pro" is active.
    if( is_plugin_active( 'woo-coupon-usage/woo-coupon-usage.php' ) || is_plugin_active( 'woo-coupon-usage-pro/woo-coupon-usage-pro.php' ) ) {
      return;
    }

    // Delete data if option is enabled.
    if($wcusage_field_deactivate_delete) {

      global $wpdb;

      // Delete Options
      delete_option( 'wcusage_options' );

      // Database Tables
      delete_option("wcusage_db_version");

      // DB Versions
      delete_option( "wcusage_db_version" );
      delete_option( "wcusage_clicks_db_version" );
      delete_option( "wcusage_register_db_version" );
      delete_option( "wcusage_campaigns_db_version" );
      delete_option( "wcusage_field_order_type_custom_isset" );
      delete_option( "wcusage_setup_complete" );

      // Delete Register Table
      $table_name1 = $wpdb->prefix . 'wcusage_register';
      $wpdb->query( "DROP TABLE IF EXISTS " . $table_name1 ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

      // Delete Payouts Table
      $table_name2 = $wpdb->prefix . 'wcusage_payouts';
      $wpdb->query( "DROP TABLE IF EXISTS " . $table_name2 ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

      // Delete Campaigns Table
      $table_name3 = $wpdb->prefix . 'wcusage_campaigns';
      $wpdb->query( "DROP TABLE IF EXISTS " . $table_name3 ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

      // Delete Clicks Table
      $table_name4 = $wpdb->prefix . 'wcusage_clicks';
      $wpdb->query( "DROP TABLE IF EXISTS " . $table_name4 ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

      // Delete Direct Links Table
      $table_name5 = $wpdb->prefix . 'wcusage_directlinks';
      $wpdb->query( "DROP TABLE IF EXISTS " . $table_name5 ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    }

  }
}
