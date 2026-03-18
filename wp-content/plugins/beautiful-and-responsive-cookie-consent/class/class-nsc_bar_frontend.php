<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_frontend
{

    private $json_config_string;
    private $plugin_url;
    private $plugin_configs;
    private $customized_font;
    private $cookietypes;
    private $cookie_name;
    private $compliance_type;
    private $custom_link;
    private $custom_link_new_window;
    private $improveBannerLoadingSpeed;
    private $dataLayerName;
    private $container;

    public function __construct()
    {
        $this->plugin_url = NSC_BAR_PLUGIN_URL;
        $this->plugin_configs = new nsc_bar_plugin_configs();
        $this->customized_font = false;
        $this->cookietypes = array();
        $this->cookie_name = "";
        $this->compliance_type = "";
        $this->custom_link = "";
        $this->custom_link_new_window = "";
        $this->improveBannerLoadingSpeed = false;
    }

    public function getCookieName()
    {
        if (empty($this->cookie_name)) {
            $this->cookie_name = $this->plugin_configs->getConsentCookieName();
        }
        return $this->cookie_name;
    }

    public function nsc_bar_set_json_configs($nsc_bar_banner_config)
    {
        $message = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("content_message", false);
        $filteredMessage = apply_filters('nsc_bar_cookie_bar_message', $message);
        $nsc_bar_banner_config->nsc_bar_update_banner_setting("content_message", $filteredMessage, "string");

        $this->json_config_string = $nsc_bar_banner_config->nsc_bar_get_banner_config_string(true);
        $this->customized_font = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("customizedFont", false);
        $this->improveBannerLoadingSpeed = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("improveBannerLoadingSpeed", false);
        $this->cookietypes = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("cookietypes", array());
        $this->cookie_name = $this->getCookieName();
        $this->compliance_type = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("type", $this->plugin_configs->nsc_bar_return_settings_field_default_value("type"));
        $this->dataLayerName = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("dataLayerName", $this->plugin_configs->nsc_bar_return_settings_field_default_value("dataLayerName"));
        $this->container = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("container", false);
        $this->custom_link = $this->get_create_custom_link($nsc_bar_banner_config, false);
        $this->custom_link_new_window = $this->get_create_custom_link($nsc_bar_banner_config, true);
    }

    public function nsc_bar_execute_frontend_wp_actions()
    {
        if ($this->plugin_configs->nsc_bar_new_banner_enabled() === false) {
            add_action('wp_enqueue_scripts', array($this, 'nsc_bar_enqueue_scripts_osano'));
        }
        add_shortcode('cc_show_cookie_banner_nsc_bar', array($this, 'nsc_bar_shortcode_show_cookie_banner'));
    }

    public function nsc_bar_enqueue_dataLayer_init_script()
    {
        $banner_active = $this->plugin_configs->nsc_bar_get_option('activate_banner');
        $banner_active = apply_filters('nsc_bar_filter_banner_is_active', $banner_active);
        if ($banner_active != true) {
            return;
        }
        $this->nsc_bar_get_dataLayer_banner_init_script(false);
    }

    public function nsc_bar_get_user_consent_values()
    {
        $config_cookie_name = $this->plugin_configs->getConsentCookieName();
        $rawValues = $this->nsc_bar_get_dataLayer_banner_init_script(true);

        $userSelects = array();
        foreach ($rawValues as $dlCookieName => $cookieValue) {
            if ($dlCookieName === $config_cookie_name) {
                $userSelects[] = $cookieValue;
                continue;
            }

            if ($cookieValue === "allow") {
                $userSelects[] = str_replace($config_cookie_name . "_", "", $dlCookieName);
            }
        }
        return $userSelects;
    }

    public function nsc_bar_get_dataLayer_banner_init_script($returnValue)
    {

        $nsc_bar_banner_config = new nsc_bar_banner_configs();
        $pushToDl = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("onStatusChange", $this->plugin_configs->nsc_bar_return_settings_field_default_value("onStatusChange"));
        if ($pushToDl !== "1" && empty($returnValue)) {
            return;
        }

        $this->cookie_name = $this->getCookieName();
        $this->compliance_type = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("type", $this->plugin_configs->nsc_bar_return_settings_field_default_value("type"));

        if ($this->compliance_type !== "newBanner") {
            $this->cookietypes = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("cookietypes", array());
            $cookies = $this->get_consent_cookie_values();
        }

        if ($this->compliance_type === "newBanner") {
            $cookieHandler = new nsc_bar_cookie_handler();
            $cookieValue = $cookieHandler->nsc_bar_get_cookies_by_name($this->getCookieName());
            $cookies = apply_filters('nsc_bar_user_choice_new_banner', "", $cookieValue);
        }

        if ($returnValue === "raw") {
            return $cookies;
        }

        // IMPORTANT!! dataLayer script reads cookie too.
        if (empty($returnValue)) {
            $this->dataLayerName = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("dataLayerName", $this->plugin_configs->nsc_bar_return_settings_field_default_value("dataLayerName"));
            echo "<script id='nsc_bar_get_dataLayer_banner_init_script' nowprocket data-pagespeed-no-defer data-cfasync data-no-optimize='1' data-no-defer='1' type='text/javascript'>";
            echo '!function(e,o,n,s){const c=' . json_encode($this->escape_cookies($cookies), JSON_UNESCAPED_UNICODE) . ',i="' . esc_js($this->dataLayerName) . '",t=Object.keys(c),a={event:"beautiful_cookie_consent_initialized"};for(let e=0;e<t.length;e++)a[t[e]]=d(t[e],"' . esc_js($this->compliance_type) . '")||c[t[e]].defaultValue,"dismiss"===a[t[e]]&&(a[t[e]]="allow");function d(e,o){if("newBanner"!==o)return l(e);let n=l("' . esc_js($this->getCookieName()) . '");return n?(n=decodeURIComponent(n),n?(n=JSON.parse(n),n?!0===n.categories.includes(e)?"allow":"deny":(console.warn("cookie not found 3"),!1)):(console.warn("cookie not found 2"),!1)):(console.warn("cookie not found 1"),!1)}function l(e){return document.cookie.match("(^|;)\\\s*"+e+"\\\s*=\\\s*([^;]+)")?.pop()||""}window[i]=window[i]||[],window[i].push(a)}();';
            echo "</script>";
            return;
        }

        $dataLayerValues = array();
        foreach ($cookies as $cookie_name => $cookie_values) {
            $cookie_value = $cookie_values["value"];
            if (empty($cookie_value)) {
                $cookie_value = $cookie_values["defaultValue"];
            }

            // goal: remove dismiss completly from application. Problem: is saved in cookie for "just info". For backward compatibility hard to change.
            // first step: in datalayer dismiss will never appear.
            if ($cookie_value === "dismiss") {
                $cookie_value = "allow";
            }
            $key = esc_js($cookie_name);
            $dataLayerValues[$key] = esc_js($cookie_value);
        }

        $dataLayerValues = apply_filters('nsc_bar_filter_data_layer_values', $dataLayerValues);
        return $dataLayerValues;
    }

    private function escape_cookies($cookies)
    {
        $escaped_cookies = array();
        foreach ($cookies as $cookie_name => $cookie_values) {
            $escaped_cookies[esc_js($cookie_name)]["value"] = esc_js($cookie_values["value"]);
            $escaped_cookies[esc_js($cookie_name)]["defaultValue"] = esc_js($cookie_values["defaultValue"]);
        }
        return $escaped_cookies;
    }

    public function nsc_bar_enqueue_scripts_osano()
    {
        wp_register_style('nsc_bar_nice-cookie-consent', $this->plugin_url . 'public/cookieNSCconsent.min.css', array(), NSC_BAR_PLUGIN_VERSION);
        if (!empty($this->customized_font)) {
            wp_add_inline_style('nsc_bar_nice-cookie-consent', '.cc-window { font-family: ' . str_replace("&#039;", "'", esc_html($this->customized_font)) . '}');
        }
        wp_enqueue_style('nsc_bar_nice-cookie-consent');

        $banner_init_script_dependencies = array();
        $banner_init_script_dependencies = apply_filters('nsc_bar_filter_banner_init_dependencies', $banner_init_script_dependencies);

        $bannerOneUrl = $this->plugin_url . 'public/cookieNSCconsent.min.js';
        $bannerOneUrl = apply_filters('nsc_bar_filter_banner_one_url', $bannerOneUrl);
        wp_register_script('nsc_bar_nice-cookie-consent_js', $bannerOneUrl, $banner_init_script_dependencies, NSC_BAR_PLUGIN_VERSION, true);

        $eventListener = 'window.addEventListener("load"';
        $additonalCheck = "";
        if ($this->improveBannerLoadingSpeed === "1") {
            $eventListener = 'document.addEventListener("DOMContentLoaded"';
        }

        if ($this->improveBannerLoadingSpeed === "2") {
            $eventListener = 'document.addEventListener("readystatechange"';
            $additonalCheck = 'if(document.readyState !== "complete") { return; }';
        }

        $bannerInitScript = 'function(){ ' . $additonalCheck . ' window.cookieconsent.initialise(' . $this->nsc_bar_json_with_js_function() . ')}';
        $bannerInitScript = apply_filters('nsc_bar_filter_inline_script_initialize', $bannerInitScript);

        wp_add_inline_script("nsc_bar_nice-cookie-consent_js", $eventListener . ',' . $bannerInitScript . ');');
        wp_enqueue_script('nsc_bar_nice-cookie-consent_js');
    }

    public function nsc_bar_add_stylesheet_attributes($html, $handle)
    {
        return $html;
    }

    public function nsc_bar_add_script_attributes($tag, $handle, $src)
    {
        return $tag;
    }

    public function nsc_bar_json_with_js_function()
    {
        $validator = new nsc_bar_input_validation();
        $popUpCloseJsFunction = '"onPopupClose": function(){location.reload();}';

        $json_config_string_with_js = $this->json_config_string;
        $json_config_string_with_js = apply_filters('nsc_bar_filter_json_config_string_before_js', $json_config_string_with_js);
        $json_config_string_with_js = $validator->escape_json_content($json_config_string_with_js);

        if (!empty($this->container)) {
            $setContainerPosition = '"container": document.querySelector("' . esc_js($this->container) . '")';
            $json_config_string_with_js = str_replace(array('"container": "' . $this->container . '"', '"container":"' . $this->container . '"'), $setContainerPosition, $json_config_string_with_js);
        }

        if (is_admin()) {
            $popUpCloseJsFunction = '"onPopupClose": function(){}';
        }

        $json_config_string_with_js = str_replace(array('"onPopupClose": "1"', '"onPopupClose":"1"'), $popUpCloseJsFunction, $json_config_string_with_js);
        $json_config_string_with_js = str_replace('{{customLink}}', $this->custom_link, $json_config_string_with_js);
        $json_config_string_with_js = str_replace('{{customLink_openNewWindow}}', $this->custom_link_new_window, $json_config_string_with_js);
        $json_config_string_with_js = apply_filters('nsc_bar_filter_json_config_string_with_js', $json_config_string_with_js);
        return $json_config_string_with_js;
    }

    public function nsc_bar_shortcode_show_cookie_banner()
    {
        $linktext = $this->plugin_configs->nsc_bar_get_option("shortcode_link_show_banner_text");
        return '<a class="nsc-bara-manage-cookie-settings" style="cursor: pointer;">' . esc_html($linktext) . '</a>';
    }

    public function nsc_bar_exclude_inline_scripts_from_caching($patterns)
    {
        $patterns[] = "nsc_bara_consent_mode_default_script";
        $patterns[] = "nsc_bara_blocking_scripts_inline";
        $patterns[] = "nsc_bar_nice-cookie-consent_js";
        $patterns[] = "nsc_bar_get_dataLayer_banner_init_script";
        return $patterns;
    }

    private function get_consent_cookie_values()
    {
        $cookieHandler = new nsc_bar_cookie_handler();
        $dataLayerEntries = array();

        $dataLayerEntries["cookieconsent_status"] = array("value" => $cookieHandler->nsc_bar_get_cookies_by_name($this->getCookieName()), "defaultValue" => $this->calculate_default_consent_setting());

        if ($this->compliance_type !== "detailed" && $this->compliance_type !== "detailedRev" && $this->compliance_type !== "detailedRevDeny") {
            return $dataLayerEntries;
        }

        // detailed consent area

        if (empty($this->cookietypes)) {
            return false;
        }

        $numberOfCookies = count($this->cookietypes);
        for ($i = 0; $i < $numberOfCookies; $i++) {
            $cookie_name = $this->getCookieName() . "_" . $this->cookietypes[$i]["cookie_suffix"];
            $dataLayerEntries["cookieconsent_status_" . $this->cookietypes[$i]["cookie_suffix"]] = array("value" => $cookieHandler->nsc_bar_get_cookies_by_name($cookie_name), "defaultValue" => $this->calculate_default_consent_setting($this->cookietypes[$i]));
        }

        return $dataLayerEntries;
    }

    private function calculate_default_consent_setting($cookietype = array())
    {
        if ($this->compliance_type === "opt-in") {
            return "deny";
        }

        if ($this->compliance_type === "opt-out" || $this->compliance_type === "info") {
            return "allow";
        }

        if (empty($cookietype)) {
            return "nochoice";
        }

        if ($cookietype["checked"] === "checked") {
            return "allow";
        }

        return "deny";
    }

    private function get_create_custom_link($nsc_bar_banner_config, $targetBlank)
    {
        $link = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("content_hrefsecond", false);
        $link_text = $nsc_bar_banner_config->nsc_bar_get_cookie_setting("content_linksecond", false);
        $link_html = "";
        $target = "";
        if ($targetBlank === true) {
            $target = " target='_blank'";
        }

        if (!empty($link) && !empty($link_text)) {
            $link_html = "<a class='cc-link' id='nsc-bar-customLink'" . $target . " href='" . esc_url_raw($link) . "'>" . esc_html($link_text) . "</a>";
        }
        return $link_html;
    }
}
