<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gets a cookie value
 *
 */
if( !function_exists( 'wcusage_get_cookie_value' ) ) {
	function wcusage_get_cookie_value($the_cookie) {

    $cookie = "";
    if ( isset( $_COOKIE[$the_cookie] ) ) {
      $cookie = wp_unslash( $_COOKIE[$the_cookie] );
    }
    $cookie = sanitize_text_field( $cookie );

    return $cookie;

  }
}

/**
 * Sets a cookie with proper attributes
 *
 */
if( !function_exists( 'wcusage_set_cookie' ) ) {
	function wcusage_set_cookie($name, $value, $expire, $secure = false, $httponly = false) {
    
    $path = '/';
    $domain = '';
    if ( defined( 'COOKIE_DOMAIN' ) ) {
        $domain = COOKIE_DOMAIN;
    }

    if ( is_ssl() ) {
        $secure = true;
    }

    if ( headers_sent() && ! $httponly && ! wp_doing_ajax() ) {
        // Headers already sent - use JavaScript fallback to set cookies.
        // Outputting script directly in footer instead of using wp_add_inline_script which won't work at this timing.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction
        add_action(
            'wp_footer',
            function() use ( $name, $value, $expire, $path, $domain, $secure ) {
                ?>
                <script type="text/javascript">
                (function() {
                    var date = new Date(<?php echo absint( $expire * 1000 ); ?>);
                    var expires = '; expires=' + date.toUTCString();
                    document.cookie = '<?php echo esc_js( $name ); ?>=<?php echo esc_js( $value ); ?>' + expires + '; path=<?php echo esc_js( $path ); ?>; domain=<?php echo esc_js( $domain ); ?>; samesite=Lax<?php echo $secure ? '; secure' : ''; ?>';
                })();
                </script>
                <?php
            },
            999
        );
    } elseif ( PHP_VERSION_ID < 70300 ) {
        setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
    } else {
        $options = array(
            'expires'  => $expire,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax',
        );
        setcookie( $name, $value, $options );
    }
  }
}

/**
 * Gets referral URL coupon code parameter value
 *
 */
if( !function_exists( 'wcusage_get_referral_value' ) ) {
	function wcusage_get_referral_value() {

    $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    $thereferral = "";
    if ( isset( $_GET[$wcusage_urls_prefix] ) ) {
      $thereferral = sanitize_text_field( wp_unslash( $_GET[$wcusage_urls_prefix] ) );
    }
    $thereferral = sanitize_text_field( $thereferral );

    return $thereferral;

  }
}

/**
 * Gets referral URL coupon code parameter value
 *
 */
if( !function_exists( 'wcusage_get_campaign_value' ) ) {
	function wcusage_get_campaign_value() {

    $wcusage_src_prefix = wcusage_get_setting_value('wcusage_field_src_prefix', 'src');
    $campaign = "";
    if ( isset( $_GET[$wcusage_src_prefix] ) ) {
      $campaign = wp_unslash( $_GET[$wcusage_src_prefix] );
    }
    $campaign = sanitize_text_field( $campaign );

    return $campaign;

  }
}

/**
 * Gets referral URL coupon code parameter value
 *
 */
if( !function_exists( 'wcusage_get_mla_referral_value' ) ) {
	function wcusage_get_mla_referral_value() {

    $wcusage_urls_prefix_mla = wcusage_get_setting_value('wcusage_urls_prefix_mla', 'mla');
    $thereferral = "";
    if(isset($_GET[$wcusage_urls_prefix_mla])) {
      $thereferral = sanitize_text_field($_GET[$wcusage_urls_prefix_mla]);
    }
    $thereferral = sanitize_text_field($thereferral);

    return $thereferral;

  }
}

/**
 * Applies cookie when URL is clicked on init
 *
 */
if( !function_exists( 'wcusage_url_cookie' ) ) {
	function wcusage_url_cookie() {

		if(!is_admin()) {

      // Prevent duplicate execution on both init and plugins_loaded
      static $wcusage_url_cookie_executed = false;
      if ($wcusage_url_cookie_executed) {
        return;
      }
      $wcusage_url_cookie_executed = true;

      if( isset($_SERVER['HTTP_REFERER']) ) {
        $refpage = wp_parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $refpage = strtok($refpage, '?');
        $refpage = preg_replace('/^www\./i', '', $refpage);
      } else {
        $refpage = "";
      }

      if( !wcusage_is_domain_blacklisted($refpage) ) {

  			global $woocommerce;
        global $wp_session;

        $cookie = wcusage_get_cookie_value("wcusage_referral");

        $campaigncookie = wcusage_get_cookie_value("wcusage_referral_campaign");

        $thereferral = wcusage_get_referral_value();
        if($thereferral && !isset($wp_session["wcusage_referral"])) $wp_session["wcusage_referral"] = $thereferral;
        if(!$thereferral && isset($wp_session["wcusage_referral"]) && !$cookie) $thereferral = $wp_session["wcusage_referral"];

        $campaign = wcusage_get_campaign_value();

  			wcusage_do_url_cookie($cookie, $thereferral, $campaigncookie, $campaign);

      }

    }

	}
}
add_action('init', 'wcusage_url_cookie', 1);
add_action('plugins_loaded', 'wcusage_url_cookie', 1);

