<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Displays the admin reports page
 *
 */
if ( !function_exists( 'wcusage_admin_reports_page_html' ) ) {
    function wcusage_admin_reports_page_html() {
        $options = get_option( 'wcusage_options' );
        $wcusage_field_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', 1 );
        // Check user capabilities
        if ( !wcusage_check_admin_access() ) {
            return;
        }
        ?>

  <link rel="stylesheet" href="<?php 
        echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
        ?>" crossorigin="anonymous">

  <div class="wrap admin-reports wcusage-admin-page">

  <?php 
        do_action( 'wcusage_hook_dashboard_page_header', '' );
        ?>

  <h1><?php 
        echo esc_html__( "Admin Reports & Analytics", "woo-coupon-usage" );
        ?></h1>

  <p style="color: #333;">
    <i class="fas fa-info-circle"></i> <?php 
        echo esc_html__( 'With admin reports, you can view statistics and analytics for all your coupons and affiliates.', 'woo-coupon-usage' );
        ?> <a href="https://couponaffiliates.com/docs/admin-reports-analytics" target="_blank">Learn More</a>.
  </p>

  <br/>

  <!----- Filters ---->
  <?php 
        if ( wcu_fs()->can_use_premium_code() ) {
            $defaultdays = "-1 month";
            $wcu_orders_date_min = "";
        } else {
            $defaultdays = "-1 month";
            $wcu_orders_date_min = date( "Y-m-d", strtotime( "-1 month" ) );
        }
        $wcu_orders_date_max = date( "Y-m-d" );
        $wcu_monthly_orders_start = date( "Y-m-d", strtotime( $defaultdays ) );
        $wcu_monthly_orders_end = date( "Y-m-d" );
        $wcu_monthly_orders_start_compare = date( "Y-m-d", strtotime( $defaultdays, strtotime( $wcu_monthly_orders_start ) ) );
        $wcu_monthly_orders_end_compare = date( "Y-m-d", strtotime( $defaultdays, strtotime( $wcu_monthly_orders_end ) ) );
        ?>
  <div>
      <form method="post" class="wcusage_settings_form wcu-admin-reports-form"
      onsubmit="return false;" style="background: linear-gradient(#fefefe, #f6f6f6); border: 1px solid #f3f3f3; border: 1px solid #f1f1f1;">

      <h2 style="margin: 0 auto 20px auto; display: block; font-size: 25px; text-align: center;"><?php 
        echo esc_html__( "Generate a new admin report", "woo-coupon-usage" );
        ?>:</h2>

      <div class="admin-report-form-row">

      <!-- Main Date Range -->
      <p style="padding-top: 0; margin-top: 0;">
        <span class="wcu-order-filters-field wcu-order-filters-field-date">
          <?php 
        echo esc_html__( "Start", "woo-coupon-usage" );
        ?>: <input type="date"
          min="<?php 
        echo esc_attr( $wcu_orders_date_min );
        ?>" max="<?php 
        echo esc_attr( $wcu_orders_date_max );
        ?>"
          id="wcu-orders-start" name="wcu_monthly_orders_start"
          value="<?php 
        echo esc_attr( $wcu_monthly_orders_start );
        ?>">
        </span>
        <span class="wcu-order-filters-space">&nbsp;</span>
        <span class="wcu-order-filters-field wcu-order-filters-field-date">
          <?php 
        echo esc_html__( "End", "woo-coupon-usage" );
        ?>: <input type="date"
          min="<?php 
        echo esc_attr( $wcu_orders_date_min );
        ?>" max="<?php 
        echo esc_attr( $wcu_orders_date_max );
        ?>"
          id="wcu-orders-end" name="wcu_monthly_orders_end"
          value="<?php 
        echo esc_attr( $wcu_monthly_orders_end );
        ?>">
        </span>
      </p>

      <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>
      <p style="color: #959595;">
        <?php 
            echo esc_html__( "Free version can display reports for up to the past 1 month.", "woo-coupon-usage" );
            ?>
        <br/>
        <?php 
            echo esc_html__( "Unlimited date range selection is available with the", "woo-coupon-usage" );
            ?> <a href="<?php 
            echo esc_url( get_admin_url() );
            ?>admin.php?page=wcusage-pricing&trial=true" style="color: green;">PRO version</a>.
      </p>
      <?php 
        }
        ?>

      <div <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>class="wcu-tooltip" style="opacity: 0.5;"<?php 
        }
        ?>>
      <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?><span class="wcu-tooltiptext"><?php 
            echo esc_html__( "Date comparisons and more filters are available with the", "woo-coupon-usage" );
            ?> <a href="<?php 
            echo esc_url( admin_url( 'admin.php?page=wcusage-pricing&trial=true' ) );
            ?>" style="color: green;">PRO version</a>.</span><?php 
        }
        ?>

        <!-- Compare Date Range -->
        <script>
        jQuery(document).ready(function(){
          jQuery(".wcu-report-compare-dates").hide();

          jQuery("#wcu_report_compare_to").on('change', function() {
            if (jQuery('#wcu_report_compare_to').is(':checked')) {
              jQuery(".wcu-report-compare-dates").show();
            } else {
              jQuery(".wcu-report-compare-dates").hide();
            }
          });
        });
        </script>

        <hr style="margin-top: 17px;" />

        <p><input type="checkbox" <?php 
        ?> value="true" style="margin-top: -2px;"> <strong><?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>(PRO) <?php 
        }
        echo esc_html__( "Compare with another date range", "woo-coupon-usage" );
        ?>.</strong></p>

        <div class="wcu-report-compare-dates" style="display: none;">

          <p <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>style="pointer-events: none;"<?php 
        }
        ?>>
            <span class="wcu-order-filters-field wcu-order-filters-field-date">
              <?php 
        echo esc_html__( "Start", "woo-coupon-usage" );
        ?>: <input type="date" <?php 
        ?> value="<?php 
        echo esc_attr( $wcu_monthly_orders_start_compare );
        ?>">
            </span>
            <span class="wcu-order-filters-space">&nbsp;</span>
            <span class="wcu-order-filters-field wcu-order-filters-field-date">
              <?php 
        echo esc_html__( "End", "woo-coupon-usage" );
        ?>: <input type="date" <?php 
        ?> value="<?php 
        echo esc_attr( $wcu_monthly_orders_end_compare );
        ?>">
            </span>
          </p>

          <div class="wcu-report-filtercompare-field">
            <p <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>style="pointer-events: none;"<?php 
        }
        ?>>
              <span class="wcu-order-filtercompare-field">
                <strong style="display: block; margin-bottom: 5px;"><?php 
        echo esc_html__( "Only show coupons where sales have", "woo-coupon-usage" );
        ?>:</strong>
                <select <?php 
        ?>>
                  <option value="both"><?php 
        echo esc_html__( "Increased or Decreased", "woo-coupon-usage" );
        ?></option>
                  <option value="more"><?php 
        echo esc_html__( "Increased", "woo-coupon-usage" );
        ?></option>
                  <option value="less"><?php 
        echo esc_html__( "Decreased", "woo-coupon-usage" );
        ?></option>
                </select>
                <?php 
        echo esc_html__( "by more than", "woo-coupon-usage" );
        ?>
                <input type="number" <?php 
        ?> value="0" style="max-width: 60px;" min="0" max="100%" required>%
              </span>
            </p>
          </div>

        </div>

      </div>

      <hr style="margin: 17px 0 15px 0;" />

      <p><input type="checkbox" id="wcu_report_users_only" name="wcu_report_users_only" value="true" style="margin-top: -2px;"> <strong><?php 
        echo sprintf( esc_html__( "Only show coupons assigned to an %s user.", "woo-coupon-usage" ), esc_html( strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) ) );
        ?></strong></p>

      <!-- User Roles Filter -->
      <div id="wcu_report_user_roles_div" style="display: none; margin-top: 10px;">
        <strong><?php 
        echo esc_html__( "Filter by user roles/groups:", "woo-coupon-usage" );
        ?></strong><br/>
        <i><?php 
        echo esc_html__( "If none selected, all roles will be included.", "woo-coupon-usage" );
        ?></i>
        <span style="height: 100px; width: 300px; overflow-y: auto; display: block; border: 1px solid #ddd; padding: 10px; margin-top: 5px;">
        <?php 
        global $wp_roles;
        $roles = $wp_roles->get_names();
        // Re-order with all those containing "coupon_affiliate" at the start
        $roles2 = array();
        foreach ( $roles as $key => $role ) {
            if ( strpos( $key, 'coupon_affiliate' ) !== false ) {
                $roles2[$key] = $role;
                unset($roles[$key]);
            }
        }
        $roles2 = array_merge( $roles2, $roles );
        foreach ( $roles2 as $key => $role_name ) {
            if ( strpos( $key, 'coupon_affiliate' ) !== false ) {
                $role_display = '(Group) ' . $role_name;
            } else {
                $role_display = $role_name;
            }
            echo '<input type="checkbox" name="wcu_report_user_roles[]" value="' . esc_attr( $key ) . '" style="margin-top: -2px;"> ' . esc_html( $role_display ) . '<br/>';
        }
        ?>
        </span>
      </div>

      <script>
      jQuery(document).ready(function(){
        jQuery("#wcu_report_users_only").on('change', function() {
          if (jQuery('#wcu_report_users_only').is(':checked')) {
            jQuery("#wcu_report_user_roles_div").show();
          } else {
            jQuery("#wcu_report_user_roles_div").hide();
          }
        });
      });
      </script>

      <hr style="margin: 17px 0;" />

      <strong>Statistics to display:</strong>
      <p style="margin-bottom: 10px">

      <!-- Compare Date Range -->
      <?php 
        $extrafilters = array(
            array("wcu_report_show_sales", ".wcu-order-filtersales-field", esc_html__( "Sales", "woo-coupon-usage" )),
            array("wcu_report_show_commission", ".wcu-order-filtercommission-field, .wcu-order-filterunpaid-field", esc_html__( "Commission", "woo-coupon-usage" )),
            array("wcu_report_show_url", ".wcu-order-filterconversions-field", esc_html__( "Referral URLs", "woo-coupon-usage" )),
            array("wcu_report_show_products", "", esc_html__( "Products", "woo-coupon-usage" ))
        );
        ?>
      <?php 
        foreach ( $extrafilters as $filters ) {
            ?>
      <script>
      jQuery(document).ready(function(){
        jQuery("#<?php 
            echo esc_html( $filters[0] );
            ?>").on('change', function() {
          if (jQuery('#<?php 
            echo esc_html( $filters[0] );
            ?>').is(':checked')) {
            jQuery("<?php 
            echo esc_html( $filters[1] );
            ?>").show();
          } else {
            jQuery("<?php 
            echo esc_html( $filters[1] );
            ?>").hide();
          }
          if ( !jQuery('#wcu_report_show_sales').is(':checked') && !jQuery('#wcu_report_show_commission').is(':checked') && !jQuery('#wcu_report_show_url').is(':checked') ) {
            jQuery(".wcu-report-filterextra-fields").hide();
          } else {
            jQuery(".wcu-report-filterextra-fields").show();
          }
        });
      });
      </script>
      <input type="checkbox" id="<?php 
            echo esc_html( $filters[0] );
            ?>" name="<?php 
            echo esc_html( $filters[0] );
            ?>" value="true" style="margin-top: -2px;" checked> <strong style="margin-right: 7px; margin-left: -4px;"><?php 
            echo esc_html( $filters[2] );
            ?></strong>
      <?php 
        }
        ?>

      </p>

    </div>

    <div class="admin-report-form-row" style="border-left: 1px solid #f3f3f3;">

      <div class="wcu-report-filterextra-fields" style="margin-bottom: 30px;">

      <strong style="display: block; margin-top: -4px;">Only show coupons where:</strong>

      <!-- Filter by Total Usage -->
      <div class="wcu-report-filterusage-field">
        <p>
          <span class="wcu-order-filterusage-field">
            <strong>Total Usage</strong> is
            <select id="wcu-orders-filterusage-type" name="wcu_orders_filterusage_type">
              <option value="more"><?php 
        echo esc_html__( "More", "woo-coupon-usage" );
        ?></option>
              <option value="more or equal"><?php 
        echo esc_html__( "Equal or More", "woo-coupon-usage" );
        ?></option>
              <option value="less or equal"><?php 
        echo esc_html__( "Equal or Less", "woo-coupon-usage" );
        ?></option>
              <option value="less"><?php 
        echo esc_html__( "Less", "woo-coupon-usage" );
        ?></option>
              <option value="equal"><?php 
        echo esc_html__( "Equal", "woo-coupon-usage" );
        ?></option>
            </select>
            than
            <input type="number" id="wcu-orders-filterusage-amount" name="wcu_orders_filterusage_amount" value="0" min="0" required>
          </span>
        </p>
      </div>

      <!-- Filter by Total Sales -->
      <div class="wcu-report-filtersales-field">
        <p>
          <span class="wcu-order-filtersales-field">
            <strong>Total Sales</strong> is
            <select id="wcu-orders-filtersales-type" name="wcu_orders_filtersales_type">
              <option value="more or equal"><?php 
        echo esc_html__( "Equal or More", "woo-coupon-usage" );
        ?></option>
              <option value="more"><?php 
        echo esc_html__( "More", "woo-coupon-usage" );
        ?></option>
              <option value="less or equal"><?php 
        echo esc_html__( "Equal or Less", "woo-coupon-usage" );
        ?></option>
              <option value="less"><?php 
        echo esc_html__( "Less", "woo-coupon-usage" );
        ?></option>
              <option value="equal"><?php 
        echo esc_html__( "Equal", "woo-coupon-usage" );
        ?></option>
            </select>
            than
            <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?>
            <input type="number" id="wcu-orders-filtersales-amount" name="wcu_orders_filtersales_amount" value="0" min="0" required>
          </span>
        </p>
      </div>

      <!-- Filter by Commission Earned -->
      <div class="wcu-report-filtercommission-field">
        <p>
          <span class="wcu-order-filtercommission-field">
            <strong>Commission Earned</strong> is
            <select id="wcu-orders-filtercommission-type" name="wcu_orders_filtercommission_type">
              <option value="more or equal"><?php 
        echo esc_html__( "Equal or More", "woo-coupon-usage" );
        ?></option>
              <option value="more"><?php 
        echo esc_html__( "More", "woo-coupon-usage" );
        ?></option>
              <option value="less or equal"><?php 
        echo esc_html__( "Equal or Less", "woo-coupon-usage" );
        ?></option>
              <option value="less"><?php 
        echo esc_html__( "Less", "woo-coupon-usage" );
        ?></option>
              <option value="equal"><?php 
        echo esc_html__( "Equal", "woo-coupon-usage" );
        ?></option>
            </select>
            than
            <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?>
            <input type="number" id="wcu-orders-filtercommission-amount" name="wcu_orders_filtercommission_amount" value="0" min="0" required>
          </span>
        </p>
      </div>

      <?php 
        if ( wcu_fs()->can_use_premium_code() ) {
            ?>
      <div>

      <!-- Filter by Unpaid Commission -->
      <div class="wcu-report-filterunpaid-field">
        <p>
          <span class="wcu-order-filterunpaid-field">
            <strong>Unpaid Commission</strong> is
            <select id="wcu-orders-filterunpaid-type" name="wcu_orders_filterunpaid_type">
              <option value="more or equal"><?php 
            echo esc_html__( "Equal or More", "woo-coupon-usage" );
            ?></option>
              <option value="more"><?php 
            echo esc_html__( "More", "woo-coupon-usage" );
            ?></option>
              <option value="less or equal"><?php 
            echo esc_html__( "Equal or Less", "woo-coupon-usage" );
            ?></option>
              <option value="less"><?php 
            echo esc_html__( "Less", "woo-coupon-usage" );
            ?></option>
              <option value="equal"><?php 
            echo esc_html__( "Equal", "woo-coupon-usage" );
            ?></option>
            </select>
            than
            <?php 
            echo wp_kses_post( wcusage_get_currency_symbol() );
            ?>
            <input type="number" id="wcu-orders-filterunpaid-amount" name="wcu_orders_filterunpaid_amount" value="0" min="0" required>
          </span>
        </p>
      </div>

      </div>
    <?php 
        } else {
            ?>
      <select id="wcu-orders-filterunpaid-type" name="wcu_orders_filterunpaid_type" hidden>
        <option value="more or equal"><?php 
            echo esc_html__( "Equal or More", "woo-coupon-usage" );
            ?></option>
      </select>
      <input type="hidden" id="wcu-orders-filterunpaid-amount" name="wcu_orders_filterunpaid_amount" value="0" hidden>
    <?php 
        }
        ?>

      <!-- Filter by URL Conversion Rate -->
      <div class="wcu-report-filterconversions-field" style="margin-bottom: -20px;">
        <p>
          <span class="wcu-order-filterconversions-field">
            <strong>URL Conversion Rate</strong> is
            <select id="wcu-orders-filterconversions-type" name="wcu_orders_filterconversions_type">
              <option value="more or equal"><?php 
        echo esc_html__( "Equal or More", "woo-coupon-usage" );
        ?></option>
              <option value="more"><?php 
        echo esc_html__( "More", "woo-coupon-usage" );
        ?></option>
              <option value="less or equal"><?php 
        echo esc_html__( "Equal or Less", "woo-coupon-usage" );
        ?></option>
              <option value="less"><?php 
        echo esc_html__( "Less", "woo-coupon-usage" );
        ?></option>
              <option value="equal"><?php 
        echo esc_html__( "Equal", "woo-coupon-usage" );
        ?></option>
            </select>
            than
            <input type="number" id="wcu-orders-filterconversions-amount" name="wcu_orders_filterconversions_amount" value="0" min="0" required>%
          </span>
        </p>
      </div>

      </div>

    </div>

    <div style="clear: both;"></div>
    <p style="margin-top: 20px;">
      <input type="text" name="page-monthly" value="1" style="display: none;"><input type="text" name="load-page" value="1" style="display: none;">
      <button class="ordersfilterbutton wcu-button-search-report-admin" type="submit" id="wcu-monthly-orders-button" name="submitmonthlyordersfilter">
        <?php 
        echo esc_html__( "GENERATE REPORT", "woo-coupon-usage" );
        ?> <i class="fas fa-arrow-right"></i>
      </button>
    </p>

    </form>

  </div>

  <!-- Loader -->
  <script>
  var isclickedreport;

  jQuery(document).ready(function(){
    jQuery(".wcu-loading-image").hide();
    jQuery(".loaded-stats").hide();
  });

  jQuery(document).on("click", "#generate-new-report", function(){
    location.reload();
  });

  jQuery(document).on("click", "#wcu-monthly-orders-button", function(){
    jQuery(".wcu-admin-reports-form").hide();
    jQuery(".show_data").html("");
    jQuery(".loaded-stats").hide();
  });

  window.wcu_calculate_stats = function() {

        // Check the field total fields and update the stats

        jQuery(".wcu-loading-image").hide();
        jQuery(".loaded-stats").show();

        // ***** Sales Statistics / Commission Statistics ***** //

        <?php 
        $stattypes = [
            'total-usage',
            'total-sales',
            'total-discounts',
            'total-commission',
            'unpaid-commission',
            'pending-commission',
            'total-clicks',
            'total-conversions',
            'total-conversion-rate'
        ];
        foreach ( $stattypes as $stat ) {
            ?>
        
          var thetotal = 0;
          <?php 
            if ( $stat == 'total-sales' || $stat == 'total-discounts' || $stat == 'total-commission' || $stat == 'unpaid-commission' || $stat == 'pending-commission' ) {
                ?>
            jQuery(".final-<?php 
                echo esc_html( $stat );
                ?>").each(function(){
              thetotal += parseFloat(jQuery(this).val());
            });
            jQuery(".<?php 
                echo esc_html( $stat );
                ?>").text(thetotal.toFixed(2));
          <?php 
            } elseif ( $stat == 'total-conversion-rate' ) {
                ?>
            // Get the total clicks and conversions conversion rate
            var totalclicks = parseFloat(jQuery(".final-total-clicks").val());
            var totalconversions = parseFloat(jQuery(".final-total-conversions").val());
            var totalconversionrate = 0;
            if ( totalclicks > 0 ) {
              totalconversionrate = (totalconversions / totalclicks) * 100;
            }
            jQuery(".<?php 
                echo esc_html( $stat );
                ?>").text(totalconversionrate.toFixed(2));
          <?php 
            } else {
                ?>
            jQuery(".final-<?php 
                echo esc_html( $stat );
                ?>").each(function(){
              thetotal += parseInt(jQuery(this).val());
            });          
            jQuery(".<?php 
                echo esc_html( $stat );
                ?>").text(thetotal.toFixed(0));
          <?php 
            }
            ?>

        <?php 
        }
        ?>

  };
  </script>

  <!-- Loader -->
  <div class="wcu-loading-image wcu-loading-stats" style="display: none;">
    <div class="wcu-loading-loader"></div>
    <p class="wcu-loading-loader-text"><?php 
        echo esc_html__( "Generating Report", "woo-coupon-usage" );
        ?>...</p>
    <p class="wcu-loading-loader-subtext"><?php 
        echo esc_html__( "This may take a few seconds. Larger date ranges may take longer.", "woo-coupon-usage" );
        ?></p>
  </div>

  <div class="loaded-stats-wrapper" style="display: none;">

    <div class="loaded-stats">

    <p style="margin: 0;">
    <a id="generate-new-report" href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_admin_reports' ) );
        ?>"
    style="text-decoration: none; font-weight: bold;">
      <?php 
        echo esc_html__( "GENERATE NEW REPORT", "woo-coupon-usage" );
        ?> <i class="fas fa-angle-double-right"></i>
    </a>
    </p>

    <br/>

    <h2 id="report-complete-title">Report Complete!</h2>

    <div class='after-report-complete'></div>

      <div class="wcusage-reports-stats-section-sales">

        <br/>
        <div style="clear: both;"></div>

          <fieldset class="wcusage-reports-stats-section">

            <legend class="wcusage-reports-stats-title">Sales Statistics</legend>

            <!-- Total Usage -->
            <div class="wcusage-info-box wcusage-info-box-usage">
              <p>
                <span class="wcusage-info-box-title">Total Usage:</span>
                <span class="total-usage">0</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-usage-old">0</span></span>
                </p>
            </div>

            <!-- Total Order -->
            <div class="wcusage-info-box wcusage-info-box-sales">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Total Sales", "woo-coupon-usage" );
        ?>:</span>
                <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?><span class="total-sales">0.00</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-sales-old">0</span></span>
              </p>
            </div>

            <!-- Total Discounts -->
            <div class="wcusage-info-box wcusage-info-box-discounts">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Total Discounts", "woo-coupon-usage" );
        ?>:</span>
                <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?><span class="total-discounts">0.00</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-discounts-old">0</span></span>
              </p>
            </div>

          </fieldset>

        </div>
        
        <div class="wcusage-reports-stats-section-commission">

          <br/>
          <div style="clear: both;"></div>

          <fieldset class="wcusage-reports-stats-section wcusage-reports-stats-section-commission">

            <legend class="wcusage-reports-stats-title">Commission Statistics</legend>

            <!-- Total Commission -->
            <div class="wcusage-info-box wcusage-info-box-dollar">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Total Commission", "woo-coupon-usage" );
        ?>:</span>
                <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?><span class="total-commission">0.00</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-commission-old">Earned during this period.</span></span>
              </p>
            </div>

            <!-- Unpaid Commission -->
            <div class="wcusage-info-box wcusage-info-box-dollar" <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?> style="opacity: 0.25; pointer-events: none;"<?php 
        }
        ?>>
              <p>
                <span class="wcusage-info-box-title"><?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>(PRO)<?php 
        }
        ?> <?php 
        echo esc_html__( "Unpaid Commission", "woo-coupon-usage" );
        ?>:</span>
                <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?><span class="unpaid-commission">0</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span><span style='display: block; color: #bebebe; font-size: 12px;'>(Awaiting Payout Request)</span></span></span>
              </p>
            </div>

            <!-- Pending Commission -->
            <div class="wcusage-info-box wcusage-info-box-dollar" <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?> style="opacity: 0.25; pointer-events: none;"<?php 
        }
        ?>>
              <p>
                <span class="wcusage-info-box-title"><?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>(PRO)<?php 
        }
        ?> <?php 
        echo esc_html__( "Pending Payouts", "woo-coupon-usage" );
        ?>:</span>
                <?php 
        echo wp_kses_post( wcusage_get_currency_symbol() );
        ?><span class="pending-commission">0</span>
                <span class="all-time-side-text" style="font-size: 12px; font-weight: bold; display: inline;"><a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=wcusage_payouts' ) );
        ?>" style="text-decoration: none;">Pay Now <i class="fas fa-arrow-right"></i></a></span>
              </p>
            </div>

          </fieldset>

        </div>

        <div class="wcusage-reports-stats-section-url">

          <br/>
          <div style="clear: both;"></div>

          <fieldset class="wcusage-reports-stats-section">

            <legend class="wcusage-reports-stats-title">Referral URL Statistics</legend>

            <!-- Total Clicks -->
            <div class="wcusage-info-box wcusage-info-box-clicks">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Total Clicks", "woo-coupon-usage" );
        ?>:</span>
                <span class="total-clicks">0</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-clicks-old"></span></span>
              </p>
            </div>

            <!-- Total Conversions -->
            <div class="wcusage-info-box wcusage-info-box-convert">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Total Conversions", "woo-coupon-usage" );
        ?>:</span>
                <span class="total-conversions">0</span>
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-conversions-old"></span></span>
              </p>
            </div>

            <!-- Total Conversions -->
            <div class="wcusage-info-box wcusage-info-box-percent">
              <p>
                <span class="wcusage-info-box-title"><?php 
        echo esc_html__( "Conversion Rate", "woo-coupon-usage" );
        ?>:</span>
                <span class="total-conversion-rate">0</span>%
                <span style="font-size: 12px; font-weight: bold; display: none;" class="all-time-previous"><span class="total-conversion-rate-old"></span></span>
              </p>
            </div>

          </fieldset>

        </div>

      <br/>
      <div style="clear: both;"></div>

      <h2>Individual Coupon Statistics</h2>

      <!-- Search -->
      <div id="search-block" style="display: inline-block;">
          <input type="text" id="inpSearch" placeholder="<?php 
        echo esc_html__( "Search Coupons", "woo-coupon-usage" );
        ?>..." style="float: left; height: 50px;" />
          <input type="button" id="inpSearchBtn" class="wcu-button-search-report-admin" value="Search">
      </div>

      <!-- Export Button -->

      <?php 
        if ( wcu_fs()->can_use_premium_code() ) {
            ?>
        <?php 
            $randomfilename = substr( md5( uniqid( mt_rand(), true ) ), 0, 8 );
            ?>
        <script src="<?php 
            echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'js/jquery.table2excel.min.js';
            ?>"></script>
        <script>
        jQuery( document ).ready(function() {

          jQuery("#exportBtn").click(function(){

            jQuery("#table-coupon-items").table2excel({
              exclude: ".excludeThisClassExport",
              name: "Coupon Affiliates Report",
              filename: "coupon-affiliates-report-<?php 
            echo esc_html( $randomfilename );
            ?>.xls",
              preserveColors: false // set to true if you want background colors and font colors preserved
            });

          });

        });
        </script>
      <?php 
        }
        ?>

      <span <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>style="opacity: 0.4;" title="Available with Pro."<?php 
        }
        ?>>
        <input type="button" id="exportBtn"
        class="wcu-button-export-admin"
        value="<?php 
        echo esc_html__( "Download CSV", "woo-coupon-usage" );
        ?> &#x025B8; <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?> (Pro)<?php 
        }
        ?>"
        <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>style="cursor: default;" onclick="return false;"<?php 
        }
        ?>>
      </span>

      <div style="clear: both;"></div>

      <p>
      Sort by:
      <span class="wcusage-reports-stats-section-sales">
      <a href="#!" id="sort-by-usage" class="sort-link"><?php 
        echo esc_html__( "Usage", "woo-coupon-usage" );
        ?></a>
      | <a href="#!" id="sort-by-orders" class="sort-link"><?php 
        echo esc_html__( "Orders", "woo-coupon-usage" );
        ?></a>
      | <a href="#!" id="sort-by-discounts" class="sort-link"><?php 
        echo esc_html__( "Discounts", "woo-coupon-usage" );
        ?></a>
      </span>
      <span class="wcusage-reports-stats-section-commission">
      | <a href="#!" id="sort-by-commission" class="sort-link"><?php 
        echo esc_html__( "Commission", "woo-coupon-usage" );
        ?></a>
        <?php 
        if ( $wcusage_field_tracking_enable && wcu_fs()->can_use_premium_code() ) {
            ?>
          | <a href="#!" id="sort-by-unpaid" class="sort-link"><?php 
            echo esc_html__( "Unpaid", "woo-coupon-usage" );
            ?></a>
          | <a href="#!" id="sort-by-pending" class="sort-link"><?php 
            echo esc_html__( "Pending", "woo-coupon-usage" );
            ?></a>
        <?php 
        }
        ?>
      </span>
      </p>

    </div>

  </div>


