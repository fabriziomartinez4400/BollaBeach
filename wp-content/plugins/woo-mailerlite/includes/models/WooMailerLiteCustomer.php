<?php

class WooMailerLiteCustomer extends WooMailerLiteModel
{
    /**
     * @var string|null
     */
    protected $table = 'wc_customer_lookup';

    protected $casts = [
        'customer_id',
        'resource_id',
        'email',
        'create_subscriber',
        'accepts_marketing',
        'orders_count',
        'total_spent',
        'name',
        'last_name',
        'company',
        'city',
        'state',
        'country',
        'zip',
        'last_order_id',
        'last_order',
    ];

    protected $format = [
        'accepts_marketing' => 'boolean',
        'create_subscriber' => 'boolean',
        'resource_id' => 'string',
    ];

    /**
     * Get all customers
     * @return mixed|WooMailerLiteCollection|null
     */
    public static function getAll($limit = 100)
    {
        $prefix = db()->prefix;

        $statesQuery = static::builder()->select("
            {$prefix}wc_order_stats.customer_id,
            {$prefix}wc_order_stats.customer_id as resource_id,
            MAX({$prefix}wc_order_stats.order_id) AS last_order_id,
            MAX({$prefix}wc_order_stats.date_created) AS last_order,
            COUNT({$prefix}wc_order_stats.order_id) AS orders_count,
            SUM({$prefix}wc_order_stats.total_sales) AS total_spent
        ")
            ->from("wc_order_stats")
            ->whereIn("status", ['wc-processing','wc-completed'])
            ->groupBy("wc_order_stats.customer_id");

        return static::builder()->select("
            {$prefix}wc_customer_lookup.customer_id,
            {$prefix}wc_customer_lookup.customer_id AS resource_id,
            {$prefix}wc_customer_lookup.email,
            CASE WHEN {$prefix}postmeta.meta_value IS NOT NULL THEN TRUE ELSE FALSE END AS create_subscriber,
            CASE WHEN {$prefix}postmeta.meta_value IS NOT NULL THEN TRUE ELSE FALSE END AS accepts_marketing,
            {$prefix}order_agg.orders_count,
            {$prefix}order_agg.total_spent,
            {$prefix}wc_customer_lookup.first_name AS name,
            {$prefix}wc_customer_lookup.last_name,
            {$prefix}wc_customer_lookup.city,
            {$prefix}wc_customer_lookup.state,
            {$prefix}wc_customer_lookup.country,
            {$prefix}wc_customer_lookup.postcode AS zip,
            COALESCE({$prefix}usermeta.meta_value, '') AS company,
            {$prefix}order_agg.last_order_id,
            {$prefix}order_agg.last_order,
            CASE WHEN {$prefix}postmeta.meta_value IS NOT NULL THEN TRUE ELSE FALSE END AS create_subscriber
        ")
            ->join($statesQuery, 'order_agg.customer_id', 'wc_customer_lookup.customer_id', 'order_agg')
            ->leftJoin("postmeta", [
                'postmeta.post_id' => 'order_agg.last_order_id',
                'postmeta.meta_key' => '_woo_ml_subscribe'
            ])
            ->leftJoin("usermeta", [
                'usermeta.user_id' => 'wc_customer_lookup.user_id',
                'usermeta.meta_key' => 'billing_company'
            ])
            ->where('order_agg.customer_id', '>', WooMailerLiteOptions::get('lastSyncedCustomer', 0))
            ->orderBy('order_agg.customer_id')->get($limit);
    }

    public static function getUntrackedCustomersCount()
    {
        $prefix = db()->prefix;
        return static::builder()->select("{$prefix}wc_customer_lookup.customer_id")
            ->from("wc_customer_lookup")
            ->join("wc_order_stats", [
                'wc_order_stats.customer_id' => 'wc_customer_lookup.customer_id',
                'wc_order_stats.status' => [
                    'in' => [
                        'wc-processing', 'wc-completed'
                    ]
                ]
            ])
        ->where("wc_order_stats.customer_id", ">", WooMailerLiteOptions::get('lastSyncedCustomer', 0))
        ->groupBy("wc_customer_lookup.customer_id")
        ->orderBy('wc_customer_lookup.customer_id')
        ->count();
    }

    public static function selectAll($sync = true)
    {
        $prefix = db()->prefix;
        $query  = static::builder()->select("*,
        CASE WHEN (
                        SELECT
                            wpm.meta_value
                        FROM
                            {$prefix}postmeta wpm
                        WHERE
                            wpm.meta_key = '_woo_ml_subscribe'
                            AND wpm.post_id = max({$prefix}wc_order_stats.order_id)
                        LIMIT 1) THEN
                        TRUE
                    ELSE
                        FALSE
                    END AS create_subscriber,
                    max({$prefix}wc_order_stats.order_id) AS last_order_id,
                    max({$prefix}wc_order_stats.date_created) AS last_order,
                    count(DISTINCT ({$prefix}wc_order_stats.order_id)) AS orders_count,
                    sum(({$prefix}wc_order_stats.total_sales)) AS total_spent")
            ->join('wc_order_stats', 'wc_order_stats.customer_id', 'wc_customer_lookup.customer_id')
            ->whereIn('wc_order_stats.status', ['wc-processing', 'wc-completed']);
        if (self::builder()->customTableEnabled() && $sync) {
            $query->where('wc_order_stats.customer_id', '>', WooMailerLiteOptions::get('lastSyncedCustomer', 0));
            $query->groupBy('wc_order_stats.customer_id, wp_wc_order_stats.order_id')
                ->orderBy('wc_order_stats.customer_id');
        }

        return $query;
    }

    public function markTracked()
    {
        WooMailerLiteOptions::update('lastSyncedOrder', $this->last_order_id);
        WooMailerLiteOptions::update('lastSyncedCustomer', $this->customer_id);
    }

    public static function martUntracked()
    {
        WooMailerLiteOptions::update('lastSyncedOrder', 0);
        WooMailerLiteOptions::update('lastSyncedCustomer', 0);
    }
}
