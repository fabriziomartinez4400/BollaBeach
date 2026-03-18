<?php

if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_UNINSTALL_PLUGIN') === false) {
    echo "no way";
    exit;
}

define("NSC_BAR_PLUGIN_DIR", dirname(__FILE__));
define("NSC_BAR_PLUGIN_URL", plugin_dir_url(__FILE__));
define("NSC_BAR_SLUG_DBVERSION", "nsc_bar_db_version");

require dirname(__FILE__) . "/class/class-nsc_bar_plugin_configs.php";
require dirname(__FILE__) . "/class/class-nsc_bar_uninstall.php";

$uninstaller = new nsc_bar_uninstaller();
$uninstaller->nsc_bar_deleteOptions();