<script>
  jQuery(document).ready(function(){

    jQuery.expr.pseudos.Contains = function(a, i, m) {
      return jQuery(a).text().toUpperCase()
          .indexOf(m[3].toUpperCase()) >= 0;
    };

    jQuery.expr.pseudos.contains = function(a, i, m) {
      return jQuery(a).text().toUpperCase()
          .indexOf(m[3].toUpperCase()) >= 0;
    };


    jQuery('#inpSearchBtn').on('click', function(){
       var sSearch = jQuery('#inpSearch').val();
       sSearch = sSearch.split(" ");
       jQuery('#table-coupon-items > tbody:not(:first-child)').hide();
       jQuery.each(sSearch, function(i){
       jQuery('#table-coupon-items > tbody:contains("' + sSearch[i] + '"):not(:first-child)').show();
       });
    });

    // Show/hide stats to prevent it showing randomly
    jQuery('.wcu-button-search-report-admin').on('click', function(){
       jQuery('.loaded-stats-wrapper').css('display', 'block');
    });

  });
  </script>

  <div class="loaded-stats-wrapper">

    <div class="wrap loaded-stats" id="content"  style="margin: 0;">

      <!-- Data -->
      <script>
      jQuery(document).ready(function(){

        jQuery('.ordersfilterbutton').on('click', function() {

          jQuery(".wcu-loading-image").show();

          jQuery(".loaded-stats").hide();

          jQuery('.show_data').html('');

          function fetchReports() {
              var response = '';
              var data = {
                  action: 'wcusage_load_admin_reports',
                  _ajax_nonce: '<?php 
        echo esc_html( wp_create_nonce( 'wcusage_admin_ajax_nonce' ) );
        ?>',
                  timestamp: new Date().getTime(),
                  wcu_orders_start: jQuery('input[name=wcu_monthly_orders_start]').val(),
                  wcu_orders_end: jQuery('input[name=wcu_monthly_orders_end]').val(),
                  <?php 
        ?>
                  <?php 
        if ( !wcu_fs()->can_use_premium_code() ) {
            ?>
                    wcu_orders_start_compare: "",
                    wcu_orders_end_compare: "",
                    wcu_compare: "",
                    wcu_orders_filtercompare_type: "",
                    wcu_orders_filtercompare_amount: "",
                  <?php 
        }
        ?>
                    wcu_orders_filterusage_type: jQuery('select[name=wcu_orders_filterusage_type]').val(),
                    wcu_orders_filterusage_amount: jQuery('input[name=wcu_orders_filterusage_amount]').val(),
                    wcu_orders_filtersales_type: jQuery('select[name=wcu_orders_filtersales_type]').val(),
                    wcu_orders_filtersales_amount: jQuery('input[name=wcu_orders_filtersales_amount]').val(),
                    wcu_orders_filtercommission_type: jQuery('select[name=wcu_orders_filtercommission_type]').val(),
                    wcu_orders_filtercommission_amount: jQuery('input[name=wcu_orders_filtercommission_amount]').val(),
                    wcu_orders_filterconversions_type: jQuery('select[name=wcu_orders_filterconversions_type]').val(),
                    wcu_orders_filterconversions_amount: jQuery('input[name=wcu_orders_filterconversions_amount]').val(),
                    wcu_orders_filterunpaid_type: jQuery('select[name=wcu_orders_filterunpaid_type]').val(),
                    wcu_orders_filterunpaid_amount: jQuery('input[name=wcu_orders_filterunpaid_amount]').val(),
                    wcu_report_users_only: jQuery('input[name=wcu_report_users_only]').prop('checked'),
                    wcu_report_user_roles: jQuery('input[name="wcu_report_user_roles[]"]:checked').map(function(){ return this.value; }).get(),
                    wcu_report_show_sales: jQuery('input[name=wcu_report_show_sales]').prop('checked'),
                    wcu_report_show_commission: jQuery('input[name=wcu_report_show_commission]').prop('checked'),
                    wcu_report_show_url: jQuery('input[name=wcu_report_show_url]').prop('checked'),
                    wcu_report_show_products: jQuery('input[name=wcu_report_show_products]').prop('checked')
              };

              jQuery.ajax({
                  type: 'POST',
                  url: ajaxurl,
                  data: data,
                  beforeSend: function() {
                      jQuery(".wcu-loading-image").show();
                  },
                  success: function(response){
                    jQuery('.show_data').append(response.html);
                    if(typeof window.wcu_calculate_stats == 'function') {
                        window.wcu_calculate_stats();
                    } else {
                        jQuery(".wcu-loading-image").hide();
                        jQuery(".loaded-stats").show();
                    }
                  },
                  error: function(xhr, status, error) {
                      console.error("Error loading reports: " + error);
                  }
              });
          }

          // Start fetching when the "Generate Report" button is clicked
          jQuery(".show_data").html(""); // Clear previous results
          fetchReports(); // Start fetching data

        });

      });
      </script>

      <div class="show_data"></div>

    </div>

  </div>

  <?php 
    }

}
/**
* Gets the admin reports data for the values submitted via the create report form
*
* @param date $wcu_orders_start
* @param date $wcu_orders_end
* @param date $wcu_orders_start_compare
* @param date $wcu_orders_end_compare
* @param bool $wcu_compare
* @param string $wcu_orders_filtercompare_type
* @param int $wcu_orders_filtercompare_amount
* @param string $wcu_orders_filterusage_type
* @param int $wcu_orders_filterusage_amount
* @param string $wcu_orders_filtersales_type
* @param int $wcu_orders_filtersales_amount
* @param string $wcu_orders_filtercommission_type
* @param int $wcu_orders_filtercommission_amount
* @param string $wcu_orders_filterconversions_type
* @param int $wcu_orders_filterconversions_amount
* @param string $wcu_orders_filterunpaid_type
* @param int $wcu_orders_filterunpaid_amount
* @param bool $wcu_report_users_only
*
* @return mixed
*
*/
add_action(
    'wcusage_hook_get_admin_report_data',
    'wcusage_get_admin_report_data',
    10,
    25
);
if ( !function_exists( 'wcusage_get_admin_report_data' ) ) {
    function wcusage_get_admin_report_data(
        $wcu_orders_start,
        $wcu_orders_end,
        $wcu_orders_start_compare,
        $wcu_orders_end_compare,
        $wcu_compare,
        $wcu_orders_filtercompare_type,
        $wcu_orders_filtercompare_amount,
        $wcu_orders_filterusage_type,
        $wcu_orders_filterusage_amount,
        $wcu_orders_filtersales_type,
        $wcu_orders_filtersales_amount,
        $wcu_orders_filtercommission_type,
        $wcu_orders_filtercommission_amount,
        $wcu_orders_filterconversions_type,
        $wcu_orders_filterconversions_amount,
        $wcu_orders_filterunpaid_type,
        $wcu_orders_filterunpaid_amount,
        $wcu_report_users_only,
        $wcu_report_user_roles,
        $wcu_report_show_sales,
        $wcu_report_show_commission,
        $wcu_report_show_url,
        $wcu_report_show_products
    ) {
        global $wpdb;
        $options = get_option( 'wcusage_options' );
        // Clear all the previous output
        ob_clean();
        if ( !$wcu_compare ) {
            $wcu_compare = "false";
        }
        // Free version date restrictions
        if ( !wcu_fs()->can_use_premium_code() ) {
            if ( strtotime( $wcu_orders_start ) < strtotime( "-1 month" ) || !$wcu_orders_start ) {
                $wcu_orders_start = date( "Y-m-d", strtotime( "-1 month" ) );
            }
            if ( strtotime( $wcu_orders_end ) > strtotime( 'now' ) || !$wcu_orders_end ) {
                $wcu_orders_end = date( "Y-m-d" );
            }
        }
        // Comparison text
        if ( $wcu_orders_filtercompare_type == "both" ) {
            $comparetypetext = "have increased or decreased";
        } elseif ( $wcu_orders_filtercompare_type == "more" ) {
            $comparetypetext = "have increased";
        } elseif ( $wcu_orders_filtercompare_type == "less" ) {
            $comparetypetext = "have decreased";
        }
        // Report details message
        $reportscripthtml = "<div class='report-complete-box'><h2>" . esc_html__( "Report Complete!", "woo-coupon-usage" ) . "</h2><p>";
        $reportscripthtml .= "<i class='fas fa-check-circle'></i> " . esc_html__( "Report created for", "woo-coupon-usage" ) . " " . date_i18n( 'j F Y', strtotime( $wcu_orders_start ) ) . " to " . date_i18n( 'j F Y', strtotime( $wcu_orders_end ) );
        if ( $wcu_compare == "true" ) {
            $reportscripthtml .= "<br/><i class='fas fa-check-circle'></i> " . esc_html__( "Comparing with date period", "woo-coupon-usage" ) . " " . date_i18n( 'F j, Y', strtotime( $wcu_orders_start_compare ) ) . " " . esc_html__( "to", "woo-coupon-usage" ) . " " . date_i18n( 'F j, Y', strtotime( $wcu_orders_end_compare ) );
            if ( $wcu_orders_filtercompare_type != "both" || $wcu_orders_filtercompare_amount != 0 ) {
                $reportscripthtml .= "<br/><i class='fas fa-check-circle'></i> " . esc_html__( "Showing coupons where sales have", "woo-coupon-usage" ) . " " . $comparetypetext . " " . esc_html__( "by more than", "woo-coupon-usage" ) . " " . $wcu_orders_filtercompare_amount . "%.";
            }
        }
        $arrayextrafilters = [
            [
                esc_html__( "total usage", "woo-coupon-usage" ),
                $wcu_orders_filterusage_type,
                $wcu_orders_filterusage_amount,
                $wcu_orders_filterusage_amount
            ],
            [
                esc_html__( "total sales", "woo-coupon-usage" ),
                $wcu_orders_filtersales_type,
                $wcu_orders_filtersales_amount,
                wcusage_get_currency_symbol() . $wcu_orders_filtersales_amount
            ],
            [
                esc_html__( "commission earned", "woo-coupon-usage" ),
                $wcu_orders_filtercommission_type,
                $wcu_orders_filtercommission_amount,
                wcusage_get_currency_symbol() . $wcu_orders_filtercommission_amount
            ],
            [
                esc_html__( "unpaid commission", "woo-coupon-usage" ),
                $wcu_orders_filterunpaid_type,
                $wcu_orders_filterunpaid_amount,
                wcusage_get_currency_symbol() . $wcu_orders_filterunpaid_amount
            ],
            [
                esc_html__( "URL conversion rate", "woo-coupon-usage" ),
                $wcu_orders_filterconversions_type,
                $wcu_orders_filterconversions_amount,
                $wcu_orders_filterconversions_amount . "%"
            ]
        ];
        foreach ( $arrayextrafilters as $filter ) {
            if ( !($filter[1] == "more or equal" && $filter[2] == 0) ) {
                $reportscripthtml .= "<br/><i class='fas fa-check-circle'></i> " . esc_html__( "Showing coupons where", "woo-coupon-usage" ) . " " . $filter[0] . " " . esc_html__( "is", "woo-coupon-usage" ) . " " . $filter[1] . " " . esc_html__( "than", "woo-coupon-usage" ) . " " . $filter[3] . ".";
            }
        }
        if ( $wcu_report_users_only == "true" ) {
            $reportscripthtml .= "<br/><i class='fas fa-check-circle'></i> " . sprintf( esc_html__( "Only showing coupons that are assigned to an %s user.", "woo-coupon-usage" ), strtolower( wcusage_get_affiliate_text( __( 'affiliate', 'woo-coupon-usage' ) ) ) );
            if ( !empty( $wcu_report_user_roles ) ) {
                $role_names = [];
                global $wp_roles;
                $all_roles = $wp_roles->get_names();
                foreach ( $wcu_report_user_roles as $role ) {
                    $role_names[] = $all_roles[$role] ?? $role;
                }
                $reportscripthtml .= "<br/><i class='fas fa-check-circle'></i> " . esc_html__( "Filtered by user roles:", "woo-coupon-usage" ) . " " . implode( ", ", $role_names );
            }
        }
        $reportscripthtml .= "</p></div>";
        // Update report title
        ?>
  <script>
  jQuery(document).ready(function(){
    jQuery("#report-complete-title").html(<?php 
        echo wp_json_encode( $reportscripthtml );
        ?>);
  });
  </script>

  <!-- Styles to Show/Hide Sections -->
  <?php 
        if ( $wcu_report_show_sales == "false" ) {
            ?>
  <style>.wcusage-reports-stats-section-sales { display: none !important; }</style>
  <?php 
        }
        ?>
  <?php 
        if ( $wcu_report_show_commission == "false" ) {
            ?>
  <style>.wcusage-reports-stats-section-commission { display: none !important; }</style>
  <?php 
        }
        ?>
  <?php 
        if ( $wcu_report_show_url == "false" ) {
            ?>
  <style>.wcusage-reports-stats-section-url { display: none !important; }</style>
  <?php 
        }
        ?>
  <?php 
        if ( $wcu_compare == "true" ) {
            ?>
  <style>.all-time-previous, .all-time-side-text { display: block !important; }</style>
  <?php 
        }
        ?>

  <?php 
        $wcusage_field_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', 1 );
        // Get coupons for the batch
        $args = [
            'posts_per_page'  => -1,
            'orderby'         => 'title',
            'order'           => 'asc',
            'post_type'       => 'shop_coupon',
            'post_status'     => 'publish',
            'query_timestamp' => time(),
        ];
        if ( $wcu_report_users_only == "true" ) {
            $args['meta_query'] = [[
                'key'     => 'wcu_select_coupon_user',
                'value'   => '',
                'compare' => '!=',
            ]];
        }
        $coupons = get_posts( $args );
        $coupons = array_unique( $coupons, SORT_REGULAR );
        // Filter coupons by user roles if specified
        if ( $wcu_report_users_only == "true" && !empty( $wcu_report_user_roles ) ) {
            $filtered_coupons = [];
            foreach ( $coupons as $coupon ) {
                $user_id = get_post_meta( $coupon->ID, 'wcu_select_coupon_user', true );
                if ( $user_id ) {
                    $user = get_userdata( $user_id );
                    if ( $user && array_intersect( $wcu_report_user_roles, $user->roles ) ) {
                        $filtered_coupons[] = $coupon;
                    }
                }
            }
            $coupons = $filtered_coupons;
        }
        // Initialize coupon stats array
        $coupon_stats = [];
        foreach ( $coupons as $coupon ) {
            $coupon_code = strtolower( $coupon->post_title );
            $coupon_stats[$coupon_code] = [
                'id'                       => $coupon->ID,
                'total_count'              => 0,
                'total_orders'             => 0.0,
                'total_commission'         => 0.0,
                'full_discount'            => 0.0,
                'list_of_products'         => [],
                'total_count_compare'      => 0,
                'total_orders_compare'     => 0.0,
                'total_commission_compare' => 0.0,
                'full_discount_compare'    => 0.0,
                'clickcount'               => 0,
                'convertedcount'           => 0,
                'conversionrate'           => 0,
                'clickcount_compare'       => 0,
                'convertedcount_compare'   => 0,
                'conversionrate_compare'   => 0,
                'unpaid_commission'        => 0.0,
                'pending_payments'         => 0.0,
                'user_id'                  => 0,
                'uniqueurl'                => '',
            ];
        }
        // Fetch all orders once
        $start_date_gmt = wcusage_convert_date_to_gmt( $wcu_orders_start, 0 );
        $end_date_gmt = wcusage_convert_date_to_gmt( $wcu_orders_end, 1 );
        $wcusage_field_order_type_custom = wcusage_get_setting_value( 'wcusage_field_order_type_custom', '' );
        if ( !$wcusage_field_order_type_custom ) {
            $statuses = wc_get_order_statuses();
            if ( isset( $statuses['wc-refunded'] ) ) {
                unset($statuses['wc-refunded']);
            }
        } else {
            $statuses = $wcusage_field_order_type_custom;
        }
        $status_list = "'" . implode( "','", array_keys( $statuses ) ) . "'";
        if ( class_exists( 'Automattic\\WooCommerce\\Utilities\\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $id = "id";
            $posts = "wc_orders";
            $postmeta = "wc_orders_meta";
            $post_date = "date_created_gmt";
            $post_status = "status";
            $post_id = "order_id";
        } else {
            $id = "ID";
            $posts = "posts";
            $postmeta = "postmeta";
            $post_date = "post_date_gmt";
            $post_status = "post_status";
            $post_id = "post_id";
        }
        $query = $wpdb->prepare( "SELECT DISTINCT p.{$id} AS order_id, p.{$post_date} AS order_date\r\n      FROM {$wpdb->prefix}{$posts} AS p\r\n      LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi\r\n          ON p.{$id} = woi.order_id AND woi.order_item_type = 'coupon'\r\n      LEFT JOIN {$wpdb->prefix}{$postmeta} AS woi2\r\n          ON p.{$id} = woi2.{$post_id} AND (\r\n              woi2.meta_key = 'lifetime_affiliate_coupon_referrer' OR\r\n              woi2.meta_key = 'wcusage_referrer_coupon'\r\n          )\r\n      WHERE p.{$post_status} IN ({$status_list})\r\n      AND (woi.order_id IS NOT NULL OR woi2.meta_key IS NOT NULL)\r\n      AND p.{$post_date} BETWEEN %s AND %s", $start_date_gmt, $end_date_gmt );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $orders = $wpdb->get_results( $query );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        // Suspend cache addition to prevent memory issues with large order sets
        $previous_cache_state = wp_suspend_cache_addition( true );
        // Process orders for main date range
        foreach ( $orders as $order_data ) {
            try {
                $order_id = $order_data->order_id;
                $order = wc_get_order( $order_id );
                if ( !$order ) {
                    continue;
                }
                // Retrieve meta fields and applied coupons
                $lifetime_coupon = strtolower( get_post_meta( $order_id, 'lifetime_affiliate_coupon_referrer', true ) );
                $referrer_coupon = strtolower( get_post_meta( $order_id, 'wcusage_referrer_coupon', true ) );
                $applied_coupons = array_map( 'strtolower', $order->get_coupon_codes() );
                // Skip if renewal check fails (assuming this is part of your system)
                $renewalcheck = wcusage_check_if_renewal_allowed( $order_id );
                if ( !$renewalcheck ) {
                    continue;
                }
                // Determine which coupon code to use
                if ( $lifetime_coupon && isset( $coupon_stats[$lifetime_coupon] ) ) {
                    $coupon_to_use = $lifetime_coupon;
                } elseif ( $referrer_coupon && isset( $coupon_stats[$referrer_coupon] ) ) {
                    $coupon_to_use = $referrer_coupon;
                } else {
                    // Fallback to applied coupons only if no meta fields are set
                    $relevant_coupons = array_intersect( $applied_coupons, array_keys( $coupon_stats ) );
                    if ( empty( $relevant_coupons ) ) {
                        continue;
                    }
                    foreach ( $relevant_coupons as $coupon_code ) {
                        $calculateorder = wcusage_calculate_order_data(
                            $order_id,
                            $coupon_code,
                            0,
                            1
                        );
                        if ( isset( $calculateorder['totalorders'] ) ) {
                            $coupon_stats[$coupon_code]['total_count'] += 1;
                            $coupon_stats[$coupon_code]['total_orders'] += (float) $calculateorder['totalorders'];
                            $coupon_stats[$coupon_code]['total_commission'] += (float) $calculateorder['totalcommission'];
                            $coupon_stats[$coupon_code]['full_discount'] += (float) $calculateorder['totaldiscounts'];
                        }
                        // Add products to the list of products
                        $product_ids = $order->get_items();
                        foreach ( $product_ids as $product_id => $product_data ) {
                            $product = wc_get_product( $product_data['product_id'] );
                            if ( $product ) {
                                $product_name = $product->get_name();
                            } else {
                                $product_name = esc_html__( 'Unknown Product', 'woo-coupon-usage' );
                            }
                            $product_qty = $product_data['quantity'];
                            if ( !isset( $coupon_stats[$coupon_code]['list_of_products'][$product_name] ) ) {
                                $coupon_stats[$coupon_code]['list_of_products'][$product_name] = 0;
                            }
                            $coupon_stats[$coupon_code]['list_of_products'][$product_name] += $product_qty;
                        }
                    }
                    // Free memory
                    $order = null;
                    continue;
                    // Move to next order after processing applied coupons
                }
                // Add products to the list of products
                $product_ids = $order->get_items();
                foreach ( $product_ids as $product_id => $product_data ) {
                    $product = wc_get_product( $product_data['product_id'] );
                    if ( $product ) {
                        $product_name = $product->get_name();
                    } else {
                        $product_name = esc_html__( 'Unknown Product', 'woo-coupon-usage' );
                    }
                    $product_qty = $product_data['quantity'];
                    if ( !isset( $coupon_stats[$coupon_to_use]['list_of_products'][$product_name] ) ) {
                        $coupon_stats[$coupon_to_use]['list_of_products'][$product_name] = 0;
                    }
                    $coupon_stats[$coupon_to_use]['list_of_products'][$product_name] += $product_qty;
                }
                // Free memory
                $order = null;
            } catch ( Exception $e ) {
                continue;
            } catch ( Throwable $e ) {
                continue;
            }
        }
        // Resume cache addition
        wp_suspend_cache_addition( $previous_cache_state );
        // Fetch orders for comparison date range if applicable
        if ( $wcu_compare == "true" && wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
            $start_date_compare_gmt = wcusage_convert_date_to_gmt( $wcu_orders_start_compare, 0 );
            $end_date_compare_gmt = wcusage_convert_date_to_gmt( $wcu_orders_end_compare, 1 );
            $query_compare = $wpdb->prepare( "SELECT DISTINCT p.{$id} AS order_id, p.{$post_date} AS order_date\r\n          FROM {$wpdb->prefix}{$posts} AS p\r\n          LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi\r\n              ON p.{$id} = woi.order_id AND woi.order_item_type = 'coupon'\r\n          LEFT JOIN {$wpdb->prefix}{$postmeta} AS woi2\r\n              ON p.{$id} = woi2.{$post_id} AND (\r\n                  woi2.meta_key = 'lifetime_affiliate_coupon_referrer' OR\r\n                  woi2.meta_key = 'wcusage_referrer_coupon'\r\n              )\r\n          WHERE p.{$post_status} IN ({$status_list})\r\n          AND (woi.order_id IS NOT NULL OR woi2.meta_key IS NOT NULL)\r\n          AND p.{$post_date} BETWEEN %s AND %s", $start_date_compare_gmt, $end_date_compare_gmt );
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            $orders_compare = $wpdb->get_results( $query_compare );
            // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            // Suspend cache addition to prevent memory issues with large order sets
            $previous_cache_state = wp_suspend_cache_addition( true );
            foreach ( $orders_compare as $order_data ) {
                try {
                    $order_id = $order_data->order_id;
                    $order = wc_get_order( $order_id );
                    if ( !$order ) {
                        continue;
                    }
                    $applied_coupons = array_map( 'strtolower', $order->get_coupon_codes() );
                    $lifetime_coupon = strtolower( get_post_meta( $order_id, 'lifetime_affiliate_coupon_referrer', true ) );
                    $referrer_coupon = strtolower( get_post_meta( $order_id, 'wcusage_referrer_coupon', true ) );
                    $meta_coupons = array_filter( [$lifetime_coupon, $referrer_coupon] );
                    $associated_coupons = array_unique( array_merge( $applied_coupons, $meta_coupons ) );
                    $relevant_coupons = array_intersect( $associated_coupons, array_keys( $coupon_stats ) );
                    foreach ( $relevant_coupons as $coupon_code ) {
                        $calculateorder = wcusage_calculate_order_data(
                            $order_id,
                            $coupon_code,
                            0,
                            1
                        );
                        if ( isset( $calculateorder['totalorders'] ) ) {
                            $coupon_stats[$coupon_code]['total_count_compare'] += 1;
                            $coupon_stats[$coupon_code]['total_orders_compare'] += (float) $calculateorder['totalorders'];
                            $coupon_stats[$coupon_code]['total_commission_compare'] += (float) $calculateorder['totalcommission'];
                            $coupon_stats[$coupon_code]['full_discount_compare'] += (float) $calculateorder['totaldiscounts'];
                        }
                    }
                    // Free memory
                    $order = null;
                } catch ( Exception $e ) {
                    continue;
                } catch ( Throwable $e ) {
                    continue;
                }
            }
            // Resume cache addition
            wp_suspend_cache_addition( $previous_cache_state );
        }
        // Populate additional coupon data
        $stats = [
            'total_usage'        => 0,
            'total_sales'        => 0.0,
            'total_discounts'    => 0.0,
            'total_commission'   => 0.0,
            'unpaid_commission'  => 0.0,
            'pending_commission' => 0.0,
            'total_clicks'       => 0,
            'total_conversions'  => 0,
            'conversion_rate'    => 0,
        ];
        echo "<table id='table-coupon-items'>";
        foreach ( $coupons as $coupon ) {
            $coupon_code = strtolower( $coupon->post_title );
            $coupon_id = $coupon->ID;
            $coupon_info = wcusage_get_coupon_info_by_id( $coupon_id );
            $coupon_stats[$coupon_code]['user_id'] = $coupon_info[1];
            $coupon_stats[$coupon_code]['unpaid_commission'] = ( $coupon_info[2] ?: 0.0 );
            $coupon_stats[$coupon_code]['uniqueurl'] = $coupon_info[4];
            $coupon_stats[$coupon_code]['pending_payments'] = ( get_post_meta( $coupon_id, 'wcu_text_pending_payment_commission', true ) ?: 0.0 );
            $url_stats = wcusage_get_url_stats( $coupon_id, $wcu_orders_start, $wcu_orders_end );
            $coupon_stats[$coupon_code]['clickcount'] = $url_stats['clicks'];
            $coupon_stats[$coupon_code]['convertedcount'] = $url_stats['convertedcount'];
            $coupon_stats[$coupon_code]['conversionrate'] = $url_stats['conversionrate'];
            if ( $wcu_compare == "true" && wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
                $url_stats_compare = wcusage_get_url_stats( $coupon_id, $wcu_orders_start_compare, $wcu_orders_end_compare );
                $coupon_stats[$coupon_code]['clickcount_compare'] = $url_stats_compare['clicks'];
                $coupon_stats[$coupon_code]['convertedcount_compare'] = $url_stats_compare['convertedcount'];
                $coupon_stats[$coupon_code]['conversionrate_compare'] = $url_stats_compare['conversionrate'];
            }
            // Calculate differences for comparison
            $diff_total_count = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['total_count'], $coupon_stats[$coupon_code]['total_count_compare'], false ) : '' );
            $diff_total_orders = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['total_orders'], $coupon_stats[$coupon_code]['total_orders_compare'], true ) : '' );
            $diff_total_commission = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['total_commission'], $coupon_stats[$coupon_code]['total_commission_compare'], true ) : '' );
            $diff_full_discount = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['full_discount'], $coupon_stats[$coupon_code]['full_discount_compare'], true ) : '' );
            $diff_clickcount = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['clickcount'], $coupon_stats[$coupon_code]['clickcount_compare'], false ) : '' );
            $diff_convertedcount = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['convertedcount'], $coupon_stats[$coupon_code]['convertedcount_compare'], false ) : '' );
            $diff_conversionrate = ( $wcu_compare == "true" ? wcusage_getPercentageChange2( $coupon_stats[$coupon_code]['conversionrate'], $coupon_stats[$coupon_code]['conversionrate_compare'], false ) : '' );
            $diff_total_orders_num = ( $wcu_compare == "true" ? wcusage_getPercentageChangeNum( $coupon_stats[$coupon_code]['total_orders'], $coupon_stats[$coupon_code]['total_orders_compare'] ) : 0 );
            // User data
            $usernamefull = ( $coupon_stats[$coupon_code]['user_id'] && ($user_info = get_userdata( $coupon_stats[$coupon_code]['user_id'] )) ? $user_info->user_login : "---" );
            $username = ( $usernamefull === "---" ? "---" : mb_strimwidth(
                $usernamefull,
                0,
                14,
                "..."
            ) );
            // Apply filters
            $checkshowthis = true;
            if ( $wcu_report_users_only == "true" && !$coupon_stats[$coupon_code]['user_id'] ) {
                $checkshowthis = false;
            }
            if ( $wcu_compare == "true" && wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
                if ( $wcu_orders_filtercompare_type == "more" && $wcu_orders_filtercompare_amount >= $diff_total_orders_num ) {
                    $checkshowthis = false;
                }
                if ( $wcu_orders_filtercompare_type == "less" && -abs( $wcu_orders_filtercompare_amount ) <= $diff_total_orders_num ) {
                    $checkshowthis = false;
                }
            }
            // Usage filters
            if ( $wcu_orders_filterusage_type == "more" && $coupon_stats[$coupon_code]['total_count'] <= $wcu_orders_filterusage_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterusage_type == "more or equal" && $coupon_stats[$coupon_code]['total_count'] < $wcu_orders_filterusage_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterusage_type == "less" && $coupon_stats[$coupon_code]['total_count'] >= $wcu_orders_filterusage_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterusage_type == "less or equal" && $coupon_stats[$coupon_code]['total_count'] > $wcu_orders_filterusage_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterusage_type == "equal" && $coupon_stats[$coupon_code]['total_count'] != $wcu_orders_filterusage_amount ) {
                $checkshowthis = false;
            }
            // Sales filters
            if ( $wcu_orders_filtersales_type == "more" && $coupon_stats[$coupon_code]['total_orders'] <= $wcu_orders_filtersales_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtersales_type == "more or equal" && $coupon_stats[$coupon_code]['total_orders'] < $wcu_orders_filtersales_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtersales_type == "less" && $coupon_stats[$coupon_code]['total_orders'] >= $wcu_orders_filtersales_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtersales_type == "less or equal" && $coupon_stats[$coupon_code]['total_orders'] > $wcu_orders_filtersales_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtersales_type == "equal" && $coupon_stats[$coupon_code]['total_orders'] != $wcu_orders_filtersales_amount ) {
                $checkshowthis = false;
            }
            // Commission filters
            if ( $wcu_orders_filtercommission_type == "more" && $coupon_stats[$coupon_code]['total_commission'] <= $wcu_orders_filtercommission_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtercommission_type == "more or equal" && $coupon_stats[$coupon_code]['total_commission'] < $wcu_orders_filtercommission_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtercommission_type == "less" && $coupon_stats[$coupon_code]['total_commission'] >= $wcu_orders_filtercommission_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtercommission_type == "less or equal" && $coupon_stats[$coupon_code]['total_commission'] > $wcu_orders_filtercommission_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filtercommission_type == "equal" && $coupon_stats[$coupon_code]['total_commission'] != $wcu_orders_filtercommission_amount ) {
                $checkshowthis = false;
            }
            // Conversion rate filters
            if ( $wcu_orders_filterconversions_type == "more" && round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) <= $wcu_orders_filterconversions_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterconversions_type == "more or equal" && round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) < $wcu_orders_filterconversions_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterconversions_type == "less" && round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) >= $wcu_orders_filterconversions_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterconversions_type == "less or equal" && round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) > $wcu_orders_filterconversions_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterconversions_type == "equal" && round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) != $wcu_orders_filterconversions_amount ) {
                $checkshowthis = false;
            }
            // Unpaid commission filters
            if ( $wcu_orders_filterunpaid_type == "more" && $coupon_stats[$coupon_code]['unpaid_commission'] <= $wcu_orders_filterunpaid_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterunpaid_type == "more or equal" && $coupon_stats[$coupon_code]['unpaid_commission'] < $wcu_orders_filterunpaid_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterunpaid_type == "less" && $coupon_stats[$coupon_code]['unpaid_commission'] >= $wcu_orders_filterunpaid_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterunpaid_type == "less or equal" && $coupon_stats[$coupon_code]['unpaid_commission'] > $wcu_orders_filterunpaid_amount ) {
                $checkshowthis = false;
            }
            if ( $wcu_orders_filterunpaid_type == "equal" && $coupon_stats[$coupon_code]['unpaid_commission'] != $wcu_orders_filterunpaid_amount ) {
                $checkshowthis = false;
            }
            // Display data if it passes filters
            if ( $checkshowthis ) {
                $stats['total_usage'] += $coupon_stats[$coupon_code]['total_count'];
                $stats['total_sales'] += $coupon_stats[$coupon_code]['total_orders'];
                $stats['total_discounts'] += $coupon_stats[$coupon_code]['full_discount'];
                $stats['total_commission'] += $coupon_stats[$coupon_code]['total_commission'];
                $stats['unpaid_commission'] += $coupon_stats[$coupon_code]['unpaid_commission'];
                $stats['pending_commission'] += $coupon_stats[$coupon_code]['pending_payments'];
                $stats['total_clicks'] += $coupon_stats[$coupon_code]['clickcount'];
                $stats['total_conversions'] += $coupon_stats[$coupon_code]['convertedcount'];
                ?>
          <tbody class="coupon-item-box"
              data-usage="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['total_count'] );
                ?>"
              data-orders="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['total_orders'] );
                ?>"
              data-commission="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['total_commission'] );
                ?>"
              data-discounts="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['full_discount'] );
                ?>"
              data-unpaid="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['unpaid_commission'] );
                ?>"
              data-pending="<?php 
                echo esc_attr( $coupon_stats[$coupon_code]['pending_payments'] );
                ?>"
          >
              <tr class="coupon-data-row" style="padding: 20px 15px 0 15px;">
                  <td colspan="7">
                      <span class="wcu-coupon-name" style="font-size: 20px; margin-bottom: 10px; display: block; font-weight: bold;">
                          <a href="<?php 
                echo esc_html( $coupon_stats[$coupon_code]['uniqueurl'] );
                ?>" target="_blank" style="text-decoration: none;" title="<?php 
                echo sprintf( esc_html__( "View %s Dashboard", "woo-coupon-usage" ), wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) );
                ?>"><?php 
                echo esc_html( $coupon_code );
                ?></a>
                          <span style="font-size: 10px;"><a href="<?php 
                echo esc_url( get_edit_post_link( $coupon_id ) );
                ?>" target="_blank" title="<?php 
                echo esc_html__( "Edit Coupon", "woo-coupon-usage" );
                ?>"><i class="fas fa-edit"></i></a></span>
                      </span>
                  </td>
              </tr>
              <tr class="coupon-data-row-head" style="padding: 0 15px 0px 15px; margin: 0px 0;">
                  <td class="wcu-r-td wcu-r-td-id" style="min-width: 90px;"><?php 
                echo esc_html__( "Coupon ID", "woo-coupon-usage" );
                ?> <a class="hide-col-id wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                  <td class="wcu-r-td wcu-r-td-120 wcu-r-td-affiliate"><?php 
                echo esc_html__( "Affiliate User", "woo-coupon-usage" );
                ?> <a class="hide-col-affiliate wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                  <?php 
                if ( $wcu_report_show_sales != "false" ) {
                    ?>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-usage"><?php 
                    echo esc_html__( "Usage", "woo-coupon-usage" );
                    ?> <a class="hide-col-usage wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-sales"><?php 
                    echo esc_html__( "Sales", "woo-coupon-usage" );
                    ?> <a class="hide-col-sales wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-discounts"><?php 
                    echo esc_html__( "Discounts", "woo-coupon-usage" );
                    ?> <a class="hide-col-discounts wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                  <?php 
                }
                ?>
                  <?php 
                if ( $wcu_report_show_commission != "false" ) {
                    ?>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-commission"><?php 
                    echo esc_html__( "Commission", "woo-coupon-usage" );
                    ?> <a class="hide-col-commission wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <?php 
                    if ( $wcusage_field_tracking_enable && wcu_fs()->can_use_premium_code() ) {
                        ?>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-unpaid"><?php 
                        echo esc_html__( "Unpaid Commission", "woo-coupon-usage" );
                        ?> <a class="hide-col-unpaid wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-pending"><?php 
                        echo esc_html__( "Pending Payout", "woo-coupon-usage" );
                        ?> <a class="hide-col-pending wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <?php 
                    }
                    ?>
                  <?php 
                }
                ?>
                           </tr>
              <tr class="coupon-data-row coupon-data-row-main" style="padding: 0 15px 0px 15px; margin: 0 0 20px 0;">
                  <td class="wcu-r-td wcu-r-td-120 wcu-r-td-id coupon-data-row-head-mobile excludeThisClassExport"><?php 
                echo esc_html__( "Coupon ID", "woo-coupon-usage" );
                ?></td>
                  <td class="wcu-r-td wcu-r-td-id" style="min-width: 90px;"><?php 
                echo esc_html( $coupon_id );
                ?></td>
                  <td class="wcu-r-td wcu-r-td-120 wcu-r-td-affiliate coupon-data-row-head-mobile excludeThisClassExport"><?php 
                echo sprintf( esc_html__( "%s User", "woo-coupon-usage" ), esc_html( wcusage_get_affiliate_text( __( 'Affiliate', 'woo-coupon-usage' ) ) ) );
                ?></td>
                  <td class="wcu-r-td wcu-r-td-120 wcu-r-td-affiliate"><span title="<?php 
                echo esc_html( $usernamefull );
                ?>"><?php 
                echo esc_html( $username );
                ?></span></td>
                  <?php 
                if ( $wcu_report_show_sales != "false" ) {
                    ?>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-usage coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Usage", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-usage" id="total-usage-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <span class="item-total-usage"><?php 
                    echo esc_html( $coupon_stats[$coupon_code]['total_count'] );
                    ?></span>
                          <span class="item-total-usage-old" style="display: none;"><?php 
                    echo esc_html( $coupon_stats[$coupon_code]['total_count_compare'] );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_total_count );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-sales coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Sales", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-sales" id="total-sales-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <?php 
                    echo wp_kses_post( wcusage_get_currency_symbol() );
                    ?><span class="item-total-sales"><?php 
                    echo esc_html( str_replace( ',', '', number_format( (float) $coupon_stats[$coupon_code]['total_orders'], 2 ) ) );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_total_orders );
                        ?></span><span class="item-total-sales-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['total_orders_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-discounts coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Discounts", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-discounts" id="total-discounts-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <?php 
                    echo wp_kses_post( wcusage_get_currency_symbol() );
                    ?><span class="item-total-discounts"><?php 
                    echo esc_html( str_replace( ',', '', number_format( (float) $coupon_stats[$coupon_code]['full_discount'], 2 ) ) );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_full_discount );
                        ?></span><span class="item-total-discounts-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['full_discount_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                  <?php 
                }
                ?>
                  <?php 
                if ( $wcu_report_show_commission != "false" ) {
                    ?>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-commission coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Commission", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-commission" id="total-commission-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <?php 
                    echo wp_kses_post( wcusage_get_currency_symbol() );
                    ?><span class="item-total-commission"><?php 
                    echo esc_html( str_replace( ',', '', number_format( (float) $coupon_stats[$coupon_code]['total_commission'], 2 ) ) );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_total_commission );
                        ?></span><span class="item-total-commission-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['total_commission_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                      <?php 
                    if ( $wcusage_field_tracking_enable && wcu_fs()->can_use_premium_code() ) {
                        ?>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-unpaid coupon-data-row-head-mobile excludeThisClassExport"><?php 
                        echo esc_html__( "Unpaid Commission", "woo-coupon-usage" );
                        ?></td>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-unpaid"><?php 
                        echo wp_kses_post( wcusage_get_currency_symbol() );
                        ?><span class="item-unpaid-commission"><?php 
                        echo esc_html( str_replace( ',', '', number_format( (float) $coupon_stats[$coupon_code]['unpaid_commission'], 2 ) ) );
                        ?></span></td>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-pending coupon-data-row-head-mobile excludeThisClassExport"><?php 
                        echo esc_html__( "Pending Payout", "woo-coupon-usage" );
                        ?></td>
                          <td class="wcu-r-td wcu-r-td-120 wcu-r-td-pending"><?php 
                        echo wp_kses_post( wcusage_get_currency_symbol() );
                        ?><span class="item-pending-commission"><?php 
                        echo esc_html( str_replace( ',', '', number_format( (float) $coupon_stats[$coupon_code]['pending_payments'], 2 ) ) );
                        ?></span></td>
                      <?php 
                    }
                    ?>
                  <?php 
                }
                ?>
              </tr>
              <?php 
                if ( $wcu_report_show_url != "false" ) {
                    ?>
                  <tr class="coupon-data-row-head wcu-r-td-products" style="padding: 0 15px 0px 15px; margin: 0px 0;">
                      <td class="wcu-r-td wcu-r-td-120 break wcu-r-td-clicks"><?php 
                    echo esc_html__( "Referral URL Clicks", "woo-coupon-usage" );
                    ?> <a class="hide-col-clicks wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <td class="wcu-r-td wcu-r-td-120 break wcu-r-td-conversions"><?php 
                    echo esc_html__( "Referral URL Conversions", "woo-coupon-usage" );
                    ?> <a class="hide-col-conversions wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                      <td class="wcu-r-td wcu-r-td-120 break wcu-r-td-conversion-rate"><?php 
                    echo esc_html__( "Conversion Rate", "woo-coupon-usage" );
                    ?> <a class="hide-col-conversion-rate wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                  </tr>
                  <tr class="coupon-data-row coupon-data-row-main" style="padding: 0 15px 0px 15px; margin: 0 0 20px 0;">
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-clicks coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Referral URL Clicks", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-clicks" id="total-clicks-<?php 
                    echo esc_attr( $coupon_id );
                    ?>">
                          <span class="item-total-clicks"><?php 
                    echo esc_html( $coupon_stats[$coupon_code]['clickcount'] );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_clickcount );
                        ?></span><span class="item-total-clicks-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['clickcount_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-conversions coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Referral URL Conversions", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-conversions" id="total-conversions-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <span class="item-total-conversions"><?php 
                    echo esc_html( $coupon_stats[$coupon_code]['convertedcount'] );
                    ?></span>
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_convertedcount );
                        ?></span><span class="item-total-conversions-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['convertedcount_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-conversion-rate coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Conversion Rate", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-conversion-rate" id="total-conversion-rate-<?php 
                    echo esc_html( $coupon_id );
                    ?>">
                          <span class="item-total-conversion-rate"><?php 
                    echo esc_html( round( $coupon_stats[$coupon_code]['conversionrate'], 2 ) );
                    ?></span>%
                          <?php 
                    if ( $wcu_compare == "true" ) {
                        ?><br/><span style="font-size: 10px;"><?php 
                        echo wp_kses_post( $diff_conversionrate );
                        ?></span><span class="item-total-conversion-rate-old" style="display: none;"><?php 
                        echo esc_html( $coupon_stats[$coupon_code]['conversionrate_compare'] );
                        ?></span><?php 
                    }
                    ?>
                      </td>
                  </tr>
              <?php 
                }
                ?>
              <?php 
                if ( $wcu_report_show_products != "false" ) {
                    ?>
                  <tr class="coupon-data-row-head wcu-r-td-products" style="padding: 0 15px 0px 15px; margin: 0px 0;">
                      <td class="wcu-r-td wcu-r-td-120 break wcu-r-td-products"><?php 
                    echo esc_html__( "Products", "woo-coupon-usage" );
                    ?> <a class="hide-col-products wcu-hide-col" href="#" onclick="return false;" title="Remove Column"><i class="fas fa-times"></i></a></td>
                  </tr>
                  <tr class="coupon-data-row coupon-data-row-main wcu-r-td-products" style="padding: 0 15px 20px 15px; margin: 0;">
                      <td class="wcu-r-td wcu-r-td-120 wcu-r-td-products coupon-data-row-head-mobile excludeThisClassExport"><?php 
                    echo esc_html__( "Products", "woo-coupon-usage" );
                    ?></td>
                      <td class="wcu-r-td break wcu-r-td-products">
                          <?php 
                    if ( $coupon_stats[$coupon_code]['list_of_products'] ) {
                        foreach ( $coupon_stats[$coupon_code]['list_of_products'] as $key => $value ) {
                            $product_name = $key;
                            echo "• " . esc_html( $value ) . " x " . esc_html( $product_name ) . "<br/>";
                        }
                    } else {
                        echo "0 products sold";
                    }
                    ?>
                      </td>
                  </tr>
              <?php 
                }
                ?>
          </tbody>
          <?php 
            }
        }
        // Output total stats as hidden fields
        echo '<input class="final-total-usage" value="' . esc_attr( $stats['total_usage'] ) . '" style="display: none;">';
        echo '<input class="final-total-sales" value="' . esc_attr( $stats['total_sales'] ) . '" style="display: none;">';
        echo '<input class="final-total-discounts" value="' . esc_attr( $stats['total_discounts'] ) . '" style="display: none;">';
        echo '<input class="final-total-commission" value="' . esc_attr( $stats['total_commission'] ) . '" style="display: none;">';
        echo '<input class="final-unpaid-commission" value="' . esc_attr( $stats['unpaid_commission'] ) . '" style="display: none;">';
        echo '<input class="final-pending-commission" value="' . esc_attr( $stats['pending_commission'] ) . '" style="display: none;">';
        echo '<input class="final-total-clicks" value="' . esc_attr( $stats['total_clicks'] ) . '" style="display: none;">';
        echo '<input class="final-total-conversions" value="' . esc_attr( $stats['total_conversions'] ) . '" style="display: none;">';
        $stats['conversion_rate'] = ( $stats['total_clicks'] > 0 ? round( $stats['total_conversions'] / $stats['total_clicks'] * 100, 2 ) : 0 );
        echo '<input class="final-total-conversion-rate" value="' . esc_attr( $stats['conversion_rate'] ) . '" style="display: none;">';
        ?>
  </table>

  <?php 
        do_action( 'wcusage_hook_get_admin_report_scripts_sorting' );
        do_action( 'wcusage_hook_get_admin_report_scripts_remove_row' );
    }

}
/**
 * Scripts For Sorting Admin Reports
 *
 */
