<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_payouts( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

	<div id="payouts-settings" class="settings-area<?php
    if ( !wcu_fs()->can_use_premium_code() ) {
        ?> premium-only-settings" title="Available with Pro version." style="pointer-events:none; opacity: 0.6;"<?php
    } else { ?>"<?php } ?>>
    
	<?php if ( !wcu_fs()->can_use_premium_code() ) { ?><p><strong style="color: green;"><?php echo esc_html__( 'Available with Pro version.', 'woo-coupon-usage' ); ?></strong></p><?php } ?>

	<h1><?php echo esc_html__( 'Commission Payouts Features', 'woo-coupon-usage' ); ?> (Pro)</h1>

  <hr/>

    <!-- Enable Payouts Features -->
    <?php wcusage_setting_toggle_option('wcusage_field_tracking_enable', 1, esc_html__( 'Enable Payouts Features', 'woo-coupon-usage' ), '0px'); ?>

    <i><?php echo esc_html__( 'This will enable payouts features, and keep track of "unpaid commission" for each coupon, whenever new orders are created using that coupon.', 'woo-coupon-usage' ); ?></i><br/>


    <?php wcusage_setting_toggle('.wcusage_field_tracking_enable', '.wcu-field-section-payouts-features'); // Show or Hide ?>
    <span class="wcu-field-section-payouts-features">

  		<br/>

      <script>
      jQuery( document ).ready(function() {
        jQuery(".wcusage_field_tracking_enable").on('change', function() {
          if( !jQuery(".wcusage_field_tracking_enable").is(':checked') ) {
            jQuery(".wcusage_field_payouts_enable").attr('checked', false);
            jQuery(".wcusage_field_payouts_enable").change();
          }
        });
      });
      </script>
      <!-- Enable Payout Requests & Log Features -->
      <?php wcusage_setting_toggle_option('wcusage_field_payouts_enable', 1, esc_html__( 'Enable Payout Requests & Log Features', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'This will show a "Payouts" tab on the coupon usage/info page, so the affiliate can view their unpaid commission, and request payouts.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'For this to show, a user/affiliate account must be assigned to that coupon. The tab is only shown to this user.', 'woo-coupon-usage' ); ?></i><br/>

  		<br/><br/>

      <?php if ( wcu_fs()->can_use_premium_code() ) { ?>
      <!-- FAQ: How to payouts work? -->
      <div class="wcu-admin-faq">

        <?php echo wcusage_admin_faq_toggle(
        "wcu_show_section_qna_manage_payouts",
        "wcu_qna_manage_payouts",
        "FAQ: How do the commission payouts work?");
        ?>

        <div class="wcu-admin-faq-content wcu_qna_manage_payouts" id="wcu_qna_manage_payouts" style="display: none;">

          <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If an order is created using an affiliates coupon, then marked as completed, the commission will be added to the affiliate account as "unpaid commission".', 'woo-coupon-usage' ); ?><br/>
          
          <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The affiliate can then request a payout for this commission in their affiliate dashboard, which will notify you. This can then be paid in the admin "Payouts" page.', 'woo-coupon-usage' ); ?><br/>

          <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'There are also a variety of options available below to automate payout requests/payments, select your payout methods, and much more!', 'woo-coupon-usage' ); ?><br/>

          <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If an order is refunded then the commission will be removed from the users account.', 'woo-coupon-usage' ); ?><br/>

          <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Note: "Unpaid Commission" will start from "0" and will only start tracking after you installed the "PRO" version and activated the payouts functionality.', 'woo-coupon-usage' ); ?><br/>

          <a href="https://couponaffiliates.com/docs/pro-payouts" target="_blank" class="button button-primary" style="margin-top: 10px;"><?php echo esc_html__( 'View Documentation', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span></a>

          <br/><br/>
          
          <strong><?php echo esc_html__( 'For more information, watch the video:', 'woo-coupon-usage' ); ?></strong>
          <br/>
          <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/837140385?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>

        </div>

      </div>
      <?php } ?>

      <hr/>

      <h3 id ="wcu-setting-header-payouts-general"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Payouts Settings:</h3>

      <?php wcusage_setting_toggle_option('wcusage_field_payout_request_button', 1, esc_html__( 'Show "Request Payout" button on affiliate dashboard.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'When enabled, the "Request Payout" button will be shown on the affiliate dashboard, allowing affiliates to manually request payouts.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'Turn this off if you want to show all their payout details on the payouts tab, but payouts requests themselves will all be handled by admins (or scheduled).', 'woo-coupon-usage' ); ?></i><br/>
      
      <br/>

      <?php wcusage_setting_toggle_option('wcusage_field_payout_details_required', 1, esc_html__( 'Require payment details to request payout.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'When enabled, the affiliate will be required to enter their payment details before they can request a payout.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <?php wcusage_setting_toggle_option('wcusage_field_payout_custom_amount', 1, esc_html__( 'Allow affiliates to enter a custom payout amount.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'When enabled, the affiliate can enter a custom amount for their payout request. The minimum amount will still be the payment threshold, and maximum amount is their total available unpaid commission.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- How much unpaid commission must be earned before the affiliate can request a payout. -->
      <?php wcusage_setting_number_option('wcusage_field_payout_threshold', '0', esc_html__( 'Payment Threshold', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'How much "unpaid commission" must be earned/available, before the affiliate can request a payout.', 'woo-coupon-usage' ); ?></i>

  		<br/><br/>

      <!-- Number of days after order "completion" until commission is earned: -->
      <?php wcusage_setting_number_option('wcusage_field_payout_days', '0', esc_html__( 'Delay Commission (Number of Days)', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'The number of days after an order is created, that the commission earned is added to the users account as "unpaid commission". Useful if you want to prevent commission being paid out early for orders that may be refunded etc.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'If set to "0" then commission will be added to the affiliates account instantly when an order is completed.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'Requires cron jobs to be enabled on your site. Make sure to test this is working after activating. We highly recommend using "Real Cron Jobs" instead of WP Cron.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/real-cron-job/"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a></i><br/>

      <br/><hr/>

       <h3 id="wcu-setting-header-payouts-scheduled"><span class="dashicons dashicons-admin-generic"
       style="margin-top: 2px;"></span> <?php echo esc_html__( 'Unpaid Commission', 'woo-coupon-usage' ); ?>:</h3>

      <p><?php echo esc_html__( 'Unpaid Commission is the total commission amount that has been earned by affiliates, but not yet paid out.', 'woo-coupon-usage' ); ?></p>

      <p><?php echo esc_html__( 'This commission earned once an order is marked as completed (or other selected statuses below), and is available for affiliates to request payouts for.', 'woo-coupon-usage' ); ?></p>

      <br/>

      <span class="wcu-field-payout-statuses-one">

      <!-- DROPDOWN - Order Status Type Field -->
      <p>
  		<strong><label for="scales"><?php echo esc_html__( 'Order status for "unpaid commission" to be granted:', 'woo-coupon-usage' ); ?></label></strong><br/>
        <select name="wcusage_options[wcusage_payout_status]" id="wcusage_payout_status" class="wcusage_payout_status">
        <?php
        $wcusage_payout_status = wcusage_get_setting_value('wcusage_payout_status', 'wc-completed');
        $orderstatuses = wc_get_order_statuses();
        foreach( $orderstatuses as $key => $status ){
          if( wc_get_order_status_name($wcusage_payout_status) == wc_get_order_status_name($status) ) {
            $checkedx = "selected";
          } else {
            $checkedx = "";
          }
          if( ($key != "wc-pending" && $key != "wc-processing" && $key != "wc-on-hold" && $key != "wc-cancelled" && $key != "wc-refunded" && $key != "wc-checkout-draft" && $key != "wc-failed") || $checkedx) {
            echo '<option value="'.esc_attr($key).'" '.esc_attr($checkedx).'>'.wc_get_order_status_name($status).'</span>';
          }
        }
        ?>
        </select>
        <br/><i><?php echo esc_html__( 'The order status required for "unpaid commission" to be granted. Default "completed" for most sites. This should be the final status for your orders, once it has been paid and delivered.', 'woo-coupon-usage' ); ?></i>
  	   </p>

      </span>
      <script>
      // If wcusage_field_payout_status_multiple is enabled, hide .wcu-field-payout-statuses-one on load and change
      jQuery( document ).ready(function() {
        if(jQuery('.wcusage_field_payout_status_multiple').is(':checked')) {
          jQuery('.wcu-field-payout-statuses-one').hide();
        }
        jQuery('.wcusage_field_payout_status_multiple').on('change', function() {
          if(jQuery('.wcusage_field_payout_status_multiple').is(':checked')) {
            jQuery('.wcu-field-payout-statuses-one').hide();
          } else {
            jQuery('.wcu-field-payout-statuses-one').show();
          }
        });
      });
      </script>

      <?php
      if( function_exists('wc_get_order_statuses') ) {
        $orderstatuses = wc_get_order_statuses();
        // Remove unwanted statuses
        unset($orderstatuses['wc-pending']);
        unset($orderstatuses['wc-processing']);
        unset($orderstatuses['wc-on-hold']);
        unset($orderstatuses['wc-cancelled']);
        unset($orderstatuses['wc-refunded']);
        unset($orderstatuses['wc-failed']);
        unset($orderstatuses['wc-checkout-draft']);
      } else {
        $orderstatuses = array(
          'wc-completed'  => esc_html__( 'Completed', 'woo-coupon-usage' ),
        );
      }
      ?>

      <?php
      // If more than one order status is in the array
      $wcusage_field_payout_status_multiple = wcusage_get_setting_value('wcusage_field_payout_status_multiple', 0);
      if( count($orderstatuses) > 1 || $wcusage_field_payout_status_multiple ) {
      ?>

      <br/>

       <?php wcusage_setting_toggle_option('wcusage_field_payout_status_multiple', 0, esc_html__( 'Advanced: Enable multiple order statuses.', 'woo-coupon-usage' ), '0px'); ?>
       <i><?php echo esc_html__( 'Only enable this if you have multiple different "final" order statuses that should grant "unpaid commission".', 'woo-coupon-usage' ); ?></i><br/>

       <?php wcusage_setting_toggle('.wcusage_field_payout_status_multiple', '.wcu-field-payout-statuses'); // Show or Hide ?>
       <span class="wcu-field-payout-statuses" style="display: block; margin-left: 40px;">

       <br/>

      <p>
        <?php echo esc_html__( 'Select multiple CUSTOM order statuses that for "unpaid commission" should be granted for. If you only have one, or do not have any custom statuses, turn the "multiple order statuses" option off.', 'woo-coupon-usage' ); ?><br/>
        <strong style="color: red;">
          <?php echo esc_html__( 'Only select any "final" order statuses for your orders, once it has been paid and delivered. If you select multiple, make sure no orders are applied to more than one of these statuses at any time.', 'woo-coupon-usage' ); ?>
        </strong>
      </p>
        
       <br/>

        <!-- Order Status Type Field -->
        <strong><label for="scales"><?php echo esc_html__( 'Order statuses for "unpaid commission" to be granted:', 'woo-coupon-usage' ); ?></label></strong><br/>

          <?php
          $wcusage_payout_statuses_custom = wcusage_get_setting_value('wcusage_payout_statuses_custom', array());

          $i = 0;
          foreach( $orderstatuses as $key => $status ){

            if($status == "Refunded") {
              if(isset($options['wcusage_payout_statuses_custom'][$key])) {
                $current = $options['wcusage_payout_statuses_custom'][$key];
              }
              if( !isset($current) ) {
                continue;
              }
            }

            $i++;
            if($i == 1) { $thisid = "wcusage_payout_statuses_custom"; }

            $checkedx = "";

            if($wcusage_payout_statuses_custom) {
              if( isset($options['wcusage_payout_statuses_custom'][$key]) ) {
                // Get Current Input Value
                $current = $options['wcusage_payout_statuses_custom'][$key];
                // See if Checked
                if( isset($current) ) {
                  $checkedx = "checked";
                }
              }
            }

            // Force completed to be checked
            if($key == "wc-completed") {
              if(!isset($options['wcusage_payout_statuses_custom']['wc-completed']) || $checkedx) {
                // Only update on non-GET requests
                if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
                  $option_group = get_option('wcusage_options');
                  $option_group['wcusage_payout_statuses_custom']['wc-completed'] = "on";
                  update_option( 'wcusage_options', $option_group );
                }
                $checkedx = "checked";
              }
            }

            // Output Checkbox
            $name = 'wcusage_options[wcusage_payout_statuses_custom]['.$key.']';
            echo '<span style="margin-right: 20px;" id="'.esc_attr($thisid).'">
            <input type="checkbox"
            checktype="multi"
            class="order-status-checkbox-'.esc_attr($key).'"
            checktypekey="'.esc_attr($key).'"
            customid="'.esc_attr($thisid).'"
            name="'.esc_attr($name).'"
            '.esc_attr($checkedx).'> '.esc_attr($status).'</span>';

          }
          ?>

          <br/>

          </span>

        <?php } else { ?>

          <i><?php echo esc_html__( '*You can select multiple statuses if you create more additional custom order statuses. Currently one 1 is available.', 'woo-coupon-usage' ); ?></i>

        <?php } ?>

       <br/><br/><hr/>

       <h3 id="wcu-setting-header-payouts-scheduled"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Processing Commission', 'woo-coupon-usage' ); ?>:</h3>

       <p><?php echo esc_html__( 'Processing Commission allows affiliates to track commission from orders that are not yet completed, but displayed on the dashboard.', 'woo-coupon-usage' ); ?></p>
       
       <p><?php echo esc_html__( 'This is useful for showing withheld commission, which would convert it to "unpaid commission" once the order is completed, which they can then request payouts for.', 'woo-coupon-usage' ); ?></p>

       <p><?php echo esc_html__( 'The "Processing Commission" will be displayed in the "Payouts" tab as a small notice just above the "Unpaid Commission" amount.', 'woo-coupon-usage' ); ?></p>

       <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_payout_pending_enable', 1, esc_html__( 'Enable "Processing Commission" tracking.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'When enabled, "Processing Commission" will be tracked and shown on the payouts tab for statuses that are set to show on the dashboard but not completed yet (not granted unpaid commission).', 'woo-coupon-usage' ); ?></i><br/>
        
       <br/><hr/>

       <h3 id="wcu-setting-header-payouts-scheduled"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Scheduled Payout Requests', 'woo-coupon-usage' ); ?>:</h3>

       <?php wcusage_setting_toggle_option('wcusage_field_enable_payoutschedule', 0, 'Enable Scheduled Payout Requests', '0px'); ?>
       <i><?php echo esc_html__( 'Enable this to automatically submit "payout requests" for your affiliates, every month/week/day, if they meet certain criteria.', 'woo-coupon-usage' ); ?></i><br/>
       <i><?php echo esc_html__( 'This will work in the same way as if the user clicked the "Request Payout" button in their dashboard.', 'woo-coupon-usage' ); ?></i><br/>
       <i><?php echo esc_html__( 'Requires cron jobs to be enabled.', 'woo-coupon-usage' ); ?></i><br/>

        <?php wcusage_setting_toggle('.wcusage_field_enable_payoutschedule', '.wcu-field-section-payoutschedule'); // Show or Hide ?>
        <span class="wcu-field-section-payoutschedule">

         <br/>
         <p><strong><?php echo esc_html__( 'A payout request will only be requested automatically if:', 'woo-coupon-usage' ); ?></strong></p>
         <p>- <?php echo esc_html__( 'The "unpaid commission" meets the required payment threshold.', 'woo-coupon-usage' ); ?></p>
         <p>- <?php echo esc_html__( 'The coupon has an affiliate user assigned to it.', 'woo-coupon-usage' ); ?></p>
         <p>- <?php echo esc_html__( 'The affiliate user has entered their payment details (in the "settings" tab on their dashboard).', 'woo-coupon-usage' ); ?></p>

         <br/>

          <!-- Frequency -->
          <p>
          	<?php $wcusage_field_payoutschedule_freq = wcusage_get_setting_value('wcusage_field_payoutschedule_freq', 'monthly'); ?>
          	<input type="hidden" value="0" id="wcusage_field_payoutschedule_freq" data-custom="custom" name="wcusage_options[wcusage_field_payoutschedule_freq]" >
          	<strong><label for="scales"><?php echo esc_html__( 'How often should payout requests be checked & submitted automatically?', 'woo-coupon-usage' ); ?></label></strong><br/>
          	<select name="wcusage_options[wcusage_field_payoutschedule_freq]" id="wcusage_field_payoutschedule_freq">
              <?php $frequency_options = array('monthly', 'weekly', 'daily', 'quarterly'); ?>
              <?php foreach ($frequency_options as $option) { ?>
                <option value="<?php echo esc_attr($option); ?>" <?php if($wcusage_field_payoutschedule_freq == $option) { ?>selected<?php } ?>><?php echo ucfirst(esc_attr($option)); ?></option>           
              <?php } ?>
              </select>
          </p>
          <i><?php echo esc_html__( 'Payout requests will be scheduled to send on the first day of the selected schedule.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- DateTime -->
          <p <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>
          	<?php $wcusage_field_payoutschedule_time = wcusage_get_setting_value('wcusage_field_payoutschedule_time', '09'); ?>
          	<input type="hidden" value="0" id="wcusage_field_payoutschedule_time" data-custom="custom" name="wcusage_options[wcusage_field_payoutschedule_time]" >
          	<strong><label for="scales"><?php echo esc_html__( 'What time of the day should payouts be requested automatically?', 'woo-coupon-usage' ); ?></label></strong><br/>
          	<select name="wcusage_options[wcusage_field_payoutschedule_time]" id="wcusage_field_payoutschedule_time">
             <?php for ($x = 0; $x <= 24; $x++) { ?>
             <?php if($x < 10) { $x = sprintf("%02d", $x); } ?>
          		<option value="<?php echo esc_attr($x); ?>" <?php if($wcusage_field_payoutschedule_time == $x) { ?>selected<?php } ?>><?php echo esc_attr($x); ?>:00</option>
             <?php } ?>
          	</select>
          </p>
        </span>

        <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_enable_payoutschedule_limit_types', 0, esc_html__( 'Only schedule payouts for specific payout methods.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'If enabled, you can select which payout methods should be used for scheduled payouts.', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <?php wcusage_setting_toggle('.wcusage_field_enable_payoutschedule_limit_types', '.wcu-field-payout-methods-one'); // Show or Hide ?>
        <span class="wcu-field-payout-methods-one">

        <strong><label for="scales"><?php echo esc_html__( 'Select Payout Methods to Schedule:', 'woo-coupon-usage' ); ?></label></strong><br/>

        <i><?php echo esc_html__( 'Select the payout methods that should be scheduled and requested automatically. If an affiliate is using a different payout method to one enabled above, their payouts will not be scheduled.', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <?php
        $wcusage_payoutschedule_methods = wcusage_get_setting_value('wcusage_payoutschedule_methods', array());

        $payoutmethods = array(
          'paypal' => esc_html__( 'Custom #1', 'woo-coupon-usage' ),
          'paypal2' => esc_html__( 'Custom #2', 'woo-coupon-usage' ),
          'banktransfer' => esc_html__( 'Bank Transfer', 'woo-coupon-usage' ),
          'stripeapi' => esc_html__( 'Stripe', 'woo-coupon-usage' ),
          'paypalapi' => esc_html__( 'PayPal', 'woo-coupon-usage' ),
          'wisebank' => esc_html__( 'Wise Bank Transfer', 'woo-coupon-usage' ),
          'credit' => esc_html__( 'Store Credit', 'woo-coupon-usage' ),
        );

        foreach( $payoutmethods as $key => $method ) {

          $checkedx = "";

          if($wcusage_payoutschedule_methods) {
            if( isset($options['wcusage_payoutschedule_methods'][$key]) ) {
              // Get Current Input Value
              $current = $options['wcusage_payoutschedule_methods'][$key];
              // See if Checked
              if( isset($current) ) {
                $checkedx = "checked";
              }
            }
          }

          // ID
          $thisid = "wcusage_payoutschedule_methods";

          // Output Checkbox
          $name = 'wcusage_options[wcusage_payoutschedule_methods]['.$key.']';
          echo '<span style="margin-right: 20px;" id="'.esc_attr($thisid).'">
          <input type="checkbox"
          checktype="multi"
          class="methods-checkbox-'.esc_attr($key).'"
          checktypekey="'.esc_attr($key).'"
          customid="'.esc_attr($thisid).'"
          name="'.esc_attr($name).'"
          '.esc_attr($checkedx).'> '.esc_attr($method).'</span>';

        }
        ?>

        <br/><br/>

        </span>

        <hr/>

        <h3 id="wcu-setting-header-payouts-scheduled"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Automatic Payouts', 'woo-coupon-usage' ); ?>:</h3>

        <?php wcusage_setting_toggle_option('wcusage_payouts_auto_accept', 0, 'Automatically and instantly pay affiliates commission into their account, after a payout request is made.', '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled commission will be paid instantly into the affiliate account automatically, as soon as they request a payout. This will apply to Stripe, PayPal and Store Credit payout methods.', 'woo-coupon-usage' ); ?></i><br/>
        <i><?php echo esc_html__( 'For Wise bank transfer payouts, it will automatically complete the first step of creating the unfunded payment in Wise ready to complete manually.', 'woo-coupon-usage' ); ?></i><br/>
        <i><?php echo esc_html__( 'Warning: If you use this option, you should be even more careful of fraudulent activity. We do recommend reviewing and accepting payouts manually instead, simply so you can make sure each payout is valid and non-fraudulent.', 'woo-coupon-usage' ); ?></i><br/>

        <?php wcusage_setting_toggle('.wcusage_payouts_auto_accept', '.wcu-field-section-auto-payout'); // Show or Hide ?>
        <span class="wcu-field-section-auto-payout">

          <br/>

          <!-- Threshold -->
          <?php wcusage_setting_number_option('wcusage_payouts_auto_accept_threshold', '200', esc_html__( 'Threshold for automatic payouts', 'woo-coupon-usage' ) . ": (" . wcusage_get_currency_symbol() . ")", '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'Set a threshold on the maximum amount that can be paid automatically. Any payout requests above this amount will require manual approval.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Manual First Payout -->
          <?php wcusage_setting_toggle_option('wcusage_payouts_auto_accept_first_manual', 0, 'Require manual approval for affiliates first payout request.', '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'With this enabled, the first ever payout request by an affiliate will require manual approval. After they have at-least 1 completed payout, all future payouts can be paid automatically.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Only enable for specific payout methods -->
          <?php wcusage_setting_toggle_option('wcusage_payouts_auto_accept_specific_methods', 0, 'Only enable automatic payouts for specific payout methods.', '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'If enabled, you can select which payout methods should be eligible for automatic payouts.', 'woo-coupon-usage' ); ?></i><br/>

          <?php wcusage_setting_toggle('.wcusage_payouts_auto_accept_specific_methods', '.wcu-field-auto-payout-methods'); // Show or Hide ?>
          <span class="wcu-field-auto-payout-methods">

            <br/>

            <strong style="margin-left: 40px;"><label for="scales"><?php echo esc_html__( 'Select Payout Methods for Automatic Payouts:', 'woo-coupon-usage' ); ?></label></strong><br/>

            <i style="margin-left: 40px;"><?php echo esc_html__( 'Select the payout methods that should be eligible for automatic payouts. If an affiliate is using a different payout method, their payouts will require manual approval.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <div style="margin-left: 40px;">
            <?php
            $wcusage_payouts_auto_accept_methods = wcusage_get_setting_value('wcusage_payouts_auto_accept_methods', array());

            $auto_payout_methods = array(
              'paypalapi' => esc_html__( 'PayPal Payouts', 'woo-coupon-usage' ),
              'stripeapi' => esc_html__( 'Stripe Payouts', 'woo-coupon-usage' ),
              'wisebank' => esc_html__( 'Wise Bank Transfer', 'woo-coupon-usage' ),
              'credit' => esc_html__( 'Store Credit / Wallet', 'woo-coupon-usage' ),
            );

            foreach( $auto_payout_methods as $key => $method ) {

              $checkedx = "";

              if($wcusage_payouts_auto_accept_methods) {
                if( isset($options['wcusage_payouts_auto_accept_methods'][$key]) ) {
                  // Get Current Input Value
                  $current = $options['wcusage_payouts_auto_accept_methods'][$key];
                  // See if Checked
                  if( isset($current) ) {
                    $checkedx = "checked";
                  }
                }
              }

              // ID
              $thisid = "wcusage_payouts_auto_accept_methods";

              // Output Checkbox
              $name = 'wcusage_options[wcusage_payouts_auto_accept_methods]['.$key.']';
              echo '<span style="margin-right: 20px; display: block; margin-bottom: 5px;" id="'.esc_attr($thisid).'">
              <input type="checkbox"
              checktype="multi"
              class="auto-methods-checkbox-'.esc_attr($key).'"
              checktypekey="'.esc_attr($key).'"
              customid="'.esc_attr($thisid).'"
              name="'.esc_attr($name).'"
              '.esc_attr($checkedx).'> '.esc_attr($method).'</span>';

            }
            ?>
            </div>

          </span>

        </span>

  		<br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Payment Methods:</h3>

      <style>
      .wcu-admin-payouts-headers label {
        font-size: 16px;
      }
      </style>

      <!-- Enable Manual Payment Method #1 -->
      <div style="margin-bottom: 20px;"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_paypal_enable', 0, esc_html__( 'Custom Payment Method', 'woo-coupon-usage' ) . " #1 (Manual)", '0px'); ?>
      </span>
      <i><?php echo esc_html__( 'A custom "manual" payment method of your choice.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_paypal_enable', '.wcu-field-section-tr-payouts-paypal'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-paypal">

        <br/>

        <!-- Change Payment Method Label (Default: "Manual") -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal_only', 'Manual', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Payment Method Info -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
        <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

    		<br/>

        <?php wcusage_setting_toggle_option('wcusage_field_paypal_enable_field', 1, esc_html__( 'Show Payment Details Field', 'woo-coupon-usage' ), '40px'); ?>

        <?php wcusage_setting_toggle('.wcusage_field_paypal_enable_field', '.wcu-field-section-tr-payouts-paypal-field'); // Show or Hide ?>
        <span class="wcu-field-section-tr-payouts-paypal-field">

          <br/>

          <!-- Change Payment Details Label (Default: "Payment Details") -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal', 'Payment Details', esc_html__( 'Payment Details Field Label', 'woo-coupon-usage' ), '40px'); ?>

        </span>

      </span>

      <!-- Enable Manual Payment Method #2 -->
      <div style="margin-bottom: 40px;"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_paypal2_enable', 0, esc_html__( 'Custom Payment Method', 'woo-coupon-usage' ) . " #2 (Manual)", '0px'); ?>
      </span>
      <i><?php echo esc_html__( 'A custom "manual" payment method of your choice.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_paypal2_enable', '.wcu-field-section-tr-payouts-paypal2'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-paypal2">

        <!-- Change Payment Method Label (Default: "Manual") -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal2_only', 'Manual', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Payment Method Info -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal2_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
        <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_paypal2_enable_field', 1, esc_html__( 'Show Payment Details Field', 'woo-coupon-usage' ), '40px'); ?>

        <?php wcusage_setting_toggle('.wcusage_field_paypal2_enable_field', '.wcu-field-section-tr-payouts-paypal2-field'); // Show or Hide ?>
        <span class="wcu-field-section-tr-payouts-paypal2-field">

          <br/>

          <!-- Change Payment Details Label (Default: "Payment Details") -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypal2', 'Payment Details', esc_html__( 'Payment Details Field Label', 'woo-coupon-usage' ), '40px'); ?>

        </span>

      </span>

      <!-- Enable Direct Bank Transfer -->
      <div style="margin-bottom: 40px;"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_banktransfer_enable', 0, esc_html__( 'Direct Bank Transfer (Manual)', 'woo-coupon-usage' ), '0px'); ?>
      </span>
      <i><?php echo esc_html__( 'A direct bank transfer payment method (paid manually).', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_banktransfer_enable', '.wcu-field-section-tr-payouts-banktransfer'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-banktransfer">

        <br/>

        <!-- Change Payment Method Label (Default: "Manual") -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_only', 'Bank Transfer', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Payment Method Info -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
        <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <!-- Change Name Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_name', 'Payee Name', esc_html__( '"Payee Name" Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Change Sort Code Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_sort', 'Sort Code', esc_html__( '"Sort Code" Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Change Account Number Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_account', 'Account Number', esc_html__( '"Account Number" Field Label', 'woo-coupon-usage' ), '40px'); ?>
        
        <br/>

        <!-- Change Account Other Info Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_other', '', esc_html__( 'Extra Field Label', 'woo-coupon-usage' ), '40px'); ?>
        
        <br class="wcusage_field_tr_payouts_banktransfer_other2" style="display: none;"/>

        <!-- Change Account Other Info Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_other2', '', esc_html__( 'Extra Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <br class="wcusage_field_tr_payouts_banktransfer_other3" style="display: none;"/>

        <!-- Change Account Other Info Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_other3', '', esc_html__( 'Extra Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <br class="wcusage_field_tr_payouts_banktransfer_other4" style="display: none;"/>

        <!-- Change Account Other Info Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_banktransfer_other4', '', esc_html__( 'Extra Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <!-- Only show extra fields if previous field is filled -->
        <script>
        jQuery( document ).ready(function() {
          wcusage_check_banktransfer_other_fields();
          jQuery('#wcusage_field_tr_payouts_banktransfer_other, #wcusage_field_tr_payouts_banktransfer_other2, #wcusage_field_tr_payouts_banktransfer_other3').on('change', function() {
            wcusage_check_banktransfer_other_fields();
          });
          function wcusage_check_banktransfer_other_fields() {
            if(jQuery('#wcusage_field_tr_payouts_banktransfer_other').val() != "") {
              jQuery('#wcusage_field_tr_payouts_banktransfer_other2').parent().show();
              jQuery('.wcusage_field_tr_payouts_banktransfer_other2').show();
              if(jQuery('#wcusage_field_tr_payouts_banktransfer_other2').val() != "") {
                jQuery('#wcusage_field_tr_payouts_banktransfer_other3').parent().show();
                jQuery('.wcusage_field_tr_payouts_banktransfer_other3').show();
                if(jQuery('#wcusage_field_tr_payouts_banktransfer_other3').val() != "") {
                  jQuery('#wcusage_field_tr_payouts_banktransfer_other4').parent().show();
                  jQuery('.wcusage_field_tr_payouts_banktransfer_other4').show();
                } else {
                  jQuery('#wcusage_field_tr_payouts_banktransfer_other4').parent().hide();
                  jQuery('.wcusage_field_tr_payouts_banktransfer_other4').hide();
                }
              }
            } else {
              jQuery('#wcusage_field_tr_payouts_banktransfer_other2').parent().hide();
              jQuery('.wcusage_field_tr_payouts_banktransfer_other2').hide();
              jQuery('#wcusage_field_tr_payouts_banktransfer_other3').parent().hide();
              jQuery('.wcusage_field_tr_payouts_banktransfer_other3').hide();
              jQuery('#wcusage_field_tr_payouts_banktransfer_other4').parent().hide();
              jQuery('.wcusage_field_tr_payouts_banktransfer_other4').hide();
            }
          }
        });
        </script>

        <!-- User Role -->
        <?php do_action('wcusage_hook_payouts_user_role_select', 'wcusage_field_tr_payouts_banktransfer_role'); ?>

      </span>

      <!-- Enable PayPal Payouts API -->
      <div style="margin-bottom: 40px;" id="paypalapi-settings"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_paypalapi_enable', 0, esc_html__( 'PayPal Payouts', 'woo-coupon-usage' ), '0px'); ?>
      </span>
      <i><?php echo esc_html__( 'PayPal Payouts payment method will allow you to one-click pay your affiliates directly into their PayPal account.', 'woo-coupon-usage' ); ?>
      <?php echo esc_html__( 'In most cases PayPal Payouts fees are 2%.', 'woo-coupon-usage' ); ?> <a href="https://www.paypal.com/us/webapps/mpp/merchant-fees#paypal-payouts" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</i><br/>
      <i><?php echo esc_html__( 'Prerequisites: To use PayPal Payouts, you will need a PayPal business account and must have access to it’s PayPal Payouts features.', 'woo-coupon-usage' ); ?> <a href="https://developer.paypal.com/docs/payouts/integrate/prerequisites" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</i><br/>
      <i><?php echo esc_html__( 'Note: Payouts can only be made if you have the required funds in your PayPal account.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_paypalapi_enable', '.wcu-field-section-tr-payouts-paypalapi'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-paypalapi">

        <br/>

        <!-- Change Payment Method Label (Default: "Manual") -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypalapi_only', 'PayPal Payouts', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Change Payment Details Label (Default: "Payment Details") -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypalapi', 'PayPal Email Address', esc_html__( 'Payment Details Field Label', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Payment Method Info -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypalapi_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
        <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <p style="margin-left: 40px; font-size: 16px; font-weight: bold;">Payment Email</p>

        <br/>

        <!-- Change PayPal Payment Subject -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypalapi_subject', 'Commission Payout', esc_html__( 'Payment Subject', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Change PayPal Payment Message -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_paypalapi_message', 'Congrats, you have received a new commission payout!', esc_html__( 'Payment Message', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <p style="margin-left: 40px; font-size: 16px; font-weight: bold;">API Credentials*</p>

        <p style="margin-left: 40px;">Instructions: <a href="https://couponaffiliates.com/docs/pro-paypal-payouts-setup/" target="_blank">https://couponaffiliates.com/docs/pro-paypal-payouts-setup</a></p>

        <br/>

        <!-- Change Payment Details Label (Default: "Payment Details") -->
        <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_paypalapi_test', 0, esc_html__( 'Enable Test Mode?', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <script>
        jQuery( document ).ready(function() {
          wcusage_check_paypal_test_mode();
          jQuery('.wcusage_field_tr_payouts_paypalapi_test').change(function(){
            wcusage_check_paypal_test_mode();
          });
          function wcusage_check_paypal_test_mode() {
            if(jQuery('.wcusage_field_tr_payouts_paypalapi_test').prop('checked')) {
              jQuery('.wcu-field-section-tr-payouts-paypalapi-live').hide();
              jQuery('.wcu-field-section-tr-payouts-paypalapi-test').show();
            } else {
              jQuery('.wcu-field-section-tr-payouts-paypalapi-live').show();
              jQuery('.wcu-field-section-tr-payouts-paypalapi-test').hide();
            }
          }
        });
        </script>

        <span class="wcu-field-section-tr-payouts-paypalapi-live">

            <?php wcusage_setting_text_option('wcusage_field_paypalapi_id', '', esc_html__( '[Live] Client ID', 'woo-coupon-usage' ), '40px'); ?>

            <br/>

            <?php wcusage_setting_text_option('wcusage_field_paypalapi_secret', '', esc_html__( '[Live] Client Secret', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <span class="wcu-field-section-tr-payouts-paypalapi-test" style="color: red;">

            <?php wcusage_setting_text_option('wcusage_field_paypalapi_test_id', '', esc_html__( '[Test] Client ID', 'woo-coupon-usage' ), '40px'); ?>

            <br/>

            <?php wcusage_setting_text_option('wcusage_field_paypalapi_test_secret', '', esc_html__( '[Test] Client Secret', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <div style="clear: both;"></div>
        
        <!-- User Role -->
        <?php do_action('wcusage_hook_payouts_user_role_select', 'wcusage_field_tr_payouts_paypalapi_role'); ?>

      </span>

      <!-- Enable Stripe Payouts API -->
      <div style="margin-bottom: 40px;" id="stripeapi-settings"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_stripeapi_enable', 0, esc_html__( 'Stripe Payouts', 'woo-coupon-usage' ), '0px'); ?>
      </span>
      <?php
      $usaicon = '<img src="'.WCUSAGE_UNIQUE_PLUGIN_URL.'images/us.png" style="height: 8px;"> US';
      $ukicon = '<img src="'.WCUSAGE_UNIQUE_PLUGIN_URL.'images/gb.png" style="height: 8px;"> UK';
      ?>
      <i><?php echo esc_html__( 'Stripe Payouts payment method will allow you to one-click pay your affiliates directly into their Stripe / bank account.', 'woo-coupon-usage' ); ?>
      <?php echo esc_html__( 'Fees vary (typically around 1% - 2%). Learn more about Stripe Connect', 'woo-coupon-usage' ); ?> <a href="https://stripe.com/connect" target="_blank">here</a>.</i><br/>
      <i><?php echo esc_html__( 'Note: Payouts can only be made if you have the required funds in your Stripe account.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-stripe-payouts/#funds" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_stripeapi_enable', '.wcu-field-section-tr-payouts-stripeapi'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-stripeapi">

        <br/>

        <?php $wcusage_field_stripeapi_connect = wcusage_get_setting_value('wcusage_field_stripeapi_connect', 'standard'); ?>
    		<strong style="margin-left: 40px; display: inline-block;"><label for="scales"><?php echo esc_html__( 'Account Type:', 'woo-coupon-usage' ); ?></label></strong><br/>
    		<select style="margin-left: 40px;" name="wcusage_options[wcusage_field_stripeapi_connect]" id="wcusage_field_stripeapi_connect" class="wcusage_field_stripeapi_connect">
          <option value="standard" <?php if($wcusage_field_stripeapi_connect == "standard") { ?>selected<?php } ?>>Standard</option>
    			<option value="express" <?php if($wcusage_field_stripeapi_connect == "express") { ?>selected<?php } ?>>Express</option>
        </select>
        <br/><i style="margin-left: 40px;">If you're not sure, then use "Standard". The "Express" option offers a better user experience, but has extra fees. Learn More: <a href="https://couponaffiliates.com/docs/pro-stripe-payouts-standard-vs-express" target="_blank">Standard vs Express</a></i>

        <br/><br/>

        <!-- Change Payment Method Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_stripeapi_only', 'Stripe Payouts', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Change Stripe Account Label -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_stripeapi', 'Stripe Account', esc_html__( 'Stripe Account Label', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <!-- Payment Method Info -->
        <?php wcusage_setting_text_option('wcusage_field_tr_payouts_stripeapi_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
        <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <p style="margin-left: 40px; font-size: 16px; font-weight: bold;">API Credentials*</p>

        <p style="margin-left: 40px;">Get API keys here: <a href="https://dashboard.stripe.com/apikeys/" target="_blank">https://dashboard.stripe.com/apikeys/</a></p>

        <p style="margin-left: 40px;">Instructions: <a href="https://couponaffiliates.com/docs/pro-stripe-payouts-setup/" target="_blank">https://couponaffiliates.com/docs/pro-stripe-payouts-setup/</a></p>

        <br/>

        <!-- Change Payment Details Label (Default: "Payment Details") -->
        <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_stripeapi_test', 0, esc_html__( 'Enable Test Mode?', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <script>
        jQuery( document ).ready(function() {
          wcusage_check_stripe_test_mode();
          jQuery('.wcusage_field_tr_payouts_stripeapi_test').change(function(){
            wcusage_check_stripe_test_mode();
          });
          function wcusage_check_stripe_test_mode() {
            if(jQuery('.wcusage_field_tr_payouts_stripeapi_test').prop('checked')) {
              jQuery('.wcu-field-section-tr-payouts-stripeapi-live').hide();
              jQuery('.wcu-field-section-tr-payouts-stripeapi-test').show();
            } else {
              jQuery('.wcu-field-section-tr-payouts-stripeapi-live').show();
              jQuery('.wcu-field-section-tr-payouts-stripeapi-test').hide();
            }
          }
        });
        </script>

        <span class="wcu-field-section-tr-payouts-stripeapi-live">

          <?php wcusage_setting_text_option('wcusage_field_stripeapi_publish', '', esc_html__( '[Live] API Publishable Key', 'woo-coupon-usage' ), '40px'); ?>

          <br/>

          <?php wcusage_setting_text_option('wcusage_field_stripeapi_secret', '', esc_html__( '[Live] API Secret Key', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <span class="wcu-field-section-tr-payouts-stripeapi-test" style="color: red;">

          <?php wcusage_setting_text_option('wcusage_field_stripeapi_test_publish', '', esc_html__( '[Test] API Publishable Key', 'woo-coupon-usage' ), '40px'); ?>

          <br/>

          <?php wcusage_setting_text_option('wcusage_field_stripeapi_test_secret', '', esc_html__( '[Test] API Secret Key', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <div style="clear: both;"></div>

        <!-- User Role -->
        <?php do_action('wcusage_hook_payouts_user_role_select', 'wcusage_field_tr_payouts_stripeapi_role'); ?>

      </span>

      <!-- Enable Wise Payouts -->
      <div style="margin-bottom: 40px;" id="wise-settings"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_wise_enable', 0, esc_html__( 'Wise Bank Transfer Payouts', 'woo-coupon-usage' ), '0px'); ?>
      </span>

      <!-- Beta Message -->
      <span style="color: orange; font-weight: bold; font-size: 12px; margin-left: 40px;">
        <p>
          (BETA) <?php echo esc_html__( 'This feature is currently in beta. Please consider testing your settings and payments. If you experience any issues, please contact plugin support.', 'woo-coupon-usage' ); ?>
        </p>
      </span>

      <br/>

      <i><?php echo esc_html__( 'Wise Bank Transfer Payouts allows you to one-click pay your affiliates directly to their bank account through Wise with low transfer fees.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'Wise fees vary based on the currency and destination.', 'woo-coupon-usage' ); ?> <a href="https://wise.com/help/articles/2571942/pricing-and-fees" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</i><br/>
      <i><?php echo esc_html__( 'Prerequisites: To use Wise Bank Transfer Payouts, you will need a Wise business account and API access.', 'woo-coupon-usage' ); ?> <a href="https://docs.wise.com/api-docs/features/strong-customer-authentication-sca-for-api" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</i><br/>
      <i><?php echo esc_html__( 'No Wise account is required for the recipient.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_wise_enable', '.wcu-field-section-tr-payouts-wise'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-wise">

      <br/><br/>

      <!-- FAQ: Wise Bank Transfer Payouts -->
      <div class="wcu-admin-faq" style="margin-left: 40px; font-weight: normal;">

        <?php echo wcusage_admin_faq_toggle(
        "wcu_show_section_qna_wise_payouts",
        "wcu_qna_wise_payouts",
        "FAQ: How do Wise Bank Transfer payouts work?");
        ?>

        <div class="wcu-admin-faq-content wcu_qna_wise_payouts" id="wcu_qna_wise_payouts" style="display: none;">

          <p style="margin-bottom: 10px;">
            <?php echo esc_html__( 'Wise Bank Transfer payouts allow you to pay affiliates directly to their bank accounts using Wise\'s low-cost international transfer service.', 'woo-coupon-usage' ); ?>
          </p>
          <p style="margin-bottom: 10px;">
            <?php echo esc_html__( 'To use this feature, you will need a Wise business account with API access. You can sign up for a Wise account and get API access on their website.', 'woo-coupon-usage' ); ?>
          </p>
          <p style="margin-bottom: 10px;">
            <?php echo esc_html__( 'Once configured, your affiliates can select "Wise Bank Transfer" as their payout method and enter their bank details.', 'woo-coupon-usage' ); ?>
          </p>

          <p style="margin-bottom: 10px;">
            <?php echo esc_html__( 'When you process a payout, the plugin will create a transfer in your Wise account using the provided bank details. You will then need to verify and complete the transfer in Wise.', 'woo-coupon-usage' ); ?>
          </p>

          <p style="margin-bottom: 0;">
            <?php echo esc_html__( 'Important: Wise Bank Transfer payouts do not require the recipient to have a Wise account or email address. The payout is sent directly to their bank account using the provided banking details.', 'woo-coupon-usage' ); ?>
          </p>

          <a href="https://couponaffiliates.com/docs/pro-wise-payouts" target="_blank" class="button button-primary" style="margin-top: 10px;"><?php echo esc_html__( 'View Documentation', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span></a>

          <br/>
          
          <strong><?php echo esc_html__( 'For more information, watch the video:', 'woo-coupon-usage' ); ?></strong>
          <br/>
          <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/1106083661?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
          
        </div>

      </div>

      <br/>

      <!-- FAQ: Currency & Bank Account Information -->
      <div class="wcu-admin-faq" style="margin-left: 40px; font-weight: normal;">

        <?php echo wcusage_admin_faq_toggle(
        "wcu_show_section_qna_wise_currency",
        "wcu_qna_wise_currency",
        "FAQ: How does currency conversion work with Wise Bank Transfers?");
        ?>

        <div class="wcu-admin-faq-content wcu_qna_wise_currency" id="wcu_qna_wise_currency" style="display: none;">

          <p style="margin-bottom: 10px;">
            <strong><?php echo esc_html__( 'Currency Handling:', 'woo-coupon-usage' ); ?></strong><br/>
            <?php echo esc_html__( 'Bank transfer payouts are sent in your store\'s base currency and Wise automatically converts them to the recipient\'s bank currency:', 'woo-coupon-usage' ); ?>
          </p>
          <ul style="margin-left: 20px; margin-bottom: 15px;">
            <li><?php echo esc_html__( 'UK Sort Code accounts → Receive GBP (converted from your store currency)', 'woo-coupon-usage' ); ?></li>
            <li><?php echo esc_html__( 'US ABA Routing accounts → Receive USD (converted from your store currency)', 'woo-coupon-usage' ); ?></li>
            <li><?php echo esc_html__( 'EU IBAN accounts → Receive EUR (converted from your store currency)', 'woo-coupon-usage' ); ?></li>
            <li><?php echo esc_html__( 'International SWIFT accounts → Receive local currency (converted from your store currency)', 'woo-coupon-usage' ); ?></li>
          </ul>
          <p style="margin-bottom: 10px;">
            <strong><?php echo esc_html__( 'Currency Conversion:', 'woo-coupon-usage' ); ?></strong><br/>
            <?php echo esc_html__( 'All payouts are deducted from your store\'s base currency balance. Wise handles currency conversion automatically at their competitive exchange rates. You will see the exact conversion rate and fees before each transfer is processed.', 'woo-coupon-usage' ); ?>
          </p>
          <p style="margin-bottom: 0;">
            <strong><?php echo esc_html__( 'No Email Required:', 'woo-coupon-usage' ); ?></strong><br/>
            <?php echo esc_html__( 'Bank transfers do not require the recipient to have a Wise account or email address. The payout is sent directly to their bank account using the provided banking details.', 'woo-coupon-usage' ); ?>
          </p>

        </div>

      </div>

      <br/>

        <!-- Wise Bank Transfer Payouts Configuration -->
        <div style="margin-left: 40px;">

          <br/>

          <!-- Change Payment Method Label -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_wisebank_only', 'Wise Bank Transfer', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Change Payment Details Label -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_wisebank', 'Bank Account Details', esc_html__( 'Payment Details Section Label', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Payment Method Info -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_wisebank_info', 'Please enter your bank account details in the individual fields below. We will create a recipient account and process the payout via Wise. Required fields are marked with an asterisk (*).', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '0px'); ?>
          <i style="margin-left: 0px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

          <div style="margin-left: -40px;">
          <!-- User Role -->
          <?php do_action('wcusage_hook_payouts_user_role_select', 'wcusage_field_tr_payouts_wisebank_role'); ?>
          </div>

        </div>
      
        <br/>

        <p style="margin-left: 40px; font-size: 16px; font-weight: bold;">API Credentials*</p>

        <p style="margin-left: 40px;">Instructions: <a href="https://docs.wise.com/api-docs/api-reference/getting-started" target="_blank">https://docs.wise.com/api-docs/api-reference/getting-started</a></p>

        <p style="margin-left: 40px;">Generate API Token: <a href="https://wise.com/your-account/integrations-and-tools/api-tokens/" target="_blank">https://wise.com/profile/api-keys</a></p>
        
        <br/>

        <!-- Test Mode Toggle -->
        <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_wiseapi_test', 0, esc_html__( 'Enable Test Mode?', 'woo-coupon-usage' ), '40px'); ?>

        <br/>

        <?php if( wcu_fs()->can_use_premium_code() ) { ?>
        <script>
        jQuery( document ).ready(function() {
          wcusage_check_wise_test_mode();
          jQuery('.wcusage_field_tr_payouts_wiseapi_test').change(function(){
            wcusage_check_wise_test_mode();
          });
          function wcusage_check_wise_test_mode() {
            if(jQuery('.wcusage_field_tr_payouts_wiseapi_test').prop('checked')) {
              jQuery('.wcu-field-section-tr-payouts-wiseapi-live').hide();
              jQuery('.wcu-field-section-tr-payouts-wiseapi-test').show();
            } else {
              jQuery('.wcu-field-section-tr-payouts-wiseapi-live').show();
              jQuery('.wcu-field-section-tr-payouts-wiseapi-test').hide();
            }
          }
          
          // Wise Profiles Fetch Functionality
          jQuery('.wise-fetch-profiles-btn').click(function() {
            var mode = jQuery(this).data('mode');
            var tokenField = mode === 'test' ? '#wcusage_field_wiseapi_test_token' : '#wcusage_field_wiseapi_token';
            var token = jQuery(tokenField).val().trim();
            var statusSpan = '#wise-fetch-status-' + mode;
            var dropdown = '#wise-profile-dropdown-' + mode;
            var selectionDiv = '#wise-profile-selection-' + mode;
            
            if (!token) {
              jQuery(statusSpan).html('<span style="color: red;">Please enter an API token first.</span>');
              return;
            }
            
            jQuery(this).prop('disabled', true).text('Fetching...');
            jQuery(statusSpan).html('<span style="color: blue;">Fetching profiles...</span>');
            jQuery(selectionDiv).hide();
            
            // Make AJAX request to fetch profiles
            jQuery.ajax({
              url: ajaxurl,
              type: 'POST',
              data: {
                action: 'wcusage_fetch_wise_profiles',
                token: token,
                test_mode: mode === 'test' ? 1 : 0,
                nonce: '<?php echo wp_create_nonce("wcusage_wise_profiles_nonce"); ?>'
              },
              success: function(response) {
                if (response.success && response.data.profiles) {
                  var profiles = response.data.profiles;
                  jQuery(dropdown).empty().append('<option value="">Select a profile...</option>');
                  
                  jQuery.each(profiles, function(index, profile) {
                    var optionText = profile.name + ' (' + profile.type + ') - ID: ' + profile.id;
                    jQuery(dropdown).append('<option value="' + profile.id + '">' + optionText + '</option>');
                  });
                  
                  jQuery(statusSpan).html('<span style="color: green;">Found ' + profiles.length + ' profile(s)</span>');
                  jQuery(selectionDiv).show();
                } else {
                  var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to fetch profiles';
                  jQuery(statusSpan).html('<span style="color: red;">' + errorMsg + '</span>');
                }
              },
              error: function() {
                jQuery(statusSpan).html('<span style="color: red;">Error: Unable to fetch profiles</span>');
              },
              complete: function() {
                jQuery('.wise-fetch-profiles-btn[data-mode="' + mode + '"]').prop('disabled', false).text('Fetch Profiles');
              }
            });
          });
          
          // Automatically update Profile ID when dropdown selection changes
          jQuery('#wise-profile-dropdown-live, #wise-profile-dropdown-test').change(function() {
            var mode = this.id.includes('test') ? 'test' : 'live';
            var profileIdField = mode === 'test' ? '#wcusage_field_wiseapi_test_profile_id' : '#wcusage_field_wiseapi_profile_id';
            var selectedId = jQuery(this).val();
            
            if (selectedId) {
              jQuery(profileIdField).val(selectedId);
              jQuery('#wise-fetch-status-' + mode).html('<span style="color: green;">Profile ID updated!</span>');
              // Trigger change on #wcusage_field_wiseapi_profile_id
              jQuery(profileIdField).trigger('change');
            } else {
              jQuery(profileIdField).val('');
              jQuery('#wise-fetch-status-' + mode).html('');
            }
          });
        });
        </script>
        <?php } ?>

        <span class="wcu-field-section-tr-payouts-wiseapi-live">

            <?php wcusage_setting_text_option('wcusage_field_wiseapi_token', '', esc_html__( '[Live] API Token', 'woo-coupon-usage' ), '40px'); ?>

            <br/>
            
            <!-- Fetch Profiles Button for Live -->
            <div style="margin-left: 40px; margin-bottom: 15px;">
                <button type="button" id="wise-fetch-profiles-live" class="button button-secondary wise-fetch-profiles-btn" data-mode="live">
                    <?php echo esc_html__( 'Fetch Profiles', 'woo-coupon-usage' ); ?>
                </button>
                <span id="wise-fetch-status-live" style="margin-left: 10px;"></span>
            </div>
            
            <!-- Profile Selection Dropdown for Live -->
            <div id="wise-profile-selection-live" style="margin-left: 40px; margin-bottom: 15px; display: none;">
                <label for="wise-profile-dropdown-live" style="font-weight: bold;">
                    <?php echo esc_html__( 'Select Profile:', 'woo-coupon-usage' ); ?>
                </label><br/>
                <select id="wise-profile-dropdown-live" style="min-width: 300px; margin-top: 5px;">
                    <option value=""><?php echo esc_html__( 'Select a profile...', 'woo-coupon-usage' ); ?></option>
                </select>
            </div>

            <?php wcusage_setting_text_option('wcusage_field_wiseapi_profile_id', '', esc_html__( '[Live] Profile ID', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <span class="wcu-field-section-tr-payouts-wiseapi-test" style="color: red;">

            <?php wcusage_setting_text_option('wcusage_field_wiseapi_test_token', '', esc_html__( '[Test] API Token', 'woo-coupon-usage' ), '40px'); ?>

            <br/>
            
            <!-- Fetch Profiles Button for Test -->
            <div style="margin-left: 40px; margin-bottom: 15px;">
                <button type="button" id="wise-fetch-profiles-test" class="button button-secondary wise-fetch-profiles-btn" data-mode="test">
                    <?php echo esc_html__( 'Fetch Profiles', 'woo-coupon-usage' ); ?>
                </button>
                <span id="wise-fetch-status-test" style="margin-left: 10px;"></span>
            </div>
            
            <!-- Profile Selection Dropdown for Test -->
            <div id="wise-profile-selection-test" style="margin-left: 40px; margin-bottom: 15px; display: none;">
                <label for="wise-profile-dropdown-test" style="font-weight: bold;">
                    <?php echo esc_html__( 'Select Profile:', 'woo-coupon-usage' ); ?>
                </label><br/>
                <select id="wise-profile-dropdown-test" style="min-width: 300px; margin-top: 5px;">
                    <option value=""><?php echo esc_html__( 'Select a profile...', 'woo-coupon-usage' ); ?></option>
                </select>
            </div>

            <?php wcusage_setting_text_option('wcusage_field_wiseapi_test_profile_id', '', esc_html__( '[Test] Profile ID', 'woo-coupon-usage' ), '40px'); ?>

        </span>

        <div style="clear: both;"></div>

        <br/>

        <?php
        // Display dynamic encryption status
        if (function_exists('wcusage_get_bank_encryption_status_content')) {
          echo wcusage_get_bank_encryption_status_content();
        } else {
          // Fallback static display if function not available
          ?>
          <div style="margin-left: 40px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; margin-bottom: 20px;">
            <strong><?php echo esc_html__( 'Bank Data Encryption', 'woo-coupon-usage' ); ?></strong><br/>
            <p style="margin: 10px 0;">
              <?php echo esc_html__( 'Sensitive bank details are automatically encrypted when you define an encryption key in wp-config.php:', 'woo-coupon-usage' ); ?>
            </p>
            <div style="background: #f0f0f1; border: 1px solid #ddd; padding: 10px; margin: 10px 0; font-family: monospace;">
              <code>define( 'CAFFS_WISE_ENCRYPTION_KEY', 'your-32-character-encryption-key-here' );</code>
            </div>
            <p style="margin: 10px 0; color: #d63638;">
              <strong><?php echo esc_html__( 'Important:', 'woo-coupon-usage' ); ?></strong> 
              <?php echo esc_html__( 'Store your encryption key securely! If lost, encrypted bank details become permanently unrecoverable.', 'woo-coupon-usage' ); ?>
            </p>
          </div>
          <?php
        }
        ?>

      </span>

      <!-- Enable Store Credit -->
      <div style="margin-bottom: 40px;" id="storecredit-settings"></div>

      <span class="wcu-admin-payouts-headers">
        <?php wcusage_setting_toggle_option('wcusage_field_storecredit_enable', 0, esc_html__( 'Store Credit / Wallet', 'woo-coupon-usage' ), '0px'); ?>
      </span>
      <i><?php echo esc_html__( 'Store credit payouts will allow affiliates to have commission paid out into a "wallet" which they can then use as a discount to purchase items/products from your shop.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-store-credit" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a></i><br/>
      <i><?php echo esc_html__( 'If you want to show the logged in users current store credit balance somewhere, use the shortcode', 'woo-coupon-usage' ); ?>: [couponaffiliates_credit]</i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_storecredit_enable', '.wcu-field-section-tr-payouts-storecredit'); // Show or Hide ?>
      <span class="wcu-field-section-tr-payouts-storecredit">

        <br/>

        <!-- Store Credit System/Plugin Picker -->
        <script>
        jQuery( document ).ready(function() {
          wcusage_js_storecredit_system_change();
          jQuery('#wcusage_field_storecredit_system').change(function() {
            wcusage_js_storecredit_system_change();
          });
        });
        function wcusage_js_storecredit_system_change() {
          jQuery('.section-default-credit-system').hide();
          jQuery('.section-default-credit-system-settings').show();
          if( jQuery('#wcusage_field_storecredit_system :selected' ).val() == '' ) {
            jQuery('.section-default-credit-system-settings').hide();
          }
          if( jQuery('#wcusage_field_storecredit_system :selected' ).val() == 'default' ) {
            jQuery('.section-default-credit-system-settings').show();
            jQuery('.section-default-credit-system').hide();
            jQuery('.section-default-credit-system-default').show();
          }
          if( jQuery('#wcusage_field_storecredit_system :selected' ).val() == 'custom' ){
            jQuery('.section-default-credit-system-settings').hide();
            jQuery('.section-default-credit-system').hide();
            jQuery('.section-default-credit-system-custom').show();
          }
          <?php
          // Custom Hook
          if( wcu_fs()->can_use_premium_code() ) {
            do_action('wcusage_hook_settings_store_credit_dropdown_script');
          }
          ?>
        }
        </script>
        <?php $wcusage_field_storecredit_system = wcusage_get_setting_value('wcusage_field_storecredit_system', 'default'); ?>
    		<strong style="margin-left: 40px; display: inline-block;"><label for="scales"><?php echo esc_html__( 'Wallet System', 'woo-coupon-usage' ); ?></label></strong><br/>
    		<select style="margin-left: 40px;" name="wcusage_options[wcusage_field_storecredit_system]" id="wcusage_field_storecredit_system" class="wcusage_field_storecredit_system">
          <option value="">Select an option...</option>
          <option value="default" <?php if($wcusage_field_storecredit_system == "default") { ?>selected<?php } ?>><?php echo esc_html__( '(Free) Built-in Store Credit & Wallet System', 'woo-coupon-usage' ); ?></option>
          <?php
          // Custom Hook
          if( wcu_fs()->can_use_premium_code() ) {
            do_action('wcusage_hook_settings_store_credit_dropdown', $wcusage_field_storecredit_system);
          }
          ?>
          <option value="custom" <?php if($wcusage_field_storecredit_system == "custom") { ?>selected<?php } ?>><?php echo esc_html__( '(Custom) 3rd Party Wallet Plugin Integrations', 'woo-coupon-usage' ); ?></option>
        </select>

        <br/>

        <span class="section-default-credit-system section-default-credit-system-default">

          <!-- Info -->
          <br/><strong style="margin-left: 40px; display: inline-block;"><label for="scales"><?php echo esc_html__( 'Information:', 'woo-coupon-usage' ); ?></label></strong><br/>
          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'You are all set! Store credit payouts can now be added to the users wallet automatically in one-click. They can spend this credit when visiting the cart/checkout.', 'woo-coupon-usage' ); ?>
          </p>
          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'This is our default store credit system, built directly into this plugin. (A more simple solution with no additional setup needed.)', 'woo-coupon-usage' ); ?>
          </p>

        </span>

        <span class="section-default-credit-system section-default-credit-system-custom">

          <!-- Info -->
          <br/><strong style="margin-left: 40px; display: inline-block;"><label for="scales"><?php echo esc_html__( 'Information:', 'woo-coupon-usage' ); ?></label></strong><br/>
          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'For most websites, unless you are already using a 3rd party wallet plugin, we would suggest just using our free built-in wallet system.', 'woo-coupon-usage' ); ?>
          </p>
          <br/>
          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'However, if preferred, you can use a 3rd party wallet plugin for the store credit payouts. This does however require more setup work, and an additional integration plugin.', 'woo-coupon-usage' ); ?>
          </p>
          <br/>
          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'The following integration addons are available to download and install right now:', 'woo-coupon-usage' ); ?>
          </p>

          <br/>

          <!-- TeraWallet Info -->
          <p style="margin-left: 40px;">
            <?php
            $terawallet_active = ( is_plugin_active( 'woo-wallet/woo-wallet.php' ) ? true : false );
            $terawallet_addon_active = ( is_plugin_active( 'woo-coupon-usage-terawallet-integration-premium/wcu-terawallet-integration.php' ) ? true : false );
            $terawallet_link = "https://en-gb.wordpress.org/plugins/woo-wallet";
            ?>
            <strong>TeraWallet</strong> <span style="font-size: 10px;">By WCBeginner <a href="<?php echo esc_url($terawallet_link); ?>" target="_blank" title="View Plugin"><span class="fas fa-external-link-alt"></span></a></span><br/>
            <?php if($terawallet_active) { ?><span class="fas fa-check-circle" style="color: green;"></span> Plugin Installed & Activated<br/><?php } ?>
            <?php if($terawallet_addon_active) { ?>
              <?php if(!$terawallet_active) { ?><span class="fas fa-times-circle" style="color: red;"></span> Plugin Installed & Activated<br/><?php } ?>
              <span class="fas fa-check-circle" style="color: green;"></span> Integration Addon Installed & Activated
            <?php } else { ?>
              Integration Addon Price: $19.99 (One-Time)<br/>
              <?php if($terawallet_active) { ?><span class="fas fa-times-circle" style="color: red;"></span><?php } ?>
                <a href="https://couponaffiliates.com/addons/terawallet-integration" target="_blank" title="View Addon" style="text-decoration: none;">
                  View Details & Download Integration <span class="fas fa-arrow-circle-right"></span>
                </a>
            <?php } ?>
          </p>

          <br/>

          <!-- YITH WooCommerce Account Funds Info -->
          <p style="margin-left: 40px;">
            <?php
            $yithfunds_active = ( is_plugin_active( 'yith-woocommerce-account-funds-premium/init.php' ) ? true : false );
            $yithfunds_addon_active = ( is_plugin_active( 'woo-coupon-usage-yithfunds-integration-premium/wcu-yithfunds-integration.php' ) ? true : false );
            $yithfunds_link = "https://yithemes.com/themes/plugins/yith-woocommerce-account-funds";
            ?>
            <strong>YITH WooCommerce Account Funds</strong> <span style="font-size: 10px;">By YITH® <a href="<?php echo esc_url($yithfunds_link); ?>" target="_blank" title="View Plugin"><span class="fas fa-external-link-alt"></span></a></span><br/>
            <?php if($yithfunds_active) { ?><span class="fas fa-check-circle" style="color: green;"></span> Plugin Installed & Activated<br/><?php } ?>
            <?php if($yithfunds_addon_active) { ?>
              <?php if(!$yithfunds_active) { ?><span class="fas fa-times-circle" style="color: red;"></span> Plugin Installed & Activated<br/><?php } ?>
              <span class="fas fa-check-circle" style="color: green;"></span> Integration Addon Installed & Activated
            <?php } else { ?>
              Integration Addon Price: $19.99 (One-Time)<br/>
              <?php if($yithfunds_active) { ?><span class="fas fa-times-circle" style="color: red;"></span><?php } ?>
                <a href="https://couponaffiliates.com/addons/yithfunds-integration" target="_blank" title="View Addon" style="text-decoration: none;">
                  View Details & Download Integration <span class="fas fa-arrow-circle-right"></span>
                </a>
            <?php } ?>
          </p>

          <br/>

          <p style="margin-left: 40px;">
            <?php echo esc_html__( 'Once you have installed/activated both the integration addon, and the wallet plugin, refresh this page. You will then be able to enable it in the "Wallet System" dropdown above.', 'woo-coupon-usage' ); ?>
          </p>

          <br/>

          <p style="margin-left: 40px; font-weight: bold;">
            <?php echo esc_html__( 'Want us to create a new plugin integration?', 'woo-coupon-usage' ); ?> <a href="https://roadmap.couponaffiliates.com/boards/feature-requests" target="_blank"><?php echo esc_html__( 'Submit a feature request.', 'woo-coupon-usage' ); ?></a>
          </p>

        </span>

        <?php
        // Custom Hook
        if( wcu_fs()->can_use_premium_code() ) {
          do_action('wcusage_hook_settings_store_credit_info');
        }
        ?>

        <br/>

        <span class="section-default-credit-system-settings">

          <!-- Change Payment Method Label - Store Credit -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_only', 'Store Credit', esc_html__( 'Payment Method Name', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'The name of your Store Credit wallet, show in the payout method selection etc.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Payment Method Info - Store Credit -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_info', '', esc_html__( 'Payment Method Information', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'Custom information/text shown when payment method is selected (in the dashboard settings).', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- "Store Credit Balance" Text - Store Credit -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_balance', 'Store Credit Balance', esc_html__( 'Custom "Store Credit Balance" Text', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'Text shown next to the users store credit balance.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Custom "Affiliate Commission" Text -->
          <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_description', 'Affiliate Commission', esc_html__( 'Custom "Affiliate Commission" Text', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'Used when describing the store credit payment/transaction in the logs.', 'woo-coupon-usage' ); ?></i><br/>

          <span class="section-default-credit-system section-default-credit-system-default">

            <br/>

            <!-- Change Cart Discount Text - Store Credit -->
            <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_discount', 'Store Credit Discount', esc_html__( 'Cart Discount Text', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'This is the name of the discount shown on the cart page, when store credit is applied.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- "Affiliate store credit available" Text - Store Credit -->
            <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_available', 'Affiliate store credit available', esc_html__( 'Custom "Affiliate store credit available" Text', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'Shown on the cart/checkout page when they have credit available to spend.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- "Apply {credit} credit to this order." Text - Store Credit -->
            <?php wcusage_setting_text_option('wcusage_field_tr_payouts_storecredit_apply', 'Apply {credit} credit to this order.', esc_html__( 'Custom "Apply credit to this order" Text', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'This is shown below the message above, next to a checkbox, allowing them to apply some or all of their credit to the cart.', 'woo-coupon-usage' ); ?></i><br/>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'Use merge tag {credit} to show the amount of credit they can apply.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>


            <!-- Show "Store Credit" Column on Users List -->
            <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_storecredit_users_col', 1, esc_html__( 'Show "Store Credit" column on admin users list?', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'This will show the current "Store Credit" for each user on the "All Users" admin page.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Multi-currency support for Store Credit -->
            <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_storecredit_multicurrency', 1, esc_html__( 'Enable multi-currency support for store credit?', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'If enabled, store credit will be converted and displayed in the selected currency at checkout. If disabled, all store credit will use the base store currency only.', 'woo-coupon-usage' ); ?></i><br/>
            <i style="margin-left: 40px;"><?php echo sprintf( esc_html__( 'Note: This requires the <a href="%s" target="_blank">multi-currency module</a> to be enabled and configured.', 'woo-coupon-usage' ), 'https://couponaffiliates.com/docs/multi-currency-support/' ); ?></i><br/>

          <?php
          // Custom Hook
          if( wcu_fs()->can_use_premium_code() ) {
            do_action('wcusage_hook_settings_store_credit_options');
          }

          ?>

          <br/>

          <!-- Commission Bonus % -->
          <?php wcusage_setting_number_option('wcusage_field_tr_payouts_storecredit_bonus', '0', esc_html__( 'Bonus Commission (%)', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'Give affiliates extra commission % as a bonus for selecting Store Credit as their payout method.', 'woo-coupon-usage' ); ?></i><br/>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'This bonus is not applied on the dashboard or when they request payouts. It will simply apply the bonus % as additional credit, when the payout is marked as paid.', 'woo-coupon-usage' ); ?></i><br/>

          <?php if(get_option("woocommerce_tax_display_cart") == "incl") { ?>

            <br/>

            <!-- "Store Credit" Exclude Tax -->
            <?php wcusage_setting_toggle_option('wcusage_field_tr_payouts_storecredit_excl_tax', 0, esc_html__( 'Exclude/Remove taxes from Store Credit in cart.', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'This will remove/deduct the tax amount from the store credit, if it is added to the credit amount in the cart.', 'woo-coupon-usage' ); ?></i><br/>

          <?php } ?>
            
          </span>

        </span>

        <!-- User Role -->
        <?php do_action('wcusage_hook_payouts_user_role_select', 'wcusage_field_tr_payouts_storecredit_role'); ?>

      </span>

      <div style="margin-bottom: 40px;"></div>

      <hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Default Payout Method:</h3>

      <p>If required, you can set one of the payout methods as the default. This means that if the affiliate does not currently have a payout method selected, this one will be enabled by default.</p>
      <p>- If set to "Store Credit Payouts" or "Custom Payment methods" (with field disabled), they will therefore be able to instantly request payouts without needing to select their payout method first.</p>
      <p>- If set to "Direct Bank Transfer", "PayPal Payouts", "Stripe Payouts", or "Wise Bank Transfer Payouts" they will still be required to update and set their payment details in the settings tab, but it will be selected as their default option.</p>

      <br/>

      <?php $currentdefaulttype = wcusage_get_setting_value('wcusage_field_payouts_default_type', '0'); ?>
      <p>
      <select name="wcusage_options[wcusage_field_payouts_default_type]" id="wcusage_field_payouts_default_type">
          <option value="-" <?php if(!$currentdefaulttype) { ?>selected<?php } ?>><?php echo esc_html__( 'No Default', 'woo-coupon-usage' ); ?></option>
          <option value="custom1" <?php if($currentdefaulttype == "custom1") { ?>selected<?php } ?>><?php echo esc_html__( 'Custom Payment Method #1', 'woo-coupon-usage' ); ?></option>
          <option value="custom2" <?php if($currentdefaulttype == "custom2") { ?>selected<?php } ?>><?php echo esc_html__( 'Custom Payment Method #2', 'woo-coupon-usage' ); ?></option>
          <option value="banktransfer" <?php if($currentdefaulttype == "banktransfer") { ?>selected<?php } ?>><?php echo esc_html__( 'Direct Bank Transfer', 'woo-coupon-usage' ); ?></option>
          <option value="paypalapi" <?php if($currentdefaulttype == "paypalapi") { ?>selected<?php } ?>><?php echo esc_html__( 'PayPal Payouts', 'woo-coupon-usage' ); ?></option>
          <option value="stripeapi" <?php if($currentdefaulttype == "stripeapi") { ?>selected<?php } ?>><?php echo esc_html__( 'Stripe Payouts', 'woo-coupon-usage' ); ?></option>
          <option value="wisebank" <?php if($currentdefaulttype == "wisebank") { ?>selected<?php } ?>><?php echo esc_html__( 'Wise Bank Transfer Payouts', 'woo-coupon-usage' ); ?></option>
          <option value="credit" <?php if($currentdefaulttype == "credit") { ?>selected<?php } ?>><?php echo esc_html__( 'Store Credit / Wallet', 'woo-coupon-usage' ); ?></option>
      </select>
      </p>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Email Notifications', 'woo-coupon-usage' ); ?></h3>

      <p>
  		  <?php echo esc_html__( 'To manage (and enable) email notifications for payouts, go to the "Emails" settings tab.', 'woo-coupon-usage' ); ?>
  		</p>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Invoices & PDF Statements:</h3>

      <p>You can enable "Invoices" and "PDF statement" features in the "PRO modules" section. A new settings tab (Invoices/Statements) will then appear on this page for setup and customisation.</p>
      <p>- Invoices will allow affiliates to upload their invoices when submitting a payout.</p>
      <p>- Statements will automatically generate a PDF payment statement for affiliates to download, when a payout is requested.</p>

    </span>

    </div>

	</div>

 <?php
}

/*
* Payouts User Role Select
*/
add_action('wcusage_hook_payouts_user_role_select', 'wcusage_payouts_user_role_select', 10, 1);
function wcusage_payouts_user_role_select($thisid) {

  $options = get_option('wcusage_options');

  if(!empty($options[$thisid])) {
    $current_roles = $options[$thisid];
  } else {
    $current_roles = '';
  }
  ?>
  
  <span style="margin-left: 40px;">

  <!-- Toggle Option -->
  <?php
  if(empty($current_roles)) {    
    $toggle_checked = 0;
  } else {
    $toggle_checked = 1;
    // Only update on non-GET requests
    if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
      $options1 = get_option('wcusage_options');
      $options1[$thisid . '_toggle'] = 1;
      update_option('wcusage_options', $options1);
    }
  }
  wcusage_setting_toggle_option($thisid.'_toggle', $toggle_checked, esc_html__( 'Limit to certain user roles & groups?', 'woo-coupon-usage' ), '40px');
  wcusage_setting_toggle('.'.$thisid.'_toggle', '.payouts-role-select-'.$thisid); // Show or Hide
  ?>

  <script>
  jQuery( document ).ready(function() {
    if(jQuery('.payouts-role-select-<?php echo esc_attr($thisid); ?> input[type="checkbox"]:checked').length > 0) {
      jQuery('#<?php echo esc_attr($thisid); ?>_toggle_p label.switch').hide();
    } else {
      jQuery('#<?php echo esc_attr($thisid); ?>_toggle_p label.switch').show();
    }
    jQuery('.payouts-role-select-<?php echo esc_attr($thisid); ?> input[type="checkbox"]').change(function() {
      if(jQuery('.payouts-role-select-<?php echo esc_attr($thisid); ?> input[type="checkbox"]:checked').length > 0) {
        jQuery('#<?php echo esc_attr($thisid); ?>_toggle_p label.switch').hide();
      } else {
        jQuery('#<?php echo esc_attr($thisid); ?>_toggle_p label.switch').show();
      }
    });
  });
  </script>

  <!-- User Role Select -->
  <span class="payouts-role-select-<?php echo esc_attr($thisid); ?>">

    <span style="height: 50px; width: 250px; overflow-y: auto;
    display: block; margin-left: 40px; border: 1px solid #ddd; padding: 10px;">

    <?php
    $roles = get_editable_roles();
    // Re-order with all those containing "coupon_affiliate" at the start
    $roles2 = array();
    foreach ($roles as $key => $role) {
      if (strpos($key, 'coupon_affiliate') !== false) {
        $roles2[$key] = $role;
        unset($roles[$key]);
      }
    }
    $roles2 = array_merge($roles2, $roles);
    foreach ($roles2 as $key => $role) {
      $name = 'wcusage_options['.$thisid.']['.$key.']';
      $role_name = $role['name'];
      if (strpos($key, 'coupon_affiliate') !== false) {
        $role_name = '(Group) '.$role_name;
      }
      $checked = '';
      if(isset($options[$thisid]) && is_array($options[$thisid])) {
        if(isset($options[$thisid][$key])) {
          $checked = 'checked';
        }
      } else {
        if(isset($options[$thisid]) && $options[$thisid] == $key) {
          $checked = 'checked';
        }
      }
      echo '<span id="'.esc_attr($thisid).'">
      <input type="checkbox" checktype="multi"
      class="payouts-role payouts-role-'.esc_attr($key).' wcusage_field_'.esc_attr($thisid).'_role"
      checktypekey="'.esc_attr($key).'"
      customid="'.esc_attr($thisid).'"
      name="'.esc_attr($name).'"
      '.esc_attr($checked).'> '.esc_attr($role_name).'</span><br/>';
    }
    ?>

    </span>

  </span>

  <i style="margin-left: 40px;"><?php echo esc_html__( 'If at-least 1 role is selected, this payout method will only be available for the selected user roles. If none are selected it will be available for all roles.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  </span>

  <?php
}