<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Floating Affiliate Widget - AJAX Handlers
 * Main AJAX handlers for popup content and interactions
 */

/**
 * Get captcha configuration data for the floating widget.
 * Returns the captcha type and site key so the popup JS can load the appropriate script dynamically.
 */
function wcusage_get_widget_captcha_data() {
    $enable_captcha = wcusage_get_setting_value('wcusage_registration_enable_captcha', '');
    if ($enable_captcha == '1') {
        $site_key = wcusage_get_setting_value('wcusage_registration_recaptcha_key', '');
        if (!empty($site_key)) {
            return array(
                'type' => 'recaptcha',
                'site_key' => $site_key
            );
        }
    } elseif ($enable_captcha == '2') {
        $site_key = wcusage_get_setting_value('wcusage_registration_turnstile_key', '');
        if (!empty($site_key)) {
            return array(
                'type' => 'turnstile',
                'site_key' => $site_key
            );
        }
    }
    return null;
}

// AJAX handler for popup content (updated to include settings)
add_action('wp_ajax_wcusage_floating_widget_content', 'wcusage_floating_widget_ajax_content');
add_action('wp_ajax_nopriv_wcusage_floating_widget_content', 'wcusage_floating_widget_ajax_content');
function wcusage_floating_widget_ajax_content() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }

        // Perform full display condition check here
        if (!wcusage_should_show_floating_widget()) {
            wp_send_json_error(__('Widget not available for this page/user.', 'woo-coupon-usage'));
            return;
        }

        $user_id = get_current_user_id();
        $settings = wcusage_get_floating_widget_settings(); // Now loads full settings
        
        if (!$user_id) {
            // Show registration form for non-logged in users
            $content = wcusage_widget_registration_form($settings);
            $response_data = array(
                'content' => $content,
                'settings' => $settings
            );
            // Include captcha data so the popup JS can load scripts dynamically
            $captcha_data = wcusage_get_widget_captcha_data();
            if ($captcha_data) {
                $response_data['captcha'] = $captcha_data;
            }
            wp_send_json_success($response_data);
            return;
        }
        
        // Check if wcusage_get_users_coupons_ids function exists
        if (!function_exists('wcusage_get_users_coupons_ids')) {
            wp_send_json_error(__('Required function not available.', 'woo-coupon-usage'));
            return;
        }
        
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        
        if (empty($user_coupons)) {
            // Show registration form for logged-in users without coupons
            $content = wcusage_widget_registration_form($settings);
            $response_data = array(
                'content' => $content,
                'settings' => $settings
            );
            // Include captcha data so the popup JS can load scripts dynamically
            $captcha_data = wcusage_get_widget_captcha_data();
            if ($captcha_data) {
                $response_data['captcha'] = $captcha_data;
            }
            wp_send_json_success($response_data);
            return;
        }
        
        // Generate affiliate dashboard content
        $content = wcusage_generate_affiliate_dashboard($user_coupons, $settings);
        
        wp_send_json_success(array(
            'content' => $content,
            'settings' => $settings
        ));
        
    } catch (Exception $e) {
        error_log('Floating widget error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while loading content.', 'woo-coupon-usage'));
    }
}

// AJAX handler for getting coupon code
add_action('wp_ajax_wcusage_floating_widget_get_coupon_code', 'wcusage_floating_widget_ajax_get_coupon_code');
function wcusage_floating_widget_ajax_get_coupon_code() {
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
        
        // Get coupon code
        $coupon_info = wcusage_get_coupon_info_by_id($coupon_id);
        if (!$coupon_info || empty($coupon_info[3])) {
            wp_send_json_error(__('Invalid coupon data.', 'woo-coupon-usage'));
            return;
        }
        
        wp_send_json_success(array(
            'coupon_code' => $coupon_info[3]
        ));
        
    } catch (Exception $e) {
        error_log('Floating widget get coupon code error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while getting coupon code.', 'woo-coupon-usage'));
    }
}

// AJAX handler for getting coupon description
add_action('wp_ajax_wcusage_floating_widget_get_coupon_description', 'wcusage_floating_widget_ajax_get_coupon_description');
function wcusage_floating_widget_ajax_get_coupon_description() {
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
        
        $description = wcusage_generate_coupon_description($coupon_id);
        
        wp_send_json_success(array(
            'description' => $description
        ));
        
    } catch (Exception $e) {
        error_log('Floating widget get coupon description error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while getting coupon description.', 'woo-coupon-usage'));
    }
}

// AJAX handler for generating custom referral URL
add_action('wp_ajax_wcusage_floating_widget_generate_url', 'wcusage_floating_widget_ajax_generate_url');
function wcusage_floating_widget_ajax_generate_url() {
    try {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wcusage_floating_widget')) {
            wp_send_json_error(__('Security check failed', 'woo-coupon-usage'));
            return;
        }
        
        $page_url = sanitize_url($_POST['page_url']);
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
        
        // Get coupon code
        $coupon_info = wcusage_get_coupon_info_by_id($coupon_id);
        if (!$coupon_info || empty($coupon_info[3])) {
            wp_send_json_error(__('Invalid coupon data.', 'woo-coupon-usage'));
            return;
        }
        
        $coupon_code = $coupon_info[3];
        $referral_url = wcusage_widget_generate_referral_url($page_url, $coupon_code);
        
        wp_send_json_success(array(
            'url' => $referral_url
        ));
        
    } catch (Exception $e) {
        error_log('Floating widget generate URL error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while generating URL.', 'woo-coupon-usage'));
    }
}

// Helper function to generate referral URL
function wcusage_widget_generate_referral_url($page_url, $coupon_code) {
    $url_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    
    // Check if URL already has parameters
    $separator = (strpos($page_url, '?') !== false) ? '&' : '?';
    
    // Generate the referral URL
    $referral_url = $page_url . $separator . $url_prefix . '=' . urlencode($coupon_code);
    
    return $referral_url;
}

// AJAX handler for getting dashboard URL
add_action('wp_ajax_wcusage_floating_widget_get_dashboard_url', 'wcusage_floating_widget_ajax_get_dashboard_url');
function wcusage_floating_widget_ajax_get_dashboard_url() {
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
        
        // Get dashboard URL
        if (function_exists('wcusage_get_coupon_shortcode_page')) {
            $dashboard_page = wcusage_get_coupon_shortcode_page('');
        } else {
            $dashboard_page = home_url();
        }
        
        if (function_exists('wcusage_get_coupon_info_by_id')) {
            $coupon_info = wcusage_get_coupon_info_by_id($coupon_id);
            
            if (!$coupon_info || empty($coupon_info[4])) {
                $dashboard_url = $dashboard_page;
            } else {
                $dashboard_url = $coupon_info[4];
            }
        } else {
            $dashboard_url = $dashboard_page;
        }
        
        wp_send_json_success(array(
            'dashboard_url' => $dashboard_url
        ));
        
    } catch (Exception $e) {
        error_log('Floating widget get dashboard URL error: ' . $e->getMessage());
        wp_send_json_error(__('An error occurred while getting dashboard URL.', 'woo-coupon-usage'));
    }
}
