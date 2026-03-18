<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
    // Check If Subscriptions Active
    /**
     * Check if order is a subscription renewal
     *
     * @param int $order_id
     *
     * @return bool
     *
     */
    if ( !function_exists( 'wcusage_is_order_renewal' ) ) {
        function wcusage_is_order_renewal(  $order_id  ) {
            if ( wcs_order_contains_subscription( $order_id, 'renewal' ) || wcs_order_contains_subscription( $order_id, 'resubscribe' ) ) {
                return true;
            } else {
                return false;
            }
        }

    }
    /**
     * Check if order is a subscription parent
     *
     * @param int $order_id
     *
     * @return bool
     *
     */
    if ( !function_exists( 'wcusage_is_order_parent' ) ) {
        function wcusage_is_order_parent(  $order_id  ) {
            if ( wcs_order_contains_subscription( $order_id, 'parent' ) ) {
                return true;
            } else {
                return false;
            }
        }

    }
    /**
     * Check if order is a subscription parent and get parent ID
     *
     * @param int $order_id
     *
     * @return int
     *
     */
    if ( !function_exists( 'wcusage_sub_get_order_parent' ) ) {
        function wcusage_sub_get_order_parent(  $order_id  ) {
            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id, array(
                'order_type' => 'any',
            ) );
            if ( $subscriptions_ids ) {
                foreach ( $subscriptions_ids as $subscription_id => $subscription_obj ) {
                    if ( $subscription_obj->order->id == $order_id ) {
                        break;
                    }
                }
                // Stop the loop
                $subscription = new WC_Subscription($subscription_id);
                $order_id = ( method_exists( $subscription, 'get_parent_id' ) ? $subscription->get_parent_id() : $subscription->order->id );
                return $order_id;
            } else {
                return "";
            }
        }

    }
    /**
     * Check current renewal count if renewal allowed
     *
     * @param int $order_id
     *
     * @return int
     *
     */
    if ( !function_exists( 'wcusage_check_renewal_order_number' ) ) {
        function wcusage_check_renewal_order_number(  $order_id  ) {
            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id, array(
                'order_type' => 'any',
            ) );
            foreach ( $subscriptions_ids as $subscription_id => $subscription_obj ) {
                if ( $subscription_obj->order->id == $order_id ) {
                    break;
                }
            }
            // Stop the loop
            $subscription = new WC_Subscription($subscription_id);
            $orders_ids = $subscription->get_related_orders( 'ids', 'renewal' );
            $orders_ids = array_reverse( $orders_ids );
            $count = 0;
            foreach ( $orders_ids as $order ) {
                $count++;
                if ( $order == $order_id ) {
                    return $count;
                }
            }
            return $count;
        }

    }
    /**
     * When there is a renewal order, check if it has a lifetime referrer, and add them to the renewal if true
     *
     * @param object $order
     * @param object $subscription
     *
     */
    if ( !function_exists( 'wcusage_new_renewal_order' ) ) {
        function wcusage_new_renewal_order(  $order, $subscription  ) {
            $parent_order_id = ( method_exists( $subscription, 'get_parent_id' ) ? $subscription->get_parent_id() : $subscription->order->id );
            if ( $parent_order_id ) {
                $parentorder = wc_get_order( $parent_order_id );
                if ( $parentorder ) {
                    $parent_coupons = $parentorder->get_used_coupons();
                    foreach ( $parent_coupons as $parent_coupon ) {
                        $coupon = new WC_Coupon($parent_coupon);
                        $coupon_meta = get_post_meta( $coupon->get_id() );
                        $coupon_info = wcusage_get_coupon_info( $parent_coupon );
                        if ( $coupon_info[1] ) {
                            $order_id = $order->get_id();
                            wcusage_update_order_meta( $order_id, 'wcusage_referrer_coupon', $parent_coupon );
                        }
                    }
                    $wcusage_referrer_coupon = $parentorder->get_meta( 'wcusage_referrer_coupon' );
                    if ( $wcusage_referrer_coupon ) {
                        $order_id = $order->get_id();
                        wcusage_update_order_meta( $order_id, 'wcusage_referrer_coupon', $wcusage_referrer_coupon );
                    }
                    $lifetimeaffiliateparent = $parentorder->get_meta( 'lifetime_affiliate_coupon_referrer' );
                    if ( $lifetimeaffiliateparent ) {
                        $order_id = $order->get_id();
                        wcusage_update_order_meta( $order_id, 'lifetime_affiliate_coupon_referrer', $lifetimeaffiliateparent );
                    }
                }
            }
            return $order;
        }

    }
    add_filter(
        'wcs_renewal_order_created',
        'wcusage_new_renewal_order',
        10,
        2
    );
}
// End Check If Subscriptions Active
/**
 * Check order to see if renewal allowed
 *
 * @param int $order_id
 *
 * @return bool
 *
 */
if ( !function_exists( 'wcusage_check_if_renewal_allowed' ) ) {
    function wcusage_check_if_renewal_allowed(  $order_id  ) {
        $renewalcheck = true;
        if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id, array(
                'order_type' => 'any',
            ) );
            if ( wcs_order_contains_subscription( $order_id, 'parent' ) ) {
                $subscriptions_ids = false;
                // Allow parent orders
            }
            if ( $subscriptions_ids ) {
                $wcusage_field_subscriptions_renewals_limit = "0";
                $this_order_renewal_number = wcusage_check_renewal_order_number( $order_id );
                if ( !$wcusage_field_subscriptions_renewals_limit || $wcusage_field_subscriptions_renewals_limit >= $this_order_renewal_number ) {
                    // This checks if order is within the renewal limit set in settings
                    $wcusage_field_subscriptions_enable_renewals = wcusage_get_setting_value( 'wcusage_field_subscriptions_enable_renewals', '1' );
                    if ( $wcusage_field_subscriptions_enable_renewals ) {
                        // Renewals enabled - allow all.
                        $renewalcheck = true;
                    } else {
                        // Renewals disabled - checking if order is renewal
                        if ( wcusage_is_order_renewal( $order_id ) ) {
                            $renewalcheck = false;
                            // Renewal
                        } else {
                            $renewalcheck = true;
                            // Parent or other
                        }
                    }
                }
            } else {
                $renewalcheck = true;
                // not part of subscription
            }
        } else {
            $renewalcheck = true;
            // subs off
        }
        // Custom filter
        $renewalcheck = apply_filters( 'wcusage_is_renewal_allowed', $renewalcheck, $order_id );
        return $renewalcheck;
    }

}
/**
 * Get subscription renewal icon
 *
 * @param int $orderid
 *
 * @return string
 *
 */
if ( !function_exists( 'wcusage_get_sub_order_icon' ) ) {
    function wcusage_get_sub_order_icon(  $orderid  ) {
        $subicon = "";
        if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
            $subid = wcusage_sub_get_order_parent( $orderid );
            if ( wcusage_is_order_renewal( $orderid ) ) {
                $showsubid = "";
                if ( $option_show_orderid ) {
                    $showsubid = " (#" . $subid . ")";
                }
                $subicon = '<i class="fas fa-sync" title="' . esc_attr__( "Subscription Renewal Order", "woo-coupon-usage" ) . $showsubid . '" style="font-size: 10px;"></i> ';
            }
            if ( wcusage_is_order_parent( $orderid ) ) {
                $subicon = '<i class="fas fa-retweet" title="' . esc_attr__( "New Subscription (Parent Order)", "woo-coupon-usage" ) . '" style="font-size: 10px;"></i> ';
            }
        }
        return $subicon;
    }

}