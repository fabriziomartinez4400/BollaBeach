<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
global $wcusage_clicks_db_version;
$wcusage_clicks_db_version = "4";
/**
 * CREATE THE TABLES
 *
 */
if ( !function_exists( 'wcusage_install_clicks_tables' ) ) {
    function wcusage_install_clicks_tables() {
        global $wpdb;
        global $wcusage_clicks_db_version;
        $installed_ver = get_option( "wcusage_clicks_db_version" );
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wcusage_clicks'" ) != $wpdb->prefix . 'wcusage_clicks' ) {
            $installed_ver = 0;
        }
        if ( !$installed_ver || $installed_ver != $wcusage_clicks_db_version ) {
            $table_name = $wpdb->prefix . 'wcusage_clicks';
            $sql = "CREATE TABLE {$table_name} (\r\n\t\t\tid bigint NOT NULL AUTO_INCREMENT,\r\n\t\t\tcouponid text(9) NOT NULL,\r\n\t\t\tcampaign text(9) NOT NULL,\r\n\t\t\tpage text(9) NOT NULL,\r\n\t\t\treferrer text(9) NOT NULL,\r\n\t\t\tipaddress text(9) NOT NULL,\r\n\t\t\torderid text(9) NOT NULL,\r\n\t\t\tconverted boolean DEFAULT false,\r\n\t\t\tdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\r\n\t\t\tPRIMARY KEY  (id)\r\n\t\t\t);";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
            update_option( "wcusage_clicks_db_version", $wcusage_clicks_db_version );
        }
    }

}
/**
 * CHECK IF TABLE IS UP TO DATE
 *
 */
if ( !function_exists( 'wcusage_update_clicks_db_check' ) ) {
    function wcusage_update_clicks_db_check() {
        global $wcusage_clicks_db_version;
        if ( get_site_option( 'wcusage_clicks_db_version' ) != $wcusage_clicks_db_version ) {
            wcusage_install_clicks_tables();
        }
    }

}
add_action( 'plugins_loaded', 'wcusage_update_clicks_db_check' );
/**
 * ADD NEW REFERRAL URL CLICK TO TABLE AND RETURN ID OF CLICK
 *
 * @param int $coupon_id
 * @param string $campaign
 * @param int $page
 * @param string $refpage
 * @param bool $converted
 * @param int $ipaddress
 *
 * @return int
 *
 */
if ( !function_exists( 'wcusage_install_clicks_data' ) ) {
    function wcusage_install_clicks_data(
        $coupon_id,
        $campaign,
        $page,
        $refpage,
        $converted,
        $ipaddress
    ) {
        global $wpdb;
        // Check the table exists, if not, create it
        wcusage_install_clicks_tables();
        // Sanitize all inputs in bulk.
        $data = array_map( 'sanitize_text_field', [
            'coupon_id' => $coupon_id,
            'campaign'  => $campaign,
            'page'      => $page,
            'refpage'   => $refpage,
            'converted' => $converted,
            'ipaddress' => $ipaddress,
        ] );
        // Check for critical data validity.
        if ( empty( $data['coupon_id'] ) || empty( $data['ipaddress'] ) ) {
            // Return false if critical data is missing.
            return false;
        }
        $table_name = $wpdb->prefix . 'wcusage_clicks';
        $insert_data = [
            'couponid'  => $data['coupon_id'],
            'campaign'  => $data['campaign'],
            'page'      => $data['page'],
            'referrer'  => $data['refpage'],
            'converted' => $data['converted'],
            'ipaddress' => $data['ipaddress'],
            'orderid'   => '',
            'date'      => current_time( 'mysql' ),
        ];
        $result = $wpdb->insert( $table_name, $insert_data );
        if ( $result === false ) {
            // Handle the error as needed.
            return false;
        }
        return $wpdb->insert_id;
    }

}
/**
 * HOOK TO DISPLAY CLICKS FOR COUPON & CAMPAIGN ON AFFILIATE DASHBOARD
 *
 * @param int $postid
 * @param string $campaign
 *
 * @return mixed
 *
 */
