<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Get a sanitized prefill value for the registration form.
function wcusage_registration_prefill_value(  $key, $fallback = ''  ) {
    if ( isset( $GLOBALS['wcusage_registration_prefill'] ) && is_array( $GLOBALS['wcusage_registration_prefill'] ) && array_key_exists( $key, $GLOBALS['wcusage_registration_prefill'] ) ) {
        return (string) $GLOBALS['wcusage_registration_prefill'][$key];
    }
    return (string) $fallback;
}

/*
* WP Head Check Registration Form
*
*/
add_action( 'wp_head', 'wcusage_registration_form_wp_head' );
function wcusage_registration_form_wp_head() {
    // Check if registration is enabled
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    if ( !$wcusage_field_registration_enable ) {
        return;
    }
    if ( !isset( $_POST['submitaffiliateapplication'] ) ) {
        return;
    }
    // Only run if current page is not the registration page
    $wcusage_registration_page = wcusage_get_setting_value( 'wcusage_registration_page', '' );
    if ( $wcusage_registration_page && is_page( $wcusage_registration_page ) ) {
        return;
    }
    // Only run if current page is not affiliate dashboard page
    $wcusage_dashboard_page = wcusage_get_setting_value( 'wcusage_dashboard_page', '' );
    if ( $wcusage_dashboard_page && is_page( $wcusage_dashboard_page ) ) {
        return;
    }
    if ( isset( $_POST['wcusage_submit_registration_form1'] ) && isset( $_POST['submitaffiliateapplication'] ) ) {
        // Skip wp_head processing for widget submissions
        if ( isset( $_POST['wcu-form-type'] ) && $_POST['wcu-form-type'] == 'widget' ) {
            if ( $wcusage_registration_page ) {
                return;
            }
        }
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form1'] ) ), 'wcusage_verify_submit_registration_form1' ) || is_user_logged_in() ) {
            $submit_form = wcusage_post_submit_application( 0 );
            ?>
      <script>
      jQuery(document).ready(function($) {
        // Get .wcu-registration-response as response
        var response = $('.wcu-registration-response');
        var pageTitle = $('.page-title');
        if(pageTitle.length) {
          response.insertAfter(pageTitle);
        }
        // If no .page-title insert before .entry-content
        if( !pageTitle.length ) {
          var entryContent = $('.entry-content');
          if(entryContent.length) {
            response.insertBefore(entryContent);
          } else {
            $('.wrap').prepend(response);
          }
        }
      });
      </script>
      <?php 
        }
    }
}

