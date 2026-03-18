<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_notifications( $args )
{
  $options = get_option( 'wcusage_options' );
  $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
  $probrackets = ( $ispro ? "" : "(PRO) " );
  ?>

	<div id="notification-settings" class="settings-area">

	<h1><?php echo esc_html__( 'Email Notifications', 'woo-coupon-usage' ); ?></h1>

  <hr style="margin-bottom: 35px;"/>

    <h3 class="wcu-setting-email-header">
      <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'General Email Settings', 'woo-coupon-usage' ); ?>:
    </h3>
    <br/>

    <!-- General Email Settings & Free Email Settings -->
    <?php do_action('wcusage_hook_setting_section_email_free'); ?>

    <!-- Cancelled Order Email -->
    <div class="wcu-setting-email-notification-box">

      <span id="wcu-setting-email-notification-new-usage">
      <?php wcusage_setting_toggle_option('wcusage_field_cancel_email_enable', 0, esc_html__( 'Referred Order Cancelled', 'woo-coupon-usage' ), '0px'); ?>
      </span>

      <i><?php echo esc_html__( 'Send an email to affiliates if order they referred has been cancelled, refunded, or failed. Only when status was previously "completed".', 'woo-coupon-usage' ); ?></i>

      <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

      <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_cancel_email_customise_1">Show <span class="fa-solid fa-arrow-down"></span></button></p>

      <?php echo wcu_admin_settings_showhide_toggle("wcu_show_cancel_email_customise_1", "wcu_cancel_email_customise_1", "Show", "Hide"); ?>
      <div id="wcu_cancel_email_customise_1" style="display: none;">

        <br/>

        <!-- Email Notification Subject -->
        <?php wcusage_setting_text_option('wcusage_field_cancel_email_subject', esc_html__( "Your referred order #{id} has been cancelled.", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <?php
        $email2message = "Hi {name},\r\n<br/>\r\nWe're sorry to inform you that one of your referred orders has been {status}.\r\n<br/>\r\nOrder ID: {id}\r\n<br/>\r\nThe following commission has been removed from your account: {commission}\r\n<br/>\r\nThank you for your continued support.";
        wcusage_setting_tinymce_option('wcusage_field_cancel_email_message', $email2message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
        ?>

        <br/>

        <?php echo wcusage_email_merge_tags(array("name", "coupon", "commission", "id", "status")); ?>

      </div>

    </div>

    <!-- PRO Email Settings -->
    <div <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

      <?php wcusage_setting_toggle_option('wcusage_field_email_enable_extra', 1, $probrackets . esc_html__( 'Enable the "Additional Email Addresses" field in affiliate settings.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'This will allow them to add extra emails to send their email notifications to.', 'woo-coupon-usage' ); ?></i>

      <br/><br/><br/>

    </div>

    <!-- Registration Email Settings -->
    <?php do_action('wcusage_hook_setting_section_email_registration'); ?>

    <!-- PRO Email Settings -->
    <div <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

    <br/>
    <h3 class="wcu-setting-email-header">
      <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Payouts Emails', 'woo-coupon-usage' ); ?>:
    </h3>
    <br/>

    <div class="wcu-setting-email-notification-box">

      <!--
      ********************
      ** [Admin Email] New Payout
      ********************
      -->
      <?php wcusage_setting_toggle_option('wcusage_field_email_payout_admin_enable', 1, esc_html__( 'New Payout Request', 'woo-coupon-usage' ), '0px'); ?>

      <i><?php echo esc_html__( 'Send an email to admin when there is a new payout request for unpaid commission.', 'woo-coupon-usage' ); ?></i>

      <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Administrator', 'woo-coupon-usage' ); ?></p>

      <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_payout_admin_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

      <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_payout_admin_customise", "wcu_email_payout_admin_customise", "Show", "Hide"); ?>
      <div id="wcu_email_payout_admin_customise" style="display: none;">

        <br/>

        <!-- Email Notification Subject -->
        <?php wcusage_setting_text_option('wcusage_field_email_payout_admin_subject', esc_html__( "New Payout Request: {coupon}", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <!-- Email Notification Message -->
        <?php
        $email4messagepayout = "You have received a new payout request from an affiliate."
        . "<br/><br/>Username: {username}"
        . "<br/><br/>Coupon code: {coupon}"
        . "<br/><br/>Amount: " . get_woocommerce_currency_symbol() . "{amount}"
        . "<br/><br/>You can manage this payout here: {adminpayoutsurl}";
        wcusage_setting_tinymce_option('wcusage_field_email_payout_admin_message', $email4messagepayout, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
        ?>

        <br/>

        <?php echo wcusage_email_merge_tags(array("username", "coupon", "adminpayoutsurl", "amount")); ?>

      </div>

    </div>

    <div class="wcu-setting-email-notification-box">

      <!--
      ********************
      ** [Admin Email] New Payout Request (Bulk Scheduled)
      ********************
      -->
      <?php wcusage_setting_toggle_option('wcusage_field_email_payout_admin_enable', 1, esc_html__( 'New Payout Request (Bulk Scheduled)', 'woo-coupon-usage' ), '0px'); ?>

      <i><?php echo esc_html__( 'Send an email to admin when there are 1 or more "scheduled" payout requests (this will be sent instead of multiple individual emails).', 'woo-coupon-usage' ); ?></i>

      <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Administrator', 'woo-coupon-usage' ); ?></p>

      <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_payout_admin_bulk_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

      <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_payout_admin_bulk_customise", "wcu_email_payout_admin_bulk_customise", "Show", "Hide"); ?>
      <div id="wcu_email_payout_admin_bulk_customise" style="display: none;">

        <br/>

        <!-- Email Notification Subject -->
        <?php wcusage_setting_text_option('wcusage_field_email_payout_admin_bulk_subject', esc_html__( "{number} New Payout Requests", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <!-- Email Notification Message -->
        <?php
        $email4messagepayoutbulk = "{number} new commission payouts have been automatically requested:"
        . "<br/><br/>{payoutslist}"
        . "<br/><br/>You can manage these payouts here: {adminpayoutsurl}";
        wcusage_setting_tinymce_option('wcusage_field_email_payout_admin_bulk_message', $email4messagepayoutbulk, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
        ?>

        <br/>

        <?php echo wcusage_email_merge_tags(array("number", "payoutslist", "adminpayoutsurl")); ?>

      </div>

    </div>

    <div class="wcu-setting-email-notification-box">

      <!--
      ********************
      ** [User Email] New Commission Payout
      ********************
      -->
      <?php wcusage_setting_toggle_option('wcusage_field_email_payout_affiliate_enable', 1, esc_html__( 'New Commission Payout', 'woo-coupon-usage' ), '0px'); ?>

      <i><?php echo esc_html__( 'Send an email to affiliates when a payout request is successfully marked as paid.', 'woo-coupon-usage' ); ?></i>

      <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

      <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_payout_affiliate_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

      <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_payout_affiliate_customise", "wcu_email_payout_affiliate_customise", "Show", "Hide"); ?>
      <div id="wcu_email_payout_affiliate_customise" style="display: none;">

        <br/>

        <!-- Email Notification Subject -->
        <?php wcusage_setting_text_option('wcusage_field_email_payout_affiliate_subject', esc_html__( "New Commission Payout!", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <!-- Email Notification Message -->
        <?php
        $payoutcurrency = get_option('woocommerce_currency');
        $email5messagepayout = "Hello {name},"
        . "<br/><br/>Your latest payout request #{id} has now been successfully paid."
        . "<br/><br/>Coupon code: {coupon}"
        . "<br/><br/>Amount: " . get_woocommerce_currency_symbol() . "{amount}"
        . "<br/><br/>Payment method: {method}";
        wcusage_setting_tinymce_option('wcusage_field_email_payout_affiliate_message', $email5messagepayout, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
        ?>

        <br/>

        <?php echo wcusage_email_merge_tags(array("username", "coupon", "amount", "method", "name")); ?>

      </div>

    </div>

    <?php wcusage_setting_toggle('.wcusage_field_enable_directlinks', '.wcu-field-section-directlinks'); // Show or Hide ?>
    <span class="wcu-field-section-directlinks">

        <br/>
        <h3 class="wcu-setting-email-header">
          <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Direct Link Tracking Emails', 'woo-coupon-usage' ); ?>:
        </h3>
        <br/>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [Admin Email] New Domain Request
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_direct_link_admin_enable', 1, esc_html__( 'New "Direct Link Tracking" Domain', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to admin when a new domain is added by affiliate for direct link tracking.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Administrator', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_direct_link_admin_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_direct_link_admin_customise", "wcu_email_direct_link_admin_customise", "Show", "Hide"); ?>
          <div id="wcu_email_direct_link_admin_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_direct_link_admin_subject', esc_html__( "New Domain Request (Direct Link Tracking)", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email6message = "You have received a new domain request for direct link tracking."
            . "<br/><br/>Coupon code: {coupon}"
            . "<br/><br/>Domain: {domain}"
            . "<br/><br/>You can approve or decline this domain here: {adminurl}";
            wcusage_setting_tinymce_option('wcusage_field_email_direct_link_admin_message', $email6message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("coupon", "domain", "adminurl")); ?>

          </div>

        </div>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [Admin Email] New Domain Request
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_direct_link_accept_enable', 1, esc_html__( 'Domain Accepted', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to affiliate users when their domain is accepted for Direct Link Tracking.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_direct_link_accept_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_direct_link_accept_customise", "wcu_email_direct_link_accept_customise", "Show", "Hide"); ?>
          <div id="wcu_email_direct_link_accept_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_direct_link_accept_subject', esc_html__( "Domain Accepted: {domain}", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email7message = "Hello {name},"
            . "<br/><br/>Your domain has been accepted for direct link tracking."
            . "<br/><br/>Coupon code: {coupon}"
            . "<br/><br/>Domain: {domain}"
            . "<br/><br/>You can now link directly to our website on this domain, and it will work in the same way as a referral URL."
            . "<br/><br/>"
            . "{dashboardurl}";
            wcusage_setting_tinymce_option('wcusage_field_email_direct_link_accept_message', $email7message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("coupon", "domain", "name", "username", "dashboardurl")); ?>

          </div>

        </div>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [User Email] Domain Request Declined
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_direct_link_decline_enable', 1, esc_html__( 'Domain Declined', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to affiliate users when their domain is declined for Direct Link Tracking.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_direct_link_decline_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_direct_link_decline_customise", "wcu_email_direct_link_decline_customise", "Show", "Hide"); ?>
          <div id="wcu_email_direct_link_decline_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_direct_link_decline_subject', esc_html__( "Domain Declined: {domain}", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email7message = "Hello {name},"
            . "<br/><br/>Sorry, your domain has been declined for direct link tracking."
            . "<br/><br/>Coupon code: {coupon}"
            . "<br/><br/>Domain: {domain}";
            wcusage_setting_tinymce_option('wcusage_field_email_direct_link_decline_message', $email7message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("coupon", "domain", "name", "username", "dashboardurl")); ?>

          </div>

        </div>

    </span>

    <?php wcusage_setting_toggle('.wcusage_field_mla_enable', '.wcu-field-section-mla-emails'); // Show or Hide ?>
    <span class="wcu-field-section-mla-emails">

        <br/>
        <h3 class="wcu-setting-email-header">
          <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Multi-Level Affiliate Emails', 'woo-coupon-usage' ); ?>:
        </h3>
        <br/>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [User Email] Affiliate Program Invitation
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_mla_invite_enable', 1, esc_html__( 'Affiliate Program Invitation', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email when parent invite enters an email address to send affiliate program invitation.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_mla_invite_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_mla_invite_customise", "wcu_email_mla_invite_customise", "Show", "Hide"); ?>
          <div id="wcu_email_mla_invite_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_mla_invite_subject', esc_html__( "Affiliate Program Invitation", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email51message = "Hello,"
            . "<br/><br/>You have just been invited to join our affiliate program."
            . "<br/><br/>Earn commission on all the sales that you refer to us!"
            . "<br/><br/>Get started by registering here: {inviteurl}";
            wcusage_setting_tinymce_option('wcusage_field_email_mla_invite_message', $email51message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("inviteurl", "inviteurltext")); ?>

          </div>

        </div>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [Parent Email] Affiliate Program Invitation
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_mla_sub_referral_enable', 1, esc_html__( 'New Sub-Affiliate Referral', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to parent affiliate when a sub-affiliate refers a new order (and it is completed).', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Parent Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_mla_sub_referral_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_mla_sub_referral_customise", "wcu_email_mla_sub_referral_customise", "Show", "Hide"); ?>
          <div id="wcu_email_mla_sub_referral_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_mla_sub_referral_subject', esc_html__( "(MLA) New Sub-Affiliate Referral", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email51message = "Hello {name},"
            . "<br/><br/>Congratulations, your sub-affiliate member '{sub-affiliate-user}' has referrered a new sale!"
            . "<br/><br/>You earned a commission share of: {commission}";
            wcusage_setting_tinymce_option('wcusage_field_email_mla_sub_referral_message', $email51message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("name", "sub-affiliate-user", "commission")); ?>

          </div>

        </div>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [Parent Email] Affiliate Program Invitation
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_mla_sub_signup_enable', 1, esc_html__( 'New Sub-Affiliate Signup', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to parent affiliate when a new affiliate signs up in their MLA network.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Parent Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_mla_sub_signup_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_mla_sub_signup_customise", "wcu_email_mla_sub_signup_customise", "Show", "Hide"); ?>
          <div id="wcu_email_mla_sub_signup_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_mla_sub_signup_subject', esc_html__( "New Sub-Affiliate Signup", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email51message = "Hello {name},"
            . "<br/><br/>The user '{sub-affiliate-user}' has just become a tier {sub-affiliate-tier} affiliate in your MLA network!"
            . "<br/><br/>You will earn {sub-affiliate-commission}% commission on all sales they refer to us.";
            wcusage_setting_tinymce_option('wcusage_field_email_mla_sub_signup_message', $email51message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("name", "sub-affiliate-user", "sub-affiliate-tier", "sub-affiliate-commission", "sub-affiliate-commission-fixed")); ?>

          </div>

        </div>

        <div class="wcu-setting-email-notification-box">

          <!--
          ********************
          ** [Parent Email] New Sub-Affiliate Registration
          ********************
          -->
          <?php wcusage_setting_toggle_option('wcusage_field_email_mla_sub_reg_notify_enable', 1, esc_html__( 'New Sub-Affiliate Registration', 'woo-coupon-usage' ), '0px'); ?>

          <i><?php echo esc_html__( 'Send an email to the MLA parent affiliate when a new sub-affiliate registers via their invite link, including the application details and a link to approve or decline from their MLA dashboard.', 'woo-coupon-usage' ); ?></i>

          <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Parent Affiliate User', 'woo-coupon-usage' ); ?></p>

          <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_mla_sub_reg_notify_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

          <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_mla_sub_reg_notify_customise", "wcu_email_mla_sub_reg_notify_customise", "Show", "Hide"); ?>
          <div id="wcu_email_mla_sub_reg_notify_customise" style="display: none;">

            <br/>

            <!-- Email Notification Subject -->
            <?php wcusage_setting_text_option('wcusage_field_email_mla_sub_reg_notify_subject', esc_html__( "New Sub-Affiliate Registration: {sub-affiliate-user}", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Email Notification Message -->
            <?php
            $email_mla_sub_reg_notify_message = "Hello {name},"
            . "<br/><br/>A new affiliate has registered via your invite link and is pending your approval:"
            . "<br/><br/><strong>Username:</strong> {username}"
            . "<br/><strong>Name:</strong> {sub-affiliate-name}"
            . "<br/><strong>Email:</strong> {sub-affiliate-email}"
            . "<br/><strong>Coupon:</strong> {coupon}"
            . "<br/><strong>Website:</strong> {website}"
            . "<br/><strong>Referrer:</strong> {referrer}"
            . "<br/><strong>Promote:</strong> {promote}"
            . "<br/>{custom-fields}"
            . "<br/><br/>Review and approve or decline this registration from your MLA dashboard:"
            . "<br/><br/>{dashboardurl}";
            wcusage_setting_tinymce_option('wcusage_field_email_mla_sub_reg_notify_message', $email_mla_sub_reg_notify_message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
            ?>

            <br/>

            <?php echo wcusage_email_merge_tags(array("name", "username", "sub-affiliate-name", "sub-affiliate-email", "coupon", "website", "referrer", "promote", "custom-fields", "dashboardurl")); ?>

          </div>

        </div>

    </span>

    </div>

	</div>

 <?php
}

/**
 * Settings Section: Email FREE
 *
 */
add_action( 'wcusage_hook_setting_section_email_free', 'wcusage_setting_section_email_free', 10, 1 );
if( !function_exists( 'wcusage_setting_sectio_email_free' ) ) {
  function wcusage_setting_section_email_free($type = "") {

  $options = get_option( 'wcusage_options' );

  if(isset($_SERVER['SERVER_NAME'])) {
    $admin_email = "admin@" . $_SERVER['SERVER_NAME'];
  } else {
    $admin_email = get_bloginfo( 'admin_email' );
  }
  ?>

    <!-- From Email Address -->
    <?php wcusage_setting_text_option('wcusage_field_from_email', $admin_email, esc_html__( 'From Email Address:', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( '(If you are using a mail SMTP plugin, the from email may be overridden.)', 'woo-coupon-usage' ); ?></i><br/>

    <br/>

    <!-- From Name -->
    <?php wcusage_setting_text_option('wcusage_field_from_name', get_bloginfo( 'name' ), esc_html__( 'From Name:', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( '(If you are using a mail SMTP plugin, the from name may be overridden.)', 'woo-coupon-usage' ); ?></i><br/>
    
    <br/>

    <!-- Admin email address for notifications -->
    <?php wcusage_setting_text_option('wcusage_field_registration_admin_email', get_bloginfo( 'admin_email' ), esc_html__( 'Email address for recieving admin notifications:', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This is the email address that will recieve admin notifications such as new affiliate registrations, and payout notifications.', 'woo-coupon-usage' ); ?></i>

    <br/>

    <span class="setup-hide">

    <br/>

    <!-- Enable New Order Info -->
    <?php wcusage_setting_toggle_option('wcusage_field_new_order_info', 1, esc_html__( 'Enable "Affiliate Information" section in the admin "New Order" email.', 'woo-coupon-usage' ), '0px'); ?>

    </span>

    <br/>
    <hr style="margin-bottom: 35px;"/>

    <h3 class="wcu-setting-email-header">
      <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'New Referral Emails', 'woo-coupon-usage' ); ?>:
    </h3>
    <br/>

    <!--
    ********************
    ** [User Email] New Coupon Usage / Pending Commission Earned
    ********************
    -->
    <div class="wcu-setting-email-notification-box">

      <span id="wcu-setting-email-notification-new-usage">
      <?php wcusage_setting_toggle_option('wcusage_field_email_enable', 1, esc_html__( 'New Order Referral', 'woo-coupon-usage' ), '0px'); ?>
      </span>

      <i><?php echo esc_html__( 'Send an email to affiliates whenever their coupon code is used.', 'woo-coupon-usage' ); ?></i>

      <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

      <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_customise_1">Show <span class="fa-solid fa-arrow-down"></span></button></p>

      <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_customise_1", "wcu_email_customise_1", "Show", "Hide"); ?>
      <div id="wcu_email_customise_1" style="display: none;">

        <br/>
        <!-- Email Notification Order Status -->
        <?php
        $statuses = wc_get_order_statuses();
        $current_email_order_status = isset( $options['wcusage_field_email_order_status'] ) ? $options['wcusage_field_email_order_status'] : 'wc-completed';
        ?>
        <label for="wcusage_field_email_order_status"><strong><?php echo esc_html__( 'Order Status:', 'woo-coupon-usage' ); ?></strong></label><br/>
        <select name="wcusage_field_email_order_status" id="wcusage_field_email_order_status" style="width: 100%; margin-bottom: 10px;">
          <!-- Completed -->
          <option value="wc-completed" <?php selected( $current_email_order_status, 'wc-completed' ); ?>><?php echo esc_html__( 'Completed', 'woo-coupon-usage' ); ?></option>
          <!-- Processing -->
          <option value="wc-processing" <?php selected( $current_email_order_status, 'wc-processing' ); ?>><?php echo esc_html__( 'Processing', 'woo-coupon-usage' ); ?></option>
        </select>

        <br/>

        <!-- Email Notification Subject -->
        <?php wcusage_setting_text_option('wcusage_field_email_subject', esc_html__( "You have made a new referral sale!", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <?php
        $email1message = "Hello {name},\r\n<br/>\r\nCongratulations, you just referred a new order to us, with the coupon code: {coupon}\r\n<br/>\r\nYou have earned {commission} in unpaid commission!\r\n<br/>\r\nHere's a list of items the customer purchased:\r\n<br/>\r\n{listproducts}\r\n<br/>\r\nThank you for your support!\r\n<br>\r\n" . get_bloginfo( 'name' );
        wcusage_setting_tinymce_option('wcusage_field_email_message', $email1message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
        ?>

        <br/>

        <?php echo wcusage_email_merge_tags(array("name", "email", "coupon", "commission", "id", "listproducts")); ?>

      </div>

    </div>

  <?php
  }
}

/**
 * Settings Section: Email Registration
 *
 */
add_action( 'wcusage_hook_setting_section_email_registration', 'wcusage_setting_section_email_registration', 10, 1 );
if( !function_exists( 'wcusage_setting_sectio_email_registration' ) ) {
  function wcusage_setting_section_email_registration($type = "") {

  $options = get_option( 'wcusage_options' );
  ?>

      <h3 class="wcu-setting-email-header">
        <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Registration Emails', 'woo-coupon-usage' ); ?>:
      </h3>
      <br/>

      <div class="wcu-setting-email-notification-box">

        <!--
        ********************
        ** [User Email] Affiliate Application Submitted
        ********************
        -->
        <?php wcusage_setting_toggle_option('wcusage_field_email_registration_enable', 1, esc_html__( 'Affiliate Application Submitted', 'woo-coupon-usage' ), '0px'); ?>

        <i><?php echo esc_html__( 'Send an email to affiliate when they submit the affiliate application form.', 'woo-coupon-usage' ); ?></i>

        <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

        <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_registration_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_registration_customise", "wcu_email_registration_customise", "Show", "Hide"); ?>
        <div id="wcu_email_registration_customise" style="display: none;">

          <br/>

          <!-- Email Notification Subject -->
          <?php wcusage_setting_text_option('wcusage_field_email_registration_subject', esc_html__( "Affiliate Application Submitted", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Email Notification Message -->
          <?php
          $email2message = "Hello {name},"
          . "<br/><br/>"
          . "Your affiliate application for the coupon code"
          . " '{coupon}' "
          . "has been submitted."
          . "<br/><br/>"
          . "We will review your application and get back to you soon.";
          wcusage_setting_tinymce_option('wcusage_field_email_registration_message', $email2message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
          ?>

          <br/>

          <?php echo wcusage_email_merge_tags(array("name", "email", "coupon")); ?>

        </div>

      </div>

      <div class="wcu-setting-email-notification-box">

        <!--
        ********************
        ** [User Email] New Affiliate Account Created
        ********************
        -->
        <?php wcusage_setting_toggle_option('wcusage_field_email_registration_new_enable', 1, esc_html__( 'New Affiliate Account Created', 'woo-coupon-usage' ), '0px'); ?>

        <i><?php echo esc_html__( 'Send a custom new user account email (replaces default registration email).', 'woo-coupon-usage' ); ?></i>

        <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

        <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_registration_new_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_registration_new_customise", "wcu_email_registration_new_customise", "Show", "Hide"); ?>
        <div id="wcu_email_registration_new_customise" style="display: none;">

          <br/>

          <!-- Email Notification Subject -->
          <?php wcusage_setting_text_option('wcusage_field_email_registration_new_subject', esc_html__( "Affiliate Account Login Details", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Email Notification Message -->
          <?php
          $email3message = "Hello {name},"
          . "<br/><br/>"
          . "Your new affiliate account has been created."
          . "<br/><br/>"
          . "Username: {username}"
          . "<br/><br/>"
          . '<a href="{passwordurl}">Click here to set your password.</a>'
          . "<br/><br/>"
          . "You can login and access the affiliate dashboard page here: "
          . "<br/>"
          . "{dashboardurl}";
          wcusage_setting_tinymce_option('wcusage_field_email_registration_new_message', $email3message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
          ?>

          <br/>

          <?php echo wcusage_email_merge_tags(array("name", "email", "coupon", "dashboardurl", "username", "passwordurl")); ?>

        </div>

      </div>

      <div class="wcu-setting-email-notification-box">

        <!--
        ********************
        ** [Admin Email] New Affiliate Application
        ********************
        -->
        <?php wcusage_setting_toggle_option('wcusage_field_email_registration_admin_enable', 1, esc_html__( 'New Affiliate Application', 'woo-coupon-usage' ), '0px'); ?>

        <i><?php echo esc_html__( 'Send an email to admin when there is a new affiliate application.', 'woo-coupon-usage' ); ?></i>

        <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Administrator', 'woo-coupon-usage' ); ?></p>

        <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_registration_admin_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_registration_admin_customise", "wcu_email_registration_admin_customise", "Show", "Hide"); ?>
        <div id="wcu_email_registration_admin_customise" style="display: none;">

          <br/>

          <!-- Email Notification Subject -->
          <?php wcusage_setting_text_option('wcusage_field_email_registration_admin_subject', esc_html__( "New Affiliate Application", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Email Notification Message -->
          <?php
          $email4message = "You have received a new coupon affiliate application!"
          . "<br/><br/>Username: {username}"
          . "<br/><br/>Preferred coupon code: {coupon}"
          . "<br/><br/>{custom-fields}"
          . "<br/><br/>You can approve or decline this application here: {adminurl}";
          wcusage_setting_tinymce_option('wcusage_field_email_registration_admin_message', $email4message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
          ?>

          <br/>

          <?php echo wcusage_email_merge_tags(array("username", "name", "email", "coupon", "adminurl", "custom-fields", "website", "promote", "referrer")); ?>

        </div>

      </div>

      <div class="wcu-setting-email-notification-box">

        <!--
        ********************
        ** [User Email] Affiliate Application Accepted
        ********************
        -->
        <?php wcusage_setting_toggle_option('wcusage_field_email_registration_accept_enable', 1, esc_html__( 'Affiliate Application Accepted', 'woo-coupon-usage' ), '0px'); ?>

        <i><?php echo esc_html__( 'Send an email to affiliate when their affiliate application is accepted.', 'woo-coupon-usage' ); ?></i>

        <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

        <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_registration_accept_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_registration_accept_customise", "wcu_email_registration_accept_customise", "Show", "Hide"); ?>
        <div id="wcu_email_registration_accept_customise" style="display: none;">

          <br/>

          <!-- Email Notification Subject -->
          <?php wcusage_setting_text_option('wcusage_field_email_registration_accept_subject', esc_html__( "Affiliate Application Accepted!", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Email Notification Message -->
          <?php
          $email5message = "Your affiliate application has been accepted for the coupon code: {coupon}"
          . "<br/><br/>Get started by visiting the affiliate dashboard here: {dashboardurl}"
          . "<br/><br/>Your default referral link is: {referralurl}"
          . "<br/><br/>You can also use the affiliate dashboard to generate referral links for specific pages and campaigns."
          . "<br/><br/>{message}";
          wcusage_setting_tinymce_option('wcusage_field_email_registration_accept_message', $email5message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
          ?>

          <br/>

          <?php echo wcusage_email_merge_tags(array("coupon", "dashboardurl", "referralurl", "message", "username", "name")); ?>

        </div>

      </div>

      <div class="wcu-setting-email-notification-box setup-hide">

        <!--
        ********************
        ** [User Email] Affiliate Application declined
        ********************
        -->
        <?php wcusage_setting_toggle_option('wcusage_field_email_registration_decline_enable', 1, esc_html__( 'Affiliate Application declined', 'woo-coupon-usage' ), '0px'); ?>

        <i><?php echo esc_html__( 'Send an email to affiliate when their affiliate application is declined.', 'woo-coupon-usage' ); ?></i>

        <br/><br/><p><span class="fa-solid fa-circle-user"></span> <strong><?php echo esc_html__( 'Recipient', 'woo-coupon-usage' ); ?>:</strong> <?php echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ); ?></p>

        <br/><p><span class="fa-solid fa-envelope-open-text"></span> <strong><?php echo esc_html__( 'Email Customizer', 'woo-coupon-usage' ); ?>:</strong> <button type="button" class="wcu-showhide-button" id="wcu_show_email_registration_decline_customise">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_email_registration_decline_customise", "wcu_email_registration_decline_customise", "Show", "Hide"); ?>
        <div id="wcu_email_registration_decline_customise" style="display: none;">

          <br/>

          <!-- Email Notification Subject -->
          <?php wcusage_setting_text_option('wcusage_field_email_registration_decline_subject', esc_html__( "Affiliate Application Declined", "woo-coupon-usage" ), esc_html__( 'Email Notification Subject', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Email Notification Message -->
          <?php
          $email6message = "Sorry, your affiliate application has been declined for the coupon code: {coupon}"
          . "<br/><br/>Please feel free to submit another application for a different coupon code, or contact us if you have any questions."
          . "<br/><br/>{message}";
          wcusage_setting_tinymce_option('wcusage_field_email_registration_decline_message', $email6message, esc_html__( 'Email Notification Message', 'woo-coupon-usage' ), '0px');
          ?>

          <br/>

          <?php echo wcusage_email_merge_tags(array("coupon", "message")); ?>

        </div>

      </div>

  <?php
  }
}

/**
 * Gets the merge tags for email notifications
 *
 */
if( !function_exists( 'wcusage_email_merge_tags' ) ) {
  function wcusage_email_merge_tags($array) {
    ?>

    <p><strong><?php echo esc_html__( 'Supported merge tags', 'woo-coupon-usage' ); ?>:</strong></p>

    <?php
    foreach ($array as &$i) {

      switch ($i) {

          case "name":
              echo "<p>- <strong>{name}</strong> ".esc_html__( 'to show the users display name.', 'woo-coupon-usage' )."</p>";
              break;
          case "email":
              echo "<p>- <strong>{email}</strong> ".esc_html__( 'to show the users email address.', 'woo-coupon-usage' )."</p>";
              break;
          case "coupon":
              echo "<p>- <strong>{coupon}</strong> ".esc_html__( 'to show the coupon code.', 'woo-coupon-usage' )."</p>";
              break;
          case "commission":
              echo "<p>- <strong>{commission}</strong> ".esc_html__( 'to show the users commission earned on that order.', 'woo-coupon-usage' )."</p>";
              break;
          case "id":
              echo "<p>- <strong>{id}</strong> ".esc_html__( 'to show the order ID.', 'woo-coupon-usage' )."</p>";
              break;
          case "listproducts":
              echo "<p>- <strong>{listproducts}</strong> ".esc_html__( 'to show a list of the products purchased (and quantities).', 'woo-coupon-usage' )."</p>";
              break;
          case "username":
              echo "<p>- <strong>{username}</strong> ".esc_html__( 'to show the account username.', 'woo-coupon-usage' )."</p>";
              break;
          case "passwordurl":
            echo "<p>- <strong>{passwordurl}</strong> ".esc_html__( 'to show the password reset URL.', 'woo-coupon-usage' )."</p>";
            break;
          case "dashboardurl":
              echo "<p>- <strong>{dashboardurl}</strong> ".esc_html__( 'to show the affiliate dashboard URL.', 'woo-coupon-usage' )."</p>";
              break;
          case "referralurl":
              echo "<p>- <strong>{referralurl}</strong> ".esc_html__( 'to show the affiliates default referral URL.', 'woo-coupon-usage' )."</p>";
              break;
          case "adminurl":
              echo "<p>- <strong>{adminurl}</strong> ".esc_html__( 'to show the admin URL.', 'woo-coupon-usage' )."</p>";
              break;
          case "message":
              echo "<p>- <strong>{message}</strong> ".esc_html__( 'to show the custom message entered when admins accept/decline affiliate registrations.', 'woo-coupon-usage' )."</p>";
              break;
          case "amount":
              echo "<p>- <strong>{amount}</strong> ".esc_html__( 'to show the amount.', 'woo-coupon-usage' )."</p>";
              break;
          case "adminpayoutsurl":
              echo "<p>- <strong>{adminpayoutsurl}</strong> ".esc_html__( 'to show the admin URL to manage payouts.', 'woo-coupon-usage' )."</p>";
              break;
          case "number":
              echo "<p>- <strong>{number}</strong> ".esc_html__( 'to show the number.', 'woo-coupon-usage' )."</p>";
              break;
          case "payoutslist":
              echo "<p>- <strong>{payoutslist}</strong> ".esc_html__( 'to show a list of all the payouts.', 'woo-coupon-usage' )."</p>";
              break;
          case "method":
              echo "<p>- <strong>{payoutslist}</strong> ".esc_html__( 'to show the payout method.', 'woo-coupon-usage' )."</p>";
              break;
          case "domain":
              echo "<p>- <strong>{domain}</strong> ".esc_html__( 'to show the domain.', 'woo-coupon-usage' )."</p>";
              break;
          case "inviteurl":
              echo "<p>- <strong>{inviteurl}</strong> ".esc_html__( 'to show the invite referral UR (with hyperlink) for the registration form.', 'woo-coupon-usage' )."</p>";
              break;
          case "inviteurltext":
              echo "<p>- <strong>{inviteurltext}</strong> ".esc_html__( 'to show the invite referral URL without hyperlink (to create your own link/button).', 'woo-coupon-usage' )."</p>";
              break;
          case "sub-affiliate-user":
              echo "<p>- <strong>{sub-affiliate-user}</strong> ".esc_html__( 'to show the sub-affiliate username.', 'woo-coupon-usage' )."</p>";
              break;
          case "sub-affiliate-email":
              echo "<p>- <strong>{sub-affiliate-email}</strong> ".esc_html__( 'to show the sub-affiliate email address.', 'woo-coupon-usage' )."</p>";
              break;
          case "sub-affiliate-name":
              echo "<p>- <strong>{sub-affiliate-name}</strong> ".esc_html__( 'to show the sub-affiliate display name.', 'woo-coupon-usage' )."</p>";
              break;
          case "custom-fields":
            echo "<p>- <strong>{custom-fields}</strong> ".esc_html__( 'to show the affiliate registration custom field values.', 'woo-coupon-usage' )."</p>";
            break;
          case "website":
            echo "<p>- <strong>{website}</strong> ".esc_html__( 'to show the affiliate registration "Website" field value.', 'woo-coupon-usage' )."</p>";
            break;
          case "promote":
            echo "<p>- <strong>{promote}</strong> ".esc_html__( 'to show the affiliate registration "How will you promote us?" field value.', 'woo-coupon-usage' )."</p>";
            break;
          case "referrer":
            echo "<p>- <strong>{referrer}</strong> ".esc_html__( 'to show the affiliate registration "How did you hear about us?" field value.', 'woo-coupon-usage' )."</p>";
            break;
          case "sub-affiliate-commission":
            echo "<p>- <strong>{sub-affiliate-commission}</strong> ".esc_html__( 'to show the percentage MLA commission earned.', 'woo-coupon-usage' )."</p>";
            break;
          case "sub-affiliate-commission-fixed":
            echo "<p>- <strong>{sub-affiliate-commission-fixed}</strong> ".esc_html__( 'to show the fixed MLA commission earned.', 'woo-coupon-usage' )."</p>";
            break;
          case "status":
            echo "<p>- <strong>{status}</strong> ".esc_html__( 'to show the order status.', 'woo-coupon-usage' )."</p>";
            break;

      }

    }

  }
}
