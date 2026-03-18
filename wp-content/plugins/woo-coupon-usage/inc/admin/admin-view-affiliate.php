<?php 
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Check user capabilities
if ( !wcusage_check_admin_access() ) {
    return;
}
// Get user ID from URL
$user_id = ( isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0 );
if ( !$user_id ) {
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid user ID.', 'woo-coupon-usage' ) . '</p></div>';
    return;
}
$user_info = get_userdata( $user_id );
if ( !$user_info ) {
    echo '<div class="notice notice-error"><p>' . esc_html__( 'User not found.', 'woo-coupon-usage' ) . '</p></div>';
    return;
}
// Get affiliate coupons
$coupons = wcusage_get_users_coupons_ids( $user_id );
// Handle form submission for user updates
if ( isset( $_POST['update_user'] ) && isset( $_POST['_wpnonce'] ) ) {
    if ( wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        // Update basic user information
        $user_data = array(
            'ID'         => $user_id,
            'user_email' => sanitize_email( $_POST['user_email'] ),
            'user_url'   => esc_url_raw( $_POST['user_url'] ),
        );
        // Update user
        $result = wp_update_user( $user_data );
        if ( !is_wp_error( $result ) ) {
            // Update user meta
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
            // Handle plugin-specific fields if the save function exists
            if ( function_exists( 'wcusage_save_profile_fields' ) ) {
                wcusage_save_profile_fields( $user_id );
            }
            // Handle bonus fields if the save function exists
            if ( function_exists( 'wcusage_save_custom_user_profile_fields' ) ) {
                wcusage_save_custom_user_profile_fields( $user_id );
            }
            echo '<div class="notice notice-success"><p>' . esc_html__( 'User updated successfully.', 'woo-coupon-usage' ) . '</p></div>';
            // Refresh user info
            $user_info = get_userdata( $user_id );
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Error updating user.', 'woo-coupon-usage' ) . '</p></div>';
        }
    }
}
// Handle form submission for adding new coupon
if ( isset( $_POST['add_new_coupon'] ) && isset( $_POST['add_coupon_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['add_coupon_nonce'], 'admin_add_coupon_for_affiliate' ) ) {
        $coupon_code = sanitize_text_field( $_POST['new_coupon_code'] );
        $affiliate_username = sanitize_text_field( $_POST['affiliate_username'] );
        $message = ( isset( $_POST['wcu-message'] ) ? sanitize_text_field( $_POST['wcu-message'] ) : '' );
        // Verify the affiliate username matches the current user
        if ( $affiliate_username !== $user_info->user_login ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid affiliate username.', 'woo-coupon-usage' ) . '</p></div>';
        } else {
            // Check if coupon already exists
            $existing_coupon = get_page_by_title( $coupon_code, OBJECT, 'shop_coupon' );
            if ( $existing_coupon ) {
                echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( 'Coupon "%s" already exists.', 'woo-coupon-usage' ), esc_html( $coupon_code ) ) . '</p></div>';
            } else {
                // Get template coupon settings
                $template_coupon_code = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
                if ( !$template_coupon_code ) {
                    echo '<div class="notice notice-error"><p>' . esc_html__( 'No template coupon configured. Please set up a template coupon in the settings.', 'woo-coupon-usage' ) . '</p></div>';
                } else {
                    // Create new coupon based on template
                    $template_coupon = get_page_by_title( $template_coupon_code, OBJECT, 'shop_coupon' );
                    if ( !$template_coupon ) {
                        echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( 'Template coupon "%s" not found.', 'woo-coupon-usage' ), esc_html( $template_coupon_code ) ) . '</p></div>';
                    } else {
                        // Get template coupon data
                        $template_coupon_obj = new WC_Coupon($template_coupon->ID);
                        $template_data = array(
                            'discount_type'              => $template_coupon_obj->get_discount_type(),
                            'coupon_amount'              => $template_coupon_obj->get_amount(),
                            'individual_use'             => $template_coupon_obj->get_individual_use(),
                            'product_ids'                => $template_coupon_obj->get_product_ids(),
                            'exclude_product_ids'        => $template_coupon_obj->get_excluded_product_ids(),
                            'usage_limit'                => $template_coupon_obj->get_usage_limit(),
                            'usage_limit_per_user'       => $template_coupon_obj->get_usage_limit_per_user(),
                            'limit_usage_to_x_items'     => $template_coupon_obj->get_limit_usage_to_x_items(),
                            'expiry_date'                => ( $template_coupon_obj->get_date_expires() ? $template_coupon_obj->get_date_expires()->date( 'Y-m-d' ) : '' ),
                            'free_shipping'              => $template_coupon_obj->get_free_shipping(),
                            'exclude_sale_items'         => $template_coupon_obj->get_exclude_sale_items(),
                            'product_categories'         => $template_coupon_obj->get_product_categories(),
                            'exclude_product_categories' => $template_coupon_obj->get_excluded_product_categories(),
                            'minimum_amount'             => $template_coupon_obj->get_minimum_amount(),
                            'maximum_amount'             => $template_coupon_obj->get_maximum_amount(),
                        );
                        // Create new coupon
                        $new_coupon = array(
                            'post_title'   => $coupon_code,
                            'post_content' => '',
                            'post_status'  => 'publish',
                            'post_author'  => 1,
                            'post_type'    => 'shop_coupon',
                        );
                        $new_coupon_id = wp_insert_post( $new_coupon );
                        if ( $new_coupon_id ) {
                            // Copy meta from template coupon
                            $template_meta = get_post_custom( $template_coupon->ID );
                            if ( is_array( $template_meta ) ) {
                                foreach ( $template_meta as $key => $values ) {
                                    foreach ( $values as $value ) {
                                        if ( is_serialized( $value ) ) {
                                            $value = unserialize( $value );
                                        }
                                        add_post_meta( $new_coupon_id, $key, $value );
                                    }
                                }
                            }
                            // Set affiliate-specific meta
                            update_post_meta( $new_coupon_id, 'wcu_select_coupon_user', $user_id );
                            update_post_meta( $new_coupon_id, 'wcu_text_unpaid_commission', '0' );
                            update_post_meta( $new_coupon_id, 'wcu_text_pending_payment_commission', '0' );
                            update_post_meta( $new_coupon_id, 'usage_count', '0' );
                            // Clear stats meta
                            delete_post_meta( $new_coupon_id, 'wcu_alltime_stats' );
                            delete_post_meta( $new_coupon_id, 'wcu_last_refreshed' );
                            // Send notification email to affiliate
                            if ( function_exists( 'wcusage_email_affiliate_register' ) ) {
                                $user_email = $user_info->user_email;
                                $firstname = get_user_meta( $user_id, 'first_name', true );
                                if ( empty( $firstname ) ) {
                                    $firstname = $user_info->display_name;
                                }
                                wcusage_email_affiliate_register(
                                    $user_email,
                                    $coupon_code,
                                    $firstname,
                                    $message
                                );
                            }
                            echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'Coupon "%s" created successfully and assigned to affiliate.', 'woo-coupon-usage' ), esc_html( $coupon_code ) ) . '</p></div>';
                            // Refresh coupons list
                            $coupons = wcusage_get_users_coupons_ids( $user_id );
                        } else {
                            echo '<div class="notice notice-error"><p>' . esc_html__( 'Error creating coupon.', 'woo-coupon-usage' ) . '</p></div>';
                        }
                    }
                }
            }
        }
    }
}
// Handle MLA per-user commission rates save
$wcusage_field_mla_per_user_group_rates = wcusage_get_setting_value( 'wcusage_field_mla_per_user_group_rates', '0' );
if ( $wcusage_field_mla_per_user_group_rates && isset( $_POST['wcusage_mla_user_rates_save'] ) && isset( $_POST['_wpnonce'] ) ) {
    if ( wp_verify_nonce( $_POST['_wpnonce'], 'wcusage_save_mla_user_rates_' . $user_id ) ) {
        // Save the enable toggle
        $custom_enabled = ( isset( $_POST['wcu_mla_custom_rates_enabled'] ) ? '1' : '0' );
        update_user_meta( $user_id, 'wcu_mla_custom_rates_enabled', $custom_enabled );
        // Only save tier fields when enabled
        $mla_tiersnumber = wcusage_get_setting_value( 'wcusage_field_mla_number_tiers', '5' );
        for ($i = 1; $i <= $mla_tiersnumber; $i++) {
            $tier_key = 'T' . $i;
            $tier_fields = array('wcu_mla_tier_percent_' . $tier_key, 'wcu_mla_tier_order_percent_' . $tier_key, 'wcu_mla_tier_fixed_' . $tier_key);
            foreach ( $tier_fields as $field ) {
                if ( isset( $_POST[$field] ) ) {
                    $value = sanitize_text_field( $_POST[$field] );
                    if ( $value === '' || $value === null ) {
                        delete_user_meta( $user_id, $field );
                    } else {
                        update_user_meta( $user_id, $field, $value );
                    }
                }
            }
        }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'MLA commission rates updated successfully.', 'woo-coupon-usage' ) . '</p></div>';
    }
}
// Handle individual delete actions (same options as Coupon Affiliate Users page)
if ( isset( $_POST['wcusage_delete_action'] ) && isset( $_POST['wcusage_user_id'] ) ) {
    $action = sanitize_text_field( $_POST['wcusage_delete_action'] );
    $delete_user_id = absint( $_POST['wcusage_user_id'] );
    $nonce = ( isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '' );
    if ( !wp_verify_nonce( $nonce, 'wcusage_delete_user_' . $delete_user_id ) ) {
        wp_die( 'Security check failed' );
    }
    if ( !wcusage_check_admin_access() ) {
        wp_die( 'Insufficient permissions' );
    }
    if ( $delete_user_id === get_current_user_id() ) {
        wp_die( 'You cannot delete your own account' );
    }
    $message = '';
    $coupons_for_user = wcusage_get_users_coupons_ids( $delete_user_id );
    switch ( $action ) {
        case 'delete_user':
            wp_delete_user( $delete_user_id );
            $message = 'User deleted successfully.';
            break;
        case 'delete_user_coupons':
            wp_delete_user( $delete_user_id );
            if ( $coupons_for_user ) {
                foreach ( $coupons_for_user as $c_id ) {
                    wp_delete_post( $c_id );
                }
            }
            $message = 'User and associated coupons deleted successfully.';
            break;
        case 'unassign_coupons':
            if ( $coupons_for_user ) {
                foreach ( $coupons_for_user as $c_id ) {
                    $c_obj = new WC_Coupon($c_id);
                    $c_obj->update_meta_data( 'wcu_select_coupon_user', '' );
                    $c_obj->save();
                }
            }
            $message = 'Coupons unassigned from user successfully.';
            break;
        case 'delete_coupons':
            if ( $coupons_for_user ) {
                foreach ( $coupons_for_user as $c_id ) {
                    wp_delete_post( $c_id );
                }
            }
            $message = 'User\'s coupons deleted successfully.';
            break;
        default:
            $message = 'Invalid action.';
            break;
    }
    // Redirect with message
    $redirect = add_query_arg( 'wcusage_message', urlencode( $message ), esc_url_raw( admin_url( 'admin.php?page=wcusage_view_affiliate&user_id=' . $user_id ) ) );
    wp_safe_redirect( $redirect );
    exit;
}
// Get current tab
$current_tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview' );
?>

<!--- Font Awesome -->
<link rel="stylesheet" href="<?php 
echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
?>" crossorigin="anonymous">

    <?php 
