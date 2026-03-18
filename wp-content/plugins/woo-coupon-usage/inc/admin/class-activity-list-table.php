<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for Activity/Visits List Table
 *
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class wcusage_activity_List_Table extends WP_List_Table {

    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'activity',
            'plural'    => 'click',
            'ajax'      => false
        ) );

    }

    function column_default($item, $column_name){

		$options = get_option( 'wcusage_options' );

      switch($column_name){
        default:
            return $item[$column_name];
        case 'id':
            return $item[$column_name];
        case 'event':
            $event_message = wcusage_activity_message($item[$column_name], $item['event_id'], $item['info']);
            return $event_message;
        case 'user_id':
            $user = get_userdata( $item[$column_name] );
            if($user) {
                return '<a href="'. esc_url( admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . absint( $item[$column_name] )) ) .'" title="'. esc_attr( $user->user_login ) .'" target="_blank">'. esc_html( $user->first_name ) .' '. esc_html( $user->last_name ) .'</a>';
            } else {
                return 'Guest';
            }
        case 'date':
            $date = date_i18n( 'F j, Y (H:i)', strtotime($item[$column_name]) );
            return $date;
      }

    }

    function column_title($item){

        //Build row actions
        $actions = array();

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['ID']
        );
    }

    function get_columns(){

        $columns = array(
            //'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'     => esc_html__( 'ID', 'woo-coupon-usage' ),
      			'date'  => esc_html__( 'Date', 'woo-coupon-usage' ),
						'user_id'  => esc_html__( 'User', 'woo-coupon-usage' ),
						'event'  => esc_html__( 'Event', 'woo-coupon-usage' ),
        );
        return $columns;

    }

    function get_sortable_columns() {
      $sortable_columns = array(
			'date'  => array('date',false),
        );
        return $sortable_columns;
    }

    /**
     * Get the list of available event types for the filter dropdown.
     */
    public static function get_event_types() {
        return array(
            'referral'                                  => __( 'Referral', 'woo-coupon-usage' ),
            'registration'                              => __( 'Registration', 'woo-coupon-usage' ),
            'registration_accept'                       => __( 'Registration Accepted', 'woo-coupon-usage' ),
            'mla_invite'                                => __( 'MLA Invite', 'woo-coupon-usage' ),
            'direct_link_domain'                        => __( 'Direct Link Domain', 'woo-coupon-usage' ),
            'payout_request'                            => __( 'Payout Request', 'woo-coupon-usage' ),
            'payout_paid'                               => __( 'Payout Paid', 'woo-coupon-usage' ),
            'payout_reversed'                           => __( 'Payout Reversed', 'woo-coupon-usage' ),
            'new_campaign'                              => __( 'New Campaign', 'woo-coupon-usage' ),
            'commission_added'                          => __( 'Commission Added', 'woo-coupon-usage' ),
            'commission_removed'                        => __( 'Commission Removed', 'woo-coupon-usage' ),
            'mla_commission_added'                      => __( 'MLA Commission Added', 'woo-coupon-usage' ),
            'mla_commission_removed'                    => __( 'MLA Commission Removed', 'woo-coupon-usage' ),
            'manual_unpaid_commission_edit'              => __( 'Manual Unpaid Commission Edit', 'woo-coupon-usage' ),
            'manual_pending_commission_edit'             => __( 'Manual Pending Commission Edit', 'woo-coupon-usage' ),
            'manual_processing_commission_edit'          => __( 'Manual Processing Commission Edit', 'woo-coupon-usage' ),
            'manual_coupon_commission_edit'              => __( 'Manual Coupon Commission Edit', 'woo-coupon-usage' ),
            'manual_coupon_commission_fixed_order_edit'  => __( 'Manual Fixed Order Commission Edit', 'woo-coupon-usage' ),
            'manual_coupon_commission_fixed_product_edit'=> __( 'Manual Fixed Product Commission Edit', 'woo-coupon-usage' ),
            'reward_earned'                             => __( 'Reward Earned', 'woo-coupon-usage' ),
            'reward_earned_bonus_amount'                => __( 'Reward Bonus Amount', 'woo-coupon-usage' ),
            'reward_earned_commission_increase'         => __( 'Reward Commission Increase', 'woo-coupon-usage' ),
            'reward_earned_email_sent'                  => __( 'Reward Email Sent', 'woo-coupon-usage' ),
            'reward_earned_role_assigned'               => __( 'Reward Role Assigned', 'woo-coupon-usage' ),
        );
    }

    /**
     * Display the filters above the table.
     */
    function extra_tablenav( $which ) {
        if ( $which !== 'top' ) {
            return;
        }

        $event_type  = isset( $_GET['event_type'] )  ? sanitize_text_field( wp_unslash( $_GET['event_type'] ) )  : '';
        $search_user = isset( $_GET['search_user'] )  ? sanitize_text_field( wp_unslash( $_GET['search_user'] ) )  : '';
        $date_from   = isset( $_GET['date_from'] )    ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) )    : '';
        $date_to     = isset( $_GET['date_to'] )      ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) )      : '';
        $search_term = isset( $_GET['s'] )             ? sanitize_text_field( wp_unslash( $_GET['s'] ) )             : '';

        $event_types = self::get_event_types();
        ?>
        <div class="wcusage-activity-filters alignleft actions">

            <input type="text" name="s" placeholder="<?php esc_attr_e( 'Search events...', 'woo-coupon-usage' ); ?>" value="<?php echo esc_attr( $search_term ); ?>" style="width:160px;" />

            <select name="event_type">
                <option value=""><?php esc_html_e( 'All Event Types', 'woo-coupon-usage' ); ?></option>
                <?php foreach ( $event_types as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $event_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="search_user" placeholder="<?php esc_attr_e( 'Search user...', 'woo-coupon-usage' ); ?>" value="<?php echo esc_attr( $search_user ); ?>" style="width:140px;" />

            <label class="wcusage-activity-date-label"><?php esc_html_e( 'From:', 'woo-coupon-usage' ); ?></label>
            <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" style="width:145px;" />

            <label class="wcusage-activity-date-label"><?php esc_html_e( 'To:', 'woo-coupon-usage' ); ?></label>
            <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" style="width:145px;" />

            <?php submit_button( __( 'Filter', 'woo-coupon-usage' ), 'action', 'filter_action', false ); ?>

            <?php
            // Show reset link when any filter is active
            if ( $event_type || $search_user || $date_from || $date_to || $search_term ) :
            ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wcusage_activity' ) ); ?>" class="button" style="margin-left:4px;"><?php esc_html_e( 'Reset', 'woo-coupon-usage' ); ?></a>
            <?php endif; ?>

        </div>
        <?php
    }

    function prepare_items() {

        global $wpdb;

        $per_page = 100;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $table_name = $wpdb->prefix . 'wcusage_activity';

        // Build WHERE clauses from filters
        $where = array();
        $values = array();

        // Event type filter (validated against known event types)
        if ( ! empty( $_GET['event_type'] ) ) {
            $event_type = sanitize_text_field( wp_unslash( $_GET['event_type'] ) );
            $allowed_event_types = array_keys( self::get_event_types() );
            if ( in_array( $event_type, $allowed_event_types, true ) ) {
                $where[] = 'a.event = %s';
                $values[] = $event_type;
            }
        }

        // User search filter (search by name, username, or email)
        if ( ! empty( $_GET['search_user'] ) ) {
            $search_user = sanitize_text_field( wp_unslash( $_GET['search_user'] ) );
            // Look up matching user IDs from the users table
            $users_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $like = '%' . $wpdb->esc_like( $search_user ) . '%';
            $user_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT u.ID FROM $users_table u
                 LEFT JOIN $usermeta_table um_fn ON (u.ID = um_fn.user_id AND um_fn.meta_key = 'first_name')
                 LEFT JOIN $usermeta_table um_ln ON (u.ID = um_ln.user_id AND um_ln.meta_key = 'last_name')
                 WHERE u.user_login LIKE %s
                 OR u.user_email LIKE %s
                 OR u.display_name LIKE %s
                 OR um_fn.meta_value LIKE %s
                 OR um_ln.meta_value LIKE %s", // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                $like, $like, $like, $like, $like
            ) );
            if ( ! empty( $user_ids ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
                $where[] = "a.user_id IN ($placeholders)";
                $values = array_merge( $values, $user_ids );
            } else {
                // No users matched – force empty result
                $where[] = '1 = 0';
            }
        }

        // Date from filter (validated as YYYY-MM-DD)
        if ( ! empty( $_GET['date_from'] ) ) {
            $date_from = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) {
                $where[] = 'a.date >= %s';
                $values[] = $date_from . ' 00:00:00';
            }
        }

        // Date to filter (validated as YYYY-MM-DD)
        if ( ! empty( $_GET['date_to'] ) ) {
            $date_to = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_to ) ) {
                $where[] = 'a.date <= %s';
                $values[] = $date_to . ' 23:59:59';
            }
        }

        // Search term filter (searches event info text)
        if ( ! empty( $_GET['s'] ) ) {
            $search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) );
            $like = '%' . $wpdb->esc_like( $search_term ) . '%';
            $where[] = '(a.info LIKE %s OR a.event LIKE %s)';
            $values[] = $like;
            $values[] = $like;
        }

        // Build the SQL
        $where_sql = '';
        if ( ! empty( $where ) ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where );
        }

        // Sorting
        $orderby = 'a.id';
        $order = 'DESC';
        if ( ! empty( $_GET['orderby'] ) ) {
            $allowed_orderby = array( 'date', 'id' );
            $request_orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
            if ( in_array( $request_orderby, $allowed_orderby, true ) ) {
                $orderby = 'a.' . $request_orderby;
            }
        }
        if ( ! empty( $_GET['order'] ) ) {
            $request_order = strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) );
            if ( in_array( $request_order, array( 'ASC', 'DESC' ), true ) ) {
                $order = $request_order;
            }
        }

        // Count query
        $count_sql = "SELECT COUNT(*) FROM $table_name a $where_sql"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        if ( ! empty( $values ) ) {
            $total_items = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        } else {
            $total_items = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        }

        // Data query with pagination
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        $data_sql = "SELECT a.* FROM $table_name a $where_sql ORDER BY $orderby $order LIMIT %d OFFSET %d"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $data_values = array_merge( $values, array( $per_page, $offset ) );
        $data = $wpdb->get_results( $wpdb->prepare( $data_sql, $data_values ), ARRAY_A ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );

    }

}
?>
