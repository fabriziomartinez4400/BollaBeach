<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Floating Affiliate Widget - Display Conditions
 * Functions for checking when and where to display the widget
 */

// Quick lightweight check for display conditions
function wcusage_should_show_floating_widget_quick() {
    // Only check essential conditions that don't require heavy processing
    $display_settings = array(
        'hide_logged_out' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_logged_out', '0'),
        'hide_logged_in' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_logged_in', '0'),
        'page_display' => wcusage_get_setting_value('wcusage_field_floating_widget_page_display', 'all'),
        'hide_affiliate_pages' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_affiliate_pages', '1')
    );
    
    // Check user login status
    $is_logged_in = is_user_logged_in();
    if ($display_settings['hide_logged_out'] && !$is_logged_in) {
        return false;
    }
    if ($display_settings['hide_logged_in'] && $is_logged_in) {
        return false;
    }
    
    // Quick affiliate page check
    if ($display_settings['hide_affiliate_pages'] && wcusage_is_affiliate_page_quick()) {
        return false;
    }
    
    // Skip complex page display logic for now - will be checked in full conditions later
    
    return true;
}

// Quick affiliate page check (minimal processing)
function wcusage_is_affiliate_page_quick() {
    // Only check the most common cases without heavy processing
    $wcusage_field_account_tab = wcusage_get_setting_value('wcusage_field_account_tab', 0);
    if ($wcusage_field_account_tab && function_exists('is_account_page') && is_account_page()) {
        return true;
    }
    
    // Check if current page ID matches any affiliate page IDs (cached)
    static $affiliate_page_ids = null;
    if ($affiliate_page_ids === null) {
        $affiliate_page_ids = array_filter(array(
            wcusage_get_setting_value('wcusage_dashboard_page', ''),
            wcusage_get_setting_value('wcusage_mla_dashboard_page', ''),
        ));
    }

    // Get current URL slug from URL headers without parameters
    $page_slug = '';
    if (isset($_SERVER['REQUEST_URI'])) {
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = wp_parse_url($request_uri);
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $page_slug = trim($path, '/');
    }

    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    // If page URL slug matches portal slug
    if ($page_slug === $wcusage_portal_slug) {
        return true;
    }

    $wcusage_mla_portal_slug = wcusage_get_setting_value('wcusage_mla_portal_slug', 'mla-affiliate-portal');
    // If page URL slug matches MLA portal slug
    if ($page_slug === $wcusage_mla_portal_slug) {
        return true;
    }

    $current_page_id = get_queried_object_id();
    if ($current_page_id && in_array($current_page_id, $affiliate_page_ids)) {
        return true;
    }
    
    return false;
}

// Check if floating widget should be displayed (full conditions check)
function wcusage_should_show_floating_widget() {
    $settings = wcusage_get_floating_widget_settings();
    $display_settings = $settings['display'];
    
    // Check user login status
    $is_logged_in = is_user_logged_in();
    if ($display_settings['hide_logged_out'] && !$is_logged_in) {
        return false;
    }
    if ($display_settings['hide_logged_in'] && $is_logged_in) {
        return false;
    }
    
    // Check if user is affiliate
    if ($display_settings['hide_non_affiliate'] && $is_logged_in) {
        $user_id = get_current_user_id();
        $is_affiliate = wcusage_is_user_affiliate($user_id);
        if (!$is_affiliate) {
            return false;
        }
    }
    
    // Check device type
    if ($display_settings['hide_mobile'] && wp_is_mobile()) {
        // Additional check to distinguish mobile from tablet
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_tablet = (bool) preg_match('/iPad|Android(?=.*Tablet)|PlayBook|Silk/', $user_agent);
        if (!$is_tablet) {
            return false;
        }
    }
    
    if ($display_settings['hide_tablet']) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_tablet = (bool) preg_match('/iPad|Android(?=.*Tablet)|PlayBook|Silk/', $user_agent);
        if ($is_tablet) {
            return false;
        }
    }
    
    // Check page display settings
    if (!wcusage_check_page_display_conditions($display_settings)) {
        return false;
    }
    
    // Check affiliate pages
    if ($display_settings['hide_affiliate_pages'] && wcusage_is_affiliate_page()) {
        return false;
    }
    
    return true;
}