// Enqueue admin view affiliate styles with cache-busting
$wcusage_admin_aff_css_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-view-affiliate.css';
$wcusage_admin_aff_css_ver = ( file_exists( $wcusage_admin_aff_css_path ) ? filemtime( $wcusage_admin_aff_css_path ) : WCUSAGE_VERSION );
wp_enqueue_style(
    'wcusage-admin-view-affiliate',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-view-affiliate.css',
    array(),
    $wcusage_admin_aff_css_ver
);
// Enqueue shared coupons quick-edit styles to match styling
$wcusage_coupons_css_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/admin-coupons.css';
$wcusage_coupons_css_ver = ( file_exists( $wcusage_coupons_css_path ) ? filemtime( $wcusage_coupons_css_path ) : WCUSAGE_VERSION );
wp_enqueue_style(
    'wcusage-coupons-shared',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'css/admin-coupons.css',
    array(),
    $wcusage_coupons_css_ver
);
// Enqueue admin view affiliate scripts
wp_enqueue_script( 'jquery-ui-autocomplete' );
$wcusage_admin_aff_js_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/admin-view-affiliate.js';
$wcusage_admin_aff_js_ver = ( file_exists( $wcusage_admin_aff_js_path ) ? filemtime( $wcusage_admin_aff_js_path ) : WCUSAGE_VERSION );
wp_enqueue_script(
    'wcusage-admin-view-affiliate',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin-view-affiliate.js',
    array('jquery'),
    $wcusage_admin_aff_js_ver,
    true
);
// Enqueue delete dropdown assets used in Coupon Affiliate Users page
$wcusage_delete_css_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'css/delete-dropdown.css';
$wcusage_delete_css_ver = ( file_exists( $wcusage_delete_css_path ) ? filemtime( $wcusage_delete_css_path ) : WCUSAGE_VERSION );
wp_enqueue_style(
    'wcusage-delete-dropdown',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'css/delete-dropdown.css',
    array(),
    $wcusage_delete_css_ver
);
$wcusage_admin_common_js_path = WCUSAGE_UNIQUE_PLUGIN_PATH . 'js/admin.js';
$wcusage_admin_common_js_ver = ( file_exists( $wcusage_admin_common_js_path ) ? filemtime( $wcusage_admin_common_js_path ) : WCUSAGE_VERSION );
wp_enqueue_script(
    'wcusage-admin-common',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'js/admin.js',
    array('jquery'),
    $wcusage_admin_common_js_ver,
    true
);
wp_localize_script( 'wcusage-admin-view-affiliate', 'WCUAdminAffiliateView', array(
    'ajax_url'                   => admin_url( 'admin-ajax.php' ),
    'user_id'                    => $user_id,
    'per_page'                   => 20,
    'coupon_nonce'               => wp_create_nonce( 'wcusage_coupon_nonce' ),
    'currency_symbol'            => get_woocommerce_currency_symbol(),
    'nonce_referrals'            => wp_create_nonce( 'wcusage_affiliate_referrals' ),
    'nonce_visits'               => wp_create_nonce( 'wcusage_affiliate_visits' ),
    'nonce_payouts'              => wp_create_nonce( 'wcusage_affiliate_payouts' ),
    'nonce_activity'             => wp_create_nonce( 'wcusage_affiliate_activity' ),
    'nonce_add_sub_affiliate'    => wp_create_nonce( 'wcusage_add_sub_affiliate_nonce' ),
    'nonce_remove_sub_affiliate' => wp_create_nonce( 'wcusage_remove_sub_affiliate_nonce' ),
) );
?>

    <div class="wrap wcusage-affiliate-view-page">

        <?php 
if ( isset( $_GET['wcusage_message'] ) ) {
    ?>
            <div class="notice notice-success is-dismissible"><p><?php 
    echo esc_html( wp_unslash( $_GET['wcusage_message'] ) );
    ?></p></div>
        <?php 
}
?>

        <?php 
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Action callbacks are responsible for proper escaping.
do_action( 'wcusage_hook_dashboard_page_header', '' );
?>

        <h1 class="wp-heading-inline"
        style="color: #23282d; font-size: 28px; font-weight: 600; margin-bottom: 10px; align-items: center; gap: 10px;">
            <?php 
echo get_avatar(
    $user_id,
    64,
    'identicon',
    '',
    array(
        'class' => 'wcusage-user-avatar',
        'style' => 'border-radius: 50%; margin-right: 10px; vertical-align: middle;',
    )
);
?>
            <span>
                <?php 
$affiliate_label = wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) );
echo esc_html( sprintf( __( '%s: %s', 'woo-coupon-usage' ), $affiliate_label, $user_info->user_login ) );
?>
            </span>
            <?php 
$portal_slug = wcusage_get_setting_value( 'wcusage_portal_slug', 'affiliate-portal' );
?>
            <?php 
$preview_nonce = wp_create_nonce( 'wcusage_preview_affiliate_' . $user_id );
?>
            <a href="<?php 
echo esc_url( home_url( '/' . $portal_slug . '/?userid=' . $user_id . '&preview_nonce=' . $preview_nonce ) );
?>"
            class="page-title-action wcusage-preview-button button-primary"
            style="margin-left: 15px; font-size: 12px; padding: 5px 10px;" target="_blank">
                <?php 
echo esc_html__( 'View affiliate dashboard', 'woo-coupon-usage' );
?>
                <i class="fas fa-external-link-alt" style="margin-left: 5px; font-size: 12px; height: 12px; margin-top: 5px;"></i>
            </a>
            <?php 
$wcusage_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', '0' );
?>
            <?php 
?>
        </h1>

        <?php 
// Delete dropdown actions for this affiliate (4 options)
$delete_nonce = wp_create_nonce( 'wcusage_delete_user_' . $user_id );
?>
        <div class="wcusage-view-affiliate-header-actions" style="float: right; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
            <a href="<?php 
echo esc_url( admin_url( 'admin.php?page=wcusage_affiliates' ) );
?>" class="page-title-action wcusage-back-button">
                <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                <?php 
echo esc_html__( 'Back to Affiliates', 'woo-coupon-usage' );
?>
            </a>
            <div class="wcusage-delete-dropdown" style="margin-top: 5px;">
                <button type="button" class="wcusage-delete-btn" data-user-id="<?php 
echo esc_attr( $user_id );
?>" title="<?php 
echo esc_attr__( 'Delete Options', 'woo-coupon-usage' );
?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="wcusage-delete-menu" style="display: none;">
                    <a href="#" class="wcusage-delete-option" data-action="delete_user" data-user-id="<?php 
echo esc_attr( $user_id );
?>" data-nonce="<?php 
echo esc_attr( $delete_nonce );
?>"><?php 
echo esc_html__( 'Delete User', 'woo-coupon-usage' );
?></a>
                    <a href="#" class="wcusage-delete-option" data-action="delete_user_coupons" data-user-id="<?php 
echo esc_attr( $user_id );
?>" data-nonce="<?php 
echo esc_attr( $delete_nonce );
?>"><?php 
echo esc_html__( 'Delete User & Coupons', 'woo-coupon-usage' );
?></a>
                    <a href="#" class="wcusage-delete-option" data-action="unassign_coupons" data-user-id="<?php 
echo esc_attr( $user_id );
?>" data-nonce="<?php 
echo esc_attr( $delete_nonce );
?>"><?php 
echo esc_html__( 'Unassign Coupons', 'woo-coupon-usage' );
?></a>
                    <a href="#" class="wcusage-delete-option" data-action="delete_coupons" data-user-id="<?php 
echo esc_attr( $user_id );
?>" data-nonce="<?php 
echo esc_attr( $delete_nonce );
?>"><?php 
echo esc_html__( 'Delete Coupons', 'woo-coupon-usage' );
?></a>
                </div>
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="wcusage-main-content">
            <!-- Left Content Area -->
            <div class="wcusage-content-left">
                <!-- Tabs -->
                <h2 class="nav-tab-wrapper wcusage-tabs">
                    <a href="#tab-overview" class="nav-tab <?php 
echo ( $current_tab === 'overview' ? 'nav-tab-active' : '' );
?>">
                        <i class="fas fa-chart-line" style="margin-right: 8px;"></i>
                        <?php 
echo esc_html__( 'Overview', 'woo-coupon-usage' );
?>
                    </a>
                    <a href="#tab-referrals" class="nav-tab <?php 
echo ( $current_tab === 'referrals' ? 'nav-tab-active' : '' );
?>">
                        <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>
                        <?php 
echo esc_html__( 'Referred Orders', 'woo-coupon-usage' );
?>
                    </a>
                    <a href="#tab-visits" class="nav-tab <?php 
echo ( $current_tab === 'visits' ? 'nav-tab-active' : '' );
?>">
                        <i class="fas fa-eye" style="margin-right: 8px;"></i>
                        <?php 
echo esc_html__( 'Visits', 'woo-coupon-usage' );
?>
                    </a>
                    <?php 
?>
                        <a href="#tab-payouts" class="nav-tab <?php 
echo ( $current_tab === 'payouts' ? 'nav-tab-active' : '' );
?> wcusage-tab-disabled" aria-disabled="true" tabindex="-1" title="<?php 
echo esc_attr__( 'Premium only', 'woo-coupon-usage' );
?>">
                            <i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>
                            <?php 
echo esc_html__( 'Payouts', 'woo-coupon-usage' );
?> (PRO)
                        </a>
                    <?php 
?>
                    <a href="#tab-activity" class="nav-tab <?php 
echo ( $current_tab === 'activity' ? 'nav-tab-active' : '' );
?>">
                        <i class="fas fa-history" style="margin-right: 8px;"></i>
                        <?php 
echo esc_html__( 'Activity', 'woo-coupon-usage' );
?>
                    </a>
                    <?php 
?>
                    <a href="#tab-edit-user" class="nav-tab <?php 
echo ( $current_tab === 'edit-user' ? 'nav-tab-active' : '' );
?>">
                        <i class="fas fa-user-edit" style="margin-right: 8px;"></i>
                        <?php 
echo esc_html__( 'Edit User', 'woo-coupon-usage' );
?>
                    </a>
                </h2>

                <!-- Tab Content -->
                <div class="wcusage-tab-content">
                    <!-- Overview Tab -->
                    <div id="tab-overview" class="tab-content <?php 
echo ( $current_tab === 'overview' ? 'active' : '' );
?>">
                        <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                            <i class="fas fa-chart-line" style="color: #007cba; margin-right: 10px;"></i>
                            <?php 
echo esc_html__( 'Statistics Overview', 'woo-coupon-usage' );
?>
                        </h3>

                        <?php 
wcusage_display_affiliate_stats( $user_id, 'all' );
?>
                    </div>

                    <!-- Latest Referrals Tab -->
                    <div id="tab-referrals" class="tab-content <?php 
echo ( $current_tab === 'referrals' ? 'active' : '' );
?>">
                        <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                            <i class="fas fa-shopping-cart" style="color: #007cba; margin-right: 10px;"></i>
                            <?php 
echo esc_html__( 'Referred Orders', 'woo-coupon-usage' );
?>
                        </h3>
                        <div class="wcusage-filters" id="wcusage-referrals-filters" style="margin: 0 0 15px; display:flex; gap:8px; align-items: center;">
                            <label>
                                <?php 
echo esc_html__( 'From', 'woo-coupon-usage' );
?>
                                <input type="date" id="referrals-start-date" />
                            </label>
                            <label>
                                <?php 
echo esc_html__( 'To', 'woo-coupon-usage' );
?>
                                <input type="date" id="referrals-end-date" />
                            </label>
                            <button class="button" id="referrals-apply-filters"><?php 
echo esc_html__( 'Filter', 'woo-coupon-usage' );
?></button>
                        </div>
                        <div id="wcusage-referrals-table-container">
                            <?php 
wcusage_display_affiliate_referrals(
    $user_id,
    1,
    20,
    '',
    ''
);
?>
                        </div>
                    </div>

                    <!-- Visits Tab -->
                    <div id="tab-visits" class="tab-content <?php 
echo ( $current_tab === 'visits' ? 'active' : '' );
?>">
                        <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                            <i class="fas fa-eye" style="color: #007cba; margin-right: 10px;"></i>
                            <?php 
echo esc_html__( 'Latest Clicks / Visits', 'woo-coupon-usage' );
?>
                        </h3>
                        <div class="wcusage-filters" id="wcusage-visits-filters" style="margin: 0 0 15px; display:flex; gap:8px; align-items: center;">
                            <label>
                                <?php 
echo esc_html__( 'From', 'woo-coupon-usage' );
?>
                                <input type="date" id="visits-start-date" />
                            </label>
                            <label>
                                <?php 
echo esc_html__( 'To', 'woo-coupon-usage' );
?>
                                <input type="date" id="visits-end-date" />
                            </label>
                            <button class="button" id="visits-apply-filters"><?php 
echo esc_html__( 'Filter', 'woo-coupon-usage' );
?></button>
                        </div>
                        <div id="wcusage-visits-table-container">
                            <?php 
wcusage_display_affiliate_visits(
    $user_id,
    1,
    20,
    '',
    ''
);
?>
                        </div>
                    </div>

                    <!-- Payouts Tab -->
                    <?php 
if ( $wcusage_tracking_enable ) {
    ?>
                    <div id="tab-payouts" class="tab-content <?php 
    echo ( $current_tab === 'payouts' ? 'active' : '' );
    ?>">
                        <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                            <i class="fas fa-dollar-sign" style="color: #007cba; margin-right: 10px;"></i>
                            <?php 
    echo esc_html__( 'Payout History', 'woo-coupon-usage' );
    ?>
                        </h3>
                        <div class="wcusage-filters" id="wcusage-payouts-filters" style="margin: 0 0 15px; display:flex; gap:8px; align-items: center;">
                            <label>
                                <?php 
    echo esc_html__( 'From', 'woo-coupon-usage' );
    ?>
                                <input type="date" id="payouts-start-date" />
                            </label>
                            <label>
                                <?php 
    echo esc_html__( 'To', 'woo-coupon-usage' );
    ?>
                                <input type="date" id="payouts-end-date" />
                            </label>
                            <button class="button" id="payouts-apply-filters"><?php 
    echo esc_html__( 'Filter', 'woo-coupon-usage' );
    ?></button>
                        </div>
                        <div id="wcusage-payouts-table-container">
                            <?php 
    wcusage_display_affiliate_payouts(
        $user_id,
        1,
        20,
        '',
        ''
    );
    ?>
                        </div>
                    </div>
                    <?php 
}
?>

                    <!-- Activity Tab -->
                    <div id="tab-activity" class="tab-content <?php 
echo ( $current_tab === 'activity' ? 'active' : '' );
?>">
                        <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                            <i class="fas fa-history" style="color: #007cba; margin-right: 10px;"></i>
                            <?php 
