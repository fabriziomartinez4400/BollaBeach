<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/***** Render Table *****/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class wcusage_registrations_List_Table extends WP_List_Table {

    function __construct(){
        global $status, $page;

        //Set parent defaults
    parent::__construct( array(
      'singular'  => 'registration',
      'plural'    => 'registrations',
      'ajax'      => false
    ) );

    }

    function column_default($item, $column_name){

		$options = get_option( 'wcusage_options' );

    $wcusage_coupon_multiple = wcusage_get_setting_value('wcusage_field_registration_multiple_template', '0');
    if( !$wcusage_coupon_multiple || !wcu_fs()->can_use_premium_code() ) { echo "<style>.column-type { display: none; }</style>"; }

    if( !wcu_fs()->can_use_premium_code() || ( empty($item['promote']) && empty($item['referrer']) && empty($item['info']) && !isset($item['info']) ) ) {
      echo "<style>.column-info { display: none; }</style>";
    }

    for ($x = 1; $x <= 10; $x++) {
      if($x == 1) {
        $template_default = "Default";
        $template_num = "";
      } else {
        $template_default = "";
        $template_num = "_" . esc_html($x);
      }
      $template_label = wcusage_get_setting_value('wcusage_field_registration_coupon_template_label' . $template_num, '');
      $template_value = wcusage_get_setting_value('wcusage_field_registration_coupon_template' . $template_num, '');
      if($template_value == $item['type']) {
        $type_num = $x;
      }
    }

		$inputfields = '<input type="text" id="wcu-id" name="wcu-id" value="'.esc_attr($item['id']).'" style="display: none;">
		<input type="text" id="user-id" name="wcu-user-id" value="'.esc_attr($item['userid']).'" style="display: none;">
    <input type="text" id="wcu-type" name="wcu-type" value="'.esc_attr($type_num).'" style="display: none;">
		<p>Coupon: <input type="text" id="coupon-code" name="wcu-coupon-code" value="'.esc_attr($item['couponcode']).'" style="width: 100%;"></p>
		<p>Message: <input type="text" id="message" name="wcu-message" value="" style="width: 100%;"></p>';

		$inputfields2 = '<input type="text" id="wcu-id" name="wcu-id" value="'.esc_attr($item['id']).'" style="display: none;">';

    $user_id = $item['userid'];
    $user_info = get_userdata($user_id);

      switch($column_name){
        default:
            return $item[$column_name]; //Show the whole array for troubleshooting purposes
  			case 'id':
  				return '<span style="border-bottom: 1px dotted #000;" title="'. date("M jS, Y (g:ia)", strtotime($item['date'])).'">' . $item[$column_name] . "</span>";
        case 'userid':
          $user_info = get_userdata($item[$column_name]);
          if($user_info) {
            $view_aff_url = admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . intval($item['userid']));
            return '<a href="' . esc_url($view_aff_url) . '" title="' . esc_attr__('View Affiliate', 'woo-coupon-usage') . '">' . esc_html($user_info->user_login) . '</a>';
          } else {
              return "-";
          }
        case 'couponcode':
          if(isset($item[$column_name])) {
            $coupon_info_main = wcusage_get_coupon_info($item[$column_name]);
            $coupon_post_id = isset($coupon_info_main[2]) ? intval($coupon_info_main[2]) : 0;
            if($coupon_post_id) {
              $edit_url = admin_url('post.php?post=' . $coupon_post_id . '&action=edit&classic-editor');
              return '<a href="' . esc_url($edit_url) . '" title="' . esc_attr__('Edit Coupon', 'woo-coupon-usage') . '">' . esc_html(get_the_title($coupon_post_id)) . '</a>';
            } else {
              return esc_html($item[$column_name]);
            }
          } else {
            return "-";
          }
				case 'website':
  				if(isset($item[$column_name])) { return $item[$column_name]; } else { return ""; }
        case 'type':
  			  if(isset($item[$column_name])) { return $item[$column_name]; } else { return ""; }
        case 'info':

          $info = "";

          if( !empty($item['promote']) || !empty($item['referrer']) || !empty($item[$column_name]) ) {
            $info .= "<button id='infobtnShow-".esc_html($item['id'])."' class='reginfobtn'>Show Info <span class='fa-solid fa-arrow-down' style='color: #2271b1;'></span></button>
            <button id='infobtnHide-".esc_html($item['id'])."' class='reginfobtn' style='display: none;'>Hide Info <span class='fa-solid fa-arrow-up' style='color: #2271b1;'></span></button>";
            $info .= "<script>
            jQuery( document ).ready(function() {
              jQuery('#infobtnHide-".esc_html($item['id'])."').click(function(){
                  jQuery('#info-show-".esc_html($item['id'])."').hide();
                  jQuery('#infobtnHide-".esc_html($item['id'])."').hide();
                  jQuery('#infobtnShow-".esc_html($item['id'])."').show();
              });
              jQuery('#infobtnShow-".esc_html($item['id'])."').click(function(){
                  jQuery('#info-show-".esc_html($item['id'])."').show();
                  jQuery('#infobtnHide-".esc_html($item['id'])."').show();
                  jQuery('#infobtnShow-".esc_html($item['id'])."').hide();
              });
            });
            </script>";
          }

          $info .= "<div id='info-show-".esc_html($item['id'])."' style='display: none;'>";

          if (is_object($user_info)) {
            // Full Name
            $item['fullname'] = $user_info->first_name . " " . $user_info->last_name;
            if (!empty($item['fullname']) && $item['fullname'] != " ") {
                $fullnamefieldlabel = wcusage_get_setting_value('wcusage_field_registration_fullname_text', esc_html__('Full Name', 'woo-coupon-usage'));
                $info .= "<p><strong>" . esc_html($fullnamefieldlabel) . "</strong>: " . esc_html($item['fullname']) . "</p>";
            }
            // Email
            $item['email'] = $user_info->user_email;
            if (!empty($item['email'])) {
                $emailfieldlabel = wcusage_get_setting_value('wcusage_field_registration_email_text', esc_html__('Email', 'woo-coupon-usage'));
                $info .= "<p><strong>" . esc_html($emailfieldlabel) . "</strong>: " . esc_html($item['email']) . "</p>";
            }
          }

          if(!empty($item['promote'])) {
            $promotefieldlabel = wcusage_get_setting_value('wcusage_field_registration_promote_text', esc_html__( 'How will you promote us?', 'woo-coupon-usage' ));
            $info .= "<p><strong>".esc_html($promotefieldlabel)."</strong><br/>".esc_html($item['promote'])."</p>";
          }
          if(!empty($item['referrer'])) {
            $referrerfieldlabel = wcusage_get_setting_value('wcusage_field_registration_referrer_text', esc_html__( 'How did you hear about us?', 'woo-coupon-usage' ));
            $info .= "<p><strong>".esc_html($referrerfieldlabel)."</strong><br/>".esc_html($item['referrer'])."</p>";
          }


          if(isset($item[$column_name])) {
            $info_array = json_decode($item[$column_name]);
            if($info_array) {
              foreach($info_array as $key => $value) {
                $info .= "<p><strong>".esc_html($key)."</strong><br/>".esc_html($value)."</p>";
              }
            }
          }

          $info .= "</div>";

          return $info;

  			case 'status':
  				$status = $item['status'];
          $titlehover = 'style="border-bottom: 1px dotted #000;" title="'. date("M jS, Y (g:ia)", strtotime($item['dateaccepted'])).'"';
  				if($status == "accepted") {
  					return '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> <span '.$titlehover.'>' . esc_html__( 'Accepted', 'woo-coupon-usage' ) . '</span>';
  				}
  				if($status == "pending") {
  					return '<span class="dashicons dashicons-warning" style="color: orange;"></span> ' . esc_html__( 'Pending', 'woo-coupon-usage' );
  				}
          if($status == "parent_approved") {
            $parent_approver_id = get_user_meta( absint($item['userid']), 'wcu_mla_parent_approver_id', true );
            $parent_approver_name = '';
            if ( $parent_approver_id ) {
              $parent_approver = get_userdata( absint($parent_approver_id) );
              $parent_approver_name = $parent_approver ? ' (' . esc_html( $parent_approver->user_login ) . ')' : '';
            }
            return '<span class="dashicons dashicons-thumbs-up" style="color: #2271b1;"></span> ' . esc_html__( 'MLA Parent Approved', 'woo-coupon-usage' ) . esc_html( $parent_approver_name );
          }
          if($status == "declined") {
  					return '<span class="dashicons dashicons-dismiss" style="color: red;"></span> ' . esc_html__( 'Declined', 'woo-coupon-usage' );
  				}
  			case 'action1':
  				$status = $item['status'];
					$user_info = get_userdata($item['userid']);

					if($user_info) {
						$usernamelogin = $user_info->user_login;
					} else {
						$usernamelogin = "-";
					}
					?>

          <?php	if($status == "pending" || $status == "parent_approved") { ?>

					<form method="post" id="submitregister">

  					<?php echo $inputfields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php wp_nonce_field( 'admin_affiliate_register_form' ); ?>

            <?php if ( $status === 'parent_approved' ) : ?>
              <p style="color: #2271b1; font-size: 12px; margin: 0 0 4px 0;"><span class="dashicons dashicons-thumbs-up" style="font-size:14px;vertical-align:middle;"></span> <?php echo esc_html__( 'MLA parent has approved this application.', 'woo-coupon-usage' ); ?></p>
            <?php endif; ?>

  					<button	type="submit" name="submitregisteraccept" class="payout-action payout-action-accepted" title="<?php echo esc_html__( 'Accept Application', 'woo-coupon-usage' ); ?>">
  						<?php echo esc_html__( 'Accept', 'woo-coupon-usage' ); ?> <span class="dashicons dashicons-arrow-right-alt" style="font-size: 19px;"></span>
  					</button>

  					<button onClick="return confirm('\nMark this affiliate application as declined? \n\n<?php echo esc_html__( 'User', 'woo-coupon-usage' ) . ": " . esc_html($usernamelogin); ?>\n<?php echo esc_html__( 'Coupon', 'woo-coupon-usage' ) . ": " . esc_html($item['couponcode']); ?> \n\n');"
  					type="submit" name="submitregisterdecline" class="payout-action payout-action-declined" title="<?php echo esc_html__( 'Decline Application', 'woo-coupon-usage' ); ?>">
  						<?php echo esc_html__( 'Decline', 'woo-coupon-usage' ); ?> <span class="dashicons dashicons-dismiss" style="font-size: 19px;"></span>
  					</button>

          </form>

          <?php } elseif ($status == 'accepted' && !empty($item['userid'])) { ?>

          <?php
              $view_aff_url = admin_url('admin.php?page=wcusage_view_affiliate&user_id=' . intval($item['userid']));
              $user_exists  = (bool) get_userdata( intval($item['userid']) );
              // Build dashboard URL from coupon code
              $dashboard_url = '';
              if (!empty($item['couponcode'])) {
                $coupon_info_main = wcusage_get_coupon_info($item['couponcode']);
                if (!empty($coupon_info_main[2])) {
                  $coupon_info = wcusage_get_coupon_info_by_id($coupon_info_main[2]);
                  if (!empty($coupon_info[4])) {
                    $dashboard_url = $coupon_info[4];
                  }
                }
              }
              $has_dashboard = ! empty( $dashboard_url );
              $dashboard_href = $has_dashboard ? $dashboard_url : '#';
              $aff_title  = $user_exists ? esc_html__( 'View Affiliate', 'woo-coupon-usage' ) : esc_html__( 'Affiliate user not found', 'woo-coupon-usage' );
              $dash_title = $has_dashboard ? esc_html__( 'View Dashboard', 'woo-coupon-usage' ) : esc_html__( 'Dashboard URL unavailable', 'woo-coupon-usage' );
          ?>
          <a href="<?php echo esc_url($view_aff_url); ?>" target="_blank" class="button button-primary<?php echo $user_exists ? '' : ' disabled'; ?>" aria-disabled="<?php echo $user_exists ? 'false' : 'true'; ?>" <?php echo $user_exists ? '' : 'tabindex="-1"'; ?> title="<?php echo esc_attr( $aff_title ); ?>">
            <?php echo esc_html__( 'View Affiliate', 'woo-coupon-usage' ); ?> <span class="dashicons dashicons-admin-users"></span>
          </a>
          <a href="<?php echo esc_url($dashboard_href); ?>" target="_blank" class="button<?php echo $has_dashboard ? '' : ' disabled'; ?>" aria-disabled="<?php echo $has_dashboard ? 'false' : 'true'; ?>" <?php echo $has_dashboard ? '' : 'tabindex="-1"'; ?> title="<?php echo esc_attr( $dash_title ); ?>">
            <?php echo esc_html__( 'View Dashboard', 'woo-coupon-usage' ); ?> <span class="dashicons dashicons-external"></span>
          </a>

          <?php } ?>

					<form method="post" id="submitregister">

  					<?php echo $inputfields2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php wp_nonce_field( 'admin_affiliate_register_form' ); ?>

            <button onClick="return confirm('\nAre you sure you want to delete this entry? \n\nThis will only remove the entry from this page. It will not remove the affiliate user or coupon code. \n\n<?php echo esc_html__( 'User', 'woo-coupon-usage' ) . ": " . esc_html($usernamelogin); ?>\n<?php echo esc_html__( 'Coupon', 'woo-coupon-usage' ) . ": " . esc_html($item['couponcode']); ?> \n\n');"
            title="<?php echo esc_html__( 'Delete this registration.', 'woo-coupon-usage' ); ?>"
            type="submit" name="submitregisterdelete" style="padding: 0; background: 0; border: 0; cursor: pointer; margin-bottom: 5px; color: #B52828;">
              <i class="fa-solid fa-trash-can"></i> <?php echo esc_html__( 'Delete', 'woo-coupon-usage' ); ?>
            </button>

					</form>

					<?php
      }
    }

    function column_title($item){

        //Build row actions
    $page_param = isset($_GET['page']) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    $actions = array(
      'edit'      => sprintf('<a href="?page=%s&action=%s&payout=%s">%s</a>', $page_param, 'edit', absint( $item['ID'] ), esc_html__( 'Edit', 'woo-coupon-usage' ) ),
      'delete'    => sprintf('<a href="?page=%s&action=%s&payout=%s">%s</a>', $page_param, 'delete', absint( $item['ID'] ), esc_html__( 'Delete', 'woo-coupon-usage' ) ),
    );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
    $status = isset($item['status']) ? esc_attr($item['status']) : '';
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" data-status="%3$s" />',
      /*$1%s*/ $this->_args['singular'],
      /*$2%s*/ $item['id'],
      /*$3%s*/ $status
    );
    }

    function no_items() {
      esc_html_e( 'No registrations applications found.', 'woo-coupon-usage' );
    }

    function get_columns(){

        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'     => esc_html__( 'ID', 'woo-coupon-usage' ),
			      'userid'  => esc_html__( 'Username', 'woo-coupon-usage' ),
            'couponcode'  => esc_html__( 'Coupon', 'woo-coupon-usage' ),
						'website'  => esc_html__( 'Website', 'woo-coupon-usage' ),
            'type'  => esc_html__( 'Template', 'woo-coupon-usage' ),
            'info'  => esc_html__( 'Other Information', 'woo-coupon-usage' ),
            'status'  => esc_html__( 'Status', 'woo-coupon-usage' ),
      			'action1'  => esc_html__( 'Action', 'woo-coupon-usage' ),
        );
        return $columns;

    }

    function get_sortable_columns() {
      $sortable_columns = array();
      return $sortable_columns;
    }

    function prepare_items() {

        global $wpdb; //This is used only if making any database queries

        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $table_name = $wpdb->prefix . 'wcusage_register';

    if ( isset($_GET['status']) ) {
      $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE status = %s ORDER BY id DESC", sanitize_text_field( wp_unslash( $_GET['status'] ) ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        } else {
            $sql = "SELECT * FROM $table_name ORDER BY id DESC"; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        }
        $data = $wpdb->get_results($sql, ARRAY_A); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        
        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );

    }

}
