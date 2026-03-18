<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_fraud( $args )
{
  $options = get_option( 'wcusage_options' );
  $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
  $probrackets1 = ( $ispro ? "" : " (PRO)" );
  $probrackets2 = ( $ispro ? "" : "(PRO) " );
  ?>

	<div id="fraud-settings" class="settings-area">

    <h1><?php echo esc_html__( 'Fraud Prevention & Usage Restrictions', 'woo-coupon-usage' ); ?></h1>

    <hr/>

    <p><?php echo esc_html__( 'Apply restrictions on when affiliate coupons can be used to help prevent affiliate fraud.', 'woo-coupon-usage' ); ?></p>

    <br/><hr/>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Coupon Usage Restrictions', 'woo-coupon-usage' ); ?>:</h3>

    <!-- Allow affiliate user to apply their own coupon code at cart / checkout. -->
    <?php wcusage_setting_toggle_option('wcusage_field_allow_assigned_user', 1, esc_html__( 'Allow affiliate user to apply their own coupon code at cart / checkout.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When disabled, the affiliate user will be prevented from using their own coupon code (coupons they are assigned to) at cart or checkout.', 'woo-coupon-usage' ); ?></i>
    <?php wcusage_setting_toggle('.wcusage_field_url_referrals', '.wcusage_field_url_referrals_p_info'); // Show or Hide ?>
    <br/><i class="wcusage_field_url_referrals_p_info"><?php echo esc_html__( 'This will also allow them to use their own referral link and earn commission on their own purchases.', 'woo-coupon-usage' ); ?></i>
    <br/>
    <i><?php echo esc_html__( 'Unless you have a specific use case, we suggest keeping this disabled as in general it can cause some issues (commission granted to all coupons).', 'woo-coupon-usage' ); ?></i>
    
    <br/><br/>

    <!-- Allow multiple affiliate coupons to be used in the same order. -->
    <?php wcusage_setting_toggle_option('wcusage_field_allow_multiple_coupons', 0, esc_html__( 'Allow multiple affiliate coupons to be used in the same order.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When disabled, it will only allow 1 affiliate coupon to be used per order. (This is any coupons that have an affiliate user assigned to them.)', 'woo-coupon-usage' ); ?></i>
    <br/>
    <i><?php echo esc_html__( 'We highly recommend that you keep this option DISABLED, as it may cause some issues, or paying too much commission.', 'woo-coupon-usage' ); ?></i>
    <br/>
    <i><?php echo esc_html__( '- Currently with this option enabled, if multiple affiliate coupons are applied with different custom commission rates, it will apply the commission rate for the first coupon to all affiliate coupons.', 'woo-coupon-usage' ); ?></i>
    <br/>
    <?php
    $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
    if($wcusage_field_mla_enable) { ?>
    <i><?php echo esc_html__( '- MLA commission will also only apply to the first affiliate coupon.', 'woo-coupon-usage' ); ?></i>
    <br/>
    <?php } ?>
    <br/>

    <!-- Require referral link before affiliate coupons can be applied. -->
    <?php wcusage_setting_toggle_option('wcusage_field_require_referral_link', 0, esc_html__( 'Require customers to visit the affiliate referral link before applying their coupon.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When enabled, affiliate coupons can only be applied if the customer currently has the tracking cookie from that affiliate referral link.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <!-- Allow affiliate coupons to be used by existing and new customers. -->
    <p>
      <?php $wcusage_field_allow_all_customers = wcusage_get_setting_value('wcusage_field_allow_all_customers', '1'); ?>
      <input type="hidden" value="0" id="wcusage_field_allow_all_customers" data-custom="custom" name="wcusage_options[wcusage_field_allow_all_customers]" >
      <strong><label for="scales"><?php echo esc_html__( 'Who can apply affiliate coupons to their cart?', 'woo-coupon-usage' ); ?></label></strong><br/>
      <select name="wcusage_options[wcusage_field_allow_all_customers]" id="wcusage_field_allow_all_customers">
        <option value="1" <?php if($wcusage_field_allow_all_customers == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'All Existing & New Customers', 'woo-coupon-usage' ); ?></option>
        <option value="0" <?php if($wcusage_field_allow_all_customers == "0") { ?>selected<?php } ?>><?php echo esc_html__( 'New Customers Only (First Order)', 'woo-coupon-usage' ); ?></option>
      </select>
    </p>
    <i><?php echo esc_html__( '(Only applies to coupons that have an affiliate user assigned to them.)', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'If preferred, you can enable "New Customers Only" for individual coupons in the "Usage limits" coupon settings tab.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/new-customers-only/" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></i><br/>

    <?php if(!is_plugin_active('better-coupon-restrictions/coupon-restrictions.php') && !is_plugin_active('better-coupon-restrictions-pro/coupon-restrictions-pro.php')) { ?>
    <br/><br/>
    <p class="wcu-admin-faq" style="padding-top: 9px;">
    <?php echo sprintf( wp_kses_post( __( 'Want more advanced coupon usage restrictions? Check out our %s plugin!',
    'woo-coupon-usage' ) ), '<a href="https://relywp.com/plugins/better-coupon-restrictions-woocommerce/?utm_source=caffs-settings" target="_blank">Better Coupon Restrictions</a>' ); ?>
    </p>
    <br/>
    <?php } ?>

    <br/><hr/>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Visitors Blacklist', 'woo-coupon-usage' ); ?>:</h3>

    <p><?php echo esc_html__( 'These visitors will not be able to use any affiliate coupons on their purchases, and can not apply to become an affiliate.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/affiliate-fraud-prevention/" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></p>

    <br/>

    <!-- Blocked Domains -->
    <?php wcusage_setting_textarea_option('wcusage_field_fraud_block_ips', "", esc_html__( 'Blocked "Visitor ID" or "IP Address" List', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Enter one per line.', 'woo-coupon-usage' ); ?></i><br/>

    <div <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Domains Blacklist', 'woo-coupon-usage' ) . esc_html($probrackets1); ?>:</h3>

      <p><?php echo esc_html__( 'Visitors referred directly from any of these domains will not have referrals tracked or coupons applied automatically.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/affiliate-fraud-prevention/" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></p>

      <?php
      $wcusage_field_store_cookies_domains = wcusage_get_setting_value('wcusage_field_store_cookies_domains', '1');
      if(!$wcusage_field_store_cookies_domains) { ?>
        <p><strong><?php echo esc_html__( 'Note:', 'woo-coupon-usage' ); ?></strong> <?php echo esc_html__( 'This feature is disabled because you have disabled the cookie storage.', 'woo-coupon-usage' ); ?></p>
      <?php } ?>

      <br/>

      <!-- Blocked Domains -->
      <?php wcusage_setting_textarea_option('wcusage_field_fraud_block_domains', "", esc_html__( 'Blocked Domains List', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'Enter one per line. You do not need to include "http://", "https://", or "www." in the domain.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- Allow manually application of affiliate coupons. -->
      <?php wcusage_setting_toggle_option('wcusage_field_fraud_block_domains_manual', 0, esc_html__( 'Also block MANUAL use of affiliate coupons, if referred by a blocked domain.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'When enabled, visitors referred by blocked domains will be completely blocked from entering any affiliate coupons manually (as long as the cookie is saved).', 'woo-coupon-usage' ); ?></i>

      <br/><br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Direct Link Tracking Restrictions', 'woo-coupon-usage' ) . esc_html($probrackets1); ?>:</h3>

      <p><?php echo esc_html__( 'You can apply additional strict fraud prevention with direct link tracking.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-direct-link-tracking" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></p></p>

      <p><?php echo esc_html__( 'With this feature, you can enable an option to prevent ALL affiliate coupons and referral links from working UNLESS the customer was directly linked by the approved domain that is assigned to that coupon.', 'woo-coupon-usage' ); ?></p>

      <br/>

      <a href="#" onclick="wcusage_go_to_settings('#tab-urls', '#wcu-setting-header-referral-directlinks');"
        class="wcu-addons-box-view-details" style="margin-left: 0px;">
        <?php echo esc_html__( 'View "Direct Link Tracking" Settings', 'woo-coupon-usage' ); ?>
      </a>

      <?php wcusage_setting_toggle('.wcusage_field_enable_directlinks', '.wcu-field-section-directlinks'); // Show or Hide ?>
      <span class="wcu-field-section-directlinks">

        <br/><br/>

        <?php wcusage_setting_toggle_option('wcusage_field_enable_directlinks_protection', 0, 'Only allow affiliate coupons to be applied when directly linked by an approved domain.', '0px'); ?>
        <i><?php echo esc_html__( 'Enabling this option will prevent ALL affiliate coupons and referral links from working UNLESS the customer was directly linked by the approved domain that is assigned to that coupon.', 'woo-coupon-usage' ); ?></i><br/>

      </span>

      <br/>

    </div>

	</div>

 <?php
}
