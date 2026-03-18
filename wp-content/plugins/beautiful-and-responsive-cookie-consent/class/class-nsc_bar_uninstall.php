<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_uninstaller
{

    public function nsc_bar_deleteOptions()
    {
        $plugin_configs = new nsc_bar_plugin_configs;
        $settings = $plugin_configs->nsc_bar_return_plugin_settings_without_db_settings();

        foreach ($settings->setting_page_fields->tabs as $tab) {
            foreach ($tab->tabfields as $fields) {
                $plugin_configs->nsc_bar_delete_option($fields->field_slug);
            }
        }
        delete_option(NSC_BAR_SLUG_DBVERSION);

        // delete options of add on if add on is not there
        if (class_exists("nsc_bara_addon_configs") === false) {
            $translated_settings = $this->nsc_bar_get_all_nsc_bar_settings();
            foreach ($translated_settings as $name) {
                delete_option($name);
            }
        }

        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s",
                $wpdb->esc_like('nsc_bar') . '%'
            )
        );
    }

    private function nsc_bar_get_all_nsc_bar_settings()
    {
        global $wpdb;
        $options = $wpdb->get_results("select * from $wpdb->options where option_name like 'nsc_bar_%'");
        $names = array();
        foreach ($options as $option) {
            $names[] = $option->option_name;
        }
        return $names;
    }
}
