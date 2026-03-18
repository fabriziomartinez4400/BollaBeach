<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_reports( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

  <div id="affiliate-reports-settings" class="settings-area" <?php if ( !wcu_fs()->can_use_premium_code() ) { ?>title="Available with Pro version." style="pointer-events:none; opacity: 0.6;"<?php } ?>>

	<?php if ( !wcu_fs()->can_use_premium_code() ) { ?><p><strong style="color: green;"><?php echo esc_html__( 'Available with Pro version.', 'woo-coupon-usage' ); ?></strong></p><?php } ?>

  	<h1><?php echo esc_html__( 'Affiliate Email Reports', 'woo-coupon-usage' ); ?> (Pro)</h1>

    <hr/>

    <?php wcusage_setting_toggle_option('wcusage_field_enable_reports', 0, 'Enable "Affiliate Reports" Features', '0px'); ?>

    <br/>

    <!-- FAQ: How do affiliate reports work? -->
    <div class="wcu-admin-faq">

      <?php echo wcusage_admin_faq_toggle(
      "wcu_show_section_qna_affiliate_reports",
      "wcu_qna_affiliate_reports",
      "FAQ: How do affiliate reports work?");
      ?>

      <div class="wcu-admin-faq-content wcu_qna_affiliate_reports" id="wcu_qna_affiliate_reports" style="display: none;">

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Automatically send an email report (with an attached PDF report) to affiliates every week/month with a summary of their recent referral/commission stats.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'This includes a nice email report, along with a downloadable PDF version attached to the email.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The email report will use the same email template/styling from the WooCommerce email settings.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If you have lots of affiliates, we highly recommend using an SMTP Service Provider via the FluentSMTP plugin for more reliable email delivery.', 'woo-coupon-usage' ); ?><br/>

        <a href="https://couponaffiliates.com/docs/pro-affiliate-reports" target="_blank" class="button button-primary" style="margin-top: 10px;"><?php echo esc_html__( 'View Documentation', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span></a>

      </div>

    </div>

      <?php wcusage_setting_toggle('.wcusage_field_enable_reports', '.wcu-field-section-reports'); // Show or Hide ?>
      <span class="wcu-field-section-reports">

            <br/><hr/>

            <h3 id="statements-settings"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Schedule:</h3>

            <span class="wcu-field-section-reports-freq">

              <?php echo sprintf( '<i>%s</i><br/>', wp_kses_post( sprintf( esc_html__( 'Requires cron jobs to be enabled. We recommend using "Real Cron Job" instead of "WP-Cron". This may offer better performance, and will give more accurate delivery (at the specific day & time). %s.', 'woo-coupon-usage' ), '<a href="https://couponaffiliates.com/docs/real-cron-job" target="_blank">' . esc_html__( 'Click here to learn more', 'woo-coupon-usage' ) . '</a>' ) ) ); ?>

              <br/>

              <!-- Frequency -->
            	<p <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>
            		<?php $wcusage_field_pdfreports_freq = wcusage_get_setting_value('wcusage_field_pdfreports_freq', 'monthly'); ?>
            		<input type="hidden" value="0" id="wcusage_field_pdfreports_freq" data-custom="custom" name="wcusage_options[wcusage_field_pdfreports_freq]" >
            		<strong><label for="scales"><?php echo esc_html__( 'How often should a report be sent to affiliates via email?', 'woo-coupon-usage' ); ?></label></strong><br/>
            		<select name="wcusage_options[wcusage_field_pdfreports_freq]" id="wcusage_field_pdfreports_freq">
            			<option value="monthly" <?php if($wcusage_field_pdfreports_freq == "monthly") { ?>selected<?php } ?>><?php echo esc_html__( 'Monthly', 'woo-coupon-usage' ); ?></option>
            			<option value="weekly" <?php if($wcusage_field_pdfreports_freq == "weekly") { ?>selected<?php } ?>><?php echo esc_html__( 'Weekly', 'woo-coupon-usage' ); ?></option>
                  <option value="quarterly" <?php if($wcusage_field_pdfreports_freq == "quarterly") { ?>selected<?php } ?>><?php echo esc_html__( 'Quarterly', 'woo-coupon-usage' ); ?></option>
                </select>
            	</p>
              <i><?php echo esc_html__( 'Reports will be automatically scheduled to send on the first day of the week or month.', 'woo-coupon-usage' ); ?></i><br/>
              <i><?php echo esc_html__( 'In the affiliate report, stats will be shown/calculated based on the previous date range. So monthly reports will show stats from the previous month.', 'woo-coupon-usage' ); ?></i><br/>

              <br/>

              <!-- DateTime -->
              <p <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>
            		<?php $wcusage_field_pdfreports_time = wcusage_get_setting_value('wcusage_field_pdfreports_time', '12'); ?>
            		<input type="hidden" value="0" id="wcusage_field_pdfreports_time" data-custom="custom" name="wcusage_options[wcusage_field_pdfreports_time]" >
            		<strong><label for="scales"><?php echo esc_html__( 'What time of the day should reports be sent?', 'woo-coupon-usage' ); ?></label></strong><br/>
            		<select name="wcusage_options[wcusage_field_pdfreports_time]" id="wcusage_field_pdfreports_time">
                  <?php for ($x = 0; $x <= 24; $x++) { ?>
                  <?php if($x < 10) { $x = sprintf("%02d", $x); } ?>
            			<option value="<?php echo esc_attr($x); ?>" <?php if($wcusage_field_pdfreports_time == $x) { ?>selected<?php } ?>><?php echo esc_attr($x); ?>:00</option>
                  <?php } ?>
            		</select>
            	</p>

            </span>

            <br/><hr/>

            <h3 id="statements-settings"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> General Settings:</h3>

            <?php wcusage_setting_toggle_option('wcusage_field_enable_reports_user_option', 1, 'Give affiliates option to toggle on/off email reports.', '0px'); ?>
            <i><?php echo esc_html__( 'If enabled, an option will be displayed in the affiliate dashboard settings tab, allowing affiliates to turn off the email reports.', 'woo-coupon-usage' ); ?></i>

            <?php wcusage_setting_toggle('.wcusage_field_enable_reports_user_option', '.wcu-field-section-reports-option'); // Show or Hide ?>
            <span class="wcu-field-section-reports-option">

              <br/><br/>

              <?php wcusage_setting_toggle_option('wcusage_field_enable_reports_default', 1, 'Make "Affiliate Reports" enabled by default for all coupon affiliates.', '0px'); ?>
              <i><?php echo esc_html__( 'If enabled, the affiliate reports will be turned on by default for all affiliate users (if they have not yet updated the setting). They can toggle this off in the settings tab of their dashboard. If disabled, then the reports will be turned off by default, and the affiliate user will need to toggle it on to receive the reports.', 'woo-coupon-usage' ); ?></i>

            </span>

            <br/><br/><hr/>

            <h3 id="statements-settings"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Report Customisation:</h3>

              <!-- IMAGE - Statement Header Image -->
              <script>
                  jQuery(document).ready(function($) {
                      $('.report_header_logo_upload').click(function(e) {
                          e.preventDefault();
                          var custom_uploader = wp.media({
                              title: 'Custom Image',
                              button: {
                                  text: 'Upload Image'
                              },
                              multiple: false  // Set this to true to allow multiple files to be selected
                          })
                          .on('select', function() {
                              var attachment = custom_uploader.state().get('selection').first().toJSON();
                              $('.wcusage_field_pdfreport_statements_logo').attr('src', attachment.url);
                              $('.wcusage_field_pdfreport_statements_logo_url').val(attachment.url);
                $('.wcusage_field_pdfreport_statements_logo_url').change();
                // Explicitly trigger AJAX save for report header logo
                if (typeof window.wcu_ajax_update_the_options === 'function') {
                  try {
                    window.wcu_ajax_update_the_options(jQuery('#wcusage_field_pdfreport_statements_logo'), 'id', 'wcu-update-text', 1, '', '');
                  } catch (e) {}
                }
                          })
                          .open();
                      });
                  });
              </script>
              <p>
                <?php $report_statements_logo = wcusage_get_setting_value('wcusage_field_pdfreport_statements_logo', ''); ?>
                <strong><?php echo esc_html__( 'PDF Report Header Image (Logo)', 'woo-coupon-usage' ); ?>:</strong><br/>
                <input class="wcusage_field_pdfreport_statements_logo_url" type="text"
                id="wcusage_field_pdfreport_statements_logo"
                name="wcusage_options['wcusage_field_pdfreport_statements_logo']"
                size="60" value="<?php echo esc_html($report_statements_logo); ?>">
                <a href="#" class="report_header_logo_upload">Upload</a>
                <br/><i><?php echo esc_html__( 'This is shown at the very top of the PDF report (attached to the email). Recommended size is 340 x 70.', 'woo-coupon-usage' ); ?></i><br/>
              </p>

              <br/>

              <!-- IMAGE - Statement Header Image -->
              <script>
                  jQuery(document).ready(function($) {
                      $('.report_header_email_logo_upload').click(function(e) {
                          e.preventDefault();
                          var custom_uploader = wp.media({
                              title: 'Custom Image',
                              button: {
                                  text: 'Upload Image'
                              },
                              multiple: false  // Set this to true to allow multiple files to be selected
                          })
                          .on('select', function() {
                              var attachment = custom_uploader.state().get('selection').first().toJSON();
                              $('.wcusage_field_pdfreport_statements_email_logo').attr('src', attachment.url);
                              $('.wcusage_field_pdfreport_statements_email_logo_url').val(attachment.url);
                $('.wcusage_field_pdfreport_statements_email_logo_url').change();
                // Explicitly trigger AJAX save for report email header logo
                if (typeof window.wcu_ajax_update_the_options === 'function') {
                  try {
                    window.wcu_ajax_update_the_options(jQuery('#wcusage_field_pdfreport_statements_email_logo'), 'id', 'wcu-update-text', 1, '', '');
                  } catch (e) {}
                }
                          })
                          .open();
                      });
                  });
              </script>
              <p>
                <?php $report_statements_email_logo = wcusage_get_setting_value('wcusage_field_pdfreport_statements_email_logo', ''); ?>
                <strong><?php echo esc_html__( 'Custom Email Header Image / Logo', 'woo-coupon-usage' ); ?>:</strong><br/>
                <input class="wcusage_field_pdfreport_statements_email_logo_url" type="text"
                id="wcusage_field_pdfreport_statements_email_logo"
                name="wcusage_options['wcusage_field_pdfreport_statements_email_logo']"
                size="60" value="<?php echo $report_statements_email_logo; ?>">
                <a href="#" class="report_header_email_logo_upload">Upload</a>
                <br/><i><?php echo esc_html__( 'This will replace your default WooCommerce email template "header image" with a custom one for your reports.', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'Leave empty to use your default "header image".', 'woo-coupon-usage' ); ?></i><br/>
              </p>

              <br/>

              <!-- Report Header Text -->
              <style>
              #wcusage_field_pdfreports_text_header_ifr { height: 150px; }
              </style>
              <?php
              $wcusage_field_pdfreports_text_header = wcusage_get_setting_value('wcusage_field_pdfreports_text_header', '');
              wcusage_setting_tinymce_option('wcusage_field_pdfreports_text_header', $wcusage_field_pdfreports_text_header, "Report Header Text", '0px');
              ?>

              <br/>

              <!-- Report Footer Text -->
              <style>
              #wcusage_field_pdfreports_text_footer_ifr { height: 150px; }
              </style>
              <?php
              $wcusage_field_pdfreports_text_footer = wcusage_get_setting_value('wcusage_field_pdfreports_text_footer', '');
              wcusage_setting_tinymce_option('wcusage_field_pdfreports_text_footer', $wcusage_field_pdfreports_text_footer, "Report Footer Text", '0px');
              ?>

              <br/>

              <!-- "Affiliate Report" -->
              <?php $wcusage_field_pdfreports_text_title = wcusage_get_setting_value('wcusage_field_pdfreports_text_title', 'Affiliate Report'); ?>
              <?php wcusage_setting_text_option('wcusage_field_pdfreports_text_title', $wcusage_field_pdfreports_text_title, esc_html__( 'Report Title', 'woo-coupon-usage' ) . ' ("Affiliate Report"):', '0px'); ?>

              <br/>

              <!-- Frequency -->
            	<p>
            		<?php $wcusage_field_pdfreports_stats_align = wcusage_get_setting_value('wcusage_field_pdfreports_stats_align', 'left'); ?>
            		<input type="hidden" value="0" id="wcusage_field_pdfreports_stats_align" data-custom="custom" name="wcusage_options[wcusage_field_pdfreports_stats_align]" >
            		<strong><label for="scales"><?php echo esc_html__( 'Statistics Text Alignment', 'woo-coupon-usage' ); ?>:</label></strong><br/>
            		<select name="wcusage_options[wcusage_field_pdfreports_stats_align]" id="wcusage_field_pdfreports_stats_align">
                  <option value="left" <?php if($wcusage_field_pdfreports_stats_align == "left") { ?>selected<?php } ?>><?php echo esc_html__( 'Left', 'woo-coupon-usage' ); ?></option>
                  <option value="center" <?php if($wcusage_field_pdfreports_stats_align == "center") { ?>selected<?php } ?>><?php echo esc_html__( 'Center', 'woo-coupon-usage' ); ?></option>
                </select>
            	</p>

              <br/>

              <?php wcusage_setting_toggle_option('wcusage_field_reports_show_sales', 1, 'Show "Sales Statistics" Section', '0px'); ?>

              <?php wcusage_setting_toggle('.wcusage_field_reports_show_sales', '.wcu-field-section-reports-text-sales'); // Show or Hide ?>
              <span class="wcu-field-section-reports-text-sales">

                <!-- "Sales Statistics" -->
                <?php $wcusage_field_pdfreports_text_sales = wcusage_get_setting_value('wcusage_field_pdfreports_text_sales', 'Sales Statistics'); ?>
                <?php wcusage_setting_text_option('wcusage_field_pdfreports_text_sales', $wcusage_field_pdfreports_text_sales, esc_html__( 'Custom Text', 'woo-coupon-usage' ) . ":", '0px'); ?>

              </span>

              <br/>

              <?php wcusage_setting_toggle_option('wcusage_field_reports_show_commission', 1, 'Show "Commission Statistics" Section', '0px'); ?>

              <?php wcusage_setting_toggle('.wcusage_field_reports_show_commission', '.wcu-field-section-reports-text-commission'); // Show or Hide ?>
              <span class="wcu-field-section-reports-text-commission">

                <!-- "Commission Statistics" -->
                <?php $wcusage_field_pdfreports_text_commission = wcusage_get_setting_value('wcusage_field_pdfreports_text_commission', 'Commission Statistics'); ?>
                <?php wcusage_setting_text_option('wcusage_field_pdfreports_text_commission', $wcusage_field_pdfreports_text_commission, esc_html__( 'Custom Text', 'woo-coupon-usage' ) . ":", '0px'); ?>

              </span>

              <br/>

              <?php wcusage_setting_toggle_option('wcusage_field_reports_show_referral', 1, 'Show "Referral URL Statistics" Section', '0px'); ?>

              <?php wcusage_setting_toggle('.wcusage_field_reports_show_referral', '.wcu-field-section-reports-text-url'); // Show or Hide ?>
              <span class="wcu-field-section-reports-text-url">

                <!-- "Referral URL Statistics" -->
                <?php $wcusage_field_pdfreports_text_url = wcusage_get_setting_value('wcusage_field_pdfreports_text_url', 'Referral URL Statistics'); ?>
                <?php wcusage_setting_text_option('wcusage_field_pdfreports_text_url', $wcusage_field_pdfreports_text_url, esc_html__( 'Custom Text', 'woo-coupon-usage' ) . ":", '0px'); ?>

              </span>

              <br/><hr/>

              <h3 id="statements-settings"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Example Report:</h3>

              <br/><a class="wcu-open-button" popup-open="wcu-popup-1" href="javascript:void(0)">Click here to send an example report &nbsp;<span class="fas fa-share"></span></a><br/>
              <br/><i><?php echo esc_html__( 'Generate and send an example report to yourself, with the settings/customisations you have set above.', 'woo-coupon-usage' ); ?></i><br/>

      </span>

	</div>

 <?php
}

