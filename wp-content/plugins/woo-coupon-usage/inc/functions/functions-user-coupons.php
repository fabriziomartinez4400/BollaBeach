<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Adds "coupon_affiliate" Custom User Role
 */
if ( !function_exists( 'wcusage_update_custom_roles' ) ) {
    function wcusage_update_custom_roles() {
        if ( get_option( 'wcusage_custom_roles_version' ) < 1 ) {
            add_role( 'coupon_affiliate', 'Coupon Affiliate', array(
                'read'    => true,
                'level_0' => true,
            ) );
            update_option( 'wcusage_custom_roles_version', 1 );
        }
    }

}
add_action( 'init', 'wcusage_update_custom_roles' );
/**
 * Add custom settings to coupons
 */
if ( !function_exists( 'add_wcusage_coupon_data_fields' ) ) {
    function add_wcusage_coupon_data_fields(  $coupon_get_id  ) {
        echo '<div id="wcusage_coupon_data" class="panel woocommerce_options_panel">';
        $options = get_option( 'wcusage_options' );
        $wcusage_lifetime = wcusage_get_setting_value( 'wcusage_field_lifetime', '0' );
        $wcusage_field_lifetime_all = wcusage_get_setting_value( 'wcusage_field_lifetime_all', '0' );
        $post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : '' );
        $getcurrentcouponuser = ( $post_id ? get_post_meta( $post_id, 'wcu_select_coupon_user', true ) : '' );
        $currentselecteduserlogin = '';
        // Convert stored user ID to username for display
        if ( is_numeric( $getcurrentcouponuser ) && $getcurrentcouponuser ) {
            $user = get_user_by( 'id', $getcurrentcouponuser );
            $currentselecteduserlogin = ( $user ? $user->user_login : '' );
        } elseif ( $getcurrentcouponuser && is_string( $getcurrentcouponuser ) ) {
            // If it's a username (legacy data), use it directly but update to ID
            $currentselecteduserlogin = $getcurrentcouponuser;
            $user = get_user_by( 'login', $getcurrentcouponuser );
            if ( $user ) {
                update_post_meta( $post_id, 'wcu_select_coupon_user', $user->ID );
            }
        }
        // Enqueue jQuery UI Autocomplete
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
        // Generate nonce for AJAX action
        $nonce = wp_create_nonce( 'wcusage_coupon_nonce' );
        ?>
        <style>
            .wcu-input-checkbox label { width: 100%; }
            .ui-autocomplete { max-height: 200px; overflow-y: auto; overflow-x: hidden; z-index: 1000 !important; }
        </style>

        <script>
            jQuery(document).ready(function($) {
                $('#wcu_select_coupon_user').autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: '<?php 
        echo esc_url( admin_url( 'admin-ajax.php' ) );
        ?>',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                search: request.term,
                                label: '',
                                action: 'wcusage_search_users',
                                nonce: '<?php 
        echo esc_js( $nonce );
        ?>'
                            },
                            success: function(data) {
                                if (!data.success) {
                                    console.error('Autocomplete error:', data.data || 'Unknown error');
                                    response([]);
                                    return;
                                }
                                var results = data.data.map(function(item) {
                                    return {
                                        label: item.label,
                                        value: item.value || item.label
                                    };
                                });
                                response(results);
                            },
                            error: function(xhr, status, error) {
                                var errorMsg = xhr.status + ' ' + (xhr.responseText || 'No response from server');
                                console.error('Autocomplete AJAX error:', errorMsg);
                                response([]);
                            }
                        });
                    },
                    minLength: 1,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        return false;
                    },
                    focus: function(event, ui) {
                        return false;
                    }
                });

                // Sync with meta box field
                $('#wcu_select_coupon_user').on('change input', function() {
                    $('#wcu_select_coupon_user_meta').val($(this).val());
                });
            });
        </script>

        <br/>   General Settings:<br/>

        <p class="form-field wcu_select_coupon_user_field">
            <label for="wcu_select_coupon_user"><?php 
        echo esc_html__( 'Affiliate User', 'woo-coupon-usage' );
        ?></label>
            <input type="text" id="wcu_select_coupon_user" name="wcu_select_coupon_user" value="<?php 
        echo esc_attr( $currentselecteduserlogin );
        ?>" class="regular-text" />
            <span class="description"><?php 
        echo esc_html__( 'Type any username. Suggestions will appear as you type, but you can keep your own input.', 'woo-coupon-usage' );
        ?></span>
        </p>

        <?php 
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            ?>
            <hr/><br/>   Custom Commission:<br/>
            <p>Custom commission amounts can be set for each coupon, or you can set the global commission rates for all coupons in the <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage_settings' ) );
            ?>">plugin settings</a> page.</p>

            <?php 
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_text_coupon_commission',
                'label'       => esc_html__( 'Commission %', 'woo-coupon-usage' ),
                'description' => esc_html__( 'Optional: Custom commission "percentage of total order" for this coupon.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_text_coupon_commission_fixed_order',
                'label'       => sprintf( esc_html__( 'Commission %s - Order', 'woo-coupon-usage' ), wcusage_get_currency_symbol() ),
                'description' => esc_html__( 'Optional: Custom commission "fixed amount per order" for this coupon.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_text_coupon_commission_fixed_product',
                'label'       => sprintf( esc_html__( 'Commission %s - Product', 'woo-coupon-usage' ), wcusage_get_currency_symbol() ),
                'description' => esc_html__( 'Optional: Custom commission "fixed amount per product" for this coupon.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_text_coupon_commission_message',
                'label'       => esc_html__( 'Custom Commission Message', 'woo-coupon-usage' ),
                'description' => esc_html__( 'Custom "Commission" message on coupon affiliate dashboard.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
            ?>
        <?php 
        }
        ?>

        <?php 
        woocommerce_wp_text_input( array(
            'type'        => 'date',
            'id'          => 'wcu_text_coupon_start_date',
            'label'       => esc_html__( 'Coupon History Start Date', 'woo-coupon-usage' ),
            'description' => '<i>' . wp_kses_post( esc_html__( 'Custom date to begin displaying past coupon data. Leave empty to show full history.', 'woo-coupon-usage' ) ) . '</i>',
            'desc_tip'    => false,
        ) );
        echo "<br/><hr/><br/>   " . esc_html__( 'Email Notifications:', 'woo-coupon-usage' ) . "<br/>";
        $wcu_enable_notifications = get_post_meta( $coupon_get_id, 'wcu_enable_notifications', true );
        woocommerce_wp_select( array(
            'id'      => 'wcu_enable_notifications',
            'label'   => esc_html__( 'Enable affiliate email notifications.', 'woo-coupon-usage' ),
            'options' => array(
                '1' => esc_html__( 'Enabled', 'woo-coupon-usage' ),
                '0' => esc_html__( 'Disabled', 'woo-coupon-usage' ),
            ),
        ) );
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_notifications_extra',
                'label'       => esc_html__( 'Additional Email Addresses', 'woo-coupon-usage' ),
                'placeholder' => 'example@email.com,another@email.com',
                'description' => esc_html__( 'Additional email addresses to send the affiliate email notifications. Separate each email with a comma.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
        }
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_lifetime && !$wcusage_field_lifetime_all ) {
            echo "<hr/><br/>";
            woocommerce_wp_select( array(
                'id'          => 'wcu_enable_lifetime_commission',
                'label'       => esc_html__( 'Lifetime Commission:', 'woo-coupon-usage' ),
                'description' => esc_html__( 'Enable lifetime commission for this coupon.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
                'options'     => array(
                    '0' => esc_html__( 'Disabled', 'woo-coupon-usage' ),
                    '1' => esc_html__( 'Enabled', 'woo-coupon-usage' ),
                ),
            ) );
            woocommerce_wp_text_input( array(
                'id'          => 'wcu_lifetime_commission_expire',
                'label'       => esc_html__( 'Lifetime Commission Expiry', 'woo-coupon-usage' ),
                'placeholder' => '',
                'description' => esc_html__( 'How many days after being assigned as a "lifetime" referral should it expire, and the customer be unlinked from the customer. Leave empty to use the default global setting. Set to "0" for permanent lifetime commission with no expiry time.', 'woo-coupon-usage' ),
                'desc_tip'    => true,
            ) );
            if ( $wcusage_field_lifetime_all ) {
                echo "<br/><hr/><br/>   <span class='dashicons dashicons-yes-alt'></span> Lifetime commission enabled globally.<br/>";
            }
        }
        echo "<br/><hr/><br/>";
        echo "<p>" . sprintf( esc_html__( 'You can set the global commission rates for all coupons in the <a href="%s">plugin settings</a> page.', 'woo-coupon-usage' ), esc_url( admin_url( "admin.php?page=wcusage_settings" ) ) ) . "</p>";
        echo "<p>" . sprintf( esc_html__( 'Extra features are available with PRO version including custom commission amounts per coupon, email notifications, and more. <a href="%s">UPGRADE</a>', 'woo-coupon-usage' ), esc_url( admin_url( "admin.php?page=wcusage-pricing&trial=true" ) ) ) . "</p>";
        echo "<img src='" . esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . "images/coupon-settings-pro.png' style='max-width: 100%;'>";
        ?>

        <?php 
        if ( !is_plugin_active( 'better-coupon-restrictions/coupon-restrictions.php' ) && !is_plugin_active( 'better-coupon-restrictions-pro/coupon-restrictions-pro.php' ) ) {
            ?>
            <br/><hr/>
            <p>
                <?php 
            echo esc_html__( 'Extra Restrictions', 'woo-coupon-usage' );
            ?>:<br/>
                <?php 
            echo sprintf( wp_kses_post( __( 'Want more advanced coupon usage restrictions? Check out our %s plugin!', 'woo-coupon-usage' ) ), '<a href="https://relywp.com/plugins/better-coupon-restrictions-woocommerce/?utm_source=caffs-settings" target="_blank">Better Coupon Restrictions</a>' );
            ?>
            </p>
        <?php 
        }
        ?>

        </div>
        <?php 
    }

}
add_action( 'woocommerce_coupon_data_panels', 'add_wcusage_coupon_data_fields', 1 );
if ( !function_exists( 'add_wcusage_coupon_data_fields_limits' ) ) {
    function add_wcusage_coupon_data_fields_limits(  $coupon_get_id  ) {
        $allow_all_customers = wcusage_get_setting_value( 'wcusage_field_allow_all_customers', '1' );
        ?>

        <br/>   <?php 
        echo esc_html__( 'Coupon Affiliates - Extra Limits:', 'woo-coupon-usage' );
        ?><br/>

        <?php 
        $wcu_enable_first_order_only = get_post_meta( $coupon_get_id, 'wcu_enable_first_order_only', true );
        woocommerce_wp_checkbox( array(
            'id'          => 'wcu_enable_first_order_only_' . wp_rand( 1, 9999 ),
            'name'        => 'wcu_enable_first_order_only',
            'class'       => 'wcu_enable_first_order_only',
            'value'       => $wcu_enable_first_order_only,
            'label'       => esc_html__( 'New customers only?', 'woo-coupon-usage' ),
            'description' => esc_html__( 'When checked, this coupon can only be used by new customers on their first order.', 'woo-coupon-usage' ),
        ) );
        ?>

        <?php 
        if ( !is_plugin_active( 'better-coupon-restrictions/coupon-restrictions.php' ) && !is_plugin_active( 'better-coupon-restrictions-pro/coupon-restrictions-pro.php' ) ) {
            ?>
            <p class="form-field" style="font-size: 12px; color: #999;">
                <?php 
            echo sprintf( wp_kses_post( __( 'Want more advanced coupon usage restrictions? Check out the %s plugin.', 'woo-coupon-usage' ) ), '<a href="https://relywp.com/plugins/better-coupon-restrictions-woocommerce/?utm_source=caffs-settings" target="_blank">Better Coupon Restrictions</a>' );
            ?>
            </p>
        <?php 
        }
        ?>
        <?php 
    }

}
add_action( 'woocommerce_coupon_options_usage_limit', 'add_wcusage_coupon_data_fields_limits', 1 );
/**
 * Save Coupon Settings on Save
 */
if ( !function_exists( 'wcusage_save_coupon_settings' ) ) {
    function wcusage_save_coupon_settings(  $post_id  ) {
        $wcu_select_coupon_user = ( isset( $_POST['wcu_select_coupon_user'] ) ? sanitize_text_field( $_POST['wcu_select_coupon_user'] ) : '' );
        // Convert username to user ID
        $user = get_user_by( 'login', $wcu_select_coupon_user );
        $user_id = ( $user ? $user->ID : '' );
        // Store the user ID
        update_post_meta( $post_id, 'wcu_select_coupon_user', $user_id );
        if ( isset( $_POST['wcu_text_coupon_start_date'] ) ) {
            $wcu_text_coupon_start_date = sanitize_text_field( $_POST['wcu_text_coupon_start_date'] );
            update_post_meta( $post_id, 'wcu_text_coupon_start_date', $wcu_text_coupon_start_date );
        }
        $first_order_only = ( isset( $_POST['wcu_enable_first_order_only'] ) ? 'yes' : 'no' );
        update_post_meta( $post_id, 'wcu_enable_first_order_only', $first_order_only );
        if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            if ( isset( $_POST ) ) {
                $wcu_text_coupon_commission = get_post_meta( $post_id, 'wcu_text_coupon_commission', true );
                $wcu_text_coupon_commission_fixed_order = get_post_meta( $post_id, 'wcu_text_coupon_commission_fixed_order', true );
                $wcu_text_coupon_commission_fixed_product = get_post_meta( $post_id, 'wcu_text_coupon_commission_fixed_product', true );
                if ( $wcu_text_coupon_commission != $_POST['wcu_text_coupon_commission'] || $wcu_text_coupon_commission_fixed_order != $_POST['wcu_text_coupon_commission_fixed_order'] || $wcu_text_coupon_commission_fixed_product != $_POST['wcu_text_coupon_commission_fixed_product'] ) {
                    delete_post_meta( $post_id, 'wcu_last_refreshed' );
                }
                if ( isset( $_POST['wcu_text_coupon_commission'] ) ) {
                    $wcu_text_coupon_commission = sanitize_text_field( $_POST['wcu_text_coupon_commission'] );
                    update_post_meta( $post_id, 'wcu_text_coupon_commission', $wcu_text_coupon_commission );
                }
                if ( isset( $_POST['wcu_text_coupon_commission_fixed_order'] ) ) {
                    $wcu_text_coupon_commission_fixed_order = sanitize_text_field( $_POST['wcu_text_coupon_commission_fixed_order'] );
                    update_post_meta( $post_id, 'wcu_text_coupon_commission_fixed_order', $wcu_text_coupon_commission_fixed_order );
                }
                if ( isset( $_POST['wcu_text_coupon_commission_fixed_product'] ) ) {
                    $wcu_text_coupon_commission_fixed_product = sanitize_text_field( $_POST['wcu_text_coupon_commission_fixed_product'] );
                    update_post_meta( $post_id, 'wcu_text_coupon_commission_fixed_product', $wcu_text_coupon_commission_fixed_product );
                }
                if ( isset( $_POST['wcu_text_coupon_commission_message'] ) ) {
                    $wcu_text_coupon_commission_message = sanitize_text_field( $_POST['wcu_text_coupon_commission_message'] );
                    update_post_meta( $post_id, 'wcu_text_coupon_commission_message', $wcu_text_coupon_commission_message );
                }
                if ( isset( $_POST['wcu_enable_lifetime_commission'] ) ) {
                    $wcu_enable_lifetime_commission = sanitize_text_field( $_POST['wcu_enable_lifetime_commission'] );
                    update_post_meta( $post_id, 'wcu_enable_lifetime_commission', $wcu_enable_lifetime_commission );
                }
                if ( isset( $_POST['wcu_lifetime_commission_expire'] ) ) {
                    $wcu_lifetime_commission_expire = sanitize_text_field( $_POST['wcu_lifetime_commission_expire'] );
                    update_post_meta( $post_id, 'wcu_lifetime_commission_expire', $wcu_lifetime_commission_expire );
                }
                if ( isset( $_POST['wcu_enable_notifications'] ) ) {
                    $wcu_enable_notifications = sanitize_text_field( $_POST['wcu_enable_notifications'] );
                    update_post_meta( $post_id, 'wcu_enable_notifications', $wcu_enable_notifications );
                }
                if ( isset( $_POST['wcu_notifications_extra'] ) ) {
                    $wcu_notifications_extra = sanitize_text_field( $_POST['wcu_notifications_extra'] );
                    update_post_meta( $post_id, 'wcu_notifications_extra', $wcu_notifications_extra );
                }
                if ( isset( $_POST['wcu_text_unpaid_commission_confirm'] ) ) {
                    $wcu_text_unpaid_commission_confirm = sanitize_text_field( $_POST['wcu_text_unpaid_commission_confirm'] );
                    if ( $wcu_text_unpaid_commission_confirm ) {
                        if ( isset( $_POST['wcu_text_unpaid_commission'] ) ) {
                            $wcu_text_unpaid_commission = floatval( wp_unslash( $_POST['wcu_text_unpaid_commission'] ) );
                            update_post_meta( $post_id, 'wcu_text_unpaid_commission', $wcu_text_unpaid_commission );
                        }
                        if ( isset( $_POST['wcu_text_pending_payment_commission'] ) ) {
                            $wcu_text_pending_payment_commission = floatval( wp_unslash( $_POST['wcu_text_pending_payment_commission'] ) );
                            update_post_meta( $post_id, 'wcu_text_pending_payment_commission', $wcu_text_pending_payment_commission );
                        }
                        if ( isset( $_POST['wcu_text_pending_order_commission'] ) ) {
                            $wcu_text_pending_order_commission = floatval( wp_unslash( $_POST['wcu_text_pending_order_commission'] ) );
                            update_post_meta( $post_id, 'wcu_text_pending_order_commission', $wcu_text_pending_order_commission );
                        }
                        update_post_meta( $post_id, 'wcu_text_unpaid_commission_confirm', 0 );
                    }
                }
            }
        }
    }

}
add_action( 'woocommerce_coupon_options_save', 'wcusage_save_coupon_settings' );
/**
 * Checks if coupon is users
 */
