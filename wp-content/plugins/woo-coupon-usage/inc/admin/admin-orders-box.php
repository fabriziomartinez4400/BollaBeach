<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Add a custom meta box to the WooCommerce order edit page
 */
function wcusage_add_custom_box() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        $screen = wc_get_page_screen_id( 'shop-order' );
    } else {
        $screen = 'shop_order';
    }
    add_meta_box(
        'wcusage_affiliate_info',
        'Coupon Affiliate',
        'wcusage_custom_box_html',
        $screen,
        'side',
        'high'
    );
}

add_action( 'add_meta_boxes', 'wcusage_add_custom_box' );
/**
 * Custom box HTML
 *
 * @param object $post The post object.
 */
function wcusage_custom_box_html(  $post  ) {
    $options = get_option( 'wcusage_options' );
    $wcusage_show_column_code = wcusage_get_setting_value( 'wcusage_field_show_orders_aff_info', '1' );
    $coupon_code = "";
    $lifetimeaffiliate = "";
    if ( !empty( $post ) && $post instanceof WP_Post && property_exists( $post, 'ID' ) ) {
        $post_id = $post->ID;
    } else {
        if ( method_exists( $post, 'get_id' ) ) {
            $post_id = $post->get_id();
        } else {
            $post_id = "";
        }
    }
    $order = wc_get_order( $post_id );
    if ( $order ) {
        if ( $wcusage_show_column_code ) {
            $affiliate = array();
            $coupon_codes = array();
            $lifetimeaffiliate = wcusage_order_meta( $post_id, 'lifetime_affiliate_coupon_referrer' );
            $affiliatereferrer = wcusage_order_meta( $post_id, 'wcusage_referrer_coupon' );
            if ( $lifetimeaffiliate ) {
                $coupon_code = $lifetimeaffiliate;
                wcusage_custom_box_html_content(
                    $lifetimeaffiliate,
                    $post,
                    $order,
                    1
                );
            } elseif ( $affiliatereferrer ) {
                wcusage_custom_box_html_content(
                    $affiliatereferrer,
                    $post,
                    $order,
                    2
                );
            } else {
                if ( class_exists( 'WooCommerce' ) ) {
                    if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
                        foreach ( $order->get_coupon_codes() as $coupon_code ) {
                            if ( $coupon_code ) {
                                wcusage_custom_box_html_content(
                                    $coupon_code,
                                    $post,
                                    $order,
                                    0
                                );
                                $coupon_codes[] = $coupon_code;
                            }
                        }
                    }
                }
            }
            if ( !$order->get_coupon_codes() && !$lifetimeaffiliate && !$affiliatereferrer ) {
                echo "<p>" . esc_html__( "No coupons were used for this order.", "woo-coupon-usage" ) . "</p>";
            }
        } else {
            echo "<p>" . esc_html__( "Affiiliate Info not available.", "woo-coupon-usage" ) . "</p>";
        }
        $wcusage_referrer_coupon = wcusage_order_meta( $post_id, 'wcusage_referrer_coupon', true );
        if ( $lifetimeaffiliate ) {
            $wcusage_referrer_coupon = "";
        }
        wp_nonce_field( basename( __FILE__ ), 'wcusage_referrer_coupon_nonce' );
    } else {
        echo "<p>" . esc_html__( "Affiiliate Info not available.", "woo-coupon-usage" ) . "</p>";
    }
    if ( $order ) {
        $order_status = $order->get_status();
    } else {
        $order_status = "";
    }
    ?>

    <?php 
    do_action(
        'wcusage_hook_order_box_before_custom_referrer',
        $post_id,
        $order,
        $coupon_code,
        $lifetimeaffiliate,
        $affiliatereferrer
    );
    ?>

    <?php 
    if ( $order_status != 'completed' || $wcusage_referrer_coupon ) {
        ?>
    <p>
        <label for="wcusage_referrer_coupon"><?php 
        echo esc_html__( 'Affiliate Referrer Coupon', 'woo-coupon-usage' );
        ?>: <?php 
        echo wp_kses_post( wc_help_tip( esc_html__( 'Set the primary referral coupon for this order. This will override all other settings, as the default and only coupon that will earn commission from this order.', 'woo-coupon-usage' ), false ) );
        ?>
        </label>
        <input type="text" id="wcusage_referrer_coupon" name="wcusage_referrer_coupon" value="<?php 
        echo esc_attr( $wcusage_referrer_coupon );
        ?>" style="width: 100%;"
        <?php 
        if ( !$wcusage_referrer_coupon && $coupon_code ) {
            if ( count( $coupon_codes ) > 1 ) {
                $coupon_code = "";
            }
            ?>placeholder="<?php 
            echo esc_html( $coupon_code );
            ?>"<?php 
        }
        ?>
        <?php 
        if ( $lifetimeaffiliate ) {
            ?>title="<?php 
            echo esc_html__( 'This can not be edited for a lifetime affiliate referral.', 'woo-coupon-usage' );
            ?>" readonly<?php 
        }
        ?>
        <?php 
        if ( !$lifetimeaffiliate && $order_status == 'completed' ) {
            ?>title="<?php 
            echo esc_html__( 'This can not be edited when the order is completed.', 'woo-coupon-usage' );
            ?>" readonly<?php 
        }
        ?>>
        <br/>
    </p>
    <?php 
    }
    ?>

    <?php 
    do_action(
        'wcusage_hook_order_box_after_custom_referrer',
        $post_id,
        $order,
        $coupon_code,
        $lifetimeaffiliate,
        $affiliatereferrer
    );
    ?>

    <?php 
}

