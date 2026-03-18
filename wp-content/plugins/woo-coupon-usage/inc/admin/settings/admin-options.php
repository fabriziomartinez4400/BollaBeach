<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * custom option and settings
 */
function wcusage_settings_init() {
    // register a new setting for "wcusage" page
    // Attach sanitize callback so legacy bulk save (options.php) merges into existing
    // options instead of overwriting missing keys (which can happen due to hidden tabs
    // or PHP max_input_vars limits).
    register_setting( 'wcusage', 'wcusage_options', array(
        'sanitize_callback' => 'wcusage_options_sanitize',
    ) );
    // register a new section in the "wcusage" page
    $options = get_option( 'wcusage_options' );
    add_settings_section(
        'wcusage_section_developers',
        '',
        'wcusage_section_developers_cb',
        'wcusage'
    );
    // register general settings
    add_settings_field(
        'wcusage_field_orders',
        esc_html__( 'General Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb',
        'wcusage',
        'wcusage_section_developers',
        [
            'class'               => 'wcusage_row wcusage_row_general',
            'wcusage_custom_data' => 'custom',
        ]
    );
    // register commission settings
    add_settings_field(
        'wcusage_field_commission',
        esc_html__( 'Commission Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_commission',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_commission',
        ]
    );
    // register commission settings
    add_settings_field(
        'wcusage_field_fraud',
        esc_html__( 'Fraud Prevention & Usage Restrictions', 'woo-coupon-usage' ),
        'wcusage_field_cb_fraud',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_fraud',
        ]
    );
    // register URL's
    add_settings_field(
        'wcusage_field_urls',
        esc_html__( 'URL Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_urls',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_urls',
        ]
    );
    // register Email Notifications
    add_settings_field(
        'wcusage_field_notifications',
        esc_html__( 'Notifications Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_notifications',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_notifications',
        ]
    );
    // register currency section
    add_settings_field(
        'wcusage_field_currency',
        esc_html__( 'Currency Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_currency',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_currency',
        ]
    );
    // register Payouts
    add_settings_field(
        'wcusage_field_payouts',
        esc_html__( 'Payouts Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_payouts',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_payouts',
        ]
    );
    // register pdf reports
    add_settings_field(
        'wcusage_field_reports',
        esc_html__( 'Affiliate Reports Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_reports',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_reports',
        ]
    );
    // register custom tabs section
    add_settings_field(
        'wcusage_field_custom_tabs',
        esc_html__( 'Custom Affiliate Dashboard Tabs', 'woo-coupon-usage' ),
        'wcusage_field_cb_custom_tabs',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_custom_tabs',
        ]
    );
    // register Registration
    add_settings_field(
        'wcusage_field_registration',
        esc_html__( 'Registration Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_registration',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_registration',
        ]
    );
    // register Subscriptions
    add_settings_field(
        'wcusage_field_subscriptions',
        esc_html__( 'Subscription Renewal Settings', 'woo-coupon-usage' ),
        'wcusage_field_cb_subscriptions',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_subscriptions',
        ]
    );
    // register design
    add_settings_field(
        'wcusage_field_design',
        esc_html__( 'Design', 'woo-coupon-usage' ),
        'wcusage_field_cb_design',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_design',
        ]
    );
    // register floating widget
    add_settings_field(
        'wcusage_field_widget',
        esc_html__( 'Floating Widget', 'woo-coupon-usage' ),
        'wcusage_field_cb_widget',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_widget',
        ]
    );
    // register debug
    add_settings_field(
        'wcusage_field_debug',
        esc_html__( 'Performance & Debug', 'woo-coupon-usage' ),
        'wcusage_field_cb_debug',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_debug',
        ]
    );
    // help area
    add_settings_field(
        'wcusage_field_help',
        esc_html__( 'Help Area', 'woo-coupon-usage' ),
        'wcusage_field_cb_help',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_help',
        ]
    );
    // pro version
    add_settings_field(
        'wcusage_field_pro_details',
        esc_html__( 'Pro Details', 'woo-coupon-usage' ),
        'wcusage_field_cb_pro_details',
        'wcusage',
        'wcusage_section_developers',
        [
            'class' => 'wcusage_row wcusage_row_pro_details',
        ]
    );
}

//register our wcusage_settings_init to the admin_init action hook
add_action( 'admin_init', 'wcusage_settings_init' );
/**
 * Get wcusage_options array safely.
 * Always returns an array, never false.
 */
if ( !function_exists( 'wcusage_get_options' ) ) {
    function wcusage_get_options() {
        $options = get_option( 'wcusage_options', array() );
        return ( is_array( $options ) ? $options : array() );
    }

}
/**
 * Merge updates into base options array.
 * Arrays are replaced entirely to allow clearing unchecked checkboxes.
 */
if ( !function_exists( 'wcusage_options_merge' ) ) {
    function wcusage_options_merge(  $base, $updates  ) {
        $merged = ( is_array( $base ) ? $base : array() );
        $new = ( is_array( $updates ) ? $updates : array() );
        foreach ( $new as $key => $value ) {
            // Replace arrays entirely to allow clearing unchecked values
            $merged[$key] = $value;
        }
        return $merged;
    }

}
/**
 * Update options using merge strategy.
 * Use this in AJAX handlers to update specific fields without wiping others.
 */
if ( !function_exists( 'wcusage_update_options_merge' ) ) {
    function wcusage_update_options_merge(  $updates  ) {
        $current = wcusage_get_options();
        $merged = wcusage_options_merge( $current, $updates );
        if ( $merged !== $current ) {
            update_option( 'wcusage_options', $merged );
        }
        return $merged;
    }

}
/**
 * Register a default value for a setting.
 * Used by wcusage_get_all_default_settings() to collect defaults.
 */
if ( !function_exists( 'wcusage_register_default_setting' ) ) {
    function wcusage_register_default_setting(  $name, $default  ) {
        global $wcusage_all_default_settings;
        if ( $default === '' || $default === null ) {
            return;
        }
        if ( !is_array( $wcusage_all_default_settings ) ) {
            $wcusage_all_default_settings = array();
        }
        if ( !array_key_exists( $name, $wcusage_all_default_settings ) ) {
            $wcusage_all_default_settings[$name] = $default;
        }
    }

}
/**
 * Apply all registered defaults to the database.
 * Called after wcusage_get_all_default_settings() collects defaults.
 */
if ( !function_exists( 'wcusage_apply_registered_defaults' ) ) {
    function wcusage_apply_registered_defaults() {
        global $wcusage_all_default_settings;
        $options = wcusage_get_options();
        if ( !is_array( $wcusage_all_default_settings ) || empty( $wcusage_all_default_settings ) ) {
            return $options;
        }
        $merged = $options;
        foreach ( $wcusage_all_default_settings as $key => $value ) {
            if ( !array_key_exists( $key, $merged ) ) {
                $merged[$key] = $value;
            }
        }
        if ( $merged !== $options ) {
            update_option( 'wcusage_options', $merged );
        }
        // Mark that defaults have been applied (simple flag, not per-key tracking)
        update_option( 'wcusage_default_set', '1' );
        return $merged;
    }

}
/**
 * Apply defaults when settings page loads.
 * Also provides self-healing if options get wiped.
 */
if ( !function_exists( 'wcusage_apply_defaults_on_settings_page' ) ) {
    function wcusage_apply_defaults_on_settings_page() {
        if ( !function_exists( 'current_user_can' ) || !current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( !is_admin() ) {
            return;
        }
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_settings' ) {
            $options = wcusage_get_options();
            $default_set = get_option( 'wcusage_default_set', array() );
            if ( !is_array( $default_set ) ) {
                $default_set = array();
            }
            $force_defaults = isset( $_GET['wcusage_init_defaults'] ) && $_GET['wcusage_init_defaults'] === '1';
            // Skip defaults if existing install has 50+ options (already populated)
            // Just mark as set and avoid processing
            if ( empty( $default_set ) && !$force_defaults && count( $options ) >= 50 ) {
                update_option( 'wcusage_default_set', '1' );
                return;
            }
            // Apply defaults if: forced, never set before, OR if options are empty (self-healing)
            // Safe to use empty($default_set) - defaults only fill missing keys, never override existing
            if ( $force_defaults || empty( $default_set ) || empty( $options ) ) {
                if ( function_exists( 'wcusage_get_all_default_settings' ) ) {
                    global $wcusage_allow_defaults_update;
                    $wcusage_allow_defaults_update = true;
                    try {
                        wcusage_get_all_default_settings();
                    } finally {
                        $wcusage_allow_defaults_update = false;
                    }
                }
            }
        }
    }

    add_action( 'admin_init', 'wcusage_apply_defaults_on_settings_page', 20 );
}
/**
 * Merge all wcusage_options updates by default to avoid wiping unrelated keys.
 * 
 * This hook intercepts ALL updates to wcusage_options and ensures that:
 * - AJAX updates only change the fields they intend to change
 * - GET requests can't accidentally wipe settings
 * - Bulk saves preserve hidden/untouched fields
 * - Empty arrays and non-arrays are blocked during GET requests
 */
