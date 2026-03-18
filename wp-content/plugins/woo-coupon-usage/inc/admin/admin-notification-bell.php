<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * AJAX handler for admin notification bell
 */
add_action( 'wp_ajax_wcusage_admin_bell_data', 'wcusage_admin_bell_data_ajax' );
function wcusage_admin_bell_data_ajax() {
    check_ajax_referer( 'wcusage_admin_bell', 'nonce' );
    // Check if this is a bell click (update date) or just a fetch
    $update_date = isset( $_POST['update_date'] ) && $_POST['update_date'] == '1';
    // Use static cache for this request
    static $cached_data = null;
    if ( $cached_data === null ) {
        // Get pending counts with transient caching (1 minute)
        $cache_key = 'wcusage_admin_bell_counts';
        $cached_counts = get_transient( $cache_key );
        if ( $cached_counts === false ) {
            $pending_registrations = wcusage_get_pending_registrations_count();
            $pending_payouts = 0;
            if ( wcu_fs()->can_use_premium_code() ) {
                $pending_payouts = wcusage_get_pending_payouts_count();
            }
            $pending_direct_links = wcusage_get_pending_direct_links_count();
            $affiliates_exist = wcusage_check_affiliates_exist();
            $cached_counts = array(
                'pending_registrations' => $pending_registrations,
                'pending_payouts'       => $pending_payouts,
                'pending_direct_links'  => $pending_direct_links,
                'affiliates_exist'      => $affiliates_exist,
            );
            set_transient( $cache_key, $cached_counts, 1 * MINUTE_IN_SECONDS );
        }
        $cached_data = $cached_counts;
    }
    $pending_registrations = $cached_data['pending_registrations'];
    $pending_payouts = $cached_data['pending_payouts'];
    $pending_direct_links = $cached_data['pending_direct_links'];
    $affiliates_exist = $cached_data['affiliates_exist'];
    $show_affiliate_notification = !$affiliates_exist;
    $pending_total = intval( $pending_registrations ) + intval( $pending_payouts ) + intval( $pending_direct_links );
    $bell_total = intval( $pending_total ) + (( $show_affiliate_notification ? 1 : 0 ));
    // Get referral notifications
    $user_id = get_current_user_id();
    $notifications_enabled = get_user_meta( $user_id, 'wcusage_admin_notifications_enabled', true );
    if ( $notifications_enabled === '' ) {
        $notifications_enabled = '1';
    }
    $meta_key = 'wcusage_referral_notify_last_date';
    $now = current_time( 'mysql' );
    $wpdb = $GLOBALS['wpdb'];
    $table = $wpdb->prefix . 'wcusage_activity';
    $max_days = 7;
    $last_date = sanitize_text_field( get_user_meta( $user_id, $meta_key, true ) );
    if ( !$last_date ) {
        $date_limit = date( 'Y-m-d H:i:s', strtotime( "-{$max_days} days", strtotime( $now ) ) );
    } else {
        $date_limit = $last_date;
    }
    $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT date FROM {$table} WHERE event = %s AND date >= %s", 'referral', $date_limit ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $referral_count = count( $referrals );
    $referral_message = '';
    if ( $referral_count > 0 ) {
        /* translators: 1: number of referrals, 2: date/time */
        $referral_message = sprintf( esc_html__( 'There have been %1$d new affiliate referrals since %2$s.', 'woo-coupon-usage' ), $referral_count, date_i18n( 'F j, Y H:iA', strtotime( $date_limit ) ) );
        $bell_total += $referral_count;
        // Add actual number of new referrals to total
    }
    if ( !$notifications_enabled ) {
        $bell_total = 0;
        // Generate simple dropdown with toggle
        ob_start();
        ?>
        <div id="wcusage-admin-bell-dropdown" style="display: none; position: absolute; margin-top: 10px; left: 50%; top: 32px; background: #fff; border: 1px solid #ddd; border-radius: 8px; width: 300px; transform: translateX(-50%); box-shadow: 0 2px 16px rgba(0,0,0,0.12); z-index: 99999;">
            <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-weight: 600; color: #333; text-align: center;"><?php 
        echo esc_html__( 'Notifications Disabled', 'woo-coupon-usage' );
        ?></div>
            <div style="padding: 12px 16px; text-align: center;">
                <a href="#" id="wcusage-toggle-notifications" style="color: #0073aa; text-decoration: underline; font-size: 11px;"><?php 
        echo esc_html__( 'Enable Notifications', 'woo-coupon-usage' );
        ?></a>
            </div>
        </div>
        <?php 
        $dropdown_html = ob_get_clean();
    } else {
        // Generate dropdown HTML
        ob_start();
        ?>
        <div id="wcusage-admin-bell-dropdown" style="display: none; position: absolute; margin-top: 10px; left: 50%; top: 32px; background: #fff; border: 1px solid #ddd; border-radius: 8px; width: 300px; transform: translateX(-50%); box-shadow: 0 2px 16px rgba(0,0,0,0.12); z-index: 99999;">
            <?php 
        if ( $referral_count > 0 ) {
            ?>
            <div id="wcusage-admin-bell-referral-section" style="display:block;">
                <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-weight: 600; color: #333; text-align: center;"><?php 
            echo esc_html__( 'Notifications', 'woo-coupon-usage' );
            ?></div>
                <div id="wcusage-admin-bell-referral-message" style="padding:10px 16px; border-bottom: 1px solid #f3f3f3;">
                    <span class="fa-solid fa-cart-plus" style="margin-right: 5px;"></span>
                    <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_referrals' ) );
            ?>"
                        style="text-decoration: none; color: #111;">
                        <?php 
            echo esc_html( $referral_message );
            ?>
                    </a>
                </div>
            </div>
            <?php 
        }
        ?>
            <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-weight: 600; color: #333; text-align: center;"><?php 
        echo esc_html__( 'Pending Admin Tasks', 'woo-coupon-usage' );
        ?></div>
            <ul style="list-style: none; margin: 0; padding: 0;" id="wcusage-admin-bell-referral-list">
                <?php 
        if ( $show_affiliate_notification ) {
            ?>
                <li style="padding: 5px 16px 7px 16px; display: flex; align-items: center; gap: 8px; margin-bottom: 0; border-bottom: 1px solid #f3f3f3;">
                    <span class="fa-solid fa-user-group" style="color: #f39c12;"></span>
                    <span style="font-weight: bold; color: #f39c12;">
                    <?php 
            echo sprintf( esc_html__( 'You currently have no %s!', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'affiliates', 'woo-coupon-usage' ) ) ) );
            ?>
                    <br/>
                    <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_add_affiliate' ) );
            ?>"
                    style="margin-left: auto; color: #f39c12; text-decoration: underline; font-size: 13px; font-weight: bold;">
                        <?php 
            echo sprintf( esc_html__( 'Add your first %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
            ?>
                    </a>
                    </span>
                </li>
                <?php 
        }
        ?>
                <?php 
        if ( $pending_registrations > 0 ) {
            ?>
                <li style="padding: 10px 16px; border-bottom: 1px solid #f3f3f3; display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
                    <span class="fa-solid fa-user-plus" style="color: #0073aa;"></span>
                    <span><?php 
            echo esc_html__( 'Pending Registrations:', 'woo-coupon-usage' );
            ?></span>
                    <span style="margin-left: auto; font-weight: bold; color: #d9534f;"><?php 
            echo intval( $pending_registrations );
            ?></span>
                    <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_registrations' ) );
            ?>" style="margin-left: 10px; color: #0073aa; text-decoration: underline; font-size: 13px;"><?php 
            echo esc_html__( 'Manage', 'woo-coupon-usage' );
            ?></a>
                </li>
                <?php 
        }
        ?>
                <?php 
        if ( $pending_direct_links > 0 ) {
            ?>
                <li style="padding: 10px 16px; border-bottom: 1px solid #f3f3f3; display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
                    <span class="fa-solid fa-globe" style="color: #6f42c1;"></span>
                    <span><?php 
            echo esc_html__( 'Pending Domains:', 'woo-coupon-usage' );
            ?></span>
                    <span style="margin-left: auto; font-weight: bold; color: #d9534f;"><?php 
            echo intval( $pending_direct_links );
            ?></span>
                    <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_domains&status=pending' ) );
            ?>" style="margin-left: 10px; color: #6f42c1; text-decoration: underline; font-size: 13px;"><?php 
            echo esc_html__( 'Manage', 'woo-coupon-usage' );
            ?></a>
                </li>
                <?php 
        }
        ?>
                <?php 
        ?>
            </ul>
            <?php 
        if ( $pending_total == 0 && !$show_affiliate_notification ) {
            ?>
            <div style="padding: 12px 16px; color: #888; text-align: center;"><?php 
            echo esc_html__( 'No pending tasks 🎉', 'woo-coupon-usage' );
            ?></div>
            <?php 
        }
        ?>
            <div style="padding: 4px 16px 9px 16px; border-top: 1px solid #eee; text-align: center;">
                <a href="#" id="wcusage-toggle-notifications" style="color: #0073aa; text-decoration: underline; font-size: 11px;"><?php 
        echo esc_html__( 'Disable Notifications', 'woo-coupon-usage' );
        ?></a>
            </div>
        </div>
        <?php 
        $dropdown_html = ob_get_clean();
    }
    // Only update last viewed date if bell is clicked
    if ( $update_date ) {
        update_user_meta( $user_id, $meta_key, $now );
    }
    wp_send_json( array(
        'count'         => $bell_total,
        'dropdown_html' => $dropdown_html,
        'enabled'       => $notifications_enabled,
    ) );
}

