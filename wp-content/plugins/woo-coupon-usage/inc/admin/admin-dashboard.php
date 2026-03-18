<?php

// Ensure WordPress functions are available
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clear all dashboard transient caches
 * Called when orders change status or commission data updates
 */
function wcusage_clear_dashboard_caches() {
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s
             OR option_name LIKE %s",
            '_transient_wcusage_dashboard_top_affiliates_%',
            '_transient_timeout_wcusage_dashboard_top_affiliates_%',
            '_transient_wcusage_dashboard_latest_affiliates_%',
            '_transient_timeout_wcusage_dashboard_latest_affiliates_%',
            '_transient_wcusage_dashboard_sidebar_top_affiliates_%',
            '_transient_timeout_wcusage_dashboard_sidebar_top_affiliates_%',
            '_transient_wcusage_dashboard_program_stats%',
            '_transient_timeout_wcusage_dashboard_program_stats%',
            '_transient_wcusage_dashboard_activity_recent%',
            '_transient_timeout_wcusage_dashboard_activity_recent%',
            '_transient_wcusage_leaderboard_%',
            '_transient_timeout_wcusage_leaderboard_%'
        )
    );
}

// Clear dashboard caches when order status changes
add_action('woocommerce_order_status_changed', 'wcusage_clear_dashboard_caches', 999);

// Clear dashboard caches when coupon is saved or deleted
add_action('save_post_shop_coupon', 'wcusage_clear_dashboard_caches', 20);
add_action('delete_post', function($post_id) {
    if (get_post_type($post_id) === 'shop_coupon') {
        wcusage_clear_dashboard_caches();
    }
}, 10);

// Clear dashboard caches when affiliate user assignments change
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        wcusage_clear_dashboard_caches();
    }
}, 10, 4);

add_action('added_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        wcusage_clear_dashboard_caches();
    }
}, 10, 4);

add_action('deleted_post_meta', function($meta_ids, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        wcusage_clear_dashboard_caches();
    }
}, 10, 4);

/**
 * Displays header section on dashboard pages.
 */
add_action( 'wcusage_hook_dashboard_page_header', 'wcusage_dashboard_page_header' );
function wcusage_dashboard_page_header() {
    // Enqueue dashboard CSS and JS
    wp_enqueue_style(
        'wcusage-admin-dashboard',
        WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-dashboard.css',
        array(),
        null
    );
    wp_enqueue_script(
        'wcusage-admin-dashboard',
        WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-dashboard.js',
        array('jquery', 'jquery-ui-sortable'),
        null,
        true
    );
    // Provide AJAX data for sortable dashboard sections
    wp_localize_script(
        'wcusage-admin-dashboard',
        'WCUsageDashboard',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wcusage_dashboard_order'),
            'paginationNonce' => wp_create_nonce('wcusage_dashboard_paginate'),
            'clearCacheNonce' => wp_create_nonce('wcusage_dashboard_clear_cache'),
        )
    );
    $rss_items = array();
    $feed_html = '<p>' . esc_html__( 'View the latest updates and changelog on our website:', 'woo-coupon-usage' ) . ' ' .
        '<a href="https://roadmap.couponaffiliates.com/updates/" target="_blank" rel="noopener">' . esc_html__( 'Open changelog', 'woo-coupon-usage' ) . '</a></p>';
    // Show changelog modal markup (styles now in CSS, logic in JS)
    $changelog_new_class = (strpos($feed_html, 'new-update') !== false) ? 'changelog-new' : 'changelog-new hide';
    echo '<div id="changelog-modal" style="display:none;">
            <div class="modal-content">
                <span id="close-changelog-modal">×</span>
                '.wp_kses_post($feed_html).'
            </div>
        </div>';
    // Enqueue admin header menu CSS
    wp_enqueue_style('wcusage-admin-header-menu', WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-header-menu.css', array(), null);

    // Get affiliate orders and clicks for last 2 months with caching
    $stats_cache_key = 'wcusage_dashboard_program_stats';
    $cached_stats = get_transient($stats_cache_key);
    
    if ($cached_stats !== false && isset($cached_stats['orders_data']) && isset($cached_stats['clicks_data'])) {
        $orders_data = $cached_stats['orders_data'];
        $clicks_data = $cached_stats['clicks_data'];
    } else {
        $wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');
        $statuses = !$wcusage_field_order_type_custom ? array_diff_key(wc_get_order_statuses(), ['wc-refunded' => '']) : $wcusage_field_order_type_custom;

        // Get ALL affiliate orders from last 2 months (no limit)
        $orders = wc_get_orders(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => array_keys($statuses),
            'meta_key' => 'wcusage_affiliate_user',
            'meta_compare' => 'EXISTS',
            'date_query' => array(
                array(
                    'after' => '2 months ago',
                    'inclusive' => true,
                ),
            ),
        ));

        $orders_data = array();
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $calculateorder = wcusage_calculate_order_data($order_id, '', 0, 1);
            $orders_data[] = array(
                'date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                'total' => $calculateorder['totalordersexcl'],
                'discounts' => $calculateorder['totaldiscounts'],
                'commission' => $calculateorder['totalcommission'],
                'subtotal' => $calculateorder['totalorders']
            );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcusage_clicks';
        $two_months_ago = gmdate("Y-m-d", strtotime('-2 months'));
        $clicks = $wpdb->get_results($wpdb->prepare(
            "SELECT date FROM $table_name WHERE date >= %s ORDER BY date DESC", // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            $two_months_ago
        ));
        $clicks_data = array_map(function($click) {
            return $click->date;
        }, $clicks);
        
        // Cache for 1 hour
        set_transient($stats_cache_key, array(
            'orders_data' => $orders_data,
            'clicks_data' => $clicks_data
        ), HOUR_IN_SECONDS);
    }
?>

