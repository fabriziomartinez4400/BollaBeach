<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Forces all stats to be refreshed
 *
 * @param string $coupon_code
 *
 */
if ( !function_exists( 'wcusage_update_all_stats' ) ) {
    function wcusage_update_all_stats(  $coupon_code, $force = 0  ) {
        $wcusage_field_enable_coupon_all_stats_meta = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
        if ( $wcusage_field_enable_coupon_all_stats_meta ) {
            $fullorders = wcusage_wh_getOrderbyCouponCode(
                $coupon_code,
                "",
                date( "Y-m-d" ),
                '',
                1,
                1,
                1
            );
        } else {
            $fullorders = "";
        }
        return $fullorders;
    }

}
add_action(
    'wcusage_hook_update_all_stats',
    'wcusage_update_all_stats',
    10,
    2
);
/**
 * Updates all stats for a coupon by adding/removing values from a single order
 *
 * @param string $coupon_code
 * @param int $order_id
 * @param bool $type - If add or remove order from stats
 * @param bool $change - If the usage should be changed.
 *
 */
if ( !function_exists( 'wcusage_update_all_stats_single' ) ) {
    function wcusage_update_all_stats_single(
        $coupon_code,
        $order_id,
        $type,
        $change,
        $update = 1
    ) {
        $order = wc_get_order( $order_id );
        $coupon_code = strtolower( $coupon_code );
        $couponinfo = wcusage_get_coupon_info( $coupon_code );
        $wcu_alltime_stats = get_post_meta( $couponinfo[2], 'wcu_alltime_stats', true );
        if ( !$wcu_alltime_stats ) {
            // On first order, set alltime stats to 0 so it can be updated
            global $woocommerce;
            $c = new WC_Coupon($coupon_code);
            $usage = $c->get_usage_count();
            if ( $usage <= 1 ) {
                $wcu_alltime_stats = array();
                $wcu_alltime_stats['total_orders'] = 0;
                $wcu_alltime_stats['full_discount'] = 0;
                $wcu_alltime_stats['total_commission'] = 0;
                $wcu_alltime_stats['total_shipping'] = 0;
                $wcu_alltime_stats['total_count'] = 0;
                $wcu_alltime_stats['commission_summary'] = array();
                update_post_meta( $couponinfo[2], 'wcu_alltime_stats', $wcu_alltime_stats );
            }
        }
        if ( $wcu_alltime_stats ) {
            // Get Current Values
            $total_orders = 0;
            if ( isset( $wcu_alltime_stats['total_orders'] ) ) {
                $total_orders = $wcu_alltime_stats['total_orders'];
            }
            $total_discount = 0;
            if ( isset( $wcu_alltime_stats['full_discount'] ) ) {
                $total_discount = $wcu_alltime_stats['full_discount'];
            }
            $total_commission = 0;
            if ( isset( $wcu_alltime_stats['total_commission'] ) ) {
                $total_commission = $wcu_alltime_stats['total_commission'];
            }
            $total_count = 0;
            if ( isset( $wcu_alltime_stats['total_count'] ) ) {
                $total_count = $wcu_alltime_stats['total_count'];
            }
            // Get Order Values
            if ( $update ) {
                $order_data = wcusage_calculate_order_data(
                    $order_id,
                    $coupon_code,
                    1,
                    0,
                    1
                );
            } else {
                $order_data = wcusage_calculate_order_data(
                    $order_id,
                    $coupon_code,
                    0,
                    1,
                    0
                );
            }
            $order_total = $order_data['totalorders'];
            $order_discounts = $order_data['totaldiscounts'];
            $order_commission = $order_data['totalcommission'];
            // Update
            $allstats = array();
            if ( $type ) {
                $allstats['total_orders'] = $total_orders + $order_total;
                $allstats['full_discount'] = $total_discount + $order_discounts;
                $allstats['total_commission'] = $total_commission + $order_commission;
                if ( $change ) {
                    $allstats['total_count'] = $total_count + 1;
                } else {
                    $allstats['total_count'] = $total_count;
                }
            } else {
                $allstats['total_orders'] = $total_orders - $order_total;
                $allstats['full_discount'] = $total_discount - $order_discounts;
                $allstats['total_commission'] = $total_commission - $order_commission;
                if ( $change ) {
                    $allstats['total_count'] = $total_count - 1;
                } else {
                    $allstats['total_count'] = $total_count;
                }
            }
            update_post_meta( $couponinfo[2], 'wcu_alltime_stats', $allstats );
            do_action( 'wcusage_hook_after_update_stats_single', $order, $couponinfo[2] );
        }
        // Reset Monthly Summary Data For This Orders Month
        do_action( 'wcusage_hook_reset_order_stats_month', $order, $couponinfo[2] );
    }

}
add_action(
    'wcusage_hook_update_all_stats_single',
    'wcusage_update_all_stats_single',
    10,
    4
);
/*
* Run wcusage_hook_reset_order_stats_month on order completed
*
* @param int $order_id
*
*/
function wcusage_reset_order_stats_month_on_order_completed(  $order_id  ) {
    $order = wc_get_order( $order_id );
    $coupons = $order->get_items( 'coupon' );
    if ( $coupons ) {
        foreach ( $coupons as $coupon ) {
            $coupon_code = $coupon->get_code();
            $couponinfo = wcusage_get_coupon_info( $coupon_code );
            do_action( 'wcusage_hook_reset_order_stats_month', $order, $couponinfo[2] );
        }
    }
}

