<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_MLA_Users_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        parent::__construct( array(
            'singular' => 'mlauser',
            'plural'   => 'mlausers',
            'ajax'     => false,
        ) );
    }

    function get_columns() {

        $column['ID'] = esc_html__('ID', 'woo-coupon-usage');
        $column['Username'] = esc_html__('Username', 'woo-coupon-usage');
        $column['roles'] = esc_html__('Group / Role', 'woo-coupon-usage');
        $column['mla_sub_affiliates'] = esc_html__('Sub-Affiliates', 'woo-coupon-usage');
        $column['mla_payouts'] = 'MLA Payouts' . wcusage_admin_tooltip(
            esc_html__('• Unpaid: MLA commission earned but not yet paid.', 'woo-coupon-usage') . '<br/>' .
            esc_html__('• Pending: Payout requests currently awaiting approval.', 'woo-coupon-usage') . '<br/>' .
            esc_html__('• Paid: Successfully paid to affiliate.', 'woo-coupon-usage')
        );
        $column['mla_tiers'] = esc_html__('Tiers Active', 'woo-coupon-usage');
        $column['view_mla_affiliate'] = esc_html__('Actions', 'woo-coupon-usage');

        return $column;
    }

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
                    // Retain other $_GET parameters in the form submission
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
                        <option value="mla_sub_affiliates" <?php selected('mla_sub_affiliates', $current_sort); ?>><?php esc_html_e('Sub-Affiliates', 'woo-coupon-usage'); ?></option>
                        <option value="ID" <?php selected('ID', $current_sort); ?>><?php esc_html_e('ID', 'woo-coupon-usage'); ?></option>
                        <option value="mla_total_commission" <?php selected('mla_total_commission', $current_sort); ?>><?php esc_html_e('MLA Commission', 'woo-coupon-usage'); ?></option>
                        <option value="mla_unpaid_commission" <?php selected('mla_unpaid_commission', $current_sort); ?>><?php esc_html_e('MLA Unpaid', 'woo-coupon-usage'); ?></option>
                    </select>
                    <input type="submit" name="filter_sort" id="sort-query-submit" class="button" value="<?php esc_html_e('Sort', 'woo-coupon-usage'); ?>">
            </div>
            <?php
        }
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

        $users = $this->get_mla_users( $search_query, $role, $sort_by );

        $total_items = count( $users );
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ) );

        $this->items = array_slice( $users, ( ( $current_page - 1 ) * $per_page ), $per_page );
    }

    /**
     * Get MLA users - affiliates with MLA access.
     * If MLA is invite-only, only show users that have wcu_ml_access.
     */
    function get_mla_users( $search_query = '', $role = '', $sort_by = '' ) {

        // Start from the standard coupon users list
        $all_users = wcusage_get_coupon_users( $search_query, $role );

        $mla_private = wcusage_get_setting_value('wcusage_field_show_mla_private', '0');

        $mla_users = array();

        foreach ( $all_users as $user ) {
            $user_id = $user['ID'];

            // If MLA is invite only, check access
            if ( $mla_private ) {
                $access = get_user_meta( $user_id, 'wcu_ml_access', true );
                if ( ! $access ) {
                    // Also check user role based access
                    $has_role_access = false;
                    $mla_invite_user_roles = wcusage_get_setting_value('wcusage_mla_invite_user_role', array());
                    if ( $mla_invite_user_roles && is_array($mla_invite_user_roles) ) {
                        $wp_user = get_userdata($user_id);
                        if ($wp_user) {
                            foreach ($wp_user->roles as $role_key) {
                                if (isset($mla_invite_user_roles[$role_key])) {
                                    $has_role_access = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ( ! $has_role_access ) {
                        continue;
                    }
                }
            }

            // Calculate MLA stats for sorting
            $sub_affiliates = array();
            if ( function_exists('wcusage_get_ml_sub_affiliates') ) {
                $sub_affiliates = wcusage_get_ml_sub_affiliates($user_id);
            }

            $mla_total_commission = 0;
            if ( function_exists('wcusage_mla_total_earnings') ) {
                $mla_total_commission = wcusage_mla_total_earnings($user_id);
            }

            $mla_unpaid = (float) get_user_meta($user_id, 'wcu_ml_unpaid_commission', true);

            $user['sort_values'] = array(
                'mla_sub_affiliates' => count($sub_affiliates),
                'mla_total_commission' => $mla_total_commission,
                'mla_unpaid_commission' => $mla_unpaid,
            );

            $mla_users[] = $user;
        }

        // Default sort by sub-affiliates count if no sort specified
        if ( ! $sort_by ) {
            $sort_by = 'mla_sub_affiliates';
        }

        // Apply sorting
        if ( $sort_by && ! empty($mla_users) ) {
            usort($mla_users, function($a, $b) use ($sort_by) {
                if ( $sort_by === 'ID' ) {
                    return $a['ID'] <=> $b['ID'];
                }
                $a_value = isset($a['sort_values'][$sort_by]) ? $a['sort_values'][$sort_by] : 0;
                $b_value = isset($b['sort_values'][$sort_by]) ? $b['sort_values'][$sort_by] : 0;
                return $b_value <=> $a_value; // Descending order for metrics
            });
        }

        return $mla_users;
    }

    function column_default( $item, $column_name ) {
        $user_id = $item['ID'];

        switch ( $column_name ) {
            case 'ID':
                $view_url = esc_url(admin_url( 'admin.php?page=wcusage_view_affiliate&user_id=' . $user_id . '&tab=mla' ));
                $alt = isset($item['name']) ? $item['name'] : '';
                $avatar_url = get_avatar_url( $user_id, array( 'size' => 40, 'default' => 'identicon' ) );
                $avatar = '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $alt ) . '" class="wcusage-avatar" style="border-radius:50%;width:40px;height:40px;object-fit:cover;display:inline-block;vertical-align:middle;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.12);" />';
                return '<div class="wcusage-idcell" style="display:flex;align-items:center;justify-content:center;gap:8px;"><a href="' . $view_url . '" class="wcusage-avatar-link" title="' . esc_attr__( 'View MLA Affiliate', 'woo-coupon-usage' ) . '">' . $avatar . '</a><a href="' . $view_url . '" class="wcusage-id-link" style="font-weight:600;">#' . $item[ 'ID' ] . '</a></div>';

            case 'Username':
                return wcusage_output_affiliate_tooltip_user_info($user_id);

            case 'roles':
                return ucwords( str_replace( '_', ' ', $item[ 'roles' ] ) );

            case 'mla_sub_affiliates':
                $sub_affiliates = array();
                if ( function_exists('wcusage_get_ml_sub_affiliates') ) {
                    $sub_affiliates = wcusage_get_ml_sub_affiliates($user_id);
                }
                $count = count($sub_affiliates);
                if ( $count > 0 ) {
                    return $count;
                }
                return '0';

            case 'mla_payouts':
                global $wpdb;
                $payouts_table = $wpdb->prefix . 'wcusage_payouts';
                $unpaid_commission = (float) get_user_meta( $user_id, 'wcu_ml_unpaid_commission', true );
                $paid_commission     = 0;
                $pending_payments    = 0;
                $payouts_table_exists = ( $wpdb->get_var( "SHOW TABLES LIKE '$payouts_table'" ) == $payouts_table );
                if ( $payouts_table_exists ) {
                    $totals = $wpdb->get_row( $wpdb->prepare(
                        "SELECT
                            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) AS paid_total,
                            COALESCE(SUM(CASE WHEN status IN ('pending','created') THEN amount END), 0) AS pending_total
                         FROM $payouts_table WHERE userid = %d AND couponid = 0",
                        $user_id
                    ) );
                    if ( $totals ) {
                        $paid_commission  = (float) $totals->paid_total;
                        $pending_payments = (float) $totals->pending_total;
                    }
                } else {
                    $mla_total = function_exists( 'wcusage_mla_total_earnings' ) ? wcusage_mla_total_earnings( $user_id ) : 0;
                    $paid_commission = max( 0, (float) $mla_total - $unpaid_commission );
                }
                $output  = '<div style="line-height: 1.4;">';
                $output .= '<div><strong>' . esc_html__( 'Unpaid', 'woo-coupon-usage' ) . ':</strong> ' . wcusage_format_price( $unpaid_commission ) . '</div>';
                $output .= '<hr style="margin: 2px 0; border: 0; border-top: 1px solid #ddd;">';
                $output .= '<div><strong>' . esc_html__( 'Pending', 'woo-coupon-usage' ) . ':</strong> ' . wcusage_format_price( $pending_payments ) . '</div>';
                $output .= '<hr style="margin: 2px 0; border: 0; border-top: 1px solid #ddd;">';
                $output .= '<div><strong>' . esc_html__( 'Paid', 'woo-coupon-usage' ) . ':</strong> ' . wcusage_format_price( $paid_commission ) . '</div>';
                $output .= '</div>';
                return $output;

            case 'mla_tiers':
                // Count how many tiers of sub-affiliates this user has
                $sub_affiliates = array();
                if ( function_exists('wcusage_get_ml_sub_affiliates') ) {
                    $sub_affiliates = wcusage_get_ml_sub_affiliates($user_id);
                }
                $max_tier = 0;
                foreach ($sub_affiliates as $sub) {
                    $get_parents = get_user_meta($sub->ID, 'wcu_ml_affiliate_parents', true);
                    if ( is_array($get_parents) ) {
                        $tier_key = array_search($user_id, $get_parents);
                        if ( $tier_key !== false ) {
                            $tier_num = (int) str_replace('T', '', $tier_key);
                            if ( $tier_num > $max_tier ) {
                                $max_tier = $tier_num;
                            }
                        }
                    }
                }
                if ( $max_tier > 0 ) {
                    return $max_tier;
                }
                return '—';

            case 'view_mla_affiliate':
                $view_url = esc_url(admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . $user_id . '&tab=mla'));
                $output = '<div class="wcusage-user-actions">';
                $output .= '<a href="' . $view_url . '" class="button button-primary">' . esc_html__('View MLA Details', 'woo-coupon-usage') . '</a>';
                $output .= '</div>';
                return $output;

            default:
                return print_r( $item, true );
        }
    }
}