<script>
jQuery(document).ready(function($) {
    setTimeout(function() {
        $('.updated.success, .notice.is-dismissible, .notice.notice-warning').insertBefore('.wcusage-admin-page-col3');
    }, 100);

    // Store all data in JS
    var allOrders = <?php echo json_encode($orders_data); ?>;
    var allClicks = <?php echo json_encode($clicks_data); ?>;
    var currencySymbol = '<?php echo esc_js(get_woocommerce_currency_symbol()); ?>';

    function formatPrice(value) {
        return currencySymbol + Number(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function calculateStats(range) {
        var now = new Date();
        var filteredOrders = allOrders;
        var filteredClicks = allClicks;

        switch(range) {
            case 'last7days':
                var last7days = new Date(now.setDate(now.getDate() - 7));
                filteredOrders = allOrders.filter(o => new Date(o.date) >= last7days);
                filteredClicks = allClicks.filter(c => new Date(c) >= last7days);
                break;
            case 'thismonth':
                var thisMonthStart = new Date(now.getFullYear(), now.getMonth(), 1);
                filteredOrders = allOrders.filter(o => new Date(o.date) >= thisMonthStart);
                filteredClicks = allClicks.filter(c => new Date(c) >= thisMonthStart);
                break;
            case 'lastmonth':
                var lastMonthStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                var lastMonthEnd = new Date(now.getFullYear(), now.getMonth(), 0);
                filteredOrders = allOrders.filter(o => {
                    var d = new Date(o.date);
                    return d >= lastMonthStart && d <= lastMonthEnd;
                });
                filteredClicks = allClicks.filter(c => {
                    var d = new Date(c);
                    return d >= lastMonthStart && d <= lastMonthEnd;
                });
                break;
        }

        var stats = filteredOrders.reduce(function(acc, order) {
            acc.referrals++;
            acc.sales += parseFloat(order.total);
            acc.discounts += parseFloat(order.discounts);
            acc.commission += parseFloat(order.commission);
            return acc;
        }, {referrals: 0, sales: 0, discounts: 0, commission: 0});

        stats.clicks = filteredClicks.length;

        $('.total-usage').text(stats.referrals);
        $('.total-sales').html(formatPrice(stats.sales));
        $('.total-discounts').html(formatPrice(stats.discounts));
        $('.total-commission').html(formatPrice(stats.commission));
        $('.total-clicks').text(stats.clicks);
    }

    // Initial load - default to "This Month"
    calculateStats('thismonth');

    // Handle toggle clicks
    $('.stats-range-toggle a').on('click', function(e) {
        e.preventDefault();
        $('.stats-range-toggle a').removeClass('active');
        $(this).addClass('active');
        var range = $(this).data('range');
        calculateStats(range);
    });
});
</script>

<div class="wcusage-admin-page-col3">
    <div style="float: left; margin: 12px 0 10px 0; display: flex; align-items: center; gap: 18px;">
        <a href="<?php echo esc_url(get_admin_url()); ?>admin.php?page=wcusage" title="View Dashboard">
            <img src="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL); ?>images/coupon-affiliates-logo.png" style="display: inline-block; width: 100%; max-width: 290px; text-align: left;">
        </a>

        <?php
        // Admin notification bell
        do_action('wcusage_hook_admin_notification_bell');
        ?>
    </div>
        <?php
    if (wcu_fs()->can_use_premium_code()) {
        $menu_items = array(
            array('label' => 'Settings', 'icon' => 'fa-solid fa-cog', 'url' => admin_url('admin.php?page=wcusage_settings')),
            array('label' => 'Coupons', 'icon' => 'fa-solid fa-ticket', 'url' => admin_url('admin.php?page=wcusage_coupons'), 'dropdown' => array(
                array('label' => 'View Affiliate Coupons', 'url' => admin_url('admin.php?page=wcusage_coupons'), 'icon' => 'fa-solid fa-tags'),
                array('label' => 'Add New Affiliate Coupon', 'url' => admin_url('admin.php?page=wcusage_add_affiliate'), 'icon' => 'fa-solid fa-plus'),
                array('label' => 'Bulk Create Affiliate Coupons', 'url' => admin_url('admin.php?page=wcusage-bulk-coupon-creator'), 'icon' => 'fa-solid fa-layer-group'),
            )),
            // Affiliates with dropdown
            array('label' => 'Affiliates', 'icon' => 'fa-solid fa-user-group', 'url' => admin_url('admin.php?page=wcusage_affiliates'), 'dropdown' => array(
                array('label' => 'View Affiliates', 'url' => admin_url('admin.php?page=wcusage_affiliates'), 'icon' => 'fa-solid fa-users'),
                array('label' => 'Manage Registrations', 'url' => admin_url('admin.php?page=wcusage_registrations'), 'icon' => 'fa-solid fa-users-gear'),
                array('label' => 'Add New Affiliate', 'url' => admin_url('admin.php?page=wcusage_add_affiliate'), 'icon' => 'fa-solid fa-user-plus'),
            )),
            array('label' => 'Referrals', 'icon' => 'fa-solid fa-arrow-right-arrow-left', 'url' => admin_url('admin.php?page=wcusage_referrals'), 'dropdown' => array(
                array('label' => 'View Referred Orders', 'url' => admin_url('admin.php?page=wcusage_referrals'), 'icon' => 'fa-solid fa-arrow-right-arrow-left'),
                array('label' => 'View URL Visits Log', 'url' => admin_url('admin.php?page=wcusage_clicks'), 'icon' => 'fa-solid fa-eye'),
            )),
            array('label' => 'Payouts', 'icon' => 'fa-solid fa-money-bill', 'url' => admin_url('admin.php?page=wcusage_payouts'), 'dropdown' => array(
                array('label' => 'View Payouts', 'url' => admin_url('admin.php?page=wcusage_payouts'), 'icon' => 'fa-solid fa-money-bill'),
                array('label' => 'Create New Payout', 'url' => admin_url('admin.php?page=wcusage_payouts_create'), 'icon' => 'fa-solid fa-plus'),
                array('label' => 'PDF Invoices', 'url' => admin_url('admin.php?post_type=wcu-statements'), 'icon' => 'fa-solid fa-file-invoice-dollar',
                'disabled' => wcusage_get_setting_value('wcusage_field_payouts_enable_statements', '0')),
            )),
        );
        $other_items = array(
            array(
                'label' => 'View Admin Reports',
                'url' => admin_url('admin.php?page=wcusage_admin_reports'),
                'icon' => 'fa-solid fa-chart-bar',
                'disabled' => false
            ),
            array(
                'label' => 'Email Newsletters',
                'url' => admin_url('admin.php?page=wcusage_email_newsletters'),
                'icon' => 'fa-solid fa-envelope',
                'disabled' => !(wcusage_get_setting_value('wcusage_field_email_newsletter_enable', '0') && function_exists('wcusage_admin_email_newsletters_page_html'))
            ),
            array(
                'label' => 'Leaderboards',
                'url' => admin_url('admin.php?page=wcusage_leaderboard'),
                'icon' => 'fa-solid fa-trophy',
                'disabled' => !wcu_fs()->can_use_premium_code()
            ),
            array(
                'label' => 'Affiliate Groups',
                'url' => admin_url('admin.php?page=wcusage_groups'),
                'icon' => 'fa-solid fa-users',
                'disabled' => !wcu_fs()->can_use_premium_code()
            ),
            array(
                'label' => 'Performance Bonuses',
                'url' => admin_url('edit.php?post_type=wcu-bonuses'),
                'icon' => 'fa-solid fa-bolt',
                'disabled' => !(wcusage_get_setting_value('wcusage_field_bonuses_enable', '0') && wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1'))
            ),
            array(
                'label' => 'Direct Link Domains',
                'url' => admin_url('admin.php?page=wcusage_domains'),
                'icon' => 'fa-solid fa-globe',
                'disabled' => !wcusage_get_setting_value('wcusage_field_enable_directlinks', 0)
            ),
            array(
                'label' => 'View Short URLs',
                'url' => admin_url('edit.php?post_type=wcu-short-urls'),
                'icon' => 'fa-solid fa-link',
                'disabled' => !wcusage_get_setting_value('wcusage_field_show_shortlink', 0)
            ),
            array(
                'label' => 'Manage Creatives',
                'url' => admin_url('admin.php?page=wcusage_creatives'),
                'icon' => 'fa-solid fa-image',
                'disabled' => !wcusage_get_setting_value('wcusage_field_creatives_enable', '1')
            ),
            array('label' => 'Admin Tools', 'url' => admin_url('admin.php?page=wcusage_tools'), 'icon' => 'fa-solid fa-wrench', 'disabled' => false),
        );
    } else {
        $menu_items = array(
            array('label' => 'Settings', 'icon' => 'fa-solid fa-cog', 'url' => admin_url('admin.php?page=wcusage_settings')),
            array('label' => 'Coupons', 'icon' => 'fa-solid fa-ticket', 'url' => admin_url('admin.php?page=wcusage_coupons'), 'dropdown' => array(
                array('label' => 'View Coupons', 'url' => admin_url('admin.php?page=wcusage_coupons'), 'icon' => 'fa-solid fa-ticket'),
                array('label' => 'Add New Affiliate Coupon', 'url' => admin_url('admin.php?page=wcusage_add_affiliate'), 'icon' => 'fa-solid fa-plus'),
                array('label' => 'Bulk Create Affiliate Coupons', 'url' => admin_url('admin.php?page=wcusage-bulk-coupon-creator'), 'icon' => 'fa-solid fa-layer-group'),
            )),
            // Affiliates with dropdown
            array('label' => 'Affiliates', 'icon' => 'fa-solid fa-user-group', 'url' => admin_url('admin.php?page=wcusage_affiliates'), 'dropdown' => array(
                array('label' => 'View Affiliates', 'url' => admin_url('admin.php?page=wcusage_affiliates'), 'icon' => 'fa-solid fa-users'),
                array('label' => 'Manage Registrations', 'url' => admin_url('admin.php?page=wcusage_registrations'), 'icon' => 'fa-solid fa-user-plus'),
                array('label' => 'Add New Affiliate', 'url' => admin_url('admin.php?page=wcusage_add_affiliate'), 'icon' => 'fa-solid fa-user-plus'),
            )),
            array('label' => 'Referrals', 'icon' => 'fa-solid fa-arrow-right-arrow-left', 'url' => admin_url('admin.php?page=wcusage_referrals'), 'dropdown' => array(
                array('label' => 'View Referred Orders', 'url' => admin_url('admin.php?page=wcusage_referrals'), 'icon' => 'fa-solid fa-arrow-right-arrow-left'),
                array('label' => 'View URL Visits Log', 'url' => admin_url('admin.php?page=wcusage_clicks'), 'icon' => 'fa-solid fa-eye'),
            )),
        );
        $other_items = array(
            array('label' => 'View Admin Reports', 'url' => admin_url('admin.php?page=wcusage_admin_reports'), 'icon' => 'fa-solid fa-chart-bar'),
            array('label' => 'Manage Payouts', 'url' => admin_url('admin.php?page=wcusage_payouts'), 'icon' => 'fa-solid fa-money-bill'),
            array('label' => 'PDF Statements', 'url' => admin_url('admin.php?page=wcusage_statements'), 'icon' => 'fa-solid fa-file-invoice-dollar'),
            array('label' => 'Email Newsletters', 'url' => admin_url('admin.php?page=wcusage_email_newsletters'), 'icon' => 'fa-solid fa-envelope'),
            array('label' => 'Leaderboards', 'url' => admin_url('admin.php?page=wcusage_leaderboard'), 'icon' => 'fa-solid fa-trophy'),
            array('label' => 'Affiliate Groups', 'url' => admin_url('admin.php?page=wcusage_groups'), 'icon' => 'fa-solid fa-users'),
            array('label' => 'Performance Bonuses', 'url' => admin_url('edit.php?post_type=wcu-bonuses'), 'icon' => 'fa-solid fa-bolt'),
            array('label' => 'Direct Link Domains', 'url' => admin_url('admin.php?page=wcusage_domains'), 'icon' => 'fa-solid fa-globe'),
            array('label' => 'View Short URLs', 'url' => admin_url('edit.php?post_type=wcu-short-urls'), 'icon' => 'fa-solid fa-link'),
            array('label' => 'Manage Creatives', 'url' => admin_url('admin.php?page=wcusage_creatives'), 'icon' => 'fa-solid fa-image'),
            array('label' => 'Admin Tools', 'url' => admin_url('admin.php?page=wcusage_tools'), 'icon' => 'fa-solid fa-wrench'),
        );
    }
    $support_items = array(
        array('label' => 'Support Forum', 'url' => 'https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0', 'icon' => 'fa-solid fa-comments', 'external' => true),
        array('label' => 'Documentation', 'url' => 'https://couponaffiliates.com/docs?utm_campaign=plugin&utm_source=dashboard-header&utm_medium=button', 'icon' => 'fa-solid fa-book', 'external' => true),
        array('label' => 'Roadmap', 'url' => 'https://roadmap.couponaffiliates.com/roadmap', 'icon' => 'fa-solid fa-list', 'external' => true),
        array('label' => 'Updates', 'url' => '#', 'icon' => 'fa-solid fa-sync', 'external' => false, 'id' => 'show-changelog'),
    );
    ?>
    <div class="wcusage-admin-header-menu">
        <ul class="wcusage-admin-menu" style="list-style: none; margin: 0; padding: 0; display: flex; gap: 10px; align-items: center;">
            <?php
            // Get current page for active menu styling
            $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            // Map pages that should force a specific top-level parent to be highlighted
            // Key = current page slug, Value = parent top-level page slug to highlight
            // Example: On "Add New Affiliate" page, only "Affiliates" should be active (not "Coupons").
            $forced_parent_for_page = array(
                'wcusage_add_affiliate' => 'wcusage_affiliates',
            );
            $forced_parent = isset($forced_parent_for_page[$current_page]) ? $forced_parent_for_page[$current_page] : '';
            foreach ($menu_items as $item):
                $is_active = false;
                $active_sub = false;
                // Extract ?page= value from menu item url
                preg_match('/[?&]page=([^&]+)/', $item['url'], $matches);
                $item_page = isset($matches[1]) ? $matches[1] : '';
                // If a forced parent is defined for the current page, only that parent should be highlighted
                if (!empty($forced_parent)) {
                    if ($item_page === $forced_parent) {
                        // Highlight if the parent itself or any of its dropdown items match current page
                        if ($item_page === $current_page) {
                            $is_active = true;
                        } elseif (!empty($item['dropdown'])) {
                            foreach ($item['dropdown'] as $subitem) {
                                preg_match('/[?&]page=([^&]+)/', $subitem['url'], $submatches);
                                $sub_page = isset($submatches[1]) ? $submatches[1] : '';
                                if ($sub_page === $current_page) {
                                    $is_active = true;
                                    $active_sub = $sub_page;
                                    break;
                                }
                            }
                        }
                    } else {
                        // Different parent: never mark active even if a dropdown contains the current page
                        $is_active = false;
                    }
                } else {
                    // Default behaviour: mark active on exact match or if any dropdown item matches
                    if ($item_page === $current_page) {
                        $is_active = true;
                    } elseif (!empty($item['dropdown'])) {
                        foreach ($item['dropdown'] as $subitem) {
                            preg_match('/[?&]page=([^&]+)/', $subitem['url'], $submatches);
                            $sub_page = isset($submatches[1]) ? $submatches[1] : '';
                            if ($sub_page === $current_page) {
                                $is_active = true;
                                $active_sub = $sub_page;
                                break;
                            }
                        }
                    }
                }
                if (!empty($item['dropdown'])): ?>
                    <li class="wcusage-admin-menu-dropdown" style="position: relative;">
                        <a href="<?php echo esc_url($item['url']); ?>" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #333;<?php echo $is_active ? ' background: #f3f3f3;' : ''; ?> font-weight: 500;">
                            <span class="<?php echo esc_attr($item['icon']); ?>"></span> <?php echo esc_html($item['label']); ?> <span style="margin-left: 4px;" class="fa-solid fa-caret-down"></span>
                        </a>
                        <ul class="wcusage-admin-menu-dropdown-list" style="display: none; position: absolute; left: 50%; top: 100%; transform: translateX(-50%); background: #fff; border: 1px solid #ddd; border-radius: 8px; min-width: 200px; box-shadow: 0 2px 16px rgba(0,0,0,0.12); z-index: 9999;">
                            <?php foreach ($item['dropdown'] as $subitem):
                                preg_match('/[?&]page=([^&]+)/', $subitem['url'], $submatches);
                                $sub_page = isset($submatches[1]) ? $submatches[1] : '';
                                $sub_active = ($sub_page === $current_page);
                            ?>
                                <?php $is_disabled = isset($subitem['disabled']) && $subitem['disabled']; ?>
                                <li>
                                    <a href="<?php echo $is_disabled ? 'javascript:void(0);' : esc_url($subitem['url']); ?>"
                                       style="display: flex; align-items: center; gap: 6px; padding: 8px 16px;<?php echo $is_disabled ? ' color: #aaa; cursor: not-allowed;' : ' color: #333;'; ?> text-decoration: none;<?php echo $sub_active ? ' background: #f3f3f3;' : ''; ?>"
                                       <?php echo $is_disabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                        <span class="<?php echo esc_attr($subitem['icon']); ?>"></span> <?php echo esc_html($subitem['label']); ?>
                                        <?php if ($is_disabled): ?><span style="margin-left: auto; color: #d9534f; font-size: 13px; font-weight: bold;">(Disabled)</span><?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li style="position: relative;">
                        <a href="<?php echo esc_url($item['url']); ?>" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #333;<?php echo $is_active ? ' background: #f3f3f3;' : ''; ?> font-weight: 500;">
                            <span class="<?php echo esc_attr($item['icon']); ?>"></span> <?php echo esc_html($item['label']); ?>
                        </a>
                    </li>
                <?php endif;
            endforeach; ?>
            <!-- Other dropdown -->
            <li class="wcusage-admin-menu-dropdown" style="position: relative;">
                <a href="#" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #333; font-weight: 500;">
                    <span class="fa-solid fa-ellipsis-h"></span> Other <span style="margin-left: 4px;" class="fa-solid fa-caret-down"></span>
                </a>
                <ul class="wcusage-admin-menu-dropdown-list" style="display: none; position: absolute; left: 0; top: 100%; background: #fff; border: 1px solid #ddd; border-radius: 8px; min-width: 200px; box-shadow: 0 2px 16px rgba(0,0,0,0.12); z-index: 9999;">
                    <?php
                    if (wcu_fs()->can_use_premium_code()) {
                        // PRO version: normal links, no PRO icon
                        foreach ($other_items as $item) {
                            preg_match('/[?&]page=([^&]+)/', $item['url'], $other_matches);
                            $other_page = isset($other_matches[1]) ? $other_matches[1] : '';
                            $other_active = ($other_page === $current_page);
                            $is_disabled = isset($item['disabled']) && $item['disabled'];
                            ?>
                            <li>
                                <a href="<?php echo $is_disabled ? 'javascript:void(0);' : esc_url($item['url']); ?>" style="display: flex; align-items: center; gap: 6px; padding: 8px 16px;<?php echo $is_disabled ? ' color: #aaa; cursor: not-allowed;' : ' color: #333;'; ?> text-decoration: none;<?php echo $other_active ? ' background: #f3f3f3;' : ''; ?>">
                                    <?php if (!empty($item['icon'])): ?><span class="<?php echo esc_attr($item['icon']); ?>"></span> <?php endif; ?><?php echo esc_html($item['label']); ?>
                                    <?php if ($is_disabled): ?><span style="margin-left: auto; color: #d9534f; font-size: 13px; font-weight: bold;">(Disabled)</span><?php endif; ?>
                                </a>
                            </li>
                            <?php
                        }
                    } else {
                        // Free version: disabled links with PRO icon
                        foreach ($other_items as $item) {
                            preg_match('/[?&]page=([^&]+)/', $item['url'], $other_matches);
                            $other_page = isset($other_matches[1]) ? $other_matches[1] : '';
                            $other_active = ($other_page === $current_page);
                            ?>
                            <li>
                                <a href="javascript:void(0);" style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; color: #aaa; text-decoration: none; cursor: not-allowed;<?php echo $other_active ? ' background: #f3f3f3;' : ''; ?>">
                                    <?php if (!empty($item['icon'])): ?><span class="<?php echo esc_attr($item['icon']); ?>"></span> <?php endif; ?><?php echo esc_html($item['label']); ?>
                                    <span style="margin-left: auto; color: #d9534f; font-size: 13px; font-weight: bold;">(PRO)</span>
                                </a>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </li>
            <!-- Support dropdown -->
            <li class="wcusage-admin-menu-dropdown" style="position: relative;">
                <a href="#" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #333; font-weight: 500;">
                    <span class="fa-solid fa-life-ring"></span> Support <span style="margin-left: 4px;" class="fa-solid fa-caret-down"></span>
                </a>
                <ul class="wcusage-admin-menu-dropdown-list" style="display: none; position: absolute; left: 0; top: 100%; background: #fff; border: 1px solid #ddd; border-radius: 8px; min-width: 200px; box-shadow: 0 2px 16px rgba(0,0,0,0.12); z-index: 9999;">
                    <?php foreach ($support_items as $item):
                        preg_match('/[?&]page=([^&]+)/', $item['url'], $support_matches);
                        $support_page = isset($support_matches[1]) ? $support_matches[1] : '';
                        $support_active = ($support_page === $current_page);
                    ?>
                        <li><a href="<?php echo esc_url($item['url']); ?>"<?php if (!empty($item['external'])) { echo ' target="_blank" rel="noopener"'; } ?><?php if (!empty($item['id'])) { echo ' id="' . esc_attr($item['id']) . '"'; } ?> style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; color: #333; text-decoration: none;<?php echo $support_active ? ' background: #f3f3f3;' : ''; ?>">
                            <?php if (!empty($item['icon'])): ?><span class="<?php echo esc_attr($item['icon']); ?>"></span> <?php endif; ?><?php echo esc_html($item['label']); ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php if (!wcu_fs()->can_use_premium_code()): ?>
            <!-- Upgrade to PRO button -->
            <li style="position: relative;">
                <a href="https://couponaffiliates.com/pricing/?utm_source=plugin&utm_medium=upgrade-menu" target="_blank" rel="noopener" style="display: flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 8px; text-decoration: none; color: #fff; font-weight: 600; background: linear-gradient(270deg,#28a745,#218838,#28a745); box-shadow: 0 2px 8px rgba(40,167,69,0.15);">
                    Upgrade to PRO
                </a>
            </li>
            <style>
            @keyframes wcusage-upgrade-anim {
                0% {background-position:0% 50%}
                50% {background-position:100% 50%}
                100% {background-position:0% 50%}
            }
            </style>
            <?php endif; ?>
            <!-- RelyWP Logo -->
            <li style="position: relative; margin-left: 10px;">
                <a href="https://relywp.com" target="_blank" rel="noopener" style="display: flex; align-items: center;">
                    <img src="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL); ?>images/relywp.png"
                    title="Developed by RelyWP"
                    alt="RelyWP" style="height: 25px; width: auto;">
                </a>
            </li>
        </ul>
    </div>
</div>

<div style="clear: both;"></div>

<script type="text/javascript">
document.getElementById("show-changelog").onclick = function() {
    document.getElementById("changelog-modal").style.display = "block";
};

document.getElementById("close-changelog-modal").onclick = function() {
    document.getElementById("changelog-modal").style.display = "none";
};

window.onclick = function(event) {
    if (event.target == document.getElementById("changelog-modal")) {
        document.getElementById("changelog-modal").style.display = "none";
    }
};
</script>

<?php
}

function wcusage_custom_page_header() {
    $screen = get_current_screen();
    if ( $screen->post_type == 'wcu-statements'
    || $screen->post_type == 'wcu-creatives'
    || $screen->post_type == 'wcu-short-url'
    || $screen->post_type == 'wcu-bonuses'
    || isset($_GET['page']) && $_GET['page'] == 'wcusage-account' ) {
        echo '<div class="wrap wcusage-admin-page">';
        do_action( 'wcusage_hook_dashboard_page_header', '');
        echo '</div>';        
        echo '<style type="text/css">
        #screen-meta-links { position: absolute; float: right; right: 0; top: -5px; transform: scale(0.75); }
        </style>';
    }
}
add_action( 'all_admin_notices', 'wcusage_custom_page_header' );

function wcusage_changelog_fetch_rss_feed($feed_url) {
    $feed = fetch_feed($feed_url);
    if (is_wp_error($feed)) {
        return array();
    }
    $max_items = $feed->get_item_quantity(4);
    $rss_items = $feed->get_items(0, $max_items);
    return $rss_items;
}

function wcusage_changelog_generate_feed_html($rss_items) {
    $output = '<div class="rss-feed-items">';
    $output = '<h2>Latest Major Updates</h2>';

    foreach ($rss_items as $item) {
        $title = esc_html($item->get_title());
        $title = str_replace('Coupon Affiliates –', '', $title);
        $date = $item->get_date('jS F Y');
        $the_date = date_create($date);
        $now = date_create();
        $diff = date_diff($the_date, $now);
        $days = $diff->format("%a");
        $new = ($days <= 7) ? ' <span style="background: green; padding: 2px; font-size: 10px; line-height: 10px; border-radius: 2px; color: #fff;" class="new-update">New</span>' : '';
        $output .= '<div class="rss-feed-item">';
        $output .= '<h4>'.$date.$new.'<br/><a href="' . esc_url($item->get_permalink()) . "?utm_campaign=plugin&utm_source=settings-changelog&utm_medium=textlink" . '">' . esc_html($title) . '</a></h4>';
        $output .= '</div>';
    }

    $output .= '<a href="https://roadmap.couponaffiliates.com/updates/" target="_blank" style="display: inline-block; background: #000; color: #fff; text-decoration: none; padding: 5px 10px; margin-bottom: 20px;">View Full Changelog</a>';
    return $output;
}

/**
 * Dashboard sections ordering helpers and AJAX
 */
function wcusage_get_dashboard_section_keys() {
    return array('activity', 'referrals', 'visits', 'coupons', 'registrations', 'payouts');
}

function wcusage_get_default_dashboard_section_order() {
    return array('activity', 'referrals', 'visits', 'coupons', 'registrations', 'payouts');
}

function wcusage_get_user_dashboard_section_order($user_id = 0) {
    $user_id = $user_id ? (int) $user_id : get_current_user_id();
    $saved = get_user_meta($user_id, 'wcusage_dashboard_order', true);
    $allowed = wcusage_get_dashboard_section_keys();
    $default = wcusage_get_default_dashboard_section_order();

    $order = array();
    if (is_array($saved) && !empty($saved)) {
        foreach ($saved as $key) {
            $key = sanitize_key($key);
            if (in_array($key, $allowed, true)) {
                $order[] = $key;
            }
        }
    }
    foreach ($default as $key) {
        if (!in_array($key, $order, true)) {
            $order[] = $key;
        }
    }
    return $order;
}

function wcusage_save_dashboard_order_ajax() {
    check_ajax_referer('wcusage_dashboard_order', 'nonce');

    if (!is_user_logged_in() || !function_exists('wcusage_check_admin_access') || !wcusage_check_admin_access()) {
        wp_send_json_error(array('message' => __('Not authorized.', 'woo-coupon-usage')), 403);
    }

    $order = isset($_POST['order']) ? (array) $_POST['order'] : array();
    $order = array_map('sanitize_key', $order);

    $allowed = wcusage_get_dashboard_section_keys();
    $order = array_values(array_intersect($order, $allowed));

    if (empty($order)) {
        $order = wcusage_get_default_dashboard_section_order();
    }

    // Save to user meta
    update_user_meta(get_current_user_id(), 'wcusage_dashboard_order', $order);

    wp_send_json_success(array('order' => $order));
}
add_action('wp_ajax_wcusage_save_dashboard_order', 'wcusage_save_dashboard_order_ajax');

/**
 * AJAX: Clear dashboard caches
 */
function wcusage_clear_dashboard_caches_ajax() {
    check_ajax_referer('wcusage_dashboard_clear_cache', 'nonce');

    if (!is_user_logged_in() || !function_exists('wcusage_check_admin_access') || !wcusage_check_admin_access()) {
        wp_send_json_error(array('message' => __('Not authorized.', 'woo-coupon-usage')), 403);
    }

    wcusage_clear_dashboard_caches();

    wp_send_json_success(array('message' => __('Dashboard caches cleared successfully!', 'woo-coupon-usage')));
}
add_action('wp_ajax_wcusage_clear_dashboard_caches', 'wcusage_clear_dashboard_caches_ajax');

/**
 * AJAX: Paginate dashboard section tables
 */
function wcusage_dashboard_paginate_ajax() {
    check_ajax_referer('wcusage_dashboard_paginate', 'nonce');

    if (!is_user_logged_in() || !function_exists('wcusage_check_admin_access') || !wcusage_check_admin_access()) {
        wp_send_json_error(array('message' => __('Not authorized.', 'woo-coupon-usage')), 403);
    }

    $section  = isset($_POST['section']) ? sanitize_key($_POST['section']) : '';
    $page     = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, (int) $_POST['per_page']) : 5;
    // Cap per_page to prevent excessive load
    $per_page = min($per_page, 50);
    $offset   = ($page - 1) * $per_page;

    global $wpdb;
    $html = '';
    $total = 0;

    switch ($section) {
        case 'affiliates_latest':
            // Check cache first for performance
            $cache_key = 'wcusage_dashboard_latest_affiliates_' . $offset . '_' . $per_page;
            $cached_data = get_transient($cache_key);
            
            if ($cached_data !== false && isset($cached_data['html']) && isset($cached_data['total'])) {
                $html = $cached_data['html'];
                $total = $cached_data['total'];
                break;
            }
            
            // Total distinct affiliate users
            $statuses = array('publish', 'pending', 'draft');
            $status_placeholders = implode(',', array_fill(0, count($statuses), '%s'));
            $sql_total = "SELECT COUNT(DISTINCT pm.meta_value)
                          FROM {$wpdb->postmeta} pm
                          INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                          WHERE pm.meta_key = %s
                            AND pm.meta_value IS NOT NULL
                            AND pm.meta_value != ''
                            AND pm.meta_value != '0'
                            AND p.post_type = %s
                            AND p.post_status IN ($status_placeholders)";
            $args_total = array_merge(array('wcu_select_coupon_user', 'shop_coupon'), $statuses);
            $total = (int) $wpdb->get_var($wpdb->prepare($sql_total, $args_total));

            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }

            // Latest affiliates by newest coupon date (distinct users)
            $sql_users = "SELECT pm.meta_value AS user_id, MAX(p.post_date) AS latest_date
                          FROM {$wpdb->postmeta} pm
                          INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                          WHERE pm.meta_key = %s
                            AND pm.meta_value IS NOT NULL
                            AND pm.meta_value != ''
                            AND pm.meta_value != '0'
                            AND p.post_type = %s
                            AND p.post_status IN ($status_placeholders)
                          GROUP BY pm.meta_value
                          ORDER BY latest_date DESC
                          LIMIT %d OFFSET %d";
            $args_users = array_merge(array('wcu_select_coupon_user', 'shop_coupon'), $statuses, array($per_page, $offset));
            $rows = $wpdb->get_results($wpdb->prepare($sql_users, $args_users));

            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $user_id = (int) $r->user_id;
                    $user = get_userdata($user_id);
                    if (!$user) { continue; }
                    $display_name = trim($user->first_name . ' ' . $user->last_name);
                    if ('' === $display_name) { $display_name = $user->display_name ?: $user->user_login; }
                    // Fetch latest coupon for this user for display
                    $coupon_post = get_posts(array(
                        'post_type' => 'shop_coupon',
                        'posts_per_page' => 1,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'meta_query' => array(
                            array(
                                'key' => 'wcu_select_coupon_user',
                                'value' => $user_id,
                                'compare' => '='
                            )
                        )
                    ));
                    $coupon_code = '';
                    $coupon_url = '';
                    if (!empty($coupon_post)) {
                        $c = $coupon_post[0];
                        $coupon_code = get_the_title($c->ID);
                        $coupon_url = admin_url('post.php?post=' . $c->ID . '&action=edit');
                    } else {
                        $coupon_code = __('Coupon unavailable', 'woo-coupon-usage');
                    }
                    $affiliate_url = admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id);
                    $html .= '<li class="wcusage-affiliates-list-item">'
                          . '<div class="wcusage-affiliates-list-row">'
                          . '<span class="wcusage-affiliates-avatar">' . wp_kses_post(get_avatar($user_id, 48, 'identicon', '', array('class' => 'wcusage-affiliates-avatar-img'))) . '</span>'
                          . '<div class="wcusage-affiliates-details">'
                          . '<a class="wcusage-affiliates-name" href="' . esc_url($affiliate_url) . '">' . esc_html($display_name) . '</a>'
                          . ( $coupon_url ? ('<a class="wcusage-affiliates-coupon" href="' . esc_url($coupon_url) . '">' . sprintf(esc_html__('Coupon: %1$s', 'woo-coupon-usage'), esc_html($coupon_code)) . '</a>')
                                          : ('<span class="wcusage-affiliates-coupon wcusage-affiliates-coupon--no-link">' . esc_html($coupon_code) . '</span>') )
                          . '</div></div></li>';
                }
            }
            
            // Cache the results for 1 hour
            set_transient($cache_key, array('html' => $html, 'total' => $total), HOUR_IN_SECONDS);
            break;

        case 'affiliates_top':
            // Check cache first for performance
            $cache_key = 'wcusage_dashboard_top_affiliates_' . $offset . '_' . $per_page;
            $cached_data = get_transient($cache_key);
            
            if ($cached_data !== false && isset($cached_data['html']) && isset($cached_data['total'])) {
                $html = $cached_data['html'];
                $total = $cached_data['total'];
                break;
            }
            
            // Build totals across coupons using batched approach to prevent memory issues
            $statuses = array('publish', 'pending', 'draft');
            $batch_size = 100;
            $batch_offset = 0;
            $top_totals = array();
            
            // First, count total coupons to process
            $count_query = new WP_Query(array(
                'post_type' => 'shop_coupon',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'post_status' => $statuses,
                'no_found_rows' => false,
                'suppress_filters' => true,
                'meta_query' => array(
                    array(
                        'key' => 'wcu_select_coupon_user',
                        'compare' => 'EXISTS',
                    ),
                ),
            ));
            $total_coupons = $count_query->found_posts;
            wp_reset_postdata();
            
            // Process in batches
            while ($batch_offset < $total_coupons) {
                $coupon_ids = get_posts(array(
                    'post_type' => 'shop_coupon',
                    'posts_per_page' => $batch_size,
                    'offset' => $batch_offset,
                    'fields' => 'ids',
                    'post_status' => $statuses,
                    'no_found_rows' => true,
                    'suppress_filters' => true,
                    'meta_query' => array(
                        array(
                            'key' => 'wcu_select_coupon_user',
                            'compare' => 'EXISTS',
                        ),
                    ),
                ));

                if (!empty($coupon_ids)) {
                    foreach ($coupon_ids as $cid) {
                        $u = (int) get_post_meta($cid, 'wcu_select_coupon_user', true);
                        if (!$u) { continue; }
                        $stats = get_post_meta($cid, 'wcu_alltime_stats', true);
                        if (empty($stats) || !is_array($stats)) { continue; }
                        $t_comm = isset($stats['total_commission']) ? (float) $stats['total_commission'] : 0.0;
                        $t_orders = isset($stats['total_orders']) ? (float) $stats['total_orders'] : 0.0;
                        $t_count = isset($stats['total_count']) ? (int) $stats['total_count'] : 0;
                        if (!isset($top_totals[$u])) {
                            $top_totals[$u] = array(
                                'total_commission' => 0.0,
                                'total_orders' => 0.0,
                                'total_count' => 0,
                            );
                        }
                        $top_totals[$u]['total_commission'] += $t_comm;
                        $top_totals[$u]['total_orders'] += $t_orders;
                        $top_totals[$u]['total_count'] += $t_count;
                    }
                }
                
                $batch_offset += $batch_size;
                wp_reset_postdata();
            }

            // Total count of affiliates with stats
            $total = (int) count($top_totals);
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }

            if (!empty($top_totals)) {
                uasort($top_totals, function ($a, $b) {
                    if ($a['total_commission'] === $b['total_commission']) { return 0; }
                    return ($a['total_commission'] < $b['total_commission']) ? 1 : -1;
                });
                $slice = array_slice($top_totals, $offset, $per_page, true);
                $rank = $offset + 1;
                foreach ($slice as $user_id => $totals) {
                    $user = get_userdata($user_id);
                    if (!$user) { continue; }
                    $display_name = trim($user->first_name . ' ' . $user->last_name);
                    if ('' === $display_name) { $display_name = $user->display_name ?: $user->user_login; }
                    $affiliate_url = admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id);
                    if (function_exists('wc_price')) {
                        $commission_formatted = wc_price($totals['total_commission']);
                    } elseif (function_exists('wcusage_format_price')) {
                        $commission_formatted = wcusage_format_price(number_format((float) $totals['total_commission'], 2, '.', ''));
                    } else {
                        $commission_formatted = esc_html(number_format_i18n((float) $totals['total_commission'], 2));
                    }
                    $html .= '<li class="wcusage-affiliates-list-item">'
                          . '<div class="wcusage-affiliates-list-row">'
                          . '<span class="wcusage-affiliates-rank">#' . esc_html($rank) . '</span>'
                          . '<span class="wcusage-affiliates-avatar">' . wp_kses_post(get_avatar($user_id, 48, 'identicon', '', array('class' => 'wcusage-affiliates-avatar-img'))) . '</span>'
                          . '<div class="wcusage-affiliates-details">'
                          . '<a class="wcusage-affiliates-name" href="' . esc_url($affiliate_url) . '">' . esc_html($display_name) . '</a>'
                          . '<span class="wcusage-affiliates-meta">' . esc_html__('Commission:', 'woo-coupon-usage') . ' '
                          . '<strong class="wcusage-affiliates-meta-amount">' . wp_kses_post($commission_formatted) . '</strong></span>'
                          . '</div></div></li>';
                    $rank++;
                }
            }
            
            // Cache the results for 1 hour
            set_transient($cache_key, array('html' => $html, 'total' => $total), HOUR_IN_SECONDS);
            break;
        case 'activity':
            $table = $wpdb->prefix . 'wcusage_activity';
            $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            // Clamp to last page if requested page is too large
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset));
            if (!empty($results)) {
                foreach ($results as $result) {
                    $the_date = $result->date;
                    $date = date_i18n('F jS', strtotime($the_date));
                    $time = gmdate('H:i', strtotime($the_date));
                    $event = $result->event;
                    $event_id = $result->event_id;
                    $info = $result->info;
                    if ($event === 'referral') {
                        $user_id = get_post_meta($event_id, 'wcusage_affiliate_user', true);
                    }
                    $event_message = wcusage_activity_message($event, $event_id, $info);
                    $html .= '<tr class="wcusage-admin-table-col-row">'
                          . '<td>' . esc_html($date) . ' (' . esc_html($time) . ')</td>'
                          . '<td>' . wp_kses_post($event_message) . '</td>'
                          . '</tr>';
                }
            }
            break;

        case 'referrals':
            $wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');
            $statuses = !$wcusage_field_order_type_custom ? array_diff_key(wc_get_order_statuses(), ['wc-refunded' => '']) : $wcusage_field_order_type_custom;
            // robust total via SQL count
            $allowed_statuses = array_keys($statuses);
            if (!empty($allowed_statuses)) {
                $status_placeholders = implode(',', array_fill(0, count($allowed_statuses), '%s'));
                $sql = "SELECT COUNT(DISTINCT p.ID)
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
                        WHERE p.post_type = %s
                        AND p.post_status IN ($status_placeholders)";
                $args = array_merge(array('wcusage_affiliate_user', 'shop_order'), $allowed_statuses);
                $total = (int) $wpdb->get_var($wpdb->prepare($sql, $args));
            } else {
                $total = 0;
            }
            // Clamp to last page if requested page is too large
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $orders = wc_get_orders(array(
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => array_keys($statuses),
                'meta_key' => 'wcusage_affiliate_user',
                'meta_compare' => 'EXISTS',
                'limit' => $per_page,
                'offset' => $offset,
            ));
            foreach ($orders as $order) {
                $order_id = $order->get_id();
                $orderinfo = wc_get_order($order_id);
                $calculateorder = wcusage_calculate_order_data($order_id, '', 0, 1);
                $order_date = get_the_time('F jS', $order_id);
                $status = $orderinfo ? $orderinfo->get_status() : '';
                $total_excl = $calculateorder['totalordersexcl'];
                $commission = $calculateorder['totalcommission'];
                $user_id = wcusage_order_meta($order_id, 'wcusage_affiliate_user');
                $user = get_userdata($user_id);
                $name = '';
                if ($user) {
                    $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
                }
                $html .= '<tr class="wcusage-admin-table-col-row">'
                      . '<td><a href="' . esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id)) . '" target="_blank" title="' . esc_attr($user ? $user->user_login : '') . '">' . esc_html($name) . '</a></td>'
                      . '<td>' . esc_html($order_date) . '</td>'
                      . '<td><a href="' . esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')) . '">#' . esc_html($order_id) . '</a></td>'
                      . '<td>' . wp_kses_post(wcusage_format_price(number_format($total_excl, 2, '.', ''))) . '</td>'
                      . '<td>' . wp_kses_post(wcusage_format_price(number_format($commission, 2, '.', ''))) . '</td>'
                      . '<td>' . ucfirst(esc_html($status)) . '</td>'
                      . '</tr>';
            }
            break;

        case 'visits':
            $table = $wpdb->prefix . 'wcusage_clicks';
            $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset));
            foreach ($results as $result) {
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                $coupon = get_the_title($result->couponid);
                $referrer = $result->referrer ? $result->referrer : '-';
                $converted = $result->converted ? 'yes' : 'no';
                $html .= '<tr class="wcusage-admin-table-col-row">'
                      . '<td>' . esc_html($date) . '</td>'
                      . '<td>' . esc_html($coupon) . '</td>'
                      . '<td>' . esc_html($referrer) . '</td>'
                      . '<td>' . ucfirst(esc_html($converted)) . '</td>'
                      . '</tr>';
            }
            break;

        case 'coupons':
            $count_query = new WP_Query(array(
                'post_type' => 'shop_coupon',
                'posts_per_page' => 1,
                'no_found_rows' => false,
                'paged' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'wcu_select_coupon_user',
                        'value' => '0',
                        'compare' => '>'
                    )
                )
            ));
            $total = (int) $count_query->found_posts;
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $coupons = get_posts(array(
                'post_type' => 'shop_coupon',
                'posts_per_page' => $per_page,
                'offset' => $offset,
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => 'wcu_select_coupon_user',
                        'value' => '0',
                        'compare' => '>'
                    )
                )
            ));
            foreach ($coupons as $coupon) {
                $coupon_id = $coupon->ID;
                $date = date_i18n('F jS (H:i)', strtotime($coupon->post_date));
                $user_id = get_post_meta($coupon_id, 'wcu_select_coupon_user', true);
                $user = get_userdata($user_id);
                $name = '';
                if ($user) {
                    $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
                }
                $coupon_info = wcusage_get_coupon_info_by_id($coupon_id);
                $uniqueurl = $coupon_info[4];
                $html .= '<tr class="wcusage-admin-table-col-row">'
                      . '<td><a href="' . esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id)) . '" target="_blank" title="' . esc_attr($name) . '">' . esc_html($name) . '</a></td>'
                      . '<td><a href="' . esc_url($uniqueurl) . '" target="_blank" title="' . esc_attr__('View Dashboard', 'woo-coupon-usage') . '">' . esc_html(get_the_title($coupon_id)) . '</a></td>'
                      . '<td>' . esc_html($date) . '</td>'
                      . '</tr>';
            }
            break;

        case 'registrations':
            $table = $wpdb->prefix . 'wcusage_register';
            $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table}` WHERE status = %s", 'pending'));
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table}` WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d", 'pending', $per_page, $offset));
            foreach ($results as $result) {
                $user = get_userdata($result->userid);
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                $name = '-';
                if ($user && isset($user->ID)) {
                    $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
                }
                $html .= '<tr class="wcusage-admin-table-col-row">'
                      . '<td><a href="' . esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $result->userid)) . '" target="_blank" title="' . esc_attr($name) . '">' . esc_html($name) . '</a></td>'
                      . '<td>' . esc_html($date) . '</td>'
                      . '<td>' . esc_html($result->couponcode) . '</td>'
                      . '<td>' . ucfirst(esc_html($result->status)) . '</td>'
                      . '</tr>';
            }
            break;

        case 'payouts':
            $table = $wpdb->prefix . 'wcusage_payouts';
            $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %s", 'pending'));
            if ($offset >= $total && $total > 0) {
                $page = (int) ceil($total / $per_page);
                $offset = ($page - 1) * $per_page;
            }
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d", 'pending', $per_page, $offset));
            foreach ($results as $result) {
                $user = get_userdata($result->userid);
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                $coupon = get_the_title($result->couponid) ?: '(MLA)';
                $name = $user ? (trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login) : '';
                $html .= '<tr class="wcusage-admin-table-col-row">'
                      . '<td><a href="' . esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $result->userid)) . '" target="_blank" title="' . esc_attr($user ? $user->user_login : '') . '">' . esc_html($name) . '</a></td>'
                      . '<td>' . esc_html($date) . '</td>'
                      . '<td>' . esc_html($coupon) . '</td>'
                      . '<td>' . wp_kses_post(wcusage_format_price(number_format($result->amount, 2, '.', ''))) . '</td>'
                      . '<td>' . ucfirst(esc_html($result->status)) . '</td>'
                      . '</tr>';
            }
            break;

        default:
            wp_send_json_error(array('message' => __('Invalid section', 'woo-coupon-usage')));
    }

    $has_prev = $page > 1;
    $has_next = ($offset + $per_page) < $total;

    wp_send_json_success(array(
        'html' => $html,
        'page' => $page,
        'has_prev' => $has_prev,
        'has_next' => $has_next,
        'total' => (int) $total,
    ));
}
add_action('wp_ajax_wcusage_dashboard_paginate', 'wcusage_dashboard_paginate_ajax');

