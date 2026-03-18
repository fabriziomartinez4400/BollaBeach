<?php

class WooMailerLiteOrderController extends WooMailerLiteController
{

    public function handleOrderStatusChanged($orderId)
    {
        try {

            if (WooMailerLiteCache::get('order_sent:'.$orderId)) {
                return true;
            }
            $order = wc_get_order($orderId);
            if (!$order->get_billing_email()) {
                return true;
            }

            $this->persistLanguageToOrder($order);

            if (WooMailerLiteSession::getMLCartHash()) {
                $order->add_meta_data('_woo_ml_cart_hash', WooMailerLiteSession::getMLCartHash());
            }
            $customer = WooMailerLiteCustomer::selectAll(false)->where('email', $order->get_billing_email())->first();
            if (!$customer) {
                $customer = WooMailerLiteCustomer::selectAll(false)
                    ->getFromOrder()
                    ->where("email", $order->get_billing_email())
                    ->whereIn("status", ["wc-completed", "wc-processing", "wc-pending"])
                    ->first();
            }
            $cart = WooMailerLiteCart::where('email', $order->get_billing_email())->first();
            if (!$cart) {
                $cart = WooMailerLiteCart::where('hash', WooMailerLiteSession::getMLCartHash())
                ->withoutPrefix(function($query) use ($order) {
                    $query->orWhere('hash', $order->get_meta('_woo_ml_cart_hash'));
                })->first();
                if ($cart instanceof WooMailerLiteCart) {
                    $cart->update([
                        'email' => $order->get_billing_email(),
                    ]);
                }
            }
            if (!$cart && !$customer) {
                return true;
            }

            if ((isset($cart->subscribe) && $cart->subscribe) || WooMailerLiteOptions::get("settings.checkoutHidden")) {
                $order->add_meta_data('_woo_ml_subscribe', true);
            }

            $syncFields = WooMailerLiteOptions::get('syncFields', []);
            if (empty($syncFields)) {
                $syncFields = [
                    'name',
                    'email',
                    'company',
                    'city',
                    'zip',
                    'state',
                    'country',
                    'phone'
                ];
                WooMailerLiteOptions::update('syncFields', $syncFields);
            }
            $filteredCustomerData = array_filter($customer ? $customer->toArray() : [], function($value) {
                return !is_null($value) && trim($value) !== '';
            });

            $syncFields[] = 'last_name';
            if (!in_array('name', $syncFields)) {
                $syncFields[] = 'name';
            }

            $customerFields = array_intersect_key($filteredCustomerData, array_flip($syncFields));
            if (WooMailerLiteOptions::get('settings.languageField')) {
                $customerFields['subscriber_language'] = $order->get_meta('_woo_ml_language');
            }

            $subscribe = false;
            $email = $customer->email ?? $order->get_billing_email();
            $subscribeCacheKey = 'woo_ml_subscribe_checkbox:'.$email;
            if ($cart && isset($cart->subscribe)) {
                $subscribe = $cart->subscribe;
            }

            if (WooMailerLiteOptions::get("settings.checkoutHidden") || $order->get_meta('_woo_ml_subscribe')) {
                $subscribe = true;
            }

            if (WooMailerLiteCache::get($subscribeCacheKey) === null) {
                WooMailerLiteCache::set($subscribeCacheKey, $subscribe, 20);
            }

            if (WooMailerLiteCache::get($subscribeCacheKey) === true) {
                $subscribe = true;
            }

            $orderCustomer = [
                'email' => $customer->email ?? $order->get_billing_email(),
                'create_subscriber' => $subscribe,
                'accepts_marketing' => $subscribe,
                'subscriber_fields' => $customerFields,
                'total_spent' => ($customer->total_spent ?? $order->get_total()),
                'orders_count' => ($customer->orders_count ?? 1),
                'last_order_id' => $customer->last_order_id ?? null,
                'last_order' => $customer->last_order ?? null
            ];
            $items = [];

            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() !== 0) {
                    $items[] = [
                        'product_resource_id' => (string)$item->get_product_id(),
                        'variant'             => $item->get_name(),
                        'quantity'            => $item->get_quantity(),
                        'price'               => (float)$item->get_product()->get_price()
                    ];
                }
            }
            $cartData = [
                'items' => $items
            ];

