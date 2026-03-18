<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
function wcusage_admin_registrations_page_html() {
    // check user capabilities
    if ( !wcusage_check_admin_access() ) {
        return;
    }
    $options = get_option( 'wcusage_options' );
    $setstatus = "";
    $coupon_code = "";
    // Post Submit Add Registration Form
    if ( isset( $_POST['_wpnonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
        if ( wp_verify_nonce( $nonce, 'admin_add_registration_form' ) && wcusage_check_admin_access() ) {
            // Get the post field values
            $post_field_values = wcusage_registration_form_post_get_fields( 1 );
            $username = sanitize_text_field( $post_field_values['username'] );
            $email = sanitize_text_field( $post_field_values['email'] );
            if ( wcusage_check_admin_access() ) {
                // Check if email exists, if so swap the username
                $user = get_user_by( 'email', $email );
                if ( $user ) {
                    $username = $user->user_login;
                    $_POST['wcu-input-username'] = $username;
                }
            }
            echo wcusage_post_submit_application( 1 );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            // Redirect to admin.php?page=wcusage_affiliates
            $redirect_user = ( isset( $_POST['wcu-input-username'] ) ? sanitize_text_field( wp_unslash( $_POST['wcu-input-username'] ) ) : '' );
            $redirect_url = admin_url( 'admin.php?page=wcusage_affiliates&success=1&user=' . $redirect_user );
            // Redirect via PHP
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }
    // Get POST requests
    // Handle JS-proxied bulk accept to avoid nested form issues with row forms
    if ( isset( $_POST['_wpnonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
        if ( wp_verify_nonce( $nonce, 'wcusage_bulk_accept' ) && wcusage_check_admin_access() ) {
            $selected = ( isset( $_POST['registrations'] ) ? array_map( 'absint', (array) $_POST['registrations'] ) : array() );
            $action = ( isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : 'accept' );
            // Optional per-row overrides from UI
            $coupon_overrides = ( isset( $_POST['coupon_for'] ) && is_array( $_POST['coupon_for'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['coupon_for'] ) ) : array() );
            $message_overrides = ( isset( $_POST['message_for'] ) && is_array( $_POST['message_for'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['message_for'] ) ) : array() );
            $type_overrides = ( isset( $_POST['type_for'] ) && is_array( $_POST['type_for'] ) ? array_map( 'absint', $_POST['type_for'] ) : array() );
            $done_count = 0;
            $skipped_count = 0;
            $errors = array();
            if ( !empty( $selected ) ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'wcusage_register';
                foreach ( $selected as $sel_id ) {
                    if ( !$sel_id ) {
                        continue;
                    }
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $sel_id ), ARRAY_A );
                    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                    if ( !$row ) {
                        $skipped_count++;
                        continue;
                    }
                    $status_now = ( isset( $row['status'] ) ? $row['status'] : '' );
                    if ( ($action === 'accept' || $action === 'decline') && in_array( $status_now, ['accepted', 'declined'], true ) ) {
                        $skipped_count++;
                        continue;
                    }
                    $userid = ( isset( $row['userid'] ) ? absint( $row['userid'] ) : 0 );
                    $get_user = ( $userid ? get_user_by( 'id', $userid ) : false );
                    $user_email = ( $get_user && isset( $get_user->user_email ) ? $get_user->user_email : '' );
                    if ( $action === 'delete' ) {
                        wcusage_delete_registration_entry( $sel_id );
                        $done_count++;
                        continue;
                    }
                    if ( $action === 'decline' ) {
                        $wpdb->update( $table_name, array(
                            'status' => 'declined',
                        ), array(
                            'id' => $sel_id,
                        ) );
                        $wpdb->update( $table_name, array(
                            'dateaccepted' => date( 'Y-m-d H:i:s' ),
                        ), array(
                            'id' => $sel_id,
                        ) );
                        if ( function_exists( 'wcusage_install_mlainvite_data' ) && $user_email ) {
                            wcusage_install_mlainvite_data(
                                '',
                                $user_email,
                                'declined',
                                1
                            );
                        }
                        $message = ( isset( $message_overrides[$sel_id] ) ? sanitize_text_field( $message_overrides[$sel_id] ) : '' );
                        $coupon_for_email = ( isset( $row['couponcode'] ) ? $row['couponcode'] : '' );
                        if ( function_exists( 'wcusage_email_affiliate_register_declined' ) ) {
                            wcusage_email_affiliate_register_declined( $user_email, $coupon_for_email, $message );
                        }
                        do_action(
                            'wcusage_hook_affiliate_register_declined',
                            $sel_id,
                            $userid,
                            $coupon_for_email,
                            $message,
                            'declined'
                        );
                        $done_count++;
                        continue;
                    }
                    // Accept (default)
                    $coupon_code = '';
                    if ( isset( $coupon_overrides[$sel_id] ) && $coupon_overrides[$sel_id] !== '' ) {
                        $coupon_code = sanitize_text_field( $coupon_overrides[$sel_id] );
                    } elseif ( isset( $row['couponcode'] ) ) {
                        $coupon_code = sanitize_text_field( $row['couponcode'] );
                    }
                    if ( empty( $coupon_code ) ) {
                        $username_for_code = ( $get_user && isset( $get_user->user_login ) ? $get_user->user_login : '' );
                        // For bulk processing, try to get first/last name from the user object or registration row
                        $first_name_bulk = '';
                        $last_name_bulk = '';
                        if ( $get_user && isset( $get_user->ID ) ) {
                            $first_name_bulk = get_user_meta( $get_user->ID, 'first_name', true );
                            $last_name_bulk = get_user_meta( $get_user->ID, 'last_name', true );
                        }
                        $coupon_code = ( function_exists( 'wcusage_generate_auto_coupon' ) ? wcusage_generate_auto_coupon( $username_for_code, $first_name_bulk, $last_name_bulk ) : wcusage_url_shorten_random( 7 ) );
                    }
                    $type_num = 1;
                    if ( isset( $type_overrides[$sel_id] ) && $type_overrides[$sel_id] > 0 ) {
                        $type_num = (int) $type_overrides[$sel_id];
                    } else {
                        $stored_type = ( isset( $row['type'] ) ? sanitize_text_field( $row['type'] ) : '' );
                        $wcusage_coupon_multiple = wcusage_get_setting_value( 'wcusage_field_registration_multiple_template', '0' );
                        if ( $wcusage_coupon_multiple ) {
                            for ($x = 1; $x <= 10; $x++) {
                                $template_num = ( $x === 1 ? '' : '_' . $x );
                                $template_value = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template' . $template_num, '' );
                                if ( $template_value && $template_value === $stored_type ) {
                                    $type_num = $x;
                                    break;
                                }
                            }
                        }
                    }
                    $message = ( isset( $message_overrides[$sel_id] ) ? sanitize_text_field( $message_overrides[$sel_id] ) : '' );
                    if ( function_exists( 'wc_get_coupon_id_by_code' ) && wc_get_coupon_id_by_code( $coupon_code ) ) {
                        $errors[] = sprintf( esc_html__( 'Skipped %s - coupon already exists.', 'woo-coupon-usage' ), esc_html( $coupon_code ) );
                        $skipped_count++;
                        continue;
                    }
                    wcusage_set_registration_status(
                        'accepted',
                        $sel_id,
                        $userid,
                        $coupon_code,
                        $message,
                        $type_num
                    );
                    if ( function_exists( 'wcusage_install_mlainvite_data' ) ) {
                        if ( $get_user && $get_user->user_email ) {
                            wcusage_install_mlainvite_data(
                                '',
                                $get_user->user_email,
                                'accepted',
                                1
                            );
                        }
                    }
                    wcusage_set_registration_role( $userid );
                    if ( $coupon_code ) {
                        $wpdb->update( $table_name, array(
                            'couponcode' => $coupon_code,
                        ), array(
                            'id' => $sel_id,
                        ) );
                    }
                    $done_count++;
                }
            }
            if ( $action === 'accept' ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Bulk accept complete. Accepted: %1$d. Skipped: %2$d.', 'woo-coupon-usage' ), intval( $done_count ), intval( $skipped_count ) ) . '</p>' . (( !empty( $errors ) ? '<ul style="margin-left: 20px;"><li>' . implode( '</li><li>', array_map( 'wp_kses_post', $errors ) ) . '</li></ul>' : '' )) . '</div>';
            } elseif ( $action === 'decline' ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Bulk decline complete. Declined: %1$d. Skipped: %2$d.', 'woo-coupon-usage' ), intval( $done_count ), intval( $skipped_count ) ) . '</p></div>';
            } elseif ( $action === 'delete' ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Bulk delete complete. Deleted: %1$d. Skipped: %2$d.', 'woo-coupon-usage' ), intval( $done_count ), intval( $skipped_count ) ) . '</p></div>';
            }
        } elseif ( wp_verify_nonce( $nonce, 'admin_affiliate_register_form' ) && wcusage_check_admin_access() ) {
            // Collect common POST fields for single accept/decline actions
            if ( isset( $_POST['submitregisteraccept'] ) || isset( $_POST['submitregisterdecline'] ) ) {
                $postid = ( isset( $_POST['wcu-id'] ) ? sanitize_text_field( $_POST['wcu-id'] ) : '' );
                $userid = ( isset( $_POST['wcu-user-id'] ) ? sanitize_text_field( $_POST['wcu-user-id'] ) : '' );
                $get_user = ( $userid ? get_user_by( 'id', $userid ) : false );
                $coupon_code = ( isset( $_POST['wcu-coupon-code'] ) ? sanitize_text_field( $_POST['wcu-coupon-code'] ) : '' );
                $message = ( isset( $_POST['wcu-message'] ) ? sanitize_text_field( $_POST['wcu-message'] ) : '' );
                $type = ( isset( $_POST['wcu-type'] ) ? sanitize_text_field( $_POST['wcu-type'] ) : '' );
                // Auto-generate coupon if empty
                if ( empty( $coupon_code ) ) {
                    $username_for_code = ( $get_user && isset( $get_user->user_login ) ? $get_user->user_login : '' );
                    // Get first/last name from POST if available
                    $first_name_post = ( isset( $_POST['wcu-input-first-name'] ) ? sanitize_text_field( $_POST['wcu-input-first-name'] ) : '' );
                    $last_name_post = ( isset( $_POST['wcu-input-last-name'] ) ? sanitize_text_field( $_POST['wcu-input-last-name'] ) : '' );
                    if ( function_exists( 'wcusage_generate_auto_coupon' ) ) {
                        $coupon_code = wcusage_generate_auto_coupon( $username_for_code, $first_name_post, $last_name_post );
                    } else {
                        $coupon_code = wcusage_url_shorten_random( 7 );
                    }
                }
            }
            // If Accepted
            if ( isset( $_POST['submitregisteraccept'] ) ) {
                $status = "accepted";
                // Check for existing coupon and abort if duplicate
                if ( function_exists( 'wc_get_coupon_id_by_code' ) && wc_get_coupon_id_by_code( $coupon_code ) ) {
                    echo "<div class='notice notice-error is-dismissible' style='position: absolute; width: 75%;'><p>" . esc_html__( 'Coupon code already exists: ', 'woo-coupon-usage' ) . esc_html( $coupon_code ) . "</p></div>";
                } else {
                    // Proceed with accept flow
                    // Update the status of the registration (also creates the coupon from template)
                    $setstatus = wcusage_set_registration_status(
                        $status,
                        $postid,
                        $userid,
                        $coupon_code,
                        $message,
                        $type
                    );
                    // Update MLA invite
                    if ( function_exists( 'wcusage_install_mlainvite_data' ) ) {
                        if ( $get_user && $get_user->user_email ) {
                            wcusage_install_mlainvite_data(
                                '',
                                $get_user->user_email,
                                'accepted',
                                1
                            );
                        }
                    }
                    // Update users role
                    wcusage_set_registration_role( $userid );
                    // Update Code in Registration
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'wcusage_register';
                    $wpdb->update( $table_name, array(
                        'couponcode' => $coupon_code,
                    ), array(
                        'id' => $postid,
                    ) );
                    // Custom Action
                    do_action(
                        'wcusage_hook_registration_accepted',
                        $userid,
                        $coupon_code,
                        $type
                    );
                }
            }
            // If Declined
            if ( isset( $_POST['submitregisterdecline'] ) ) {
                $status = "declined";
                // Update the status of the registration
                $setstatus = wcusage_set_registration_status(
                    $status,
                    $postid,
                    $userid,
                    $coupon_code,
                    $message,
                    $type
                );
                // Update MLA invite
                if ( function_exists( 'wcusage_install_mlainvite_data' ) ) {
                    if ( $get_user && $get_user->user_email ) {
                        wcusage_install_mlainvite_data(
                            '',
                            $get_user->user_email,
                            'declined',
                            1
                        );
                    }
                }
                // Custom Action
                do_action( 'wcusage_hook_registration_declined', $userid, esc_html( $coupon_code ) );
            }
            // If Deleted
            if ( isset( $_POST['submitregisterdelete'] ) ) {
                $postid = sanitize_text_field( $_POST['wcu-id'] );
                // Delete the registration
                $setstatus = wcusage_delete_registration_entry( $postid );
            }
        }
    }
    // Enqueue styles for registrations list page (buttons layout and alignment)
    add_action( 'admin_enqueue_scripts', 'wcusage_enqueue_registrations_admin_assets' );
    function wcusage_enqueue_registrations_admin_assets(  $hook  ) {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_registrations' ) {
            $style = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-registrations.css';
            $ver = ( file_exists( $style ) ? filemtime( $style ) : '1.0.0' );
            wp_enqueue_style(
                'wcusage-admin-registrations',
                WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-registrations.css',
                array(),
                $ver
            );
        }
    }

    $statussearch = "";
    if ( isset( $_GET['status'] ) ) {
        $statussearch = sanitize_text_field( wp_unslash( $_GET['status'] ) );
    }
    ?>

<!-- Check Promote Field Enabled -->
<?php 
    $wcusage_registration_enable_promote = wcusage_get_setting_value( 'wcusage_field_registration_enable_promote', '0' );
    if ( !$wcusage_registration_enable_promote ) {
        echo "<style>.column-promote { display: none; }</style>";
    }
    ?>

<!-- Check Referrer Field Enabled -->
<?php 
    $wcusage_registration_enable_referrer = wcusage_get_setting_value( 'wcusage_field_registration_enable_referrer', '0' );
    if ( !$wcusage_registration_enable_referrer ) {
        echo "<style>.column-referrer { display: none; }</style>";
    }
    ?>

<!-- Check Website Field Enabled -->
<?php 
    $wcusage_registration_enable_website = wcusage_get_setting_value( 'wcusage_field_registration_enable_website', '0' );
    if ( !$wcusage_registration_enable_website ) {
        echo "<style>.column-website { display: none; }</style>";
    }
    ?>

<style type="text/css">
.column-id { width: 50px; }
.column-payment { width: 15%; }
.column-date, .column-datepaid { width: 200px; }
</style>

<link rel="stylesheet" href="<?php 
    echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
    ?>" crossorigin="anonymous">

<div id="wcu-create-new-registration" class="wrap plugin-settings">

  <?php 
    do_action( 'wcusage_hook_dashboard_page_header', '' );
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>

  <h1 class="wp-heading-inline"><?php 
    echo esc_html( get_admin_page_title() );
    ?>
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_add_affiliate' ) );
    ?>" class="wcusage-settings-button" id="wcu-admin-create-registration-link">
      <?php 
    echo sprintf( esc_html__( 'Add New %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
    ?>
    </a>
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_affiliates' ) );
    ?>" class="wcusage-settings-button" id="wcu-admin-create-registration-link">
        <?php 
    echo sprintf( esc_html__( 'Manage %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'Affiliates', 'woo-coupon-usage' ), true ) ) );
    ?>
    </a>
  </h1>

  <?php 
    do_action( 'wcusage_hook_admin_new_registration_button' );
    // "Create New Registration" button action
    ?>


  <?php 
    if ( isset( $_POST['submitregisteraccept'] ) ) {
        echo wp_kses_post( $setstatus );
        echo "<style>.wcusage-register-form-title { display: none; }</style>";
    }
    ?>

  <p style="color: #333;">
    <i class="fas fa-info-circle"></i> <?php 
    echo esc_html__( 'Accept and decline affiliate registrations submitted via the affiliate registration form.', 'woo-coupon-usage' );
    ?> <a href="https://couponaffiliates.com/docs/affiliate-registration" target="_blank">Learn More</a>.
  </p>

  <?php 
    $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
    if ( !$template_coupon_code ) {
        ?>
  <p style="color: #b11818; font-weight: bold;"><i class="fa-solid fa-circle-exclamation"></i> <?php 
        echo esc_html__( 'Warning: You will want to create a "template coupon" and assign it in the settings for affiliate coupons to be generated properly.', 'woo-coupon-usage' );
        ?> <a href="https://couponaffiliates.com/docs/template-coupon-code" target="_blank">Learn More</a>.</p>
  <?php 
    }
    ?>

  <?php 
    $get_template_coupon = wcusage_get_coupon_info( $template_coupon_code );
    ?>
  <?php 
    if ( $template_coupon_code && !$get_template_coupon[2] ) {
        ?>
    <p style="color: #b11818; font-weight: bold;"><span class="dashicons dashicons-warning"></span> <?php 
        echo esc_html__( 'The "template coupon" you have set does not exist. Please make sure you have created it, and entered the exact name in the settings.', 'woo-coupon-usage' );
        ?> <a href="https://couponaffiliates.com/docs/template-coupon-code" target="_blank"><?php 
        echo esc_html__( 'Learn More', 'woo-coupon-usage' );
        ?>.</a><br/></p>
  <?php 
    }
    ?>

  <div class="wcu-payout-filters" style="margin-bottom: -30px;">
    <br/><?php 
    echo esc_html__( 'Filter by', 'woo-coupon-usage' );
    ?>:
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_registrations' ) );
    ?>" style="<?php 
    if ( $statussearch == '' ) {
        ?>font-weight: bold;<?php 
    }
    ?>"><?php 
    echo esc_html__( 'All', 'woo-coupon-usage' );
    ?></a>
    &#xb7
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_registrations&status=accepted' ) );
    ?>" style="<?php 
    if ( $statussearch == 'accepted' ) {
        ?>font-weight: bold;<?php 
    }
    ?>"><?php 
    echo esc_html__( 'Accepted', 'woo-coupon-usage' );
    ?></a>
    &#xb7
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_registrations&status=pending' ) );
    ?>" style="<?php 
    if ( $statussearch == 'pending' ) {
        ?>font-weight: bold;<?php 
    }
    ?>"><?php 
    echo esc_html__( 'Pending', 'woo-coupon-usage' );
    ?></a>
    &#xb7;
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_registrations&status=parent_approved' ) );
    ?>" style="<?php 
    if ( $statussearch == 'parent_approved' ) {
        ?>font-weight: bold;<?php 
    }
    ?>"><?php 
    echo esc_html__( 'MLA Parent Approved', 'woo-coupon-usage' );
    ?></a>
    &#xb7;
    <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_registrations&status=declined' ) );
    ?>" style="<?php 
    if ( $statussearch == 'declined' ) {
        ?>font-weight: bold;<?php 
    }
    ?>"><?php 
    echo esc_html__( 'Declined', 'woo-coupon-usage' );
    ?></a>
  </div>

	<?php 
    if ( class_exists( 'wcusage_registrations_List_Table' ) ) {
        $testListTable = new wcusage_registrations_List_Table();
        $testListTable->prepare_items();
    }
    ?>

  <!-- Bulk actions toolbar (Apply via proxy form) -->
  <div class="tablenav top" style="margin: 55px 0 -40px 0; display:flex; gap:10px; align-items:center;">
    <div style="display:flex; gap:6px; align-items:center;">
      <label for="wcusage-bulk-select" class="screen-reader-text"><?php 
    echo esc_html__( 'Bulk actions', 'woo-coupon-usage' );
    ?></label>
      <select id="wcusage-bulk-select" class="bulk-actions">
        <option value="-1"><?php 
    echo esc_html__( 'Bulk actions', 'woo-coupon-usage' );
    ?></option>
        <option value="accept"><?php 
    echo esc_html__( 'Accept selected', 'woo-coupon-usage' );
    ?></option>
        <option value="decline"><?php 
    echo esc_html__( 'Decline selected', 'woo-coupon-usage' );
    ?></option>
        <option value="delete"><?php 
    echo esc_html__( 'Delete selected', 'woo-coupon-usage' );
    ?></option>
      </select>
      <button type="button" id="wcusage-bulk-apply" class="button action"><?php 
    echo esc_html__( 'Apply', 'woo-coupon-usage' );
    ?></button>
    </div>
  </div>

	<div id="icon-users" class="icon32"><br/></div>
	<?php 
    if ( isset( $testListTable ) ) {
        $testListTable->display();
    }
    ?>

  <!-- Hidden proxy form for bulk accepts (avoid nested forms with row actions) -->
  <form id="wcusage-bulk-proxy" method="post" style="display:none;">
    <?php 
    wp_nonce_field( 'wcusage_bulk_accept' );
    ?>
    <input type="hidden" name="action" value="accept" />
  </form>
  <script type="text/javascript">
  // Bulk actions UI logic
  jQuery(document).ready(function($){
    function getTableCheckboxes(){
      return $(".wp-list-table input[type='checkbox'][name='registration[]']");
    }
    function updateCheckboxAvailability(action){
      var $boxes = getTableCheckboxes();
      // Enable all by default
      $boxes.prop('disabled', false);
      if (action === 'accept' || action === 'decline') {
        $boxes.each(function(){
          var s = $(this).data('status');
          if (s === 'accepted' || s === 'declined') {
            $(this).prop('checked', false).prop('disabled', true);
          }
        });
      }
    }
    $('#wcusage-bulk-select').on('change', function(){ updateCheckboxAvailability($(this).val()); });

    // Custom bulk Apply -> post to hidden proxy form
    $('#wcusage-bulk-apply').on('click', function(e){
      e.preventDefault();
      var action = $('#wcusage-bulk-select').val();
      if (action === '-1') { return; }
      var $checked = $(".wp-list-table input[type='checkbox'][name='registration[]']:checked");
      if ($checked.length === 0) { return; }
      if (action === 'delete') {
        var confirmMsg = "<?php 
    echo esc_js( __( 'Are you sure you want to delete all these registration entries?', 'woo-coupon-usage' ) );
    ?>\n\n<?php 
    echo esc_js( __( 'This will only remove the entry from this page. It will not remove the affiliate user or coupon code.', 'woo-coupon-usage' ) );
    ?>";
        if (!window.confirm(confirmMsg)) { return; }
      }
      var $form = $('#wcusage-bulk-proxy');
      // ensure action value is set
      if ($form.find("input[name='action']").length) {
        $form.find("input[name='action']").val(action);
      } else {
        $('<input>').attr({type:'hidden', name:'action', value:action}).appendTo($form);
      }
      // clear old
      $form.find("input[name='registrations[]'], input[name^='coupon_for'], input[name^='message_for'], input[name^='type_for']").remove();
      $checked.each(function(){
        var id = $(this).val();
        var $tr = $(this).closest('tr');
        var coupon = $tr.find("input[name='wcu-coupon-code']").val() || '';
        var message = $tr.find("input[name='wcu-message']").val() || '';
        var type = $tr.find("input[name='wcu-type']").val() || '';
        $('<input>').attr({type:'hidden', name:'registrations[]', value:id}).appendTo($form);
        // Only include coupon/type when needed
        if (action === 'accept') {
          $('<input>').attr({type:'hidden', name:'coupon_for['+id+']', value:coupon}).appendTo($form);
          if (type !== '') {
            $('<input>').attr({type:'hidden', name:'type_for['+id+']', value:type}).appendTo($form);
          }
        }
        // Include message for accept/decline
        if (action === 'accept' || action === 'decline') {
          $('<input>').attr({type:'hidden', name:'message_for['+id+']', value:message}).appendTo($form);
        }
      });
      $form.trigger('submit');
    });

    // Intercept Bulk Action Apply buttons and submit via proxy hidden form
    function submitBulkProxy(which){
      var selectName = which === 'top' ? 'action' : 'action2';
      var $sel = $("select[name='"+selectName+"']");
      var val = $sel.val();
      if (val !== 'accept') { return true; }
      var $checked = $(".wp-list-table input[type='checkbox'][name='registration[]']:checked");
      if ($checked.length === 0) { return false; }
  // We primarily use the custom toolbar above; keeping this for compatibility is optional.
  return true;
      return false;
    }

    $(document).on('click', '#doaction', function(e){ if (submitBulkProxy('top') === false) { e.preventDefault(); } });
    $(document).on('click', '#doaction2', function(e){ if (submitBulkProxy('bottom') === false) { e.preventDefault(); } });
  });
  </script>
  

</div>

<?php 
}

/**
 * Updates users role on affiliate registration accept
 *
 */
function wcusage_set_registration_role(  $userid  ) {
    $wcusage_register_role = wcusage_get_setting_value( 'wcusage_field_register_role', '1' );
    $u = new WP_User($userid);
    if ( $wcusage_register_role ) {
        $wcusage_field_registration_accepted_role = wcusage_get_setting_value( 'wcusage_field_registration_accepted_role', 'coupon_affiliate' );
        $wcusage_field_register_role_only_accept = wcusage_get_setting_value( 'wcusage_field_register_role_only_accept', '0' );
        $wcusage_field_registration_pending_role = wcusage_get_setting_value( 'wcusage_field_registration_pending_role', 'subscriber' );
        $wcusage_field_register_role_remove_pending = wcusage_get_setting_value( 'wcusage_field_register_role_remove_pending', '1' );
        if ( $wcusage_field_registration_accepted_role == 'administrator' || $wcusage_field_registration_accepted_role == 'editor' || $wcusage_field_registration_accepted_role == 'author' || $wcusage_field_registration_accepted_role == 'shop_manager' ) {
            $wcusage_field_registration_accepted_role == "coupon_affiliate";
        }
        if ( $role_object = get_role( $wcusage_field_registration_accepted_role ) ) {
            if ( $role_object->has_cap( 'manage_options' ) ) {
                $wcusage_field_registration_accepted_role = 'coupon_affiliate';
            }
        }
        if ( $wcusage_field_register_role_only_accept && $wcusage_field_register_role_remove_pending ) {
            $u->remove_role( 'subscriber' );
            $u->remove_role( $wcusage_field_registration_pending_role );
        }
        $u->add_role( $wcusage_field_registration_accepted_role );
    } else {
        $u->remove_role( 'subscriber' );
        $u->add_role( 'subscriber' );
    }
}

/**
 * Updates registration status
 *
 */
add_action(
    'wcusage_hook_set_registration_status',
    'wcusage_set_registration_status',
    10,
    6
);
function wcusage_set_registration_status(
    $status,
    $id,
    $userid,
    $coupon_code,
    $message = "",
    $type = ""
) {
    if ( !$coupon_code ) {
        return;
    }
    $options = get_option( 'wcusage_options' );
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_register';
    $data = [
        'status' => $status,
    ];
    $where = [
        'id' => $id,
    ];
    $wpdb->update( $table_name, $data, $where );
    $data2 = [
        'dateaccepted' => date( 'Y-m-d H:i:s' ),
    ];
    $where2 = [
        'id' => $id,
    ];
    $wpdb->update( $table_name, $data2, $where2 );
    if ( !$status ) {
        $status = "";
    }
    if ( !$userid ) {
        $userid = $wpdb->get_var( $wpdb->prepare( "SELECT userid FROM {$table_name} WHERE id = %d", $id ) );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    }
    if ( !$coupon_code ) {
        $coupon_code = $wpdb->get_var( $wpdb->prepare( "SELECT couponcode FROM {$table_name} WHERE id = %d", $id ) );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    }
    $user_info = get_userdata( $userid );
    if ( is_object( $user_info ) ) {
        $user_email = $user_info->user_email;
        $username = $user_info->user_login;
    } else {
        $user_email = "";
        $username = "";
    }
    $name = "";
    if ( isset( $user_info->display_name ) ) {
        $name = $user_info->display_name;
    }
    if ( $status == "accepted" ) {
        do_action(
            'wcusage_hook_affiliate_register_accepted',
            $id,
            $userid,
            $coupon_code,
            $message,
            $status
        );
        $activity_log = wcusage_add_activity( $id, 'registration_accept', $username );
        wcusage_email_affiliate_register_accepted(
            $user_email,
            $coupon_code,
            $message,
            $username,
            $name
        );
        $wcusage_coupon_multiple = wcusage_get_setting_value( 'wcusage_field_registration_multiple_template', '0' );
        if ( !$type || !$wcusage_coupon_multiple ) {
            $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
        } else {
            if ( $type == 1 ) {
                $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
            } else {
                $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template_' . $type, '' );
            }
        }
        $template_coupon_info = wcusage_get_coupon_info( $template_coupon_code );
        $template_post_id = $template_coupon_info[2];
        if ( !$template_coupon_info ) {
            // No template coupon selected
            return "<div class='notice notice-error is-dismissible'><p>" . esc_html__( 'Coupon code was not created. Template has not been selected.', 'woo-coupon-usage' ) . " " . $coupon_code . "</p></div>";
        } else {
        }
        // Generating the new coupon
        $title = $coupon_code;
        $post = array(
            'post_title'  => $title,
            'post_status' => 'publish',
            'post_type'   => 'shop_coupon',
            'post_author' => 1,
        );
        $new_post_id = wp_insert_post( $post );
        // Copy meta from template
        if ( isset( $template_post_id ) ) {
            $data = get_post_custom( $template_post_id );
            if ( is_array( $data ) ) {
                foreach ( $data as $key => $values ) {
                    foreach ( $values as $value ) {
                        if ( is_serialized( $value ) ) {
                            $value = unserialize( $value );
                        }
                        add_post_meta( $new_post_id, $key, $value );
                    }
                }
            }
        }
        // Update defaults
        if ( is_numeric( $userid ) ) {
            update_post_meta( $new_post_id, 'wcu_select_coupon_user', $userid );
            // Clear the affiliate users cache when a new coupon is assigned to a user
            if ( function_exists( 'wcusage_clear_coupon_users_cache' ) ) {
                wcusage_clear_coupon_users_cache( $userid );
            }
        } else {
            error_log( "User ID is not numeric: " . $userid );
        }
        update_post_meta( $new_post_id, 'wcu_text_unpaid_commission', '0' );
        update_post_meta( $new_post_id, 'wcu_text_pending_payment_commission', '0' );
        update_post_meta( $new_post_id, 'usage_count', '0' );
        delete_post_meta( $new_post_id, 'wcu_alltime_stats' );
        delete_post_meta( $new_post_id, 'wcu_last_refreshed' );
        if ( wcu_fs()->is_free_plan() ) {
            update_post_meta( $new_post_id, 'wcu_text_coupon_commission', '' );
            update_post_meta( $new_post_id, 'wcu_text_coupon_commission_fixed_order', '' );
            update_post_meta( $new_post_id, 'wcu_text_coupon_commission_fixed_product', '' );
        }
        update_post_meta( $new_post_id, 'wcu_last_refreshed', time() );
        $wcu_alltime_stats = array();
        $wcu_alltime_stats['total_orders'] = 0;
        $wcu_alltime_stats['full_discount'] = 0;
        $wcu_alltime_stats['total_commission'] = 0;
        $wcu_alltime_stats['total_shipping'] = 0;
        $wcu_alltime_stats['total_count'] = 0;
        $wcu_alltime_stats['commission_summary'] = array();
        update_post_meta( $new_post_id, 'wcu_alltime_stats', $wcu_alltime_stats );
        $combined_commission = wcusage_commission_message( $new_post_id );
        update_post_meta( $new_post_id, 'wcu_commission_message', $combined_commission );
        do_action(
            'wcusage_hook_affiliate_register_added',
            $id,
            $userid,
            $coupon_code,
            $message,
            $status
        );
        return "<div class='notice notice-success is-dismissible'><p>" . esc_html__( 'Coupon code successfully created:', 'woo-coupon-usage' ) . " " . $coupon_code . "</p></div>";
    } else {
        do_action(
            'wcusage_hook_affiliate_register_declined',
            $id,
            $userid,
            $coupon_code,
            $message,
            $status
        );
        wcusage_email_affiliate_register_declined( $user_email, $coupon_code, $message );
    }
}

/**
 * Deletes a registration table row
 *
 * @param int $id
 *
 */
function wcusage_delete_registration_entry(  $id  ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_register';
    $where = [
        'id' => $id,
    ];
    $wpdb->delete( $table_name, $where );
}

/**
 * Generates auto coupon code for affiliate registration
 *
 * @param string $username Username or email to generate coupon for
 * @param string $first_name First name from POST data (optional)
 * @param string $last_name Last name from POST data (optional)
 * @return string Generated coupon code
 * 
 * Supported merge tags:
 * {username} - User's login name (sanitized)
 * {amount} - Discount amount from template coupon
 * {random} - Random 7-character string
 * {first_name} - User's first name (sanitized)
 * {last_name} / {Last_name} - User's last name (sanitized, case-insensitive)
 * {first_name_initial} - First letter of first name
 * {last_name_initial} - First letter of last name
 */
function wcusage_generate_auto_coupon(  $username = "", $first_name = "", $last_name = ""  ) {
    return wcusage_url_shorten_random( 7 );
}

/**
 * Show "Add New Registration" page
 *
 * @param int $userid
 *
 * @return string
 *
 */
add_action( 'wcusage_hook_admin_new_registration_page', 'wcusage_admin_new_registration_page' );
function wcusage_admin_new_registration_page() {
    $auto_coupon = wcusage_get_setting_value( 'wcusage_field_registration_auto_coupon', '0' );
    $auto_coupon_format = wcusage_get_setting_value( 'wcusage_field_registration_auto_coupon_format', '{username}{amount}' );
    $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
    $wcusage_field_registration_emailusername = wcusage_get_setting_value( 'wcusage_field_registration_emailusername', '0' );
    $wcusage_field_registration_auto_coupon = wcusage_get_setting_value( 'wcusage_field_registration_auto_coupon', '0' );
    $wcusage_registration_page = wcusage_get_setting_value( 'wcusage_registration_page', '' );
    if ( !empty( $wcusage_registration_page ) ) {
        $registrationpage_url = get_permalink( $wcusage_registration_page );
    } else {
        $registrationpage_url = admin_url( 'admin.php?page=wcusage_registrations' );
    }
    // Post Submit Add Registration Form
    if ( isset( $_POST['_wpnonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
        if ( wp_verify_nonce( $nonce, 'admin_add_registration_form' ) && wcusage_check_admin_access() ) {
            // Get the post field values
            $post_field_values = wcusage_registration_form_post_get_fields( 1 );
            $username = sanitize_text_field( $post_field_values['username'] );
            $email = sanitize_text_field( $post_field_values['email'] );
            if ( wcusage_check_admin_access() ) {
                // Check if email exists, if so swap the username
                $user = get_user_by( 'email', $email );
                if ( $user ) {
                    $username = $user->user_login;
                    $_POST['wcu-input-username'] = $username;
                }
            }
            echo wcusage_post_submit_application( 1 );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            // Redirect to admin.php?page=wcusage_affiliates
            $redirect_url = admin_url( 'admin.php?page=wcusage_affiliates&success=1&user=' . $_POST['wcu-input-username'] );
            // Redirect via PHP
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }
    ?>
  <?php 
    // (enqueue moved to separate hook defined after this function)
    ?>

  <link rel="stylesheet" href="<?php 
    echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
    ?>" crossorigin="anonymous">
  
  <div class="wrap">

  <?php 
    do_action( 'wcusage_hook_dashboard_page_header', '' );
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>

  <div class="wcusage-page">

    <h1 id="wcu-add-new-affiliate"><?php 
    echo sprintf( esc_html__( 'Add New %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
    ?></h1>

    <p>
      <?php 
    echo sprintf( esc_html__( 'Use this form to create a new %s registration.', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
    ?>
    </p>

    <p><?php 
    echo sprintf( esc_html__( 'When completing this form, it will automatically submit an approved %s registration for that user, automatically creating the coupon and assigning them to it.', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
    ?></p>
    
    <p><?php 
    echo esc_html__( 'If the user does not exist, a new one will be created.', 'woo-coupon-usage' );
    ?></p>

    <!-- Notices -->

    <?php 
    if ( !$template_coupon_code ) {
        ?>

    <p style="color: red;">
      <span class="dashicons dashicons-warning"></span> <?php 
        echo esc_html__( 'For affiliate registrations to work properly, you will need to create a "template coupon" and assign it in the "registration" settings tab.', 'woo-coupon-usage' );
        ?> <a href="https://couponaffiliates.com/docs/template-coupon-code" target="_blank"><?php 
        echo esc_html__( 'Learn More', 'woo-coupon-usage' );
        ?>.</a><br/>
    </p>

    <a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_settings&section=tab-registration' ) );
        ?>" class="button button-primary"><?php 
        echo esc_html__( 'Go to Settings', 'woo-coupon-usage' );
        ?></a>

    <?php 
    } else {
        ?>
    
    <?php 
        $get_template_coupon = wcusage_get_coupon_info( $template_coupon_code );
        ?>
      <?php 
        if ( $template_coupon_code && !$get_template_coupon[2] ) {
            ?>
        <p style="color: red;">
          <span class="dashicons dashicons-warning"></span> <?php 
            echo esc_html__( 'The "template coupon" you have set does not exist. Please make sure you have created it, and entered the exact name in the settings.', 'woo-coupon-usage' );
            ?><br/><a href="https://couponaffiliates.com/docs/template-coupon-code" target="_blank"><?php 
            echo esc_html__( 'Learn More', 'woo-coupon-usage' );
            ?>.</a><br/>
        </p>
    <?php 
        }
        ?>
    
    <form method="post" class="wcu_form_affiliate_register" enctype="multipart/form-data">
      
      <?php 
        wp_nonce_field( 'admin_add_registration_form' );
        ?>

      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="wcu-input-username"><?php 
        echo esc_html__( 'Username', 'woo-coupon-usage' );
        ?></label></th>
          <td><input name="wcu-input-username" type="text" id="wcu-input-username" class="regular-text" value="" required>
          <br class="wcu-input-username-text"/><i style="font-size: 10px;" class="wcu-input-username-text">Enter either an existing user, or a new user to create a new account.</i></td>
        </tr>
        <tr class="wcu-add-affiliate-email">
          <th scope="row"><label for="wcu-input-email"><?php 
        echo esc_html__( 'Email Address', 'woo-coupon-usage' );
        ?></label></th>
          <td><input name="wcu-input-email" type="email" id="wcu-input-email" class="regular-text ltr" value="">
          <br class="wcu-input-email-text"><i style="font-size: 10px;" class="wcu-input-email-text">The email address for creating the new user account.</i></td>
        </tr>
        <tr class="wcu-add-affiliate-first-name">
          <th scope="row"><label for="wcu-input-first-name"><?php 
        echo esc_html__( 'First Name', 'woo-coupon-usage' );
        ?></label></th>
          <td><input name="wcu-input-first-name" type="text" id="wcu-input-first-name" class="regular-text" value="">
          <br/><i style="font-size: 10px;">The first name of the new user account.</i></td>
        </tr>
        <tr class="wcu-add-affiliate-last-name">
          <th scope="row"><label for="wcu-input-last-name"><?php 
        echo esc_html__( 'Last Name', 'woo-coupon-usage' );
        ?></label></th>
          <td><input name="wcu-input-last-name" type="text" id="wcu-input-last-name" class="regular-text" value="">
          <br/><i style="font-size: 10px;">The last name of the new user account.</i></td>
        </tr>
        <?php 
        if ( !$wcusage_field_registration_auto_coupon ) {
            ?>
        <tr>
          <th scope="row"><label for="wcu-input-coupon"><?php 
            echo esc_html__( 'Coupon Code', 'woo-coupon-usage' );
            ?></label></th>
          <td><input name="wcu-input-coupon" type="text" id="wcu-input-coupon" class="regular-text" value="" required>
          <br/><i style="font-size: 10px;">Enter the name of the coupon code that will be created.</i></td>
        </tr>
        <?php 
        } else {
            ?>
        <tr>
          <th scope="row"><label for="wcu-input-coupon"><?php 
            echo esc_html__( 'Coupon Code', 'woo-coupon-usage' );
            ?></label></th>
          <td><i style="font-size: 10px;">The coupon code will be automatically generated based on the format: <?php 
            echo esc_html( $auto_coupon_format );
            ?></i></td>
        </tr>
        <?php 
        }
        ?>
        
        <!-- Coupon Type -->
        <?php 
        $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
        ?>

        <!-- Affiliate Group -->
        <?php 
        // Loop through user roles that start with "coupon_affiliate"
        $affiliate_roles = array();
        $all_roles = wp_roles()->roles;
        foreach ( $all_roles as $key => $role ) {
            if ( strpos( $key, 'coupon_affiliate' ) === 0 ) {
                $affiliate_roles[] = $key;
            }
        }
        if ( $affiliate_roles && count( $affiliate_roles ) > 1 ) {
            ?>
          <tr>
            <th scope="row"><label for="wcu-input-role"><?php 
            echo esc_html__( 'Affiliate Group', 'woo-coupon-usage' );
            ?></label></th>
            <td>
                <select id="wcu-input-role" name="wcu-input-role">
                <option value=""><?php 
            echo esc_html__( '- Default -', 'woo-coupon-usage' );
            ?></option>
                <?php 
            foreach ( $affiliate_roles as $key => $role ) {
                $role_name = $all_roles[$role]['name'];
                ?>
                  <option value="<?php 
                echo esc_html( $role );
                ?>"><?php 
                echo esc_html( $role_name );
                ?></option>
                  <?php 
            }
            ?>
                </select>
                <br/><i style="font-size: 10px;">Select a custom group to assign the user to. Keep as default to use the normal settings.</i>
              </td>
              </tr>
          <?php 
        }
        ?>

        <tr>
          <th scope="row"><label for="wcu-message"><?php 
        echo esc_html__( 'Custom Message', 'woo-coupon-usage' );
        ?></label></th>
          <td><input name="wcu-message" type="text" id="wcu-message" class="regular-text" value="">
          <br/><i style="font-size: 10px;">A custom message sent to the affiliate in the welcome/accepted email.</i></td>
        </tr>

      </table>

      <p class="submit">
        <input type="submit" name="submitaffiliateapplication" id="wcu-register-button" class="button button-primary" value="<?php 
        echo sprintf( esc_html__( 'Add New %s', 'woo-coupon-usage' ), esc_html( wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
        ?>">
      </p>
    </form>

    <br/><br/><strong><?php 
        echo sprintf( wp_kses_post( __( 'Note: Your users can also register themselves as affiliates using the <a href="%s" target="_blank">affiliate registration form</a>.', 'woo-coupon-usage' ) ), esc_url( $registrationpage_url ) );
        ?></strong>

    <?php 
    }
    ?>

  </div>

  </div>

  <script type="text/javascript">
  // Check username existence and show/hide email and first name fields
  jQuery(document).ready(function($) {
      var usernameField = $('#wcu-input-username');
      var emailRow = $('.wcu-add-affiliate-email');
      var firstNameRow = $('.wcu-add-affiliate-first-name');
      var lastNameRow = $('.wcu-add-affiliate-last-name');

      function checkUsername() {
          var username = usernameField.val().trim();

          if (username.length === 0) {
              emailRow.show();
              firstNameRow.show();
              lastNameRow.show();
              return;
          }

          $.ajax({
              url: ajaxurl,
              method: 'POST',
              data: {
                  action: 'wcusage_check_username_exists',
                  username: username
              },
              success: function(response) {
                  if (response.success && response.data.exists) {
                      emailRow.hide();
                      firstNameRow.hide();
                      lastNameRow.hide();
                      // Show a message saying the username exists
                      $('.username-exists-message').remove(); // Remove any existing message
                      usernameField.after('<p class="username-exists-message" style="color: green; font-size: 12px; margin: 0;"><span class="fa fa-check-circle" style="color: green;"></span> ' + '<?php 
    echo esc_js( __( 'This is an existing user.', 'woo-coupon-usage' ) );
    ?>' + '</p>');
                      // Hide .wcu-input-username-text
                      $('.wcu-input-username-text').hide();
                      // Make email and first name not required
                      emailRow.find('input').removeAttr('required');
                      firstNameRow.find('input').removeAttr('required');
                      lastNameRow.find('input').removeAttr('required');
                      // Set to empty fields
                      $('#wcu-input-email').val('');
                      $('#wcu-input-first-name').val('');
                      $('#wcu-input-last-name').val('');
                    } else {
                      emailRow.show();
                      firstNameRow.show();
                      lastNameRow.show();
                      // Show a message saying the username does not exist
                      $('.username-exists-message').remove(); // Remove any existing message
                      usernameField.after('<p class="username-exists-message" style="color: orange; font-size: 12px; margin: 0;"><span class="fa fa-exclamation-circle" style="color: orange;"></span> ' + '<?php 
    echo esc_js( __( 'This username does not exist. A new user will be created.', 'woo-coupon-usage' ) );
    ?>' + '</p>');
                      // Show .wcu-input-username-text
                      $('.wcu-input-username-text').hide();
                      // Make email and first name required
                      emailRow.find('input').attr('required', true);
                      firstNameRow.find('input').attr('required', true);
                      lastNameRow.find('input').attr('required', true);
                    }
                    // If field is empty, show email and first name rows
                  if( username.length === 0) {
                      // Handle error
                      emailRow.show();
                      firstNameRow.show();
                      lastNameRow.show();
                      $('.username-exists-message').remove(); // Remove any existing message
                      usernameField.after('<p class="username-exists-message" style="color: red; font-size: 12px; margin: 0;"><span class="fa fa-times-circle" style="color: red;"></span> ' + '<?php 
    echo esc_js( __( 'Error checking username.', 'woo-coupon-usage' ) );
    ?>' + '</p>');
                      // Show .wcu-input-username-text
                      $('.wcu-input-username-text').show();
                  }
              }
          });
      }

      usernameField.on('change', function() {
          emailRow.show();
          firstNameRow.show();
          checkUsername();
      });

      // Run on page load (in case browser auto-fills)
      checkUsername();
  });

  // Check email existence
  jQuery(document).ready(function($) {
      var emailField = $('#wcu-input-email');
      var usernameField = $('#wcu-input-username');

      function checkEmail() {
          var email = emailField.val().trim();
          var username = usernameField.val().trim();

          if (email.length === 0) {
              $('.email-exists-message').remove();
              $('#wcu-register-button').prop('disabled', false);
              return;
          }

          $.ajax({
              url: ajaxurl,
              method: 'POST',
              data: {
                  action: 'wcusage_check_email_exists',
                  email: email,
                  username: username
              },
              success: function(response) {
                  if (response.success && response.data.exists) {
                      if (!response.data.matches_username && username.length > 0) {
                          // Email exists but doesn't match username
                          $('.email-exists-message').remove();
                          $('.wcu-input-email-text').hide();
                          emailField.after('<p class="email-exists-message" style="color: red; font-size: 12px; margin: 0;"><span class="fa fa-times-circle" style="color: red;"></span> ' + '<?php 
    echo esc_js( __( 'This email address is already associated with username: ', 'woo-coupon-usage' ) );
    ?>' + response.data.username + '</p>');
                          $('#wcu-register-button').prop('disabled', true);
                      } else {
                          // Email exists and matches username (or no username entered yet)
                          $('.email-exists-message').remove();
                          $('#wcu-register-button').prop('disabled', false);
                      }
                  } else {
                      // Email doesn't exist
                      $('.email-exists-message').remove();
                      $('#wcu-register-button').prop('disabled', false);
                  }
              }
          });
      }

      emailField.on('change', function() {
          checkEmail();
      });

      // Also check when username changes (in case email was entered first)
      usernameField.on('change', function() {
          if (emailField.val().trim().length > 0) {
              checkEmail();
          }
      });
  });

  // Check coupon code existence
  jQuery(document).ready(function($) {
      var couponField = $('#wcu-input-coupon');

      function checkCoupon() {
          var couponCode = couponField.val().trim();

          if (couponCode.length === 0) {
              return;
          }

          $.ajax({
              url: ajaxurl,
              method: 'POST',
              data: {
                  action: 'wcusage_check_coupon_exists',
                  coupon_code: couponCode
              },
              success: function(response) {
                  if (response.success && response.data.exists) {
                      // Show a message saying the coupon code exists
                      $('.coupon-exists-message').remove(); // Remove any existing message
                      couponField.after('<p class="coupon-exists-message" style="color: red; font-size: 12px; margin: 0;"><span class="fa fa-times-circle" style="color: red;"></span> ' + '<?php 
    echo esc_js( __( 'This coupon code already exists.', 'woo-coupon-usage' ) );
    ?>' + '</p>');
                      // Disable the submit button
                      $('#wcu-register-button').prop('disabled', true);
                    } else {
                      // Show a message saying the coupon code does not exist
                      $('.coupon-exists-message').remove(); // Remove any existing message
                      // Enable the submit button
                      $('#wcu-register-button').prop('disabled', false);
                    }
              }
          });
      }

      couponField.on('change', function() {
          checkCoupon();
      });

      // Run on page load (in case browser auto-fills)
      checkCoupon();
  });
  </script>

  

  <?php 
}

// Proper enqueue of username autocomplete assets
add_action( 'admin_enqueue_scripts', 'wcusage_enqueue_add_affiliate_autocomplete' );
function wcusage_enqueue_add_affiliate_autocomplete(  $hook  ) {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage_add_affiliate' ) {
        $script = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/admin-add-affiliate-autocomplete.js';
        $ver = ( file_exists( $script ) ? filemtime( $script ) : '1.0.' . date( 'Ymd' ) );
        wp_enqueue_script(
            'wcusage-admin-add-affiliate-autocomplete',
            WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-add-affiliate-autocomplete.js',
            array('jquery'),
            $ver,
            true
        );
        wp_localize_script( 'wcusage-admin-add-affiliate-autocomplete', 'WCUsageAffiliateAutocomplete', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'wcusage_search_usernames' ),
            'minChars'  => 2,
            'noResults' => __( 'No matches found', 'woo-coupon-usage' ),
        ) );
    }
}

// AJAX: search usernames for autocomplete on Add New Affiliate page
add_action( 'wp_ajax_wcusage_search_usernames', 'wcusage_search_usernames' );
function wcusage_search_usernames() {
    if ( !wcusage_check_admin_access() ) {
        wp_send_json_error( array(
            'message' => 'forbidden',
        ), 403 );
    }
    check_ajax_referer( 'wcusage_search_usernames', 'nonce' );
    global $wpdb;
    $term = ( isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '' );
    $results = array();
    if ( strlen( $term ) >= 2 ) {
        $like = '%' . $wpdb->esc_like( $term ) . '%';
        $users = $wpdb->get_results( $wpdb->prepare( "SELECT user_login, user_email, ID FROM {$wpdb->users} WHERE user_login LIKE %s OR user_email LIKE %s ORDER BY user_login ASC LIMIT 15", $like, $like ) );
        if ( $users ) {
            foreach ( $users as $u ) {
                $results[] = array(
                    'login' => $u->user_login,
                    'email' => $u->user_email,
                    'id'    => (int) $u->ID,
                );
            }
        }
    }
    wp_send_json_success( array(
        'results' => $results,
    ) );
}

// Check if username exists via AJAX
add_action( 'wp_ajax_wcusage_check_username_exists', 'wcusage_check_username_exists' );
function wcusage_check_username_exists() {
    if ( !wcusage_check_admin_access() ) {
        wp_send_json_error();
    }
    $username = ( isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '' );
    if ( username_exists( $username ) ) {
        wp_send_json_success( [
            'exists' => true,
        ] );
    } else {
        wp_send_json_success( [
            'exists' => false,
        ] );
    }
    wp_die();
}

// Check if coupon code exists via AJAX
add_action( 'wp_ajax_wcusage_check_coupon_exists', 'wcusage_check_coupon_exists' );
function wcusage_check_coupon_exists() {
    if ( !wcusage_check_admin_access() ) {
        wp_send_json_error();
    }
    $coupon_code = ( isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '' );
    if ( function_exists( 'wc_get_coupon_id_by_code' ) && wc_get_coupon_id_by_code( $coupon_code ) ) {
        wp_send_json_success( [
            'exists' => true,
        ] );
    } else {
        wp_send_json_success( [
            'exists' => false,
        ] );
    }
    wp_die();
}

// Check if email exists via AJAX
add_action( 'wp_ajax_wcusage_check_email_exists', 'wcusage_check_email_exists' );
function wcusage_check_email_exists() {
    if ( !wcusage_check_admin_access() ) {
        wp_send_json_error();
    }
    $email = ( isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '' );
    $username = ( isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '' );
    $user = get_user_by( 'email', $email );
    if ( $user ) {
        // Email exists, check if it matches the username
        $matches_username = $user->user_login === $username;
        wp_send_json_success( [
            'exists'           => true,
            'username'         => $user->user_login,
            'matches_username' => $matches_username,
        ] );
    } else {
        wp_send_json_success( [
            'exists' => false,
        ] );
    }
    wp_die();
}