/**
 * Custom box HTML content
 *
 * @param string $coupon_code The coupon code.
 * @param object $post The post object.
 * @param object $order The order object.
 * @param int $type The type of referral (1 for lifetime, 2 for custom).
 */
function wcusage_custom_box_html_content(
    $coupon_code,
    $post,
    $order,
    $type
) {
    $order_id = $order->get_id();
    $order = wc_get_order( $order_id );
    $paidcommission = wcusage_order_meta( $order_id, 'wcu_commission_paid', true );
    $lifetimeaffiliatedone = false;
    if ( !empty( $_GET['update_unpaid_commission'] ) && $_GET['update_unpaid_commission'] ) {
        $paidcommission = wcusage_order_meta( $order_id, 'wcu_commission_paid', true );
        $lifetimeaffiliate = wcusage_order_meta( $order_id, 'lifetime_affiliate_coupon_referrer' );
        if ( $lifetimeaffiliate && !$lifetimeaffiliatedone ) {
            wcusage_do_action_order_update_commission(
                $order,
                $order_id,
                $lifetimeaffiliate,
                $paidcommission
            );
            $lifetimeaffiliatedone = true;
        }
        if ( !$lifetimeaffiliate ) {
            $affiliatereferrer = wcusage_order_meta( $order_id, 'wcusage_referrer_coupon' );
            if ( $affiliatereferrer ) {
                wcusage_do_action_order_update_commission(
                    $order,
                    $order_id,
                    $affiliatereferrer,
                    $paidcommission
                );
            } else {
                foreach ( $order->get_coupon_codes() as $coupon_code ) {
                    wcusage_do_action_order_update_commission(
                        $order,
                        $order_id,
                        $coupon_code,
                        $paidcommission
                    );
                }
            }
        }
    }
    $getinfo = wcusage_get_the_order_coupon_info( $coupon_code, "", $order_id );
    $coupon_info = wcusage_get_coupon_info( $coupon_code );
    $coupon_id = $coupon_info[2];
    // Check if pending commission needs to be added
    if ( function_exists( 'wcusage_check_and_add_pending_commission' ) ) {
        wcusage_check_and_add_pending_commission( $order_id );
    }
    echo "<p style='position: absolute; right: 10px; top: -2px; margin: 0; padding: 0;'>";
    echo "<a href='" . esc_url( admin_url( 'post.php?post=' . esc_attr( $order_id ) . '&action=edit&refresh_stats=1' ) ) . "' style='text-decoration: none;'\r\n    onClick='return confirm(\"" . esc_html__( 'Are you sure you want to refresh the affiliate stats for this order? This will delete the current referral stats/commission and recalculate them.', 'woo-coupon-usage' ) . "\");'\r\n    title='" . esc_html__( 'Recalculate the affiliate stats for this order.', 'woo-coupon-usage' ) . "'\r\n    ><span class='dashicons dashicons-update' style='font-size: 14px; height: 14px; display: inline-block; margin-top: 4px;'></span></a>";
    if ( isset( $_GET['refresh_stats'] ) && $_GET['refresh_stats'] ) {
        if ( function_exists( 'wcusage_update_pending_commission_action' ) ) {
            wcusage_update_pending_commission_action( $order_id, 'remove' );
        }
        delete_post_meta( $order_id, 'wcusage_commission_summary' );
        delete_post_meta( $order_id, 'wcusage_stats' );
        delete_post_meta( $order_id, 'wcusage_total_commission' );
        delete_post_meta( $order_id, 'wcusage_fixed_order_commission' );
        delete_post_meta( $order_id, 'wcu_mla_commission' );
        $url = remove_query_arg( 'refresh_stats' );
        wp_safe_redirect( $url );
        exit;
    }
    echo "</p>";
    echo "<p>";
    if ( $type == 1 ) {
        echo '(' . esc_html__( 'Lifetime Referrer', 'woo-coupon-usage' ) . ')<br/>';
    }
    if ( $type == 2 ) {
        echo '<strong>(' . esc_html__( 'Custom / URL Referral', 'woo-coupon-usage' ) . ')</strong><br/>';
    }
    $ispaid = "";
    if ( isset( $coupon_id ) && $coupon_id ) {
        echo 'Referral Code: <a href="' . esc_url( admin_url( 'post.php?post=' . esc_attr( $coupon_id ) . '&action=edit' ) ) . '" target="_blank" style="color: #07bbe3;">' . esc_html( $coupon_code ) . '</a>';
        $order_status = $order->get_status();
        if ( $order_status == 'processing' || $order_status == 'completed' ) {
            echo ' <span class="delete-coupon dashicons dashicons-no" style="color:rgb(92, 7, 7); cursor: pointer; font-size: 10px; height: 10px; width: 10px; vertical-align: middle;" data-order-id="' . esc_attr( $order_id ) . '" data-coupon-code="' . esc_attr( $coupon_code ) . '" title="Remove this coupon from order"></span>';
        }
        echo '<br/>';
    }
    $wcusage_affiliate_user = $coupon_info[1];
    if ( $wcusage_affiliate_user ) {
        $affiliate = get_user_by( 'ID', $wcusage_affiliate_user );
        $affiliate_username = $affiliate->user_login;
        echo esc_html__( 'Affiliate User', 'woo-coupon-usage' ) . ": <a href='" . esc_url( admin_url( "admin.php?page=wcusage_view_affiliate&user_id=" . $wcusage_affiliate_user ) ) . "' target='_blank' style='color: #07bbe3;'>" . esc_html( $affiliate_username ) . "</a><br/>";
    }
    if ( $order->get_status() != "refunded" && !wcusage_coupon_disable_commission( $coupon_id ) ) {
        echo esc_html__( 'Commission', 'woo-coupon-usage' ) . ": " . wp_kses_post( $getinfo['thecommission'] ) . wp_kses_post( $ispaid ) . "<br/>";
    }
    // Get discount amount
    $applied_coupons = $order->get_coupon_codes();
    if ( in_array( $coupon_code, $applied_coupons ) ) {
        $discount_amount = 0;
        foreach ( $order->get_items( 'coupon' ) as $item_id => $item ) {
            if ( $item->get_code() === $coupon_code ) {
                $discount_amount = $item->get_discount();
                break;
            }
        }
        if ( $discount_amount > 0 ) {
            echo esc_html__( 'Discount', 'woo-coupon-usage' ) . ": " . wp_kses_post( wcusage_format_price( $discount_amount ) ) . "<br/>";
        } else {
            echo esc_html__( 'No Discount (Tracking Only)', 'woo-coupon-usage' ) . "<br/>";
        }
    }
    echo "<a href='" . esc_url( $getinfo['uniqueurl'] ) . "' target='_blank' style='color: #07bbe3;' title='" . esc_html__( 'View the affiliate dashboard for this affiliate coupon.', 'woo-coupon-usage' ) . "'>" . esc_html__( 'View Dashboard', 'woo-coupon-usage' ) . "</a>";
    echo "</p>";
    if ( wcu_fs()->can_use_premium_code() ) {
        $wcusage_field_mla_enable = wcusage_get_setting_value( 'wcusage_field_mla_enable', '0' );
        if ( $wcusage_field_mla_enable && !wcusage_coupon_disable_commission( $coupon_id ) ) {
            $get_parents = get_user_meta( $getinfo['theuserid'], 'wcu_ml_affiliate_parents', true );
            if ( !empty( $get_parents ) && is_array( $get_parents ) ) {
                // Try to read stored MLA commission from order meta (persisted at order time)
                $order_obj = wc_get_order( $order_id );
                $stored_mla_raw = ( $order_obj ? $order_obj->get_meta( 'wcu_mla_commission', true ) : '' );
                $stored_mla = ( is_string( $stored_mla_raw ) ? json_decode( $stored_mla_raw, true ) : $stored_mla_raw );
                $needs_mla_meta_save = false;
                echo "<p><strong>MLA Commission:</strong>";
                foreach ( $get_parents as $key => $parent_id ) {
                    $parent_user_info = get_user_by( 'ID', $parent_id );
                    $parent_user_name = ( $parent_user_info ? $parent_user_info->user_login : '#' . $parent_id );
                    $parent_user_id = ( $parent_user_info ? $parent_user_info->ID : $parent_id );
                    // Use stored commission if available, otherwise recalculate and flag for saving
                    if ( is_array( $stored_mla ) && isset( $stored_mla[$key]['commission'] ) ) {
                        $parent_commission = (float) $stored_mla[$key]['commission'];
                    } else {
                        $coupon_info = wcusage_get_coupon_info( $coupon_code );
                        $coupon_id = $coupon_info[2];
                        $parent_commission = wcusage_mla_get_commission_from_tier(
                            $getinfo['thecommissionnum'],
                            $key,
                            1,
                            $order_id,
                            $coupon_code,
                            0,
                            $parent_user_id
                        );
                        // Collect recalculated data so we can persist it
                        $tier_rates = ( function_exists( 'wcusage_mla_get_tier_rates' ) ? wcusage_mla_get_tier_rates( $key, $parent_user_id ) : array() );
                        if ( !is_array( $stored_mla ) ) {
                            $stored_mla = array();
                        }
                        $stored_mla[$key] = array(
                            'parent_id'  => (int) $parent_user_id,
                            'commission' => round( (float) $parent_commission, 2 ),
                            'rates'      => $tier_rates,
                        );
                        $needs_mla_meta_save = true;
                    }
                    echo "<br/>(" . esc_html( $key ) . ") <a href='" . esc_url( admin_url( "admin.php?page=wcusage_view_affiliate&user_id=" . $parent_user_id ) ) . "' target='_blank' style='color: #07bbe3;'>" . esc_html( $parent_user_name ) . "</a>: " . wp_kses_post( wcusage_format_price( esc_html( $parent_commission ) ) );
                }
                echo "</p>";
                // Persist recalculated MLA data for old orders so it won't recalculate again
                if ( $needs_mla_meta_save && $order_obj && !empty( $stored_mla ) ) {
                    $order_obj->update_meta_data( 'wcu_mla_commission', json_encode( $stored_mla ) );
                    $order_obj->save_meta_data();
                }
            }
        }
    }
}

