<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_db_upgrader
{
    public function nsc_bar_do_update()
    {
        $db_version = $this->get_database_version();
        if ($db_version == NSC_BAR_PLUGIN_VERSION) {
            // no update necessary
            return;
        }

        $updated = $this->do_update_from_20_to_21($db_version);
        $updated = $this->do_update_from_u20($db_version);

        if ($updated === true) {
            $this->set_database_version();
        }

    }

    private function do_update_from_20_to_21($db_version)
    {
        if (version_compare($db_version, "2.1", ">=")) {
            return true;
        }

        $banner_configs = new nsc_bar_banner_configs;
        $config_array = $banner_configs->nsc_bar_get_banner_config_array();
        $save = false;
        $do_update = false;

        // not all 2.0 version have set a db version. but only 2.0 should have cookietypes
        if (isset($config_array["cookietypes"]) && empty($db_version)) {
            $db_version = "2.0";
        }

        if (isset($config_array["type"]) && $config_array["type"] == "detailed") {
            $do_update = true;
        }

        if (version_compare($db_version, "2.0", "=") == true && $do_update == true) {
            if (isset($config_array["content"]) && isset($config_array["content"]["allow"])) {
                $banner_configs->nsc_bar_update_banner_setting("content_savesettings", $config_array["content"]["allow"], "string");
                $save = true;
            }
        }

        if ($save) {
            return $banner_configs->nsc_bar_save_banner_settings();
        } else {
            return true;
        }
    }

    private function do_update_from_u20($db_version)
    {
        if (version_compare($db_version, "2.0", ">=")) {
            return true;
        }

        $banner_configs = new nsc_bar_banner_configs;
        $config_array = $banner_configs->nsc_bar_get_banner_config_array();
        $save = false;

        if ($config_array["type"] == "info" && !isset($config_array["revokable"])) {
            $banner_configs->nsc_bar_update_banner_setting("revokable", false, "string");
            $save = true;
        }

        if (isset($config_array["revokeBtn"]) && !isset($config_array["revokable"])) {
            $banner_configs->nsc_bar_update_banner_setting("revokable", false, "string");
            $save = true;
        }

        if (isset($config_array["revokeBtn"]) && strlen($config_array["revokeBtn"]) < 15) {
            $banner_configs->nsc_bar_remove_revokeBtn();
            $save = true;
        }

        if ($save) {
            return $banner_configs->nsc_bar_save_banner_settings();
        } else {
            return true;
        }

    }

    private function set_database_version()
    {
        update_option(NSC_BAR_SLUG_DBVERSION, NSC_BAR_PLUGIN_VERSION);
    }

    private function get_database_version()
    {
        return get_option(NSC_BAR_SLUG_DBVERSION, NSC_BAR_PLUGIN_VERSION);
    }
}
