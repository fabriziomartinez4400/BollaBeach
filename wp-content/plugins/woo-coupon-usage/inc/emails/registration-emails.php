<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Email to affiliate on registration
function wcusage_email_affiliate_register($user_email, $coupon_code, $firstname) {

  $options = get_option( 'wcusage_options' );

  $wcusage_field_email_registration_enable = wcusage_get_setting_value('wcusage_field_email_registration_enable', '1');

  if($wcusage_field_email_registration_enable) {

    if(!empty($options['wcusage_field_email_registration_subject']) && !empty($options['wcusage_field_email_registration_message'])) {

      $to = $user_email;
      $from = wcusage_get_from_email();

      $subject = $options['wcusage_field_email_registration_subject'];
      if(!$subject) { $subject = ""; }
      $body = html_entity_decode( $options['wcusage_field_email_registration_message'] );

      if(isset($subject)) {
        if($coupon_code) { $subject = str_replace("{coupon}", $coupon_code, $subject); }
        if($firstname) { $subject = str_replace("{name}", $firstname, $subject); }
      }

      if($body) {
        $body = str_replace("{coupon}", $coupon_code, $body);
        $body = str_replace("{name}", $firstname, $body);
        $body = str_replace("{email}", $user_email, $body);
      }

      $dashboardurl = wcusage_get_coupon_shortcode_page(0);
      $dashboardurl = "<a href='".$dashboardurl."'>" . $dashboardurl . "</a>";
      $body = str_replace("{dashboardurl}", $dashboardurl, $body);

      $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
      $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
      $referralurl = esc_html($wcusage_field_default_ref_url . "?" . $wcusage_urls_prefix . "=" . $coupon_code);
      $referralurl = "<a href='".$referralurl."'>" . $referralurl . "</a>";
      $body = str_replace("{referralurl}", $referralurl, $body);

      $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

      $mailer = WC()->mailer();
      $wrapped_message = $mailer->wrap_message($subject, $body);
      $wc_email = new WC_Email;
      $html_message = $wc_email->style_inline($wrapped_message);

      wp_mail( $to, $subject, $html_message, $headers );

    }

  }

}

// Email to affiliate on registration if new account
function wcusage_email_affiliate_register_new($user_email, $coupon_code, $firstname, $username, $user_id = "") {

  $options = get_option( 'wcusage_options' );

  $wcusage_field_email_registration_new_enable = wcusage_get_setting_value('wcusage_field_email_registration_new_enable', '1');

  if($wcusage_field_email_registration_new_enable) {

    if(!empty($options['wcusage_field_email_registration_new_subject']) && !empty($options['wcusage_field_email_registration_new_message'])) {

      $to = $user_email;
      $from = wcusage_get_from_email();

      $subject = $options['wcusage_field_email_registration_new_subject'];
      if(!$subject) { $subject = ""; }
      $body = html_entity_decode( $options['wcusage_field_email_registration_new_message'] );

      if(isset($subject)) {
        if($coupon_code) { $subject = str_replace("{coupon}", $coupon_code, $subject); }
        if($firstname) { $subject = str_replace("{name}", $firstname, $subject); }
      }

      if($body) {
        $body = str_replace("{coupon}", $coupon_code, $body);
        $body = str_replace("{name}", $firstname, $body);
        $body = str_replace("{username}", $username, $body);
        $body = str_replace("{email}", $user_email, $body);
      }

      if($user_id) {
        $user = get_user_by( 'id', $user_id );
        $user_data = get_userdata( $user_id );
        if($to == $user_data->user_email) {
          $password_url = wcusage_generate_password_reset_url($user_id);
          $body = str_replace("{passwordurl}", $password_url, $body);
        }
      }

      $dashboardurl = wcusage_get_coupon_shortcode_page(0);
      $dashboardurl = "<a href='".$dashboardurl."'>" . $dashboardurl . "</a>";
      if(!$dashboardurl) { $dashboardurl = ""; }
      $body = str_replace("{dashboardurl}", $dashboardurl, $body);

      $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
      $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
      $referralurl = esc_html($wcusage_field_default_ref_url . "?" . $wcusage_urls_prefix . "=" . $coupon_code);
      $referralurl = "<a href='".$referralurl."'>" . $referralurl . "</a>";
      $body = str_replace("{referralurl}", $referralurl, $body);

      $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

      $mailer = WC()->mailer();
      $wrapped_message = $mailer->wrap_message($subject, $body);
      $wc_email = new WC_Email;
      $html_message = $wc_email->style_inline($wrapped_message);

      wp_mail( $to, $subject, $html_message, $headers );

    }

  }

}

