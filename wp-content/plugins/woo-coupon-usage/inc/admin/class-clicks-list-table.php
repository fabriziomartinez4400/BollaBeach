<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Class for Clicks/Visits List Table
 *
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class wcusage_clicks_List_Table extends WP_List_Table {

    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'clicks',
            'plural'    => 'click',
            'ajax'      => false
        ) );

    }

    function column_default($item, $column_name){

		$options = get_option( 'wcusage_options' );

      switch($column_name){
        default:
            return $item[$column_name]; //Show the whole array for troubleshooting purposes
        case 'id':
          return $item[$column_name];
        case 'couponid':
          if (isset($item[$column_name]) && $item[$column_name] != 0) {
              $coupon_info = wcusage_get_coupon_info_by_id($item[$column_name]);
              $uniqueurl = $coupon_info[4];
              return "<a href='" . esc_url($uniqueurl) . "' target='_blank' title='" . sprintf(__('View %s Dashboard', 'woo-coupon-usage'), wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))) . "'>"
                  . get_the_title($item[$column_name])
                  . "</a> <a href='" . esc_url(admin_url('post.php?post=' . $item[$column_name] . '&action=edit&classic-editor')) . "' target='_blank' title='" . __('Edit Coupon', 'woo-coupon-usage') . "'>"
                  . "<span class='dashicons dashicons-edit-page' style='font-size: 12px; margin-top: 5px; display: inline-block; width: 12px;'></span></a>"
                  . "<a href='" . esc_url(admin_url('admin.php?page=wcusage_clicks&coupon=' . get_the_title($item[$column_name]))) . "' title='" . __('View all visits for this coupon.', 'woo-coupon-usage') . "'>"
                  . "<span class='dashicons dashicons-search' style='font-size: 12px; margin-top: 5px;'></span></a>";
          } else {
              return "";
          }
        case 'campaign':
          if (!empty($item[$column_name])) {
              return ucfirst($item[$column_name])
                  . "<a href='" . esc_url(admin_url('admin.php?page=wcusage_clicks&campaign=' . $item[$column_name])) . "' title='" . __('View all visits for this campaign name.', 'woo-coupon-usage') . "'>"
                  . "<span class='dashicons dashicons-search' style='font-size: 12px; margin-top: 5px;'></span></a>";
          } else {
              return "---";
          }
        case 'page':
  				if(isset($item[$column_name])) {
            return "<a href='".esc_url(get_permalink($item[$column_name]))."' target='_blank' title='".__( 'View Landing Page', 'woo-coupon-usage' )."'>"
            . get_the_title($item[$column_name]) . "</a>";
          } else {
            return "";
          }
        case 'referrer':
          if (!empty($item[$column_name])) {
              return $item[$column_name]
                  . "<a href='".esc_url(admin_url('admin.php?page=wcusage_clicks&referrer=' . $item[$column_name]))."' title='" . __('View all visits for this referrer.', 'woo-coupon-usage') . "'>"
                  . "<span class='dashicons dashicons-search' style='font-size: 12px; margin-top: 5px;'></span></a>";
          } else {
              return "";
          }
				case 'ipaddress':
  				if(isset($item[$column_name])) {

            if( wcusage_is_customer_blacklisted($item[$column_name]) ) {
              $blacklist_button_part1 = '<input type="text" id="wcu-blacklist-ipaddress-remove" name="wcu-blacklist-ipaddress-remove" value="'.$item['ipaddress'].'" style="display: none;">';
              $blacklist_button_part2 = '<span class="fa-solid fa-shield icon-blacklist-remove"></span>';
              $blacklist_button_part3 = esc_html__( 'Remove from Blacklist', 'woo-coupon-usage' );
            } else {
              $blacklist_button_part1 = '<input type="text" id="wcu-blacklist-ipaddress" name="wcu-blacklist-ipaddress" value="'.$item['ipaddress'].'" style="display: none;">';
              $blacklist_button_part2 = '<span class="fa-solid fa-ban icon-blacklist-add"></span>';
              $blacklist_button_part3 = esc_html__( 'Add to Blacklist', 'woo-coupon-usage' );
            }

            $blacklist_button = '<form method="post" id="submitclick" style="display: inline-block;">
  					'.$blacklist_button_part1.'
            '.wp_nonce_field( 'blacklist_url' ).'
            <button type="submit" name="submitclickblacklistip" class="payout-action-blacklistip" style="padding: 0; background: transparent; border: 0;" title="'.$blacklist_button_part3.'">
               '.$blacklist_button_part2.'
            </button>
            </form>';

            if( wcusage_is_customer_blacklisted($item[$column_name]) ) {

              return '<span style="color: red;" title="This visitor is blacklisted from using affiliate coupons.">' . $item[$column_name] . " " . $blacklist_button . '</span>';

            } else {

              return $item[$column_name] . " " . $blacklist_button;
            }

          } else {
            return "";
          }
  			case 'converted':
  				if($item[$column_name] == 1) {
            $orderinfo = "";
            if(!empty($item['orderid'])) {
              $theorder = wc_get_order( $item['orderid'] );
              $theordertotal = "0";
              if($theorder) {
                $theordertotal = $theorder->get_formatted_order_total($item['orderid']);
              }
              $orderinfo = "<br/><a href='".get_edit_post_link($item['orderid'])."'>#" . $item['orderid'] . "</a> (" . $theordertotal . ")";
            }
  					return '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> ' . esc_html__( 'Yes', 'woo-coupon-usage' ) . $orderinfo;
  				} else {
  					return '<span class="dashicons dashicons-dismiss" style="color: red;"></span> ' . esc_html__( 'No', 'woo-coupon-usage' );
  				}
  			case 'date':
  				$thedatetime = strtotime($item[$column_name]);
  				return date_i18n("M jS, Y (g:ia)", $thedatetime);
  			case 'action1':
					?>
					<form method="post" id="submitclick">
					<input type="text" id="wcu-id" name="wcu-id" value="<?php echo esc_attr($item['id']); ?>" style="display: none;">
					<input type="text" id="wcu-status-delete" name="wcu-status-delete" value="cancel" style="display: none;">
          <?php wp_nonce_field( 'delete_url' ); ?>

          <button onClick="return confirm('Are you sure you want to delete visit #<?php echo esc_attr($item['id']); ?>?');"
            title="<?php echo esc_html__( 'Delete this visit.', 'woo-coupon-usage' ); ?>"
          type="submit" name="submitclickdelete" style="padding: 0; background: 0; border: 0; cursor: pointer; margin-bottom: 5px; color: #B52828;">
            <i class="fa-solid fa-trash-can"></i> <?php echo esc_html__( 'Delete', 'woo-coupon-usage' ); ?>
          </button>

					</form>
					<?php
      }
    }

    function column_title($item){

        //Build row actions
        $actions = array(
          'delete'    => sprintf('<a href="?page=%s&action=%s&click=%s">%s</a>', sanitize_text_field( $_GET['page'] ), 'delete', $item['ID'], esc_html__( 'Delete', 'woo-coupon-usage' )),
        );

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

      $wcusage_field_track_click_ip = wcusage_get_setting_value('wcusage_field_track_click_ip', '1');
      if($wcusage_field_track_click_ip) {
        $ip_text = esc_html__( 'IP Address', 'woo-coupon-usage' );
      } else {
        $ip_text = esc_html__( 'Visitor ID', 'woo-coupon-usage' );
      }

        $columns = array(
            //'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'     => esc_html__( 'ID', 'woo-coupon-usage' ),
            'couponid'  => sprintf(esc_html__( '%s Coupon', 'woo-coupon-usage' ), wcusage_get_affiliate_text(__( 'Affiliate', 'woo-coupon-usage' ))),
						'campaign'  => esc_html__( 'Campaign Name', 'woo-coupon-usage' ),
						'page'  => esc_html__( 'Landing Page', 'woo-coupon-usage' ),
						'referrer'  => esc_html__( 'Referrer URL', 'woo-coupon-usage' ),
						'ipaddress'  => $ip_text,
      			'date'  => esc_html__( 'Visit Date', 'woo-coupon-usage' ),
            'converted'  => esc_html__( 'Converted', 'woo-coupon-usage' ),
      			'action1'  => esc_html__( 'Action', 'woo-coupon-usage' ),
        );
        return $columns;

    }

    function get_sortable_columns() {
      $sortable_columns = array();
      return $sortable_columns;
    }

    function prepare_items() {

        global $wpdb;

        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

				$table_name = $wpdb->prefix . 'wcusage_clicks';

        // Filters
        $where_clauses = array();
        $params = array();
        
        // Check each condition and append to the where clauses array
        if (isset($_GET['referrer']) && !empty($_GET['referrer'])) {
            $where_clauses[] = "referrer = %s";
            $params[] = sanitize_text_field($_GET['referrer']);
        }
        if (isset($_GET['coupon']) && !empty($_GET['coupon'])) {
            $coupon = wcusage_get_coupon_info($_GET['coupon']);
            if (!empty($coupon) && isset($coupon[2])) {
                $where_clauses[] = "couponid = %s";
                $params[] = sanitize_text_field($coupon[2]);
            }
        }
        if (isset($_GET['campaign']) && !empty($_GET['campaign'])) {
            $where_clauses[] = "campaign = %s";
            $params[] = sanitize_text_field($_GET['campaign']);
        }
        if (isset($_GET['converted']) && ($_GET['converted'] === "1" || $_GET['converted'] === "0")) {
            $where_clauses[] = "converted = %d";
            $params[] = intval($_GET['converted']);
        }
        
        // Combine where clauses into a single string
        if (!empty($where_clauses)) {
          $sqlwhere = " WHERE " . implode(" AND ", $where_clauses);
          $full_query = "SELECT * FROM $table_name" . $sqlwhere . " ORDER BY id DESC"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
          // Prepare the full query with all parameters
          $sql = $wpdb->prepare($full_query, ...$params); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        } else {
          $sql = "SELECT * FROM $table_name ORDER BY id DESC"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
          
        }

        $data = $wpdb->get_results($sql, ARRAY_A); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }

}
?>