echo esc_html__( 'Activity Log', 'woo-coupon-usage' );
?>
                        </h3>
                        <div class="wcusage-filters" id="wcusage-activity-filters" style="margin: 0 0 15px; display:flex; gap:8px; align-items: center;">
                            <label>
                                <?php 
echo esc_html__( 'From', 'woo-coupon-usage' );
?>
                                <input type="date" id="activity-start-date" />
                            </label>
                            <label>
                                <?php 
echo esc_html__( 'To', 'woo-coupon-usage' );
?>
                                <input type="date" id="activity-end-date" />
                            </label>
                            <button class="button" id="activity-apply-filters"><?php 
echo esc_html__( 'Filter', 'woo-coupon-usage' );
?></button>
                        </div>
                        <div id="wcusage-activity-table-container">
                            <?php 
if ( function_exists( 'wcusage_affiliate_activity_table' ) ) {
    wcusage_affiliate_activity_table(
        $user_id,
        1,
        20,
        '',
        ''
    );
}
?>
                        </div>
                    </div>

                    <?php 
if ( wcu_fs()->can_use_premium_code__premium_only() && $wcusage_field_mla_enable && function_exists( 'wcusage_get_ml_sub_affiliates' ) && function_exists( 'wcusage_get_network_chart_item' ) ) {
    ?>
                    <!-- MLA Tab -->
                    <div id="tab-mla" class="tab-content <?php 
    echo ( $current_tab === 'mla' ? 'active' : '' );
    ?>">

                        <?php 
    // Get MLA data
    $mla_sub_affiliates = wcusage_get_ml_sub_affiliates( $user_id );
    $mla_total_commission = 0;
    if ( function_exists( 'wcusage_mla_total_earnings' ) ) {
        $mla_total_commission = wcusage_mla_total_earnings( $user_id );
    }
    $mla_unpaid = (float) get_user_meta( $user_id, 'wcu_ml_unpaid_commission', true );
    // Get parent affiliates
    $mla_get_parents = get_user_meta( $user_id, 'wcu_ml_affiliate_parents', true );
    if ( !is_array( $mla_get_parents ) ) {
        $mla_get_parents = array();
    }
    // Determine active MLA sub-tab
    $mla_subtab = ( isset( $_GET['mla_subtab'] ) ? sanitize_text_field( $_GET['mla_subtab'] ) : 'mla-overview' );
    ?>

                        <!-- MLA Sub-tabs -->
                        <h3 class="nav-tab-wrapper wcusage-mla-subtabs">
                            <a href="#mla-subtab-overview" class="nav-tab <?php 
    echo ( $mla_subtab === 'mla-overview' ? 'nav-tab-active' : '' );
    ?>" data-mla-subtab="mla-overview">
                                <i class="fas fa-chart-line" style="margin-right: 6px;"></i>
                                <?php 
    echo esc_html__( 'Overview', 'woo-coupon-usage' );
    ?>
                            </a>
                            <a href="#mla-subtab-network" class="nav-tab <?php 
    echo ( $mla_subtab === 'mla-network' ? 'nav-tab-active' : '' );
    ?>" data-mla-subtab="mla-network">
                                <i class="fa-solid fa-network-wired" style="margin-right: 6px;"></i>
                                <?php 
    echo esc_html__( 'Network Tree', 'woo-coupon-usage' );
    ?>
                            </a>
                            <a href="#mla-subtab-tiers" class="nav-tab <?php 
    echo ( $mla_subtab === 'mla-tiers' ? 'nav-tab-active' : '' );
    ?>" data-mla-subtab="mla-tiers">
                                <i class="fas fa-layer-group" style="margin-right: 6px;"></i>
                                <?php 
    echo esc_html__( 'Tiers', 'woo-coupon-usage' );
    ?>
                            </a>
                            <?php 
    $wcusage_field_mla_per_user_group_rates_tab = wcusage_get_setting_value( 'wcusage_field_mla_per_user_group_rates', '0' );
    if ( $wcusage_field_mla_per_user_group_rates_tab ) {
        ?>
                            <a href="#mla-subtab-rates" class="nav-tab <?php 
        echo ( $mla_subtab === 'mla-rates' ? 'nav-tab-active' : '' );
        ?>" data-mla-subtab="mla-rates">
                                <i class="fas fa-sliders-h" style="margin-right: 6px;"></i>
                                <?php 
        echo esc_html__( 'Commission Rates', 'woo-coupon-usage' );
        ?>
                            </a>
                            <?php 
    }
    ?>
                        </h3>

                        <!-- MLA Sub-tab Content -->
                        <div class="wcusage-mla-subtab-content">

                            <!-- MLA Overview Sub-tab -->
                            <div id="mla-subtab-overview" class="mla-subtab-panel <?php 
    echo ( $mla_subtab === 'mla-overview' ? 'active' : '' );
    ?>">
                                <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                                    <i class="fas fa-chart-line" style="color: #007cba; margin-right: 10px;"></i>
                                    <?php 
    echo esc_html__( 'MLA Statistics Overview', 'woo-coupon-usage' );
    ?>
                                </h3>

                                <div class="wcusage-stats-grid">
                                    <div class="wcusage-stat-box">
                                        <div class="stat-value"><?php 
    echo esc_html( count( $mla_sub_affiliates ) );
    ?></div>
                                        <div class="stat-label"><?php 
    echo esc_html__( 'Sub-Affiliates', 'woo-coupon-usage' );
    ?></div>
                                    </div>
                                    <div class="wcusage-stat-box">
                                        <div class="stat-value"><?php 
    echo wcusage_format_price( $mla_total_commission );
    ?></div>
                                        <div class="stat-label"><?php 
    echo esc_html__( 'Total MLA Commission', 'woo-coupon-usage' );
    ?></div>
                                    </div>
                                    <div class="wcusage-stat-box">
                                        <div class="stat-value"><?php 
    echo wcusage_format_price( $mla_unpaid );
    ?></div>
                                        <div class="stat-label"><?php 
    echo esc_html__( 'Unpaid MLA Commission', 'woo-coupon-usage' );
    ?></div>
                                    </div>
                                </div>

                                <!-- Sub-Affiliates List -->
                                <div class="wcusage-mla-section-header">
                                    <h3><?php 
    echo esc_html__( 'Sub-Affiliates', 'woo-coupon-usage' );
    ?></h3>
                                    <button type="button" id="wcusage-add-sub-affiliate-toggle" class="button">
                                        <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle; margin-right: 4px;"></span>
                                        <?php 
    echo esc_html__( 'Add New Sub-Affiliate', 'woo-coupon-usage' );
    ?>
                                    </button>
                                </div>

                                <!-- Add Sub-Affiliate Form (hidden by default) -->
                                <div id="wcusage-add-sub-affiliate-form" class="wcusage-add-sub-affiliate-form" style="display:none;">
                                    <p class="wcusage-add-sub-affiliate-desc">
                                        <?php 
    echo esc_html__( 'Search for an existing user who does not already have a parent affiliate. Select them and click Add.', 'woo-coupon-usage' );
    ?>
                                    </p>
                                    <div class="wcusage-add-sub-affiliate-row">
                                        <input
                                            type="text"
                                            id="wcusage-sub-affiliate-search"
                                            class="regular-text"
                                            placeholder="<?php 
    echo esc_attr__( 'Search by username or email…', 'woo-coupon-usage' );
    ?>"
                                            autocomplete="off"
                                        />
                                        <input type="hidden" id="wcusage-sub-affiliate-id" value="" />
                                        <button type="button" id="wcusage-add-sub-affiliate-submit" class="button button-primary" disabled>
                                            <?php 
    echo esc_html__( 'Add Sub-Affiliate', 'woo-coupon-usage' );
    ?>
                                        </button>
                                        <span class="spinner wcusage-add-sub-spinner"></span>
                                    </div>
                                    <p id="wcusage-add-sub-affiliate-msg" class="wcusage-add-sub-msg" style="display:none;"></p>
                                    <input type="hidden" id="wcusage-parent-affiliate-id" value="<?php 
    echo esc_attr( $user_id );
    ?>" />
                                </div>

                                <?php 
    // Build a lookup: user_id => WP_User object + pre-fetched meta
    $mla_sub_map = array();
    // id => ['user' => obj, 'parents' => array, 'tier_num' => int]
    foreach ( $mla_sub_affiliates as $sub_user ) {
        $sub_parents = get_user_meta( $sub_user->ID, 'wcu_ml_affiliate_parents', true );
        if ( !is_array( $sub_parents ) ) {
            $sub_parents = array();
        }
        // tier_num relative to the viewed affiliate
        $t_key = array_search( (string) $user_id, array_map( 'strval', $sub_parents ) );
        $t_num = ( $t_key !== false ? (int) str_replace( 'T', '', $t_key ) : 0 );
        $mla_sub_map[$sub_user->ID] = array(
            'user'          => $sub_user,
            'parents'       => $sub_parents,
            'tier_num'      => $t_num,
            'direct_parent' => ( isset( $sub_parents['T1'] ) ? (int) $sub_parents['T1'] : 0 ),
        );
    }
    // Build children map: parent_id => [child_id, ...]
    $mla_children_map = array();
    foreach ( $mla_sub_map as $sid => $sdata ) {
        $pid = $sdata['direct_parent'];
        $mla_children_map[$pid][] = $sid;
    }
    // Recursive row renderer
    function wcusage_render_mla_sub_rows(
        $parent_id,
        $mla_sub_map,
        $mla_children_map,
        $root_user_id,
        $depth = 0
    ) {
        if ( empty( $mla_children_map[$parent_id] ) ) {
            return;
        }
        foreach ( $mla_children_map[$parent_id] as $sid ) {
            $sdata = $mla_sub_map[$sid];
            $sub_user = $sdata['user'];
            $sub_user_info = get_userdata( $sid );
            $sub_parents = $sdata['parents'];
            $t_num = $sdata['tier_num'];
            // tier key relative to root_user_id
            $sub_tier_key = array_search( (string) $root_user_id, array_map( 'strval', $sub_parents ) );
            // Coupons & commission
            $sub_coupons = wcusage_get_users_coupons_ids( $sid );
            $sub_coupon_names = array();
            $sub_commission = 0;
            foreach ( $sub_coupons as $sub_coupon_id ) {
                $sub_coupon_names[] = get_the_title( $sub_coupon_id );
                if ( function_exists( 'wcusage_mla_get_total_commission_earned_tier' ) && $sub_tier_key !== false ) {
                    $sub_commission += wcusage_mla_get_total_commission_earned_tier( $sub_coupon_id, $sub_tier_key );
                }
            }
            $tier_colors = array(
                1 => '#2271b1',
                2 => '#6f42c1',
                3 => '#0f7a6b',
                4 => '#d63638',
                5 => '#e87c0c',
            );
            $badge_color = ( isset( $tier_colors[$t_num] ) ? $tier_colors[$t_num] : '#50575e' );
            $indent_px = $depth * 28;
            ?>
                                        <tr class="wcusage-mla-tree-row">
                                            <td>
                                                <div class="wcusage-mla-user-cell" style="padding-left:<?php 
            echo esc_attr( $indent_px );
            ?>px;">
                                                    <?php 
            if ( $depth > 0 ) {
                ?>
                                                        <span class="wcusage-mla-indent-connector"></span>
                                                    <?php 
            }
            ?>
                                                    <?php 
            echo get_avatar(
                $sid,
                28,
                'identicon',
                '',
                array(
                    'style' => 'border-radius:50%;vertical-align:middle;margin-right:10px;flex-shrink:0;',
                )
            );
            ?>
                                                    <span class="wcusage-mla-user-info">
                                                        <strong><?php 
            echo esc_html( $sub_user_info->user_login );
            ?></strong>
                                                        <small><?php 
            echo esc_html( $sub_user_info->user_email );
            ?></small>
                                                    </span>
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="wcusage-mla-tier-pill" style="background:<?php 
            echo esc_attr( $badge_color );
            ?>;"><?php 
            echo esc_html( sprintf( __( 'T%s', 'woo-coupon-usage' ), $t_num ) );
            ?></span>
                                            </td>
                                            <td><?php 
            echo esc_html( implode( ', ', $sub_coupon_names ) );
            ?></td>
                                            <td><?php 
            echo wcusage_format_price( $sub_commission );
            ?></td>
                                            <td>
                                                <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_view_affiliate&user_id=' . $sid . '&tab=mla' ) );
            ?>" class="button button-small"><?php 
            echo esc_html__( 'View User', 'woo-coupon-usage' );
            ?></a>
                                                <?php 
            if ( $sub_tier_key === 'T1' ) {
                ?>
                                                <button type="button"
                                                    class="button button-small wcusage-remove-sub-affiliate"
                                                    data-sub-id="<?php 
                echo esc_attr( $sid );
                ?>"
                                                    data-sub-name="<?php 
                echo esc_attr( $sub_user_info->user_login );
                ?>"
                                                    data-parent-id="<?php 
                echo esc_attr( $root_user_id );
                ?>"
                                                ><?php 
                echo esc_html__( 'Remove', 'woo-coupon-usage' );
                ?></button>
                                                <?php 
            }
            ?>
                                            </td>
                                        </tr>
                                        <?php 
            // Recurse into this sub's children
            wcusage_render_mla_sub_rows(
                $sid,
                $mla_sub_map,
                $mla_children_map,
                $root_user_id,
                $depth + 1
            );
        }
    }

    ?>
                                <?php 
    if ( empty( $mla_sub_affiliates ) ) {
        ?>
                                    <p><?php 
        echo esc_html__( "This affiliate doesn't currently have any sub-affiliates.", 'woo-coupon-usage' );
        ?></p>
                                <?php 
    } else {
        ?>
                                    <table class="wp-list-table widefat fixed wcusage-mla-tiered-table">
                                        <thead>
                                            <tr>
                                                <th><?php 
        echo esc_html__( 'User', 'woo-coupon-usage' );
        ?></th>
                                                <th style="width:60px;text-align:center;"><?php 
        echo esc_html__( 'Tier', 'woo-coupon-usage' );
        ?></th>
                                                <th style="width:120px;"><?php 
        echo esc_html__( 'Coupons', 'woo-coupon-usage' );
        ?></th>
                                                <th style="width:160px;"><?php 
        echo esc_html__( 'Earned', 'woo-coupon-usage' );
        ?></th>
                                                <th style="width:220px;"><?php 
        echo esc_html__( 'Actions', 'woo-coupon-usage' );
        ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
        wcusage_render_mla_sub_rows(
            $user_id,
            $mla_sub_map,
            $mla_children_map,
            $user_id,
            0
        );
        ?>
                                        </tbody>
                                    </table>
                                <?php 
    }
    ?>
                            </div>

                            <!-- MLA Network Tree Sub-tab -->
                            <div id="mla-subtab-network" class="mla-subtab-panel <?php 
    echo ( $mla_subtab === 'mla-network' ? 'active' : '' );
    ?>">
                                <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                                    <i class="fa-solid fa-network-wired" style="color: #007cba; margin-right: 10px;"></i>
                                    <?php 
    echo esc_html__( 'MLA Network Tree', 'woo-coupon-usage' );
    ?>
                                </h3>
                                <?php 
    if ( empty( $mla_sub_affiliates ) ) {
        echo '<p>' . esc_html__( "This affiliate doesn't currently have any sub-affiliates in their MLA network.", 'woo-coupon-usage' ) . '</p>';
    } elseif ( function_exists( 'wcusage_get_network_chart_item' ) ) {
        $network_array = '';
        // Root node (self)
        $network_array .= wcusage_get_network_chart_item( $user_id, $user_id, $user_id );
        $coupon_ids = array();
        foreach ( $mla_sub_affiliates as $mla_user ) {
            $this_user_id = $mla_user->ID;
            $mla_parents = get_user_meta( $this_user_id, 'wcu_ml_affiliate_parents', true );
            if ( !$mla_parents ) {
                $mla_parents = array();
            }
            $this_users_coupons = wcusage_get_users_coupons_ids( $this_user_id );
            foreach ( $this_users_coupons as $this_users_coupon_id ) {
                $coupon_ids[] = $this_users_coupon_id;
            }
            $super_affiliate = ( empty( $mla_parents ) ? 1 : 0 );
            if ( !empty( $this_users_coupons ) && is_array( $mla_parents ) ) {
                $mla_parents = array_reverse( $mla_parents );
                $x = end( $mla_parents );
                // Link to top-most parent
                if ( !$super_affiliate ) {
                    $network_array .= wcusage_get_network_chart_item( $this_user_id, $x, $user_id );
                }
            }
        }
        $network_array = rtrim( $network_array, ',' );
        $wcusage_color_tab = wcusage_get_setting_value( 'wcusage_field_color_tab', '#333' );
        // Merge network data into existing WCUAdminAffiliateView object without overwriting other keys
        wp_add_inline_script( 'wcusage-admin-view-affiliate', 'if(window.WCUAdminAffiliateView){WCUAdminAffiliateView.mla_network_array=' . wp_json_encode( $network_array ) . ';}', 'after' );
        $mla_network_text = wcusage_get_setting_value( 'wcusage_field_mla_network_text', '' );
        if ( $mla_network_text ) {
            echo '<p>' . wp_kses_post( $mla_network_text ) . '</p><br/>';
        }
        ?>
                                    <div id="mla_chart_div"></div>
                                    <?php 
    } else {
        echo '<p>' . esc_html__( 'MLA network chart functions are not available.', 'woo-coupon-usage' ) . '</p>';
    }
    ?>
                            </div>

                            <!-- MLA Tiers Sub-tab -->
                            <div id="mla-subtab-tiers" class="mla-subtab-panel <?php 
    echo ( $mla_subtab === 'mla-tiers' ? 'active' : '' );
    ?>">
                                <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                                    <i class="fas fa-layer-group" style="color: #007cba; margin-right: 10px;"></i>
                                    <?php 
    echo esc_html__( 'Commission Per Tier', 'woo-coupon-usage' );
    ?>
                                </h3>

                                <?php 
    $tiersnumber = wcusage_get_setting_value( 'wcusage_field_mla_number_tiers', '5' );
    ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php 
    echo esc_html__( 'Tier', 'woo-coupon-usage' );
    ?></th>
                                            <th><?php 
    echo esc_html__( 'Sub-Affiliates', 'woo-coupon-usage' );
    ?></th>
                                            <th><?php 
    echo esc_html__( 'Earned', 'woo-coupon-usage' );
    ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
    for ($i = 1; $i <= $tiersnumber; $i++) {
        $tier_key = 'T' . $i;
        // Count sub-affiliates in this tier
        $tier_count = 0;
        $tier_commission = 0;
        foreach ( $mla_sub_affiliates as $sub ) {
            $sub_parents = get_user_meta( $sub->ID, 'wcu_ml_affiliate_parents', true );
            if ( is_array( $sub_parents ) ) {
                $sub_tier = array_search( $user_id, $sub_parents );
                if ( $sub_tier === $tier_key ) {
                    $tier_count++;
                    // Calculate commission from this sub-affiliate's coupons for this tier
                    $sub_coupons = wcusage_get_users_coupons_ids( $sub->ID );
                    foreach ( $sub_coupons as $sub_coupon_id ) {
                        if ( function_exists( 'wcusage_mla_get_total_commission_earned_tier' ) ) {
                            $tier_commission += wcusage_mla_get_total_commission_earned_tier( $sub_coupon_id, $tier_key );
                        }
                    }
                }
            }
        }
        ?>
                                            <tr>
                                                <td><strong><?php 
        echo esc_html( sprintf( __( 'Tier %d', 'woo-coupon-usage' ), $i ) );
        ?></strong></td>
                                                <td><?php 
        echo esc_html( $tier_count );
        ?></td>
                                                <td><?php 
        echo wcusage_format_price( $tier_commission );
        ?></td>
                                            </tr>
                                        <?php 
    }
    ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- MLA Commission Rates Sub-tab -->
                            <?php 
    if ( wcusage_get_setting_value( 'wcusage_field_mla_per_user_group_rates', '0' ) ) {
        ?>
                            <div id="mla-subtab-rates" class="mla-subtab-panel <?php 
        echo ( $mla_subtab === 'mla-rates' ? 'active' : '' );
        ?>">
                                <h3 style="color: #23282d; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                                    <i class="fas fa-sliders-h" style="color: #007cba; margin-right: 10px;"></i>
                                    <?php 
        echo esc_html__( 'Per-User MLA Commission Rates', 'woo-coupon-usage' );
        ?>
                                </h3>

                                <p style="background: #f0f6fc; border-left: 4px solid #72aee6; padding: 10px 14px; margin: 0 0 20px 0;">
                                    <strong><?php 
        echo esc_html__( 'Rate Override Priority:', 'woo-coupon-usage' );
        ?></strong>
                                    <?php 
        echo esc_html__( 'Per User > Per Group > Global. Leave fields empty to use group or global rates.', 'woo-coupon-usage' );
        ?>
                                </p>

                                <?php 
        // Check if user has group rates
        $user_data = get_userdata( $user_id );
        $user_group_name = '';
        if ( $user_data && !empty( $user_data->roles ) ) {
            foreach ( $user_data->roles as $role ) {
                if ( $role === 'coupon_affiliate' || strpos( $role, 'coupon_affiliate_' ) === 0 ) {
                    global $wp_roles;
                    $user_group_name = ( isset( $wp_roles->roles[$role]['name'] ) ? $wp_roles->roles[$role]['name'] : $role );
                    break;
                }
            }
        }
        if ( $user_group_name ) {
            echo '<p style="margin-bottom: 15px;"><span class="dashicons dashicons-groups" style="color: #646970;"></span> ';
            echo sprintf( esc_html__( 'This user belongs to the group: %s', 'woo-coupon-usage' ), '<strong>' . esc_html( $user_group_name ) . '</strong>' );
            echo '</p>';
        }
        $mla_tiersnumber = wcusage_get_setting_value( 'wcusage_field_mla_number_tiers', '5' );
        $options_check = get_option( 'wcusage_options' );
        ?>

                                <form method="post" action="">
                                    <?php 
        wp_nonce_field( 'wcusage_save_mla_user_rates_' . $user_id );
        ?>
                                    <input type="hidden" name="wcusage_mla_user_rates_save" value="1" />

                                    <?php 
        $user_custom_rates_enabled = get_user_meta( $user_id, 'wcu_mla_custom_rates_enabled', true );
        ?>
                                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-weight: 600; font-size: 14px; cursor: pointer;">
                                        <input type="checkbox" name="wcu_mla_custom_rates_enabled" id="wcu_mla_custom_rates_enabled_user" value="1" <?php 
        checked( $user_custom_rates_enabled, '1' );
        ?> style="width: 16px; height: 16px;" />
                                        <?php 
        echo esc_html__( 'Enable Custom MLA Commission Rates', 'woo-coupon-usage' );
        ?>
                                    </label>
                                    <div id="wcu-mla-user-rates-table">
                                    <table class="wp-list-table widefat fixed striped" style="max-width: 800px;">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;"><?php 
        echo esc_html__( 'Tier', 'woo-coupon-usage' );
        ?></th>
                                                <th><?php 
        echo esc_html__( '% of Affiliate Earnings', 'woo-coupon-usage' );
        ?></th>
                                                <th><?php 
        echo esc_html__( '% of Order Total', 'woo-coupon-usage' );
        ?></th>
                                                <th><?php 
        echo sprintf( esc_html__( '%s Fixed Amount', 'woo-coupon-usage' ), ( function_exists( 'wcusage_get_currency_symbol' ) ? wcusage_get_currency_symbol() : '$' ) );
        ?></th>
                                                <th style="width: 120px;"><?php 
        echo esc_html__( 'Source', 'woo-coupon-usage' );
        ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
        for ($i = 1; $i <= $mla_tiersnumber; $i++) {
            $tier_key = 'T' . $i;
            $user_tier_percent = get_user_meta( $user_id, 'wcu_mla_tier_percent_' . $tier_key, true );
            $user_tier_order_percent = get_user_meta( $user_id, 'wcu_mla_tier_order_percent_' . $tier_key, true );
            $user_tier_fixed = get_user_meta( $user_id, 'wcu_mla_tier_fixed_' . $tier_key, true );
            // Determine which source is active
            $effective_rates = ( function_exists( 'wcusage_mla_get_tier_rates' ) ? wcusage_mla_get_tier_rates( $tier_key, $user_id ) : array(
                'percent'       => 0,
                'order_percent' => 0,
                'fixed'         => 0,
            ) );
            // Determine Source label — must mirror wcusage_mla_get_tier_rates() priority logic
            $source_label = '<span style="color: #646970;">' . esc_html__( 'Global', 'woo-coupon-usage' ) . '</span>';
            // Check if group rates are active (priority 2)
            if ( $user_data && !empty( $user_data->roles ) ) {
                foreach ( $user_data->roles as $role ) {
                    if ( $role === 'coupon_affiliate' || strpos( $role, 'coupon_affiliate_' ) === 0 ) {
                        $gp = ( isset( $options_check['wcusage_field_mla_tier_percent_' . $tier_key . '_' . $role] ) ? $options_check['wcusage_field_mla_tier_percent_' . $tier_key . '_' . $role] : '' );
                        $gop = ( isset( $options_check['wcusage_field_mla_tier_order_percent_' . $tier_key . '_' . $role] ) ? $options_check['wcusage_field_mla_tier_order_percent_' . $tier_key . '_' . $role] : '' );
                        $gf = ( isset( $options_check['wcusage_field_mla_tier_fixed_' . $tier_key . '_' . $role] ) ? $options_check['wcusage_field_mla_tier_fixed_' . $tier_key . '_' . $role] : '' );
                        $has_group_rates = $gp !== '' || $gop !== '' || $gf !== '';
                        $group_flag_exists = isset( $options_check['wcusage_field_mla_custom_rates_enabled_' . $role] );
                        $group_flag_enabled = $group_flag_exists && ($options_check['wcusage_field_mla_custom_rates_enabled_' . $role] === '1' || $options_check['wcusage_field_mla_custom_rates_enabled_' . $role] === 1);
                        if ( $has_group_rates && (!$group_flag_exists || $group_flag_enabled) ) {
                            $source_label = '<span style="color: #6f42c1; font-weight: 600;">' . esc_html__( 'Group', 'woo-coupon-usage' ) . '</span>';
                            break;
                        }
                    }
                }
            }
            // Check if user rates are active (priority 1 — overrides group)
            $has_user_rate = $user_tier_percent !== '' || $user_tier_order_percent !== '' || $user_tier_fixed !== '';
            if ( $has_user_rate ) {
                $user_flag_exists = metadata_exists( 'user', $user_id, 'wcu_mla_custom_rates_enabled' );
                $user_flag_val = get_user_meta( $user_id, 'wcu_mla_custom_rates_enabled', true );
                $user_flag_enabled = $user_flag_val === '1' || $user_flag_val === 1 || $user_flag_val === true;
                if ( !$user_flag_exists || $user_flag_enabled ) {
                    $source_label = '<span style="color: #2271b1; font-weight: 600;">' . esc_html__( 'User', 'woo-coupon-usage' ) . '</span>';
                }
            }
            ?>
                                            <tr>
                                                <td><strong><?php 
            echo sprintf( esc_html__( 'Tier %d', 'woo-coupon-usage' ), $i );
            ?></strong></td>
                                                <td>
                                                    <input type="number" step="0.1" name="wcu_mla_tier_percent_<?php 
            echo esc_attr( $tier_key );
            ?>" value="<?php 
            echo esc_attr( $user_tier_percent );
            ?>" class="small-text" placeholder="<?php 
            echo esc_attr( $effective_rates['percent'] );
            ?>" style="width: 80px;" />
                                                </td>
                                                <td>
                                                    <input type="number" step="0.1" name="wcu_mla_tier_order_percent_<?php 
            echo esc_attr( $tier_key );
            ?>" value="<?php 
            echo esc_attr( $user_tier_order_percent );
            ?>" class="small-text" placeholder="<?php 
            echo esc_attr( $effective_rates['order_percent'] );
            ?>" style="width: 80px;" />
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="wcu_mla_tier_fixed_<?php 
            echo esc_attr( $tier_key );
            ?>" value="<?php 
            echo esc_attr( $user_tier_fixed );
            ?>" class="small-text" placeholder="<?php 
            echo esc_attr( $effective_rates['fixed'] );
            ?>" style="width: 80px;" />
                                                </td>
                                                <td><?php 
            echo wp_kses_post( $source_label );
            ?></td>
                                            </tr>
                                        <?php 
        }
        ?>
                                        </tbody>
                                    </table>

                                    <p style="margin-top: 10px; color: #646970; font-size: 12px;">
                                        <span class="dashicons dashicons-info" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        <?php 
        echo esc_html__( 'Placeholder values show the currently active rate from either group or global settings. Clear a field to revert to the inherited rate.', 'woo-coupon-usage' );
        ?>
                                    </p>
                                    </div><!-- #wcu-mla-user-rates-table -->
                                    <script>
                                    (function(){
                                        var cb = document.getElementById('wcu_mla_custom_rates_enabled_user');
                                        var tbl = document.getElementById('wcu-mla-user-rates-table');
                                        function toggle(){ tbl.style.display = cb.checked ? '' : 'none'; }
                                        cb.addEventListener('change', toggle);
                                        toggle();
                                    })();
                                    </script>

                                    <p class="submit" style="margin-top: 15px;">
                                        <input type="submit" class="button button-primary" value="<?php 
        echo esc_attr__( 'Save Commission Rates', 'woo-coupon-usage' );
        ?>" />
                                    </p>
                                </form>
                            </div>
                            <?php 
    }
    // end per-user/per-group rates check
    ?>

                        </div><!-- .wcusage-mla-subtab-content -->

                    </div>
                    <?php 
}
?>

                    <!-- Edit User Tab -->
                    <div id="tab-edit-user" class="tab-content <?php 