/**
 * Runs code to apply cookies, and click/campaign tracking when URL is clicked
 *
 * @param string $cookie
 * @param string $thereferral
 * @param string $campaigncookie
 * @param string $campaign
 *
 */
 if( !function_exists( 'wcusage_do_url_cookie' ) ) {
	function wcusage_do_url_cookie($cookie, $thereferral, $campaigncookie, $campaign) {

    $options = get_option( 'wcusage_options' );

    $wcusage_store_cookies = wcusage_get_setting_value('wcusage_field_store_cookies', '1');

    if( isset($_SERVER['HTTP_REFERER']) ) {
      $refpage = wp_parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
      $refpage = strtok($refpage, '?');
      $refpage = preg_replace('/^www\./i', '', $refpage);
    } else {
      $refpage = "";
    }

    do_action('wcusage_before_referral_cookies', $refpage);

    if( !wcusage_is_domain_blacklisted($refpage) || !$refpage ) {

  		$thereferral = sanitize_text_field($thereferral);
      $campaign = sanitize_text_field( $campaign );
  		$cookie = sanitize_text_field($cookie);

  		$wcusage_urls_cookie_days = wcusage_get_setting_value('wcusage_urls_cookie_days', '30');
  		$wcusage_urls_enable = wcusage_get_setting_value('wcusage_field_urls_enable', '1');
  		$wcusage_field_track_all_clicks = wcusage_get_setting_value('wcusage_field_track_all_clicks', '1');
      $wcusage_field_show_click_history = wcusage_get_setting_value('wcusage_field_show_click_history', 1 );

  		$coupon_id = "";

  		if( $wcusage_urls_enable ) {

  			if($wcusage_urls_cookie_days == "") { $wcusage_urls_cookie_days = 30; }

  			// Ref URL

  			$expiry = strtotime('+'.$wcusage_urls_cookie_days.' days');

  			if( $thereferral ) {

          $coupon = new WC_Coupon($thereferral);
          if($coupon) {
            $coupon_id = $coupon->get_id();
          }

          // Track if we replaced the referral cookie (used to force new click record)
          $did_replace_referral = false;
          if($wcusage_store_cookies) {
            $first_click = wcusage_get_setting_value('wcusage_field_click_attribution_first', '0');
            $has_existing = ( isset($_COOKIE['wcusage_referral']) && sanitize_text_field( wp_unslash($_COOKIE['wcusage_referral']) ) ) || ( isset($_COOKIE['wcusage_referral_code']) && sanitize_text_field( wp_unslash($_COOKIE['wcusage_referral_code']) ) );
            if( $first_click && $has_existing ) {
              // Do not replace existing referral cookies in first-click mode.
            } else {
              // Determine if the referral code is changing (last-click replacement)
              $existing_ref = '';
              if ( isset($_COOKIE['wcusage_referral']) ) {
                $existing_ref = sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral'] ) );
              }
              if ( strtolower($existing_ref) !== strtolower($thereferral) ) {
                $did_replace_referral = true;
              }
              // Mark pending value for this request so removal hooks won't clear it.
              $GLOBALS['wcusage_referral_cookie_pending'] = $thereferral;
      		    wcusage_set_cookie('wcusage_referral', $thereferral, $expiry);
              // In last-click mode, clear wcusage_referral_code to prefer live referral.
              if( ! $first_click ) {
                wcusage_set_cookie("wcusage_referral_code", "", 1);
              }
            }
          }

          if($wcusage_field_show_click_history) {
            
            // Get IP Address of visitor
            $ipaddress = wcusage_get_visitor_ip();

            // Get referring page
            $refpage = "";
            if(isset($_SERVER['HTTP_REFERER'])) {
              $refpage = wp_parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
              $refpage = strtok($refpage, '?'); // Remove Query
              $refpage = sanitize_text_field($refpage);
            }

            // Get referral click ID cookie
            $clickcookie = '';
            if ( isset( $_COOKIE['wcusage_referral_click'] ) ) {
              $clickcookie = wp_unslash( $_COOKIE['wcusage_referral_click'] );
            }
            $clickcookie = sanitize_text_field( $clickcookie );

            // Check if click within last minute
            $clickcookie_recent = '';
            if ( isset( $_COOKIE['wcusage_referral_click_recent'] ) ) {
              $clickcookie_recent = sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral_click_recent'] ) );
            }

            $wcusage_field_show_click_history = wcusage_get_setting_value('wcusage_field_show_click_history', 1 );
            // If replacement occurred OR not currently a click ID OR track all clicks enabled, add click
            if( $did_replace_referral || !$clickcookie || ($wcusage_field_track_all_clicks && !$clickcookie_recent) ) {
              $addclick = wcusage_install_clicks_data($coupon_id, $campaign, '', $refpage, 0, $ipaddress);
              if($wcusage_store_cookies) {
                wcusage_set_cookie('wcusage_referral_click', $addclick, $expiry); // Updates click ID cookie
                wcusage_set_cookie('wcusage_referral_click_recent', 'true', time() + 60);
              }
            }

          }

  			}

  			// Campaign Tracking
  			if($thereferral && $campaign) {
          if($wcusage_store_cookies) {
  				  $expiry = strtotime('+'.$wcusage_urls_cookie_days.' days');
  				  wcusage_set_cookie('wcusage_referral_campaign', $campaign, $expiry);
          }
  		  }

  		}

    }

	}
}

