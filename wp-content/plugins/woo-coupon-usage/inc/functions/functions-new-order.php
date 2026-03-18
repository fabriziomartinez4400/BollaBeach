<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * On order status change update all stats
 *
 * @param int $order_id
 *
 */
if ( !function_exists( 'wcusage_new_order_update_stats' ) ) {
    function wcusage_new_order_update_stats(  $order_id, $status_from = "", $status_to = ""  ) {
        $order = wc_get_order( $order_id );
        $options = get_option( 'wcusage_options' );
        // For new orders, status_to might be empty, so get current status
        if ( $status_to == "" && $order ) {
            $status_to = $order->get_status();
        }
        if ( $status_to != "refunded" ) {
            $check_status_from_show = wcusage_check_status_show( $status_from );
            $check_status_to_show = wcusage_check_status_show( $status_to );
            // Check if order is to be added to stats
            $show_order = 0;
            $wcusage_all_updated = wcusage_order_meta( $order_id, 'wcusage_all_updated' );
            if ( !$wcusage_all_updated ) {
                if ( !$check_status_from_show && $check_status_to_show ) {
                    wcusage_update_order_meta( $order_id, 'wcusage_all_updated', 1 );
                    $show_order = 1;
                }
                if ( !$show_order ) {
                    $wcusage_field_order_type_custom = wcusage_get_setting_value( 'wcusage_field_order_type_custom', '' );
                    if ( $wcusage_field_order_type_custom ) {
                        if ( isset( $options['wcusage_field_order_type_custom']['wc-pending'] ) && isset( $options['wcusage_field_order_type_custom']['wc-processing'] ) ) {
                            if ( $status_from == "pending" && $status_to == "processing" ) {
                                wcusage_update_order_meta( $order_id, 'wcusage_all_updated', 1 );
                                $show_order = 1;
                            }
                        }
                    }
                }
            }
            // Check if order is to be removed from stats
            $remove_order = 0;
            $wcusage_all_updated = wcusage_order_meta( $order_id, 'wcusage_all_updated' );
            if ( $check_status_from_show && !$check_status_to_show && $wcusage_all_updated ) {
                wcusage_delete_order_meta( $order_id, 'wcusage_all_updated' );
                $remove_order = 1;
            }
            // Check if refresh
            $coupon_refresh = wcusage_order_meta( $order_id, 'wcusage_referrer_refresh' );
            $coupon_refresh_prev = wcusage_order_meta( $order_id, 'wcusage_referrer_refresh_prev' );
            $meta_data = [];
            if ( $order ) {
                // Set the currency conversion rate for order.
                $enablecurrency = wcusage_get_setting_value( 'wcusage_field_enable_currency', '0' );
                $enable_save_rate = wcusage_get_setting_value( 'wcusage_field_enable_currency_save_rate', '0' );
                if ( $enablecurrency && $enable_save_rate ) {
                    $currencycode = $order->get_currency();
                    $currency_rate = wcusage_get_currency_rate( $currencycode );
                    $meta_data['wcusage_currency_conversion'] = $currency_rate;
                }
                $lifetimeaffiliate = wcusage_order_meta( $order_id, 'lifetime_affiliate_coupon_referrer' );
                $affiliatereferrer = wcusage_order_meta( $order_id, 'wcusage_referrer_coupon' );
                $wcusage_field_mla_enable = wcusage_get_setting_value( 'wcusage_field_mla_enable', '0' );
                // Update All-Time Stats
                $wcusage_field_enable_coupon_all_stats_meta = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
                if ( $lifetimeaffiliate ) {
                    $calculateorder = wcusage_calculate_order_data(
                        $order_id,
                        $lifetimeaffiliate,
                        1,
                        0,
                        1
                    );
                    // If is a lifetime affiliate order.
                    if ( $wcusage_field_enable_coupon_all_stats_meta ) {
                        if ( $show_order ) {
                            // If old order NOT shown and new order IS shown
                            do_action(
                                'wcusage_hook_update_all_stats_single',
                                $lifetimeaffiliate,
                                $order_id,
                                1,
                                1
                            );
                            // Add
                        }
                        if ( $remove_order ) {
                            // If old order IS shown and new order NOT shown
                            do_action(
                                'wcusage_hook_update_all_stats_single',
                                $lifetimeaffiliate,
                                $order_id,
                                0,
                                1
                            );
                            // Remove
                        }
                    }
                    $coupon_info = wcusage_get_coupon_info( $lifetimeaffiliate );
                    $coupon_user_id = $coupon_info[1];
                    $paidcommission = ( isset( $calculateorder['totalcommission'] ) ? $calculateorder['totalcommission'] : 0 );
                    $meta_data = wcusage_update_ml_affiliate_parents(
                        $meta_data,
                        $coupon_user_id,
                        $order,
                        $lifetimeaffiliate,
                        $paidcommission
                    );
                } elseif ( $affiliatereferrer ) {
                    $calculateorder = wcusage_calculate_order_data(
                        $order_id,
                        $affiliatereferrer,
                        1,
                        0,
                        1
                    );
                    if ( $wcusage_field_enable_coupon_all_stats_meta ) {
                        // Update Coupon Stats
                        if ( $show_order ) {
                            // If old status NOT shown and new status IS shown
                            do_action(
                                'wcusage_hook_update_all_stats_single',
                                $affiliatereferrer,
                                $order_id,
                                1,
                                1
                            );
                            // Add
                        }
                        if ( $remove_order ) {
                            // If old status IS shown and new status NOT shown
                            do_action(
                                'wcusage_hook_update_all_stats_single',
                                $affiliatereferrer,
                                $order_id,
                                0,
                                1
                            );
                            // Remove
                        }
                    }
                    $coupon_info = wcusage_get_coupon_info( $affiliatereferrer );
                    $coupon_user_id = $coupon_info[1];
                    $paidcommission = ( isset( $calculateorder['totalcommission'] ) ? $calculateorder['totalcommission'] : 0 );
                    $user_parents = get_user_meta( $coupon_user_id, 'wcu_ml_affiliate_parents', true );
                    $meta_data = wcusage_update_ml_affiliate_parents(
                        $meta_data,
                        $coupon_user_id,
                        $order,
                        $affiliatereferrer,
                        $paidcommission
                    );
                } else {
                    // Coupons
                    if ( class_exists( 'WooCommerce' ) ) {
                        if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
                            $coupons_array = $order->get_coupon_codes();
                        } else {
                            $coupons_array = $order->get_used_coupons();
                        }
                        foreach ( $coupons_array as $coupon_code ) {
                            $calculateorder = wcusage_calculate_order_data(
                                $order_id,
                                $coupon_code,
                                1,
                                0,
                                1
                            );
                            if ( $wcusage_field_enable_coupon_all_stats_meta ) {
                                // Update Coupon Stats
                                if ( $show_order ) {
                                    // If old status NOT shown and new status IS shown
                                    do_action(
                                        'wcusage_hook_update_all_stats_single',
                                        $coupon_code,
                                        $order_id,
                                        1,
                                        1
                                    );
                                    // Add
                                }
                                if ( $remove_order ) {
                                    // If old status IS shown and new status NOT shown
                                    do_action(
                                        'wcusage_hook_update_all_stats_single',
                                        $coupon_code,
                                        $order_id,
                                        0,
                                        1
                                    );
                                    // Remove
                                }
                            }
                            $coupon_info = wcusage_get_coupon_info( $coupon_code );
                            $coupon_user_id = $coupon_info[1];
                            $paidcommission = ( isset( $calculateorder['totalcommission'] ) ? $calculateorder['totalcommission'] : 0 );
                            $meta_data = wcusage_update_ml_affiliate_parents(
                                $meta_data,
                                $coupon_user_id,
                                $order,
                                $coupon_code,
                                $paidcommission
                            );
                        }
                    }
                }
                if ( $coupon_refresh ) {
                    wcusage_delete_order_meta( $order_id, 'wcusage_referrer_refresh' );
                }
                if ( $coupon_refresh_prev ) {
                    wcusage_delete_order_meta( $order_id, 'wcusage_referrer_refresh_prev' );
                }
            }
        }
        if ( !empty( $meta_data ) ) {
            wcusage_update_order_meta_bulk( $order_id, $meta_data );
        }
    }

}
add_action(
    'woocommerce_checkout_update_order_meta',
    'wcusage_new_order_update_stats',
    10,
    2
);
add_action(
    'woocommerce_order_status_changed',
    'wcusage_new_order_update_stats',
    10,
    3
);
add_action(
    'woocommerce_process_shop_order_meta',
    'wcusage_new_order_update_stats',
    10,
    3
);
// NOTE: The wcusage_all_updated flag is intentionally NOT deleted on order completion.
// Deleting it on 'woocommerce_order_status_completed' caused double-counting because
// woocommerce_order_status_changed fires after that hook, sees no flag, and re-adds
// the order to the all-time stats a second time. The flag is only cleared by the
// $remove_order path when an order moves from a counted status to a non-counted one.
/* Update MLA Parents When New Order Placed */
function wcusage_update_ml_affiliate_parents(
    $meta_data,
    $coupon_user_id,
    $order,
    $coupon_code = '',
    $paidcommission = 0
) {
}

