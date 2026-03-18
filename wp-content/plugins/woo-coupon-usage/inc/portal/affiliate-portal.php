<?php
if(!defined('ABSPATH')) {
    exit;
}

add_action('wp', 'wcusage_affiliate_portal_redirect_registration');
function wcusage_affiliate_portal_redirect_registration() {
    $wcusage_registration_page = wcusage_get_setting_value('wcusage_registration_page', '0');
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    if(!$wcusage_registration_page) {
        if(isset( $_POST['submitaffiliateapplication'])) {
            if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form1'] ) ), 'wcusage_verify_submit_registration_form1' ) || is_user_logged_in() ) {
                $submit_form = wcusage_post_submit_application(0);
                $status = $submit_form['status'];
                // Redirect to the affiliate portal page
                wp_safe_redirect(home_url('/' . $wcusage_portal_slug . '/?status=' . $status));
                exit;
            }
        }
    }
}

// Register rewrite rule for affiliate portal
add_action('init', 'wcusage_add_affiliate_portal_rewrite_rule');
function wcusage_add_affiliate_portal_rewrite_rule() {
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    add_rewrite_rule('^' . $wcusage_portal_slug . '/?$', 'index.php?affiliate_portal=1', 'top');
}

// Function to check if rewrite rule exists
function wcusage_check_affiliate_portal_rewrite_rule() {
    global $wp_rewrite;
    $rules = $wp_rewrite->wp_rewrite_rules();
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    $rule = '^' . $wcusage_portal_slug . '/?$';
    return isset($rules[$rule]);
}

// Suppress default query entirely
add_action('pre_get_posts', 'wcusage_handle_affiliate_portal_query', 1);
function wcusage_handle_affiliate_portal_query($query) {
    if (!is_admin() && $query->is_main_query() && $query->get('affiliate_portal')) {
        $query->set('post_type', 'none'); // Invalid post type
        $query->set('posts_per_page', 0);
        $query->set('paged', 1);
        $query->set('pagename', ''); // Prevent page lookup
        $query->is_home = false;
        $query->is_archive = false;
    }
}

// Add custom query variable
add_filter('query_vars', 'wcusage_add_affiliate_portal_query_var');
function wcusage_add_affiliate_portal_query_var($vars) {
    $vars[] = 'affiliate_portal';
    return $vars;
}

// Flush rewrite rules on plugin activation
register_activation_hook(__FILE__, 'wcusage_flush_rewrite_rules1');
function wcusage_flush_rewrite_rules1() {
    wcusage_add_affiliate_portal_rewrite_rule();
    flush_rewrite_rules();
}

// Load custom template with maximum priority
add_filter('template_include', 'wcusage_load_affiliate_portal_template', PHP_INT_MAX);
function wcusage_load_affiliate_portal_template($template) {
    if (get_query_var('affiliate_portal')) {
        $custom_template = plugin_dir_path(__FILE__) . 'template.php';
        if (file_exists($custom_template)) {
            // Force HTTP 200 and page state
            status_header(200);
            global $wp_query;
            $wp_query->is_404 = false;
            $wp_query->is_page = true;
            return $custom_template;
        }
    }
    return $template;
}

// Debug final template and override if necessary
add_action('wp', function () {
    if (get_query_var('affiliate_portal')) {
        global $wp_query;
        $current_template = get_template();
        // If template isn’t ours, force it
        $custom_template = plugin_dir_path(__FILE__) . 'template.php';
        if (file_exists($custom_template) && $current_template !== $custom_template) {
            include $custom_template;
            exit; // Stop further processing
        }
    }
}, PHP_INT_MAX);