/**
 * Get the IP or ID for visitor
 *
 */
function wcusage_get_visitor_ip() {

  $wcusage_field_track_click_ip = wcusage_get_setting_value('wcusage_field_track_click_ip', '1');

  // Get IP Address of visitor
  $ipaddress = "";
  if($wcusage_field_track_click_ip) {
    
    $ipaddress = wcusage_get_ip_only();

  } else {
    if ( isset( $_COOKIE['wcusage_referral_id'] ) ) {
      $ipaddress = wp_unslash( $_COOKIE['wcusage_referral_id'] );
    } else {
      $randomid = wcusage_url_shorten_random(20);
      $wcusage_urls_cookie_days = wcusage_get_setting_value('wcusage_urls_cookie_days', '30');
      $expiry = strtotime('+'.$wcusage_urls_cookie_days.' days');
      wcusage_set_cookie('wcusage_referral_id', $randomid, $expiry);
      $ipaddress = $randomid;
    }
  }
  $ipaddress = sanitize_text_field($ipaddress);

  return $ipaddress;

}

/**
 * Get IP Address of visitor (Cloudflare compatible)
 *
 */
function wcusage_get_ip_only() {
  $ipaddress = "";
  if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
      $ipaddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
  } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } elseif(isset($_SERVER['REMOTE_ADDR'])) {
      $ipaddress = $_SERVER['REMOTE_ADDR'];
  }
  
  if (strpos($ipaddress, ',') !== false) {
      $ip_parts = explode(',', $ipaddress);
      $ipaddress = trim($ip_parts[0]);
  }
  return $ipaddress;
}

/**
 * Apply coupon to cart if initial referral visit, or if cookie is set
 *
 */
if( !function_exists( 'wcusage_apply_coupon_to_cart' ) ) {
	function wcusage_apply_coupon_to_cart() {

    // Settings

    $wcusage_apply_enable = wcusage_get_setting_value('wcusage_field_apply_enable', '1');
    $wcusage_field_apply_instant_enable = wcusage_get_setting_value('wcusage_field_apply_instant_enable', '1');

    // Attribution mode and existing cookies
    $first_click = wcusage_get_setting_value('wcusage_field_click_attribution_first', '0');
    $has_ref_cookie = false;
    $cookie_ref = wcusage_get_cookie_value("wcusage_referral");
    $cookie_ref_code = wcusage_get_cookie_value("wcusage_referral_code");
    if( $cookie_ref || $cookie_ref_code ) { $has_ref_cookie = true; }

    // Apply Coupon Via Referral Link
    $thereferral = wcusage_get_referral_value();
    if($thereferral && $wcusage_apply_enable && $wcusage_field_apply_instant_enable) {
      // In first-click mode, if a referral cookie already exists, do not auto-apply new coupon
      if( $first_click && $has_ref_cookie ) {
        // Skip applying new coupon from URL
      } else {
      wcusage_auto_apply_discount_coupon($thereferral);
      }
    }

    // Apply Coupon Via MLA Link
    $mla_link_normal = wcusage_get_setting_value('wcusage_field_mla_link_normal', '0');
    if($mla_link_normal) {
      $thereferral = wcusage_get_mla_referral_value();
      if($thereferral && $wcusage_apply_enable && $wcusage_field_apply_instant_enable) {
        if( $first_click && $has_ref_cookie ) {
          // Skip applying new coupon from MLA URL if first-click already set
        } else {
          wcusage_auto_apply_discount_coupon($thereferral);
        }
      }
    }

    // Apply Coupon Via Cookie
    $cookie = wcusage_get_cookie_value("wcusage_referral");
    if($cookie && !is_admin() && $wcusage_apply_enable) {
      $first_click = wcusage_get_setting_value('wcusage_field_click_attribution_first', '0');
      if( $first_click && function_exists('WC') && WC()->cart ) {
        // If a different affiliate coupon is already applied, don't auto-apply cookie coupon
        $has_other_coupon = false;
        foreach ( WC()->cart->get_coupons() as $code => $c_obj ) {
          if ( $c_obj && method_exists($c_obj, 'get_code') ) {
            $applied_code = strtolower( $c_obj->get_code() );
            if ( $applied_code && $applied_code !== strtolower( $cookie ) ) {
              $has_other_coupon = true;
              break;
            }
          }
        }
        if( ! $has_other_coupon ) {
          wcusage_auto_apply_discount_coupon($cookie);
        }
      } else {
        wcusage_auto_apply_discount_coupon($cookie);
      }
    }

	}
}
add_action('wp', 'wcusage_apply_coupon_to_cart', 1);