/**
 * Adds referrer meta and activity log on new order
 *
 * @param int $order_id
 *
 */
function wcusage_on_new_order_set_coupon_referrer(  $order_id  ) {
    // Get settings
    $wcusage_field_url_referrals = wcusage_get_setting_value( 'wcusage_field_url_referrals', '0' );
    // Get cookie
    $cookie = "";
    $url_applied = "";
    $coupon_applied = "";
    if ( isset( $_COOKIE['wcusage_referral'] ) ) {
        $cookie = $_COOKIE['wcusage_referral'];
    }
    if ( !$cookie && isset( $_COOKIE['wcusage_referral_code'] ) ) {
        $cookie = $_COOKIE['wcusage_referral_code'];
        $url_applied = 1;
    }
    $cookie = sanitize_text_field( $cookie );
    // If $cookie is not a coupon applied to order
    $order = wc_get_order( $order_id );
    if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
        $coupons_array = $order->get_coupon_codes();
    } else {
        $coupons_array = $order->get_used_coupons();
    }
    if ( $cookie ) {
        if ( !in_array( $cookie, $coupons_array ) ) {
            $url_applied = 1;
        }
    }
    // If one of coupons is a referral coupon
    if ( !$cookie && !$url_applied && $coupons_array ) {
        foreach ( $coupons_array as $coupon_code ) {
            $coupon_info = wcusage_get_coupon_info( $coupon_code );
            $coupon_id = $coupon_info[2];
            $coupon_user = get_post_meta( $coupon_id, 'wcu_select_coupon_user', true );
            if ( $coupon_user ) {
                $coupon_applied = 1;
                $activity_log = wcusage_add_activity( $order_id, 'referral', $coupon_code );
            }
        }
    }
    // Activity Log
    if ( $cookie && !$coupon_applied && $wcusage_field_url_referrals ) {
        $coupon_info = wcusage_get_coupon_info( $cookie );
        $coupon_id = $coupon_info[2];
        $coupon_user = get_post_meta( $coupon_id, 'wcu_select_coupon_user', true );
        if ( $coupon_user ) {
            $activity_log = wcusage_add_activity( $order_id, 'referral', $cookie );
        }
    }
    // URL Referrals
    if ( $cookie && $url_applied && $wcusage_field_url_referrals ) {
        $meta_data = [];
        $coupon = new WC_Coupon($cookie);
        $wcusage_field_allow_assigned_user = wcusage_get_setting_value( 'wcusage_field_allow_assigned_user', 1 );
        $current_user_id = get_current_user_id();
        $iscouponusers = wcusage_iscouponusers( $coupon->get_code(), $current_user_id );
        if ( $wcusage_field_allow_assigned_user || !$iscouponusers ) {
            // if $cookie is not one of the coupons used in order
            $order = wc_get_order( $order_id );
            if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
                $coupons_array = $order->get_coupon_codes();
            } else {
                $coupons_array = $order->get_used_coupons();
            }
            if ( !in_array( $cookie, $coupons_array ) ) {
                $meta_data['wcusage_referrer_coupon'] = $cookie;
                $coupon_user_id = $coupon_info[1];
                $meta_data['wcusage_affiliate_user'] = $coupon_user_id;
            }
            if ( !empty( $meta_data ) ) {
                wcusage_update_order_meta_bulk( $order_id, $meta_data );
            }
        }
    }
}

