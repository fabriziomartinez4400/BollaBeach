<?php

/* Get the coupon IDs assigned to user (and unpaid commission) based on user ID */

add_action('rest_api_init', function () {
  register_rest_route( 'woo-coupon-usage/v1', '/users-coupons',array(
      'methods'  => 'GET',
      'callback' => 'wcusage_api_users_coupons',
      'permission_callback' => function() {
          return current_user_can('administrator');
      }
  ));
});

/**
  * @param string $user_id
  *
  * @return array
  *
  */
if( !function_exists( 'wcusage_api_users_coupons' ) ) {
  function wcusage_api_users_coupons($params) {

    $user_login = $params['user'];
    $user = get_user_by('login', $user_login);
    $coupons = wcusage_get_users_coupons_ids( $user->ID );

    return $coupons;

  }
}


