<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Render pagination controls
if (!function_exists('wcusage_render_pagination')) {
function wcusage_render_pagination($type, $page, $per_page, $total) {
    $total_pages = max(1, (int) ceil($total / $per_page));
    if ($total_pages <= 1) {
        return;
    }
    ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo esc_html($total); ?> <?php echo esc_html__('items', 'woo-coupon-usage'); ?></span>
            <span class="pagination-links" data-type="<?php echo esc_attr($type); ?>">
                <a class="first-page button" data-page="1" aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">«</a>
                <a class="prev-page button" data-page="<?php echo esc_attr(max(1, $page - 1)); ?>" aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">‹</a>
                <span class="paging-input">
                    <input class="current-page" type="text" size="2" value="<?php echo esc_attr($page); ?>" aria-label="Current page"> of <span class="total-pages"><?php echo esc_html($total_pages); ?></span>
                </span>
                <a class="next-page button" data-page="<?php echo esc_attr(min($total_pages, $page + 1)); ?>" aria-disabled="<?php echo $page >= $total_pages ? 'true' : 'false'; ?>">›</a>
                <a class="last-page button" data-page="<?php echo esc_attr($total_pages); ?>" aria-disabled="<?php echo $page >= $total_pages ? 'true' : 'false'; ?>">»</a>
            </span>
        </div>
    </div>
    <?php
}
}

