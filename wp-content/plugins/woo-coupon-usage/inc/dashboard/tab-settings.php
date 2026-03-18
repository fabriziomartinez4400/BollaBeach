<?php
if (!defined('ABSPATH')) {
    exit;
}

// AJAX Handler
add_action('wp_ajax_wcusage_update_settings', 'wcusage_ajax_update_settings');
function wcusage_ajax_update_settings() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcusage_settings_update')) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

    $postid = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $currentuserid = get_current_user_id();
    $couponuserid = get_post_meta($postid, 'wcu_select_coupon_user', true);

    if (!$postid || ($couponuserid != $currentuserid && !wcusage_check_admin_access())) {
        if(!$postid) {
            wp_send_json_error('Permission denied: Invalid post ID');
        } elseif ($couponuserid != $currentuserid) {
            wp_send_json_error('Permission denied: You are not assigned to this coupon.');
        } else {
            wp_send_json_error('Permission denied.');
        }
        wp_die();
    }

    // Update notification settings
    $wcu_enable_notifications = isset($_POST['wcu_enable_notifications']) ? sanitize_text_field($_POST['wcu_enable_notifications']) : '0';
    update_post_meta($postid, 'wcu_enable_notifications', $wcu_enable_notifications);
    
    // Newsletter subscription toggle (user meta) - default subscribed (meta absent). If checkbox unchecked we add meta flag.
    $newsletter_subscribed = isset($_POST['wcu_newsletter_subscribed']) ? '1' : '0';
    if($newsletter_subscribed === '1') {
        delete_user_meta($couponuserid, 'wcusage_newsletter_unsubscribed');
    } else {
        update_user_meta($couponuserid, 'wcusage_newsletter_unsubscribed', 1);
    }

    $enable_reports_user_option = wcusage_get_setting_value('wcusage_field_enable_reports_user_option', 1);
    if ($enable_reports_user_option) {
        $wcu_enable_reports = isset($_POST['wcu_enable_reports']) ? sanitize_text_field($_POST['wcu_enable_reports']) : '0';
        update_post_meta($postid, 'wcu_enable_reports', $wcu_enable_reports);
    }

    $wcu_notifications_extra = isset($_POST['wcu_notifications_extra']) ? sanitize_text_field($_POST['wcu_notifications_extra']) : '';
    update_post_meta($postid, 'wcu_notifications_extra', $wcu_notifications_extra);

    // Update payout settings
    $payout_fields = [
        'payouttype' => 'wcu_payout_type',
        'paypalemail' => 'wcu_paypal',
        'paypalemail2' => 'wcu_paypal2',
        'bankname' => 'wcu_bank_name',
        'banksort' => 'wcu_bank_sort',
        'bankaccount' => 'wcu_bank_account',
        'bankother' => 'wcu_bank_other',
        'bankother2' => 'wcu_bank_other2',
        'bankother3' => 'wcu_bank_other3',
        'bankother4' => 'wcu_bank_other4',
        'paypalemailapi' => 'wcu_paypalapi',
        'wisebank_region' => 'wcu_wisebank_region',
        'wisebank_account_name' => 'wcu_wisebank_account_name',
        'wisebank_account_number' => 'wcu_wisebank_account_number',
        'wisebank_routing_number' => 'wcu_wisebank_routing_number',
        'wisebank_swift_code' => 'wcu_wisebank_swift_code',
        'wisebank_iban' => 'wcu_wisebank_iban',
        'wisebank_sort_code' => 'wcu_wisebank_sort_code',
        'wisebank_bank_name' => 'wcu_wisebank_bank_name',
        'wisebank_bank_address' => 'wcu_wisebank_bank_address',
        'wisebank_country' => 'wcu_wisebank_country',
        'wisebank_address' => 'wcu_wisebank_address',
        'wisebank_city' => 'wcu_wisebank_city',
        'wisebank_postcode' => 'wcu_wisebank_postcode',
        'wisebank_state' => 'wcu_wisebank_state',
        'wisebank_recipient_country' => 'wcu_wisebank_recipient_country'
    ];

    // Handle region-specific account number fields
    $region_account_fields = [
        'wisebank_account_number_us' => 'wcu_wisebank_account_number',
        'wisebank_account_number_uk' => 'wcu_wisebank_account_number',
        'wisebank_account_number_intl' => 'wcu_wisebank_account_number'
    ];

    $updated_payout_fields = [];
    foreach($payout_fields as $post_key => $meta_key) {
        if(isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            
            // Check if this field should be encrypted
            if (function_exists('wcusage_should_encrypt_field') && wcusage_should_encrypt_field($meta_key)) {
                $value = wcusage_encrypt_bank_data($value);
            }
            
            update_user_meta($couponuserid, $meta_key, $value);
            $updated_payout_fields[$post_key] = sanitize_text_field($_POST[$post_key]); // Return unencrypted for response
        }
    }

    // Handle region-specific account number fields - only update if they have a value
    foreach($region_account_fields as $post_key => $meta_key) {
        if(isset($_POST[$post_key]) && !empty($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            
            // Check if this field should be encrypted
            if (function_exists('wcusage_should_encrypt_field') && wcusage_should_encrypt_field($meta_key)) {
                $value = wcusage_encrypt_bank_data($value);
            }
            
            update_user_meta($couponuserid, $meta_key, $value);
            $updated_payout_fields['wisebank_account_number'] = sanitize_text_field($_POST[$post_key]); // Return unencrypted for response
        }
    }

    // Special handling for Wise Bank Transfer - combine individual fields OR handle old textarea format
    if (isset($_POST['wisebank_account_name']) || isset($_POST['wisebank_account_number']) || 
        isset($_POST['wisebank_routing_number']) || isset($_POST['wisebank_swift_code']) || 
        isset($_POST['wisebank_iban']) || isset($_POST['wisebank_sort_code']) || 
        isset($_POST['wisebank_bank_name']) || isset($_POST['wisebank_bank_address']) || 
        isset($_POST['wisebank_country']) || isset($_POST['wisebank_state'])) {
        
        $wisebank_combined = wcusage_combine_wisebank_fields($_POST);
        update_user_meta($couponuserid, 'wcu_wisebank', $wisebank_combined);
        $updated_payout_fields['wisebank'] = $wisebank_combined;
    }
    
    // Handle old textarea format for backwards compatibility
    if (isset($_POST['wisebankapi']) && !empty($_POST['wisebankapi'])) {
        $wisebank_textarea = sanitize_textarea_field($_POST['wisebankapi']);
        update_user_meta($couponuserid, 'wcu_wisebank', $wisebank_textarea);
        $updated_payout_fields['wisebank'] = $wisebank_textarea;
    }

    if (!empty($updated_payout_fields)) {
        do_action('wcusage_hook_dash_update_payment_methods');
    }

    // Update statement (billing) settings
    $billing_fields = [
        'wcu-company' => 'wcu_billing_company',
        'wcu-billing1' => 'wcu_billing_address_1',
        'wcu-billing2' => 'wcu_billing_address_2',
        'wcu-billing3' => 'wcu_billing_address_3',
        'wcu-taxid' => 'wcu_billing_taxid'
    ];

    $updated_billing_fields = [];
    foreach($billing_fields as $post_key => $meta_key) {
        if(isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            update_user_meta($couponuserid, $meta_key, $value);
            $updated_billing_fields[$post_key] = $value;
        }
    }

    // Update custom account details
    $account_fields = [
        'wcu_first_name' => 'first_name',
        'wcu_last_name' => 'last_name',
        'wcu_display_name' => 'display_name',
        'wcu_email' => 'user_email',
        'wcu_phone' => 'wcu_phone',
        'wcu_website' => 'wcu_website'
    ];

    $updated_account_fields = [];
    $user_data = ['ID' => $couponuserid];

    // If $couponuserid matches current user ID
    if($couponuserid == get_current_user_id()) {
        foreach($account_fields as $post_key => $meta_key) {
            if($meta_key === 'user_email') {
                // Check the email is not empty
                if(empty($_POST[$post_key])) {
                    wp_send_json_error(esc_html__('Email is required.', 'woo-coupon-usage'));
                    wp_die();
                }
                // Check email is valid
                if(!is_email($_POST[$post_key])) {
                    wp_send_json_error(esc_html__('Invalid account email address.', 'woo-coupon-usage'));
                    wp_die();
                }
                // Check email does not already exist (for a different user)
                $existing_email_user_id = email_exists($_POST[$post_key]);
                if($existing_email_user_id && $existing_email_user_id != $couponuserid) {
                    wp_send_json_error(esc_html__('Email already exists.', 'woo-coupon-usage'));
                    wp_die();
                }
            }
            if(isset($_POST[$post_key])) {
                $value = $meta_key === 'user_email' ? sanitize_email($_POST[$post_key]) : sanitize_text_field($_POST[$post_key]);
                if($meta_key === 'user_email') {
                    $user_data['user_email'] = $value;
                } else {
                    update_user_meta($couponuserid, $meta_key, $value);
                }
                $updated_account_fields[$post_key] = $value;
            }
        }
    } else {
        // Error message
        wp_send_json_error('Permission denied: You can only update your own account details.');
        wp_die();
    }

    // Handle state field for US bank accounts
    if (isset($_POST['wcu_wisebank_state'])) {
        $state = sanitize_text_field($_POST['wcu_wisebank_state']);
        update_user_meta($couponuserid, 'wcu_wisebank_state', $state);
    }

    if (count($user_data) > 1) {
        $result = wp_update_user($user_data);
        if (is_wp_error($result)) {
            wp_send_json_error('Failed to update user: ' . $result->get_error_message());
            wp_die();
        }
    }

    wp_send_json_success([
        'message' => __('Settings updated successfully.', 'woo-coupon-usage'),
        'updated_payout_fields' => $updated_payout_fields,
        'updated_billing_fields' => $updated_billing_fields,
        'updated_account_fields' => $updated_account_fields
    ]);
    wp_die();
}

