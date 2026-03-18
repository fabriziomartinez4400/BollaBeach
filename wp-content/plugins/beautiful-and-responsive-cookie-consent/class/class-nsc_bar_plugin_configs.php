<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_plugin_configs
{
    private $settingsFile;
    private $settings_as_object;
    private $settings_as_object_without_db;
    private $active_tab;

    public function nsc_bar_get_option($option_slug)
    {
        $option_value = false;
        $settings_for_options = $this->nsc_bar_return_plugin_settings_without_db_settings();
        foreach ($settings_for_options->setting_page_fields->tabs as $tab) {
            foreach ($tab->tabfields as $field) {
                if (strpos($field->field_slug, $option_slug . "_") === 0) {
                    $option_slug = $field->field_slug;
                }
                if ($field->field_slug == $option_slug || strpos($option_slug, "bannersettings_json_") === 0) {
                    $option_value = get_option($settings_for_options->plugin_prefix . $option_slug, $field->pre_selected_value);
                    break;
                }
            }
        }

        $option_value = apply_filters('nsc_bar_filter_get_cookie_setting', $option_value, $option_slug);
        return $option_value;
    }

    public function nsc_bar_new_banner_enabled()
    {
        $newBannerEnabled = false;
        if (class_exists("nsc_bara_new_banner") === true) {
            $nb = new nsc_bara_new_banner;
            $newBannerEnabled = $nb->nsc_bara_new_banner_enabled();
        }
        return $newBannerEnabled;
    }

    public function nsc_bar_update_option($option_name, $option_value, $autoLoad = true)
    {
        $settings_for_options = $this->nsc_bar_return_plugin_settings_without_db_settings();
        $option_name_with_prefix = $settings_for_options->plugin_prefix . $option_name;
        return update_option($option_name_with_prefix, $option_value, $autoLoad);
    }

    public function nsc_bar_delete_option($option_name)
    {
        $settings_for_options = $this->nsc_bar_return_plugin_settings_without_db_settings();
        $option_name_with_prefix = $settings_for_options->plugin_prefix . $option_name;
        delete_option($option_name_with_prefix);
    }

    public function nsc_bar_return_plugin_settings()
    {
        if (empty($this->settings_as_object)) {
            $this->settings_as_object = $this->nsc_bar_return_plugin_settings_without_db_settings();
            $this->add_current_setting_values();
            $this->add_html_description_templates();
            $this->add_bannersettings_json();
        }
        return $this->settings_as_object;
    }

    public function nsc_bar_plugin_prefix()
    {
        $this->nsc_bar_return_plugin_settings_without_db_settings();
        return $this->settings_as_object_without_db->plugin_prefix;
    }

    public function nsc_bar_return_settings_field_default_value($searched_field_slug)
    {
        $settings_field = $this->return_settings_field($searched_field_slug);
        return $settings_field->pre_selected_value;
    }

    public function nsc_bar_replace_variables_in_config($varname, $replace_value)
    {
        $configs = $this->nsc_bar_return_plugin_settings();
        $configs_string = json_encode($configs, JSON_UNESCAPED_UNICODE);
        $configs_string = str_replace("{{" . $varname . "}}", $replace_value, $configs_string);
        $this->settings_as_object = json_decode($configs_string);
    }

    public function nsc_bar_return_plugin_settings_without_db_settings()
    {
        if (empty($this->settings_as_object_without_db)) {
            $this->settings_as_object_without_db = $this->set_settings_as_object();
            if (empty($this->settings_as_object_without_db)) {
                throw new Exception($this->settingsFile . " was not readable. Make sure it contains valid json.");
            }
        }
        return $this->settings_as_object_without_db;
    }

    public function nsc_bar_plugin_activation()
    {
        $firstActivation = get_option("nsc_bar_first_activation", false);
        if ($firstActivation === false) {
            update_option("nsc_bar_first_activation", time());
        }
    }

    public function getConsentCookieName()
    {
        if ($this->nsc_bar_new_banner_enabled()) {
            $cookieName = $this->nsc_bar_get_option("cookie_name_nb");
            if (is_array($cookieName)) {
                $cookieName = $this->getLastArrayEntry($cookieName);
            }
            return $cookieName;
        }

        $banner_configs = new nsc_bar_banner_configs;
        $cookieName = $banner_configs->nsc_bar_get_cookie_setting("cookie_name", $this->nsc_bar_return_settings_field_default_value("cookie_name"));
        if (is_array($cookieName)) {
            $cookieName = $this->getLastArrayEntry($cookieName);
        }
        return $cookieName;
    }

    private function getLastArrayEntry(array $array)
    {
        $firstEntry = reset($array);
        if (is_array($firstEntry)) {
            $this->getLastArrayEntry($firstEntry);
        } else {
            return $firstEntry;
        }
    }

    private function return_settings_field($searched_field_slug)
    {
        $this->nsc_bar_return_plugin_settings();
        foreach ($this->settings_as_object->setting_page_fields->tabs as $tab) {
            $number_of_fields = count($tab->tabfields);
            for ($i = 0; $i < $number_of_fields; $i++) {
                if ($tab->tabfields[$i]->field_slug == $searched_field_slug) {
                    return $tab->tabfields[$i];
                }
            }
        }
    }

    private function set_settings_as_object()
    {
        $this->settingsFile = NSC_BAR_PLUGIN_DIR . "/plugin-configs.json";
        $settings = file_get_contents($this->settingsFile);
        $settings = json_decode($settings);
        $settings = apply_filters('nsc_bar_plugin_settings_as_an_object', $settings);
        if (empty($settings)) {
            throw new Exception($this->settingsFile . " was not readable. Make sure it contains valid json.");
        }
        if (class_exists("nsc_bara_addon_configs")) {
            $bara = new nsc_bara_addon_configs;
            $settings = $bara->nsc_bara_add_addon_settings($settings);
        }

        return $settings;
    }

    private function get_active_tab()
    {
        $this->active_tab = "";
        if (isset($_GET["tab"])) {
            $this->active_tab = sanitize_text_field($_GET["tab"]);
        } else {
            $this->active_tab = $this->settings_as_object->setting_page_fields->tabs[0]->tab_slug;
        }
    }

    private function add_html_description_templates()
    {
        $number_of_tabs = count($this->settings_as_object->setting_page_fields->tabs);
        for ($t = 0; $t < $number_of_tabs; $t++) {
            $this->settings_as_object->setting_page_fields->tabs[$t]->tab_description = $this->get_tab_description_tipps_template($t, "tab_description");
            $this->settings_as_object->setting_page_fields->tabs[$t]->tab_tipps = $this->get_tab_description_tipps_template($t, "tab_tipps");
        }
    }

    private function get_tab_description_tipps_template($tab_index, $type)
    {
        // TODO: needed for premium add-on versions without this tab <= 1.2.1
        if (isset($this->settings_as_object->setting_page_fields->tabs[$tab_index]->$type) === false) {
            return "";
        }
        if (
            strpos($this->settings_as_object->setting_page_fields->tabs[$tab_index]->$type, ".html") === false ||
            !file_exists(NSC_BAR_PLUGIN_DIR . "/admin/tpl/" . $this->settings_as_object->setting_page_fields->tabs[$tab_index]->$type)
        ) {
            return $this->settings_as_object->setting_page_fields->tabs[$tab_index]->$type;
        }
        return file_get_contents(NSC_BAR_PLUGIN_DIR . "/admin/tpl/" . $this->settings_as_object->setting_page_fields->tabs[$tab_index]->$type);
    }

    // this fuctions gets the value saved in wordpress db using get_option
    // and adds it to the settings object in the pre_selected_value field.
    // if no value is set it sets the default value from settings file.
    private function add_current_setting_values()
    {
        $banner_configs = new nsc_bar_banner_configs;

        $this->get_active_tab();
        $this->settings_as_object->setting_page_fields->active_tab_slug = $this->active_tab;
        $number_of_tabs = count($this->settings_as_object->setting_page_fields->tabs);
        for ($t = 0; $t < $number_of_tabs; $t++) {
            $number_of_fields_in_this_tab = count($this->settings_as_object->setting_page_fields->tabs[$t]->tabfields);
            if ($this->active_tab == $this->settings_as_object->setting_page_fields->tabs[$t]->tab_slug) {
                $this->settings_as_object->setting_page_fields->tabs[$t]->active = true;
                $this->settings_as_object->setting_page_fields->active_tab_index = $t;
            }
            for ($f = 0; $f < $number_of_fields_in_this_tab; $f++) {
                $default_value = $this->settings_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->pre_selected_value;
                $field_slug_without_prefix = $this->settings_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->field_slug;
                if ($this->settings_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->save_in_db === false) {
                    $this->settings_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->pre_selected_value = $banner_configs->nsc_bar_get_cookie_setting($field_slug_without_prefix, $default_value);
                    continue;
                }

                $field_slug = $this->settings_as_object->plugin_prefix . $field_slug_without_prefix;
                $this->settings_as_object->setting_page_fields->tabs[$t]->tabfields[$f]->pre_selected_value = get_option($field_slug, $default_value);
            }
        }
    }

    private function add_bannersettings_json()
    {
        $banner_configs = new nsc_bar_banner_configs;

        foreach ($this->settings_as_object->setting_page_fields->tabs as $tabindex => $tab) {
            foreach ($tab->tabfields as $fieldindex => $field) {
                if ($field->field_slug === "bannersettings_json") {
                    //value from DB or default file:
                    $json_config = $banner_configs->nsc_bar_get_banner_config_string();
                    $this->settings_as_object->setting_page_fields->tabs[$tabindex]->tabfields[$fieldindex]->pre_selected_value = $json_config;
                    break (2);
                }
            }
        }
    }

    public function nsc_bar_isPremiumAddOnInstalled()
    {
        $installed = false;
        if (class_exists("nsc_bara_addon_configs") === true) {
            $installed = true;
        }
        return $installed;
    }
}