if ( !function_exists( 'wcusage_merge_option_updates' ) ) {
    function wcusage_merge_option_updates(  $new_value, $old_value, $option  ) {
        if ( $option !== 'wcusage_options' ) {
            return $new_value;
        }
        $method = ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : '' );
        $is_settings_page = isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_settings';
        global $wcusage_allow_defaults_update;
        $is_get_settings = $method === 'GET' && $is_settings_page && empty( $wcusage_allow_defaults_update ) && !(function_exists( 'wp_doing_ajax' ) && wp_doing_ajax());
        $old = ( is_array( $old_value ) ? $old_value : wcusage_get_options() );
        // Block non-array updates during GET requests
        if ( !is_array( $new_value ) && $is_get_settings ) {
            return $old;
        }
        // Block empty array updates during GET requests
        if ( $is_get_settings && empty( $new_value ) ) {
            return $old;
        }
        // During GET requests, only allow specific whitelisted keys to be updated
        if ( $is_get_settings ) {
            $allow_update_keys = array('wcusage_refresh_date');
            if ( isset( $_GET['update_conversion_rates'] ) ) {
                $allow_update_keys[] = 'wcusage_field_currencies';
            }
            if ( array_key_exists( '__force_update', $new_value ) ) {
                $force_update = !empty( $new_value['__force_update'] );
                unset($new_value['__force_update']);
                if ( !$force_update ) {
                    $allow_update_keys = array();
                }
            }
            foreach ( $new_value as $key => $value ) {
                if ( !array_key_exists( $key, $old ) && !in_array( $key, $allow_update_keys, true ) ) {
                    unset($new_value[$key]);
                    continue;
                }
                if ( array_key_exists( $key, $old ) && !in_array( $key, $allow_update_keys, true ) ) {
                    $new_value[$key] = $old[$key];
                }
            }
        }
        // Allow explicit full replace when requested (escape hatch)
        if ( array_key_exists( '__full_replace', $new_value ) ) {
            $replace = !empty( $new_value['__full_replace'] );
            unset($new_value['__full_replace']);
            if ( $replace ) {
                return $new_value;
            }
        }
        // Merge new values into existing options
        return wcusage_options_merge( $old, $new_value );
    }

    add_filter(
        'pre_update_option_wcusage_options',
        'wcusage_merge_option_updates',
        5,
        3
    );
}
/**
 * Sanitize handler for wcusage_options.
 *
 * Merges the submitted array with the existing option so that any fields not
 * present in the POST (e.g., hidden tabs, or trimmed by max_input_vars) are
 * preserved instead of being reset to defaults.
 *
 * - Submitted empty strings and explicit 0 values still override previous values.
 * - Arrays are merged recursively, preferring submitted values.
 */
if ( !function_exists( 'wcusage_options_sanitize' ) ) {
    function wcusage_options_sanitize(  $input  ) {
        // Ensure only authorized users can affect options via direct calls.
        if ( function_exists( 'current_user_can' ) && !current_user_can( 'manage_options' ) ) {
            return get_option( 'wcusage_options', array() );
        }
        // Normalize input/output arrays.
        $new = ( is_array( $input ) ? $input : array() );
        $old = get_option( 'wcusage_options', array() );
        return wcusage_options_merge( $old, $new );
    }

}
// Display admin settings
function wcusage_section_developers_cb(  $args  ) {
    if ( !wcu_fs()->is__premium_only() || !wcu_fs()->can_use_premium_code() ) {
        $ispro = false;
    } else {
        $ispro = true;
    }
    $options = get_option( 'wcusage_options' );
    if ( function_exists( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    } else {
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'thickbox' );
    }
    ?>

<!--- Font Awesome -->
<link rel="stylesheet" href="<?php 
    echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
    ?>" crossorigin="anonymous">

<?php 
    if ( class_exists( 'WooCommerce' ) ) {
        if ( version_compare( WC_VERSION, 3.7, "<=" ) ) {
            ?>
    <p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> You are using an old version of WooCommerce. Version 3.7 or later is required for full access to all this plugins features.</strong><br/></p>
    <?php 
        }
    } else {
        // Check if WooCommerce is installed
        $path = 'woocommerce/woocommerce.php';
        $installed_plugins = get_plugins();
        // WooCommerce is installed but not active
        if ( isset( $installed_plugins[$path] ) ) {
            $activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $path, 'activate-plugin_' . $path );
            echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce is installed but not activated. <a href="' . esc_url( $activate_url ) . '">Click here to activate it.</a></strong></p>';
        } else {
            $install_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce' );
            echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce needs to be installed for this plugin to work. <a href="' . esc_url( $install_url ) . '">Click here to install it.</a></strong></p>';
        }
    }
    ?>

<!---
***** Script to Toggle Settings Sidebar *****
--->

<script>
jQuery( document ).ready(function() {

  // Handle sidebar navigation clicks
  jQuery( ".wcu-sidebar-link" ).on('click', function(){
  jQuery(".wcu-sidebar-link" ).removeClass("active");
  jQuery( this ).addClass("active");
  // Always scroll to top of window and content area
  window.scrollTo({ top: 0, behavior: 'smooth' });
  jQuery('html, body').animate({ scrollTop: 0 }, 100);
  var $content = jQuery('.wcu-admin-content');
  if ($content.length) {
    $content.animate({ scrollTop: 0 }, 100);
  }
	});

  // Set first tab as active by default
  jQuery(".wcu-sidebar-link").first().addClass("active");
  jQuery(".wcusage_row").first().show();

  <?php 
    wcusage_admin_settings_tab_click( "#tab-general", ".wcusage_row_general", 0 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-commission", ".wcusage_row_commission", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-fraud", ".wcusage_row_fraud", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-pro", ".wcusage_row_pro", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-urls", ".wcusage_row_urls", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-notifications", ".wcusage_row_notifications", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-payouts", ".wcusage_row_payouts", 1 );
    ?>

  <?php 
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-currency", ".wcusage_row_currency", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-registration", ".wcusage_row_registration", 1 );
    ?>

  <?php 
    ?>

  <?php 
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-subscriptions", ".wcusage_row_subscriptions", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-translations", ".wcusage_row_translations", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-design", ".wcusage_row_design", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-widget", ".wcusage_row_widget", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-debug", ".wcusage_row_debug", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-help", ".wcusage_row_help", 1 );
    ?>

  <?php 
    wcusage_admin_settings_tab_click( "#tab-pro-details", ".wcusage_row_pro_details", 1 );
    ?>

});
</script>

<!---
***** Settings Layout with Sidebar *****
--->

