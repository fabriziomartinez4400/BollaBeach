<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Floating Affiliate Widget - Settings & Configuration
 * Settings retrieval and caching functions
 */

// Get only essential settings needed for initial widget display
function wcusage_get_floating_widget_essential_settings() {
    // Cache settings to avoid multiple DB queries
    static $cached_settings = null;
    if ($cached_settings !== null) {
        return $cached_settings;
    }
    
    $cached_settings = array(
        'position' => wcusage_get_setting_value('wcusage_field_floating_widget_position', 'bottom-left'),
        'size' => wcusage_get_setting_value('wcusage_field_floating_widget_size', 'medium'),
        'popup_title' => wcusage_get_setting_value('wcusage_field_floating_widget_popup_title', 'Affiliate Dashboard'),
        'button_colors' => array(
            'bg' => wcusage_get_setting_value('wcusage_field_floating_button_bg_color', '#1b3e47'),
            'text' => wcusage_get_setting_value('wcusage_field_floating_button_text_color', '#ffffff'),
            'hover' => wcusage_get_setting_value('wcusage_field_floating_button_hover_color', '#005d75'),
            'border' => wcusage_get_setting_value('wcusage_field_floating_button_border_color', '#1b3e47')
        ),
        'theme_color' => wcusage_get_setting_value('wcusage_field_floating_widget_theme_color', '#1b3e47'),
        'display' => array(
            'hide_mobile' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_mobile', '0'),
            'hide_tablet' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_tablet', '0')
        )
    );
    
    return $cached_settings;
}

// Get all floating widget settings (called only when needed)
function wcusage_get_floating_widget_settings() {
    // Cache settings to avoid multiple DB queries
    static $cached_settings = null;
    if ($cached_settings !== null) {
        return $cached_settings;
    }
    
    // Get floating button color settings
    $wcusage_button_bg_color = wcusage_get_setting_value('wcusage_field_floating_button_bg_color', '#1b3e47');
    $wcusage_button_text_color = wcusage_get_setting_value('wcusage_field_floating_button_text_color', '#ffffff');
    $wcusage_button_hover_color = wcusage_get_setting_value('wcusage_field_floating_button_hover_color', '#005d75');
    $wcusage_button_border_color = wcusage_get_setting_value('wcusage_field_floating_button_border_color', '#1b3e47');
    
    // Get widget theme color
    $wcusage_widget_theme_color = wcusage_get_setting_value('wcusage_field_floating_widget_theme_color', '#1b3e47');
    
    $cached_settings = array(
        'enable' => wcusage_get_setting_value('wcusage_field_floating_widget_enable', '0'),
        'text_affiliate' => wcusage_get_setting_value('wcusage_field_floating_widget_text_affiliate', 'Affiliates'),
        'text_non_affiliate' => wcusage_get_setting_value('wcusage_field_floating_widget_text_non_affiliate', 'Refer and Earn'),
        'icon' => wcusage_get_setting_value('wcusage_field_floating_widget_icon', '🎁'),
        'position' => wcusage_get_setting_value('wcusage_field_floating_widget_position', 'bottom-left'),
        'size' => wcusage_get_setting_value('wcusage_field_floating_widget_size', 'medium'),
        'popup_title' => wcusage_get_setting_value('wcusage_field_floating_widget_popup_title', 'Affiliate Dashboard'),
        'show_refer_tab' => wcusage_get_setting_value('wcusage_field_floating_widget_show_refer_tab', '1'),
        'show_stats_tab' => wcusage_get_setting_value('wcusage_field_floating_widget_show_stats_tab', '1'),
        'show_orders_tab' => wcusage_get_setting_value('wcusage_field_floating_widget_show_orders_tab', '1'),
        'show_payouts_tab' => wcusage_get_setting_value('wcusage_field_floating_widget_show_payouts_tab', '1'),
        'show_creatives_tab' => wcusage_get_setting_value('wcusage_field_floating_widget_show_creatives_tab', '1'),
        'show_payout_button' => wcusage_get_setting_value('wcusage_field_floating_widget_show_payout_button', '1'),
        'show_dashboard_button' => wcusage_get_setting_value('wcusage_field_floating_widget_show_dashboard_button', '1'),
        'refer_tab_text' => wcusage_get_setting_value('wcusage_field_floating_widget_refer_tab_text', ''),
        'stats_tab_text' => wcusage_get_setting_value('wcusage_field_floating_widget_stats_tab_text', ''),
        'orders_tab_text' => wcusage_get_setting_value('wcusage_field_floating_widget_orders_tab_text', ''),
        'payouts_tab_text' => wcusage_get_setting_value('wcusage_field_floating_widget_payouts_tab_text', ''),
        'creatives_tab_text' => wcusage_get_setting_value('wcusage_field_floating_widget_creatives_tab_text', ''),
        'benefits_title' => wcusage_get_setting_value('wcusage_field_floating_widget_benefits_title', 'Join Our Affiliate Program'),
        'benefits_subtitle' => wcusage_get_setting_value('wcusage_field_floating_widget_benefits_subtitle', 'Start earning money by referring your friends!'),
        'benefit_1' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_1', 'Give your friends a discount on their purchase'),
        'benefit_1_icon' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_1_icon', 'fas fa-dollar-sign'),
        'benefit_2' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_2', 'Get your unique referral links instantly'),
        'benefit_2_icon' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_2_icon', 'fas fa-chart-bar'),
        'benefit_3' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_3', 'Earn commission on every sale'),
        'benefit_3_icon' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_3_icon', 'fas fa-link'),
        'benefit_4' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_4', 'Fast and reliable payouts'),
        'benefit_4_icon' => wcusage_get_setting_value('wcusage_field_floating_widget_benefit_4_icon', 'fas fa-credit-card'),
        'button_colors' => array(
            'bg' => $wcusage_button_bg_color,
            'text' => $wcusage_button_text_color,
            'hover' => $wcusage_button_hover_color,
            'border' => $wcusage_button_border_color
        ),
        'colors' => array(
            'theme' => $wcusage_widget_theme_color
        ),
        'display' => array(
            'hide_logged_out' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_logged_out', '0'),
            'hide_logged_in' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_logged_in', '0'),
            'hide_non_affiliate' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_non_affiliate', '0'),
            'hide_mobile' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_mobile', '0'),
            'hide_tablet' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_tablet', '0'),
            'page_display' => wcusage_get_setting_value('wcusage_field_floating_widget_page_display', 'all'),
            'specific_pages' => wcusage_get_setting_value('wcusage_field_floating_widget_specific_pages', ''),
            'hide_affiliate_pages' => wcusage_get_setting_value('wcusage_field_floating_widget_hide_affiliate_pages', '1')
        )
    );
    
    return $cached_settings;
}
