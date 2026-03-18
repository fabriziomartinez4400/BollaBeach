<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Displays setup page.
 *
 */
function wcusage_setup_page_html() {
  // check user capabilities
  if ( ! wcusage_check_admin_access() ) {
  return;
  }

  do_action('wcusage_hook_setup_page_update'); // Update on Post

  $options = get_option( 'wcusage_options' );

  if(isset($_GET['step'])) {
    $step = $_GET['step'];
  } else {
    $step = "";
  }
  ?>

  <style>
  .admin_page_wcusage_setup {
    background: -moz-linear-gradient(top, #f3f3f3 0%, #e8fff0 100%);
    background: -webkit-linear-gradient(top, #f3f3f3 0%, #e8fff0 100%);
    background: linear-gradient(to bottom, #f3f3f3 0%, #e8fff0 100%);
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;
  }
  .wp-admin #wpcontent .notice, .wp-admin #wpcontent .updated, .wp-admin #wpcontent .success {
    display: none !important;
  }
  </style>

  <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

  <div class="wrap plugin-setup-settings">

    <center>

      <a href="https://couponaffiliates.com?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=logo"
      target="_blank" style="display: inline-block;">
        <img src="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL); ?>images/coupon-affiliates-logo.png"
        style="display: inline-block; width: 100%; max-width: 400px; text-align: left; margin: 25px 0 10px 0;">
      </a>

    </center>

    <div class="bar-container">
      <ul class="progressbar">
        <li class="<?php if(!$step || $step >= "1") { ?>active<?php } ?><?php if(!$step || $step == "1") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=1"><?php echo esc_html__('Dashboard', 'woo-coupon-usage'); ?></a>
        </li>
        <li class="<?php if($step >= "2") { ?>active<?php } ?><?php if($step == "2") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=2"><?php echo esc_html__('Registration', 'woo-coupon-usage'); ?></a>
        </li>
        <li class="<?php if($step >= "3") { ?>active<?php } ?><?php if($step == "3") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=3"><?php echo esc_html__('Commission', 'woo-coupon-usage'); ?></a>
        </li>
        <?php if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) ) { ?>
        <li class="<?php if($step >= "4") { ?>active<?php } ?><?php if($step == "4") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=4"><?php echo esc_html__('Taxes', 'woo-coupon-usage'); ?></a>
        </li>
        <?php } ?>
        <li class="<?php if($step >= "5") { ?>active<?php } ?><?php if($step == "5") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=5"><?php echo esc_html__('Emails', 'woo-coupon-usage'); ?></a>
        </li>
        <li class="<?php if($step >= "6") { ?>active<?php } ?><?php if($step == "6") { ?> current<?php } ?>">
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=6"><?php echo esc_html__('Finish', 'woo-coupon-usage'); ?></a>
        </li>
      </ul>
    </div>

    <?php
    if ( !class_exists( 'WooCommerce' ) ) {
      // Check if WooCommerce is installed
      $path = 'woocommerce/woocommerce.php';
      $installed_plugins = get_plugins();
      // WooCommerce is installed but not active
      if( isset( $installed_plugins[ $path ] ) ) {
        $activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $path, 'activate-plugin_' . $path );
        echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce is installed but not activated. <a href="' . esc_url($activate_url) . '">Click here to activate it.</a></strong></p>';
      }
      // WooCommerce is not installed
      else {
        $install_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce' );
        echo '<br/><p style="text-align: center; display: block; margin: 20px auto; font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce needs to be installed for this plugin to work. <a href="' . esc_url($install_url) . '">Click here to install it.</a></strong></p>';
      }
    }
    ?>

    <div class="wcusage_row wcusage-settings-form" style="width: 100%;<?php if ( !class_exists( 'WooCommerce' ) ) { ?>display: none;<?php } ?>">
      <div style="display: block; width: 800px; max-width: calc(100% - 50px); margin: 20px auto; padding: 15px 25px; background: #FFF; border: 2px solid #e3e3e3; border-radius: 10px;">

        <!-- Step 1 -->

        <?php if(!$step || $step == "1") { ?>

          <p style="font-size: 20px;">
            <strong><?php echo esc_html__('Welcome to the Coupon Affiliates setup wizard!', 'woo-coupon-usage'); ?></strong>
          </p>

          <p>
            <?php echo esc_html__('We are going to run you through some of the most important settings in the Coupon Affiliates plugin, to help you get everything setup!', 'woo-coupon-usage'); ?>
            <strong><?php echo sprintf( wp_kses_post( __('You will be able to customise more options in the <a href="%s" target="_blank">settings page</a> later.', 'woo-coupon-usage') ), esc_url(get_admin_url()) . 'admin.php?page=wcusage_settings'); ?></strong>
            <strong><?php echo esc_html__('Lets get started...', 'woo-coupon-usage'); ?></strong>
          </p>

          <hr style="margin: 20px 0;" />

          <div class="affiliate-dashboard-page-settings">

          <h3><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__( 'Affiliate Dashboard Page', 'woo-coupon-usage' ); ?>:</h3>

          <p>
            <?php echo esc_html__('Firstly, we need to create the main affiliate dashboard page on your website.', 'woo-coupon-usage'); ?>
          </p>

          <?php
          $coupon_shortcode_page = wcusage_get_coupon_shortcode_page('0');
          if(!$coupon_shortcode_page) {
            ?>
            <p>
                <?php echo esc_html__('Click the button below to automatically generate the page:', 'woo-coupon-usage'); ?>
            </p>
            <?php
            do_action('wcusage_hook_getting_started_create');
            if(!isset( $_POST['submitnewpage2'] )) {
              do_action('wcusage_hook_getting_started3');
            }
            ?>
            <p style="margin: 0;">
              <?php echo sprintf( esc_html__('Alternatively, you can add the %s shortcode to a new page, then select the page from the dropdown below.', 'woo-coupon-usage'), '[couponaffiliates]'); ?>
            </p>
            <?php
          }
          ?>

          </div>

          <form action="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=2" method="post">

            <?php do_action( 'wcusage_hook_setting_section_dashboard_page' ); ?>

            <hr style="margin: 25px 0;" />

            <button type="submit" name="submit_step1" id="submit_step1" class="button button-primary"
            style="padding: 5px 20px; margin-bottom: 0px; font-size: 15px;"><?php echo esc_html__('Save & Continue', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></button>

          </form>

        <?php } // End Step 1 ?>

        <!-- Step 2 -->
        <?php if($step == "2") {
          
          // wcusage_field_portal_enable
          $wcusage_field_portal_enable = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
          if($wcusage_field_portal_enable) {
              $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
              add_rewrite_rule('^' . $wcusage_portal_slug . '/?$', 'index.php?affiliate_portal=1', 'top');
          }
          flush_rewrite_rules();
          ?>

          <form action="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=3" method="post">

            <h3><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__( 'Affiliate Registration System', 'woo-coupon-usage' ); ?>:</h3>

            <p>
              <?php echo esc_html__('Next, we need to setup the affiliate registration system.', 'woo-coupon-usage'); ?>
              <?php echo esc_html__('This will allow users to register as affiliates on your website. Once accepted, it will automatically generate their new account, create their affiliate coupon, and assign them to it, so they can access the dashboard.', 'woo-coupon-usage'); ?>
            </p>

            <hr style="margin: 20px 0;">

            <!-- Enable Affiliate Registration Features -->
            <?php wcusage_setting_toggle_option('wcusage_field_registration_enable', 1, esc_html__( 'Enable Affiliate Registration Features', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'This will enable the coupon affiliate registration features on your website.', 'woo-coupon-usage' ); ?></i><br/>

            <?php wcusage_setting_toggle('.wcusage_field_registration_enable', '.wcu-field-section-registration-settings'); // Show or Hide ?>
            <span class="wcu-field-section-registration-settings">

              <br/>

              <p>
                <?php echo esc_html__('Next, you need to create the affiliate registration page on your website.', 'woo-coupon-usage'); ?>
              </p>

              <p>
                <?php echo sprintf( esc_html__('Simply add the %s shortcode to a new page, then select the page from the dropdown below.', 'woo-coupon-usage'), '[couponaffiliates-register]'); ?>
              </p>

              <?php
              $registration_shortcode_page = wcusage_get_registration_shortcode_page_id();
              if(!$registration_shortcode_page) {
                do_action('wcusage_hook_getting_started_registration_post');
                if(!isset($_GET['action'])) {
                ?>
                <p>
                    <?php echo esc_html__('Or you can click the button below to automatically generate the page for you:', 'woo-coupon-usage'); ?>
                </p>
                <?php
                  do_action('wcusage_hook_getting_started_registration');
                }
              }
              ?>

              <script>
              jQuery(document).ready(function($) {
                // Only require the template coupon if registration system is enabled.
                function wcusage_toggle_template_required() {
                  var $template = $('#wcusage_field_registration_coupon_template');
                  // Attempt to find the registration enable toggle by id or name.
                  var $enable = $('#wcusage_field_registration_enable');
                  if(!$enable.length) {
                    $enable = $("[name='wcusage_options[wcusage_field_registration_enable]']");
                  }
                  var $submit = $('#submit_step2');
                  var enabled = false;
                  if($enable.length) {
                    if($enable.is(':checkbox')) {
                      enabled = $enable.is(':checked');
                    } else {
                      enabled = ($enable.val() === '1');
                    }
                  }
                  if(enabled) {
                    $template.prop('required', true);
                    $template.trigger('change');
                  } else {
                    $template.prop('required', false);
                    if($submit.length) {
                      $submit.prop('disabled', false).removeClass('disabled');
                    }
                  }
                }
                wcusage_toggle_template_required();
                jQuery(document).on('change', '#wcusage_field_registration_enable, [name="wcusage_options[wcusage_field_registration_enable]"]', wcusage_toggle_template_required);
              });
              </script>

              <br/>

              <?php do_action( 'wcusage_hook_setting_section_registration_page' ); ?>

              <br/><hr style="margin: 15px 0 25px 0;"/>

              <!-- Template Coupon -->
              <?php do_action( 'wcusage_hook_setting_section_registration_template' ); ?>

              <br/><hr style="margin: 15px 0 25px 0;"/>

              <?php echo esc_html__('Note: You can customise the registration system more in the plugin settings page.', 'woo-coupon-usage'); ?>

            </span>

            <hr style="margin: 25px 0;" />

            <button type="submit" name="submit_step2" id="submit_step2" class="button button-primary"
            style="padding: 5px 20px; margin-bottom: 0px; font-size: 15px;"><?php echo esc_html__('Save & Continue', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></button>

          </form>

        <?php } // End Step 2 ?>

        <!-- Step 3 -->
        <?php if($step == "3") {
          
          flush_rewrite_rules();
          ?>

          <form action="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=<?php if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) ) { ?>4<?php } else { ?>5<?php } ?>" method="post">

            <!-- Commission -->
            <h3><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__( 'Commission Amounts', 'woo-coupon-usage' ); ?>:</h3>
            <?php do_action( 'wcusage_hook_setting_section_commission_amounts' ); ?>

            <hr style="margin: 25px 0 25px 0;" />

            <button type="submit" name="submit_step3" id="submit_step3" class="button button-primary"
            style="padding: 5px 20px; margin-bottom: 0px; font-size: 15px;"><?php echo esc_html__('Save & Continue', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></button>

          </form>

        <?php } // End Step 3 ?>

        <!-- Step 4 -->
        <?php if($step == "4") { ?>

          <form action="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=5" method="post">

            <!-- Tax -->
            <h3><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__( 'Tax Settings', 'woo-coupon-usage' ); ?>:</h3>

            <?php do_action( 'wcusage_hook_setting_section_tax' ); ?>

            <br/>

            <p><?php echo esc_html__('Note: If required, you can customise more calculation settings in the plugin settings page.', 'woo-coupon-usage'); ?></p>

            <hr style="margin: 25px 0 25px 0;" />

            <button type="submit" name="submit_step4" id="submit_step4" class="button button-primary"
            style="padding: 5px 20px; margin-bottom: 0px; font-size: 15px;"><?php echo esc_html__('Save & Continue', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></button>

          </form>

        <?php } // End Step 4 ?>

        <!-- Step 5 -->
        <?php if($step == "5") { ?>

          <form action="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_setup&step=6" method="post">

            <h3><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__('General Email Settings', 'woo-coupon-usage'); ?>:</h3>
            
            <p style="margin-bottom: 20px;"><?php echo esc_html__('Finally, we need to setup the email notifications for your affiliate program.', 'woo-coupon-usage'); ?></p>

            <?php do_action('wcusage_hook_setting_section_email_free'); ?>

            <p>
              <?php echo esc_html__('Note: You can customise some other email notifications on the plugin settings page later if needed.', 'woo-coupon-usage'); ?>
            </p>

            <hr style="margin: 25px 0 25px 0;" />

            <button type="submit" name="submit_step5" id="submit_step5" class="button button-primary"
            style="padding: 5px 20px; margin-bottom: 0px; font-size: 15px;"><?php echo esc_html__('Save & Continue', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></button>

          </form>

        <?php } // End Step 5 ?>

        <!-- Step 6 -->
        <?php if($step == "6") {
          
          // Save option "wcusage_setup_complete"
          update_option( 'wcusage_setup_complete', '1' );

          // Populate any remaining default settings not yet stored.
          if ( function_exists( 'wcusage_get_all_default_settings' ) ) {
            wcusage_get_all_default_settings();
          }
          // Fire a hook so extensions can react after defaults are ensured.
          do_action( 'wcusage_setup_completed' );
          ?>

          <h1><?php echo esc_html__('Setup Wizard Complete!', 'woo-coupon-usage'); ?></h1>

          <p style="font-weight: bold;"><?php echo wp_kses_post( __('You\'re almost ready to launch your affiliate program, and start growing your revenue!', 'woo-coupon-usage') ); ?></p>

          <p><?php echo wp_kses_post( __('Here\'s some of the next steps you can take:', 'woo-coupon-usage') ); ?></p>

          <style>
            .steps-container {
              display: flex;
              flex-wrap: wrap;
              gap: 20px;
              margin: 20px 0;
            }
            .step-box {
              flex: 1 1 calc(33.333% - 14px);
              background: #f8f9fa;
              border: 1px solid #e0e0e0;
              border-radius: 8px;
              padding: 20px;
              box-shadow: 0 2px 4px rgba(0,0,0,0.1);
              transition: transform 0.2s;
              min-width: 250px;
            }
            .step-box:hover {
              transform: translateY(-5px);
              box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }
            .step-box h3 {
              margin: 0 0 10px;
              font-size: 16px;
              color: #333;
            }
            .step-box p {
              margin: 0;
              font-size: 14px;
              line-height: 1.5;
            }
            .step-number {
              font-weight: bold;
              color: #0073aa;
            }
          </style>

          <div class="steps-container">
            <div class="step-box">
              <h3><span class="step-number">1)</span> Customize Your Program</h3>
              <p><?php echo sprintf( wp_kses_post( __('Visit the <a href="%s" target="_blank">settings page</a> to edit more options, enable more features, and customise your affiliate program to work exactly how you want!', 'woo-coupon-usage') ), esc_url(get_admin_url()) . 'admin.php?page=wcusage_settings'); ?></p>
            </div>

            <div class="step-box">
              <h3><span class="step-number">2)</span> Manage Coupons</h3>
              <p><?php echo sprintf( wp_kses_post( __('View and manage all of your affiliate coupons, and access links to each of their affiliate dashboards on the <a href="%s" target="_blank">coupons list</a> page.', 'woo-coupon-usage') ), esc_url(admin_url("admin.php?page=wcusage_coupons"))); ?></p>
            </div>

            <?php
            $template = $options['wcusage_field_registration_coupon_template'];
            $get_template = wcusage_get_coupon_info($template);
            $template_id = $get_template[2];
            $registrationpage = "";
            if ( isset($options['wcusage_registration_page']) && $options['wcusage_registration_page'] ) {
                $registrationpage = $options['wcusage_registration_page'];
            } else {
                $registrationpage = wcusage_get_registration_shortcode_page_id();
            }
            ?>
            <div class="step-box">
              <h3><span class="step-number">3)</span> Template Coupon</h3>
              <?php if($template_id) { ?>
                <p>
                  <?php echo sprintf( wp_kses_post( __('You can <a href="%s" target="_blank">edit your template coupon</a> if you want to change the default affiliate coupon settings.', 'woo-coupon-usage') ), esc_url(admin_url("post.php?post=" . $template_id . "&action=edit"))); ?>
                  <?php echo sprintf( wp_kses_post( __('This is the template coupon used for generating new affiliate coupons when a new affiliate is created.', 'woo-coupon-usage') ), esc_url(admin_url("admin.php?page=wcusage_coupon_stats&coupon_id=" . $template_id))); ?>
                </p>
              <?php } else { ?>
                <p><?php echo sprintf( wp_kses_post( __('Don\'t forget to <a href="%s" target="_blank">create your template coupon</a> and set this in the plugin settings! <a href="%s" target="_blank">Learn More</a>.', 'woo-coupon-usage') ), esc_url(admin_url("post-new.php?post_type=shop_coupon")), 'https://couponaffiliates.com/docs/template-coupon-code/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step'); ?></p>
              <?php } ?>
            </div>

            <div class="step-box">
              <h3><span class="step-number">4)</span> Add New Affiliates</h3>
              <p><?php echo sprintf( wp_kses_post( __('Ready to get started? Create your first affiliate user on the <a href="%s" target="_blank">affiliates page</a> or share your <a href="%s" target="_blank">affiliate registration form</a> with people to signup. Any new affiliate registrations will auto-create their new coupon code.', 'woo-coupon-usage') ), esc_url(admin_url("admin.php?page=wcusage_affiliates")), esc_url(get_permalink($registrationpage)) ); ?></p>
            </div>

            <div class="step-box">
              <h3><span class="step-number">5)</span> Explore PRO Features</h3>
              <p><?php echo sprintf( wp_kses_post( __('For advanced features like automated payouts, multi-level affiliates, dynamic creatives, performance bonuses, affiliate groups, email reports, and more, visit the <a href="%s" target="_blank">PRO modules section</a>.', 'woo-coupon-usage') ), esc_url(admin_url('admin.php?page=wcusage_settings&section=tab-pro-details'))); ?></p>
            </div>

            <?php if( wcu_fs()->can_use_premium_code() ) { ?>
            <!-- Payouts -->
            <div class="step-box">
              <h3><span class="step-number">6)</span> Commission Payouts Settings</h3>
              <p>
                <?php echo sprintf( wp_kses_post( __('Setup your commission payout methods and settings on the <a href="%s" target="_blank">payouts settings page</a>. You can pay your affiliates via PayPal, Stripe, or Store Credit, and even automate payouts to be paid automatically on a scheduled basis.', 'woo-coupon-usage') ), esc_url(admin_url('admin.php?page=wcusage_settings&section=tab-payouts'))); ?>
                <a href="https://couponaffiliates.com/docs/commission-tracking-and-payouts/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step" target="_blank"><?php echo esc_html__('Learn more about payouts.', 'woo-coupon-usage'); ?></a>
              </p>
            </div>
            <?php } ?>

          </div>

          <p><strong><?php echo sprintf( wp_kses_post( __('Be sure to watch the setup guide video below, for a detailed walkthrough.', 'woo-coupon-usage') ), esc_url(admin_url('admin.php?page=wcusage_contact'))); ?>
          <?php echo wp_kses_post(__('Need help?', 'woo-coupon-usage')); ?> <?php if ( wcu_fs()->can_use_premium_code() ) { ?><a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage-contact" target="_blank"><?php } else { ?><a href="https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0" target="_blank" style="text-decoration: none;"><?php } ?><?php echo esc_html__('Create a new support ticket', 'woo-coupon-usage'); ?></a>.</strong><br/></p>

          <br/>

          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_settings">
            <button type="submit" class="button button-primary" style="padding: 7px 20px;">
              <?php echo esc_html__('Continue to Settings Page', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>
          &nbsp;
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_add_affiliate">
            <button type="submit" class="button button-secondary" style="padding: 7px 20px;">
              <?php echo esc_html__('Create Your First Affiliate', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>

          <br/><br/>

          <hr style="margin: 20px 0 20px 0;" />

          <h1><?php echo esc_html__('How To Get Started', 'woo-coupon-usage'); ?>:</h1>

          <p style="font-weight: bold;"><?php echo esc_html__('Here is a video guide explaining how to get started with the plugin', 'woo-coupon-usage'); ?>...</p>

          <br/>

          <iframe width="560" height="315" src="https://www.youtube.com/embed/64Sub5pKf7k?si=oS-OgpSonXAflh8p" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
          
          <br/>

          <p style="margin: 0 0 10px 0;"><a href="https://couponaffiliates.com/docs/setup-guide-free/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step" target="_blank" style="text-decoration: none;"><?php echo esc_html__('Open setup guide in new tab', 'woo-coupon-usage'); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>

          <br/>

          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_settings">
            <button type="submit" class="button button-primary" style="padding: 7px 20px;">
              <?php echo esc_html__('Continue to Settings Page', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>
          &nbsp;
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_add_affiliate"
          <button type="submit" class="button button-secondary" style="padding: 7px 20px;">
              <?php echo esc_html__('Create Your First Affiliate', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>

          <br/><br/>

          <hr style="margin: 20px 0 20px 0;" />

          <h1><?php echo esc_html__('Frequently Asked Questions:', 'woo-coupon-usage'); ?></h1>

          <?php
          $support_url = 'https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0';
          if ( wcu_fs()->can_use_premium_code() ) {
              $support_url = admin_url('admin.php?page=wcusage-contact');
          }
          
          $questions = array(
              array(
                  'answer' => sprintf(__('Coupon Affiliates enhances affiliate marketing by integrating coupons as a core feature. Unlike traditional affiliate systems that rely primarily on referral links, this plugin allows affiliates to share their unique coupon codes. These codes not only track sales for commission purposes but also offer customers immediate discounts or special offers, enriching the purchasing experience. However, the Coupon Affiliates system still maintains the flexibility of referral links; affiliates can share URLs that automatically apply their coupon at checkout, and even if a coupon isn\'t used, you can <a href="%s" target="_blank">still make the link alone track referrals</a>, ensuring affiliates receive credit for every sale referred.', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/tracking-conversions-via-referral-url-without-coupons/'),
                  'question' => __('What is a "coupon-based" affiliate program?', 'woo-coupon-usage'),
              ),
              array(
                  'id' => 'registration',
                  'question' => __('How do I create affiliate users?', 'woo-coupon-usage'),
                  'answer' => sprintf(__('You can manually create an affiliate by <a href="%s" target="_blank">clicking here</a>.<br/><br/>You can add either an existing or new user. Alternatively, you can also link users to your <a href="%s" target="_blank">affiliate registration page</a> to submit an affiliate application. When an affiliate user is added, this will automatically create the affiliate coupon, assign the user to it, and send them a link to the affiliate dashboard. <a href="%s" target="_blank">Learn more about affiliate registration.</a>', 'woo-coupon-usage'), esc_url(admin_url('admin.php?page=wcusage_add_affiliate')), get_permalink($registrationpage), 'https://couponaffiliates.com/docs/affiliate-registration/'),
              ),
              array(
                  'id' => 'payouts',
                  'question' => __('How do I pay my affiliates?', 'woo-coupon-usage'),
                  'answer' => sprintf(__('With the free version, you will need to manually handle payouts and payment details. There are a few ways that you can do this, such as generating a detailed admin report. <a href="%s">Click here to learn more.</a>', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/how-to-pay-affiliates-free-version/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step'),
                  'answerpro' => sprintf(__('You can easily pay your affiliates via a variety of different payout methods including one-click Stripe, PayPal, or Store Credit, along with manual Bank Transfer payouts, or any custom manual payout method of your own. You can even automate payouts to be paid automatically on a scheduled basis, collect invoices, generate PDF statements, and more. <a href="%s" target="_blank">%s</a>', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/commission-tracking-and-payouts/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step', __('Learn more about payouts.', 'woo-coupon-usage')),
              ),
              array(
                  'question' => __('How are referral URLs generated?', 'woo-coupon-usage'),
                  'answer' => __('Referral URLs can be easily generated by the affiliate (or you) on the coupon affiliate dashboard. They can customise the link, and if enabled, also generate short URLs, QR codes, campaigns, and more.', 'woo-coupon-usage')
                  . '<br/><br/>' . __('By default, the referral URLs will automatically apply the affiliates coupon code to the customers cart when clicked. The affiliate can view their referral link clicks and conversions on the affiliate dashboard.', 'woo-coupon-usage')
                  . '<br/><br/>' . sprintf(__('If needed, you can <a href="%s" target="_blank">make the link alone track referrals</a> without requiring the affiliates coupon to be applied, ensuring affiliates receive credit for every sale referred.', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/tracking-conversions-via-referral-url-without-coupons/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step')
                  . '<br/><br/>' . sprintf(__('<a href="%s" target="_blank">Learn more about referral URLs.</a>', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/referral-urls/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step'),
              ),
              array(
                  'question' => __('How do I give affiliate users access to their own dashboard?', 'woo-coupon-usage'),
                  'answer' => __('When you create an affiliate user, or accept an affiliate registration, their coupon code is automatically created and assigned to them. They will automatically be sent an email which includes a link to their dashboard. You can customize this email in the plugin settings.', 'woo-coupon-usage'),
              ),
              array(
                  'question' => __('Can I view the dashboard for each of my affiliates?', 'woo-coupon-usage'),
                  'answer' => sprintf(__('Yes, you can view the dashboard for each of your affiliates\' coupons by going to the <a href="%s" target="_blank">coupons page</a> and click the "Dashboard" link.', 'woo-coupon-usage'), esc_url(admin_url("admin.php?page=wcusage_coupons"))),
              ),
              array(
                  'question' => __('Where can I get plugin support?', 'woo-coupon-usage'),
                  'answer' => sprintf(__('If you need help getting started, or have any questions at all, you can <a href="%s">create a support ticket</a> any time, and we will be happy to help.', 'woo-coupon-usage'), $support_url)
                  . '<br/><br/>' . sprintf(__(' You can also view the <a href="%s">plugin documentation</a> for more information on all the plugins features.', 'woo-coupon-usage'), 'https://couponaffiliates.com/docs/?utm_campaign=plugin&utm_source=setup-wizard-link&utm_medium=final-step'),
              ),
          );        
          ?>

          <?php foreach ($questions as $index => $qa): ?>
              <h2 style="margin-top: 24px; cursor: pointer;">
                  <span class="faq-question" id="faq-<?php echo esc_html($index); ?>">
                    - <?php echo esc_html($qa['question']); ?> <span class="fa-solid fa-arrow-down"></span>
                  </span>
              </h2>
              <div class="faq-answer" style="margin-bottom: 20px;">
                <?php
                if( !isset($qa['answerpro']) || !wcu_fs()->can_use_premium_code() ) { ?>
                  <?php if( isset($qa['answerpro']) && !wcu_fs()->can_use_premium_code() ) { ?><?php echo esc_html__('Free Version:', 'woo-coupon-usage'); ?> <?php } ?>
                  <?php echo wp_kses_post($qa['answer']); ?>
                <?php } ?>
                <?php if( isset($qa['answerpro']) ) { ?>
                  
                  <?php if( !wcu_fs()->can_use_premium_code() ) { ?><br/><br/><?php echo esc_html__('Pro Version:', 'woo-coupon-usage'); ?> <?php } ?>
              
                  <?php echo wp_kses_post($qa['answerpro']); ?>

                  <?php if(isset($qa['id']) && $qa['id'] == 'payouts') { ?>
                    <br/><br/><strong><?php echo esc_html__('Commission payouts setup guide (PRO):', 'woo-coupon-usage'); ?></strong>
                    <br/><br/>
                    <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/837140385?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
                  <?php } ?>
                  <?php } ?>

              </div>
          <?php endforeach; ?>

          <script>
          jQuery('.faq-answer').hide();
          jQuery(document).ready(function($) {
              $('.faq-question').click(function() {
                if ($(this).find('.fa-solid').hasClass('fa-arrow-down')) {
                  $(this).find('.fa-solid').removeClass('fa-arrow-down');
                  $(this).find('.fa-solid').addClass('fa-arrow-up');
                } else {
                  $(this).find('.fa-solid').removeClass('fa-arrow-up');
                  $(this).find('.fa-solid').addClass('fa-arrow-down');
                }
                const index = $(this).attr('id').split('-')[1];
                $('.faq-answer:eq(' + index + ')').slideToggle();

                // Pause video in .faq-answer
                $('.faq-answer').each(function() {
                  var src = $(this).find('iframe').attr('src');
                  $(this).find('iframe').attr('src', '');
                  $(this).find('iframe').attr('src', src);
                });

              });
          });
          </script>

          <br/>

          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_settings">
            <button type="submit" class="button button-primary" style="padding: 7px 20px;">
              <?php echo esc_html__('Continue to Settings Page', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>
          &nbsp;
          <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_add_affiliate"
          <button type="submit" class="button button-secondary" style="padding: 7px 20px;">
              <?php echo esc_html__('Create Your First Affiliate', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
            </button></a>
            
            <?php if( !wcu_fs()->can_use_premium_code() ) { ?>

              <br/><br/><hr style="margin: 20px 0 20px 0;" />

              <h1><?php echo esc_html__('Want more advanced features?', 'woo-coupon-usage'); ?></h1>

              <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage-pricing">
                <button type="submit"class="button button-primary" style="background: #40965d; border: 1px solid #333; padding: 5px 20px; font-size: 15px; margin-top: 10px;">
                  <?php echo esc_html__('Upgrade to Pro', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span>
                </button>
              </a>

              <?php
              // Black Friday Deal
              $todayDate = strtotime('now');
              $dealDateBegin = strtotime('15-11-2025');
              $dealDateEnd = strtotime('30-11-2025');
              if ($todayDate >= $dealDateBegin && $todayDate <= $dealDateEnd) { $specialsale = true; } else { $specialsale = false; }
              ?>
              <?php if($specialsale) { ?>
                <br/><br/>
                <strong style="color: #ce1a1a; font-size: 14px;"><span class="fas fa-star fa-spin"></span> Black Friday Sale! 30% off with code: BF2025</strong>
              <?php } ?>

            <?php } ?>

          <br/>
          

        <?php } // End Step 6 ?>

        <br/>

      </div>
    </div>

    <br/>

    <p style="text-align: center; font-size: 12px;"><?php echo esc_html__('Note: There are lots more options available in the settings page.', 'woo-coupon-usage'); ?></p>

    <p style="text-align: center; font-weight: bold;">
      <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage_settings" style="font-size: 15px; text-decoration: none;"><?php echo esc_html__('Skip Setup Wizard / Go To Settings Page', 'woo-coupon-usage'); ?> <span class="fa-solid fa-circle-arrow-right"></span></a>
    </p>

    <br/>

  </div>

  <?php
}

/**
 * Updates setup page options on each step.
 *
 */
add_action( 'wcusage_hook_setup_page_update', 'wcusage_setup_page_update' );
function wcusage_setup_page_update() {

  // check user capabilities
  if ( ! wcusage_check_admin_access() ) {
  return;
  }

  $option_group = get_option('wcusage_options');

  // 1
  if( isset( $_POST['submit_step1'] ) ) {

    // wcusage_dashboard_page
    if( isset( $_POST['wcusage_options']['wcusage_dashboard_page'] ) ) {
      $option_group['wcusage_dashboard_page'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_dashboard_page'] );
    }

    // wcusage_field_portal_enable
    if( isset( $_POST['wcusage_options']['wcusage_field_portal_enable'] ) ) {
      $option_group['wcusage_field_portal_enable'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_portal_enable'] );
      if( $option_group['wcusage_field_portal_enable'] == 1 ) {
        $wcusage_portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal');
        add_rewrite_rule('^' . $wcusage_portal_slug . '/?$', 'index.php?affiliate_portal=1', 'top');
      }
      flush_rewrite_rules();
    }

    // wcusage_portal_title
    if( isset( $_POST['wcusage_options']['wcusage_portal_title'] ) ) {
      $option_group['wcusage_portal_title'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_portal_title'] );
    }

    // wcusage_portal_slug
    if( isset( $_POST['wcusage_options']['wcusage_portal_slug'] ) ) {
      $option_group['wcusage_portal_slug'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_portal_slug'] );
    }

    // wcusage_portal_logo
    if( isset( $_POST['wcusage_options']['wcusage_portal_logo'] ) ) {
      $option_group['wcusage_portal_logo'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_portal_logo'] );
    }

    // wcusage_portal_footer_text
    if( isset( $_POST['wcusage_options']['wcusage_portal_footer_text'] ) ) {
      // Save tinyMCE content
      $option_group['wcusage_portal_footer_text'] = wp_kses_post( $_POST['wcusage_options']['wcusage_portal_footer_text'] );
    }

    // wcusage_portal_dark_mode
    if( isset( $_POST['wcusage_options']['wcusage_portal_dark_mode'] ) ) {
      $option_group['wcusage_portal_dark_mode'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_portal_dark_mode'] );
    }

  }

  // 2
  if( isset( $_POST['submit_step2'] ) ) {

    if( isset( $_POST['wcusage_options']['wcusage_field_registration_enable'] ) ) {
      $option_group['wcusage_field_registration_enable'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_registration_enable'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_registration_page'] ) || $_POST['wcusage_options']['wcusage_registration_page'] == "" ) {
      $option_group['wcusage_registration_page'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_registration_page'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_registration_coupon_template'] ) || $_POST['wcusage_options']['wcusage_field_registration_coupon_template'] == "" ) {
      $option_group['wcusage_field_registration_coupon_template'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_registration_coupon_template'] );
    }

  }

  // 3
  if( isset( $_POST['submit_step3'] ) ) {

    if( isset( $_POST['wcusage_options']['wcusage_field_affiliate'] ) ) {
      $option_group['wcusage_field_affiliate'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_affiliate'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_affiliate_fixed_order'] ) ) {
      $option_group['wcusage_field_affiliate_fixed_order'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_affiliate_fixed_order'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_affiliate_fixed_product'] ) ) {
      $option_group['wcusage_field_affiliate_fixed_product'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_affiliate_fixed_product'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_affiliate_custom_message'] ) ) {
      $option_group['wcusage_field_affiliate_custom_message'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_affiliate_custom_message'] );
    }

  }

  // 4
  if( isset( $_POST['submit_step4'] ) ) {

    if( isset( $_POST['wcusage_options']['wcusage_field_commission_before_discount'] ) ) {
      $option_group['wcusage_field_commission_before_discount'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_commission_before_discount'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_commission_include_shipping'] ) ) {
      $option_group['wcusage_field_commission_include_shipping'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_commission_include_shipping'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_commission_before_discount_custom'] ) ) {
      $option_group['wcusage_field_commission_before_discount_custom'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_commission_before_discount_custom'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_commission_include_fees'] ) ) {
      $option_group['wcusage_field_commission_include_fees'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_commission_include_fees'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_show_tax'] ) ) {
      $option_group['wcusage_field_show_tax'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_show_tax'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_show_tax_fixed'] ) ) {
      $option_group['wcusage_field_show_tax_fixed'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_show_tax_fixed'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_affiliate_deduct_percent'] ) ) {
      $option_group['wcusage_field_affiliate_deduct_percent'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_affiliate_deduct_percent'] );
    }

  }

  // 5
  if( isset( $_POST['submit_step5'] ) ) {

    if( isset( $_POST['wcusage_options']['wcusage_field_from_email'] ) ) {
      $option_group['wcusage_field_from_email'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_from_email'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_from_name'] ) ) {
      $option_group['wcusage_field_from_name'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_from_name'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_registration_admin_email'] ) ) {
      $option_group['wcusage_field_registration_admin_email'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_registration_admin_email'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_email_enable'] ) ) {
      $option_group['wcusage_field_email_enable'] = $_POST['wcusage_options']['wcusage_field_email_enable'];
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_email_subject'] ) ) {
      $option_group['wcusage_field_email_subject'] = sanitize_text_field( $_POST['wcusage_options']['wcusage_field_email_subject'] );
    }

    if( isset( $_POST['wcusage_options']['wcusage_field_email_message'] ) ) {
      $option_group['wcusage_field_email_message'] = html_entity_decode(stripslashes( $_POST['wcusage_options']['wcusage_field_email_message'] ));
    }

  }

  update_option( 'wcusage_options', $option_group );

}