/*
* Shortcode to display registration form
*
*/
function wcusage_couponusage_register(  $atts  ) {
    // Get the attributes
    $atts = shortcode_atts( array(
        'template' => '',
        'type'     => '',
    ), $atts );
    // Check if registration is enabled - if not, return empty string
    // This check is inside the function to ensure shortcode always exists
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    if ( !$wcusage_field_registration_enable ) {
        return '';
    }
    ob_start();
    // Used to hide the form after a successful submit (avoids relying on <style> output that may be sanitized).
    unset($GLOBALS['wcusage_registration_hide_form']);
    unset($GLOBALS['wcusage_registration_prefill']);
    $options = get_option( 'wcusage_options' );
    $current_user_id = get_current_user_id();
    $user_info = get_userdata( $current_user_id );
    $wcusage_registration_enable_logout = wcusage_get_setting_value( 'wcusage_field_registration_enable_logout', '1' );
    $enable_captcha = wcusage_get_setting_value( 'wcusage_registration_enable_captcha', '' );
    $wcusage_registration_recaptcha_key = wcusage_get_setting_value( 'wcusage_registration_recaptcha_key', '' );
    $wcusage_registration_recaptcha_secret = wcusage_get_setting_value( 'wcusage_registration_recaptcha_secret', '' );
    $wcusage_registration_turnstile_key = wcusage_get_setting_value( 'wcusage_registration_turnstile_key', '' );
    $wcusage_registration_turnstile_secret = wcusage_get_setting_value( 'wcusage_registration_turnstile_secret', '' );
    $wcusage_field_registration_emailusername = wcusage_get_setting_value( 'wcusage_field_registration_emailusername', '0' );
    $wcusage_field_registration_enable_terms = wcusage_get_setting_value( 'wcusage_field_registration_enable_terms', '' );
    $wcusage_field_registration_terms_message = wcusage_get_setting_value( 'wcusage_field_registration_terms_message', 'I have read and agree to the Affiliate Terms and Privacy Policy.' );
    $wcusage_registration_enable_admincan = wcusage_get_setting_value( 'wcusage_field_registration_enable_admincan', '0' );
    $auto_coupon = "";
    $auto_coupon_format = "";
    // Handle form submission
    $form_response = '';
    if ( isset( $_POST['wcusage_submit_registration_form1'] ) && isset( $_POST['submitaffiliateapplication'] ) ) {
        // Skip widget submissions that should redirect
        if ( !(isset( $_POST['wcu-form-type'] ) && $_POST['wcu-form-type'] == 'widget') ) {
            if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form1'] ) ), 'wcusage_verify_submit_registration_form1' ) || is_user_logged_in() ) {
                ob_start();
                wcusage_post_submit_application( 0 );
                $form_response = ob_get_clean();
            }
        }
    }
    ?>

    <?php 
    if ( is_page() ) {
        ?>
      <?php 
        do_action( 'wcusage_hook_custom_styles' );
        // Custom Styles
        ?>
    <?php 
    }
    ?>

    <?php 
    if ( isset( $_SESSION["wcu_registration_token"] ) ) {
        unset($_SESSION["wcu_registration_token"]);
    }
    ?>

    <?php 
    if ( $enable_captcha == "1" && isset( $options['wcusage_registration_recaptcha_key'] ) && !wp_script_is( 'g-recaptcha', 'enqueued' ) ) {
        wp_enqueue_script(
            'g-recaptcha',
            'https://www.google.com/recaptcha/api.js',
            array(),
            '1.0.0',
            true
        );
    }
    if ( $enable_captcha == "2" && isset( $options['wcusage_registration_turnstile_key'] ) && !wp_script_is( 'cf-turnstile', 'enqueued' ) ) {
        wp_enqueue_script(
            'cf-turnstile',
            'https://challenges.cloudflare.com/turnstile/v0/api.js',
            array(),
            '1.0.0',
            true
        );
    }
    ?>

    <?php 
    if ( !wcusage_is_customer_blacklisted() ) {
        ?>

      <?php 
        if ( is_user_logged_in() || $wcusage_registration_enable_logout && $wcusage_field_registration_enable ) {
            ?>

      <div class="wcu-form-section<?php 
            ?> wcu-form-section-free<?php 
            ?>">

      <?php 
            // Handle widget form submissions
            $widget_submission_message = '';
            if ( isset( $_POST['wcusage_submit_registration_form1'] ) && isset( $_POST['submitaffiliateapplication'] ) && isset( $_POST['wcu-form-type'] ) && $_POST['wcu-form-type'] == 'widget' ) {
                if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form1'] ) ), 'wcusage_verify_submit_registration_form1' ) || is_user_logged_in() ) {
                    ob_start();
                    wcusage_post_submit_application( 0 );
                    $widget_submission_message = ob_get_clean();
                }
            }
            // Display form response if available
            if ( !empty( $form_response ) ) {
                echo '<div class="wcu-registration-response">' . wp_kses_post( $form_response ) . '</div>';
            }
            // Display widget submission message if available
            if ( !empty( $widget_submission_message ) ) {
                echo '<div class="wcu-registration-response">' . wp_kses_post( $widget_submission_message ) . '</div>';
            }
            $hide_form_after_submit = !empty( $GLOBALS['wcusage_registration_hide_form'] );
            if ( !$hide_form_after_submit ) {
                // Form Title
                $wcusage_field_registration_form_title = wcusage_get_setting_value( 'wcusage_field_registration_form_title', '' );
                if ( $wcusage_field_registration_form_title ) {
                    $form_title = $wcusage_field_registration_form_title;
                } else {
                    $form_title = sprintf( esc_html__( 'Register New %s Account', 'woo-coupon-usage' ), wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) );
                }
                ?>

      <p class="wcusage-register-form-title" style="font-size: 1.2em;"><strong><?php 
                echo esc_html( $form_title );
                ?>:</strong></p>

      <link rel="stylesheet" href="<?php 
                echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
                ?>" crossorigin="anonymous">

      <?php 
                // Disable form for existing affiliates?
                $disable_existing = wcusage_get_setting_value( 'wcusage_field_registration_disable_existing', '1' );
                $is_existing_affiliate = 0;
                if ( $disable_existing && is_user_logged_in() && $current_user_id ) {
                    $users_coupons = wcusage_get_users_coupons_ids( $current_user_id );
                    if ( !empty( $users_coupons ) ) {
                        $is_existing_affiliate = 1;
                    }
                }
                // Check if user already has active application
                if ( is_user_logged_in() ) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'wcusage_register';
                    $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE userid = %d AND status = 'pending' ORDER BY id DESC", $current_user_id ) );
                    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                } else {
                    $existing = 0;
                }
                // Check if user already has active application
                if ( empty( $existing ) || wcusage_check_admin_access() && $wcusage_registration_enable_admincan ) {
                    ?>

        <?php 
                    // Get template coupon code
                    $registration_coupon_template = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
                    $wcusage_field_form_style = wcusage_get_setting_value( 'wcusage_field_form_style', '3' );
                    $wcusage_field_form_style_columns = wcusage_get_setting_value( 'wcusage_field_form_style_columns', '1' );
                    $name_required = wcusage_get_setting_value( 'wcusage_field_registration_name_required', '0' );
                    $field_password_confirm = wcusage_get_setting_value( 'wcusage_field_registration_password_confirm', '0' );
                    $get_template_coupon = wcusage_get_coupon_info( $registration_coupon_template );
                    $wcusage_registration_page = wcusage_get_setting_value( 'wcusage_registration_page', '' );
                    $registration_page_url = get_permalink( $wcusage_registration_page );
                    // If registration page is not set, use home page URL
                    if ( !$registration_page_url ) {
                        $registration_page_url = home_url();
                    }
                    ?>

        <?php 
                    if ( !$is_existing_affiliate || wcusage_check_admin_access() && $wcusage_registration_enable_admincan ) {
                        ?>

          <!-- Form -->
          <div class="wcu_form_style_<?php 
                        echo esc_html( $wcusage_field_form_style );
                        if ( $wcusage_field_form_style_columns ) {
                            ?> wcu_form_style_columns<?php 
                        }
                        ?>">
          <form method="post" id="wcu_form_affiliate_register" class="wcu_form_affiliate_register" enctype="multipart/form-data"
          <?php 
                        if ( isset( $atts['type'] ) && $atts['type'] == "widget" ) {
                            ?>
          action="<?php 
                            echo esc_url( $registration_page_url );
                            ?>"
          <?php 
                        }
                        ?>>

            <?php 
                        if ( is_user_logged_in() && (!$wcusage_registration_enable_admincan && wcusage_check_admin_access() || !wcusage_check_admin_access()) ) {
                            ?>

              <p class="wcu-register-field-col wcu-register-field-col-1"><label for="wcu-input-first-name"><?php 
                            echo esc_html__( 'First Name', 'woo-coupon-usage' );
                            ?>:</label>
                <input type="text" id="wcu-input-first-name" name="wcu-input-first-name" class="input-text
                form-control" value="<?php 
                            echo esc_html( $user_info->first_name );
                            ?>" style="max-width: 300px;"
                <?php 
                            if ( $user_info->first_name ) {
                                ?> disabled<?php 
                            }
                            ?>>
              </p>

              <p class="wcu-register-field-col wcu-register-field-col-2"><label for="wcu-input-last-name"><?php 
                            echo esc_html__( 'Last Name', 'woo-coupon-usage' );
                            ?>:</label>
                <input type="text" id="wcu-input-last-name" name="wcu-input-last-name" class="input-text
                form-control" value="<?php 
                            echo esc_html( $user_info->last_name );
                            ?>" style="max-width: 300px;"
                <?php 
                            if ( $user_info->last_name ) {
                                ?> disabled<?php 
                            }
                            ?>>
              </p>

              <p class="wcu-register-field-col-username"><label for="wcu-input-username"><?php 
                            echo esc_html__( 'Username', 'woo-coupon-usage' );
                            ?>:</label>
                <input type="text" id="wcu-input-username" name="wcu-input-username" class="input-text form-control" value="<?php 
                            echo esc_html( $user_info->user_login );
                            ?>" style="max-width: 300px;" disabled>
              </p>

              <p class="wcu-register-field-col-email"><label for="wcu-input-email"><?php 
                            echo esc_html__( 'Email Address', 'woo-coupon-usage' );
                            ?>:</label>
                <input type="email" id="wcu-input-email" name="wcu-input-email" class="input-text form-control" value="<?php 
                            echo esc_html( $user_info->user_email );
                            ?>" style="max-width: 300px;" disabled>
              </p>

            <?php 
                        } else {
                            ?>

              <p class="wcu-register-field-col wcu-register-field-col-1"><label for="wcu-input-first-name"><?php 
                            echo esc_html__( 'First Name', 'woo-coupon-usage' );
                            ?>:<?php 
                            if ( $name_required ) {
                                ?>*<?php 
                            }
                            ?></label>
                <input type="text" id="wcu-input-first-name" name="wcu-input-first-name" class="input-text form-control" value="<?php 
                            echo esc_attr( wcusage_registration_prefill_value( 'firstname' ) );
                            ?>" style="max-width: 300px;" <?php 
                            if ( $name_required ) {
                                ?>required<?php 
                            }
                            ?>>
              </p>

              <p class="wcu-register-field-col wcu-register-field-col-2"><label for="wcu-input-last-name"><?php 
                            echo esc_html__( 'Last Name', 'woo-coupon-usage' );
                            ?>:<?php 
                            if ( $name_required ) {
                                ?>*<?php 
                            }
                            ?></label>
                <input type="text" id="wcu-input-last-name" name="wcu-input-last-name" class="input-text form-control" value="<?php 
                            echo esc_attr( wcusage_registration_prefill_value( 'lastname' ) );
                            ?>" style="max-width: 300px;" <?php 
                            if ( $name_required ) {
                                ?>required<?php 
                            }
                            ?>>
              </p>

              <?php 
                            if ( !$wcusage_field_registration_emailusername ) {
                                ?>
              <p class="wcu-register-field-col-username wcu-register-field-col-1"><label for="wcu-input-username"><?php 
                                echo esc_html__( 'Username', 'woo-coupon-usage' );
                                ?>:*</label>
                <input type="text" id="wcu-input-username" name="wcu-input-username" class="input-text form-control" value="<?php 
                                echo esc_attr( wcusage_registration_prefill_value( 'username' ) );
                                ?>" style="max-width: 300px;" required>
              </p>
              <?php 
                            }
                            ?>

              <p class="wcu-register-field-col-email <?php 
                            if ( !$wcusage_field_registration_emailusername ) {
                                ?>wcu-register-field-col-2<?php 
                            }
                            ?>"><label for="wcu-input-email"><?php 
                            echo esc_html__( 'Email Address', 'woo-coupon-usage' );
                            ?>:*</label>
                <input type="email" id="wcu-input-email" name="wcu-input-email" class="input-text form-control" value="<?php 
                            echo esc_attr( wcusage_registration_prefill_value( 'email' ) );
                            ?>" style="max-width: 300px;" required>
              </p>

              <div style="clear: both;"></div>

              <p class="wcu-register-field-col-password<?php 
                            if ( $field_password_confirm ) {
                                ?> wcu-register-field-col-1<?php 
                            }
                            ?>"><label for="wcu-input-password"><?php 
                            echo esc_html__( 'Password', 'woo-coupon-usage' );
                            ?>:*</label>
                <input type="password" id="wcu-input-password" name="wcu-input-password" class="input-text form-control" value="" style="max-width: 300px; display: inline-block;" required>
              </p>

              <?php 
                            if ( $field_password_confirm ) {
                                ?>
              <p class="wcu-register-field-col-password-confirm<?php 
                                if ( $field_password_confirm ) {
                                    ?> wcu-register-field-col-2<?php 
                                }
                                ?>"><label for="wcu-input-password-confirm"><?php 
                                echo esc_html__( 'Confirm Password', 'woo-coupon-usage' );
                                ?>:*</label>
                <input type="password" id="wcu-input-password-confirm" name="wcu-input-password-confirm" class="input-text form-control" value="" style="max-width: 300px; display: inline-block;" required>
              </p>
              <?php 
                            }
                            ?>

              <div style="clear: both;"></div>

            <?php 
                        }
                        ?>

            <!-- Preferred Code -->
            <?php 
                        if ( !$auto_coupon ) {
                            ?>
            <?php 
                            $wcusage_field_registration_coupon_label = wcusage_get_setting_value( 'wcusage_field_registration_coupon_label', esc_html__( 'Preferred Coupon Code', 'woo-coupon-usage' ) );
                            if ( !$wcusage_field_registration_coupon_label ) {
                                $wcusage_field_registration_coupon_label = esc_html__( 'Preferred Coupon Code', 'woo-coupon-usage' );
                            }
                            ?>
            <p class="wcu-register-field-col"><label for="wcu-input-coupon"><?php 
                            echo esc_html( $wcusage_field_registration_coupon_label );
                            ?>:*</label>
              <input type="text" id="wcu-input-coupon" name="wcu-input-coupon" class="input-text form-control" value="<?php 
                            echo esc_attr( wcusage_registration_prefill_value( 'couponcode' ) );
                            ?>" minlength="3" style="max-width: 300px;" required>
            </p>
            <?php 
                        }
                        ?>

            <?php 
                        ?>

            <?php 
                        $wcusage_registration_enable_honeypot = wcusage_get_setting_value( 'wcusage_registration_enable_honeypot', 1 );
                        ?>
            <?php 
                        if ( $wcusage_registration_enable_honeypot ) {
                            ?>
            <!-- HP -->
            <div style="display: none;">
              <label for="wcu-input-hp">Dont put anything here..</label>
              <input type="text" id="wcu-input-hp" name="wcu-input-hp" class="input-text
              form-control" autocomplete="off" value="">
            </div>
            <?php 
                        }
                        ?>

            <!-- Terms -->
            <?php 
                        if ( $wcusage_field_registration_enable_terms ) {
                            ?>
            <div class="wcu-reg-terms">
              <span style="margin-top: 1px; margin-right: 7px;">
                <input type="checkbox" name="reg-checkbox" value="check" id="agree" style="margin: 0;" required></span>
              <span style="line-height: 1.5em !important;"><?php 
                            echo wp_kses_post( html_entity_decode( $wcusage_field_registration_terms_message ) );
                            ?></span>
            </div>
            <?php 
                        }
                        ?>

            <div style="clear: both;"></div>

            <!-- Recaptcha -->
            <?php 
                        if ( $enable_captcha == "1" && !empty( $wcusage_registration_recaptcha_key ) && $wcusage_registration_recaptcha_key != "" ) {
                            ?>
            <div class="captcha_wrapper">
                <div class="g-recaptcha" data-sitekey="<?php 
                            echo esc_attr( $wcusage_registration_recaptcha_key );
                            ?>"></div>
            </div>
            <?php 
                        }
                        ?>

            <!-- Turnstile -->
            <?php 
                        if ( $enable_captcha == "2" && !empty( $wcusage_registration_turnstile_key ) && $wcusage_registration_turnstile_key != "" ) {
                            ?>
            <div class="captcha_wrapper">
                <div class="cf-turnstile" data-sitekey="<?php 
                            echo esc_attr( $wcusage_registration_turnstile_key );
                            ?>"></div>
            </div>
            <?php 
                        }
                        ?>

            <div style="clear: both;"></div>

            <?php 
                        $submit_button_text = wcusage_get_setting_value( 'wcusage_field_registration_submit_button_text', '' );
                        if ( !$submit_button_text ) {
                            $submit_button_text = esc_html__( 'Submit Application', 'woo-coupon-usage' );
                        }
                        ?>

            <?php 
                        wp_nonce_field( 'wcusage_verify_submit_registration_form1', 'wcusage_submit_registration_form1' );
                        ?>
            <?php 
                        wp_nonce_field( 'wcusage_verify_submit_registration_form2', 'wcusage_submit_registration_form2' );
                        ?>

            <p class="wcu-register-form-button"><input type="submit" class="woocommerce-button button"  id="wcu-register-button" name="submitaffiliateapplication" value="<?php 
                        echo esc_attr( $submit_button_text );
                        ?>"></p>

            <i class="register-spinner fa fa-spinner fa-spin" style="display: none; text-align: center; margin: 10px auto; font-size: 20px; width: 40px;"></i>

            <?php 
                        // If atts type is set to "widget" add an extra hidden field
                        if ( isset( $atts['type'] ) && $atts['type'] == "widget" ) {
                            ?>
              <input type="hidden" name="wcu-form-type" value="widget">
              <?php 
                        }
                        ?>

          </form>
          </div>

        <?php 
                    } else {
                        $coupon_shortcode_page = wcusage_get_coupon_shortcode_page( '0' );
                        ?>

          <?php 
                        if ( !isset( $_POST['submitaffiliateapplication'] ) ) {
                            ?>
          <p><?php 
                            printf( esc_html__( 'You are already registered as an %s.', 'woo-coupon-usage' ), esc_html( strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) );
                            ?></p>
          <?php 
                        }
                        ?>

          <p style="font-weight: bold;">
            <a href="<?php 
                        echo esc_url( $coupon_shortcode_page );
                        ?>" style="text-decoration: none;">
              <button class="wcu-save-settings-button woocommerce-Button button"><?php 
                        printf( esc_html__( 'View %s dashboard', 'woo-coupon-usage' ), esc_html( strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) );
                        ?> ></button>
            </a>
          </p>

          <?php 
                    }
                    ?>

      <?php 
                } else {
                    if ( !isset( $_POST['submitaffiliateapplication'] ) ) {
                        $pending_message_enable = wcusage_get_setting_value( 'wcusage_field_registration_pending_message_enable', '0' );
                        if ( $pending_message_enable ) {
                            $pending_default_message = '<p>You have a pending affiliate application.</p><p>We are reviewing your application and will be in touch soon!</p>';
                            $pending_message = wcusage_get_setting_value( 'wcusage_field_registration_pending_message', $pending_default_message );
                            echo '<div class="registration-message">' . wp_kses_post( $pending_message ) . '</div>';
                        } else {
                            ?>

            <p class="registration-message"><?php 
                            printf( esc_html__( 'You have a pending %s application.', 'woo-coupon-usage' ), esc_html( strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) );
                            ?></p>

            <p class="registration-message"><?php 
                            echo esc_html__( 'We are reviewing your application and will be in touch soon!', 'woo-coupon-usage' );
                            ?></p>

            <?php 
                        }
                    }
                }
                ?>

      <?php 
            }
            // End hide-after-submit check
            ?>

      </div>

    <?php 
        } else {
            ?>

      <?php 
            // Get Login Form
            woocommerce_output_all_notices();
            woocommerce_login_form();
            ?>

    <?php 
        }
        ?>

    <?php 
    } else {
        echo wp_kses_post( "<p>" . sprintf( esc_html__( 'Sorry, you are not currently allowed to apply as an %s.', 'woo-coupon-usage' ), esc_html( strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) ) . "</p>" );
    }
    $thecontent = ob_get_contents();
    ob_end_clean();
    wp_reset_postdata();
    return $thecontent;
}