echo ( $current_tab === 'edit-user' ? 'active' : '' );
?>">
                        <div>
                            <h3 class="wcusage-form-header">
                                <i class="fas fa-user-edit" style="margin-right: 10px;"></i>
                                <?php 
echo esc_html__( 'Edit User', 'woo-coupon-usage' );
?>
                            </h3>

                            <form method="post" action="" class="wcusage-form-body">
                                <?php 
wp_nonce_field( 'update-user_' . $user_id );
?>

                                <!-- Basic User Information -->
                                <div class="wcusage-form-section">
                                    <h4><?php 
echo esc_html__( 'Basic Information', 'woo-coupon-usage' );
?></h4>

                                    <div class="wcusage-form-row">
                                        <div class="wcusage-form-group">
                                            <label for="user_login"><?php 
echo esc_html__( 'Username', 'woo-coupon-usage' );
?></label>
                                            <input type="text" name="user_login" id="user_login" value="<?php 
echo esc_attr( $user_info->user_login );
?>" class="regular-text" readonly />
                                            <small class="description"><?php 
echo esc_html__( 'Usernames cannot be changed.', 'woo-coupon-usage' );
?></small>
                                        </div>
                                    </div>

                                    <div class="wcusage-form-row">
                                        <div class="wcusage-form-group">
                                            <label for="first_name"><?php 