<div class="wcu-admin-layout-container">
  
  <!-- Left Sidebar Navigation (Flat List, No Groups) -->
  <div class="wcu-admin-sidebar">
    <nav class="wcu-admin-sidebar-nav">
      <ul class="wcu-sidebar-menu">
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-general",
        esc_html__( "General", "woo-coupon-usage" ),
        "fa fa-gear",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-commission",
        esc_html__( "Commission", "woo-coupon-usage" ),
        "fas fa-money-bill-wave",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_js_settings_tab_toggle( '.wcusage_field_enable_currency', '', '#tab-currency' );
    ?>
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-currency",
        esc_html__( "Currencies", "woo-coupon-usage" ),
        "fas fa-dollar-sign",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-registration",
        esc_html__( "Registration", "woo-coupon-usage" ),
        "fas fa-user-circle",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-urls",
        esc_html__( "Referral Links", "woo-coupon-usage" ),
        "fas fa-link",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-fraud",
        esc_html__( "Fraud Prevention", "woo-coupon-usage" ),
        "fa-solid fa-user-secret",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-notifications",
        esc_html__( "Emails", "woo-coupon-usage" ),
        "fas fa-envelope",
        0,
        ''
    );
    ?>
        </li>
        <?php 
    ?>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_js_settings_tab_toggle( '.wcusage_field_tracking_enable', '', '#tab-payouts' );
    ?>
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-payouts",
        esc_html__( "Payouts", "woo-coupon-usage" ),
        "fas fa-handshake",
        1,
        ''
    );
    ?>
        </li>
        <?php 
    ?>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_js_settings_tab_toggle( '.wcusage_field_enable_reports', '', '#tab-reports' );
    ?>
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-reports",
        esc_html__( "Reports", "woo-coupon-usage" ),
        "fas fa-file-alt",
        1,
        ''
    );
    ?>
        </li>
        <?php 
    ?>
        <?php 
    $wcusage_subscriptions_enable = ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ? true : false );
    if ( $wcusage_subscriptions_enable ) {
        ?>
        <li class="wcu-sidebar-menu-item">
          <?php 
        wcusage_admin_settings_sidebar_button(
            "tab-subscriptions",
            esc_html__( "Subscriptions", "woo-coupon-usage" ),
            "fas fa-sync-alt",
            0,
            ''
        );
        ?>
        </li>
        <?php 
    }
    ?>
        <?php 
    $wcusage_field_show_custom_translations = wcusage_get_setting_value( 'wcusage_field_show_custom_translations', '0' );
    if ( $wcusage_field_show_custom_translations ) {
        ?>
        <li class="wcu-sidebar-menu-item">
          <?php 
        wcusage_admin_settings_sidebar_button(
            "tab-translations",
            esc_html__( "Translations", "woo-coupon-usage" ),
            "fas fa-language",
            0,
            ''
        );
        ?>
        </li>
        <?php 
    }
    ?>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-design",
        esc_html__( "Design", "woo-coupon-usage" ),
        "fas fa-palette",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-widget",
        esc_html__( "Floating Widget", "woo-coupon-usage" ),
        "fas fa-square-caret-right",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-debug",
        esc_html__( "Debug", "woo-coupon-usage" ),
        "fas fa-wrench",
        0,
        ''
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-help",
        esc_html__( "Help & Support", "woo-coupon-usage" ),
        "fas fa-question-circle",
        0,
        'background: #bb9523; color: #fff;'
    );
    ?>
        </li>
        <li class="wcu-sidebar-menu-item">
          <?php 
    wcusage_admin_settings_sidebar_button(
        "tab-pro-details",
        esc_html__( "PRO Modules", "woo-coupon-usage" ),
        "fas fa-star",
        0,
        'background: green; color: #fff;'
    );
    ?>
        </li>
      </ul>
    </nav>
  </div>
  
  <!-- Main Content Area -->
  <div class="wcu-admin-content">
<?php 
}

/**
 * Options Page
 *
 */