add_action(
    'woocommerce_checkout_order_processed',
    'wcusage_on_new_order_set_coupon_referrer',
    10,
    1
);
/**
 * On thank you page delete cookies
 *
 * @param int $order_id
 *
 */
function wcusage_on_new_order_delete_cookies(  $order_id  ) {
    // Remove cookies if set and wcusage_remove_cookies option is enabled
    $wcusage_remove_cookies = wcusage_get_setting_value( 'wcusage_remove_cookies', '0' );
    if ( $wcusage_remove_cookies ) {
        // wcusage_referral
        if ( isset( $_COOKIE['wcusage_referral'] ) ) {
            unset($_COOKIE['wcusage_referral']);
            wcusage_set_cookie( 'wcusage_referral', '', -1 );
        }
        // wcusage_referral_code
        if ( isset( $_COOKIE['wcusage_referral_code'] ) ) {
            unset($_COOKIE['wcusage_referral_code']);
            wcusage_set_cookie( 'wcusage_referral_code', '', -1 );
        }
        // wcusage_referral_click
        if ( isset( $_COOKIE['wcusage_referral_click'] ) ) {
            unset($_COOKIE['wcusage_referral_click']);
            wcusage_set_cookie( 'wcusage_referral_click', '', -1 );
        }
        // wcusage_referral_campaign
        if ( isset( $_COOKIE['wcusage_referral_campaign'] ) ) {
            unset($_COOKIE['wcusage_referral_campaign']);
            wcusage_set_cookie( 'wcusage_referral_campaign', '', -1 );
        }
    }
}

add_action(
    'woocommerce_thankyou',
    'wcusage_on_new_order_delete_cookies',
    20,
    1
);