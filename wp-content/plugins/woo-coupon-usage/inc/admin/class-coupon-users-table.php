<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Coupon_Users_Table extends WP_List_Table {
	
	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular' => 'affiliateuser',
			'plural'   => 'affiliateusers',
			'ajax'     => false,
		) );
	}
	
	function get_columns() {

        $column['cb'] = '<input type="checkbox" />';
        $column['ID'] = esc_html__('ID', 'woo-coupon-usage');
        $column['Username'] = esc_html__('Username', 'woo-coupon-usage');
        
        $column['roles'] = esc_html__('Group / Role', 'woo-coupon-usage');

        $all_stats = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
        $wcusage_field_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '0');
        if($wcusage_field_hide_all_time) {
            $all_stats = 0;
        }

        if($all_stats) {

            $column['usage'] = esc_html__( 'Total Referrals', 'woo-coupon-usage');

            $column['sales'] = esc_html__( 'Total Sales', 'woo-coupon-usage');

            $column['commission'] = esc_html__( 'Total Commission', 'woo-coupon-usage');

        } else {

            $column['usage'] = esc_html__( 'Total Coupon Usage', 'woo-coupon-usage');

        }

        if( wcu_fs()->can_use_premium_code() ) {
            $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
            if ($wcusage_tracking_enable) {
                $column['unpaidcommission'] = 'Payouts' . wcusage_admin_tooltip(esc_html__('• Unpaid: Earned from completed orders but not yet paid.', 'woo-coupon-usage') . '<br/>' . esc_html__('• Pending: Payout requests currently awaiting approval.', 'woo-coupon-usage') . '<br/>' . esc_html__('• Paid: Successfully paid to affiliate.', 'woo-coupon-usage'));
            }
        }

        if( wcu_fs()->can_use_premium_code() ) {
            $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
            $system = wcusage_get_setting_value('wcusage_field_storecredit_system', 'default');
            $storecredit_users_col = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_users_col', 1);
            if($credit_enable && $storecredit_users_col && $system == "default") {
                $credit_label = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_only', esc_html__( 'Store Credit', 'woo-coupon-usage'));
                $column['affiliatestorecredit'] = $credit_label;
            }
        }

        $column['affiliateinfo'] = 'Affiliate Coupons';

        if( wcu_fs()->can_use_premium_code() ) {
            $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
            if($wcusage_field_mla_enable) {
                $column['mlacommission'] = 'Total MLA Commission';
                $column['affiliatemla'] = 'MLA Dashboard';
            }
        }

        $column['view_affiliate'] = esc_html__('Actions', 'woo-coupon-usage');

        return $column;

	}

    // Add dropdown for filtering by role
    function extra_tablenav( $which ) {
        if ( $which == "top" ) {
            $roles = get_editable_roles();
            
            $current_role = '';
            if(isset($_POST['filter_role'])) {
                $current_role = sanitize_text_field($_POST['role']);
            } else {
                if ( isset($_GET['role']) ) {
                    $current_role = sanitize_text_field( wp_unslash( $_GET['role'] ) );
                }   
            }

            // Move all roles with "coupon_affiliate" prefix to the top of the list
            $roles = array_merge(
                array_filter($roles, function($role) {
                    return strpos($role, 'coupon_affiliate') === 0;
                }, ARRAY_FILTER_USE_KEY),
                array_filter($roles, function($role) {
                    return strpos($role, 'coupon_affiliate') !== 0;
                }, ARRAY_FILTER_USE_KEY)
            );

            // Add "(Group)" to the start of the name if role key starts with "coupon_affiliate"
            foreach ($roles as $role => $details) {
                if (strpos($role, 'coupon_affiliate') === 0) {
                    $roles[$role]['name'] = '(Group) ' . $details['name'];
                }
            }

            // Get current sort option
            $current_sort = '';
            if(isset($_POST['filter_sort'])) {
                $current_sort = sanitize_text_field($_POST['sort_by']);
            } else {
                if ( isset($_GET['sort_by']) ) {
                    $current_sort = sanitize_text_field( wp_unslash( $_GET['sort_by'] ) );
                }
            }

            ?>
            <div class="alignleft actions">
                    <?php
                    // Retain other $_GET parameters in the form submission (like the page identifier)
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'role' && $key !== 'filter_role' && $key !== 'sort_by' && $key !== 'filter_sort') {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr( is_array($value) ? '' : wp_unslash( $value ) ) . '">';
                        }
                    }
                    ?>
                    <select name="role">
                        <option value=""><?php esc_html_e('All Groups & Roles', 'woo-coupon-usage'); ?></option>
                        <?php foreach ($roles as $role => $details) { ?>
                            <option value="<?php echo esc_attr($role); ?>" <?php selected($role, $current_role); ?>><?php echo esc_html($details['name']); ?></option>
                        <?php } ?>
                    </select>
                    <input type="submit" name="filter_role" id="post-query-submit" class="button" value="<?php esc_html_e('Filter', 'woo-coupon-usage'); ?>">
            </div>
            <div class="alignleft actions" style="margin-left: 0px;">
                    <select name="sort_by">
                        <option value=""><?php esc_html_e('Sort by...', 'woo-coupon-usage'); ?></option>
                        <option value="ID" <?php selected('ID', $current_sort); ?>><?php esc_html_e('ID', 'woo-coupon-usage'); ?></option>
                        <option value="total_referrals" <?php selected('total_referrals', $current_sort); ?>><?php esc_html_e('Total Referrals', 'woo-coupon-usage'); ?></option>
                        <option value="total_sales" <?php selected('total_sales', $current_sort); ?>><?php esc_html_e('Total Sales', 'woo-coupon-usage'); ?></option>
                        <option value="total_commission" <?php selected('total_commission', $current_sort); ?>><?php esc_html_e('Total Commission', 'woo-coupon-usage'); ?></option>
                        <?php if (wcu_fs()->can_use_premium_code()): ?>
                            <?php $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0'); ?>
                            <?php if ($wcusage_tracking_enable): ?>
                                <option value="unpaid_commission" <?php selected('unpaid_commission', $current_sort); ?>><?php esc_html_e('Commission Payouts', 'woo-coupon-usage'); ?></option>
                            <?php endif; ?>
                        <?php endif; ?>
                    </select>
                    <input type="submit" name="filter_sort" id="sort-query-submit" class="button" value="<?php esc_html_e('Sort', 'woo-coupon-usage'); ?>">
            </div>
            <?php
        }
    }
	
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
        );    
    }

    function get_bulk_actions() {
        $affiliate_text = wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ));
        $affiliates_text = wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ), true);
        
        $actions = [
            'bulk-delete-users' => 'Delete ' . $affiliate_text . ' Users',
            'bulk-delete-all' => 'Delete ' . $affiliate_text . ' Users and Coupons',
            'bulk-unassign' => 'Unassign Coupons from ' . $affiliate_text . ' Users',
            'bulk-delete-coupons' => 'Delete Coupons',
        ];

        return $actions;
    }

	function prepare_items() {

        $this->_column_headers = array($this->get_columns(), array(), array());

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$per_page = 25;
		$current_page = $this->get_pagenum();

        $search_query = isset($_POST['s']) ? trim($_POST['s']) : '';
        $search_query = sanitize_text_field($search_query);

        $role = '';
        if(isset($_POST['filter_role'])) {
            $role = sanitize_text_field($_POST['role']);
        } else {
            if ( isset($_GET['role']) ) {
                $role = sanitize_text_field( wp_unslash( $_GET['role'] ) );
            }
        }

        $sort_by = '';
        if(isset($_POST['filter_sort'])) {
            $sort_by = sanitize_text_field($_POST['sort_by']);
        } else {
            if ( isset($_GET['sort_by']) ) {
                $sort_by = sanitize_text_field( wp_unslash( $_GET['sort_by'] ) );
            }
        }
        
        $users = $this->get_coupon_users( $search_query, $role, $sort_by );
    
        $total_items = count( $users );
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ) );
    
        $this->items = array_slice( $users, ( ( $current_page - 1 ) * $per_page ), $per_page );

        $this->process_bulk_action();
        $this->process_individual_delete_action();

	}

    function process_bulk_action() {
        
        // Check nonce for security (support legacy and current action names)
        $nonce_valid = false;
        if ( isset( $_POST['_wcusage_bulk_nonce'] ) ) {
            $nonce_value = sanitize_text_field( wp_unslash( $_POST['_wcusage_bulk_nonce'] ) );
            // Current
            if ( wp_verify_nonce( $nonce_value, 'wcusage_coupon_users_bulk_action' ) ) {
                $nonce_valid = true;
            }
            // Legacy/fallback
            if ( ! $nonce_valid && wp_verify_nonce( $nonce_value, 'wcusage_affiliates_bulk_action' ) ) {
                $nonce_valid = true;
            }
        }
        if ( ! $nonce_valid ) {
            return;
        }

        // Check if the user has permission to perform the action
        if ( ! wcusage_check_admin_access() ) {
            return;
        }

        if ( 'bulk-delete-users' === $this->current_action() ) {
            $delete_ids = isset($_POST['bulk-delete']) ? array_map( 'absint', (array) $_POST['bulk-delete'] ) : array();
            foreach ( $delete_ids as $id ) {
                if ( $id != get_current_user_id() ) {
                    wp_delete_user( $id );
                }
            }
        }

        if ( 'bulk-delete-all' === $this->current_action() ) {
            $delete_ids = isset($_POST['bulk-delete']) ? array_map( 'absint', (array) $_POST['bulk-delete'] ) : array();
            foreach ( $delete_ids as $id ) {
                if ( $id != get_current_user_id() ) {
                    wp_delete_user( $id );
                }
                $coupons = wcusage_get_users_coupons_ids( $id );
                foreach ($coupons as $coupon) {
                    wp_delete_post( $coupon );
                }
            }
        }

        if ( 'bulk-unassign' === $this->current_action() ) {
            $delete_ids = isset($_POST['bulk-delete']) ? array_map( 'absint', (array) $_POST['bulk-delete'] ) : array();
            foreach ( $delete_ids as $id ) {
                $coupons = wcusage_get_users_coupons_ids( $id );
                foreach ($coupons as $coupon) {
                    $coupon_id = $coupon;
                    $coupon = new WC_Coupon($coupon_id);
                    $coupon->update_meta_data('wcu_select_coupon_user', '');
                    $coupon->save();
                }
            }
        }

        if ( 'bulk-delete-coupons' === $this->current_action() ) {
            $delete_ids = isset($_POST['bulk-delete']) ? array_map( 'absint', (array) $_POST['bulk-delete'] ) : array();
            foreach ( $delete_ids as $id ) {
                $coupons = wcusage_get_users_coupons_ids( $id );
                foreach ($coupons as $coupon) {
                    wp_delete_post( $coupon );
                }
            }
        }

    }

    // Process individual delete actions
    function process_individual_delete_action() {
        if ( isset( $_POST['wcusage_delete_action'] ) && isset( $_POST['wcusage_user_id'] ) ) {
            $action = sanitize_text_field( $_POST['wcusage_delete_action'] );
            $user_id = absint( $_POST['wcusage_user_id'] );
            $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            
            // Verify nonce
            if ( ! wp_verify_nonce( $nonce, 'wcusage_delete_user_' . $user_id ) ) {
                wp_die( 'Security check failed' );
            }
            
            // Check permissions
            if ( ! wcusage_check_admin_access() ) {
                wp_die( 'Insufficient permissions' );
            }
            
            // Prevent self-deletion
            if ( $user_id == get_current_user_id() ) {
                wp_die( 'You cannot delete your own account' );
            }
            
            $coupons = wcusage_get_users_coupons_ids( $user_id );
            
            switch ( $action ) {
                case 'delete_user':
                    wp_delete_user( $user_id );
                    $message = 'User deleted successfully.';
                    break;
                    
                case 'delete_user_coupons':
                    wp_delete_user( $user_id );
                    foreach ( $coupons as $coupon ) {
                        wp_delete_post( $coupon );
                    }
                    $message = 'User and associated coupons deleted successfully.';
                    break;
                    
                case 'unassign_coupons':
                    foreach ( $coupons as $coupon ) {
                        $coupon_obj = new WC_Coupon( $coupon );
                        $coupon_obj->update_meta_data( 'wcu_select_coupon_user', '' );
                        $coupon_obj->save();
                    }
                    $message = 'Coupons unassigned from user successfully.';
                    break;
                    
                case 'delete_coupons':
                    foreach ( $coupons as $coupon ) {
                        wp_delete_post( $coupon );
                    }
                    $message = 'User\'s coupons deleted successfully.';
                    break;
                    
                default:
                    $message = 'Invalid action.';
                    break;
            }
            
            // Redirect with success message
            $redirect_url = add_query_arg( 'wcusage_message', urlencode( $message ), wp_get_referer() );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    function get_coupon_users($search_query = '', $role = '', $sort_by = '') {
        return wcusage_get_coupon_users($search_query, $role, $sort_by);
    }
    
	function column_default( $item, $column_name ) {
        $user_id = $item['ID'];

        // Usage
        $coupons = wcusage_get_users_coupons_ids( $user_id );
        $total_referrals = 0;
        $usage = 0;
        foreach ($coupons as $coupon) {
            $all_stats = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
            $wcusage_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '0');
            $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
            if($all_stats && !$wcusage_hide_all_time && isset($wcu_alltime_stats) && isset($wcu_alltime_stats['total_count'])) {
                $usage = $wcu_alltime_stats['total_count'];
            }
            if(!$usage) {
                global $woocommerce;
                $coupon_code = get_the_title($coupon);
                $c = wcusage_get_coupon_object_safe($coupon_code);
                if(!$c) {
                    continue;
                }
                $usage = $c->get_usage_count();
            }
            if($usage) {
                $total_referrals += $usage;
            }
        }

        $qmessage = esc_html__('The affiliate dashboard for this coupon needs to be loaded at-least once.', 'woo-coupon-usage');

    // Switch
        $coupons = wcusage_get_users_coupons_ids( $user_id );
		switch ( $column_name ) {
			case 'ID':
        $view_url = esc_url(admin_url( 'admin.php?page=wcusage_view_affiliate&user_id=' . $user_id ));
        $alt = isset($item['name']) ? $item['name'] : '';
        $avatar = get_avatar( $user_id, 40, 'identicon', $alt, array( 'class' => 'wcusage-avatar' ) );
        return '<div class="wcusage-idcell"><a href="' . $view_url . '" class="wcusage-avatar-link" 
        itle="' . esc_attr__( 'View Affiliate', 'woo-coupon-usage' ) . '">' . $avatar . '</a><a href="' . $view_url . '" class="wcusage-id-link">#' . $item[ $column_name ] . '</a></div>';
            case 'Username':
                return wcusage_output_affiliate_tooltip_user_info($user_id);
            case 'roles':
                return ucwords( str_replace( '_', ' ', $item[ $column_name ] ) ); // Capitalize and separate with spaces
            case 'affiliateinfo':
                $theoutput = "";
                $coupon_count = count($coupons);
                
                // Add wrapper div for scrolling if more than 2 coupons
                if ($coupon_count > 2) {
                    $theoutput .= '<div class="wcusage-affiliate-coupons-wrapper">';
                    $theoutput .= '<div class="wcusage-affiliate-coupons-scrollable">';
                }
                
                foreach ($coupons as $coupon) {
                    $theoutput .= wcusage_output_affiliate_tooltip_users($coupon);
                }
                
                // Close wrapper divs if they were added
                if ($coupon_count > 2) {
                    $theoutput .= '</div>'; // Close scrollable
                    $theoutput .= '</div>'; // Close wrapper
                }
                
                return $theoutput;
            case 'unpaidcommission':
                // Check if PRO version and tracking is enabled
                if (!wcu_fs()->can_use_premium_code()) {
                    return '';
                }
                $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
                if (!$wcusage_tracking_enable) {
                    return '';
                }
                
                global $wpdb;
                $payouts_table = $wpdb->prefix . 'wcusage_payouts';
                $coupons = wcusage_get_users_coupons_ids( $user_id );
                $unpaid_commission = 0;
                $total_commission = 0; // Still accumulate total commission for fallback when payouts table absent
                foreach ($coupons as $coupon) {
                    $unpaid_commission += (float) get_post_meta($coupon, 'wcu_text_unpaid_commission', true);
                    $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                    if ( $wcu_alltime_stats && isset( $wcu_alltime_stats['total_commission'] ) ) {
                        $total_commission += (float) $wcu_alltime_stats['total_commission'];
                    }
                }

                // Default values
                $paid_commission = 0;
                $pending_payments = 0;
                $processing_payments = 0; // Not displayed yet, but available for future use

                $payouts_table_exists = ( $wpdb->get_var("SHOW TABLES LIKE '$payouts_table'") == $payouts_table ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                if ( $payouts_table_exists ) {
                    $totals = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT
                                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) AS paid_total,
                                COALESCE(SUM(CASE WHEN status = 'processing' THEN amount END), 0) AS processing_total,
                                COALESCE(SUM(CASE WHEN status IN ('pending','created') THEN amount END), 0) AS pending_total
                             FROM $payouts_table
                             WHERE userid = %d",
                            $user_id
                        )
                    ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                    if ( $totals ) {
                        $paid_commission      = (float) $totals->paid_total;
                        $processing_payments  = (float) $totals->processing_total; // reserved
                        $pending_payments     = (float) $totals->pending_total;
                    }
                } else {
                    // Fallback paid calculation when payouts table not present
                    $paid_commission = $total_commission - $unpaid_commission;
                    if ( $paid_commission < 0 ) { $paid_commission = 0; }
                }
                
                $output = '<div style="line-height: 1.4;">';
                $output .= '<div><strong>Unpaid:</strong> ' . wcusage_format_price($unpaid_commission) . '</div>';
                $output .= '<hr style="margin: 2px 0; border: 0; border-top: 1px solid #ddd;">';
                $output .= '<div><strong>Pending:</strong> ' . wcusage_format_price($pending_payments) . '</div>';
                $output .= '<hr style="margin: 2px 0; border: 0; border-top: 1px solid #ddd;">';
                $output .= '<div><strong>Paid:</strong> ' . wcusage_format_price($paid_commission) . '</div>';
                $output .= '</div>';
                return $output;
            case 'usage':
                return $total_referrals;
            case 'sales':
                $coupons = wcusage_get_users_coupons_ids( $user_id );
                $total_sales = 0;
                $sales = 0;
                if(!$coupons) {
                    return wcusage_format_price($sales);
                }
                foreach ($coupons as $coupon) {
                    $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                    if($wcu_alltime_stats) {
                        if(isset($wcu_alltime_stats['total_orders'])) {
                            $sales = $wcu_alltime_stats['total_orders'];
                        }
                        if(isset($wcu_alltime_stats['full_discount'])) {
                            $discounts = $wcu_alltime_stats['full_discount'];
                            $sales = (float)$sales - (float)$discounts;
                        }
                    }
                    if($sales) {
                        $total_sales += (float)$sales;
                    }
                }
                if($total_referrals > 0 && !$total_sales) {
                    return "<span title='".$qmessage."'><strong><i class='fa-solid fa-ellipsis'></i></strong></span></a>";
                }
                return wcusage_format_price($sales);
            case 'commission':
                $theoutput = "";
                $coupons = wcusage_get_users_coupons_ids( $user_id );
                $total_commission = 0;
                $commission = 0;
                foreach ($coupons as $coupon) {
                    $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                    if($wcu_alltime_stats && isset($wcu_alltime_stats['total_commission'])) {
                        $commission = $wcu_alltime_stats['total_commission'];
                        if($commission) {
                            $total_commission += $wcu_alltime_stats['total_commission'];
                        }
                    }
                }
                if($total_referrals > 0 && !$total_commission) {
                    return "<span title='".$qmessage."'><strong><i class='fa-solid fa-ellipsis'></i></strong></span></a>";
                }
                return wcusage_format_price($total_commission);
            case 'mlacommission':
                $total_commission = wcusage_mla_total_earnings($user_id);
                return wcusage_format_price($total_commission);
            case 'affiliatemla':
                if( wcu_fs()->can_use_premium_code() ) {
                    $theoutput = "";
                    $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
                    if($wcusage_field_mla_enable) {
                        $dash_page_id = wcusage_get_mla_shortcode_page_id();
                        $dash_page = get_page_link($dash_page_id);
                        $user_info = get_userdata($user_id);
                        $theoutput = '<a href="'.$dash_page.'?user='.$user_info->user_login.'" title="View MLA Dashboard" target="_blank">MLA <span class="dashicons dashicons-external"></span></a>';
                    }
                    return $theoutput;     
                }   
            case 'affiliatestorecredit':
                    if( wcu_fs()->can_use_premium_code() ) {
                    $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
                    if( $credit_enable && function_exists( 'wcusage_get_credit_users_balance' ) ) {
                        $balance = wcusage_format_price( wcusage_get_credit_users_balance( $user_id ) );
                        return $balance;
                    } else {
                        return "";
                    }
                }
            case 'view_affiliate':
                $view_url = esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id));
                $delete_nonce = wp_create_nonce('wcusage_delete_user_' . $user_id);
                
                $output = '<div class="wcusage-user-actions">';
                $output .= '<a href="' . $view_url . '" class="button button-primary">' . esc_html__('View', 'woo-coupon-usage') . '</a> ';
                $output .= '<div class="wcusage-delete-dropdown">';
                $output .= '<button type="button" class="wcusage-delete-btn" data-user-id="' . $user_id . '" title="' . esc_attr__('Delete Options', 'woo-coupon-usage') . '">';
                $output .= '<span class="dashicons dashicons-trash"></span>';
                $output .= '</button>';
                $output .= '<div class="wcusage-delete-menu" style="display: none;">';
                $output .= '<a href="#" class="wcusage-delete-option" data-action="delete_user" data-user-id="' . $user_id . '" data-nonce="' . $delete_nonce . '">' . esc_html__('Delete User', 'woo-coupon-usage') . '</a>';
                $output .= '<a href="#" class="wcusage-delete-option" data-action="delete_user_coupons" data-user-id="' . $user_id . '" data-nonce="' . $delete_nonce . '">' . esc_html__('Delete User & Coupons', 'woo-coupon-usage') . '</a>';
                $output .= '<a href="#" class="wcusage-delete-option" data-action="unassign_coupons" data-user-id="' . $user_id . '" data-nonce="' . $delete_nonce . '">' . esc_html__('Unassign Coupons', 'woo-coupon-usage') . '</a>';
                $output .= '<a href="#" class="wcusage-delete-option" data-action="delete_coupons" data-user-id="' . $user_id . '" data-nonce="' . $delete_nonce . '">' . esc_html__('Delete Coupons', 'woo-coupon-usage') . '</a>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';
                
                return $output;
			default:
				return print_r( $item, true );
		}
	}
}