/**
 * Function to create the form to send a test affiliate report to admin
 *
 */
if( !function_exists( 'wcusage_test_report_form' ) ) {
  function wcusage_test_report_form() {
  ?>

  <?php
  if( isset($_POST['wcu_example_report_coupon']) && isset($_POST['wcu_example_report_email']) ) {
    $wcu_example_report_coupon = sanitize_text_field( $_POST['wcu_example_report_coupon'] );
    $wcu_example_report_email = sanitize_text_field( $_POST['wcu_example_report_email'] );
    wcusage_output_reports($wcu_example_report_coupon, $wcu_example_report_email);
    echo "<p>An example affiliate report was sent to " . esc_html($wcu_example_report_email) . " for coupon: ".esc_html($wcu_example_report_coupon)."</p>";
  }
  ?>

  <div class="wcu-popup" popup-name="wcu-popup-1">
      <div class="wcu-popup-content">

        <form method="post" id="example_report_form" style="width: 100%; display: inline-block; margin: 0 auto;">

          <h3 id="statements-settings"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Send an example affiliate report:</h3>

    			<p><strong>Coupon Code:</strong>
          <br/>
          <input type="text" id="wcu_example_report_coupon" name="wcu_example_report_coupon" checktype="ignore">
          <i>Which coupon should it show statistics for?</i>
          </p>

          <p><strong>Email Address:</strong>
          <br/>
          <input type="text" id="wcu_example_report_email" name="wcu_example_report_email" checktype="ignore">
          <i>Which email address should the example report be sent to?</i>
          </p>

          <br/>

          <button onclick="examplereport_form_submit()" name='submitexamplereport' class="wcu-open-button">Send Example Report</button>

          <script type="text/javascript">
          function examplereport_form_submit() {
            document.getElementById("example_report_form").submit();
           }
          </script>

          <br/><br/>

    		</form>

        <a class="wcu-close-button" popup-close="wcu-popup-1" href="javascript:void(0)">x</a>

      </div>
  </div>

  <style>
  /* Popup Open button */
  .wcu-open-button {
      color:#FFF;
      background:#0066CC;
      padding:10px;
      text-decoration:none;
      border:1px solid #0157ad;
      border-radius:3px;
      cursor: pointer;
  }
  .wcu-open-button:hover{
      background: #01478e;
      color: #fff;
  }

  .wcu-popup {
      position:fixed;
      top:0px;
      left:0px;
      background:rgba(0,0,0,0.75);
      width:100%;
      height:100%;
      display:none;
      z-index: 99999;
  }

  /* Popup inner div */
  .wcu-popup-content {
      width: 300px;
      margin: 0 auto;
      box-sizing: border-box;
      padding: 0px;
      margin-top: 100px;
      box-shadow: 0px 2px 6px rgba(0,0,0,1);
      border-radius: 20px;
      background: #fff;
      position: relative;
  }

  /* Popup close button */
  .wcu-close-button {
      width: 25px;
      height: 25px;
      position: absolute;
      top: -10px;
      right: -50px;
      border-radius: 20px;
      background: rgba(0,0,0,0.8);
      font-size: 20px;
      text-align: center;
      color: #fff;
      text-decoration:none;
  }

  .wcu-close-button:hover {
      background: rgba(0,0,0,1);
  }

  @media screen and (max-width: 720px) {
  .wcu-popup-content {
      width:90%;
      }
  }
  </style>

  <script>
  jQuery(function() {
      // Open Popup
      jQuery('[popup-open]').on('click', function() {
          var popup_name = jQuery(this).attr('popup-open');
  	jQuery('[popup-name="' + popup_name + '"]').fadeIn(300);
      });

      // Close Popup
      jQuery('[popup-close]').on('click', function() {
  	var popup_name = jQuery(this).attr('popup-close');
  	jQuery('[popup-name="' + popup_name + '"]').fadeOut(300);
      });

      // Close Popup When Click Outside
      jQuery('.popup').on('click', function() {
  	var popup_name = jQuery(this).find('[popup-close]').attr('popup-close');
  	jQuery('[popup-name="' + popup_name + '"]').fadeOut(300);
      }).children().click(function() {
  	return false;
      });

  });
  </script>

  <?php
  }
}
