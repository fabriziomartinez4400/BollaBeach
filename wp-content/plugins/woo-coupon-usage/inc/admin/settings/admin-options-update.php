<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wcusage_is_settings_page' ) ) {
  function wcusage_is_settings_page() {
    $is_wcu_settings_page = ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_settings' );
    $screen_ok = false;
    if ( function_exists( 'get_current_screen' ) ) {
      $screen = get_current_screen();
      if ( isset( $screen->id ) ) {
        $screen_ok = ( $screen->id === 'coupon-affiliates_page_wcusage_settings' || false !== strpos( $screen->id, 'wcusage_settings' ) );
      }
    }
    return ( $is_wcu_settings_page || $screen_ok );
  }
}

if ( ! function_exists( 'wcusage_send_settings_nocache_headers' ) ) {
  function wcusage_send_settings_nocache_headers() {
    if ( ! function_exists( 'nocache_headers' ) ) {
      return;
    }
    if ( ! wcusage_is_settings_page() ) {
      return;
    }
    nocache_headers();
    header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );
  }
}

// Ajax Script
function wcusage_admin_options_update_scripts( $hook_suffix ) {

  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Only load on our settings page. Be resilient to screen ID differences.
  if ( ! wcusage_is_settings_page() ) {
    return;
  }

  wcusage_send_settings_nocache_headers();

  // Enqueue our script early (in head) and localize data.
  $rel_path  = '../../../js/admin-options-update.js';
  $script_url  = plugin_dir_url( __FILE__ ) . $rel_path;
  $script_path = plugin_dir_path( __FILE__ ) . $rel_path;
  $version = file_exists( $script_path ) ? filemtime( $script_path ) : false;

  wp_enqueue_script( 'wcusage-admin-options-update', $script_url, array( 'jquery' ), $version, false ); // false = load in head
  wp_localize_script( 'wcusage-admin-options-update', 'wcusageUpdate', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'wcusage_dashboard_settings_ajax_nonce' ),
  ) );
}
add_action( 'admin_enqueue_scripts', 'wcusage_admin_options_update_scripts', 5 );

/***************
***** UPDATE: Text Input
***************/
add_action( 'wp_ajax_wcu-update-text', 'wcu_update_text' );
function wcu_update_text() {

  if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Unauthorized' ) );
  }

  check_ajax_referer('wcusage_dashboard_settings_ajax_nonce');

  $option = sanitize_text_field( $_POST['option'] ?? '' );
  if ( empty( $option ) ) {
    wp_send_json_error( array( 'message' => 'Missing required information.' ) );
  }

  $value = sanitize_textarea_field( htmlentities( $_POST['value'] ?? '' ) );
  $value = html_entity_decode( stripslashes( $value ) );
  
  $CustomNum = sanitize_text_field( $_POST['customnum'] ?? '' );

  // Handle nested array values
  if ( $CustomNum ) {
    $CustomNum1 = sanitize_text_field( $_POST['customnum1'] ?? '' );
    $CustomNum2 = sanitize_text_field( $_POST['customnum2'] ?? '' );
    
    $current = wcusage_get_options();
    $nested = isset( $current[$option] ) && is_array( $current[$option] ) ? $current[$option] : array();
    $nested[$CustomNum1][$CustomNum2] = $value;
    
    wcusage_update_options_merge( array( $option => $nested ) );
  } else {
    wcusage_update_options_merge( array( $option => $value ) );
  }

  wcusage_check_if_option_refresh_stats( $option );

  wp_send_json_success();
}

/***************
***** UPDATE: Toggles
***************/
add_action( 'wp_ajax_wcu-update-toggle', 'wcu_update_toggle' );
function wcu_update_toggle() {

  if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Unauthorized' ) );
  }

  check_ajax_referer('wcusage_dashboard_settings_ajax_nonce');

  $option = sanitize_text_field( $_POST['option'] ?? '' );
  if ( empty( $option ) ) {
    wp_send_json_error( array( 'message' => 'Missing required information.' ) );
  }

  $multi = sanitize_text_field( $_POST['multi'] ?? '' );
  $value = sanitize_text_field( $_POST['value'] ?? '' );
  $key = sanitize_text_field( $_POST['key'] ?? '' );

  // Handle multi-checkbox (array) toggles
  if ( $multi ) {
    $current = wcusage_get_options();
    $existing = isset( $current[$option] ) ? $current[$option] : array();
    
    // Convert non-array to array
    if ( ! is_array( $existing ) ) {
      $existing = $existing ? array( $existing => 'on' ) : array();
    }
    
    if ( $value ) {
      $existing[$key] = 'on';
    } else {
      unset( $existing[$key] );
    }
    
    wcusage_update_options_merge( array( $option => $existing ) );
  } else {
    // Handle single toggle
    $thevalue = ( $value === '1' || $value === 1 || $value === true || $value === 'true' ) ? '1' : '0';
    wcusage_update_options_merge( array( $option => $thevalue ) );
  }

  wcusage_check_if_option_refresh_stats( $option );

  wp_send_json_success();
}

