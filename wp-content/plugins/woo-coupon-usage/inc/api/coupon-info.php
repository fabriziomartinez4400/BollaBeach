<?php

/* Get the coupon name and info by ID */

add_action('rest_api_init', function () {
  register_rest_route( 'woo-coupon-usage/v1', '/coupon-info',array(
      'methods'  => 'GET',
      'callback' => 'wcusage_api_coupon_info',
      'permission_callback' => function() {
          return current_user_can('administrator');
      }
  ));
});

/**
  * @param string $coupon_id
  *
  * @return array
  *
  */
if( !function_exists( 'wcusage_api_coupon_info' ) ) {
  function wcusage_api_coupon_info($params) {

    $coupon_id = $params['coupon_id'];
    $couponinfo = wcusage_get_coupon_info_by_id($coupon_id);

    // Return
    $return_array['coupon_name'] = $couponinfo[3];
    $return_array['unpaid_commission'] = $couponinfo[2];
    $return_array['pending_payouts'] = $couponinfo[5];
    $return_array['coupon_user_id'] = $couponinfo[1];
    $return_array['referral_url'] = $couponinfo[4];
  	return $return_array;

  }
}