/*
* Create coupon users page
*/
function wcusage_coupon_users_page() {

    // Post Submit Add Registration Form
    if(isset($_POST['_wpnonce'])) {
        $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
        if( wp_verify_nonce( $nonce, 'admin_add_registration_form' ) && wcusage_check_admin_access() ) {
            echo wcusage_post_submit_application(1); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    // If GET success = 1, show success message
    if(isset($_GET['success']) && $_GET['success'] == 1) {
        if(isset($_GET['user'])) {
            $username = sanitize_text_field($_GET['user']);
            echo '<div class="notice notice-success is-dismissible"><p>'
            . sprintf(esc_html__('The %s user %s has been successfully added.', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))), esc_html($username))
            . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>'
            . sprintf(esc_html__('The %s user has been successfully added.', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))))
            . '</p></div>';
        }
    }

    $coupon_users_table = new WC_Coupon_Users_Table();
    $coupon_users_table->process_bulk_action();
	$coupon_users_table->prepare_items();
	?>
    
    <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

    <style>
    @media screen and (min-width: 782px) {
        .wcusage_users_page_desc { margin-bottom: -5px; }
    }
    @media screen and (max-width: 782px) {
        .wcusage_users_page_desc { display: inline-block; }
    }
    </style>
	<div class="wrap wcusage_users_page_header">

        <?php do_action( 'wcusage_hook_dashboard_page_header', ''); ?>

		<h2 class="wp-heading-inline wcusage-admin-title">
        <?php echo sprintf(esc_html__('Coupon %s Users', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )))); ?>
        <span class="wcusage-admin-title-buttons">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_add_affiliate')); ?>" class="wcusage-settings-button" id="wcu-admin-create-registration-link">Add New <?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-bulk-coupon-creator')); ?>" class="wcusage-settings-button" id="wcu-admin-create-registration-link">Bulk Create <?php echo esc_html(wcusage_get_affiliate_text(__( 'Affiliates', 'woo-coupon-usage' ), true)); ?></a>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wcusage_affiliates&action=export_csv'), 'wcusage_export_users_csv')); ?>" class="wcusage-settings-button" id="wcu-admin-export-csv" style="float: right;">
                <?php echo sprintf(esc_html__('Export %s Users', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )))); ?> <span class="fa-solid fa-download"></span>
            </a>
        </span>
        </h2>
        
        <!-- Load delete dropdown styles -->
        <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL . 'css/delete-dropdown.css'); ?>" />
        
        <!-- Load admin JavaScript -->
        <script src="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin.js'); ?>"></script>
        
        <?php
        // Display success message for individual delete actions
        if (isset($_GET['wcusage_message'])) {
            $message = sanitize_text_field(wp_unslash($_GET['wcusage_message']));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        ?>
        
        <form method="post">
            <?php wp_nonce_field( 'wcusage_coupon_users_bulk_action', '_wcusage_bulk_nonce' ); ?>
            <input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : ''; ?>" />
            <?php $coupon_users_table->search_box('Search Users', 'user_search'); ?>
            <?php $coupon_users_table->display(); ?>
        </form>
	</div>
    <script>
    jQuery(document).ready(function($) {
        $('#doaction, #doaction2').click(function(e) {
            var actionSelected = $(this).siblings('select').val();
            var actionText = '';
            switch (actionSelected) {
                case 'bulk-delete-users':
                    actionText = 'Are you sure you want to delete selected <?php echo esc_js(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))); ?> users?\n\nThis will NOT delete the coupons assigned to them.';
                    break;
                case 'bulk-delete-all':
                    actionText = 'Are you sure you want to delete the selected <?php echo esc_js(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))); ?> users, and delete all the coupons they are assigned to?';
                    break;
                case 'bulk-unassign':
                    actionText = 'Are you sure you want to unassign the selected users from their coupons?\n\nThis will essentially remove their access to the <?php echo esc_js(wcusage_get_affiliate_text(__( 'affiliate', 'woo-coupon-usage' ))); ?> dashboard and commission earnings.\n\nThe users and coupons will NOT be deleted.';
                    break;
                case 'bulk-delete-coupons':
                    actionText = 'Are you sure you want to delete the coupons assigned to the selected users?\n\nThe users will NOT be deleted.';
                    break;
                default:
                    return;
            }

            if (!window.confirm(actionText)) {
                e.preventDefault();
                return false;
            }
        });
    });
    </script>
	<?php
}

