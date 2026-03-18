<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_input_validation
{
    private $admin_error_obj;
    private $allowedHtml;

    public function __construct()
    {
        $this->admin_error_obj = new nsc_bar_admin_error;
        $this->allowedHtml = array(
            "strong" => array(),
            "i" => array(),
            "a" => array(
                "href" => array(),
                "id" => array(),
                "title" => array(),
                "target" => array(),
                "class" => array(),
            ),
            "div" => array(
                "class" => array(),
                "id" => array(),
            ),
            "span" => array(
                "class" => array(),
                "id" => array(),
            ),
            "p" => array(
                "class" => array(),
                "id" => array(),
            ),
            "br" => array(),
            "ul" => array(
                "class" => array(),
                "id" => array(),
            ),
            "ol" => array(
                "class" => array(),
                "id" => array(),
            ),
            "li" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h1" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h2" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h3" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h4" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h5" => array(
                "class" => array(),
                "id" => array(),
            ),
            "h6" => array(
                "class" => array(),
                "id" => array(),
            ),
            "hr" => array(
                "class" => array(),
                "id" => array(),
            )
        );
    }

    public function nsc_bar_validate_field_custom_save($tabfield, $input)
    {

        if (isset($tabfield->disabled) && $tabfield->disabled === true) {
            return $tabfield->pre_selected_value;
        }

        $extra_validation_value = $tabfield->extra_validation_name;
        $return = $this->nsc_bar_sanitize_input($input, $extra_validation_value);

        switch ($extra_validation_value) {
            case "nsc_bar_check_input_color_code":
                $return = $this->nsc_bar_check_input_color_code($return);
                break;
            case "nsc_bar_check_input_export_json_string":
                $return = $this->nsc_bar_check_input_export_json_string($return);
                break;
            case "nsc_bar_check_valid_json_string":
                $return = $this->nsc_bar_check_valid_json_string($return);
                break;
            case "nsc_bar_check_cookietypes":
                $return = $this->nsc_bar_check_cookietypes($return);
                break;
            case "nsc_bar_replace_doublequote_with_single":
                $return = $this->nsc_bar_replace_doublequote_with_single($return);
                break;
            case "nsc_bar_integer":
                $return = $this->nsc_bar_integer($return);
                break;
            case "nsc_bara_custom_services":
                $return = $this->nsc_bar_bara_custom_services($return);
                break;
            case "nsc_bar_link_input":
                $return = $this->nsc_bar_link_input($return);
                break;
            case "nsc_bar_text_only":
                $return = $this->nsc_bar_text_only($return);
                break;
            case "nsc_bar_text_number_only":
                $return = $this->nsc_bar_text_number_only($return);
                break;
            case "customConsentButtons":
                $return = $this->customConsentButtons($return);
                break;
        }
        $return = apply_filters('nsc_bar_filter_input_validation', $return, $extra_validation_value);
        return $return;
    }

    public function nsc_bar_sanitize_input($input, $validationRule = "")
    {
        $jsonRules = array("nsc_bar_check_valid_json_string", "nsc_bar_check_input_export_json_string", "nsc_bara_new_banner_config");

        $cleandValue = $input;
        if (in_array($validationRule, $jsonRules) === false) {
            $cleandValue = stripslashes($input);
        }
        // for backward compatibility
        if (getType($cleandValue) !== "string") {
            return sanitize_text_field($cleandValue);
        }

        // customized. Got from WP function _sanitize_text_fields

        $cleandValue = wp_check_invalid_utf8($cleandValue);

        if (in_array($validationRule, $jsonRules) === false) {
            $cleandValue = wp_kses($cleandValue, $this->allowedHtml);
        }
        $cleandValue = preg_replace('/[\r\n\t ]+/', ' ', $cleandValue);
        $cleandValue = trim($cleandValue);

        // Remove percent-encoded characters.
        $found = false;
        while (preg_match('/%[a-f0-9]{2}/i', $cleandValue, $match)) {
            $cleandValue = str_replace($match[0], '', $cleandValue);
            $found = true;
        }

        if ($found) {
            // Strip out the whitespace that may now exist after removing percent-encoded characters.
            $cleandValue = trim(preg_replace('/ +/', ' ', $cleandValue));
        }

        return $cleandValue;
    }

    public function nsc_bar_link_input($url)
    {
        if (!is_string($url)) {
            return null;
        }

        $url = trim($url);
        if ('' === $url) {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        if (empty($parts['scheme']) || empty($parts['host'])) {
            $parts['scheme'] = "https";
            $parts['host'] = "test.com";
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, array('http', 'https'), true)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to be missing http or https.");
            return null;
        }

        if (!function_exists('idn_to_ascii')) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url.");
                return null;
            }
            return trim($url);
        }

        $host_ascii = idn_to_ascii($parts['host'], 0, INTL_IDNA_VARIANT_UTS46);
        if ($host_ascii === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars.");
            return null;
        }

        $normalized_url = $scheme . '://' . $host_ascii;
        if (isset($parts['port'])) {
            $normalized_url .= ':' . $parts['port'];
        }

        if (isset($parts['path'])) {
            $normalized_url .= $this->encode_non_ascii($parts['path']);
        }

        if (isset($parts['query'])) {
            $normalized_url .= '?' . $this->encode_non_ascii($parts['query']);
        }

        if (isset($parts['fragment'])) {
            $normalized_url .= '#' . $this->encode_non_ascii($parts['fragment']);
        }
        if (filter_var($normalized_url, FILTER_VALIDATE_URL) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid url. Yours seems to contain invalid chars or something else went wrong.");
            return null;
        }
        return $url;
    }

    public function nsc_bar_text_number_only($input)
    {
        $forbidden = "/[^\w\-\.\ 0-9,%]/";
        $forbidden_chars = preg_match_all($forbidden, $input);

        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Text could not be saved. Please provide only word and number characters in this field, space, - and . are allowed, too.");
            return null;
        }

        return $input;
    }

    public function nsc_bar_text_only($input)
    {
        $forbidden = "/[^\w\-\.\ ,]/";
        $forbidden_chars = preg_match_all($forbidden, $input);

        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Text could not be saved. Please provide only word characters in this field, space, - and . are allowed, too.");
            return null;
        }

        return $input;
    }

    public function nsc_bar_bara_custom_services($input)
    {

        $testedJson = $this->nsc_bar_check_valid_json_string($input);
        if (empty($testedJson)) {
            return null;
        }

        if (class_exists("nsc_bara_input_validation")) {
            $bara_validation = new nsc_bara_input_validation;
            return $bara_validation->nsc_bara_custom_services($testedJson);
        }
        return null;
    }

    public function nsc_bar_integer($input)
    {
        $valid = preg_match("/^[0-9]*$/", $input);
        if (empty($valid) && $input != "") {
            $this->admin_error_obj->nsc_bar_set_admin_error("Number could not be saved. Please provide an integer. Your input: " . esc_html($input));
            return null;
        }
        return $input;
    }

    public function nsc_bar_check_input_color_code($input)
    {
        $forbidden = "/[^\w^,^\.^ ^%^(^)^#]/";
        $forbidden_chars = preg_match_all($forbidden, $input);
        if (empty($forbidden_chars) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide valid color value for the color field, like #ffffff or rgba(100,100,100,0.9)");
            return null;
        }
        return $input;
    }

    public function nsc_bar_replace_doublequote_with_single($input)
    {
        return str_replace(['"', "\""], "'", $input);
    }

    public function nsc_bar_check_valid_json_string($json_string)
    {
        if (is_numeric($json_string) === true || $json_string === true) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string. Seems you provided booleanlike data. Data was not saved.");
            return null;
        }

        if (is_string($json_string) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json STRING. Your provided a non-string. Data was not saved.");
            return null;
        }

        $tested_json_string = json_encode(json_decode($json_string), JSON_UNESCAPED_UNICODE);

        if (empty($tested_json_string) || $tested_json_string === "null") {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string. Data was not saved.");
            return null;
        }

        return $json_string;
    }

    public function customConsentButtons($input)
    {
        if (is_string($input) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a string for the order of buttons. Data was not saved.");
            return null;
        }

        $expectedEmptyString = str_replace(array("{{deny}}", "{{savesettings}}", "{{allowall}}", " ", ",", ";"), "", $input);
        if (empty($expectedEmptyString) === false) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide valid configuration for this field. Only {{deny}}, {{savesettings}} and {{allowall}} are allowed.");
            return null;
        }
        return str_replace(array(" ", ",", ";"), "", $input);
    }

    public function nsc_bar_check_cookietypes($input)
    {
        //should be an impossible case, because default settings have cookie types and the frontend js makes it impossible to delete all cookie types.
        if (empty($input)) {
            //$this->admin_error_obj->nsc_bar_set_admin_error("Please provide at least one cookie type.");
            //$this->admin_error_obj->nsc_bar_display_errors();
            //TODO: if all installation are >= v2.0 change this line to "return null" and uncomment lines above.
            $input = '[{"label": "Technical","checked": "checked","disabled":"disabled","cookie_suffix":"tech"}]';
        }

        $valid = $this->nsc_bar_check_valid_json_string($input);
        if (empty($valid)) {
            return null;
        }

        $arr_cookietypes = json_decode($valid, true);
        foreach ($arr_cookietypes as $arr_cookietype) {
            if (preg_match('/^[a-z_]+$/', $arr_cookietype["cookie_suffix"]) === 0) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Cookie suffix must be only lowercase letter and underscores.");
                return null;
            }
            if (strlen($arr_cookietype["cookie_suffix"]) > 10) {
                $this->admin_error_obj->nsc_bar_set_admin_error("Cookie suffix must only have ten characters.");
                return null;
            }
        }
        return $valid;
    }

    public function nsc_bar_check_input_export_json_string($input)
    {
        if ($input === "") {
            return "";
        }

        $valid = $this->nsc_bar_check_valid_json_string($input);
        if (empty($valid)) {
            return null;
        }

        $settings = json_decode($input);
        $valid = $this->nsc_bar_check_cookietypes(json_encode($settings->cookietypes, JSON_UNESCAPED_UNICODE));

        if (empty($valid)) {
            $this->admin_error_obj->nsc_bar_set_admin_error("Please provide a valid json string with valid cookie types.");
            return null;
        }
        return $input;
    }

    public function esc_array_for_js(array $array_to_escape)
    {
        $escapedArray = array();
        foreach ($array_to_escape as $key => $value) {
            $escKey = esc_js($key);
            if (!is_array($value)) {
                $escValue = esc_js($value);
                $escapedArray[$escKey] = $escValue;
            }

            if (is_array($value)) {
                foreach ($value as $key_of_v => $value_of_v) {
                    $escKey_v = esc_js($key_of_v);
                    $escValue_v = esc_js($value_of_v);
                    $escapedArray[$escKey][$escKey_v] = $escValue_v;
                }
            }
        }
        return $escapedArray;
    }

    public function escape_json_content(string $json_string)
    {
        $decoded_json = json_decode($json_string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        function escape_recursive($data, $allowedHtml)
        {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if ($key === "message" && is_string($value) === true) {
                        $data[$key] = wp_kses($value, $allowedHtml);
                        continue;
                    }
                    $data[$key] = escape_recursive($value, $allowedHtml);
                }
            } elseif (is_string($data)) {
                $data = stripslashes(esc_js($data));
            }
            return $data;
        }

        $escaped_json = escape_recursive($decoded_json, $this->allowedHtml);

        return json_encode($escaped_json, JSON_UNESCAPED_UNICODE);
    }

    public function return_errors_obj()
    {
        return $this->admin_error_obj;
    }

    public function nsc_bar_validate_addon()
    {
        if (defined('NSC_BARA_UPDATE_TRANSIENT_NAME') === false) {
            return;
        }

        if (defined('NSC_BARA_PLUGIN_VERSION') === false) {
            return;
        }


        $updateInfos = "";
        if (stripos(NSC_BARA_UPDATE_TRANSIENT_NAME, NSC_BARA_PLUGIN_VERSION) !== false) {
            $updateInfos = get_transient(NSC_BARA_UPDATE_TRANSIENT_NAME);
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->new_version) === false && version_compare(NSC_BARA_PLUGIN_VERSION, "3.8.0", '<=')) {
            $slug = "nsc_bar_version_too_outdated_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but your version " . esc_html(NSC_BARA_PLUGIN_VERSION) . " is very much outdated. Please update to the latest version " . esc_html($updateInfos->new_version) . ". Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working.";
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->update_message) === false) {
            $slug = "nsc_bar_no_valid_license_key_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but there seems to be a problem with your license key. Please double check your <a href=\"/wp-admin/options-general.php?page=nsc_bar-cookie-consent&tab=license&nsc_bara_language_selector=xx\">license key</a> or remove the addon. Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working in the future.";
        }

        if (empty(get_option("nsc_bar_license_key", "")) === true) {
            $slug = "nsc_bar_no_license_key_warning_dismiss";
            $version = 1;
            $message = "The Beautiful Cookie Banner Addon is activated but the license key is missing. Please enter a valid <a href=\"/wp-admin/options-general.php?page=nsc_bar-cookie-consent&tab=license&nsc_bara_language_selector=xx\">license key</a> to receive updates or remove the addon. Without updates, your WordPress site may become vulnerable to security risks and the banner might stop working in the future.";
        }

        if (!empty($updateInfos) && is_object($updateInfos) && empty($updateInfos->global_wp_message) === false) {
            $slug = "nsc_bar_global_remote_warning_dismiss";
            $version = md5($updateInfos->global_wp_message);
            $message = $updateInfos->global_wp_message;
        }

        if (empty($message)) {
            return;
        }

        if (empty($slug)) {
            return;
        }

        if (empty($version)) {
            return;
        }


        $admin_error = new nsc_bar_admin_error;
        $admin_error->nsc_bar_set_global_warning_message($message, $slug, $version);
        add_action('admin_notices', array($admin_error, 'nsc_bar_global_admin_warning'));
        add_action('network_admin_notices', array($admin_error, 'nsc_bar_global_admin_warning'));
        add_action('admin_post_' . $slug, array($admin_error, 'nsc_bar_handle_dismiss'));
    }

    private function encode_non_ascii($string)
    {
        return preg_replace_callback(
            '/[^\x00-\x7F]/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $string
        );
    }
}
