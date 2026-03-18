<?php

class WooMailerLiteCart extends WooMailerLiteModel
{
    protected $table = 'woo_mailerlite_carts';

    protected $casts = [
        'id',
        'hash',
        'email',
        'data',
        'subscribe',
    ];

	protected $format = [
		'data' => 'array',
		'subscribe' => 'boolean',
	];
}