/**
 * Handle CSV export request
 */
add_action('admin_init', 'wcusage_handle_export_csv');
function wcusage_handle_export_csv() {
    // Check if we're on the correct page and action
    if ( isset($_GET['page']) && 
        ( ( isset($_GET['page']) && ( wp_unslash($_GET['page']) === 'wcusage_coupon_users' || wp_unslash($_GET['page']) === 'wcusage_affiliates' ) ) ) && 
        isset($_GET['action']) && wp_unslash($_GET['action']) === 'export_csv' && 
        isset($_GET['_wpnonce']) ) {
        
        // Verify nonce and permissions
    if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wcusage_export_users_csv' ) ) {
            // Double-check admin access
            if ( ! wcusage_check_admin_access() ) {
                wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'woo-coupon-usage'));
            }
            
            wcusage_export_coupon_users_csv();
            exit;
        }
    }
}

/**
 * Export coupon users to CSV
 */
function wcusage_export_coupon_users_csv() {
    // Get all users without pagination
    $users = wcusage_get_coupon_users();
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="coupon-affiliate-users-' . date('Y-m-d-H-i-s') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Prepare column headers
    $headers = array(
        'User ID',
        'Username',
        'Display Name',
        'Email',
        'Group / Role',
        'Total Referrals',
        'Total Sales',
        'Total Commission'
    );
    
    // Add Commission Payouts header conditionally
    if (wcu_fs()->can_use_premium_code()) {
        $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
        if ($wcusage_tracking_enable) {
            $headers[] = 'Commission Payouts';
        }
    }
    
    $headers[] = wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )) . ' Coupons';
    
    // Add conditional headers
    if (wcu_fs()->can_use_premium_code()) {
        $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
        $system = wcusage_get_setting_value('wcusage_field_storecredit_system', 'default');
        $storecredit_users_col = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_users_col', 1);
        if ($credit_enable && $storecredit_users_col && $system == "default") {
            $credit_label = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_only', 'Store Credit');
            $headers[] = $credit_label;
        }
        
        $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
        if ($wcusage_field_mla_enable) {
            $headers[] = 'Total MLA Commission';
        }
    }
    
    // Write headers
    fputcsv($output, $headers);
    
    // Process each user
    foreach ($users as $user) {
        $user_id = $user['ID'];
        $user_data = get_userdata($user_id);
        
        // Get coupons for this user
        $coupons = wcusage_get_users_coupons_ids($user_id);
        
        // Calculate totals
        $total_referrals = 0;
        $total_sales = 0;
        $total_commission = 0;
        $unpaid_commission = 0;
        $coupon_codes = array();
        
        foreach ($coupons as $coupon) {
            $coupon_code = get_the_title($coupon);
            $coupon_codes[] = $coupon_code;
            
            // Get usage count
            $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
            $all_stats = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
            $wcusage_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '0');
            
            if ($all_stats && $wcu_alltime_stats && !$wcusage_hide_all_time) {
                if (isset($wcu_alltime_stats['total_count'])) {
                    $total_referrals += $wcu_alltime_stats['total_count'];
                }
                if (isset($wcu_alltime_stats['total_orders'])) {
                    $sales = $wcu_alltime_stats['total_orders'];
                    if (isset($wcu_alltime_stats['full_discount'])) {
                        $sales -= $wcu_alltime_stats['full_discount'];
                    }
                    $total_sales += $sales;
                }
                if (isset($wcu_alltime_stats['total_commission'])) {
                    $total_commission += $wcu_alltime_stats['total_commission'];
                }
            } else {
                $c = wcusage_get_coupon_object_safe($coupon_code);
                if(!$c) {
                    continue;
                }
                $usage = $c->get_usage_count();
                $total_referrals += $usage;
            }
            
            // Get commission payouts - only if PRO and tracking enabled
            $unpaid_commission_display = '';
            if (wcu_fs()->can_use_premium_code()) {
                $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
                if ($wcusage_tracking_enable) {
                    $unpaid_commission = (float)get_post_meta($coupon, 'wcu_text_unpaid_commission', true);
                    $unpaid_commission_display = number_format($unpaid_commission, 2, '.', '');
                }
            }
        }
        
        // Prepare row data
        $row = array(
            $user_id,
            $user_data->user_login,
            $user_data->display_name,
            $user_data->user_email,
            ucwords(str_replace('_', ' ', implode(', ', $user_data->roles))),
            $total_referrals,
            number_format($total_sales, 2, '.', ''),
            number_format($total_commission, 2, '.', '')
        );
        
        // Add Commission Payouts conditionally
        if (wcu_fs()->can_use_premium_code()) {
            $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
            if ($wcusage_tracking_enable) {
                $row[] = number_format($unpaid_commission, 2, '.', '');
            }
        }
        
        $row[] = implode(', ', $coupon_codes);
        
        // Add conditional data
        if (wcu_fs()->can_use_premium_code()) {
            $credit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', 0);
            $system = wcusage_get_setting_value('wcusage_field_storecredit_system', 'default');
            $storecredit_users_col = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_users_col', 1);
            if ($credit_enable && $storecredit_users_col && $system == "default" && function_exists('wcusage_get_credit_users_balance')) {
                $row[] = number_format(wcusage_get_credit_users_balance($user_id), 2, '.', '');
            }
            
            $wcusage_field_mla_enable = wcusage_get_setting_value('wcusage_field_mla_enable', '0');
            if ($wcusage_field_mla_enable) {
                $mla_commission = wcusage_mla_total_earnings($user_id);
                $row[] = number_format($mla_commission, 2, '.', '');
            }
        }
        
        // Write row
        fputcsv($output, $row);
    }
    
    fclose($output);
}

