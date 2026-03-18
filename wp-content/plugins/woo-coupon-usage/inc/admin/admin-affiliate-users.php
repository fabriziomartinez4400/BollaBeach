<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle unlinking an affiliate from a coupon via URL
 *
 * @return mixed
 */
add_action('admin_init', 'wcusage_handle_unlink_affiliate_via_url');
function wcusage_handle_unlink_affiliate_via_url() {
  if (
      isset($_GET['action']) && $_GET['action'] === 'wcusage_unlink_affiliate' &&
      isset($_GET['unassign_coupon']) && isset($_GET['_wpnonce'])
  ) {
      // Verify nonce for security
      if (!wp_verify_nonce($_GET['_wpnonce'], 'admin_unlink_affiliate')) {
          wp_die('Security check failed');
      }

      // Check permissions
      if (!current_user_can('manage_woocommerce')) {
          wp_die('You do not have permission to perform this action.');
      }

      $couponid = intval($_GET['unassign_coupon']);
      if ($couponid) {
          wcusage_coupon_affiliate_unlink($couponid);
          $current_page = sanitize_text_field($_GET['current_page']);
          if(!$current_page) {
            $current_page = 'users.php';
          } else {
            $current_page = admin_url('admin.php?page='.$current_page);
          }
          $current_search = sanitize_text_field($_GET['current_search']);
          if($current_search) {
            $current_page = add_query_arg('s', $current_search, $current_page);
          }
          $current_role = sanitize_text_field($_GET['current_role']);
          if($current_role) {
            $current_page = add_query_arg('role', $current_role, $current_page);
          }
          wp_safe_redirect($current_page);
          exit;
      } else {
          wp_die('Invalid coupon ID.');
      }
  }
}

/**
 * On users list "Add new Affiliate"
 *
 */
