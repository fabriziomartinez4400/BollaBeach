<?php
if (!defined('ABSPATH')) {
    exit;
}


class nsc_bar_integrations
{

    private $banner_configs_obj;

    public function __construct()
    {
        $this->banner_configs_obj = new nsc_bar_banner_configs();
    }

    public function add_filters()
    {
        add_filter('wp_get_consent_type', array($this, 'nsc_bar_wp_consent_api_consenttype'));
    }

    public function nsc_bar_wp_consent_api_consenttype($consenttype)
    {
        $consenttype = $this->banner_configs_obj->nsc_bar_get_cookie_setting('type', 'info');
        $optOut = array('opt-out', 'info');

        if (in_array($consenttype, $optOut)) {
            return 'opt-out';
        }

        return 'opt-in';
    }
}
