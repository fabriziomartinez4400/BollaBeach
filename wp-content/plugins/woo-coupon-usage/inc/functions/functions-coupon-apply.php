<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Function that runs when coupon is applied to check if it is allowed
 *
 */
if( !function_exists( 'wcusage_applied_coupon_check_allow_coupons' ) ) {
  function wcusage_applied_coupon_check_allow_coupons() {

    $current_coupons = 0;
    $coupon = "";

    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {

      // Check if template coupon is applied and remove it
      $wcusage_field_registration_coupon_template = wcusage_get_setting_value('wcusage_field_registration_coupon_template', '');
      if($wcusage_field_registration_coupon_template) {
        if($coupon->get_code() == $wcusage_field_registration_coupon_template) {
          WC()->cart->remove_coupon( $coupon->get_code() );
          wc_clear_notices();
          if ( wcusage_check_admin_access() ) {
            wc_add_notice( esc_html__( "Admin notice: The 'template coupon code' can not be applied to any cart.", "woo-coupon-usage" ), "error" );
          }
        }
      }

      // Check if coupon is expired
      $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
      if($coupon_user_id) {

        $current_coupons++;

        $wcusage_field_allow_assigned_user = wcusage_get_setting_value('wcusage_field_allow_assigned_user', 1);
        if(!$wcusage_field_allow_assigned_user) {

            /***** Checks if current user is assigned to the coupon *****/

            $current_user_id = get_current_user_id();

            $iscouponusers = wcusage_iscouponusers( $coupon->get_code(), $current_user_id );

            if($iscouponusers) {

              WC()->cart->remove_coupon( $coupon->get_code() );

              wc_clear_notices();

              wc_add_notice( esc_html__( "Sorry, you can't use your own affiliate coupon code.", "woo-coupon-usage" ), "error" );

            }

            /***** Checks if current cart email address matches email of user assigned to coupon *****/

            $cart_email = WC()->checkout()->get_value( 'billing_email' );
            $cart_user_id = get_user_by( 'email', $cart_email )->ID;
            $iscouponusers2 = wcusage_iscouponusers( $coupon->get_code(), $cart_user_id );
            if($iscouponusers2) {

              WC()->cart->remove_coupon( $coupon->get_code() );

              wc_clear_notices();

              wc_add_notice( esc_html__( "Sorry, you can't use this coupon code.", "woo-coupon-usage" ), "error" );

            }

        }

      }

    }

    /***** Checks if other affiliate coupons already used *****/

    $wcusage_field_allow_multiple_coupons = wcusage_get_setting_value('wcusage_field_allow_multiple_coupons', 0);
    if(!$wcusage_field_allow_multiple_coupons) {

  if($current_coupons > 1) {

        // Respect click attribution setting
        $first_click = wcusage_get_setting_value('wcusage_field_click_attribution_first', '0');

        // Capture current applied coupons in order
        $applied_coupons = array();
        if ( function_exists('WC') && WC()->cart ) {
          $applied_coupons = WC()->cart->get_applied_coupons(); // array of codes in apply order
        }

  // Remove all coupons first
  // Suppress cookie adjustments while we resolve conflicts
  global $wcusage_suppress_cookie_on_remove;
  $wcusage_suppress_cookie_on_remove = true;
  foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
          WC()->cart->remove_coupon( $coupon->get_code() );
          wc_clear_notices();
        }
  // End suppression; we'll re-apply the chosen coupon next
  $wcusage_suppress_cookie_on_remove = false;

        if ( $first_click ) {
          // Keep the last applied affiliate coupon (manual choice wins)
          if ( ! empty( $applied_coupons ) ) {
            $last_applied = end( $applied_coupons );
            if ( $last_applied ) {
              WC()->cart->add_discount( $last_applied );
            }
          }
        } else {
          // Last-click: prefer the newest applied affiliate coupon this request
          $reapplied = false;
          if ( ! empty( $applied_coupons ) ) {
            // Iterate from newest to oldest
            for ( $i = count( $applied_coupons ) - 1; $i >= 0; $i-- ) {
              $code_try = $applied_coupons[$i];
              // Check if this code is an affiliate coupon
              $is_affiliate = false;
              try {
                $wc_c = new WC_Coupon( $code_try );
                if ( $wc_c && method_exists( $wc_c, 'get_id' ) ) {
                  $cid = $wc_c->get_id();
                  if ( $cid ) {
                    $assigned_user = get_post_meta( $cid, 'wcu_select_coupon_user', true );
                    if ( $assigned_user ) {
                      $is_affiliate = true;
                    }
                  }
                }
              } catch ( Exception $e ) {
                $is_affiliate = false;
              }
              if ( $is_affiliate ) {
                WC()->cart->add_discount( $code_try );
                $reapplied = true;
                break;
              }
            }
          }

          if ( ! $reapplied ) {
            // Fallback: prefer the current referral cookie coupon (may be stale during this request)
            $referral_code = "";
            if(isset($_COOKIE['wcusage_referral_code'])) {
              $referral_code = $_COOKIE['wcusage_referral_code'];
            } else if(isset($_COOKIE['wcusage_referral'])) {
              $referral_code = $_COOKIE['wcusage_referral'];
            }
            if($referral_code) {
              WC()->cart->add_discount( $referral_code );
            }
          }
        }

        wc_add_notice( esc_html__( "Sorry, you can only use one affiliate coupon per order.", "woo-coupon-usage" ), "error" );

      }

    }

  }
}
add_action( 'woocommerce_applied_coupon', 'wcusage_applied_coupon_check_allow_coupons', 10, 0 );
add_action( 'woocommerce_before_cart', 'wcusage_applied_coupon_check_allow_coupons', 10, 0 );
add_action( 'woocommerce_before_checkout_form', 'wcusage_applied_coupon_check_allow_coupons', 10, 0 );
// On checkout update
add_action( 'woocommerce_checkout_update_order_review', 'wcusage_applied_coupon_check_allow_coupons', 10, 0 );

