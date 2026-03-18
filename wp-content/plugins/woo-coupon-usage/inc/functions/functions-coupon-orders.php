<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Get Orders For Coupon Code Within Date Range
 *
 * @param string $coupon_code
 * @param date $start_date
 * @param date $end_date
 * @param int $numberoforders
 * @param bool $refresh
 * @param bool $update
 *
 * @return mixed
 *
 */
if( !function_exists( 'wcusage_wh_getOrderbyCouponCode' ) ) {
  function wcusage_wh_getOrderbyCouponCode( $coupon_code, $start_date, $end_date, $numberoforders = '', $refresh = 1, $update = 0, $alltime = false ) {

    $coupon_code = sanitize_text_field($coupon_code);
    $get_start_date = sanitize_text_field($start_date);
    $get_end_date = sanitize_text_field($end_date);

	$start_date = wcusage_convert_date_to_gmt($get_start_date, 0);
	$end_date = wcusage_convert_date_to_gmt($get_end_date, 1);

    $coupon_code = strtolower($coupon_code);
    $couponinfo = wcusage_get_coupon_info($coupon_code);

  	$options = get_option( 'wcusage_options' );
  	$wcu_save_all_stats_as_meta = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
    if(!$wcu_save_all_stats_as_meta) {
      delete_post_meta( $couponinfo[2], 'wcu_alltime_stats' );
    }

    $wcu_all_total_orders = "";
    $wcu_all_full_discount = "";
    $wcu_all_total_commission = "";

    $wcu_alltime_stats = get_post_meta( $couponinfo[2], 'wcu_alltime_stats', true );
  	if($wcu_alltime_stats && $wcu_save_all_stats_as_meta) {

  		if(isset($wcu_alltime_stats['total_orders'])) {
  			$wcu_all_total_orders = $wcu_alltime_stats['total_orders'];
  		}

  		if(isset($wcu_alltime_stats['full_discount'])) {
  			$wcu_all_full_discount = $wcu_alltime_stats['full_discount'];
  		}

  		if(isset($wcu_alltime_stats['total_commission'])) {
  			$wcu_all_total_commission = $wcu_alltime_stats['total_commission'];
  		}

  	}

  	$list_of_products = "";
	
  	//$refresh = 1;
  	if( $refresh || ($start_date && $end_date) || $numberoforders || !$wcu_all_total_orders || !$wcu_all_full_discount || !$wcu_all_total_commission || !$wcu_save_all_stats_as_meta ) {

  		global  $wpdb ;
  		$return_array = [];
  		$total_discount = 0;
  		$total_orders = 0;
  		$total_shipping = 0;
  		$total_count = 0;
  		$total_commission = 0;
		
  		$wcusage_field_order_sort = wcusage_get_setting_value('wcusage_field_order_sort', '');

  		$wcu_text_coupon_start_date = get_post_meta( $couponinfo[2], 'wcu_text_coupon_start_date', true );

  		if($wcu_text_coupon_start_date) {
  			if( strtotime($start_date) < strtotime($wcu_text_coupon_start_date) || !$start_date ) {
  				$start_date = $wcu_text_coupon_start_date;
  			}
  		}
      	if(!$start_date) { $start_date = "0001-01-01"; }

  		// Check if enable lifetime
  		$wcusage_field_lifetime_all = wcusage_get_setting_value('wcusage_field_lifetime_all', '0');
  		$wcu_coupon_enable_lifetime_commission = get_post_meta( $couponinfo[2], 'wcu_enable_lifetime_commission', true );
		$enable_renewals = wcusage_get_setting_value('wcusage_field_subscriptions_enable_renewals', '1');
		$subscription_renewals = is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
  		if( $wcusage_field_lifetime_all || $wcu_coupon_enable_lifetime_commission || ($enable_renewals && $subscription_renewals) ) {
  			$enablelifetime = true;
  		} else {
  			$enablelifetime = false;
  		}

  		$wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');
  		if(!$wcusage_field_order_type_custom) {
  			$statuses = wc_get_order_statuses();
  			if( isset( $statuses['wc-refunded'] ) ){
  	        	unset( $statuses['wc-refunded'] );
  	    	}
  		} else {
  			$statuses = $wcusage_field_order_type_custom;
  		}

		// Custom Orders Table or Posts Table
		if (class_exists(OrderUtil::class) && method_exists(OrderUtil::class, 'custom_orders_table_usage_is_enabled') && OrderUtil::custom_orders_table_usage_is_enabled()) {
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
			$post_date = "post_date_gmt";
			$post_type = "WHERE\r\n p.post_type = 'shop_order'";
			$post_status = "post_status";
			$post_id = "post_id";
		}

		// Query to get orders
		$query = $wpdb->prepare(
			"SELECT DISTINCT p.$id AS order_id, p.$post_date AS order_date
			FROM {$wpdb->prefix}$posts AS p
			LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi
				ON p.$id = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name = %s
			LEFT JOIN {$wpdb->prefix}$postmeta AS woi2
				ON p.$id = woi2.$post_id AND (
					(woi2.meta_key = 'lifetime_affiliate_coupon_referrer' AND woi2.meta_value = %s) OR
					(woi2.meta_key = 'wcusage_referrer_coupon' AND woi2.meta_value = %s)
				)
			WHERE p.$post_status IN ('" . implode("','", array_keys($statuses)) . "')
			AND (woi.order_id IS NOT NULL OR woi2.meta_value = %s AND woi2.meta_key IS NOT NULL)",
			$coupon_code, $coupon_code, $coupon_code, $coupon_code
		);

		if ($wcusage_field_order_sort != "completeddate") {
			$query .= $wpdb->prepare(" AND p.$post_date BETWEEN %s AND %s", $start_date, $end_date);
		} else {
			$query .= $wpdb->prepare(" AND p.$id IN (
				SELECT woi2.post_id
				FROM {$wpdb->prefix}postmeta AS woi2
				WHERE woi2.meta_key = '_completed_date' AND woi2.meta_value BETWEEN %s AND %s)", 
				$start_date, $end_date
			);
		}		

		if ($numberoforders) {
			$numberoforders = intval($numberoforders);
			$limit = "LIMIT " . $numberoforders;
		} else {
			$limit = "";
		}
		
		$query .= " ORDER BY order_date DESC $limit";		

		$orders = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		if (!is_array($orders)) {
			$orders = [];
		}

		$orders = array_reverse($orders);
		
  		$list_of_products = array();
      	$commission_summary = array();

  		$wcusage_show_tax = wcusage_get_setting_value('wcusage_field_show_tax', '0');

  		if ( !empty($orders) ) {
		$dp = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );

		// looping through all the order_id
		foreach ( $orders as $key => $the_order ) {
		


		$order_id = $the_order->order_id;
		$order = wc_get_order( $order_id );

		if($refresh && $update && $alltime) {

			// Clear existing pending meta
			delete_post_meta($order_id, 'wcusage_pending_commission');

			// Clear MLA commission so it gets recalculated fresh (unless "Never update saved commission" is enabled)
			$never_update_commission_meta = wcusage_get_setting_value('wcusage_field_enable_never_update_commission_meta', '0');
			if ( ! $never_update_commission_meta ) {
				wcusage_delete_order_meta( $order_id, 'wcu_mla_commission' );
			}

			// Check and add if pending (this handles the logic)
			if( function_exists('wcusage_check_and_add_pending_commission') ) {
				wcusage_check_and_add_pending_commission($order_id, $coupon_code);
			}

		}

		// if meta "lifetime_affiliate_coupon_referrer" is set, check if it's same as $coupon_code if not then skip
		$lifetime_affiliate_coupon_referrer = get_post_meta( $order_id, 'lifetime_affiliate_coupon_referrer', true );
		if( $lifetime_affiliate_coupon_referrer && $lifetime_affiliate_coupon_referrer != $coupon_code ) {
			continue;
		}

		// if meta "wcusage_referrer_coupon" is set, check if it's same as $coupon_code if not then skip
		$wcusage_referrer_coupon = get_post_meta( $order_id, 'wcusage_referrer_coupon', true );
		if( !$lifetime_affiliate_coupon_referrer && $wcusage_referrer_coupon && $wcusage_referrer_coupon != $coupon_code ) {
			continue;
		}

		$renewalcheck = wcusage_check_if_renewal_allowed($order_id);
		if(!$renewalcheck) {
			continue;
		}

        if($order_id) {
		
			$theorderstatus = $order->get_status();

			$theordertotal = $order->get_total();
			$theordertotaltax = $order->get_total_tax();

			$check_status_show = wcusage_check_status_show($theorderstatus);

			if(!$theorderstatus || !$theordertotal) { continue; }

			// Check Lifetime
			$lifetimecheck = wcusage_check_lifetime_or_coupon($order_id, $coupon_code);

			// Subscription renewals check
			$renewalcheck = wcusage_check_if_renewal_allowed($order_id);

			if ( ($theorderstatus == "completed" || $check_status_show) && $renewalcheck && $lifetimecheck ) {

				if($update) {
					$calculateorder = wcusage_calculate_order_data( $order_id, $coupon_code, 1, 0 );
				} else {
					$calculateorder = wcusage_calculate_order_data( $order_id, $coupon_code, 0, 1 );
				}
				
				$never_update_commission_meta = wcusage_get_setting_value('wcusage_field_enable_never_update_commission_meta', '0');
				
				if(isset($calculateorder['totalorders'])) {

					$shipping_data_total = 0;
					$return_array[$key]['order_id'] = $order_id;

					$order_totals = wcusage_get_order_totals( $order_id );

					// Get Totals For Order
					$return_array[$key]['total'] = $calculateorder['totalorders'];
					$return_array[$key]['total_discount'] = $calculateorder['totaldiscounts'];
					$return_array[$key]['total_shipping'] = $order_totals['total_shipping'];

					// Get Totals
					$this_total_discount = $return_array[$key]['total_discount'];
					$this_total_orders = $return_array[$key]['total'];
					$this_total_shipping = $return_array[$key]['total_shipping'];

					// Add To Combined Total
					$total_discount += (float)$this_total_discount;
					$total_orders += (float)$this_total_orders;
					$total_shipping += (float)$this_total_shipping;
					$total_count++;

					$affiliatecommission = $calculateorder['totalcommission'];
					$total_commission += (float)$affiliatecommission;

					// Get List Products
					$items = $order->get_items();
					$order_refunds = $order->get_refunds();
					$refunded_quantity = 0;
					foreach ( $items as $item_id => $item ) {
						$refunded_quantity = 0;
						foreach ( $order_refunds as $refund ) {
							foreach ( $refund->get_items() as $item_id => $item2 ) {

								if ( $item2->get_product_id() == $item['product_id'] ) {
									$refunded_quantity += abs( $item2->get_quantity() ); // Get Refund Qty
								}

							}
						}
						$product_id = $item->get_product_id();
						if ( !$product_id ) {
							$product_id = 0;
						}
						$product_quantity = $item->get_quantity() - (float)$refunded_quantity;
						if ( !$product_quantity ) {
							$product_quantity = 0;
						}
						if ( isset($list_of_products[$product_id]) ) {
							$list_of_products[$product_id] += (float)$product_quantity;
						} else {
							$list_of_products[$product_id] = (float)$product_quantity;
						}

					}

					}

				}

				if($start_date != "0001-01-01") {

					if(!empty($calculateorder['commission_summary'])) {
						$a2 = $calculateorder['commission_summary'];
						if(!is_array($a2)) { $a2 = maybe_unserialize($a2); }
						if(!is_array($a2)) { $a2 = array(); }
						$a1 = $commission_summary;
						foreach (array_keys($a1 + $a2) as $key) {
							$a1_total = isset($a1[$key]['total']) && is_numeric($a1[$key]['total']) ? $a1[$key]['total'] : 0;
							$a2_total = isset($a2[$key]['total']) && is_numeric($a2[$key]['total']) ? $a2[$key]['total'] : 0;
							$a2_total = wcusage_convert_order_value_to_currency($order, $a2_total);
							$total1 = $a1_total + $a2_total;
							$commission_summary[$key]['total'] = $total1;

							$a1_subtotal = isset($a1[$key]['subtotal']) && is_numeric($a1[$key]['subtotal']) ? $a1[$key]['subtotal'] : 0;
							$a2_subtotal = isset($a2[$key]['subtotal']) && is_numeric($a2[$key]['subtotal']) ? $a2[$key]['subtotal'] : 0;
							$a2_subtotal = wcusage_convert_order_value_to_currency($order, $a2_subtotal);
							$subtotal1 = $a1_subtotal + $a2_subtotal;
							$commission_summary[$key]['subtotal'] = $subtotal1;
							
							$a1_commission = isset($a1[$key]['commission']) && is_numeric($a1[$key]['commission']) ? $a1[$key]['commission'] : 0;
							$a2_commission = isset($a2[$key]['commission']) && is_numeric($a2[$key]['commission']) ? $a2[$key]['commission'] : 0;
							$a2_commission = wcusage_convert_order_value_to_currency($order, $a2_commission);
							$commission1 = $a1_commission + $a2_commission;
							$commission_summary[$key]['commission'] = $commission1;

							$a1_number = isset($a1[$key]['number']) && is_numeric($a1[$key]['number']) ? $a1[$key]['number'] : 0;
							$a2_number = isset($a2[$key]['number']) && is_numeric($a2[$key]['number']) ? $a2[$key]['number'] : 0;
							$commission_summary[$key]['number'] = $a1_number + $a2_number;
						}
					}
				}

          		}

  			}
			

  		}

		$allstats = array();
		$allstats['total_orders'] = $total_orders;
		$allstats['full_discount'] = $total_discount;
		$allstats['total_commission'] = $total_commission;
		$allstats['total_shipping'] = $total_shipping;
		$allstats['total_count'] = $total_count;
		if($start_date != "0001-01-01") {
			$allstats['commission_summary'] = $commission_summary;
		}
  		if( (!$start_date || $start_date == "0001-01-01") && $refresh && $update) {
  			update_post_meta( $couponinfo[2], 'wcu_alltime_stats', $allstats );
  		}
  		//delete_post_meta( $couponinfo[2], 'wcu_alltime_stats' );

  	} else {

  		if(isset($wcu_alltime_stats['total_orders'])) {
  			$total_orders = $wcu_alltime_stats['total_orders'];
  		} else {
  			$total_orders = 0;
  		}

  		if(isset($wcu_alltime_stats['full_discount'])) {
  			$total_discount = $wcu_alltime_stats['full_discount'];
  		} else {
  			$total_discount = 0;
  		}

  		if(isset($wcu_alltime_stats['total_commission'])) {
  			$total_commission = $wcu_alltime_stats['total_commission'];
  		} else {
  			$total_commission = 0;
  		}

  		if(isset($wcu_alltime_stats['total_shipping'])) {
  			$total_shipping = $wcu_alltime_stats['total_shipping'];
  		} else {
  			$total_shipping = 0;
  		}

  		if(isset($wcu_alltime_stats['total_count'])) {
  			$total_count = $wcu_alltime_stats['total_count'];
  		} else {
  			$total_count = 0;
  		}
		
      	if(isset($wcu_alltime_stats['commission_summary'])) {
  			$commission_summary = $wcu_alltime_stats['commission_summary'];
  		} else {
  			$commission_summary = array();
  		}

  	}

  	if( !$total_orders || !is_numeric($total_orders) ) {
  		$total_orders = 0;
  	}
  	if( !$total_shipping || !is_numeric($total_shipping) ) {
  		$total_shipping = 0;
  	}
  	if(!$list_of_products) {
  		$list_of_products = "";
  	}

	$return_array['orders'] = $orders;
  	$return_array['list_of_products'] = $list_of_products;
  	$return_array['total_count'] = $total_count;
  	$return_array['full_discount'] = $total_discount;
  	$return_array['total_shipping'] = $total_shipping;
  	$return_array['total_orders'] = $total_orders;
  	$return_array['total_commission'] = $total_commission;
    $return_array['commission_summary'] = $commission_summary;
	$return_array['allstats'] = $allstats;
  	return $return_array;

  }
}

