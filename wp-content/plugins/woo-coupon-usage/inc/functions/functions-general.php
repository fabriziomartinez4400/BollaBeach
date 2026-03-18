<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Filters to render text from wp editor fields
 *
 */
add_filter( 'wcu_meta_content', 'wptexturize' );
add_filter( 'wcu_meta_content', 'convert_smilies' );
add_filter( 'wcu_meta_content', 'convert_chars' );
add_filter( 'wcu_meta_content', 'wpautop' );
add_filter( 'wcu_meta_content', 'shortcode_unautop' );
add_filter( 'wcu_meta_content', 'prepend_attachment' );

/**
 * Load admin ajax only on pages that include main plugin shortcode.
 *
 */
add_action( 'wp_enqueue_scripts', 'wcusage_enqueue_frontend_ajax', 100 );
if( !function_exists( 'wcusage_enqueue_frontend_ajax' ) ) {
 function wcusage_enqueue_frontend_ajax() {
   $post_id = get_the_ID();
   $dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
   $mla_dashboard_page = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
   if( function_exists( 'is_product' ) ) {
     if( ( !is_front_page() && !is_product() ) || $post_id == $dashboard_page ) {
       if( $post_id == $dashboard_page || $post_id == $mla_dashboard_page || is_account_page() || wcusage_page_contain_shortcode($post_id) ) {
         add_filter( 'script_loader_tag', 'wcusage_remove_defer_js', 100, 1 );
       }
     }
   }
 }
}

/**
 * Replaces "defer" with nothing.
 *
 */
function wcusage_remove_defer_js( $url ) {
  return str_replace( ' defer', '', $url );
}

/**
 * Fix javascript deferred conflicts on pages that include main plugin shortcode.
 *
 */
add_action( 'wp_head', 'wcusage_fix_defer_js', 1 );
if( !function_exists( 'wcusage_fix_defer_js' ) ) {
  function wcusage_fix_defer_js() {
    if ( is_plugin_active( 'wp-rocket/wp-rocket.php' )
    || is_plugin_active( 'perfmatters/perfmatters.php' )
    || is_plugin_active( 'autoptimize/autoptimize.php' )
    || is_plugin_active( 'flying-press/flying-press.php' )
    || is_plugin_active( 'wp-compress-image-optimizer/wp-compress.php' ) ) {
      $post_id = get_the_ID();
      $dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
      $mla_dashboard_page = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
      $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
      if( $post_id == $dashboard_page
      || $post_id == $mla_dashboard_page
      || is_account_page()
      || get_query_var( 'affiliate_portal' )
      || ( !is_admin() && isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], $wcusage_portal_slug ) !== false ) ) {

        // WP Rocket
        if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
          add_filter( 'pre_get_rocket_option_defer_all_js', '__return_zero' );
        }
        // Perfmatters
        if ( is_plugin_active( 'perfmatters/perfmatters.php' ) ) {
          add_filter('perfmatters_defer_js', function($defer) { return false; });
        }
        // Autoptimize
        if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
          add_filter('autoptimize_filter_js_defer','__return_false');
        }
        // FlyingPress
        if ( is_plugin_active( 'flying-press/flying-press.php' ) ) {
          add_filter('flying_press_is_cacheable', false);
          add_filter('flying_press_exclude_from_minify:js', function($exclude_keywords){
            $exclude_keywords = array_merge($exclude_keywords, array('woo-coupon-usage'));
            return $exclude_keywords;
          });
        }

      }
    }
  }
}

/**
 * Fix WP Compress conflicts on affiliate dashboard and portal pages.
 * Hooked on template_redirect (earlier than wp_head) so WP Compress
 * filters are registered before it processes the page.
 */
