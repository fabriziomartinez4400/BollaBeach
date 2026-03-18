<?php

/**
* Plugin Name: Coupon Affiliates for WooCommerce
* Plugin URI: https://couponaffiliates.com
* Description: The most powerful affiliate plugin for WooCommerce. Track commission, generate referral URLs, assign affiliate coupons, and display detailed stats.
* Version: 7.5.1
* Author: Elliot Sowersby, RelyWP
* Author URI: https://couponaffiliates.com/
* License: GPLv3
* Text Domain: woo-coupon-usage
* Domain Path: /languages
* Requires Plugins: woocommerce
*
* WC requires at least: 3.7
* WC tested up to: 10.4
*
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Define plugin version constant
if ( !defined( 'WCUSAGE_VERSION' ) ) {
    define( 'WCUSAGE_VERSION', '7.2.3' );
}
if ( function_exists( 'wcu_fs' ) ) {
    wcu_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'wcu_fs' ) ) {
        // ***** SDK Integration *****
        function wcu_fs() {
            global $wcu_fs;
            if ( !isset( $wcu_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_2732_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_2732_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wcu_fs = fs_dynamic_init( array(
                    'id'               => '2732',
                    'slug'             => 'woo-coupon-usage',
                    'premium_slug'     => 'woo-coupon-usage-pro',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_a8d9ceeaec08247afd31dbb3e26b3',
                    'is_premium'       => false,
                    'premium_suffix'   => '(PRO)',
                    'has_addons'       => true,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'trial'            => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'             => array(
                        'slug'       => 'wcusage',
                        'first-path' => 'admin.php?page=wcusage_setup',
                        'support'    => true,
                        'contact'    => true,
                        'pricing'    => true,
                        'addons'     => false,
                    ),
                    'is_live'          => true,
                ) );
            }
            return $wcu_fs;
        }

        // Init Freemius.
        wcu_fs();
        // Signal that SDK was initiated.
        do_action( 'wcu_fs_loaded' );
        function wcu_fs_settings_url() {
            // Open the Freemius connect screen on the top-level page.
            return admin_url( 'admin.php?page=wcusage' );
        }

        function wcu_fs_settings_url2() {
            $wcusage_setup_complete = get_option( 'wcusage_setup_complete' );
            if ( !$wcusage_setup_complete ) {
                return admin_url( 'admin.php?page=wcusage_setup' );
            } else {
                return admin_url( 'admin.php?page=wcusage_setup&step=6' );
            }
        }

        wcu_fs()->add_filter( 'connect_url', 'wcu_fs_settings_url' );
        wcu_fs()->add_filter( 'after_skip_url', 'wcu_fs_settings_url2' );
        wcu_fs()->add_filter( 'after_connect_url', 'wcu_fs_settings_url2' );
        wcu_fs()->add_filter( 'after_pending_connect_url', 'wcu_fs_settings_url2' );
        /*** Include Plugin Icon ***/
        function wcusage_fs_custom_icon() {
            return dirname( __FILE__ ) . '/images/logo-icon.png';
        }

        wcu_fs()->add_filter( 'plugin_icon', 'wcusage_fs_custom_icon' );
        // ***** END SDK Integration *****
    }
    // Get Plugin Base URL
    $url = plugin_dir_url( __FILE__ );
    define( 'WCUSAGE_UNIQUE_PLUGIN_URL', $url );
    // Get Plugin Base PATH
    $url_path = plugin_dir_path( __FILE__ );
    define( 'WCUSAGE_UNIQUE_PLUGIN_PATH', $url_path );
    // Scripts
    function wcusage_include_scripts_basic() {
        // Return if not WooCommerce
        if ( !function_exists( 'is_woocommerce' ) ) {
            return;
        }
        global $post;
        // Determine whether this page contains a shortcode
        $shortcode_found = false;
        if ( $post ) {
            $post_id = get_the_ID();
            $dashboard_page = wcusage_get_setting_value( 'wcusage_dashboard_page', '' );
            $mla_dashboard_page = wcusage_get_setting_value( 'wcusage_mla_dashboard_page', '' );
            if ( $post_id == $dashboard_page || $post_id == $mla_dashboard_page ) {
                $shortcode_found = true;
            }
            if ( has_shortcode( $post->post_content, 'couponusage' ) || has_shortcode( $post->post_content, 'couponaffiliates' ) || has_shortcode( $post->post_content, 'couponaffiliates-creatives' ) || has_shortcode( $post->post_content, 'couponaffiliates-leaderboard' ) || has_shortcode( $post->post_content, 'couponaffiliates-mla' ) ) {
                $shortcode_found = true;
            }
        }
        $wcusage_field_account_tab_create = wcusage_get_setting_value( 'wcusage_field_account_tab_create', 0 );
        if ( $shortcode_found || is_account_page() && $wcusage_field_account_tab_create ) {
            if ( !is_admin() ) {
                // Ensure core jQuery is available and enqueued (use WordPress bundled version only)
                if ( !wp_script_is( 'jquery', 'enqueued' ) ) {
                    wp_enqueue_script( 'jquery' );
                }
                // Enqueue custom settings script
                wp_enqueue_script(
                    'wcusage-tab-settings',
                    plugin_dir_url( __FILE__ ) . 'js/tab-settings.js',
                    array('jquery'),
                    '1.0.3',
                    true
                );
                // Enqueue custom settings styles
                wp_enqueue_style(
                    'wcusage-tab-settings',
                    plugin_dir_url( __FILE__ ) . 'css/tab-settings.css',
                    array(),
                    '7.0.0'
                );
                // Localize script with necessary data
                wp_localize_script( 'wcusage-tab-settings', 'wcusage_ajax', array(
                    'ajax_url'    => admin_url( 'admin-ajax.php' ),
                    'saving_text' => __( 'Saving...', 'woo-coupon-usage' ),
                    'save_text'   => __( 'Save changes', 'woo-coupon-usage' ),
                ) );
                // Dark Mode - Enqueue styles and scripts if enabled (not for portal)
                $wcusage_field_portal_enable = wcusage_get_setting_value( 'wcusage_field_portal_enable', '0' );
                $wcusage_field_dark_mode_enable = wcusage_get_setting_value( 'wcusage_field_dark_mode_enable', 0 );
                if ( $wcusage_field_dark_mode_enable && !$wcusage_field_portal_enable ) {
                    // Enqueue dark mode CSS
                    wp_enqueue_style(
                        'wcusage-dark-mode',
                        plugin_dir_url( __FILE__ ) . 'css/dark-mode.css',
                        array(),
                        '1.0.0'
                    );
                    // Enqueue dark mode JS
                    wp_enqueue_script(
                        'wcusage-dark-mode',
                        plugin_dir_url( __FILE__ ) . 'js/dark-mode.js',
                        array('jquery'),
                        '1.0.0',
                        true
                    );
                    // Localize dark mode script with settings
                    $wcusage_field_dark_mode_default = wcusage_get_setting_value( 'wcusage_field_dark_mode_default', 0 );
                    wp_localize_script( 'wcusage-dark-mode', 'wcusage_dark_mode', array(
                        'enabled'         => '1',
                        'default'         => ( $wcusage_field_dark_mode_default ? '1' : '0' ),
                        'text_dark_mode'  => __( 'Dark Mode', 'woo-coupon-usage' ),
                        'text_light_mode' => __( 'Light Mode', 'woo-coupon-usage' ),
                    ) );
                }
            }
            // Custom JS Only Loads on Page
            wp_register_script(
                'woo-coupon-usage',
                plugins_url( '/js/woo-coupon-usage.js', __FILE__ ),
                array('jquery'),
                '5.8.0',
                false
            );
            wp_enqueue_script( 'woo-coupon-usage' );
        }
        $wcusage_urls_prefix = wcusage_get_setting_value( 'wcusage_field_urls_prefix', 'coupon' );
        $wcusage_urls_prefix_mla = wcusage_get_setting_value( 'wcusage_urls_prefix_mla', 'mla' );
        if ( isset( $_GET[$wcusage_urls_prefix] ) || isset( $_GET[$wcusage_urls_prefix_mla] ) ) {
            wp_enqueue_script(
                "jquery-cookie",
                WCUSAGE_UNIQUE_PLUGIN_URL . 'js/jquery.cookie.js',
                array(),
                '0'
            );
        }
    }

    add_action( 'wp_enqueue_scripts', 'wcusage_include_scripts_basic' );
    /*** Localization ***/
    add_action( 'init', 'wcusage_load_textdomain' );
    function wcusage_load_textdomain() {
        load_plugin_textdomain( 'woo-coupon-usage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /*** Include Styles ***/
    function wcusage_include_plugin_css() {
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style(
            'woo-coupon-usage-style',
            $plugin_url . 'css/style.css',
            array(),
            '7.0.0'
        );
    }

    add_action( 'wp_enqueue_scripts', 'wcusage_include_plugin_css' );
    /*** Include Admin Styles ***/
    function wcusage_include_admin_styles() {
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style(
            'woo-coupon-usage-admin-style',
            $plugin_url . 'css/admin-style.css',
            array(),
            '7.0.0'
        );
    }

    add_action( 'admin_enqueue_scripts', 'wcusage_include_admin_styles' );
    /*** Enqueue Font Awesome on relevant admin pages (including Statements/Bonuses CPTs) ***/
    function wcusage_enqueue_font_awesome_admin(  $hook  ) {
        $should_enqueue = false;
        // Load on specific plugin admin pages
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage-account' ) {
            $should_enqueue = true;
        }
        // Also load on the Statements and Bonuses custom post type admin screens (list, add, edit)
        if ( !$should_enqueue && function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && isset( $screen->post_type ) && ('wcu-statements' === $screen->post_type || 'wcu-bonuses' === $screen->post_type || 'wcu-creatives' === $screen->post_type) ) {
                $should_enqueue = true;
            }
        }
        if ( $should_enqueue ) {
            wp_enqueue_style(
                'wcusage-font-awesome',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'fonts/font-awesome/css/all.min.css',
                array(),
                null
            );
        }
    }

    add_action( 'admin_enqueue_scripts', 'wcusage_enqueue_font_awesome_admin' );
    /**
     * Enqueue custom JavaScript for confirming coupon title change.
     */
    function enqueue_coupon_title_change_confirmation() {
        global $post;
        if ( $post && 'shop_coupon' === $post->post_type ) {
            // If coupon meta wcu_select_coupon_user exists
            $coupon_user = get_post_meta( $post->ID, 'wcu_select_coupon_user', true );
            if ( !$coupon_user ) {
                return;
            }
            // Enqueue the script only on coupon edit page
            wp_enqueue_script(
                'coupon-title-change-confirmation',
                plugin_dir_url( __FILE__ ) . 'js/coupon-title-change-confirmation.js',
                // Make sure to adjust the path if needed.
                array('jquery'),
                // Add jQuery as a dependency
                false,
                true
            );
            // Pass the current coupon title to the JavaScript.
            wp_localize_script( 'coupon-title-change-confirmation', 'couponTitleData', array(
                'currentTitle'   => esc_js( $post->post_title ),
                'warningMessage' => __( 'Changing the coupon name may cause the affiliate dashboard statistics to be reset. Are you sure you want to proceed?', 'woo-coupon-usage' ),
            ) );
        }
    }

    add_action( 'admin_enqueue_scripts', 'enqueue_coupon_title_change_confirmation' );
    /*** Include Files ***/
    // Helper: Detect if site likely uses an SMTP plugin for outgoing email.
    if ( !function_exists( 'wcusage_is_smtp_configured' ) ) {
        function wcusage_is_smtp_configured() {
            if ( !function_exists( 'is_plugin_active' ) ) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $smtp_plugins = array(
                'wp-mail-smtp/wp_mail_smtp.php',
                'wp-mail-smtp-pro/wp_mail_smtp.php',
                'post-smtp/postman-smtp.php',
                'post-smtp/post-smtp.php',
                'easy-wp-smtp/easy-wp-smtp.php',
                'fluent-smtp/fluent-smtp.php',
                'mailgun/mailgun.php',
                'sendgrid-email-delivery-simplified/wpsendgrid.php',
                'sendinblue/sendinblue.php',
                'smtp-mailer/main.php',
                'gmail-smtp/smtp.php',
                'amazon-ses-smtp/wp-aws-ses.php'
            );
            foreach ( $smtp_plugins as $plug ) {
                if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $plug ) ) {
                    return true;
                }
            }
            // Heuristic: additional phpmailer_init hooks often indicate SMTP plugin.
            if ( has_action( 'phpmailer_init' ) ) {
                global $wp_filter;
                $hooks = ( isset( $wp_filter['phpmailer_init'] ) ? $wp_filter['phpmailer_init'] : null );
                if ( $hooks ) {
                    $count = 0;
                    if ( is_object( $hooks ) && property_exists( $hooks, 'callbacks' ) ) {
                        $count = count( $hooks->callbacks );
                    } elseif ( is_array( $hooks ) ) {
                        $count = count( $hooks );
                    }
                    if ( $count > 1 ) {
                        return true;
                    }
                    // >1 implies something else hooked besides core.
                }
            }
            return false;
        }

    }
    // Admin Settings
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/admin-options.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/admin-options-update.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/admin-setup.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-commission.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-currency.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-debug.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-design.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-fraud.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-general.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-help.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-notifications.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-reports.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-subscriptions.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-tabs.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-urls.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-widget.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-payouts.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/settings/options-registrations.php';
    // Admin Affiliate View data/ajax (for AJAX handlers used on admin-ajax.php)
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-view-affiliate-data.php';
    // Admin Files
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-dashboard.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-notification-bell.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-page.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-tools.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-list.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-menu.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-pro-details.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-getting-started.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-orders-list.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-orders-box.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-url-clicks.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-affiliate-users.php';
    // Classes
    include plugin_dir_path( __FILE__ ) . 'inc/admin/class-clicks-list-table.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/class-orders-filter-coupons.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/class-referrals-table.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/class-coupon-users-table.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/class-coupons-table.php';
    // Admin Tools
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-coupons.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-assign-orders.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-edit-products.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-edit-coupons.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-import-export.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin/tools/admin-bulk-product-rates.php';
    // Activity Log
    $enable_activity_log = wcusage_get_setting_value( 'wcusage_enable_activity_log', '1' );
    if ( $enable_activity_log ) {
        include plugin_dir_path( __FILE__ ) . 'inc/admin/admin-activity.php';
        include plugin_dir_path( __FILE__ ) . 'inc/admin/class-activity-list-table.php';
    }
    // Main Functions
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-ajax.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-update-notice.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-shortcode.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-shortcode-extra.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-shortcode-page.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-dashboard.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-custom-styles.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-general.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-coupon-orders.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-coupon-info.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-coupon-apply.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-commission-message.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-urls.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-url-clicks.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-calculate-order.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-percentage-change.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-uninstall.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-refund.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-all-time.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-new-order.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-user-coupons.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-activity.php';
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-helper.php';
    // Widget - New organized structure
    $wcusage_field_floating_widget_enable = wcusage_get_setting_value( 'wcusage_field_floating_widget_enable', '0' );
    include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-settings.php';
    if ( $wcusage_field_floating_widget_enable ) {
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-core.php';
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-conditions.php';
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-ajax.php';
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-tabs.php';
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-helpers.php';
        include plugin_dir_path( __FILE__ ) . 'inc/widget/widget-content.php';
    }
    // Portal
    $wcusage_field_portal_enable = wcusage_get_setting_value( 'wcusage_field_portal_enable', '0' );
    if ( $wcusage_field_portal_enable ) {
        include plugin_dir_path( __FILE__ ) . 'inc/portal/affiliate-portal.php';
    }
    // API
    include plugin_dir_path( __FILE__ ) . 'inc/api/coupon-info.php';
    include plugin_dir_path( __FILE__ ) . 'inc/api/users-coupons.php';
    include plugin_dir_path( __FILE__ ) . 'inc/api/request-payout.php';
    // WC Account Tab
    $wcusage_field_account_tab = wcusage_get_setting_value( 'wcusage_field_account_tab', 0 );
    if ( $wcusage_field_account_tab ) {
        include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-wc-tab.php';
    }
    // Subscriptions
    include plugin_dir_path( __FILE__ ) . 'inc/functions/functions-subscriptions.php';
    // Tabs Files
    include plugin_dir_path( __FILE__ ) . 'inc/dashboard/tab-statistics.php';
    include plugin_dir_path( __FILE__ ) . 'inc/dashboard/tab-latest-orders.php';
    include plugin_dir_path( __FILE__ ) . 'inc/dashboard/tab-referral-url.php';
    include plugin_dir_path( __FILE__ ) . 'inc/dashboard/tab-settings.php';
    // Emails
    include plugin_dir_path( __FILE__ ) . 'inc/emails/new-order-email.php';
    $wcusage_cancel_email_enable = wcusage_get_setting_value( 'wcusage_field_cancel_email_enable', '0' );
    if ( $wcusage_cancel_email_enable ) {
        include plugin_dir_path( __FILE__ ) . 'inc/emails/cancelled-email.php';
    }
    // Admin Reports
    include plugin_dir_path( __FILE__ ) . 'inc/admin-reports/admin-reports.php';
    include plugin_dir_path( __FILE__ ) . 'inc/admin-reports/ajax-admin-reports.php';
    // Register
    include plugin_dir_path( __FILE__ ) . 'inc/emails/registration-emails.php';
    include plugin_dir_path( __FILE__ ) . 'inc/registration/registration-admin.php';
    include plugin_dir_path( __FILE__ ) . 'inc/registration/registration-form.php';
    include plugin_dir_path( __FILE__ ) . 'inc/registration/functions-registration.php';
    include plugin_dir_path( __FILE__ ) . 'inc/registration/registration-landing-page.php';
    include plugin_dir_path( __FILE__ ) . 'inc/registration/registration-ajax.php';
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    if ( $wcusage_field_registration_enable ) {
        // Classes
        include plugin_dir_path( __FILE__ ) . 'inc/registration/class-registrations-list-table.php';
    }
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcusage_add_action_links' );
    function wcusage_add_action_links(  $links  ) {
        $support_link = "https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0";
        $mylinks = array('<a href="' . esc_url( admin_url( '/admin.php?page=wcusage_settings' ) ) . '">Settings</a>', '<a href="' . $support_link . '">Support</a>');
        return array_merge( $links, $mylinks );
    }

    function wcusage_fs_is_submenu_visible(  $is_visible, $submenu_id  ) {
        $pro = wcu_fs()->can_use_premium_code();
        $trial = wcu_fs()->is_trial();
        if ( $submenu_id == "contact" ) {
            $is_visible = ( $pro ? true : false );
        }
        if ( $submenu_id == "pricing" ) {
            $is_visible = ( $pro ? false : true );
            if ( $trial ) {
                $is_visible = true;
            }
        }
        if ( $submenu_id == "support" ) {
            $is_visible = ( $pro ? false : true );
        }
        return $is_visible;
    }

    wcu_fs()->add_filter(
        'is_submenu_visible',
        'wcusage_fs_is_submenu_visible',
        10,
        2
    );
    /**
     * Hook the activation function
     */
    if ( !function_exists( 'wcusage_plugin_activation_redirect' ) ) {
        register_activation_hook( __FILE__, 'wcusage_plugin_activation_redirect' );
        function wcusage_plugin_activation_redirect() {
            // Set a transient to trigger the redirect
            set_transient( 'wcusage_activation_redirect', true, 30 );
        }

    }
    /**
     * Hook into admin_init to perform the redirect
     */
    add_action( 'admin_init', 'wcusage_do_activation_redirect' );
    function wcusage_do_activation_redirect() {
        $wcusage_setup_complete = get_option( 'wcusage_setup_complete' );
        // Check if the transient exists
        if ( get_transient( 'wcusage_activation_redirect' ) && !$wcusage_setup_complete ) {
            // Delete the transient so the redirect only happens once
            delete_transient( 'wcusage_activation_redirect' );
            // If Freemius opt-in is still pending, show the Freemius connect screen first.
            if ( function_exists( 'wcu_fs' ) ) {
                $fs = wcu_fs();
                $optin_pending = false;
                if ( method_exists( $fs, 'is_registered' ) && method_exists( $fs, 'is_anonymous' ) ) {
                    // Treat as pending if not registered and not explicitly anonymous (opted-out).
                    $optin_pending = !$fs->is_registered( true ) && !$fs->is_anonymous();
                }
                if ( $optin_pending ) {
                    wp_safe_redirect( admin_url( 'admin.php?page=wcusage_setup' ) );
                    exit;
                }
            }
            // Otherwise proceed to setup wizard as before.
            wp_safe_redirect( admin_url( 'admin.php?page=wcusage_setup' ) );
            exit;
        }
    }

    /**
     * If Freemius opt-in is pending, redirect Settings/Setup pages
     * to the top-level page which will display the connect screen.
     */
    add_action( 'admin_init', function () {
        if ( !is_admin() ) {
            return;
        }
        if ( !function_exists( 'wcu_fs' ) ) {
            return;
        }
        // Only gate our plugin pages.
        $page = ( isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '' );
        if ( !in_array( $page, array('wcusage_settings', 'wcusage_setup'), true ) ) {
            return;
        }
        $fs = wcu_fs();
        $optin_pending = false;
        if ( method_exists( $fs, 'is_registered' ) && method_exists( $fs, 'is_anonymous' ) && method_exists( $fs, 'is_activation_mode' ) ) {
            $optin_pending = !$fs->is_registered( true ) && !$fs->is_anonymous() && $fs->is_activation_mode();
        }
        if ( $optin_pending ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wcusage' ) );
            exit;
        }
    }, 3 );
}
/**
 * Compatible with WooCommerce HP
 *
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );