<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mailerlite.com
 * @since      3.0.0
 *
 * @package    WooMailerlite
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    WooMailerlite
 *
 */
class WooMailerLite {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WooMailerLiteLoader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * @var string
     */
    protected $environment = 'production';

    protected $is_configured;

    protected static $logging_config = null;

    public function __construct()
    {
        $this->loader = new WooMailerLiteLoader();
        $this->getCustomFields();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->setupWooMailerLite();
//        $this->handleUpgrade();
    }
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        // Add menu item
        function woo_ml_bulk_edit_quick_edit()
        {

            echo '<div class="inline-edit-group">';
            woocommerce_wp_checkbox(array(
                'id'          => 'ml_ignore_product',
                'label'       => __('MailerLite e-commerce automations', 'woo-mailerlite'),
                'description' => __('Ignore product', 'woo-mailerlite'),
                'value'       => '',
                'desc_tip'    => false
            ));
            echo '</div>';
        }

        $pluginAdmin = WooMailerLiteAdmin::instance();

        $this->loader->add_action('woocommerce_product_quick_edit_end', $pluginAdmin, 'ignoreProductBlock');
        $this->loader->add_action('woocommerce_product_bulk_edit_end', $pluginAdmin, 'ignoreProductBlock');
        $this->loader->add_action('manage_product_posts_custom_column', $pluginAdmin, 'populateIgnoreProductBlock', 99, 2);
        $this->loader->add_action('woocommerce_process_product_meta', WooMailerLiteAdminSettingsController::instance(), 'updateProduct');
        $this->loader->add_action('woocommerce_product_data_panels', $pluginAdmin, 'abss');
        $this->loader->add_action('woocommerce_update_product', WooMailerLiteAdminSettingsController::instance(), 'updateProduct', 10, 1);
        $this->loader->add_action('created_product_cat', WooMailerLiteAdminSettingsController::instance(), 'updateCategory', 10, 2);
        $this->loader->add_action('edited_product_cat', WooMailerLiteAdminSettingsController::instance(), 'updateCategory', 10, 2);
        $this->loader->add_action('delete_product_cat', WooMailerLiteAdminSettingsController::instance(), 'deleteCategory', 10, 2);


