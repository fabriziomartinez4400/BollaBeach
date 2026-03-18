<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/*** CREATE THE TABLES ***/

global $wcusage_activity_db_version;
$wcusage_activity_db_version = "3";

/**
 * Create database tables for activity
 *
 */
function wcusage_install_activity_tables() {

	global $wpdb;
	global $wcusage_activity_db_version;
	$installed_ver = get_option( "wcusage_activity_db_version" );

	if ( $installed_ver != $wcusage_activity_db_version ) {

		$table_name = $wpdb->prefix . 'wcusage_activity';

		$sql = "CREATE TABLE $table_name (
			id bigint NOT NULL AUTO_INCREMENT,
			event_id bigint NOT NULL,
			event text(9) NOT NULL,
      user_id bigint NOT NULL,
      info text(9) NOT NULL,
      date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( "wcusage_activity_db_version", $wcusage_activity_db_version );

	}
}

/**
 * Check / Update Creatives Database Table
 *
 */
function wcusage_update_activity_db_check() {
    global $wcusage_activity_db_version;
    if ( get_site_option( 'wcusage_activity_db_version' ) != $wcusage_activity_db_version ) {
        wcusage_install_activity_tables();
    }
}
add_action( 'plugins_loaded', 'wcusage_update_activity_db_check' );

/**
 * Function to install data to table
 *
 * @param int $coupon_id
 * @param string $name
 *
 * @return mixed
 *
 */
function wcusage_add_activity($event_id, $event, $info) {

    $enable_activity_log = wcusage_get_setting_value('wcusage_enable_activity_log', '1');
    if($enable_activity_log) {

  		$event_id = sanitize_text_field($event_id);
  		$event = sanitize_text_field($event);

      global $wpdb;
  		$table_name = $wpdb->prefix . 'wcusage_activity';

  		$wpdb->insert(
  			$table_name,
  			array(
  				'event_id' => $event_id,
  				'event' => $event,
          'user_id' => get_current_user_id(),
          'date' => current_time( 'mysql' ),
          'info' => $info,
  			)
  		);
  		$last_id = $wpdb->insert_id;

  		return $last_id;

    } else {

      return 0;

    }

}

/**
 * Displays activity log event message.
 *
 * @param string $event
 * @param int $event_id
 * @param string $info
 *
 * @return string
 *
 */
