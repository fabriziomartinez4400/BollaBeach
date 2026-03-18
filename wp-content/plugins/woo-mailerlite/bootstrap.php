<?php

// If this file is called directly, abort.
if (!defined( 'WPINC')) {
    die;
}

$woo_mailerlite_autoload = true;
spl_autoload_register(function($class) {
    $classes = array(
        // includes
        'WooMailerLiteActivator' => 'includes/WooMailerLiteActivator.php',
        'WooMailerLiteDeActivator' => 'includes/WooMailerLiteDeActivator.php',
        'WooMailerLiteLoader' => 'includes/WooMailerLiteLoader.php',
        'WooMailerLite' => 'includes/WooMailerLite.php',
        'WooMailerLiteService' => 'includes/WooMailerLiteService.php',
        'WooMailerLiteSession' => 'includes/WooMailerLiteSession.php',
        'WooMailerLiteCache' => 'includes/WooMailerLiteCache.php',

        // includes/controllers
        'WooMailerLiteAdminWizardController' => 'admin/controllers/WooMailerLiteAdminWizardController.php',
        'WooMailerLiteAdminGroupController' => 'admin/controllers/WooMailerLiteAdminGroupController.php',
        'WooMailerLiteAdminSettingsController' => 'admin/controllers/WooMailerLiteAdminSettingsController.php',
        'WooMailerLiteAdminSyncController' => 'admin/controllers/WooMailerLiteAdminSyncController.php',
        'WooMailerLiteAdminMetaBoxController' => 'admin/controllers/WooMailerLiteAdminMetaBoxController.php',
        'WooMailerLiteCheckoutBlocksController' => 'admin/controllers/WooMailerLiteCheckoutBlocksController.php',
        'WooMailerLitePluginController' => 'includes/controllers/WooMailerLitePluginController.php',
        'WooMailerLiteOrderController' => 'includes/controllers/WooMailerLiteOrderController.php',


        'WooMailerLiteCheckoutDataService' => 'includes/services/WooMailerLiteCheckoutDataService.php',

        // includes/api
        'WooMailerLiteApi' => 'includes/api/WooMailerLiteApi.php',
        'WooMailerLiteRewriteApi' => 'includes/api/WooMailerLiteRewriteApi.php',
        'WooMailerLiteClassicApi' => 'includes/api/WooMailerLiteClassicApi.php',
        'WooMailerLiteApiResponse' => 'includes/api/WooMailerLiteApiResponse.php',

        //includes/jobs
        'WooMailerLiteAbstractJob' => 'includes/jobs/WooMailerLiteAbstractJob.php',
        'WooMailerLiteProductSyncJob' => 'includes/jobs/WooMailerLiteProductSyncJob.php',
        'WooMailerLiteCategorySyncJob' => 'includes/jobs/WooMailerLiteCategorySyncJob.php',
        'WooMailerLiteCustomerSyncJob' => 'includes/jobs/WooMailerLiteCustomerSyncJob.php',
        'WooMailerLiteProductSyncResetJob' => 'includes/jobs/WooMailerLiteProductSyncResetJob.php',
        'WooMailerLiteCategorySyncResetJob' => 'includes/jobs/WooMailerLiteCategorySyncResetJob.php',
        'WooMailerLiteCustomerSyncResetJob' => 'includes/jobs/WooMailerLiteCustomerSyncResetJob.php',

        //includes/models
        'WooMailerLiteModel' => 'includes/models/WooMailerLiteModel.php',
        'WooMailerLiteCart' => 'includes/models/WooMailerLiteCart.php',
        'WooMailerLiteJob' => 'includes/models/WooMailerLiteJob.php',
        'WooMailerLiteProduct' => 'includes/models/WooMailerLiteProduct.php',
        'WooMailerLiteCategory' => 'includes/models/WooMailerLiteCategory.php',
        'WooMailerLiteCustomer' => 'includes/models/WooMailerLiteCustomer.php',

        // includes/controllers
        'WooMailerLiteController' => 'includes/controllers/WooMailerLiteController.php',

        // admin
        'WooMailerLiteAdmin' => 'admin/WooMailerLiteAdmin.php',
        'WooMailerLiteBlocksIntegration' => 'admin/Integrations/WooMailerLiteBlocksIntegration.php',


        // includes/common
        'WooMailerLiteDBConnection' => 'includes/common/WooMailerLiteDBConnection.php',
        'WooMailerLiteOptions' => 'includes/common/WooMailerLiteOptions.php',
        'WooMailerLiteResources' => 'includes/common/traits/WooMailerLiteResources.php',
        'WooMailerLiteCollection' => 'includes/common/WooMailerLiteCollection.php',
        'WooMailerLiteQueryBuilder' => 'includes/common/WooMailerLiteQueryBuilder.php',
        'WooMailerLiteEncryption' => 'includes/common/WooMailerLiteEncryption.php',

        // includes/migrations
        'WooMailerLiteMigration' => 'includes/migrations/WooMailerLiteMigration.php',

        // includes/public
        'WooMailerLitePublic' => 'public/WooMailerLitePublic.php',
    );

    // if the file exists, require it
    $path = plugin_dir_path( __FILE__ );
    if (array_key_exists($class, $classes) && file_exists($path.$classes[$class])) {
        require $path.$classes[$class];
    }
});

