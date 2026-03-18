<?php

trait WooMailerLiteResources
{

    /**
     * @var array $resources
     */
    public array $resources = [
        'products' => 'WooMailerLiteProduct',
        'categories' => 'WooMailerLiteCategory',
        'customers' => 'WooMailerLiteCustomer',
    ];

    /**
     * @var array $args
     */
    protected array $args = [];


    /**
     * @var string $resource
     */
    protected ?string $resource;

    protected int $counter = 0;

    protected bool $setCustomerResource = false;
    public $customerResourceCount = [];
    /**
     * Set resource
     * @param string $resource
     * @return WooMailerLiteDBConnection|WooMailerLiteModel|WooMailerLiteResources
     */
    public function set_resource(string $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get all resources
     * @return void|WooMailerLiteCollection|int
     */
    public function resource_all($count = 0)
    {
        $collection = new WooMailerLiteCollection();
        switch ($this->resource) {
            case 'WooMailerLiteProduct':
                if (isset($this->args['id'])) {
                    $this->args['include'] = [$this->args['id']];
                    unset($this->args['id']);
                }
                $this->args = array_merge([
                    'limit' => -1,
                    'return' => 'objects',
                ], $this->args);

                if ($this->args['limit'] == -1) {
                    $this->args = array_merge($this->args, [
                        'paginate' => true,
                        'return' => 'ids',
                        'status' => 'publish',
                    ]);
                    return (int) (new WC_Product_Query($this->args))->get_products()->total;
                }
                $items = (new WC_Product_Query($this->args))->get_products();
                break;

            case 'WooMailerLiteCategory':
                if (isset($this->args['id'])) {
                    $this->args['include'] = [$this->args['id']];
                    unset($this->args['id']);
                }
                $this->args = array_merge([
                    'taxonomy'   => 'product_cat',
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ], $this->args);

                $items = new WP_Term_Query($this->args);
                $items = $items->terms;
                break;
            case 'WooMailerLiteCustomer':
                    $this->args = array_merge([
                        'status' => ['wc-completed', 'wc-processing'],
                        'type' => 'shop_order',
                        'orderby' => 'date',
                        'order' => 'ASC',
                        'return' => 'ids'
                    ], $this->args);
                    if ($count == 1) {
                        $this->args['order'] = 'DESC';
                    }
                    $items = wc_get_orders($this->args);
                    if ($count > 1) {
                        $items = array_filter($items, function ($order_id) {
                            return $order_id > WooMailerLiteOptions::get('lastSyncedOrder', 0);
                        });
                    }
                    break;
            default:
                $this->args = [];
                return $collection;
        }
        $collection->collect(null);
        if (!empty($items)) {
            $collection->empty();
            foreach ($items as $item) {
                $this->counter++;
                if (($item instanceof WC_Product_Simple) && (empty($item->get_permalink()) || !is_string($item->get_permalink()))) {
                    continue;
                }
                if (is_int($item)) {
                    $data = $item;
                } else {
                    $data = ($item instanceof WC_Product_Simple) ?  $item->get_data() : get_object_vars($item);
                }
                $first = false;
                if ($count == 1) {
                    $first = true;
                }

                $this->prepareResourceData($this->resource, $data, $item, $first);
                if ($this->resource === 'WooMailerLiteCustomer') {
                    continue;
                }
                $model = new $this->resource();

                if (!empty($this->model->getCastsArray())) {
                    $model->attributes = array_intersect_key($data, array_flip($this->model->getCastsArray()));

                } else {
                    $model->attributes = $data;
                }

                if (!empty($this->model->getRemoveEmptyArray())) {
                    foreach ($this->model->getRemoveEmptyArray() as $key) {
                        if (isset($model->attributes[$key]) && empty($model->attributes[$key])) {
                            unset($model->attributes[$key]);
                        }
                    }
                }

                $collection->collect($model);
            }
        }
        if ($this->resource === 'WooMailerLiteCustomer') {
            foreach ($this->customerResourceCount as $item) {
                if (!empty($item)) {
                    $model = new $this->resource();
                    if ($this->model->getCastsArray()) {
                        $model->attributes = array_intersect_key($item, array_flip($this->model->getCastsArray() ?? []));
                    } else {
                        $model->attributes = $item;
                    }
                    $collection->collect($model);
                }
            }
        }
        return $collection;
    }

    /**
     * Get resources
     * @param int $count
     * @return WooMailerLiteCollection|WooMailerLiteProduct|WooMailerLiteCustomer|WooMailerLiteCategory|null
     */
    public function resource_get(int $count = -1)
    {
        switch ($this->resource) {
            case 'WooMailerLiteCategory':
                $this->args['number'] = $count;
                return $this->resource_all();
            default:
                $this->args['limit'] = $count;
                return $this->resource_all($count);
        }
    }

    /**
     * Where clause for resources
     * @param $column
     * @param $value
     * @return array|mixed|void
     */
    public function resource_where($column, $value)
    {
        $this->args[$column] = $value;
    }

    public function setArgs($args)
    {
        $this->args = array_merge($this->args, $args);
        $prefix = $this->db()->prefix;
        $this->select = "{$prefix}posts.ID";
        if (isset($args['meta_query'][0]['key']) && ($args['meta_query'][0]['key'] !== '_woo_ml_category_tracked')) {
            return $this;
        }
        if (isset($args['metaQuery'][0]['value']) && ($args['metaQuery'][0]['value'] === true) && ($args['metaQuery'][0]['key'] === '_woo_ml_product_tracked')) {
            $this->leftJoin('postmeta', [
                'posts.id' => "postmeta.post_id",
                'postmeta.meta_key' => '_woo_ml_product_tracked'
            ])
            ->where('posts.post_type', 'product')
            ->where('posts.post_status', 'publish')
                ->andCombine(function($query) {
                    $query->where('postmeta.meta_value', '1')
                        ->orWhere('postmeta.meta_value', true);
                })
            ->columnsOnly();
            return $this;
        }
        $this->leftJoin('postmeta', [
            'posts.id' => "postmeta.post_id",
            'postmeta.meta_key' => '_woo_ml_product_tracked'
            ])
            ->where('posts.post_type', 'product')
            ->where('posts.post_status', 'publish')
            ->andCombine(function($query) {
                $query->where('postmeta.meta_value', '0')
                      ->orWhere('postmeta.meta_value', null)
                      ->orWhere('postmeta.meta_value', false);
            })
            ->columnsOnly();
        return $this;
    }

    public function prepareResourceData($resource, &$data, $item, $first = false)
    {
        switch ($resource) {
            case 'WooMailerLiteProduct':
                $variableProduct = $item->is_type('variation') ?? false;
                $data['resource_id'] = (string) ($variableProduct ? $item->get_parent_id() : $item->get_id());
                $data['url'] = get_permalink($item->get_id());
                $data['name'] = $item->get_name();
                $data['price'] = floatval($item->get_price());
                $data['url'] = get_permalink( $item->get_id() );
                $data['category_ids'] = $item->get_category_ids();
                $data['image'] = (string) wp_get_attachment_image_url($item->get_image_id(), 'full');
                $data['description'] = $item->get_description();
                $data['short_description'] = $item->get_short_description();
                $data['status'] = $item->get_status();
                $data['ignored'] = get_post_meta($data['resource_id'], '_woo_ml_product_ignored', true);
                $data['tracked'] = get_post_meta($data['resource_id'], '_woo_ml_product_tracked', true);
                break;
            case 'WooMailerLiteCategory':
                $data['resource_id'] = (string) $item->term_id;
                $data['name'] = $item->name;
                $data['tracked'] = get_term_meta($item->term_id, '_woo_ml_category_tracked', true);
                break;
            case 'WooMailerLiteCustomer':
                $item = wc_get_order($item);
                $data = (array) $data;
                $email = $item->get_billing_email();
                $similarOrderCount = 0;
                $similarSpent = 0;
                if (($item->get_customer_id() != 0) && in_array($item->get_customer_id(), array_column($this->customerResourceCount, 'resource_id'))) {
                    $key = array_search($item->get_customer_id(), array_column($this->customerResourceCount, 'resource_id', 'email'));
                    $similarOrderCount = $this->customerResourceCount[$key]['orders_count'] ?? 0;
                    $similarSpent = $this->customerResourceCount[$key]['total_spent'] ?? 0;
                    unset($this->customerResourceCount[$key]);
                }
                if (!isset($this->customerResourceCount[$email])) {
                    $this->customerResourceCount[$email] = [
                        'orders_count' => 0,
                        'total_spent' => 0,
                        'create_subscriber' => false,
                        'accepts_marketing' => false,
                        'key' => 0,
                        'resource_id' => 0,
//                        'customer_id' => $data['customer_id'] ?? $this->counter,
                    ];

                }


                if ($first) {
                    $orders = wc_get_orders([
                        'billing_email' => $item->get_billing_email(),
                        'status' => ['wc-processing', 'wc-completed'],
                        'customer_id' => $item->get_customer_id(),
                        'limit' => -1
                    ]);

                    if (count($orders) > 1) {
                        foreach ($orders as $order) {
                            if ($order->get_id() == $item->get_id()) {
                                continue;
                            }
                            $similarSpent += $order->get_total() ?? 0;
                            $similarOrderCount += 1;
                        }
                    }
                }



                $this->customerResourceCount[$email] = [
                    'customer_id' => $data['customer_id'] ?? $this->counter,
                    'resource_id' => $data['customer_id'] ?? $this->counter,
                    'email' => $item->get_billing_email(),
                    'name' => $item->get_billing_first_name(),
                    'last_name' => $item->get_billing_last_name(),
                    'accepts_marketing' => $item->get_meta('_woo_ml_subscribe'),
                    'create_subscriber' => $item->get_meta('_woo_ml_subscribe'),
                    'city' => $item->get_billing_city(),
                    'state' => $item->get_billing_state(),
                    'country' => $item->get_billing_country(),
                    'zip' => $item->get_billing_postcode(),
                    'last_order_id' => $item->get_id(),
                    'last_order' => $item->get_date_created()->date('Y-m-d H:i:s'),
                    'orders_count' => $this->customerResourceCount[$email]['orders_count'] + $similarOrderCount + 1,
                    'total_spent' => $this->customerResourceCount[$email]['total_spent'] + $similarSpent + $item->get_total(),
//                    'tracked'
                ];
                $data = $this->customerResourceCount[$email];
                break;
        }
    }

    public function customTableEnabled()
    {
        // temporarily because it works
        return true;
        if ($this->model->isResource) {
            return false;
        }
        return 'yes' === get_option(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::CUSTOM_ORDERS_TABLE_USAGE_ENABLED_OPTION);
    }
}