// Create password reset URL
function wcusage_generate_password_reset_url($user_id) {
  
    $user = get_user_by('id', $user_id);
    
    if (is_wp_error($user)) {
    return false;
    }
    
    $user_data = get_userdata($user_id);
    $user_login = stripslashes($user->user_login);
    $key = get_password_reset_key($user_data);
    
    if (is_wp_error($key)) {
    return false;
    }
    
    // Get the URL of your account page
    $account_page_url = wc_get_page_permalink('myaccount');
    
    // Construct the password reset URL
    $rp_link = add_query_arg(
    array(
    'key' => $key,
    'id' => $user_id
    ),
    wc_get_endpoint_url('lost-password', '', $account_page_url)
    );
    
    return $rp_link;
  
}

// Email to admin on affiliate application
function wcusage_email_admin_affiliate_register($username, $coupon_code, $referrer, $promote, $website, $type, $info) {

  $options = get_option( 'wcusage_options' );

  $wcusage_field_registration_enable = wcusage_get_setting_value('wcusage_field_registration_enable', '1');
  $wcusage_field_email_registration_admin_enable = wcusage_get_setting_value('wcusage_field_email_registration_admin_enable', '1');

  if($wcusage_field_registration_enable && $wcusage_field_email_registration_admin_enable) {

    if(!empty($options['wcusage_field_email_registration_admin_subject']) && !empty($options['wcusage_field_email_registration_admin_message'])) {

    $from = wcusage_get_from_email();

    $subject = $options['wcusage_field_email_registration_admin_subject'];
    $body = html_entity_decode( $options['wcusage_field_email_registration_admin_message'] );

    if(isset($subject)) {
      if($coupon_code) { $subject = str_replace("{coupon}", $coupon_code, $subject); }
      if($username) { $subject = str_replace("{username}", $username, $subject); }
    }

    $user = get_user_by( 'login', $username );
    if($user) {
      $user_id = $user->ID;
      $user_data = get_userdata( $user_id );
      $name = $user_data->first_name . " " . $user_data->last_name;
      $email = $user_data->user_email;
    } else {
      $user_id = "";
      $name = "";
      $email = "";
    }

    $body = str_replace("{coupon}", $coupon_code, $body);
    $body = str_replace("{username}", $username, $body);
    $body = str_replace("{referrer}", $referrer, $body);
    $body = str_replace("{promote}", $promote, $body);
    $body = str_replace("{website}", $website, $body);
    $body = str_replace("{name}", $name, $body);
    $body = str_replace("{email}", $email, $body);

    $the_info = "";
    if($info) {
      $info = json_decode($info, true);
      if(is_array($info)) {
        foreach ($info as $key => $value) {
          $the_info .= "<p>" . $key . ": " . $value . "</p>";
        }
      }
    }
    $body = str_replace("{custom-fields}", $the_info, $body);

    $applicationsurl = admin_url() . "admin.php?page=wcusage_registrations";
    $applicationsurl = "<a href='".$applicationsurl."'>" . $applicationsurl . "</a>";
    $body = str_replace("{adminapplicationsurl}", $applicationsurl, $body);
    $body = str_replace("{adminurl}", $applicationsurl, $body);

      if(isset($options['wcusage_field_registration_admin_email'])) {
        $wcusage_field_registration_admin_email = $options['wcusage_field_registration_admin_email'];
      } else {
        $wcusage_field_registration_admin_email = get_bloginfo( 'admin_email' );
      }

      $to = $wcusage_field_registration_admin_email;

      $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

      $mailer = WC()->mailer();
      $wrapped_message = $mailer->wrap_message($subject, $body);
      $wc_email = new WC_Email;
      $html_message = $wc_email->style_inline($wrapped_message);

      wp_mail( $to, $subject, $html_message, $headers );

    }

  }

}

