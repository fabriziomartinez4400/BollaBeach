<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Floating Affiliate Widget - Core Functions
 * Main initialization and core widget functionality
 */

// Enqueue scripts and styles for the floating widget
add_action('wp_enqueue_scripts', 'wcusage_enqueue_floating_widget_assets');
function wcusage_enqueue_floating_widget_assets() {
    $enable_widget = wcusage_get_setting_value('wcusage_field_floating_widget_enable', '0');
    
    if (!$enable_widget) {
        return;
    }
    
    // Quick display condition check
    if (!wcusage_should_show_floating_widget_quick()) {
        return;
    }

    wp_enqueue_script('jquery');
    
    // Enqueue minimal floating widget CSS (button only)
    wp_enqueue_style(
        'wcusage-floating-widget-button',
        WCUSAGE_UNIQUE_PLUGIN_URL . 'inc/widget/css/floating-widget-button.css',
        array(),
        '1.0.0'
    );
    
    // Enqueue minimal floating widget JS (button only)
    wp_enqueue_script(
        'wcusage-floating-widget-button',
        WCUSAGE_UNIQUE_PLUGIN_URL . 'inc/widget/js/floating-widget-button.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Only get essential settings for initial load
    $essential_settings = wcusage_get_floating_widget_essential_settings();
    
    // Localize script with minimal necessary data
    wp_localize_script('wcusage-floating-widget-button', 'wcusage_floating_widget', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wcusage_floating_widget'),
        'shorturl_nonce' => wp_create_nonce('wcusage_shorturl_ajax_nonce'),
        'current_page_url' => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'url_prefix' => wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon'),
        'essential_settings' => $essential_settings,
        'loading_text' => __('Loading...', 'woo-coupon-usage'),
        'error_text' => __('Error loading content. Please try again.', 'woo-coupon-usage'),
        'copy_success_text' => __('Copied to clipboard!', 'woo-coupon-usage'),
        'copy_fail_text' => __('Failed to copy', 'woo-coupon-usage'),
        'plugin_url' => WCUSAGE_UNIQUE_PLUGIN_URL,
        'qr_enabled' => wcusage_get_setting_value('wcusage_field_show_qrcodes', '0'),
        'social_enabled' => wcusage_get_setting_value('wcusage_field_show_social', 1),
        'is_premium' => wcu_fs()->can_use_premium_code__premium_only() ? '1' : '0'
    ));
    
    // Add inline styles for dynamic positioning and sizing
    add_action('wp_footer', 'wcusage_floating_widget_output');
}

// Output the floating widget HTML (optimized)
function wcusage_floating_widget_output() {
    $enable_widget = wcusage_get_setting_value('wcusage_field_floating_widget_enable', '0');
    
    if (!$enable_widget) {
        return;
    }
    
    // Perform full display condition check here (after page load)
    if (!wcusage_should_show_floating_widget()) {
        return;
    }
    
    $essential_settings = wcusage_get_floating_widget_essential_settings();
    
    // Determine button text efficiently
    $user_id = get_current_user_id();
    $is_affiliate = false;
    $button_text = wcusage_get_setting_value('wcusage_field_floating_widget_text_non_affiliate', 'Refer and Earn');
    
    if ($user_id && function_exists('wcusage_get_users_coupons_ids')) {
        // Use a quick check first, detailed check happens in AJAX
        $user_coupons = wcusage_get_users_coupons_ids($user_id);
        if (!empty($user_coupons)) {
            $is_affiliate = true;
            $button_text = wcusage_get_setting_value('wcusage_field_floating_widget_text_affiliate', 'Affiliates');
        }
    }
    
    // Get button icon
    $icon = wcusage_get_setting_value('wcusage_field_floating_widget_icon', '🎁');
    
    // Position classes
    $position_class = 'wcusage-position-' . $essential_settings['position'];
    
    // Size classes
    $size_class = 'wcusage-size-' . $essential_settings['size'];
    
    // Set CSS custom properties for dynamic colors
    $color_vars = '--wcusage-button-bg-color: ' . esc_attr($essential_settings['button_colors']['bg']) . '; ';
    $color_vars .= '--wcusage-button-text-color: ' . esc_attr($essential_settings['button_colors']['text']) . '; ';
    $color_vars .= '--wcusage-button-hover-color: ' . esc_attr($essential_settings['button_colors']['hover']) . '; ';
    $color_vars .= '--wcusage-button-border-color: ' . esc_attr($essential_settings['button_colors']['border']) . '; ';
    $color_vars .= '--wcusage-widget-theme-color: ' . esc_attr($essential_settings['theme_color']) . ';';
    
    // Device-specific classes
    $device_classes = '';
    if ($essential_settings['display']['hide_mobile']) {
        $device_classes .= ' wcusage-hide-mobile';
    }
    if ($essential_settings['display']['hide_tablet']) {
        $device_classes .= ' wcusage-hide-tablet';
    }
    
    ?>
    <div class="wcusage-floating-widget <?php echo esc_attr($position_class . ' ' . $size_class . $device_classes); ?>" style="<?php echo esc_attr($color_vars); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
        <button class="wcusage-floating-button" id="wcusage-floating-btn">
            <span class="wcusage-floating-icon"><?php echo esc_html($icon); ?></span>
            <?php echo esc_html($button_text); ?>
        </button>
        
        <div class="wcusage-floating-popup" id="wcusage-floating-popup">
            <!-- Popup content will be dynamically loaded here -->
        </div>
    </div>
    <?php
}
