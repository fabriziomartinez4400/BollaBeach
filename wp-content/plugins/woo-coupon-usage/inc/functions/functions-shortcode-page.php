<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if Page Contains Shortcode
 *
 * @param bool $seperate
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_page_contain_shortcode' ) ) {
	function wcusage_page_contain_shortcode($pageid) {
		if ( empty( $pageid ) ) {
			return false;
		}

		$post = get_post( $pageid );
		if ( ! $post || ( isset($post->post_status) && $post->post_status !== 'publish' ) ) {
			return false;
		}

		$content = isset( $post->post_content ) ? (string) $post->post_content : '';
		$shortcodes = array( 'couponaffiliates', 'couponusage', 'couponaffiliates-mla' );

		if(!function_exists('has_shortcode')) {
			return false;
		}

		// Directly use WordPress helper to detect shortcodes
		foreach ( $shortcodes as $sc ) {
			if ( has_shortcode( $content, $sc ) ) {
				return true;
			}
		}

		return false;

	}
}

/**
 * Get Coupon Shortcode Page
 *
 * @param bool $seperate
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_get_coupon_shortcode_page' ) ) {
	function wcusage_get_coupon_shortcode_page($seperate, $search = "1") {

	$options = get_option( 'wcusage_options' );
	$structure = get_option( 'permalink_structure' );

	$wcusage_field_portal_enable = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
	$portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
	if($wcusage_field_portal_enable && $portal_slug && wcusage_check_affiliate_portal_rewrite_rule() ) {
		$thepageurl = get_site_url() . '/' . $portal_slug . '/';
		if($seperate) {
			$seperatepermalink = "?";
			$thepageurl = $thepageurl . $seperatepermalink;
		}
		return $thepageurl;
	}

    $wcusage_dashboard_page = "";
    if(isset($options['wcusage_dashboard_page'])) {
      $wcusage_dashboard_page = $options['wcusage_dashboard_page'];
	  $wcusage_dashboard_page = apply_filters( 'change_wcusage_dashboard_page', $wcusage_dashboard_page );
    }

    if ( !get_post_status( $wcusage_dashboard_page ) ) {
			$option_group = get_option('wcusage_options');
			if ( ! is_array( $option_group ) ) {
				$option_group = array();
			}
      $option_group['wcusage_dashboard_page'] = "";
      update_option( 'wcusage_options', $option_group );
    }

    $seperatepermalink = "";
	if($seperate) {
		if($structure == "") { $seperatepermalink = "&"; } else { $seperatepermalink = "?"; }
	}

    $thepageid = "";

	if ( !$search || ($wcusage_dashboard_page && get_post_status ( $wcusage_dashboard_page ) == 'publish') ) {

		//$slug = get_post_field( 'post_name', $wcusage_dashboard_page );
		$slug = rtrim(get_permalink( $wcusage_dashboard_page ),'/');

		$thepageurl = $slug . $seperatepermalink;

	} else {

		global $wpdb;
		$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates]%' AND post_status = 'publish'";
		$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

		if(!$results) {
			$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponusage]%' AND post_status = 'publish'";
			$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		}

		$thepageurl = "";

		if($results) {

			foreach ( $results as $result ) {
				$thepageid = $result->ID;
				$slug = rtrim(get_permalink( $result->ID ),'/');
			}

			$thepageurl = $slug . $seperatepermalink;

		}

		if( !$wcusage_dashboard_page ) {
			if($thepageid) {
				$option_group = get_option('wcusage_options');
				$option_group['wcusage_dashboard_page'] = $thepageid;
				update_option( 'wcusage_options', $option_group );
			}
		}

	}

	return $thepageurl;

	}
}

/**
 * Get Coupon Shortcode Page ID
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_coupon_shortcode_page_id' ) ) {
	function wcusage_get_coupon_shortcode_page_id() {

		$options = get_option( 'wcusage_options' );

		if ( isset($options['wcusage_dashboard_page']) && get_post_status ( $options['wcusage_dashboard_page'] ) == 'publish' ) {

			$thepageid = $options['wcusage_dashboard_page'];
			$thepageid = apply_filters( 'change_wcusage_dashboard_page', $thepageid );

		} else {

			// Check transient cache first to avoid expensive post_content LIKE query
			$cached_page_id = get_transient( 'wcusage_coupon_shortcode_page_id' );
			if ( $cached_page_id !== false && get_post_status( $cached_page_id ) == 'publish' ) {
				return $cached_page_id;
			}

			global $wpdb;
			$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates]%' AND post_status = 'publish' LIMIT 1";
			$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

			if(!$results) {
				$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponusage]%' AND post_status = 'publish' LIMIT 1";
				$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
			}

			$thepageid = "";

			if($results) {

				foreach ( $results as $result ) {
					$thepageid = $result->ID;
				}

				// Cache the result for 24 hours
				if ( $thepageid ) {
					set_transient( 'wcusage_coupon_shortcode_page_id', $thepageid, DAY_IN_SECONDS );
				}

			}

		}

		return $thepageid;

	}
}

/**
 * Get Registration Shortcode Page
 *
 * @param bool $seperate
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_registration_shortcode_page' ) ) {
	function wcusage_get_registration_shortcode_page($seperate) {

		$structure = get_option( 'permalink_structure' );
		if($seperate) {
			if($structure == "") { $seperatepermalink = "&"; } else { $seperatepermalink = "?"; }
		} else {
			$seperatepermalink = "";
		}

		$wcusage_registration_page = wcusage_get_setting_value('wcusage_registration_page', '');

		if ( !get_post_status ( $wcusage_registration_page ) ) {
		$option_group = get_option('wcusage_options');
		$option_group['wcusage_registration_page'] = "";
		update_option( 'wcusage_options', $option_group );
		}

		if ( $wcusage_registration_page && get_post_status ( $wcusage_registration_page ) == 'publish' ) {

			$slug = rtrim(get_permalink( $wcusage_registration_page ),'/');
			$thepageurl = $slug . $seperatepermalink;

		} else {

			global $wpdb;
			$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates-register]%' AND post_status = 'publish'";
			$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

			$thepageurl = "";

			if($results) {

				foreach ( $results as $result ) {
					$slug = rtrim(get_permalink( $result->ID ),'/');
				}

				$thepageurl = $slug . $seperatepermalink;

			}

		}

		return $thepageurl;

	}
}

/**
 * Get Registration Shortcode Page ID
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_registration_shortcode_page_id' ) ) {
	function wcusage_get_registration_shortcode_page_id() {

		$options = get_option( 'wcusage_options' );

		$thepageid = "";

		if ( isset( $options['wcusage_registration_page'] ) && get_post_status ( $options['wcusage_registration_page'] ) == 'publish' ) {

      		$thepageid = $options['wcusage_registration_page'];

		} else {

			// Check transient cache first to avoid expensive post_content LIKE query
			$cached_page_id = get_transient( 'wcusage_registration_shortcode_page_id' );
			if ( $cached_page_id !== false && get_post_status( $cached_page_id ) == 'publish' ) {
				return $cached_page_id;
			}

			global $wpdb;
			$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates-register]%' AND post_status = 'publish' LIMIT 1";
			$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

			$thepageid = "";

			if($results) {

				foreach ( $results as $result ) {
					$thepageid = $result->ID;
				}

				// Cache the result for 24 hours
				if ( $thepageid ) {
					set_transient( 'wcusage_registration_shortcode_page_id', $thepageid, DAY_IN_SECONDS );
				}

			}

		}

		return $thepageid;

	}
}

/**
 * Get MLA Shortcode Page ID
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_mla_shortcode_page_id' ) ) {
	function wcusage_get_mla_shortcode_page_id() {

		$options = get_option( 'wcusage_options' );

		$thepageid = "";

		if ( $options['wcusage_mla_dashboard_page'] && get_post_status ( $options['wcusage_mla_dashboard_page'] ) == 'publish' ) {

			if(isset($options['wcusage_mla_dashboard_page'])) {
				$thepageid = $options['wcusage_mla_dashboard_page'];
				$thepageid = apply_filters( 'change_wcusage_mla_dashboard_page', $thepageid );
			} else {
				$thepageid = "";
			}

		} else {

			// Check transient cache first to avoid expensive post_content LIKE query
			$cached_page_id = get_transient( 'wcusage_mla_shortcode_page_id' );
			if ( $cached_page_id !== false && get_post_status( $cached_page_id ) == 'publish' ) {
				return $cached_page_id;
			}

			global $wpdb;
			$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates-mla]%' AND post_status = 'publish' LIMIT 1";
			$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

			$thepageurl = "";

			if($results) {

				foreach ( $results as $result ) {
					$thepageid =  $result->ID;
				}

				// Cache the result for 24 hours
				if ( $thepageid ) {
					set_transient( 'wcusage_mla_shortcode_page_id', $thepageid, DAY_IN_SECONDS );
				}

			}

		}

		return $thepageid;

	}
}

/**
 * Get MLA Shortcode Page
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_mla_shortcode_page' ) ) {
	function wcusage_get_mla_shortcode_page() {

		$options = get_option( 'wcusage_options' );

    if ( $options['wcusage_mla_dashboard_page'] && get_post_status ( $options['wcusage_mla_dashboard_page'] ) == 'publish' ) {

      if(isset($options['wcusage_mla_dashboard_page'])) {
        $thepageid = $options['wcusage_mla_dashboard_page'];
		$thepageid = apply_filters( 'change_wcusage_mla_dashboard_page', $thepageid );
      } else {
        $thepageid = "";
      }

		} else {

      $thepageid = wcusage_get_mla_shortcode_page_id();

    }

    $thepageurl = rtrim(get_permalink( $thepageid ),'/');

		return $thepageurl;

	}
}

/**
 * Get Registration Shortcode Page by ID
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_get_coupon_register_shortcode_page_id' ) ) {
	function wcusage_get_coupon_register_shortcode_page_id() {

		// Check transient cache first to avoid expensive post_content LIKE query
		$cached_page_id = get_transient( 'wcusage_coupon_register_shortcode_page_id' );
		if ( $cached_page_id !== false && get_post_status( $cached_page_id ) == 'publish' ) {
			return $cached_page_id;
		}

		global $wpdb;
		$query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[couponaffiliates-register]%' AND post_status = 'publish' LIMIT 1";
		$results = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

		$thepageurl = "";

		if($results) {
			foreach ( $results as $result ) {
				// Cache the result for 24 hours
				if ( $result->ID ) {
					set_transient( 'wcusage_coupon_register_shortcode_page_id', $result->ID, DAY_IN_SECONDS );
				}
				return $result->ID;
			}
		} else {
			return false;
		}

	}
}

/**
 * Clear shortcode page caches when pages are saved or deleted
 * This ensures the cache is refreshed when page content changes
 */
if( !function_exists( 'wcusage_clear_shortcode_page_caches' ) ) {
	function wcusage_clear_shortcode_page_caches( $post_id ) {
		// Only clear cache for pages
		if ( get_post_type( $post_id ) !== 'page' ) {
			return;
		}

		// Clear all shortcode page caches
		delete_transient( 'wcusage_coupon_shortcode_page_id' );
		delete_transient( 'wcusage_mla_shortcode_page_id' );
		delete_transient( 'wcusage_registration_shortcode_page_id' );
		delete_transient( 'wcusage_coupon_register_shortcode_page_id' );
	}
}
add_action( 'save_post', 'wcusage_clear_shortcode_page_caches' );
add_action( 'delete_post', 'wcusage_clear_shortcode_page_caches' );