function wcusage_render_dashboard_section($key) {
    if ($key === 'activity' && !wcusage_get_setting_value('wcusage_enable_activity_log', '1')) {
        return;
    }
    if ($key === 'visits' && !wcusage_get_setting_value('wcusage_field_show_click_history', 1)) {
        return;
    }
    if ($key === 'registrations' && !wcusage_get_setting_value('wcusage_field_registration_enable', '1')) {
        return;
    }
    if ($key === 'payouts' && !(function_exists('wcu_fs') && wcu_fs()->can_use_premium_code() && wcusage_get_setting_value('wcusage_field_tracking_enable', 1))) {
        return;
    }

    echo '<div class="wcusage-admin-page-col wcusage-dashboard-section-item" data-section-key="' . esc_attr($key) . '">';
    echo '<span class="wcusage-drag-handle fa-solid fa-grip-vertical" title="' . esc_attr__('Drag to reorder', 'woo-coupon-usage') . '"></span>';

    $title = '';
    $view_url = '';
    switch ($key) {
        case 'activity':
            $title = esc_html__('Recent Activity', 'woo-coupon-usage');
            $view_url = admin_url('admin.php?page=wcusage_activity');
            break;
        case 'referrals':
            $title = esc_html__('Latest Referrals', 'woo-coupon-usage');
            $view_url = admin_url('admin.php?page=wcusage_referrals');
            break;
        case 'visits':
            $title = esc_html__('Latest Referral Visits', 'woo-coupon-usage');
            $view_url = admin_url('admin.php?page=wcusage_clicks');
            break;
        case 'coupons':
            $title = sprintf(esc_html__('Newest %s Coupons', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliate', 'woo-coupon-usage')));
            $view_url = admin_url('admin.php?page=wcusage_coupons');
            break;
        case 'registrations':
            $title = sprintf(esc_html__('Pending %s Registrations', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliate', 'woo-coupon-usage')));
            $view_url = admin_url('admin.php?page=wcusage_registrations');
            break;
        case 'payouts':
            $title = esc_html__('Pending Payout Requests', 'woo-coupon-usage');
            $view_url = admin_url('admin.php?page=wcusage_payouts');
            break;
    }

    // Output section title with small "View All" button on the right
    echo '<h2 class="wcusage-section-title">' . esc_html($title) .
        ' <a href="' . esc_url($view_url) . '" class="button button-secondary button-small">' . esc_html__('View All', 'woo-coupon-usage') . ' <i class="fa-solid fa-arrow-right"></i></a>' .
        '</h2>';

    // Render the section content via hooks
    switch ($key) {
        case 'activity':
            do_action('wcusage_hook_dashboard_page_section_activity', '');
            break;
        case 'referrals':
            do_action('wcusage_hook_dashboard_page_section_referrals', '');
            break;
        case 'visits':
            do_action('wcusage_hook_dashboard_page_section_visits', '');
            break;
        case 'coupons':
            do_action('wcusage_hook_dashboard_page_section_coupons', '');
            break;
        case 'registrations':
            do_action('wcusage_hook_dashboard_page_section_registrations', '');
            break;
        case 'payouts':
            do_action('wcusage_hook_dashboard_page_section_payouts', '');
            break;
    }

    echo '</div>';
}

/**
 * Displays statistics section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_statistics', 'wcusage_dashboard_page_section_statistics' );
function wcusage_dashboard_page_section_statistics() {
    // Reordered to show "This Month" first and "Last 7 Days" last
    $date_ranges = array(
        'thismonth' => 'This Month',
        'lastmonth' => 'Last Month',
        'last7days' => 'Last 7 Days',
    );
?>

<div>
    <div class="stats-range-toggle">
        <?php foreach ($date_ranges as $key => $label): ?>
            <a data-range="<?php echo esc_attr($key); ?>" class="<?php echo $key === 'thismonth' ? 'active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="wcusage-stats-boxes-container">

        <div class="wcusage-info-box2 wcusage-info-box-usage">
            <p><span class="wcusage-info-box-title">Referrals:</span><span class="total-usage">0</span></p>
        </div>

        <div class="wcusage-info-box2 wcusage-info-box-sales">
            <p><span class="wcusage-info-box-title">Sales:</span><span class="total-sales">0</span></p>
        </div>

        <div class="wcusage-info-box2 wcusage-info-box-discounts">
            <p><span class="wcusage-info-box-title">Discounts:</span><span class="total-discounts">0</span></p>
        </div>

        <div class="wcusage-info-box2 wcusage-info-box-dollar">
            <p><span class="wcusage-info-box-title">Commission:</span><span class="total-commission">0</span></p>
        </div>

        <div class="wcusage-info-box2 wcusage-info-box-clicks">
            <p><span class="wcusage-info-box-title">Clicks:</span><span class="total-clicks">0</span></p>
        </div>

    </div>

</div>

<?php
}

/**
 * Displays activity section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_activity', 'wcusage_dashboard_page_section_activity' );
function wcusage_dashboard_page_section_activity() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_activity';
    $per_page = 5;
    
    // Check cache first
    $cache_key = 'wcusage_dashboard_activity_recent';
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        $get_activity = $cached_data['activities'];
        $has_next = $cached_data['has_next'];
        $total_count = isset($cached_data['total_count']) ? $cached_data['total_count'] : 0;
    } else {
        $get_activity = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d", $per_page)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        // Total count for pagination
        $total_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}"); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $has_next = ($total_count > $per_page);
        
        // Cache for 5 minutes
        set_transient($cache_key, array('activities' => $get_activity, 'has_next' => $has_next, 'total_count' => $total_count), 5 * MINUTE_IN_SECONDS);
    }
?>

<div>
    <?php if(!empty($get_activity)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th>Date</th>
                <th>Event</th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-activity">
            <?php
            foreach ($get_activity as $result) {
                $event_id = $result->event_id;
                $the_date = $result->date;
                $date = date_i18n('F jS', strtotime($the_date));
                $time = gmdate('H:i', strtotime($the_date));
                $user_id = $result->user_id;
                $user = get_userdata($user_id);
                $event = $result->event;
                $info = $result->info;

                if($event == "referral") {
                    $user_id = get_post_meta($event_id, 'wcusage_affiliate_user', true);
                }
                
                $name = "";
                if (is_object($user)) {
                    if (isset($user->first_name) || isset($user->last_name)) {
                        $name = trim($user->first_name . ' ' . $user->last_name);
                    }
                    if (empty($name)) {
                        $name = $user->user_login;
                    }
                }

                $event_message = wcusage_activity_message($event, $event_id, $info);
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><?php echo esc_html($date); ?> (<?php echo esc_html($time); ?>)</td>
                <td><?php echo wp_kses_post($event_message); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="activity" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo esc_html__('No recent activity found.', 'woo-coupon-usage'); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays referrals section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_referrals', 'wcusage_dashboard_page_section_referrals' );
function wcusage_dashboard_page_section_referrals() {
    $wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');
    $statuses = !$wcusage_field_order_type_custom ? array_diff_key(wc_get_order_statuses(), ['wc-refunded' => '']) : $wcusage_field_order_type_custom;

    $per_page = 5;
    $orders = wc_get_orders(array(
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => array_keys($statuses),
        'meta_key' => 'wcusage_affiliate_user',
        'meta_compare' => 'EXISTS',
        'limit' => $per_page,
    ));
    // Get total count for pagination (robust SQL count)
    global $wpdb;
    $allowed_statuses = array_keys($statuses);
    if (!empty($allowed_statuses)) {
        $status_placeholders = implode(',', array_fill(0, count($allowed_statuses), '%s'));
        $sql = "SELECT COUNT(DISTINCT p.ID)
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
                WHERE p.post_type = %s
                AND p.post_status IN ($status_placeholders)";
        $args = array_merge(array('wcusage_affiliate_user', 'shop_order'), $allowed_statuses);
        $total_count = (int) $wpdb->get_var($wpdb->prepare($sql, $args));
    } else {
        $total_count = 0;
    }
    $has_next = ($total_count > $per_page);
?>

<div>
    <?php if(!empty($orders)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th><?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))); ?></th>
                <th>Date</th>
                <th>Order ID</th>
                <th>Total</th>
                <th>Commission</th>
                <th>Status</th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-referrals">
            <?php
            foreach ($orders as $order) {
                $order_id = $order->get_id();
                $orderinfo = wc_get_order($order_id);
                $calculateorder = wcusage_calculate_order_data($order_id, '', 0, 1);
                $order_date = get_the_time('F jS', $order_id);
                $status = $orderinfo->get_status();
                $total = $calculateorder['totalordersexcl'];
                $commission = $calculateorder['totalcommission'];
                $user_id = wcusage_order_meta($order_id, 'wcusage_affiliate_user');
                $user = get_userdata($user_id);

                $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><a href="<?php echo esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id) ); ?>" title="<?php echo esc_html($user->user_login); ?>" target="_blank"><?php echo esc_html($name); ?></a></td>
                <td><?php echo esc_html($order_date); ?></td>
                <td><a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>">#<?php echo esc_html($order_id); ?></a></td>
                <td><?php echo wp_kses_post(wcusage_format_price(number_format($total, 2, '.', ''))); ?></td>
                <td><?php echo wp_kses_post(wcusage_format_price(number_format($commission, 2, '.', ''))); ?></td>
                <td><?php echo esc_html(ucfirst($status)); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="referrals" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo esc_html__('No recent referral orders found.', 'woo-coupon-usage'); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays visits section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_visits', 'wcusage_dashboard_page_section_visits' );
function wcusage_dashboard_page_section_visits() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_clicks';
    $per_page = 5;
    $get_visits = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d", $per_page)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}"); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $has_next = ($total_count > $per_page);
?>

<div>
    <?php if(!empty($get_visits)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th><?php echo esc_html__('Date', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Coupon', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Referrer Domain', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Converted', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-visits">
            <?php
            foreach ($get_visits as $result) {
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                $coupon = get_the_title($result->couponid);
                $referrer = $result->referrer;
                if(!$referrer) {
                    $referrer = '-';
                }
                $converted = $result->converted ? "yes" : "no";
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><?php echo esc_html($date); ?></td>
                <td><?php echo esc_html($coupon); ?></td>
                <td><?php echo esc_html($referrer); ?></td>
                <td><?php echo esc_html(ucfirst($converted)); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="visits" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo esc_html__('No recent clicks found.', 'woo-coupon-usage'); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays coupons section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_coupons', 'wcusage_dashboard_page_section_coupons' );
function wcusage_dashboard_page_section_coupons() {
    // Get custom terminology
    $custom_affiliate_term = wcusage_get_setting_value('wcusage_field_affiliate_term', 'Affiliate');
    
    $per_page = 5;
    $args = array(
        'post_type' => 'shop_coupon',
        'posts_per_page' => $per_page,
        'meta_query' => array(
            array(
                'key' => 'wcu_select_coupon_user',
                'value' => '0',
                'compare' => '>'
            )
        )
    );
    $coupons = get_posts($args);
    // Total count using WP_Query to access found_posts
    $count_args = $args;
    $count_args['posts_per_page'] = 1;
    $count_args['no_found_rows'] = false;
    $count_args['paged'] = 1;
    $count_query = new WP_Query($count_args);
    $total_count = (int) $count_query->found_posts;
    $has_next = ($total_count > $per_page);
?>

<div>
    <?php if(!empty($coupons)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th><?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))); ?></th>
                <th><?php echo esc_html__('Coupon', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Created', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-coupons">
            <?php
            foreach ($coupons as $coupon) {
                $coupon_id = $coupon->ID;
                $date = date_i18n('F jS (H:i)', strtotime($coupon->post_date));
                $user_id = get_post_meta($coupon_id, 'wcu_select_coupon_user', true);
                $user = get_userdata($user_id);
                $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
                $coupon_info = wcusage_get_coupon_info_by_id($coupon_id);
                $uniqueurl = $coupon_info[4];
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><a href="<?php echo esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id) ); ?>" title="<?php echo esc_html($name); ?>" target="_blank"><?php echo esc_html($name); ?></a></td>
                <td><a href="<?php echo esc_html($uniqueurl); ?>" title="View Dashboard" target="_blank"><?php echo esc_html(get_the_title($coupon_id)); ?></a></td>
                <td><?php echo esc_html($date); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="coupons" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo sprintf(esc_html__('No new %s coupons found.', 'woo-coupon-usage'), esc_html(strtolower(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))))); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays registrations section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_registrations', 'wcusage_dashboard_page_section_registrations' );
function wcusage_dashboard_page_section_registrations() {
    // Get custom terminology
    $custom_affiliate_term = wcusage_get_setting_value('wcusage_field_affiliate_term', 'Affiliate');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_register';
    $per_page = 5;
    $get_visits = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE status = %s ORDER BY id DESC LIMIT %d", 'pending', $per_page)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table_name}` WHERE status = %s", 'pending')); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $has_next = ($total_count > $per_page);
?>

<div>
    <?php if(!empty($get_visits)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th><?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))); ?></th>
                <th><?php echo esc_html__('Date', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Coupon', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Status', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-registrations">
            <?php
            foreach ($get_visits as $result) {
                $user = get_userdata($result->userid);
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                // If user is not found, skip this row
                if (isset($user->ID)) {
                    $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
                } else {
                    $name = '-';
                }
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><a href="<?php echo esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $result->userid) ); ?>" title="<?php echo esc_html($name); ?>" target="_blank"><?php echo esc_html($name); ?></a></td>
                <td><?php echo esc_html($date); ?></td>
                <td><?php echo esc_html($result->couponcode); ?></td>
                <td><?php echo esc_html(ucfirst($result->status)); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="registrations" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo sprintf(esc_html__('you have no pending %s registrations.', 'woo-coupon-usage'), esc_html(strtolower(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))))); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays payouts section on dashboard page.
 */
add_action( 'wcusage_hook_dashboard_page_section_payouts', 'wcusage_dashboard_page_section_payouts' );
function wcusage_dashboard_page_section_payouts() {
    // Get custom terminology
    $custom_affiliate_term = wcusage_get_setting_value('wcusage_field_affiliate_term', 'Affiliate');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_payouts';
    $per_page = 5;
    $get_visits = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE status = %s ORDER BY id DESC LIMIT %d", 'pending', $per_page)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'pending')); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $has_next = ($total_count > $per_page);
?>

<div>
    <?php if(!empty($get_visits)) { ?>
    <div class="wcusage-table-scroll">
    <table style="border: 2px solid #f3f3f3; width: 100%; text-align: center; border-collapse: collapse;">
        <thead>
            <tr class="wcusage-admin-table-col-head">
                <th><?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))); ?></th>
                <th><?php echo esc_html__('Date', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Coupon', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Amount', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Status', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
    <tbody id="wcusage-tbody-payouts">
            <?php
            foreach ($get_visits as $result) {
                $user = get_userdata($result->userid);
                $date = date_i18n('F jS (H:i)', strtotime($result->date));
                $coupon = get_the_title($result->couponid) ?: "(MLA)";
                $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->user_login;
            ?>
            <tr class="wcusage-admin-table-col-row">
                <td><a href="<?php echo esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $result->userid) ); ?>" title="<?php echo esc_html($user->user_login); ?>" target="_blank"><?php echo esc_html($name); ?></a></td>
                <td><?php echo esc_html($date); ?></td>
                <td><?php echo esc_html($coupon); ?></td>
                <td><?php echo wp_kses_post(wcusage_format_price(number_format($result->amount, 2, '.', ''))); ?></td>
                <td><?php echo esc_html(ucfirst($result->status)); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <div class="wcusage-pagination" data-section="payouts" data-page="1" data-per-page="<?php echo esc_attr($per_page); ?>" data-total="<?php echo esc_attr($total_count); ?>">
    <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
        <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
    <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
    </div>
    <?php } else { ?>
    <p><?php echo esc_html__('You have no pending payout requests.', 'woo-coupon-usage'); ?></p>
    <?php } ?>
</div>

<?php
}

/**
 * Displays dashboard page.
 */
function wcusage_dashboard_page_html() {
    if (!wcusage_check_admin_access()) {
        return;
    }
?>

<link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

<div class="wrap wcusage-admin-page">

    <?php do_action('wcusage_hook_dashboard_page_header', ''); ?>

    <?php if (class_exists('WooCommerce')) {
        global $wpdb;

    $affiliate_sidebar_total = 0;
    $affiliate_sidebar_latest = array();
    $affiliate_sidebar_statuses = array('publish', 'pending', 'draft');
    $affiliate_sidebar_meta_key = 'wcu_select_coupon_user';
    $affiliate_sidebar_latest_limit = 5;

        $affiliate_sidebar_top_limit = 5;
        $affiliate_sidebar_top_affiliates = array();
        $affiliate_sidebar_top_enabled = (bool) wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
        $affiliate_sidebar_top_notice = '';

        if (!empty($affiliate_sidebar_statuses)) {
            $status_placeholders = implode(',', array_fill(0, count($affiliate_sidebar_statuses), '%s'));

            $total_affiliates_query = "
                SELECT COUNT(DISTINCT pm.meta_value)
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = %s
                  AND pm.meta_value IS NOT NULL
                  AND pm.meta_value != ''
                  AND pm.meta_value != '0'
                  AND p.post_type = %s
                  AND p.post_status IN ($status_placeholders)
            ";

            $total_affiliates_args = array_merge(
                array($affiliate_sidebar_meta_key, 'shop_coupon'),
                $affiliate_sidebar_statuses
            );

            $affiliate_sidebar_total = (int) $wpdb->get_var(
                $wpdb->prepare($total_affiliates_query, $total_affiliates_args)
            );

            // Get latest affiliate users by newest coupons (limit 5)
            $latest_coupon_args = array(
                'post_type'      => 'shop_coupon',
                'posts_per_page' => $affiliate_sidebar_latest_limit,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post_status'    => $affiliate_sidebar_statuses,
                'meta_query'     => array(
                    array(
                        'key'     => $affiliate_sidebar_meta_key,
                        'value'   => '0',
                        'compare' => '>',
                    ),
                ),
            );
            $latest_coupons = get_posts($latest_coupon_args);

            $affiliate_sidebar_latest = array();
            $latest_user_ids = array();
            foreach ($latest_coupons as $coupon) {
                $coupon_id = $coupon->ID;
                $user_id = (int) get_post_meta($coupon_id, $affiliate_sidebar_meta_key, true);
                if (!$user_id || in_array($user_id, $latest_user_ids)) {
                    continue;
                }
                $latest_user_ids[] = $user_id;
                $user = get_userdata($user_id);
                if (!$user) {
                    continue;
                }
                $display_name = trim($user->first_name . ' ' . $user->last_name);
                if ('' === $display_name) {
                    $display_name = $user->display_name ?: $user->user_login;
                }
                $coupon_code = get_the_title($coupon_id);
                $affiliate_sidebar_latest[] = array(
                    'user_id'       => $user_id,
                    'name'          => $display_name,
                    'coupon_code'   => $coupon_code,
                    'coupon_id'     => $coupon_id,
                    'affiliate_url' => admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id),
                    'coupon_url'    => admin_url('post.php?post=' . $coupon_id . '&action=edit'),
                );
                if (count($affiliate_sidebar_latest) >= $affiliate_sidebar_latest_limit) {
                    break;
                }
            }
        }

        if ($affiliate_sidebar_top_enabled && !empty($affiliate_sidebar_statuses)) {
            // Check cache first for performance
            $sidebar_cache_key = 'wcusage_dashboard_sidebar_top_affiliates_' . $affiliate_sidebar_top_limit;
            $cached_sidebar_data = get_transient($sidebar_cache_key);
            
            if ($cached_sidebar_data !== false && is_array($cached_sidebar_data)) {
                $affiliate_sidebar_top_affiliates = $cached_sidebar_data['affiliates'];
                $affiliate_sidebar_top_total_all = $cached_sidebar_data['total'];
            } else {
                // Use batched approach to prevent memory issues with large datasets
                $batch_size = 100;
                $offset = 0;
                $top_affiliates_totals = array();
            
            do {
                $top_affiliate_coupon_args = array(
                    'post_type'      => 'shop_coupon',
                    'posts_per_page' => $batch_size,
                    'offset'         => $offset,
                    'fields'         => 'ids',
                    'post_status'    => $affiliate_sidebar_statuses,
                    'no_found_rows'  => false,
                    'suppress_filters' => true,
                    'meta_query'     => array(
                        array(
                            'key'     => $affiliate_sidebar_meta_key,
                            'compare' => 'EXISTS',
                        ),
                    ),
                );

                $top_affiliate_query = new WP_Query($top_affiliate_coupon_args);
                $top_affiliate_coupon_ids = $top_affiliate_query->posts;
                $total_coupons = $top_affiliate_query->found_posts;

                if (!empty($top_affiliate_coupon_ids)) {
                    foreach ($top_affiliate_coupon_ids as $coupon_id) {
                        $coupon_user_id = (int) get_post_meta($coupon_id, $affiliate_sidebar_meta_key, true);
                        if (!$coupon_user_id) {
                            continue;
                        }

                        $all_time_stats = get_post_meta($coupon_id, 'wcu_alltime_stats', true);
                        if (empty($all_time_stats) || !is_array($all_time_stats)) {
                            continue;
                        }

                        $total_commission = isset($all_time_stats['total_commission']) ? (float) $all_time_stats['total_commission'] : 0.0;
                        $total_orders = isset($all_time_stats['total_orders']) ? (float) $all_time_stats['total_orders'] : 0.0;
                        $total_count = isset($all_time_stats['total_count']) ? (int) $all_time_stats['total_count'] : 0;

                        if (!isset($top_affiliates_totals[$coupon_user_id])) {
                            $top_affiliates_totals[$coupon_user_id] = array(
                                'total_commission' => 0.0,
                                'total_orders'     => 0.0,
                                'total_count'      => 0,
                            );
                        }

                        $top_affiliates_totals[$coupon_user_id]['total_commission'] += $total_commission;
                        $top_affiliates_totals[$coupon_user_id]['total_orders'] += $total_orders;
                        $top_affiliates_totals[$coupon_user_id]['total_count'] += $total_count;
                    }
                }

                $offset += $batch_size;
                wp_reset_postdata();
            } while ($offset < $total_coupons);

            if (!empty($top_affiliates_totals)) {
                // Save total count BEFORE slicing for sidebar pagination totals
                $affiliate_sidebar_top_total_all = count($top_affiliates_totals);
                uasort($top_affiliates_totals, function ($a, $b) {
                    if ($a['total_commission'] === $b['total_commission']) {
                        return 0;
                    }
                    return ($a['total_commission'] < $b['total_commission']) ? 1 : -1;
                });

                $top_affiliates_totals = array_slice($top_affiliates_totals, 0, $affiliate_sidebar_top_limit, true);

                foreach ($top_affiliates_totals as $user_id => $totals) {
                    $user = get_userdata($user_id);
                    if (!$user) {
                        continue;
                    }

                    $display_name = trim($user->first_name . ' ' . $user->last_name);
                    if ('' === $display_name) {
                        $display_name = $user->display_name ?: $user->user_login;
                    }

                    $formatted_commission = '';
                    if (function_exists('wc_price')) {
                        $formatted_commission = wc_price($totals['total_commission']);
                    } elseif (function_exists('wcusage_format_price')) {
                        $formatted_commission = wcusage_format_price(number_format((float) $totals['total_commission'], 2, '.', ''));
                    } else {
                        $formatted_commission = esc_html(number_format_i18n((float) $totals['total_commission'], 2));
                    }

                    $affiliate_sidebar_top_affiliates[] = array(
                        'user_id'             => $user_id,
                        'name'                => $display_name,
                        'total_commission'    => (float) $totals['total_commission'],
                        'commission_formatted'=> $formatted_commission,
                        'affiliate_url'       => admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id),
                    );
                }
            }
            
            // Cache the sidebar results for 1 hour
            set_transient($sidebar_cache_key, array(
                'affiliates' => $affiliate_sidebar_top_affiliates,
                'total' => $affiliate_sidebar_top_total_all
            ), HOUR_IN_SECONDS);
        }
    }
    
    if (!$affiliate_sidebar_top_enabled) {
        $affiliate_sidebar_top_notice = esc_html__('Enable "All Time Stats" in the plugin settings to view top affiliate performance.', 'woo-coupon-usage');
    }
    ?>

        <div class="wcusage-admin-dashboard-layout">
            <div class="wcusage-admin-dashboard-main">
                <div class="wcusage-admin-page-col-section" style="margin-top: -20px;">
                    <div class="wcusage-admin-page-col" style="width: 100%;">
                        <h2><?php printf(esc_html__('%s Program Statistics', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )))); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_admin_reports')); ?>" style="text-decoration: none; float: right; font-size: 14px;"
                        class="button button-secondary button-large">
                            <?php echo esc_html__('View Full Report', 'woo-coupon-usage'); ?> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <button type="button" id="wcusage-clear-cache-btn" class="button button-secondary button-large" style="float: right; margin-top: 0px; margin-right: 5px; font-size: 14px;" title="<?php echo esc_attr__('Clear all dashboard caches to refresh statistics', 'woo-coupon-usage'); ?>">
                            <span class="fa-solid fa-sync"></span> <?php echo esc_html__('Clear Cache', 'woo-coupon-usage'); ?>
                        </button></h2>
                        <?php do_action('wcusage_hook_dashboard_page_section_statistics', ''); ?>
                    </div>

                    <?php
                    // Render sortable dashboard sections based on user preference
                    $sections_order = wcusage_get_user_dashboard_section_order(get_current_user_id());
                    foreach ($sections_order as $section_key) {
                        wcusage_render_dashboard_section($section_key);
                    }
                    ?>
                </div>
            </div>
            <?php
            $affiliate_sidebar_singular = wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ));
            $affiliate_sidebar_plural = wcusage_get_affiliate_text(__( 'Affiliates', 'woo-coupon-usage' ), true);
            $affiliate_sidebar_plural_lower = function_exists('mb_strtolower') ? mb_strtolower($affiliate_sidebar_plural) : strtolower($affiliate_sidebar_plural);
            $affiliate_sidebar_singular_label = esc_html($affiliate_sidebar_singular);
            $affiliate_sidebar_plural_label = esc_html($affiliate_sidebar_plural);
            $affiliate_sidebar_plural_lower_label = esc_html($affiliate_sidebar_plural_lower);
            ?>
            <aside class="wcusage-admin-dashboard-sidebar">
                <div class="wcusage-affiliates-sidebar">
                    <h2 style="margin: 0;"><?php printf(esc_html__('%s Overview', 'woo-coupon-usage'), esc_html($affiliate_sidebar_plural_label)); ?></h2>

                    <?php if (!empty($affiliate_sidebar_latest)) { ?>

                    <div class="wcusage-affiliates-total-card"
                    style="display: flex; align-items: center; justify-content: center; gap: 18px;
                    border-radius: 12px; box-shadow: 0 2px 12px rgba(40,167,69,0.08); padding: 22px 0; margin-bottom: 18px;">
                        <span class="wcusage-affiliates-total-icon" style="display: flex; align-items: center; justify-content: center; background: #333; color: #fff; border-radius: 50%; width: 54px; height: 54px; font-size: 2.1em; box-shadow: 0 2px 8px rgba(40,167,69,0.12);">
                            <span class="fa-solid fa-user-group"></span>
                        </span>
                        <div style="display: flex; flex-direction: column; align-items: flex-start;">
                            <span class="wcusage-affiliates-total-label" style="font-size: 16px; line-height: 28px; color: #333; font-weight: 500;">
                                <?php printf(esc_html__('Total %s', 'woo-coupon-usage'), esc_html($affiliate_sidebar_plural_label)); ?>:
                            </span>
                            <span class="wcusage-affiliates-total-number" style="font-size: 32px; color: #333; margin-bottom: 2px; line-height: 1;">
                                <?php echo esc_html(number_format_i18n($affiliate_sidebar_total)); ?>
                            </span>
                        </div>
                    </div>

                    <?php } else { ?>

                        <div class="wcusage-affiliates-no-affiliates" style="margin-top: 22px; margin-bottom: 18px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px; padding: 18px 22px; text-align: center;">
                            <span class="fa-solid fa-user-plus" style="font-size: 22px; color: #f39c12; margin-bottom: 8px;"></span><br>
                            <strong><?php echo esc_html(str_replace('%s', esc_html($affiliate_sidebar_plural_label), esc_html__('You have no %s yet.', 'woo-coupon-usage'))); ?></strong><br>
                            <br/>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_add_affiliate')); ?>" class="button button-primary" style="font-size: 15px; padding: 8px 22px; border-radius: 6px;">
                                <span class="fa-solid fa-user-plus" style="margin-right: 7px;"></span> <?php echo esc_html(str_replace('%s', esc_html($affiliate_sidebar_singular_label), esc_html__('Add New %s', 'woo-coupon-usage'))); ?>
                            </a>
                        </div>

                    <?php } ?>

                    <a class="wcusage-affiliates-manage-link button button-secondary button-large"
                    style="text-decoration: none; margin-bottom: 18px; display: block; text-align: center; color: #333;
                    font-size: 14px; border: 1px solid #c3c4c7; border-radius: 2px;"
                    href="<?php echo esc_url(admin_url('admin.php?page=wcusage_affiliates')); ?>">
                        <span class="fa-solid fa-users" style="margin-right: 7px;"></span> <?php printf(esc_html__('Manage %s', 'woo-coupon-usage'), esc_html($affiliate_sidebar_plural_label)); ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a class="wcusage-affiliates-manage-link button button-secondary button-large"
                    style="text-decoration: none; display: block; text-align: center; color: #333;
                    font-size: 14px; border: 1px solid #c3c4c7; border-radius: 2px;"
                    href="<?php echo esc_url(admin_url('admin.php?page=wcusage_add_affiliate')); ?>">
                        <span class="fa-solid fa-user-plus" style="margin-right: 7px;"></span> <?php printf(esc_html__('Add New %s', 'woo-coupon-usage'), esc_html($affiliate_sidebar_singular_label)); ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <hr style="margin: 22px 0 19px 0;">

                    <div class="wcusage-affiliates-section">
                        <h3 class="wcusage-affiliates-section-title"><?php printf(esc_html__('Latest %2$s', 'woo-coupon-usage'), (int)$affiliate_sidebar_latest_limit, esc_html($affiliate_sidebar_plural_label)); ?></h3>

                        <ul id="wcusage-list-affiliates_latest" class="wcusage-affiliates-list wcusage-affiliates-list--latest">
                            <?php
                            $latest_count = !empty($affiliate_sidebar_latest) ? count($affiliate_sidebar_latest) : 0;
                            $latest_to_show = 5;
                            if (!empty($affiliate_sidebar_latest)) {
                                foreach ($affiliate_sidebar_latest as $affiliate_item) {
                                    $coupon_text = $affiliate_item['coupon_code'];
                                    if ('' === $coupon_text) {
                                        $coupon_text = __('Coupon unavailable', 'woo-coupon-usage');
                                    }
                                    ?>
                                    <li class="wcusage-affiliates-list-item">
                                        <div class="wcusage-affiliates-list-row">
                                            <span class="wcusage-affiliates-avatar"><?php echo wp_kses_post(get_avatar($affiliate_item['user_id'], 48, 'identicon', '', array('class' => 'wcusage-affiliates-avatar-img'))); ?></span>
                                            <div class="wcusage-affiliates-details">
                                                <a class="wcusage-affiliates-name" href="<?php echo esc_url($affiliate_item['affiliate_url']); ?>"><?php echo esc_html($affiliate_item['name']); ?></a>
                                                <?php if (!empty($affiliate_item['coupon_id']) && !empty($affiliate_item['coupon_url'])) : ?>
                                                    <a class="wcusage-affiliates-coupon" href="<?php echo esc_url($affiliate_item['coupon_url']); ?>">
                                                        <?php printf(
                                                            esc_html__('Coupon: %1$s', 'woo-coupon-usage'),
                                                            esc_html($coupon_text)
                                                        ); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <span class="wcusage-affiliates-coupon wcusage-affiliates-coupon--no-link"><?php echo esc_html($coupon_text); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                }
                            }
                            // Add placeholders if less than 5
                            for ($i = $latest_count; $i < $latest_to_show; $i++) {
                                // Generate random gravatar hash
                                $rand_email = md5(uniqid(wp_rand(), true));
                                $rand_name = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, wp_rand(5,8));
                                $rand_coupon = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, wp_rand(6,10));
                                ?>
                                <li class="wcusage-affiliates-list-item wcusage-affiliates-list-item--placeholder">
                                    <div class="wcusage-affiliates-list-row">
                                        <span class="wcusage-affiliates-avatar">
                                            <span class="wcusage-affiliates-avatar-img wcusage-affiliates-avatar-img--blur" style="display: flex; align-items: center; justify-content: center; background: #f0f0f1; color: #c3c4c7; font-size: 24px;"><i class="fa-solid fa-user"></i></span>
                                        </span>
                                        <div class="wcusage-affiliates-details">
                                            <span class="wcusage-affiliates-name wcusage-affiliates-name--blur"><?php echo esc_html($rand_name); ?></span>
                                            <span class="wcusage-affiliates-coupon wcusage-affiliates-coupon--blur"><?php echo esc_html($rand_coupon); ?></span>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>

                        <?php
                        // Pagination for latest affiliates
                        $latest_total = (int) $affiliate_sidebar_total; // total distinct affiliate users
                        $latest_has_next = ($latest_total > $affiliate_sidebar_latest_limit);
                        ?>
                        <div class="wcusage-pagination" data-section="affiliates_latest" data-page="1" data-per-page="<?php echo esc_attr($affiliate_sidebar_latest_limit); ?>" data-total="<?php echo esc_attr($latest_total); ?>">
                            <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
                            <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
                            <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $latest_has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
                        </div>
                    </div>

                    <hr style="margin: 20px 0 22px 0;">

                    <div class="wcusage-affiliates-section" style="margin-bottom: -20px;">
                        <h3 class="wcusage-affiliates-section-title"><?php printf(esc_html__('Top %1$s', 'woo-coupon-usage'), esc_html($affiliate_sidebar_plural_label)); ?></h3>
                        <?php
                        // Use total BEFORE slicing if available to determine pagination
                        $affiliate_sidebar_top_total = isset($affiliate_sidebar_top_total_all)
                            ? (int) $affiliate_sidebar_top_total_all
                            : (is_array($affiliate_sidebar_top_affiliates) ? count($affiliate_sidebar_top_affiliates) : 0);
                        ?>

                        <ul id="wcusage-list-affiliates_top" class="wcusage-affiliates-list wcusage-affiliates-list--top">
                            <?php
                            $top_count = !empty($affiliate_sidebar_top_affiliates) ? count($affiliate_sidebar_top_affiliates) : 0;
                            $top_to_show = 5;
                            $affiliate_rank = 1;
                            if (!empty($affiliate_sidebar_top_affiliates)) {
                                foreach ($affiliate_sidebar_top_affiliates as $affiliate_item) {
                                    ?>
                                    <li class="wcusage-affiliates-list-item">
                                        <div class="wcusage-affiliates-list-row">
                                            <span class="wcusage-affiliates-rank">#<?php echo esc_html($affiliate_rank); ?></span>
                                            <span class="wcusage-affiliates-avatar"><?php echo wp_kses_post(get_avatar($affiliate_item['user_id'], 48, 'identicon', '', array('class' => 'wcusage-affiliates-avatar-img'))); ?></span>
                                            <div class="wcusage-affiliates-details">
                                                <a class="wcusage-affiliates-name" href="<?php echo esc_url($affiliate_item['affiliate_url']); ?>"><?php echo esc_html($affiliate_item['name']); ?></a>
                                                <span class="wcusage-affiliates-meta">
                                                    <?php echo esc_html__('Commission:', 'woo-coupon-usage'); ?>
                                                    <strong class="wcusage-affiliates-meta-amount"><?php echo wp_kses_post($affiliate_item['commission_formatted']); ?></strong>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                    $affiliate_rank++;
                                }
                            }
                            // Add placeholders if less than 5
                            for ($i = $top_count; $i < $top_to_show; $i++) {
                                $rand_email = md5(uniqid(wp_rand(), true));
                                $rand_name = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, wp_rand(5,8));
                                $rand_coupon = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, wp_rand(6,10));
                                ?>
                                <li class="wcusage-affiliates-list-item wcusage-affiliates-list-item--placeholder">
                                    <div class="wcusage-affiliates-list-row">
                                        <span class="wcusage-affiliates-rank">#<?php echo esc_html($affiliate_rank); ?></span>
                                        <span class="wcusage-affiliates-avatar">
                                            <span class="wcusage-affiliates-avatar-img wcusage-affiliates-avatar-img--blur" style="display: flex; align-items: center; justify-content: center; background: #f0f0f1; color: #c3c4c7; font-size: 24px;"><i class="fa-solid fa-user"></i></span>
                                        </span>
                                        <div class="wcusage-affiliates-details">
                                            <span class="wcusage-affiliates-name wcusage-affiliates-name--blur"><?php echo esc_html($rand_name); ?></span>
                                            <span class="wcusage-affiliates-meta wcusage-affiliates-meta--blur">
                                                <?php echo esc_html__('Commission:', 'woo-coupon-usage'); ?>
                                                <strong class="wcusage-affiliates-meta-amount">--</strong>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                <?php
                                $affiliate_rank++;
                            }
                            ?>
                        </ul>

                        <?php $top_has_next = ($affiliate_sidebar_top_total > $affiliate_sidebar_top_limit); ?>
                        <div class="wcusage-pagination" data-section="affiliates_top" data-page="1" data-per-page="<?php echo esc_attr($affiliate_sidebar_top_limit); ?>" data-total="<?php echo esc_attr($affiliate_sidebar_top_total); ?>">
                            <button type="button" class="button button-secondary button-small wcusage-page-prev" aria-label="<?php echo esc_attr__('Previous Page', 'woo-coupon-usage'); ?>" disabled><span class="fa-solid fa-arrow-left" aria-hidden="true"></span></button>
                            <span class="wcusage-page-indicator" aria-live="polite">Page 1</span>
                            <button type="button" class="button button-secondary button-small wcusage-page-next" aria-label="<?php echo esc_attr__('Next Page', 'woo-coupon-usage'); ?>"<?php echo $top_has_next ? '' : ' disabled'; ?>><span class="fa-solid fa-arrow-right" aria-hidden="true"></span></button>
                        </div>
                    </div>

                </div>
            </aside>
        </div>
    <?php } else {
        $path = 'woocommerce/woocommerce.php';
        $installed_plugins = get_plugins();
        if(isset($installed_plugins[$path])) {
            $activate_url = wp_nonce_url('plugins.php?action=activate&plugin=' . $path, 'activate-plugin_' . $path);
            echo '<p style="font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce is installed but not activated. <a href="' . esc_url($activate_url) . '">Click here to activate it.</a></strong></p>';
        } else {
            $install_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce');
            echo '<p style="margin-left: 20px; font-size: 15px; color: red;"><strong><span class="dashicons dashicons-bell"></span> WooCommerce needs to be installed for this plugin to work. <a href="' . esc_url($install_url) . '">Click here to install it.</a></strong></p>';
        }
    } ?>
</div>

<?php
}