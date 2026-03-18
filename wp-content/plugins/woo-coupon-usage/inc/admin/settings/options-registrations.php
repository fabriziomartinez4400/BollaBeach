<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_registration( $args )
{
    $options = get_option( 'wcusage_options' );

    $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
    $probrackets = ( $ispro ? "" : " (PRO)" );
    $wcusage_registration_page = wcusage_get_setting_value('wcusage_registration_page', '');
    ?>

  	<h1><?php echo esc_html__( 'Affiliate Registration', 'woo-coupon-usage' ); ?></h1>

    <hr/>

    <p><?php echo esc_html__( 'Affiliate registration will allow your users to easily register to become affiliate, and automatically create an affiliate coupon for them.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/affiliate-registration" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>.</p>

    <?php if( !wcu_fs()->can_use_premium_code() ) { ?>
    <br/><p>- <?php echo esc_html__( 'PRO features: Custom form fields, dynamic code generator, auto accept, auto registration, and join button on checkout.', 'woo-coupon-usage' ); ?></p>
    <?php } ?>
    
    <?php if ( ! get_option( 'users_can_register' ) ) { ?>
      <p style="color: #c44747ff; font-size: 12px;" class="registration-warning">
        <?php echo sprintf( wp_kses_post( __( 'Warning: You have "<a href="%s" target="_blank">Anyone can register</a>" disabled in WordPress, which will be ignored for the registration form on the affiliate dashboard.', 'woo-coupon-usage' ) ), esc_url( admin_url( 'options-general.php' ) . '#users_can_register' ) ); ?>
      </p>
    <?php } ?>

  	<br/><hr/>

      <!-- Enable Affiliate Registration Features -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_enable', 1, esc_html__( 'Enable Affiliate Registration Features', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'This will enable the coupon affiliate registration system on your website.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_registration_enable', '.wcu-field-section-registration-settings'); // Show or Hide ?>
      <span class="wcu-field-section-registration-settings">

        <?php if(!$wcusage_registration_page) { ?>
        <br/><br/>

        <p>- <strong><?php echo esc_html__( 'To get started with affiliate registration, you will need to add this shortcode to a NEW page:', 'woo-coupon-usage' ); ?> <span style="color: red;">[couponaffiliates-register]</span></strong></p>

        <p>- <strong><?php echo esc_html__( 'Please do not add this shortcode to the same page as your affiliate dashboard shortcode.', 'woo-coupon-usage' ); ?></strong></p>
        <?php } ?>

        <br/><br/>

        <!-- Registration Page -->
        <?php do_action( 'wcusage_hook_setting_section_registration_page' ); ?>

        <br/><br/>

        <!-- FAQ: How does the affiliate registration system work? -->
        <div class="wcu-admin-faq">

          <?php echo wcusage_admin_faq_toggle(
          "wcu_show_section_qna_registrations",
          "wcu_qna_registrations",
          "FAQ: How does the affiliate registration system work?");
          ?>

          <div class="wcu-admin-faq-content wcu_qna_registrations" id="wcu_qna_registrations" style="display: none;">

            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Firstly, if you have not already, get started by creating a new page and adding the shortcode: [couponaffiliates-register]', 'woo-coupon-usage' ); ?><br/>

            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can customise the form and add your own custom fields via the below settings.', 'woo-coupon-usage' ); ?><br/>

            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'When a user submits the form, you will be notified, and can review the application in the admin "Registrations" page.', 'woo-coupon-usage' ); ?><br/>

            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'When approved, it will generate a coupon code automatically, and assign it to that affiliate user.', 'woo-coupon-usage' ); ?><br/>
          
            <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The user will be notified by email, and can then login to their affiliate dashboard to view their statistics and more.', 'woo-coupon-usage' ); ?><br/>

            <a href="https://couponaffiliates.com/docs/affiliate-registration" target="_blank" class="button button-primary" style="margin-top: 10px;"><?php echo esc_html__( 'View Documentation', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span></a>

            <br/><br/>

            <strong><?php echo esc_html__( 'For more information, please see the video below:', 'woo-coupon-usage' ); ?></strong>

            <br/>
            <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/713487822?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>

          </div>

        </div>

        <br/><hr/>

        <!-- Template Coupon -->
        <?php do_action( 'wcusage_hook_setting_section_registration_template' ); ?>

        <!-- Template Coupon Multi -->
        <?php do_action( 'wcusage_hook_setting_section_registration_template2' ); ?>

        <br/><hr/>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Form Visibility Settings', 'woo-coupon-usage' ); ?></h3>

        <?php wcusage_setting_toggle_option('wcusage_field_loginform', 1, esc_html__( 'Show login form on affiliate dashboard.', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <?php
        $wcusage_portal_enabled_on_load = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
        $wcusage_users_can_register = (int) get_option('users_can_register');
        // Default to enabled for existing portals, but keep disabled when the portal toggle is off and user registration is disabled
        $wcusage_portal_form_default = 1;
        if ('1' !== $wcusage_portal_enabled_on_load && !$wcusage_users_can_register) {
          $wcusage_portal_form_default = 0;
        }
        wcusage_setting_toggle_option('wcusage_field_enable_portal_registration', $wcusage_portal_form_default, esc_html__( 'Show registration form on affiliate dashboard.', 'woo-coupon-usage' ), '0px');
        ?>
        
        <?php wcusage_setting_toggle('.wcusage_field_enable_portal_registration', '#wcu-dashboard-register-settings'); // Show or Hide ?>

        <div id="wcu-dashboard-register-settings">

          <br/>

          <!-- Show registration form on affiliate page for logged in users -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_register_loggedin', 1, esc_html__( 'Show for logged in users.', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'If the user is not already registered as an affiliate and has no active coupons, this will show the registration from on the affiliate dashboard page.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>
          
          <!-- Show registration form on affiliate page for logged out users. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_login', 1, esc_html__( 'Show for logged out users.', 'woo-coupon-usage' ), '40px'); ?>
          <i style="margin-left: 40px;"><?php echo esc_html__( 'This will show the affiliate application/registration form automatically on the affiliate page for logged out users (alongside the login form).', 'woo-coupon-usage' ); ?></i><br/>

        </div>

        <br/>

        <!-- Allow logged out users to register for an affiliate coupon. -->
        <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_logout', 1, esc_html__( 'Allow logged out users to register as an affiliate.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled, logged out users can view the registration form (with some extra fields). When submitted it will create a new account for them, and submit the affiliate application.', 'woo-coupon-usage' ); ?></i><br/>
        <i><?php echo esc_html__( 'With this disabled, only logged in users can apply.', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

        <!-- Disable form for existing affiliates -->
        <?php wcusage_setting_toggle_option('wcusage_field_registration_disable_existing', 1, esc_html__( 'Disable registration form for existing affiliate users.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'If enabled, then the registration form shortcode will be disabled/hidden for any affiliate user that is already assigned to an affiliate coupon.', 'woo-coupon-usage' ); ?></i><br/>

        <?php $wcusage_field_registration_enable_admincan = wcusage_get_setting_value('wcusage_field_registration_enable_admincan', '0'); ?>
        <?php if($wcusage_field_registration_enable_admincan) { ?>
        <br/>
        <!-- Allow administrator users to fill out the registration form for new users. -->
        <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_admincan', 0, esc_html__( 'Allow administrator users to fill out the registration form for new users.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled, "administrator" users will be able to fill out the affiliate registration form for new users (custom username/email etc), whilst logged in.', 'woo-coupon-usage' ); ?></i><br/>
        <i><?php echo esc_html__( 'As an admin, you can also manually add new affiliate registrations easily in the "Registrations" admin page, via the "Create New Registration" button.', 'woo-coupon-usage' ); ?></i><br/>
        <?php } ?>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Basic Fields Customisation', 'woo-coupon-usage' ); ?></h3>
      
      <!-- First name and last name. -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_name_required', 0, esc_html__( 'First & Last Name Required', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, the first name and last name fields will be required.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- Use the email address as username. -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_emailusername', 0, esc_html__( 'Use the email address as username.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, the username field will be hidden, and the email address will be used as their username instead.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- First name and last name. -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_password_confirm', 0, esc_html__( 'Show "Confirm Password" Field', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, a second password field will be shown to require users to confirm their password.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- "Preferred Coupon Code" Field Label -->
      <?php wcusage_setting_text_option('wcusage_field_registration_coupon_label', '', esc_html__( 'Custom "Preferred Coupon Code" Field Label', 'woo-coupon-usage' ), '0px'); ?>

      <br/><hr/>

      <?php if( wcu_fs()->can_use_premium_code() ) { ?>

      <div>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Terms and Conditions Generator', 'woo-coupon-usage' ); ?></h3>

        <p>
          <?php echo esc_html__( 'You can use the terms and conditions manager and generation tool to help you easily create your terms and conditions page.', 'woo-coupon-usage' ); ?>
          <a href="https://couponaffiliates.com/docs/terms-generator/" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>
        </p>

        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wcusage-terms-generator' ) ); ?>" target="_blank"
        class="button button-primary" style="margin-top: 10px;">
          <?php echo esc_html__( 'Generate & Edit Terms and Conditions', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span>
        </a>

        <br/><br/><hr/>

      </div>

      <?php } ?>

      <h3 id="wcu-setting-header-terms"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Terms and Conditions Acceptance Checkbox', 'woo-coupon-usage' ); ?></h3>

      <!-- Enable terms acceptance checkbox on affiliate registration form. -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_terms', 0, esc_html__( 'Enable terms and conditions checkbox on registration form.', 'woo-coupon-usage' ), '0px'); ?>

      <style>
      #wcusage_field_registration_terms_message_ifr { height: 60px !important; }
      </style>
      <?php wcusage_setting_toggle('.wcusage_field_registration_enable_terms', '.wcu-field-section-registration-terms-message'); // Show or Hide ?>
      <div class="wcu-field-section-registration-terms-message">
        <br/>
        <!-- Terms and Conditions Message -->
        <?php
        $terms1message = wcusage_get_setting_value('wcusage_field_registration_terms_message', 'I have read and agree to the Affiliate Terms and Privacy Policy.');
        wcusage_setting_tinymce_option('wcusage_field_registration_terms_message', $terms1message, "Terms and Conditions Message", '0px');
        ?>
        <i><?php echo esc_html__( 'Enter your terms acceptance message. Make sure you edit the message to include links to your terms and privacy policy!', 'woo-coupon-usage' ); ?></i><br/>
      </div>

      <?php if( !wcu_fs()->can_use_premium_code() ) { ?>
      <br/><br/>

      <strong><?php echo esc_html__( 'Terms and Conditions Page Generator', 'woo-coupon-usage' ); ?>:</strong>
      <p>
        <?php echo esc_html__( 'The PRO version of this plugin includes a terms and conditions page generator, which allows you to easily create your own terms and conditions page.', 'woo-coupon-usage' ); ?>
        <a href="https://couponaffiliates.com/docs/terms-generator/" target="_blank"><?php echo esc_html__( 'Learn More', 'woo-coupon-usage' ); ?></a>
      </p>

      <p>
        <?php echo sprintf(wp_kses_post(__( 'This may be made available in the free version soon. For now, if you need help with creating your terms and conditions page in the free version, you can view <a href="%s" target="_blank">this article</a>.', 'woo-coupon-usage' )), 'https://couponaffiliates.com/how-to-create-affiliate-terms/'); ?>
      </p>

      <?php } ?>

      <br/><hr style="margin-top: 15px;" />

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Form Submission', 'woo-coupon-usage' ); ?></h3>

      <!-- DROPDOWN - Select submission complete type -->
      <p>
    		<?php $wcusage_field_registration_submit_type = wcusage_get_setting_value('wcusage_field_registration_submit_type', 'message'); ?>
    		<input type="hidden" value="0" id="wcusage_field_registration_submit_type" data-custom="custom" name="wcusage_options[wcusage_field_registration_submit_type]" >
    		<strong><label for="scales"><?php echo esc_html__( 'What should happen after form submission?', 'woo-coupon-usage' ); ?></label></strong><br/>
    		<select name="wcusage_options[wcusage_field_registration_submit_type]" id="wcusage_field_registration_submit_type" class="wcusage_field_registration_submit_type">
          <option value="message" <?php if($wcusage_field_registration_submit_type == "message") { ?>selected<?php } ?>><?php echo esc_html__( 'Show a message on the same page.', 'woo-coupon-usage' ); ?></option>
    			<option value="redirect" <?php if($wcusage_field_registration_submit_type == "redirect") { ?>selected<?php } ?>><?php echo esc_html__( 'Redirect to a different page.', 'woo-coupon-usage' ); ?></option>
        </select>
      </p>

      <br/>
      <script>
      jQuery('.wcusage_field_registration_submit_type').change(function() {
        wcusage_js_registration_type_change();
      });
      jQuery( document ).ready(function() {
        wcusage_js_registration_type_change();
      });
      function wcusage_js_registration_type_change() {
        jQuery('.section-registration-type-message').hide();
        jQuery('.section-registration-type-redirect').hide();
        if( jQuery('.wcusage_field_registration_submit_type :selected' ).val() == 'message' ){
          jQuery('.section-registration-type-message').show();
          jQuery('.section-registration-type-redirect').hide();
        }
        if(jQuery('.wcusage_field_registration_submit_type :selected' ).val() == 'redirect' ){
          jQuery('.section-registration-type-message').hide();
          jQuery('.section-registration-type-redirect').show();
        }
      }
      </script>
      <div class="section-registration-type-message">
        <style>
        #wcusage_field_registration_accept_message_ifr { height: 60px !important; }
        </style>
        <!-- Message -->
        <?php
        $terms2message = wcusage_get_setting_value('wcusage_field_registration_accept_message', 'Your affiliate application for the coupon code "{coupon}" has been submitted.');
        wcusage_setting_tinymce_option('wcusage_field_registration_accept_message', $terms2message, 'Submission Message', '0px');
        ?>
        <i><?php echo esc_html__( 'This is the message shown on the page as soon as the user submits the application form. The {couponcode} placeholder will be replaced with their chosen coupon code.', 'woo-coupon-usage' ); ?></i><br/>
      </div>

      <!-- DROPDOWN - Redirect to page -->
      <div class="section-registration-type-redirect">
        <p>
          <strong><?php echo esc_html__( 'Redirect to Page:', 'woo-coupon-usage' ); ?></strong><br/>
          <?php
          $dashboardpage = "";
          if ( isset($options['wcusage_field_registration_accept_redirect']) ) {
              $dashboardpage = $options['wcusage_field_registration_accept_redirect'];
          } else {
              $dashboardpage = wcusage_get_coupon_shortcode_page_id();
          }
          $dropdown_args = array(
            'post_type'        => 'page',
            'selected'         => $dashboardpage,
            'name'             => 'wcusage_options[wcusage_field_registration_accept_redirect]',
            'id'               => 'wcusage_field_registration_accept_redirect',
            'value_field'      => 'wcusage_field_registration_accept_redirect',
            'show_option_none' => '-'
          );
          foreach ( $dropdown_args as $key => $value ) {
            if ( is_string( $value ) ) {
                  $dropdown_args[ $key ] = esc_attr( $value );
            }
          }
          wp_dropdown_pages( $dropdown_args );
          ?>
          <br/>
        </p>
      </div>

      <br/>

      <!-- Pending Application Message -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_pending_message_enable', 0, esc_html__( 'Customise the pending application message (shown while awaiting review).', 'woo-coupon-usage' ), '0px'); ?>
      
      <br/>

      <?php wcusage_setting_toggle('.wcusage_field_registration_pending_message_enable', '.wcu-field-section-registration-pending-message'); // Show or Hide ?>
      <span class="wcu-field-section-registration-pending-message">
        <style>
        #wcusage_field_registration_pending_message_ifr { height: 80px !important; }
        </style>
        <?php
        $pending_default_message = '<p>You have a pending affiliate application.</p><p>We are reviewing your application and will be in touch soon!</p>';
        $pending_message = wcusage_get_setting_value('wcusage_field_registration_pending_message', $pending_default_message);
        wcusage_setting_tinymce_option('wcusage_field_registration_pending_message', $pending_message, esc_html__( 'Pending Application Message', 'woo-coupon-usage' ), '0px');
        ?>
        <br/>
      </span>
      
      <!-- Automatically log the user in after registration. -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_auto_login', 1, esc_html__( 'Automatically log the user in after registration.', 'woo-coupon-usage' ), '0px'); ?>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( '"Coupon Affiliate" User Role', 'woo-coupon-usage' ); ?></h3>

      <!-- Upon new registration, assign user to custom "coupon affiliate" user role. -->
      <?php wcusage_setting_toggle_option('wcusage_field_register_role', 1, esc_html__( 'Upon new registration, assign user to custom "coupon affiliate" user role.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, instead of using the default WordPress "subscriber" user role, new affiliate users will be assigned to the custom "coupon affiliate" user role (or the custom role defined below) instead.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <?php wcusage_setting_toggle('.wcusage_field_register_role', '.wcu-field-section-registration-accepted-role'); // Show or Hide ?>
      <span class="wcu-field-section-registration-accepted-role">
        <p>

          <!-- DROPDOWN - Accepted Affiliate User Role -->
          <strong><?php echo esc_html__( 'Accepted Affiliate User Role', 'woo-coupon-usage' ); ?>:</strong><br/>
          <?php $wcusage_field_registration_accepted_role = wcusage_get_setting_value('wcusage_field_registration_accepted_role', 'coupon_affiliate'); ?>
          <select name="wcusage_options[wcusage_field_registration_accepted_role]" id="wcusage_field_registration_accepted_role" class="wcusage_field_registration_accepted_role">
            <?php
            $r1 = "";
            $editable_roles = get_editable_roles();
              foreach ( $editable_roles as $role => $details ) {
                  if($role != 'administrator' && $role != 'editor' && $role != 'author' && $role != 'shop_manager' && (!is_array($details['capabilities']) || !array_key_exists( 'manage_options', $details['capabilities'] )) ) {
                    $name = translate_user_role( $details['name'] );
                    if ( $wcusage_field_registration_accepted_role === $role ) {
                        $r1 .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
                    } else {
                        $r1 .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
                    }
                  }
              }
            echo $r1;
            ?>
          </select>

        </p>

        <br/>

        <!-- Set a different user role for pending affiliate users. -->
        <?php wcusage_setting_toggle_option('wcusage_field_register_role_only_accept', 0, esc_html__( 'Set a different user role for pending affiliate users.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled, the new user account created will be assigned to the default "Subscriber" role (or the custom role defined below) initially, and only when their affiliate application is accepted will they be assigned to the "coupon affiliate" user role instead.', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

      </span>

      <?php wcusage_setting_toggle('.wcusage_field_register_role_only_accept', '.wcu-field-section-registration-pending-role'); // Show or Hide ?>
      <span class="wcu-field-section-registration-pending-role">
        <p>

          <!-- DROPDOWN - Pending Affiliate User Role -->
          <strong><?php echo esc_html__( 'Pending Affiliate User Role', 'woo-coupon-usage' ); ?>:</strong><br/>
          <?php $wcusage_field_registration_pending_role = wcusage_get_setting_value('wcusage_field_registration_pending_role', 'subscriber'); ?>
          <select name="wcusage_options[wcusage_field_registration_pending_role]" id="wcusage_field_registration_pending_role" class="wcusage_field_registration_pending_role">
            <?php
            $r2 = "";
            $editable_roles = array_reverse( get_editable_roles() );
              foreach ( $editable_roles as $role => $details ) {
                  if($role != 'administrator' && $role != 'editor' && $role != 'author' && $role != 'shop_manager' && (!is_array($details['capabilities']) || !array_key_exists( 'manage_options', $details['capabilities'] )) ) {
                    $name = translate_user_role( $details['name'] );
                    // Preselect specified role.
                    if ( $wcusage_field_registration_pending_role === $role ) {
                        $r2 .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
                    } else {
                        $r2 .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
                    }
                  }
              }
              echo $r2;
            ?>
          </select>

        </p>

        <br/>

        <!-- Remove the pending affiliate role from user when their affiliate application is accepted. -->
        <?php wcusage_setting_toggle_option('wcusage_field_register_role_remove_pending', 1, esc_html__( 'Remove the pending affiliate role from user when their affiliate application is accepted.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled, the pending user role will be removed from the affiliate when the application is accepted.', 'woo-coupon-usage' ); ?></i><br/>

        <br/>

      </span>

      <hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Email Notifications', 'woo-coupon-usage' ); ?></h3>

      <p>
        <?php echo esc_html__( 'To manage (and enable) email notifications for affiliate applications, go to the "Emails" settings tab.', 'woo-coupon-usage' ); ?>
      </p>

      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Form CAPTCHA - Spam Protection', 'woo-coupon-usage' ); ?></h3>

      <!-- Enable HoneyPot Spam Protection -->
      <?php wcusage_setting_toggle_option('wcusage_registration_enable_honeypot', 1, esc_html__( 'Enable HoneyPot Spam Prevention', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, a hidden field will be added to the registration form to help prevent spam.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'This is only a basic spam protection method, and is not as effective as CAPTCHA.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <Strong><?php echo esc_html__( 'Advanced CAPTCHA Spam Protection', 'woo-coupon-usage' ); ?>:</Strong><br/>

      <!-- Enable Spam CAPTCHA -->
      <?php
      $recaptcha_key = wcusage_get_setting_value('wcusage_registration_recaptcha_key', '');
      $recaptcha_default = ($recaptcha_key ? '1' : '0');
      $wcusage_registration_enable_captcha = wcusage_get_setting_value('wcusage_registration_enable_captcha', '');
      ?>
      <select name="wcusage_options[wcusage_registration_enable_captcha]" id="wcusage_registration_enable_captcha" class="wcusage_registration_enable_captcha">
        <option value="0" <?php if($wcusage_registration_enable_captcha == "0") { ?>selected<?php } ?>><?php echo esc_html__( '- Disabled -', 'woo-coupon-usage' ); ?></option>
        <option value="2" <?php if($wcusage_registration_enable_captcha == "2") { ?>selected<?php } ?>><?php echo esc_html__( 'Cloudlare Turnstile (Recommended)', 'woo-coupon-usage' ); ?></option>
        <option value="1" <?php if($wcusage_registration_enable_captcha == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'Google reCAPTCHA', 'woo-coupon-usage' ); ?></option>
      </select>

      <script>
      jQuery(document).ready(function() {
          jQuery('.wcu-turnstile-help').hide();
          jQuery('.wcu-recaptcha-help').hide();
          jQuery('.wcu-field-section-registration-captcha').hide();
          if (jQuery('.wcusage_registration_enable_captcha').val() == '1') {
            jQuery('.wcu-recaptcha-help').show();
            jQuery('.wcu-turnstile-help').hide();
            jQuery('.wcu-field-section-registration-captcha').show();
          }
          if (jQuery('.wcusage_registration_enable_captcha').val() == '2') {
            jQuery('.wcu-turnstile-help').show();
            jQuery('.wcu-recaptcha-help').hide();
            jQuery('.wcu-field-section-registration-captcha').show();
          }
      });
      jQuery('.wcusage_registration_enable_captcha').change(function() {
          jQuery('.wcu-turnstile-help').hide();
          jQuery('.wcu-recaptcha-help').hide();
          jQuery('.wcu-field-section-registration-captcha').hide();
          if (jQuery('.wcusage_registration_enable_captcha').val() == '1') {
            jQuery('.wcu-recaptcha-help').show();
            jQuery('.wcu-turnstile-help').hide();
            jQuery('.wcu-field-section-registration-captcha').show();
          }
          if (jQuery('.wcusage_registration_enable_captcha').val() == '2') {
            jQuery('.wcu-turnstile-help').show();
            jQuery('.wcu-recaptcha-help').hide();
            jQuery('.wcu-field-section-registration-captcha').show();
          }
      });
      </script>

      <br/><i><?php echo esc_html__( 'Setup a CAPTCHA on your affiliate registration form to help prevent spam.', 'woo-coupon-usage' ); ?></i><br/>

      <div class="wcu-field-section-registration-captcha">

        <div class="wcu-recaptcha-help"><br/>
          <p><?php echo esc_html__( 'You can get your site key and secret key from here:', 'woo-coupon-usage' ); ?> <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a></p>
          <p style="font-weight: bold;"><?php echo esc_html__( 'Currently only reCAPTCHA "v2" is supported.', 'woo-coupon-usage' ); ?></p>
          <br/>
          <!-- Site Key -->
          <?php wcusage_setting_text_option('wcusage_registration_recaptcha_key', '', esc_html__( 'Site Key', 'woo-coupon-usage' ), '0px'); ?>
          <br/>
          <!-- Secret Key -->
          <?php wcusage_setting_text_option('wcusage_registration_recaptcha_secret', '', esc_html__( 'Secret Key', 'woo-coupon-usage' ), '0px'); ?>
        </div>

        <div class="wcu-turnstile-help"><br/>
          <p><?php echo esc_html__( 'Cloudflare Turnstile is a new, user-friendly, privacy-preserving reCAPTCHA alternative.', 'woo-coupon-usage' ); ?></p>
          <p><?php echo esc_html__( 'You can get your site key and secret key from here:', 'woo-coupon-usage' ); ?> <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">https://dash.cloudflare.com/?to=/:account/turnstile</a></p>
          <br/>
          <!-- Site Key -->
          <?php wcusage_setting_text_option('wcusage_registration_turnstile_key', '', esc_html__( 'Site Key', 'woo-coupon-usage' ), '0px'); ?>
          <br/>
          <!-- Secret Key -->
          <?php wcusage_setting_text_option('wcusage_registration_turnstile_secret', '', esc_html__( 'Secret Key', 'woo-coupon-usage' ), '0px'); ?>
        </div>

      </div>

      <br/><hr/>

      <div id="pro-registration-settings" class="settings-area<?php if ( !wcu_fs()->can_use_premium_code() ) { ?> premium-only-settings<?php } ?>" <?php
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>title="Available with Pro version." style="pointer-events:none; opacity: 0.4;"<?php
        }
        ?>>

          <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Auto-Accept Registrations', 'woo-coupon-usage' ); ?></h3>

          <!-- Automatically accept all affiliate registrations. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_auto_accept', 0, esc_html__( 'Automatically accept all affiliate registrations.', 'woo-coupon-usage' ) . esc_html($probrackets), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, affiliate registrations will be automatically accepted (and coupon auto-created instantly), instead of manual approval.', 'woo-coupon-usage' ); ?></i><br/>


          <?php wcusage_setting_toggle('.wcusage_field_registration_auto_accept', '.wcu-field-section-registration-auto-accept-roles-limit'); // Show or Hide ?>
          <span class="wcu-field-section-registration-auto-accept-roles-limit">

            <br/>

            <!-- Limit auto-accept to certain roles/groups -->
            <?php wcusage_setting_toggle_option('wcusage_field_registration_auto_accept_limit', 0, esc_html__( 'Only auto-accept for certain user roles & groups?', 'woo-coupon-usage' ), '20px'); ?>
            <i style="margin-left: 20px; display: inline-block;"><?php echo esc_html__( 'If enabled, only users with one of the selected roles/groups (or the role/group assigned to the selected template) will be auto-accepted.', 'woo-coupon-usage' ); ?></i><br/>

            <?php wcusage_setting_toggle('.wcusage_field_registration_auto_accept_limit', '.wcu-field-section-registration-auto-accept-roles'); // Show or Hide ?>
            <div class="wcu-field-section-registration-auto-accept-roles" style="margin-left: 40px;">
              <span style="height: 120px; width: 250px; overflow-y: auto; display: block; border: 1px solid #ddd; padding: 10px;">

              <?php
              $options = get_option('wcusage_options');
              $thisid = 'wcusage_field_registration_auto_accept_roles';

              $current_selected_roles = array();
              if (isset($options[$thisid]) && is_array($options[$thisid])) {
                $current_selected_roles = $options[$thisid];
              }

              // Remove any saved roles that no longer exist.
              foreach ($current_selected_roles as $key => $val) {
                $rolesx = get_editable_roles();
                if (!isset($rolesx[$key])) {
                  // Only update on non-GET requests
                  if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
                    $options_new = get_option('wcusage_options');
                    if ( ! is_array( $options_new ) ) {
                      $options_new = array();
                    }
                    unset($options_new[$thisid][$key]);
                    update_option('wcusage_options', $options_new);
                  }
                  unset($options[$thisid][$key]);
                }
              }

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
                $role_name = $role['name'];
                if (strpos($key, 'coupon_affiliate') !== false) {
                  $role_name = '(Group) ' . $role_name;
                }
                $checked = '';
                if (isset($options[$thisid]) && is_array($options[$thisid]) && isset($options[$thisid][$key])) {
                  $checked = 'checked';
                }
                echo '<span id="' . esc_attr($thisid) . '">' .
                  '<input type="checkbox" class="wcusage_field_' . esc_attr($thisid) . '" name="wcusage_options[' . esc_attr($thisid) . '][' . esc_attr($key) . ']" value="1" ' . esc_attr($checked) . '> ' . esc_html($role_name) .
                  '</span><br/>';
              }
              ?>

              </span>
              <i style="display:block; margin-top: 6px;"><?php echo esc_html__( 'If none are selected, auto-accept will apply to all registrations.', 'woo-coupon-usage' ); ?></i>
            </div>

          </span>

          <br/><hr/>

          <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Dynamic Code Generator', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?></h3>

          <p><?php echo esc_html__( 'By default, a required "preferred coupon code" field will be shown on the registration form for the affiliate to enter their prefered coupon code name.', 'woo-coupon-usage' ); ?></p>
          <p><?php echo esc_html__( 'When they submit the affiliate registration form you can then view and edit the coupon code they have entered, before approving the affiliate registration.', 'woo-coupon-usage' ); ?></p>
          <p><?php echo esc_html__( 'Alternatively, enable the option below to disable this field and generate a specific code automatically via a merge tag template.', 'woo-coupon-usage' ); ?></p>

          <br/>

          <!-- Automatically generate coupon code? -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_auto_coupon', 0, esc_html__( 'Generate a dynamic coupon name automatically.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, instead of the user entering their "preferred coupon code", a code will be generated for them automatically.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'You will still be able to review and edit the generated code before approving.', 'woo-coupon-usage' ); ?></i>

          <br/>

          <?php wcusage_setting_toggle('.wcusage_field_registration_auto_coupon', '.wcu-field-section-registration-auto-coupon-text'); // Show or Hide ?>
          <span class="wcu-field-section-registration-auto-coupon-text">
            <!-- Coupon Format Field -->
            <br/>
            <?php wcusage_setting_text_option('wcusage_field_registration_auto_coupon_format', '{username}{amount}', esc_html__( 'Coupon Format', 'woo-coupon-usage' ), '0px'); ?>

            <?php
            $template_coupon_code = wcusage_get_setting_value('wcusage_field_registration_coupon_template', '');
            if(!empty($template_coupon_code)) {
              $template_coupon_info = wcusage_get_coupon_info($template_coupon_code);
              $template_coupon_id = $template_coupon_info[2];
              $template_coupon_amount = get_post_meta( $template_coupon_id, 'coupon_amount', true );
            } else {
              $template_coupon_amount = "10";
            }
            ?>

            <script>
            jQuery( document ).ready(function() {
              wcusage_update_example_coupon();
            });
            jQuery('#wcusage_field_registration_auto_coupon_format').on('input', wcusage_update_example_coupon );
            function wcusage_update_example_coupon() {
              var couponexample = jQuery('#wcusage_field_registration_auto_coupon_format').val();
              var couponexample = couponexample.replace("{username}", "JOHN");
              var couponexample = couponexample.replace("{amount}", "<?php echo esc_html($template_coupon_amount); ?>");
              var couponexample = couponexample.replace("{random}", "KPQS9JY");
              // New name-based merge tags
              couponexample = couponexample.replace(/\{first_name\}/g, 'JOHN');
              couponexample = couponexample.replace(/\{Last_name\}/g, 'DOE');
              couponexample = couponexample.replace(/\{last_name\}/g, 'DOE');
              couponexample = couponexample.replace(/\{first_name_initial\}/g, 'J');
              couponexample = couponexample.replace(/\{last_name_initial\}/g, 'D');
              jQuery('#coupon_format_example').text(couponexample);
            }
            </script>
            <p><strong>Example code:</strong> <span id="coupon_format_example"></span></p>
            <br/>Merge tags:
            <br/><strong>{username}</strong> - The affiliate's username.
            <br/><strong>{amount}</strong> - The discount amount the coupon gives for example "<?php echo esc_html($template_coupon_amount); ?>" (if it was a "<?php echo esc_html($template_coupon_amount); ?>% off" or "$<?php echo esc_html($template_coupon_amount); ?> off" discount code).
            <br/><strong>{random}</strong> - A randomly generated 7 letter/number phrase for example "KPQS9JY". Unique every time.
            <br/><strong>{first_name}</strong> - The affiliate's first name, for example "JOHN".
            <br/><strong>{last_name}</strong> - The affiliate's last name, for example "DOE".
            <br/><strong>{first_name_initial}</strong> - First initial, for example "J".
            <br/><strong>{last_name_initial}</strong> - Last initial, for example "D".
            <br/>You can also place your own custom text in the format before, after or inbetween the merge tags.
            <br/>

          </span>

          <br/><hr/>

          <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Extra Fields', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?></h3>

          <!-- Show "Website" field on affiliate application form. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_website', 0, esc_html__( '"Website" Field', 'woo-coupon-usage' ), '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_registration_enable_website', '.wcu-field-section-registration-website-text'); // Show or Hide ?>
          <span class="wcu-field-section-registration-website-text" style="margin-top: 7px; display: block;">
            <div style="display: inline-block;padding: 5px 10px 8px 10px;background: #fff;border: 2px solid #e3e3e3;border-radius: 10px;">
              <!-- Website field label -->
              <div style="width: auto; float: left; display: block;">
                <?php wcusage_setting_text_option('wcusage_field_registration_website_text', 'Your Website', '<span class="reg-field-label">' . esc_html__( 'Field Label:', 'woo-coupon-usage' ) . '</span>', '0px'); ?>
              </div>
              <div style="width: auto; float: left; display: block; margin-top: -5px;">
                <strong style="display: block; margin: 5px 0 -5px 10px;"><label for="wcusage_field_registration_enable_website_req"><?php echo esc_html__( 'Required?', 'woo-coupon-usage' ); ?></label></strong>
                <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_website_req', 1, '', '10px'); ?>
              </div>
            </div>
          </span>

          <div style="clear: both;"></div>
          <br/>

          <!-- Show "How will you promote us?" field on affiliate application form. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_promote', 0, esc_html__( '"How will you promote us?" Field', 'woo-coupon-usage' ), '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_registration_enable_promote', '.wcu-field-section-registration-promote-text'); // Show or Hide ?>
          <span class="wcu-field-section-registration-promote-text" style="margin-top: 7px; display: block;">
            <div style="display: inline-block;padding: 5px 10px 8px 10px;background: #fff;border: 2px solid #e3e3e3;border-radius: 10px;">
              <!-- Promote field label -->
              <div style="width: auto; float: left; display: block;">
                <?php wcusage_setting_text_option('wcusage_field_registration_promote_text', 'How will you promote us?', '<span class="reg-field-label">' . esc_html__( 'Field Label:', 'woo-coupon-usage' ) . '</span>', '0px'); ?>
              </div>
              <div style="width: auto; float: left; display: block; margin-top: -5px;">
                <strong style="display: block; margin: 5px 0 -5px 10px;"><label for="wcusage_field_registration_enable_promote_req"><?php echo esc_html__( 'Required?', 'woo-coupon-usage' ); ?></label></strong>
                <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_promote_req', 1, '', '10px'); ?>
              </div>
            </div>
          </span>

          <div style="clear: both;"></div>
          <br/>

          <!-- Show "How did you hear about us?" field on affiliate application form. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_referrer', 0, esc_html__( '"How did you hear about us?" Field', 'woo-coupon-usage' ), '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_registration_enable_referrer', '.wcu-field-section-registration-referrer-text'); // Show or Hide ?>
          <span class="wcu-field-section-registration-referrer-text" style="margin-top: 7px; display: block;">
            <div style="display: inline-block;padding: 5px 10px 8px 10px;background: #fff;border: 2px solid #e3e3e3;border-radius: 10px;">
              <!-- Referrer field label -->
              <div style="width: auto; float: left; display: block;">
                <?php wcusage_setting_text_option('wcusage_field_registration_referrer_text', 'How did you hear about us?', '<span class="reg-field-label">' . esc_html__( 'Field Label:', 'woo-coupon-usage' ) . '</span>', '0px'); ?>
              </div>
              <div style="width: auto; float: left; display: block; margin-top: -5px;">
                <strong style="display: block; margin: 5px 0 -5px 10px;"><label for="wcusage_field_registration_enable_referrer_req"><?php echo esc_html__( 'Required?', 'woo-coupon-usage' ); ?></label></strong>
                <?php wcusage_setting_toggle_option('wcusage_field_registration_enable_referrer_req', 0, '', '10px'); ?>
              </div>
            </div>
          </span>

          <div style="clear: both;"></div>
          <br/>

          <hr/>

          <h3 style="margin-bottom: 0px;"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Custom Form Fields', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?></h3>

          <?php
          $tiersnumber = wcusage_get_setting_value('wcusage_field_registration_custom_fields', '5');
          // Hidden field to persist count on full save; actual updates are done via AJAX below.
          ?>
          <input type="hidden" name="wcusage_options[wcusage_field_registration_custom_fields]" id="wcusage_field_registration_custom_fields" value="<?php echo esc_attr( $tiersnumber ); ?>" />
          <?php
          ?>
          <br/>

          <style>.registration_custom_fields .wcu-update-icon { display: none !important; }</style>
          <div class="registration_custom_fields" id="wcu-registration-custom-fields">
          <?php for ($x = 1; $x <= $tiersnumber; $x++) {
            $type = "";
            ?>

            <div style="display: inline-block;padding: 5px 10px 8px 10px;background: #fff;border: 2px solid #e3e3e3;border-radius: 10px;"
            class="registration_custom_<?php echo esc_attr($x); ?>">

              <div style="width: auto; float: left; display: block; margin-bottom: 2px;" class="registration_custom_label_<?php echo esc_attr($x); ?>">
                <?php wcusage_setting_text_option('wcusage_field_registration_custom_label_' . esc_html($x), '', '<span class="reg-field-label">' . esc_html__( 'Field Label:', 'woo-coupon-usage' ) . '</span>', '0px'); ?>
              </div>

              <div style="width: auto; float: left; display: block; margin-left: 10px;">
                <p>
              		<?php $type = wcusage_get_setting_value('wcusage_field_registration_custom_type_' . esc_html($x), ''); ?>
              		<strong><?php echo esc_html__( 'Type', 'woo-coupon-usage' ); ?>:</strong><br/>
              		<select name="wcusage_options[wcusage_field_registration_custom_type_<?php echo esc_attr($x); ?>]" id="wcusage_field_registration_custom_type_<?php echo esc_attr($x); ?>" class="wcusage_field_registration_custom_type_<?php echo esc_attr($x); ?>">
                    <option value="text" <?php if($type == "text") { ?>selected<?php } ?>><?php echo esc_html__( 'Text Field', 'woo-coupon-usage' ); ?></option>
                    <option value="textarea" <?php if($type == "textarea") { ?>selected<?php } ?>><?php echo esc_html__( 'Text Area Field', 'woo-coupon-usage' ); ?></option>
              			<option value="dropdown" <?php if($type == "dropdown") { ?>selected<?php } ?>><?php echo esc_html__( 'Dropdown Field', 'woo-coupon-usage' ); ?></option>
              			<option value="checkbox" <?php if($type == "checkbox") { ?>selected<?php } ?>><?php echo esc_html__( 'Checkbox Field', 'woo-coupon-usage' ); ?></option>
                    <option value="radio" <?php if($type == "radio") { ?>selected<?php } ?>><?php echo esc_html__( 'Radio Field', 'woo-coupon-usage' ); ?></option>
                    <option value="acceptance" <?php if($type == "acceptance") { ?>selected<?php } ?>><?php echo esc_html__( 'Acceptance Field', 'woo-coupon-usage' ); ?></option>
                    <option value="date" <?php if($type == "date") { ?>selected<?php } ?>><?php echo esc_html__( 'Date Field', 'woo-coupon-usage' ); ?></option>
                    <option value="header" <?php if($type == "header") { ?>selected<?php } ?>><?php echo esc_html__( 'Custom Header Text', 'woo-coupon-usage' ); ?></option>
                    <option value="paragraph" <?php if($type == "paragraph") { ?>selected<?php } ?>><?php echo esc_html__( 'Custom Paragraph Text', 'woo-coupon-usage' ); ?></option>
                  </select>
                </p>
              </div>

              <div style="width: auto; float: left; margin-left: 10px; margin-bottom: 0px;" class="registration_custom_options_<?php echo esc_attr($x); ?>">
                <?php wcusage_setting_textarea_option('wcusage_field_registration_custom_options_' . esc_html($x), '', esc_html__( 'Options (One Per Line)', 'woo-coupon-usage' ), "0px"); ?>
              </div>

              <div style="width: auto; float: left; display: block; margin-left: 10px;" class="registration_custom_required_<?php echo esc_attr($x); ?>">
                <strong style="display: block; margin-top: 5px; margin-bottom: -5px;"><label for="wcusage_field_registration_custom_required_<?php echo esc_attr($x); ?>"><?php echo esc_html__( 'Required?', 'woo-coupon-usage' ); ?></label></strong>
                <?php wcusage_setting_toggle_option('wcusage_field_registration_custom_required_' . esc_html($x), '', '', '0px'); ?>
              </div>

              <?php if($x > 1) { ?>
              <div style="width: auto; float: left; display: block; margin-left: 10px; margin-top: 25px;">
                  <button id="up-<?php echo esc_html($x); ?>" type="button" title="Move Up"
                    style="background: transparent; border: 0; padding: 0; cursor: pointer;">
                    <span class="fa-solid fa-arrow-up-wide-short"></span>
                  </button>
              </div>
              <?php } ?>

              <script>
              jQuery( document ).ready(function() {
                registration_custom_fields_check_<?php echo esc_html($x); ?>();
              });
              jQuery('.wcusage_field_registration_custom_type_<?php echo esc_html($x); ?>').change(registration_custom_fields_check_<?php echo esc_html($x); ?>);
              function registration_custom_fields_check_<?php echo esc_html($x); ?>() {
                jQuery('.registration_custom_options_<?php echo esc_html($x); ?>').hide();
                var selected_check_<?php echo esc_html($x); ?> = jQuery('.wcusage_field_registration_custom_type_<?php echo esc_html($x); ?> :selected').val();
                if( selected_check_<?php echo esc_html($x); ?> == 'dropdown' || selected_check_<?php echo esc_html($x); ?> == 'radio' ) {
                  jQuery('.registration_custom_options_<?php echo esc_html($x); ?>').show();
                } else {
                  jQuery('.registration_custom_options_<?php echo esc_html($x); ?>').hide();
                }
                if( selected_check_<?php echo esc_html($x); ?> == 'header' || selected_check_<?php echo esc_html($x); ?> == 'paragraph' ) {
                  jQuery('.registration_custom_required_<?php echo esc_html($x); ?>').hide();
                  jQuery('.registration_custom_label_<?php echo esc_html($x); ?> .reg-field-label').text('<?php echo esc_html__( "Text:", "woo-coupon-usage" ); ?>');
                } else {
                  jQuery('.registration_custom_required_<?php echo esc_html($x); ?>').show();
                  jQuery('.registration_custom_label_<?php echo esc_html($x); ?> .reg-field-label').text('<?php echo esc_html__( "Field Label:", "woo-coupon-usage" ); ?>');
                }
              }
              </script>

            </div>

            <br/><br/>

          <?php } ?>
          </div>


          <div style="margin: 6px 0 40px 0;">
            <button type="button" class="button button-primary" id="wcu-add-custom-field">
              <span class="dashicons dashicons-plus-alt2" style="vertical-align: text-bottom;"></span>
              <?php echo esc_html__( 'Add New Field', 'woo-coupon-usage' ); ?>
            </button>
            <button type="button" class="button" id="wcu-remove-last-custom-field" style="margin-left: 6px;">
              <span class="dashicons dashicons-minus" style="vertical-align: text-bottom;"></span>
              <?php echo esc_html__( 'Remove Last Field', 'woo-coupon-usage' ); ?>
            </button>
          </div>

          <hr/>

          <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Automatic Affiliate Registration', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?></h3>

          <!-- Automatically register all new users as an affiliate. -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_auto_new_user', 0, esc_html__( 'Automatically register all new users as an affiliate.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'If enabled, whenever a new user is created (in any way), an affiliate registration will also be submitted for them automatically.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'The username will be used as the coupon code by default, unless you have the "Dynamic Code Generator" enabled.', 'woo-coupon-usage' ); ?></i><br/>

          <br/><hr/>

          <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Checkout Page: Join Affiliate Program', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?></h3>

          <!-- Join affiliate program checkbox -->
          <?php wcusage_setting_toggle_option('wcusage_field_registration_checkout_checkbox', 0, esc_html__( 'Show a "join our affiliate program" checkbox on the store checkout.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'When enabled, a new checkbox will appear on store checkout, under order notes, for them to join the affiliate program. This will submit an affiliate registration application for the user.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'Note: This will only show for users that are not currently assigned to any affiliate coupons. They must also be logged in, or have selected "Create an account?" for it to show.', 'woo-coupon-usage' ); ?></i><br/>

          <?php wcusage_setting_toggle('.wcusage_field_registration_checkout_checkbox', '.wcu-field-section-checkout-checkbox-text'); // Show or Hide ?>
          <span class="wcu-field-section-checkout-checkbox-text">
            <br/>
            <!-- Checkout checkbox label -->
            <?php wcusage_setting_text_option('wcusage_field_registration_checkout_checkbox_text', 'Click here to join our affiliate program', esc_html__( 'Checkbox label', 'woo-coupon-usage' ), '0px'); ?>
            <br/>
            <!-- Join affiliate program checked by default? -->
            <?php wcusage_setting_toggle_option('wcusage_field_registration_checkout_checkbox_checked', 0, esc_html__( 'Checkbox ticked by default?', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'When enabled, the checkbox to join the affiliate program will be checked automatically.', 'woo-coupon-usage' ); ?></i><br/>
          </span>

          <br/><hr/>

          <h3><span class="dashicons dashicons-admin-generic" id="wcu-setting-header-mailing-lists" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Mailing List Integrations', 'woo-coupon-usage' ); ?></h3>

          <i><?php echo esc_html__( 'Connect your affiliate registration system to a mailing list. When a user joins your affiliate program, they will be automatically added to your mailing list.', 'woo-coupon-usage' ); ?></i><br/><br/>
          
          <?php
          $wcusage_mailing_list = wcusage_get_setting_value('wcusage_mailing_list', '');
          ?>
          <select name="wcusage_options[wcusage_mailing_list]" id="wcusage_mailing_list" class="wcusage_mailing_list">
            <option value="0" <?php if($wcusage_mailing_list == "0") { ?>selected<?php } ?>><?php echo esc_html__( '- Disabled -', 'woo-coupon-usage' ); ?></option>
            <option value="newsletter" <?php if($wcusage_mailing_list == "newsletter") { ?>selected<?php } ?>><?php echo esc_html__( 'Built-In Newsletter System', 'woo-coupon-usage' ); ?></option>
            <option value="mailpoet" <?php if($wcusage_mailing_list == "mailpoet") { ?>selected<?php } ?>><?php echo esc_html__( 'Mailpoet', 'woo-coupon-usage' ); ?></option>
            <option value="mailchimp" <?php if($wcusage_mailing_list == "mailchimp") { ?>selected<?php } ?>><?php echo esc_html__( 'Mailchimp', 'woo-coupon-usage' ); ?></option>
            <option value="convertkit" <?php if($wcusage_mailing_list == "convertkit") { ?>selected<?php } ?>><?php echo esc_html__( 'ConvertKit', 'woo-coupon-usage' ); ?></option>
            <option value="mailerlite" <?php if($wcusage_mailing_list == "mailerlite") { ?>selected<?php } ?>><?php echo esc_html__( 'MailerLite', 'woo-coupon-usage' ); ?></option>
            <option value="activecampaign" <?php if($wcusage_mailing_list == "activecampaign") { ?>selected<?php } ?>><?php echo esc_html__( 'ActiveCampaign', 'woo-coupon-usage' ); ?></option>
            <option value="sendinblue" <?php if($wcusage_mailing_list == "sendinblue") { ?>selected<?php } ?>><?php echo esc_html__( 'Brevo (Sendinblue)', 'woo-coupon-usage' ); ?></option>
            <option value="klaviyo" <?php if($wcusage_mailing_list == "klaviyo") { ?>selected<?php } ?>><?php echo esc_html__( 'Klaviyo', 'woo-coupon-usage' ); ?></option>
            <option value="getresponse" <?php if($wcusage_mailing_list == "getresponse") { ?>selected<?php } ?>><?php echo esc_html__( 'GetResponse', 'woo-coupon-usage' ); ?></option>
            <option value="mailjet" <?php if($wcusage_mailing_list == "mailjet") { ?>selected<?php } ?>><?php echo esc_html__( 'Mailjet', 'woo-coupon-usage' ); ?></option>
          </select>

          <script>
          jQuery(document).ready(function() {

              // Define a list of all possible mailing list types
              var allMailingLists = ['newsletter', 'mailpoet', 'mailchimp', 'convertkit', 'mailerlite', 'activecampaign', 'sendinblue', 'klaviyo', 'getresponse', 'mailjet'];

              // Hide all sections initially
              allMailingLists.forEach(function(list) {
                  jQuery('.wcu-list-' + list).hide();
              });

              // Show only the selected mailing list section
              var selectedList = jQuery('.wcusage_mailing_list').val();
              jQuery('.wcu-list-' + selectedList).show();
              jQuery('.wcu-field-section-lists').show();

              jQuery('.wcusage_mailing_list').change(function() {
                  // Hide all sections initially
                  allMailingLists.forEach(function(list) {
                      jQuery('.wcu-list-' + list).hide();
                  });

                  // Show only the selected mailing list section
                  var selectedList = jQuery('.wcusage_mailing_list').val();
                  jQuery('.wcu-list-' + selectedList).show();
                  jQuery('.wcu-field-section-lists').show();
              });

          });
          </script>

          <div class="wcu-field-section-lists" id="wcu-setting-mailing-lists">

            <div class="wcu-list-newsletter"><br/>
              <p><?php echo esc_html__( 'Use the built-in newsletter system to send emails to your affiliates. No external service required.', 'woo-coupon-usage' ); ?></p>
              <br/>
              <p><?php echo esc_html__( 'All registered affiliates will be automatically included in your newsletter campaigns.', 'woo-coupon-usage' ); ?></p>
              <br/>
              <?php
              wcusage_setting_toggle_option('wcusage_field_email_newsletter_enable', 1, esc_html__( 'Enable Built-In Newsletter System', 'woo-coupon-usage' ), '0px');
              ?>
              <br/>
              <a href="#" onclick="wcusage_go_to_settings('#tab-newsletter', ''); return false;">
                <?php echo esc_html__( 'Go to Newsletter Settings', 'woo-coupon-usage' ); ?>
              </a>
            </div>

            <div class="wcu-list-mailpoet"><br/>
              <?php if ( class_exists('MailPoet\API\API') ) { ?>
              <!-- List ID -->
              <p><strong><?php echo esc_html__( 'Add subscriber to list:', 'woo-coupon-usage' ); ?></strong></p>
              <?php
              $wcusage_mailpoet_list_id = wcusage_get_setting_value('wcusage_mailpoet_list_id', '');
              ?>
              <select name="wcusage_options[wcusage_mailpoet_list_id]" id="wcusage_mailpoet_list_id" class="wcusage_mailpoet_list_id">
              <option value=""></option>
              <?php
                $data = \MailPoet\API\API::MP('v1')->getLists();
                if ( !empty($data) ) {
                  foreach ($data as $list) {
                    $list_id = $list['id'];
                    $list_name = $list['name'];
                    echo '<option value="'.$list_id.'"';
                    if($wcusage_mailpoet_list_id == $list_id) { echo ' selected'; }
                    echo '>'.$list_name.'</option>';
                  }
                }
              } else {
              ?>
              <p><?php echo esc_html__( 'MailPoet is not installed or activated.', 'woo-coupon-usage' ); ?></p>
              <p><?php echo esc_html__( 'Please install and activate the MailPoet plugin to use this feature.', 'woo-coupon-usage' ); ?></p>
              <p><?php echo esc_html__( 'You can download MailPoet here:', 'woo-coupon-usage' ); ?> <a href="<?php echo admin_url('plugin-install.php?s=mailpoet&tab=search&type=term'); ?>" target="_blank">Add Plugin</a></p>
              <?php } ?>
              </select>
            </div>

            <div class="wcu-list-mailchimp"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://admin.mailchimp.com/account/api/" target="_blank">https://admin.mailchimp.com/account/api/</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your Audience ID from here:', 'woo-coupon-usage' ); ?> <a href="https://admin.mailchimp.com/lists/" target="_blank">https://admin.mailchimp.com/lists/</a> (Audience > Settings > Audience name and campaign defaults)</p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_mailchimp_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- List ID -->
              <?php wcusage_setting_text_option('wcusage_mailchimp_list_id', '', esc_html__( 'Audience ID', 'woo-coupon-usage' ), '0px'); ?>              
            </div>

            <div class="wcu-list-convertkit"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://app.convertkit.com/account_settings/advanced_settings" target="_blank">https://app.convertkit.com/account_settings/advanced_settings</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your Form ID from here:', 'woo-coupon-usage' ); ?> <a href="https://app.convertkit.com/forms" target="_blank">https://app.convertkit.com/forms</a></p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_convertkit_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- Form ID -->
              <?php wcusage_setting_text_option('wcusage_convertkit_form_id', '', esc_html__( 'Form ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-mailerlite"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://dashboard.mailerlite.com/integrations/api" target="_blank">https://dashboard.mailerlite.com/integrations/api</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can create groups here:', 'woo-coupon-usage' ); ?> <a href="https://dashboard.mailerlite.com/groups" target="_blank">https://dashboard.mailerlite.com/groups/</a> <?php echo esc_html__( 'and view the group ID here:', 'woo-coupon-usage' ); ?> <a href="https://dashboard.mailerlite.com/integrations/api" target="_blank">https://dashboard.mailerlite.com/integrations/api</a></p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_mailerlite_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- Group ID -->
              <?php wcusage_setting_text_option('wcusage_mailerlite_group_id', '', esc_html__( 'Group ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-activecampaign"><br/>
              <p><?php echo esc_html__( 'You can get your API URL from your ActiveCampaign dashboard: Account > Settings > Developer', 'woo-coupon-usage' ); ?></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your List ID from your ActiveCampaign dashboard: Contacts > Lists > Click on the list > View the ID in the URL ("listid").', 'woo-coupon-usage' ); ?></p>
              <br/>
              <!-- API URL -->
              <?php wcusage_setting_text_option('wcusage_activecampaign_api_url', '', esc_html__( 'API URL', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_activecampaign_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- List ID -->
              <?php wcusage_setting_number_option('wcusage_activecampaign_list_id', '', esc_html__( 'List ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-sendinblue"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://app.brevo.com/settings/keys/api" target="_blank">https://app.brevo.com/settings/keys/api</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your List ID from here:', 'woo-coupon-usage' ); ?> <a href="https://app.brevo.com/contact/list-listing" target="_blank">https://app.brevo.com/contact/list-listing</a></p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_sendinblue_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- List ID -->
              <?php wcusage_setting_number_option('wcusage_sendinblue_list_id', '', esc_html__( 'List ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-klaviyo"><br/>
              <p><?php echo esc_html__( 'You can get your private API key from here:', 'woo-coupon-usage' ); ?> <a href="https://www.klaviyo.com/account#api-keys-tab" target="_blank">https://www.klaviyo.com/account#api-keys-tab</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your List ID from here:', 'woo-coupon-usage' ); ?> <a href="https://www.klaviyo.com/lists" target="_blank">https://www.klaviyo.com/lists</a></p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_klaviyo_api_key', '', esc_html__( 'Private API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- List ID -->
              <?php wcusage_setting_text_option('wcusage_klaviyo_list_id', '', esc_html__( 'List ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-getresponse"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://app.getresponse.com/api" target="_blank">https://app.getresponse.com/api</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your List Token from here:', 'woo-coupon-usage' ); ?> <a href="https://app.getresponse.com/lists" target="_blank">https://app.getresponse.com/lists</a> (List Options > Settings)</p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_getresponse_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- Campaign ID -->
              <?php wcusage_setting_text_option('wcusage_getresponse_list_token', '', esc_html__( 'List Token', 'woo-coupon-usage' ), '0px'); ?>
            </div>

            <div class="wcu-list-mailjet"><br/>
              <p><?php echo esc_html__( 'You can get your API key from here:', 'woo-coupon-usage' ); ?> <a href="https://app.mailjet.com/account/apikeys" target="_blank">https://app.mailjet.com/account/apikeys</a></p>
              <br/>
              <p><?php echo esc_html__( 'You can get your Contact List ID from here:', 'woo-coupon-usage' ); ?> <a href="https://app.mailjet.com/contacts/lists" target="_blank">https://app.mailjet.com/contacts/lists</a></p>
              <br/>
              <!-- API Key -->
              <?php wcusage_setting_text_option('wcusage_mailjet_api_key', '', esc_html__( 'API Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- Secret Key -->
              <?php wcusage_setting_text_option('wcusage_mailjet_secret_key', '', esc_html__( 'Secret Key', 'woo-coupon-usage' ), '0px'); ?>
              <br/>
              <!-- List ID -->
              <?php wcusage_setting_number_option('wcusage_mailjet_list_id', '', esc_html__( 'Contact List ID', 'woo-coupon-usage' ), '0px'); ?>
            </div>

          </div>

      </div>

      <?php do_action( 'wcusage_hook_setting_section_registration_end' ); ?>

    </span>

 <?php
}

/**
 * Settings Section: Registration Page
 *
 */
add_action( 'wcusage_hook_setting_section_registration_page', 'wcusage_setting_section_registration_page' );
if( !function_exists( 'wcusage_setting_section_registration_page' ) ) {
  function wcusage_setting_section_registration_page() {

    $options = get_option( 'wcusage_options' );
    ?>

    <?php if (!class_exists('SitePress')) { ?>

      <!-- Registration Form Page Dropdown -->
      <strong><?php echo esc_html__( 'Registration Form Page:', 'woo-coupon-usage' ); ?></strong><br/>
      <?php
      $registrationpage = "";
      if ( isset($options['wcusage_registration_page']) && $options['wcusage_registration_page'] ) {
          $registrationpage = $options['wcusage_registration_page'];
      } else {
          $registrationpage = wcusage_get_registration_shortcode_page_id();
          // Only update on non-GET requests
          if ( $_SERVER['REQUEST_METHOD'] !== 'GET' && $registrationpage ) {
            $options['wcusage_registration_page'] = $registrationpage;
            update_option( 'wcusage_options', $options );
          }
      }

      $dropdown_args = array(
          'post_type'        => 'page',
          'selected'         => $registrationpage,
          'name'             => 'wcusage_options[wcusage_registration_page]',
          'id'               => 'wcusage_registration_page',
          'value_field'      => 'wcusage_registration_page',
          'show_option_none' => '-',
      );
      foreach ( $dropdown_args as $key => $value ) {
          if ( is_string( $value ) ) {
              $dropdown_args[ $key ] = esc_attr( $value );
          }
      }
      wp_dropdown_pages( $dropdown_args );
      ?>

      <br/><i><?php echo esc_html__( '(The page that has the [couponaffiliates-register] shortcode on.)', 'woo-coupon-usage' ); ?></i>
      
      <br/>

      <?php
      // Show the link
      echo "<a id='registration_link' style='margin-top: 10px; display: inline-block;' href='".esc_url(get_permalink($registrationpage))."' target='_blank'>".esc_url(get_permalink($registrationpage))."</a>";
      ?>
      <?php if($registrationpage) { ?>
      <br/>
      <?php } ?>
      <script type="text/javascript">
      jQuery(document).ready(function($) {
          // Update the link when the dropdown changes
          $('#wcusage_registration_page').on('change', function() {
              var pageID = $(this).val();
              if (pageID) {
                  $.post(
                      '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                      {
                          'action': 'wcusage_get_permalink',
                        'page_id': pageID,
                        'nonce': '<?php echo esc_js( wp_create_nonce( 'wcusage_get_permalink_nonce' ) ); ?>'
                      },
                      function(response) {
                          $('#registration_link').attr('href', response).text(response);
                      }
                  );
              } else {
                  $('#registration_link').attr('href', '#').text('');
              }
              check_registration_page_shortcode();
          });
          // Check if the selected page contains the shortcode
          function check_registration_page_shortcode() {
              var pageID = $('#wcusage_registration_page').val();
              if (!pageID) {
                  $('.registration_shortcode_check').show();
                  return;
              }
              $.post(
                  '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                  {
                      'action': 'wcusage_check_registration_shortcode',
                      'page_id': pageID,
                    // Nonce for AJAX security check
                    'nonce': '<?php echo esc_js( wp_create_nonce( 'wcusage_check_registration_shortcode' ) ); ?>'
                  },
                  function(response) {
                    // Back-compat: older versions returned plain 1/0. New versions return JSON.
                    var hasShortcode = false;
                    if (typeof response === 'object' && response !== null && typeof response.success !== 'undefined') {
                      hasShortcode = !!(response.success && response.data && response.data.has_shortcode);
                    } else {
                      hasShortcode = (response == 1);
                    }
                    if (hasShortcode) {
                          $('.registration_shortcode_check').hide();
                      } else {
                          $('.registration_shortcode_check').show();
                      }
                  }
              );
          }
          // Generate a new registration page on button click
          $('#wcu-generate-registration-page').on('click', function() {
              // Disable button and change to spinner
              $(this).prop('disabled', true).html('<span class="spinner"></span> <?php echo esc_html__( 'Generating...', 'woo-coupon-usage' ); ?>');
              // Make the AJAX request to generate the page
              $.post(
                  '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                  {
                      'action': 'wcusage_generate_registration_page'
                      , 'nonce': '<?php echo esc_js( wp_create_nonce( 'wcusage_generate_registration_page' ) ); ?>'
                  },
                  function(response) {
                      if (response.success) {
                          // Add the new page to the dropdown
                          var newOption = $('<option></option>')
                              .val(response.data.page_id)
                              .text(response.data.page_title)
                              .prop('selected', true);
                          $('#wcusage_registration_page').append(newOption);
                          
                          // Update the link
                          $('#registration_link')
                              .attr('href', response.data.permalink)
                              .text(response.data.permalink);
                          
                          // Hide the error message since the new page has the shortcode
                          $('.registration_shortcode_check').hide();

                          // Remove .wcusage-checklist-registration
                          $('.wcusage-checklist-registration').remove();
                      } else {
                          alert('Error: ' + response.data.message);
                      }
                      // Re-enable the button and reset its text
                      $('#wcu-generate-registration-page').prop('disabled', false).html('<?php echo esc_html__( 'Generate Registration Page', 'woo-coupon-usage' ); ?> <span class="fa-solid fa-arrow-right"></span>');
                  }
              );
          });

          // Initial check for shortcode
          $('.registration_shortcode_check').hide();
          check_registration_page_shortcode();
      });
      </script>

    <?php } else { ?>

      <!-- Showing number input if WPML installed -->
      <?php wcusage_setting_number_option('wcusage_registration_page', '', esc_html__( 'Registration Form Page (ID):', 'woo-coupon-usage' ), '0px'); ?>

    <?php } ?>

    <div class="setup-hide">

      <div class="registration_shortcode_check" style="margin-bottom: 0px; font-size: 12px; margin-top: 10px; color: red; display: none;">

        <?php echo esc_html__( '(ERROR) This page does not contain the shortcode:', 'woo-coupon-usage' ); ?> <strong>[couponaffiliates-register]</strong><br/>
        <?php echo esc_html__( 'Please add the shortcode to a new page, and select it from the dropdown above.', 'woo-coupon-usage' ); ?><br/>

        <?php echo esc_html__('Or you can click the button below to automatically generate the page for you:', 'woo-coupon-usage'); ?>

        <br/><br/>

        <button type="button" id="wcu-generate-registration-page" class="button" style="margin-top: 10px;">
          <?php echo esc_html__('Generate Registration Page', 'woo-coupon-usage'); ?> <span class="fa-solid fa-arrow-right"></span>
        </button>
        
      </div>

      <br/>

      <p style="margin-bottom: 0px; font-size: 12px;">
        <?php echo esc_html__( 'Create a more effective signup promo page design with the generator tool:', 'woo-coupon-usage' ); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=signup-page-generator')); ?>" target="_blank" style="font-weight: bold; text-decoration: none;"><?php echo esc_html__( 'Generate Promo Page', 'woo-coupon-usage' ); ?> <span class="fa-solid fa-arrow-up-right-from-square"></span></a>
      </p>

  </div>

  <?php
  }
}

/**
 * Settings Section: Registration Page Template
 *
 */
add_action( 'wcusage_hook_setting_section_registration_template', 'wcusage_setting_section_registration_template' );
if( !function_exists( 'wcusage_setting_section_registration_template' ) ) {
  function wcusage_setting_section_registration_template() {

    $options = get_option( 'wcusage_options' );

    $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
    $probrackets = ( $ispro ? "" : " (PRO)" );
    ?>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Template Coupon', 'woo-coupon-usage' ); ?> <span style="color: red;"><?php echo esc_html__( '(Required)', 'woo-coupon-usage' ); ?></span></h3>

    <p>
      <a href="<?php echo esc_url(admin_url('post-new.php?post_type=shop_coupon')); ?>" target="_blank" style="font-weight: bold; text-decoration: none;"><?php echo esc_html__( 'Create a new coupon code', 'woo-coupon-usage' ); ?> <span class="fa-solid fa-arrow-up-right-from-square"></span></a> <?php echo esc_html__( 'as a template and enter the exact name of it below.', 'woo-coupon-usage' ); ?><br/>
    </p>
    <p>  
      <?php echo esc_html__( 'When you accept an affiliate, a new coupon is created with the same settings, and is automatically assigned to user.', 'woo-coupon-usage' ); ?><br/>
    </p>

    <div class="wcu-field-section-registration-single">

    <?php if( isset($_GET['page']) && $_GET['page'] == 'wcusage_settings' ) { ?>
    <br/>
    <?php } ?>

    <?php
    if( ( isset($_GET['page']) && $_GET['page'] == 'wcusage_setup' ) && empty($options['wcusage_field_registration_coupon_template'])) { ?>
    <span id="wcusage_generate_coupon" style="display: block; margin: 10px 0 15px 0;">
    
    <button type="button" onclick="showCouponFields()" class="submit-generate-page" style="margin-top: 10px;">
      <?php echo esc_html__( "Generate Coupon", "woo-coupon-usage" ); ?> <span class="fa-solid fa-arrow-right"></span></button>

    <div id="couponFields" style="display: none; padding: 5px 10px 20px 10px; margin-top: 10px; background: #f3f3f3; border-radius: 5px;">

    <p><?php echo esc_html__("Use this quick form to generate a template coupon code automatically with the basic default settings. You can customise the more advanced coupon settings later if needed.", "woo-coupon-usage"); ?></p>

      <p><strong><label for="coupon_type">Coupon Type:</label></strong></p>
      <select name="coupon_type" id="coupon_type">
        <?php
        $coupon_types = wc_get_coupon_types();
        foreach ($coupon_types as $key => $coupon_type) {
          echo '<option value="' . esc_attr($key) . '">' . esc_html($coupon_type) . '</option>';
        }
        ?>
      </select>
      <p><strong><label for="coupon_discount"><?php echo esc_html__( 'Coupon Discount Amount:', 'woo-coupon-usage' ); ?></label></strong></p>
      <!-- Do not allow text input, only numbers -->
       
      <input type="number" name="coupon_discount" id="coupon_discount" step="0.01" min="0">

      <p>
      <button type="button" onclick="generateCoupon()"><?php echo esc_html__( "Generate", "woo-coupon-usage" ); ?> <span class="fa-solid fa-arrow-right"></span></button>
      </p>
    </div>
    <script>
      function showCouponFields() {
        var couponFields = document.getElementById("couponFields");
        couponFields.style.display = "block";
      }

      function generateCoupon() {
        var couponType = document.getElementById("coupon_type").value;
        var couponDiscount = document.getElementById("coupon_discount").value;
        var nonce = '<?php echo wp_create_nonce("generate_coupon"); ?>';
        var url = '<?php echo esc_url(admin_url()); ?>admin.php?page=wcusage_setup&step=2&action=generate_coupon&coupon_type=' + couponType + '&coupon_discount=' + couponDiscount + '&_wpnonce=' + nonce;
        window.location.href = url;
      }
    </script>

    <?php
    $nonce = $_GET['_wpnonce'];
    if(isset($_GET['coupon_type']) && isset($_GET['coupon_discount']) && wp_verify_nonce($nonce, 'generate_coupon')) {

      // Get coupon code and discount from the URL parameters
      $couponName = "affiliate_template_coupon";
      $couponType = $_GET['coupon_type'];
      $couponDiscount = $_GET['coupon_discount'];

      // Sanitize input
      $couponDiscount = floatval($couponDiscount);
      $couponType = sanitize_text_field($couponType);

      // Check if coupon "affiliate_template_coupon" already exists
      $coupon_id = wc_get_coupon_id_by_code($couponName);

      // Generate a brand new coupon code in WooCommerce with name $couponName, type $couponType and discount $couponDiscount and the rest of the default settings
      if(!$coupon_id) {
        $coupon = new WC_Coupon();
        $coupon->set_code($couponName);
        $coupon->set_discount_type($couponType);
        $coupon->set_amount($couponDiscount);
        $coupon_id = $coupon->save();
      } else {
        // update existing code
        update_post_meta($coupon_id, 'discount_type', $couponType);
        update_post_meta($coupon_id, 'coupon_amount', $couponDiscount);
      }
      
      // Use merge helper to preserve other settings
      if ( function_exists( 'wcusage_update_options_merge' ) ) {
        wcusage_update_options_merge( array( 'wcusage_field_registration_coupon_template' => $couponName ) );
      } else {
        $options = get_option( 'wcusage_options' );
        $options['wcusage_field_registration_coupon_template'] = $couponName;
        update_option( 'wcusage_options', $options );
      }
      ?>
      <script>
      jQuery(document).ready(function() {
        jQuery('#wcusage_field_registration_coupon_template').val('<?php echo esc_html($couponName); ?>');
        jQuery('#wcusage_generate_coupon').hide();
      });
      </script>
      <?php
      // remove parameters from URL except page and step
      $url = strtok($_SERVER["REQUEST_URI"],'?');
      $url .= '?page=wcusage_setup&step=2';
      wp_safe_redirect( $url );
      exit;
    }
    ?>

    </span>
    <?php } ?>

      <!-- Template coupon code for new affiliate coupon generation -->
      <?php wcusage_setting_text_option('wcusage_field_registration_coupon_template', '', esc_html__( 'Template coupon code:', 'woo-coupon-usage' ), '0px'); ?>
      <p><div id="edit_link"></div></p>
      <i><?php echo esc_html__( 'Make sure this matches the exact name of an existing template coupon code (case sensitive).', 'woo-coupon-usage' ); ?></i>
      <script>
        jQuery('#wcusage_field_registration_coupon_template').on('keyup', function() {
          jQuery('.registration_template_1 #wcusage_field_registration_coupon_template').val(jQuery('#wcusage_field_registration_coupon_template').val());
        });
      </script>
      <!-- Edit Link -->
      <script>
      jQuery(document).ready(function() {
          function validateCouponTemplate() {
              var couponName = jQuery('#wcusage_field_registration_coupon_template').val();
              jQuery.post(
                  ajaxurl, 
                  {
                      action: 'wcusage_ajax_get_coupon_id',
                      coupon_name: couponName,
                      nonce: '<?php echo wp_create_nonce("wcusage_ajax_get_coupon_id_nonce"); ?>'
                  },
                  function(couponId) {
                      if (!couponId || !couponName) {
                          jQuery('#edit_link').html('');
                          jQuery('#submit_step2').prop('disabled', true);
                          return;
                      }
                      if (couponId == 0) {
                          jQuery('#edit_link').html('<p style="color: red;">Invalid coupon! This should be the exact name of an existing coupon code.</p>');
                          jQuery('#submit_step2').prop('disabled', true);
                          return;
                      }
                      var editLink = "<?php echo esc_url(admin_url()); ?>post.php?post=" + couponId + "&action=edit";
                      jQuery('#edit_link').html('<span class="fa-solid fa-circle-check" style="color: green; margin-right: 5px;"></span> <a href="' + editLink + '" target="_blank" style="font-weight: bold; text-decoration: none;">Edit Coupon <span class="fa-solid fa-arrow-up-right-from-square"></span></a>');
                      jQuery('#submit_step2').prop('disabled', false);
                  }
              );
          }

          jQuery('#wcusage_field_registration_coupon_template').on('change', validateCouponTemplate);
          validateCouponTemplate();
      });
      </script>

      <br/>

    </span>

    </div>

  <?php
  }
}

/**
 * Settings Section: Registration Page Template Multi
 *
 */
add_action( 'wcusage_hook_setting_section_registration_template2', 'wcusage_setting_section_registration_template2' );
if( !function_exists( 'wcusage_setting_section_registration_template2' ) ) {
  function wcusage_setting_section_registration_template2() {

    $options = get_option( 'wcusage_options' );

    $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
    $probrackets = ( $ispro ? "" : " (PRO)" );
    ?>

    <div id="pro-settings" <?php if ( !wcu_fs()->can_use_premium_code() ) { ?>class="settings-area setup-hide premium-only-settings"
    title="Available with Pro version." style="pointer-events:none; opacity: 0.4;"<?php } else { ?>class="settings-area setup-hide"<?php } ?>>

      <br/>

      <!-- Multiple Template Coupons -->
      <?php wcusage_setting_toggle_option('wcusage_field_registration_multiple_template', 0, esc_html__( 'Enable Multiple Templates', 'woo-coupon-usage' ) . esc_html($probrackets), '0px'); ?>
      <i><?php echo esc_html__( 'With this enabled, multiple template coupons will be available and the affiliate will be able to choose which type they want via the registration form.', 'woo-coupon-usage' ); ?></i>

      <script>
      jQuery( document ).ready(function() {
        if(jQuery('.wcusage_field_registration_multiple_template').prop('checked')) {
          jQuery('.wcu-field-section-registration-single').hide();
        }
        jQuery('.wcusage_field_registration_multiple_template').change(function(){
          if(jQuery(this).prop('checked')) {
            jQuery('.wcu-field-section-registration-single').hide();
          } else {
            jQuery('.wcu-field-section-registration-single').show();
          }
        });
      });
      </script>

      <?php wcusage_setting_toggle('.wcusage_field_registration_multiple_template', '.wcu-field-section-registration-templates'); // Show or Hide ?>
      <span class="wcu-field-section-registration-templates">

        <br/><i><?php echo esc_html__( 'Make sure template codes match the exact name of an existing template coupon code, otherwise the coupon may not be created automatically.', 'woo-coupon-usage' ); ?></i>
        <br/><br/>

        <?php wcusage_setting_text_option('wcusage_field_registration_coupon_template_field', 'What type of coupon would you like?', esc_html__( 'Select field label:', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_registration_multiple_template_roles', 0, esc_html__( 'Assign user roles (groups) to specific templates', 'woo-coupon-usage' ) . esc_html($probrackets), '0px'); ?>
        <i><?php echo esc_html__( 'With this enabled, you will be able assign a user role to a template coupon, so when someone registers via that template option, they will also be assigned to this user role.', 'woo-coupon-usage' ); ?></i>
        
        <script>
        jQuery( document ).ready(function() {
          if(jQuery('.wcusage_field_registration_multiple_template_roles').prop('checked')) {
            jQuery('.wcu-field-section-registration-roles').show();
          } else {
            jQuery('.wcu-field-section-registration-roles').hide();
          }
          jQuery('.wcusage_field_registration_multiple_template_roles').change(function(){
            if(jQuery(this).prop('checked')) {
              jQuery('.wcu-field-section-registration-roles').show();
            } else {
              jQuery('.wcu-field-section-registration-roles').hide();
            }
          });
        });
        </script>

        <br/><br/>

        <?php for ($x = 1; $x <= 10; $x++) { ?>

          <?php
          if($x == 1) {
            $template_default = "Default";
            $template_num = "";
          } else {
            $template_default = "";
            $template_num = "_" . esc_html($x);
          }
          ?>

          <div style="width: 100%; display: inline-block;" class="registration_template_<?php echo esc_html($x); ?>">
            <br/><strong style="display: block; margin-bottom: 5px; text-decoration: underline;">Template option #<?php echo esc_html($x); ?></strong>
            <div style="width: auto; float: left; display: block;">
              <?php wcusage_setting_text_option('wcusage_field_registration_coupon_template_label' . esc_html($template_num), $template_default, esc_html__( 'Option name:', 'woo-coupon-usage' ), '0px'); ?>
            </div>
            <div style="width: auto; float: left; display: block; margin-left: 10px;">
              <?php
              wcusage_setting_text_option('wcusage_field_registration_coupon_template' . esc_html($template_num), '', esc_html__( 'Template coupon code:', 'woo-coupon-usage' ), '0px');
              $get_code = $options['wcusage_field_registration_coupon_template' . esc_html($template_num)] ?? '';
              // Check if coupon exists, if not, show error message
              $coupon_id = wc_get_coupon_id_by_code($get_code);
              if(!$coupon_id && $get_code) {
                echo '<p style="color: red; font-size: 12px;">' . esc_html__( 'This coupon does not exist!', 'woo-coupon-usage' ) . '</p>';
              }
              ?>
            </div>
            <!-- User Role -->
            <div style="width: auto; float: left; display: block; margin-left: 10px;" class="wcu-field-section-registration-roles">
              <p style="margin: 0;"><strong>
                <label for="wcusage_field_registration_coupon_template_role<?php echo esc_html($template_num); ?>"><?php echo esc_html__( 'Assign to role:', 'woo-coupon-usage' ); ?></label>
              </strong></p>
              <select name="wcusage_options[wcusage_field_registration_coupon_template_role<?php echo esc_html($template_num); ?>]" id="wcusage_field_registration_coupon_template_role<?php echo esc_html($template_num); ?>">
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
                ?>
                <option value="">
                  <?php echo esc_html__( '-', 'woo-coupon-usage' ); ?>
                </option>
                <?php foreach ($roles2 as $role => $details) {
                  $role_name = $details['name'];
                  if (strpos($role, 'coupon_affiliate') !== false) {
                    $role_name = "(Group) " . $role_name;
                  }
                  ?>
                  <?php if($role != 'administrator' && $role != 'editor' && $role != 'author' && $role != 'shop_manager' && (!is_array($details['capabilities']) || !array_key_exists( 'manage_options', $details['capabilities'] )) ) { ?>
                    <option value="<?php echo esc_html($role); ?>"
                    <?php if(isset($options['wcusage_field_registration_coupon_template_role' . esc_html($template_num)]) && $options['wcusage_field_registration_coupon_template_role' . esc_html($template_num)] == $role) { ?>selected<?php } ?>
                    ><?php echo esc_html($role_name);?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </div>
            <br/>
          </div>
          <script>
          jQuery( document ).ready(function() {
            registration_fields_check();
            jQuery('#wcusage_field_registration_coupon_template<?php echo esc_html($template_num); ?>').change(registration_fields_check);
            function registration_fields_check() {
              if(!jQuery('#wcusage_field_registration_coupon_template<?php echo esc_html($template_num); ?>').val()) {
                jQuery('.registration_template_<?php echo esc_html($x) + 1; ?>').hide();
              } else {
                jQuery('.registration_template_<?php echo esc_html($x) + 1; ?>').show();
              }
            }
          });
          </script>

        <?php } ?>

        <br/>

      </div>

  <?php
  }
}

/**
 * Update custom fields
 *
 */
 function wcusage_update_custom_fields() {

  check_ajax_referer( 'wcusage_custom_fields', '_ajax_nonce' );

  if ( ! function_exists( 'wcusage_check_admin_access' ) || ! wcusage_check_admin_access() ) {
    die();
  }
  ?>

  <?php
  $label = sanitize_text_field($_POST["label"]);
  $type = sanitize_text_field($_POST["type"]);
  $options = sanitize_textarea_field($_POST["options"]);
  $required = sanitize_text_field($_POST["required"]);

  $label_this = sanitize_text_field($_POST["label_this"]);
  $type_this = sanitize_text_field($_POST["type_this"]);
  $options_this = sanitize_textarea_field($_POST["options_this"]);
  $required_this = sanitize_text_field($_POST["required_this"]);

  $current = sanitize_text_field($_POST["current"]);
  $before = sanitize_text_field($_POST["before"]);

  $option_group = get_option('wcusage_options');

  $updates = array(
    'wcusage_field_registration_custom_label_' . $current => $label,
    'wcusage_field_registration_custom_type_' . $current => $type,
    'wcusage_field_registration_custom_options_' . $current => $options,
    'wcusage_field_registration_custom_required_' . $current => $required,
    'wcusage_field_registration_custom_label_' . $before => $label_this,
    'wcusage_field_registration_custom_type_' . $before => $type_this,
    'wcusage_field_registration_custom_options_' . $before => $options_this,
    'wcusage_field_registration_custom_required_' . $before => $required_this
  );

  // Use merge helper to preserve other settings
  if ( function_exists( 'wcusage_update_options_merge' ) ) {
    wcusage_update_options_merge( $updates );
  } else {
    foreach ( $updates as $key => $value ) {
      $option_group[$key] = $value;
    }
    update_option( 'wcusage_options', $option_group );
  }

  return true;

  exit;

}
add_action( 'wp_ajax_wcusage_update_custom_fields', 'wcusage_update_custom_fields' );

/**
 * Update custom fields count (AJAX)
 */
function wcusage_update_custom_fields_count() {
  check_ajax_referer( 'wcusage_custom_fields', '_ajax_nonce' );

  if ( ! function_exists( 'wcusage_check_admin_access' ) || ! wcusage_check_admin_access() ) {
    wp_send_json_error( array( 'message' => __( 'Access denied.', 'woo-coupon-usage' ) ), 403 );
  }

  $count = isset($_POST['count']) ? intval($_POST['count']) : 0;
  if ($count < 0) $count = 0;
  if ($count > 200) $count = 200; // hard upper bound safety

  // Use merge helper to preserve other settings
  if ( function_exists( 'wcusage_update_options_merge' ) ) {
    wcusage_update_options_merge( array( 'wcusage_field_registration_custom_fields' => $count ) );
  } else {
    $option_group = get_option('wcusage_options');
    $option_group['wcusage_field_registration_custom_fields'] = $count;
    update_option( 'wcusage_options', $option_group );
  }

  wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_wcusage_update_custom_fields_count', 'wcusage_update_custom_fields_count' );

// Function to check wcusage_check_registration_shortcode
add_action( 'wp_ajax_wcusage_check_registration_shortcode', 'wcusage_check_registration_shortcode' );
function wcusage_check_registration_shortcode() {

  // Capability check: restrict to plugin admin access
  if ( ! function_exists( 'wcusage_check_admin_access' ) || ! wcusage_check_admin_access() ) {
    wp_send_json_error( array( 'message' => __( 'Access denied.', 'woo-coupon-usage' ) ), 403 );
  }

  // Nonce check (support both new and legacy action strings)
  $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
  $nonce_ok = ( $nonce && ( wp_verify_nonce( $nonce, 'wcusage_check_registration_shortcode' ) || wp_verify_nonce( $nonce, 'wcusage_check_registration_shortcode_nonce' ) ) );
  if ( ! $nonce_ok ) {
    wp_send_json_error( array( 'message' => __( 'Security check failed.', 'woo-coupon-usage' ) ), 400 );
  }

  $page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
  if ( ! $page_id ) {
    wp_send_json_success( array( 'has_shortcode' => false ) );
  }

  $page = get_post( $page_id );
  if ( ! $page ) {
    wp_send_json_success( array( 'has_shortcode' => false ) );
  }

  $content = isset( $page->post_content ) ? (string) $page->post_content : '';
  $has_shortcode = ( strpos( $content, '[couponaffiliates-register]' ) !== false );
  wp_send_json_success( array( 'has_shortcode' => $has_shortcode ) );
}

/*
* Function to handle the AJAX request for generating the registration page
*/
add_action('wp_ajax_wcusage_generate_registration_page', 'wcusage_generate_registration_page');
function wcusage_generate_registration_page() {

  // Capability check: restrict to plugin admin access
  if ( ! function_exists( 'wcusage_check_admin_access' ) || ! wcusage_check_admin_access() ) {
    wp_send_json_error( array( 'message' => __( 'Access denied.', 'woo-coupon-usage' ) ), 403 );
  }

  // Nonce check for CSRF protection
  $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'wcusage_generate_registration_page' ) ) {
    wp_send_json_error( array( 'message' => __( 'Invalid request. Please refresh and try again.', 'woo-coupon-usage' ) ), 400 );
  }

  $current_user_id = get_current_user_id();

    // If /affiliate-registration/ page already exists, create a unique slug
    $post_name = 'affiliate-registration';
    $existing_page = get_page_by_path( $post_name );
    if ( $existing_page ) {
        $suffix = 2;
        while ( get_page_by_path( $post_name . '-' . $suffix ) ) {
            $suffix++;
        }
        $post_name = $post_name . '-' . $suffix;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'posts';
    $wpdb->insert(
      $table_name,
      array(
        'post_title'     => 'Affiliate Registration',
        'post_type'      => 'page',
        'post_name'      => $post_name,
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_content'   => '[couponaffiliates-register]',
        'post_status'    => 'publish',
        'post_author'    => $current_user_id,
      )
    );
    $page_id = $wpdb->insert_id;

    // Use merge helper to preserve other settings
    if ( function_exists( 'wcusage_update_options_merge' ) ) {
      wcusage_update_options_merge( array( 'wcusage_registration_page' => $page_id ) );
    } else {
      $option_group = get_option('wcusage_options');
      $option_group['wcusage_registration_page'] = $page_id;
      update_option( 'wcusage_options', $option_group );
    }
    
    if (!is_wp_error($page_id)) {
        // Get the page permalink
        $permalink = get_permalink($page_id);
        // Return the page ID, title, and permalink as JSON
        wp_send_json_success(array(
            'page_id'    => $page_id,
            'page_title' => 'Affiliate Registration',
            'permalink'  => $permalink,
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Failed to create the page.', 'woo-coupon-usage'),
        ));
    }
    
    wp_die();
}