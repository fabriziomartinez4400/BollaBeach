<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get info from the coupon based on coupon code
 *
 * @param string $coupon_code
 *
 * @return mixed
 *
 */
if( !function_exists( 'wcusage_get_coupon_info' ) ) {
	function wcusage_get_coupon_info($coupon_code) {

		// Static cache to reduce database queries
		static $coupon_info_cache = array();

		// Return cached result if available
		if (isset($coupon_info_cache[$coupon_code])) {
			return $coupon_info_cache[$coupon_code];
		}

		try {

			if($coupon_code) {

				$couponid = wcusage_get_coupon_id($coupon_code);

				$coupon_commission_percent = get_post_meta( $couponid, 'wcu_text_coupon_commission', true );
					if(!$coupon_commission_percent) { $coupon_commission_percent = wcusage_get_setting_value('wcusage_field_affiliate', '0'); }

				$coupon_user_id = get_post_meta( $couponid, 'wcu_select_coupon_user', true );

				$result = array($coupon_commission_percent, $coupon_user_id, $couponid);
				// Cache the result
				$coupon_info_cache[$coupon_code] = $result;
				return $result;

			} else {

				$result = array('', '', '');
				$coupon_info_cache[$coupon_code] = $result;
				return $result;
			
			}

		} catch (Exception $e) {

			$result = array();
			$coupon_info_cache[$coupon_code] = $result;
			return $result;

		}

	}
}
add_action('wcusage_hook_get_coupon_info', 'wcusage_get_coupon_info', 10, 1);

/**
 * Get coupon ID
 *
 * @param string $coupon_code
 *
 * @return mixed
 *
 */
function wcusage_get_coupon_id($coupon_code) {

	// Static cache for coupon IDs
	static $coupon_id_cache = array();

    if (!isset($coupon_code)) {
		return "";
	}

	// Return cached ID if available
	if (isset($coupon_id_cache[$coupon_code])) {
		return $coupon_id_cache[$coupon_code];
	}

    $coupon_id = wc_get_coupon_id_by_code(sanitize_text_field($coupon_code));

	if(!$coupon_id)	{
		$coupon_id_cache[$coupon_code] = 0;
		return 0;
	}

	$result = esc_html($coupon_id);
	$coupon_id_cache[$coupon_code] = $result;
    return $result;

}

/**
 * Safely get a WC_Coupon object without throwing exceptions.
 *
 * @param mixed $coupon_value
 *
 * @return WC_Coupon|false
 */
if( !function_exists( 'wcusage_get_coupon_object_safe' ) ) {
	function wcusage_get_coupon_object_safe( $coupon_value ) {
		if ( empty( $coupon_value ) ) {
			return false;
		}

		$coupon_id = 0;
		$coupon_value_string = is_string( $coupon_value ) ? $coupon_value : (string) $coupon_value;
		if ( function_exists( 'wc_get_coupon_id_by_code' ) ) {
			$coupon_id_by_code = wc_get_coupon_id_by_code( $coupon_value_string );
		} else {
			$coupon_id_by_code = 0;
		}

		if ( is_numeric( $coupon_value ) ) {
			$candidate_id = absint( $coupon_value );
			if ( $candidate_id && get_post_type( $candidate_id ) === 'shop_coupon' ) {
				$coupon_id = $candidate_id;
			} elseif ( $coupon_id_by_code ) {
				$coupon_id = $coupon_id_by_code;
			}
		} else {
			$coupon_id = $coupon_id_by_code;
		}

		if ( $coupon_id ) {
			$coupon_value = $coupon_id;
		}

		try {
			$coupon = new WC_Coupon( $coupon_value );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! $coupon || ! $coupon->get_id() ) {
			return false;
		}

		return $coupon;
	}
}

/**
 * Get coupon ID by coupon code via ajax
 *
 * @param string $coupon_code
 *
 * @return mixed
 *
 */
add_action('wp_ajax_wcusage_ajax_get_coupon_id', 'wcusage_ajax_get_coupon_id');
function wcusage_ajax_get_coupon_id() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcusage_ajax_get_coupon_id_nonce')) {
        wp_die('Security check failed');
    }
    if (!current_user_can('manage_options')) {
        wp_die('Access denied');
    }
	$coupon_id = wcusage_get_coupon_id($_POST['coupon_name']);
    echo esc_html($coupon_id);
    wp_die();
}

/**
 * Get info from the coupon based on ID
 *
 * @param string $couponid
 *
 * @return mixed
 *
 */
if( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
	function wcusage_get_coupon_info_by_id($couponid) {

		$options = get_option( 'wcusage_options' );

		$coupon_commission_percent = get_post_meta( $couponid, 'wcu_text_coupon_commission', true );
			if(!$coupon_commission_percent) { $coupon_commission_percent = wcusage_get_setting_value('wcusage_field_affiliate', '0'); }

		$coupon_user_id = get_post_meta( $couponid, 'wcu_select_coupon_user', true );

		$unpaid_commission = get_post_meta( $couponid, 'wcu_text_unpaid_commission', true );
			if(!$unpaid_commission) { $unpaid_commission = 0; }

    	$pending_payouts = get_post_meta( $couponid, 'wcu_text_pending_payment_commission', true );
			if(!$pending_payouts) { $pending_payouts = 0; }

		$wcusage_justcoupon = wcusage_get_setting_value('wcusage_field_justcoupon', '1');

		$coupon = get_the_title($couponid);

		// Getting the URL
		if($wcusage_justcoupon) {
			$secretid = $coupon;
		} else {
			$secretid = $coupon . "-" . $couponid;
		}

		$thepageurl = wcusage_get_coupon_shortcode_page(1, 0);

		// If secretid contains a & make it a URL safe string
		if (strpos($secretid, '&') !== false) {
			$secretid = str_replace('&', '%26', $secretid);
		}

		$uniqueurl = $thepageurl . 'couponid=' . $secretid;

		// Return
		return array($coupon_commission_percent, $coupon_user_id, $unpaid_commission, $coupon, $uniqueurl, $pending_payouts);

	}
}
add_action('wcusage_hook_get_coupon_info_by_id', 'wcusage_get_coupon_info_by_id', 10, 1);