/*
* Register the shortcode (always registered to prevent intermittent display issues)
*/
add_shortcode(
    'couponaffiliates-register',
    'wcusage_couponusage_register',
    10,
    1
);
/*
* Create a new registration submission
*
*/
function wcusage_create_new_registration(
    $couponcode = "",
    $username = "",
    $referrer = "",
    $promote = "",
    $website = "",
    $accept = "",
    $type = "",
    $info = "",
    $message = "",
    $role = ""
) {
    $the_user = get_user_by( 'login', $username );
    if ( $the_user ) {
        $userid = $the_user->ID;
        $email = $the_user->user_email;
        $firstname = $the_user->first_name;
    } else {
        $userid = "";
    }
    // Add register data
    if ( $userid ) {
        $getregisterid = wcusage_install_register_data(
            $couponcode,
            $userid,
            $referrer,
            $promote,
            $website,
            $type,
            $info
        );
        // If auto accept is enabled, then instantly accept
        $auto_accept = "";
        $do_auto_accept = $auto_accept && wcusage_registration_auto_accept_allowed( $userid, $type );
        if ( $do_auto_accept || $accept ) {
            $setstatus = wcusage_set_registration_status(
                'accepted',
                $getregisterid,
                $userid,
                $couponcode,
                $message,
                $type
            );
            // Custom Action
            do_action(
                'wcusage_hook_registration_accepted',
                $userid,
                $couponcode,
                $type
            );
            $get_user = get_user_by( 'id', $userid );
            // Update MLA invite
            if ( function_exists( 'wcusage_install_mlainvite_data' ) ) {
                wcusage_install_mlainvite_data(
                    '',
                    $get_user->user_email,
                    'accepted',
                    1
                );
            }
            if ( !$role ) {
                // Set affiliate role
                $setaffiliaterole = wcusage_set_registration_role( $userid );
            }
        } else {
            // Send email to affiliate
            wcusage_email_affiliate_register( $email, $couponcode, $firstname );
        }
        // Send email to admin
        if ( !is_admin() ) {
            $adminemail = get_bloginfo( 'admin_email' );
            wcusage_email_admin_affiliate_register(
                $username,
                $couponcode,
                $referrer,
                $promote,
                $website,
                $type,
                $info
            );
        }
    }
}