if ( !function_exists( 'wcusage_display_coupon_url_clicks' ) ) {
    function wcusage_display_coupon_url_clicks(
        $postid,
        $campaign,
        $page = 0,
        $converted = 0
    ) {
        $wcusage_field_show_click_history = wcusage_get_setting_value( 'wcusage_field_show_click_history', 1 );
        $wcusage_field_show_click_history_amount = wcusage_get_setting_value( 'wcusage_field_show_click_history_amount', 15 );
        $wcusage_field_show_click_history_amount = absint( $wcusage_field_show_click_history_amount );
        $wcusage_field_show_campaigns = wcusage_get_setting_value( 'wcusage_field_show_campaigns', 1 );
        $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', 1 );
        $show_converted = wcusage_get_setting_value( 'wcusage_field_show_click_history_converted', 1 );
        $show_converted_col = 1;
        $wcusage_store_cookies = wcusage_get_setting_value( 'wcusage_field_store_cookies', '1' );
        if ( !$wcusage_store_cookies ) {
            $show_converted = 0;
            $show_converted_col = 0;
        }
        $offset = $page * $wcusage_field_show_click_history_amount;
        if ( $campaign && $campaign != "all" ) {
            $campaignline = " AND campaign = '" . $campaign . "'";
        } else {
            $campaignline = "";
        }
        $convertedline = "";
        if ( $converted ) {
            $convertedline = " AND converted = '1'";
        }
        $postid = absint( $postid );
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcusage_clicks';
        $query = "SELECT * FROM {$table_name} WHERE couponid = %d {$campaignline} {$convertedline} ORDER BY id DESC LIMIT %d OFFSET %d";
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $query = $wpdb->prepare(
            $query,
            $postid,
            $wcusage_field_show_click_history_amount,
            $offset
        );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $result2 = $wpdb->get_results( $query );
        // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        $totalresults = count( $result2 );
        ?>

		<!-- Table Mobile Labels -->
		<style>
		@media only screen and (max-width: 760px) {
			.wcu-table-clicks td:nth-of-type(1):before { content: "ID"; }
			.wcu-table-clicks td:nth-of-type(2):before { content: "Landing Page"; }
			.wcu-table-clicks td:nth-of-type(3):before { content: "Referring URL"; }
			.wcu-table-clicks td:nth-of-type(4):before { content: "Converted"; }
			<?php 
        ?>
			.wcu-table-clicks td:nth-of-type(6):before { content: "Date"; }
		}
		</style>

	  <div style="clear: both;"></div>

    <!-- Heading -->
	<p class="wcu-tab-title wcusage-subheader wcusage-title-referral-clicks" style='font-size: 22px; float: left; margin-top: 20px; margin-bottom: 10px;' id='wcu-recent-clicks-section'><?php 
        echo esc_html__( 'Recent Clicks', 'woo-coupon-usage' );
        ?>:</p>

    <!-- Converted only toggle -->
    <?php 
        if ( $wcusage_field_load_ajax && $show_converted ) {
            ?>
    <p style="float: right; font-size: 17px; margin-top: 25px; margin-bottom: 10px;">
      <label for="wcu-checkbox-clicks-converted" style="vertical-align: baseline;">
        <input type="checkbox" id="wcu-checkbox-clicks-converted" name="wcu-checkbox-clicks-converted" value="1" style=""> <?php 
            echo esc_html__( 'Converted Only', 'woo-coupon-usage' );
            ?>
      </label>
    </p>
    <?php 
        }
        ?>

    <div style="clear: both;"></div>

    <!-- Show the clicks table -->
    <?php 
        if ( $totalresults > 0 ) {
            echo "<table class='wcuTable wcu-table-clicks'>";
            echo "<tr class='wcu-thetitlerow'>";
            echo "<td class='wcuTableHead'>#</td>";
            if ( $show_converted_col ) {
                echo "<td class='wcuTableHead'><i class='fas fa-cart-plus' title='" . esc_attr( ucfirst( esc_html__( 'Converted?', 'woo-coupon-usage' ) ) ) . "'></i></td>";
            }
            echo "<td class='wcuTableHead' style='max-width: 300px;'>" . esc_html( ucfirst( esc_html__( 'Landing Page', 'woo-coupon-usage' ) ) ) . "</td>";
            echo "<td class='wcuTableHead' style='max-width: 350px;'>" . esc_html( ucfirst( esc_html__( 'Referring URL', 'woo-coupon-usage' ) ) ) . "</td>";
            echo "<td class='wcuTableHead'>" . ucfirst( esc_html__( 'Date', 'woo-coupon-usage' ) ) . "</td>";
            echo "</tr>";
            foreach ( $result2 as $result ) {
                echo "<tr class='wcuTableRow'>";
                echo "<td class='wcuTableCell' style='max-width: 100%;'>" . esc_html( $result->id ) . "</td>";
                if ( $result->converted == 1 ) {
                    $convertedicon = '<i class="fas fa-check" style="color: green;" title="' . esc_html__( "Converted", "woo-coupon-usage" ) . '"></i>';
                } else {
                    $convertedicon = '<i class="fas fa-times" title="' . esc_html__( "Not Converted", "woo-coupon-usage" ) . '"></i>';
                }
                echo "<td class='wcuTableCell' style='max-width: 100%;'>" . wp_kses_post( $convertedicon ) . "</td>";
                // Display landing page with proper handling for homepage/empty values
                if ( $result->page && $result->page != '0' && $result->page != 0 ) {
                    // Regular page/post
                    $page_title = get_the_title( $result->page );
                    $page_url = get_permalink( $result->page );
                    if ( $page_title && $page_url ) {
                        echo "<td class='wcuTableCell wcuTableCell-ref-landing'><a href='" . esc_url( $page_url ) . "'>" . esc_html( $page_title ) . "</a></td>";
                    } else {
                        echo "<td class='wcuTableCell wcuTableCell-ref-landing'>-</td>";
                    }
                } elseif ( $result->page === '0' || $result->page === 0 || $result->page == '0' ) {
                    // Homepage (blog index)
                    echo "<td class='wcuTableCell wcuTableCell-ref-landing'><a href='" . esc_url( home_url( '/' ) ) . "'>" . esc_html__( 'Homepage', 'woo-coupon-usage' ) . "</a></td>";
                } else {
                    // Empty/not set
                    echo "<td class='wcuTableCell wcuTableCell-ref-landing'>-</td>";
                }
                if ( $result->referrer ) {
                    $referrerurl = "<span style='word-wrap: break-word !important;'>" . $result->referrer . "</span>";
                } else {
                    $referrerurl = 'Direct Traffic';
                }
                echo "<td class='wcuTableCell wcuTableCell-ref-website'>" . wp_kses_post( $referrerurl ) . "</td>";
                $thedatetime = strtotime( $result->date );
                echo "<td class='wcuTableCell' style='max-width: 100%;'>" . ucfirst( date_i18n( get_option( 'date_format' ) . " " . "(" . get_option( 'time_format' ) . ")", $thedatetime ) ) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            if ( $page == 0 ) {
                ?>
				<p><?php 
                echo esc_html__( 'There have been no clicks for this campaign yet.', 'woo-coupon-usage' );
                ?></p>
				<script>
				jQuery(document).ready(function(){
					jQuery( ".wcu-clicks-pagination" ).css({
						"display": "none !important"
					});
				});
				</script>
				<?php 
            } else {
                ?>
				<p><?php 
                echo esc_html__( 'No clicks available.', 'woo-coupon-usage' );
                ?></p>
				<?php 
            }
        }
    }

}
add_action(
    'wcusage_hook_display_coupon_url_clicks',
    'wcusage_display_coupon_url_clicks',
    10,
    4
);