function wcusage_activity_message($event, $event_id = "", $info = "") {

  $event_message = '';
  $event_id = sanitize_text_field($event_id);
  $event = sanitize_text_field($event);
  $info = sanitize_text_field($info);

  if($event == 'reward_earned' || $event == 'reward_earned_bonus_amount' || $event == 'reward_earned_commission_increase' || $event == 'reward_earned_email_sent' || $event == 'reward_earned_role_assigned') {
    $reward_meta = get_post_meta($event_id);
    $trigger_type = isset($reward_meta['trigger_type'][0]) ? $reward_meta['trigger_type'][0] : '';
    $trigger_condition = isset($reward_meta['trigger_condition'][0]) ? $reward_meta['trigger_condition'][0] : '';
    $trigger_amount = isset($reward_meta['trigger_amount'][0]) ? $reward_meta['trigger_amount'][0] : '';
    $action_reward_bonus = isset($reward_meta['action_reward_bonus'][0]) ? $reward_meta['action_reward_bonus'][0] : '';
    $action_reward_credit = isset($reward_meta['action_reward_credit'][0]) ? $reward_meta['action_reward_credit'][0] : '';
    $action_change_commission = isset($reward_meta['action_change_commission'][0]) ? $reward_meta['action_change_commission'][0] : '';
    $action_increase_commission = isset($reward_meta['action_increase_commission'][0]) ? $reward_meta['action_increase_commission'][0] : '';
    $action_free_product = isset($reward_meta['action_free_product'][0]) ? $reward_meta['action_free_product'][0] : '';
    $action_free_coupon = isset($reward_meta['action_free_coupon'][0]) ? $reward_meta['action_free_coupon'][0] : '';
    $action_send_email = isset($reward_meta['action_send_email'][0]) ? $reward_meta['action_send_email'][0] : '';
    $action_assign_role = isset($reward_meta['action_assign_role'][0]) ? $reward_meta['action_assign_role'][0] : '';
    $bonus_amount = isset($reward_meta['bonus_amount'][0]) ? $reward_meta['bonus_amount'][0] : '';
    $credit_amount = isset($reward_meta['credit_amount'][0]) ? $reward_meta['credit_amount'][0] : '';
    $commission_increase = isset($reward_meta['commission_increase'][0]) ? $reward_meta['commission_increase'][0] : '';
    $new_user_role = isset($reward_meta['new_user_role'][0]) ? $reward_meta['new_user_role'][0] : '';
    $product_id = isset($reward_meta['free_product'][0]) ? $reward_meta['free_product'][0] : '';
    $product_quantity = isset($reward_meta['free_product_quantity'][0]) ? $reward_meta['free_product_quantity'][0] : 1;
  }

  switch ( $event ) {
    case 'referral':
      $order_info = wc_get_order($event_id);
      if($order_info) {
        $order_info = wc_get_order($event_id);
        $order_total = $order_info->get_total();
        $order_total = wc_price($order_total);
        $order_meta = get_post_meta($event_id);
        if(isset($order_meta['wcusage_affiliate_user'][0])) {
          $affiliate_user_id = $order_meta['wcusage_affiliate_user'][0];
          $affiliate_user = "'" . get_the_author_meta( 'user_login', $affiliate_user_id ) . "'";
        } else {
          $affiliate_user = 'an affiliate';
        }
        $event_message = "New order referral of " . $order_total . " by " . $affiliate_user . ": " . "<a href='" . esc_url(admin_url('post.php?post=' . $event_id . '&action=edit')) . "'>#" . $event_id . "</a>";
      } else {
        $event_message = "New order referral: " . "<a href='" . esc_url(admin_url('post.php?post=' . $event_id . '&action=edit')) . "'>#" . esc_html($event_id) . "</a>";
      }
      break;
    case 'registration':
      $event_message = "New affiliate registration (".esc_html($event_id)."):" . " " . wp_kses_post($info);
      break;
    case 'registration_accept':
      $event_message = "Affiliate registration accepted:" . " " . wp_kses_post($info);
      break;
    case 'mla_invite':
      $event_message = wp_kses_post($info) . " was invited to an affiliate network.";
      break;
    case 'direct_link_domain':
      $event_message = "Direct link domain request:" . " " . wp_kses_post($info);
      break;
    case 'payout_request':
      $event_message = "New payout request (#".esc_html($event_id)."):" . " " . wcusage_format_price(wp_kses_post($info));
      break;
    case 'payout_paid':
      $event_message = "Payout request paid (#".esc_html($event_id)."):" . " " . wcusage_format_price(wp_kses_post($info));
      break;
    case 'payout_reversed':
      $event_message = "Payout request reversed (#".esc_html($event_id)."):" . " " . wcusage_format_price(wp_kses_post($info));
      break;
    case 'new_campaign':
      $event_message = "New campaign added by an affiliate:" . " " . wp_kses_post($info);
      break;
    case 'commission_added':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      // Convert HTML entities to their corresponding characters
      $info = html_entity_decode($info, ENT_QUOTES, 'UTF-8');
      // If $info has a number with # at start, link it to the order
      if (preg_match('/#(\d+)/', $info, $matches)) {
        $order_id = $matches[1];
        $order_link = '<a href="'.esc_url(get_edit_post_link($order_id)).'" target="_blank">#'.$order_id.'</a>';
        $info = str_replace('#'.$order_id, $order_link, $info);
      }
      $event_message = "Unpaid commission added to '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'commission_removed':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      // Convert HTML entities to their corresponding characters
      $info = html_entity_decode($info, ENT_QUOTES, 'UTF-8');
      // If $info has a number with # at start, link it to the order
      if (preg_match('/#(\d+)/', $info, $matches)) {
        $order_id = $matches[1];
        $order_link = '<a href="'.esc_url(get_edit_post_link($order_id)).'" target="_blank">#'.$order_id.'</a>';
        $info = str_replace('#'.$order_id, $order_link, $info);
      }
      $event_message = "Unpaid commission removed from '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'mla_commission_added':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $info = html_entity_decode($info, ENT_QUOTES, 'UTF-8');
      if (preg_match('/#(\d+)/', $info, $matches)) {
        $order_id = $matches[1];
        $order_link = '<a href="'.esc_url(get_edit_post_link($order_id)).'" target="_blank">#'.$order_id.'</a>';
        $info = str_replace('#'.$order_id, $order_link, $info);
      }
      $event_message = "MLA unpaid commission added to '" . esc_html($coupon_name) . "':" . " " . wp_kses_post($info);
      break;
    case 'mla_commission_removed':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $info = html_entity_decode($info, ENT_QUOTES, 'UTF-8');
      if (preg_match('/#(\d+)/', $info, $matches)) {
        $order_id = $matches[1];
        $order_link = '<a href="'.esc_url(get_edit_post_link($order_id)).'" target="_blank">#'.$order_id.'</a>';
        $info = str_replace('#'.$order_id, $order_link, $info);
      }
      $event_message = "MLA unpaid commission removed from '" . esc_html($coupon_name) . "':" . " " . wp_kses_post($info);
      break;
    case 'manual_unpaid_commission_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Unpaid commission edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'manual_pending_commission_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Pending commission edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'manual_processing_commission_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Processing commission edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'manual_coupon_commission_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Coupon commission (percentage) edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'manual_coupon_commission_fixed_order_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Coupon commission (fixed per order) edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'manual_coupon_commission_fixed_product_edit':
      $coupon_info = wcusage_get_coupon_info_by_id($event_id);
      $coupon_name = $coupon_info[3];
      $event_message = "Coupon commission (fixed per product) edited for coupon '".esc_html($coupon_name)."':" . " " . wp_kses_post($info);
      break;
    case 'reward_earned':
      $coupon_info = wcusage_get_coupon_info_by_id($info);
      $coupon_name = $coupon_info[3];
      $coupon_name = '<a href="'.get_edit_post_link($info).'">'.esc_html($coupon_name).'</a>';
      $user_id = $coupon_info[1];
      $username = get_the_author_meta( 'user_login', $user_id );
  $username = '<a href="'. esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id) ) .'">'.esc_html($username).'</a>';
      if($username == '') {
        $username = '';
      } else {
        $username = "by " . $username . " ";
      }
      $post_id = $event_id;
      $post_title = get_the_title($post_id);
      $post_title = '<a href="'.esc_url(get_edit_post_link($post_id)).'">'.esc_html($post_title).'</a>';
      $event_message = "Reward '".wp_kses_post($post_title)."' was earned ".wp_kses_post($username)."via coupon: ".wp_kses_post($coupon_name)."";
      if ($action_reward_bonus) {
        $event_message .= "<br/>Bonus 'unpaid commission' added to coupon: ".wcusage_format_price(esc_html($bonus_amount));
      }
      if ($action_reward_credit) {
        $wcusage_field_storecredit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', '0');
        if($wcusage_field_storecredit_enable) {
          $event_message .= "<br/>Bonus store credit added to user wallet: ".wcusage_format_price(esc_html($credit_amount));
        }
      }
      if ($action_change_commission) {
        $event_message .= "<br/>Commission rates were updated for the affiliate coupon.";
      }
      if ($action_free_product) {
        $event_message .= "<br/>Free product order created for: ".esc_html($product_quantity)." x ".esc_html(get_the_title($product_id))."";
      }
      if ($action_free_coupon) {
        $event_message .= "<br/>Free gift coupon was created for the user.";
      }
      if ($action_send_email) {
        $event_message .= "<br/>Custom reward email sent to user.";
      }
      if ($action_assign_role) {
        $event_message .= "<br/>User role added to user: ".esc_html($new_user_role);
      }
      break;
  }

  return $event_message;

}