if ( !function_exists( 'wcusage_iscouponusers' ) ) {
    function wcusage_iscouponusers(  $coupon, $current_user_id  ) {
        if ( !$current_user_id ) {
            return false;
        }
        // Check cache first
        $cache_key = 'wcusage_is_coupon_users_' . md5( $coupon . '_' . $current_user_id );
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            return (bool) $cached;
        }
        // Get the coupon by name
        $coupon_obj = new WC_Coupon($coupon);
        if ( !$coupon_obj->get_id() ) {
            set_transient( $cache_key, 0, HOUR_IN_SECONDS );
            return false;
        }
        // Check if this specific coupon is assigned to the user
        $assigned_user_id = get_post_meta( $coupon_obj->get_id(), 'wcu_select_coupon_user', true );
        $is_users = $assigned_user_id && $assigned_user_id == $current_user_id;
        // Cache the result
        set_transient( $cache_key, ( $is_users ? 1 : 0 ), HOUR_IN_SECONDS );
        return $is_users;
    }

}
/**
 * Checks if user id is an affiliate (assigned to at least 1 coupon)
 */
if ( !function_exists( 'wcusage_is_user_affiliate' ) ) {
    function wcusage_is_user_affiliate(  $user_id  ) {
        if ( !$user_id ) {
            return false;
        }
        // Check transient cache first
        $cache_key = 'wcusage_is_affiliate_' . $user_id;
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            return (bool) $cached;
        }
        // Only need to find 1 coupon to confirm affiliate status
        $args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => array(array(
                'key'     => 'wcu_select_coupon_user',
                'value'   => $user_id,
                'compare' => '=',
            )),
        );
        $query = new WP_Query($args);
        $is_affiliate = $query->post_count > 0;
        // Cache for 1 hour
        set_transient( $cache_key, ( $is_affiliate ? 1 : 0 ), HOUR_IN_SECONDS );
        wp_reset_postdata();
        return $is_affiliate;
    }

}
/**
 * Get IDs of all coupons assigned to user
 */
