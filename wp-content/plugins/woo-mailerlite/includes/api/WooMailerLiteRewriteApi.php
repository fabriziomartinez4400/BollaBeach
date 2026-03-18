<?php

class WooMailerLiteRewriteApi extends WooMailerLiteApi
{
    const BASE_URL = 'https://connect.mailerlite.com/api';

    public function __construct()
    {
        parent::__construct(self::BASE_URL);
    }

    public function validateKey($key)
    {
        $this->setApiKey($key);
        return $this->get('/account');
    }

    public function getGroups($args = [])
    {
        return $this->get('/groups', $args);
    }

    public function createGroup($group)
    {
        return $this->post('/groups', ['name' => $group]);
    }

    public function getShops()
    {
        return $this->get('/ecommerce/shops');
    }

    public function setConsumerData($data)
    {
        $shopId = WooMailerLiteOptions::get('shopId');
        if ($shopId) {
            return $this->put('/ecommerce/shops/' . $shopId, $data);
        }
        return $this->post('/ecommerce/shops', $data);
    }

    public function getGroupById($id)
    {
       return $this->get('/groups/'.$id);
    }

    public function importProducts($products)
    {

        return $this->post('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/products/import?with_resource_id&replace_categories',
            $products);
    }

    public function importCategories($categories)
    {
        return $this->post('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/categories/import?with_resource_id', $categories);
    }

    public function syncProduct($shopId, $data, $replaceCategories = false)
    {
        $response = $this->post('/ecommerce/shops/' . $shopId . '/products?with_resource_id',
            $data);
        if ($replaceCategories && isset($data['resource_id']) && isset($data['category_ids'])) {
            $response = $this->put('/ecommerce/shops/' . $shopId . '/products/' . $data['resource_id'] . '/categories/multiple?with_resource_id',
                [
                    'replace'    => true,
                    'categories' => $data['category_ids']
                ]);
        }
        return $response;
    }

    public function syncCustomers($customers)
    {
        return $this->post('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/customers/import?with_resource_id',
            $customers);
    }

    public function syncCategory($category)
    {
        return $this->post('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/categories?with_resource_id', $category);
    }

    public function deleteCategory($category)
    {
        return $this->delete('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/categories/' . $category . '?with_resource_id');
    }

    public function syncOrder($shopId, $orderId, $customer, $cart, $status, $totalPrice, $createdAt)
    {
        $orderStatus = 'pending';
        // if order status from woocommerce is wc-completed or wc-processing, set order status to completed
        if (in_array($status, ['wc-completed', 'wc-processing'])) {
            $status = 'completed';
        } else if (in_array($status, ['completed', 'processing'])) {
            $orderStatus = 'complete';
        }

        if ( ! isset($cart['resource_id'])) {
            $cart['resource_id'] = (string)$orderId;
        }

        if ( ! isset($cart['cart_total'])) {
            $cart['cart_total'] = $totalPrice;
        }

        $parameters = [
            'resource_id' => (string)$orderId,
            'customer'    => $customer,
            'cart'        => $cart,
            'status'      => $orderStatus,
            'total_price' => $totalPrice,
            'created_at'  => $createdAt
        ];
        // check if wpml is enabled and current language is not english
        if ( defined( 'ICL_SITEPRESS_VERSION' )) {
            foreach($parameters['cart']['items'] as &$product) {

                // send english product id
                $args = array('element_id' => $product['product_resource_id'], 'element_type' => 'post' );
                $productLang = apply_filters( 'wpml_element_language_details', null, $args );

                if ($productLang->source_language_code) {
                    $product['product_resource_id'] = (string)apply_filters( 'wpml_object_id', $product['product_resource_id'], 'post', FALSE, $productLang->source_language_code ) ?? $product['product_resource_id'];
                }
            }
        }
       return $this->post('/ecommerce/shops/' . $shopId . '/orders/queue-order-sync?with_resource_id',
            $parameters);
    }

    public function deleteOrder($orderId)
    {
        return $this->delete('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/orders/' . $orderId . '?with_resource_id');
    }

    public function getDoubleOptin()
    {
        return $this->get('/subscribe-settings/double-optin');
    }

    public function setDoubleOptin()
    {
        return $this->post('/subscribe-settings/double-optin/toggle');
    }

    public function getFields($params = [])
    {
        return $this->get('/fields', $params);
    }

    public function createField($params)
    {
        return $this->post('/fields', $params);
    }

    public function updateField($id, $params)
    {
        return $this->put('/fields/' . $id, $params);
    }

    public function ping()
    {
        return $this->get('/ecommerce/shops');
    }

    public function toggleShop($shop, $state)
    {

        return $this->put('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId'), [
            'name'    => get_bloginfo('name'),
            'url'     => $shop,
            'enabled' => $state
        ]);
    }

    public function deleteProduct($productId)
    {
        return $this->delete('/ecommerce/shops/' . WooMailerLiteOptions::get('shopId') . '/products/' . $productId . '?with_resource_id');
    }
}