/*
* Updates the monthly stats for a coupon based on order
*
* @param string $coupon_code
* @param int $order_id
*
*/
function wcusage_reset_order_stats_month(  $order, $coupon_id  ) {
    // Check valid order
    if ( !$order ) {
        return;
    }
    // Check valid coupon
    if ( !$coupon_id ) {
        return;
    }
    // Reset Monthly Summary Data For This Orders Month
    $wcusage_field_order_sort = wcusage_get_setting_value( 'wcusage_field_order_sort', 'paiddate' );
    if ( $wcusage_field_order_sort == "paiddate" ) {
        $order_date = $order->get_date_created();
    } else {
        $order_date = $order->get_date_completed();
    }
    $order_date = date( 'Y-m-01', strtotime( $order_date ) );
    $wcusage_monthly_summary_data = get_post_meta( $coupon_id, 'wcusage_monthly_summary_data', true );
    if ( !empty( $wcusage_monthly_summary_data ) ) {
        $wcusage_monthly_summary_data[strtotime( $order_date )] = "";
        update_post_meta( $coupon_id, 'wcusage_monthly_summary_data', $wcusage_monthly_summary_data );
    }
    $wcusage_monthly_summary_data_orders = get_post_meta( $coupon_id, 'wcusage_monthly_summary_data_orders', true );
    if ( !empty( $wcusage_monthly_summary_data_orders ) ) {
        $wcusage_monthly_summary_data_orders[strtotime( $order_date )] = "";
        update_post_meta( $coupon_id, 'wcusage_monthly_summary_data_orders', $wcusage_monthly_summary_data_orders );
    }
}

add_action(
    'wcusage_hook_reset_order_stats_month',
    'wcusage_reset_order_stats_month',
    10,
    2
);
/**
 * Updates all stats for a coupon on specific day.
 */
function wcusage_get_orders_by_coupon_ajax() {
    check_ajax_referer( 'wcusage_update_stats_nonce', 'security' );
    $coupon_code = $_POST['coupon_code'];
    $startdate = $_POST['start'];
    $enddate = $_POST['end'];
    $fullorders = wcusage_wh_getOrderbyCouponCode(
        $coupon_code,
        $startdate,
        $enddate,
        '',
        1,
        1,
        1
    );
    echo json_encode( $fullorders['allstats'] );
    wp_die();
}

add_action( 'wp_ajax_wcusage_get_orders_by_coupon_ajax', 'wcusage_get_orders_by_coupon_ajax' );
add_action( 'wp_ajax_nopriv_wcusage_get_orders_by_coupon_ajax', 'wcusage_get_orders_by_coupon_ajax' );
/**
 * Updates all stats for a coupon
 */
