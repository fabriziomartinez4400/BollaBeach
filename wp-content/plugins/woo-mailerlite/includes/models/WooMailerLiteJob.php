<?php

class WooMailerLiteJob extends WooMailerLiteModel
{
    protected $table = 'woo_mailerlite_jobs';

    protected $format = [
        'data' => 'array'
    ];
}