echo esc_html__( 'First Name', 'woo-coupon-usage' );
?></label>
                                            <input type="text" name="first_name" id="first_name" value="<?php 
echo esc_attr( get_user_meta( $user_id, 'first_name', true ) );
?>" />
                                        </div>
                                        <div class="wcusage-form-group">
                                            <label for="last_name"><?php 
echo esc_html__( 'Last Name', 'woo-coupon-usage' );
?></label>
                                            <input type="text" name="last_name" id="last_name" value="<?php 
echo esc_attr( get_user_meta( $user_id, 'last_name', true ) );
?>" />
                                        </div>
                                    </div>

                                    <div class="wcusage-form-row">
                                        <div class="wcusage-form-group">
                                            <label for="user_email"><?php 
echo esc_html__( 'Email', 'woo-coupon-usage' );
?></label>
                                            <input type="email" name="user_email" id="user_email" value="<?php 
echo esc_attr( $user_info->user_email );
?>" />
                                        </div>
                                        <div class="wcusage-form-group">
                                            <label for="user_url"><?php 
echo esc_html__( 'Website', 'woo-coupon-usage' );
?></label>
                                            <input type="url" name="user_url" id="user_url" value="<?php 
echo esc_attr( $user_info->user_url );
?>" />
                                        </div>
                                    </div>
                                </div>

                                <?php 
// Include plugin-specific user profile fields
if ( function_exists( 'wcusage_profile_fields' ) ) {
    echo '<div class="wcusage-form-section">';
    wcusage_profile_fields( $user_info );
    echo '</div>';
}
// Include bonus fields if available
if ( function_exists( 'wcusage_custom_user_profile_fields' ) ) {
    echo '<div class="wcusage-form-section">';
    wcusage_custom_user_profile_fields( $user_info );
    echo '</div>';
}
?>

                                <div class="wcusage-form-actions">
                                    <button type="submit" name="update_user" class="wcusage-btn wcusage-btn-primary">
                                        <?php 
echo esc_html__( 'Update User', 'woo-coupon-usage' );
?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="wcusage-sidebar">
                <!-- Affiliate Information -->
                <div class="wcusage-affiliate-info-box">
                    <h3><?php 
echo esc_html__( 'Affiliate Information', 'woo-coupon-usage' );
?></h3>
                    <div class="info-content">
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'Name:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value"><?php 
echo esc_html( $user_info->display_name );
?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'Email:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value">
                                <a href="mailto:<?php 
echo esc_attr( $user_info->user_email );
?>"><?php 
echo esc_html( $user_info->user_email );
?></a>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'Join Date:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value">
                                <?php 
echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $user_info->user_registered ) ) );
?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'Website:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value">
                                <?php 
$website = ( isset( $user_info->user_url ) ? $user_info->user_url : '' );
if ( !empty( $website ) ) {
    echo '<a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a>';
} else {
    echo esc_html__( 'Not provided', 'woo-coupon-usage' );
}
?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'Coupons:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value"><?php 
echo count( $coupons );
?> assigned</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?php 
echo esc_html__( 'User Roles:', 'woo-coupon-usage' );
?></span>
                            <span class="info-value">
                                <?php 
$user_roles = $user_info->roles;
if ( !empty( $user_roles ) ) {
    $role_names = array();
    foreach ( $user_roles as $role ) {
        $role_names[] = ucfirst( $role );
    }
    echo esc_html( implode( ', ', $role_names ) );
} else {
    echo esc_html__( 'No roles assigned', 'woo-coupon-usage' );
}
?>
                            </span>
                        </div>
                        <?php 
// Extra registration details inline
$wcu_info_meta = get_user_meta( $user_id, 'wcu_info', true );
$wcu_promote = get_user_meta( $user_id, 'wcu_promote', true );
$wcu_referrer = get_user_meta( $user_id, 'wcu_referrer', true );
// Normalize wcu_info into an associative array
$wcu_info = array();
if ( is_array( $wcu_info_meta ) ) {
    $wcu_info = $wcu_info_meta;
} elseif ( is_string( $wcu_info_meta ) && strlen( $wcu_info_meta ) ) {
    $decoded = json_decode( $wcu_info_meta, true );
    if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
        $wcu_info = $decoded;
    } elseif ( function_exists( 'is_serialized' ) && is_serialized( $wcu_info_meta ) ) {
        $maybe = maybe_unserialize( $wcu_info_meta );
        if ( is_array( $maybe ) ) {
            $wcu_info = $maybe;
        }
    }
}
if ( !empty( $wcu_promote ) ) {
    ?>
                            <div class="info-row">
                                <span class="info-label"><?php 
    echo esc_html__( 'Promote:', 'woo-coupon-usage' );
    ?></span>
                                <span class="info-value"><?php 
    echo esc_html( (string) $wcu_promote );
    ?></span>
                            </div>
                        <?php 
}
?>

                        <?php 
if ( !empty( $wcu_referrer ) ) {
    ?>
                            <div class="info-row">
                                <span class="info-label"><?php 
    echo esc_html__( 'Referrer:', 'woo-coupon-usage' );
    ?></span>
                                <span class="info-value"><?php 
    echo esc_html( (string) $wcu_referrer );
    ?></span>
                            </div>
                        <?php 
}
?>

                        <?php 
if ( !empty( $wcu_info ) && is_array( $wcu_info ) ) {
    foreach ( $wcu_info as $key => $val ) {
        if ( $val === '' || $val === null ) {
            continue;
        }
        $label = ( is_string( $key ) ? trim( $key ) : (string) $key );
        if ( $label === '' ) {
            $label = __( 'Field', 'woo-coupon-usage' );
        }
        if ( is_array( $val ) ) {
            $flat_vals = array();
            foreach ( $val as $vv ) {
                if ( is_scalar( $vv ) ) {
                    $flat_vals[] = (string) $vv;
                }
            }
            $value = implode( ', ', $flat_vals );
        } else {
            $value = (string) $val;
        }
        ?>
                                <div class="info-row">
                                    <span class="info-label"><?php 
        echo esc_html( $label . ':' );
        ?></span>
                                    <span class="info-value"><?php 
        echo esc_html( $value );
        ?></span>
                                </div>
                            <?php 
    }
}
?>
                    </div>
                </div>

                <br/>

                <?php 
$wcusage_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', '0' );
$wcusage_field_payout_pending_enable = wcusage_get_setting_value( 'wcusage_field_payout_pending_enable', '1' );
$show_pending_commission = false;
$show_unpaid_commission = false;
?>
        </div>
    </div>

    <div style="clear: both;"></div>

<?php 
/**
 * Display affiliate statistics
 */