/**
 * Save the custom meta box data
 *
 * @param int $post_id The ID of the post being saved.
 */
function wcusage_save_postdata(  $post_id  ) {
    if ( array_key_exists( 'wcusage_field', $_POST ) ) {
        update_post_meta( $post_id, '_wcusage_meta_key', sanitize_text_field( $_POST['wcusage_field'] ) );
    }
}

add_action( 'save_post', 'wcusage_save_postdata' );
/**
 * Save the custom meta box data
 *
 * @param int $post_id The ID of the post being saved.
 */
function wcusage_wcusage_referrer_coupon_meta_box_save(  $post_id  ) {
    if ( !isset( $_POST['wcusage_referrer_coupon_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcusage_referrer_coupon_nonce'] ) ), basename( __FILE__ ) ) ) {
        return;
    }
    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    $coupon_code = '';
    if ( isset( $_POST['wcusage_referrer_coupon'] ) ) {
        $coupon_code = $_POST['wcusage_referrer_coupon'];
    }
    $coupon_id = wc_get_coupon_id_by_code( $coupon_code );
    $wcusage_referrer_coupon = ( isset( $_POST['wcusage_referrer_coupon'] ) ? sanitize_text_field( $_POST['wcusage_referrer_coupon'] ) : '' );
    $wcusage_referrer_coupon_old = wcusage_order_meta( $post_id, 'wcusage_referrer_coupon', true );
    if ( $coupon_code && !$coupon_id ) {
        echo '<div class="error"><p>' . esc_html__( 'The coupon code you entered does not exist.', 'woo-coupon-usage' ) . '</p></div>';
        return;
    }
    $meta_data = [];
    $meta_data['wcusage_referrer_coupon'] = $wcusage_referrer_coupon;
    if ( !$wcusage_referrer_coupon_old && $wcusage_referrer_coupon ) {
        $meta_data['wcusage_referrer_refresh'] = 1;
    }
    if ( $wcusage_referrer_coupon_old && !$wcusage_referrer_coupon ) {
        $meta_data['wcusage_referrer_refresh'] = 1;
        $meta_data['wcusage_referrer_refresh_prev'] = $wcusage_referrer_coupon_old;
    }
    if ( $wcusage_referrer_coupon_old && $wcusage_referrer_coupon && $wcusage_referrer_coupon_old != $wcusage_referrer_coupon ) {
        $meta_data['wcusage_referrer_refresh'] = 1;
        $meta_data['wcusage_referrer_refresh_prev'] = $wcusage_referrer_coupon_old;
    }
    $wcusage_field_enable_coupon_all_stats_meta = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
    if ( $wcusage_field_enable_coupon_all_stats_meta ) {
        $order = wc_get_order( $post_id );
        if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
            $coupons_array = $order->get_coupon_codes();
        } else {
            $coupons_array = $order->get_used_coupons();
        }
        if ( $wcusage_referrer_coupon_old != $wcusage_referrer_coupon ) {
            if ( $wcusage_referrer_coupon ) {
                do_action(
                    'wcusage_hook_update_all_stats_single',
                    $wcusage_referrer_coupon,
                    $post_id,
                    1,
                    1
                );
            } else {
                foreach ( $coupons_array as $this_coupon_code ) {
                    do_action(
                        'wcusage_hook_update_all_stats_single',
                        $this_coupon_code,
                        $post_id,
                        1,
                        1
                    );
                }
            }
            if ( $wcusage_referrer_coupon_old ) {
                do_action(
                    'wcusage_hook_update_all_stats_single',
                    $wcusage_referrer_coupon_old,
                    $post_id,
                    0,
                    1
                );
            } else {
                foreach ( $coupons_array as $this_coupon_code ) {
                    do_action(
                        'wcusage_hook_update_all_stats_single',
                        $this_coupon_code,
                        $post_id,
                        0,
                        1
                    );
                }
            }
        }
    }
    if ( !empty( $meta_data ) ) {
        wcusage_update_order_meta_bulk( $post_id, $meta_data );
    }
}