if ( !function_exists( 'wcusage_options_page_html' ) ) {
    function wcusage_options_page_html() {
        // check user capabilities
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        // add error/update messages
        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // Compose message with number of changed settings (legacy bulk save)
            $changed_msg = esc_html__( 'Settings Saved', 'woo-coupon-usage' );
            if ( function_exists( 'get_transient' ) ) {
                $changed = get_transient( 'wcusage_last_bulk_changed_count' );
                if ( false !== $changed ) {
                    $changed_msg = sprintf( esc_html__( '%d settings updated.', 'woo-coupon-usage' ), intval( $changed ) );
                    delete_transient( 'wcusage_last_bulk_changed_count' );
                }
            }
            add_settings_error(
                'wcusage_messages',
                'wcusage_message',
                $changed_msg,
                'updated'
            );
            flush_rewrite_rules( false );
        }
        // show error/update messages
        settings_errors( 'wcusage_messages' );
        ?>

   <div class="wrap plugin-settings wcusage-settings">

   <?php 
        do_action( 'wcusage_hook_dashboard_page_header', '' );
        ?>

  <div class="wcu-settings-header">
      <h2 class="wcu-settings-title">
        <?php 
        echo esc_html( get_admin_page_title() );
        ?>
        <a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_setup' ) );
        ?>" class="wcusage-settings-button"><?php 
        echo esc_html__( 'Setup Wizard', 'woo-coupon-usage' );
        ?> <span class="fa-solid fa-circle-arrow-right"></span></a>
        <a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_add_affiliate' ) );
        ?>" class="wcusage-settings-button"><?php 
        echo sprintf( esc_html__( 'Add New %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
        ?> <span class="fa-solid fa-circle-arrow-right"></span></a>
        <a href="https://couponaffiliates.com/docs/setup-guide-free?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=getting-started"
        target="_blank" style="margin-left: 20px; font-size: 14px;"><?php 
        echo esc_html__( "View setup guide", "woo-coupon-usage" );
        ?> <span class='fas fa-arrow-circle-right'></span></a>
      </h2>

  <div id="wcu-settings-search-right" aria-label="Search settings">
        <div class="wcu-search-row">
          <span class="wcu-search-prompt" aria-hidden="true">
            <span class="wcu-search-prompt-text">Looking for something?</span>
            <span class="wcu-search-prompt-arrow" role="presentation"></span>
          </span>
          <input type="search" id="wcu-settings-search" placeholder="Search settings..." />
        </div>
        <div id="wcu-settings-search-results">
          <ul></ul>
        </div>
  <div id="wcu-settings-search-empty" style="display:none;">
    <ul>
      <li class="wcu-search-no-results" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; color: #888;">
        <span style="font-weight: 600;">No matching settings found.</span>
      </li>
    </ul>
  </div>
      </div>
    </div>

    <?php 
        $wcusage_field_deactivate_delete = wcusage_get_setting_value( 'wcusage_field_deactivate_delete', '0' );
        if ( $wcusage_field_deactivate_delete ) {
            echo "<p style='color: red; font-weight: bold; margin-bottom: 0;'>" . esc_html__( "[Warning] You have this option enabled: Delete plugin options and custom database tables on plugin deletion. (See 'Debug' Settings)", "woo-coupon-usage" ) . "</p>";
        }
        ?>

    <?php 
        if ( !wcu_fs()->is_premium() && wcu_fs()->can_use_premium_code() ) {
            ?>
    <p style="font-size: 20px; color: red; margin-bottom: 0;"><strong>
      <?php 
            echo esc_html__( "You have a Pro license! Please deactivate the FREE version and install the PRO version instead to enable the new functionality.", "woo-coupon-usage" );
            ?>
    </strong></p>
    <?php 
        }
        ?>

    <?php 
        ?>

  	<?php 
        $coupon_shortcode_page = wcusage_get_coupon_shortcode_page( 1 );
        ?>

  	<!-- Generate Getting Started Message -->
  	<?php 
        do_action( 'wcusage_hook_getting_started_create' );
        do_action( 'wcusage_hook_checklist' );
        ?>

    <?php 
        // Output if refresh stats link clicked
        if ( isset( $_GET['refreshstats'] ) ) {
            if ( $_GET['refreshstats'] ) {
                $option_group = get_option( 'wcusage_options' );
                $option_group['wcusage_refresh_date'] = time();
                update_option( 'wcusage_options', $option_group );
                if ( isset( $options['wcusage_refresh_date'] ) ) {
                    $wcusage_refresh_date = $options['wcusage_refresh_date'];
                } else {
                    $wcusage_refresh_date = "";
                }
                ?>

          <p style="max-width: 500px;">Success! All affiliate dashboard stats will now be refreshed and re-calculated, the next time the affiliate dashboard is loaded (first load may take a few seconds longer).</p>

          <p>Redirecting back to settings in <span id="count">5</span> seconds...</p>

          <script type="text/javascript">

          window.onload = function(){

          (function(){
            var counter = 5;

            setInterval(function() {
              counter--;
              if (counter >= 0) {
                span = document.getElementById("count");
                span.innerHTML = counter;
              }
              // Display 'counter' wherever you want to display it.
              if (counter === 0) {
              //    alert('this is where it happens');
                  clearInterval(counter);
              }

            }, 1000);

          })();

          }

          </script>

          <?php 
                echo "<style>.wcusage-settings-form, .wcu-settings-sidebar { display: none; }</style>";
                header( "refresh:5; url=" . esc_url( get_admin_url() ) . "admin.php?page=wcusage_settings" );
            }
        }
        ?>

    <?php 
        // Output if refresh stats link clicked
        if ( isset( $_GET['section'] ) ) {
            ?>
      <script>
      jQuery( document ).ready(function() {
        setTimeout(
          function()
          {
            jQuery( "#<?php 
            echo esc_html( $_GET['section'] );
            ?>" ).trigger('click');
          }, 50);
      });
      </script>
      <?php 
        }
        ?>

    <?php 
        wcusage_test_report_form();
        ?>

    <?php 
        if ( !class_exists( 'WooCommerce' ) ) {
            // Check if WooCommerce is installed
            $path = 'woocommerce/woocommerce.php';
            $installed_plugins = get_plugins();
            // WooCommerce is installed but not active
            if ( isset( $installed_plugins[$path] ) ) {
                $activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $path, 'activate-plugin_' . $path );
                echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce is installed but not activated. <a href="' . esc_url( $activate_url ) . '">Click here to activate it.</a></strong></p>';
            } else {
                $install_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce' );
                echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce needs to be installed for this plugin to work. <a href="' . esc_url( $install_url ) . '">Click here to install it.</a></strong></p>';
            }
            ?>
        <style>.wcusage-settings-form { display: none; }</style>
        <?php 
        }
        ?>

  	<!-- Generate Settings Page Area -->
  	<form class="wcusage_row_setting wcusage-settings-form" action="options.php" method="post">
  	<?php 
        settings_fields( 'wcusage' );
        do_settings_sections( 'wcusage' );
        ?>

      <br/><hr/>

      <p style="font-size: 20px; color: green; display: none;" id="wcu-number-settings-saved-message"><i class="fas fa-check-square" style="font-size: 20px; color: green; background: transparent; padding: 0;"></i>&nbsp; <span id="wcu-number-settings-saved">0</span> settings have been successfully updated & saved.</p>
      <div id="wcu-save-all-container" style="display:none; margin-top:6px;">
        <button type="button" id="wcu-save-all-button" class="button button-small" style="padding: 0 8px; height: 24px; line-height: 22px;">
          <?php 
        echo esc_html__( 'Save All Settings', 'woo-coupon-usage' );
        ?>
        </button>
        <span id="wcu-save-all-status" style="margin-left:8px; font-size:12px; color:#666; display:none;"></span>
      </div>

      <div style="transform: scale(0.7); transform-origin: 0 0; margin-top: 40px;">

        <?php 
        $wcusage_field_settings_legacy = wcusage_get_setting_value( 'wcusage_field_settings_legacy', '0' );
        ?>
        <?php 
        wcusage_setting_select_option(
            'wcusage_field_settings_legacy',
            $wcusage_field_settings_legacy,
            esc_html__( 'Settings not saving correctly? Switch saving mode', 'woo-coupon-usage' ),
            '0px',
            array(
                '0' => esc_html__( 'Automatic Saving (AJAX)', 'woo-coupon-usage' ),
                '1' => esc_html__( 'Manual Saving (Legacy bulk)', 'woo-coupon-usage' ),
            )
        );
        ?>
        <i style="margin-top: -5px;"><?php 
        echo esc_html__( 'Selecting manual will disable automatic ajax saving, and instead will enable the "Save Settings" button, and you will save all settings at once.', 'woo-coupon-usage' );
        ?></i>
        
        <br/>

        <script>
        jQuery( document ).ready(function() {
          function wcusage_update_save_visibility() {
            var val = jQuery('#wcusage_field_settings_legacy').val();
            if (val === '1') {
              jQuery('.wcu-field-section-save').show();
            } else {
              jQuery('.wcu-field-section-save').hide();
            }
          }
          wcusage_update_save_visibility();
          jQuery('#wcusage_field_settings_legacy').on('change', wcusage_update_save_visibility);
        });
        </script>
        <span class="wcu-field-section-save">
          
          <?php 
        submit_button( esc_html__( 'Save Settings', 'woo-coupon-usage' ) );
        ?>

          <?php 
        if ( ini_get( 'max_input_vars' ) < 1000 ) {
            ?>
          <p style="font-size: 14px; color: red;"><strong><?php 
            echo sprintf( esc_html__( 'Settings not saving? Try disabling "legacy" saving, or increasing your PHP "max_input_vars" in your hosting configuration to 1000 or higher (currently %s).', 'woo-coupon-usage' ), esc_html( ini_get( 'max_input_vars' ) ) );
            ?> <a href="https://couponaffiliates.com/docs/increase-max-input-vars-limit" target="_blank"><?php 
            echo esc_html__( 'Learn More.', 'woo-coupon-usage' );
            ?></a></strong><br/></p>
          <?php 
        }
        ?>

        </span>

      </div>

      <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">

      <p style="display: block; font-size: 15px; margin-top: 0px; margin-bottom: 0px; font-weight: bold;">
      <?php 
        echo esc_html__( 'Need help? Have a suggestion? Found a bug?', 'woo-coupon-usage' );
        ?> <?php 
        if ( wcu_fs()->can_use_premium_code() ) {
            ?><a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage-contact' ) );
            ?>"><?php 
        } else {
            ?><a href="https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0" target="_blank"><?php 
        }
        echo esc_html__( 'Create a support ticket.', 'woo-coupon-usage' );
        ?></a>
      </p>

      <br/>

      <span style="font-size: 11px; color: #9c9c9cff;">
      <?php 
        $pluginname = "woo-coupon-usage";
        // If PRO version, get that version number
        if ( wcu_fs()->can_use_premium_code() ) {
            $pluginname = "woo-coupon-usage-pro";
        }
        $plugin = get_plugin_data( WP_PLUGIN_DIR . '/' . $pluginname . '/woo-coupon-usage.php', false, false );
        $pluginversion = $plugin['Version'];
        ?>
      Thank you for using Coupon Affiliates<?php 
        if ( $pluginversion ) {
            ?> Version <?php 
            echo esc_html( $pluginversion );
        }
        ?>. <a href="https://roadmap.couponaffiliates.com/updates" target="_blank">View Changelog</a>.
      <br/>
      Developed and supported by <a href="https://relywp.com">RelyWP Ltd</a>.
      </span>

      </div>

      <?php 
        // Simple Points and Rewards Ad — show only on PRO, and only if neither variant of the plugin is active
        $spr_plugins = array('simple-points-and-rewards/simple-points-and-rewards.php', 'simple-points-and-rewards-pro/simple-points-and-rewards.php');
        $spr_active = false;
        foreach ( $spr_plugins as $spr_plugin ) {
            if ( is_plugin_active( $spr_plugin ) ) {
                $spr_active = true;
                break;
            }
        }
        if ( wcu_fs()->can_use_premium_code() && !$spr_active ) {
            ?>
      <!-- Simple Points and Rewards Ad -->
      <div style="margin-top: 25px; padding: 10px 14px; background: linear-gradient(145deg, #f8fbff, #ffffff); border: 2px solid #eaeff6; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,200,0.05); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <span style="font-size: 14px; color: #1a2a4a; font-weight: 600; flex-shrink: 0;"><?php 
            echo esc_html__( 'Try our other new plugin!', 'woo-coupon-usage' );
            ?></span>
        &#160;
        <span style="font-size: 14px; color: #c5247fff; font-weight: 600; flex-shrink: 0;"><a style="color: #c5247fff" href="https://relywp.com/plugins/simple-points-rewards-woocommerce/?utm_source=coupon-affiliates&utm_medium=settings-ad&utm_campaign=cross-promote" target="_blank">Simple Points &amp; Rewards</a></span>
        &#160;
        <span style="font-size: 14px; color: #4a6a8a; flex: 1; min-width: 120px;"><?php 
            echo esc_html__( 'Boost customer loyalty with a powerful WooCommerce points and rewards plugin.', 'woo-coupon-usage' );
            ?></span>
        <a href="https://relywp.com/plugins/simple-points-rewards-woocommerce/?utm_source=coupon-affiliates&utm_medium=settings-ad&utm_campaign=cross-promote"
        target="_blank" style="flex-shrink: 0; padding: 5px 12px; background: linear-gradient(145deg, #c5247fff, #a81e6fff); color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; white-space: nowrap;">
          <?php 
            echo esc_html__( 'Get 25% Off + Free Trial', 'woo-coupon-usage' );
            ?> <span class="dashicons dashicons-external" style="font-size: 12px; width: 12px; height: 12px; vertical-align: middle; margin-top: -1px;"></span>
        </a>
      </div>
      <?php 
        }
        ?>

  	</form>
    
    </div> <!-- Close wcu-admin-content -->
    
    <?php 
        // Output PRO upgrade sidebar for both desktop and mobile using a reusable function
        if ( !function_exists( 'wcusage_output_pro_upgrade_sidebar' ) ) {
            function wcusage_output_pro_upgrade_sidebar() {
                ?>
        <a href="https://couponaffiliates.com/docs/setup-guide-free?utm_campaign=plugin&utm_source=dashboard-sidebar&utm_medium=setup-guide" style="text-decoration: none;" target="_blank">
          <div class="wcu-settings-sidebar-box">
            <span style="font-size: 10px; color: green; font-weight: bold;">Need help getting started?</span><br/>
            Setup Guide <span class="dashicons dashicons-external"></span>
          </div>
        </a>
        <script>
        jQuery( document ).ready(function() {
          jQuery('.wcu-settings-sidebar-pro-upgrade-showmore-content').hide();
          jQuery('.wcu-settings-sidebar-pro-upgrade-showmore').click(function(){
            jQuery('.wcu-settings-sidebar-pro-upgrade-showmore-content').show();
          });
        });
        </script>
        <div id="wcu-settings-sidebar-pro-upgrade">
          <span style="font-size: 10px; color: #fff;">Want more advanced features?</span><br/>
          <p style="font-size: 24px; line-height: 30px; margin: 0;">Upgrade to PRO!</p>
          <a href="https://couponaffiliates.com/pricing?utm_campaign=plugin&utm_source=dashboard-sidebar&utm_medium=pro-upgrade" target="_blank" style="text-decoration: none;">
          <p class="wcu-settings-sidebar-pro-upgrade-button">FREE 7 DAY TRIAL <span class="fas fa-arrow-right"></span></p>
          </a>
          <p style="font-size: 10px; line-height: 20px; margin-top: 15px;">After your trial, just $14.99 per month.</p>
          <?php 
                // Black Friday Deal
                $todayDate = strtotime( 'now' );
                $dealDateBegin = strtotime( '15-11-2025' );
                $dealDateEnd = strtotime( '30-11-2025' );
                if ( $todayDate >= $dealDateBegin && $todayDate <= $dealDateEnd ) {
                    $specialsale = true;
                } else {
                    $specialsale = false;
                }
                ?>
          <?php 
                if ( !$specialsale ) {
                    ?>
            <p style="font-size: 12px; color: #3fc13f; font-weight: bold; line-height: 20px; margin-bottom: 15px;">20% discount code: DASH20</p>
          <?php 
                } else {
                    ?>
            <p style="font-size: 14px; color: #3fc13f; font-weight: bold; line-height: 20px; margin-bottom: 15px;">Black Friday - 30% discount!<br/>Use code: BF2025</p>
          <?php 
                }
                ?>
          <a href="#!" onclick="return false;" class="wcu-settings-sidebar-pro-upgrade-showmore">
            What's included? <span class="fas fa-angle-double-down"></span>
          </a>
          <div style="font-size: 12px;" class="wcu-settings-sidebar-pro-upgrade-showmore-content">
            <br><span class="dashicons dashicons-yes-alt"></span> Advanced Admin Reports
            <br><span class="dashicons dashicons-yes-alt"></span> Affiliate Email Reports
            <br><span class="dashicons dashicons-yes-alt"></span> Affiliate Email Newsletters
            <br><span class="dashicons dashicons-yes-alt"></span> Automation Features
            <br><span class="dashicons dashicons-yes-alt"></span> Advanced Registration Features
            <br><span class="dashicons dashicons-yes-alt"></span> Creatives Section
            <br><span class="dashicons dashicons-yes-alt"></span> Dynamic Creatives
            <br><span class="dashicons dashicons-yes-alt"></span> Performance Bonuses
            <br><span class="dashicons dashicons-yes-alt"></span> Multi-Level Affiliates
            <br><span class="dashicons dashicons-yes-alt"></span> Unpaid Commission Tracking
            <br><span class="dashicons dashicons-yes-alt"></span> Commission Payout Requests
            <br><span class="dashicons dashicons-yes-alt"></span> Commission Payout Tracking
            <br><span class="dashicons dashicons-yes-alt"></span> One-Click Stripe Payouts
            <br><span class="dashicons dashicons-yes-alt"></span> One-Click PayPal Payouts
            <br><span class="dashicons dashicons-yes-alt"></span> Wise Bank Transfer Payouts
            <br><span class="dashicons dashicons-yes-alt"></span> Scheduled Payout Requests
            <br><span class="dashicons dashicons-yes-alt"></span> Automatic Payouts
            <br><span class="dashicons dashicons-yes-alt"></span> PDF Statements & Invoices
            <br><span class="dashicons dashicons-yes-alt"></span> Lifetime Commissions
            <br><span class="dashicons dashicons-yes-alt"></span> Affiliate Landing Pages
            <br><span class="dashicons dashicons-yes-alt"></span> Monthly Summary Table
            <br><span class="dashicons dashicons-yes-alt"></span> Commission Line Graphs
            <br><span class="dashicons dashicons-yes-alt"></span> Export to Excel Buttons
            <br><span class="dashicons dashicons-yes-alt"></span> Custom Commission Per Coupon
            <br><span class="dashicons dashicons-yes-alt"></span> Custom Commission Per Product
            <br><span class="dashicons dashicons-yes-alt"></span> Custom Commission Per Role
            <br><span class="dashicons dashicons-yes-alt"></span> Campaigns
            <br><span class="dashicons dashicons-yes-alt"></span> Direct Link Tracking
            <br><span class="dashicons dashicons-yes-alt"></span> Social Sharing
            <br><span class="dashicons dashicons-yes-alt"></span> Short URL Generator
            <br><span class="dashicons dashicons-yes-alt"></span> QR Code Generator
            <br><span class="dashicons dashicons-yes-alt"></span> Custom Dashboard Tabs
            <br><span class="dashicons dashicons-yes-alt"></span> and more great features!
            <br>
            <br><span class="dashicons dashicons-yes-alt"></span> All Future PRO Features
            <br><span class="dashicons dashicons-yes-alt"></span> Priority UK-based Support
            <br><span class="dashicons dashicons-yes-alt"></span> 14 Day Money-Back Guarantee
          </div>
        </div>
        <a href="https://couponaffiliates.com?utm_campaign=plugin&utm_source=dashboard-sidebar&utm_medium=learn-more"
        style="text-decoration: none;" target="_blank">
          <div class="wcu-learn-more-pro">
            Learn more about PRO <span class="dashicons dashicons-external"></span>
          </div>
        </a>
        <!-- Claim LIFETIME Deal Link -->
        <a href="https://couponaffiliates.com/pricing?utm_campaign=plugin&utm_source=dashboard-sidebar&utm_medium=lifetime-deal"
        style="text-decoration: none; margin-top: -10px;" target="_blank">
          <div style="text-align: center; font-size: 12px; font-weight: bold; margin: 10px;">
            Pay once with lifetime deal <span class="dashicons dashicons-external"></span>
          </div>
        </a>
  <center><a href="https://twitter.com/CouponAffs" target="_blank" rel="noopener" class="button">Follow @CouponAffs on X</a></center>
        <button type="button" class="wcu-sidebar-toggle" style="margin:18px auto 0 auto;display:block;padding:7px 18px;border-radius:18px;border:none;background:#e5e7eb;color:#333;font-size:15px;cursor:pointer;">Hide Sidebar &raquo;</button>
        <script>
        jQuery(function($){
          $('.wcu-sidebar-toggle').on('click', function(){
            $('.wcu-settings-sidebar-bottom').remove();
          });
        });
        </script>
        <?php 
            }

        }
        ?>
    <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>
      <!-- Pro Upgrade Sidebar (for free version) -->
      <div class="wcu-settings-sidebar-bottom">
        <?php 
            wcusage_output_pro_upgrade_sidebar();
            ?>
      </div>
    <?php 
        }
        ?>
    

    </div> <!-- Close wcu-admin-layout-container -->

    <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>
      <!-- Pro Upgrade Sidebar (for free version, mobile placement) -->
      <div class="wcu-settings-sidebar-bottom-mobile">
        <?php 
            wcusage_output_pro_upgrade_sidebar();
            ?>
      </div>
    <?php 
        }
        ?>

   <div style="clear: both;"></div>

   <?php 
    }

}
function wcusage_get_plugin_version(  $pluginname  ) {
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $pluginname . '/' . $pluginname . '.php', false, false );
    return $plugin_data['Version'];
}