        $this->loader->add_filter('woocommerce_product_data_tabs', $pluginAdmin, 'woo_ml_product_data_tab');
        $this->loader->add_filter('woocommerce_product_data_store_cpt_get_products_query', $pluginAdmin, 'handleCustomProductQuery', 10, 2);
        $this->loader->add_filter('plugin_action_links_woo-mailerlite/woo-mailerlite.php', $pluginAdmin, 'addSettingsOptionInPluginList');
        $this->loader->add_action('woocommerce_product_bulk_and_quick_edit', WooMailerLiteAdminSettingsController::instance(), 'updateIgnoreProductsBulkAndQuickEdit', 10, 2);
        $this->loader->add_filter('script_loader_tag', $pluginAdmin, 'addModuleTypeScript', 10, 3);
        $this->loader->add_action('admin_enqueue_scripts', $pluginAdmin, 'enqueueScripts');
        $this->loader->add_action('admin_enqueue_scripts', $pluginAdmin, 'enqueueStyles');
        $this->loader->add_action('admin_menu', $pluginAdmin, 'addPluginAdminMenu', 71);
        $this->loader->add_action('wp_ajax_woo_mailerlite_handle_connect_account', WooMailerLiteAdminWizardController::instance(), 'handleConnectAccount');
        $this->loader->add_action('wp_ajax_woo_mailerlite_get_groups', WooMailerLiteAdminWizardController::instance(), 'getGroups');
        $this->loader->add_action('wp_ajax_woo_mailerlite_shop_setup', WooMailerLiteAdminWizardController::instance(), 'shopSetup');
        $this->loader->add_action('wp_ajax_woo_mailerlite_create_group', WooMailerLiteAdminGroupController::instance(), 'createGroup');
        $this->loader->add_action('wp_ajax_woo_mailerlite_sync_handler', WooMailerLiteAdminSyncController::instance(), 'sync');
        $this->loader->add_action('wp_ajax_woo_mailerlite_reset_sync_handler', WooMailerLiteAdminSyncController::instance(), 'resetSync');
        $this->loader->add_action('wp_ajax_handle_save_settings', WooMailerLiteAdminSettingsController::instance(), 'saveSettings');
        $this->loader->add_action('wp_ajax_woo_mailerlite_reset_integration_settings', WooMailerLiteAdminSettingsController::instance(), 'resetIntegration');
        $this->loader->add_action('wp_ajax_handle_debug_log', WooMailerLiteAdminWizardController::instance(), 'getDebugLogs');
        $this->loader->add_action('wp_ajax_woo_mailerlite_downgrade_plugin', WooMailerLiteAdminSettingsController::instance(), 'downgradePlugin');
        $this->loader->add_action('wp_ajax_woo_mailerlite_enable_debug_mode', WooMailerLiteAdminSettingsController::instance(), 'enableDebugMode');
        $this->loader->add_action('add_meta_boxes', WooMailerLiteAdminMetaBoxController::instance(), 'addMetaBoxes');
        $this->asyncJobHandler();
    }

    public function asyncJobHandler()
    {
        if (!WooMailerLiteCache::get('table_check')) {
            WooMailerLiteMigration::migrate();
            WooMailerLiteCache::set('table_check', true, 86400);
        }
        $jobsDirectory = __DIR__ . '/./jobs';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($jobsDirectory));
        foreach ($iterator as $job) {
            if ($job->isFile() && $job->getExtension() === 'php') {
                $class = pathinfo($job->getFilename(), PATHINFO_FILENAME);
                if ($class === 'WooMailerLiteAbstractJob') {
                    continue;
                }
                if (class_exists($class) && method_exists($class, 'getInstance')) {
                    $this->loader->add_action($class, $class::getInstance(), 'runSafely');
                }
            }
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $pluginPublicInstance = WooMailerLitePublic::instance();

        $this->loader->add_action('wp_enqueue_scripts', $pluginPublicInstance, 'enqueueScripts');

        $checkout_position      = WooMailerLiteOptions::get('settings.selectedCheckoutPosition', 'checkout_billing');
        $checkout_position_hook = 'woocommerce_' . $checkout_position;

        $this->loader->add_action($checkout_position_hook, WooMailerLitePluginController::instance(), 'addMlSubscribeCheckbox', PHP_INT_MAX);
        $this->loader->add_action('init', WooMailerLitePluginController::instance(), 'reloadCheckout');
        $this->loader->add_filter('woocommerce_form_field', $pluginPublicInstance, 'removeOptionalFromMlCheckbox', 10, 4);
        $this->loader->add_filter('woocommerce_checkout_fields', WooMailerLitePluginController::instance(), 'addBillingCheckoutFields', PHP_INT_MAX);
    }

    private function setupWooMailerLite()
    {
        $service = WooMailerLiteService::instance();
        $this->loader->add_filter('woocommerce_update_cart_action_cart_updated', $service, 'handleCartUpdated');
        $this->loader->add_action('woocommerce_cart_item_set_quantity', $service, 'handleCartUpdated');
        $this->loader->add_action('woocommerce_add_to_cart', $service, 'handleCartUpdated');
        $this->loader->add_action('woocommerce_cart_item_removed', $service, 'handleCartUpdated');
        $this->loader->add_action('woocommerce_order_status_changed', WooMailerLiteOrderController::instance(), 'handleOrderStatusChanged');
        $this->loader->add_action('woocommerce_saved_order_items', WooMailerLiteOrderController::instance(), 'handleOrderStatusChanged');
        $this->loader->add_action('woocommerce_order_status_completed', WooMailerLiteOrderController::instance(), 'handleOrderStatusChanged');
        $this->loader->add_action('wp_ajax_woo_mailerlite_set_cart_email', $service, 'setCartEmail');
        $this->loader->add_action('wp_ajax_nopriv_woo_mailerlite_set_cart_email', $service, 'setCartEmail');
    }

    public function getCustomFields()
    {
        if (!WooMailerLiteOptions::get('apiKey')) {
            return false;
        }
        $client = WooMailerLiteApi::client();
        $fieldsOnApp = get_transient('mailerlite_custom_fields');
        if ($fieldsOnApp === false) {
            $fieldsOnApp = $client->getFields();
            set_transient('mailerlite_custom_fields', $fieldsOnApp, 24 * HOUR_IN_SECONDS);
        } else {
            return true;
        }
        $fields = $this->getPluginFields();

        try {
            if ($fieldsOnApp->success && !empty($fieldsOnApp->data)) {

                foreach ($fieldsOnApp->data as $appField) {
                    if (isset($appField->key) && isset($fields[$appField->key])) {
                        unset($fields[$appField->key]);
                    }
                }

                if (sizeof($fields) > 0) {
                    foreach ($fields as $field) {
                        $tempName = false;

                        if (isset($field['key'])) {
                            $tempName = $field['key'] . ' ' . $field['title'];
                        }
                        $data = [
                            'title' => $tempName ?: $field['title'],
                            'type' => $field['type']
                        ];
                        if ($client->apiType === WooMailerLiteApi::REWRITE_API) {
                            $data['name'] = $data['title'];
                            unset($data['title']);
                        }
                            $fieldAdded = $client->createField($data);
                            if (isset($fieldAdded->data->id)) {
                                if ($client->apiType === WooMailerLiteApi::REWRITE_API) {

                                    $client->updateField($fieldAdded->data->id, [
                                        'name' => $field['name'] . ' ' . $field['title']
                                    ]);
                                }
                                return $fieldAdded;
                            } else {
                                return false;
                            }
                        }

                    }
                }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function getPluginFields()
    {
        if (WooMailerLiteApi::client()->apiType === WooMailerLiteApi::CLASSIC_API) {

            return [
                'woo_orders_count'  => [
                    'title' => 'Woo Orders Count',
                    'type'  => 'NUMBER'
                ],
                'woo_total_spent'   => [
                    'title' => 'Woo Total Spent',
                    'type'  => 'NUMBER'
                ],
                'woo_last_order'    => [
                    'title' => 'Woo Last Order',
                    'type'  => 'DATE'
                ],
                'woo_last_order_id' => [
                    'title' => 'Woo Last Order ID',
                    'type'  => 'NUMBER'
                ],
            ];
        } else {

            $shopUrl = home_url();
            $shopKey = preg_replace('/[^A-Za-z0-9 ]/', '', $shopUrl);

            $shop_name = get_bloginfo('name');

            if (empty($shop_name)) {
                $shop_name = $shopUrl;
            }

            return [
                $shopKey . '_total_spent'       => [
                    'key'   => $shopKey,
                    'name'  => $shop_name,
                    'title' => 'Total spent',
                    'type'  => 'number',
                ],
                $shopKey . '_orders_count'      => [
                    'key'   => $shopKey,
                    'name'  => $shop_name,
                    'title' => 'Orders count',
                    'type'  => 'number',
                ],
                $shopKey . '_accepts_marketing' => [
                    'key'   => $shopKey,
                    'name'  => $shop_name,
                    'title' => 'Accepts marketing',
                    'type'  => 'number',
                ],
            ];
        }
    }


    public function run()
    {
        $this->loader->run();
    }

    public function handleUpgrade()
    {
        if (!WooMailerLiteOptions::get('customTableCheck')) {
            WooMailerLiteMigration::customPrefixTablesMigrate();
        }
        if (get_option('woo_ml_key', false) && (get_option('woo_ml_wizard_setup', 0) == 2)) {
            $settings = get_option('woocommerce_mailerlite_settings', []);
            if (!(get_option('woo_ml_key') == WooMailerLiteOptions::get('apiKey'))) {
                return false;
            }
            if (!empty($settings)) {
                WooMailerLiteOptions::updateMultiple([
                    'apiKey' => get_option('woo_ml_key'),
                    'wizardStep' => 2,
                    'accountName' => get_option('woo_ml_account_name', ''),
                    'accountId' => get_option('account_id', false),
                    'accountSubdomain' => get_option('account_subdomain', ''),
                    'shopId' => get_option('woo_ml_shop_id'),
                    'woo_ml_shop_id' => get_option('woo_ml_shop_id'),
                    'popupsEnabled' => $settings['popups'] ?? false,
                    'syncFields' => $settings['sync_fields'] ?? [],
                    'enabled' => true,
                    'consumerKey' => $settings['consumer_key'] ?? '',
                    'consumerSecret' => $settings['consumer_secret'] ?? '',
                    'lastSyncedCustomer' => get_option('woo_ml_last_synced_guest_id', 0),
                    'settings' => [
                        'languageField' => false,
                        'subscribeOnCheckout' => ($settings['checkout'] ?? null) == 'yes',
                        'resubscribe' => ($settings['resubscribe'] ?? null) == 'yes',
                        'selectedCheckoutPosition' => $settings['checkout_position'] ?? 'checkout_billing',
                        'checkoutPreselect' => ($settings['checkout_preselect'] ?? null) == 'yes',
                        'checkoutHidden' => ($settings['checkout_hide'] ?? null) == 'yes',
                        'syncAfterCheckout' => ($settings['disable_checkout_sync'] ?? null) == 'yes',
                        'checkoutLabel' => $settings['checkout_label'] ?? 'Yes, I want to receive your newsletter.',
                        'doubleOptIn' => ($settings['double_optin'] ?? 'no') == 'yes',
                        'popUps' => $settings['popups'] ?? false,
                        'autoUpdatePlugin' => $settings['auto_update_plugin'] ?? false,
                        'group' => $settings['group'] ?? null,
                        'syncFields' => $settings['sync_fields'] ?? [],
                        'shopId' => get_option('woo_ml_shop_id'),
                        'woo_ml_shop_id' => get_option('woo_ml_shop_id'),
                    ]
                ]);
            }
            if (isset($settings['group'])) {
                $response = WooMailerLiteApi::client()->getGroupById($settings['group']);
                if (!$response->success) {
                    return false;
                }
                WooMailerLiteOptions::update('group', ['id' => $response->data->id, 'name' => $response->data->name]);
            }

            delete_option('woo_ml_key');
            delete_option('woo_ml_wizard_setup');
        }
        return true;
    }

}
