<?php

class nsc_bar_return_filter
{
    private $banner_configs;
    private $frontend;
    private $plugin_configs;


    public function register_return_filter()
    {
        add_filter('nsc_bar_return_filter_configured_cookie_name', array($this, 'nsc_bar_return_filter_configured_cookie_name'));
        add_filter('nsc_bar_return_filter_dataLayer_banner_init_script', array($this, 'nsc_bar_return_filter_dataLayer_banner_init_script'));
        add_filter('nsc_bar_return_filter_banner_config_array', array($this, 'nsc_bar_return_filter_banner_config_array'));
        add_filter('nsc_bar_return_filter_cookie_setting', array($this, 'nsc_bar_return_filter_cookie_setting'), 10, 2);
        add_filter('nsc_bar_return_filter_user_consent_values', array($this, 'nsc_bar_return_filter_user_consent_values'));
    }

    public function nsc_bar_return_filter_configured_cookie_name()
    {
        if (empty($this->plugin_configs) === true) {
            $this->plugin_configs = new nsc_bar_plugin_configs;
        }
        return $this->plugin_configs->getConsentCookieName();
    }

    public function nsc_bar_return_filter_dataLayer_banner_init_script($returnValue)
    {
        if (empty($this->frontend) === true) {
            $this->frontend = new nsc_bar_frontend;
        }
        return $this->frontend->nsc_bar_get_dataLayer_banner_init_script($returnValue);
    }

    public function nsc_bar_return_filter_banner_config_array()
    {
        if (empty($this->banner_configs) === true) {
            $this->banner_configs = new nsc_bar_banner_configs;
        }
        return $this->banner_configs->nsc_bar_get_banner_config_array();
    }

    public function nsc_bar_return_filter_cookie_setting($defaultValue, $slug)
    {
        if (empty($this->banner_configs) === true) {
            $this->banner_configs = new nsc_bar_banner_configs;
        }
        return $this->banner_configs->nsc_bar_get_cookie_setting($slug, $defaultValue);
    }

    public function nsc_bar_return_filter_user_consent_values()
    {
        if (empty($this->frontend) === true) {
            $this->frontend = new nsc_bar_frontend;
        }
        return $this->frontend->nsc_bar_get_user_consent_values();
    }
}