/*
* POST: Submit the affiliate application
*
*/
function wcusage_post_submit_application(  $adminpost  ) {
    // Skip AJAX requests
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    $options = get_option( 'wcusage_options' );
    $wcusage_registration_enable_logout = wcusage_get_setting_value( 'wcusage_field_registration_enable_logout', '1' );
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    $wcusage_registration_recaptcha_key = wcusage_get_setting_value( 'wcusage_registration_recaptcha_key', '' );
    $wcusage_registration_recaptcha_secret = wcusage_get_setting_value( 'wcusage_registration_recaptcha_secret', '' );
    $enable_captcha = wcusage_get_setting_value( 'wcusage_registration_enable_captcha', '' );
    $wcusage_field_registration_emailusername = wcusage_get_setting_value( 'wcusage_field_registration_emailusername', '0' );
    $username = "";
    $password = "";
    $email = "";
    $firstname = "";
    $lastname = "";
    $password = "";
    $referrer = "";
    $promote = "";
    $website = "";
    $type = "";
    $info = "";
    $cookie = "";
    $current_user_id = get_current_user_id();
    $user_info = "";
    if ( $current_user_id ) {
        $user_info = get_userdata( $current_user_id );
    }
    if ( !$current_user_id ) {
        if ( isset( $_SESSION['wcu_login_success'] ) && wp_verify_nonce( $_SESSION['wcu_login_success'], 'wcu_login_success' ) ) {
            $username = $_SESSION['wcu_login_username'];
            $user_info = get_user_by( 'login', $username );
        }
    }
    // Get the field values from POST
    $post_field_values = wcusage_registration_form_post_get_fields( $adminpost );
    if ( !$username ) {
        $username = sanitize_text_field( $post_field_values['username'] );
    }
    if ( !$username && isset( $user_info->user_login ) ) {
        $username = $user_info->user_login;
    }
    $email = sanitize_text_field( $post_field_values['email'] );
    $firstname = sanitize_text_field( $post_field_values['firstname'] );
    $lastname = sanitize_text_field( $post_field_values['lastname'] );
    $couponcode = sanitize_text_field( $post_field_values['couponcode'] );
    $website = sanitize_text_field( $post_field_values['website'] );
    $type = sanitize_text_field( $post_field_values['type'] );
    $info = sanitize_text_field( $post_field_values['info'] );
    $promote = sanitize_text_field( $post_field_values['promote'] );
    $referrer = sanitize_text_field( $post_field_values['referrer'] );
    $password = sanitize_text_field( $post_field_values['password'] );
    $password_confirm = sanitize_text_field( $post_field_values['password_confirm'] );
    $message = sanitize_text_field( $post_field_values['message'] );
    $role = sanitize_text_field( $post_field_values['role'] );
    // Store submitted values so the shortcode can re-render the form with values prefilled (no inline <script> needed).
    $GLOBALS['wcusage_registration_prefill'] = array(
        'username'   => $username,
        'email'      => $email,
        'firstname'  => $firstname,
        'lastname'   => $lastname,
        'couponcode' => $couponcode,
        'website'    => $website,
        'type'       => $type,
        'promote'    => $promote,
        'referrer'   => $referrer,
    );
    $captcha_checked = false;
    if ( isset( $_SESSION['wcu_captcha_verified'] ) ) {
        $hash_username = wp_hash( $username );
        if ( $_SESSION['wcu_captcha_verified'] == $hash_username ) {
            $captcha_checked = true;
        }
    }
    $captchaverify = wcusage_registration_form_verify_captcha( $adminpost );
    if ( isset( $_POST['submitaffiliateapplication'] ) ) {
        do_action( 'wcusage_hook_registration_form_submitted' );
    }
    if ( !$enable_captcha || ($captcha_checked || $captchaverify) ) {
        // clear the session
        if ( isset( $_SESSION['wcu_captcha_verified'] ) ) {
            unset($_SESSION['wcu_captcha_verified']);
        }
        if ( isset( $_POST['submitaffiliateapplication'] ) ) {
            $wcusage_registration_enable_admincan = wcusage_get_setting_value( 'wcusage_field_registration_enable_admincan', '' );
            $field_password_confirm = wcusage_get_setting_value( 'wcusage_field_registration_password_confirm', '0' );
            if ( wcusage_register_verify( $post_field_values ) ) {
                echo wcusage_register_verify( $post_field_values );
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                // Clear sessions wcu_login_success and wcu_login_username
                if ( isset( $_SESSION['wcu_login_success'] ) ) {
                    unset($_SESSION['wcu_login_success']);
                }
                if ( isset( $_SESSION['wcu_login_username'] ) ) {
                    unset($_SESSION['wcu_login_username']);
                }
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcusage_register';
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponcode = %s AND status != 'declined'", $couponcode ) );
                // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                if ( $count > 0 && function_exists( 'wc_get_coupon_id_by_code' ) ) {
                    $coupon_post_id = wc_get_coupon_id_by_code( $couponcode );
                    // If the coupon post no longer exists but previous submissions were accepted, allow re-using the code.
                    if ( !$coupon_post_id ) {
                        $accepted_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponcode = %s AND status = 'accepted'", $couponcode ) );
                        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                        if ( $accepted_count > 0 ) {
                            $count = 0;
                        }
                    }
                }
                try {
                    $thiscoupon = new WC_Coupon($couponcode);
                } catch ( Exception $e ) {
                    $thiscoupon = false;
                }
                if ( $count <= 0 && (!$thiscoupon || !$thiscoupon->is_valid()) ) {
                    if ( !isset( $_SESSION['wcu_registration_token'] ) || is_admin() ) {
                        // Add User If Admin Post
                        $user_id = "";
                        if ( is_admin() ) {
                            if ( !username_exists( $username ) && !email_exists( $email ) ) {
                                $new_affiliate_user = wcusage_add_new_affiliate_user(
                                    $username,
                                    $password,
                                    $email,
                                    $firstname,
                                    $lastname,
                                    $couponcode,
                                    '',
                                    $info,
                                    $role
                                );
                                if ( isset( $new_affiliate_user['user_id'] ) ) {
                                    $user_id = $new_affiliate_user['user_id'];
                                }
                            }
                        }
                        // Create new registration
                        if ( $adminpost ) {
                            $accept = 1;
                        } else {
                            $accept = 0;
                        }
                        $createregistration = wcusage_create_new_registration(
                            $couponcode,
                            $username,
                            $referrer,
                            $promote,
                            $website,
                            $accept,
                            $type,
                            $info,
                            $message,
                            $role
                        );
                        // Get MLA fields
                        $mla = "";
                        $wcusage_field_mla_enable = wcusage_get_setting_value( 'wcusage_field_mla_enable', '0' );
                        if ( $wcusage_field_mla_enable ) {
                            if ( !$adminpost ) {
                                $cookie = wcusage_get_cookie_value( "wcusage_referral_mla" );
                                $cookie = str_replace( "%20", " ", $cookie );
                                $mla = wcusage_get_mla_referral_value();
                            }
                        }
                        if ( $mla || $cookie ) {
                            if ( !$mla ) {
                                $mla = $cookie;
                            }
                            $mla_user = get_user_by( 'login', $mla );
                            $this_user = get_user_by( 'login', $username );
                            if ( function_exists( 'wcusage_mla_add_parent_to_user' ) ) {
                                wcusage_mla_add_parent_to_user( $mla_user->ID, $this_user->ID );
                            }
                            if ( function_exists( 'wcusage_install_mlainvite_data' ) ) {
                                wcusage_install_mlainvite_data(
                                    $mla_user->ID,
                                    $this_user->user_email,
                                    'pending',
                                    1
                                );
                            }
                            // Notify MLA parent about the new sub-affiliate registration.
                            do_action(
                                'wcusage_hook_mla_sub_registration_new',
                                $mla_user->ID,
                                $this_user->ID,
                                $couponcode
                            );
                        }
                        $alreadyexists = 0;
                    } else {
                        $alreadyexists = 1;
                    }
                    // Create Redirect On Submit
                    $wcusage_field_registration_submit_type = wcusage_get_setting_value( 'wcusage_field_registration_submit_type', 'message' );
                    $wcusage_field_registration_accept_redirect = wcusage_get_setting_value( 'wcusage_field_registration_accept_redirect', wcusage_get_coupon_shortcode_page_id() );
                    if ( !$adminpost ) {
                        $GLOBALS['wcusage_registration_hide_form'] = true;
                    }
                    if ( $wcusage_field_registration_submit_type == "redirect" && !$adminpost ) {
                        $redirectpage = get_permalink( $wcusage_field_registration_accept_redirect );
                        ?>

              <div class="wcu-loading-image wcu-loading-stats">
                <div class="wcu-loading-loader"></div>
                <p class="wcu-loading-loader-text"><?php 
                        echo esc_html__( "Success! Redirecting", "woo-coupon-usage" );
                        ?>...</p>
              </div>

              <?php 
                        // Do The Page Redirect
                        echo '<meta http-equiv="refresh" content="0; URL=' . esc_html( $redirectpage ) . '" />';
                        wp_safe_redirect( $redirectpage );
                        exit;
                    } else {
                        if ( !$adminpost ) {
                            if ( !$alreadyexists ) {
                                if ( isset( $options['wcusage_field_registration_accept_message'] ) ) {
                                    $acceptmessage = $options['wcusage_field_registration_accept_message'];
                                } else {
                                    $acceptmessage = sprintf( esc_html__( 'Your %s application for the coupon code "{coupon}" has been submitted.', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
                                }
                            } else {
                                $acceptmessage = sprintf( esc_html__( 'Your %s application has been submitted.', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
                            }
                        } else {
                            $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
                            if ( $wcusage_field_registration_enable ) {
                                $acceptmessage = '<div class="notice notice-success is-dismissible" style="margin-top: 20px;">';
                                $acceptmessage .= '<p>- ' . sprintf( esc_html__( 'An %1$s application has been submitted for user "{username}" for the coupon "{coupon}".', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                                if ( !$accept ) {
                                    $acceptmessage .= '<p>- ' . esc_html__( 'You can view, edit and accept the application below.', 'woo-coupon-usage' ) . '</p>';
                                } else {
                                    $acceptmessage .= '<p>- ' . sprintf( esc_html__( 'The %1$s application was automatically accepted.', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                                }
                                if ( $user_id ) {
                                    $acceptmessage .= '<p>- ' . sprintf( esc_html__( 'The %1$s user did not exist, so a new user account has been created, and details sent to their email.', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                                }
                                $acceptmessage .= '</div>';
                            } else {
                                $acceptmessage = '<div class="notice notice-success is-dismissible">';
                                $acceptmessage .= '<p>- ' . sprintf( esc_html__( '%1$s user "{username}" was assigned to the new %2$s coupon "{coupon}".', 'woo-coupon-usage' ), ucfirst( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                                if ( $user_id ) {
                                    $acceptmessage .= '<p>- ' . sprintf( esc_html__( 'The %1$s user did not exist, so a new user account has been created, and details sent to their email.', 'woo-coupon-usage' ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                                }
                                $acceptmessage .= '</div>';
                            }
                        }
                        $acceptmessage = str_replace( "{username}", $username, $acceptmessage );
                        $acceptmessage = str_replace( "{coupon}", $couponcode, $acceptmessage );
                        // Display message on submit form
                        echo "<p style='margin-top: 20px;'>" . wp_kses_post( $acceptmessage ) . "</p>";
                    }
                } else {
                    echo wp_kses_post( "<p style='margin-top: 20px; font-weight: bold; color: red;'>" . sprintf( esc_html__( 'The "%s" coupon already exists. Please try again with a different coupon code.', 'woo-coupon-usage' ), esc_html( $couponcode ) ) . "</p>" );
                }
            }
            // Session
            $_SESSION['wcu_registration_token'] = uniqid();
            if ( isset( $_SESSION['wcu_registration_user_created'] ) ) {
                unset($_SESSION['wcu_registration_user_created']);
            }
        } else {
            if ( isset( $_SESSION["wcu_registration_token"] ) ) {
                unset($_SESSION["wcu_registration_token"]);
            }
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>" . esc_html__( 'Please complete the captcha.', 'woo-coupon-usage' ) . "</p>";
    }
}

/*
* POST: Verify the affiliate application fields
*
*/
function wcusage_register_verify(  $post_field_values  ) {
    $wcusage_registration_recaptcha_key = wcusage_get_setting_value( 'wcusage_registration_recaptcha_key', '' );
    $field_password_confirm = wcusage_get_setting_value( 'wcusage_field_registration_password_confirm', '0' );
    $username = '';
    if ( isset( $post_field_values['username'] ) ) {
        $username = sanitize_text_field( $post_field_values['username'] );
    }
    $skip_username = false;
    if ( isset( $_SESSION['wcu_login_success'] ) && wp_verify_nonce( $_SESSION['wcu_login_success'], 'wcu_login_success' ) ) {
        $skip_username = true;
    }
    $email = '';
    if ( isset( $post_field_values['email'] ) ) {
        $email = sanitize_text_field( $post_field_values['email'] );
    }
    $firstname = '';
    if ( isset( $post_field_values['firstname'] ) ) {
        $firstname = sanitize_text_field( $post_field_values['firstname'] );
    }
    $lastname = '';
    if ( isset( $post_field_values['lastname'] ) ) {
        $lastname = sanitize_text_field( $post_field_values['lastname'] );
    }
    $couponcode = '';
    if ( isset( $post_field_values['couponcode'] ) ) {
        $couponcode = sanitize_text_field( $post_field_values['couponcode'] );
    }
    $website = '';
    if ( isset( $post_field_values['website'] ) ) {
        $website = sanitize_text_field( $post_field_values['website'] );
    }
    $type = '';
    if ( isset( $post_field_values['type'] ) ) {
        $type = sanitize_text_field( $post_field_values['type'] );
    }
    $info = '';
    if ( isset( $post_field_values['info'] ) ) {
        $info = sanitize_text_field( $post_field_values['info'] );
    }
    $promote = '';
    if ( isset( $post_field_values['promote'] ) ) {
        $promote = sanitize_text_field( $post_field_values['promote'] );
    }
    $referrer = '';
    if ( isset( $post_field_values['referrer'] ) ) {
        $referrer = sanitize_text_field( $post_field_values['referrer'] );
    }
    $password = '';
    if ( isset( $post_field_values['password'] ) ) {
        $password = sanitize_text_field( $post_field_values['password'] );
    }
    $password_confirm = '';
    if ( isset( $post_field_values['password_confirm'] ) ) {
        $password_confirm = sanitize_text_field( $post_field_values['password_confirm'] );
    }
    $skip_existing_user = false;
    $auto_login_enabled = wcusage_get_setting_value( 'wcusage_field_registration_auto_login', '1' );
    if ( !$auto_login_enabled && isset( $_SESSION['wcu_registration_user_created'] ) ) {
        $created_hash = sanitize_text_field( wp_unslash( $_SESSION['wcu_registration_user_created'] ) );
        $current_hash = wp_hash( $username . '|' . $email );
        if ( $created_hash === $current_hash ) {
            $skip_existing_user = true;
        }
    }
    // Store submitted values so the form can be re-rendered with values prefilled.
    $GLOBALS['wcusage_registration_prefill'] = array(
        'username'   => $username,
        'email'      => $email,
        'firstname'  => $firstname,
        'lastname'   => $lastname,
        'couponcode' => $couponcode,
        'website'    => $website,
        'type'       => $type,
        'promote'    => $promote,
        'referrer'   => $referrer,
    );
    $output = "";
    if ( !$skip_username && !$skip_existing_user && username_exists( $username ) && !is_user_logged_in() && !is_admin() && !isset( $_SESSION['wcu_registration_token'] ) ) {
        $output = "<p style='color: red; font-weight: bold;'>" . esc_html__( 'This username already exists. Please try again, or login first.', 'woo-coupon-usage' ) . "</p>";
        $output .= "<p><a href='" . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . "'>" . esc_html__( 'Login to your account.', 'woo-coupon-usage' ) . "</a></p>";
    } elseif ( !$skip_username && !$skip_existing_user && email_exists( $email ) && !is_user_logged_in() && !is_admin() && !isset( $_SESSION['wcu_registration_token'] ) ) {
        $output = "<p style='color: red; font-weight: bold;'>" . esc_html__( 'This email already exists. Please try again, or login first.', 'woo-coupon-usage' ) . "</p>";
        $output .= "<p><a href='" . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . "'>" . esc_html__( 'Login to your account.', 'woo-coupon-usage' ) . "</a></p>";
    } elseif ( !username_exists( $username ) && !$email && is_admin() && !isset( $_SESSION['wcu_registration_token'] ) ) {
        $output = "<p style='color: red; font-weight: bold;'>" . esc_html__( 'This user does not exist, please enter a valid user, or enter an email address to create a new account.', 'woo-coupon-usage' ) . "</p>";
    } elseif ( $field_password_confirm && $password != $password_confirm ) {
        $output = "<p style='color: red; font-weight: bold;'>" . esc_html__( 'The passwords do not match. Please try again.', 'woo-coupon-usage' ) . "</p>";
    } else {
        $output = "";
    }
    return $output;
}

/*
* Login user after submit register form
*
*/
function wcusage_login_after_registration() {
    // If ajax request, return
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    // if wordpress user not logged in
    if ( !is_user_logged_in() ) {
        $captchaverify = wcusage_registration_form_verify_captcha( 0 );
        $post_field_values = wcusage_registration_form_post_get_fields( 0 );
        if ( $captchaverify && isset( $_POST['wcusage_submit_registration_form2'] ) && isset( $_POST['submitaffiliateapplication'] ) ) {
            // create a nonce to verify later
            $hash_username = wp_hash( $post_field_values['username'] );
            $_SESSION['wcu_captcha_verified'] = $hash_username;
            if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form2'] ) ), 'wcusage_verify_submit_registration_form2' ) ) {
                $username = $post_field_values['username'];
                $password = $post_field_values['password'];
                $email = $post_field_values['email'];
                $firstname = $post_field_values['firstname'];
                $lastname = $post_field_values['lastname'];
                $couponcode = $post_field_values['couponcode'];
                $website = $post_field_values['website'];
                $info = sanitize_text_field( $post_field_values['info'] );
                if ( !wcusage_register_verify( $post_field_values ) ) {
                    if ( !wcusage_registration_coupon_available( $couponcode ) ) {
                        return;
                    }
                    // Add User
                    $new_affiliate_user = wcusage_add_new_affiliate_user(
                        $username,
                        $password,
                        $email,
                        $firstname,
                        $lastname,
                        $couponcode,
                        $website,
                        $info
                    );
                    $userid = $new_affiliate_user['userid'];
                    $new_password = $new_affiliate_user['new_password'];
                    if ( $new_password ) {
                        $password = $new_password;
                    }
                    if ( $userid ) {
                        $_SESSION['wcu_registration_user_created'] = wp_hash( $username . '|' . $email );
                    }
                    // Login
                    $auto_login = wcusage_get_setting_value( 'wcusage_field_registration_auto_login', '1' );
                    if ( $userid && !get_current_user_id() && !wcusage_check_admin_access() && $auto_login ) {
                        $creds = array(
                            'user_login'    => $username,
                            'user_password' => $password,
                            'remember'      => true,
                        );
                        // check if creds are set
                        if ( isset( $creds['user_login'] ) && isset( $creds['user_password'] ) ) {
                            add_filter( 'wordfence_ls_require_captcha', '__return_false' );
                            $user = wp_signon( $creds, false );
                            $nonce = wp_create_nonce( 'wcu_login_success' );
                            $_SESSION['wcu_login_success'] = $nonce;
                            $_SESSION['wcu_login_username'] = $username;
                            remove_filter( 'wordfence_ls_require_captcha', '__return_true' );
                            // check if user exists
                            if ( !is_wp_error( $user ) ) {
                                wp_set_current_user( $user->ID );
                            } else {
                                error_log( 'Failed to auto login after affiliate registration. Error: ' . $user->get_error_message() );
                            }
                        }
                    }
                }
            }
        }
    }
}

add_action( 'init', 'wcusage_login_after_registration' );
/*
* Simple Cloudflare Turnstile Compatibility
*/
add_filter( 'cfturnstile_wp_login_checks', 'wcusage_cfturnstile_wp_login_checks' );
function wcusage_cfturnstile_wp_login_checks(  $checks  ) {
    if ( isset( $_POST['wcusage_submit_registration_form2'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_submit_registration_form2'] ) ), 'wcusage_verify_submit_registration_form2' ) ) {
        return true;
    }
    return $checks;
}

/*
* POST: Function to register a new affiliate user account
*
*/
function wcusage_add_new_affiliate_user(
    $username,
    $password,
    $email,
    $firstname,
    $lastname,
    $couponcode,
    $website = "",
    $info = "",
    $role = ""
) {
    $wcusage_field_registration_accepted_role = wcusage_get_setting_value( 'wcusage_field_registration_accepted_role', 'coupon_affiliate' );
    $wcusage_register_role = wcusage_get_setting_value( 'wcusage_field_register_role', '1' );
    $wcusage_field_register_role_only_accept = wcusage_get_setting_value( 'wcusage_field_register_role_only_accept', '0' );
    $wcusage_field_registration_pending_role = wcusage_get_setting_value( 'wcusage_field_registration_pending_role', 'subscriber' );
    if ( username_exists( $username ) ) {
        return;
    }
    $new_password = "";
    if ( !$password ) {
        $new_password = wp_generate_password( 15, false );
        $password = $new_password;
    }
    if ( !$role ) {
        if ( $wcusage_register_role ) {
            if ( !$wcusage_field_register_role_only_accept ) {
                $userrole = $wcusage_field_registration_accepted_role;
            } else {
                $userrole = $wcusage_field_registration_pending_role;
            }
            if ( $userrole == 'administrator' || $userrole == 'editor' || $userrole == 'author' || $userrole == 'shop_manager' ) {
                $userrole = 'subscriber';
            }
            if ( $role_object = get_role( $userrole ) ) {
                if ( $role_object->has_cap( 'manage_options' ) ) {
                    $userrole = 'subscriber';
                }
            }
        } else {
            $userrole = 'subscriber';
        }
    } else {
        $userrole = $role;
    }
    $userdata = array(
        'user_login'      => $username,
        'user_email'      => $email,
        'first_name'      => $firstname,
        'last_name'       => $lastname,
        'user_pass'       => $password,
        'user_url'        => $website,
        'user_registered' => date( 'Y-m-d H:i:s' ),
        'role'            => $userrole,
    );
    // Add The User
    $userid = wp_insert_user( $userdata );
    if ( $info ) {
        update_user_meta( $userid, 'wcu_info', $info );
    }
    if ( isset( $_POST['wcu-input-promote'] ) ) {
        $promote = sanitize_text_field( $_POST['wcu-input-promote'] );
        if ( $promote ) {
            update_user_meta( $userid, 'wcu_promote', $promote );
        }
    }
    if ( isset( $_POST['wcu-input-referrer'] ) ) {
        $referrer = sanitize_text_field( $_POST['wcu-input-referrer'] );
        if ( $referrer ) {
            update_user_meta( $userid, 'wcu_referrer', $referrer );
        }
    }
    // Send New Account Email
    $wcusage_email_registration_new_enable = wcusage_get_setting_value( 'wcusage_field_email_registration_new_enable', '1' );
    if ( $userid && $wcusage_email_registration_new_enable ) {
        wcusage_email_affiliate_register_new(
            $email,
            $couponcode,
            $firstname,
            $username,
            $userid
        );
    }
    if ( !$userid ) {
        return;
    }
    $data = array();
    $data['couponcode'] = $couponcode;
    $data['userid'] = $userid;
    $data['new_password'] = $new_password;
    return $data;
}

/*
* POST: Get values from registration form when submitted
*
*/
function wcusage_registration_form_post_get_fields(  $adminpost = 0  ) {
    $current_user_id = get_current_user_id();
    $user_info = get_userdata( $current_user_id );
    $wcusage_registration_enable_admincan = wcusage_get_setting_value( 'wcusage_field_registration_enable_admincan', '0' );
    $wcusage_field_registration_emailusername = wcusage_get_setting_value( 'wcusage_field_registration_emailusername', '0' );
    $auto_coupon = "";
    $auto_coupon_format = "";
    $couponcode = "";
    $username = "";
    $password = "";
    $password_confirm = "";
    $email = "";
    $firstname = "";
    $lastname = "";
    $password = "";
    $referrer = "";
    $promote = "";
    $website = "";
    $type = "";
    $role = "";
    $info = "";
    $message = "";
    if ( !is_user_logged_in() || $wcusage_registration_enable_admincan && wcusage_check_admin_access() || is_admin() || $adminpost ) {
        if ( !$wcusage_field_registration_emailusername || $adminpost ) {
            if ( isset( $_POST['wcu-input-username'] ) ) {
                $username = sanitize_user( $_POST['wcu-input-username'] );
            }
        } else {
            if ( isset( $_POST['wcu-input-email'] ) ) {
                $username = sanitize_email( $_POST['wcu-input-email'] );
            }
        }
        if ( isset( $_POST['wcu-input-password'] ) ) {
            $password = sanitize_text_field( $_POST['wcu-input-password'] );
        }
        if ( isset( $_POST['wcu-input-password-confirm'] ) ) {
            $password_confirm = sanitize_text_field( $_POST['wcu-input-password-confirm'] );
        }
        if ( isset( $_POST['wcu-input-email'] ) ) {
            $email = sanitize_email( $_POST['wcu-input-email'] );
        }
        if ( isset( $_POST['wcu-input-first-name'] ) ) {
            $firstname = sanitize_text_field( $_POST['wcu-input-first-name'] );
        }
        if ( isset( $_POST['wcu-input-last-name'] ) ) {
            $lastname = sanitize_text_field( $_POST['wcu-input-last-name'] );
        }
    } else {
        $username = sanitize_user( $user_info->user_login );
        $userid = $user_info->ID;
        $email = sanitize_email( $user_info->user_email );
        $firstname = sanitize_text_field( $user_info->first_name );
    }
    if ( $adminpost && isset( $_POST['wcu-message'] ) ) {
        $message = sanitize_text_field( $_POST['wcu-message'] );
    }
    if ( isset( $_POST['wcu-input-coupon'] ) ) {
        $couponcode = sanitize_text_field( $_POST['wcu-input-coupon'] );
    } else {
        $couponcode = "";
    }
    if ( $couponcode == "" ) {
        $first_name_for_coupon = ( isset( $firstname ) ? $firstname : '' );
        $last_name_for_coupon = ( isset( $lastname ) ? $lastname : '' );
        $couponcode = wcusage_generate_auto_coupon( $username, $first_name_for_coupon, $last_name_for_coupon );
    }
    $info = json_encode( $info );
    $return_array = [];
    $return_array['username'] = $username;
    $return_array['password'] = $password;
    $return_array['password_confirm'] = $password_confirm;
    $return_array['email'] = $email;
    $return_array['firstname'] = $firstname;
    $return_array['lastname'] = $lastname;
    $return_array['couponcode'] = $couponcode;
    $return_array['referrer'] = $referrer;
    $return_array['promote'] = $promote;
    $return_array['website'] = $website;
    $return_array['type'] = $type;
    $return_array['info'] = $info;
    $return_array['role'] = $role;
    $return_array['message'] = $message;
    return $return_array;
}

/*
* Function to verify captcha on registration form
*
*/
function wcusage_registration_form_verify_captcha(  $adminpost  ) {
    if ( !isset( $_POST['wcu-input-email'] ) ) {
        return true;
    }
    // Check honeypot
    $wcusage_registration_enable_honeypot = wcusage_get_setting_value( 'wcusage_registration_enable_honeypot', 1 );
    if ( $wcusage_registration_enable_honeypot && isset( $_POST['wcu-input-hp'] ) && $_POST['wcu-input-hp'] != "" ) {
        return false;
    }
    // Check captcha
    $enable_captcha = wcusage_get_setting_value( 'wcusage_registration_enable_captcha', '' );
    if ( $enable_captcha && !$adminpost ) {
        $recaptcha_secret = "";
        $response = "";
        if ( $enable_captcha == "1" ) {
            $recaptcha_key = wcusage_get_setting_value( 'wcusage_registration_recaptcha_key', '' );
            $recaptcha_secret = wcusage_get_setting_value( 'wcusage_registration_recaptcha_secret', '' );
            if ( isset( $_POST["g-recaptcha-response"] ) ) {
                $response = $_POST["g-recaptcha-response"];
            }
            $url = 'https://www.google.com/recaptcha/api/siteverify';
        }
        if ( $enable_captcha == "2" ) {
            $recaptcha_key = wcusage_get_setting_value( 'wcusage_registration_turnstile_key', '' );
            $recaptcha_secret = wcusage_get_setting_value( 'wcusage_registration_turnstile_secret', '' );
            if ( isset( $_POST["cf-turnstile-response"] ) ) {
                $response = $_POST["cf-turnstile-response"];
            }
            $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        }
        if ( empty( $recaptcha_key ) || $recaptcha_key == "" || empty( $recaptcha_secret ) || $recaptcha_secret == "" ) {
            return true;
        }
        $args = array(
            'body' => array(
                'secret'   => $recaptcha_secret,
                'response' => $response,
            ),
        );
        $verify = wp_remote_post( $url, $args );
        $verify = wp_remote_retrieve_body( $verify );
        $data = json_decode( $verify );
        if ( $data->success ) {
            $captchaverify = true;
        } else {
            $captchaverify = false;
        }
    } else {
        $captchaverify = true;
    }
    return $captchaverify;
}

/*
* Check if a coupon code is available for registration
*
*/
function wcusage_registration_coupon_available(  $couponcode  ) {
    if ( empty( $couponcode ) ) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_register';
    $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponcode = %s AND status != 'declined'", $couponcode ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( $count > 0 && function_exists( 'wc_get_coupon_id_by_code' ) ) {
        $coupon_post_id = wc_get_coupon_id_by_code( $couponcode );
        if ( !$coupon_post_id ) {
            $accepted_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponcode = %s AND status = 'accepted'", $couponcode ) );
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            if ( $accepted_count > 0 ) {
                $count = 0;
            }
        }
    }
    try {
        $thiscoupon = new WC_Coupon($couponcode);
    } catch ( Exception $e ) {
        $thiscoupon = false;
    }
    if ( $count <= 0 && (!$thiscoupon || !$thiscoupon->is_valid()) ) {
        return true;
    }
    return false;
}