if ( !function_exists( 'wcusage_get_admin_report_scripts_sorting' ) ) {
    function wcusage_get_admin_report_scripts_sorting() {
        ?>
    <script>
    jQuery( "#sort-by-usage" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("usage")-jQuery(a).data("usage")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-usage" ).css("font-weight","Bold");
    });
    jQuery( "#sort-by-orders" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("orders")-jQuery(a).data("orders")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-orders" ).css("font-weight","Bold");
    });
    jQuery( "#sort-by-commission" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("commission")-jQuery(a).data("commission")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-commission" ).css("font-weight","Bold");
    });
    jQuery( "#sort-by-discounts" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("discounts")-jQuery(a).data("discounts")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-discounts" ).css("font-weight","Bold");
    });
    jQuery( "#sort-by-unpaid" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("unpaid")-jQuery(a).data("unpaid")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-unpaid" ).css("font-weight","Bold");
    });
    jQuery( "#sort-by-pending" ).on('click', function() {
      var divList = jQuery(".coupon-item-box");
      divList.sort(function(a, b){
          return jQuery(b).data("pending")-jQuery(a).data("pending")
      });
      jQuery("#table-coupon-items").html(divList);
      jQuery( ".sort-link" ).css("font-weight","normal");
      jQuery( "#sort-by-pending" ).css("font-weight","Bold");
    });
    </script>
  <?php 
    }

}
add_action( 'wcusage_hook_get_admin_report_scripts_sorting', 'wcusage_get_admin_report_scripts_sorting' );
/**
 * Scripts For Remove Cols Buttons
 *
 */