add_action( 'template_redirect', 'wcusage_fix_wp_compress', 1 );
if( !function_exists( 'wcusage_fix_wp_compress' ) ) {
  function wcusage_fix_wp_compress() {
    if ( ! is_plugin_active( 'wp-compress-image-optimizer/wp-compress.php' ) ) {
      return;
    }
    if ( is_admin() ) {
      return;
    }

    $post_id = get_the_ID();
    $dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
    $mla_dashboard_page = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');

    $is_affiliate_page = (
      get_query_var( 'affiliate_portal' )
      || $post_id == $dashboard_page
      || $post_id == $mla_dashboard_page
      || is_account_page()
      || ( isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], $wcusage_portal_slug ) !== false )
    );

    if ( ! $is_affiliate_page ) {
      return;
    }

    // Disable JavaScript optimization
    add_filter('wpc_js_exclude', function($exclude_list) {
      if (!is_array($exclude_list)) {
        $exclude_list = array();
      }
      $exclude_list[] = 'woo-coupon-usage';
      $exclude_list[] = 'wcusage';
      $exclude_list[] = 'jquery.cookie';
      $exclude_list[] = 'portal.js';
      $exclude_list[] = 'tab-settings';
      $exclude_list[] = 'dark-mode';
      return $exclude_list;
    });
    // Disable CSS optimization
    add_filter('wpc_css_exclude', function($exclude_list) {
      if (!is_array($exclude_list)) {
        $exclude_list = array();
      }
      $exclude_list[] = 'woo-coupon-usage';
      $exclude_list[] = 'wcusage';
      return $exclude_list;
    });
    // Disable page caching
    add_filter('wpc_disable_caching', '__return_true');
    // Disable minification
    add_filter('wpc_disable_minify', '__return_true');
    // Disable lazy load
    add_filter('wpc_disable_lazyload', '__return_true');
  }
}

/**
 * Fix caching issues on affiliate dashboard
 *
 */
function wcusage_fix_cache() {
  if ( is_admin() ) {
    return;
  }

  if ( get_query_var( 'affiliate_portal' ) ) {
    $is_affiliate_page = true;
  } else {
    $post_id = get_the_ID();
    $dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
    $mla_dashboard_page = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
    $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
    $is_affiliate_page = (
      $post_id == $dashboard_page
      || $post_id == $mla_dashboard_page
      || is_account_page()
      || ( !is_admin() && isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], $wcusage_portal_slug ) !== false )
    );
  }

  if ( ! $is_affiliate_page ) {
    return;
  }

  if ( ! defined( 'DONOTCACHEPAGE' ) ) {
    define( 'DONOTCACHEPAGE', true );
  }
  if ( ! defined( 'DONOTCACHEDB' ) ) {
    define( 'DONOTCACHEDB', true );
  }
  if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
    define( 'DONOTCACHEOBJECT', true );
  }
  if ( ! defined( 'DONOTMINIFY' ) ) {
    define( 'DONOTMINIFY', true );
  }
  if ( ! defined( 'DONOTCDN' ) ) {
    define( 'DONOTCDN', true );
  }
  if ( ! defined( 'DONOTLAZYLOAD' ) ) {
    define( 'DONOTLAZYLOAD', true );
  }

  if ( function_exists( 'nocache_headers' ) ) {
    nocache_headers();
  }
}
add_action( 'template_redirect', 'wcusage_fix_cache' );

/**
 * Filter to control Elementor's page caching.
 *
 * @param bool $allow Whether to allow page cache. Default true.
 * @return bool Modified cache allowance.
 */
function wcusage_elementor_page_cache_control( $use_cache, $post_id ) {
  $post_id = get_the_ID();
  $dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
  $mla_dashboard_page = wcusage_get_setting_value('wcusage_mla_dashboard_page', '');
  $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');  
  if( $post_id == $dashboard_page
  || $post_id == $mla_dashboard_page
  || is_account_page()
  || ( !is_admin() && isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], $wcusage_portal_slug ) !== false ) ) {
      return false;
  }
  return $use_cache;
}
add_filter( 'elementor/frontend/use_cache', 'wcusage_elementor_page_cache_control', 10, 2 );

