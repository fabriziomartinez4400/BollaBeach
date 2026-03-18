<?php

class WooMailerLiteCustomerSyncJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        $customers = WooMailerLiteCustomer::getAll(100);
        $processed = false;
        if ($customers->hasItems()) {
            $countInCache = WooMailerLiteCache::get('resource_sync_counts', false);
            if (isset($countInCache['customers'])) {
                $countInCache = $countInCache['customers'];
            } else {
                $countInCache = 0;
            }
            foreach ($customers->items as $customer) {
                $customer->markTracked();
                $processed = true;
                if (WooMailerLiteApi::client()->isClassic()) {
                    $this->syncToClassic($customer);
                }
            }
            if (WooMailerLiteApi::client()->isRewrite()) {
                $originalCustomers = $customers->toArray();

                $transformedCustomers = array_map(function ($data) {
                    $rootKeys = [
                        'resource_id',
                        'email',
                        'create_subscriber',
                        'accepts_marketing',
                        'total_spent',
                        'orders_count',
                        'last_order_id',
                        'last_order'
                    ];
                    $flippedRootKeys = array_flip($rootKeys);
                    $subscriberFields = array_diff_key($data, $flippedRootKeys);
                    $rootFields = array_intersect_key($data, $flippedRootKeys);

                    return array_merge($rootFields, ['subscriber_fields' => $this->prepareCustomerFieldsForSync($subscriberFields)]);
                }, $originalCustomers);

                WooMailerLiteApi::client()->syncCustomers($transformedCustomers);
            }

            if ($processed && (WooMailerLiteCustomer::getAll()->count() < $countInCache)) {
                self::dispatch($data);
            }
        }
    }

    protected function syncToClassic($customer)
    {
        try {
            $customer = $customer->toArray();
            $customerFields = $this->prepareCustomerFieldsForSync($customer);
            $customerFields['woo_orders_count'] = $customer['orders_count'] ?? 0;
            $customerFields['woo_total_spent'] = $customer['total_spent'] ?? 0;
            $customerFields['woo_last_order'] = $customer['last_order'] ?? null;
            $customerFields['woo_last_order_id'] = $customer['last_order_id'] ?? null;
            WooMailerLiteApi::client()->syncCustomers([
                'email' => $customer['email'],
                'subscriber_fields' => $customerFields,
                'shop' => home_url()
            ]);
            return true;
        } catch(\Throwable $e) {
            WooMailerLiteLog()->error('classic:sync_customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return true;
        }
    }

    protected function prepareCustomerFieldsForSync($customer)
    {
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
        $syncFields[] = 'last_name';
        if (!in_array('name', $syncFields)) {
            $syncFields[] = 'name';
        }
        return array_intersect_key($customer, array_flip($syncFields));
    }
}
