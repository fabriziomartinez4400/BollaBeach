<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds new menu item to My Account page.
 *
 */
if( !function_exists( 'wcusage_account_tab_affiliate_link' ) ) {
function wcusage_account_tab_affiliate_link( $menu_links ){

	$wcusage_field_account_tab_affonly = wcusage_get_setting_value('wcusage_field_account_tab_affonly', 0);

	if( !$wcusage_field_account_tab_affonly || wcusage_is_user_affiliate( get_current_user_id() ) ) {

		$new = array( "coupon-affiliate" => esc_html__( "Affiliate", "woo-coupon-usage" ) );

		// Identify the position of 'logout' link in the menu_links array.
		$logout_link_key = array_search('customer-logout', array_keys($menu_links));

		// Insert 'Affiliate' link before 'Logout' link.
		$menu_links = array_slice( $menu_links, 0, $logout_link_key, true )
		+ $new
		+ array_slice( $menu_links, $logout_link_key, NULL, true );

	}

	return $menu_links;
	}
}
add_filter( 'woocommerce_account_menu_items', 'wcusage_account_tab_affiliate_link' );

$wcusage_field_account_tab_create = wcusage_get_setting_value('wcusage_field_account_tab_create', 0);
if(!$wcusage_field_account_tab_create) {

	/**
	 * Adds link for menu item to affiliate dashboard.
	 *
	 */
	if( !function_exists( 'wcusage_account_tab_affiliate_hook_endpoint' ) ) {
	function wcusage_account_tab_affiliate_hook_endpoint( $url, $endpoint, $value, $permalink ){

		if( $endpoint === "coupon-affiliate" ) {

			$url = wcusage_get_coupon_shortcode_page(0);

		}
		return $url;

	}
	}
	add_filter( 'woocommerce_get_endpoint_url', 'wcusage_account_tab_affiliate_hook_endpoint', 10, 4 );

} else {

	/**
	 * Adds new endpoint for Affiliate page.
	 */
	function wcusage_add_affiliate_endpoint() {
		add_rewrite_endpoint( 'coupon-affiliate', EP_ROOT | EP_PAGES );
	}
	add_action( 'init', 'wcusage_add_affiliate_endpoint' );

	/**
	 * Handles the content for the Affiliate endpoint.
	 */
	function wcusage_affiliate_content() {
		echo do_shortcode('[couponaffiliates]');
	}
	add_action( 'woocommerce_account_coupon-affiliate_endpoint', 'wcusage_affiliate_content' );

	/**
	 * Flush rewrite rules on plugin activation.
	 * Only once, to avoid performance issues.
	 */
	function wcusage_flush_rewrite_rules() {
		// Check if the endpoint is already registered to avoid unnecessary flush.
		if ( ! get_option( 'wcusage_affiliate_endpoint_registered' ) ) {
			flush_rewrite_rules();
			update_option( 'wcusage_affiliate_endpoint_registered', true );
		}
	}
	add_action( 'wp_loaded', 'wcusage_flush_rewrite_rules' );

}
