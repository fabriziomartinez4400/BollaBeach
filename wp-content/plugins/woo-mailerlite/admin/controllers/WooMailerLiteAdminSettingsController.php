<?php

class WooMailerLiteAdminSettingsController extends WooMailerLiteController
{
    public function updateIgnoreProductsBulkAndQuickEdit($productId, $post)
    {
        $product = $this->resolveResource(WooMailerLiteProduct::class, $productId);
        if (!$product) {
            return $productId;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $productId;
        }

        if ('product' !== $post->post_type) {
            return $productId;
        }

        $ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);
        if ($this->request('ml_ignore_product')) {
            $product->ignored = true;
            $ignoredProducts[$product->resource_id] = $post->post_title;
        } else {
            $product->ignored = false;

            unset($ignoredProducts[$product->resource_id]);
        }
        $product->save();

        WooMailerLiteOptions::update('ignored_products', $ignoredProducts);
        if ($product->isDeleted()) {
            return $this->deleteProduct($product);
        }

        if ($this->apiClient()->isClassic()) {
            $this->apiClient()->setConsumerData([
                'store'           => home_url(),
                'currency'        => get_option('woocommerce_currency'),
                'ignore_list'     => array_map('strval', array_keys(WooMailerLiteOptions::get('ignored_products', []))),
                'consumer_key'    => WooMailerLiteOptions::get('consumerKey', null),
                'consumer_secret' => WooMailerLiteOptions::get('consumerSecret', null),
                'group_id'        => WooMailerLiteOptions::get('group.id'),
                'resubscribe'     => WooMailerLiteOptions::get('settings.resubscribe'),
                'create_segments' => false
            ]);
        } else {
            $product->exclude_from_automations = $product->ignored;
            $this->apiClient()->syncProduct(WooMailerLiteOptions::get('shopId'), $product->toArray(), true);
        }
        return true;
    }

    public function updateIgnoreProductList($productId)
    {
        $title = $this->request['post_title'];
        $product = $this->resolveResource(WooMailerLiteProduct::class, $productId);
        $ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);

        if ($this->requestHas('ml_ignore_product')) {

            $ignoredProducts[$productId] = $title;

        } else {
            unset($ignoredProducts[$productId]);
        }
        WooMailerLiteOptions::update('ignored_products', $ignoredProducts);
        if ($product) {
            $product->ignored = $this->requestHas('ml_ignore_product');
            $product->save();
        }
        return true;
    }

    public function updateProduct($productId)
    {
        if (did_action('woocommerce_update_product') === 1) {

            if (defined('ICL_SITEPRESS_VERSION') && isset($_POST['icl_translation_of']) && !empty($_POST['icl_translation_of'])) {
                return true;
            }

            $product = $this->resolveResource(WooMailerLiteProduct::class, $productId);
            $shopId = WooMailerLiteOptions::get('shopId');
            if ($product) {
                $product->exclude_from_automations = $product->ignored ? 1 : 0;
                $product->categories = $product->category_ids;
                $response = $this->apiClient()->syncProduct($shopId, $product->toArray(), true);
                if ($response->success) {
                    $product->tracked = true;
                    $product->save();
                } else {
                    WooMailerLiteLog()->error('product:sync:failed', [
                        'product_id' => $product->resource_id,
                        'response' => $response
                    ]);
                }

                $ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);
                if ($this->request('ml_ignore_product')) {
                    $product->ignored = true;
                    $ignoredProducts[$product->resource_id] = $product->name;
                } else {
                    $product->ignored = false;

                    unset($ignoredProducts[$product->resource_id]);
                }
                $product->save();

                WooMailerLiteOptions::update('ignored_products', $ignoredProducts);
                if ($this->apiClient()->isClassic()) {
                    $this->apiClient()->setConsumerData([
                        'store'           => home_url(),
                        'currency'        => get_option('woocommerce_currency'),
                        'ignore_list'     => array_map('strval', array_keys(WooMailerLiteOptions::get('ignored_products', []))),
                        'consumer_key'    => WooMailerLiteOptions::get('consumerKey', null),
                        'consumer_secret' => WooMailerLiteOptions::get('consumerSecret', null),
                        'group_id'        => WooMailerLiteOptions::get('group.id'),
                        'resubscribe'     => WooMailerLiteOptions::get('settings.resubscribe'),
                        'create_segments' => false
                    ]);
                } else {
                    $product->exclude_from_automations = $product->ignored;
                    $this->apiClient()->syncProduct(WooMailerLiteOptions::get('shopId'), $product->toArray(), true);
                }
            } else {
                WooMailerLiteLog()->error('product:update:not_found', [
                    'product_id' => $productId
                ]);
            }

            return true;
        }
    }

    public function updateCategory($categoryId)
    {
        if ((did_action('created_product_cat') === 1) || (did_action('edited_product_cat') === 1)) {
            $category = $this->resolveResource(WooMailerLiteCategory::class, $categoryId);
            if ($category) {
                $response = $this->apiClient()->syncCategory($category->toArray());
                if ($response->success) {
                    $category->tracked = true;
                    $category->save();
                }
            }
        }
        return true;
    }

    public function deleteCategory($categoryId)
    {
        if ((did_action('delete_product_cat') === 1)) {
            $this->apiClient()->deleteCategory($categoryId);
        }
        return true;
    }
    
    private function deleteProduct($product)
    {
        $response = $this->apiClient()->deleteProduct($product->resource_id);
        if ($response->success) {
            return true;
        }

        WooMailerLiteLog()->error('product:delete:failed', [
            'product_id' => $product->resource_id,
            'error' => $response->message ?? 'Unknown error'
        ]);

        return false;
    }

    public function saveSettings()
    {

        $this->authorize()->validate([
            'settings.group' => ['required', 'int'],
            'settings.popUps' => ['sometimes', 'int'],
            'settings.syncFields' => ['required', 'array']
        ]);

        $currentStatus = $this->apiClient()->getDoubleOptin();
        $currentStatus->data->double_optin = $currentStatus->data->double_optin ?? $currentStatus->data->enabled ?? false;

        if ($this->request('settings.doubleOptIn')) {
            if ($currentStatus->success) {
                if ($currentStatus->data->double_optin != $this->request('settings.doubleOptIn')) {
                    $this->apiClient()->setDoubleOptin($this->request('settings.doubleOptIn'));
                }
            }
        } else {
            if ($currentStatus->success) {
                if ($currentStatus->data->double_optin) {
                    $this->apiClient()->setDoubleOptin(false);
                }
            }
        }
        if (!WooMailerLiteOptions::get('group') || (!empty(WooMailerLiteOptions::get('group', [])) && WooMailerLiteOptions::get('group')['id'] != $this->validated['settings.group'])) {
            $response = $this->apiClient()->getGroupById($this->validated['settings.group']);
            if (!$response->success) {
                return $this->response($response, $response->status);
            }
            WooMailerLiteOptions::update('group', ['id' => $response->data->id, 'name' => $response->data->name]);
        }
        $shopName = get_bloginfo('name');
        $shopName = !empty($shopName) ? $shopName : home_url();
        $shopId = WooMailerLiteOptions::get('shopId');
        $currency = get_option('woocommerce_currency');
        $store = home_url();
        if ($this->apiClient()->isReWrite()) {
            if ($shopId === false) {
                $shops = $this->apiClient()->getShops();
                if ($shops->success) {
                    foreach ($shops->data as $shop) {
                        if ($shop->url == home_url()) {
                            $shopId = $shop->id;
                            WooMailerLiteOptions::update('shopId', $shopId);
                            break;
                        }
                    }
                }
            }
            $data = [
                'name'               => $shopName,
                'url'                => $store,
                'currency'           => $currency,
                'platform'           => 'woocommerce',
                'group_id'           => $this->validated['settings.group'],
                'enable_popups'      => (bool)$this->request('settings.popUps'),
                'enable_resubscribe' => (bool)$this->request('settings.resubscribe'),
                'enabled'            => true,
                'access_data'        => '-'
            ];
        } else {
            $data = [
                'consumer_key'    => WooMailerLiteOptions::get('consumerKey', null),
                'consumer_secret' => WooMailerLiteOptions::get('consumerSecret', null),
                'store'           => $store,
                'currency'        => $currency,
                'group_id'        => $this->validated['settings.group'],
                'resubscribe'     => $this->request('settings.resubscribe') ?? 0,
                'ignore_list'     => $this->request('settings.ignoreList') ?? '',
                'create_segments' => $this->request('settings.createSegments') ?? ''
            ];
        }
        $response = $this->apiClient()->setConsumerData($data);
        if ($response->success) {
            WooMailerLiteOptions::updateMultiple([
                'shopId' => $response->data->id,
                'popupsEnabled' => $response->data->enable_popups ?? ($this->request('settings.popUps') ?? false),
                'syncFields' => $this->validated['settings.syncFields'],
                'settings' => $this->request('settings'),
            ]);
            if (!$response->data->group) {
                $response->data->group = WooMailerLiteOptions::get('group');
            }
        }
        $message = 'Settings saved successfully.';
        if (!$response->success) {
            $message = $response->message ?? 'Unable to set up the shop. Please try again.';
        }
        return $this->response($response, $response->status, $message);
    }

    public function resetIntegration()
    {        
        $this->authorize();
        $this->apiClient()->toggleShop(home_url(), 0);
        WooMailerLiteProductSyncResetJob::dispatchSync();
        WooMailerLiteOptions::deleteAll();
        WooMailerLiteMigration::rollback();
        WooMailerLiteCache::delete('table_check');
        return $this->response(['success' => true, 'message' => 'Integration settings reset'], 200);
    }

    public function downgradePlugin()
    {
        $this->authorize();
        
        $slug = 'woo-mailerlite';
        $version = '2.1.29';
        $zip_url = "https://downloads.wordpress.org/plugin/{$slug}.{$version}.zip";

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // Overwrite plugin files (no delete needed)
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->install($zip_url);

        if (is_wp_error($result)) {
            wp_die('Failed to install old version: ' . $result->get_error_message());
        }

        wp_redirect(admin_url('plugins.php?downgrade=success'));
        exit;
    }

    public function enableDebugMode()
    {        
        $this->authorize();
        WooMailerLiteOptions::update('debugMode', !WooMailerLiteOptions::get('debugMode'));
        return $this->response(['success' => true, 'message' => 'Debug mode enabled'], WooMailerLiteOptions::get('debugMode') ? 200 : 201);
    }
}