// Email to affiliate on registration accepted
function wcusage_email_affiliate_register_accepted($user_email, $coupon_code, $message, $username, $name) {

  $options = get_option( 'wcusage_options' );

  $wcusage_field_registration_enable = wcusage_get_setting_value('wcusage_field_registration_enable', '1');
  $wcusage_field_email_registration_accept_enable = wcusage_get_setting_value('wcusage_field_email_registration_accept_enable', '1');

  if($wcusage_field_registration_enable && $wcusage_field_email_registration_accept_enable) {

    if(!empty($options['wcusage_field_email_registration_accept_subject']) && !empty($options['wcusage_field_email_registration_accept_message'])) {

      $to = $user_email;
      $from = wcusage_get_from_email();

      $subject = $options['wcusage_field_email_registration_accept_subject'];
      if(!$subject) { $subject = ""; }
      $body = html_entity_decode( $options['wcusage_field_email_registration_accept_message'] );

      if(isset($subject)) {
        if($coupon_code) { $subject = str_replace("{coupon}", $coupon_code, $subject); }
        if($username) { $subject = str_replace("{username}", $username, $subject); }
      }

      if($body) {
        $body = str_replace("{coupon}", $coupon_code, $body);
        $body = str_replace("{name}", $name, $body);
        $body = str_replace("{username}", $username, $body);
        $body = str_replace("{message}", $message, $body);
      }

      $dashboardurl = wcusage_get_coupon_shortcode_page(0);
      $dashboardurl = "<a href='".$dashboardurl."'>" . $dashboardurl . "</a>";
      $body = str_replace("{dashboardurl}", $dashboardurl, $body);

      $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
      $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
      $referralurl = esc_html($wcusage_field_default_ref_url . "?" . $wcusage_urls_prefix . "=" . $coupon_code);
      $referralurl = "<a href='".$referralurl."'>" . $referralurl . "</a>";
      $body = str_replace("{referralurl}", $referralurl, $body);

      $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

      $mailer = WC()->mailer();
      $wrapped_message = $mailer->wrap_message($subject, $body);
      $wc_email = new WC_Email;
      $html_message = $wc_email->style_inline($wrapped_message);

      wp_mail( $to, $subject, $html_message, $headers );

    }

  }

}

// Email to affiliate on registration declined
function wcusage_email_affiliate_register_declined($user_email, $coupon_code, $message) {

  $options = get_option( 'wcusage_options' );

  $wcusage_field_registration_enable = wcusage_get_setting_value('wcusage_field_registration_enable', '1');
  $wcusage_field_email_registration_decline_enable = wcusage_get_setting_value('wcusage_field_email_registration_decline_enable', '1');

  if($wcusage_field_registration_enable && $wcusage_field_email_registration_decline_enable) {

    if(!empty($options['wcusage_field_email_registration_decline_subject']) && !empty($options['wcusage_field_email_registration_decline_message'])) {

      $to = $user_email;
      $from = wcusage_get_from_email();

      $subject = $options['wcusage_field_email_registration_decline_subject'];
      if(!$subject) { $subject = ""; }
      $body = html_entity_decode( $options['wcusage_field_email_registration_decline_message'] );

      if(isset($subject)) {
        if($coupon_code) { $subject = str_replace("{coupon}", $coupon_code, $subject); }
      }
      if($body) {
        $body = str_replace("{coupon}", $coupon_code, $body);
        $body = str_replace("{message}", $message, $body);
      }

      $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

      $mailer = WC()->mailer();
      $wrapped_message = $mailer->wrap_message($subject, $body);
      $wc_email = new WC_Email;
      $html_message = $wc_email->style_inline($wrapped_message);

      wp_mail( $to, $subject, $html_message, $headers );

    }

  }

}