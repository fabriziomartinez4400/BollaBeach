<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Displays the latest orders tab content on affiliate dashboard
 *
 * @param int $postid
 * @param string $coupon_code
 * @param date $wcu_orders_start
 * @param date $wcu_orders_end
 * @param date $isordersstartset
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_tab_latest_orders',
    'wcusage_tab_latest_orders',
    10,
    9
);
if ( !function_exists( 'wcusage_tab_latest_orders' ) ) {
    function wcusage_tab_latest_orders(
        $postid,
        $coupon_code,
        $wcu_orders_start,
        $wcu_orders_end,
        $isordersstartset,
        $show_status = "",
        $limit = "",
        $header = true,
        $footer = true
    ) {
        if ( function_exists( 'wcusage_requests_session_check' ) ) {
            $requests_session = wcusage_requests_session_check( $postid );
        } else {
            $requests_session = array(
                'status'  => false,
                'message' => '',
            );
        }
        if ( isset( $requests_session['status'] ) && $requests_session['status'] ) {
            echo esc_html( $requests_session['message'] );
        } else {
            $couponinfo = wcusage_get_coupon_info_by_id( $postid );
            $couponuser = $couponinfo[1];
            $currentuserid = get_current_user_id();
            $wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
            // Check if user is parent affiliate
            $is_mla_parent = "";
            if ( function_exists( 'wcusage_network_check_sub_affiliate' ) ) {
                $is_mla_parent = wcusage_network_check_sub_affiliate( $currentuserid, $couponuser );
            }
            // Check to make sure not set to private, coupon is assigned to current user, or is admin
            if ( $is_mla_parent || !$couponuser && !$wcusage_urlprivate || $couponuser == $currentuserid || wcusage_check_admin_access() ) {
                $options = get_option( 'wcusage_options' );
                $option_show_orderid = wcusage_get_setting_value( 'wcusage_field_orderid', '0' );
                $option_show_status = wcusage_get_setting_value( 'wcusage_field_status', '1' );
                $option_show_ordercountry = wcusage_get_setting_value( 'wcusage_field_ordercountry', '0' );
                $option_show_ordercity = wcusage_get_setting_value( 'wcusage_field_ordercity', '0' );
                $option_show_ordername = wcusage_get_setting_value( 'wcusage_field_ordername', '0' );
                $option_show_ordernamelast = wcusage_get_setting_value( 'wcusage_field_ordernamelast', '0' );
                $option_show_amount = wcusage_get_setting_value( 'wcusage_field_amount', '1' );
                $option_show_amount_saved = wcusage_get_setting_value( 'wcusage_field_amount_saved', '1' );
                $option_show_shipping = wcusage_get_setting_value( 'wcusage_field_show_shipping', '0' );
                $option_show_tax = wcusage_get_setting_value( 'wcusage_field_show_order_tax', '0' );
                $option_show_list_products = wcusage_get_setting_value( 'wcusage_field_list_products', '1' );
                $wcusage_show_commission = wcusage_get_setting_value( 'wcusage_field_show_commission', '1' );
                $isordersstartset = false;
                /* Get If Page Load */
                global $woocommerce;
                $c = new WC_Coupon($coupon_code);
                $the_coupon_usage = $c->get_usage_count();
                $wcusaFge_page_load = wcusage_get_setting_value( 'wcusage_field_page_load', '' );
                //if($the_coupon_usage > 5000) { $wcusage_page_load = 1; }
                /**/
                $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', '1' );
                $wcusage_field_order_sort = wcusage_get_setting_value( 'wcusage_field_order_sort', '' );
                if ( !$wcusage_field_load_ajax ) {
                    // Filter Orders Submitted
                    if ( isset( $_POST['submitordersfilter'] ) ) {
                        if ( !$wcusage_page_load ) {
                            echo "<script>jQuery( document ).ready(function() { jQuery( '.tabrecentorders' ).click(); });</script>";
                        }
                        $wcu_orders_start = sanitize_text_field( $_POST['wcu_orders_start'] );
                        $wcu_orders_start = preg_replace( "([^0-9-])", "", $wcu_orders_start );
                        $wcu_orders_end = sanitize_text_field( $_POST['wcu_orders_end'] );
                        $wcu_orders_end = preg_replace( "([^0-9-])", "", $wcu_orders_end );
                    }
                    if ( $wcu_orders_start == "" ) {
                        $wcu_orders_start = "";
                    } else {
                        $isordersstartset = true;
                    }
                    if ( $wcu_orders_end == "" ) {
                        $wcu_orders_end = date( "Y-m-d" );
                    }
                }
                // Orders to Show
                $wcusage_field_show_order_tab = wcusage_get_setting_value( 'wcusage_field_show_order_tab', '1' );
                if ( !$limit ) {
                    $option_coupon_orders = wcusage_get_setting_value( 'wcusage_field_orders', '15' );
                    if ( $wcu_orders_start ) {
                        $option_coupon_orders = "";
                    }
                } else {
                    $option_coupon_orders = $limit;
                }
                $orders = wcusage_wh_getOrderbyCouponCode(
                    $coupon_code,
                    $wcu_orders_start,
                    $wcu_orders_end,
                    $option_coupon_orders,
                    1
                );
                $orders = array_reverse( $orders );
                // Show Table
                if ( $wcusage_field_show_order_tab && ($option_coupon_orders > 0 || $option_coupon_orders == "") ) {
                    do_action(
                        'wcusage_hook_show_latest_orders_table',
                        $orders,
                        "",
                        $wcu_orders_start,
                        $wcu_orders_end,
                        "",
                        $show_status,
                        $postid,
                        $header,
                        $footer
                    );
                }
            }
        }
    }

}
/**
 * Displays the latest orders tab content on affiliate dashboard
 *
 * @param int $postid
 * @param string $type
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_show_latest_orders_table',
    'wcusage_show_latest_orders_table',
    10,
    9
);
if ( !function_exists( 'wcusage_show_latest_orders_table' ) ) {
    function wcusage_show_latest_orders_table(
        $orders,
        $type,
        $wcu_orders_start,
        $wcu_orders_end,
        $user_id = "",
        $show_status = "",
        $postid = "",
        $header = true,
        $footer = true
    ) {
        $options = get_option( 'wcusage_options' );
        if ( !$user_id ) {
            $user_id = get_current_user_id();
        }
        $option_show_orderid = wcusage_get_setting_value( 'wcusage_field_orderid', '0' );
        $wcusage_field_orderid_click = wcusage_get_setting_value( 'wcusage_field_orderid_click', '0' );
        $option_show_date = wcusage_get_setting_value( 'wcusage_field_date', '1' );
        $option_show_time = wcusage_get_setting_value( 'wcusage_field_time', '0' );
        $option_show_status = wcusage_get_setting_value( 'wcusage_field_status', '1' );
        $option_show_ordercountry = wcusage_get_setting_value( 'wcusage_field_ordercountry', '0' );
        $option_show_ordercity = wcusage_get_setting_value( 'wcusage_field_ordercity', '0' );
        $option_show_ordername = wcusage_get_setting_value( 'wcusage_field_ordername', '0' );
        $option_show_ordernamelast = wcusage_get_setting_value( 'wcusage_field_ordernamelast', '0' );
        $option_show_amount = wcusage_get_setting_value( 'wcusage_field_amount', '1' );
        $option_show_amount_saved = wcusage_get_setting_value( 'wcusage_field_amount_saved', '1' );
        $option_show_shipping = wcusage_get_setting_value( 'wcusage_field_show_shipping', '0' );
        $option_show_tax = wcusage_get_setting_value( 'wcusage_field_show_order_tax', '0' );
        $option_show_list_products = wcusage_get_setting_value( 'wcusage_field_list_products', '1' );
        $wcusage_show_commission = wcusage_get_setting_value( 'wcusage_field_show_commission', '1' );
        // Check if disable non affiliate commission
        $disable_commission = wcusage_coupon_disable_commission( $postid );
        if ( $disable_commission ) {
            $wcusage_show_commission = 0;
        }
        // Always show commission column on MLA dashboard (it shows what the parent earned)
        if ( $type == "mla" ) {
            $wcusage_show_commission = 1;
        }
        $wcusage_show_orders_table_status_totals = wcusage_get_setting_value( 'wcusage_field_show_orders_table_status_totals', '1' );
        $option_coupon_orders = wcusage_get_setting_value( 'wcusage_field_orders', '15' );
        $option_coupon_max_orders = wcusage_get_setting_value( 'wcusage_field_max_orders', '250' );
        $customdaterange = false;
        if ( $wcu_orders_start ) {
            $option_coupon_orders = $option_coupon_max_orders;
            $customdaterange = true;
        }
        $wcusage_field_order_sort = wcusage_get_setting_value( 'wcusage_field_order_sort', '' );
        echo "<div class='coupon-orders-list'>";
        $totalcount = count( $orders );
        $completedorders = 0;
        ?>

    <!-- Mobile Reponsive Labels -->
    <?php 
        $wcusage_ro_label_count = 1;
        ?>
    <style>
    @media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px)  {

      .listtheproducts { display: none; }
      .listtheproducts td:before { content: "" !important; }
      .listtheproducts { padding: 10px; margin-top: -5px !important; margin-bottom: 20px !important; }
      .wcuTableFoot:nth-of-type(1):before { content: "" !important; }
      .wcuTableFoot:nth-of-type(2):before { content: "" !important; }
      .wcuTableFoot:nth-of-type(9):before { content: "" !important; }
      .wcuTableFoot:nth-of-type(10):before { content: "" !important; }
      .wcuTableFoot:nth-of-type(11):before { content: "" !important; }
      .wcuTableFoot:nth-of-type(12):before { content: "" !important; }

      <?php 
        if ( $option_show_orderid ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "ID", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $type == "mla" ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( 'Coupon', 'woo-coupon-usage' );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_date ) {
            ?>
      .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Date", "woo-coupon-usage" );
            ?>"; }
      <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>      

      <?php 
        if ( $option_show_time ) {
            ?>
      .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Time", "woo-coupon-usage" );
            ?>"; }
      <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_status ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( 'Status', 'woo-coupon-usage' );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_amount ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( 'Subtotal', 'woo-coupon-usage' );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_amount_saved ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Discount", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_amount ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Total", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $wcusage_show_commission ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Commission", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_shipping ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "Shipping"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_tax ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "Tax"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_ordercountry ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Country", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_ordercity ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "City", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_ordername || $option_show_ordernamelast ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Name", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

      <?php 
        if ( $option_show_list_products ) {
            ?>
        .wcu-table-recent-orders td:nth-of-type(<?php 
            echo esc_html( $wcusage_ro_label_count );
            ?>):before { content: "<?php 
            echo esc_html__( "Products", "woo-coupon-usage" );
            ?>"; }
        <?php 
            $wcusage_ro_label_count++;
            ?>
      <?php 
        }
        ?>

    }
    </style>

    <!-- Recent Orders Table -->
    <table id='wcuTable2' class='wcuTable wcu-table-recent-orders' border='2'>

    <?php 
        if ( $header ) {
            ?>
    <thead valign="top">

      <tr class='wcuTableRow'>

        <?php 
            if ( $option_show_orderid ) {
                ?><th class='wcuTableHead'><?php 
                echo esc_html__( 'ID', 'woo-coupon-usage' );
                ?></th><?php 
            }
            ?>

        <?php 
            if ( $type == "mla" ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Coupon', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_date ) {
                ?>
          <th class='wcuTableHead' style='width: 25%;'><?php 
                echo esc_html__( 'Date', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_time ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Time', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_status ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Status', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_amount ) {
                ?><th class='wcuTableHead'><?php 
                echo esc_html__( 'Subtotal', 'woo-coupon-usage' );
                ?></th><?php 
            }
            ?>

        <?php 
            if ( $option_show_amount_saved ) {
                ?><th class='wcuTableHead'><?php 
                echo esc_html__( 'Discount', 'woo-coupon-usage' );
                ?></th><?php 
            }
            ?>

        <?php 
            if ( $option_show_amount ) {
                ?><th class='wcuTableHead'><?php 
                echo esc_html__( 'Total', 'woo-coupon-usage' );
                ?></th><?php 
            }
            ?>

        <?php 
            if ( $option_show_tax ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Tax', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $orders['total_commission'] > 0 && $wcusage_show_commission ) {
                ?>
        <th class='wcuTableHead'>
          <?php 
                echo esc_html__( 'Commission', 'woo-coupon-usage' );
                ?>
        </th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_shipping ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Shipping', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_ordercountry ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Country', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_ordercity ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'City', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_ordername || $option_show_ordernamelast ) {
                ?>
          <th class='wcuTableHead'><?php 
                echo esc_html__( 'Name', 'woo-coupon-usage' );
                ?></th>
        <?php 
            }
            ?>

        <?php 
            if ( $option_show_list_products == "1" ) {
                ?>
          <th class='wcuTableHead'> </th>
        <?php 
            }
            ?>

      </tr>

    </thead>
    <?php 
        }
        ?>

    <?php 
        $count_orders = 0;
        $currentid = 0;
        $combined_total_discount = 0;
        $combined_shipping = 0;
        $combined_ordertotal = 0;
        $combined_ordertotaldiscounted = 0;
        $combined_totalcommission = 0;
        $colstatus = 0;
        $coltime = 0;
        $i = 0;
        $count = 0;
        $cols = 0;
        $col1 = 0;
        $col2 = 0;
        $col3 = 0;
        $col4 = 0;
        $col5 = 0;
        $col6 = 0;
        $col7 = 0;
        $col8 = 0;
        $col9 = 0;
        $col10 = 0;
        $col11 = 0;
        $colmla = 0;
        $total_statuses = array();
        foreach ( $orders as $item ) {
            if ( isset( $orders[$i]['order_id'] ) ) {
                $orderid = $orders[$i]['order_id'];
            } else {
                $orderid = "";
            }
            if ( $currentid != $orderid ) {
                if ( !$orderid ) {
                    break;
                }
                $currentid = $orderid;
                $orderinfo = wc_get_order( $orderid );
                if ( $orderinfo ) {
                    // Count
                    $i++;
                    if ( $orderinfo->get_status() != "refunded" ) {
                        $count++;
                    }
                    // Check if order can be shown by current status
                    $status = $orderinfo->get_status();
                    $check_status_show = wcusage_check_status_show( $status );
                    // MLA Tier Commission
                    $tier = 0;
                    $totalcommissionmla = 0;
                    if ( $type == "mla" ) {
                        $lifetimeaffiliate = wcusage_order_meta( $orderid, 'lifetime_affiliate_coupon_referrer' );
                        $affiliatereferrer = wcusage_order_meta( $orderid, 'wcusage_referrer_coupon' );
                        if ( $lifetimeaffiliate ) {
                            $coupon_info = wcusage_get_coupon_info( $lifetimeaffiliate );
                            $coupon_user_id = $coupon_info[1];
                            if ( $coupon_user_id ) {
                                $get_parents = get_user_meta( $coupon_user_id, 'wcu_ml_affiliate_parents', true );
                                if ( is_array( $get_parents ) ) {
                                    $tier = array_search( $user_id, $get_parents );
                                }
                            }
                        } elseif ( $affiliatereferrer ) {
                            $coupon_info = wcusage_get_coupon_info( $affiliatereferrer );
                            $coupon_user_id = $coupon_info[1];
                            if ( $coupon_user_id ) {
                                $get_parents = get_user_meta( $coupon_user_id, 'wcu_ml_affiliate_parents', true );
                                if ( is_array( $get_parents ) ) {
                                    $tier = array_search( $user_id, $get_parents );
                                }
                            }
                        } else {
                            foreach ( $orderinfo->get_coupon_codes() as $coupon_code ) {
                                $coupon = new WC_Coupon($coupon_code);
                                $couponid = $coupon->get_id();
                                $coupon_user_id = get_post_meta( $couponid, 'wcu_select_coupon_user', true );
                                if ( $coupon_user_id ) {
                                    $get_parents = get_user_meta( $coupon_user_id, 'wcu_ml_affiliate_parents', true );
                                    if ( is_array( $get_parents ) ) {
                                        $tier = array_search( $user_id, $get_parents );
                                    }
                                }
                            }
                        }
                    }
                    // Show Order
                    if ( (!$show_status || "wc-" . $status == $show_status) && $check_status_show && ($tier || $type != "mla") ) {
                        $count_orders++;
                        $enablecurrency = wcusage_get_setting_value( 'wcusage_field_enable_currency', '0' );
                        if ( $orderinfo ) {
                            $currencycode = $orderinfo->get_currency();
                        }
                        $offset = get_option( 'gmt_offset' );
                        $order_date = date_i18n( "F j, Y", strtotime( $orderinfo->get_date_created() ) + $offset * HOUR_IN_SECONDS );
                        if ( $orderinfo ) {
                            $completed_date = $orderinfo->get_date_completed();
                            if ( $completed_date ) {
                                $completed_date = date_i18n( "F j, Y", strtotime( $completed_date ) + $offset * HOUR_IN_SECONDS );
                            } else {
                                $completed_date = "";
                            }
                        }
                        if ( $wcusage_field_order_sort != "completeddate" ) {
                            $showdate = $order_date;
                            $showtime = get_the_time( 'U', $orderid );
                            $showtime = date_i18n( "g:i a", $showtime );
                        } else {
                            $showdate = $completed_date;
                            $showtime = strtotime( $orderinfo->get_date_completed() );
                            $showtime = date_i18n( "g:i a", $showtime );
                        }
                        $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '0' );
                        $wcusage_currency_conversion = wcusage_order_meta( $orderid, 'wcusage_currency_conversion', true );
                        $enable_save_rate = wcusage_get_setting_value( 'wcusage_field_enable_currency_save_rate', '0' );
                        if ( !$wcusage_currency_conversion || !$enable_save_rate ) {
                            $wcusage_currency_conversion = "";
                        }
                        $include_shipping_tax = 0;
                        $shipping = 0;
                        if ( $orderinfo->get_total_shipping() ) {
                            if ( $wcusage_show_tax ) {
                                $include_shipping_tax = wcusage_get_order_tax_percent( $orderid );
                            }
                            $shipping = $orderinfo->get_total_shipping() * (1 + $include_shipping_tax);
                        }
                        if ( $enablecurrency ) {
                            $shipping = wcusage_calculate_currency( $currencycode, $shipping, $wcusage_currency_conversion );
                        }
                        $combined_shipping += (float) $shipping;
                        if ( $wcusage_show_tax == 1 ) {
                            $total_tax = 0;
                        } else {
                            $total_tax = $orderinfo->get_total_tax();
                        }
                        $coupon = new WC_Coupon($postid);
                        $coupon_code = $coupon->get_code();
                        $calculateorder = wcusage_calculate_order_data(
                            $orderid,
                            $coupon_code,
                            0,
                            1
                        );
                        $ordertotal = $calculateorder['totalorders'];
                        $combined_ordertotal += (float) $ordertotal;
                        $ordertotaldiscounted = $calculateorder['totalordersexcl'];
                        $combined_ordertotaldiscounted += (float) $ordertotaldiscounted;
                        $totalorders = $calculateorder['totalorders'];
                        $totaldiscounts = $calculateorder['totaldiscounts'];
                        $combined_total_discount += (float) $totaldiscounts;
                        $totalordersexcl = $calculateorder['totalordersexcl'];
                        $totalcommission = $calculateorder['totalcommission'];
                        $wcusage_field_mla_enable = wcusage_get_setting_value( 'wcusage_field_mla_enable', '0' );
                        if ( $wcusage_field_mla_enable && $type == "mla" ) {
                            // Try stored MLA commission from order meta (persisted at order time)
                            $stored_mla = wcusage_order_meta( $orderid, 'wcu_mla_commission', true );
                            if ( is_array( $stored_mla ) && isset( $stored_mla[$tier]['commission'] ) ) {
                                $totalcommission = (float) $stored_mla[$tier]['commission'];
                            } else {
                                // Fallback: recalculate (for orders before this feature was added)
                                $totalcommission = wcusage_mla_get_commission_from_tier(
                                    $totalcommission,
                                    $tier,
                                    '1',
                                    $orderid,
                                    $coupon_code,
                                    0,
                                    $user_id
                                );
                                // Persist the recalculated value so it won't recalculate again next time
                                $tier_rates = ( function_exists( 'wcusage_mla_get_tier_rates' ) ? wcusage_mla_get_tier_rates( $tier, $user_id ) : array() );
                                if ( !is_array( $stored_mla ) ) {
                                    $stored_mla = array();
                                }
                                $stored_mla[$tier] = array(
                                    'parent_id'  => (int) $user_id,
                                    'commission' => round( (float) $totalcommission, 2 ),
                                    'rates'      => $tier_rates,
                                );
                                $mla_order_obj = wc_get_order( $orderid );
                                if ( $mla_order_obj ) {
                                    $mla_order_obj->update_meta_data( 'wcu_mla_commission', json_encode( $stored_mla ) );
                                    $mla_order_obj->save_meta_data();
                                }
                            }
                        }
                        $combined_totalcommission += (float) $totalcommission;
                        $affiliatecommission = "";
                        if ( isset( $calculateorder['affiliatecommission'] ) ) {
                            $affiliatecommission = $calculateorder['affiliatecommission'];
                        }
                        $currency = $orderinfo->get_currency();
                        $order_refunds = $orderinfo->get_refunds();
                        // Get subscription renewal icon if exist
                        $subicon = wcusage_get_sub_order_icon( $orderid );
                        $random = wp_rand();
                        ?>

              <!-- Script for toggling list of products section -->
              <script>
              jQuery( document ).ready(function() {
                jQuery( "#listproductsbutton-<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?>" ).click(function() {
                  jQuery( ".wcuTableCell.orderproductstd<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?> .fa-chevron-down" ).toggle();
                  jQuery( ".wcuTableCell.orderproductstd<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?> .fa-chevron-up" ).toggle();
                  jQuery( ":not(#listproducts-<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?>).listtheproducts" ).hide();
                  jQuery( "#listproducts-<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?>" ).toggle();
                  jQuery( "#listproductsb-<?php 
                        echo esc_html( $random ) . "-" . esc_html( $orderid );
                        ?>" ).toggle();
                });
              });
              </script>

              <tr class='wcuTableRow'>
                <?php 
                        // Order ID
                        if ( $option_show_orderid ) {
                            echo "<td class='wcuTableCell'>";
                            if ( $wcusage_field_orderid_click && wcusage_check_admin_access() ) {
                                echo "<a href='" . esc_url( admin_url( 'post.php?post=' . $orderid . '&action=edit' ) ) . "' target='_blank' title='" . esc_html__( 'View Order in Backend (Admin Only)', 'woo-coupon-usage' ) . "'>";
                            }
                            echo "#" . esc_html( $orderid );
                            if ( $wcusage_field_orderid_click && wcusage_check_admin_access() ) {
                                echo "</a>";
                            }
                            echo "</td>";
                            $col1 = true;
                        }
                        if ( $type == "mla" ) {
                            echo "<td class='wcuTableCell'>";
                            foreach ( $orderinfo->get_coupon_codes() as $coupon_code ) {
                                $coupon = new WC_Coupon($coupon_code);
                                $couponid = $coupon->get_id();
                                $coupon_user_id = get_post_meta( $couponid, 'wcu_select_coupon_user', true );
                                $coupon_user_info = get_user_by( 'ID', $coupon_user_id );
                                $coupon_user_name = $coupon_user_info->user_login;
                                echo "<span title='User: " . esc_attr( $coupon_user_name ) . "'><span class='fa-solid fa-tags' style='font-size: 12px; display: inline; margin-right: 5px;'></span>" . esc_html( $coupon_code ) . "</span><br/>";
                            }
                            echo "</td>";
                            $colmla = true;
                        }
                        // Date
                        if ( $option_show_date ) {
                            echo "<td class='wcuTableCell'>";
                            if ( $completed_date && $wcusage_field_order_sort == "completeddate" ) {
                                echo "<span title='Completed Date: " . esc_html( $completed_date ) . "'>" . wp_kses_post( $subicon ) . esc_html( ucfirst( $showdate ) ) . "</span>";
                            } else {
                                echo "<span title='Order Date: " . esc_html( $order_date ) . "'>" . wp_kses_post( $subicon ) . esc_html( ucfirst( $showdate ) ) . "</span>";
                            }
                            echo "</td>";
                        }
                        // Time
                        if ( $option_show_time ) {
                            echo "<td class='wcuTableCell wcuTableCell-time'>";
                            echo "<span>" . esc_html( $showtime ) . "</span>";
                            echo "</td>";
                            $coltime = true;
                        }
                        // Status
                        if ( $option_show_status ) {
                            if ( $wcusage_show_orders_table_status_totals ) {
                                $the_status = ucfirst( wc_get_order_status_name( $orderinfo->get_status() ) );
                                if ( !isset( $total_statuses[$the_status] ) ) {
                                    $total_statuses[$the_status] = 1;
                                } else {
                                    $total_statuses[$the_status]++;
                                }
                            }
                            echo "<td class='wcuTableCell'>" . esc_html( ucfirst( wc_get_order_status_name( $orderinfo->get_status() ) ) ) . "</td>";
                            $colstatus = true;
                        }
                        // Total
                        if ( $option_show_amount != "0" ) {
                            echo "<td class='wcuTableCell'> " . wp_kses_post( wcusage_format_price( $ordertotal ) ) . "</td>";
                            $col2 = true;
                        }
                        if ( $option_show_amount_saved != "0" ) {
                            echo "<td class='wcuTableCell'> " . wp_kses_post( wcusage_format_price( number_format(
                                (float) $totaldiscounts,
                                2,
                                '.',
                                ''
                            ) ) ) . "</td>";
                            $col3 = true;
                        }
                        if ( $option_show_amount != 0 ) {
                            echo "<td class='wcuTableCell'> " . wp_kses_post( wcusage_format_price( number_format(
                                (float) $ordertotaldiscounted,
                                2,
                                '.',
                                ''
                            ) ) ) . "</td>";
                        }
                        // Tax
                        if ( $option_show_tax != "0" ) {
                            echo "<td class='wcuTableCell'> " . wp_kses_post( wcusage_format_price( $orderinfo->get_total_tax() ) ) . "</td>";
                            $col11 = true;
                        }
                        // Commission
                        if ( $orders['total_commission'] > 0 && $wcusage_show_commission ) {
                            echo "<td class='wcuTableCell'> ";
                            if ( $type == "mla" ) {
                                echo "<span title='Your commission earned from this sub-affiliate referral.'>";
                            }
                            echo wp_kses_post( wcusage_format_price( number_format(
                                (float) $totalcommission,
                                2,
                                '.',
                                ''
                            ) ) );
                            if ( $type == "mla" ) {
                                echo "</span>";
                            }
                            echo "</td>";
                            $col5 = true;
                        }
                        // Shipping
                        if ( $option_show_shipping != "0" ) {
                            echo "<td class='wcuTableCell'> " . wp_kses_post( wcusage_format_price( $shipping ) ) . "</td>";
                            $col6 = true;
                        }
                        // Country
                        $zone_country = $orderinfo->get_billing_country();
                        if ( $option_show_ordercountry ) {
                            echo "<td class='wcuTableCell'> " . esc_html( $zone_country ) . "</td>";
                            $col8 = true;
                        }
                        // City
                        $zone_city = $orderinfo->get_billing_city();
                        if ( $option_show_ordercity ) {
                            echo "<td class='wcuTableCell'> " . esc_html( $zone_city ) . "</td>";
                            $col9 = true;
                        }
                        // Name
                        if ( $option_show_ordername || $option_show_ordernamelast ) {
                            echo "<td class='wcuTableCell'> ";
                            // Billing Name
                            $zone_name = $orderinfo->get_billing_first_name();
                            $zone_name_last = $orderinfo->get_billing_last_name();
                            if ( $zone_name || $zone_name_last ) {
                                if ( $option_show_ordername ) {
                                    echo esc_html( $zone_name );
                                }
                                if ( $option_show_ordernamelast ) {
                                    echo " " . esc_html( $zone_name_last );
                                }
                            } else {
                                // Show the users username instead.
                                $user_info = get_userdata( $orderinfo->get_user_id() );
                                if ( $user_info ) {
                                    $username = $user_info->user_login;
                                    if ( strlen( $username ) > 20 ) {
                                        $username = substr( $username, 0, 20 ) . "..";
                                    }
                                    echo esc_html( $username );
                                } else {
                                    echo "";
                                }
                            }
                            echo "</td>";
                            $col10 = true;
                        }
                        /* Show the "MORE" products list column / toggle on table */
                        if ( $option_show_list_products == "1" ) {
                            if ( $orderinfo->get_items() && $orderinfo->get_status() != "refunded" && $orderinfo->get_status() != "cancelled" && $orderinfo->get_status() != "failed" ) {
                                echo "<td class='wcuTableCell excludeThisClass orderproductstd orderproductstd" . esc_attr( $random ) . "-" . esc_html( $orderid ) . "' style='min-width: 100px; font-size: 16px;'>";
                                echo "<a class='listproductsbutton' href='javascript:void(0);' id='listproductsbutton-" . esc_attr( $random ) . "-" . esc_html( $orderid ) . "'>" . esc_html__( "MORE", "woo-coupon-usage" ) . " <i class='fas fa-chevron-down'></i> <i class='fas fa-chevron-up' style='display: none;'></i></i></i></a>";
                            } else {
                                echo "<td class='wcuTableCell excludeThisClass orderproductstd'>";
                            }
                            echo "</td>";
                            $col7 = true;
                            $cols++;
                        }
                        $totalorders = 0;
                        $totaldiscounts = 0;
                        $totalcommission = 0;
                        ?>
              </tr>

              <?php 
                        // Cols Count
                        $cols = $wcusage_ro_label_count + 1;
                        /* Show the "MORE" products list section */
                        if ( $option_show_list_products == "1" ) {
                            if ( $orderinfo->get_items() ) {
                                $order_summary = $calculateorder['commission_summary'];
                                if ( isset( $order_summary ) ) {
                                    $extracols = $wcusage_ro_label_count - 7;
                                    $productcols = 2 + $extracols - 1;
                                    ?>

                <span class="excludeThisClass">

                  <tbody style="margin-bottom: 15px;" id="listproducts-<?php 
                                    echo esc_html( $random ) . "-" . esc_html( $orderid );
                                    ?>" class="listtheproducts listtheproducts-summary excludeThisClass"<?php 
                                    if ( $option_show_list_products ) {
                                        ?> style="display: none;"<?php 
                                    }
                                    ?>>

                    <?php 
                                    do_action(
                                        'wcusage_hook_get_detailed_products_summary_tr',
                                        $orderinfo,
                                        $order_summary,
                                        $productcols,
                                        $tier,
                                        $postid
                                    );
                                    ?>

                  </tbody>

                  <tbody id="listproductsb-<?php 
                                    echo esc_html( $random ) . "-" . esc_html( $orderid );
                                    ?>" style="display: none;" class="excludeThisClass">
                    <tr class="listtheproducts listtheproducts-small excludeThisClass">
                      <?php 
                                    do_action(
                                        'wcusage_hook_get_basic_list_order_products',
                                        $orderinfo,
                                        $order_refunds,
                                        $cols
                                    );
                                    ?>
                    </tr>
                  </tbody>

                </span>

                <?php 
                                }
                            }
                        }
                        ?>

            <?php 
                        $completedorders = $completedorders + 1;
                    }
                    if ( $count >= $totalcount || $count >= $option_coupon_orders ) {
                        if ( $customdaterange ) {
                            echo "<p>" . esc_html__( "Maximum orders per request reached", "woo-coupon-usage" ) . " (" . esc_html( $option_coupon_orders ) . "). " . esc_html__( "Please shorten your date range to show all orders.", "woo-coupon-usage" ) . "</p>";
                        }
                        ?>
              <script>
              jQuery( document ).ready(function() {
                jQuery( '#wcu-orders-start' ).val("<?php 
                        echo esc_html( date( "Y-m-d", strtotime( $showdate ) ) );
                        ?>");
              });
              </script>
              <?php 
                        break;
                    }
                }
            }
        }
        ?>

    <?php 
        $wcusage_show_orders_table_totals = wcusage_get_setting_value( 'wcusage_field_show_orders_table_totals', '1' );
        if ( $footer && $wcusage_show_orders_table_totals ) {
            ?>

      <?php 
            if ( $completedorders > 0 ) {
                ?>
      <tfoot valign="top">
        <tr class='wcuTableRow'>

          <?php 
                if ( $col1 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                echo "<td class='wcuTableFoot'><strong>" . esc_html__( "Totals", "woo-coupon-usage" ) . ": (" . esc_html( $count_orders ) . ") </strong></td>";
                if ( $colstatus ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $coltime ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $colmla ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $col2 ) {
                    echo "<td class='wcuTableFoot'><strong>" . wp_kses_post( wcusage_format_price( number_format(
                        (float) $combined_ordertotal,
                        2,
                        '.',
                        ''
                    ) ) ) . "</strong></td>";
                }
                if ( $col3 ) {
                    echo "<td class='wcuTableFoot'><strong>" . wp_kses_post( wcusage_format_price( number_format(
                        (float) $combined_total_discount,
                        2,
                        '.',
                        ''
                    ) ) ) . "</strong></td>";
                }
                echo "<td class='wcuTableFoot'><strong>" . wp_kses_post( wcusage_format_price( number_format(
                    (float) $combined_ordertotaldiscounted,
                    2,
                    '.',
                    ''
                ) ) ) . "</strong></td>";
                if ( $col5 ) {
                    echo "<td class='wcuTableFoot'><strong>" . wp_kses_post( wcusage_format_price( number_format(
                        (float) $combined_totalcommission,
                        2,
                        '.',
                        ''
                    ) ) ) . "</strong></td>";
                }
                if ( $col6 ) {
                    echo "<td class='wcuTableFoot'><strong>" . wp_kses_post( wcusage_format_price( number_format(
                        (float) $combined_shipping,
                        2,
                        '.',
                        ''
                    ) ) ) . "</strong></td>";
                }
                $finalcolspan = 1;
                if ( $col8 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $col9 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $col10 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $col7 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                if ( $col11 ) {
                    echo "<td class='wcuTableFoot'></td>";
                }
                ?>
        </tr>
      </tfoot>
      <?php 
            }
            ?>

    <?php 
        }
        ?>

    </table>
    <?php 
        // Total statuses
        if ( $wcusage_show_orders_table_status_totals && $option_show_status && !empty( $total_statuses ) ) {
            echo "<div class='wcuOrdersStatuses'><br/>";
            foreach ( $total_statuses as $status => $status_total ) {
                $color = "#000";
                if ( $status == "Completed" ) {
                    $color = "green";
                }
                if ( $status == "Pending" ) {
                    $color = "cyan";
                }
                if ( $status == "Processing" ) {
                    $color = "orange";
                }
                if ( $status == "Refunded" ) {
                    $color = "red";
                }
                if ( $status == "Cancelled" ) {
                    $color = "red";
                }
                echo '<i class="fa-solid fa-circle-dot" style="color: ' . esc_attr( $color ) . ';"></i> ' . esc_html( $status ) . ': ' . esc_html( $status_total ) . "";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            echo "</div>";
        }
        // No orders found
        if ( $completedorders == 0 ) {
            echo "<p>" . esc_html__( "No orders found.", "woo-coupon-usage" ) . "<p>";
            echo "<style>.wcu-table-recent-orders { display: none; }</style>";
        }
        echo "</div>";
    }

}
/**
 * Gets the filters for latest orders
 *
 * @param date $wcu_orders_start
 * @param date $wcu_orders_end
 * @param string $coupon_code
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_tab_latest_orders_filters',
    'wcusage_tab_latest_orders_filters',
    10,
    4
);
if ( !function_exists( 'wcusage_tab_latest_orders_filters' ) ) {
    function wcusage_tab_latest_orders_filters(
        $wcu_orders_start,
        $wcu_orders_end,
        $coupon_code,
        $mla = 0
    ) {
        $options = get_option( 'wcusage_options' );
        $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', '1' );
        ?>

	<?php 
        ?>

	<div class="wcu-filters-col1">
		<div class="wcu-filters-inner">
			<div class="wcu-order-filters">

					<form <?php 
        if ( !$wcusage_field_load_ajax ) {
            ?>method="post" <?php 
        }
        ?>id="wcusage_settings_form_orders" class="wcusage_settings_form">
						<span class="wcu-order-filters-field"><?php 
        echo esc_html__( "Start", "woo-coupon-usage" );
        ?>: <input type="date" id="wcu-orders-start" name="wcu_orders_start" value="<?php 
        echo esc_html( $wcu_orders_start );
        ?>"></span>
            <span class="wcu-order-filters-space">&nbsp;</span>
            <span class="wcu-order-filters-field"><?php 
        echo esc_html__( "End", "woo-coupon-usage" );
        ?>: <input type="date" id="wcu-orders-end" name="wcu_orders_end" value="<?php 
        echo esc_html( $wcu_orders_end );
        ?>"></span>
            <span class="wcu-order-filters-space">&nbsp;</span>
            <?php 
        $option_show_status = wcusage_get_setting_value( 'wcusage_field_status', '1' );
        $option_filter_status = wcusage_get_setting_value( 'wcusage_field_show_orders_table_filter_status', '1' );
        if ( $option_show_status && $option_filter_status ) {
            $orderstatuses = wc_get_order_statuses();
            $show_statuses = array();
            $show_statuses_num = 0;
            foreach ( $orderstatuses as $key => $status ) {
                $checked = false;
                $wcusage_field_order_type_custom = wcusage_get_setting_value( 'wcusage_field_order_type_custom', '' );
                if ( $wcusage_field_order_type_custom ) {
                    if ( isset( $options['wcusage_field_order_type_custom'][$key] ) ) {
                        if ( $options['wcusage_field_order_type_custom'][$key] ) {
                            $checked = true;
                        }
                    }
                }
                if ( $checked ) {
                    $show_statuses_num++;
                    array_push( $show_statuses, array(
                        $key => $status,
                    ) );
                }
            }
            if ( $show_statuses_num > 1 ) {
                ?>
              <?php 
                if ( $wcusage_field_load_ajax ) {
                    ?>
              <span class="wcu-order-filters-field" style="display: inline-block;">
                <?php 
                    echo esc_html__( "Status", "woo-coupon-usage" );
                    ?>: <select id="wcu-orders-status" name="wcu_orders_status" style="width: auto; display: inline-block !important;">
                <?php 
                    echo '<option value="">' . esc_html__( "All", "woo-coupon-usage" ) . '</option>';
                    foreach ( $show_statuses as $status ) {
                        echo '<option value="' . esc_attr( key( $status ) ) . '">' . esc_html( reset( $status ) ) . '</option>';
                    }
                    ?>
                </select>
              </span>
              <?php 
                }
                ?>
              <span class="wcu-order-filters-space">&nbsp;</span>
              <?php 
            }
        }
        ?>
            <input type="text" name="page-orders" value="1" style="display: none;">
            <input type="text" name="load-page" value="1" style="display: none;">
            <input class="ordersfilterbutton" <?php 
        if ( $wcusage_field_load_ajax ) {
            ?>type="button"<?php 
        } else {
            ?>type="submit"<?php 
        }
        ?> id="wcu-orders-button" name="submitordersfilter"
            value="<?php 
        echo esc_html__( "Filter", "woo-coupon-usage" );
        ?>" style="padding: 1px 10px;" onclick="wcusage_run_tab_page_orders<?php 
        if ( $mla ) {
            ?>_mla<?php 
        }
        ?>();">
					</form>

			</div>
		</div>
	</div>

	<div class="wcu-filters-col2">
		<div class="wcu-filters-inner">

			<?php 
        ?>
		</div>
	</div>

	<style>.wcu-loading-orders { display: none; }</style>

	<?php 
    }

}
/**
 * Gets latest orders tab for shortcode page
 *
 * @param int $postid
 * @param string $coupon_code
 * @param int $combined_commission
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_dashboard_tab_content_latest_orders',
    'wcusage_dashboard_tab_content_latest_orders',
    10,
    4
);
if ( !function_exists( 'wcusage_dashboard_tab_content_latest_orders' ) ) {
    function wcusage_dashboard_tab_content_latest_orders(
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load
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
        $wcusage_justcoupon = wcusage_get_setting_value( 'wcusage_field_justcoupon', '1' );
        $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '0' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        $wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
        if ( wcusage_check_admin_access() ) {
            $wcusage_urlprivate = 0;
        }
        $ajaxerrormessage = wcusage_ajax_error();
        // *** DISPLAY CONTENT *** //
        ?>

  <script>
  function wcusage_run_tab_page_orders() {
    /* 3 second disable on click button */
    jQuery("#wcu-orders-button").css("opacity", "0.5");
    jQuery("#wcu-orders-button").css("pointer-events", "none");
    setTimeout(function() {
      jQuery("#wcu-orders-button").css("opacity", "1");
      jQuery("#wcu-orders-button").css("pointer-events", "auto");
    }, 3 * 1000);

    /* Set content to empty */
    jQuery('.show_orders').html('');
    jQuery('.wcu-loading-orders').show();

    /* Ajax request */
    var data = {
      action: 'wcusage_load_page_orders',
      _ajax_nonce: '<?php 
        echo esc_html( wp_create_nonce( 'wcusage_dashboard_ajax_nonce' ) );
        ?>',
      postid: '<?php 
        echo esc_html( $postid );
        ?>',
      couponcode: '<?php 
        echo esc_html( $coupon_code );
        ?>',
      startdate: jQuery('input[name=wcu_orders_start]').val(),
      enddate: jQuery('input[name=wcu_orders_end]').val(),
      status: jQuery('#wcu-orders-status option').filter(":selected").val(),
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
      jQuery('#wcu3 .wcuTable').remove();
      jQuery('.show_orders').html(data);
      jQuery('.wcu-loading-orders').hide();
      },
      error: function(data) {
      jQuery('.show_orders').html('<?php 
        echo wp_kses_post( $ajaxerrormessage );
        ?>');
      }
    });
  }

  jQuery(document).ready(function() {
    <?php 
        if ( $wcusage_field_load_ajax_per_page ) {
            ?>
    jQuery("#tab-page-orders").one('click', wcusage_run_tab_page_orders);
    <?php 
        }
        ?>
    jQuery(".wcusage-refresh-data").on('click', wcusage_run_tab_page_orders);
  });
  </script>

  <?php 
        if ( isset( $_POST['page-orders'] ) || !isset( $_POST['load-page'] ) || $wcusage_page_load == false ) {
            ?>

    <?php 
            // Get orders date filters
            $isordersstartset = false;
            $wcu_orders_start = "";
            $wcu_orders_end = "";
            if ( !$wcusage_field_load_ajax ) {
                if ( isset( $_POST['submitordersfilter'] ) ) {
                    $wcu_orders_start = sanitize_text_field( $_POST['wcu_orders_start'] );
                    $wcu_orders_start = preg_replace( "([^0-9-])", "", $wcu_orders_start );
                    $wcu_orders_end = sanitize_text_field( $_POST['wcu_orders_end'] );
                    $wcu_orders_end = preg_replace( "([^0-9-])", "", $wcu_orders_end );
                }
            }
            if ( $wcu_orders_start == "" ) {
                $wcu_orders_start = "";
            } else {
                $isordersstartset = true;
            }
            if ( $wcu_orders_end == "" ) {
                $wcu_orders_end = date( "Y-m-d" );
            }
            ?>

    <?php 
            if ( isset( $_POST['page-orders'] ) ) {
                ?>
    <style>#wcu3 { display: block;  }</style>
    <?php 
            }
            ?>

    <div id="wcu3" <?php 
            if ( $wcusage_show_tabs == '1' || $wcusage_show_tabs == '' ) {
                ?>class="wcutabcontent"<?php 
            }
            ?>>

      <?php 
            echo "<p class='wcu-tab-title coupon-orders-list-title' style='font-size: 22px; margin-bottom: 0;'>" . esc_html__( "Recent Orders", "woo-coupon-usage" ) . ":</p>";
            // Recent Orders
            ?>

      <?php 
            do_action(
                'wcusage_hook_tab_latest_orders_filters',
                $wcu_orders_start,
                $wcu_orders_end,
                $coupon_code
            );
            ?>
      <div style="clear: both;"></div>

      <?php 
            if ( $wcusage_field_load_ajax ) {
                ?>

        <div class="show_orders"></div>

        <div class="wcu-loading-image wcu-loading-orders">
          <div class="wcu-loading-loader">
            <div class="loader"></div>
          </div>
          <p class="wcu-loading-loader-text"><br/><?php 
                echo esc_html__( "Loading", "woo-coupon-usage" );
                ?>...</p>
        </div>

      <?php 
            } else {
                ?>

        <?php 
                do_action(
                    'wcusage_hook_tab_latest_orders',
                    $postid,
                    $coupon_code,
                    $wcu_orders_start,
                    $wcu_orders_end,
                    $isordersstartset
                );
                ?>

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