if ( !function_exists( 'wcusage_get_users_coupons_ids' ) ) {
    function wcusage_get_users_coupons_ids(  $user_id  ) {
        if ( !$user_id ) {
            return array();
        }
        // Check transient cache first
        $cache_key = 'wcusage_user_coupon_ids_' . $user_id;
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            return ( is_array( $cached ) ? $cached : array() );
        }
        // Use 'fields' => 'ids' for efficiency - only returns IDs, not full post objects
        $args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => array(array(
                'key'     => 'wcu_select_coupon_user',
                'value'   => $user_id,
                'compare' => '=',
            )),
        );
        $post_ids = get_posts( $args );
        // Cache for 1 hour
        set_transient( $cache_key, $post_ids, HOUR_IN_SECONDS );
        return ( is_array( $post_ids ) ? $post_ids : array() );
    }

}
/**
 * Get IDs of all coupons assigned to user by name
 */
if ( !function_exists( 'wcusage_get_users_coupons_names' ) ) {
    function wcusage_get_users_coupons_names(  $user_id  ) {
        if ( !$user_id ) {
            return array();
        }
        // Check cache first
        $cache_key = 'wcusage_user_coupon_names_' . $user_id;
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }
        // Get coupon IDs efficiently (reuse existing optimized function)
        $coupon_ids = wcusage_get_users_coupons_ids( $user_id );
        $coupons = array();
        foreach ( $coupon_ids as $coupon_id ) {
            $coupon_name = get_the_title( $coupon_id );
            if ( $coupon_name ) {
                $coupons[] = $coupon_name;
            }
        }
        // Cache the result for 1 hour
        set_transient( $cache_key, $coupons, HOUR_IN_SECONDS );
        return $coupons;
    }

}
/**
 * Function to output the list of coupons assigned to user, on the affiliate dashboard
 */