/**
 * AJAX handler for toggling admin notifications
 */
add_action( 'wp_ajax_wcusage_toggle_admin_notifications', 'wcusage_toggle_admin_notifications_ajax' );
function wcusage_toggle_admin_notifications_ajax() {
    check_ajax_referer( 'wcusage_admin_bell', 'nonce' );
    $user_id = get_current_user_id();
    $current = get_user_meta( $user_id, 'wcusage_admin_notifications_enabled', true );
    if ( $current === '' ) {
        $current = '1';
    }
    $new_value = ( $current == '1' ? '0' : '1' );
    update_user_meta( $user_id, 'wcusage_admin_notifications_enabled', $new_value );
    wp_send_json( array(
        'enabled' => $new_value,
    ) );
}

/**
 * Output the admin notification bell
 */
function wcusage_admin_notification_bell() {
    // Enqueue scripts and styles
    wp_enqueue_script(
        'wcusage-admin-notification-bell',
        WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-notification-bell.js',
        array('jquery'),
        null,
        true
    );
    wp_localize_script( 'wcusage-admin-notification-bell', 'wcusageAdminBell', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wcusage_admin_bell' ),
    ) );
    // Add inline CSS for bell shake animation
    $shake_css = "\r\n    @keyframes wcusage-bell-shake {\r\n        0% { transform: rotate(0deg); }\r\n        25% { transform: rotate(-10deg); }\r\n        50% { transform: rotate(10deg); }\r\n        75% { transform: rotate(-10deg); }\r\n        100% { transform: rotate(0deg); }\r\n    }\r\n    .wcusage-bell-shake {\r\n        animation: wcusage-bell-shake 0.5s ease-in-out;\r\n    }\r\n    ";
    wp_add_inline_style( 'wcusage-admin-header-menu', $shake_css );
    // Output the bell HTML placeholder
    $user_id = get_current_user_id();
    $notifications_enabled = get_user_meta( $user_id, 'wcusage_admin_notifications_enabled', true );
    if ( $notifications_enabled === '' ) {
        $notifications_enabled = '1';
    }
    // default true
    ?>
    <div class="wcusage-admin-bell-container" style="position: relative; margin-left: 10px;">
        <a href="#" id="wcusage-admin-bell" style="display: flex; align-items: center; position: relative; text-decoration: none; <?php 
    if ( !$notifications_enabled ) {
        echo 'opacity: 0.5;';
    }
    ?>">
            <span class="fa-solid fa-bell" style="font-size: 22px; color: #333;"></span>
            <span class="wcusage-admin-bell-count" style="position: absolute;
            top: -11px; right: -2px; background: #d9534f; color: #fff;
            font-size: 10px; font-weight: bold; border-radius: 50%;
            padding: 1px 1px; min-width: 22px; text-align: center; box-shadow: 0 2px 8px rgba(217,83,79,0.15); display: none;"></span>
        </a>
        <div id="wcusage-admin-bell-dropdown-placeholder"></div>
    </div>
    <?php 
}

