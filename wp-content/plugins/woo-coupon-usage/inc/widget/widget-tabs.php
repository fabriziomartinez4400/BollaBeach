<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**

* Floating Affiliate Widget - Tab Handlers
 * AJAX handlers and content generators for individual tabs
 */

// AJAX handler for coupon statistics
add_action('wp_ajax_wcusage_floating_widget_stats', 'wcusage_floating_widget_ajax_stats');
function wcusage_floating_widget_ajax_stats() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $stats_html = wcusage_get_floating_widget_stats($coupon_id);
        wp_send_json_success($stats_html);
        
    } catch (Exception $e) {
        error_log('Floating widget stats error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading statistics.', 'woo-coupon-usage'));
    }
}

// AJAX handler for links tab
add_action('wp_ajax_wcusage_floating_widget_links', 'wcusage_floating_widget_ajax_links');
function wcusage_floating_widget_ajax_links() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $links_html = wcusage_get_floating_widget_links($coupon_id);
        wp_send_json_success($links_html);
        
    } catch (Exception $e) {
        error_log('Floating widget links error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading links.', 'woo-coupon-usage'));
    }
}

// AJAX handler for referrals tab
add_action('wp_ajax_wcusage_floating_widget_referrals', 'wcusage_floating_widget_ajax_referrals');
function wcusage_floating_widget_ajax_referrals() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $referrals_html = wcusage_get_floating_widget_referrals($coupon_id);
        wp_send_json_success($referrals_html);
        
    } catch (Exception $e) {
        error_log('Floating widget referrals error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading referrals.', 'woo-coupon-usage'));
    }
}

// AJAX handler for payouts tab
add_action('wp_ajax_wcusage_floating_widget_payouts', 'wcusage_floating_widget_ajax_payouts');
function wcusage_floating_widget_ajax_payouts() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $payouts_html = wcusage_get_floating_widget_payouts($coupon_id);
        wp_send_json_success($payouts_html);
        
    } catch (Exception $e) {
        error_log('Floating widget payouts error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading payouts.', 'woo-coupon-usage'));
    }
}

// AJAX handler for custom tab 1
add_action('wp_ajax_wcusage_floating_widget_custom_tab_1', 'wcusage_floating_widget_ajax_custom_tab_1');
function wcusage_floating_widget_ajax_custom_tab_1() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $custom_tab_1_html = wcusage_get_floating_widget_custom_tab_1($coupon_id);
        wp_send_json_success($custom_tab_1_html);
        
    } catch (Exception $e) {
        error_log('Floating widget custom tab 1 error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading custom tab 1.', 'woo-coupon-usage'));
    }
}

// AJAX handler for custom tab 2
add_action('wp_ajax_wcusage_floating_widget_custom_tab_2', 'wcusage_floating_widget_ajax_custom_tab_2');
function wcusage_floating_widget_ajax_custom_tab_2() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $custom_tab_2_html = wcusage_get_floating_widget_custom_tab_2($coupon_id);
        wp_send_json_success($custom_tab_2_html);
        
    } catch (Exception $e) {
        error_log('Floating widget custom tab 2 error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading custom tab 2.', 'woo-coupon-usage'));
    }
}

// AJAX handler for creatives tab
add_action('wp_ajax_wcusage_floating_widget_creatives', 'wcusage_floating_widget_ajax_creatives');
function wcusage_floating_widget_ajax_creatives() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        $user_id = get_current_user_id();
        
        // Check if required functions exist
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        // Verify user has access to this coupon
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!in_array($coupon_id, $user_coupons)) {
            wp_send_json_error(__('Access denied.', 'woo-coupon-usage'));
            return;
        }
        
        $creatives_html = wcusage_get_floating_widget_creatives($coupon_id);
        wp_send_json_success($creatives_html);
        
    } catch (Exception $e) {
        error_log('Floating widget creatives error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading creatives.', 'woo-coupon-usage'));
    }
}
