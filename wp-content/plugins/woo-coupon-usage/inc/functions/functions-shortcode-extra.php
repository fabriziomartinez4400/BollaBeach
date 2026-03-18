<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Outputs shortcode to show the customers referrer coupon
 *
 * @param mixed $atts
 *
 */
if( !function_exists( 'wcusage_couponusage_referrer_shortcode' ) ) {
  function wcusage_couponusage_referrer_shortcode( $atts ) {

    $atts = shortcode_atts( array(
        'text' => '',
        'type' => '',
    ), $atts, 'couponaffiliates-referrer' );
    $the_text = "";
    $return_string = "";

    $user_id = get_current_user_id();

    if(isset($_COOKIE['wcusage_referral'])) {
        $cookie = $_COOKIE['wcusage_referral'];
    } else {
        $cookie = "";
    }
    $cookie = sanitize_text_field($cookie);

    $wcu_lifetime_referrer = get_user_meta( $user_id, 'wcu_lifetime_referrer', true );
    if($wcu_lifetime_referrer) { $cookie = $wcu_lifetime_referrer; }

    if($cookie) {

      $the_text = sanitize_text_field(esc_html($atts['text']));
      $return_string = $the_text . " " . $cookie;

      if($atts['type'] == "url") {
        $the_text = str_replace(' ', '%20', $the_text);
        $return_string = str_replace(' ', '%20', $return_string);
      }

    }

    return $return_string;

  }
}
add_shortcode( 'couponaffiliates-referrer', 'wcusage_couponusage_referrer_shortcode' );

/**
 * Outputs shortcode to show current users affiliate coupons
 *
 * @param mixed $atts
 *
 */
if( !function_exists( 'wcusage_couponusage_my_coupons_shortcode' ) ) {
  function wcusage_couponusage_my_coupons_shortcode( $atts ) {

    $coupon_ids = wcusage_get_users_coupons_ids( get_current_user_id() );

    $i = 0;

    ob_start();

    foreach($coupon_ids as $coupon) {

      $i++;
      if($i > 1) { echo ", "; }
      echo esc_html(get_the_title($coupon));

    }

    $return_string = ob_get_clean();

    return $return_string;

  }
}
add_shortcode( 'couponaffiliates-my-coupons', 'wcusage_couponusage_my_coupons_shortcode' );

/**
 * Outputs shortcode to show current users affiliate referral URLs
 *
 * @param mixed $atts
 *
 */
if( !function_exists( 'wcusage_couponusage_referral_urls_shortcode' ) ) {
  function wcusage_couponusage_referral_urls_shortcode( $atts ) {

    $coupon_ids = wcusage_get_users_coupons_ids( get_current_user_id() );

    $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
    $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');

    $i = 0;

    ob_start();

    foreach($coupon_ids as $coupon) {

      $i++;
    
      $url = $wcusage_field_default_ref_url . "?" . $wcusage_urls_prefix . "=" . get_the_title($coupon);
      ?>
      <a href="<?php echo esc_url($url); ?>"><?php echo esc_url($url); ?></a>
      <?php
      if($i > 1) { echo "<br/>"; }

    }

    $return_string = ob_get_clean();

    return $return_string;

  }
}
add_shortcode( 'couponaffiliates-referral-urls', 'wcusage_couponusage_referral_urls_shortcode' );
add_shortcode( 'couponaffiliates-referral-url', 'wcusage_couponusage_referral_urls_shortcode' );