function activate_woo_mailerlite() {
    WooMailerLiteActivator::activate();
}

function deactivate_woo_mailerlite()
{
    WooMailerLiteDeActivator::deactivate();
}

function woo_mailerlite_check_woocommerce_is_installed() {
    if (!woo_mailerlit_check_woocommerce_plugin_status()) {
        // Deactivate the plugin
        deactivate_plugins(__FILE__);
        $error_message = __('The MailerLite – WooCommerce integration plugin requires the <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!', 'woocommerce');
        wp_die($error_message);
    }
    return true;
}

/**
* @return bool
*/
function woo_mailerlit_check_woocommerce_plugin_status()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')))) {
        return true;
    }
    $plugins = get_site_option( 'active_sitewide_plugins');
    return isset($plugins['woocommerce/woocommerce.php']);
}


function run_woo_mailerlite() {
    try {
        $plugin = new WooMailerLite();
        $plugin->run();
    } catch(Throwable $th) {
        WooMailerLiteLog()->error('run_woo_mailerlite', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
        return true;
    }

}

function db() {
    $class = new WooMailerLiteDBConnection();
    return $class->db();
}

function get_table(string $class) : ?string {
    $model_classes = [
        'WooMailerLiteJob' => 'jobs',
        'WooMailerLiteCart' => 'woo_mailerlite_carts',
        'WooMailerLiteProduct' => 'products',
    ];
    return $model_classes[$class] ?? null;
}
//register_activation_hook(__FILE__, 'schedule_product_sync');

function schedule_product_sync() {
    // Run an immediate sync when the plugin is activated
    as_schedule_single_action(time(), 'sync_products_action');

    // Then schedule recurring syncs
//    if (!as_next_scheduled_action('sync_products_action')) {
//        as_schedule_recurring_action(time() + 10, HOUR_IN_SECONDS, 'sync_products_action');
//    }
}

function cacheSet($key, $value, $expire = 0)
{
    wp_cache_set($key, $value, 'woo_mailerlite', $expire);
}

function cacheGet($key)
{
    return wp_cache_get($key, 'woo_mailerlite');
}

function WooMailerLiteLog() {
    return new class() {
        public function debug($message, $data = []) {
            $this->log('debug', $message, $data);
        }
        public function notice($message, $data = []) {
            $this->log('notice', $message, $data);
        }
        public function info($message, $data = []) {
            $this->log('notice', $message, $data);
        }

        public function error($message, $data = []) {
            $this->log('error', $message, $data);
        }

        protected function log($action, $message, $data = [])
        {
            if (is_array($data) && !empty($data)) {
                $message .= " :: ".wc_print_r($data, true);
            } else {
                $message .= " :: ".json_encode($data);
            }
            wc_get_logger()->$action("{$message}", ['source' => 'woo_mailerlite']);
        }
    };
}

function textInput($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $default;
}

function emailInput($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_email($_POST[$key]) : $default;
}