/**
 * Displays the settings tab content on affiliate dashboard
 */
if (!function_exists('wcusage_tab_settings')) {
    function wcusage_tab_settings($postid, $couponuserid) {
        $options = get_option('wcusage_options');
        $currentuserid = get_current_user_id();

        // Notifications
        $wcu_enable_notifications = get_post_meta($postid, 'wcu_enable_notifications', true);
        if ($wcu_enable_notifications == "") {
            $wcu_enable_notifications = true;
        }

        // Reports
        $wcusage_field_enable_reports = wcusage_get_setting_value('wcusage_field_enable_reports', 1);
        $enable_reports_user_option = wcusage_get_setting_value('wcusage_field_enable_reports_user_option', 1);
        $enable_reports_default = wcusage_get_setting_value('wcusage_field_enable_reports_default', 1);
        if ($enable_reports_user_option) {
            $wcu_enable_reports = get_post_meta($postid, 'wcu_enable_reports', true);
            if ($wcu_enable_reports == "") {
                $wcu_enable_reports = $enable_reports_default;
            }
        }

        // Extra
        $wcu_notifications_extra = get_post_meta($postid, 'wcu_notifications_extra', true);
        $wcusage_email_enable_extra = wcusage_get_setting_value('wcusage_field_email_enable_extra', 1);

        // Account details
        $user = get_userdata($couponuserid);
        if($couponuserid) {
            $first_name = get_user_meta($couponuserid, 'first_name', true);
            $last_name = get_user_meta($couponuserid, 'last_name', true);
            $display_name = $user->display_name;
            $email = $user->user_email;
            $phone = get_user_meta($couponuserid, 'wcu_phone', true);
            $website = get_user_meta($couponuserid, 'wcu_website', true);
        } else {
            $first_name = '';
            $last_name = '';
            $display_name = '';
            $email = '';
            $phone = '';
            $website = '';
        }
        ?>

        <p class="wcu-tab-title settings-title" style="font-size: 22px; margin-bottom: 25px;"><?php echo esc_html__("Settings", "woo-coupon-usage"); ?>:</p>

        <?php if ($couponuserid == $currentuserid || wcusage_check_admin_access()) { ?>

            <form method="post" class="wcusage_settings_form" id="wcusage-settings-form" data-post-id="<?php echo esc_attr($postid); ?>">
                <?php wp_nonce_field('wcusage_settings_update', 'wcusage_settings_nonce'); ?>
                <div class="wcu-settings-tabs">
                    <ul class="wcu-settings-tab-nav">
                        <?php $active = 0; ?>
                        <?php if (wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code()) { ?>
                            <?php
                            $wcusage_field_payouts_enable = wcusage_get_setting_value('wcusage_field_payouts_enable', '1');
                            if($wcusage_field_payouts_enable) {
                            $active = 1;
                            ?>
                            <li class="active"><a href="#tab-payout-settings"><?php echo esc_html__("Payout Settings", "woo-coupon-usage"); ?></a></li>
                            <?php } ?>
                            <?php
                            $wcu_enable_statements = wcusage_get_setting_value('wcusage_field_payouts_enable_statements', '0');
                            $wcu_enable_statements_data = wcusage_get_setting_value('wcusage_field_payouts_enable_statements_data', '1');
                            if($wcu_enable_statements && $wcu_enable_statements_data) { ?>
                            <li><a href="#tab-statement-settings"><?php echo esc_html__("Statement Settings", "woo-coupon-usage"); ?></a></li>
                            <?php } ?>
                        <?php } ?>
                        <li <?php if(!$active) { ?>class="active"<?php } ?>><a href="#tab-email-notifications"><?php echo esc_html__("Email Notifications", "woo-coupon-usage"); ?></a></li>
                        <?php if (wcusage_get_setting_value('wcusage_field_show_settings_tab_account', '1')) { ?>
                            <li><a href="#tab-account-details"><?php echo esc_html__("Account Details", "woo-coupon-usage"); ?></a></li>
                        <?php } ?>
                    </ul>

                    <div class="wcu-settings-tab-content">
                        <!-- Email Notifications Tab -->
                        <div id="tab-email-notifications" class="wcu-settings-tab-pane <?php if(!$active) { ?>active<?php } ?>">
                            <p><strong><?php echo esc_html__("Email Notification Settings", "woo-coupon-usage"); ?></strong></p>
                            <p><input type="checkbox" id="wcu_enable_notifications" name="wcu_enable_notifications"
                                value="1" <?php if ($wcu_enable_notifications) { ?>checked<?php } ?>>
                                <?php echo esc_html__("Enable Email Notifications", "woo-coupon-usage"); ?></p>

                            <?php if (wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code()) { ?>
                                <?php
                                // Newsletter subscription state: subscribed if user meta flag not set
                                $is_unsub = get_user_meta($couponuserid, 'wcusage_newsletter_unsubscribed', true) ? true : false;
                                $newsletters_enabled = wcusage_get_setting_value('wcusage_field_email_newsletter_enable', 0);
                                $global_unsub_enabled = wcusage_get_setting_value('wcusage_field_newsletter_enable_unsubscribe', 1);
                                if($newsletters_enabled &&$global_unsub_enabled) { ?>
                                    <p><input type="checkbox" id="wcu_newsletter_subscribed" name="wcu_newsletter_subscribed" value="1" <?php if(!$is_unsub) { ?>checked<?php } ?>>
                                    <?php echo esc_html__("Subscribe to Affiliate Newsletters", "woo-coupon-usage"); ?>
                                <?php } ?>
                            <?php } ?>

                            <?php if ($enable_reports_user_option && $wcusage_field_enable_reports && wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code()) { ?>
                                <?php
                                $wcusage_field_pdfreports_freq = wcusage_get_setting_value('wcusage_field_pdfreports_freq', 'monthly');
                                $pdfreports_freq = '';
                                if ($wcusage_field_pdfreports_freq == "monthly") {
                                    $pdfreports_freq = esc_html__("Monthly", "woo-coupon-usage");
                                } elseif ($wcusage_field_pdfreports_freq == "weekly") {
                                    $pdfreports_freq = esc_html__("Weekly", "woo-coupon-usage");
                                } elseif ($wcusage_field_pdfreports_freq == "quarterly") {
                                    $pdfreports_freq = esc_html__("Quarterly", "woo-coupon-usage");
                                }
                                ?>
                                <p><input type="checkbox" id="wcu_enable_reports" name="wcu_enable_reports"
                                    value="1" <?php if ($wcu_enable_reports) { ?>checked<?php } ?>>
                                    <?php echo esc_html__("Enable Email Reports", "woo-coupon-usage"); ?> (<?php echo esc_html($pdfreports_freq); ?>)</p>
                            <?php } ?>

                            <?php if ($wcusage_email_enable_extra && wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code()) { ?>
                                <p><?php echo esc_html__("Additional Email Addresses: (Separate with Comma)", "woo-coupon-usage"); ?><br/>
                                    <input type="text" id="wcu_notifications_extra" name="wcu_notifications_extra"
                                        value="<?php echo esc_html($wcu_notifications_extra); ?>" style="width: 400px; max-width: 100%;"
                                        placeholder="example@email.com,another@email.com"></p>
                            <?php } ?>
                        </div>

                        <!-- Payout Settings Tab -->
                        <?php if (wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code()) { ?>
                            <?php if($wcusage_field_payouts_enable) { ?>
                                <div id="tab-payout-settings" class="wcu-settings-tab-pane <?php if($active) { ?>active<?php } ?>">
                                    <?php do_action('wcusage_hook_output_payout_data_section', $postid, ''); ?>
                                </div>
                            <?php } ?>

                            <!-- Statement Settings Tab -->
                            <?php if($wcu_enable_statements && $wcu_enable_statements_data) { ?>
                                <div id="tab-statement-settings" class="wcu-settings-tab-pane">
                                    <?php do_action('wcusage_hook_output_statement_data_section', $couponuserid); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>

                        <!-- Account Details Tab -->
                        <?php if (wcusage_get_setting_value('wcusage_field_show_settings_tab_account', '1')) { ?>
                            <div id="tab-account-details" class="wcu-settings-tab-pane">
                                <p class="wcu-settings-header"><strong><?php echo esc_html__("Account Details", "woo-coupon-usage"); ?></strong></p>
                                <?php if ($couponuserid && $currentuserid == $couponuserid) { ?>
                                    <?php $wcusage_field_show_settings_tab_gravatar = wcusage_get_setting_value('wcusage_field_show_settings_tab_gravatar', '1'); ?>
                                    <?php if($wcusage_field_show_settings_tab_gravatar) { ?>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label><?php echo esc_html__('Profile Picture', 'woo-coupon-usage'); ?></label>
                                        <div style="margin-bottom: 10px;" class="profile-picture">
                                            <?php echo get_avatar($couponuserid, 96); ?>
                                        </div>
                                        <p style="margin-top: 0px;font-size:12px;"><?php echo esc_html__('Your profile picture is managed via Gravatar. To set or change it, visit ', 'woo-coupon-usage'); ?><a href="https://gravatar.com/profile/avatars" target="_blank"><?php echo esc_html__('Gravatar.com', 'woo-coupon-usage'); ?></a>.</p>
                                    </p>
                                    <?php } ?>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_first_name"><?php echo esc_html__('First Name', 'woo-coupon-usage'); ?>:</label>
                                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                                            id="wcu_first_name" name="wcu_first_name" value="<?php echo esc_attr($first_name); ?>" autocomplete="given-name">
                                    </p>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_last_name"><?php echo esc_html__('Last Name', 'woo-coupon-usage'); ?>:</label>
                                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                                            id="wcu_last_name" name="wcu_last_name" value="<?php echo esc_attr($last_name); ?>" autocomplete="family-name">
                                    </p>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_display_name"><?php echo esc_html__('Display Name', 'woo-coupon-usage'); ?>:</label>
                                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                                            id="wcu_display_name" name="wcu_display_name" value="<?php echo esc_attr($display_name); ?>" autocomplete="nickname">
                                    </p>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_email"><?php echo esc_html__('Email Address', 'woo-coupon-usage'); ?>:</label>
                                        <input type="email" class="woocommerce-Input woocommerce-Input--email input-text"
                                            id="wcu_email" name="wcu_email" value="<?php echo esc_attr($email); ?>" autocomplete="email"
                                            required>
                                    </p>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_phone"><?php echo esc_html__('Phone Number', 'woo-coupon-usage'); ?>:</label>
                                        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text"
                                            id="wcu_phone" name="wcu_phone" value="<?php echo esc_attr($phone); ?>" autocomplete="tel">
                                    </p>
                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <label for="wcu_website"><?php echo esc_html__('Website', 'woo-coupon-usage'); ?>:</label>
                                        <input type="url" class="woocommerce-Input woocommerce-Input--text input-text"
                                            id="wcu_website" name="wcu_website" value="<?php echo esc_attr($website); ?>" autocomplete="url">
                                    </p>
                                    <p>
                                        <label for="wcu_password"><?php echo esc_html__('Password', 'woo-coupon-usage'); ?>:</label>
                                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" target="_blank">
                                            <?php echo esc_html__('Click here to reset your password.', 'woo-coupon-usage'); ?>
                                        </a>
                                    </p>
                                <?php } else { ?>
                                    <p><?php echo esc_html__("Sorry, this coupon is not assigned to you. You can only edit your own account details.", "woo-coupon-usage"); ?></p>
                                    <?php if (wcusage_check_admin_access() && current_user_can('edit_users')) { ?>
                                        <p><?php echo sprintf(esc_html__("[Admin] You can edit the account details for this user in the admin area: %s", "woo-coupon-usage"),
                                            "<a href='" . esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $couponuserid) ) . "' target='_blank'>" . esc_html__("View Affiliate", "woo-coupon-usage") . "</a>"); ?></p>
                                        <br/>
                                        <span class='admin-edit-account'>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('First Name', 'woo-coupon-usage'); ?>: <?php echo esc_html($first_name); ?></label>
                                            </p>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('Last Name', 'woo-coupon-usage'); ?>: <?php echo esc_html($last_name); ?></label>
                                            </p>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('Display Name', 'woo-coupon-usage'); ?>: <?php echo esc_html($display_name); ?></label>
                                            </p>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('Email Address', 'woo-coupon-usage'); ?>: <?php echo esc_html($email); ?></label>
                                            </p>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('Phone Number', 'woo-coupon-usage'); ?>: <?php echo esc_html($phone); ?></label>
                                            </p>
                                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                                <label><?php echo esc_html__('Website', 'woo-coupon-usage'); ?>: <?php echo esc_html($website); ?></label>
                                            </p>
                                        </span>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>

                    <p>
                        <button type="submit" id="wcu-settings-update-button" class="wcu-save-settings-button woocommerce-Button button" name="submitsettingsupdate">
                            <?php echo esc_html__('Save changes', 'woo-coupon-usage'); ?>
                        </button>
                    </p>

                    <div id="wcu-settings-ajax-message"></div>
                </div>
            </form>

        <?php } else { ?>
            <br/><p><?php echo esc_html__("Sorry, this coupon is not assigned to you.", "woo-coupon-usage"); ?></p>
        <?php } ?>
        <?php
    }
}
add_action('wcusage_hook_tab_settings', 'wcusage_tab_settings', 10, 2);

/**
 * Gets settings tab for shortcode page
 */
add_action('wcusage_hook_dashboard_tab_content_settings', 'wcusage_dashboard_tab_content_settings', 10, 6);
if (!function_exists('wcusage_dashboard_tab_content_settings')) {
    function wcusage_dashboard_tab_content_settings($postid, $coupon_code, $combined_commission, $wcusage_page_load, $coupon_user_id, $other_affiliate = '') {
        if ($other_affiliate) {
            $coupon_user_id = $other_affiliate;
        }

        $options = get_option('wcusage_options');
        $currentuserid = get_current_user_id();

        if (isset($_POST['page-settings']) || isset($_POST['ml-page-settings']) || !isset($_POST['load-page']) || $wcusage_page_load == false) { ?>
            <div id="<?php echo $other_affiliate ? 'ml-wcu4' : 'wcu6'; ?>" <?php if (wcusage_get_setting_value('wcusage_field_show_tabs', '1')) { ?>class="wcutabcontent"<?php } ?>>
                <?php
                if ($coupon_user_id != $currentuserid && wcusage_check_admin_access()) {
                    //echo "<p style='margin: 5px 0 0 0; font-size: 12px;'>Admin notice: The 'settings' section is only visible to affiliate users assigned to the coupon. You are also able to see this because you are an administrator.</p>";
                }

                if ($coupon_user_id == $currentuserid || wcusage_check_admin_access()) {
                    do_action('wcusage_hook_tab_settings', $postid, $coupon_user_id);
                } else { ?>
                    <br/><p><?php echo esc_html__("Sorry, this coupon is not assigned to you.", "woo-coupon-usage"); ?></p>
                <?php } ?>
            </div>
            <div style="width: 100%; clear: both; display: inline;"></div>
        <?php } ?>
        <?php
    }
}

/**
 * Combine Wisebank fields for backward compatibility
 */
if( !function_exists( 'wcusage_combine_wisebank_fields' ) ) {
    function wcusage_combine_wisebank_fields($post_data) {
        $combined = '';
        
        if (!empty($post_data['wisebank_region'])) {
            $combined .= "Region: " . $post_data['wisebank_region'] . "\n";
        }
        
        if (!empty($post_data['wisebank_account_name'])) {
            $combined .= "Account Name: " . $post_data['wisebank_account_name'] . "\n";
        }
        
        if (!empty($post_data['wisebank_account_number'])) {
            $combined .= "Account Number: " . $post_data['wisebank_account_number'] . "\n";
        }
        
        if (!empty($post_data['wisebank_routing_number'])) {
            $combined .= "Routing Number: " . $post_data['wisebank_routing_number'] . "\n";
        }
        
        if (!empty($post_data['wisebank_swift_code'])) {
            $combined .= "SWIFT Code: " . $post_data['wisebank_swift_code'] . "\n";
        }
        
        if (!empty($post_data['wisebank_iban'])) {
            $combined .= "IBAN: " . $post_data['wisebank_iban'] . "\n";
        }
        
        if (!empty($post_data['wisebank_sort_code'])) {
            $combined .= "Sort Code: " . $post_data['wisebank_sort_code'] . "\n";
        }
        
        if (!empty($post_data['wisebank_bank_name'])) {
            $combined .= "Bank Name: " . $post_data['wisebank_bank_name'] . "\n";
        }
        
        if (!empty($post_data['wisebank_bank_address'])) {
            $combined .= "Bank Address: " . $post_data['wisebank_bank_address'] . "\n";
        }
        
        if (!empty($post_data['wisebank_country'])) {
            $combined .= "Country: " . $post_data['wisebank_country'] . "\n";
        }
        
        if (!empty($post_data['wisebank_address'])) {
            $combined .= "Recipient Address: " . $post_data['wisebank_address'] . "\n";
        }
        
        if (!empty($post_data['wisebank_city'])) {
            $combined .= "Recipient City: " . $post_data['wisebank_city'] . "\n";
        }
        
        if (!empty($post_data['wisebank_postcode'])) {
            $combined .= "Recipient Postcode: " . $post_data['wisebank_postcode'] . "\n";
        }
        
        if (!empty($post_data['wisebank_recipient_country'])) {
            $combined .= "Recipient Country: " . $post_data['wisebank_recipient_country'] . "\n";
        }
        
        return trim($combined);
    }
}