/**
 * Round down number to decimals
 *
 * @param int $decimal
 * @param int $precision
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_roundDown' ) ) {
  function wcusage_roundDown( $decimal, $precision )
  {

    $sign = ( $decimal > 0 ? 1 : -1 );
    $base = pow( 10, $precision );
  	$number = floor( abs( $decimal ) * $base ) / $base * $sign;

  	if($number <= 0) {
  		return 0;
  	} else {
  		return floor( abs( $decimal ) * $base ) / $base * $sign;
  	}

  }
}

/**
 * Function to trim number to 2 decimals
 *
 * @param int $number
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_trim_number' ) ) {
  function wcusage_trim_number($number) {
  	return number_format((float)str_replace( ',', '', $number ) , 2, '.', '');
  }
}

/**
 * Function to create shortcode that shows edit account form from WooCommerce
 *
 * @param mixed $atts
 *
 * @return mixed
 *
 */
function wcusage_customer_edit_account_html_shortcode($atts) {
  // Define shortcode attributes
  $atts = shortcode_atts(
      array(
        'user' => '',
        'text' => 'Edit Account',
      ),
      $atts,
      'user_account_edit'
  );

  // Check if WooCommerce is active
  if (!class_exists('WooCommerce')) {
      return 'WooCommerce is not active';
  }

  // Check if user is provided
  if (empty($atts['user'])) {
      return 'Please provide a user ID';
  }

  // Verify user exists
  $user = get_user_by('id', $atts['user']);
  if (!$user) {
      return 'Invalid user ID';
  }

  // Check if user is logged in and has permission
  if (!is_user_logged_in() || (!current_user_can('edit_users') && get_current_user_id() != $atts['user'])) {
      return 'You don\'t have permission to edit this account';
  }

  // Start output buffering
  ob_start();

  // Include WooCommerce account edit form
  wc_get_template('myaccount/form-edit-account.php', array(
      'user' => $user
  ));

  return ob_get_clean();
}
add_shortcode('wcusage_customer_edit_account_html', 'wcusage_customer_edit_account_html_shortcode');

/**
 * Function to create the redirect for shortcode page when edit profilee
 *
 */
if( !function_exists( 'wcusage_custom_profile_redirect' ) ) {
  function wcusage_custom_profile_redirect() {

      if(wcusage_get_coupon_shortcode_page_id()) {
  			if ( get_queried_object_id() == wcusage_get_coupon_shortcode_page_id() ) {
  				wp_safe_redirect( $_SERVER['REQUEST_URI'] );
  				exit;
  			}
  		}

      $wcusage_field_portal_enable = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
      $portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
  
      if($wcusage_field_portal_enable && $portal_slug) {
        if ( strpos( $_SERVER['REQUEST_URI'], $portal_slug ) !== false ) {
          wp_safe_redirect( $_SERVER['REQUEST_URI'] );
          exit;
        }
      }

  }
}
add_action( 'profile_update', 'wcusage_custom_profile_redirect', 12 );

/**
 * Function to create the redirect for shortcode page when login
 *
 * @param string $redirect
 * @param string $user
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_custom_login_redirect' ) ) {
  function wcusage_custom_login_redirect( $redirect, $user ) {

  		if( wcusage_get_coupon_shortcode_page_id() ) {

  			$prev_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
  			$prev_path = str_replace( home_url(), '', $prev_url );
  		  $page = get_page_by_path( $prev_path );

  			if ( $page->ID == wcusage_get_coupon_shortcode_page_id() ) {

  				$redirect = get_page_link( wcusage_get_coupon_shortcode_page_id() );
  				wp_safe_redirect( $redirect, 302 );
  				exit;

  			}

        $wcusage_field_portal_enable = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
        $portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
        if($wcusage_field_portal_enable && $portal_slug) {
          if ( strpos( $prev_path, $portal_slug ) !== false ) {
            $redirect = home_url() . $portal_slug;
            wp_safe_redirect( $redirect, 302 );
            exit;
          }
        }

  		}

  		return $redirect;

  }
}
add_action( 'woocommerce_login_redirect', 'wcusage_custom_login_redirect', 9999, 2 );


/**
 * Retrieve the capability required for Coupon Affiliates admin pages.
 *
 * @return string
 */
