<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Floating Affiliate Widget - Helper Functions
 * Utility functions and content generators
 */

// Generate coupon description
function wcusage_generate_coupon_description($coupon_id) {
    // Get discount information
    $discount_type = get_post_meta($coupon_id, 'discount_type', true);
    $amount = get_post_meta($coupon_id, 'coupon_amount', true);
    $free_shipping = get_post_meta($coupon_id, 'free_shipping', true);
    
    // Format discount text
    if ($amount) {
        if ($discount_type == "percent" || $discount_type == "recurring_percent" || $discount_type == "percent_per_product") {
            $discount_text = $amount . '% ' . esc_html__('discount', 'woo-coupon-usage');
        } elseif ($discount_type == "fixed_cart") {
            $discount_text = wcusage_format_price($amount) . ' ' . esc_html__('discount', 'woo-coupon-usage');
        } else {
            $discount_text = wcusage_format_price($amount) . ' ' . esc_html__('discount', 'woo-coupon-usage');
        }
    } elseif ($free_shipping == "yes") {
        $discount_text = esc_html__('free shipping', 'woo-coupon-usage');
    } else {
        $discount_text = esc_html__('a discount', 'woo-coupon-usage');
    }
    
    // Get commission message
    $commission_message = '';
    if (function_exists('wcusage_commission_message')) {
        $commission_message = wcusage_commission_message($coupon_id);
    } else {
        // Fallback to percentage if function doesn't exist
        $commission_type = wcusage_get_setting_value('wcusage_field_commission_type', 'percentage');
        $commission_rate = wcusage_get_setting_value('wcusage_field_commission', '');
        
        if ($commission_type == 'percentage') {
            $commission_message = $commission_rate . '%';
        } else {
            $commission_message = wcusage_format_price($commission_rate);
        }
    }
    
    return sprintf(
        esc_html__('Customers get %s and you earn %s!', 'woo-coupon-usage'),
        '<strong>' . esc_html($discount_text) . '</strong>',
        '<strong>' . wp_kses_post($commission_message) . '</strong>'
    );
}

// Generate registration form content
function wcusage_widget_registration_form($settings) {
    ob_start();
    ?>
    <div class="wcusage-widget-registration-form">
        <!-- Benefits Message -->
        <div class="wcusage-widget-benefits-message">
            <div class="wcusage-widget-benefits-header">
                <h3><?php echo esc_html($settings['benefits_title']); ?></h3>
                <p><?php echo esc_html($settings['benefits_subtitle']); ?></p>
            </div>
            <div class="wcusage-widget-benefits-list">
                <?php if (!empty($settings['benefit_1'])): ?>
                <div class="wcusage-widget-benefit-item">
                    <span class="wcusage-widget-benefit-icon"><i class="<?php echo esc_attr($settings['benefit_1_icon']); ?>"></i></span>
                    <span><?php echo esc_html($settings['benefit_1']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['benefit_2'])): ?>
                <div class="wcusage-widget-benefit-item">
                    <span class="wcusage-widget-benefit-icon"><i class="<?php echo esc_attr($settings['benefit_2_icon']); ?>"></i></span>
                    <span><?php echo esc_html($settings['benefit_2']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['benefit_3'])): ?>
                <div class="wcusage-widget-benefit-item">
                    <span class="wcusage-widget-benefit-icon"><i class="<?php echo esc_attr($settings['benefit_3_icon']); ?>"></i></span>
                    <span><?php echo esc_html($settings['benefit_3']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['benefit_4'])): ?>
                <div class="wcusage-widget-benefit-item">
                    <span class="wcusage-widget-benefit-icon"><i class="<?php echo esc_attr($settings['benefit_4_icon']); ?>"></i></span>
                    <span><?php echo esc_html($settings['benefit_4']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php echo do_shortcode('[couponaffiliates-register type="widget"]'); ?>
    </div>
    <?php
    return ob_get_clean();
}