// Referrals table
if (!function_exists('wcusage_affiliate_referrals_table')) {
function wcusage_affiliate_referrals_table($user_id, $page = 1, $per_page = 20, $start_date = '', $end_date = '') {
    global $wpdb;

    $coupons = wcusage_get_users_coupons_ids($user_id);
    if (empty($coupons)) {
        echo '<p>' . esc_html__('No coupons assigned to this affiliate.', 'woo-coupon-usage') . '</p>';
        return;
    }

    $coupon_codes = array();
    foreach ($coupons as $coupon_id) {
        $coupon_code = get_the_title($coupon_id);
        if ($coupon_code) {
            $coupon_codes[] = $coupon_code;
        }
    }
    if (empty($coupon_codes)) {
        echo '<p>' . esc_html__('No valid coupon codes found for this affiliate.', 'woo-coupon-usage') . '</p>';
        return;
    }

    $placeholders = array_fill(0, count($coupon_codes), '%s');
    $in_clause = '(' . implode(',', $placeholders) . ')';

    $statuses = wc_get_order_statuses();
    if (isset($statuses['wc-refunded'])) {
        unset($statuses['wc-refunded']);
    }

    $where_date = '';
    $params = $coupon_codes;
    if (!empty($start_date)) {
        $where_date .= " AND p.post_date >= %s";
        $params[] = $start_date . ' 00:00:00';
    }
    if (!empty($end_date)) {
        $where_date .= " AND p.post_date <= %s";
        $params[] = $end_date . ' 23:59:59';
    }

    $page = max(1, intval($page));
    $per_page = max(1, intval($per_page));
    $offset = ($page - 1) * $per_page;

    $count_sql = $wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID)
         FROM {$wpdb->prefix}posts AS p
         INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi
            ON p.ID = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name IN $in_clause
         WHERE p.post_type = 'shop_order'
           AND p.post_status IN ('" . implode("','", array_keys($statuses)) . "')" . $where_date,
        $params
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval($wpdb->get_var($count_sql)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    $list_sql = $wpdb->prepare(
        "SELECT DISTINCT p.ID AS order_id, p.post_date AS order_date
        FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi
            ON p.ID = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name IN $in_clause
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('" . implode("','", array_keys($statuses)) . "')" . $where_date .
        " ORDER BY p.post_date DESC LIMIT %d OFFSET %d",
        array_merge($params, array($per_page, $offset))
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $orders = $wpdb->get_results($list_sql); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    if (empty($orders)) {
        echo '<p>' . esc_html__('No recent referrals found for this affiliate\'s coupons. This could mean that the assigned coupons have not been used in any orders yet, or the orders are still pending.', 'woo-coupon-usage') . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__('Order ID', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Date', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Customer', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Coupon Code', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Total', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Commission', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Status', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order_data): ?>
                <?php
                $order_id = $order_data->order_id;
                $order = wc_get_order($order_id);
                if (!$order) continue;
                $commission = wcusage_order_meta($order_id, 'wcusage_total_commission');
                $billing_first_name = $order->get_billing_first_name();
                $billing_last_name = $order->get_billing_last_name();
                $customer_name = trim($billing_first_name . ' ' . $billing_last_name);
                if (empty($customer_name)) { $customer_name = esc_html__('Guest', 'woo-coupon-usage'); }
                $coupon_code = '';
                $used_coupons = $order->get_coupon_codes();
                if (!empty($used_coupons)) { $coupon_code = $used_coupons[0]; }
                ?>
                <tr>
                    <td><a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>">#<?php echo esc_html($order_id); ?></a></td>
                    <td><?php echo esc_html($order->get_date_created()->date_i18n(get_option('date_format'))); ?></td>
                    <td><?php echo esc_html($customer_name); ?></td>
                    <td><?php echo esc_html($coupon_code); ?></td>
                    <td><?php echo wp_kses_post(wcusage_format_price($order->get_total())); ?></td>
                    <td><?php echo wp_kses_post(wcusage_format_price($commission)); ?></td>
                    <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php wcusage_render_pagination('referrals', $page, $per_page, $total); ?>
    <?php
}
}

// Visits table
if (!function_exists('wcusage_affiliate_visits_table')) {
function wcusage_affiliate_visits_table($user_id, $page = 1, $per_page = 20, $start_date = '', $end_date = '') {
    global $wpdb;

    $coupons = wcusage_get_users_coupons_ids($user_id);
    if (empty($coupons)) {
        echo '<p>' . esc_html__('No coupons assigned to this affiliate.', 'woo-coupon-usage') . '</p>';
        return;
    }

    $table_name = $wpdb->prefix . 'wcusage_clicks';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) { // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        echo '<div class="notice notice-info"><p>';
        echo esc_html__('Click tracking is not currently enabled.', 'woo-coupon-usage');
        echo '<br><br>';
        echo sprintf(
            esc_html__('To enable click tracking, go to %s and enable the "Click Tracking" option.', 'woo-coupon-usage'),
            '<a href="' . esc_url(admin_url('admin.php?page=wcusage_settings')) . '">' . esc_html__('Settings', 'woo-coupon-usage') . '</a>'
        );
        echo '</p></div>';
        return;
    }

    $placeholders = array_fill(0, count($coupons), '%d');
    $in_clause = '(' . implode(',', $placeholders) . ')';

    $where_date = '';
    $params = $coupons;
    if (!empty($start_date)) { $where_date .= " AND date >= %s"; $params[] = $start_date . ' 00:00:00'; }
    if (!empty($end_date)) { $where_date .= " AND date <= %s"; $params[] = $end_date . ' 23:59:59'; }

    $page = max(1, intval($page));
    $per_page = max(1, intval($per_page));
    $offset = ($page - 1) * $per_page;

    $count_sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE couponid IN $in_clause" . $where_date,
        $params
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval($wpdb->get_var($count_sql)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    $list_sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE couponid IN $in_clause" . $where_date . " ORDER BY date DESC LIMIT %d OFFSET %d",
        array_merge($params, array($per_page, $offset))
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $clicks = $wpdb->get_results($list_sql); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    if (empty($clicks)) {
        echo '<p>' . esc_html__('No recent visits found for this affiliate\'s coupons.', 'woo-coupon-usage') . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped wcusage-visits-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('ID', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html(sprintf(esc_html__('%s Coupon', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliate', 'woo-coupon-usage')))); ?></th>
                <th><?php echo esc_html__('Landing Page', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Referrer URL', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('IP Address', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Visit Date', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Converted', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Action', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clicks as $click): ?>
                <?php
                $coupon_title = '';
                $coupon_edit_link = '';
                $uniqueurl = '';
                if ($click->couponid) {
                    $coupon_title = get_the_title($click->couponid);
                    $coupon_info = wcusage_get_coupon_info_by_id($click->couponid);
                    $uniqueurl = isset($coupon_info[4]) ? $coupon_info[4] : '';
                    $coupon_edit_link = admin_url("post.php?post=" . $click->couponid . "&action=edit&classic-editor");
                }
                $landing_page_title = '';
                if ($click->page) {
                    $landing_page_title = get_the_title($click->page);
                    if (empty($landing_page_title)) { $landing_page_title = esc_html__('Unknown Page', 'woo-coupon-usage'); }
                }
                $referrer_display = $click->referrer;
                if (empty($referrer_display)) { $referrer_display = '<em>' . esc_html__('Direct', 'woo-coupon-usage') . '</em>'; }
                $visit_datetime = strtotime($click->date);
                $formatted_date = date_i18n("M jS, Y (g:ia)", $visit_datetime);
                $is_converted = !empty($click->orderid);
                ?>
                <tr>
                    <td><?php echo esc_html($click->id); ?></td>
                    <td>
                        <?php if ($coupon_title): ?>
                            <a href="<?php echo esc_url($uniqueurl); ?>" target="_blank" title="<?php echo esc_attr(sprintf(__('View %s Dashboard', 'woo-coupon-usage'), wcusage_get_affiliate_text(__('Affiliate', 'woo-coupon-usage')))); ?>">
                                <?php echo esc_html($coupon_title); ?>
                            </a>
                        <?php else: ?>
                            <em><?php echo esc_html__('Unknown', 'woo-coupon-usage'); ?></em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($click->page && $landing_page_title): ?>
                            <a href="<?php echo esc_url(get_permalink($click->page)); ?>" target="_blank" title="<?php echo esc_attr__('View Landing Page', 'woo-coupon-usage'); ?>">
                                <?php echo esc_html($landing_page_title); ?>
                            </a>
                        <?php else: ?>
                            <em><?php echo esc_html__('Unknown', 'woo-coupon-usage'); ?></em>
                        <?php endif; ?>
                    </td>
                    <td><?php echo wp_kses_post($referrer_display); ?></td>
                    <td>
                        <code style="background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 12px;">
                            <?php echo esc_html($click->ipaddress); ?>
                        </code>
                    </td>
                    <td><?php echo esc_html($formatted_date); ?></td>
                    <td>
                        <?php if ($is_converted): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php echo esc_html__('Yes', 'woo-coupon-usage'); ?>
                            <?php if (!empty($click->orderid)): ?>
                                <br/><a href="<?php echo esc_url(get_edit_post_link($click->orderid)); ?>" target="_blank">
                                    #<?php echo esc_html($click->orderid); ?>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                            <?php echo esc_html__('No', 'woo-coupon-usage'); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" id="submitclick">
                            <input type="text" id="wcu-id" name="wcu-id" value="<?php echo esc_attr($click->id); ?>" style="display: none;">
                            <input type="text" id="wcu-status-delete" name="wcu-status-delete" value="cancel" style="display: none;">
                            <?php wp_nonce_field('delete_url'); ?>
                            <button onClick="return confirm('Are you sure you want to delete visit #<?php echo esc_attr($click->id); ?>?');"
                                title="<?php echo esc_attr__('Delete this visit.', 'woo-coupon-usage'); ?>"
                                type="submit" name="submitclickdelete" style="padding: 0; background: 0; border: 0; cursor: pointer; margin-bottom: 5px; color: #B52828;">
                                <i class="fa-solid fa-trash-can"></i> <?php echo esc_html__('Delete', 'woo-coupon-usage'); ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php wcusage_render_pagination('visits', $page, $per_page, $total); ?>
    <?php
}
}

// Payouts table
if (!function_exists('wcusage_affiliate_payouts_table')) {
function wcusage_affiliate_payouts_table($user_id, $page = 1, $per_page = 20, $start_date = '', $end_date = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_payouts';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) { // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        echo '<p>' . esc_html__('Payouts system not enabled or table not found.', 'woo-coupon-usage') . '</p>';
        return;
    }
    // Determine if Files column should be shown (based on settings similar to admin payouts page)
    $payouts_enable_invoices = function_exists('wcusage_get_setting_value') ? wcusage_get_setting_value('wcusage_field_payouts_enable_invoices', '0') : '0';
    $payouts_enable_statements = function_exists('wcusage_get_setting_value') ? wcusage_get_setting_value('wcusage_field_payouts_enable_statements', '0') : '0';
    $show_files_column = ($payouts_enable_invoices || $payouts_enable_statements) ? true : false;
    $where_date = '';
    $params = array($user_id);
    if (!empty($start_date)) { $where_date .= " AND date >= %s"; $params[] = $start_date . ' 00:00:00'; }
    if (!empty($end_date)) { $where_date .= " AND date <= %s"; $params[] = $end_date . ' 23:59:59'; }

    $page = max(1, intval($page));
    $per_page = max(1, intval($per_page));
    $offset = ($page - 1) * $per_page;

    $count_sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE userid = %d" . $where_date,
        $params
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval($wpdb->get_var($count_sql)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    $list_sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE userid = %d" . $where_date . " ORDER BY id DESC LIMIT %d OFFSET %d",
        array_merge($params, array($per_page, $offset))
    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $payouts = $wpdb->get_results($list_sql); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    if (empty($payouts)) {
        echo '<p>' . esc_html__('No payout history found.', 'woo-coupon-usage') . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__('ID', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Coupon', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Amount', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Method', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Status', 'woo-coupon-usage'); ?></th>
                <?php if ($show_files_column): ?>
                    <th><?php echo esc_html__('Files', 'woo-coupon-usage'); ?></th>
                <?php endif; ?>
                <th><?php echo esc_html__('Date Requested', 'woo-coupon-usage'); ?></th>
                <th><?php echo esc_html__('Date Paid', 'woo-coupon-usage'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payouts as $payout): ?>
                <?php
                $status_class = '';
                switch ($payout->status) {
                    case 'paid': $status_class = 'status-completed'; break;
                    case 'pending': $status_class = 'status-on-hold'; break;
                    case 'cancel': $status_class = 'status-cancelled'; break;
                    default: $status_class = 'status-processing'; break;
                }
                $coupon_title = '';
                if ($payout->couponid) { $coupon_title = get_the_title($payout->couponid); }
                // Build files column content similar to admin payouts list
                $files_html = '';
                if ($show_files_column && function_exists('wcusage_files_downloads_buttons')) {
                    $files_html = wcusage_files_downloads_buttons(
                        isset($payout->invoiceid) ? $payout->invoiceid : 0,
                        $payout->id,
                        1,   // always_invoice (show placeholder when enabled but missing)
                        1,   // show_text
                        0,   // download (open in new tab by default)
                        isset($payout->status) ? $payout->status : '',
                        1    // showpending
                    );
                }
                ?>
                <tr>
                    <td><?php echo esc_html($payout->id); ?></td>
                    <td><?php echo esc_html($coupon_title); ?></td>
                    <td><?php echo wp_kses_post(wcusage_format_price($payout->amount)); ?></td>
                    <td><?php echo esc_html($payout->method); ?></td>
                    <td><span class="order-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($payout->status)); ?></span></td>
                    <?php if ($show_files_column): ?>
                        <td><?php echo wp_kses_post($files_html); ?></td>
                    <?php endif; ?>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payout->date))); ?></td>
                    <td><?php echo ($payout->status === 'paid' && !empty($payout->datepaid)) ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payout->datepaid))) : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php wcusage_render_pagination('payouts', $page, $per_page, $total); ?>
    <?php
}
}

// AJAX handlers – loadable on admin-ajax.php
add_action('wp_ajax_wcusage_get_affiliate_referrals', function() {
    check_ajax_referer('wcusage_affiliate_referrals', '_wpnonce');
    if (!wcusage_check_admin_access()) { wp_die('Access denied'); }
    $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $per_page = isset($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20;
    $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
    $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';
    if (!$user_id) { wp_die('Invalid user ID'); }
    wcusage_affiliate_referrals_table($user_id, $page, $per_page, $start_date, $end_date);
    wp_die();
});

add_action('wp_ajax_wcusage_get_affiliate_visits', function() {
    check_ajax_referer('wcusage_affiliate_visits', '_wpnonce');
    if (!wcusage_check_admin_access()) { wp_die('Access denied'); }
    $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $per_page = isset($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20;
    $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
    $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';
    if (!$user_id) { wp_die('Invalid user ID'); }
    wcusage_affiliate_visits_table($user_id, $page, $per_page, $start_date, $end_date);
    wp_die();
});

add_action('wp_ajax_wcusage_get_affiliate_payouts', function() {
    check_ajax_referer('wcusage_affiliate_payouts', '_wpnonce');
    if (!wcusage_check_admin_access()) { wp_die('Access denied'); }
    $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $per_page = isset($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20;
    $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
    $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';
    if (!$user_id) { wp_die('Invalid user ID'); }
    wcusage_affiliate_payouts_table($user_id, $page, $per_page, $start_date, $end_date);
    wp_die();
});

// Activity table
if (!function_exists('wcusage_affiliate_activity_table')) {
function wcusage_affiliate_activity_table($user_id, $page = 1, $per_page = 20, $start_date = '', $end_date = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_activity';

    // Build filters
    $where = ' WHERE user_id = %d';
    $params = array($user_id);
    if (!empty($start_date)) { $where .= ' AND date >= %s'; $params[] = $start_date . ' 00:00:00'; }
    if (!empty($end_date)) { $where .= ' AND date <= %s'; $params[] = $end_date . ' 23:59:59'; }

    // Pagination
    $page = max(1, intval($page));
    $per_page = max(1, intval($per_page));
    $offset = ($page - 1) * $per_page;

    // Count
    $count_sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name" . $where, $params); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval($wpdb->get_var($count_sql)); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    // Fetch
    $list_sql = $wpdb->prepare("SELECT * FROM $table_name" . $where . " ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($params, array($per_page, $offset))); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $activities = $wpdb->get_results($list_sql, ARRAY_A); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

    if (empty($activities)) {
        echo '<p>' . esc_html__('No activity found for this affiliate.', 'woo-coupon-usage') . '</p>';
        return;
    }
    ?>
    <div style="margin-top: 20px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Date', 'woo-coupon-usage'); ?></th>
                    <th><?php echo esc_html__('Event', 'woo-coupon-usage'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td>
                            <?php echo esc_html(date_i18n('F j, Y (H:i)', strtotime($activity['date']))); ?>
                        </td>
                        <td>
                            <?php
                            $event_message = wcusage_activity_message($activity['event'], $activity['event_id'], $activity['info']);
                            echo wp_kses_post($event_message);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php wcusage_render_pagination('activity', $page, $per_page, $total); ?>
    </div>
    <?php
}
}

// AJAX: Activity list
add_action('wp_ajax_wcusage_get_affiliate_activity', function() {
    check_ajax_referer('wcusage_affiliate_activity', '_wpnonce');
    if (!wcusage_check_admin_access()) { wp_die('Access denied'); }
    $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $per_page = isset($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20;
    $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
    $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';
    if (!$user_id) { wp_die('Invalid user ID'); }
    wcusage_affiliate_activity_table($user_id, $page, $per_page, $start_date, $end_date);
    wp_die();
});
