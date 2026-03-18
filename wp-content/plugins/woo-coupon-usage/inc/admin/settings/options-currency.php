<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
function wcusage_field_cb_currency(  $args  ) {
    $options = get_option( 'wcusage_options' );
    ?>

	<h1><?php 
    echo esc_html__( 'Multi-Currency Settings', 'woo-coupon-usage' );
    ?></h1>

  <hr/>

	<p>- <?php 
    echo esc_html__( 'In this section, you can manage multi-currency settings, if you are using a multi currency plugin.', 'woo-coupon-usage' );
    ?></p>

  <p>- <?php 
    echo esc_html__( 'This will then automatically convert all the stats, order totals and commission on the affiliate dashboard into your base store currency, even if the order is made in a different currency.', 'woo-coupon-usage' );
    ?></p>

  <p>- <?php 
    echo esc_html__( 'NOTE: Updating the conversion rates below will only update the totals for NEW orders (if the affiliate dashboard has been viewed at-least once).', 'woo-coupon-usage' );
    ?></p>

    <p>
      - <?php 
    printf( wp_kses_post( __( 'To completely refresh each of your affiliate dashboards stats for past orders, with the new conversion rates, %s and click "refresh all data". (The first page load for each dashboard may take slightly longer.)', 'woo-coupon-usage' ) ), '<a href="#" onclick="wcusage_go_to_settings(\'#tab-debug\', \'#wcusage_field_enable_coupon_all_stats_meta_p\');">go to the debug settings tab</a>' );
    ?>
  </p>

  <br/><hr/>

  <?php 
    $wcusage_field_currencies = wcusage_get_setting_value( 'wcusage_field_currencies', '' );
    ?>

  <!-- Main Currency -->
  <?php 
    wcusage_setting_toggle_option(
        'wcusage_field_enable_currency',
        0,
        esc_html__( 'Enable multi currency settings.', 'woo-coupon-usage' ),
        '0px'
    );
    ?>

  <?php 
    wcusage_setting_toggle( '.wcusage_field_enable_currency', '.wcu-field-section-currency' );
    // Show or Hide
    ?>
  <span class="wcu-field-section-currency">

  <br/><br/>

  <!-- Main Currency -->
  <?php 
    $defaultcurrency = get_woocommerce_currency();
    $defaultcurrencysym = get_woocommerce_currency_symbol();
    ?>
  <strong>Base Store Currency:</strong> <?php 
    echo esc_html( $defaultcurrency );
    ?><br/>
  <i><?php 
    echo esc_html__( 'This is the base currency for your store, in which totals/commission will be converted to. You can change this in the WooCommerce settings.', 'woo-coupon-usage' );
    ?></i><br/>

  <br/><br/>

  <!-- Save Rate -->
  <?php 
    wcusage_setting_toggle_option(
        'wcusage_field_enable_currency_save_rate',
        0,
        esc_html__( 'Save the conversion rate for each order.', 'woo-coupon-usage' ),
        '0px'
    );
    ?>
  <i><?php 
    echo esc_html__( 'With this enabled, it will permanently save and use the conversion rate that was set at the time the order is created, even if you update the rates below.', 'woo-coupon-usage' );
    ?></i><br/>
  <i><?php 
    echo esc_html__( '(This is saved as meta data "wcusage_currency_conversion" for the order.)', 'woo-coupon-usage' );
    ?></i><br/>
  <i><?php 
    echo esc_html__( 'Note: When enabled, any existing orders that do not currently have a conversion rate set, will save the rate as the rate set below (when the affiliate dashboard is next loaded).', 'woo-coupon-usage' );
    ?></i><br/>

  <br/><br/>

  <!-- Number of currency -->
  <?php 
    $currencynumber = wcusage_get_setting_value( 'wcusage_field_currency_number', '5' );
    ?>
  <?php 
    wcusage_setting_number_option(
        'wcusage_field_currency_number',
        $currencynumber,
        esc_html__( 'Number of Extra Currencies', 'woo-coupon-usage' ),
        '0px'
    );
    ?>
  <i><?php 
    echo esc_html__( 'Please refresh the page to add/remove the new currency options (found below) when you update this number.', 'woo-coupon-usage' );
    ?></i><br/>

  <br/><hr/>

  <?php 
    if ( isset( $_GET['update_conversion_rates'] ) ) {
        do_action( 'wcusage_hook_update_conversion_rates' );
    }
    // Loop through custom tabs
    for ($i = 1; $i <= $currencynumber; $i++) {
        echo '<h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> Currency #' . esc_html( $i ) . '</h3>';
        $get_default_currency_settings = wcusage_get_default_currency_settings( $i );
        $wcusage_field_currency_name = $get_default_currency_settings['wcusage_field_currency_name'];
        $wcusage_field_currency_rate = $get_default_currency_settings['wcusage_field_currency_rate'];
        ?>
    <div class="input_fields_wrap"></div>
    <span style="display: block; float: left;"><span style="margin-left: 35px; font-size: 12px;">Currency Code:</span><br/> <span style="font-size: 12px;">1.00 x</span> <input type="text" style="max-width: 82px;" id="wcusage_field_currencies_name_<?php 
        echo esc_attr( $i );
        ?>" customid="wcusage_field_currencies" name="wcusage_options[wcusage_field_currencies][<?php 
        echo esc_attr( $i );
        ?>][name]" checktype="customnumber" custom1="<?php 
        echo esc_attr( $i );
        ?>" custom2="name" placeholder="" value="<?php 
        echo esc_attr( $wcusage_field_currency_name );
        ?>"></span>
    <span style="display: block; float: left;"><span style="margin-left: 18px; font-size: 12px;">Conversion:</span><br/>&nbsp;= <input type="number"
    style="max-width: 82px;" lang="en" id="wcusage_field_currencies_rate_<?php 
        echo esc_attr( $i );
        ?>"
    customid="wcusage_field_currencies" name="wcusage_options[wcusage_field_currencies][<?php 
        echo esc_attr( $i );
        ?>][rate]"
    checktype="customnumber" custom1="<?php 
        echo esc_attr( $i );
        ?>" custom2="rate" placeholder="1.00"
    step="0.01" min="0"
    value="<?php 
        echo esc_attr( $wcusage_field_currency_rate );
        ?>" oninput="this.value = this.value.replace(/,/g, '')"> <span style="font-size: 12px;"><?php 
        echo esc_html( $defaultcurrency );
        ?></span></span>
    <div style="clear: both;"></div><br/><hr/>
    <?php 
    }
    ?>

  </span>

  <span <?php 
    if ( !wcu_fs()->can_use_premium_code() ) {
        ?>style="opacity: 0.5; pointer-events: none;"<?php 
    }
    ?>>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php 
    echo esc_html__( 'Automated Conversion Rates', 'woo-coupon-usage' );
    if ( !wcu_fs()->can_use_premium_code() ) {
        ?> (Pro)<?php 
    }
    ?></h3>

    <p><?php 
    echo esc_html__( 'This feature allows you to collect the conversion rates automatically from an Exchange Rates API.', 'woo-coupon-usage' );
    ?></p>

    <p><?php 
    echo esc_html__( 'Exchange rates will be updated automatically every 12 hours, or you can click the button below to update it now.', 'woo-coupon-usage' );
    ?><br/></p>

    <br/>

    <!-- Conversion Rates API -->
    <?php 
    $wcusage_field_exchange_source = wcusage_get_setting_value( 'wcusage_field_exchange_source', '' );
    if ( !$wcusage_field_exchange_source && wcusage_get_setting_value( 'wcusage_field_exchangeratesapi', '' ) ) {
        $wcusage_field_exchange_source = "exchangeratesapi";
    } elseif ( !$wcusage_field_exchange_source && wcusage_get_setting_value( 'wcusage_field_apilayer', '' ) ) {
        $wcusage_field_exchange_source = "apilayer";
    }
    ?>
    
    <strong><?php 
    echo esc_html__( 'Select an API Provider:', 'woo-coupon-usage' );
    ?></strong><br/>

    <select name="wcusage_options[wcusage_field_exchange_source]" id="wcusage_field_exchange_source" class="wcusage_field_exchange_source">
        <option value="">-</option>
        <option value="apilayer" <?php 
    if ( $wcusage_field_exchange_source == "apilayer" ) {
        ?>selected<?php 
    }
    ?>><?php 
    echo esc_html__( 'apilayer.com', 'woo-coupon-usage' ) . " " . esc_html__( '(Recommended)', 'woo-coupon-usage' );
    ?></option>
        <option value="exchangeratesapi" <?php 
    if ( $wcusage_field_exchange_source == "exchangeratesapi" ) {
        ?>selected<?php 
    }
    ?>><?php 
    echo esc_html__( 'exchangeratesapi.io', 'woo-coupon-usage' );
    ?></option>
    </select>
 
    <script type="text/javascript">
    jQuery(document).ready(function($) {
      
        // Hide all divs
        $('.show_field_exchangeratesapi').hide();
        $('.show_field_apilayer').hide();

        // Initial check on page load
        toggleRatesDivs($('#wcusage_field_exchange_source').val());

        // On select change
        $('#wcusage_field_exchange_source').on('change', function() {
            toggleRatesDivs($(this).val());
        });

        function toggleRatesDivs(value) {
            if (value == 'exchangeratesapi') {
                $('.show_field_exchangeratesapi').show();
                $('.show_field_apilayer').hide();
                $('.show_field_currency_button').show();
            } else if (value == 'apilayer') {
                $('.show_field_apilayer').show();
                $('.show_field_exchangeratesapi').hide();
                $('.show_field_currency_button').show();
            } else {
                $('.show_field_exchangeratesapi').hide();
                $('.show_field_apilayer').hide();
                $('.show_field_currency_button').hide();
            }
        }
    });
    </script>

    <br/><br/>

    <div class="show_field_exchangeratesapi">

      <p><?php 
    echo esc_html__( 'Signup for a free "Exchangerates API" account to get your API key', 'woo-coupon-usage' );
    ?>: <a href="https://exchangeratesapi.io/#pricing_plan" target="_blank">https://exchangeratesapi.io</a><br/></p>

      <?php 
    if ( $defaultcurrency != "EUR" ) {
        ?>

      <br/>

      <p style="font-weight: bold;"><?php 
        echo esc_html__( 'Note: Currently only the "EUR" base store currency is supported on the free API plan.', 'woo-coupon-usage' );
        ?></p>
      
      <p style="font-weight: bold;"><?php 
        echo sprintf( esc_html__( 'If you need to use a different base currency (%s), you will need to upgrade to the "Basic" API plan.', 'woo-coupon-usage' ), esc_html( $defaultcurrency ) );
        ?></p>

      <?php 
    }
    ?>

      <br/>

      <?php 
    $exchangeratesapi = wcusage_get_setting_value( 'wcusage_field_exchangeratesapi', '' );
    ?>
      <?php 
    wcusage_setting_text_option(
        'wcusage_field_exchangeratesapi',
        $exchangeratesapi,
        esc_html__( 'Exchangerates API Key', 'woo-coupon-usage' ) . ":",
        '0px'
    );
    ?>
    
    </div>

    <div class="show_field_apilayer">

      <p><?php 
    echo esc_html__( 'Signup for a free "APILayer" account to get your API key', 'woo-coupon-usage' );
    ?>: <a href="https://apilayer.com/marketplace/exchangerates_data-api" target="_blank">https://apilayer.com/marketplace/exchangerates_data-api</a><br/></p>

      <br/>

      <p style="font-weight: bold;"><?php 
    echo esc_html__( 'The APILayer free plan should support all base currencies and 100 requests/month which is all that is required.', 'woo-coupon-usage' );
    ?></p>
      
      <br/>

      <?php 
    $apilayer = wcusage_get_setting_value( 'wcusage_field_apilayer', '' );
    ?>
      <?php 
    wcusage_setting_text_option(
        'wcusage_field_apilayer',
        $apilayer,
        esc_html__( 'APILayer API Key', 'woo-coupon-usage' ) . ":",
        '0px'
    );
    ?>

    </div>

    <!-- Add a form with an 'Update Conversion Rates' button -->
    <br/><a onclick="window.location.href='<?php 
    echo esc_attr( add_query_arg( 'update_conversion_rates', '1' ) );
    ?>';"
    class="show_field_currency_button button button-primary" style="background: green; font-size: 12px; font-weight: bold;"><?php 
    echo esc_html__( 'Get Rates', 'woo-coupon-usage' );
    ?>&nbsp;<span class="dashicons dashicons-update" style="font-size:15px;margin-top:7px;height:12px;width:12px;"></span></a>

  <span>

	</div>

 <?php 
}
