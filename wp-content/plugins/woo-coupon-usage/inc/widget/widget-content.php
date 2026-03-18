<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Generate affiliate dashboard content
function wcusage_generate_affiliate_dashboard(  $user_coupons, $settings  ) {
    ob_start();
    // Get first coupon info for the coupon code display
    $first_coupon_id = $user_coupons[0];
    $first_coupon_info = wcusage_get_coupon_info_by_id( $first_coupon_id );
    $first_coupon_code = ( isset( $first_coupon_info[3] ) ? $first_coupon_info[3] : '' );
    $discount_text = '';
    $commission_message = '';
    if ( $first_coupon_id ) {
        // Check if stats refresh is needed for any of the user's coupons
        $needs_refresh = false;
        $dashboard_url = '';
        foreach ( $user_coupons as $coupon_id ) {
            $force_refresh_stats = wcusage_check_if_refresh_needed( $coupon_id );
            if ( $force_refresh_stats ) {
                $needs_refresh = true;
                $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
                if ( isset( $coupon_info[4] ) ) {
                    $dashboard_url = $coupon_info[4];
                }
                break;
            }
        }
        // If refresh is needed, show message instead of normal dashboard
        if ( $needs_refresh ) {
            ?>
            <div class="wcusage-widget-refresh-notice">
                <div class="wcusage-widget-notice-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="wcusage-widget-notice-content">
                    <h3><?php 
            echo esc_html__( 'Statistics Update Required', 'woo-coupon-usage' );
            ?></h3>
                    <p><?php 
            echo esc_html__( 'Your affiliate statistics need to be refreshed and recalculated. Please visit your full affiliate dashboard to complete this process.', 'woo-coupon-usage' );
            ?></p>
                    <?php 
            if ( $dashboard_url ) {
                ?>
                        <a href="<?php 
                echo esc_url( $dashboard_url );
                ?>" class="wcusage-widget-dashboard-button" target="_blank">
                            <?php 
                echo esc_html__( 'Visit Full Dashboard', 'woo-coupon-usage' );
                ?> <i class="fas fa-external-link-alt"></i>
                        </a>
                    <?php 
            }
            ?>
                </div>
            </div>
            <?php 
            return ob_get_clean();
        }
        // Get discount information
        $discount_type = get_post_meta( $first_coupon_id, 'discount_type', true );
        $amount = get_post_meta( $first_coupon_id, 'coupon_amount', true );
        $free_shipping = get_post_meta( $first_coupon_id, 'free_shipping', true );
        // Format discount text
        if ( $amount ) {
            if ( $discount_type == "percent" || $discount_type == "recurring_percent" || $discount_type == "percent_per_product" ) {
                $discount_text = $amount . '% ' . esc_html__( 'discount', 'woo-coupon-usage' );
            } elseif ( $discount_type == "fixed_cart" ) {
                $discount_text = wcusage_format_price( $amount ) . ' ' . esc_html__( 'discount', 'woo-coupon-usage' );
            } else {
                $discount_text = wcusage_format_price( $amount ) . ' ' . esc_html__( 'discount', 'woo-coupon-usage' );
            }
            if ( $free_shipping == "yes" ) {
                $discount_text .= ' + ' . esc_html__( 'free shipping', 'woo-coupon-usage' );
            }
        } elseif ( $free_shipping == "yes" ) {
            $discount_text = esc_html__( 'free shipping discount', 'woo-coupon-usage' );
        } else {
            $discount_text = esc_html__( 'discount', 'woo-coupon-usage' );
        }
        // Get commission message
        if ( function_exists( 'wcusage_commission_message' ) ) {
            $commission_message = wcusage_commission_message( $first_coupon_id );
        } else {
            // Fallback to percentage if function doesn't exist
            $commission_type = wcusage_get_setting_value( 'wcusage_field_commission_type', 'percentage' );
            $commission_rate = wcusage_get_setting_value( 'wcusage_field_commission', '' );
            if ( $commission_type == 'percentage' ) {
                $commission_message = $commission_rate . '%';
            } else {
                $commission_message = wcusage_format_price( $commission_rate );
            }
        }
    }
    ?>
    
    <!-- Coupon selector if multiple coupons - Now at top level -->
    <?php 
    if ( count( $user_coupons ) > 1 ) {
        ?>
        <div class="wcusage-widget-coupon-select-global">
            <label for="wcusage-widget-coupon-select-main"><?php 
        echo esc_html__( 'Select Coupon:', 'woo-coupon-usage' );
        ?></label>
            <select id="wcusage-widget-coupon-select-main">
                <?php 
        foreach ( $user_coupons as $coupon_id ) {
            $coupon = get_post( $coupon_id );
            if ( $coupon ) {
                // Get the actual coupon code
                $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
                $coupon_code = ( isset( $coupon_info[3] ) ? $coupon_info[3] : '' );
                ?>
                <option value="<?php 
                echo esc_attr( $coupon_id );
                ?>" data-coupon-code="<?php 
                echo esc_attr( $coupon_code );
                ?>" <?php 
                selected( $coupon_id, $user_coupons[0] );
                ?>>
                    <?php 
                echo esc_html( $coupon->post_title );
                ?>
                </option>
                <?php 
            }
        }
        ?>
            </select>
        </div>
        <?php 
    } else {
        // For single coupon, add hidden data attribute
        ?>
        <div id="wcusage-single-coupon-data" data-coupon-id="<?php 
        echo esc_attr( $user_coupons[0] );
        ?>" data-coupon-code="<?php 
        echo esc_attr( $first_coupon_code );
        ?>" style="display: none;"></div>
        <?php 
    }
    ?>
    
    <!-- Create tabs structure -->
    <div class="wcusage-widget-tabs">
        <?php 
    if ( $settings['show_refer_tab'] ) {
        ?>
        <button class="wcusage-widget-tab active" data-tab="tab-links">
            <i class="fas fa-link"></i> <?php 
        echo esc_html__( 'Refer', 'woo-coupon-usage' );
        ?>
        </button>
        <?php 
    }
    ?>
        
        <?php 
    if ( $settings['show_stats_tab'] ) {
        ?>
        <button class="wcusage-widget-tab<?php 
        echo ( !$settings['show_refer_tab'] ? ' active' : '' );
        ?>" data-tab="tab-statistics">
            <i class="fas fa-chart-line"></i> <?php 
        echo esc_html__( 'Stats', 'woo-coupon-usage' );
        ?>
        </button>
        <?php 
    }
    ?>
        
        <?php 
    if ( $settings['show_orders_tab'] ) {
        ?>
        <button class="wcusage-widget-tab<?php 
        echo ( !$settings['show_refer_tab'] && !$settings['show_stats_tab'] ? ' active' : '' );
        ?>" data-tab="tab-referrals">
            <i class="fas fa-shopping-cart"></i> <?php 
        echo esc_html__( 'Orders', 'woo-coupon-usage' );
        ?>
        </button>
        <?php 
    }
    ?>
        
        <?php 
    // Show Payouts tab if payouts are enabled and user has coupons and setting is enabled
    $wcusage_field_payouts_enable = wcusage_get_setting_value( 'wcusage_field_payouts_enable', '1' );
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_payouts_enable && $settings['show_payouts_tab'] ) {
        $no_other_tabs = !$settings['show_refer_tab'] && !$settings['show_stats_tab'] && !$settings['show_orders_tab'];
        ?>
        <button class="wcusage-widget-tab<?php 
        echo ( $no_other_tabs ? ' active' : '' );
        ?>" data-tab="tab-payouts">
            <i class="fas fa-money-bill-wave"></i> <?php 
        echo esc_html__( 'Payouts', 'woo-coupon-usage' );
        ?>
        </button>
        <?php 
    }
    ?>
        
        <?php 
    // Show Creatives tab if creatives are enabled and setting is enabled
    $wcusage_field_creatives_enable = wcusage_get_setting_value( 'wcusage_field_creatives_enable', '1' );
    $total_creatives = wp_count_posts( 'wcu-creatives' );
    $published_creatives = ( isset( $total_creatives->publish ) ? $total_creatives->publish : 0 );
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_creatives_enable && $published_creatives > 0 && $settings['show_creatives_tab'] ) {
        $no_other_tabs_creatives = !$settings['show_refer_tab'] && !$settings['show_stats_tab'] && !$settings['show_orders_tab'] && (!$wcusage_field_payouts_enable || !$settings['show_payouts_tab']);
        ?>
        <button class="wcusage-widget-tab<?php 
        echo ( $no_other_tabs_creatives ? ' active' : '' );
        ?>" data-tab="tab-creatives">
            <i class="fas fa-palette"></i> <?php 
        echo esc_html__( 'Creatives', 'woo-coupon-usage' );
        ?>
        </button>
        <?php 
    }
    ?>
    </div>
    
    <!-- Tab Content Container -->
    <div class="wcusage-widget-tab-container">
        <!-- Links/Refer Tab -->
        <?php 
    if ( $settings['show_refer_tab'] ) {
        ?>
        <div class="wcusage-widget-tab-content active" id="tab-links">
            <?php 
        if ( !empty( $settings['refer_tab_text'] ) ) {
            ?>
            <div class="wcusage-widget-tab-custom-text">
                <p><?php 
            echo esc_html( $settings['refer_tab_text'] );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
            <!-- Coupon Code Display - Moved to Links tab -->
            <div class="wcusage-widget-section">
                <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Your Coupon', 'woo-coupon-usage' );
        ?></h4>
                <div class="wcusage-widget-coupon-display">
                    <div class="wcusage-widget-coupon-code-container">
                        <div class="wcusage-widget-coupon-code" id="wcusage-widget-main-coupon-code"><?php 
        echo esc_html( $first_coupon_code );
        ?></div>
                        <button class="wcusage-widget-coupon-copy-btn" data-coupon-code="<?php 
        echo esc_attr( $first_coupon_code );
        ?>">
                            <i class="fas fa-copy wcusage-copy-icon"></i>
                        </button>
                    </div>
                    <div class="wcusage-widget-coupon-description">
                        <?php 
        echo sprintf( wp_kses_post( __( 'Customers get a %s and you earn %s!', 'woo-coupon-usage' ) ), '<strong>' . wp_kses_post( $discount_text ) . '</strong>', '<strong>' . wp_kses_post( $commission_message ) . '</strong>' );
        ?>
                    </div>
                </div>
            </div>
            
            <!-- Referral Links Content -->
            <div id="wcusage-links-content">
                <?php 
        echo wcusage_get_floating_widget_links( $first_coupon_id );
        ?>
            </div>
        </div>
        <?php 
    }
    ?>
        
        <!-- Statistics Tab -->
        <?php 
    if ( $settings['show_stats_tab'] ) {
        ?>
        <div class="wcusage-widget-tab-content<?php 
        echo ( !$settings['show_refer_tab'] ? ' active' : '' );
        ?>" id="tab-statistics">
            <?php 
        if ( !empty( $settings['stats_tab_text'] ) ) {
            ?>
            <div class="wcusage-widget-tab-custom-text">
                <p><?php 
            echo esc_html( $settings['stats_tab_text'] );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
            <div class="wcusage-widget-stats">
                <?php 
        echo wp_kses_post( wcusage_get_floating_widget_stats( $first_coupon_id ) );
        ?>
            </div>
        </div>
        <?php 
    }
    ?>
        
        <!-- Referrals Tab -->
        <?php 
    if ( $settings['show_orders_tab'] ) {
        ?>
        <div class="wcusage-widget-tab-content<?php 
        echo ( !$settings['show_refer_tab'] && !$settings['show_stats_tab'] ? ' active' : '' );
        ?>" id="tab-referrals">
            <?php 
        if ( !empty( $settings['orders_tab_text'] ) ) {
            ?>
            <div class="wcusage-widget-tab-custom-text">
                <p><?php 
            echo esc_html( $settings['orders_tab_text'] );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
            <?php 
        echo wp_kses_post( wcusage_get_floating_widget_referrals( $first_coupon_id ) );
        ?>
        </div>
        <?php 
    }
    ?>
        
        <?php 
    // Show Payouts tab content if payouts are enabled and setting is enabled
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_payouts_enable && $settings['show_payouts_tab'] ) {
        $no_other_tabs = !$settings['show_refer_tab'] && !$settings['show_stats_tab'] && !$settings['show_orders_tab'];
        ?>
        <!-- Payouts Tab -->
        <div class="wcusage-widget-tab-content<?php 
        echo ( $no_other_tabs ? ' active' : '' );
        ?>" id="tab-payouts">
            <?php 
        if ( !empty( $settings['payouts_tab_text'] ) ) {
            ?>
            <div class="wcusage-widget-tab-custom-text">
                <p><?php 
            echo esc_html( $settings['payouts_tab_text'] );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
            <?php 
        echo wp_kses_post( wcusage_get_floating_widget_payouts( $first_coupon_id ) );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
        </div>
        <?php 
    }
    ?>
        
        <?php 
    // Show Creatives tab content if creatives are enabled and setting is enabled
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_creatives_enable && $published_creatives > 0 && $settings['show_creatives_tab'] ) {
        $no_other_tabs_creatives = !$settings['show_refer_tab'] && !$settings['show_stats_tab'] && !$settings['show_orders_tab'] && (!$wcusage_field_payouts_enable || !$settings['show_payouts_tab']);
        ?>
        <!-- Creatives Tab -->
        <div class="wcusage-widget-tab-content<?php 
        echo ( $no_other_tabs_creatives ? ' active' : '' );
        ?>" id="tab-creatives">
            <?php 
        if ( !empty( $settings['creatives_tab_text'] ) ) {
            ?>
            <div class="wcusage-widget-tab-custom-text">
                <p><?php 
            echo esc_html( $settings['creatives_tab_text'] );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
            <?php 
        echo wp_kses_post( wcusage_get_floating_widget_creatives( $first_coupon_id ) );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
        </div>
        <?php 
    }
    ?>
    </div>

    <!-- Shared Actions Container -->
    <?php 
    if ( $settings['show_dashboard_button'] ) {
        ?>
    <?php 
        // Get dashboard URL for the shared actions
        if ( function_exists( 'wcusage_get_coupon_shortcode_page' ) ) {
            $dashboard_page = wcusage_get_coupon_shortcode_page( '' );
        } else {
            $dashboard_page = home_url();
        }
        if ( function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            $coupon_info = wcusage_get_coupon_info_by_id( $first_coupon_id );
            if ( !$coupon_info || empty( $coupon_info[4] ) ) {
                $dashboard_url = $dashboard_page;
            } else {
                $dashboard_url = $coupon_info[4];
            }
        } else {
            $dashboard_url = $dashboard_page;
        }
        ?>
    <div class="wcusage-widget-actions">
        <a href="<?php 
        echo esc_url( $dashboard_url );
        ?>" class="wcusage-widget-btn wcusage-widget-btn-primary" id="wcusage-main-dashboard-link">
            <?php 
        echo esc_html__( 'View Full Dashboard', 'woo-coupon-usage' );
        ?>
        </a>
    </div>
    <?php 
    }
    ?>

    <?php 
    return ob_get_clean();
}

// Generate coupon statistics HTML
function wcusage_get_floating_widget_stats(  $coupon_id  ) {
    try {
        // Check if required functions exist
        if ( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Required function not available.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
        if ( !$coupon_info || empty( $coupon_info[3] ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Invalid coupon data.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_code = $coupon_info[3];
        // Get stats from wcu_alltime_stats meta
        $wcu_alltime_stats = get_post_meta( $coupon_id, 'wcu_alltime_stats', true );
        if ( $wcu_alltime_stats && is_array( $wcu_alltime_stats ) ) {
            $total_orders_count = ( isset( $wcu_alltime_stats['total_count'] ) ? $wcu_alltime_stats['total_count'] : 0 );
            $total_sales = ( isset( $wcu_alltime_stats['total_orders'] ) ? $wcu_alltime_stats['total_orders'] : 0 );
            $total_discount = ( isset( $wcu_alltime_stats['full_discount'] ) ? $wcu_alltime_stats['full_discount'] : 0 );
            $total_commission = ( isset( $wcu_alltime_stats['total_commission'] ) ? $wcu_alltime_stats['total_commission'] : 0 );
            // Calculate net sales (total orders minus discounts)
            $total_sales = (float) $total_sales - (float) $total_discount;
        } else {
            // Fallback to coupon usage count if no alltime stats
            global $woocommerce;
            $c = new WC_Coupon($coupon_code);
            $total_orders_count = $c->get_usage_count();
            $total_sales = 0;
            $total_discount = 0;
            $total_commission = 0;
        }
        // Format currency
        if ( function_exists( 'wcusage_format_price' ) ) {
            $total_commission_formatted = wcusage_format_price( $total_commission );
            $total_sales_formatted = wcusage_format_price( $total_sales );
            $total_discount_formatted = wcusage_format_price( $total_discount );
        } else {
            $total_commission_formatted = number_format( $total_commission, 2 );
            $total_sales_formatted = number_format( $total_sales, 2 );
            $total_discount_formatted = number_format( $total_discount, 2 );
        }
        // Get URL stats using the existing function from admin reports
        $total_clicks = 0;
        $total_conversions = 0;
        $conversion_rate = 0;
        $wcusage_field_urls_enable = wcusage_get_setting_value( 'wcusage_field_urls_enable', 1 );
        if ( $wcusage_field_urls_enable ) {
            // Use the same method as admin reports to get accurate click counts
            global $wpdb;
            $table_name = $wpdb->prefix . 'wcusage_clicks';
            // Get total clicks for this coupon
            $click_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponid = %d", $coupon_id );
            $total_clicks = $wpdb->get_var( $click_query );
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            // Get conversions (clicks that resulted in orders)
            $conversion_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponid = %d AND converted = 1", $coupon_id );
            $total_conversions = $wpdb->get_var( $conversion_query );
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            // Calculate conversion rate
            if ( $total_clicks > 0 ) {
                $conversion_rate = round( $total_conversions / $total_clicks * 100, 1 );
            }
        }
        ob_start();
        ?>
        <!-- Referral Stats Section -->
        <div class="wcusage-widget-section">
            <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Referral Stats', 'woo-coupon-usage' );
        ?></h4>
            <div class="wcusage-widget-stats-grid">
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo esc_html( $total_orders_count );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Orders', 'woo-coupon-usage' );
        ?></span>
                </div>
                
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo wp_kses_post( $total_commission_formatted );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Commission', 'woo-coupon-usage' );
        ?></span>
                </div>
                
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo wp_kses_post( $total_sales_formatted );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Sales', 'woo-coupon-usage' );
        ?></span>
                </div>
                
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo wp_kses_post( $total_discount_formatted );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Discounts', 'woo-coupon-usage' );
        ?></span>
                </div>
            </div>
        </div>
        
        <!-- Link Stats Section -->
        <div class="wcusage-widget-section">
            <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Link Stats', 'woo-coupon-usage' );
        ?></h4>
            <div class="wcusage-widget-stats-grid wcusage-widget-link-stats-grid">
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo esc_html( $total_clicks );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Total Clicks', 'woo-coupon-usage' );
        ?></span>
                </div>
                
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo esc_html( $total_conversions );
        ?></span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Conversions', 'woo-coupon-usage' );
        ?></span>
                </div>
                
                <div class="wcusage-widget-stat-box">
                    <span class="wcusage-widget-stat-value"><?php 
        echo esc_html( $conversion_rate );
        ?>%</span>
                    <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Rate', 'woo-coupon-usage' );
        ?></span>
                </div>
            </div>
        </div>
        
        <?php 
        return ob_get_clean();
    } catch ( Exception $e ) {
        error_log( 'Floating widget stats generation error: ' . $e->getMessage() );
        return '<div class="wcusage-widget-error">' . esc_html__( 'Error loading statistics.', 'woo-coupon-usage' ) . '</div>';
    }
}

// Generate links tab content
function wcusage_get_floating_widget_links(  $coupon_id  ) {
    try {
        if ( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Required function not available.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
        if ( !$coupon_info || empty( $coupon_info[3] ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Invalid coupon data.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_code = $coupon_info[3];
        ob_start();
        ?>
        <!-- Referral Links Section -->
        <div class="wcusage-widget-section">
            <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Referral Links', 'woo-coupon-usage' );
        ?></h4>
            
            <div class="wcusage-widget-url-form">
                <label for="wcusage-custom-page-url"><?php 
        echo esc_html__( 'Page URL:', 'woo-coupon-usage' );
        ?></label>
                <input type="url" id="wcusage-custom-page-url" class="wcusage-custom-page-url" value="" placeholder="<?php 
        echo esc_attr__( 'Enter page URL...', 'woo-coupon-usage' );
        ?>">
            </div>
            
            <div class="wcusage-widget-url-display">
                <span id="wcusage-generated-url"></span>
            </div>
            
            <div class="wcusage-widget-copy-actions">
                <button class="wcusage-widget-btn wcusage-widget-btn-secondary wcusage-copy-referral-url" data-url="">
                    <?php 
        echo esc_html__( 'Copy Link', 'woo-coupon-usage' );
        ?> &nbsp; <i class="fas fa-copy"></i>
                </button>
                <?php 
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            // Add short URL button if short URL addon is enabled
            $wcusage_field_show_shortlink = wcusage_get_setting_value( 'wcusage_field_show_shortlink', '0' );
            if ( $wcusage_field_show_shortlink ) {
                ?>
                <button class="wcusage-widget-btn wcusage-widget-btn-secondary wcusage-generate-short-url" data-url="">
                    <span class="wcusage-short-url-text"><?php 
                echo esc_html__( 'Short URL', 'woo-coupon-usage' );
                ?></span>
                    &nbsp;
                    <i class="fas fa-link wcusage-short-url-icon"></i>
                    <i class="fas fa-sync fa-spin wcusage-short-url-spinner" style="display: none;"></i>
                </button>
                <?php 
            }
        }
        ?>
            </div>
            
            <?php 
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            // Add social sharing under the Copy Link button
            $site_url = home_url();
            do_action(
                'wcusage_hook_widget_social_sharing',
                $site_url,
                $coupon_code,
                'widget'
            );
            // Show QR code if enabled
            $wcusage_field_qrcode_enable = wcusage_get_setting_value( 'wcusage_field_show_qrcodes', '0' );
            if ( $wcusage_field_qrcode_enable ) {
                ?>
                    <div class="wcusage-widget-qr-section" style="margin-top: 15px;">
                        <div style="margin-bottom: 10px;">
                            <strong><?php 
                echo esc_html__( 'QR Code:', 'woo-coupon-usage' );
                ?></strong>
                            <button id="wcusage-widget-qr-show" class="wcusage-widget-btn wcusage-widget-btn-secondary" style="margin-left: 10px; padding: 4px 8px; font-size: 11px;">
                                <?php 
                echo esc_html__( 'Generate', 'woo-coupon-usage' );
                ?> <i class="fas fa-qrcode"></i>
                            </button>
                            <button id="wcusage-widget-qr-hide" class="wcusage-widget-btn wcusage-widget-btn-secondary" style="margin-left: 10px; padding: 4px 8px; font-size: 11px; display: none;">
                                <?php 
                echo esc_html__( 'Hide', 'woo-coupon-usage' );
                ?>
                            </button>
                        </div>
                        <div class="wcusage-widget-qr-display" style="display: none; text-align: center;">
                            <div id="wcusage-widget-qr-code" style="margin: 10px 0;"></div>
                            <div style="margin: 10px 0;">
                                <input type="color" id="wcusage-widget-qr-color" value="#000000" title="<?php 
                echo esc_attr__( 'QR Code Color', 'woo-coupon-usage' );
                ?>" style="margin-right: 10px;">
                                <button id="wcusage-widget-qr-download" class="wcusage-widget-btn wcusage-widget-btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                                    <?php 
                echo esc_html__( 'Download', 'woo-coupon-usage' );
                ?> <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php 
            }
        }
        ?>
        </div>
        
        <?php 
        return ob_get_clean();
    } catch ( Exception $e ) {
        error_log( 'Floating widget links generation error: ' . $e->getMessage() );
        return '<div class="wcusage-widget-error">' . esc_html__( 'Error loading links.', 'woo-coupon-usage' ) . '</div>';
    }
}

// Generate referrals tab content
function wcusage_get_floating_widget_referrals(  $coupon_id  ) {
    try {
        if ( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Required function not available.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
        if ( !$coupon_info || empty( $coupon_info[3] ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Invalid coupon data.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_code = $coupon_info[3];
        // Get recent orders using the same method as wcusage_tab_latest_orders
        $orders = array();
        if ( function_exists( 'wcusage_wh_getOrderbyCouponCode' ) ) {
            // Use the same function as the main dashboard
            $wcu_orders_start = '';
            $wcu_orders_end = date( 'Y-m-d H:i:s', strtotime( 'now' ) );
            $all_orders = wcusage_wh_getOrderbyCouponCode(
                $coupon_code,
                $wcu_orders_start,
                $wcu_orders_end,
                10,
                1
            );
            $orders = $all_orders['orders'];
        }
        ob_start();
        ?>
        
        <!-- Orders Section Title -->
        <div class="wcusage-widget-section">
            <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Recent Referred Orders', 'woo-coupon-usage' );
        ?></h4>
            
            <?php 
        if ( !empty( $orders ) ) {
            ?>
            <div class="wcusage-widget-referrals-list">
                <?php 
            foreach ( $orders as $order_data ) {
                if ( !$order_data || !is_object( $order_data ) ) {
                    continue;
                }
                $order_id = $order_data->order_id;
                $order_date = date_i18n( get_option( 'date_format' ), strtotime( $order_data->order_date ) );
                // Get the actual WooCommerce order object to access status
                $wc_order = wc_get_order( $order_id );
                if ( !$wc_order ) {
                    continue;
                }
                $order_status = $wc_order->get_status();
                // Calculate commission using the same method as the main dashboard
                $commission = 0;
                if ( function_exists( 'wcusage_calculate_order_data' ) ) {
                    $calculateorder = wcusage_calculate_order_data(
                        $order_id,
                        $coupon_code,
                        0,
                        1
                    );
                    // Defensive defaults to avoid undefined index notices
                    if ( !is_array( $calculateorder ) ) {
                        $calculateorder = array();
                    }
                    $total = ( isset( $calculateorder['ordertotal'] ) ? (float) $calculateorder['ordertotal'] : 0 );
                    $discount_amount = ( isset( $calculateorder['orderdiscount'] ) ? (float) $calculateorder['orderdiscount'] : 0 );
                    $commission = ( isset( $calculateorder['totalcommission'] ) ? (float) $calculateorder['totalcommission'] : 0 );
                    $total_formatted = wcusage_format_price( $total );
                    $discount_formatted = wcusage_format_price( $discount_amount );
                }
                $commission_formatted = wcusage_format_price( $commission );
                ?>
                <div class="wcusage-widget-referral-item">
                    <div class="wcusage-widget-referral-header">
                        <span class="wcusage-widget-order-id"></span>
                        <span class="wcusage-widget-order-date"><?php 
                echo esc_html( $order_date );
                ?></span>
                    </div>
                    <div class="wcusage-widget-referral-details">
                        <div class="wcusage-widget-referral-stat">
                            <span><?php 
                echo esc_html__( 'Total:', 'woo-coupon-usage' );
                ?></span>
                            <span><?php 
                echo wp_kses_post( $total_formatted );
                ?></span>
                        </div>
                        <?php 
                if ( $discount_amount > 0 ) {
                    ?>
                        <div class="wcusage-widget-referral-stat">
                            <span><?php 
                    echo esc_html__( 'Discount:', 'woo-coupon-usage' );
                    ?></span>
                            <span><?php 
                    echo wp_kses_post( $discount_formatted );
                    ?></span>
                        </div>
                        <?php 
                }
                ?>
                        <div class="wcusage-widget-referral-stat">
                            <span><?php 
                echo esc_html__( 'Commission:', 'woo-coupon-usage' );
                ?></span>
                            <span><?php 
                echo wp_kses_post( $commission_formatted );
                ?></span>
                        </div>
                        <div class="wcusage-widget-referral-stat">
                            <span><?php 
                echo esc_html__( 'Status:', 'woo-coupon-usage' );
                ?></span>
                            <span class="wcusage-widget-order-status status-<?php 
                echo esc_attr( $order_status );
                ?>">
                                <?php 
                echo esc_html( ucfirst( $order_status ) );
                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php 
            }
            ?>
            </div>
            <?php 
        } else {
            ?>
            <div class="wcusage-widget-no-referrals">
                <p><?php 
            echo esc_html__( 'No recent referral orders found.', 'woo-coupon-usage' );
            ?></p>
            </div>
            <?php 
        }
        ?>
            
        </div>
        
        <?php 
        return ob_get_clean();
    } catch ( Exception $e ) {
        error_log( 'Floating widget referrals generation error: ' . $e->getMessage() );
        return '<div class="wcusage-widget-error">' . esc_html__( 'Error loading referrals.', 'woo-coupon-usage' ) . '</div>';
    }
}

// Generate payouts tab content
function wcusage_get_floating_widget_payouts(  $coupon_id  ) {
    try {
        if ( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Required function not available.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
        if ( !$coupon_info || empty( $coupon_info[3] ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Invalid coupon data.', 'woo-coupon-usage' ) . '</div>';
        }
        $unpaid_commission = $coupon_info[2];
        $coupon_user_id = $coupon_info[1];
        $currentuserid = get_current_user_id();
        // Check access
        if ( $coupon_user_id != $currentuserid && !wcusage_check_admin_access() ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Access denied.', 'woo-coupon-usage' ) . '</div>';
        }
        // Check if payouts are enabled
        $wcusage_field_payouts_enable = wcusage_get_setting_value( 'wcusage_field_payouts_enable', '1' );
        if ( !$wcusage_field_payouts_enable ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Payouts are not enabled.', 'woo-coupon-usage' ) . '</div>';
        }
        if ( !$unpaid_commission ) {
            $unpaid_commission = 0;
        }
        // Get settings for show_payout_button
        $settings = wcusage_get_floating_widget_settings();
        // Get dashboard URL
        if ( function_exists( 'wcusage_get_coupon_shortcode_page' ) ) {
            $dashboard_page = wcusage_get_coupon_shortcode_page( '' );
        } else {
            $dashboard_page = home_url();
        }
        if ( function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            $coupon_info_for_url = wcusage_get_coupon_info_by_id( $coupon_id );
            if ( !$coupon_info_for_url || empty( $coupon_info_for_url[4] ) ) {
                $dashboard_url = $dashboard_page;
            } else {
                $dashboard_url = $coupon_info_for_url[4];
            }
        } else {
            $dashboard_url = $dashboard_page;
        }
        // Get payouts data
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcusage_payouts';
        $pending_payouts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE couponid = %d AND status = 'pending' ORDER BY id DESC LIMIT 5", $coupon_id ) );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $completed_payouts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table_name . " WHERE couponid = %d AND status = 'paid' ORDER BY id DESC LIMIT 5", $coupon_id ) );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        ob_start();
        ?>

        <h4 class="wcusage-widget-section-title"><?php 
        echo esc_html__( 'Commission Payouts', 'woo-coupon-usage' );
        ?></h4>
        
        <!-- Unpaid Commission -->
        <div class="wcusage-widget-stat-box" style="margin-bottom: 15px;">
            <span class="wcusage-widget-stat-value"><?php 
        echo wp_kses_post( wcusage_format_price( number_format(
            (float) $unpaid_commission,
            2,
            '.',
            ''
        ) ) );
        ?></span>
            <span class="wcusage-widget-stat-label"><?php 
        echo esc_html__( 'Unpaid Commission', 'woo-coupon-usage' );
        ?></span>
        </div>
        
        <!-- Request Payout Link -->
        <?php 
        if ( $settings['show_payout_button'] ) {
            ?>
        <div style="margin-bottom: 20px;">
            <a href="<?php 
            echo esc_url( $dashboard_url . '&tab=payouts' );
            ?>" class="wcusage-widget-btn wcusage-widget-btn-secondary" style="width: 100%; display: block; text-align: center; padding: 10px;">
                <?php 
            echo esc_html__( 'Request Payout', 'woo-coupon-usage' );
            ?> <i class="fas fa-money-bill-wave"></i>
            </a>
        </div>
        <?php 
        }
        ?>
        
        <!-- Pending Payments -->
        <?php 
        if ( !empty( $pending_payouts ) ) {
            ?>
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; margin-bottom: 10px; color: #495057;"><?php 
            echo esc_html__( 'Pending Payments', 'woo-coupon-usage' );
            ?></h4>
            <div class="wcusage-widget-simple-table">
                <?php 
            foreach ( $pending_payouts as $payout ) {
                ?>
                <div class="wcusage-widget-table-row">
                    <span class="wcusage-widget-table-amount"><?php 
                echo wp_kses_post( wcusage_format_price( $payout->amount ) );
                ?></span>
                    <span class="wcusage-widget-table-date"><?php 
                echo esc_html( date_i18n( 'M j, Y', strtotime( $payout->date ) ) );
                ?></span>
                    <span class="wcusage-widget-table-status pending"><?php 
                echo esc_html__( 'Pending', 'woo-coupon-usage' );
                ?></span>
                </div>
                <?php 
            }
            ?>
            </div>
        </div>
        <?php 
        }
        ?>
        
        <!-- Completed Payments -->
        <?php 
        if ( !empty( $completed_payouts ) ) {
            ?>
        <div style="margin-bottom: 15px;">
            <h4 style="font-size: 14px; margin-bottom: 10px; color: #495057;"><?php 
            echo esc_html__( 'Recent Payments', 'woo-coupon-usage' );
            ?></h4>
            <div class="wcusage-widget-simple-table">
                <?php 
            foreach ( $completed_payouts as $payout ) {
                ?>
                <div class="wcusage-widget-table-row">
                    <span class="wcusage-widget-table-amount"><?php 
                echo wp_kses_post( wcusage_format_price( $payout->amount ) );
                ?></span>
                    <span class="wcusage-widget-table-date"><?php 
                echo esc_html( date_i18n( 'M j, Y', strtotime( $payout->datepaid ) ) );
                ?></span>
                    <span class="wcusage-widget-table-status paid"><?php 
                echo esc_html__( 'Paid', 'woo-coupon-usage' );
                ?></span>
                </div>
                <?php 
            }
            ?>
            </div>
        </div>
        <?php 
        }
        ?>
        
        <?php 
        return ob_get_clean();
    } catch ( Exception $e ) {
        error_log( 'Floating widget payouts generation error: ' . $e->getMessage() );
        return '<div class="wcusage-widget-error">' . esc_html__( 'Error loading payouts.', 'woo-coupon-usage' ) . '</div>';
    }
}

// Generate creatives tab content
function wcusage_get_floating_widget_creatives(  $coupon_id  ) {
    try {
        if ( !function_exists( 'wcusage_get_coupon_info_by_id' ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Required function not available.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
        if ( !$coupon_info || empty( $coupon_info[3] ) ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Invalid coupon data.', 'woo-coupon-usage' ) . '</div>';
        }
        $coupon_code = $coupon_info[3];
        $user_id = $coupon_info[1];
        // Check if creatives are enabled
        $wcusage_field_creatives_enable = wcusage_get_setting_value( 'wcusage_field_creatives_enable', '1' );
        if ( !$wcusage_field_creatives_enable ) {
            return '<div class="wcusage-widget-error">' . esc_html__( 'Creatives are not enabled.', 'woo-coupon-usage' ) . '</div>';
        }
        // Check if there are any creatives
        $total_creatives = wp_count_posts( 'wcu-creatives' );
        $published_creatives = ( isset( $total_creatives->publish ) ? $total_creatives->publish : 0 );
        if ( $published_creatives <= 0 ) {
            return '<div class="wcusage-widget-no-creatives"><p>' . esc_html__( 'No creatives available at this time.', 'woo-coupon-usage' ) . '</p></div>';
        }
        ob_start();
        // Get settings
        $wcusage_urls_prefix = wcusage_get_setting_value( 'wcusage_field_urls_prefix', 'coupon' );
        $wcusage_src_prefix = wcusage_get_setting_value( 'wcusage_field_src_prefix', 'src' );
        ?>
        
        <div class="wcusage-widget-creatives-container">
            
            <?php 
        // Get creatives without category first
        $args = array(
            'post_type'      => 'wcu-creatives',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'tax_query'      => array(array(
                'taxonomy' => 'wcu-creatives-category',
                'operator' => 'NOT EXISTS',
            )),
            'meta_key'       => 'creative_type',
            'meta_value'     => 'color',
            'meta_compare'   => '!=',
        );
        $result = new WP_Query($args);
        if ( $result->have_posts() ) {
            echo '<div class="wcusage-widget-creatives-section" style="margin-bottom: 0px;">';
            echo '<h4 class="wcusage-widget-section-title">' . esc_html__( 'Creatives', 'woo-coupon-usage' ) . '</h4>';
            echo '<div class="wcusage-widget-creatives-grid">';
            wcusage_widget_display_creatives(
                $result,
                $coupon_code,
                $user_id,
                $wcusage_urls_prefix,
                $wcusage_src_prefix
            );
            echo '</div>';
            echo '</div>';
        }
        // Get creative colors
        $args_colors = array(
            'post_type'      => 'wcu-creatives',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => 'creative_type',
            'meta_value'     => 'color',
            'orderby'        => 'post_date',
            'order'          => 'DESC',
        );
        $result_colors = new WP_Query($args_colors);
        if ( $result_colors->have_posts() ) {
            echo '<div class="wcusage-widget-creatives-section">';
            echo '<h4 class="wcusage-widget-section-sub-title">' . esc_html__( 'Brand Colors', 'woo-coupon-usage' ) . '</h4>';
            echo '<div class="wcusage-widget-colors-grid">';
            wcusage_widget_display_color_creatives( $result_colors );
            echo '</div>';
            echo '</div>';
        }
        // Get creatives by category
        $terms = get_terms( array(
            'taxonomy'   => 'wcu-creatives-category',
            'hide_empty' => true,
        ) );
        foreach ( $terms as $term ) {
            $args_cat = array(
                'post_type'      => 'wcu-creatives',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => array(array(
                    'taxonomy' => 'wcu-creatives-category',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                )),
            );
            $result_cat = new WP_Query($args_cat);
            if ( $result_cat->have_posts() ) {
                echo '<div class="wcusage-widget-creatives-section">';
                echo '<h4 class="wcusage-widget-section-sub-title">' . esc_html( $term->name ) . '</h4>';
                echo '<div class="wcusage-widget-creatives-grid">';
                wcusage_widget_display_creatives(
                    $result_cat,
                    $coupon_code,
                    $user_id,
                    $wcusage_urls_prefix,
                    $wcusage_src_prefix
                );
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
            
        </div>
        
        <?php 
        return ob_get_clean();
    } catch ( Exception $e ) {
        error_log( 'Floating widget creatives generation error: ' . $e->getMessage() );
        return '<div class="wcusage-widget-error">' . esc_html__( 'Error loading creatives.', 'woo-coupon-usage' ) . '</div>';
    }
}

// Widget-specific function to display creatives
function wcusage_widget_display_creatives(
    $result,
    $coupon_code,
    $user_id,
    $wcusage_urls_prefix,
    $wcusage_src_prefix
) {
    $allowed_html = wp_kses_allowed_html( 'post' );
    $current_date = new DateTime();
    while ( $result->have_posts() ) {
        $result->the_post();
        $post_id = get_the_ID();
        $creative_url = get_post_meta( $post_id, 'creative_url', true );
        if ( !$creative_url ) {
            $creative_url = wcusage_get_default_ref_url();
        }
        // Check user role access
        $creative_user_role = get_post_meta( $post_id, 'creative_user_role', true );
        $creative_user_role = ( $creative_user_role ?: 'all' );
        if ( $creative_user_role != 'all' ) {
            if ( !is_array( $creative_user_role ) ) {
                $creative_user_role = array(
                    $creative_user_role => 1,
                );
            }
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) {
                $user_roles = $user->roles;
                $allow = false;
                foreach ( $creative_user_role as $key => $value ) {
                    if ( $user_roles && in_array( $key, $user_roles ) ) {
                        $allow = true;
                        break;
                    }
                }
                if ( !$allow ) {
                    continue;
                }
            }
        }
        // Get creative data
        $creative_type = sanitize_text_field( get_post_meta( $post_id, 'creative_type', true ) );
        $creative_alt = sanitize_text_field( get_post_meta( $post_id, 'creative_alt', true ) );
        $creative_image = sanitize_text_field( get_post_meta( $post_id, 'creative_image', true ) );
        $creative_youtube_url = sanitize_text_field( get_post_meta( $post_id, 'creative_youtube_url', true ) );
        $creative_mp4 = sanitize_text_field( get_post_meta( $post_id, 'creative_mp4', true ) );
        $creative_pdf = sanitize_text_field( get_post_meta( $post_id, 'creative_pdf', true ) );
        $creative_description = sanitize_text_field( get_post_meta( $post_id, 'creative_description', true ) );
        $creative_name = get_the_title();
        // Check schedule
        $creative_start_date_day = sanitize_text_field( get_post_meta( $post_id, 'creative_start_date_day', true ) );
        $creative_start_date_month = sanitize_text_field( get_post_meta( $post_id, 'creative_start_date_month', true ) );
        $creative_start_date_year = sanitize_text_field( get_post_meta( $post_id, 'creative_start_date_year', true ) );
        $creative_end_date_day = sanitize_text_field( get_post_meta( $post_id, 'creative_end_date_day', true ) );
        $creative_end_date_month = sanitize_text_field( get_post_meta( $post_id, 'creative_end_date_month', true ) );
        $creative_end_date_year = sanitize_text_field( get_post_meta( $post_id, 'creative_end_date_year', true ) );
        if ( $creative_start_date_month && $creative_start_date_day ) {
            $creative_start_date_year = ( $creative_start_date_year ?: $current_date->format( 'Y' ) );
            $creative_start_date = DateTime::createFromFormat( 'Y-m-d', "{$creative_start_date_year}-{$creative_start_date_month}-{$creative_start_date_day}" );
        } else {
            $creative_start_date = null;
        }
        if ( $creative_end_date_month && $creative_end_date_day ) {
            $creative_end_date_year = ( $creative_end_date_year ?: $current_date->format( 'Y' ) );
            $creative_end_date = DateTime::createFromFormat( 'Y-m-d', "{$creative_end_date_year}-{$creative_end_date_month}-{$creative_end_date_day}" );
        } else {
            $creative_end_date = null;
        }
        $within_schedule = (!$creative_start_date || $current_date >= $creative_start_date) && (!$creative_end_date || $current_date <= $creative_end_date);
        if ( $within_schedule && $creative_type != "color" ) {
            ?>
            <div class="wcusage-widget-creative-item wcusage-widget-creative-type-<?php 
            echo esc_attr( $creative_type );
            ?>">
                
                <!-- Creative Image/Media -->
                <div class="wcusage-widget-creative-preview">
                    <?php 
            if ( !$creative_type || $creative_type == 'image' ) {
                ?>
                        <div class="wcusage-widget-creative-image" style="background-image: url(<?php 
                echo esc_url( $creative_image );
                ?>);" onclick="window.open('<?php 
                echo esc_url( $creative_image );
                ?>', '_blank')"></div>
                    <?php 
            } elseif ( $creative_type == 'dynamicimage' ) {
                $endpoint = 'wcusage/v1/generate_creative';
                $unique_id = get_post_meta( $post_id, 'creative_unique_id', true );
                if ( !$unique_id ) {
                    $unique_id = wp_generate_password( 15, false );
                    update_post_meta( $post_id, 'creative_unique_id', $unique_id );
                }
                $unique_string = $post_id . "-" . $coupon_code . "-" . $unique_id;
                $token = wp_hash( $unique_string );
                $parameters = array(
                    'creative_id' => urlencode( $post_id ),
                    'coupon_code' => urlencode( $coupon_code ),
                    'token'       => $token,
                );
                $final_image_url = rest_url( $endpoint ) . '?' . http_build_query( $parameters );
                ?>
                        <div class="wcusage-widget-creative-image" style="background-image: url(<?php 
                echo esc_url( $final_image_url );
                ?>);" onclick="window.open('<?php 
                echo esc_url( $final_image_url );
                ?>', '_blank')"></div>
                    <?php 
            } elseif ( $creative_type == 'pdf' ) {
                ?>
                        <div class="wcusage-widget-creative-image wcusage-widget-creative-pdf" style="background-image: url(<?php 
                echo esc_url( $creative_image );
                ?>);" onclick="window.open('<?php 
                echo esc_attr( $creative_pdf );
                ?>', '_blank')">
                            <div class="wcusage-widget-creative-overlay">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                        </div>
                    <?php 
            } elseif ( $creative_type == 'youtube' ) {
                $creative_youtube_url_embed = str_replace( 'watch?v=', 'embed/', $creative_youtube_url );
                ?>
                        <div class="wcusage-widget-creative-video">
                            <iframe src="<?php 
                echo esc_url( $creative_youtube_url_embed );
                ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php 
            } elseif ( $creative_type == 'mp4' ) {
                ?>
                        <div class="wcusage-widget-creative-video">
                            <video controls>
                                <source src="<?php 
                echo esc_attr( $creative_mp4 );
                ?>" type="video/mp4">
                            </video>
                        </div>
                    <?php 
            }
            ?>
                </div>
                
                <!-- Creative Info -->
                <div class="wcusage-widget-creative-info">
                    <h5 class="wcusage-widget-creative-title"><?php 
            echo esc_html( $creative_name );
            ?></h5>
                    <?php 
            if ( $creative_description ) {
                ?>
                        <p class="wcusage-widget-creative-description"><?php 
                echo wp_kses( $creative_description, $allowed_html );
                ?></p>
                    <?php 
            }
            ?>
                    
                    <!-- Action Buttons -->
                    <div class="wcusage-widget-creative-actions">
                        <?php 
            if ( !$creative_type || $creative_type == 'image' || $creative_type == 'dynamicimage' ) {
                $file_url = ( $creative_type == 'dynamicimage' ? $final_image_url : $creative_image );
                ?>
                            <button class="wcusage-widget-creative-btn wcusage-widget-creative-download" onclick="wcusageWidgetDownload('<?php 
                echo esc_attr( $file_url );
                ?>', '<?php 
                echo esc_attr( sanitize_file_name( $creative_name ) );
                ?>')">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="wcusage-widget-creative-btn wcusage-widget-creative-copy" onclick="wcusageWidgetCopyCreativeCode('<?php 
                echo esc_attr( $post_id );
                ?>')">
                                <i class="fas fa-code"></i>
                            </button>
                        <?php 
            } elseif ( $creative_type == 'pdf' ) {
                ?>
                            <button class="wcusage-widget-creative-btn wcusage-widget-creative-download" onclick="wcusageWidgetDownload('<?php 
                echo esc_attr( $creative_pdf );
                ?>', '<?php 
                echo esc_attr( sanitize_file_name( $creative_name ) );
                ?>')">
                                <i class="fas fa-download"></i>
                            </button>
                        <?php 
            } elseif ( $creative_type == 'youtube' && $creative_mp4 ) {
                ?>
                            <button class="wcusage-widget-creative-btn wcusage-widget-creative-download" onclick="wcusageWidgetDownload('<?php 
                echo esc_attr( $creative_mp4 );
                ?>', '<?php 
                echo esc_attr( sanitize_file_name( $creative_name ) );
                ?>')">
                                <i class="fas fa-download"></i>
                            </button>
                        <?php 
            } elseif ( $creative_type == 'mp4' ) {
                ?>
                            <button class="wcusage-widget-creative-btn wcusage-widget-creative-download" onclick="wcusageWidgetDownload('<?php 
                echo esc_attr( $creative_mp4 );
                ?>', '<?php 
                echo esc_attr( sanitize_file_name( $creative_name ) );
                ?>')">
                                <i class="fas fa-download"></i>
                            </button>
                        <?php 
            }
            ?>
                    </div>
                </div>
                
                <!-- Hidden embed code for copying -->
                <?php 
            if ( $creative_type != 'pdf' && $creative_type != 'youtube' && $creative_type != 'mp4' ) {
                $embed_url = $creative_url . '?' . $wcusage_urls_prefix . '=' . $coupon_code;
                $embed_image = ( $creative_type == 'dynamicimage' ? $final_image_url : $creative_image );
                $embed_code = '<a href="' . $embed_url . '" title="' . esc_attr( $creative_alt ) . '"><img src="' . $embed_image . '" alt="' . esc_attr( $creative_alt ) . '"></a>';
                ?>
                    <input type="hidden" id="wcusage-widget-embed-<?php 
                echo esc_attr( $post_id );
                ?>" value="<?php 
                echo esc_attr( $embed_code );
                ?>">
                <?php 
            }
            ?>
                
            </div>
            <?php 
        }
    }
}

// Widget-specific function to display color creatives
function wcusage_widget_display_color_creatives(  $result  ) {
    while ( $result->have_posts() ) {
        $result->the_post();
        $post_id = get_the_ID();
        $creative_name = get_the_title();
        $creative_color = sanitize_text_field( get_post_meta( $post_id, 'creative_color', true ) );
        $creative_description = sanitize_text_field( get_post_meta( $post_id, 'creative_description', true ) );
        // Calculate text color based on background
        $creative_color_code = str_replace( '#', '', $creative_color );
        if ( hexdec( substr( $creative_color_code, 0, 2 ) ) + hexdec( substr( $creative_color_code, 2, 2 ) ) + hexdec( substr( $creative_color_code, 4, 2 ) ) > 381 ) {
            $textcolor = '#000';
        } else {
            $textcolor = '#fff';
        }
        ?>
        <div class="wcusage-widget-color-item">
            <div class="wcusage-widget-color-swatch" style="background-color: <?php 
        echo esc_attr( $creative_color );
        ?>; color: <?php 
        echo esc_attr( $textcolor );
        ?>;" onclick="wcusageWidgetCopyColor('<?php 
        echo esc_attr( $creative_color );
        ?>', this)">
                <span class="wcusage-widget-color-code"><?php 
        echo esc_html( $creative_color );
        ?></span>
            </div>
            <div class="wcusage-widget-color-info">
                <h6 class="wcusage-widget-color-name"><?php 
        echo esc_html( $creative_name );
        ?></h6>
                <?php 
        if ( $creative_description ) {
            ?>
                    <p class="wcusage-widget-color-description"><?php 
            echo esc_html( $creative_description );
            ?></p>
                <?php 
        }
        ?>
            </div>
        </div>
        <?php 
    }
}