if ( !function_exists( 'wcusage_getUserCouponList' ) ) {
    function wcusage_getUserCouponList() {
        ob_start();
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $current_username = $current_user->user_login;
        // Check if admin is previewing another user's dashboard
        $preview_user_id = $current_user_id;
        $is_admin_preview = false;
        if ( isset( $_GET['userid'] ) && isset( $_GET['preview_nonce'] ) && wcusage_check_admin_access() ) {
            $preview_user_id_param = intval( $_GET['userid'] );
            $preview_nonce = sanitize_text_field( $_GET['preview_nonce'] );
            // Verify the nonce
            if ( wp_verify_nonce( $preview_nonce, 'wcusage_preview_affiliate_' . $preview_user_id_param ) ) {
                $preview_user_id = $preview_user_id_param;
                $is_admin_preview = true;
            }
        }
        $args = array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'meta_query'     => array(array(
                'key'     => 'wcu_select_coupon_user',
                'value'   => $preview_user_id,
                'compare' => '=',
            )),
        );
        $obituary_query = new WP_Query($args);
        $numcoupons = $obituary_query->post_count;
        $urlid = ( isset( $_GET['couponid'] ) ? sanitize_text_field( $_GET['couponid'] ) : "" );
        $urlid = str_replace( array(']', '[', '"'), '', $urlid );
        $wcusage_justcoupon = wcusage_get_setting_value( 'wcusage_field_justcoupon', '1' );
        $wcusage_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
        $wcusage_loginform = wcusage_get_setting_value( 'wcusage_field_loginform', '1' );
        $wcusage_registration_enable_login = wcusage_get_setting_value( 'wcusage_field_registration_enable_login', '1' );
        $wcusage_registration_enable_logout = wcusage_get_setting_value( 'wcusage_field_registration_enable_logout', '1' );
        $wcusage_show_coupon_if_single = wcusage_get_setting_value( 'wcusage_field_show_coupon_if_single', '1' );
        $wcusage_field_form_style = wcusage_get_setting_value( 'wcusage_field_form_style', '3' );
        $wcusage_field_form_style_columns = wcusage_get_setting_value( 'wcusage_field_form_style_columns', '1' );
        if ( $urlid ) {
            if ( shortcode_exists( 'couponaffiliates' ) ) {
                echo do_shortcode( '[couponaffiliates coupon="' . esc_attr( $urlid ) . '"]' );
            }
        } else {
            ?>
            
            <?php 
            // Get username for display
            $display_username = $current_username;
            if ( $is_admin_preview ) {
                $preview_user = get_userdata( $preview_user_id );
                $display_username = ( $preview_user ? $preview_user->user_login : 'Unknown User' );
            }
            ?>

            <h3 class="wcu-user-coupon-title"><?php 
            echo sprintf( esc_html__( 'My %s Coupons', 'woo-coupon-usage' ), esc_html( ( function_exists( 'wcusage_get_affiliate_text' ) ? wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) : __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
            ?> <?php 
            if ( $is_admin_preview ) {
                echo '<small>(Viewing as: ' . esc_html( $display_username ) . ')</small>';
            }
            ?></h3>
            <hr class="wcu-user-coupon-linebreak" />

            <?php 
            if ( !is_user_logged_in() && !$is_admin_preview ) {
                if ( $wcusage_loginform || $wcusage_registration_enable ) {
                    ob_start();
                    ?>
                    <style>.wcu-user-coupon-title { display: none; }</style>

                    <div class="wcusage-login-form-cols">

                    <?php 
                    if ( $wcusage_loginform && $wcusage_registration_enable && $wcusage_registration_enable_login && $wcusage_registration_enable_logout ) {
                        ?>
                        <div class="wcusage-login-form-col wcu_form_style_<?php 
                        echo esc_attr( $wcusage_field_form_style );
                        if ( $wcusage_field_form_style_columns ) {
                            ?> wcu_form_style_columns<?php 
                        }
                        ?>">
                    <?php 
                    }
                    ?>

                    <?php 
                    if ( $wcusage_loginform ) {
                        ?>

                    <div class="wcu-form-section">

                        <p class="wcusage-login-form-title" style="font-size: 1.2em;"><strong><?php 
                        echo esc_html__( 'Login', 'woo-coupon-usage' );
                        ?>:</strong></p>

                        <div class="wcusage-login-form-section">
                        <?php 
                        if ( function_exists( 'wc_print_notices' ) ) {
                            woocommerce_output_all_notices();
                        }
                        if ( function_exists( 'woocommerce_login_form' ) ) {
                            woocommerce_login_form();
                        }
                        ?>
                        </div>

                    </div>

                    <?php 
                    }
                    ?>

                    <?php 
                    if ( $wcusage_loginform && $wcusage_registration_enable && $wcusage_registration_enable_login && $wcusage_registration_enable_logout ) {
                        ?>
                    </div>
                    <?php 
                    }
                    ?>

                    <?php 
                    if ( $wcusage_registration_enable && $wcusage_registration_enable_login && $wcusage_registration_enable_logout ) {
                        echo "<div class='wcusage-login-form-col'>";
                        if ( shortcode_exists( 'couponaffiliates-register' ) ) {
                            echo do_shortcode( '[couponaffiliates-register]' );
                        }
                        echo "</div>";
                    }
                    ?>

                    </div>

                    <?php 
                    return ob_get_clean();
                } else {
                    echo esc_html__( "No affiliate dashboard found. Please contact us.", "woo-coupon-usage" );
                    if ( current_user_can( 'administrator' ) ) {
                        echo "<br/><br/><strong>Admin message:</strong><br/>To get started, go to the '<strong><a href='" . esc_url( admin_url( "admin.php?page=wcusage_coupons" ) ) . "'>coupons list</a></strong>' in your dashboard, where you can find a list of the affiliate dashboard URLs.";
                    }
                }
            } else {
                if ( !$numcoupons ) {
                    echo '<p>' . sprintf( esc_html__( "You don't have any active %s coupons right now.", 'woo-coupon-usage' ), esc_html( ( function_exists( 'wcusage_get_affiliate_text' ) ? wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) : __( 'affiliate', 'woo-coupon-usage' ) ) ) ) . '</p>';
                    $wcusage_field_registration_enable_register_loggedin = wcusage_get_setting_value( 'wcusage_field_registration_enable_register_loggedin', '1' );
                    if ( $wcusage_field_registration_enable_register_loggedin || isset( $_POST['submitaffiliateapplication'] ) ) {
                        echo "<br/>";
                        if ( shortcode_exists( 'couponaffiliates-register' ) ) {
                            echo do_shortcode( '[couponaffiliates-register]' );
                        }
                    }
                }
                $countcoupons = 0;
                $countcouponsloop = 0;
                $lastcoupon = "";
                while ( $obituary_query->have_posts() ) {
                    $obituary_query->the_post();
                    $postid = get_the_ID();
                    $coupon = get_the_title();
                    $page_url = wcusage_get_coupon_shortcode_page( 1 );
                    $secretid = $coupon . "-" . $postid;
                    $uniqueurl = $page_url . 'couponid=' . $secretid;
                    if ( $numcoupons <= 1 && $wcusage_show_coupon_if_single ) {
                        if ( wcusage_iscouponusers( $coupon, $preview_user_id ) && $lastcoupon != $coupon ) {
                            $coupon = str_replace( ' ', '%20', $coupon );
                            if ( shortcode_exists( 'couponaffiliates' ) ) {
                                echo do_shortcode( "[couponaffiliates coupon=" . $coupon . "]" );
                            }
                            echo "<style>.admin-only-list-coupons, .wcu-user-coupon-title, .wcu-user-coupon-linebreak { display: none; }</style>";
                        }
                        $lastcoupon = $coupon;
                    } else {
                        $wcu_select_coupon_user = get_post_meta( $postid, 'wcu_select_coupon_user', true );
                        // This is a user ID
                        if ( get_the_title() && $wcu_select_coupon_user == $preview_user_id ) {
                            $countcoupons++;
                            $countcouponsloop++;
                            if ( $countcouponsloop == 1 ) {
                                echo "<div class='wcu-user-coupon-list-group'>";
                            }
                            echo "<div class='wcu-user-coupon-list'>";
                            echo "<h3>" . esc_html( get_the_title() ) . "</h3>";
                            $amount = get_post_meta( $postid, 'coupon_amount', true );
                            $discount_type = get_post_meta( $postid, 'discount_type', true );
                            $combined_commission = wcusage_commission_message( $postid );
                            if ( $discount_type == "percent" ) {
                                $discount_msg = $amount . "%";
                            } elseif ( $discount_type == "recurring_percent" ) {
                                $discount_msg = $amount . "% (" . esc_html__( 'Recurring', 'woo-coupon-usage' ) . ")";
                            } elseif ( $discount_type == "fixed_cart" ) {
                                $discount_msg = wcusage_get_currency_symbol() . $amount;
                            } else {
                                if ( $discount_type ) {
                                    $discount_msg = $amount . " (" . $discount_type . ")";
                                } else {
                                    $discount_msg = "";
                                }
                            }
                            if ( $discount_msg ) {
                                echo '<p>' . esc_html__( "Discount", "woo-coupon-usage" ) . ': ' . esc_html( $discount_msg ) . '</p>';
                            }
                            global $woocommerce;
                            $c = new WC_Coupon(get_the_title());
                            $usage = $c->get_usage_count();
                            if ( $usage === "" ) {
                                $usage = '0';
                            }
                            $wcu_alltime_stats = get_post_meta( $postid, 'wcu_alltime_stats', true );
                            if ( !empty( $wcu_alltime_stats['total_count'] ) ) {
                                $usage = $wcu_alltime_stats['total_count'];
                            }
                            echo '<p>' . esc_html__( "Total Usage", "woo-coupon-usage" ) . ': ' . esc_html( $usage ) . '</p>';
                            echo '<p>' . esc_html__( "Commission", "woo-coupon-usage" ) . ': ' . wp_kses_post( $combined_commission ) . '</p>';
                            // Convert user ID to username for display
                            $user = get_user_by( 'id', $wcu_select_coupon_user );
                            $display_username = ( $user ? $user->user_login : '' );
                            echo '<p>' . esc_html__( "Affiliate", "woo-coupon-usage" ) . ': ' . esc_html( $display_username ) . '</p>';
                            echo '<p style="margin: 0 0 10px 0;"><a class="wcu-coupon-list-button"
                            href="' . esc_url( $uniqueurl ) . '">' . esc_html__( 'Dashboard', 'woo-coupon-usage' ) . ' <i class="far fa-arrow-alt-circle-right"></i></a></p>';
                            echo "</div>";
                            if ( $countcouponsloop == 3 ) {
                                echo "</div>";
                                $countcouponsloop = 0;
                            }
                        }
                    }
                }
                if ( $countcouponsloop != 3 ) {
                    echo "</div>";
                }
            }
            echo "<div style='clear: both;'></div>";
        }
        $thecontent = ob_get_contents();
        ob_end_clean();
        wp_reset_postdata();
        return $thecontent;
    }

}
add_shortcode( 'couponusage-user', 'wcusage_getUserCouponList' );
add_shortcode( 'couponaffiliates-user', 'wcusage_getUserCouponList' );
add_action(
    'wcusage_hook_getUserCouponList',
    'wcusage_getUserCouponList',
    10,
    0
);
/**
 * Adds meta box to coupon page.
 */