/**
 * Function that checks if customer is allowed to use the applied coupons at all stages.
 *
 */
if( !function_exists( 'wcusage_applied_coupon_check_allow_customer' ) ) {
  function wcusage_applied_coupon_check_allow_customer() {

    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {

      if($coupon && !empty($coupon->get_id())) {

        $first_order_only = get_post_meta( $coupon->get_id(), 'wcu_enable_first_order_only', true );

        /***** Check if user assigned to coupon *****/

        $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
        if(!$coupon_user_id && $first_order_only != "yes") {
          continue;
        }

        $require_referral_link = wcusage_get_setting_value('wcusage_field_require_referral_link', 0);
        if ( $require_referral_link ) {
          $coupon_code = strtolower( $coupon->get_code() );
          $ref_sources = array();

          if ( isset( $_COOKIE['wcusage_referral'] ) ) {
            $ref_sources[] = sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral'] ) );
          }

          if ( isset( $_COOKIE['wcusage_referral_code'] ) ) {
            $ref_sources[] = sanitize_text_field( wp_unslash( $_COOKIE['wcusage_referral_code'] ) );
          }

          global $wp_session;
          if ( isset( $wp_session['wcusage_referral'] ) ) {
            $ref_sources[] = sanitize_text_field( $wp_session['wcusage_referral'] );
          }

          $matching_referral = false;
          foreach ( $ref_sources as $ref_source ) {
            if ( $ref_source && strtolower( $ref_source ) === $coupon_code ) {
              $matching_referral = true;
              break;
            }
          }

          if ( ! $matching_referral ) {
            wc_clear_notices();
            WC()->cart->remove_coupon( $coupon->get_code() );
            wc_add_notice( esc_html__( 'Sorry, this affiliate coupon can only be used after visiting the affiliate referral link.', 'woo-coupon-usage' ), 'error' );
            continue;
          }
        }

        /***** Check existing customer. *****/

        $allow_all_customers = wcusage_get_setting_value('wcusage_field_allow_all_customers', 1);
        if( $first_order_only == "yes" || !$allow_all_customers ) {
          $checkout_email = WC()->checkout()->get_value( 'billing_email' );
          if(wcusage_is_existing_customer($checkout_email)) {
            wc_clear_notices();
            WC()->cart->remove_coupon( $coupon->get_code() );
            wc_add_notice( esc_html__( "Sorry, only new customers can use this coupon code.", "woo-coupon-usage" ), "error" );
          }
        }

        /***** Check if visitor is blacklisted *****/

        if( wcusage_is_customer_blacklisted() ) {
            wc_clear_notices();
            WC()->cart->remove_coupon( $coupon->get_code() );
            wc_add_notice( esc_html__( "Sorry, you can't use this coupon code or it has expired.", "woo-coupon-usage"), "error" );
        }

        /***** Check if referrer domain is blacklisted *****/

        $block_domains_manual = wcusage_get_setting_value('wcusage_field_fraud_block_domains_manual', '0');
        if( wcusage_is_domain_blacklisted() && $block_domains_manual ) {
            wc_clear_notices();
            WC()->cart->remove_coupon( $coupon->get_code() );
            wc_add_notice( esc_html__( "Sorry, you can't use this coupon code or it has expired.", "woo-coupon-usage"), "error" );
        }

      }

    }

  }
}
add_action( 'woocommerce_before_checkout_form', 'wcusage_applied_coupon_check_allow_customer', 10, 0 );
add_action( 'woocommerce_before_cart', 'wcusage_applied_coupon_check_allow_customer', 10, 0 );
add_action( 'woocommerce_applied_coupon', 'wcusage_applied_coupon_check_allow_customer', 10, 0 );