/**
 * Script for showing section when toggle is on/off
 *
 */
if ( !function_exists( 'wcusage_setting_toggle' ) ) {
    function wcusage_setting_toggle(  $toggleclass, $showclass  ) {
        $script = "<script>\r\n    jQuery( document ).ready(function() {\r\n      if(!jQuery('" . $toggleclass . "').prop('checked')) {\r\n        jQuery('" . $showclass . "').hide();\r\n      }\r\n      jQuery('" . $toggleclass . "').change(function(){\r\n        if(jQuery(this).prop('checked')) {\r\n          jQuery('" . $showclass . "').show();\r\n        } else {\r\n          jQuery('" . $showclass . "').hide();\r\n        }\r\n      });\r\n    });\r\n    </script>";
        echo $script;
        return $script;
    }

}
/**
 * Function for toggle settings option
 *
 */
if ( !function_exists( 'wcusage_setting_toggle_option' ) ) {
    function wcusage_setting_toggle_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
    <p id="<?php 
        echo esc_html( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_html( $margin );
        ?>">
      <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        $checked2 = ( $setting == '1' ? ' checked="checked"' : '' );
        ?>
      <label class="switch">
          <input type="hidden" value="0" data-custom="custom" name="wcusage_options[<?php 
        echo esc_html( $name );
        ?>]" >
          <input type="checkbox" value="1" id="<?php 
        echo esc_html( $name );
        ?>" class="<?php 
        echo esc_html( $name );
        ?>" data-custom="custom" name="wcusage_options[<?php 
        echo esc_html( $name );
        ?>]"
          <?php 
        echo esc_html( $checked2 );
        ?>>
        <span class="slider round">
          <span class="on"><span class="fa-solid fa-check"></span></span>
          <span class="off"></span>
        </span>
      </label>
      <strong style="display: inline-block;"><label for="scales"><?php 
        echo wp_kses_post( $label );
        ?></label></strong>
    </p>
  <?php 
    }

}
/**
 * Function for textarea settings option
 *
 */
