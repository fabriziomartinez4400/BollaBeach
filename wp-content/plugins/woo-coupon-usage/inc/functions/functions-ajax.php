<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'wcusage_ajax_resolve_coupon' ) ) {
    function wcusage_ajax_resolve_coupon(  $postid, $couponcode  ) {
        $resolved_postid = absint( $postid );
        $resolved_code = sanitize_text_field( $couponcode );
        if ( $resolved_postid && get_post_type( $resolved_postid ) === 'shop_coupon' ) {
            $status = get_post_status( $resolved_postid );
            if ( $status === 'trash' || $status === false ) {
                $resolved_postid = 0;
            }
        } else {
            $resolved_postid = 0;
        }
        if ( !$resolved_postid && $resolved_code && function_exists( 'wc_get_coupon_id_by_code' ) ) {
            $resolved_postid = wc_get_coupon_id_by_code( $resolved_code );
        }
        if ( !$resolved_postid ) {
            echo '<div class="wcusage-error">' . esc_html__( 'Error: Coupon does not exist.', 'woo-coupon-usage' ) . '</div>';
            return array(0, '');
        }
        if ( !$resolved_code ) {
            $resolved_code = get_the_title( $resolved_postid );
        }
        return array($resolved_postid, $resolved_code);
    }

}
/**
 * Tab - Latest Orders
 */
if ( !function_exists( 'wcusage_load_page_orders' ) ) {
    function wcusage_load_page_orders() {
        check_ajax_referer( 'wcusage_dashboard_ajax_nonce' );
        $language = ( isset( $_POST["language"] ) ? sanitize_text_field( $_POST["language"] ) : '' );
        $postid = ( isset( $_POST["postid"] ) ? sanitize_text_field( $_POST["postid"] ) : '' );
        $couponcode = ( isset( $_POST["couponcode"] ) ? sanitize_text_field( $_POST["couponcode"] ) : '' );
        wcusage_load_custom_language_wpml( $language );
        // WPML Support
        list( $resolved_postid, $resolved_code ) = wcusage_ajax_resolve_coupon( $postid, $couponcode );
        if ( !$resolved_postid ) {
            exit;
        }
        ?>
    <?php 
        $startdate = ( isset( $_POST["startdate"] ) ? sanitize_text_field( $_POST["startdate"] ) : '' );
        $enddate = ( isset( $_POST["enddate"] ) ? sanitize_text_field( $_POST["enddate"] ) : '' );
        $isordersstartset = $startdate !== '';
        $status = ( isset( $_POST["status"] ) ? sanitize_text_field( $_POST["status"] ) : '' );
        do_action(
            'wcusage_hook_tab_latest_orders',
            $resolved_postid,
            $resolved_code,
            $startdate,
            $enddate,
            $isordersstartset,
            sanitize_text_field( $status )
        );
        exit;
    }

}
add_action( 'wp_ajax_wcusage_load_page_orders', 'wcusage_load_page_orders' );
add_action( 'wp_ajax_nopriv_wcusage_load_page_orders', 'wcusage_load_page_orders' );
/**
 * Tab - Referral URL Stats
 */
if ( !function_exists( 'wcusage_load_referral_url_stats' ) ) {
    function wcusage_load_referral_url_stats() {
        check_ajax_referer( 'wcusage_dashboard_ajax_nonce' );
        $language = ( isset( $_POST["language"] ) ? sanitize_text_field( $_POST["language"] ) : '' );
        $postid = ( isset( $_POST["postid"] ) ? sanitize_text_field( $_POST["postid"] ) : '' );
        $couponcode = ( isset( $_POST["couponcode"] ) ? sanitize_text_field( $_POST["couponcode"] ) : '' );
        wcusage_load_custom_language_wpml( $language );
        // WPML Support
        list( $resolved_postid, $resolved_code ) = wcusage_ajax_resolve_coupon( $postid, $couponcode );
        if ( !$resolved_postid ) {
            exit;
        }
        ?>
    <?php 
        $campaign = ( isset( $_POST["campaign"] ) ? sanitize_text_field( $_POST["campaign"] ) : '' );
        $page = ( isset( $_POST["page"] ) ? sanitize_text_field( $_POST["page"] ) : '' );
        $converted = ( isset( $_POST["converted"] ) ? sanitize_text_field( $_POST["converted"] ) : '' );
        do_action(
            'wcusage_hook_tab_referral_url_stats',
            $resolved_postid,
            $resolved_code,
            $campaign,
            $page,
            $converted
        );
        exit;
    }

}
add_action( 'wp_ajax_wcusage_load_referral_url_stats', 'wcusage_load_referral_url_stats' );
add_action( 'wp_ajax_nopriv_wcusage_load_referral_url_stats', 'wcusage_load_referral_url_stats' );
/**
 * Tab - Statistics
 */
if ( !function_exists( 'wcusage_load_page_statistics' ) ) {
    function wcusage_load_page_statistics() {
        check_ajax_referer( 'wcusage_dashboard_ajax_nonce' );
        $language = ( isset( $_POST["language"] ) ? sanitize_text_field( $_POST["language"] ) : '' );
        $postid = ( isset( $_POST["postid"] ) ? sanitize_text_field( $_POST["postid"] ) : '' );
        $couponcode = ( isset( $_POST["couponcode"] ) ? sanitize_text_field( $_POST["couponcode"] ) : '' );
        wcusage_load_custom_language_wpml( $language );
        // WPML Support
        list( $resolved_postid, $resolved_code ) = wcusage_ajax_resolve_coupon( $postid, $couponcode );
        if ( !$resolved_postid ) {
            exit;
        }
        $combinedcommission = ( isset( $_POST["combinedcommission"] ) ? $_POST["combinedcommission"] : '' );
        $refresh = ( isset( $_POST["refresh"] ) ? sanitize_text_field( $_POST["refresh"] ) : '' );
        do_action(
            'wcusage_hook_tab_statistics',
            $resolved_postid,
            $resolved_code,
            wcusage_convert_symbols_revert( $combinedcommission ),
            $refresh
        );
        exit;
    }

}
add_action( 'wp_ajax_wcusage_load_page_statistics', 'wcusage_load_page_statistics' );
add_action( 'wp_ajax_nopriv_wcusage_load_page_statistics', 'wcusage_load_page_statistics' );
// Pro
if ( wcu_fs()->can_use_premium_code() ) {
}