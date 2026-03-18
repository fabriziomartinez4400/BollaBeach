<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_cookie_handler
{
    private $banner_configs_object;
    private $cookie_configs;
    private $plugin_configs;
    private $newBannerEnabled;

    public function __construct()
    {
        $this->get_plugin_configs();
        $this->get_banner_configs();
        $this->get_cookie_configs();
    }

    public function nsc_bar_set_itp_cookie()
    {
        if (
            $this->plugin_configs->nsc_bar_get_option('activate_banner') == true &&
            $this->plugin_configs->nsc_bar_get_option('backend_cookie_conversion') == true
        ) {
            $expiryDate = $this->get_expiry_date($this->cookie_configs['name'], $this->cookie_configs['expirydays']);

            if (isset($_COOKIE[$this->cookie_configs['name']])) {
                $this->set_cookie($this->cookie_configs['name'], stripslashes($_COOKIE[$this->cookie_configs['name']]), $expiryDate, $this->cookie_configs['path'], $this->cookie_configs['domain'], $this->cookie_configs['secure']);
                $this->set_cookie("nsc_bar_cs_done", $this->cookie_configs['name'] . "---_---" . $expiryDate, $expiryDate, $this->cookie_configs['path'], $this->cookie_configs['domain'], $this->cookie_configs['secure'], true);
            }

            if (!empty($this->cookie_configs['cookietypes']) && !empty($cookietype_configs["cookie_suffix"])) {
                foreach ($this->cookie_configs['cookietypes'] as $cookietype_configs) {
                    $differentiatedCookieName = $this->cookie_configs['name'] . "_" . $cookietype_configs["cookie_suffix"];
                    if (isset($_COOKIE[$differentiatedCookieName])) {
                        $this->set_cookie($differentiatedCookieName, $_COOKIE[$differentiatedCookieName], $expiryDate, $this->cookie_configs['path'], $this->cookie_configs['domain'], $this->cookie_configs['secure']);
                    }
                }
            }
        }
    }

    public function nsc_bar_set_default_cookies()
    {
        if ($this->plugin_configs->nsc_bar_get_option('activate_banner') == false) {
            return;
        }

        if ($this->banner_configs_object->nsc_bar_get_cookie_setting("setDiffDefaultCookiesFirstPV", $this->plugin_configs->nsc_bar_return_settings_field_default_value("setDiffDefaultCookiesFirstPV")) == false) {
            return;
        }

        $expiryDate = $this->get_expiry_date($this->cookie_configs['name'], $this->cookie_configs['expirydays']);
        if (in_array($this->cookie_configs['compliance_type'], array("detailed", "detailedRev", "detailedRevDeny")) && !empty($this->cookie_configs['cookietypes'])) {
            foreach ($this->cookie_configs['cookietypes'] as $cookietype_configs) {
                $differentiatedCookieName = $this->cookie_configs['name'] . "_" . $cookietype_configs["cookie_suffix"];
                $value = "deny";
                if ($cookietype_configs["checked"] == "checked") {
                    $value = "allow";
                }

                if (!isset($_COOKIE[$differentiatedCookieName])) {
                    $this->set_cookie($differentiatedCookieName, $value, $expiryDate, $this->cookie_configs['path'], $this->cookie_configs['domain'], $this->cookie_configs['secure']);
                }
            }
        }
    }

    public function nsc_bar_get_cookies_by_name($cookie_name)
    {
        $input_validation = new nsc_bar_input_validation();
        if (isset($_COOKIE[$cookie_name])) {
            return $input_validation->nsc_bar_sanitize_input($_COOKIE[$cookie_name]);
        }
        return null;
    }

    public function nsc_bar_delete_cookie_for_preview()
    {
        if (
            $this->plugin_configs->nsc_bar_new_banner_enabled() === false && $this->plugin_configs->nsc_bar_get_option('activate_test_banner') == true &&
            stripos($_SERVER["REQUEST_URI"], "page=nsc_bar-cookie-consent") !== false
        ) {
            $this->nsc_bar_delete_cookie();
        }
    }

    //TODO: remove if everbody is a time on >2.1. needed for migration from 2.0 to 2.1, because the content of the cookieconsent_status cookie was changed in case of differentiated consent. from "detailed" to savesettings
    public function nsc_bar_migrate_cookie_detailed_to_savesettings()
    {
        if (isset($_COOKIE[$this->cookie_configs['name']]) && $_COOKIE[$this->cookie_configs['name']] == "detailed") {
            $expiryDate = $this->get_expiry_date($this->cookie_configs['name'], $this->cookie_configs['expirydays']);
            $this->set_cookie($this->cookie_configs['name'], "savesettings", $expiryDate, $this->cookie_configs['path'], $this->cookie_configs['domain'], $this->cookie_configs['secure']);
        }
    }

    public function clean_up_old_itpsave_cookie_banner_2()
    {
        if (!isset($_COOKIE["nsc_bar_cs_done"])) {
            return;
        }

        if (version_compare(phpversion(), '7.3', '<')) {
            setcookie("nsc_bar_cs_done", "del", time() - 3600, '/; samesite=lax', "", false, false);
        } else {
            /**
             * @disregard P1006
             */
            setcookie("nsc_bar_cs_done", "del", [
                'expires' => time() - 3600,
                'path' => "/",
                'domain' => "",
                'samesite' => 'lax',
                'secure' => false,
                'httponly' => false,
            ]);
        }

        if (!isset($_COOKIE[$this->cookie_configs['name']])) {
            return;
        }

        if ($this->newBannerEnabled === false) {
            return;
        }

        if (version_compare(phpversion(), '7.3', '<')) {
            setcookie($this->cookie_configs['name'], "del", time() - 3600, '/; samesite=lax', "", false, false);
        } else {
            /**
             * @disregard P1006
             */
            setcookie($this->cookie_configs['name'], "del", [
                'expires' => time() - 3600,
                'path' => "/",
                'domain' => "",
                'samesite' => 'lax',
                'secure' => false,
                'httponly' => false,
            ]);
        }
    }

    public function nsc_bar_cookie_cleanup()
    {
        if (!isset($_COOKIE[$this->cookie_configs['name']])) {
            return;
        }

        $this->clean_up_old_itpsave_cookie_banner_2();

        $input_validation = new nsc_bar_input_validation();
        $current_cookie_value = $input_validation->nsc_bar_sanitize_input($_COOKIE[$this->cookie_configs['name']]);

        $allowedXClickValues = array("allow");
        $xClick = $this->banner_configs_object->nsc_bar_get_cookie_setting("closeXClickStatus", "default");
        if (!empty($xClick) && $xClick !== "default") {
            $allowedXClickValues = array($xClick);
        }

        $allowedInfoClickValues = array("dismiss");
        $infoClick = $this->banner_configs_object->nsc_bar_get_cookie_setting("infoClickStatus", "default");
        if (!empty($infoClick) && $infoClick !== "default") {
            $allowedInfoClickValues = array($infoClick);
        }

        if ($this->cookie_configs['compliance_type'] == "info" && !in_array($current_cookie_value, array_merge($allowedInfoClickValues, $allowedXClickValues))) {
            $this->nsc_bar_delete_cookie();
        }

        if (in_array($this->cookie_configs['compliance_type'], array("opt-in", "opt-out")) && !in_array($current_cookie_value, array_merge(array("deny", "allow"), $allowedXClickValues))) {
            $this->nsc_bar_delete_cookie();
        }

        if ($this->cookie_configs['compliance_type'] == "detailed" && !in_array($current_cookie_value, array_merge(array("savesettings", "detailed", "deny"), $allowedXClickValues))) {
            $this->nsc_bar_delete_cookie();
        }

        if ($this->cookie_configs['compliance_type'] == "detailedRev" && !in_array($current_cookie_value, array_merge(array("savesettings", "allowall", "deny"), $allowedXClickValues))) {
            $this->nsc_bar_delete_cookie();
        }

        if ($this->cookie_configs['compliance_type'] == "detailedRevDeny" && !in_array($current_cookie_value, array_merge(array("savesettings", "allowall", "deny"), $allowedXClickValues))) {
            $this->nsc_bar_delete_cookie();
        }

        if ($this->plugin_configs->nsc_bar_get_option('ask_until_acceptance') == "1" && $current_cookie_value == "deny") {
            $this->nsc_bar_delete_cookie();
        }

        if ($this->plugin_configs->nsc_bar_get_option('ask_until_acceptance') == "1" && in_array($this->cookie_configs['compliance_type'], array("detailed", "detailedRev", "detailedRevDeny"))) {
            foreach ($this->cookie_configs['cookietypes'] as $cookietype_configs) {
                $differentiatedCookieName = $this->cookie_configs['name'] . "_" . $cookietype_configs["cookie_suffix"];
                if (isset($_COOKIE[$differentiatedCookieName]) && $_COOKIE[$differentiatedCookieName] != "allow") {
                    $this->nsc_bar_delete_cookie(false);
                }
            }
        }
    }

    public function nsc_bar_delete_cookie($delete_detailed = true)
    {

        if (is_string($this->cookie_configs['name']) && isset($_COOKIE[$this->cookie_configs['name']])) {
            unset($_COOKIE[$this->cookie_configs['name']]);
            $this->set_cookie($this->cookie_configs['name'], "emptyvalue", time() - 3600, $this->cookie_configs['path'], $this->cookie_configs['domain']);
            //delete itp saver cookie as well, if cookie is deleted
            if (isset($_COOKIE["nsc_bar_cs_done"])) {
                unset($_COOKIE["nsc_bar_cs_done"]);
                $this->set_cookie("nsc_bar_cs_done", "emptyvalue", time() - 3600, $this->cookie_configs['path'], $this->cookie_configs['domain']);
            }
        }

        if ($delete_detailed === false) {
            return;
        }

        if (!empty($this->cookie_configs['cookietypes'])) {
            foreach ($this->cookie_configs['cookietypes'] as $cookietype_configs) {
                $differentiatedCookieName = $this->cookie_configs['name'] . "_" . $cookietype_configs["cookie_suffix"];

                if (isset($_COOKIE[$differentiatedCookieName])) {
                    unset($_COOKIE[$differentiatedCookieName]);
                    $this->set_cookie($differentiatedCookieName, "emptyvalue", time() - 3600, $this->cookie_configs['path'], $this->cookie_configs['domain']);
                }
            }
        }
    }

    /*
    will return the expirydate.
    if the cookie was already handled by the saver it will return the original date stored in a cookie
    if the cookie was never handled by this script it will create a fresh one.
     */

    private function get_expiry_date($cookiename, $expiryDays)
    {
        $expiryDate = time() + 60 * 60 * 24 * $expiryDays;

        if (!isset($_COOKIE["nsc_bar_cs_done"])) {
            return $expiryDate;
        }

        $input_validation = new nsc_bar_input_validation();
        $expiryCookie = $input_validation->nsc_bar_sanitize_input($_COOKIE["nsc_bar_cs_done"]);
        $done_cookie_values = explode("---_---", $expiryCookie);

        if (count($done_cookie_values) != 2) {
            return $expiryDate;
        }

        if ($done_cookie_values[0] != $cookiename) {
            return $expiryDate;
        }

        return $done_cookie_values[1];
    }

    private function get_banner_configs()
    {
        if (empty($this->banner_configs_object)) {
            $this->banner_configs_object = new nsc_bar_banner_configs();
        }
        return $this->banner_configs_object;
    }

    private function get_plugin_configs()
    {
        if (empty($this->plugin_configs)) {
            $this->plugin_configs = new nsc_bar_plugin_configs;
        }
        $this->newBannerEnabled = $this->plugin_configs->nsc_bar_new_banner_enabled();
        return $this->plugin_configs;
    }

    private function get_cookie_configs()
    {
        if (empty($this->cookie_configs)) {
            $this->cookie_configs['name'] = $this->plugin_configs->getConsentCookieName();
            $this->cookie_configs['path'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("cookie_path", "/");
            $this->cookie_configs['domain'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("cookie_domain", $this->plugin_configs->nsc_bar_return_settings_field_default_value("cookie_domain"));
            $this->cookie_configs['expirydays'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("cookie_expiryDays", $this->plugin_configs->nsc_bar_return_settings_field_default_value("cookie_expiryDays"));
            $this->cookie_configs['secure'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("cookie_secure", false);
            $this->cookie_configs['cookietypes'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("cookietypes", array());
            $this->cookie_configs['compliance_type'] = $this->banner_configs_object->nsc_bar_get_cookie_setting("type", $this->plugin_configs->nsc_bar_return_settings_field_default_value("type"));

            if (empty($this->cookie_configs['domain'])) {
                $this->cookie_configs['domain'] = "";
            }
        }
        return $this->cookie_configs;
    }

    private function set_cookie($name, $value, $expire, $path, $domain, $secure = false, $httpOnly = false)
    {

        $cleanDomain = empty($domain) ? "." . parse_url(home_url(), PHP_URL_HOST) : "." . ltrim($domain, ".");
        if ($cleanDomain === ".") {
            $cleanDomain = "";
        }

        if (version_compare(phpversion(), '7.3', '<')) {
            setcookie($name, $value, $expire, $path . '; samesite=lax', $cleanDomain, $secure, $httpOnly);
            return;
        }

        /**
         * @disregard P1006
         */
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $cleanDomain,
            'samesite' => 'lax',
            'secure' => $secure,
            'httponly' => $httpOnly,
        ]);
    }
}
