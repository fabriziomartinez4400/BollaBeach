<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_subscriptions( $args )
{
  $options = get_option( 'wcusage_options' );
  $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
  $probrackets = ( $ispro ? "" : "(PRO) " );
?>

<div id="subscriptions-settings" class="settings-area">

	<h1><?php echo esc_html__( 'WooCommerce Subscriptions Settings', 'woo-coupon-usage' ); ?></h1>

  <hr/>

	<p>
	<?php
  $wcusage_field_subscriptions_enable_renewals = wcusage_get_setting_value('wcusage_field_subscriptions_enable_renewals', '1');
  $checked2 = ( $wcusage_field_subscriptions_enable_renewals == '1' ? ' checked="checked"' : '' );
  ?>
<label class="switch">
		<input type="hidden" value="0" <?php if ( wcu_fs()->can_use_premium_code() ) { ?>id="wcusage_field_subscriptions_enable_renewals" data-custom="custom" name="wcusage_options[wcusage_field_subscriptions_enable_renewals]"<?php } ?>>
		<input type="checkbox" value="1" class="wcusage_field_subscriptions_enable_renewals" <?php if ( wcu_fs()->can_use_premium_code() ) { ?>id="wcusage_field_subscriptions_enable_renewals" data-custom="custom" name="wcusage_options[wcusage_field_subscriptions_enable_renewals]"<?php } ?> <?php
  echo esc_html($checked2);
  ?>>
<span class="slider round">
  <span class="on"><span class="fa-solid fa-check"></span></span>
  <span class="off"></span>
</span>
</label>
		<strong><label for="scales"><?php echo esc_html__( 'Display orders and reward commission for WooCommerce subscription renewals', 'woo-coupon-usage' ); ?></label></strong><br/>
		<i><?php echo esc_html__( 'With this enabled, all renewals for subscriptions will also be rewarded commission (if the affiliate referred the customer) and renewal orders displayed on the dashboard.', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'Commission will be earned for every renewal order in the subscription, at the rates you have set.', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'If disabled, only the parent (first) order for the subscription will earn commission.', 'woo-coupon-usage' ); ?></i><br/>
	</p>

  <?php wcusage_setting_toggle('.wcusage_field_subscriptions_enable_renewals', '.wcu-field-section-subscriptions'); // Show or Hide ?>
  <span class="wcu-field-section-subscriptions">

  <br/>

  <span <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>
    <?php wcusage_setting_number_option('wcusage_field_subscriptions_renewals_limit', '0', $probrackets . esc_html__( 'Recurring Subscription Referral Limit', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This is the limit on how many renewal orders will be rewarded to the affiliate after the initial signup. This does not include the initial order. Set to 0 for unlimited renewals.', 'woo-coupon-usage' ); ?></i><br/>
  </span>

  </span>

  <br/><hr/>

  Looking for more subscription options? <?php if ( wcu_fs()->can_use_premium_code() ) { ?><a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-contact')); ?>"><?php } else { ?><a href="https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0" target="_blank"><?php } ?>Contact us</a> with your suggestions.

</div>

 <?php
}