add_action( 'woocommerce_process_shop_order_meta', 'wcusage_wcusage_referrer_coupon_meta_box_save' );
/*
 * Add a link to add a coupon below the coupons in the order edit page
 */
add_action(
    'wcusage_hook_order_box_after_custom_referrer',
    'add_coupon_link_below_coupons',
    10,
    1
);
function add_coupon_link_below_coupons(  $order_id  ) {
    $order = wc_get_order( $order_id );
    $order_status = $order->get_status();
    $wcusage_referrer_coupon = wcusage_order_meta( $order_id, 'wcusage_referrer_coupon', true );
    ?>

    <?php 
    if ( ($order_status == 'completed' || $order_status == 'processing') && !$wcusage_referrer_coupon ) {
        ?>
    <div>
        <a href="#" class="add-coupon-link" style="font-size: 10px; text-decoration: none;"><?php 
        echo esc_html__( 'Add a referrer coupon to this order', 'woo-coupon-usage' );
        ?> <i class="fa fa-plus" style="font-size: 10px;"></i></a>
        <div class="add-coupon-form" style="display: none; margin-top: 10px; border: 1px solid #ccc; padding: 10px 10px 12px 10px; background-color: #f9f9f9;">
            <p style="font-size: 10px; margin: 0 0 7px 0;"><?php 
        echo sprintf( esc_html__( 'Add a coupon to this order for tracking. Since the order is already %s, the coupon will be added with a zero discount.', 'woo-coupon-usage' ), esc_html( $order_status ) );
        ?>
            <?php 
        if ( $order_status == 'completed' ) {
            echo esc_html__( 'Unpaid commission will also NOT be automatically granted to this affiliate coupon and should be done manually.', 'woo-coupon-usage' );
        }
        ?></p>
            <input type="text" id="add_coupon_code" name="add_coupon_code" placeholder="Coupon code" style="width: 150px;" />
            <button type="button" class="button add-coupon-to-order" style="margin-left: 10px;"><?php 
        echo esc_html__( 'Add', 'woo-coupon-usage' );
        ?></button>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('.add-coupon-link').on('click', function(e) {
                e.preventDefault();
                $('.add-coupon-form').toggle();
            });

            $('.add-coupon-to-order').on('click', function() {

                // Change button to spinner
                $(this).html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                var couponCode = $('#add_coupon_code').val();
                if (!couponCode) {
                    alert('Please enter a coupon code.');
                    return;
                }

                $.ajax({
                    url: '<?php 
        echo esc_url( admin_url( 'admin-ajax.php' ) );
        ?>',
                    type: 'POST',
                    data: {
                        action: 'add_coupon_to_order',
                        order_id: '<?php 
        echo esc_js( $order->get_id() );
        ?>',
                        coupon_code: couponCode,
                        security: '<?php 
        echo esc_js( wp_create_nonce( 'add_coupon_nonce' ) );
        ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data.message);
                        }
                    }
                });
            });

            $('.delete-coupon').on('click', function() {
                var couponCode = $(this).data('coupon-code');
                var orderId = $(this).data('order-id');
                
                if (confirm('<?php 
        echo esc_js( esc_html__( 'Are you sure you want to remove this coupon from the order?', 'woo-coupon-usage' ) );
        ?> - <?php 
        echo esc_js( esc_html__( 'This will NOT affect the discount that has already been applied unless you recalculate the order.', 'woo-coupon-usage' ) );
        if ( $order_status == 'completed' && wcu_fs()->can_use_premium_code() ) {
            ?> <?php 
            echo esc_js( esc_html__( 'This will only affect the affiliate dashboard statistics. Any unpaid commission already granted will NOT be deducted.', 'woo-coupon-usage' ) );
        }
        ?>')) {
                    $.ajax({
                        url: '<?php 
        echo esc_url( admin_url( 'admin-ajax.php' ) );
        ?>',
                        type: 'POST',
                        data: {
                            action: 'remove_coupon_from_order',
                            order_id: orderId,
                            coupon_code: couponCode,
                            security: '<?php 
        echo esc_js( wp_create_nonce( 'remove_coupon_nonce' ) );
        ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        },
                        error: function() {
                            alert('Error removing coupon.');
                        }
                    });
                }
            });
        });
    </script>
    <?php 
    }
    ?>
    <?php 
}