/**
 * Get array of user IDs that have been assigned to coupons
 */
if( !function_exists( 'wcusage_get_coupon_users' ) ) {
    function wcusage_get_coupon_users($search_query = '', $role = '', $sort_by = '') {
        
        // Check cache first (10 minutes)
        $cache_key = 'wcusage_coupon_users_list_' . md5($search_query . $role . $sort_by);
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }
        
        // Optimized: Only fetch coupons with assigned users
        $args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'wcu_select_coupon_user',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => 'wcu_select_coupon_user',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
        );

        $coupon_ids = get_posts($args);
        $user_ids = array();

        foreach ($coupon_ids as $coupon_id) {
            $user_id = get_post_meta($coupon_id, 'wcu_select_coupon_user', true);
            if ($user_id) {
                if (!is_numeric($user_id) && is_string($user_id)) {
                    // If it's a username (legacy data), convert to ID
                    $user = get_user_by('login', $user_id);
                    if ($user) {
                        $user_id = $user->ID;
                        update_post_meta($coupon_id, 'wcu_select_coupon_user', $user_id);
                    } else {
                        $user_id = '';
                    }
                }
                if(!is_numeric($user_id)) {
                    $user_id = '';
                }
                if ($user_id) {
                    $user_ids[] = $user_id;
                }
            }
        }


        if (empty($user_ids) || !is_array($user_ids)) {
            $result = array();
            set_transient($cache_key, $result, MINUTE_IN_SECONDS * 10);
            return $result;
        }

        $filtered_user_ids = array_filter($user_ids, function($item) {
            return !empty($item);
        });

        $users_array = array_unique($filtered_user_ids);
        $users = array();

        foreach ($users_array as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $user_roles = implode(', ', $user->roles);
                if ($search_query && stripos($user->user_login, $search_query) === false && stripos($user->display_name, $search_query) === false) {
                    continue;
                }
                if ($role && !in_array($role, $user->roles)) {
                    continue;
                }
                
                // Calculate sorting values
                $sort_values = array();
                if ($sort_by) {
                    $coupons = wcusage_get_users_coupons_ids($user_id);
                    
                    // Total Referrals
                    $total_referrals = 0;
                    foreach ($coupons as $coupon) {
                        $all_stats = wcusage_get_setting_value('wcusage_field_enable_coupon_all_stats_meta', '1');
                        $wcusage_hide_all_time = wcusage_get_setting_value('wcusage_field_hide_all_time', '0');
                        $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                        if($all_stats && !$wcusage_hide_all_time && isset($wcu_alltime_stats) && isset($wcu_alltime_stats['total_count'])) {
                            $usage = $wcu_alltime_stats['total_count'];
                        } else {
                            global $woocommerce;
                            $coupon_code = get_the_title($coupon);
                            $c = wcusage_get_coupon_object_safe($coupon_code);
                            if(!$c) {
                                continue;
                            }
                            $usage = $c->get_usage_count();
                        }
                        if($usage) {
                            $total_referrals += $usage;
                        }
                    }
                    $sort_values['total_referrals'] = $total_referrals;
                    
                    // Total Sales
                    $total_sales = 0;
                    foreach ($coupons as $coupon) {
                        $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                        if($wcu_alltime_stats) {
                            if(isset($wcu_alltime_stats['total_orders'])) {
                                $sales = $wcu_alltime_stats['total_orders'];
                            }
                            if(isset($wcu_alltime_stats['full_discount'])) {
                                $discounts = $wcu_alltime_stats['full_discount'];
                                $sales = (float)$sales - (float)$discounts;
                            }
                            if($sales) {
                                $total_sales += (float)$sales;
                            }
                        }
                    }
                    $sort_values['total_sales'] = $total_sales;
                    
                    // Total Commission
                    $total_commission = 0;
                    foreach ($coupons as $coupon) {
                        $wcu_alltime_stats = get_post_meta($coupon, 'wcu_alltime_stats', true);
                        if($wcu_alltime_stats && isset($wcu_alltime_stats['total_commission'])) {
                            $commission = $wcu_alltime_stats['total_commission'];
                            if($commission) {
                                $total_commission += $wcu_alltime_stats['total_commission'];
                            }
                        }
                    }
                    $sort_values['total_commission'] = $total_commission;
                    
                    // Commission Payouts - only if PRO and tracking enabled
                    if (wcu_fs()->can_use_premium_code()) {
                        $wcusage_tracking_enable = wcusage_get_setting_value('wcusage_field_tracking_enable', '0');
                        if ($wcusage_tracking_enable) {
                            $unpaid_commission = 0;
                            foreach ($coupons as $coupon) {
                                $unpaid_commission += (float)get_post_meta($coupon, 'wcu_text_unpaid_commission', true);
                            }
                            $sort_values['unpaid_commission'] = $unpaid_commission;
                        }
                    }
                }
                
                $users[] = array(
                    'ID'       => $user->ID,
                    'Username' => $user->user_login,
                    'roles'    => implode(', ', $user->roles),
                    'name'     => $user->display_name,
                    'email'    => $user->user_email,
                    'action'   => '',
                    'sort_values' => $sort_values
                );
            }
        }

        // Apply sorting if sort_by is specified
        if ($sort_by && !empty($users)) {
            usort($users, function($a, $b) use ($sort_by) {
                $a_value = isset($a['sort_values'][$sort_by]) ? $a['sort_values'][$sort_by] : 0;
                $b_value = isset($b['sort_values'][$sort_by]) ? $b['sort_values'][$sort_by] : 0;
                
                if ($sort_by === 'ID') {
                    return $a['ID'] <=> $b['ID'];
                } else {
                    return $b_value <=> $a_value; // Descending order for metrics
                }
            });
        }

        // Cache for 10 minutes
        set_transient($cache_key, $users, MINUTE_IN_SECONDS * 10);

        return $users;
    }
}