/**
 * Revalidate coupons when the email address is updated on checkout.
 *
 * @param array $fields Checkout fields.
 * @param WP_Error $errors Validation errors.
 */
function wcusage_validate_checkout_email_on_edit( $fields, $errors ) {
  if ( isset( $fields['billing_email'] ) ) {
      wcusage_applied_coupon_check_allow_customer();
  }
}
add_action( 'woocommerce_after_checkout_validation', 'wcusage_validate_checkout_email_on_edit', 10, 2 );

/**
 * Wrapper function for the woocommerce_update_cart_action_cart_updated action.
 *
 * @param bool $cart_updated Whether the cart was updated.
 * 
 * @return bool
 */
function wcusage_applied_coupon_check_allow_customer_with_param( $cart_updated ) {
  wcusage_applied_coupon_check_allow_customer();
  return $cart_updated;
}
add_action( 'woocommerce_update_cart_action_cart_updated', 'wcusage_applied_coupon_check_allow_customer_with_param', 10, 1 );

/**
 * Function that checks if user is a new customer
 *
 */
if( !function_exists( 'wcusage_is_existing_customer' ) ) {
  function wcusage_is_existing_customer( $email = "" ) {

      // Get current user id
      $user_id = get_current_user_id();

      if( $user_id ) {
        // Args for wc_get_orders()
        $args = array(
            'status' => array('wc-completed', 'wc-processing', 'wc-pending'), // Only orders with "completed" status
            'customer_id' => $user_id, // Set current user id
            'limit' => 1, // Only need to check if at least one order exists
            'return' => 'ids', // Return Ids
        );
        // Get all customer orders
        $customer_orders = wc_get_orders( $args );
      } else {
        if($email) {
          $customer_orders = wc_get_orders( array(
            'status' => array('wc-completed', 'wc-processing', 'wc-pending'), // Only orders with "completed" status
            'email' => $email, // Set current user id
            'limit' => 1, // Only need to check if at least one order exists
            'return' => 'ids', // Return Ids
          ) );
        } else {
          return false;
        }
      }

      // Return "true" when customer has already at least one order (false if not)
      return count($customer_orders) > 0 ? true : false;
  }
}

/**
 * Function that checks if visitor is blacklisted
 *
 */
if( !function_exists( 'wcusage_is_customer_blacklisted' ) ) {
  function wcusage_is_customer_blacklisted($ip_address = "") {

    $block_ips = wcusage_get_setting_value('wcusage_field_fraud_block_ips', '');

    if($block_ips) {

      $block_ips = preg_split("/\r\n|\n|\r/", $block_ips);

      $referral_id = "";
      if(!$ip_address) {
        $ip_address = wcusage_get_ip_only();
        if(isset($_COOKIE['wcusage_referral_id'])) {
          $referral_id = $_COOKIE['wcusage_referral_id'];
        }
      }

      if( ( $ip_address && in_array($ip_address, $block_ips) ) || ( $referral_id && in_array($referral_id, $block_ips) ) ) {
        return true;
      }

    }

    return false;

  }
}

/**
 * Function that checks if referrer domain is blacklisted
 *
 */
