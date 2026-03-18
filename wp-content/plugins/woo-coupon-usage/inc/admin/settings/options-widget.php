<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_widget( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

    <div id="floating-widget-settings" class="settings-area">

        <h1><?php echo esc_html__( 'Floating Affiliate Widget', 'woo-coupon-usage' ); ?></h1>

        <hr/>

        <p><?php echo esc_html__( 'Customize the floating affiliate widget that appears on your website to provide easy access to affiliate features.', 'woo-coupon-usage' ); ?></p>

        <br/>

        <!-- FAQ: What is the floating widget? -->
        <div class="wcu-admin-faq">

            <?php echo wcusage_admin_faq_toggle(
            "wcu_show_section_qna_floating_widget",
            "wcu_qna_floating_widget",
            "FAQ: What is the floating affiliate widget?");
            ?>

            <div class="wcu-admin-faq-content wcu_qna_floating_widget" id="wcu_qna_floating_widget" style="display: none;">

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The floating widget displays a small button on your website that opens a compact affiliate dashboard popup.', 'woo-coupon-usage' ); ?><br/>

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'For logged-in affiliates, it shows their dashboard with statistics, referral links, recent orders, payouts, and creatives.', 'woo-coupon-usage' ); ?><br/>

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'For non-affiliates, it shows an affiliate registration form with program benefits.', 'woo-coupon-usage' ); ?><br/>

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The widget is responsive and works well on both desktop and mobile devices.', 'woo-coupon-usage' ); ?><br/>

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The widget is built with performance in mind, only loading most of the resources needed when the button is clicked.', 'woo-coupon-usage' ); ?><br/>

                <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If affiliate statistics need to be recalculated, users will be directed to visit the full dashboard to complete the calculations first.', 'woo-coupon-usage' ); ?><br/>

                <br/>
                
                <strong><?php echo esc_html__( 'For more information, watch the video:', 'woo-coupon-usage' ); ?></strong>
                <br/>
                <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/1098315791?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>

            </div>

        </div>

        <br/><hr/>

        <!-- Enable Floating Widget -->
        <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_enable', 0, 'Enable Floating Affiliate Widget', '0px'); ?>
        <i><?php echo esc_html__( 'Display a floating affiliate button on your website that opens a compact dashboard popup.', 'woo-coupon-usage' ); ?></i><br/>

        <?php wcusage_setting_toggle('.wcusage_field_floating_widget_enable', '.wcu-field-section-floating-widget'); // Show or Hide ?>
        <span class="wcu-field-section-floating-widget">

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Button Settings', 'woo-coupon-usage' ); ?></h3>

            <!-- Button Text for Affiliates -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_text_affiliate', 'Refer & Earn', esc_html__( 'Button Text (For Affiliates)', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Text shown on the floating button for users who are already affiliates.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Button Text for Non-Affiliates -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_text_non_affiliate', 'Refer & Earn', esc_html__( 'Button Text (For Non-Affiliates)', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Text shown on the floating button for users who are not yet affiliates.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Button Icon -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_icon', '🎁', esc_html__( 'Button Icon', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Icon displayed on the floating button. You can use emojis or text.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Button Position -->
            <p>
                <?php $wcusage_field_floating_widget_position = wcusage_get_setting_value('wcusage_field_floating_widget_position', 'bottom-left'); ?>
                <input type="hidden" value="0" id="wcusage_field_floating_widget_position" data-custom="custom" name="wcusage_options[wcusage_field_floating_widget_position]" >
                <strong><label for="scales"><?php echo esc_html__( 'Button Position', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_position]" id="wcusage_field_floating_widget_position">
                    <option value="bottom-left" <?php if($wcusage_field_floating_widget_position == "bottom-left") { ?>selected<?php } ?>><?php echo esc_html__( 'Bottom Left', 'woo-coupon-usage' ); ?></option>
                    <option value="bottom-right" <?php if($wcusage_field_floating_widget_position == "bottom-right") { ?>selected<?php } ?>><?php echo esc_html__( 'Bottom Right', 'woo-coupon-usage' ); ?></option>
                    <option value="top-left" <?php if($wcusage_field_floating_widget_position == "top-left") { ?>selected<?php } ?>><?php echo esc_html__( 'Top Left', 'woo-coupon-usage' ); ?></option>
                    <option value="top-right" <?php if($wcusage_field_floating_widget_position == "top-right") { ?>selected<?php } ?>><?php echo esc_html__( 'Top Right', 'woo-coupon-usage' ); ?></option>
                    <option value="center-left" <?php if($wcusage_field_floating_widget_position == "center-left") { ?>selected<?php } ?>><?php echo esc_html__( 'Center Left', 'woo-coupon-usage' ); ?></option>
                    <option value="center-right" <?php if($wcusage_field_floating_widget_position == "center-right") { ?>selected<?php } ?>><?php echo esc_html__( 'Center Right', 'woo-coupon-usage' ); ?></option>
                </select>
            </p>

            <br/>

            <!-- Button Size -->
            <p>
                <?php $wcusage_field_floating_widget_size = wcusage_get_setting_value('wcusage_field_floating_widget_size', 'medium'); ?>
                <input type="hidden" value="0" id="wcusage_field_floating_widget_size" data-custom="custom" name="wcusage_options[wcusage_field_floating_widget_size]" >
                <strong><label for="scales"><?php echo esc_html__( 'Button Size', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_size]" id="wcusage_field_floating_widget_size">
                    <option value="small" <?php if($wcusage_field_floating_widget_size == "small") { ?>selected<?php } ?>><?php echo esc_html__( 'Small', 'woo-coupon-usage' ); ?></option>
                    <option value="medium" <?php if($wcusage_field_floating_widget_size == "medium") { ?>selected<?php } ?>><?php echo esc_html__( 'Medium', 'woo-coupon-usage' ); ?></option>
                    <option value="large" <?php if($wcusage_field_floating_widget_size == "large") { ?>selected<?php } ?>><?php echo esc_html__( 'Large', 'woo-coupon-usage' ); ?></option>
                </select>
            </p>

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Popup Settings', 'woo-coupon-usage' ); ?></h3>

            <!-- Popup Title -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_popup_title', 'Affiliate Program', esc_html__( 'Popup Title', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Title shown at the top of the popup window.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Tab Visibility Settings -->
            <h4><?php echo esc_html__( 'Tab Visibility', 'woo-coupon-usage' ); ?></h4>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_refer_tab', 1, esc_html__( 'Show "Refer" tab', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the tab with coupon code and referral links.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_stats_tab', 1, esc_html__( 'Show "Stats" tab', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the tab with affiliate statistics.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_orders_tab', 1, esc_html__( 'Show "Orders" tab', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the tab with recent referred orders.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_payouts_tab', 1, esc_html__( 'Show "Payouts" tab', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the payouts tab (only shown if payouts are enabled).', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_creatives_tab', 1, esc_html__( 'Show "Creatives" tab', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the creatives tab (only shown if creatives are enabled).', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Button Visibility Settings -->
            <h4><?php echo esc_html__( 'Button Visibility', 'woo-coupon-usage' ); ?></h4>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_payout_button', 1, esc_html__( 'Show "Request Payout" button', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the payout request button on the payouts tab (PRO).', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_show_dashboard_button', 1, esc_html__( 'Show "View Full Dashboard" button', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Display the link to the full affiliate dashboard.', 'woo-coupon-usage' ); ?></i><br/>

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Tab Custom Text', 'woo-coupon-usage' ); ?></h3>

            <i><?php echo esc_html__( 'Add custom text that will appear at the top of each tab (below the section title).', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Refer Tab Custom Text -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_refer_tab_text', '', esc_html__( 'Refer Tab Custom Text', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Custom text shown at the top of the Refer tab.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Stats Tab Custom Text -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_stats_tab_text', '', esc_html__( 'Stats Tab Custom Text', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Custom text shown at the top of the Stats tab.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Orders Tab Custom Text -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_orders_tab_text', '', esc_html__( 'Orders Tab Custom Text', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Custom text shown at the top of the Orders tab.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Payouts Tab Custom Text -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_payouts_tab_text', '', esc_html__( 'Payouts Tab Custom Text', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Custom text shown at the top of the Payouts tab.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Creatives Tab Custom Text -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_creatives_tab_text', '', esc_html__( 'Creatives Tab Custom Text', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Custom text shown at the top of the Creatives tab.', 'woo-coupon-usage' ); ?></i><br/>

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Floating Button Colors', 'woo-coupon-usage' ); ?></h3>

            <!-- Floating Button Background Color -->
            <?php wcusage_setting_color_option('wcusage_field_floating_button_bg_color', '#1b3e47', esc_html__( 'Button Background Color', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Background color for the main floating button.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Floating Button Text Color -->
            <?php wcusage_setting_color_option('wcusage_field_floating_button_text_color', '#ffffff', esc_html__( 'Button Text Color', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Text color for the main floating button.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Floating Button Hover Color -->
            <?php wcusage_setting_color_option('wcusage_field_floating_button_hover_color', '#005d75', esc_html__( 'Button Hover Color', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Background color when hovering over the floating button.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Floating Button Border Color -->
            <?php wcusage_setting_color_option('wcusage_field_floating_button_border_color', '#1b3e47', esc_html__( 'Button Border Color', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Border color for the main floating button.', 'woo-coupon-usage' ); ?></i><br/>

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Widget Colors', 'woo-coupon-usage' ); ?></h3>

            <!-- Widget Theme Color -->
            <?php wcusage_setting_color_option('wcusage_field_floating_widget_theme_color', '#1b3e47', esc_html__( 'Theme Color', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Primary color for widget header, benefits box, titles, and values.', 'woo-coupon-usage' ); ?></i><br/>

            <br/><hr/>

            <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Registration Form Benefits', 'woo-coupon-usage' ); ?></h3>

            <!-- Benefits Title -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefits_title', 'Join Our Affiliate Program', esc_html__( 'Benefits Section Title', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Title shown in the benefits section for non-affiliates.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Benefits Subtitle -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefits_subtitle', 'Start earning money by referring your friends!', esc_html__( 'Benefits Section Subtitle', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Subtitle shown in the benefits section for non-affiliates.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Benefit 1 -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefit_1', 'Earn commission on every sale', esc_html__( 'Benefit #1', 'woo-coupon-usage' ), '0px'); ?>

            <?php
            $benefit_icons = array(
                'fas fa-dollar-sign' => __( 'Dollar Sign', 'woo-coupon-usage' ),
                'fas fa-chart-bar' => __( 'Chart Bar', 'woo-coupon-usage' ),
                'fas fa-link' => __( 'Link', 'woo-coupon-usage' ),
                'fas fa-credit-card' => __( 'Credit Card', 'woo-coupon-usage' ),
                'fas fa-money-bill-wave' => __( 'Money Bill', 'woo-coupon-usage' ),
                'fas fa-coins' => __( 'Coins', 'woo-coupon-usage' ),
                'fas fa-piggy-bank' => __( 'Piggy Bank', 'woo-coupon-usage' ),
                'fas fa-chart-line' => __( 'Chart Line', 'woo-coupon-usage' ),
                'fas fa-percentage' => __( 'Percentage', 'woo-coupon-usage' ),
                'fas fa-gift' => __( 'Gift', 'woo-coupon-usage' ),
                'fas fa-star' => __( 'Star', 'woo-coupon-usage' ),
                'fas fa-trophy' => __( 'Trophy', 'woo-coupon-usage' ),
                'fas fa-heart' => __( 'Heart', 'woo-coupon-usage' ),
                'fas fa-handshake' => __( 'Handshake', 'woo-coupon-usage' ),
                'fas fa-users' => __( 'Users', 'woo-coupon-usage' ),
                'fas fa-thumbs-up' => __( 'Thumbs Up', 'woo-coupon-usage' ),
                'fas fa-check-circle' => __( 'Check Circle', 'woo-coupon-usage' )
            );
            ?>

            <!-- Benefit 1 Icon -->
            <?php $wcusage_field_floating_widget_benefit_1_icon = wcusage_get_setting_value('wcusage_field_floating_widget_benefit_1_icon', 'fas fa-dollar-sign'); ?>
            <p>
                <strong><label><?php echo esc_html__( 'Benefit #1 Icon', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_benefit_1_icon]" id="wcusage_field_floating_widget_benefit_1_icon" class="wcusage-icon-select">
                <?php foreach ($benefit_icons as $icon_class => $icon_label) : ?>
                    <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($wcusage_field_floating_widget_benefit_1_icon, $icon_class); ?>>
                        <?php echo esc_html($icon_label); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <span class="wcusage-icon-preview" id="icon-preview-1"><i class="<?php echo esc_attr($wcusage_field_floating_widget_benefit_1_icon); ?>"></i></span>
            </p>

            <br/>

            <!-- Benefit 2 -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefit_2', 'Track your performance in real-time', esc_html__( 'Benefit #2', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Benefit 2 Icon -->
            <?php $wcusage_field_floating_widget_benefit_2_icon = wcusage_get_setting_value('wcusage_field_floating_widget_benefit_2_icon', 'fas fa-chart-bar'); ?>
            <p>
                <strong><label><?php echo esc_html__( 'Benefit #2 Icon', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_benefit_2_icon]" id="wcusage_field_floating_widget_benefit_2_icon" class="wcusage-icon-select">
                    <?php foreach ($benefit_icons as $icon_class => $icon_label) : ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($wcusage_field_floating_widget_benefit_2_icon, $icon_class); ?>>
                            <?php echo esc_html($icon_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="wcusage-icon-preview" id="icon-preview-2"><i class="<?php echo esc_attr($wcusage_field_floating_widget_benefit_2_icon); ?>"></i></span>
            </p>

            <br/>

            <!-- Benefit 3 -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefit_3', 'Get your unique referral links instantly', esc_html__( 'Benefit #3', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Benefit 3 Icon -->
            <?php $wcusage_field_floating_widget_benefit_3_icon = wcusage_get_setting_value('wcusage_field_floating_widget_benefit_3_icon', 'fas fa-link'); ?>
            <p>
                <strong><label><?php echo esc_html__( 'Benefit #3 Icon', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_benefit_3_icon]" id="wcusage_field_floating_widget_benefit_3_icon" class="wcusage-icon-select">
                    <?php foreach ($benefit_icons as $icon_class => $icon_label) : ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($wcusage_field_floating_widget_benefit_3_icon, $icon_class); ?>>
                            <?php echo esc_html($icon_label); ?>
                        </option>
                    <?php endforeach; ?>        
                </select>
                <span class="wcusage-icon-preview" id="icon-preview-3"><i class="<?php echo esc_attr($wcusage_field_floating_widget_benefit_3_icon); ?>"></i></span>
            </p>

            <br/>

            <!-- Benefit 4 -->
            <?php wcusage_setting_text_option('wcusage_field_floating_widget_benefit_4', 'Fast and reliable payouts', esc_html__( 'Benefit #4', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Benefit 4 Icon -->
            <?php $wcusage_field_floating_widget_benefit_4_icon = wcusage_get_setting_value('wcusage_field_floating_widget_benefit_4_icon', 'fas fa-credit-card'); ?>
            <p>
                <strong><label><?php echo esc_html__( 'Benefit #4 Icon', 'woo-coupon-usage' ); ?>:</label></strong><br/>
                <select name="wcusage_options[wcusage_field_floating_widget_benefit_4_icon]" id="wcusage_field_floating_widget_benefit_4_icon" class="wcusage-icon-select">
                    <?php foreach ($benefit_icons as $icon_class => $icon_label) : ?>
                        <option value="<?php echo esc_attr($icon_class); ?>" <?php selected($wcusage_field_floating_widget_benefit_4_icon, $icon_class); ?>>
                            <?php echo esc_html($icon_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="wcusage-icon-preview" id="icon-preview-4"><i class="<?php echo esc_attr($wcusage_field_floating_widget_benefit_4_icon); ?>"></i></span>
            </p>

            <br/><hr/>

            <!-- Display Settings -->
            <h3 id='floating-widget-display'><?php echo esc_html__( 'Display Settings', 'woo-coupon-usage' ); ?></h3>

            <!-- User Visibility Settings -->
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_logged_out', 0, __( 'Hide for logged out users', 'woo-coupon-usage' ), '0px'); ?>
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_logged_in', 0, __( 'Hide for logged in users', 'woo-coupon-usage' ), '0px'); ?>
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_non_affiliate', 0, __( 'Hide for non-affiliate users', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Device Visibility Settings -->
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_mobile', 0, __( 'Hide on mobile devices', 'woo-coupon-usage' ), '0px'); ?>
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_tablet', 0, __( 'Hide on tablet devices', 'woo-coupon-usage' ), '0px'); ?>

            <br/>

            <!-- Page Display Settings -->
            <?php $wcusage_field_floating_widget_page_display = wcusage_get_setting_value('wcusage_field_floating_widget_page_display', 'all'); ?>
            <?php wcusage_setting_select_option('wcusage_field_floating_widget_page_display', $wcusage_field_floating_widget_page_display, __( 'Page Display', 'woo-coupon-usage' ), '0px',
                array(
                    'all' => __( 'Show on all pages', 'woo-coupon-usage' ),
                    'specific_show' => __( 'Show on specific pages', 'woo-coupon-usage' ),
                    'specific_hide' => __( 'Hide on specific pages', 'woo-coupon-usage' )
                )
            ); ?>

            <span class="wcu-field-section-floating-widget-specific-pages">
            <!-- Specific Pages Input -->
            <?php $wcusage_field_floating_widget_specific_pages = wcusage_get_setting_value('wcusage_field_floating_widget_specific_pages', ''); ?>
            <?php wcusage_setting_textarea_option('wcusage_field_floating_widget_specific_pages', $wcusage_field_floating_widget_specific_pages, __( 'Specific Pages (URLs)', 'woo-coupon-usage' ), '0px'); ?>
            <p><em><?php echo esc_html__( 'Enter URLs separated by commas. Use relative URLs (e.g., /shop, /about) or full URLs. Use * for wildcards (e.g., /shop/* for all shop pages).', 'woo-coupon-usage' ); ?></em></p>
            </span>
            <script>
            jQuery(document).ready(function($) {
                $('#wcusage_field_floating_widget_page_display').change(function() {
                    var selected = $(this).val();
                    if (selected === 'specific_show' || selected === 'specific_hide') {
                        $('.wcu-field-section-floating-widget-specific-pages').show();
                    } else {
                        $('.wcu-field-section-floating-widget-specific-pages').hide();
                    }
                });
                if( $('#wcusage_field_floating_widget_page_display').val() === 'specific_show' || $('#wcusage_field_floating_widget_page_display').val() === 'specific_hide') {
                    $('.wcu-field-section-floating-widget-specific-pages').show();
                } else {
                    $('.wcu-field-section-floating-widget-specific-pages').hide();
                }
            });
            </script>

            <br/>

            <!-- Hide on Affiliate Pages -->
            <?php wcusage_setting_toggle_option('wcusage_field_floating_widget_hide_affiliate_pages', 1, __( 'Hide on affiliate pages (affiliate dashboard and registration pages)', 'woo-coupon-usage' ), '0px'); ?>

        </span>

    </div>

    <style>
    .wcusage-icon-preview i {
        background: none;
        margin-left: 10px;
        font-size: 20px;
        vertical-align: middle;
        display: inline-block;
        width: 20px;
        text-align: center;
    }
    .wcusage-icon-select {
        width: 200px;
        margin-right: 10px;
        vertical-align: middle;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {

        // Update icon preview when dropdown changes
        $('.wcusage-icon-select').on('change', function() {
            var iconClass = $(this).val();
            var selectId = $(this).attr('id');
            var benefitNumber = selectId.replace('wcusage_field_floating_widget_benefit_', '').replace('_icon', '');
            $('#icon-preview-' + benefitNumber + ' i').attr('class', iconClass);
        });

        // Show on load
        $('.wcusage-icon-select').each(function() {
            var iconClass = $(this).val();
            var selectId = $(this).attr('id');
            var benefitNumber = selectId.replace('wcusage_field_floating_widget_benefit_', '').replace('_icon', '');
            $('#icon-preview-' + benefitNumber + ' i').attr('class', iconClass);
        });

    });
    </script>

    <?php
}