<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_save_form_fields
{
    private $plugin_settings;
    private $banner_configs_obj;
    private $plugin_configs;
    private $updated_fields;

    public function __construct()
    {
        $this->plugin_configs = new nsc_bar_plugin_configs();
        $this->plugin_settings = $this->plugin_configs->nsc_bar_return_plugin_settings_without_db_settings();
        $this->banner_configs_obj = new nsc_bar_banner_configs();
        $this->updated_fields = array();
    }

    public function nsc_bar_save_submitted_form_fields()
    {

        if (!isset($_GET["page"]) || $_GET["page"] !== $this->plugin_settings->plugin_slug) {
            return false;
        }

        if (isset($_POST['submit']) === false) {
            return false;
        }

        if (isset($_POST['action']) === false || $_POST['action'] !== "nsc_bar_cookie_settings_save") {
            return false;
        }

        $currentTab = isset($_GET['tab']) ? $_GET['tab'] : "general";
        $action = "save_cookie_settings_" . $this->plugin_settings->plugin_slug . "--" . $currentTab;
        check_admin_referer($action, 'nsc_bar_nonce');

        if (current_user_can(esc_attr($this->plugin_settings->settings_page_configs->capability)) === false) {
            return false;
        }

        $validate = new nsc_bar_input_validation;
        $updated = $this->save_settings(null, $validate);
        $saved_language_configs = $this->banner_configs_obj->nsc_bar_get_banner_config_array();
        $this->override_other_addon_configs($updated, $saved_language_configs, $validate);
        //needed for testing
        $validate->return_errors_obj()->nsc_bar_display_errors();

        return $saved_language_configs;
    }

    public function nsc_bar_save_settings_api($settings)
    {
        foreach ($settings as $setting) {
            $_POST[$setting["option_name"]] = $setting["option_value"];
        }

        $validate = new nsc_bar_input_validation;
        $this->save_settings(null, $validate);
        $validate->return_errors_obj()->nsc_bar_display_errors();
        return $this->updated_fields;
    }

    private function save_settings($addon_settings, $validate)
    {
        // just to make sure.
        if (current_user_can(esc_attr($this->plugin_settings->settings_page_configs->capability)) === false) {
            return false;
        }

        $tabs = $this->plugin_settings->setting_page_fields->tabs;
        $plugin_prefix = $this->plugin_settings->plugin_prefix;
        $banner_settings_updated = false;
        $configs_updated = false;

        foreach ($tabs as $tab_index => $tab) {
            foreach ($tab->tabfields as $tabfield_index => $tabfield) {
                $tabfield_slug = $plugin_prefix . $tabfield->field_slug;
                $manager_string = $this->value_save_manager($tabfield, $tabfield_slug, $addon_settings);
                if ($manager_string === "update_banner") {
                    $post_value = isset($_POST[$tabfield_slug]) ? $_POST[$tabfield_slug] : $_POST[$tabfield_slug . "_hidden"];
                    $new_value = $validate->nsc_bar_validate_field_custom_save($tabfield, $post_value);
                    if (!is_null($new_value)) {
                        $this->banner_configs_obj->nsc_bar_update_banner_setting($tabfield->field_slug, $new_value, $tabfield->save_as);
                        $banner_settings_updated = true;
                        $this->updated_fields[] = $tabfield_slug;
                    }
                }

                if ($manager_string === "update_wp_option") {
                    $post_value = isset($_POST[$tabfield_slug]) ? $_POST[$tabfield_slug] : $_POST[$tabfield_slug . "_hidden"];
                    $new_value = $validate->nsc_bar_validate_field_custom_save($tabfield, $post_value);
                    if (!is_null($new_value)) {
                        $autoLoad = isset($tabfield->auto_load) ? $tabfield->auto_load : true;
                        $this->plugin_configs->nsc_bar_update_option($tabfield->field_slug, $new_value, $autoLoad);
                        $configs_updated = true;
                        $this->updated_fields[] = $tabfield_slug;
                    }
                }
            }
        }

        if ($banner_settings_updated) {
            $this->banner_configs_obj->nsc_bar_save_banner_settings();
        }

        if ($banner_settings_updated === true || $configs_updated === true) {
            return true;
        }
        return false;
    }


    // TODO: to save different languages with API you need to do some changes here. Here is the magic what saves different json language string.
    // alternative: when doing an api call just adding the other languages to the plugin config.
    private function override_other_addon_configs($updated, $saved_language_configs, $validate)
    {
        if (class_exists("nsc_bara_save_form_fields_addon") !== true) {
            return false;
        }

        $bara = new nsc_bara_save_form_fields_addon;
        if ($bara->nsc_bara_must_i_save_other_languages($updated) !== true) {
            return false;
        }

        $bara_banner_configs = new nsc_bara_banner_configs_addon;
        $language_configs = $bara->nsc_bara_get_all_languages_configs($saved_language_configs);
        foreach ($language_configs as $lang => $language_config) {
            $addon_settings = $bara->nsc_bara_get_addon_settings("override", $lang);
            $this->banner_configs_obj->nsc_bar_set_banner_config_array($language_config);
            // to know where to save
            $this->banner_configs_obj->nsc_bar_set_banner_configs_slug($bara_banner_configs->nsc_bara_get_banner_settings_slug($addon_settings));
            $this->save_settings($addon_settings, $validate);
        }
    }

    private function value_save_manager($tabfield, $tabfield_slug, $addon_settings)
    {

        if ($tabfield->save_as === "display_only") {
            return "skip";
        }

        if (
            $tabfield->save_in_db === false &&
            $this->save_field_with_data_from_post($tabfield, $addon_settings) &&
            (isset($_POST[$tabfield_slug]) || isset($_POST[$tabfield_slug . "_hidden"]))
        ) {
            return "update_banner";
        }

        if (
            $tabfield->save_in_db === true &&
            $this->save_field_with_data_from_post($tabfield, $addon_settings) &&
            (isset($_POST[$tabfield_slug]) || isset($_POST[$tabfield_slug . "_hidden"]))
        ) {
            return "update_wp_option";
        }

        return "skip";
    }

    private function save_field_with_data_from_post($tabfield, $addon_settings)
    {
        if (class_exists("nsc_bara_save_form_fields_addon") === true) {
            $save_fields_addon = new nsc_bara_save_form_fields_addon();
            return $save_fields_addon->nsc_bara_save_field_with_data_from_post($tabfield, $addon_settings);
        }
        return true;
    }
}