if( !function_exists( 'wcusage_is_domain_blacklisted' ) ) {
  function wcusage_is_domain_blacklisted($referral_domain = "") {

    $block_domains = wcusage_get_setting_value('wcusage_field_fraud_block_domains', '');
    $referral_domain = "";

    if($block_domains) {

      $block_domains = preg_split("/\r\n|\n|\r/", $block_domains);
      $block_domains = str_replace("http://", "", $block_domains);
      $block_domains = str_replace("https://", "", $block_domains);
      $block_domains = preg_replace('/^www\./i', '', $block_domains);

      if(!$referral_domain && isset($_COOKIE['wcusage_referral_domain'])) {
        $referral_domain = $_COOKIE['wcusage_referral_domain'];
        $referral_domain = preg_replace('/^www\./i', '', $referral_domain);
      }

      if( $referral_domain && in_array($referral_domain, $block_domains) ) {
        return true;
      }

    }

    return false;

  }
}

/*
* Function that changes the coupon label on the cart page to "Referral code"
*
*/
add_filter( 'woocommerce_cart_totals_coupon_label', 'wcusage_custom_woocommerce_coupon_label', 10, 2 );
function wcusage_custom_woocommerce_coupon_label( $label, $coupon ) {

  $wcusage_field_coupon_custom_text = wcusage_get_setting_value('wcusage_field_coupon_custom_text', '');
  if(!$wcusage_field_coupon_custom_text) {
    return $label;
  }

  // Get the user ID of the coupon
  $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
  if( ! $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true ) ) {
      return $label;
  }

  if ( WC()->cart->get_coupon_discount_amount( $coupon->get_code(), true ) == 0 ) {
    $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
    if ( $coupon_user_id == get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true ) ) {
      return str_replace( 'Coupon:', $wcusage_field_coupon_custom_text . ":", $label );
    } else {
      return $label;
    }
  } else {
    return $label;
  }

}

/*
* Function that hides the £0.00 value of a coupon on the cart and checkout page
*
*/
add_filter( 'woocommerce_cart_totals_coupon_html', 'wcusage_custom_woocommerce_coupon_html', 1000, 2 );
function wcusage_custom_woocommerce_coupon_html( $discount_html, $coupon ) {
  
    // Check if the setting is enabled
    $wcusage_field_coupon_hide_zero = wcusage_get_setting_value('wcusage_field_coupon_hide_zero', 0);
    if(!$wcusage_field_coupon_hide_zero) {
        return $discount_html;
    }

    // Get the user ID of the coupon
    $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
    if( ! $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true ) ) {
        return $discount_html;
    }
    
  // Check if the discount amount is £0.00
  if ( WC()->cart->get_coupon_discount_amount( $coupon->get_code(), true ) == 0 ) {
    // Hide the £0.00 value but keep the Remove link. Prefer extracting the existing link from $discount_html
    // to preserve WooCommerce's nonce and attributes. Fallback to building a URL if needed.
    $extracted = '';
    if ( is_string( $discount_html ) ) {
      // Find the first anchor tag (usually the remove link) and keep from there onwards
      $a_pos = strpos( $discount_html, '<a ' );
      if ( $a_pos !== false ) {
        $extracted = substr( $discount_html, $a_pos );
      }
    }

    if ( $extracted ) {
      $discount_html = $extracted;
    } else {
      // Build a safe remove URL as a fallback
      if ( function_exists( 'wc_get_cart_remove_coupon_url' ) ) {
        $remove_url = wc_get_cart_remove_coupon_url( $coupon->get_code() );
      } else {
        $remove_url = add_query_arg( 'remove_coupon', $coupon->get_code(), wc_get_cart_url() );
      }
      $discount_html = '<a href="' . esc_url( $remove_url ) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '" aria-label="' . esc_attr( sprintf( __( 'Remove coupon: %s', 'woocommerce' ), $coupon->get_code() ) ) . '">' . esc_html__( '[Remove]', 'woocommerce' ) . '</a>';
    }
  }

    return $discount_html;
}