if ( ! function_exists( 'wcusage_get_admin_menu_capability' ) ) {
  function wcusage_get_admin_menu_capability() {

    $default_capability = 'administrator';
    $options = get_option( 'wcusage_options', array() );

    $configured_capability = $default_capability;
    if ( isset( $options['wcusage_field_admin_permission'] ) && is_string( $options['wcusage_field_admin_permission'] ) ) {
      $maybe_capability = sanitize_key( $options['wcusage_field_admin_permission'] );
      if ( '' !== $maybe_capability ) {
        $configured_capability = $maybe_capability;
      }
    }

    if ( current_user_can( 'administrator' ) ) {
      $configured_capability = 'administrator';
    }

    $configured_capability = apply_filters( 'wcusage_admin_menu_capability', $configured_capability, $options );

    if ( ! is_string( $configured_capability ) ) {
      $configured_capability = $default_capability;
    } else {
      $configured_capability = sanitize_key( $configured_capability );
      if ( '' === $configured_capability ) {
        $configured_capability = $default_capability;
      }
    }

    return $configured_capability;
  }
}

/**
 * Check if user has admin access based on settings
 *
 * @return bool
 *
 */
if( !function_exists( 'wcusage_check_admin_access' ) ) {
  function wcusage_check_admin_access() {

    $capability = wcusage_get_admin_menu_capability();
    $custom_filter = (bool) apply_filters( 'wcusage_custom_admin_access', false );

    if ( $custom_filter ) {
      return true;
    }

    if ( $capability && current_user_can( $capability ) ) {
      return true;
    }

    if ( current_user_can( 'administrator' ) ) {
      return true;
    }

    return false;

  }
}

/**
 * Check if coupon same as lifetime referrer assigned to it
 *
 * @param int $order_id
 * @param string $coupon_code
 *
 * @return bool
 *
 */
if( !function_exists( 'wcusage_check_lifetime_or_coupon' ) ) {
  function wcusage_check_lifetime_or_coupon($order_id, $coupon_code) {
  	$wcu_lifetime_referrer = wcusage_order_meta( $order_id, 'lifetime_affiliate_coupon_referrer', true );
  	if($wcu_lifetime_referrer) {
  		if($wcu_lifetime_referrer != $coupon_code) {
  			$lifetimecheck = false;
  		} else {
  			$lifetimecheck = true;
  		}
  	} else {
  		$lifetimecheck = true;
  	}
  	return $lifetimecheck;
  }
}