if ( !function_exists( 'wcusage_setting_textarea_option' ) ) {
    function wcusage_setting_textarea_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
    <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
      <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
      <?php 
        if ( $label ) {
            ?><strong><?php 
            echo wp_kses_post( $label );
            ?>:</strong><br/><?php 
        }
        ?>
    	<textarea rows="3" cols="30" id="<?php 
        echo esc_attr( $name );
        ?>" style="width: 300px; max-width: 100%;"
    	name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]"><?php 
        echo esc_html( $setting );
        ?></textarea><br/>
    </p>
  <?php 
    }

}
/**
 * Function for select settings option
 *
 */
if ( !function_exists( 'wcusage_setting_select_option' ) ) {
    function wcusage_setting_select_option(
        $name,
        $default,
        $label,
        $margin,
        $items
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
    <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
      <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
      <?php 
        if ( $label ) {
            ?><strong><?php 
            echo wp_kses_post( $label );
            ?>:</strong><br/><?php 
        }
        ?>
      <select id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]">
        <?php 
        foreach ( $items as $option_value => $option_name ) {
            echo '<option value="' . esc_attr( $option_value ) . '" ' . selected( $setting, $option_value, false ) . '>' . esc_html( $option_name ) . '</option>';
        }
        ?>
      </select>
    </p>
  <?php 
    }

}
/**
 * Function for text settings option
 *
 */
if ( !function_exists( 'wcusage_setting_text_option' ) ) {
    function wcusage_setting_text_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
  <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
    <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
    <?php 
        if ( $label ) {
            ?><strong><?php 
            echo wp_kses_post( $label );
            ?></strong><br/><?php 
        }
        ?>
    <input type="text" value="<?php 
        echo esc_attr( $setting );
        ?>" id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]">
  </p>
  <?php 
    }

}
/**
 * Function for hidden settings option
 *
 */
if ( !function_exists( 'wcusage_setting_hidden_option' ) ) {
    function wcusage_setting_hidden_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
    <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
      <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
      <?php 
        if ( $label ) {
            ?><strong><?php 
            echo wp_kses_post( $label );
            ?></strong><br/><?php 
        }
        ?>
      <input type="hidden" value="<?php 
        echo esc_attr( $setting );
        ?>" id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]">
    </p>
  <?php 
    }

}
/**
 * Function for password text settings option
 *
 */
if ( !function_exists( 'wcusage_setting_password_option' ) ) {
    function wcusage_setting_password_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
  <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
    <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
    <strong><?php 
        echo wp_kses_post( $label );
        ?></strong><br/>
    <input type="password" value="<?php 
        echo esc_attr( $setting );
        ?>" id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]">
  </p>
  <?php 
    }

}
/**
 * Function for selecting a user role settings option
 *
 */
if ( !function_exists( 'wcusage_setting_user_role' ) ) {
    function wcusage_setting_user_role(
        $name = "",
        $default = "",
        $label = "",
        $margin = ""
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        if ( !$default ) {
            $default = '';
        }
        if ( !$label ) {
            $label = esc_html__( 'User role:', 'woo-coupon-usage' );
        }
        ?>
  <p id="<?php 
        echo esc_attr( $name );
        ?>_p" style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
    <?php 
        $role = wcusage_get_setting_value( $name, '' );
        ?>
    <input type="hidden" value="0" id="<?php 
        echo esc_attr( $name );
        ?>" data-custom="custom" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]" >
    <strong><label for="scales"><?php 
        echo esc_attr( $label );
        ?></label></strong><br/>
    <select name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]" id="<?php 
        echo esc_attr( $name );
        ?>">
      <?php 
        global $wp_roles;
        $roles = $wp_roles->get_names();
        echo '<option value="">- ' . esc_html__( 'All Roles', 'woo-coupon-usage' ) . ' -</option>';
        foreach ( $roles as $role_value => $role_name ) {
            echo '<option value="' . esc_attr( $role_value ) . '" ' . selected( $role, $role_value, false ) . '>' . esc_html( $role_name ) . '</option>';
        }
        ?>
    </select>
  </p>
  <?php 
    }

}
add_action( 'admin_enqueue_scripts', 'wcu_admin_enqueue_scripts' );
function wcu_admin_enqueue_scripts(  $hook_suffix  ) {
    if ( isset( $_GET['page'] ) && ($_GET['page'] == 'wcusage_setup' || $_GET['page'] == 'wcusage_settings' || $_GET['page'] == 'wcusage_affiliates' || $_GET['page'] == 'wcusage_coupons') ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script(
            'caffs-admin',
            WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            '1.1',
            true
        );
        // Settings quick search
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wcusage_settings' ) {
            wp_enqueue_script(
                'wcusage-settings-search',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'js/settings-search.js',
                array('jquery'),
                '1.0.0',
                true
            );
            // Registrations settings dynamic custom fields handler
            $reg_js_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/registrations-settings.js';
            $reg_js_ver = ( file_exists( $reg_js_path ) ? filemtime( $reg_js_path ) : '1.0.0' );
            wp_enqueue_script(
                'wcusage-registrations-settings',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'js/registrations-settings.js',
                array('jquery'),
                $reg_js_ver,
                true
            );
            $wcusage_options = get_option( 'wcusage_options' );
            $custom_fields_count = ( isset( $wcusage_options['wcusage_field_registration_custom_fields'] ) ? intval( $wcusage_options['wcusage_field_registration_custom_fields'] ) : 5 );
            wp_localize_script( 'wcusage-registrations-settings', 'wcuRegSettings', array(
                'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'wcusage_custom_fields' ),
                'initialCount' => $custom_fields_count,
                'textLabel'    => esc_html__( 'Text:', 'woo-coupon-usage' ),
                'fieldLabel'   => esc_html__( 'Field Label:', 'woo-coupon-usage' ),
            ) );
        }
        // Enable WordPress code editor (CodeMirror) on settings page for custom CSS textarea
        if ( function_exists( 'wp_enqueue_code_editor' ) && isset( $_GET['page'] ) && $_GET['page'] == 'wcusage_settings' ) {
            // Prepare and enqueue editor for CSS; this also ensures necessary scripts/styles are available
            wp_enqueue_code_editor( array(
                'type' => 'text/css',
            ) );
            wp_enqueue_script( 'code-editor' );
            wp_enqueue_style( 'code-editor' );
        }
        // Affiliate users table styles and scripts
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array('wcusage_affiliates', 'wcusage_mla_users'), true ) ) {
            $css_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-affiliate-users-table.css';
            $css_ver = ( file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0' );
            wp_enqueue_style(
                'wcusage-admin-affiliate-users-table',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-affiliate-users-table.css',
                array(),
                $css_ver
            );
            $users_css_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-affiliate-users.css';
            $users_css_ver = ( file_exists( $users_css_path ) ? filemtime( $users_css_path ) : '1.0.0' );
            wp_enqueue_style(
                'wcusage-admin-affiliate-users',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-affiliate-users.css',
                array('wcusage-admin-affiliate-users-table'),
                $users_css_ver
            );
            $js_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/admin-affiliate-users-table.js';
            $js_ver = ( file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0' );
            wp_enqueue_script(
                'wcusage-admin-affiliate-users-table',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-affiliate-users-table.js',
                array('jquery'),
                $js_ver,
                true
            );
        }
    }
    // Always: Registrations page styles (actions column layout/icons)
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wcusage_registrations' ) {
        $style_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-registrations.css';
        $style_ver = ( file_exists( $style_path ) ? filemtime( $style_path ) : '1.0.0' );
        wp_enqueue_style(
            'wcusage-admin-registrations',
            WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-registrations.css',
            array(),
            $style_ver
        );
    }
}

