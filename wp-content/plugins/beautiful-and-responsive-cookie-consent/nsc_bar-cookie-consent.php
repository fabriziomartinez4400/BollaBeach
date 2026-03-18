<?php
/*
Plugin Name: Beautiful and responsive cookie consent
Description: An easy way to get a beautiful GDPR Cookie Consent Banner. Customize it to match your compliance requirements and website layout. Highly customisable and responsive.
Author: Beautiful Cookie Banner
Version: 4.9.2
Author URI: https://beautiful-cookie-banner.com
Plugin URI: https://beautiful-cookie-banner.com
Text Domain: bar-cookie-consent
License:     GPLv3

Beautiful and responsive cookie consent is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Beautiful and responsive cookie consent is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Beautiful and responsive cookie consent. If not, see {License URI}.
 */

if (!defined('ABSPATH')) {
    exit;
}

define("NSC_BAR_PLUGIN_DIR", dirname(__FILE__));
define("NSC_BAR_PLUGIN_URL", plugin_dir_url(__FILE__));
define("NSC_BAR_PLUGIN_VERSION", "4.9.2");
define("NSC_BAR_SLUG_DBVERSION", "nsc_bar_db_version");

require dirname(__FILE__) . "/class/class-nsc_bar_admin_error.php";
require dirname(__FILE__) . "/class/class-nsc_bar_input_validation.php";
require dirname(__FILE__) . "/class/class-nsc_bar_plugin_configs.php";
require dirname(__FILE__) . "/class/class-nsc_bar_banner_configs.php";
require dirname(__FILE__) . "/class/class-nsc_bar_html_formfields.php";
require dirname(__FILE__) . "/class/class-nsc_bar_frontend.php";
require dirname(__FILE__) . "/class/class-nsc_bar_save_form_fields.php";
require dirname(__FILE__) . "/class/class-nsc_bar_cookie_handler.php";
require dirname(__FILE__) . "/class/class-nsc_bar_admin_settings.php";
require dirname(__FILE__) . "/class/class-nsc_bar_rest_api.php";
require dirname(__FILE__) . "/class/class-nsc_bar_db_upgrader.php";
require dirname(__FILE__) . "/class/class-nsc_bar_return_filter.php";
require dirname(__FILE__) . "/class/class-nsc_bar_integrations.php";

$nsc_bar_db_upgrader = new nsc_bar_db_upgrader;
add_action('plugins_loaded', array($nsc_bar_db_upgrader, 'nsc_bar_do_update'));

$nsc_bar_return_filter = new nsc_bar_return_filter;
$nsc_bar_return_filter->register_return_filter();

$nsc_bar_rest_api = new nsc_bar_rest_api();
add_action('rest_api_init', array($nsc_bar_rest_api, "nsc_bar_register_endpoints"));

$nsc_bar_plugin_configs = new nsc_bar_plugin_configs;
register_activation_hook(__FILE__, array($nsc_bar_plugin_configs, 'nsc_bar_plugin_activation'));

$nsc_bar_cookie_handler = new nsc_bar_cookie_handler;
if (is_admin()) {
    $nsc_bar_save_formfields = new nsc_bar_save_form_fields();
    add_action('plugins_loaded', array($nsc_bar_save_formfields, 'nsc_bar_save_submitted_form_fields'), 50);
    $nsc_bar_input_validation = new nsc_bar_input_validation;
    add_action('plugins_loaded', array($nsc_bar_input_validation, 'nsc_bar_validate_addon'));

    $nsc_bar_admin_settings = new nsc_bar_admin_settings;
    $nsc_bar_admin_settings->nsc_bar_execute_backend_wp_actions();
    add_filter("plugin_action_links_" . plugin_basename(__FILE__), array($nsc_bar_admin_settings, 'nsc_bar_add_settings_link'));

    add_action('admin_init', array($nsc_bar_cookie_handler, 'nsc_bar_delete_cookie_for_preview'), 20);
}

// frontend actions
add_action('send_headers', array($nsc_bar_cookie_handler, 'nsc_bar_migrate_cookie_detailed_to_savesettings'), 1);
add_action('send_headers', array($nsc_bar_cookie_handler, 'nsc_bar_cookie_cleanup'), 2);
add_action('send_headers', array($nsc_bar_cookie_handler, 'nsc_bar_set_itp_cookie'), 5);
add_action('send_headers', array($nsc_bar_cookie_handler, 'nsc_bar_set_default_cookies'), 10);

// dataLayer needs to be pushed so early
$nsc_bar_frontend_banner = new nsc_bar_frontend();
// stick to wp_print_scripts -> might cause caching problems otherwise.
add_action('wp_print_scripts', array($nsc_bar_frontend_banner, "nsc_bar_enqueue_dataLayer_init_script"), -2147483647);

add_filter('style_loader_tag', array($nsc_bar_frontend_banner, 'nsc_bar_add_stylesheet_attributes'), 10, 2);
add_filter('script_loader_tag', array($nsc_bar_frontend_banner, 'nsc_bar_add_script_attributes'), 10, 3);

// this is so late for the wp filter for the message and for weglot needed and possible other plugins.
function nsc_bar_do_frontend_actions()
{
    if (is_admin()) {
        return false;
    }

    $nsc_bar_plugin_configs = new nsc_bar_plugin_configs;
    $banner_active = $nsc_bar_plugin_configs->nsc_bar_get_option('activate_banner');
    $banner_active = apply_filters('nsc_bar_filter_banner_is_active', $banner_active);
    if ($banner_active == true) {
        $nsc_bar_banner_config = new nsc_bar_banner_configs();
        $nsc_bar_frontend_banner = new nsc_bar_frontend();
        $nsc_bar_frontend_banner->nsc_bar_set_json_configs($nsc_bar_banner_config);
        $nsc_bar_frontend_banner->nsc_bar_execute_frontend_wp_actions();
        add_filter('rocket_excluded_inline_js_content', array($nsc_bar_frontend_banner, 'nsc_bar_exclude_inline_scripts_from_caching'));
        add_filter('rocket_defer_inline_exclusions', array($nsc_bar_frontend_banner, 'nsc_bar_exclude_inline_scripts_from_caching'));
    }
}
add_action("plugins_loaded", "nsc_bar_do_frontend_actions");

$nscBarIntgerations = new nsc_bar_integrations();
$nscBarIntgerations->add_filters();