/**
 * Get a random color part used in wcusage_random_color()
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_random_color_part' ) ) {
  function wcusage_random_color_part() {
      return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
  }
}

/**
 * Get a random color code
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_random_color' ) ) {
  function wcusage_random_color() {
      return wcusage_random_color_part() . wcusage_random_color_part() . wcusage_random_color_part();
  }
}

/**
 * Convert order value to main currency
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_convert_order_value_to_currency' ) ) {
  function wcusage_convert_order_value_to_currency($orderinfo, $the_value) {

    if($orderinfo) {

      $currencycode = $orderinfo->get_currency();
      $wcusage_currency_conversion = wcusage_order_meta( $orderinfo->get_id(), 'wcusage_currency_conversion', true );

      $enable_save_rate = wcusage_get_setting_value('wcusage_field_enable_currency_save_rate', '0');
      if(!$wcusage_currency_conversion || !$enable_save_rate) {
        $wcusage_currency_conversion = "";
      }

      $enablecurrency = wcusage_get_setting_value('wcusage_field_enable_currency', '0');

      if($enablecurrency && $currencycode) {
        $the_value = wcusage_calculate_currency($currencycode, $the_value, $wcusage_currency_conversion);
      }

    }

    return $the_value;

  }
}

/**
 * Get woocommerce currency symbol
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_get_currency_symbol' ) ) {
  function wcusage_get_currency_symbol() {
  	if( function_exists('get_woocommerce_currency_symbol') ) {
  		$currency_symbol = get_woocommerce_currency_symbol();
  	} else {
  		$currency_symbol = "";
  	}
  	return $currency_symbol;
  }
}

/**
 * Converts Symbols In Ajax to Stop Modsec Firewall Block
 *
 * @param int $combined_commission
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_convert_symbols' ) ) {
  function wcusage_convert_symbols($combined_commission) {
  	$combined_commission = str_replace("%", "[[percent]]", $combined_commission);
  	$combined_commission = str_replace("+", "[[plus]]", $combined_commission);
  	$combined_commission = str_replace("$", "[[dollar]]", $combined_commission);
  	$combined_commission = str_replace("£", "[[pound]]", $combined_commission);
  	$combined_commission = str_replace("€", "[[euro]]", $combined_commission);
  	return sanitize_text_field($combined_commission);
  }
}

/**
 * Reverts Symbols In Ajax to Stop Modsec Firewall Block
 *
 * @param int $combined_commission
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_convert_symbols_revert' ) ) {
  function wcusage_convert_symbols_revert($combined_commission) {
  	$combined_commission = str_replace("[[percent]]", "%", $combined_commission);
  	$combined_commission = str_replace("[[plus]]", "+", $combined_commission);
  	$combined_commission = str_replace("[[dollar]]", "$", $combined_commission);
  	$combined_commission = str_replace("[[pound]]", "£", $combined_commission);
  	$combined_commission = str_replace("[[euro]]", "€", $combined_commission);
  	return sanitize_text_field($combined_commission);
  }
}

/**
 * Returns language code
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_get_language_code' ) ) {
  function wcusage_get_language_code() {
  	// Get Language
  	if (class_exists('SitePress')) {
  	  global $sitepress;
  	  $language = ICL_LANGUAGE_CODE;
  	} else {
  		$language = "";
  	}
  }
}

/**
 * WPML Support Function
 *
 * @param string $language
 *
 */