// Store the previous value before the update
add_filter('update_postmeta', function($check, $object_id, $meta_key, $meta_value) {
  $meta_configs = [
      'wcu_text_unpaid_commission' => 'manual_unpaid_commission_edit',
      'wcu_text_pending_commission' => 'manual_pending_commission_edit',
      'wcu_text_pending_order_commission' => 'manual_processing_commission_edit',
      'wcu_text_coupon_commission' => 'manual_coupon_commission_edit',
      'wcu_text_coupon_commission_fixed_order' => 'manual_coupon_commission_fixed_order_edit',
      'wcu_text_coupon_commission_fixed_product' => 'manual_coupon_commission_fixed_product_edit',
  ];

  if (array_key_exists($meta_key, $meta_configs)) {
      // Store the previous value in a transient
      $previous_value = get_post_meta($object_id, $meta_key, true);
      set_transient("wcusage_prev_{$object_id}_{$meta_key}", $previous_value, 60); // Expires in 60 seconds
  }
  return $check;
}, 10, 4);

// Log the update with the previous value
add_action('updated_post_meta', 'wcusage_after_update_function', 10, 4);
function wcusage_after_update_function($meta_id, $post_id, $meta_key, $meta_value) {
  $meta_configs = [
      'wcu_text_unpaid_commission' => 'manual_unpaid_commission_edit',
      'wcu_text_pending_commission' => 'manual_pending_commission_edit',
      'wcu_text_pending_order_commission' => 'manual_processing_commission_edit',
      'wcu_text_coupon_commission' => 'manual_coupon_commission_edit',
      'wcu_text_coupon_commission_fixed_order' => 'manual_coupon_commission_fixed_order_edit',
      'wcu_text_coupon_commission_fixed_product' => 'manual_coupon_commission_fixed_product_edit',
  ];

  foreach ($meta_configs as $target_meta_key => $activity_type) {
      if ($meta_key === $target_meta_key) {
          // Retrieve the previous value from the transient
          $previous_value = get_transient("wcusage_prev_{$post_id}_{$meta_key}");
          if ($previous_value === false) {
              $previous_value = "0"; // Fallback if transient expired or wasn't set
          }
          $new_value = $meta_value;

          // Check if the value changed
          if ($previous_value !== $new_value) {

              if($meta_key == 'wcu_text_unpaid_commission'
              || $meta_key == 'wcu_text_pending_commission'
              || $meta_key == 'wcu_text_pending_order_commission'
              || $meta_key == 'wcu_text_coupon_commission_fixed_order'
              || $meta_key == 'wcu_text_coupon_commission_fixed_product')
              {
                $formatted_new_value = strip_tags(wc_price($new_value));
                $formatted_previous_value = strip_tags(wc_price($previous_value));
              }
              if($meta_key == 'wcu_text_coupon_commission') {
                $formatted_new_value = strip_tags($new_value) . "%";
                $formatted_previous_value = strip_tags($previous_value) . "%";
              }

              // Log the activity
              wcusage_add_activity(
                  $post_id,
                  $activity_type,
                  "From '{$formatted_previous_value}' to '{$formatted_new_value}'"
              );
          }

          // Clean up the transient
          delete_transient("wcusage_prev_{$post_id}_{$meta_key}");
          break;
      }
  }
}