/**
 * Check if there is a referrer domain, and creates a cookie for it
 *
 */
function wcusage_set_link_tracking_cookie($refpage) {

  $usage_field_enable_directlinks = wcusage_get_setting_value('wcusage_field_enable_directlinks', '');
  $wcusage_field_fraud_block_domains = wcusage_get_setting_value('wcusage_field_fraud_block_domains', '');
  if(!$usage_field_enable_directlinks && !$wcusage_field_fraud_block_domains) { return; }

  $wcusage_field_store_cookies_domains = wcusage_get_setting_value('wcusage_field_store_cookies_domains', '1');
  if(!$wcusage_field_store_cookies_domains) { return; }

  if ( wcu_fs()->can_use_premium_code() ) {

    if(isset($refpage) && $refpage) {

      $this_domain = $_SERVER['HTTP_HOST'];
        $this_domain = preg_replace('/^www\./i', '', $this_domain);

      if($this_domain != $refpage) {

        // Set Cookie
        $wcusage_urls_cookie_days = wcusage_get_setting_value('wcusage_urls_cookie_days', '30');
        $expiry = strtotime('+'.$wcusage_urls_cookie_days.' days');
        wcusage_set_cookie('wcusage_referral_domain', $refpage, $expiry);

      }

    }

  }

}
add_action('wcusage_before_referral_cookies', 'wcusage_set_link_tracking_cookie', 10, 1);

/**
 * On "wp" run updating of landing page id for the last referral click
 *
 */
if( !function_exists( 'wcusage_url_click_set_page' ) ) {
	function wcusage_url_click_set_page(){

		if(!is_admin()) {

      $thereferral = wcusage_get_referral_value();

      do_action('wcusage_hook_update_click_page_value', $thereferral);

		}

	}
}
add_action('wp', 'wcusage_url_click_set_page', 1);

/**
 * Updates the landing page id for the last referral click for visitor
 *
 * @param string $thecode
 *
 */
if( !function_exists( 'wcusage_update_click_page_value' ) ) {
	function wcusage_update_click_page_value($thecode) {

    $wcusage_field_track_click_ip = wcusage_get_setting_value('wcusage_field_track_click_ip', '1');

    if($thecode) {

      // Get IP Address of visitor
      $ipaddress = "";
      if($wcusage_field_track_click_ip) {
        $ipaddress = wcusage_get_ip_only();
      } else {
  if ( isset( $_COOKIE['wcusage_referral_id'] ) ) {
					$ipaddress = wp_unslash( $_COOKIE['wcusage_referral_id'] );
				}
      }
      $ipaddress = sanitize_text_field($ipaddress);

      global $wp_query;

      $page = 0;
      
      // Try to get the current page ID
      if($wp_query && isset($wp_query->post->ID)) {
        $page = $wp_query->post->ID;
      } 
      // If no post ID, check if this is the front page/homepage
      elseif(is_front_page() || is_home()) {
        // Get the homepage ID if set as a static page
        $page = get_option('page_on_front');
        // If homepage is set to show latest posts (not a static page), use 0 or a special identifier
        if(!$page || $page == 0) {
          // We can store -1 to indicate homepage/blog index, or just leave as 0
          $page = 0;
        }
      }

      if($page || $page === 0) {

        $coupon = wcusage_get_coupon_object_safe($thecode);
        if($coupon) {
          $couponid = $coupon->get_id();
          if($couponid) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wcusage_clicks';
            // Get the most recent click for this coupon and IP
            $click_record = $wpdb->get_row($wpdb->prepare("SELECT id, page FROM $table_name WHERE couponid = %d AND ipaddress = %s ORDER BY id DESC LIMIT 1", $couponid, $ipaddress)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            // Only update the page if it hasn't been set yet (empty or null, NOT 0 which represents homepage)
            if($click_record && ($click_record->page === null || $click_record->page === '' || $click_record->page === '')) {
              $results2 = $wpdb->update( $table_name, array( 'page' => $page ), array( 'id' => $click_record->id ) );
            }
          }
        }

      }

    }

	}
}
add_action('wcusage_hook_update_click_page_value', 'wcusage_update_click_page_value', 1, 1);