function wcusage_filter_users_custom_button($which) {
  // Enqueue JS to inject the action buttons on Users list
  $script_handle = 'wcusage-admin-affiliate-users';
  $script_path   = WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-affiliate-users.js';
  $script_fs     = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/admin-affiliate-users.js';
  $script_ver    = file_exists($script_fs) ? filemtime($script_fs) : WCUSAGE_VERSION;
  wp_enqueue_script($script_handle, $script_path, array('jquery'), $script_ver, true);

  // Localize URLs and labels
  wp_localize_script($script_handle, 'wcusageAffUsers', array(
    'addAffiliateUrl'    => admin_url('admin.php?page=wcusage_add_affiliate'),
    'manageAffiliatesUrl'=> admin_url('admin.php?page=wcusage_affiliates'),
    'addLabel'           => sprintf(__('Add New %s', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliate', 'woo-coupon-usage'))),
    'manageLabel'        => sprintf(__('Manage %s', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliates', 'woo-coupon-usage'), true)),
  ));
}
add_action('admin_footer-users.php', 'wcusage_filter_users_custom_button');

/**
 * Add Custom Columns to Users List
 *
 */
 function wcusage_new_modify_user_table( $column ) {

     // Check if affiliate column is disabled for performance on high-volume sites
     $disable_affiliate_column = wcusage_get_setting_value('wcusage_field_disable_users_affiliate_column', '0');
     if ($disable_affiliate_column) {
         return $column;
     }

     if( wcu_fs()->can_use_premium_code() ) {
       $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
       $system = wcusage_get_setting_value('wcusage_field_storecredit_system', 'default');
       $storecredit_users_col = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_users_col', 1);
       if($credit_enable && $storecredit_users_col && $system == "default") {
         $credit_label = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_only', 'Store Credit');
         $column['affiliatestorecredit'] = $credit_label;
       }
     }

     $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
     if($wcusage_field_mla_enable) {
       $column['affiliatemla'] = 'MLA';
     }

     $column['affiliateinfo'] = wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )) . ' Coupons';

     return $column;
 }
 add_filter( 'manage_users_columns', 'wcusage_new_modify_user_table' );

 function wcusage_new_modify_user_table_row( $val, $column_name, $user_id ) {
     $coupons = wcusage_get_users_coupons_ids( $user_id );
     switch ($column_name) {
         case 'affiliateinfo':
             // Check cache first for performance
             $cache_key = 'wcusage_user_affiliate_col_' . $user_id;
             $cached_output = get_transient( $cache_key );
             
             if ( $cached_output !== false ) {
                 return $cached_output;
             }
             
             $theoutput = "";
             $max_coupons = 10; // Limit to first 10 coupons for performance
             $coupon_count = 0;
             
             foreach ($coupons as $coupon) {
               if ( $coupon_count >= $max_coupons ) {
                   $remaining = count($coupons) - $max_coupons;
                   $theoutput .= '<span style="color:#666;font-style:italic;"> +' . $remaining . ' more</span>';
                   break;
               }
               $theoutput .= wcusage_output_affiliate_tooltip_users($coupon);
               $coupon_count++;
            }
            
            // Cache for 1 hour
            if ( ! empty( $theoutput ) ) {
                set_transient( $cache_key, $theoutput, HOUR_IN_SECONDS );
            }
            
            return $theoutput;
         case 'affiliatemla':
            $wcusage_field_show_mla_private = wcusage_get_setting_value('wcusage_field_show_mla_private', '0');
            $access = get_user_meta($user_id, 'wcu_ml_access', true);
            if($wcusage_field_show_mla_private && !$access) {
              return "";
            }
            $theoutput = "";
            $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
            if($wcusage_field_mla_enable) {
              $dash_page_id = wcusage_get_mla_shortcode_page_id();
              $dash_page = get_page_link($dash_page_id);
              $user_info = get_userdata($user_id);
              $theoutput = '<a href="'.esc_url($dash_page).'?user='.esc_attr($user_info->user_login).'" title="View MLA Dashboard" target="_blank">MLA <span class="dashicons dashicons-external"></span></a>';
            }
            return $theoutput;
         case 'affiliatestorecredit':
            $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
            if( $credit_enable && function_exists( 'wcusage_get_credit_users_balance' ) ) {
              $balance = wcusage_format_price( wcusage_get_credit_users_balance( $user_id ) );
              return $balance;
            } else {
              return "";
            }
         default:
     }
     return $val;
 }
 add_filter( 'manage_users_custom_column', 'wcusage_new_modify_user_table_row', 10, 3 );

 /**
  * Set users page as WooCommerce screen to load tooltip
  *
  */
  add_filter('woocommerce_screen_ids','wcusage_set_uses_wc_screen' );
  function wcusage_set_uses_wc_screen( $screen ){
        $screen[] = 'users';
        return $screen;
  }

 /**
  * Get Coupon Tooltip
  *
  */
 function wcusage_output_affiliate_tooltip_users($couponid) {

  $coupon_info = wcusage_get_coupon_info_by_id($couponid);
  $user_id = $coupon_info[1];
  $user_info = get_userdata($user_id);

  $coupon_code = $coupon_info[3];
 	$unpaid_commission = wcusage_format_price($coupon_info[2]);

  $wcusage_field_urls_enable = wcusage_get_setting_value('wcusage_field_urls_enable', 1);
  $dashboard_url = $coupon_info[4];
  $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');

  $home_page = get_home_url();
  $link = $home_page.'?' . $wcusage_urls_prefix . '='.esc_html($coupon_code);

  $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', 1);
  if( $wcusage_tracking_enable && wcu_fs()->can_use_premium_code() ) {
    $commission_message = "<strong>" . esc_html__( 'Unpaid Commission', 'woo-coupon-usage' ) . "</strong>: " . wp_kses_post($unpaid_commission) . "<br/>";
  } else {
    $commission_message = "";
  }

  if ($user_info) {
    // Get current page after /wp-admin/ without parameters
    if(isset($_GET['page'])) {
      $current_page = sanitize_text_field($_GET['page']);
    } else {
      $current_page = '';
    }
    $unlink_url = add_query_arg(
        array(
            'action'    => 'wcusage_unlink_affiliate',
            'unassign_coupon'  => $couponid,
            'current_page' => $current_page,
            'current_search' => (isset($_GET['s']) ? sanitize_text_field($_GET['s']) : ''),
            'current_role' => (isset($_GET['role']) ? sanitize_text_field($_GET['role']) : ''),
            '_wpnonce'  => wp_create_nonce('admin_unlink_affiliate')
        ),
        admin_url('users.php')
    );

  $unlink_message = '<a href="' . esc_url($unlink_url) . '" onClick="return confirm(\'Unassign ' . wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' )) . ' user &#8220;'
      . esc_attr($user_info->user_login) . '&#8220; from the coupon code &#8220;'
      . esc_html($coupon_code) . '&#8220;? This will not delete the coupon or user, it will simply remove them from the coupon, so they can no longer gain commission or view the ' . wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' )) . ' dashboard for it.\');" 
      class="wcu-affiliate-tooltip-unlink-button">Unassign</a>';
  } else {
      $unlink_message = "";
  }

$coupon_code_linked = "<span class='wcusage-users-affiliate-column'>"
  ."<div class='custom-tooltip'><a href='javascript:void(0);' class='wcusage-tooltip-trigger'>".esc_html($coupon_code)."</a>
  <div class='tooltip-content wcusage-tooltip-content'>
  <span class='wcusage-tooltip-inner'>"
      . wp_kses_post($commission_message)
      . "<a href='".esc_url($dashboard_url)."' target='_blank' class='wcu-affiliate-tooltip-dashboard-button'>"
  . sprintf(esc_html__( 'View %s Dashboard', 'woo-coupon-usage' ), wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))) . "<span class='dashicons dashicons-external'></span>"
      . "</a>";
      if($wcusage_field_urls_enable) {
        $coupon_code_linked .= '<div class="wcusage-copyable-link"><strong>' . esc_html__( 'Default Referral Link', 'woo-coupon-usage' ) . ':</strong>'
        . '<input type="text" id="wcusageLink'.esc_attr($coupon_code).'" class="wcusage-copy-link-text" value="'.esc_url($link).'" readonly>'
        . '<button type="button" class="wcusage-copy-link-button"
        title="'.esc_html__( 'Copy Link', 'woo-coupon-usage' ).'"><i class="fa-regular fa-copy"></i></button>'
        . '</div>';
      } else {
        $coupon_code_linked .= '<br/>';
      }
      $coupon_code_linked .= "<a href='".esc_url(get_admin_url())."post.php?post=".esc_attr($couponid)."&action=edit'
      target='_blank' class='wcu-affiliate-tooltip-edit-button'>" . esc_html__( 'Edit Coupon', 'woo-coupon-usage' ) . "</a> - "
      . wp_kses_post($unlink_message)
      . "</span>
      </div>
  </div>";

 	return $coupon_code_linked;

 }
 add_action('wcusage_hook_output_affiliate_tooltip_users', 'wcusage_output_affiliate_tooltip_users');

 /**
  * Clear user affiliate column cache when coupon user assignment changes
  *
  */
 function wcusage_clear_user_affiliate_column_cache( $post_id ) {
     // Only for shop_coupon post type
     if ( get_post_type( $post_id ) !== 'shop_coupon' ) {
         return;
     }
     
     // Get the OLD user ID (before save) from global variable if available
     global $wcusage_old_coupon_user_id;
     
     // Get the NEW/current assigned user ID
     $new_user_id = get_post_meta( $post_id, 'wcu_select_coupon_user', true );
     
     // Clear cache for new user
     if ( $new_user_id ) {
         delete_transient( 'wcusage_user_affiliate_col_' . $new_user_id );
         delete_transient( 'wcusage_is_affiliate_' . $new_user_id );
         delete_transient( 'wcusage_user_coupon_ids_' . $new_user_id );
         delete_transient( 'wcusage_user_coupon_names_' . $new_user_id );
     }
     
     // Clear cache for the old user (if there was one and it's different)
     if ( ! empty( $wcusage_old_coupon_user_id ) && $wcusage_old_coupon_user_id != $new_user_id ) {
         delete_transient( 'wcusage_user_affiliate_col_' . $wcusage_old_coupon_user_id );
         delete_transient( 'wcusage_is_affiliate_' . $wcusage_old_coupon_user_id );
         delete_transient( 'wcusage_user_coupon_ids_' . $wcusage_old_coupon_user_id );
         delete_transient( 'wcusage_user_coupon_names_' . $wcusage_old_coupon_user_id );
     }
 }
 add_action( 'save_post', 'wcusage_clear_user_affiliate_column_cache' );
 add_action( 'delete_post', 'wcusage_clear_user_affiliate_column_cache' );
 
 /**
  * Store the old coupon user ID before saving
  *
  */
 function wcusage_store_old_coupon_user_id( $post_id ) {
     // Only for shop_coupon post type
     if ( get_post_type( $post_id ) !== 'shop_coupon' ) {
         return;
     }
     
     // Store the old user ID in a global variable before the save happens
     global $wcusage_old_coupon_user_id;
     $wcusage_old_coupon_user_id = get_post_meta( $post_id, 'wcu_select_coupon_user', true );
 }
 add_action( 'pre_post_update', 'wcusage_store_old_coupon_user_id' );
 
 /**
  * Clear user affiliate column cache when user meta is updated
  *
  */
 function wcusage_clear_user_affiliate_column_cache_on_meta_update( $meta_id, $user_id, $meta_key, $meta_value ) {
     // Clear cache when commission-related meta is updated
     if ( in_array( $meta_key, array( 'wcu_text_unpaid_commission', 'wcu_ml_unpaid_commission' ) ) ) {
         delete_transient( 'wcusage_user_affiliate_col_' . $user_id );
     }
 }
 add_action( 'update_user_meta', 'wcusage_clear_user_affiliate_column_cache_on_meta_update', 10, 4 );

 /**
  * Get Coupon Tooltip
  *
  */
  function wcusage_output_affiliate_tooltip_user_info($user_id) {

    $user = get_userdata($user_id);
    
    $user_info = array();
    
    $username = $user->user_login;
    $user_info['Username'] = $username;

    $user_info['Email'] = $user->user_email;

    if($user->first_name) {
      $user_info['First Name'] = $user->first_name;
    }

    if($user->last_name) {
      $user_info['Last Name'] = $user->last_name;
    }

    if($user->user_url) {
      $user_info['Website'] = $user->user_url;
    }

    $wcu_promote = get_user_meta( $user_id, 'wcu_promote', true );
    $user_info['Promote'] = $wcu_promote;

    $wcu_referrer = get_user_meta( $user_id, 'wcu_referrer', true );
    $user_info['Referrer'] = $wcu_referrer;

    $wcu_info = get_user_meta( $user_id, 'wcu_info', true );
    $wcu_info = json_decode($wcu_info, true);
    if(!$wcu_info) {
      $wcu_info = array();
    }
    foreach ($wcu_info as $key => $value) {
      $user_info[$key] = $value;
    }

  $info = "<span class='wcusage-users-affiliate-column'>"
  ."<div class='custom-tooltip'><a href='" . esc_url(admin_url( 'admin.php?page=wcusage_view_affiliate&user_id=' . $user_id )) . "' class='wcusage-tooltip-trigger'>".esc_html($username)."</a>
    <div class='tooltip-content wcusage-tooltip-content'>";

        if ( $user_info ) {
            foreach ( $user_info as $key => $value ) {
                if(!$value) { continue; }
                // If email make it a mailto link
                if($key == "Email") {
                  $value = '<a href="mailto:'.$value.'" style="text-decoration: underline; color: inherit;">'.$value.'</a>';
                }
                // If website, remove http:// or https://
                if($key == "Website") {
                  $value = str_replace('http://', '', $value);
                  $value = str_replace('https://', '', $value);
                }
                $info .= '<strong class="wcusage-info-label">' . esc_html( $key ) . ':</strong><br/>' . wp_kses_post( $value ) . '<br/>';
            }
            // Remove last <br/>
            $info = substr($info, 0, -5);
        }

    $info .= "</div>
    </div>";
  
     return $info;
  
   }
   add_action('wcusage_hook_output_affiliate_tooltip_users', 'wcusage_output_affiliate_tooltip_users');

/**
 * Add Coupon Affiliates & Commission tab to coupons
 *
 */
if( !function_exists( 'add_wcusage_coupon_data_tab' ) ) {
  function add_wcusage_coupon_data_tab( $product_data_tabs ) {
      $product_data_tabs['coupon-affiliates'] = array(
        'label' => esc_html__( 'Coupon Affiliates & Commission', 'woo-coupon-usage' ),
        'target' => 'wcusage_coupon_data',
        'order' => 0,
        'class' => '',
      );
      return $product_data_tabs;
  }
}
add_filter( 'woocommerce_coupon_data_tabs', 'add_wcusage_coupon_data_tab', 99 , 1 );

/**
 * Vertically center content in all columns on the Users screen
 */
function wcusage_users_table_vertical_center_css() {
  // Enqueue Users page-specific CSS
  $style_handle = 'wcusage-admin-affiliate-users';
  $style_path   = WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-affiliate-users.css';
  $style_fs     = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-affiliate-users.css';
  $style_ver    = file_exists($style_fs) ? filemtime($style_fs) : WCUSAGE_VERSION;
  wp_enqueue_style($style_handle, $style_path, array(), $style_ver);
}
add_action('admin_head-users.php', 'wcusage_users_table_vertical_center_css');