/**
 * Clear coupon users list cache when assignments change
 */
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        // Get old user ID to clear their cache too
        $old_user_id = get_post_meta($post_id, 'wcu_select_coupon_user', true);
        wcusage_clear_coupon_users_cache($meta_value);
        if ($old_user_id && $old_user_id != $meta_value) {
            wcusage_clear_coupon_users_cache($old_user_id);
        }
    }
}, 10, 4);

add_action('added_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        wcusage_clear_coupon_users_cache($meta_value);
    }
}, 10, 4);

add_action('deleted_post_meta', function($meta_ids, $post_id, $meta_key, $meta_value) {
    if ($meta_key === 'wcu_select_coupon_user') {
        wcusage_clear_coupon_users_cache($meta_value);
    }
}, 10, 4);

// Also clear cache when a shop_coupon post is saved/published
add_action('save_post_shop_coupon', function($post_id, $post, $update) {
    // Check if this is an autosave or revision
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    
    // Check if the coupon has an assigned user
    $assigned_user = get_post_meta($post_id, 'wcu_select_coupon_user', true);
    if ($assigned_user) {
        wcusage_clear_coupon_users_cache();
    }
}, 20, 3);

// Clear cache after WooCommerce coupon save action completes
add_action('woocommerce_coupon_options_save', function($post_id) {
    wcusage_clear_coupon_users_cache();
}, 20);

if (!function_exists('wcusage_clear_coupon_users_cache')) {
    function wcusage_clear_coupon_users_cache($user_id = null) {
        global $wpdb;
        
        // Delete all affiliate users list caches
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wcusage_coupon_users_list_%' 
             OR option_name LIKE '_transient_timeout_wcusage_coupon_users_list_%'"
        );
        
        // If a specific user ID is provided, clear their per-user coupon cache
        if ($user_id) {
            delete_transient('wcusage_user_coupon_ids_' . $user_id);
            delete_transient('wcusage_user_coupon_names_' . $user_id);
        } else {
            // Clear all per-user coupon caches
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                 WHERE option_name LIKE '_transient_wcusage_user_coupon_ids_%' 
                 OR option_name LIKE '_transient_timeout_wcusage_user_coupon_ids_%'
                 OR option_name LIKE '_transient_wcusage_user_coupon_names_%'
                 OR option_name LIKE '_transient_timeout_wcusage_user_coupon_names_%'"
            );
        }
    }
}