// Ajax: Refresh Stats for certain updates updating
function wcusage_check_if_option_refresh_stats($option) {
  $never_update_commission_meta = wcusage_get_setting_value('wcusage_field_enable_never_update_commission_meta', '0');
  if ( $never_update_commission_meta ) {
    return;
  }
  
  // Only refresh for specific options that affect commission calculations
  static $refresh_keys = null;
  if ( $refresh_keys === null ) {
    $refresh_keys = array_flip( array(
      'wcusage_field_affiliate',
      'wcusage_field_affiliate_fixed_order',
      'wcusage_field_affiliate_fixed_product',
      'wcusage_field_commission_before_discount',
      'wcusage_field_commission_include_shipping',
      'wcusage_field_commission_before_discount_custom',
      'wcusage_field_commission_include_fees',
      'wcusage_field_order_max_commission',
      'wcusage_field_commission_rounding_mode',
      'wcusage_field_show_tax',
      'wcusage_field_affiliate_deduct_percent',
      'wcusage_field_priority_commission',
      'wcusage_field_affiliate_deduct_percent_show',
      'wcusage_field_order_type_custom',
      'wcusage_field_order_sort'
    ) );
  }
  
  if ( isset( $refresh_keys[$option] ) ) {
    wcusage_update_options_merge( array( 'wcusage_refresh_date' => time() ) );
  }
  
  do_action('wcusage_check_if_option_refresh_stats', $option);
}

// Hook into options update
function wcusage_check_portal_option_update($option) {
  if($option == "wcusage_field_portal_enable") {
    $option_group = get_option('wcusage_options');
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    if($option_group['wcusage_field_portal_enable'] == "1") {
      add_rewrite_rule('^' . $wcusage_portal_slug . '/?$', 'index.php?affiliate_portal=1', 'top');
    } else {
      // Remove the rewrite rule
      $rules = get_option('rewrite_rules');
      if(isset($rules['^' . $wcusage_portal_slug . '/?$'])) {
        unset($rules['^' . $wcusage_portal_slug . '/?$']);
        update_option('rewrite_rules', $rules);
      }
    }
    flush_rewrite_rules();
  }
  // If wcusage_portal_slug is updated and wcusage_field_portal_enable is enabled
  if($option == "wcusage_portal_slug") {
    $option_group = get_option('wcusage_options');
    if($option_group['wcusage_field_portal_enable'] == "1") {
      add_rewrite_rule('^' . $option_group['wcusage_portal_slug'] . '/?$', 'index.php?affiliate_portal=1', 'top');
      flush_rewrite_rules();
    }
  }
  // If wcusage_mla_portal_slug is updated and wcusage_field_portal_enable is enabled
  if($option == "wcusage_mla_portal_slug") {
    $option_group = get_option('wcusage_options');
    if($option_group['wcusage_field_portal_enable'] == "1") {
      add_rewrite_rule('^' . $option_group['wcusage_mla_portal_slug'] . '/?$', 'index.php?mla_affiliate_portal=1', 'top');
      flush_rewrite_rules();
    }
  }
}
add_action('wcusage_check_if_option_refresh_stats', 'wcusage_check_portal_option_update', 10, 2);

// Post: Refresh Stats for certain updates updating
add_action('updated_option', 'wcusage_check_if_option_refresh_stats_post', 10, 3);
function wcusage_check_if_option_refresh_stats_post($option_name, $old_value, $value) {
    if ( 'wcusage_options' !== $option_name ) {
      return;
    }
    
    $never_update_commission_meta = wcusage_get_setting_value('wcusage_field_enable_never_update_commission_meta', '0');
    if ( $never_update_commission_meta ) {
      return;
    }
    
    static $refresh_keys = null;
    if ( $refresh_keys === null ) {
      $refresh_keys = array(
        'wcusage_field_affiliate',
        'wcusage_field_affiliate_fixed_order',
        'wcusage_field_affiliate_fixed_product',
        'wcusage_field_commission_before_discount',
        'wcusage_field_commission_include_shipping',
        'wcusage_field_commission_before_discount_custom',
        'wcusage_field_commission_include_fees',
        'wcusage_field_order_max_commission',
        'wcusage_field_commission_rounding_mode',
        'wcusage_field_show_tax',
        'wcusage_field_affiliate_deduct_percent',
        'wcusage_field_priority_commission',
        'wcusage_field_affiliate_deduct_percent_show',
        'wcusage_field_order_type_custom',
        'wcusage_field_order_sort'
      );
    }
    
    foreach ( $refresh_keys as $key_interest ) {
      if ( isset( $old_value[$key_interest] ) && isset( $value[$key_interest] ) ) {
        if ( $old_value[$key_interest] != $value[$key_interest] ) {
          wcusage_update_options_merge( array( 'wcusage_refresh_date' => time() ) );
          break;
        }
      }
    }
}