/**
 * Display the MLA Users page
 */
function wcusage_mla_users_page() {

    $mla_users_table = new WC_MLA_Users_Table();
    $mla_users_table->prepare_items();
    ?>

    <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

    <style>
    @media screen and (min-width: 782px) {
        .wcusage_mla_users_page_desc { margin-bottom: -5px; }
    }
    @media screen and (max-width: 782px) {
        .wcusage_mla_users_page_desc { display: inline-block; }
    }
    </style>
    <div class="wrap wcusage_users_page_header">

        <?php do_action( 'wcusage_hook_dashboard_page_header', ''); ?>

        <h2 class="wp-heading-inline wcusage-admin-title">
        <?php echo esc_html__('MLA Affiliate Users', 'woo-coupon-usage'); ?>
        <span class="wcusage-admin-title-buttons">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_affiliates')); ?>" class="wcusage-settings-button"><?php echo sprintf(esc_html__('All %s Users', 'woo-coupon-usage'), esc_html(wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' )))); ?></a>
        </span>
        </h2>

        <p class="wcusage_mla_users_page_desc"><?php echo esc_html__('This page shows affiliate users who have access to the Multi-Level Affiliate (MLA) system, along with their MLA statistics.', 'woo-coupon-usage'); ?></p>

        <!-- Load admin styles -->
        <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL . 'css/delete-dropdown.css'); ?>" />
        <script src="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin.js'); ?>"></script>

        <form method="post">
            <input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : ''; ?>" />
            <?php $mla_users_table->search_box('Search Users', 'user_search'); ?>
            <?php $mla_users_table->display(); ?>
        </form>
    </div>
    <?php
}