add_action( 'wp_ajax_add_coupon_to_order', 'handle_add_coupon_to_order' );
function handle_add_coupon_to_order() {
    check_ajax_referer( 'add_coupon_nonce', 'security' );
    $order_id = ( isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0 );
    $coupon_code = ( isset( $_POST['coupon_code'] ) ? sanitize_text_field( $_POST['coupon_code'] ) : '' );
    if ( !$order_id || !$coupon_code ) {
        wp_send_json_error( [
            'message' => 'Invalid order ID or coupon code.',
        ] );
    }
    $order = wc_get_order( $order_id );
    if ( !$order ) {
        wp_send_json_error( [
            'message' => 'Order not found.',
        ] );
    }
    $coupon = new WC_Coupon($coupon_code);
    if ( !$coupon->get_id() ) {
        wp_send_json_error( [
            'message' => 'Coupon code does not exist.',
        ] );
    }
    $existing_coupons = $order->get_coupon_codes();
    if ( in_array( $coupon_code, $existing_coupons ) ) {
        wp_send_json_error( [
            'message' => 'Coupon already added to this order.',
        ] );
    }
    $coupon_item = new WC_Order_Item_Coupon();
    $coupon_item->set_props( [
        'code'         => $coupon_code,
        'discount'     => 0,
        'discount_tax' => 0,
    ] );
    $order->add_item( $coupon_item );
    $order->save();
    do_action(
        'wcusage_hook_update_all_stats_single',
        $coupon_code,
        $order_id,
        1,
        1
    );
    $coupon->increase_usage_count();
    wp_send_json_success( [
        'message' => 'Coupon added to order.',
    ] );
}