            if ($this->apiClient()->isClassic()) {
                $orderData['order'] = $order->get_data();
                $orderData['checkout_id'] = $cart->data['checkout_id'] ?? null;
                $orderData['order_url'] = home_url() . "/wp-admin/post.php?post=" . $orderId . "&action=edit";
                $customerFields['woo_total_spent'] = ($customer->total_spent ?? $order->get_total());
                $customerFields['woo_orders_count'] = ($customer->orders_count ?? 1);
                $customerFields['woo_last_order_id'] = $customer->last_order_id ?? null;
                $customerFields['woo_last_order'] = $customer->last_order ?? nulL;
                $data = [
                    'email' => $customer->email,
                    'checked_sub_to_mailist' => (bool)$subscribe ?? (bool)$cart->subscribe,
                    'checkout_id' => $cart->data['checkout_id'] ?? null,
                    'order_id' => $orderId,
                    'payment_method' => $order->get_payment_method(),
                    'fields' => $customerFields,
                    'shop_url' => home_url(),
                    'order_url' => home_url() . "/wp-admin/post.php?post=" . $orderId . "&action=edit",
                    'checkout_data' => WooMailerLiteCheckoutDataService::getCheckoutData($order->get_billing_email())
                ];
                $this->apiClient()->sendOrderProcessing($data);
                if (in_array($order->get_status(), ['completed', 'processing']) && $order->get_items()) {
                    $order_items = $order->get_items();
                    foreach ($order_items as $key => $value) {
                        $item_data = $value->get_data();
                        $orderData['order']['line_items'][$key] = $item_data;
                        $orderData['order']['line_items'][$key]['ignored_product'] = in_array($item_data['product_id'],
                            array_map('strval', array_keys(WooMailerLiteOptions::get('ignored_products', [])))) ? 1 : 0;
                    }
                    $orderData['order']['status'] = 'completed';
                    WooMailerLiteCache::set('order_sent:'.$orderId, true, 20);
                    $response = $this->apiClient()->syncOrder(home_url(), $orderData);
                    $this->apiClient()->sendSubscriberData($data);
                }

            } else {
                $date = null;
                if ($order->get_date_created()) {
                    $date = $order->get_date_created()->format('Y-m-d H:i:s');
                }
                $response = $this->apiClient()->syncOrder(WooMailerLiteOptions::get('shopId'), $orderId, $orderCustomer, $cartData, $order->get_status(), $order->get_total(), $date);
            }

            if (isset($response) && $response->success) {
                $order->add_meta_data('_woo_ml_order_data_submitted', true);
                if (in_array($order->get_status(), ['wc-completed', 'wc-processing','completed','processing']) && !empty($cart)) {
                    if ($cart instanceof WooMailerLiteCart) {
                        if ($this->apiClient()->isRewrite()) {
                            $this->apiClient()->deleteOrder($cart->data['checkout_id']);
                        }
                       $cart->delete();
                    }
                }

                $createdAt = null;
                $updatedAt = null;

                if ($this->apiClient()->isClassic()) {
                    $response = $this->apiClient()->searchSubscriber($customer->email);

                    // For classic response, we have accesss to the date_created and date_updated fields
                    if ($response->success) {
                        $createdAt = $response->data->date_created;
                        $updatedAt = $response->data->date_updated;
                    }
                }

                // For rewrite response, we have accesss to the subscriber object
                if (isset($response->data->customer->subscriber)) {
                    $createdAt = $response->data->customer->subscriber->created_at;
                    $updatedAt = $response->data->customer->subscriber->updated_at;
                }
                
                // We will only set the order meta when we have these dates
                if ($createdAt && $updatedAt) {
                    $createdAt = strtotime($createdAt);
                    $updatedAt = strtotime($updatedAt);
                    
                    // If the created and updated timestamps are within 60 seconds, the subscriber was just created
                    if (abs($createdAt - $updatedAt) < 60) {
                        $order->add_meta_data('_woo_ml_subscribed', true);
                    } else {
                        $order->add_meta_data('_woo_ml_already_subscribed', true);
                        $order->add_meta_data('_woo_ml_subscriber_updated', true);
                    }
                }

                $order->add_meta_data('_woo_ml_order_tracked', true);
            }
            $order->save();
        } catch(\Throwable $e) {
            WooMailerLiteLog()->error('handleOrderStatusChanged', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'orderId' => $orderId
            ]);

            return true;
        }
    }

    private function persistLanguageToOrder($order): void
    {
        $key = '_woo_ml_language';
        if (isset(WC()->session)) {
            $language = WC()->session->get($key);
            if ($language && !$order->get_meta($key)) {
                $order->add_meta_data($key, $language);
                $order->save();
            }
        }
    }
}
