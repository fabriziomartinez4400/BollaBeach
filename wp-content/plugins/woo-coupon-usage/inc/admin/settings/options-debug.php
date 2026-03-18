<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_debug( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

	<div id="debug-settings" class="settings-area">

	<h1><?php echo esc_html__( 'Debug, Performance & Extra Settings', 'woo-coupon-usage' ); ?></h1>

  <hr/>

  <strong style="color: green;"><p>- <?php echo esc_html__( 'For most websites, the settings on this page can be ignored (keep them as they are).', 'woo-coupon-usage' ); ?></strong></p>

	<p>- <?php echo esc_html__( 'If you are experiencing any performance issues or other bugs with the plugin, please try enabling/disabling relevant settings below.', 'woo-coupon-usage' ); ?></p>

	<p>- <?php echo esc_html__( 'This plugin is frequently updated and maintained. If you notice any bugs, issues, or conflicts with other themes/plugins, please get in touch and it will be looked into.', 'woo-coupon-usage' ); ?></p>

  <br/>

  <hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Performance Settings', 'woo-coupon-usage' ); ?> - <?php echo esc_html__( 'Saving Data', 'woo-coupon-usage' ); ?></h3>

  <i><?php echo esc_html__( 'These options will improve loading speed of your affiliate dashboard for large coupons with lots of orders (since it wont need to calculate every time).', 'woo-coupon-usage' ); ?></i><br/>
  <i><?php echo esc_html__( 'Generally there should not be any reason to turn these off, but it is here just incase, and for debugging.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <p>
    <?php wcusage_setting_toggle_option('wcusage_field_enable_order_commission_meta', 1, esc_html__( '(Recommended)', 'woo-coupon-usage' ) . " " . esc_html__( 'Save the calculated "commission" values as meta data on each individual order.', 'woo-coupon-usage' ), '0px'); ?>
  </p>

  <br/>

  <p>
    <?php wcusage_setting_toggle_option('wcusage_field_enable_coupon_all_stats_meta', 1, esc_html__( '(Recommended)', 'woo-coupon-usage' ) . " " . esc_html__( 'Save the calculated "all time" stats for coupons as meta data.', 'woo-coupon-usage' ), '0px'); ?>
  </p>

  <?php wcusage_setting_toggle('.wcusage_field_enable_order_commission_meta', '.wcu-field-section-field-never-update-commission-meta'); // Show or Hide ?>
  <span class="wcu-field-section-field-never-update-commission-meta">

    <br/>

    <p>
      <?php wcusage_setting_toggle_option('wcusage_field_enable_never_update_commission_meta', 0, esc_html__( 'Never update the saved "commission" value for past orders.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'When disabled, if you change commission rates, it will automatically update the stats/commission for ALL new and past orders on the affiliate dashboard.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'When enabled, the PAST orders will not be affected (even if clicking "refresh data"), and it will only set the updated rates for NEW orders. The only time it WILL be updated is if an order is refunded.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'Please note, the commission displayed for all past orders is calculated the first time the affiliate dashboard is loaded for a coupon. New orders are calculated instantly.', 'woo-coupon-usage' ); ?></i><br/>
    </p>

  </span>

  <br/>
  
  <p><strong>Data not currently accurate, due to settings changes?</strong></p>

  <p>If you want to force refresh (re-calculate) all data that is saved on the affiliate dashboards (for past orders), then click the button below. (The first page load for each coupon dashboard may take slightly longer.)</p>

  <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings&refreshstats=true')); ?>"
   onclick="if (confirm('Are you sure you want to refresh all affiliate dashboard data? The next time your affiliates visit their affiliate dashboard, it may take significantly longer to load (first visit).')){return true;}else{event.stopPropagation(); event.preventDefault();};"
   class="wcu-addons-box-view-details" style="padding: 7px 20px; margin: 10px 0;">
    <?php echo esc_html__( 'REFRESH ALL DATA', 'woo-coupon-usage' ); ?> <i class="fas fa-sync" style="background: transparent; margin: 0;"></i>
  </a>
  
	<br/>

	<hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Performance Settings', 'woo-coupon-usage' ); ?> - <?php echo esc_html__( 'Other', 'woo-coupon-usage' ); ?></h3>

  <p>
    <?php wcusage_setting_toggle_option('wcusage_field_load_ajax', 1, esc_html__( 'Enable "Ajax Loading" on Affiliate Dashboard.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This will make the initial page loading much faster for larger coupons, and show a "loading" animation in these sections whilst it loads content (usually takes no longer than a few seconds).', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'Please consider clearing your cache after updating this option, if you do not see any changes.', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'NOTE: In some rare cases, or certain themes, this option may not work and will show the "loading..." animation continuously. In this case, simply disable it or contact us to look into fixing it for you.', 'woo-coupon-usage' ); ?></i><br/>
  </p>

  <?php wcusage_setting_toggle('.wcusage_field_load_ajax', '.wcu-field-section-field-show-refresh'); // Show or Hide ?>
  <span class="wcu-field-section-field-show-refresh">

  <br/>

  <p>
    <?php wcusage_setting_toggle_option('wcusage_field_enable_coupon_all_stats_batch', 1, esc_html__( 'Run ajax "all time" stats refresh / calculations in batches, to help prevent timeouts or ajax issues.', 'woo-coupon-usage' ), '0px'); ?>
  </p>

  <?php wcusage_setting_toggle('.wcusage_field_enable_coupon_all_stats_batch', '.wcu-field-section-show-ajax-batch'); // Show or Hide ?>
  <span class="wcu-field-section-show-ajax-batch">

    <p>
      <?php wcusage_setting_number_option('wcusage_field_enable_coupon_all_stats_batch_amount', '20', esc_html__( 'Batch size:', 'woo-coupon-usage' ), '70px'); ?>
      <i style="margin-left: 70px;"><?php echo esc_html__( 'This is the amount of days that will be calculated at a time. If you experience issues with the ajax loading, try lowering this number (will be slower but more reliable).', 'woo-coupon-usage' ); ?></i><br/>
    </p>

  </span>

  <br/>

  <p>
    <!-- Load each page individually with ajax. -->
    <?php wcusage_setting_toggle_option('wcusage_field_load_ajax_per_page', 1, esc_html__( 'Load tabs individually with Ajax.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This will further increase initial loading speed/performance. It will only start loading content for each tab when the tab is clicked, showing the "loading..." animation whilst it loads.', 'woo-coupon-usage' ); ?></i><br/>
  </p>

  </span>

  <script>
  jQuery( document ).ready(function() {
    if(jQuery('.wcusage_field_load_ajax').prop('checked')) {
      jQuery('.section-wcusage-field-page-load').hide();
    }
    jQuery('.wcusage_field_load_ajax').change(function(){
      if(jQuery(this).prop('checked')) {
        jQuery('.section-wcusage-field-page-load').hide();
      } else {
        jQuery('.section-wcusage-field-page-load').show();
      }
    });
  });
  </script>

	<span class="section-wcusage-field-page-load">

      <br/>
      <!-- Load tabs on affiliate dashboard as separate pages. -->
      <?php wcusage_setting_toggle_option('wcusage_field_page_load', 0, esc_html__( 'Load tabs on affiliate dashboard as separate pages.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'This will make it so when each tab is clicked, it reloads the page, but it only loads the content for the selected tab.', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'If you experience very high volumes of orders for each coupon, this should help greately with affiliate dashboard speed/performance.', 'woo-coupon-usage' ); ?></i><br/>

  </span>

	<br/>

	<p>
    <!-- Hide the "all-time" stats on statistics tab and line graph. -->
    <?php wcusage_setting_toggle_option('wcusage_field_hide_all_time', 0, esc_html__( 'Hide the "all-time" stats on statistics tab and line graph.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This will still show the "Last 30 Days" and "Last 7 Days". It will also cause the "usage" stat to be calculated slightly different.', 'woo-coupon-usage' ); ?></i><br/>
	</p>

  <br/>

  <?php $wcusage_field_user_list_affiliates = wcusage_get_setting_value('wcusage_field_user_list_affiliates', '0'); ?>
  <?php if($wcusage_field_user_list_affiliates) { ?>
    <?php  wcusage_setting_toggle_option('wcusage_field_user_list_affiliates', 0, esc_html__( 'Only show users with the "coupon affiliate" role when manually assigning users to coupons.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When assigning users to coupons, if this is enabled, it will only show the list of users with the custom "coupon affiliate" role.', 'woo-coupon-usage' ); ?></i>
    <br/><i><?php echo esc_html__( 'This means that you will need to manually edit existing users to the "coupon affiliate", or have them automatically assign to this role when filling out the registration form (enable this in "registration settings").', 'woo-coupon-usage' ); ?></i>
    <br/><br/>
  <?php } ?>

  <?php $wcusage_field_hide_coupon_edit_user_list = wcusage_get_setting_value('wcusage_field_hide_coupon_edit_user_list', '0'); ?>
  <?php if($wcusage_field_hide_coupon_edit_user_list) { ?>
    <?php wcusage_setting_toggle_option('wcusage_field_hide_coupon_edit_user_list', 0, esc_html__( 'Disable the autofill user picker when assigning users to coupon.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Turn this option on to disable the user search/picker, and to just enter the user ID manually.', 'woo-coupon-usage' ); ?></i><br/>
    <br/>
  <?php } ?>

	<hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Cookie Settings', 'woo-coupon-usage' ); ?>:</h3>

  <p>
    <?php echo esc_html__('Disabling these will prevent cookies from being stored by the plugin in the visitors browsers.', 'woo-coupon-usage'); ?>
    <br/>
    <?php echo esc_html__('You should only disable this if your primary method of tracking referrals is through customers manually applying the affiliates coupon at checkout.', 'woo-coupon-usage'); ?>
    <br/>
    <?php echo esc_html__('If cookies are disabled, the referral links will still work but will not be as effective, since they can only attempt to auto-apply coupons on the first page they load.', 'woo-coupon-usage'); ?>
  </p>

  <br/>

  <!-- wcusage_field_store_cookies -->
  <?php wcusage_setting_toggle_option('wcusage_field_store_cookies', 1, esc_html__( 'Store cookies for referral links (recommended).', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'This will store a cookie in the visitors browser when they click on a referral link, to automatically apply the coupon code once they add items to their cart, and to track link stats better.', 'woo-coupon-usage' ); ?></i><br/>
  <i><?php echo esc_html__( 'If disabled, then it will only be able to try to automatically apply the coupon code on their first page visit, and URL only conversion tracking will not work. Coupons will be required to track referrals.', 'woo-coupon-usage' ); ?></i><br/>
  <?php wcusage_setting_toggle('.wcusage_field_store_cookies', '.wcu-referral-cookies'); // Show or Hide ?>

  <br/>

  <!-- wcusage_field_store_cookies -->
  <?php wcusage_setting_toggle_option('wcusage_field_store_cookies_mla', 1, esc_html__( 'Store cookies for MLA referral links (recommended).', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'This will store a cookie in the visitors browser when they click on a referral link, so the referral can be tracked even if they do not register on their first page visit.', 'woo-coupon-usage' ); ?></i><br/>
  <i><?php echo esc_html__( 'If this is disabled, then it will only be able to try to track referrals if they register on their first page visit.', 'woo-coupon-usage' ); ?></i><br/>
  
  <br/>

  <!-- wcusage_field_store_cookies -->
  <?php wcusage_setting_toggle_option('wcusage_field_store_cookies_domains', 1, esc_html__( 'Store cookies for domain link tracking and blacklists.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'This is required for domain link tracking and domain blacklists to work.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( '(Admin) Activity Log', 'woo-coupon-usage' ); ?>:</h3>

  <!-- Enable Activity Log -->
  <?php wcusage_setting_toggle_option('wcusage_enable_activity_log', 1, esc_html__( 'Enable Activity Log', 'woo-coupon-usage' ), '0px'); ?>

	<br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - User Access', 'woo-coupon-usage' ); ?>:</h3>

  <!-- Show full coupon page info automatically, if there is only one coupon. -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_coupon_if_single', 1, 'Users Dashboard - ' . esc_html__( 'Show full coupon page info automatically, if there is only one coupon.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'With the "[couponaffiliates]" shortcode, when a user visits this page (without the unique URL ID), enable to show full affiliate dashboard automatically if the affiliate user is only assigned to one coupon.', 'woo-coupon-usage' ); ?></i>
  <br/><i><?php echo esc_html__( 'Normally it will just show the coupon name, discount, usage, and button to direct them to the unique URL ID, for the affiliate dashboard for that coupon.', 'woo-coupon-usage' ); ?></i>
  <br/><i><?php echo esc_html__( 'Useful if you simply want a generic "affiliate" page to direct affiliates to, instead of a unique link for each one.', 'woo-coupon-usage' ); ?></i>

  <br/><br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - Privacy', 'woo-coupon-usage' ); ?>:</h3>

  <!-- Make all dashboard URLs private/hidden to everyone except administrators. -->
  <?php wcusage_setting_toggle_option('wcusage_field_urlprivate', 1, esc_html__( 'Make all dashboard URLs private/hidden to everyone except administrators and assigned user.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, all unique affiliate dashboard URLs will ALWAYS be private, and only be visible to the assigned user (and admins).', 'woo-coupon-usage' ); ?></i>
  <br/><i><?php echo esc_html__( 'You will just need to use the shortcode:', 'woo-coupon-usage' ); ?> [couponaffiliates] - <?php echo esc_html__( 'Then, only users that are assigned to a coupon will be able to see their dashboard (for that coupon) on this page.', 'woo-coupon-usage' ); ?></i>
  <br/><i><?php echo esc_html__( 'When disabled, if there are no users assigned to a coupon, the dashboard can be viewed by anyone if they visit the unique URL directly. However, if there is a user assigned to it, the URL will be private.', 'woo-coupon-usage' ); ?></i>
  
  <?php if ( wcu_fs()->can_use_premium_code() ) { ?>
    
  <br/><br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - Payouts', 'woo-coupon-usage' ); ?>:</h3>

  <!-- Allow admin accounts to view payouts tab (and request payouts) for all coupons. -->
  <?php wcusage_setting_toggle_option('wcusage_field_payouts_enable_admin', 1, esc_html__( 'Allow admin accounts to view payouts tab (and request payouts) for all coupons.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'With this enabled, admin accounts will also be able to view the "payouts" tab when viewing any of the affiliate coupon dashboard pages.', 'woo-coupon-usage' ); ?></i>

  <?php } ?>

  <br/><br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Extra Settings', 'woo-coupon-usage' ); ?></h3>

  <!-- Remove coupon ID from unique coupon URL. -->
  <?php wcusage_setting_toggle_option('wcusage_field_justcoupon', 1, esc_html__( 'Remove coupon ID from unique coupon dashboard URLs.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'Enabling this will allow the unique coupon affiliate dashboard URLs to be used without the ID, but both URLs will still work.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <!-- Hide the "Coupon code applied successfully." -->
  <?php wcusage_setting_toggle_option('wcusage_field_coupon_applied_hide', 1, esc_html__( 'Hide the "Coupon code applied successfully." message on all pages except for the cart/checkout pages.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When someone uses the referral URL, if the code is automatically applied, it will show this message on all pages.', 'woo-coupon-usage' ); ?></i><br/>
  <i><?php echo esc_html__( 'If you dont want the message to always show, toggle this setting on, and it will instead only show on the cart/checkout pages.', 'woo-coupon-usage' ); ?></i><br/>
  
  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Coupon Checkout Settings', 'woo-coupon-usage' ); ?></h3>

  <!-- Hide "0.00" value on checkout from referral coupons. -->
  <?php wcusage_setting_toggle_option('wcusage_field_coupon_hide_zero', 1, esc_html__( 'Hide "0.00" value on checkout from referral coupons.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When a referral coupon is applied, if the discount is "0.00", it will hide the 0.00 discount line on the checkout page.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <!-- Custom text for "Coupon" on checkout. -->
  <?php wcusage_setting_text_option('wcusage_field_coupon_custom_text', '', esc_html__( 'Custom text for "Coupon" on checkout for affiliate coupons:', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'If you want to change the text "Coupon" to something else on the checkout page, enable this option and enter the custom text below.', 'woo-coupon-usage' ); ?></i><br/>
  <i><?php echo esc_html__( 'This will only be replaced for coupons with an affiliate assigned to it.', 'woo-coupon-usage' ); ?></i><br/>

  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( '(Admin) WooCommerce Orders "Affiliate Info" Sections', 'woo-coupon-usage' ); ?>:</h3>

	<i><?php echo esc_html__( 'Enable or disable the "affiliate info" sections displayed on WooCommerce orders in the backend.', 'woo-coupon-usage' ); ?></i>

	<br/><br/>

  <!-- Show "Affiliate Info" Column in orders list. -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_column_code', 1, esc_html__( 'Show "Affiliate Info" Column in orders list.', 'woo-coupon-usage' ), '0px'); ?>

	<br/>

  <!-- Show "Affiliate Info" widget in single orders. -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_orders_aff_info', 1, esc_html__( 'Show "Affiliate Info" widget in single orders.', 'woo-coupon-usage' ), '0px'); ?>

  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Admin Permissions', 'woo-coupon-usage' ); ?>:</h3>

  <!-- DROPDOWN - Admin Permission -->
  <p>
    <?php
    $wcusage_field_admin_permission = wcusage_get_setting_value('wcusage_field_admin_permission', 'administrator');
    ?>
    <input type="hidden" value="0" id="wcusage_field_admin_permission" data-custom="custom" name="wcusage_options[wcusage_field_admin_permission]" >

    <strong><label for="scales"><?php echo esc_html__( 'User role required for plugin admin capabilities:', 'woo-coupon-usage' ); ?></label></strong><br/>
    <select name="wcusage_options[wcusage_field_admin_permission]" id="wcusage_field_admin_permission">
      <?php
      global $wp_roles;
      $roles = $wp_roles->get_names();

      foreach($roles as $role_value => $role_name) {
        $role_object = get_role($role_value);
        
        if($role_object->has_cap('manage_options') || $role_object->has_cap('read_shop_order') || $role_object->has_cap('wcusage_manage')) {
          echo '<option value="'.esc_attr($role_value).'"'.selected($wcusage_field_admin_permission, $role_value, false).'>'.esc_html($role_name).'</option>';
        }
      }
      ?>
    </select>
    <br/>
    <i><?php echo esc_html__( 'This is the user permission required to have full access for this plugin. Administrator will always have access to everything.', 'woo-coupon-usage' ); ?></i>
    <br/>
    <i><?php echo esc_html__( 'This excludes the plugin settings which are available to those with the "manage_options" permission.', 'woo-coupon-usage' ); ?></i>
  </p>

  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Translations', 'woo-coupon-usage' ); ?></h3>

  <p style="display: none;">
		<?php
    if(isset($options['wcusage_field_show_custom_translations'])) {
      $wcusage_show_custom_translations = $options['wcusage_field_show_custom_translations'];
    } else {
      $wcusage_show_custom_translations = "";
    }
    $checked2 = ( $wcusage_show_custom_translations == '1' ? ' checked="checked"' : '' );
    ?>

	<label class="switch">
		<input type="hidden" value="0" id="wcusage_field_show_custom_translations" data-custom="custom" name="wcusage_options[wcusage_field_show_custom_translations]" >
		<input type="checkbox" value="1" id="wcusage_field_show_custom_translations" data-custom="custom" name="wcusage_options[wcusage_field_show_custom_translations]" <?php
    echo esc_html($checked2);
    ?>>
	<span class="slider round">
    <span class="on"><span class="fa-solid fa-check"></span></span>
    <span class="off"></span>
  </span>
	</label>
		<strong><label for="scales"><?php echo esc_html__( 'Show/enable custom translation settings (discontinued - not recommended).', 'woo-coupon-usage' ); ?></label></strong><br/>
	</p>
  <i><?php echo esc_html__( 'Note: We recommended using', 'woo-coupon-usage' ); ?> "<a href="<?php echo esc_url(admin_url('plugin-install.php?s=Loco%20Translate&tab=search&type=term')); ?>" target="_blank">Loco Translate</a>" <?php echo esc_html__( 'or', 'woo-coupon-usage' ); ?> "<a href="https://wpml.org" target="_blank">WPML</a>" <?php echo esc_html__( 'to fully translate this plugin.', 'woo-coupon-usage' ); ?></i><br/>

  <!--
  <br/><hr/>
	<h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Export Settings</h3>

  <textarea style="width: 100%; height: 100px;"><?php // echo json_encode($options); ?></textarea>
  -->

  <br/><hr/>
	<h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Plugin Uninstallation</h3>

	<p>
		<?php
    $wcusage_field_deactivate_delete = wcusage_get_setting_value('wcusage_field_deactivate_delete', '0');
    $checked2 = ( $wcusage_field_deactivate_delete == '1' ? ' checked="checked"' : '' );
    ?>
	<label class="switch">
		<input type="hidden" value="0" id="wcusage_field_deactivate_delete" data-custom="custom" name="wcusage_options[wcusage_field_deactivate_delete]" >
		<input type="checkbox" value="1" id="wcusage_field_deactivate_delete" data-custom="custom" name="wcusage_options[wcusage_field_deactivate_delete]" <?php
    echo esc_html($checked2);
    ?>>
	<span class="slider round">
    <span class="on"><span class="fa-solid fa-check"></span></span>
    <span class="off"></span>
  </span>
	</label>
		<strong><label for="scales"><?php echo esc_html__( 'Delete plugin options and custom database tables on plugin deletion.', 'woo-coupon-usage' ); ?></label></strong>
	</p>
  <i><?php echo esc_html__( 'If enabled, when uninstalling (deleting) the plugin, most plugin options and custom tables/data created by this plugin will be deleted. Some data will still remain such as custom order & coupon meta data (if any).', 'woo-coupon-usage' ); ?></i>
  <br/><i><?php echo esc_html__( 'This will not delete your orders or WooCommerce data. If you want to be safe, be sure to make a backup of your website beforehand in-case you want to restore this data.', 'woo-coupon-usage' ); ?></i>

	</div>

 <?php
}
