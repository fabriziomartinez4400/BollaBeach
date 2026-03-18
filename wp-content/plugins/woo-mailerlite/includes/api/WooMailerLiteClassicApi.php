<?php

class WooMailerLiteClassicApi extends WooMailerLiteApi
{
    const BASE_URL = 'https://api.mailerlite.com/api/v2';

    public function __construct()
    {
        parent::__construct(self::BASE_URL);
    }

    public function validateKey($key)
    {
        $this->setApiKey($key);
        return $this->get('/');
    }

    public function getGroups($params = [])
    {
        return $this->get('/groups', $params);
    }

    public function getGroupById($id)
    {
        return $this->get("/groups/$id");
    }

    public function setConsumerData($data)
    {
        return $this->post('/woocommerce/consumer_data', $data);
    }

    public function createGroup($group)
    {
        return $this->post('/groups', ['name' => $group]);
    }

    public function sendCart($shop, $cartData)
    {

        return $this->post('/woocommerce/save_cart', [
            'cart_data' => $cartData,
            'shop'      => $shop
        ]);
    }

    public function sendOrderProcessing($data)
    {
        return $this->post('/woocommerce/order_processing', ['data' => $data]);
    }

    public function getFields()
    {
        return $this->get('/fields');
    }

    public function ping()
    {
        return $this->get('/');
    }

    public function syncProduct()
    {
        return $this->successResponse();
    }

    public function syncCategory()
    {
        return $this->successResponse();
    }

    public function deleteProduct()
    {
        return $this->successResponse();
    }

    public function syncCustomers($data)
    {
       return $this->post('/woocommerce/sync_customer', $data);
    }

    public function importCategories()
    {
        return $this->successResponse();
    }

    public function importProducts()
    {
        return $this->successResponse();
    }

    public function createField($params)
    {
        return $this->post('/fields', $params);
    }

    public function getDoubleOptin()
    {
        return $this->get('/settings/double_optin');
    }

    public function setDoubleOptin($enable)
    {

        return $this->post('/settings/double_optin', ['enable' => $enable]);
    }

    public function syncOrder($shop, $orderData)
    {
        return $this->post('/woocommerce/alternative_save_order', [
            'order_data' => $orderData,
            'shop'       => $shop
        ]);
    }

    public function sendSubscriberData($data)
    {
        return $this->post('/woocommerce/save_subscriber', ['data' => $data]);
    }

    public function processOrder($data)
    {
        return $this->post('/woocommerce/order_processing', ['data' => $data]);
    }

    public function toggleShop($shop, $state = true)
    {
        return $this->post('/woocommerce/toggle_shop_connection', [
            'active_state' => $state,
            'shop'         => $shop
        ]);
    }

    public function searchSubscriber($email)
    {
        return $this->get('/subscribers/' . $email);
    }
}
