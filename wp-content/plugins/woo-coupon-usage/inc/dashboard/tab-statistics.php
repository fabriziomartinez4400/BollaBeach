<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Displays the statistics tab content on affiliate dashboard
 *
 * @param int $postid
 * @param string $coupon_code
 * @param int $combined_commission
 *
 * @return mixed
 *
 */
if ( !function_exists( 'wcusage_tab_statistics' ) ) {
    function wcusage_tab_statistics(
        $postid,
        $coupon_code,
        $combined_commission,
        $force_refresh_stats = ""
    ) {
        $options = get_option( 'wcusage_options' );
        $couponinfo = wcusage_get_coupon_info_by_id( $postid );
        $couponuser = $couponinfo[1];
        $currentuserid = get_current_user_id();
        $wcusage_page_load = wcusage_get_setting_value( 'wcusage_field_page_load', '' );
        $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', 1 );
        $discount_type_original = get_post_meta( $postid, 'discount_type', true );
        $option_text = wcusage_get_setting_value( 'wcusage_field_text', '' );
        $wcusage_show_commission = wcusage_get_setting_value( 'wcusage_field_show_commission', '' );
        $wcusage_show_tabs = wcusage_get_setting_value( 'wcusage_field_show_tabs', '1' );
        $wcusage_field_which_toggle = wcusage_get_setting_value( 'wcusage_field_which_toggle', '1' );
        $wcusage_show_graphs = wcusage_get_setting_value( 'wcusage_field_show_graphs', '1' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        $save_order_commission_meta = wcusage_get_setting_value( 'wcusage_field_enable_order_commission_meta', '1' );
        $save_coupon_all_stats_meta = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_meta', '1' );
        $current_commission_message = get_post_meta( $postid, 'wcu_commission_message', true );
        $hide_commission = wcusage_coupon_disable_commission( $postid );
        $update_stats = "";
        if ( $force_refresh_stats ) {
            delete_post_meta( $postid, 'wcusage_monthly_summary_data_orders' );
            $update_stats = wcusage_update_all_stats( $coupon_code, 1 );
        }
        $is_mla_parent = "";
        if ( function_exists( 'wcusage_network_check_sub_affiliate' ) ) {
            $is_mla_parent = wcusage_network_check_sub_affiliate( $currentuserid, $couponuser );
        }
        $thismonthorders = "";
        $pastmonthorders = "";
        $pastoldmonthorders = "";
        $sections = array(
            'section_couponinfo'        => esc_html__( 'Coupon Info', 'woo-coupon-usage' ),
            'section_commissionamounts' => esc_html__( 'Commission Earnings', 'woo-coupon-usage' ),
            'section_commissiongraphs'  => esc_html__( 'Commission Graph', 'woo-coupon-usage' ),
            'section_latestreferrals'   => esc_html__( 'Latest Referrals', 'woo-coupon-usage' ),
            'section_commissionpayouts' => esc_html__( 'Commission Payouts', 'woo-coupon-usage' ),
        );
        if ( $is_mla_parent || !$couponuser || $couponuser == $currentuserid || wcusage_check_admin_access() ) {
            $amount = get_post_meta( $postid, 'coupon_amount', true );
            $section_order = ( isset( $options['wcusage_statistics_layout'] ) ? $options['wcusage_statistics_layout'] : '' );
            if ( $section_order ) {
                $sections_order = explode( ',', $section_order );
            } else {
                $sections_order = array_keys( $sections );
            }
            ?>

          <?php 
            if ( $wcusage_hide_all_time ) {
                ?>
              <script>
                  jQuery(document).ready(function () {
                      jQuery('.wcusage-show-last-30').show();
                      jQuery('.wcusage-show-last-7').delay(1).fadeOut('fast');
                      jQuery('.wcusage-show-last-all').delay(1).fadeOut('fast');
                      jQuery('#wcusage-last-days30').css("color", "#333");
                  });
              </script>
          <?php 
            } else {
                ?>
              <script>
                  jQuery(document).ready(function () {
                      jQuery('.wcusage-show-last-all').show();
                      jQuery('.wcusage-show-last-7').delay(1).fadeOut('fast');
                      jQuery('.wcusage-show-last-30').delay(1).fadeOut('fast');
                      jQuery('.wcusage-show-last-all-30').show();
                  });
              </script>
          <?php 
            }
            ?>

          <br/>

          <?php 
            // Get statistics data
            if ( !$wcusage_field_which_toggle ) {
                $date7 = date( 'Y-m-d', strtotime( '-7 days' ) );
                $date14 = date( 'Y-m-d', strtotime( '-14 days' ) );
                $this7orders = wcusage_wh_getOrderbyCouponCode(
                    $coupon_code,
                    $date7,
                    date( "Y-m-d" ),
                    '',
                    1
                );
                $past14orders = wcusage_wh_getOrderbyCouponCode(
                    $coupon_code,
                    $date14,
                    $date7,
                    '',
                    1
                );
                $date30 = date( 'Y-m-d', strtotime( '-30 days' ) );
                $date60 = date( 'Y-m-d', strtotime( '-60 days' ) );
                $this30orders = wcusage_wh_getOrderbyCouponCode(
                    $coupon_code,
                    $date30,
                    date( "Y-m-d" ),
                    '',
                    1
                );
                $past60orders = wcusage_wh_getOrderbyCouponCode(
                    $coupon_code,
                    $date60,
                    $date30,
                    '',
                    1
                );
                // Text
                $this30text = esc_html__( "Last 30 Days", "woo-coupon-usage" );
                $this7text = esc_html__( "Last 7 Days", "woo-coupon-usage" );
            } else {
                // Current Month
                $date1month = date( 'Y-m-01' );
                // Last Month
                $date2month = date( 'Y-m-d', strtotime( "first day of last month" ) );
                $date2monthend = date( 'Y-m-d', strtotime( "last day of last month" ) );
                // Month Before
                $date3month = date( 'Y-m-d', strtotime( "first day of -2 month" ) );
                $date3monthend = date( 'Y-m-d', strtotime( "last day of -2 month" ) );
                // Get Monthly Statistics
                $wcusage_monthly_summary_data_orders = get_post_meta( $postid, 'wcusage_monthly_summary_data_orders', true );
                if ( !$wcusage_monthly_summary_data_orders ) {
                    $wcusage_monthly_summary_data_orders = array();
                }
                // Delete old months that are not needed
                if ( is_array( $wcusage_monthly_summary_data_orders ) && count( $wcusage_monthly_summary_data_orders ) > 12 ) {
                    foreach ( $wcusage_monthly_summary_data_orders as $key => $value ) {
                        if ( $key != strtotime( $date1month ) && $key != strtotime( $date2month ) && $key != strtotime( $date3month ) ) {
                            $wcusage_monthly_summary_data_orders[strtotime( $key )] = "";
                        }
                    }
                }
                // This Month
                if ( empty( $wcusage_monthly_summary_data_orders[strtotime( $date1month )] ) ) {
                    $thismonthorders = wcusage_wh_getOrderbyCouponCode(
                        $coupon_code,
                        $date1month,
                        date( "Y-m-d" ),
                        '',
                        1,
                        0
                    );
                    $wcusage_monthly_summary_data_orders[strtotime( $date1month )] = $thismonthorders;
                    //echo "a1: " . $date1month . "<br/>";
                } else {
                    $monthly_summary_data1 = $wcusage_monthly_summary_data_orders[strtotime( $date1month )];
                    $thismonthorders = $monthly_summary_data1;
                    //echo "a2: " . $date1month . "<br/>";
                }
                // Last Month
                if ( empty( $wcusage_monthly_summary_data_orders[strtotime( $date2month )] ) ) {
                    $pastmonthorders = wcusage_wh_getOrderbyCouponCode(
                        $coupon_code,
                        $date2month,
                        $date2monthend,
                        '',
                        1,
                        0
                    );
                    $wcusage_monthly_summary_data_orders[strtotime( $date2month )] = $pastmonthorders;
                    //echo "b1: " . $date2month . "<br/>";
                } else {
                    $monthly_summary_data2 = $wcusage_monthly_summary_data_orders[strtotime( $date2month )];
                    $pastmonthorders = $monthly_summary_data2;
                    //echo "b2: " . $date2month . "<br/>";
                }
                // 2 Months Ago
                if ( empty( $wcusage_monthly_summary_data_orders[strtotime( $date3month )] ) ) {
                    $pastoldmonthorders = wcusage_wh_getOrderbyCouponCode(
                        $coupon_code,
                        $date3month,
                        $date3monthend,
                        '',
                        1,
                        0
                    );
                    $wcusage_monthly_summary_data_orders[strtotime( $date3month )] = $pastoldmonthorders;
                    //echo "c1: " . $date3month . "<br/>";
                } else {
                    $monthly_summary_data3 = $wcusage_monthly_summary_data_orders[strtotime( $date3month )];
                    $pastoldmonthorders = $monthly_summary_data3;
                    //echo "c2: " . $date3month . "<br/>";
                }
                $this7orders = $pastmonthorders;
                $past14orders = $pastoldmonthorders;
                $this30orders = $thismonthorders;
                $past60orders = $pastmonthorders;
                // Text
                $this30text = esc_html__( "This Month", "woo-coupon-usage" );
                $this7text = esc_html__( "Last Month", "woo-coupon-usage" );
            }
            if ( !$wcusage_hide_all_time ) {
                $wcu_alltime_stats = get_post_meta( $postid, 'wcu_alltime_stats', true );
                if ( !isset( $wcu_alltime_stats ) || $wcu_alltime_stats == "" ) {
                    if ( !$update_stats ) {
                        $fullorders = wcusage_wh_getOrderbyCouponCode(
                            $coupon_code,
                            "",
                            date( "Y-m-d" ),
                            '',
                            1,
                            1
                        );
                    } else {
                        $fullorders = $update_stats;
                    }
                    if ( !empty( $fullorders ) ) {
                        // Check if $fullorders is not empty
                        $fullorders = array_reverse( $fullorders );
                        $usage = $fullorders['total_count'];
                    }
                } else {
                    $fullorders = "";
                    $wcu_alltime_stats = get_post_meta( $postid, 'wcu_alltime_stats', true );
                    $usage = $wcu_alltime_stats['total_count'];
                }
            } else {
                global $woocommerce;
                $c = new WC_Coupon($coupon_code);
                $usage = $c->get_usage_count();
            }
            // Loop though sections
            echo '<div class="wcusage-tab-container">';
            $first_switch = true;
            foreach ( $sections_order as $section ) {
                echo '<div class="wcusage-tab-section">';
                switch ( $section ) {
                    case 'section_couponinfo':
                        // Section 1: Coupon Info
                        if ( !$first_switch ) {
                            $margintop = "margin-top: 40px";
                        } else {
                            $margintop = "";
                        }
                        if ( wcusage_get_setting_value( 'wcusage_field_statistics_couponinfo', '1' ) ) {
                            echo '<div style="width: 100%; clear: both;"></div>';
                            echo '<p class="wcusage-stats-title" style="' . esc_attr( $margintop ) . '">' . esc_html__( "Coupon Info", "woo-coupon-usage" ) . '</p>';
                            // Text
                            if ( $option_text ) {
                                echo '<p><span class="wcusage-info-box-title">' . esc_html( $option_text ) . '</p>';
                            }
                            // Usage
                            echo '<div class="wcusage-info-box wcusage-info-box-usage">';
                            echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Total Usage", "woo-coupon-usage" ) . ':</span> <span id="wcu-total-usage-number">' . wp_kses_post( $usage ) . '</span></p>';
                            echo '</div>';
                            // Type
                            echo '<div class="wcusage-info-box wcusage-info-box-discount">';
                            $amount = get_post_meta( $postid, 'coupon_amount', true );
                            if ( $amount ) {
                                if ( $discount_type_original == "percent" ) {
                                    echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . wp_kses_post( $amount ) . '%</span></p>';
                                } elseif ( $discount_type_original == "fixed_cart" ) {
                                    echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . wp_kses_post( wcusage_format_price( $amount ) ) . '</span></p>';
                                } else {
                                    if ( $discount_type_original == "recurring_percent" ) {
                                        echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . wp_kses_post( $amount ) . '%</span></p>';
                                    } elseif ( $discount_type_original == "percent_per_product" ) {
                                        echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . wp_kses_post( $amount ) . '%</span></p>';
                                    } else {
                                        echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . wp_kses_post( wcusage_format_price( $amount ) ) . '</span></p>';
                                    }
                                }
                            } else {
                                // If free shipping enabled
                                $free_shipping = get_post_meta( $postid, 'free_shipping', true );
                                if ( $free_shipping == "yes" ) {
                                    echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . esc_html__( "Free Shipping", "woo-coupon-usage" ) . '</span></p>';
                                } else {
                                    echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Discount", "woo-coupon-usage" ) . ':</span> <span id="wcu-the-discount-type">' . esc_html__( "No Discount", "woo-coupon-usage" ) . '</span></p>';
                                }
                            }
                            echo '</div>';
                            $multitypes = 0;
                            // Commission
                            if ( $wcusage_show_commission && !$hide_commission ) {
                                // Commission Type
                                echo '<div class="wcusage-info-box wcusage-info-box-percent">';
                                echo '<p>';
                                $commissionthisstyles = "";
                                if ( strlen( $combined_commission ) >= 19 ) {
                                    $commissionthisstyles = 'font-size: 18px; line-height: 18px;';
                                }
                                echo '<span class="wcusage-info-box-title">' . esc_html__( "Commission", "woo-coupon-usage" ) . ":</span> ";
                                echo '<span class="wcu-total-commission-text" style="' . esc_attr( $commissionthisstyles ) . '">' . wp_kses_post( $combined_commission ) . "</span> ";
                                echo "</p>";
                                echo '</div>';
                            }
                        }
                        break;
                    case 'section_commissionamounts':
                        // Section 2: Commission Amounts
                        if ( !$first_switch ) {
                            $margintop = "margin-top: 40px";
                        } else {
                            $margintop = "";
                        }
                        echo '<div style="width: 100%; clear: both;"></div>';
                        if ( wcusage_get_setting_value( 'wcusage_field_statistics_commissionearnings', '1' ) ) {
                            echo '<div class="wcusage-sales-stats-toggles" style="' . esc_attr( $margintop ) . '">';
                            echo '<p class="wcusage-last-days" style="margin-top: 0; margin-left: 5px; font-weight: bold;">';
                            if ( !$wcusage_hide_all_time ) {
                                echo '<a href="javascript:void(0);" id="wcusage-last-days-all">' . esc_html__( "All-time", "woo-coupon-usage" ) . '</a> <span style="color: #f3f3f3;">|</span> ';
                            }
                            echo '<a href="javascript:void(0);" id="wcusage-last-days30" style="color: #a6a6a6;">' . esc_html( $this30text ) . '</a> <span style="color: #f3f3f3;">|</span> <a href="javascript:void(0);" id="wcusage-last-days7" style="color: #a6a6a6;">' . esc_html( $this7text ) . '</a>
                          </p>';
                            echo '</div>';
                            echo '<div class="wcusage-sales-stats">';
                            if ( !$wcusage_hide_all_time ) {
                                echo '<div class="wcusage-show-last-all">';
                                do_action(
                                    'wcusage_hook_get_main_info_boxes',
                                    $fullorders,
                                    '',
                                    $combined_commission,
                                    $postid
                                );
                                echo '</div>';
                            }
                            if ( !$wcusage_hide_all_time ) {
                                echo '<div class="wcusage-show-last-30" style="display: none;">';
                            } else {
                                echo '<div class="wcusage-show-last-30">';
                            }
                            do_action(
                                'wcusage_hook_get_main_info_boxes',
                                $this30orders,
                                $past60orders,
                                $combined_commission,
                                $postid
                            );
                            echo '</div>';
                            echo '<div class="wcusage-show-last-7" style="display: none;">';
                            do_action(
                                'wcusage_hook_get_main_info_boxes',
                                $this7orders,
                                $past14orders,
                                $combined_commission,
                                $postid
                            );
                            echo '</div>';
                            echo '</div>';
                        }
                        break;
                    case 'section_commissiongraphs':
                        // Section 3: Graphs
                        if ( !$first_switch ) {
                            $margintop = "margin-top: 40px";
                        } else {
                            $margintop = "";
                        }
                        echo '<div ' . (( $wcusage_field_load_ajax ? 'class="wcu-loading-hide" style="visibility: hidden; height: 0;"' : '' )) . '>';
                        echo '</div>';
                        break;
                    case 'section_latestreferrals':
                        // Section 4: Latest Referrals
                        if ( !$first_switch ) {
                            $margintop = "margin-top: 40px;";
                        } else {
                            $margintop = "";
                        }
                        if ( wcusage_get_setting_value( 'wcusage_field_statistics_latest', '1' ) ) {
                            echo '<div style="width: 100%; clear: both;"></div>';
                            echo '<p class="wcusage-stats-title" style="' . esc_attr( $margintop ) . ' margin-bottom: 15px;">' . esc_html__( "Latest Referrals", "woo-coupon-usage" ) . ' <a href="javascript:void(0);" class="wcusage-orders-link" style="margin-left: 5px; font-size: 12px; font-weight: bold;">' . esc_html__( "View More", "woo-coupon-usage" ) . ' <i class="fa-solid fa-arrow-right"></i></a></p>';
                            echo '<script>
                        jQuery(document).ready(function($) {
                          $(".wcusage-orders-link").click(function() {
                            $("#tab-page-orders").click();
                            window.scrollTo(0, 0);
                          });
                        });
                        </script>';
                            echo '<style>.wcu-statistics-orders .wcuOrdersStatuses { display: none; }</style>';
                            echo '<div class="wcu-statistics-orders" div style="margin: 0 auto; width: calc(100% - 10px);">';
                            do_action(
                                'wcusage_hook_tab_latest_orders',
                                $postid,
                                $coupon_code,
                                "",
                                date( "Y-m-d" ),
                                false,
                                "",
                                5,
                                true,
                                false
                            );
                            echo '</div>';
                        }
                        break;
                    case 'section_commissionpayouts':
                        // Section 5: Latest Payouts
                        if ( !$first_switch ) {
                            $margintop = "margin-top: 40px;";
                        } else {
                            $margintop = "";
                        }
                        if ( wcu_fs()->can_use_premium_code__premium_only() && wcusage_get_setting_value( 'wcusage_field_statistics_commissionpayouts', '1' ) && wcusage_get_setting_value( 'wcusage_field_payouts_enable', '1' ) && wcusage_get_setting_value( 'wcusage_field_tracking_enable', '1' ) ) {
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'wcusage_payouts';
                            $unpaid_commission = $couponinfo[2];
                            $pendingpayments = get_post_meta( $postid, 'wcu_text_pending_payment_commission', true );
                            $result2 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE couponid = %d AND status = 'paid' ORDER BY id DESC LIMIT 50", $postid ) );
                            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
                            $paid_amount = 0;
                            foreach ( $result2 as $row2 ) {
                                $paid_amount += $row2->amount;
                            }
                            echo '<div style="width: 100%; clear: both;"></div>';
                            echo '<div style="' . esc_attr( $margintop ) . '">';
                            echo '<p class="wcusage-stats-title">' . esc_html__( "Commission Payouts", "woo-coupon-usage" ) . ' <a href="javascript:void(0);" class="wcusage-payouts-link" style="margin-left: 5px; font-size: 12px; font-weight: bold;">' . esc_html__( "View More", "woo-coupon-usage" ) . ' <i class="fa-solid fa-arrow-right"></i></a></p>';
                            echo '<script>
                        jQuery(document).ready(function($) {
                          $(".wcusage-payouts-link").click(function() {
                            $("#tab-page-payouts").click();
                            window.scrollTo(0, 0);
                          });
                        });
                        </script>';
                            echo '<div class="wcusage-sales-stats">';
                            echo '<div class="wcusage-info-box wcusage-info-box-dollar2"><p><span class="wcusage-info-box-title">' . esc_html__( "Unpaid Commission:", "woo-coupon-usage" ) . '</span> <span id="wcu-the-discount-type">' . wp_kses_post( wcusage_format_price( number_format(
                                (float) $unpaid_commission,
                                2,
                                '.',
                                ''
                            ) ) ) . '</span></p></div>';
                            echo '<div class="wcusage-info-box wcusage-info-box-dollar3"><p><span class="wcusage-info-box-title">' . esc_html__( "Pending Payments:", "woo-coupon-usage" ) . '</span> <span id="wcu-the-discount-type">' . wp_kses_post( wcusage_format_price( number_format(
                                (float) $pendingpayments,
                                2,
                                '.',
                                ''
                            ) ) ) . '</span></p></div>';
                            echo '<div class="wcusage-info-box wcusage-info-box-dollar4"><p><span class="wcusage-info-box-title">' . esc_html__( "Completed Payments:", "woo-coupon-usage" ) . '</span> <span id="wcu-the-discount-type">' . wp_kses_post( wcusage_format_price( $paid_amount ) ) . '</span></p></div>';
                            echo '<div style="width: 100%; clear: both;"></div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        break;
                }
                echo '</div>';
                $first_switch = false;
            }
            echo '</div>';
            ?>

            <div style="width: 100%; clear: both;"></div>

            <?php 
        }
    }

}
add_action(
    'wcusage_hook_tab_statistics',
    'wcusage_tab_statistics',
    10,
    4
);
/**
 * Get the info boxes for the statistics tab
 *
 * @param array $orders
 * @param array $past30orders
 * @param int $combined_commission
 *
 * @return mixed
 *
 */
if ( !function_exists( 'wcusage_get_main_info_boxes' ) ) {
    function wcusage_get_main_info_boxes(
        $orders,
        $past30orders,
        $combined_commission,
        $postid = ""
    ) {
        $hide_commission = wcusage_coupon_disable_commission( $postid );
        if ( !$orders ) {
            $orders = get_post_meta( $postid, 'wcu_alltime_stats', true );
        }
        if ( $orders && is_array( $orders ) ) {
            $options = get_option( 'wcusage_options' );
            $wcusage_show_commission = wcusage_get_setting_value( 'wcusage_field_show_commission', '1' );
            $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '' );
            if ( $wcusage_show_tax == 1 && isset( $orders['full_discount_tax'] ) ) {
                $full_discount = $orders['full_discount'] + $orders['full_discount_tax'];
            } else {
                $full_discount = $orders['full_discount'];
            }
            $full_discount = number_format(
                (float) $full_discount,
                2,
                '.',
                ''
            );
            $total_orders = $orders['total_orders'] - $orders['full_discount'];
            $total_orders = number_format(
                (float) $total_orders,
                2,
                '.',
                ''
            );
            $totalcommissionearned = number_format(
                (float) $orders['total_commission'],
                2,
                '.',
                ''
            );
            if ( $totalcommissionearned == "" || !$totalcommissionearned ) {
                $totalcommissionearned = 0;
            }
            // Past 30 Days
            if ( isset( $past30orders['full_discount'] ) ) {
                $full_discount_30 = $past30orders['full_discount'];
            } else {
                $full_discount_30 = 0;
            }
            if ( isset( $past30orders['full_discount_tax'] ) ) {
                $full_discount_30_tax = $past30orders['full_discount_tax'];
            } else {
                $full_discount_30_tax = 0;
            }
            if ( $wcusage_show_tax == 1 ) {
                $past30_full_discount = $full_discount_30 + $full_discount_30_tax;
            } else {
                $past30_full_discount = $full_discount_30;
            }
            $full_discount_diff = "";
            $past30_full_discount = number_format(
                (float) $past30_full_discount,
                2,
                '.',
                ''
            );
            if ( $past30orders ) {
                $full_discount_diff = wcusage_getPercentageChange( $full_discount, $past30_full_discount );
                $full_discount_diff = wcusage_show_pos_neg(
                    $full_discount_diff,
                    $past30_full_discount,
                    1,
                    2
                );
            }
            if ( isset( $past30orders['total_orders'] ) ) {
                $past30_total_orders = $past30orders['total_orders'] - $past30orders['full_discount'];
            } else {
                $past30_total_orders = 0;
            }
            $total_orders_diff = "";
            $past30_total_orders = number_format(
                (float) $past30_total_orders,
                2,
                '.',
                ''
            );
            if ( $past30orders ) {
                $total_orders_diff = wcusage_getPercentageChange( $total_orders, $past30_total_orders );
                $total_orders_diff = wcusage_show_pos_neg(
                    $total_orders_diff,
                    $past30_total_orders,
                    1,
                    2
                );
            }
            if ( isset( $past30orders['total_commission'] ) ) {
                $past30_totalcommissionearned = number_format(
                    (float) $past30orders['total_commission'],
                    2,
                    '.',
                    ''
                );
            } else {
                $past30_totalcommissionearned = 0;
            }
            $totalcommissionearned_diff = "";
            if ( $past30_totalcommissionearned == "" || !$past30_totalcommissionearned ) {
                $past30_totalcommissionearned = 0;
            }
            if ( $past30orders ) {
                $totalcommissionearned_diff = wcusage_getPercentageChangeNum( $totalcommissionearned, $past30_totalcommissionearned );
                $totalcommissionearned_diff = wcusage_show_pos_neg(
                    $totalcommissionearned_diff,
                    $past30_totalcommissionearned,
                    1,
                    0
                );
            }
            if ( wcusage_get_setting_value( 'wcusage_field_statistics_commissionearnings_total', '1' ) ) {
                echo '<div class="wcusage-info-box wcusage-info-box-sales">';
                echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Total Sales", "woo-coupon-usage" ) . ':</span> ' . wp_kses_post( wcusage_format_price( number_format(
                    (float) $total_orders,
                    2,
                    '.',
                    ''
                ) ) ) . wp_kses_post( $total_orders_diff ) . '</p>';
                echo '</div>';
            }
            if ( wcusage_get_setting_value( 'wcusage_field_statistics_commissionearnings_discounts', '1' ) ) {
                echo '<div class="wcusage-info-box wcusage-info-box-discounts">';
                echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Total Discounts", "woo-coupon-usage" ) . ':</span> ';
                echo '<span class="wcu-total-discount-saved">' . wp_kses_post( wcusage_format_price( number_format(
                    (float) $full_discount,
                    2,
                    '.',
                    ''
                ) ) ) . wp_kses_post( $full_discount_diff ) . '</span>';
                echo "</p>";
                echo '</div>';
            }
        }
        if ( wcusage_get_setting_value( 'wcusage_field_statistics_commissionearnings_commission', '1' ) ) {
            if ( $wcusage_show_commission && !$hide_commission ) {
                // Earned Commission
                echo '<div class="wcusage-info-box wcusage-info-box-dollar">';
                echo '<p><span class="wcusage-info-box-title">' . esc_html__( "Total Commission", "woo-coupon-usage" ) . ':</span> ' . wp_kses_post( wcusage_format_price( number_format(
                    (float) $totalcommissionearned,
                    2,
                    '.',
                    ''
                ) ) ) . wp_kses_post( $totalcommissionearned_diff ) . "</p>";
                echo '</div>';
            }
        }
    }

}
add_action(
    'wcusage_hook_get_main_info_boxes',
    'wcusage_get_main_info_boxes',
    10,
    4
);
/**
 * Gets statistics tab for shortcode page
 *
 * @param int $postid
 * @param string $coupon_code
 * @param int $combined_commission
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_dashboard_tab_content_statistics',
    'wcusage_dashboard_tab_content_statistics',
    10,
    5
);
if ( !function_exists( 'wcusage_dashboard_tab_content_statistics' ) ) {
    function wcusage_dashboard_tab_content_statistics(
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load,
        $force_refresh_stats
    ) {
        // *** GET SETTINGS *** /
        $options = get_option( 'wcusage_options' );
        $language = wcusage_get_language_code();
        $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', 1 );
        $wcusage_field_load_ajax_per_page = wcusage_get_setting_value( 'wcusage_field_load_ajax_per_page', 1 );
        if ( !$wcusage_field_load_ajax ) {
            $wcusage_field_load_ajax_per_page = 0;
        }
        $wcusage_show_tabs = wcusage_get_setting_value( 'wcusage_field_show_tabs', '1' );
        $wcusage_show_graphs = wcusage_get_setting_value( 'wcusage_field_show_graphs', '1' );
        $wcusage_justcoupon = wcusage_get_setting_value( 'wcusage_field_justcoupon', '1' );
        $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '0' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        $wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
        if ( wcusage_check_admin_access() ) {
            $wcusage_urlprivate = 0;
        }
        $wcusage_field_which_toggle = wcusage_get_setting_value( 'wcusage_field_which_toggle', '1' );
        $current_commission_message = get_post_meta( $postid, 'wcu_commission_message', true );
        $ajaxerrormessage = wcusage_ajax_error();
        // *** DISPLAY CONTENT *** //
        ?>

    <?php 
        if ( isset( $_POST['page-stats'] ) || !isset( $_POST['load-page'] ) || $wcusage_page_load == false ) {
            ?>

      <?php 
            if ( isset( $_POST['page-stats'] ) || !isset( $_POST['load-page'] ) ) {
                ?>
      <style>#wcu1 { display: block; }</style>
      <?php 
            }
            ?>

      <div id="wcu1" <?php 
            if ( $wcusage_show_tabs == '1' || $wcusage_show_tabs == '' ) {
                ?>class="wcutabcontent"<?php 
            }
            ?>>

      <?php 
            if ( $wcusage_field_load_ajax ) {
                ?>

        <script>
        /* Get & display data */
        jQuery(document).ready(function(){

          setTimeout(function(){
             jQuery('.stuck-loading-message').show();
          }, 45000);
          <?php 
                if ( $wcusage_field_load_ajax_per_page ) {
                    ?>
          wcusage_run_tab_page_stats();
          <?php 
                } else {
                    ?>
          jQuery( ".wcusage-refresh-data" ).on('click', wcusage_run_tab_page_stats);
          <?php 
                }
                ?>

        /* Load Page Statistics data & content */
        function wcusage_run_tab_page_stats() {

          var data = {
            action: 'wcusage_load_page_statistics',
            _ajax_nonce: '<?php 
                echo esc_html( wp_create_nonce( 'wcusage_dashboard_ajax_nonce' ) );
                ?>',
            postid: '<?php 
                echo esc_html( $postid );
                ?>',
            couponcode: '<?php 
                echo esc_html( $coupon_code );
                ?>',
            combinedcommission: '<?php 
                echo esc_html( wcusage_convert_symbols( $combined_commission ) );
                ?>',
            refresh: '<?php 
                echo esc_html( $force_refresh_stats );
                ?>',
            language: '<?php 
                echo esc_html( $language );
                ?>',
          };
              jQuery.ajax({
              type: 'POST',
              url: '<?php 
                echo esc_url( admin_url( 'admin-ajax.php' ) );
                ?>',
              data: data,
              success: function(data) {
                jQuery('.show_statistics').html(data);
                jQuery('.wcutablinks').css("opacity", "1");
                jQuery('.wcutablinks').css("pointer-events", "auto");
                <?php 
                if ( wcu_fs()->can_use_premium_code() ) {
                    ?>
                  <?php 
                    if ( $wcusage_show_graphs ) {
                        ?>
                  setTimeout( function() { wcusage_run_tab_page_stats_graph_update(); }, 500);
                  <?php 
                    }
                    ?>
                <?php 
                }
                ?>
                jQuery('.wcu-loading-stats-main').hide();
              },
              error: function(data){
                var errorMessage = 'AJAX error.';
                if (errorThrown) {
                  errorMessage += ', ' + errorThrown;
                }
                jQuery('.show_statistics').html('<?php 
                echo wp_kses_post( $ajaxerrormessage );
                ?><br/><br/>' + errorMessage); 
              }
          });

        }

        });
        </script>

        <div class="show_statistics"></div>

      <?php 
            } else {
                ?>

        <div class="show_statistics">
          <?php 
                do_action(
                    'wcusage_hook_tab_statistics',
                    $postid,
                    $coupon_code,
                    $combined_commission,
                    $force_refresh_stats
                );
                ?>
        </div>

      <?php 
            }
            ?>

        <div style="width: 100%; clear: both;"></div>

        <?php 
            if ( $wcusage_field_load_ajax ) {
                ?>

        <div class="wcu-loading-image wcu-loading-stats wcu-loading-stats-main">
          <div class="wcu-loading-loader"></div>
          <p class="wcu-loading-loader-text"><?php 
                echo esc_html__( "Loading statistics", "woo-coupon-usage" );
                ?>...</p>
          <?php 
                if ( $force_refresh_stats ) {
                    ?>
          <p class="wcu-loading-loader-subtext"><?php 
                    echo esc_html__( "First visit - this will take a little longer than usual.", "woo-coupon-usage" );
                    ?></p>
          <?php 
                }
                ?>
          <?php 
                if ( current_user_can( 'administrator' ) ) {
                    ?>
          <p class="stuck-loading-message wcu-loading-loader-subtext" style="display:none; margin-top: 12px;">
            <?php 
                    echo esc_html__( "Notice (admin only): Page constantly loading? Try refreshing the page.", "woo-coupon-usage" );
                    ?> <a href='https://couponaffiliates.com/docs/affiliate-dashboard-is-not-showing' target='_blank'><?php 
                    echo esc_html__( "Or click here", "woo-coupon-usage" );
                    ?></a>.
          </p>
          <?php 
                }
                ?>
        </div>

        <?php 
            }
            ?>

      </div>

      <div style="width: 100%; clear: both;"></div>

    <?php 
        }
        ?>

  <?php 
    }

}