add_action( 'wcusage_hook_admin_notification_bell', 'wcusage_admin_notification_bell' );
/**
 * Helper functions
 */
function wcusage_clear_admin_bell_cache() {
    delete_transient( 'wcusage_admin_bell_counts' );
}

// Clear cache when relevant data changes
add_action( 'wcusage_hook_registration_status_changed', 'wcusage_clear_admin_bell_cache' );
add_action( 'wcusage_hook_payout_status_changed', 'wcusage_clear_admin_bell_cache' );
add_action(
    'updated_post_meta',
    function (
        $meta_id,
        $post_id,
        $meta_key,
        $meta_value
    ) {
        if ( $meta_key === 'wcu_select_coupon_user' ) {
            wcusage_clear_admin_bell_cache();
        }
    },
    10,
    4
);
add_action(
    'deleted_post_meta',
    function (
        $meta_ids,
        $post_id,
        $meta_key,
        $meta_value
    ) {
        if ( $meta_key === 'wcu_select_coupon_user' ) {
            wcusage_clear_admin_bell_cache();
        }
    },
    10,
    4
);
function wcusage_get_pending_registrations_count() {
    global $wpdb;
    return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wcusage_register WHERE status = 'pending'" );
}

function wcusage_get_pending_payouts_count() {
    global $wpdb;
    return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wcusage_payouts WHERE status = 'pending'" );
}

function wcusage_get_pending_direct_links_count() {
    global $wpdb;
    $pending_direct_links = 0;
    $direct_links_enabled = wcusage_get_setting_value( 'wcusage_field_enable_directlinks', 0 );
    if ( $direct_links_enabled && wcu_fs()->can_use_premium_code() ) {
        $direct_links_table = $wpdb->prefix . 'wcusage_directlinks';
        $pending_direct_links = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$direct_links_table} WHERE status = 'pending'" ) );
    }
    return $pending_direct_links;
}

function wcusage_check_affiliates_exist() {
    // Use direct SQL query - much faster than get_posts()
    global $wpdb;
    $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT pm.post_id) \r\n         FROM {$wpdb->postmeta} pm\r\n         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id\r\n         WHERE pm.meta_key = %s \r\n         AND CAST(pm.meta_value AS UNSIGNED) > 0\r\n         AND p.post_type = %s\r\n         AND p.post_status IN ('publish', 'pending', 'draft')\r\n         LIMIT 1", 'wcu_select_coupon_user', 'shop_coupon' ) );
    return intval( $count ) > 0;
}
