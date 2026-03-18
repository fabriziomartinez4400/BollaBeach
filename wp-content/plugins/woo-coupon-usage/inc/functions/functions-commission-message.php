<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Returns the commission amount message for a coupon ID
 *
 * @param int $postid
 *
 * @return string
 *
 */
if ( !function_exists( 'wcusage_commission_message' ) ) {
    function wcusage_commission_message(  $postid  ) {
        $coupon_user_id = get_post_meta( $postid, 'wcu_select_coupon_user', true );
        $user = get_user_by( 'ID', $coupon_user_id );
        // Global Defaults
        $option_affiliate = wcusage_get_setting_value( 'wcusage_field_affiliate', '0' );
        $option_affiliate_fixed_order = wcusage_get_setting_value( 'wcusage_field_affiliate_fixed_order', '0' );
        $option_affiliate_fixed_product = wcusage_get_setting_value( 'wcusage_field_affiliate_fixed_product', '0' );
        // *** User Role Values *** //
        $affiliate_per_user = wcusage_get_setting_value( 'wcusage_field_affiliate_per_user', '0' );
        $apply_role_commission = $affiliate_per_user;
        if ( wcu_fs()->is__premium_only() && $apply_role_commission ) {
            $done = 0;
            if ( $affiliate_per_user ) {
                $user_roles = $user->roles;
                foreach ( $user_roles as $role ) {
                    $fixed_order_role = wcusage_get_setting_value( 'wcusage_field_affiliate_percent_role_' . $role, '' );
                    if ( $fixed_order_role != "" ) {
                        $option_affiliate = $fixed_order_role;
                        $done = 1;
                    }
                    $fixed_product_role = wcusage_get_setting_value( 'wcusage_field_affiliate_fixed_product_role_' . $role, '' );
                    if ( $fixed_product_role != "" ) {
                        $option_affiliate_fixed_product = $fixed_product_role;
                        $done = 1;
                    }
                    $percent_role = wcusage_get_setting_value( 'wcusage_field_affiliate_fixed_order_role_' . $role, '' );
                    if ( $percent_role != "" ) {
                        $option_affiliate_fixed_order = $percent_role;
                        $done = 1;
                    }
                    if ( $done ) {
                        break;
                    }
                }
            }
        }
        // *** Coupon Values *** //
        $wcu_text_coupon_commission = get_post_meta( $postid, 'wcu_text_coupon_commission', true );
        $wcu_text_coupon_commission_fixed_order = get_post_meta( $postid, 'wcu_text_coupon_commission_fixed_order', true );
        $wcu_text_coupon_commission_fixed_product = get_post_meta( $postid, 'wcu_text_coupon_commission_fixed_product', true );
        // %
        if ( $wcu_text_coupon_commission != "" ) {
            $option_affiliate = $wcu_text_coupon_commission;
        }
        // Fixed
        if ( $wcu_text_coupon_commission_fixed_order != "" ) {
            $option_affiliate_fixed_order = $wcu_text_coupon_commission_fixed_order;
        }
        // Per Product
        if ( $wcu_text_coupon_commission_fixed_product != "" ) {
            $option_affiliate_fixed_product = $wcu_text_coupon_commission_fixed_product;
        }
        // *** Message *** //
        $wcusage_show_commission = wcusage_get_setting_value( 'wcusage_field_show_commission', '1' );
        $combined_commission = wcusage_get_the_commission_message(
            $postid,
            $wcu_text_coupon_commission,
            $wcu_text_coupon_commission_fixed_order,
            $wcu_text_coupon_commission_fixed_product,
            $option_affiliate,
            $option_affiliate_fixed_order,
            $option_affiliate_fixed_product
        );
        return $combined_commission;
    }

}
/**
 * Works on what commission message to show
 *
 * @return string
 *
 */
if ( !function_exists( 'wcusage_get_the_commission_message' ) ) {
    function wcusage_get_the_commission_message(
        $postid,
        $wcu_text_coupon_commission,
        $wcu_text_coupon_commission_fixed_order,
        $wcu_text_coupon_commission_fixed_product,
        $option_affiliate,
        $option_affiliate_fixed_order,
        $option_affiliate_fixed_product
    ) {
        $combined_commission = "";
        $multitypes = "";
        $options = get_option( 'wcusage_options' );
        $coupon_commission_message = get_post_meta( $postid, 'wcu_text_coupon_commission_message', true );
        if ( $coupon_commission_message == "" && $wcu_text_coupon_commission == "" && $wcu_text_coupon_commission_fixed_order == "" && $wcu_text_coupon_commission_fixed_product == "" ) {
            $coupon_commission_message = wcusage_get_setting_value( 'wcusage_field_affiliate_custom_message', '' );
        }
        if ( !$coupon_commission_message ) {
            if ( $option_affiliate ) {
                $combined_commission .= $option_affiliate . "%";
                $multitypes = 1;
            }
            if ( $option_affiliate_fixed_order ) {
                if ( $multitypes ) {
                    $combined_commission .= " + ";
                }
                $combined_commission .= wcusage_format_price( $option_affiliate_fixed_order );
                $multitypes = 1;
            }
            if ( $option_affiliate_fixed_product ) {
                if ( $multitypes ) {
                    $combined_commission .= " + ";
                }
                $combined_commission .= wcusage_format_price( $option_affiliate_fixed_product ) . esc_html__( ' / Product', 'woo-coupon-usage' );
            }
        } else {
            $combined_commission .= $coupon_commission_message;
        }
        if ( !$combined_commission ) {
            $combined_commission = 0;
        }
        return $combined_commission;
    }

}