if ( !function_exists( 'wcusage_get_admin_report_scripts_remove_row' ) ) {
    function wcusage_get_admin_report_scripts_remove_row() {
        ?>
    <script>
    jQuery( document ).ready(function() {
      wcu_report_remove_col();
    });
    jQuery( ".sort-link" ).on('click', function() {
      wcu_report_remove_col();
    });

    function wcu_report_remove_col() {

      jQuery( ".hide-col-id" ).on('click', function() {
        jQuery( ".wcu-r-td-id" ).remove();
      });
      jQuery( ".hide-col-affiliate" ).on('click', function() {
        jQuery( ".wcu-r-td-affiliate" ).remove();
      });
      jQuery( ".hide-col-usage" ).on('click', function() {
        jQuery( ".wcu-r-td-usage" ).remove();
      });
      jQuery( ".hide-col-sales" ).on('click', function() {
        jQuery( ".wcu-r-td-sales" ).remove();
      });
      jQuery( ".hide-col-commission" ).on('click', function() {
        jQuery( ".wcu-r-td-commission" ).remove();
      });
      jQuery( ".hide-col-discounts" ).on('click', function() {
        jQuery( ".wcu-r-td-discounts" ).remove();
      });
      jQuery( ".hide-col-unpaid" ).on('click', function() {
        jQuery( ".wcu-r-td-unpaid" ).remove();
      });
      jQuery( ".hide-col-pending" ).on('click', function() {
        jQuery( ".wcu-r-td-pending" ).remove();
      });
      jQuery( ".hide-col-clicks" ).on('click', function() {
        jQuery( ".wcu-r-td-clicks" ).remove();
      });
      jQuery( ".hide-col-conversions" ).on('click', function() {
        jQuery( ".wcu-r-td-conversions" ).remove();
      });
      jQuery( ".hide-col-conversion-rate" ).on('click', function() {
        jQuery( ".wcu-r-td-conversion-rate" ).remove();
      });
      jQuery( ".hide-col-products" ).on('click', function() {
        jQuery( ".wcu-r-td-products" ).remove();
      });

    }
    </script>
  <?php 
    }

}
add_action( 'wcusage_hook_get_admin_report_scripts_remove_row', 'wcusage_get_admin_report_scripts_remove_row' );