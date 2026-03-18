<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Compatibility with woocommerce-checkout-manager-pro 6.x
 */
function WOOCCM() {
	return Quadlayers\WOOCCM\WOOCCM();
}
/**
 * Compatibility with WordPress < 6.5
 */
if ( ! function_exists( 'wp_is_serving_rest_request' ) ) {
	function wp_is_serving_rest_request() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}
}
