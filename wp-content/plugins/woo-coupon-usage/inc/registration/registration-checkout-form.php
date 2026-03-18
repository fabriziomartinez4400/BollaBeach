<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
* Add custom checkout field to join affiliate program
*/
add_action( 'woocommerce_after_order_notes', 'wcusage_checkout_affiliate_register_fields' );
function wcusage_checkout_affiliate_register_fields() {

    $enable_checkout_checkbox = wcusage_get_setting_value('wcusage_field_registration_checkout_checkbox', '0');
    $checkout_checkbox_text = wcusage_get_setting_value('wcusage_field_registration_checkout_checkbox_text', 'Click here to join our affiliate program');
    $checkout_checkbox_checked = wcusage_get_setting_value('wcusage_field_registration_checkout_checkbox_checked', '0');

    if($enable_checkout_checkbox) { // If checkout affiliate registration enabled

      $current_user_id = get_current_user_id();

      if($current_user_id) {
        $users_coupons = wcusage_get_users_coupons_ids( $current_user_id );
      } else {
        $users_coupons = "";
      }

      if( empty($users_coupons) ) { // if they dont already have affiliate coupon ?>

        <?php if ( !is_user_logged_in() ) { ?>
        <script>
        jQuery(document).ready(function () {
          jQuery('#section_wcusage_join_affiliate_program_area').hide();
          if ( jQuery('#createaccount').is(':checked') ) {
            jQuery('#section_wcusage_join_affiliate_program_area').show();
          }
          jQuery('#createaccount').change(function (e) {
            jQuery('#section_wcusage_join_affiliate_program_area').toggle(this.checked);
          });
        });
        </script>
        <?php } ?>

        <style>#wcusage_join_affiliate_program_coupon_field .optional { display: none; }</style>
        <script>
        jQuery(document).ready(function () {
          jQuery('#section_wcusage_join_affiliate_program_coupon').hide();
          if ( jQuery('#wcusage_join_affiliate_program').is(':checked') ) {
            jQuery('#section_wcusage_join_affiliate_program_coupon').show();
          }
          jQuery('#wcusage_join_affiliate_program').change(function (e) {
            jQuery('#section_wcusage_join_affiliate_program_coupon').toggle(this.checked);
          });
        });
        </script>

        <div id="section_wcusage_join_affiliate_program_area">
        <?php
        echo '<div id="section_wcusage_join_affiliate_program" style="margin-top: 20px; max-height: 70px;">';
        woocommerce_form_field( 'wcusage_join_affiliate_program', array(
            'type'      => 'checkbox',
            'class'     => array('input-checkbox'),
            'label'     => $checkout_checkbox_text,
            'default'     => $checkout_checkbox_checked
        ),  WC()->checkout->get_value( 'wcusage_join_affiliate_program' ) );
        echo '</div>';

        $auto_coupon = wcusage_get_setting_value('wcusage_field_registration_auto_coupon', '0');

        if(!$auto_coupon) {
          echo '<div id="section_wcusage_join_affiliate_program_coupon">';
          woocommerce_form_field( 'wcusage_join_affiliate_program_coupon', array(
              'type'      => 'text',
              'class'     => array('input-text'),
              'label'     => esc_html__('What is your preferred affiliate coupon code?', 'woo-coupon-usage') . " <abbr class='required' title='required'>*</abbr>",
          ),  WC()->checkout->get_value( 'wcusage_join_affiliate_program_coupon' ) );
          echo '</div><br/>';
        }
        ?>
        </div>

      <?php
      }

    }

}

/**
 * On order complete add to affiliate program if checked.
 *
 * @param int $order_id
 *
 */
add_action( 'woocommerce_checkout_update_order_meta', 'wcusage_checkout_affiliate_register_field_submit', 10, 1 );
function wcusage_checkout_affiliate_register_field_submit( $order_id ) {

  // Get order user ID
  $order = wc_get_order( $order_id );
  if(!$order) { return; }

  $user_id = $order->get_user_id();
  if(!$user_id) { return; }

  if ( !empty( $_POST['wcusage_join_affiliate_program'] ) && $user_id ) {

    if($_POST['wcusage_join_affiliate_program']) {

      $user_info = get_userdata($user_id);
      $username = sanitize_user( $user_info->user_login );
      $userid = $user_info->ID;
      $email = sanitize_email( $user_info->user_email );
      $firstname = sanitize_text_field( $user_info->first_name );
      $lastname = sanitize_text_field( $user_info->last_name );

      $auto_coupon = wcusage_get_setting_value('wcusage_field_registration_auto_coupon', '0');

      if( $auto_coupon && wcu_fs()->can_use_premium_code__premium_only() ) {

        $couponcode = wcusage_generate_auto_coupon($username, $firstname, $lastname);

      } else {

        if(isset($_POST['wcusage_join_affiliate_program_coupon'])) {
          $couponcode = wc_sanitize_coupon_code( $_POST['wcusage_join_affiliate_program_coupon'] );
        } else {
          $couponcode = "";
        }

      }

      $createregistration = wcusage_create_new_registration($couponcode, $username, '*' . esc_html__( 'Checkout Page', 'woo-coupon-usage') . '*', '', '', 0, '', '');

    }

  }

}
