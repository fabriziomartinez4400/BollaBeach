<?php

class WooMailerLiteAdmin
{
    public static $instance;

    public static function instance()
    {
        if (!empty(static::$instance)) {
            return static::$instance;
        }
        static::$instance = new self();
        return static::$instance;
    }


    public function addPluginAdminMenu()
    {
        add_submenu_page('woocommerce', 'MailerLite', 'MailerLite', 'manage_options', 'mailerlite',
            [$this, 'wooMailerLiteSettingsPageCallback']);
    }

    public function enqueueScripts($hook)
    {
        if ($hook !== 'woocommerce_page_mailerlite') {
            return;
        }
        wp_enqueue_script('woo-mailerlite-vue-cdn', 'https://cdn.jsdelivr.net/npm/vue@3.5.13/dist/vue.global.prod.js', [], null, true);
        wp_localize_script('woo-mailerlite-admin', 'woo_mailerlite_admin_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'language' => get_locale()
        ));

        wp_enqueue_script('style2-script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        wp_enqueue_script('woo-mailerlite-admin', plugin_dir_url(__FILE__) . '../admin/assets/js/ml-app.js', ['jquery', 'woo-mailerlite-vue-cdn'], null, true);

    }

    public function enqueueStyles($hook) {
        if ($hook !== 'woocommerce_page_mailerlite') {
            return;
        }
        wp_enqueue_style('woo-mailerlite-admin-css', plugin_dir_url( __FILE__ ) . '../admin/assets/css/admin.css', false, WOO_MAILERLITE_VERSION);
        wp_enqueue_style('style2-style', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_style('style2-mailerlite-style', plugin_dir_url( __FILE__ ) . '../public/css/mailerlite-select2.css',false, WOO_MAILERLITE_VERSION);
    }

    public function wooMailerLiteSettingsPageCallback()
    {
       $falseApi = false;
//        if (!WooMailerLiteCache::get('valid_api')) {
//            $response = WooMailerLiteApi::client()->ping();
//            if ($response->status === 401) {
//                $falseApi = true;
////                WooMailerLiteOptions::deleteAll();
//            } else {
//                WooMailerLiteCache::set('valid_api', true, 86400);
//            }
//        }
        $untrackedResources = WooMailerLiteProduct::getUntrackedProductsCount() +  WooMailerLiteCategory::getUntrackedCategoriesCount() +  WooMailerLiteCustomer::getUntrackedCustomersCount();

        wp_localize_script('woo-mailerlite-vue-cdn', 'woo_mailerlite_admin_data', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'productsUrl' => esc_url(admin_url('edit.php?post_type=product')),
            'currentStep' =>  WooMailerLiteOptions::get('wizardStep', 0),
            'account' => [
                'isConnected' => false,
                'accountName' => WooMailerLiteOptions::get('accountName', ''),
                'platform' => WooMailerLiteApi::getApiType() ?? 1,
            ],
            'selectedGroup' => WooMailerLiteOptions::get('group', []),
            'woo_mailerlite_admin_nonce' => wp_create_nonce( 'woo_mailerlite_admin' ),
            'ignoredProducts' => (array)WooMailerLiteOptions::get('ignored_products', []),
            'sync' => [
                'lastCustomerSync' => 0,
                'newMLSync' => true,
                'syncInProgress' =>  WooMailerLiteCache::get('manual_sync', false),
                'totalUntrackedResources' => $untrackedResources,
                'totalTrackedResources' => 0
            ],
            'settings' => WooMailerLiteOptions::get('settings', []),
            'syncFields' => WooMailerLiteOptions::get('syncFields'),
            // Need better solution for async sync, adding true for now and force sync (works)
            'asyncSync' => WooMailerLiteCache::get('scheduled_jobs') && $untrackedResources,
            'falseApi' => $falseApi,
            'debugMode' => WooMailerLiteOptions::get('debugMode')
        ]);

        require_once __DIR__ . '/../views/mailerlite-app.php';
    }

    public function addModuleTypeScript($tag, $handle, $src)
    {
        if ('woo-mailerlite-admin' === $handle) {
            // modify the <script> tag to include type="module"
            $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
        }

        return $tag;
    }

    public function ignoreProductBlock()
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

    public function populateIgnoreProductBlock($column, $post_id)
    {
        $ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);

        switch ($column) {
            case 'name' :
                ?>
                <div class="hidden ml_ignore_product_inline" id="ml_ignore_product_inline_<?=intval($post_id) ?>">
                    <div id="_ml_ignore_product"><?php echo array_key_exists($post_id, $ignoredProducts) ? 'yes' : 'no' ?></div>
                </div>
                <?php

                break;
        }
    }

    public function abss()
    {
        global $post;
        ?>
        <div id="woo-ml-product-data" class="panel woocommerce_options_panel">
            <?php

            $ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);

            woocommerce_wp_checkbox(array(
                'id'          => 'ml_ignore_product',
                'label'       => __('Ignore Product', 'woo-mailerlite'),
                'description' => __('Select if you do not wish to trigger any e-commerce automations for this product',
                    'woo-mailerlite'),
                'value'       => array_key_exists($post->ID, $ignoredProducts) ? 'yes' : 'no',
                'desc_tip'    => true,
            ));
            ?>
        </div>
        <?php
    }

    public function woo_ml_product_data_tab($product_data_tabs)
    {
        $product_data_tabs['woo-ml'] = array(
            'label'    => __('MailerLite', 'woo-mailerlite'),
            'target'   => 'woo-ml-product-data',
            'class' => 'woo_mailerlite_product_tab',
            'priority' => 99
        );

        return $product_data_tabs;
    }

    public function handleCustomProductQuery( $query, $query_vars ) {

        if ( isset( $query_vars['metaQuery'] ) ) {
            $query['meta_query'][] = $query_vars['metaQuery'];
        }

        return $query;
    }

    public function addSettingsOptionInPluginList($links)
    {
        $settings_url = admin_url('admin.php?page=mailerlite');
        $settings_link = '<a href="' . esc_url($settings_url) . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