if( !function_exists( 'wcusage_load_custom_language_wpml' ) ) {
    function wcusage_load_custom_language_wpml($language) {
        if (class_exists('SitePress')) {
          global $sitepress;
          $sitepress->switch_lang($language, true);
          if ( defined('WCUSAGE_UNIQUE_PLUGIN_PATH') ) {
            $plugin_main = WCUSAGE_UNIQUE_PLUGIN_PATH . 'woo-coupon-usage.php';
            load_plugin_textdomain( 'woo-coupon-usage', false, dirname( plugin_basename( $plugin_main ) ) . '/languages' );
          } else {
            load_plugin_textdomain( 'woo-coupon-usage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
          }
        }
    }
}

/**
 * Returns ajax error message
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_ajax_error' ) ) {
  function wcusage_ajax_error() {

  $ajaxerrormessage = '<br/><span style="color: red; font-weight: bold;">' . esc_html( __( 'ERROR: Failed to load ajax request. Session may have timed out. Refresh the page to try again.', 'woo-coupon-usage' ) ) . '</span>';
    if(current_user_can( 'edit_posts' )) {
      $ajaxerrormessage .= '<br/>Admin: If this keeps happening, <a href="https://couponaffiliates.com/docs/error-ajax-request/" target="_blank"><strong>click here</strong></a> for more information.';
    }

    return $ajaxerrormessage;

  }
}

/**
 * Returns username for ID
 *
 * @return string
 *
 */
function wcusage_get_username_by_id($user_id) {

  $user = get_user_by( 'ID', $user_id );
  $user_name = $user->user_login;

  return $user_name;

}

/**
 * Check if a coupon needs stats refresh
 */
if (!function_exists('wcusage_check_if_refresh_needed')) {
    function wcusage_check_if_refresh_needed($postid) {

        // Get options
        $options = get_option('wcusage_options');

				/*** REFRESH STATS? ***/
				$force_refresh_stats = 0;
        $never_update_commission_meta = wcusage_get_setting_value('wcusage_field_enable_never_update_commission_meta', '0');

				$wcu_last_refreshed = get_post_meta( $postid, 'wcu_last_refreshed', true );
				$wcu_alltime_stats = get_post_meta( $postid, 'wcu_alltime_stats', true );
        
				$combined_commission = wcusage_commission_message($postid);
				$current_commission_message = get_post_meta( $postid, 'wcu_commission_message', true );

        // This checks to see if commission amount updated, if so then refresh stats
        if($combined_commission != $current_commission_message) {
          update_post_meta( $postid, 'wcu_commission_message', $combined_commission );
          if(!$never_update_commission_meta) {
            $force_refresh_stats = 1;
          }
        }

				// Force refresh stats if coupon usage is more than 10, but stats are not set
				$wcusage_field_enable_coupon_all_stats_meta = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
				$wcusage_field_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '0');
				if($wcusage_field_enable_coupon_all_stats_meta && !$wcusage_field_hide_all_time) {
					if(isset($the_coupon_usage) && $the_coupon_usage > 10) {
						$wcu_alltime_stats = get_post_meta($postid, 'wcu_alltime_stats', true);
						if(!$wcu_alltime_stats || empty($wcu_alltime_stats['total_count']) || $wcu_alltime_stats['total_count'] == 0) {
							$force_refresh_stats = 1;
						}
					}
				}
				
				// Get force refresh date
				$wcusage_refresh_date = "";
				if(isset($options['wcusage_refresh_date'])) {
					$wcusage_refresh_date = $options['wcusage_refresh_date'];
				}

				// Check if batch refresh enabled
				$wcusage_field_enable_coupon_all_stats_batch = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_batch', '1');

				// Check if force refresh needed
        if( $force_refresh_stats || ( !$never_update_commission_meta && $wcusage_refresh_date && ($wcusage_refresh_date > $wcu_last_refreshed) ) ) {
					$force_refresh_stats = 1;
					if(!$wcusage_field_enable_coupon_all_stats_batch) {
						update_post_meta( $postid, 'wcu_last_refreshed', $wcusage_refresh_date );
					}
				}

				// Check if force refresh not done
				if(!$wcu_last_refreshed) {
					// If coupon usage is 0 and coupon is newer than 20 minutes old, do not force refresh and set stats to 0
					$c = new WC_Coupon($postid);
					$the_coupon_usage = $c->get_usage_count();
					if(empty($wcu_alltime_stats) && (!$the_coupon_usage || $the_coupon_usage == 0)) {
						$wcu_last_refreshed = time();
						update_post_meta( $postid, 'wcu_last_refreshed', $wcu_last_refreshed );
						$wcu_alltime_stats = array();
						$wcu_alltime_stats['total_orders'] = 0;
						$wcu_alltime_stats['full_discount'] = 0;
						$wcu_alltime_stats['total_commission'] = 0;
						$wcu_alltime_stats['total_shipping'] = 0;
						$wcu_alltime_stats['total_count'] = 0;
						$wcu_alltime_stats['commission_summary'] = array();
						update_post_meta( $postid, 'wcu_alltime_stats', $wcu_alltime_stats );
						$force_refresh_stats = 0;
					} else {
						$force_refresh_stats = 1;
					}
				}

        // Return force refresh status
        return $force_refresh_stats;
        
    }
}

