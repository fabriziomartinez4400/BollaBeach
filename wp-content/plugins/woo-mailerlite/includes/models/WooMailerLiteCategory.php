<?php

class WooMailerLiteCategory extends WooMailerLiteModel
{
    protected $casts = [
        'resource_id',
        'name',
        'tracked'
    ];

    protected $isResource = true;

    public static function tracked()
    {
        return static::builder()->setArgs([
            'meta_query' => [
                [
                    'key'     => '_woo_ml_category_tracked',
                    'value'   => true,
                    'compare' => '=',
                ],
            ],
        ]);
    }

    public static function untracked()
    {
        return static::builder()->setArgs([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => '_woo_ml_category_tracked',
                    'value'   => ['1', 'true', 'yes'],
                    'compare' => 'NOT IN',
                ],
                [
                    'key'     => '_woo_ml_category_tracked',
                    'compare' => 'NOT EXISTS',
                ],
            ],
            'number' => -1
        ]);
    }

    public static function getUntrackedCategoriesCount()
    {
        return self::untracked()->count();
    }

    public static function getTrackedCategoriesCount()
    {
        return self::tracked()->count();
    }

}