/**
 * Auto apply the coupon to cart
 *
 * @param string $coupon
 *
 */
if( !function_exists( 'wcusage_auto_apply_discount_coupon' ) ) {
	function wcusage_auto_apply_discount_coupon($coupon) {

    if( function_exists( 'WC' ) ) {

  		if ( WC()->cart->get_cart_contents_count() > 0 ) {

        $wc_coupon = wcusage_get_coupon_object_safe($coupon); // get intance of wc_coupon
        if (!$wc_coupon || !$wc_coupon->is_valid()) {
  				return;
  			}

  			$coupon_code = $wc_coupon->get_code();
  			if (!$coupon_code) {
  				return;
  			}

  			global $woocommerce;
  			if (!$woocommerce->cart->has_discount($coupon_code)) {
  				// This if-check prevents the customer getting a error message saying
  				// “The coupon has already been applied” every time the cart is updated.
  				if (!$woocommerce->cart->apply_coupon($coupon_code)) {
  					if ( function_exists( 'wc_print_notices' ) ) {
  							wc_print_notices();
  					}
  					return;
  				}

  			}

  		}

    }

		return;

	}
}

/**
 * Function to Remove Cookie when coupon is removed from cart
 *
 * @param string $coupon_code
 *
 */
if( !function_exists( 'wcusage_action_woocommerce_removed_coupon' ) ) {
  function wcusage_action_woocommerce_removed_coupon( $coupon_code ) {
    // If we're resolving conflicts or have a pending new cookie this request, skip cookie changes.
    global $wcusage_suppress_cookie_on_remove;
    $pending = isset($GLOBALS['wcusage_referral_cookie_pending']) ? $GLOBALS['wcusage_referral_cookie_pending'] : '';
    if ( !empty($wcusage_suppress_cookie_on_remove) || !empty($pending) ) {
      return;
    }
    if(!$coupon_code) { return; }
    if (headers_sent()) {
      return;
    }
  $cookie = isset( $_COOKIE['wcusage_referral'] ) ? strtolower( sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral'] ) ) ) : '';
    $coupon_code = strtolower($coupon_code);
    if (isset($_COOKIE['wcusage_referral']) && $cookie == $coupon_code) {
      unset($_COOKIE['wcusage_referral']);
  		wcusage_set_cookie("wcusage_referral", "", time() - 3600);
      $wcusage_field_url_referrals = wcusage_get_setting_value('wcusage_field_url_referrals', '0');
      if($wcusage_field_url_referrals) {
        $wcusage_urls_cookie_days = wcusage_get_setting_value('wcusage_urls_cookie_days', '30');
        $expiry = strtotime('+'.$wcusage_urls_cookie_days.' days');
        wcusage_set_cookie('wcusage_referral_code', $coupon_code, $expiry);
      }
    }
    if (isset($wp_session["wcusage_referral"]) && $wp_session["wcusage_referral"] == $coupon_code) {
      $wp_session["wcusage_referral"] = "";
    }
	}
}
add_action( 'woocommerce_removed_coupon', 'wcusage_action_woocommerce_removed_coupon', 10, 1 );

/**
 * Function to add click to coupon stats
 *
 * @param id $coupon_id
 *
 */
if( !function_exists( 'wcusage_url_add_click_coupon' ) ) {
	function wcusage_url_add_click_coupon($coupon_id) {
		$current_clicks = get_post_meta( $coupon_id, 'wcu_text_coupon_url_clicks', true );
		if(!$current_clicks) { $current_clicks = 0; }
		$update_clicks = $current_clicks + 1;
		update_post_meta( $coupon_id, 'wcu_text_coupon_url_clicks', $update_clicks );
	}
}

/**
 * Hook into "woocommerce_coupon_message" - Check if coupon applied message should be shown
 *
 */
if( !function_exists( 'wcusage_woocommerce_coupon_message' ) ) {
function wcusage_woocommerce_coupon_message( $msg, $msg_code, $coupon ) {
  // Skip manual apply coupon
  if( isset($_POST['coupon_code']) ) {
    return $msg;
  }
  // Skip if remove coupon
  if( $msg_code === 'coupon_removed' ) {
    return $msg;
  }
  // Skip message if not applied on cart or checkout
  if( !is_object($coupon) ) {
    // Check if the setting to hide coupon messages is enabled.
    $wcusage_field_coupon_applied_hide = wcusage_get_setting_value('wcusage_field_coupon_applied_hide', '0');
    if ($wcusage_field_coupon_applied_hide) {
      $referer = $_SERVER['HTTP_REFERER'] ?? '';
      if ( !strstr($referer, 'cart') && !strstr($referer, 'checkout') ) {
        if ( !defined('DOING_AJAX') || (defined('DOING_AJAX') && DOING_AJAX && (!isset($_POST['action']) || $_POST['action'] !== 'apply_coupon')) ) {
          if( $msg === esc_html__( 'Coupon code applied successfully.', 'woocommerce' ) ) {
            $msg = "";
          }
          if( $msg === esc_html__( 'Sorry, this coupon is not applicable to selected products.', 'woocommerce' ) ) {
            $msg = "";
          }
        }
      }
    }
    return $msg;
  }
}
}
add_filter( 'woocommerce_coupon_message', 'wcusage_woocommerce_coupon_message', 10, 3 );
add_filter( 'woocommerce_coupon_error', 'wcusage_woocommerce_coupon_message', 10, 3 );

/**
 * Hook into "woocommerce_add_error" - Check coupon applied error message
 *
 */
if( !function_exists( 'wcusage_woocommerce_coupon_error_message' ) ) {
	function wcusage_woocommerce_coupon_error_message( $error ) {

		$wcusage_field_coupon_applied_hide = wcusage_get_setting_value('wcusage_field_coupon_applied_hide', '0');

		if($wcusage_field_coupon_applied_hide) {

			if ( !is_cart() && !is_checkout() ) {
				if( 'Coupon code already applied!' == $error ) {
					$error = '';
				}
			}

		}

		return $error;

	}
}
add_filter( 'woocommerce_add_error', 'wcusage_woocommerce_coupon_error_message' );

/**
 * Click Tracking Log - Set Click To Converted When Order Taken to Thank You Page
 *
 * @param int $order_id
 *
 */
if( !function_exists( 'wcusage_clicks_log_converted' ) ) {
	function wcusage_clicks_log_converted( $order_id ) {

      $clickcookie = "";
      if ( isset( $_COOKIE['wcusage_referral_click'] ) ) {
        $clickcookie = sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral_click'] ) );
      }

			if($clickcookie) {

				if (!$order_id) {
		        return;
		    }

		    $order = wc_get_order( $order_id );

        $lifetimeaffiliate = wcusage_order_meta($order_id,'lifetime_affiliate_coupon_referrer');
        $affiliatereferrer = wcusage_order_meta($order_id,'wcusage_referrer_coupon');

        if($lifetimeaffiliate) {

          $coupon_code = $lifetimeaffiliate;
          $coupon = new WC_Coupon($coupon_code);
          $couponid = $coupon->get_id();

          if($couponid) {
            global $wpdb;
            $table_name2 = $wpdb->prefix . 'wcusage_clicks';
            $results2 = $wpdb->update( $table_name2, array( 'converted' => 1, 'orderid' => $order_id ), array( 'id' => $clickcookie, 'couponid' => $couponid ) );
          }

        } elseif($affiliatereferrer) {
  
          $coupon_code = $affiliatereferrer;
          $coupon = new WC_Coupon($coupon_code);
          $couponid = $coupon->get_id();

          if($couponid) {
            global $wpdb;
            $table_name2 = $wpdb->prefix . 'wcusage_clicks';
            $results2 = $wpdb->update( $table_name2, array( 'converted' => 1, 'orderid' => $order_id ), array( 'id' => $clickcookie, 'couponid' => $couponid ) );
          }

        } else {

          // Loop Coupons
          foreach( $order->get_coupon_codes() as $coupon_code ) {

            $coupon_code = sanitize_text_field($coupon_code);
            $coupon = new WC_Coupon($coupon_code);
            $couponid = $coupon->get_id();

            if($couponid) {
              global $wpdb;
              $table_name2 = $wpdb->prefix . 'wcusage_clicks';
              $results2 = $wpdb->update( $table_name2, array( 'converted' => 1, 'orderid' => $order_id ), array( 'id' => $clickcookie, 'couponid' => $couponid ) );
            }

          }

        }

        // remove cookie
        wcusage_set_cookie('wcusage_referral_click', '', time() - 3600);

			}

	}
}
add_action( 'woocommerce_thankyou', 'wcusage_clicks_log_converted', 1, 1  );

/**
 * Hook to show referral URL stats / boxes for the affiliate dashboard
 *
 * @param int $postid
 * @param string $coupon_code
 * @param string $campaign
 *
 * @return mixed
 *
 */
if( !function_exists( 'wcusage_get_referral_url_stats' ) ) {
	function wcusage_get_referral_url_stats($postid, $coupon_code, $campaign) {

			$options = get_option( 'wcusage_options' );

			$wcusage_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '');

      $urls_generator_enable = wcusage_get_setting_value('wcusage_field_urls_generator_enable', 1 );
      $urls_statistics_enable = wcusage_get_setting_value('wcusage_field_urls_statistics_enable', 1 );

			/* Get If Page Load */
			global $woocommerce;
			$c = new WC_Coupon($coupon_code);
			$the_coupon_usage = $c->get_usage_count();

			$wcusage_page_load = wcusage_get_setting_value('wcusage_field_page_load', '');
				//if($the_coupon_usage > 5000) { $wcusage_page_load = 1; }

      // ***** Get Values from Database ***** //

      global $wpdb;
      $table_name = $wpdb->prefix . 'wcusage_clicks';

      $params = array( $postid );
      $where_campaign = '';
      if ( $campaign && $campaign !== 'all' && wcu_fs()->can_use_premium_code() ) {
        $where_campaign = ' AND campaign = %s';
        $params[] = sanitize_text_field( $campaign );
      }

      $sql_clicks = "SELECT * FROM $table_name WHERE couponid = %d$where_campaign ORDER BY id ASC"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
      $sql_conv   = "SELECT * FROM $table_name WHERE couponid = %d$where_campaign AND converted = 1 ORDER BY id ASC"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
      $getclicks = $wpdb->get_results( $wpdb->prepare( $sql_clicks, $params ), ARRAY_A ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
      $getconversions = $wpdb->get_results( $wpdb->prepare( $sql_conv, $params ), ARRAY_A ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
      
  $totalclicks = is_array($getclicks) ? count($getclicks) : 0;
      $totalclicksshow = $totalclicks;
  $usage = is_array($getconversions) ? count($getconversions) : 0;

      if($totalclicks) {
        if(!$usage) { $usage = 0; }
        $conversionrate = round($usage / $totalclicks * 100, 2);
        if($conversionrate > 100) { $conversionrate = 100; }
        if(is_nan($conversionrate) || $totalclicks <= 0) { $conversionrate = 0; }
      } else {
        $totalclicksshow = 0;
        $usage = 0;
        $conversionrate = 0;
      }

			// ***** Display URL Statistics Boxes ***** //

			echo "<div style='margin-top: 25px;' id='wcu-referral-stats-section'></div>";

      echo "<span id='wcu-total-usage-clicks-url-num' style='display: none;'>" . esc_html($totalclicksshow) . "</span>";
      echo "<span id='wcu-total-usage-number-url-num' style='display: none;'>" . esc_html($usage) . "</span>";

      if($urls_statistics_enable) {

        echo "<div id='wcu-referral-statistics'>";

    			if($campaign) {
    				echo "<p class='wcu-tab-title wcusage-subheader wcusage-title-referral-stats' style='font-size: 22px; margin-bottom: 10px;'>" . esc_html__( 'Referral Statistics for', 'woo-coupon-usage' ) . " '" . esc_html(ucfirst($campaign)) . "' " . esc_html__( 'Campaign', 'woo-coupon-usage' ) . ":</p>";
    			} else {
    				echo "<p class='wcu-tab-title wcusage-subheader wcusage-title-referral-stats' style='font-size: 22px; margin-bottom: 10px;'>" . esc_html__( 'Referral Statistics', 'woo-coupon-usage' ) . ":</p>";
    			}

    			echo '<div class="wcusage-info-box wcusage-info-box-clicks">';
    				echo  '<p><span class="wcusage-info-box-title">' . esc_html(ucfirst( esc_html__( "Total Clicks", "woo-coupon-usage" ) )) . ':</span> <span id="wcu-total-usage-clicks-url">' . esc_html($totalclicksshow) . '</span></p>' ;
    			echo '</div>';

    			if($totalclicks >= 0) {
    				echo '<div class="wcusage-info-box wcusage-info-box-usage">';
    					echo  '<p><span class="wcusage-info-box-title">' . esc_html(ucfirst( esc_html__( 'Total Conversions', 'woo-coupon-usage' ) )) . ':</span> <span id="wcu-total-usage-number-url">' . esc_html($usage) . '</span></p>' ;
    				echo '</div>';

    				echo '<div class="wcusage-info-box wcusage-info-box-percent">';
    					echo  '<p><span class="wcusage-info-box-title">' . esc_html(ucfirst( esc_html__( "Conversion Rate", "woo-coupon-usage" ) )) . ':</span> <span id="wcu-total-usage-clicks-conversion">' . esc_html($conversionrate) . '</span>%</p>' ;
    				echo '</div>';
    			}

    			echo "<style>.wcu-loading-referral { display: none !important; }</style>";

          echo "<div style='clear: both; margin-bottom: 20px;'></div>";

        echo "</div>";

      }

	}
}
add_action('wcusage_hook_get_referral_url_stats', 'wcusage_get_referral_url_stats', 10, 3);

/**
 * Generate Referral URL for Coupon
 *
 * @param string $page_url
 * @param string $coupon_code
 *
 * @return string
 */
function wcusage_generate_referral_url($page_url, $coupon_code) {
    $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    $separator = strpos($page_url, '?') !== false ? '&' : '?';
    return $page_url . $separator . $wcusage_urls_prefix . '=' . urlencode($coupon_code);
}

/**
 * Generate Random Short URL Slug
 *
 * @param int $length
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_url_shorten_random' ) ) {
	function wcusage_url_shorten_random( $length = 8 ) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[wp_rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}

/**
 * Gets referral URL stats for coupon between set dates
 *
 * @param int $postid
 * @param date $date1
 * @param date $date2
 *
 * @return array
 *
 */
if( !function_exists( 'wcusage_get_url_stats' ) ) {
  function wcusage_get_url_stats($postid, $date1, $date2) {

    $date1 = date("Y-m-d", strtotime("-1 day", strtotime($date1)));
    $date2 = date("Y-m-d", strtotime("+1 day", strtotime($date2)));

    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_clicks';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE couponid = %d AND date > %s AND date < %s ORDER BY id DESC", $postid, $date1, $date2); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $result2 = $wpdb->get_results($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $clickcount = count($result2);    

    $convertedcount = 0;
    foreach ($result2 as $result) {
      if($result->converted) {
        $convertedcount++;
      }
    }

    if($clickcount > 0) {
      $conversionrate = number_format(($convertedcount / $clickcount) * 100, 2, '.', '');
    } else {
      $conversionrate = 0;
    }

    $return_array = [];
		$return_array['clicks'] = $clickcount;
		$return_array['convertedcount'] = $convertedcount;
		$return_array['conversionrate'] = $conversionrate;
		return $return_array;

  }
}

/**
 * Prevent canonical redirects from stripping referral query args like ?coupon=EXAMPLE.
 * Some setups/plugins/themes incorrectly redirect URLs that include our referral
 * query variables to the posts page or a 404. Returning false here disables the
 * canonical redirect for requests that include the referral parameter.
 */
add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
  // Respect custom prefixes and MLA param if configured.
  $prefix      = function_exists('wcusage_get_setting_value') ? wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon') : 'coupon';
  $mla_prefix  = function_exists('wcusage_get_setting_value') ? wcusage_get_setting_value('wcusage_urls_prefix_mla', 'mla') : 'mla';

  // If any of our referral query vars are present on the front-end, disable canonical redirect.
  if ( ! is_admin() && ( isset($_GET[$prefix]) || isset($_GET[$mla_prefix]) || isset($_GET['coupon']) ) ) {
    return false; // Do not perform canonical redirect
  }

  return $redirect_url;
}, 9999, 2 );

