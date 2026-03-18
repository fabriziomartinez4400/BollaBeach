<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Force refresh/update affiliate stats on order refunds change
 *
 * @param int $refund
 * @param array $args
 *
 */
if( !function_exists( 'wcusage_order_update_stats_refund' ) ) {
  function wcusage_order_update_stats_refund( $refund, $args ) {

    $order_id = $args['order_id'];

    $wcusage_field_enable_order_commission_meta = wcusage_get_setting_value('wcusage_field_enable_order_commission_meta', '1');

    if($wcusage_field_enable_order_commission_meta) {

      $order = wc_get_order( $order_id );
      if($order) {

        $order = wc_get_order( $order_id );
        
        $order = wc_get_order( $order_id );
        $status = $order->get_status();

        if($status != "refunded") {
        
          $lifetimeaffiliate = wcusage_order_meta($order_id,'lifetime_affiliate_coupon_referrer');
          $affiliatereferrer = wcusage_order_meta($order_id,'wcusage_referrer_coupon');

          if($lifetimeaffiliate) {
            
            wcusage_update_all_stats_single($lifetimeaffiliate, $order_id, 0, 0, 0);

          } elseif($affiliatereferrer) {

            wcusage_update_all_stats_single($affiliatereferrer, $order_id, 0, 0, 0);

          } else {

            foreach( $order->get_coupon_codes() as $coupon_code ) {

              wcusage_update_all_stats_single($coupon_code, $order_id, 0, 0, 0);

            }

          }

        }

      }

    }

  }
}
add_action( 'woocommerce_create_refund', 'wcusage_order_update_stats_refund', 5, 2);

/**
 * Refund deleted
 *
 */
function wcusage_order_update_stats_refund_delete($refund_id, $order_id) {

  wcusage_delete_order_meta($order_id, 'wcusage_stats');
  wcusage_delete_order_meta($order_id, 'wcusage_commission_summary');
  wcusage_delete_order_meta($order_id, 'wcusage_total_commission');
  wcusage_delete_order_meta($order_id, 'wcu_mla_commission');

}
add_action( 'woocommerce_refund_deleted', 'wcusage_order_update_stats_refund_delete', 5, 2 );

/**
 * Force refresh/update affiliate stats on order refunds change
 *
 * @param int $order_id
 * @param int $refund_id
 *
 */
function wcusage_order_update_stats_refund_complete( $order_id, $refund_id ) {

  $order = wc_get_order( $order_id );
  $status = $order->get_status();

  $wcusage_field_enable_order_commission_meta = wcusage_get_setting_value('wcusage_field_enable_order_commission_meta', '1');

  if($wcusage_field_enable_order_commission_meta) {

    $order = wc_get_order( $order_id );
    if($order) {

      $lifetimeaffiliate = wcusage_order_meta($order_id,'lifetime_affiliate_coupon_referrer');
      $affiliatereferrer = wcusage_order_meta($order_id,'wcusage_referrer_coupon');

      if($lifetimeaffiliate) {
        
        wcusage_update_all_stats_single($lifetimeaffiliate, $order_id, 1, 0);

        $calculateorder = wcusage_calculate_order_data( $order_id, $lifetimeaffiliate, 1, 0, 1 );

        $coupon_info = wcusage_get_coupon_info($lifetimeaffiliate);
        $coupon_id = $coupon_info[2];
        do_action('wcusage_hook_reset_order_stats_month', $order, $coupon_id);

      } elseif($affiliatereferrer) {

        wcusage_update_all_stats_single($affiliatereferrer, $order_id, 1, 0);

        $calculateorder = wcusage_calculate_order_data( $order_id, $affiliatereferrer, 1, 0, 1 );

        $coupon_info = wcusage_get_coupon_info($affiliatereferrer);
        $coupon_id = $coupon_info[2];
        do_action('wcusage_hook_reset_order_stats_month', $order, $coupon_id);
        
      } else {

        foreach( $order->get_coupon_codes() as $coupon_code ) {

          wcusage_update_all_stats_single($coupon_code, $order_id, 1, 0);

          $calculateorder = wcusage_calculate_order_data( $order_id, $coupon_code, 1, 0, 1 );

          $coupon_info = wcusage_get_coupon_info($coupon_code);
          $coupon_id = $coupon_info[2];
          do_action('wcusage_hook_reset_order_stats_month', $order, $coupon_id);

        }

      }

    }

  }

}
add_action( 'woocommerce_order_refunded', 'wcusage_order_update_stats_refund_complete', 5, 2);