/**
 * Check if the current order status can be shown
 *
 * @param string $theorderstatus
 *
 * @return bool
 *
 */
if( !function_exists( 'wcusage_check_status_show' ) ) {
	function wcusage_check_status_show($theorderstatus) {

		$wcusage_field_order_type = wcusage_get_setting_value('wcusage_field_order_type', '');
		$wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');

		$isthistrue = false;

    if(is_string($theorderstatus)) {

  		// Check Old Settings
  		if(!$wcusage_field_order_type_custom) {
  			if($wcusage_field_order_type != "completed") {
  				if ( $theorderstatus == "processing" || $theorderstatus == "completed" ) {
  					$isthistrue = true;
  				}
  			}
  			if($wcusage_field_order_type == "completed") {
  				if ( $theorderstatus == "completed" ) {
  					$isthistrue = true;
  				}
  			}
  		}

  		// Check New Settings
  		if($wcusage_field_order_type_custom) {
  			foreach( $wcusage_field_order_type_custom as $key2 => $status2 ) {
  				$thestatus = wc_get_order_status_name( $key2 );
  				$thisstatusname = wc_get_order_status_name( $theorderstatus );
  				if( $thisstatusname == $thestatus ) {
  					$isthistrue = true;
  				}
  			}
  		}

    }

		return $isthistrue;

	}
}

