<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_rest_api
{

    public function nsc_bar_register_endpoints()
    {

        register_rest_route('beautiful-and-responsive-cookie-consent/v1', '/admin-notices', array(
            'methods' => 'POST',
            'callback' => array($this, "nsc_bar_admin_notices"),
            'permission_callback' => array($this, "nsc_bar_check_admin_permissions"),
        ));
    }

    public function nsc_bar_admin_notices(WP_REST_Request $request)
    {
        update_option("nsc_bar_intern_notice_review_later", time());
    }

    public function nsc_bar_check_admin_permissions($request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', 'Invalid nonce', ['status' => 403]);
        }
        $neededCapability = get_option("nsc_bar_capabilityCustom", "manage_options");
        return current_user_can($neededCapability);
    }
}