add_action( 'wp_ajax_remove_coupon_from_order', 'handle_remove_coupon_from_order' );
function handle_remove_coupon_from_order() {
    check_ajax_referer( 'remove_coupon_nonce', 'security' );
    $order_id = ( isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0 );
    $coupon_code = ( isset( $_POST['coupon_code'] ) ? sanitize_text_field( $_POST['coupon_code'] ) : '' );
    if ( !$order_id || !$coupon_code ) {
        wp_send_json_error( [
            'message' => 'Invalid order ID or coupon code.',
        ] );
    }
    $order = wc_get_order( $order_id );
    if ( !$order ) {
        wp_send_json_error( [
            'message' => 'Order not found.',
        ] );
    }
    $order_status = $order->get_status();
    if ( $order_status != 'processing' && $order_status != 'completed' ) {
        wp_send_json_error( [
            'message' => 'Coupon can only be removed from processing or completed orders.',
        ] );
    }
    $existing_coupons = $order->get_items( 'coupon' );
    $coupon_found = false;
    foreach ( $existing_coupons as $item_id => $item ) {
        if ( strtolower( $item->get_code() ) === strtolower( $coupon_code ) ) {
            do_action(
                'wcusage_hook_update_all_stats_single',
                $coupon_code,
                $order_id,
                0,
                1
            );
            $order->remove_item( $item_id );
            $coupon_found = true;
            break;
        }
    }
    if ( !$coupon_found ) {
        wp_send_json_error( [
            'message' => 'Coupon not found in order.',
        ] );
    }
    $order->save();
    wp_send_json_success( [
        'message' => 'Coupon removed from order.',
    ] );
}
