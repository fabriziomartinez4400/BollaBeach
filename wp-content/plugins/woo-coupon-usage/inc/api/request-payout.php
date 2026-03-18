<?php
if ( wcu_fs()->can_use_premium_code() && wcu_fs()->is_premium() ) {

  /* Get the coupon name by ID */

  add_action('rest_api_init', function () {
    register_rest_route( 'woo-coupon-usage/v1', '/request-payout',array(
        'methods'  => 'POST',
        'callback' => 'wcusage_api_request_payout',
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
  if( !function_exists( 'wcusage_api_request_payout' ) ) {
    function wcusage_api_request_payout($params) {

      $coupon_id = $params['coupon_id'];
      $user_login = $params['user'];

      $wcusage_field_payouts_enable = wcusage_get_setting_value('wcusage_field_payouts_enable', '1');
      if($wcusage_field_payouts_enable == '1') {

        $couponinfo = wcusage_get_coupon_info_by_id($coupon_id);
        $unpaid_commission = $couponinfo[2];
        $coupon_user_id = $couponinfo[1];

        $user = get_user_by('login', $user_login);
        $userid = $user->ID;

        // Custom Hook - Post Payout
        if($unpaid_commission && $coupon_user_id == $userid) {

          // Payouts Data
          $payout_details_required = wcusage_get_setting_value('wcusage_field_payout_details_required', 1);
          $payouts_data = wcusage_get_user_payouts_details($coupon_user_id);
          $currenttype = $payouts_data['currenttype'];
          $payout_details = $payouts_data['payout_details'];
          $require_details = $payouts_data['require_details'];
          if( $payout_details || !$payout_details_required || ($currenttype && !$require_details) ) {

            // Request Payout
            $post_payout = do_action( 'wcusage_hook_payout_post_submit', $coupon_user_id, $coupon_id, $unpaid_commission, 1, '' );
            return 1;
            
          }

        }

        return 0;

      }

      return 0;

    }
  }

}
