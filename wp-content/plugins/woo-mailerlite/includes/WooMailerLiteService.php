<?php

class WooMailerLiteService
{
    /**
     * @var null|static $instance
     */
    protected static $instance = null;

    protected $apiClient;

    public function __construct()
    {
        $this->apiClient = WooMailerLiteApi::client();
    }

    public static function instance()
    {
        if (!empty(static::$instance)) {
            return static::$instance;
        }

        static::$instance = new static();
        return static::$instance;
    }

    /**
     * Triggered when the cart is created/updated
     * @return true
     */
    public function handleCartUpdated()
    {
        WooMailerLiteSession::set('woo_mailerlite_cart_hash', WC()->session->get_customer_id());
        $cart = WooMailerLiteCart::where('hash', WooMailerLiteSession::getMLCartHash())->first();
        $data = WooMailerLiteSession::cart();
        $data = json_decode($data, true);
        if (!isset($data['checkout_id'])) {
            $data['checkout_id'] = wp_generate_uuid4();
        }

        $data = json_encode($data);
        $subscribe = textInput('signup', 'false') === 'true' ? 1 : 0;

        if (!$cart) {
            WooMailerLiteCart::create([
                'hash' => WooMailerLiteSession::getMLCartHash(),
                'email' => WooMailerLiteSession::billingEmail(),
                'subscribe' => $subscribe,
                'data' => $data,
            ]);

        } else {
            $cart->update([
                'email' => WooMailerLiteSession::billingEmail(),
                'subscribe' => $subscribe,
                'data' => $data,
            ]);
        }
        return true;
    }

    /**
     * Triggered when the checkout form data is changed
     * @return void
     */
    public function setCartEmail()
    {
        check_ajax_referer('woo_mailerlite_cart_nonce', 'nonce');
        $email = emailInput('email');
        $subscribe = textInput('signup', 'false') === 'true' ? 1 : 0;
        WooMailerLiteSession::set('woo_mailerlite_checkbox', boolval($subscribe));
        // set the customer in session
        $data = WooMailerLiteSession::cart();
        $data = json_decode($data, true);
        if (!isset($data['checkout_id'])) {
            $data['checkout_id'] = wp_generate_uuid4();
        }

        if (!WooMailerLiteSession::getMLCartHash()) {
            $this->handleCartUpdated();
        }

        WooMailerLiteSession::set('woo_mailerlite_customer_data', ['customer' => $_POST, 'cart' => WC()->session->get( 'woo_mailerlite_cart_hash')]);
        // find the cart by cart id
        $cart = WooMailerLiteCart::where('hash', WooMailerLiteSession::getMLCartHash())->first();

        // update email in cart
        if ($cart) {
            $cart->update([
                'email' => $email,
                'subscribe' => $subscribe,
            ]);
        } else {
            // create cart if it doesn't exist
            WooMailerLiteCart::create([
                'hash' => WooMailerLiteSession::getMLCartHash(),
                'email' => $email,
                'subscribe' => $subscribe,
                'data' => $data,
            ]);
        }
        $this->sendCart();
    }

    public function sendCart()
    {
        if (WooMailerLiteOptions::get('settings.syncAfterCheckout')) {
            return true;
        }
        $customer = WooMailerLiteSession::getMLCustomer();
        $checkoutData = WooMailerLiteCheckoutDataService::getCheckoutData($customer['customer']['email'] ?? null);
        $customerQuery = WooMailerLiteCustomer::where('email', $customer['customer']['email'])->first();
        try {
            if (self::instance()->apiClient->isClassic()) {
                home_url();
                self::instance()->apiClient->sendCart(home_url(), $checkoutData);
                return true;
            }

            if (self::instance()->apiClient->isRewrite()) {
                $shop = WooMailerLiteOptions::get('shopId');

                if ($shop === false) {
                    return false;
                }

                $orderCustomer = [
                    'email'             => $checkoutData['email'],
                    'accepts_marketing' => $checkoutData['subscribe'] ?? false,
                    'create_subscriber' => $checkoutData['subscribe'] ?? false,
                ];


                if (isset($checkoutData['subscriber_fields'])) {
                    $orderCustomer['subscriber_fields'] = array_merge($orderCustomer['subscriber_fields'] ?? [],
                        $checkoutData['subscriber_fields']);
                }
                if ($customerQuery) {
                    $customerQuery->name = $customer['customer']['name'] ?? '';
                    $orderCustomer['subscriber_fields'] = array_merge($orderCustomer['subscriber_fields'] ?? [], $customerQuery->toArray());
                } else {
                    $orderCustomer['subscriber_fields'] = array_merge($orderCustomer['subscriber_fields'] ?? [],
                        $customer['customer'] ?? []);
                }

                $orderCustomer['subscriber_fields'] = array_filter(
                    $orderCustomer['subscriber_fields'],
                    function ($v, $k) {
                        return in_array($k, WooMailerLiteOptions::get("settings.syncFields"));
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (isset($checkoutData['language'])) {
                    $orderCustomer['subscriber_fields']['subscriber_language'] = $checkoutData['language'];
                    $orderCustomer['subscriber_fields']['language'] = $checkoutData['language'];
                }

                $orderCart = [
                    'resource_id'  => (string)$checkoutData['id'],
                    'checkout_url' => $checkoutData['abandoned_checkout_url'],
                    'items'        => []
                ];

                if (empty($checkoutData['line_items'])) {
                    self::$instance->apiClient->deleteOrder($checkoutData['id']);
                    return false;
                }

                foreach ($checkoutData['line_items'] as $item) {
                    $product = wc_get_product($item['product_id']);

                    $orderCart['items'][] = [
                        'product_resource_id' => (string)$item['product_id'],
                        'variant'             => $product->get_name(),
                        'quantity'            => (int)$item['quantity'],
                        'price'               => floatval($product->get_price('edit')),
                    ];
                }

                self::$instance->apiClient->syncOrder($shop, $checkoutData['id'], $orderCustomer, $orderCart, 'pending',
                    $checkoutData['total_price'], $checkoutData['created_at']);
            }
        } catch (\Exception $e) {
            WooMailerLiteLog()->error('sendCart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }

        return true;
    }
}