/**
 * Get a coupons total sales, commission, and referrals for the current year
 *
 * @param string $couponid
 *
 * @return mixed
 *
 */
if( !function_exists( 'wcusage_get_coupon_yearly_totals' ) ) {
	function wcusage_get_coupon_yearly_totals($coupon_id, $update = false) {
		
		update_post_meta($coupon_id, 'wcusage_yearly_summary_data', '');
		$wcusage_monthly_summary_data = get_post_meta($coupon_id, 'wcusage_monthly_summary_data', true);
		if(!$wcusage_monthly_summary_data) { $wcusage_monthly_summary_data = array(); }

		$coupon_code = get_the_title($coupon_id);

		$total_sales_year = 0;
		$total_commission_year = 0;
		$total_referrals_year = 0;

		for ($i = 1; $i <= 12; $i++) {

			$first_day = date('Y-m-d', mktime(0, 0, 0, $i, 1, date('Y'))); // First day of the month
			$last_day = date('Y-m-d', mktime(0, 0, 0, $i + 1, 0, date('Y'))); // Last day of the month

			if( isset($wcusage_monthly_summary_data[strtotime($first_day)]) ) {

				if(isset($wcusage_monthly_summary_data[strtotime($first_day)]) && $wcusage_monthly_summary_data[strtotime($first_day)]) {

					$monthly_summary_data = $wcusage_monthly_summary_data[strtotime($first_day)];

					$total_sales_year += (float)$monthly_summary_data['totalorders'] - (float)$monthly_summary_data['totaldiscounts'];
					$total_commission_year += $monthly_summary_data['totalcommission'];
					$total_referrals_year += $monthly_summary_data['total_count'];

				}

			} else {

				$orders = wcusage_wh_getOrderbyCouponCode( $coupon_code, $first_day, $last_day, '', 1, 0 );

				$totalorders = $orders['total_orders'];
				$totaldiscounts = $orders['full_discount'];
				$totalordersexcl = $totalorders - $totaldiscounts;
				$totalcommission = $orders['total_commission'];
				$ordercount = $orders['total_count'];
				$list_of_products = $orders['list_of_products'];
				$order_summary = $orders['commission_summary'];

				$total_sales_year += $totalordersexcl;
				$total_commission_year += $totalcommission;
				$total_referrals_year += $ordercount;

				// Return Totals
				$return_array = [];
				$return_array['totalorders'] = $totalorders;
				$return_array['totaldiscounts'] = $totaldiscounts;
				$return_array['totalordersexcl'] = $totalordersexcl;
				$return_array['totalcommission'] = $totalcommission;
				$return_array['total_count'] = $ordercount;
				$return_array['list_of_products'] = $list_of_products;
				$return_array['order_summary'] = $order_summary;
				$monthly_summary_data[strtotime($first_day)] = $return_array;

			}

		}

		if(isset($monthly_summary_data)) {
			update_post_meta($coupon_id, 'wcusage_monthly_summary_data', $monthly_summary_data);
		}

		$array = array(
			'sales' => $total_sales_year,
			'commission' => $total_commission_year,
			'referrals' => $total_referrals_year,
		);

		return $array;

	}
}