/**
 * Function for color settings option
 *
 */
if ( !function_exists( 'wcusage_setting_color_option' ) ) {
    function wcusage_setting_color_option(
        $name,
        $default,
        $label,
        $margin
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>

  <script>
  jQuery(document).ready(function($){
    var colortimeout;
    jQuery('#<?php 
        echo esc_html( $name );
        ?>').wpColorPicker({
        change: function( event, ui ) {
          clearTimeout(colortimeout);
          colortimeout = setTimeout(function(){
            jQuery('#<?php 
        echo esc_html( $name );
        ?>').trigger("change");
          },500);
        }
    });
    jQuery('.wp-color-picker').click(function() {
      jQuery('.iris-picker').hide();
    });
  });
  </script>

  <p style="margin-left: <?php 
        echo esc_html( $margin );
        ?>">
      <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
      <?php 
        if ( $label ) {
            ?><strong><?php 
            echo wp_kses_post( $label );
            ?>:</strong><br/><?php 
        }
        ?>
      <input type="text" value="<?php 
        echo esc_attr( $setting );
        ?>" data-default-color="<?php 
        echo esc_attr( $default );
        ?>" id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]">
  </p>
  <?php 
    }

}
/**
 * Function for number settings option
 *
 */
if ( !function_exists( 'wcusage_setting_number_option' ) ) {
    function wcusage_setting_number_option(
        $name,
        $default,
        $label,
        $margin,
        $increment = 1
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
  <p style="margin-left: <?php 
        echo esc_attr( $margin );
        ?>">
    <?php 
        $setting = wcusage_get_setting_value( $name, $default );
        ?>
    <strong><?php 
        echo wp_kses_post( $label );
        ?></strong><br/>
    <input type="number" value="<?php 
        echo esc_attr( $setting );
        ?>"
    id="<?php 
        echo esc_attr( $name );
        ?>" name="wcusage_options[<?php 
        echo esc_attr( $name );
        ?>]"
    step="<?php 
        echo esc_attr( $increment );
        ?>">
    <br/>
  </p>
  <?php 
    }

}
/**
 * Function for textarea settings option
 *
 */
if ( !function_exists( 'wcusage_setting_tinymce_option' ) ) {
    function wcusage_setting_tinymce_option(
        $name,
        $default,
        $label,
        $margin,
        $size = "150"
    ) {
        $options = get_option( 'wcusage_options' );
        wcusage_setting_option_set_default( $options, $name, $default );
        ?>
    <strong style="margin-bottom: 5px; display: block;"><?php 
        echo wp_kses_post( $label );
        ?></strong>
    <?php 
        $setting = html_entity_decode( wcusage_get_setting_value( $name, $default ) );
        $settings1 = array(
            'wpautop'       => true,
            'media_buttons' => true,
            'textarea_name' => 'wcusage_options[' . $name . ']',
            'textarea_rows' => 5,
            'editor_class'  => $name,
            'tinymce'       => true,
            'editor_height' => $size,
        );
        wcusage_tinymce_ajax_script( $name );
        wp_editor( $setting, $name, $settings1 );
    }

}
/**
 * Saves the current default option value if not already set.
 *
 */
if ( !function_exists( 'wcusage_setting_option_set_default' ) ) {
    function wcusage_setting_option_set_default(  $options, $name, $default  ) {
        // If $default is empty, do nothing
        if ( $default === '' || $default === null ) {
            return;
        }
        // Optionally collect defaults into a registry (only during setup wizard
        // or when explicitly enabled via a flag).
        global $wcusage_all_default_settings, $wcusage_collect_defaults_enabled;
        $collect_defaults = !empty( $wcusage_collect_defaults_enabled );
        if ( !$collect_defaults ) {
            // Allow automatic collection when on the setup wizard page in admin.
            $on_setup_page = function_exists( 'is_admin' ) && is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_setup';
            $collect_defaults = $on_setup_page;
        }
        if ( $collect_defaults ) {
            wcusage_register_default_setting( $name, $default );
        }
    }

}
/**
 * Get Value of Setting with Default
 *
 */
if ( !function_exists( 'wcusage_get_setting_value' ) ) {
    function wcusage_get_setting_value(  $theoption, $thedefault  ) {
        $options = wcusage_get_options();
        if ( array_key_exists( $theoption, $options ) ) {
            $wcusage_field = $options[$theoption];
        } else {
            $wcusage_field = $thedefault;
        }
        if ( !is_array( $wcusage_field ) ) {
            $wcusage_field = wp_kses_post( $wcusage_field );
        }
        return $wcusage_field;
    }

}
/**
 * Script for TinyMCE editor to auto update via ajax
 *
 */
if ( !function_exists( 'wcusage_tinymce_ajax_script' ) ) {
    function wcusage_tinymce_ajax_script(  $id  ) {
        ?>
  <script>
  function wcusettingsdelay(callback, ms) {
    var timer = 0;
    return function() {
      var context = this, args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        callback.apply(context, args);
      }, ms || 0);
    };
  }
  jQuery( document ).ready(function() {
      tinymce.editors['<?php 
        echo esc_html( $id );
        ?>'].onChange.add(wcusettingsdelay(function (ed, e) {
          wcu_ajax_update_the_options('<?php 
        echo esc_html( $id );
        ?>', 'data-id', 'wcu-update-text', 1);
      }, 1500));
  });
  </script>
  <?php 
    }

}
/**
 * Ensure (and return) all settings defaults.
 *
 * This triggers every settings field callback once in an output buffer so that
 * any wcusage_setting_option_set_default() calls inside them populate missing
 * defaults without needing to visit the settings UI manually.
 *
 * Returns the full wcusage_options array after defaults have been applied.
 */
if ( !function_exists( 'wcusage_get_all_default_settings' ) ) {
    function wcusage_get_all_default_settings() {
        // Prevent running multiple times needlessly in a single request.
        static $ran = false;
        global $wcusage_all_default_settings, $wcusage_collect_defaults_enabled;
        if ( !$ran ) {
            $ran = true;
            // Enable collection while we invoke field callbacks, and restore after.
            $prev_collect_flag = $wcusage_collect_defaults_enabled;
            $wcusage_collect_defaults_enabled = true;
            // List of callbacks registered via add_settings_field in this file.
            $callbacks = array(
                'wcusage_field_cb',
                'wcusage_field_cb_commission',
                'wcusage_field_cb_fraud',
                'wcusage_field_cb_urls',
                'wcusage_field_cb_notifications',
                'wcusage_field_cb_currency',
                'wcusage_field_cb_payouts',
                'wcusage_field_cb_invoices',
                // premium only – existence checked below
                'wcusage_field_cb_reports',
                'wcusage_field_cb_custom_tabs',
                'wcusage_field_cb_registration',
                'wcusage_field_cb_subscriptions',
                'wcusage_field_cb_creatives',
                // premium
                'wcusage_field_cb_newsletter',
                // premium
                'wcusage_field_cb_bonuses',
                // premium
                'wcusage_field_cb_mla',
                // premium
                'wcusage_field_cb_design',
                'wcusage_field_cb_widget',
                'wcusage_field_cb_debug',
                'wcusage_field_cb_help',
                'wcusage_field_cb_pro_details',
            );
            foreach ( $callbacks as $cb ) {
                if ( function_exists( $cb ) ) {
                    ob_start();
                    try {
                        call_user_func( $cb, array() );
                        // side effects populate registry
                    } catch ( \Throwable $e ) {
                        // ignore
                    }
                    ob_end_clean();
                }
            }
            // Restore previous collection flag state.
            $wcusage_collect_defaults_enabled = $prev_collect_flag;
        }
        return wcusage_apply_registered_defaults();
    }

}
/**
 * Function to display a settings tab
 *
 */