if ( !function_exists( 'wcusage_add_coupon_meta_box' ) ) {
    function wcusage_add_coupon_meta_box() {
        add_meta_box(
            "wcusage-meta-box",
            "Coupon Affiliates",
            "wcusage_coupon_meta_box_markup",
            "shop_coupon",
            "side",
            "low",
            null
        );
    }

}
add_action( "add_meta_boxes", "wcusage_add_coupon_meta_box" );
/**
 * Content for meta box on coupons page.
 */
if ( !function_exists( 'wcusage_coupon_meta_box_markup' ) ) {
    function wcusage_coupon_meta_box_markup() {
        if ( isset( $_GET['post'] ) ) {
            $post_id = $_GET['post'];
            $coupon_info = wcusage_get_coupon_info_by_id( $post_id );
            $uniqueurl = $coupon_info[4];
            $coupon_user = get_post_meta( $post_id, 'wcu_select_coupon_user', true );
            // Convert user ID to username for display
            if ( is_numeric( $coupon_user ) && $coupon_user ) {
                $user = get_user_by( 'id', $coupon_user );
                $coupon_user = ( $user ? $user->user_login : '' );
            } elseif ( $coupon_user && is_string( $coupon_user ) ) {
                // If it's a username (legacy data), update to ID
                $user = get_user_by( 'login', $coupon_user );
                if ( $user ) {
                    update_post_meta( $post_id, 'wcu_select_coupon_user', $user->ID );
                }
                $coupon_user = ( $user ? $user->user_login : '' );
            }
            if ( !is_string( $coupon_user ) && !is_numeric( $coupon_user ) ) {
                $coupon_user = '';
            }
            if ( isset( $_GET['refreshstats'] ) ) {
                if ( $_GET['refreshstats'] ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php 
                    echo sprintf( wp_kses_post( __( 'Done! The affiliate statistics for this coupon will be refreshed the next time the <a href="%s">affiliate dashboard</a> is loaded.', 'woo-coupon-usage' ) ), esc_url( $uniqueurl ) );
                    ?></p>
                    </div>
                    <?php 
                    delete_post_meta( $post_id, 'wcu_last_refreshed' );
                }
            }
            // Enqueue jQuery UI Autocomplete
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
            // Generate nonce for AJAX
            $nonce = wp_create_nonce( 'wcusage_coupon_nonce' );
            ?>

            <p style="margin-top: 14px;"><a href="<?php 
            echo esc_url( $uniqueurl );
            ?>" target="_blank" class="wcusage-settings-button"
            style="margin: 0; text-align: center; margin: 0 auto; display: block;">
                <?php 
            echo esc_html__( 'View Affiliate Dashboard', 'woo-coupon-usage' );
            ?>
                <span class="dashicons dashicons-external"></span></a></p>

            <p class="form-field wcu_select_coupon_user_meta_field" style="margin-top: 5px; margin-bottom: 20px;">
                <label for="wcu_select_coupon_user_meta"><?php 
            echo esc_html__( 'Affiliate User', 'woo-coupon-usage' );
            ?></label>
                <input type="text" id="wcu_select_coupon_user_meta" style="width: 100%;"
                name="wcu_select_coupon_user_meta" value="<?php 
            echo esc_attr( $coupon_user );
            ?>" class="regular-text" />
            </p>

            <p style="margin-bottom: 5px;">
              <a href="#" class="" onclick="if (confirm('<?php 
            echo esc_html__( 'Are you sure you want to refresh all this coupons affiliate dashboard data? The next time you visit the affiliate dashboard, it may take significantly longer to load (first visit).', 'woo-coupon-usage' );
            ?>')){location+='&refreshstats=true'}else{event.stopPropagation(); event.preventDefault();};">
                <?php 
            echo esc_html__( 'REFRESH ALL DATA', 'woo-coupon-usage' );
            ?> <i class="fas fa-sync" style="background: transparent; margin: 0;"></i>
              </a>
            </p>

            <style>
                .ui-autocomplete { max-height: 200px; overflow-y: auto; overflow-x: hidden; z-index: 1000 !important; }
            </style>

            <script>
                jQuery(document).ready(function($) {
                    $('#wcu_select_coupon_user_meta').autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: '<?php 
            echo admin_url( 'admin-ajax.php' );
            ?>',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    search: request.term,
                                    label: '',
                                    action: 'wcusage_search_users',
                                    nonce: '<?php 
            echo esc_js( $nonce );
            ?>'
                                },
                                success: function(data) {
                                    if (!data.success) {
                                        response([]);
                                        return;
                                    }
                                    var results = data.data.map(function(item) {
                                        return {
                                            label: item.label,
                                            value: item.value || item.label
                                        };
                                    });
                                    response(results);
                                },
                                error: function(xhr, status, error) {
                                    response([]);
                                }
                            });
                        },
                        minLength: 1,
                        select: function(event, ui) {
                            $(this).val(ui.item.value);
                            $('#wcu_select_coupon_user').val(ui.item.value);
                            return false;
                        },
                        focus: function(event, ui) {
                            return false;
                        }
                    });

                    // Sync with main field
                    $('#wcu_select_coupon_user_meta').on('change input', function() {
                        $('#wcu_select_coupon_user').val($(this).val());
                    });
                });
            </script>

            <?php 
        }
    }

}
/**
 * Unlink user from coupon
 */
if ( !function_exists( 'wcusage_coupon_affiliate_unlink' ) ) {
    function wcusage_coupon_affiliate_unlink(  $coupon  ) {
        // Get the current user ID before unlinking
        $user_id = get_post_meta( $coupon, 'wcu_select_coupon_user', true );
        // Unlink the coupon
        update_post_meta( $coupon, 'wcu_select_coupon_user', '' );
        // Clear the user's affiliate column cache, affiliate status cache, and coupon IDs cache
        if ( $user_id ) {
            delete_transient( 'wcusage_user_affiliate_col_' . $user_id );
            delete_transient( 'wcusage_is_affiliate_' . $user_id );
            delete_transient( 'wcusage_user_coupon_ids_' . $user_id );
            delete_transient( 'wcusage_user_coupon_names_' . $user_id );
        }
        $coupon_name = get_the_title( $coupon );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Coupon unlinked from user:', 'woo-coupon-usage' ) . esc_html( $coupon ) . '</p></div>';
    }

}
add_filter(
    'wcusage_hook_coupon_affiliate_unlink',
    'wcusage_coupon_affiliate_unlink',
    10,
    1
);