/**
 * Redirect from 404 homepage with ?coupon= to actual homepage.
 */
add_action( 'template_redirect', function() {
    $wcusage_field_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    if ( isset( $_GET[$wcusage_field_urls_prefix] ) ) {
        
        // Fix for when homepage loads posts page with query string
        if ( ( is_home() || is_archive() || is_search() ) && 'page' === get_option( 'show_on_front' ) ) {
          $request_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
          $home_path = wp_parse_url( home_url(), PHP_URL_PATH );
            if ( ! $home_path ) {
                $home_path = '/';
            }
            if ( untrailingslashit( $request_path ) === untrailingslashit( $home_path ) ) {
                wp_safe_redirect( home_url() );
                exit;
            }
        }

        // Fix for when homepage 404s with query string
        if ( is_404() && is_front_page() ) {
            wp_safe_redirect( home_url() );
            exit;
        }

    }
});

/**
 * Gets the default referral URL, updates if wrong domain
 */
if( !function_exists( 'wcusage_get_default_ref_url' ) ) {
  function wcusage_get_default_ref_url() {

    // Get the default referral URL from the settings or use the home URL as a fallback.
    $wcusage_field_default_ref_url = wcusage_get_setting_value('wcusage_field_default_ref_url', get_home_url());
    
    // Get the host/domain of the default referral URL.
    $default_ref_domain = wp_parse_url($wcusage_field_default_ref_url, PHP_URL_HOST);

    // Get the host/domain of the current WordPress site.
    $site_domain = wp_parse_url(get_home_url(), PHP_URL_HOST);

    // Get the path of the default referral URL.
    $path = wp_parse_url($wcusage_field_default_ref_url, PHP_URL_PATH);

    // Check if the domains match.
    if ($default_ref_domain === $site_domain) {
        return trailingslashit($wcusage_field_default_ref_url);
    } else {
        // Update the default referral URL with the home URL and get the path.
        $options = get_option('wcusage_options');
        $options['wcusage_field_default_ref_url'] = trailingslashit(get_home_url()) . ltrim($path, '/');
        update_option('wcusage_options', $options);
        return wcusage_get_setting_value('wcusage_field_default_ref_url', get_home_url());
    }
    
  }
}

/**
 * Gets the affiliate referral URL
 */
if( !function_exists( 'wcusage_get_affiliate_url' ) ) {
  function wcusage_get_affiliate_url($coupon_code) {

    $prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    $affiliate_url = wcusage_get_default_ref_url() . "?" . $prefix . "=" . rawurlencode($coupon_code);

    return $affiliate_url;

  }
}