if ( !function_exists( 'wcusage_admin_settings_tab_button' ) ) {
    function wcusage_admin_settings_tab_button(
        $id,
        $name,
        $icon,
        $pro,
        $css
    ) {
        ?>
  <a href="javascript:void(0);" class="nav-tab" <?php 
        if ( $css ) {
            echo 'style="' . esc_attr( $css ) . '"';
        }
        ?> id="<?php 
        echo esc_attr( $id );
        ?>" <?php 
        if ( (!wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium()) && $pro ) {
            ?>style="opacity: 0.4;"<?php 
        }
        ?>>
    <span class="<?php 
        echo esc_attr( $icon );
        ?> settings-tab-icon"></span>
    <?php 
        echo esc_html( $name );
        if ( !wcu_fs()->can_use_premium_code() && $pro ) {
            ?><span class="wcu-settings-pro-icon">Pro</span><?php 
        }
        ?>
  </a>
  <?php 
    }

}
/**
 * Function to display a sidebar menu item
 *
 */
if ( !function_exists( 'wcusage_admin_settings_sidebar_button' ) ) {
    function wcusage_admin_settings_sidebar_button(
        $id,
        $name,
        $icon,
        $pro,
        $css
    ) {
        ?>
  <a href="javascript:void(0);" class="wcu-sidebar-link" <?php 
        if ( $css ) {
            echo 'style="' . esc_attr( $css ) . '"';
        }
        ?> id="<?php 
        echo esc_attr( $id );
        ?>" <?php 
        if ( (!wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium()) && $pro ) {
            ?>style="opacity: 0.4;"<?php 
        }
        ?>>
    <span class="<?php 
        echo esc_attr( $icon );
        ?> wcu-sidebar-icon"></span>
    <span class="wcu-sidebar-text"><?php 
        echo esc_html( $name );
        ?></span>
    <?php 
        if ( !wcu_fs()->can_use_premium_code() && $pro ) {
            ?><span class="wcu-settings-pro-icon">Pro</span><?php 
        }
        ?>
  </a>
  <?php 
    }

}
/**
 * Function for onclick script event on click settings tab
 *
 */
if ( !function_exists( 'wcusage_admin_settings_tab_click' ) ) {
    function wcusage_admin_settings_tab_click(  $tab, $class, $hide  ) {
        if ( $hide == 1 ) {
            echo 'jQuery( "' . $class . '" ).hide();';
        }
        echo '
    jQuery( "' . $tab . '" ).click(function() {
    	jQuery( ".wcusage_row" ).hide();
    	jQuery( ".plugin-settings .submit" ).show();
    	jQuery( "' . $class . '" ).show();
    });
    ';
    }

}
/**
 * Creates the toggle for the settings page tabs.
 *
 */
if ( !function_exists( 'wcusage_js_settings_tab_toggle' ) ) {
    function wcusage_js_settings_tab_toggle(  $class1, $class2, $tab  ) {
        $rand = rand( 1000, 9999 );
        ?>
  <script>
  jQuery( document ).ready(function() {
    var class1 = "<?php 
        echo esc_html( $class1 );
        ?>:not(.pro-setting-toggle)";
    <?php 
        if ( $class2 ) {
            ?>
    var class2 = "<?php 
            echo esc_html( $class2 );
            ?>:not(.pro-setting-toggle)";
    <?php 
        }
        ?>
    var tabid = "<?php 
        echo esc_html( $tab );
        ?>";

    function wcuUpdateTabVisibility<?php 
        echo esc_html( $rand );
        ?>() {
      var enabled = false;
      // Check all matching toggles (including dynamically added ones)
      jQuery(class1).each(function() {
        if (jQuery(this).is(':checked')) {
          enabled = true;
        }
      });
      <?php 
        if ( $class2 ) {
            ?>
      jQuery(class2).each(function() {
        if (jQuery(this).is(':checked')) {
          enabled = true;
        }
      });
      <?php 
        }
        ?>
      if (enabled) {
        jQuery(tabid).show();
      } else {
        jQuery(tabid).hide();
      }
    }

    wcuUpdateTabVisibility<?php 
        echo esc_html( $rand );
        ?>();
    jQuery(document).on('change', class1<?php 
        if ( $class2 ) {
            ?> + ',' + class2<?php 
        }
        ?>, wcuUpdateTabVisibility<?php 
        echo esc_html( $rand );
        ?>);
  });
  </script>
  <?php 
    }

}
/**
 * Function to create show hide toggle
 *
 */
if ( !function_exists( 'wcu_admin_settings_showhide_toggle' ) ) {
    function wcu_admin_settings_showhide_toggle(
        $buttonid,
        $sectionid,
        $show,
        $hide
    ) {
        ?>
    <script>
    jQuery(document).ready(function() {
      jQuery('#<?php 
        echo esc_html( $buttonid );
        ?>').click(function() {
        jQuery( "#<?php 
        echo esc_html( $sectionid );
        ?>" ).toggle();
        if(jQuery('#<?php 
        echo esc_html( $sectionid );
        ?>:visible').length == 0) {
          jQuery( "#<?php 
        echo esc_html( $buttonid );
        ?>" ).html("<?php 
        echo esc_html( $show );
        ?> <span class='fa-solid fa-arrow-down'></span>");
        } else {
          jQuery( "#<?php 
        echo esc_html( $buttonid );
        ?>" ).html("<?php 
        echo esc_html( $hide );
        ?> <span class='fa-solid fa-arrow-up'></span>");
        }
      });
    });
    </script>
  <?php 
    }

}
/*
* Admin FAQ Toggle
*/
function wcusage_admin_faq_toggle(  $id, $class, $title  ) {
    ?>
  <?php 
    wcu_admin_settings_showhide_toggle(
        $id,
        $class,
        "Show",
        "Hide"
    );
    ?>
  <p style="font-weight: bold;"><span class="dashicons dashicons-info" style="margin-top: 5px;"></span>
  <?php 
    echo esc_html( $title );
    ?>
  <button class="wcu-showhide-button" type="button" id="<?php 
    echo esc_attr( $id );
    ?>">
  <?php 
    echo esc_html__( 'Show', 'woo-coupon-usage' );
    ?> <span class='fa-solid fa-arrow-down'></span>
  </button></p>
  <?php 
}

/**
 * Function to show custom tooltip
 *
 */
if ( !function_exists( 'wcusage_admin_tooltip' ) ) {
    function wcusage_admin_tooltip(  $text, $icon = 'dashicons-editor-help'  ) {
        return "<span class='wcusage-users-affiliate-column' style='margin-left: 5px; display: inline-block;'>\r\n    <span class='custom-tooltip'><span class='dashicons " . esc_attr( $icon ) . "' style='color: green;'></span>\r\n        <span class='tooltip-content' style='white-space: normal;'>\r\n        <span style='font-size: 12px;'>" . $text . "</span>\r\n        </span>\r\n    </span>\r\n    </span>";
    }

}
if ( !function_exists( 'wcusage_admin_vimeo_embed' ) ) {
    function wcusage_admin_vimeo_embed(  $embed_url  ) {
        $embed_url = esc_url( $embed_url );
        $html = '<div style="max-width: 720px;">' . '<div style="padding:56.25% 0 0 0;position:relative;">' . '<iframe src="' . $embed_url . '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen="allowfullscreen" frameborder="0" style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>' . '</div>' . '</div>';
        $allowed_html = array(
            'div'    => array(
                'style' => true,
            ),
            'iframe' => array(
                'src'             => true,
                'allow'           => true,
                'allowfullscreen' => true,
                'frameborder'     => true,
                'style'           => true,
            ),
        );
        return wp_kses( $html, $allowed_html );
    }

}