function wcusage_update_all_stats_data() {
    check_ajax_referer( 'wcusage_update_stats_nonce', 'security' );
    $options = get_option( 'wcusage_options' );
    $stats = $_POST['stats'];
    $coupon_code = $_POST['coupon_code'];
    $coupon = wcusage_get_coupon_info( $coupon_code );
    $coupon_user_id = $coupon[1];
    $coupon_id = $coupon[2];
    $currentuserid = get_current_user_id();
    // Check MLA sub-affiliate
    $sub_affiliate = false;
    // Check access
    if ( $coupon_user_id != $currentuserid && !$sub_affiliate && !wcusage_check_admin_access() ) {
        wp_send_json_error( esc_html__( 'You do not have permission to access this data.', 'woo-coupon-usage' ) );
        wp_die();
    }
    // Stats
    $allstats = array();
    $allstats['total_orders'] = ( isset( $stats['total_orders'] ) ? floatval( $stats['total_orders'] ) : 0 );
    $allstats['full_discount'] = ( isset( $stats['full_discount'] ) ? floatval( $stats['full_discount'] ) : 0 );
    $allstats['total_commission'] = ( isset( $stats['total_commission'] ) ? floatval( $stats['total_commission'] ) : 0 );
    $allstats['total_shipping'] = ( isset( $stats['total_shipping'] ) ? floatval( $stats['total_shipping'] ) : 0 );
    $allstats['total_count'] = ( isset( $stats['total_count'] ) ? floatval( $stats['total_count'] ) : 0 );
    if ( isset( $stats['commission_summary'] ) && is_array( $stats['commission_summary'] ) ) {
        foreach ( $stats['commission_summary'] as $key => $value ) {
            $sanitized_key = sanitize_text_field( $key );
            if ( is_array( $value ) || is_object( $value ) ) {
                $value = (array) $value;
                $allstats['commission_summary'][$sanitized_key] = array(
                    'total'      => ( isset( $value['total'] ) ? floatval( $value['total'] ) : 0 ),
                    'commission' => ( isset( $value['commission'] ) ? floatval( $value['commission'] ) : 0 ),
                    'number'     => ( isset( $value['number'] ) ? intval( $value['number'] ) : 0 ),
                );
            }
        }
    } else {
        $allstats['commission_summary'] = array();
    }
    update_post_meta( $coupon_id, 'wcu_alltime_stats', $allstats );
    update_post_meta( $coupon_id, 'wcu_last_refreshed', time() );
    delete_post_meta( $coupon_id, 'wcusage_monthly_summary_data' );
    delete_post_meta( $coupon_id, 'wcusage_monthly_summary_data_orders' );
    echo json_encode( $allstats );
    wp_die();
}

add_action( 'wp_ajax_wcusage_update_all_stats_data', 'wcusage_update_all_stats_data' );
add_action( 'wp_ajax_nopriv_wcusage_update_all_stats_data', 'wcusage_update_all_stats_data' );
/**
 * Updates all stats for a coupon in batches via ajax
 */