// Run at "Place order": remove coupons where the billing email user matches the assigned coupon user.
if ( ! function_exists( 'wcusage_checkout_place_order_validate_assigned_user_coupon' ) ) {
  function wcusage_checkout_place_order_validate_assigned_user_coupon() {

    // Respect existing setting: only run this restriction when assigned-user usage is not allowed
    $wcusage_field_allow_assigned_user = wcusage_get_setting_value('wcusage_field_allow_assigned_user', 1);
    if ( $wcusage_field_allow_assigned_user ) {
      return;
    }

    if ( ! WC()->cart ) {
      return;
    }

    // Get billing email from submitted checkout data
    $cart_email = WC()->checkout()->get_value( 'billing_email' );
    if ( ! $cart_email && ! empty( $_POST['billing_email'] ) ) {
      $cart_email = sanitize_email( wp_unslash( $_POST['billing_email'] ) );
    }
    if ( ! $cart_email ) {
      return;
    }

    // Safe lookup of user by email
    $cart_user = get_user_by( 'email', $cart_email );
    $cart_user_id = $cart_user ? (int) $cart_user->ID : 0;
    if ( ! $cart_user_id ) {
      return;
    }

    $removed_any = false;

    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
      if ( ! $coupon || empty( $coupon->get_id() ) ) {
        continue;
      }

      // Only consider coupons assigned to a user (affiliate coupons)
      $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
      if ( ! $coupon_user_id ) {
        continue;
      }

      // If billing email's user owns this coupon, remove it
      if ( function_exists( 'wcusage_iscouponusers' ) && wcusage_iscouponusers( $coupon->get_code(), $cart_user_id ) ) {
        WC()->cart->remove_coupon( $coupon->get_code() );
        $removed_any = true;
      }
    }

    // If anything was removed, show an error so checkout reloads and the customer sees the message
    if ( $removed_any ) {
      wc_add_notice( sprintf( esc_html__( "Sorry, you can't use your own affiliate coupon code '%s'.", "woo-coupon-usage" ), $coupon->get_code() ), 'error' );
    }
  }
}
add_action( 'woocommerce_checkout_process', 'wcusage_checkout_place_order_validate_assigned_user_coupon', 10 );
// Block use on 

// Also run during checkout recalculation: remove coupons where updated billing email user matches assigned coupon user.
if ( ! function_exists( 'wcusage_checkout_update_order_review_validate_assigned_user_coupon' ) ) {
  function wcusage_checkout_update_order_review_validate_assigned_user_coupon( $post_data ) {

    // Respect setting: only enforce when assigned-user usage is not allowed
    $wcusage_field_allow_assigned_user = wcusage_get_setting_value('wcusage_field_allow_assigned_user', 1);
    if ( $wcusage_field_allow_assigned_user || ! WC()->cart ) {
      return;
    }

    // Parse posted checkout data to get the latest billing email
    $data = array();
    if ( is_string( $post_data ) ) {
      parse_str( $post_data, $data );
    } elseif ( is_array( $post_data ) ) {
      $data = $post_data;
    }

    $cart_email = '';
    if ( ! empty( $data['billing_email'] ) ) {
      $cart_email = sanitize_email( wp_unslash( $data['billing_email'] ) );
    } elseif ( WC()->checkout() ) {
      $cart_email = WC()->checkout()->get_value( 'billing_email' );
    }
    if ( ! $cart_email ) {
      return;
    }

    // Resolve email to WP user ID
    $cart_user = get_user_by( 'email', $cart_email );
    $cart_user_id = $cart_user ? (int) $cart_user->ID : 0;
    if ( ! $cart_user_id ) {
      return;
    }

    // Remove any applied affiliate coupon owned by this user
    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
      if ( ! $coupon || empty( $coupon->get_id() ) ) {
        continue;
      }
      $coupon_user_id = get_post_meta( $coupon->get_id(), 'wcu_select_coupon_user', true );
      if ( ! $coupon_user_id ) {
        continue;
      }
      if ( function_exists( 'wcusage_iscouponusers' ) && wcusage_iscouponusers( $coupon->get_code(), $cart_user_id ) ) {
        WC()->cart->remove_coupon( $coupon->get_code() );
        // Show notice to inform user about removed coupon
        wc_add_notice( sprintf( esc_html__( "Sorry, you can't use your own affiliate coupon code '%s'.", "woo-coupon-usage" ), $coupon->get_code() ), 'error' );
      }
    }
  }
}
add_action( 'woocommerce_checkout_update_order_review', 'wcusage_checkout_update_order_review_validate_assigned_user_coupon', 9, 1 );