// Check page display conditions
function wcusage_check_page_display_conditions($display_settings) {
    $page_display = $display_settings['page_display'];
    $specific_pages = $display_settings['specific_pages'];
    
    if ($page_display === 'all') {
        return true;
    }
    
    if (empty($specific_pages) || $specific_pages === '') {
        if($page_display === 'specific_show') {
            return false; // No specific pages set, cannot show
        }
        if($page_display === 'specific_hide') {
            return true; // No specific pages set, can hide
        }
    }
    
    // Get current page URL and path
    $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $current_path = $_SERVER['REQUEST_URI'];
    
    // Parse specific pages - split by comma and remove empty entries
    $pages = array_filter(array_map('trim', explode(',', $specific_pages)));
    $is_match = false;
    
    foreach ($pages as $page) {
        if (empty($page)) continue;
        
        // Normalize the page URL for comparison
        $page = trim($page);
        
        // If it's a relative URL (starts with /), compare with current path
        if (strpos($page, '/') === 0) {
            $compare_against = $current_path;
        } else {
            // If it's a full URL, compare with full current URL
            $compare_against = $current_url;
        }
        
        // Handle wildcards
        if (strpos($page, '*') !== false) {
            $pattern = str_replace(['*', '/'], ['.*', '\/'], preg_quote($page, '/'));
            if (preg_match('/^' . $pattern . '$/i', $compare_against)) {
                $is_match = true;
                break;
            }
        } else {
            // Exact match - also try with trailing slash variations
            if ($compare_against === $page || 
                rtrim($compare_against, '/') === rtrim($page, '/') ||
                $compare_against === rtrim($page, '/') . '/' ||
                rtrim($compare_against, '/') === $page) {
                $is_match = true;
                break;
            }
        }
    }
    
    // Return based on display type
    if ($page_display === 'specific_show') {
        return $is_match;
    } elseif ($page_display === 'specific_hide') {
        return !$is_match;
    }
    
    return true;
}

// Check if current page is an affiliate page (full check)
function wcusage_is_affiliate_page() {
    global $post;
    
    // Check if it's the WooCommerce account page with affiliate tab
    $wcusage_field_account_tab = wcusage_get_setting_value('wcusage_field_account_tab', 0);
    if ($wcusage_field_account_tab && function_exists('is_account_page') && is_account_page()) {
        return true;
    }
    
    // Check affiliate dashboard pages by ID
    $dashboard_page_id = wcusage_get_setting_value('wcusage_dashboard_page', '');
    if ($dashboard_page_id && is_page($dashboard_page_id)) {
        return true;
    }
    
    // Check affiliate registration page by ID
    $registration_page_id = wcusage_get_setting_value('wcusage_registration_page', '');
    if ($registration_page_id && is_page($registration_page_id)) {
        return true;
    }
    
    // Check MLA dashboard page by ID
    $mla_dashboard_page_id = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
    if ($mla_dashboard_page_id && is_page($mla_dashboard_page_id)) {
        return true;
    }
    
    // Check affiliate portal slugs
    $portal_slug = wcusage_get_setting_value('wcusage_portal_slug', '');
    if ($portal_slug && is_page($portal_slug)) {
        return true;
    }
    
    // Check MLA portal slug
    $mla_portal_slug = wcusage_get_setting_value('wcusage_mla_portal_slug', '');
    if ($mla_portal_slug && is_page($mla_portal_slug)) {
        return true;
    }
    
    // Check for affiliate registration landing page (legacy check)
    $wcusage_field_registration_enable = wcusage_get_setting_value('wcusage_field_registration_enable', '1');
    if ($wcusage_field_registration_enable) {
        $wcusage_field_registration_page = wcusage_get_setting_value('wcusage_field_registration_page', '');
        if ($wcusage_field_registration_page && is_page($wcusage_field_registration_page)) {
            return true;
        }
    }
    
    return false;
}