function wcusage_update_all_stats_batch_ajax(  $coupon_code, $the_coupon_usage  ) {
    global $wpdb;
    $coupon_code = sanitize_text_field( $coupon_code );
    $ajaxerrormessage = wcusage_ajax_error();
    $wcusage_field_order_type_custom = wcusage_get_setting_value( 'wcusage_field_order_type_custom', '' );
    if ( !$wcusage_field_order_type_custom ) {
        $statuses = wc_get_order_statuses();
        if ( isset( $statuses['wc-refunded'] ) ) {
            unset($statuses['wc-refunded']);
        }
    } else {
        $statuses = $wcusage_field_order_type_custom;
    }
    // Custom Orders Table or Posts Table
    if ( class_exists( OrderUtil::class ) && method_exists( OrderUtil::class, 'custom_orders_table_usage_is_enabled' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
        $id = "id";
        $posts = "wc_orders";
        $postmeta = "wc_orders_meta";
        $post_date = "date_created_gmt";
        $post_type = "";
        $post_status = "status";
        $post_id = "order_id";
    } else {
        $id = "ID";
        $posts = "posts";
        $postmeta = "postmeta";
        $post_date = "post_date";
        $post_type = "WHERE\r\n p.post_type = 'shop_order'";
        $post_status = "post_status";
        $post_id = "post_id";
    }
    // Query to get orders
    $query = $wpdb->prepare(
        "SELECT DISTINCT p." . $id . " AS order_id, p." . $post_date . " AS order_date\r\n      FROM {$wpdb->prefix}" . $posts . " AS p\r\n      LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi\r\n        ON p." . $id . " = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name = %s\r\n      LEFT JOIN {$wpdb->prefix}" . $postmeta . " AS woi2\r\n        ON p." . $id . " = woi2." . $post_id . " AND (\r\n          (woi2.meta_key = 'lifetime_affiliate_coupon_referrer' AND woi2.meta_value = %s) OR\r\n          (woi2.meta_key = 'wcusage_referrer_coupon' AND woi2.meta_value = %s)\r\n        )\r\n      WHERE p." . $post_status . " IN ('" . implode( "','", array_keys( $statuses ) ) . "')\r\n      AND (woi.order_id IS NOT NULL OR woi2.meta_value = %s AND woi2.meta_key IS NOT NULL)",
        $coupon_code,
        $coupon_code,
        $coupon_code,
        $coupon_code
    );
    // Get the oldest order date
    $results = $wpdb->get_results( $wpdb->prepare( $query . " ORDER BY order_date ASC LIMIT %d", 1 ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( !empty( $results ) ) {
        $first_order_date = $results[0]->order_date;
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        if ( $wcusage_hide_all_time ) {
            $first_order_date = date( "Y-m-d" );
        }
    } else {
        $first_order_date = date( "Y-m-d" );
    }
    // Get the newest order date
    $results2 = $wpdb->get_results( $wpdb->prepare( $query . " ORDER BY order_date DESC LIMIT %d", 1 ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( !empty( $results2 ) ) {
        $last_order_date = $results2[0]->order_date;
    } else {
        $last_order_date = date( "Y-m-d" );
    }
    // Batch amount (wcusage_field_enable_coupon_all_stats_batch_amount)
    $batch_amount = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_batch_amount', '20' );
    $batch_amount = intval( $batch_amount );
    $batch_amount2 = $batch_amount + 1;
    $coupon_info = wcusage_get_coupon_info( $coupon_code );
    $post_id = $coupon_info[2];
    delete_post_meta( $post_id, 'wcu_alltime_stats' );
    delete_post_meta( $post_id, 'wcu_last_refreshed' );
    update_post_meta( $post_id, 'wcu_text_pending_order_commission', 0 );
    ?>

    <script>
    var endDate = new Date('<?php 
    echo esc_html( $last_order_date );
    ?>');
    var startDate = new Date('<?php 
    echo esc_html( $last_order_date );
    ?>');
    startDate.setDate(startDate.getDate() - <?php 
    echo esc_html( $batch_amount );
    ?>);
    var the_coupon_usage = <?php 
    echo esc_html( $the_coupon_usage );
    ?>;
    var loop = 0;
    var allstats = {
    total_orders: 0,
    full_discount: 0,
    total_commission: 0,
    total_shipping: 0,
    total_count: 0,
    commission_summary: {}
    };
    var first_order_date = new Date('<?php 
    echo esc_html( $first_order_date );
    ?>');
    var last_order_date = new Date('<?php 
    echo esc_html( $last_order_date );
    ?>');
    var updateStatsNonce = '<?php 
    echo esc_html( wp_create_nonce( 'wcusage_update_stats_nonce' ) );
    ?>';

    function getOrders() {
    jQuery.ajax({
      url: '<?php 
    echo esc_url( admin_url( 'admin-ajax.php' ) );
    ?>',
      type: 'POST',
      data: {
      'action': 'wcusage_get_orders_by_coupon_ajax',
      'start': startDate.toISOString().slice(0, 10),
      'end': endDate.toISOString().slice(0, 10),
      'coupon_code': '<?php 
    echo esc_html( $coupon_code );
    ?>',
      'security': updateStatsNonce
      },
      success: function(response) {
        loop++;
        var responseData = JSON.parse(response);
        allstats.total_count += Number(responseData.total_count);
        allstats.total_orders += Number(responseData.total_orders);
        allstats.full_discount += Number(responseData.full_discount);
        allstats.total_commission += Number(responseData.total_commission);
        allstats.total_shipping += Number(responseData.total_shipping);
        console.log('Testing responseData:', responseData);
        for (var key in responseData.commission_summary) {
          if (allstats.commission_summary[key]) {
          allstats.commission_summary[key].total += Number(responseData.commission_summary[key].total);
          allstats.commission_summary[key].commission += Number(responseData.commission_summary[key].commission);
          allstats.commission_summary[key].number += Number(responseData.commission_summary[key].number);
          } else {
          allstats.commission_summary[key] = {
            total: Number(responseData.commission_summary[key].total),
            commission: Number(responseData.commission_summary[key].commission),
            number: Number(responseData.commission_summary[key].number)
          };
          }
        }
        if (startDate >= first_order_date) {
          var today = new Date('<?php 
    echo esc_html( $last_order_date );
    ?>');
          startDate.setDate(startDate.getDate() - <?php 
    echo esc_html( $batch_amount );
    ?>);
          if(loop == 1) {
            endDate.setDate(endDate.getDate() - <?php 
    echo esc_html( $batch_amount2 );
    ?>);
          } else {
            endDate.setDate(endDate.getDate() - <?php 
    echo esc_html( $batch_amount );
    ?>);
          }
          var progress = Math.floor(((today - startDate) / (today - first_order_date)) * 100);
          updateProgressBar(progress);
          getOrders();
        } else {
          updateAllStats(allstats);
        }
      },
      error: function(error) {
        console.log('Testing error: ', error);
      }
    });
    }

    function updateAllStats(allstats) {
    jQuery.ajax({
      url: '<?php 
    echo esc_url( admin_url( 'admin-ajax.php' ) );
    ?>',
      type: 'POST',
      data: {
      'action': 'wcusage_update_all_stats_data',
      'stats': allstats,
      'coupon_code': '<?php 
    echo esc_html( $coupon_code );
    ?>',
      'security': updateStatsNonce
      },
      success: function(response) {
        updateProgressBar(100);
        jQuery('#updated_total').html("Complete! Reloading...");
        location.reload();
      },
      error: function(error) {
        console.log(error);
        jQuery('#updated_total').html('<?php 
    echo wp_kses_post( $ajaxerrormessage );
    ?>');
      }
    });
    }

    function updateProgressBar(progress) {
      if(progress > 100) {
        progress = 100;
      }
      var progressBarFill = document.querySelector('.wcu-progress-bar-fill');
      progressBarFill.style.width = progress + '%';
      var updatedTotal = document.getElementById('updated_total');
      if(updatedTotal) {
        updatedTotal.textContent = '<?php 
    echo esc_html__( "Calculating statistics", "woo-coupon-usage" );
    ?>... ' + Math.round(progress) + '%';
      }
    }

    jQuery(document).ready(function() {
      getOrders();
    });
  </script>

  <style>
  .wcu-progress-bar {
    max-width: 500px;
    height: 8px;
    background-color: rgba(0, 0, 0, 0.06);
    border-radius: 10px;
    overflow: hidden;
    margin: 16px auto 0 auto;
  }
  .wcu-progress-bar-fill {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    border-radius: 10px;
    transition: width 0.4s ease;
    font-size: 0;
  }
  </style>

  <div class="wcu-loading-image wcu-loading-stats">
    <div class="wcu-loading-loader"></div>
    <p class="wcu-loading-loader-text" id="updated_total"><?php 
    echo esc_html__( "Calculating statistics", "woo-coupon-usage" );
    ?>...</p>
    <p class="wcu-loading-loader-subtext"><?php 
    echo esc_html__( "First visit - this will take a little longer than usual.", "woo-coupon-usage" );
    ?></p>
    <?php 
    if ( current_user_can( 'administrator' ) ) {
        ?>
    <p class="stuck-loading-message wcu-loading-loader-subtext" style="display:none; margin-top: 12px;">
      <?php 
        echo esc_html__( "Notice (admin only): Page constantly loading? Try refreshing the page.", "woo-coupon-usage" );
        ?> <a href='https://couponaffiliates.com/docs/affiliate-dashboard-is-not-showing' target='_blank'><?php 
        echo esc_html__( "Or click here", "woo-coupon-usage" );
        ?></a>.
    </p>
    <?php 
    }
    ?>
  </div>

  <div class="wcu-progress-bar">
    <div class="wcu-progress-bar-fill"></div>
  </div>

  <p class="wcu-loading-loader-subtext" style="text-align:center; margin-top: 8px;"><?php 
    echo esc_html__( "The page will reload automatically when it is complete.", "woo-coupon-usage" );
    ?></p>
        
<?php 
}

add_action(
    'wcusage_hook_update_all_stats_batch_ajax',
    'wcusage_update_all_stats_batch_ajax',
    10,
    2
);