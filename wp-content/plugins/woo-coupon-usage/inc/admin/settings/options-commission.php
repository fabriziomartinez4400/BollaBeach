<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Commission Settings
function wcusage_field_cb_commission( $args )
{
  $options = get_option( 'wcusage_options' );
  $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
  $probrackets = ( $ispro ? "" : " (PRO)" );
  ?>

	<div id="commission-settings" class="settings-area">

	<h1><?php echo esc_html__( 'Flexible Commission Settings', 'woo-coupon-usage' ); ?></h1>
  
  <hr/>

  <!-- Enable commission calculation statistics -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_commission', 1, esc_html__( 'Enable Commission Calculations & Statistics', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, commission will be calculated and displayed on the affiliate dashboard.', 'woo-coupon-usage' ); ?></i>

  <br/><br/><hr/>

  <!-- ********** Commission Amounts ********** -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Commission Amounts', 'woo-coupon-usage' ); ?>:</h3>
  <?php do_action( 'wcusage_hook_setting_section_commission_amounts' ); ?>

  <?php $wcusage_field_affiliate_custom_message = wcusage_get_setting_value('wcusage_field_affiliate_custom_message', ''); ?>

  <?php if($wcusage_field_affiliate_custom_message) { ?>
	<br/>
  <?php wcusage_setting_text_option('wcusage_field_affiliate_custom_message', '', esc_html__( 'Custom Text', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'Custom text shown affiliate dashboard for the "commission" amount. This will be overridden if you enter commission amounts on the coupon level.', 'woo-coupon-usage' ); ?></i>
  <br/>
  <?php } ?>

  <br/>

  <p class="setup-hide" style="font-size: 12px;"><?php echo esc_html__( 'Note: When updating these settings saved data will be refreshed for all dashboards automatically (first page load may take longer).', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'If you do not want past orders to be affected when commission stats are refreshed, you can enable this in the', 'woo-coupon-usage' ); ?>
    <a href="#" onclick="wcusage_go_to_settings('#tab-debug', '#wcusage_field_enable_never_update_commission_meta_p');"
    style="margin-top: 10px;"><?php echo esc_html__( 'debug settings tab', 'woo-coupon-usage' ); ?></a>.
  </p>

  <br/><hr/>

  <!-- ********** Calculations ********** -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Calculation Settings', 'woo-coupon-usage' ); ?>:</h3>
  <?php do_action( 'wcusage_hook_setting_section_calculations' ); ?>
  
  <br/><hr/>

  <!-- ********** Calculations ********** -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Tax Settings', 'woo-coupon-usage' ); ?>:</h3>
  <?php do_action( 'wcusage_hook_setting_section_tax' ); ?>

  <br/><br/><hr/>

  <!-- Currency Settings Toggle -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Multi-Currency Settings', 'woo-coupon-usage' ); ?>:</h3>

  <?php wcusage_setting_toggle_option('wcusage_field_enable_currency', 0, esc_html__( 'Enable "Multi-Currency" Functionality', 'woo-coupon-usage' ), '0px'); ?>

  <?php wcusage_setting_toggle('.wcusage_field_enable_currency', '.wcu-field-section-currency'); // Show or Hide ?>
  <span class="wcu-field-section-currency">
    <br/>

    <a href="#" onclick="wcusage_go_to_settings('#tab-currency', '#tab-currency');"
      class="wcu-addons-box-view-details" style="margin-left: 5px;">Click here</a> to manage multi-currency settings.

    <br/>
  </span>

	<span <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; display: block; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

    <!-- Priority Commission Field -->
    <br/><hr/>
    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Commission Priority', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?>:</h3>

		<?php
    $wcusage_field_priority_commission = wcusage_get_setting_value('wcusage_field_priority_commission', 'product');
    wcusage_setting_option_set_default($options, 'wcusage_field_priority_commission', 'product');
    ?>
		<input type="hidden" value="0" id="wcusage_field_priority_commission" data-custom="custom" name="wcusage_options[wcusage_field_priority_commission]" >
		<strong><label for="scales"><?php echo esc_html__( 'Which custom commission values should be applied as priority?', 'woo-coupon-usage' ); ?></label></strong><br/>
		<select name="wcusage_options[wcusage_field_priority_commission]" id="wcusage_field_priority_commission">
			<option value="product" <?php if($wcusage_field_priority_commission == "product") { ?>selected<?php } ?>><?php echo esc_html__( 'Product Commission Settings', 'woo-coupon-usage' ); ?></option>
			<option value="coupon" <?php if($wcusage_field_priority_commission == "coupon") { ?>selected<?php } ?>><?php echo esc_html__( 'Coupon Commission Settings', 'woo-coupon-usage' ); ?></option>
		</select>
    <br/><i><?php echo esc_html__( 'This setting is required in case you have set custom commission amounts on both a coupon level, and product level.', 'woo-coupon-usage' ); ?></i>
    <br/><i><?php echo esc_html__( 'It will set one as priority, so if both are set, the commission settings for your chosen priority will be used.', 'woo-coupon-usage' ); ?></i><br/>
    
    <br/><hr/>

    <h3 id="wcu-setting-header-lifetime">
      <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Lifetime Commission', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?>:
    </h3>

    <i><?php echo esc_html__( 'With lifetime commission enabled, once someone uses the affiliates coupon code, that customer will be linked to the affiliate forever, and ALL future purchases from that customer and will count as a referral, even if they dont re-use the coupon code.', 'woo-coupon-usage' ); ?></i>
    <i><?php echo esc_html__( 'Even if the "coupon code" isnt used, the commission and sales will still be tracked on the coupons affiliate dashboard. All future orders by that customer, even with different coupon codes, will only apply to the original coupon affiliate.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Enable "lifetime commission" features. -->
    <?php wcusage_setting_toggle_option('wcusage_field_lifetime', 0, esc_html__( 'Enable "lifetime commission" features.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This option will allow you to enable "lifetime commission" either globally, or on a per-coupon basis.', 'woo-coupon-usage' ); ?></i>

    <br/>

    <?php wcusage_setting_toggle('.wcusage_field_lifetime', '.wcu-field-section-lifetime-features'); // Show or Hide ?>
    <span class="wcu-field-section-lifetime-features">
    <br/>

    <!-- Enable "lifetime commission" functionality globally for all affiliate coupons. -->
    <?php wcusage_setting_toggle_option('wcusage_field_lifetime_all', 0, esc_html__( 'Enable "lifetime commission" functionality globally for all affiliate coupons.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Enabling this option will enable lifetime commission for ALL your affiliates & coupons. You can alternatively enable lifetime commission on a per-coupon basis, in the individual coupon settings.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Only trigger lifetime referral if coupon is assigned to user. -->
    <?php wcusage_setting_toggle_option('wcusage_field_lifetime_require_user', 1, esc_html__( 'Only trigger lifetime referral if coupon is assigned to user.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Enable this to only link customer to coupon as "lifetime referral" if the coupon has a user affiliate assigned to it.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Track user registrations as a lifetime referral. -->
    <?php wcusage_setting_toggle_option('wcusage_field_lifetime_track_register', 0, esc_html__( 'Track user registrations as a lifetime referral.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'With this enabled, if someone follows a referral link and registers (creates an account), they will then be linked to that affiliate, even without initially placing an order.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Lifetime Commission Expiry (Days) -->
    <?php wcusage_setting_number_option('wcusage_field_lifetime_expire', '0', esc_html__( 'Lifetime Commission Expiry (Days)', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Optional: How many days after being assigned as a "lifetime" referral should it expire, and the customer be unlinked from the customer.', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'Set to "0" for permanent lifetime commission with no expiry time.', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'Can also be set on a per-coupon basis.', 'woo-coupon-usage' ); ?></i><br/>

    </span>

    <!-- Per User Role -->
    <br/><hr/>
    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;" id="wcu-setting-header-commission-user-role"></span> <?php echo esc_html__( 'Per User Role Commission', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?>:</h3>

    <?php wcusage_setting_toggle_option('wcusage_field_affiliate_per_user', 0, esc_html__( 'Enable "Per User Role" Commission', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Allows you to set custom commission rates per user role. This will replace the settings set above, if it is set.', 'woo-coupon-usage' ); ?></i>

    <?php wcusage_setting_toggle('.wcusage_field_affiliate_per_user', '.wcu-field-section-per-user'); // Show or Hide ?>
    <span class="wcu-field-section-per-user">

    <br/><br/>

    <p style="font-size: 17px; margin-left: 40px;"><strong><?php echo esc_html__( 'Information:', 'woo-coupon-usage' ); ?></strong></p>

    <p style="margin-left: 40px;">- <?php echo esc_html__( 'Set the custom commission rates for each user role below (this is the role of the affiliate user). Leave empty to use default rates.', 'woo-coupon-usage' ); ?></p>

    <p style="margin-left: 40px;">- <?php echo esc_html__( 'If you set custom "coupon" commission for that affiliate, or "per product" commission, they WILL take priority over the "user role" commission.', 'woo-coupon-usage' ); ?></p>

    <p style="margin-left: 40px;">- <?php echo esc_html__( 'If the affiliate user is assigned to multiple user roles, it will apply the commission rates for the first role it detects with any custom values set.', 'woo-coupon-usage' ); ?></p>

    <p style="margin-left: 40px;">- <?php echo esc_html__( 'When updating these settings, you may need to click the "REFRESH ALL DATA" button in the "Debug" tab for changes to show immediately for existing orders.', 'woo-coupon-usage' ); ?></p>

    <br/>

    <style>
    .settings-user-role-fields .wcu-update-icon {
      position: absolute !important;
    }
    </style>
    <?php
    $editable_roles = get_editable_roles();
    foreach ($editable_roles as $role => $details) {
        echo "<div class='settings-user-role-fields' style='width: 100%; max-width: 320px; float: left; display: block; margin-bottom: 15px;'>";
        echo "<br/><strong style='font-size: 17px; margin-left: 40px;'>" . esc_html(translate_user_role($details['name'])) . ":</strong>";
        wcusage_setting_text_option('wcusage_field_affiliate_percent_role_' . esc_attr($role), "", '% - ' . esc_html__( 'Percentage Amount Of Total Order', 'woo-coupon-usage' ), '40px');
        wcusage_setting_text_option('wcusage_field_affiliate_fixed_order_role_' . esc_attr($role), "", wcusage_get_currency_symbol() . ' - ' . esc_html__( 'Fixed Amount Per Order', 'woo-coupon-usage' ), '40px');
        wcusage_setting_text_option('wcusage_field_affiliate_fixed_product_role_' . esc_attr($role), "", wcusage_get_currency_symbol() . ' - ' . esc_html__( 'Fixed Amount Per Product', 'woo-coupon-usage' ), '40px');
        echo "</div>";
    }
    ?>

    <div style="clear: both;"></div>

    </span>

    <div style="clear: both;"></div>

    <br/><hr/>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Non-Affiliate Coupon Settings', 'woo-coupon-usage' ); ?>:</h3>

    <!-- Disable commission statistics for non-affiliate coupons. -->
    <?php wcusage_setting_toggle_option('wcusage_field_commission_disable_non_affiliate', 0, esc_html__( 'Hide commission statistics for non-affiliate coupons.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When enabled, commission statistics are disabled/hidden for coupons that are not assigned to an affiliate user.', 'woo-coupon-usage' ); ?></i>

    <?php wcusage_setting_toggle('.wcusage_field_commission_disable_non_affiliate', '.wcu-field-section-non-affiliate'); // Show or Hide ?>
    <span class="wcu-field-section-non-affiliate">
    <?php if( wcu_fs()->can_use_premium_code() ) { ?>
    <br/><br/>

    <?php wcusage_setting_toggle_option('wcusage_field_commission_disable_non_affiliate_unpaid', 1, esc_html__( 'Stop "unpaid commission" from being earned for non-affiliate coupons.', 'woo-coupon-usage' ), '40px'); ?>
    <i style="margin-left: 40px;"><?php echo esc_html__( 'When enabled, the unpaid commission will also not be added to non-affiliate coupons.', 'woo-coupon-usage' ); ?></i>
    <?php } ?>
    </span>

  </span>

	</div>

 <?php
}

/**
 * Settings Section: Commission Amounts
 *
 */
add_action( 'wcusage_hook_setting_section_commission_amounts', 'wcusage_setting_section_commission_amounts' );
if( !function_exists( 'wcusage_setting_section_commission_amounts' ) ) {
  function wcusage_setting_section_commission_amounts() {

  $options = get_option( 'wcusage_options' );
  ?>

  <p>- <?php echo esc_html__( 'Enter your commission amounts below (0 to disable). If you enter multiple types, they will be combined. For example you could have: 10% of total order, plus an extra $2 per product.', 'woo-coupon-usage' ); ?></p>

	<p>- (Pro Version) <?php echo esc_html__( 'These values be assigned on a per affiliate coupon, per product, per category, or per group basis.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/flexible-commission-settings/" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</p>

  <br/>

  <?php $textaffiliatecommission = esc_html__( 'Affiliate commission', 'woo-coupon-usage' ) . ": "; ?>

  <!-- Percentage Amount Of Total Order -->
  <?php wcusage_setting_number_option('wcusage_field_affiliate', '0', esc_html__('Percentage Commission (% Of Total Order)', 'woo-coupon-usage'), '0px', '0.01'); ?>

  <br/>

  <!-- Fixed Amount Per Order -->
  <?php 
  $fixed_order_label = wp_kses_post(sprintf(__('Fixed Commission (%s - Amount Per Order)', 'woo-coupon-usage'), wcusage_get_currency_symbol()));
  wcusage_setting_number_option('wcusage_field_affiliate_fixed_order', '0', $fixed_order_label, '0px', '0.01'); 
  ?>

  <br/>

  <!-- Fixed Amount Per Product -->
  <?php 
  $fixed_product_label = wp_kses_post(sprintf(__('Fixed Commission (%s - Amount Per Product)', 'woo-coupon-usage'), wcusage_get_currency_symbol()));
  wcusage_setting_number_option('wcusage_field_affiliate_fixed_product', '0', $fixed_product_label, '0px', '0.01'); 
  ?>

  <?php
  }
}

/**
 * Settings Section: Calculation Settings
 *
 */
add_action( 'wcusage_hook_setting_section_calculations', 'wcusage_setting_section_calculations' );
if( !function_exists( 'wcusage_setting_section_calculations' ) ) {
  function wcusage_setting_section_calculations() {

  $options = get_option( 'wcusage_options' );
  ?>

  <p><?php echo esc_html__( 'By default the order totals displayed on the dashboard, and used for % commission calculations exclude shipping costs, fees, taxes, and discounts (recommended).', 'woo-coupon-usage' ); ?></p>
  <p><?php echo esc_html__( 'You can however customise this below if required:', 'woo-coupon-usage' ); ?></p>

  <br/>

  <!-- Calculate commission BEFORE the discount is applied (at full price). -->
  <?php wcusage_setting_toggle_option('wcusage_field_commission_before_discount', 0, esc_html__( 'Include the "coupon discount" in % commission calculations.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, % commission will be calculated on the order subtotal (instead of total), before the coupon discount is deducted from it.', 'woo-coupon-usage' ); ?></i>

  <br/><br/>

  <!-- Calculate affiliate commission BEFORE the discount is applied (at full price). -->
  <?php wcusage_setting_toggle_option('wcusage_field_commission_include_shipping', 0, esc_html__( 'Include "shipping costs" in % commission calculations & order totals.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, % commission will be calculated based on the order subtotal/total including "shipping costs".', 'woo-coupon-usage' ); ?></i>

  <br/>

  <br/><p><span class="fa-solid fa-gear"></span> <strong><?php echo esc_html__( 'Advanced Calculation Settings', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_commission_calc_advanced">Show <span class="fa-solid fa-arrow-down"></span></button></p>

  <?php wcu_admin_settings_showhide_toggle("wcu_show_commission_calc_advanced", "wcu_commission_calc_advanced", "Show", "Hide"); ?>
  <div id="wcu_commission_calc_advanced" style="display: none; padding-top: 10px;">

    <!-- Calculate commission BEFORE any custom discounts are applied. -->
    <?php wcusage_setting_toggle_option('wcusage_field_commission_before_discount_custom', 0, esc_html__( 'Include "custom discounts" in % commission calculations & order totals.', 'woo-coupon-usage' ), ''); ?>
    <i><?php echo esc_html__( 'When enabled, % commission will be calculated before any custom discounts are deducted from the subtotal/total. It will also be added to the subtotal/total shown in statistics.', 'woo-coupon-usage' ); ?></i>
    <br/><i><?php echo esc_html__( '(Custom discounts include negative fees, store credit, and discounts added by other plugins.)', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Calculate affiliate commission BEFORE the discount is applied (at full price). -->
    <?php wcusage_setting_toggle_option('wcusage_field_commission_include_fees', 0, esc_html__( 'Include "fees" in % commission calculations & order totals.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When enabled, % commission will be calculated based on the order subtotal/total including "fees". It will also be added to the subtotal/total shown in statistics.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Max Commission -->
    <?php wcusage_setting_text_option('wcusage_field_order_max_commission', '', esc_html__( 'Maximum commission per order:', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This allows you to set the maximum commission amount that is calculated and can be earned per order referred by affiliates.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Commission rounding mode -->
    <?php wcusage_setting_select_option(
      'wcusage_field_commission_rounding_mode',
      'standard',
      esc_html__( 'Commission rounding mode', 'woo-coupon-usage' ),
      '0px',
      array(
        'standard' => esc_html__( 'Round to nearest pence/cent (default)', 'woo-coupon-usage' ),
        'down'     => esc_html__( 'Round down to nearest pence/cent (floor/truncate)', 'woo-coupon-usage' ),
      )
    ); ?>
    <i><?php echo esc_html__( 'Choose how commission amounts are rounded to currency decimals. "Round down" will always truncate extra decimals instead of rounding up.', 'woo-coupon-usage' ); ?></i>

  </div>

  <p class="setup-hide" style="font-size: 12px;"><br/><?php echo esc_html__( 'Note: When updating these settings saved data will be refreshed for all dashboards automatically (first page load may take longer).', 'woo-coupon-usage' ); ?></p>

  <?php
  }
}

/**
 * Settings Section: Tax Settings
 *
 */
add_action( 'wcusage_hook_setting_section_tax', 'wcusage_setting_section_tax' );
if( !function_exists( 'wcusage_setting_section_tax' ) ) {
  function wcusage_setting_section_tax() {

  $options = get_option( 'wcusage_options' );
  ?>

  <!-- Include tax in orders and commission calculations. -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_tax', 0, esc_html__( 'Include "taxes" in % commission calculations & order totals.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'If enabled, order tax will be added to the order subtotal/total/discount for orders in the statistics, and when calculating percentage (%) commission calculations.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <!-- Include tax on fixed commission amounts. -->
  <span class="wcu-field-section-tax-fixed">
    <?php wcusage_setting_toggle_option('wcusage_field_show_tax_fixed', 0, esc_html__( 'Include "taxes" in "fixed" commission calculations.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'If enabled, order tax will be added to the fixed commission calculations.', 'woo-coupon-usage' ); ?></i><br/>
    <br/>
  </span>

  <!-- Deduct a custom percentage from order total before calculating commission -->
  <?php wcusage_setting_number_option('wcusage_field_affiliate_deduct_percent', '0', esc_html__( 'Custom Tax Adjustment (%):', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'Deduct a custom percentage from the order subtotal, before calculating the commission (% commission).', 'woo-coupon-usage' ); ?></i>

  <script>
  jQuery( document ).ready(function() {
    if(jQuery('#wcusage_field_affiliate_deduct_percent').val() <= 0) {
      jQuery('.wcu-field-section-deduct-percent-show').hide();
    }
    jQuery('#wcusage_field_affiliate_deduct_percent').change(function(){
      if(jQuery('#wcusage_field_affiliate_deduct_percent').val() > 0) {
        jQuery('.wcu-field-section-deduct-percent-show').show();
      } else {
        jQuery('.wcu-field-section-deduct-percent-show').hide();
      }
    });
  });
  </script>
  <span class="wcu-field-section-deduct-percent-show">
  <br/><br/>

  <!-- Display adjusted total and subtotal. -->
  <?php wcusage_setting_toggle_option('wcusage_field_affiliate_deduct_percent_show', 0, esc_html__( 'Display adjusted total and subtotal.', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, this will also show the adjusted "total" and "subtotal" (with deducted percentage) on affiliate dashboard. When disabled, only the "commission" stat will be affected by the adjustment.', 'woo-coupon-usage' ); ?></i>
  </span>

  <br/>

  <i class="setup-hide"><?php echo esc_html__( 'Note: Changing these tax settings will affect stats for all coupons and all new/past orders. Stats are refreshed next time there is a new order (for that specific coupon), or you click the "refresh all data" button in "debug" settings to refresh ALL coupon stats.', 'woo-coupon-usage' ); ?></i>

  <?php
  }
}