// Convert date to GMT
if( !function_exists( 'wcusage_convert_date_to_gmt' ) ) {
	function wcusage_convert_date_to_gmt($date, $end = 0) {
		// Convert the date to a timestamp
		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return $date;
		}
		// Convert to GMT using WordPress' built-in timezone functions
		$gmt_offset = get_option( 'gmt_offset' ); // Get the GMT offset from settings
		$gmt_timestamp = $timestamp - ( $gmt_offset * HOUR_IN_SECONDS );
		if($end) {
			// Add 1 day to the end date
			$gmt_timestamp = $gmt_timestamp + ( 24 * HOUR_IN_SECONDS );
			// Take 1 second off the end date
			$gmt_timestamp = $gmt_timestamp - 1;
		}
		// Format and return the GMT date
		return gmdate( 'Y-m-d H:i:s', $gmt_timestamp );
	}
}

// Convert date from GMT
if( !function_exists( 'wcusage_convert_date_from_gmt' ) ) {
	function wcusage_convert_date_from_gmt($date) {
		// Convert the date to a timestamp
		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return $date;
		}
		// Convert to GMT using WordPress' built-in timezone functions
		$gmt_offset = get_option( 'gmt_offset' ); // Get the GMT offset from settings
		$gmt_timestamp = $timestamp + ( $gmt_offset * HOUR_IN_SECONDS );
		// Format and return the GMT date
		return date( 'Y-m-d H:i:s', $gmt_timestamp );
	}
}