function wcusage_display_affiliate_stats(  $user_id, $coupon_id = 'all'  ) {
    $coupons = ( $coupon_id === 'all' ? wcusage_get_users_coupons_ids( $user_id ) : array($coupon_id) );
    // Get user info for the form
    $user_info = get_userdata( $user_id );
    $total_referrals = 0;
    $total_sales = 0;
    $total_commission = 0;
    $unpaid_commission = 0;
    $show_dashboard_message = false;
    $uncalculated_coupons = array();
    foreach ( $coupons as $coupon ) {
        // Get stats
        $wcu_alltime_stats = get_post_meta( $coupon, 'wcu_alltime_stats', true );
        $coupon_title = get_the_title( $coupon );
        // Get referrals with backup logic
        $coupon_referrals = 0;
        $all_stats = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        if ( $all_stats && !$wcusage_hide_all_time && isset( $wcu_alltime_stats ) && isset( $wcu_alltime_stats['total_count'] ) ) {
            $coupon_referrals = $wcu_alltime_stats['total_count'];
        }
        if ( !$coupon_referrals ) {
            global $woocommerce;
            $c = new WC_Coupon($coupon_title);
            $coupon_referrals = $c->get_usage_count();
        }
        $total_referrals += $coupon_referrals;
        // Calculate coupon sales with discount subtraction
        $coupon_sales = 0;
        if ( $wcu_alltime_stats ) {
            if ( isset( $wcu_alltime_stats['total_orders'] ) ) {
                $coupon_sales = $wcu_alltime_stats['total_orders'];
            }
            if ( isset( $wcu_alltime_stats['full_discount'] ) ) {
                $discounts = $wcu_alltime_stats['full_discount'];
                $coupon_sales = (float) $coupon_sales - (float) $discounts;
            }
        }
        // Calculate coupon commission
        $coupon_commission = ( isset( $wcu_alltime_stats['total_commission'] ) ? $wcu_alltime_stats['total_commission'] : 0 );
        // Check if this coupon needs dashboard message
        if ( $coupon_referrals > 0 && (!$coupon_sales || !$coupon_commission) ) {
            $show_dashboard_message = true;
            $coupon_info = wcusage_get_coupon_info_by_id( $coupon );
            $dashboard_url = ( isset( $coupon_info[4] ) ? $coupon_info[4] : '' );
            $uncalculated_coupons[] = array(
                'title'         => $coupon_title,
                'dashboard_url' => $dashboard_url,
            );
        }
        $total_sales += $coupon_sales;
        $total_commission += $coupon_commission;
        $unpaid_commission += (float) get_post_meta( $coupon, 'wcu_text_unpaid_commission', true );
    }
    ?>
    <div class="wcusage-stats-grid">
        <div class="wcusage-stat-box">
            <div class="stat-value"><?php 
    echo esc_html( $total_referrals );
    ?></div>
            <div class="stat-label"><?php 
    echo esc_html__( 'Total Referrals', 'woo-coupon-usage' );
    ?></div>
        </div>
        <div class="wcusage-stat-box">
            <div class="stat-value"><?php 
    echo wcusage_format_price( $total_sales );
    ?></div>
            <div class="stat-label"><?php 
    echo esc_html__( 'Total Sales', 'woo-coupon-usage' );
    ?></div>
        </div>
        <div class="wcusage-stat-box">
            <div class="stat-value"><?php 
    echo wcusage_format_price( $total_commission );
    ?></div>
            <div class="stat-label"><?php 
    echo esc_html__( 'Total Commission', 'woo-coupon-usage' );
    ?></div>
        </div>
    </div>

    <?php 
    if ( $show_dashboard_message ) {
        ?>
    <div style="margin-top: -20px; margin-bottom: 40px; padding: 10px 15px; border: 1px solid #000000ff; border-radius: 4px; background-color: #ffd2d2ff; display: flex; flex-wrap: wrap; align-items: center; gap: 15px;">
        <p style="flex: 1 1 260px; margin: 0;"><strong><?php 
        echo esc_html__( 'Note:', 'woo-coupon-usage' );
        ?></strong> <?php 
        echo esc_html__( 'The affiliate dashboard for one or more coupons needs to be loaded at least once to initially calculate and display complete the statistics.', 'woo-coupon-usage' );
        ?></p>
        <?php 
        if ( !empty( $uncalculated_coupons ) ) {
            ?>
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; margin-left: auto;">
                <?php 
            foreach ( $uncalculated_coupons as $uncalculated_coupon ) {
                ?>
                    <div style="display: flex; align-items: center; gap: 8px; padding: 6px 8px; background-color: #ffffff; border-radius: 3px;">
                        <span style="font-weight: 600;"><?php 
                echo esc_html( $uncalculated_coupon['title'] );
                ?></span>
                        <?php 
                if ( !empty( $uncalculated_coupon['dashboard_url'] ) ) {
                    ?>
                            <a href="<?php 
                    echo esc_url( $uncalculated_coupon['dashboard_url'] );
                    ?>" target="_blank" class="button button-small button-primary">
                                <?php 
                    echo esc_html__( 'Calculate Statistics', 'woo-coupon-usage' );
                    ?> <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i>
                            </a>
                        <?php 
                } else {
                    ?>
                            <em style="font-size: 12px;"><?php 
                    echo esc_html__( 'Not available', 'woo-coupon-usage' );
                    ?></em>
                        <?php 
                }
                ?>
                    </div>
                <?php 
            }
            ?>
            </div>
        <?php 
        }
        ?>
    </div>
    
    <?php 
    }
    ?>

    <?php 
    if ( !empty( $coupons ) ) {
        ?>
    <?php 
        $wcusage_field_payout_pending_enable = wcusage_get_setting_value( 'wcusage_field_payout_pending_enable', '1' );
        $wcusage_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', '0' );
        ?>
    <h3><?php 
        echo esc_html__( 'Affiliates Coupons', 'woo-coupon-usage' );
        ?></h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php 
        echo esc_html__( 'Coupon', 'woo-coupon-usage' );
        ?></th>
                <th><?php 
        echo esc_html__( 'Usage', 'woo-coupon-usage' );
        ?></th>
                <th><?php 
        echo esc_html__( 'Sales', 'woo-coupon-usage' );
        ?></th>
                <th><?php 
        echo esc_html__( 'Commission', 'woo-coupon-usage' );
        ?></th>
                <?php 
        ?>
                <th><?php 
        echo esc_html__( 'Dashboard', 'woo-coupon-usage' );
        ?></th>
                <?php 
        if ( wcusage_get_setting_value( 'wcusage_field_urls_enable', 1 ) ) {
            ?>
                <th class="wcusage-col-link"><?php 
            echo esc_html__( 'Link', 'woo-coupon-usage' );
            ?></th>
                <?php 
        }
        ?>
                <th><?php 
        echo esc_html__( 'Actions', 'woo-coupon-usage' );
        ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
        $include_referral_col = wcusage_get_setting_value( 'wcusage_field_urls_enable', 1 );
        $column_count = 6;
        if ( $include_referral_col ) {
            $column_count++;
        }
        $colspan = $column_count;
        // total columns in table body rows
        foreach ( $coupons as $coupon ) {
            ?>
                <?php 
            $coupon_title = get_the_title( $coupon );
            $wcu_alltime_stats = get_post_meta( $coupon, 'wcu_alltime_stats', true );
            $all_stats = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
            $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
            $coupon_referrals = 0;
            if ( $all_stats && !$wcusage_hide_all_time && isset( $wcu_alltime_stats ) && isset( $wcu_alltime_stats['total_count'] ) ) {
                $coupon_referrals = $wcu_alltime_stats['total_count'];
            }
            if ( !$coupon_referrals ) {
                global $woocommerce;
                $coupon_code = get_the_title( $coupon );
                $c = new WC_Coupon($coupon_code);
                $coupon_referrals = $c->get_usage_count();
            }
            // Calculate coupon sales (same logic as class-coupon-users-table.php)
            $coupon_sales = 0;
            if ( $wcu_alltime_stats ) {
                if ( isset( $wcu_alltime_stats['total_orders'] ) ) {
                    $coupon_sales = $wcu_alltime_stats['total_orders'];
                }
                if ( isset( $wcu_alltime_stats['full_discount'] ) ) {
                    $discounts = $wcu_alltime_stats['full_discount'];
                    $coupon_sales = (float) $coupon_sales - (float) $discounts;
                }
            }
            $coupon_commission = ( isset( $wcu_alltime_stats['total_commission'] ) ? $wcu_alltime_stats['total_commission'] : 0 );
            $coupon_unpaid_commission = (float) get_post_meta( $coupon, 'wcu_text_unpaid_commission', true );
            $coupon_pending_commission = (float) get_post_meta( $coupon, 'wcu_text_pending_order_commission', true );
            // Message for when stats need to be loaded
            $qmessage = esc_html__( 'The affiliate dashboard for this coupon needs to be loaded at-least once.', 'woo-coupon-usage' );
            // Generate affiliate dashboard URL
            $coupon_info = wcusage_get_coupon_info_by_id( $coupon );
            $dashboard_url = ( isset( $coupon_info[4] ) ? $coupon_info[4] : '' );
            $wcusage_urls_prefix = wcusage_get_setting_value( 'wcusage_field_urls_prefix', 'coupon' );
            ?>
                <tr id="coupon-row-<?php 
            echo esc_attr( $coupon );
            ?>">
                    <td>
                        <a href="<?php 
            echo esc_url( admin_url( 'post.php?post=' . $coupon . '&action=edit' ) );
            ?>" title="<?php 
            echo esc_attr__( 'Edit coupon', 'woo-coupon-usage' );
            ?>">
                            <?php 
            echo esc_html( $coupon_title );
            ?>
                        </a>
                    </td>
                    <td><?php 
            echo esc_html( $coupon_referrals );
            ?></td>
                    <td>
                        <?php 
            if ( $coupon_referrals > 0 && !$coupon_sales ) {
                echo "<span title='" . esc_attr( $qmessage ) . "'><strong><i class='fa-solid fa-ellipsis'></i></strong></span>";
            } else {
                echo wcusage_format_price( $coupon_sales );
            }
            ?>
                    </td>
                    <td>
                        <?php 
            if ( $coupon_referrals > 0 && !$coupon_commission ) {
                echo "<span title='" . esc_attr( $qmessage ) . "'><strong><i class='fa-solid fa-ellipsis'></i></strong></span>";
            } else {
                echo wcusage_format_price( $coupon_commission );
            }
            ?>
                    </td>
                    <?php 
            ?>
                    <td>
                        <?php 
            if ( $dashboard_url ) {
                ?>
                            <a href="<?php 
                echo esc_url( $dashboard_url );
                ?>" target="_blank"
                            class="button button-large button-primary wcusage-view-dashboard-btn">
                                <?php 
                echo esc_html__( 'View Dashboard', 'woo-coupon-usage' );
                ?>
                                <i class="fas fa-external-link-alt" style="margin-right: 5px;"></i>
                            </a>
                        <?php 
            } else {
                ?>
                            <em><?php 
                echo esc_html__( 'Not available', 'woo-coupon-usage' );
                ?></em>
                        <?php 
            }
            ?>
                    </td>
                    <?php 
            if ( wcusage_get_setting_value( 'wcusage_field_urls_enable', 1 ) ) {
                ?>
                    <td class="wcusage-col-link">
                        <?php 
                $ref_link = trailingslashit( get_home_url() );
                // Keep consistent with coupons table: home_url + ?prefix=code
                $ref_link = get_home_url() . '?' . $wcusage_urls_prefix . '=' . esc_html( $coupon_title );
                $input_id = 'wcusageLink' . sanitize_html_class( $coupon_title );
                ?>
                        <div class="wcusage-copyable-link">
                            <input type="text" id="<?php 
                echo esc_attr( $input_id );
                ?>" class="wcusage-copy-link-text" value="<?php 
                echo esc_url( $ref_link );
                ?>" style="max-width: 220px; width: 75%; max-height: 24px; min-height: 24px; font-size: 12px;" readonly>
                            <button type="button" class="wcusage-copy-link-button" title="<?php 
                echo esc_attr__( 'Copy', 'woo-coupon-usage' );
                ?>" style="max-height: 24px; min-height: 24px; background: none; border: 1px solid #ddd; padding: 2px 6px; border-radius: 3px;">
                                <i class="fa-regular fa-copy" style="cursor: pointer;"></i>
                            </button>
                        </div>
                    </td>
                    <?php 
            }
            ?>
                    <td>
                        <?php 
            // Actions: stack vertically to save width
            $edit_link = admin_url( 'post.php?post=' . $coupon . '&action=edit' );
            $delete_link = wp_nonce_url( admin_url( 'admin.php?page=wcusage_coupons&delete_coupon=' . $coupon ), 'delete_coupon' );
            ?>
                        <div class="wcusage-actions-inline">
                            <a href="#" class="button button-large button-primary quick-edit-coupon" data-coupon-id="<?php 
            echo esc_attr( $coupon );
            ?>"><?php 
            echo esc_html__( 'Quick Edit', 'woo-coupon-usage' );
            ?></a>
                            <a href="<?php 
            echo esc_url( $edit_link );
            ?>" class="wcusage-inline-link"><?php 
            echo esc_html__( 'Edit', 'woo-coupon-usage' );
            ?></a>
                            <span class="sep">|</span>
                            <a href="<?php 
            echo esc_url( $delete_link );
            ?>" class="wcusage-inline-link wcusage-delete-link" onclick="return confirm('<?php 
            echo esc_js( __( 'Are you sure you want to delete this coupon?', 'woo-coupon-usage' ) );
            ?>');"><?php 
            echo esc_html__( 'Delete', 'woo-coupon-usage' );
            ?></a>
                        </div>
                    </td>
                </tr>
                <?php 
            // Prepare current values for quick edit
            $coupon_obj = new WC_Coupon($coupon);
            $desc = $coupon_obj->get_description();
            $discount_type = $coupon_obj->get_discount_type();
            $amount = $coupon_obj->get_amount();
            $free_shipping = ( $coupon_obj->get_free_shipping() ? 'yes' : 'no' );
            $date_expires = ( $coupon_obj->get_date_expires() ? $coupon_obj->get_date_expires()->date( 'Y-m-d' ) : '' );
            $min_amount = $coupon_obj->get_minimum_amount();
            $max_amount = $coupon_obj->get_maximum_amount();
            $individual_use = ( $coupon_obj->get_individual_use() ? 'yes' : 'no' );
            $exclude_sale_items = ( $coupon_obj->get_exclude_sale_items() ? 'yes' : 'no' );
            $usage_limit_per_user = $coupon_obj->get_usage_limit_per_user();
            $first_order_only = ( get_post_meta( $coupon, 'wcu_enable_first_order_only', true ) === 'yes' ? 'yes' : 'no' );
            $coupon_user_id = $coupon_info[1];
            $coupon_user = ( $coupon_user_id ? get_userdata( $coupon_user_id ) : null );
            $coupon_username = ( $coupon_user ? $coupon_user->user_login : '' );
            $meta_commission = get_post_meta( $coupon, 'wcu_text_coupon_commission', true );
            $meta_commission_fixed_order = get_post_meta( $coupon, 'wcu_text_coupon_commission_fixed_order', true );
            $meta_commission_fixed_product = get_post_meta( $coupon, 'wcu_text_coupon_commission_fixed_product', true );
            $meta_unpaid = get_post_meta( $coupon, 'wcu_text_unpaid_commission', true );
            $meta_pending = get_post_meta( $coupon, 'wcu_text_pending_payment_commission', true );
            ?>
                <?php 
            // Shared quick edit row (same as coupons list)
            include_once WCUSAGE_UNIQUE_PLUGIN_PATH . 'inc/admin/tools/quick-edit-coupon.php';
            wcusage_render_quick_edit_row( $coupon, intval( $colspan ) );
            ?>
            <?php 
        }
        ?>
        </tbody>
    </table>
    <?php 
    }
    ?>

    <br/>

    <?php 
    if ( !empty( $coupons ) ) {
        ?>
    <div style="margin-top: 20px;">
        <button type="button" id="toggle-add-coupon-form" class="button button-secondary"
        onclick="toggleAddCouponForm()">
            <?php 
        echo esc_html__( 'Add New Coupon', 'woo-coupon-usage' );
        ?>
            <i class="fa-solid fa-plus" style="margin-left: 5px;"></i>
        </button>

        <div id="add-coupon-form-container" style="display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <h3><?php 
        echo esc_html__( 'Add New Coupon for Affiliate', 'woo-coupon-usage' );
        ?></h3>
            <p><?php 
        echo esc_html__( 'Create a new coupon and assign it to this affiliate.', 'woo-coupon-usage' );
        ?></p>

            <form method="post" action="" enctype="multipart/form-data">
                <?php 
        wp_nonce_field( 'admin_add_coupon_for_affiliate', 'add_coupon_nonce' );
        ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="affiliate_username"><?php 
        echo esc_html__( 'Affiliate Username', 'woo-coupon-usage' );
        ?></label></th>
                        <td>
                            <input name="affiliate_username" type="text" id="affiliate_username" class="regular-text" value="<?php 
        echo esc_attr( $user_info->user_login );
        ?>" readonly>
                            <br/><i style="font-size: 10px;"><?php 
        echo esc_html__( 'This affiliate will be assigned to the new coupon.', 'woo-coupon-usage' );
        ?></i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="new_coupon_code"><?php 
        echo esc_html__( 'Coupon Code', 'woo-coupon-usage' );
        ?></label></th>
                        <td>
                            <input name="new_coupon_code" type="text" id="new_coupon_code" class="regular-text" value="" required>
                            <br/><i style="font-size: 10px;"><?php 
        echo esc_html__( 'Enter the name of the coupon code that will be created.', 'woo-coupon-usage' );
        ?></i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wcu-message"><?php 
        echo esc_html__( 'Custom Message', 'woo-coupon-usage' );
        ?></label></th>
                        <td>
                            <input name="wcu-message" type="text" id="wcu-message" class="regular-text" value="">
                            <br/><i style="font-size: 10px;"><?php 
        echo esc_html__( 'Optional custom message to include in the notification email.', 'woo-coupon-usage' );
        ?></i>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="add_new_coupon" class="button button-primary" value="<?php 
        echo esc_html__( 'Create Coupon', 'woo-coupon-usage' );
        ?>">
                    <button type="button" class="button button-secondary" onclick="toggleAddCouponForm()"><?php 
        echo esc_html__( 'Cancel', 'woo-coupon-usage' );
        ?></button>
                </p>
            </form>
        </div>
    </div>
    <?php 
    }
    ?>

    <?php 
}

/**
 * Display affiliate referrals
 */
function wcusage_display_affiliate_referrals(
    $user_id,
    $page = 1,
    $per_page = 20,
    $start_date = '',
    $end_date = ''
) {
    global $wpdb;
    // Get all coupons assigned to this affiliate
    $coupons = wcusage_get_users_coupons_ids( $user_id );
    if ( empty( $coupons ) ) {
        echo '<p>' . esc_html__( 'No coupons assigned to this affiliate.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    // Get coupon codes for these coupons
    $coupon_codes = array();
    foreach ( $coupons as $coupon_id ) {
        $coupon_code = get_the_title( $coupon_id );
        if ( $coupon_code ) {
            $coupon_codes[] = $coupon_code;
        }
    }
    if ( empty( $coupon_codes ) ) {
        echo '<p>' . esc_html__( 'No valid coupon codes found for this affiliate.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    // Build query to find orders that used any of these coupon codes
    $placeholders = array_fill( 0, count( $coupon_codes ), '%s' );
    $in_clause = '(' . implode( ',', $placeholders ) . ')';
    $statuses = wc_get_order_statuses();
    if ( isset( $statuses['wc-refunded'] ) ) {
        unset($statuses['wc-refunded']);
    }
    // Date filtering
    $where_date = '';
    $params = $coupon_codes;
    if ( !empty( $start_date ) ) {
        $where_date .= " AND p.post_date >= %s";
        $params[] = $start_date . ' 00:00:00';
    }
    if ( !empty( $end_date ) ) {
        $where_date .= " AND p.post_date <= %s";
        $params[] = $end_date . ' 23:59:59';
    }
    // Pagination
    $page = max( 1, intval( $page ) );
    $per_page = max( 1, intval( $per_page ) );
    $offset = ($page - 1) * $per_page;
    // Count total
    $count_sql = $wpdb->prepare( "SELECT COUNT(DISTINCT p.ID)\r\n         FROM {$wpdb->prefix}posts AS p\r\n         INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi\r\n            ON p.ID = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name IN {$in_clause}\r\n         WHERE p.post_type = 'shop_order'\r\n           AND p.post_status IN ('" . implode( "','", array_keys( $statuses ) ) . "')" . $where_date, $params );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval( $wpdb->get_var( $count_sql ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    // Query to get orders that used any of the affiliate's coupons
    $list_sql = $wpdb->prepare( "SELECT DISTINCT p.ID AS order_id, p.post_date AS order_date\r\n        FROM {$wpdb->prefix}posts AS p\r\n        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi\r\n            ON p.ID = woi.order_id AND woi.order_item_type = 'coupon' AND woi.order_item_name IN {$in_clause}\r\n        WHERE p.post_type = 'shop_order'\r\n        AND p.post_status IN ('" . implode( "','", array_keys( $statuses ) ) . "')" . $where_date . " ORDER BY p.post_date DESC LIMIT %d OFFSET %d", array_merge( $params, array($per_page, $offset) ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $orders = $wpdb->get_results( $list_sql );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( empty( $orders ) ) {
        echo '<p>' . esc_html__( 'No recent referrals found for this affiliate\'s coupons. This could mean that the assigned coupons have not been used in any orders yet, or the orders are still pending.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php 
    echo esc_html__( 'Order ID', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Date', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Customer', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Coupon Code', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Total', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Commission', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Status', 'woo-coupon-usage' );
    ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
    foreach ( $orders as $order_data ) {
        ?>
                <?php 
        $order_id = $order_data->order_id;
        $order = wc_get_order( $order_id );
        if ( !$order ) {
            continue;
        }
        $commission = wcusage_order_meta( $order_id, 'wcusage_total_commission' );
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $customer_name = trim( $billing_first_name . ' ' . $billing_last_name );
        if ( empty( $customer_name ) ) {
            $customer_name = esc_html__( 'Guest', 'woo-coupon-usage' );
        }
        // Get coupon code used in this order
        $coupon_code = '';
        $used_coupons = $order->get_coupon_codes();
        if ( !empty( $used_coupons ) ) {
            $coupon_code = $used_coupons[0];
            // Get first coupon code
        }
        ?>
                <tr>
                    <td><a href="<?php 
        echo esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
        ?>">#<?php 
        echo esc_html( $order_id );
        ?></a></td>
                    <td><?php 
        echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) );
        ?></td>
                    <td><?php 
        echo esc_html( $customer_name );
        ?></td>
                    <td><?php 
        echo esc_html( $coupon_code );
        ?></td>
                    <td><?php 
        echo wcusage_format_price( $order->get_total() );
        ?></td>
                    <td><?php 
        echo wcusage_format_price( $commission );
        ?></td>
                    <td><?php 
        $order_status = $order->get_status();
        $order_status_class = '';
        switch ( $order_status ) {
            case 'completed':
                $order_status_class = 'status-completed';
                break;
            case 'processing':
                $order_status_class = 'status-processing';
                break;
            case 'on-hold':
                $order_status_class = 'status-on-hold';
                break;
            case 'cancelled':
            case 'refunded':
            case 'failed':
                $order_status_class = 'status-cancelled';
                break;
            default:
                $order_status_class = 'status-processing';
                break;
        }
        ?><span class="order-status <?php 
        echo esc_attr( $order_status_class );
        ?>"><?php 
        echo esc_html( wc_get_order_status_name( $order_status ) );
        ?></span></td>
                </tr>
            <?php 
    }
    ?>
        </tbody>
    </table>

    <?php 
    wcusage_render_pagination(
        'referrals',
        $page,
        $per_page,
        $total
    );
    ?>
    <?php 
}

/**
 * Display affiliate visits
 */
function wcusage_display_affiliate_visits(
    $user_id,
    $page = 1,
    $per_page = 20,
    $start_date = '',
    $end_date = ''
) {
    global $wpdb;
    // Handle delete click entry
    if ( isset( $_POST['_wpnonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
        if ( isset( $_POST['wcu-status-delete'] ) && wp_verify_nonce( $nonce, 'delete_url' ) ) {
            $postid = sanitize_text_field( $_POST['wcu-id'] );
            wcusage_delete_click_entry( $postid );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Visit deleted successfully.', 'woo-coupon-usage' ) . '</p></div>';
        }
    }
    // Get all coupons assigned to this affiliate
    $coupons = wcusage_get_users_coupons_ids( $user_id );
    if ( empty( $coupons ) ) {
        echo '<p>' . esc_html__( 'No coupons assigned to this affiliate.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    $table_name = $wpdb->prefix . 'wcusage_clicks';
    // Check if clicks table exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
        echo '<div class="notice notice-info"><p>';
        echo esc_html__( 'Click tracking is not currently enabled.', 'woo-coupon-usage' );
        echo '<br><br>';
        echo sprintf( esc_html__( 'To enable click tracking, go to %s and enable the "Click Tracking" option.', 'woo-coupon-usage' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wcusage_settings' ) ) . '">' . esc_html__( 'Settings', 'woo-coupon-usage' ) . '</a>' );
        echo '</p></div>';
        return;
    }
    // Get recent clicks for any of the affiliate's coupons
    $placeholders = array_fill( 0, count( $coupons ), '%d' );
    $in_clause = '(' . implode( ',', $placeholders ) . ')';
    // Date filtering
    $where_date = '';
    $params = $coupons;
    if ( !empty( $start_date ) ) {
        $where_date .= " AND date >= %s";
        $params[] = $start_date . ' 00:00:00';
    }
    if ( !empty( $end_date ) ) {
        $where_date .= " AND date <= %s";
        $params[] = $end_date . ' 23:59:59';
    }
    // Pagination
    $page = max( 1, intval( $page ) );
    $per_page = max( 1, intval( $per_page ) );
    $offset = ($page - 1) * $per_page;
    // Count total
    $count_sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE couponid IN {$in_clause}" . $where_date, $params );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval( $wpdb->get_var( $count_sql ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    // Fetch page
    $list_sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE couponid IN {$in_clause}" . $where_date . " ORDER BY date DESC LIMIT %d OFFSET %d", array_merge( $params, array($per_page, $offset) ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $clicks = $wpdb->get_results( $list_sql );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( empty( $clicks ) ) {
        echo '<p>' . esc_html__( 'No recent visits found for this affiliate\'s coupons.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped wcusage-visits-table">
        <thead>
            <tr>
                <th><?php 
    echo esc_html__( 'ID', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo sprintf( esc_html__( '%s Coupon', 'woo-coupon-usage' ), wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) );
    ?></th>
                <th><?php 
    echo esc_html__( 'Landing Page', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Referrer URL', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'IP Address', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Visit Date', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Converted', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Action', 'woo-coupon-usage' );
    ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
    foreach ( $clicks as $click ) {
        ?>
                <?php 
        // Get coupon title from coupon ID
        $coupon_title = '';
        $coupon_edit_link = '';
        $uniqueurl = '';
        if ( $click->couponid ) {
            $coupon_title = get_the_title( $click->couponid );
            $coupon_info = wcusage_get_coupon_info_by_id( $click->couponid );
            $uniqueurl = ( isset( $coupon_info[4] ) ? $coupon_info[4] : '' );
            $coupon_edit_link = admin_url( "post.php?post=" . $click->couponid . "&action=edit&classic-editor" );
        }
        // Format landing page
        $landing_page_title = '';
        if ( $click->page ) {
            $landing_page_title = get_the_title( $click->page );
            if ( empty( $landing_page_title ) ) {
                $landing_page_title = esc_html__( 'Unknown Page', 'woo-coupon-usage' );
            }
        }
        // Format referrer
        $referrer_display = $click->referrer;
        if ( empty( $referrer_display ) ) {
            $referrer_display = '<em>' . esc_html__( 'Direct', 'woo-coupon-usage' ) . '</em>';
        }
        // Format date
        $visit_datetime = strtotime( $click->date );
        $formatted_date = date_i18n( "M jS, Y (g:ia)", $visit_datetime );
        // Check if converted
        $is_converted = !empty( $click->orderid );
        ?>
                <tr>
                    <td><?php 
        echo esc_html( $click->id );
        ?></td>
                    <td>
                        <?php 
        if ( $coupon_title ) {
            ?>
                            <a href="<?php 
            echo esc_url( $uniqueurl );
            ?>" target="_blank" title="<?php 
            echo esc_attr( sprintf( __( 'View %s Dashboard', 'woo-coupon-usage' ), wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
            ?>">
                                <?php 
            echo esc_html( $coupon_title );
            ?>
                            </a>
                        <?php 
        } else {
            ?>
                            <em><?php 
            echo esc_html__( 'Unknown', 'woo-coupon-usage' );
            ?></em>
                        <?php 
        }
        ?>
                    </td>
                    <td>
                        <?php 
        if ( $click->page && $landing_page_title ) {
            ?>
                            <a href="<?php 
            echo esc_url( get_permalink( $click->page ) );
            ?>" target="_blank" title="<?php 
            echo esc_attr__( 'View Landing Page', 'woo-coupon-usage' );
            ?>">
                                <?php 
            echo esc_html( $landing_page_title );
            ?>
                            </a>
                        <?php 
        } else {
            ?>
                            <em><?php 
            echo esc_html__( 'Unknown', 'woo-coupon-usage' );
            ?></em>
                        <?php 
        }
        ?>
                    </td>
                    <td><?php 
        echo wp_kses_post( $referrer_display );
        ?></td>
                    <td>
                        <code style="background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 12px;">
                            <?php 
        echo esc_html( $click->ipaddress );
        ?>
                        </code>
                    </td>
                    <td><?php 
        echo esc_html( $formatted_date );
        ?></td>
                    <td>
                        <?php 
        if ( $is_converted ) {
            ?>
                            <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php 
            echo esc_html__( 'Yes', 'woo-coupon-usage' );
            ?>
                            <?php 
            if ( !empty( $click->orderid ) ) {
                ?>
                                <br/><a href="<?php 
                echo esc_url( get_edit_post_link( $click->orderid ) );
                ?>" target="_blank">
                                    #<?php 
                echo esc_html( $click->orderid );
                ?>
                                </a>
                            <?php 
            }
            ?>
                        <?php 
        } else {
            ?>
                            <span class="dashicons dashicons-dismiss" style="color: red;"></span>
                            <?php 
            echo esc_html__( 'No', 'woo-coupon-usage' );
            ?>
                        <?php 
        }
        ?>
                    </td>
                    <td>
                        <form method="post" id="submitclick">
                            <input type="text" id="wcu-id" name="wcu-id" value="<?php 
        echo esc_attr( $click->id );
        ?>" style="display: none;">
                            <input type="text" id="wcu-status-delete" name="wcu-status-delete" value="cancel" style="display: none;">
                            <?php 
        wp_nonce_field( 'delete_url' );
        ?>
                            <button onClick="return confirm('Are you sure you want to delete visit #<?php 
        echo esc_attr( $click->id );
        ?>?');"
                                title="<?php 
        echo esc_attr__( 'Delete this visit.', 'woo-coupon-usage' );
        ?>"
                                type="submit" name="submitclickdelete" style="padding: 0; background: 0; border: 0; cursor: pointer; margin-bottom: 5px; color: #B52828;">
                                <i class="fa-solid fa-trash-can"></i> <?php 
        echo esc_html__( 'Delete', 'woo-coupon-usage' );
        ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php 
    }
    ?>
        </tbody>
    </table>

    <?php 
    wcusage_render_pagination(
        'visits',
        $page,
        $per_page,
        $total
    );
    ?>
    <?php 
}

/**
 * Display affiliate payouts
 */
function wcusage_display_affiliate_payouts(
    $user_id,
    $page = 1,
    $per_page = 20,
    $start_date = '',
    $end_date = ''
) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcusage_payouts';
    // Check if payouts table exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
        echo '<p>' . esc_html__( 'Payouts system not enabled or table not found.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    // Date filtering
    $where_date = '';
    $params = array($user_id);
    if ( !empty( $start_date ) ) {
        $where_date .= " AND date >= %s";
        $params[] = $start_date . ' 00:00:00';
    }
    if ( !empty( $end_date ) ) {
        $where_date .= " AND date <= %s";
        $params[] = $end_date . ' 23:59:59';
    }
    // Pagination
    $page = max( 1, intval( $page ) );
    $per_page = max( 1, intval( $per_page ) );
    $offset = ($page - 1) * $per_page;
    // Count total
    $count_sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE userid = %d" . $where_date, $params );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $total = intval( $wpdb->get_var( $count_sql ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    // Get payouts for this affiliate
    $list_sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE userid = %d" . $where_date . " ORDER BY id DESC LIMIT %d OFFSET %d", array_merge( $params, array($per_page, $offset) ) );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $payouts = $wpdb->get_results( $list_sql );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( empty( $payouts ) ) {
        echo '<p>' . esc_html__( 'No payout history found.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    // Determine if Files column should be shown (based on settings similar to admin payouts page)
    $payouts_enable_invoices = ( function_exists( 'wcusage_get_setting_value' ) ? wcusage_get_setting_value( 'wcusage_field_payouts_enable_invoices', '0' ) : '0' );
    $payouts_enable_statements = ( function_exists( 'wcusage_get_setting_value' ) ? wcusage_get_setting_value( 'wcusage_field_payouts_enable_statements', '0' ) : '0' );
    $show_files_column = ( $payouts_enable_invoices || $payouts_enable_statements ? true : false );
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php 
    echo esc_html__( 'ID', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Coupon', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Amount', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Method', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Status', 'woo-coupon-usage' );
    ?></th>
                <?php 
    if ( $show_files_column ) {
        ?>
                    <th><?php 
        echo esc_html__( 'Files', 'woo-coupon-usage' );
        ?></th>
                <?php 
    }
    ?>
                <th><?php 
    echo esc_html__( 'Date Requested', 'woo-coupon-usage' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Date Paid', 'woo-coupon-usage' );
    ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
    foreach ( $payouts as $payout ) {
        ?>
                <?php 
        $status_class = '';
        switch ( $payout->status ) {
            case 'paid':
                $status_class = 'status-completed';
                break;
            case 'pending':
                $status_class = 'status-on-hold';
                break;
            case 'cancel':
                $status_class = 'status-cancelled';
                break;
            default:
                $status_class = 'status-processing';
                break;
        }
        // Get coupon title
        $coupon_title = '';
        $is_mla_payout = empty( $payout->couponid ) || intval( $payout->couponid ) === 0;
        if ( !$is_mla_payout ) {
            $coupon_title = get_the_title( $payout->couponid );
        }
        // Build files column content similar to admin payouts list
        $files_html = '';
        if ( $show_files_column && function_exists( 'wcusage_files_downloads_buttons' ) ) {
            $files_html = wcusage_files_downloads_buttons(
                ( isset( $payout->invoiceid ) ? $payout->invoiceid : 0 ),
                $payout->id,
                1,
                // always_invoice (show placeholder when enabled but missing)
                1,
                // show_text
                0,
                // download (open in new tab by default)
                ( isset( $payout->status ) ? $payout->status : '' ),
                1
            );
        }
        ?>
                <tr>
                    <td><?php 
        echo esc_html( $payout->id );
        ?></td>
                    <td>
                        <?php 
        if ( $is_mla_payout ) {
            ?>
                            <span class="order-status" style="background:#e8d5f5;color:#5b2c8d;"><?php 
            echo esc_html__( 'MLA', 'woo-coupon-usage' );
            ?></span>
                        <?php 
        } else {
            ?>
                            <?php 
            echo esc_html( $coupon_title );
            ?>
                        <?php 
        }
        ?>
                    </td>
                    <td><?php 
        echo wcusage_format_price( $payout->amount );
        ?></td>
                    <td><?php 
        echo esc_html( $payout->method );
        ?></td>
                    <td><span class="order-status <?php 
        echo esc_attr( $status_class );
        ?>"><?php 
        echo esc_html( ucfirst( $payout->status ) );
        ?></span></td>
                    <?php 
        if ( $show_files_column ) {
            ?>
                        <td><?php 
            echo wp_kses_post( $files_html );
            ?></td>
                    <?php 
        }
        ?>
                    <td><?php 
        echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $payout->date ) ) );
        ?></td>
                    <td><?php 
        echo ( $payout->status === 'paid' && !empty( $payout->datepaid ) ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $payout->datepaid ) ) ) : '-' );
        ?></td>
                </tr>
            <?php 
    }
    ?>
        </tbody>
    </table>

    <?php 
    wcusage_render_pagination(
        'payouts',
        $page,
        $per_page,
        $total
    );
    ?>
    <?php 
}

// Pagination controls are provided by admin-view-affiliate-data.php
/**
 * AJAX handler for getting affiliate stats
 */
add_action( 'wp_ajax_wcusage_get_affiliate_stats', 'wcusage_get_affiliate_stats_ajax' );
function wcusage_get_affiliate_stats_ajax() {
    check_ajax_referer( 'wcusage_affiliate_stats', '_wpnonce' );
    if ( !wcusage_check_admin_access() ) {
        wp_die( 'Access denied' );
    }
    $user_id = ( isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0 );
    $coupon_id = ( isset( $_GET['coupon_id'] ) ? sanitize_text_field( $_GET['coupon_id'] ) : 'all' );
    if ( !$user_id ) {
        wp_die( 'Invalid user ID' );
    }
    wcusage_display_affiliate_stats( $user_id, $coupon_id );
    wp_die();
}

// Referrals/Visits/Payouts AJAX handlers are registered in admin-view-affiliate-data.php (included globally)
/**
 * Display affiliate activity log
 */
function wcusage_display_affiliate_activity(  $user_id  ) {
    global $wpdb;
    // Get activity data for this user
    $table_name = $wpdb->prefix . 'wcusage_activity';
    $sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY id DESC LIMIT 100", $user_id );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    $activities = $wpdb->get_results( $sql, ARRAY_A );
    // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
    if ( empty( $activities ) ) {
        echo '<p>' . esc_html__( 'No activity found for this affiliate.', 'woo-coupon-usage' ) . '</p>';
        return;
    }
    // Display activity in a table format similar to the main activity page
    ?>
    <div style="margin-top: 20px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php 
    echo esc_html__( 'Date', 'woo-coupon-usage' );
    ?></th>
                    <th><?php 
    echo esc_html__( 'Event', 'woo-coupon-usage' );
    ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
    foreach ( $activities as $activity ) {
        ?>
                    <tr>
                        <td>
                            <?php 
        echo esc_html( date_i18n( 'F j, Y (H:i)', strtotime( $activity['date'] ) ) );
        ?>
                        </td>
                        <td>
                            <?php 
        $event_message = wcusage_activity_message( $activity['event'], $activity['event_id'], $activity['info'] );
        echo wp_kses_post( $event_message );
        ?>
                        </td>
                    </tr>
                <?php 
    }
    ?>
            </tbody>
        </table>

        <?php 
    if ( count( $activities ) >= 100 ) {
        ?>
            <p style="margin-top: 10px; color: #666; font-style: italic;">
                <?php 
        echo esc_html__( 'Showing the most recent 100 activities. View the full activity log for more details.', 'woo-coupon-usage' );
        ?>
                <a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_activity' ) );
        ?>" target="_blank">
                    <?php 
        echo esc_html__( 'View Full Activity Log', 'woo-coupon-usage' );
        ?>
                </a>
            </p>
        <?php 
    }
    ?>
    </div>
    <?php 
}

?>

<script type="text/javascript">
function toggleAddCouponForm() {
    var formContainer = document.getElementById('add-coupon-form-container');
    var button = document.getElementById('toggle-add-coupon-form');

    if (formContainer.style.display === 'none') {
        formContainer.style.display = 'block';
        button.innerHTML = '<?php 
echo esc_js( __( "Hide Add Coupon Form", "woo-coupon-usage" ) );
?>';
    } else {
        formContainer.style.display = 'none';
        button.innerHTML = '<?php 
echo esc_js( __( "Add New Coupon +", "woo-coupon-usage" ) );
?>';
    }
}
</script>

