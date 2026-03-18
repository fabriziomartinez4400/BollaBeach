<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Outputs the affiliate dashboard page as shortcode
 *
 * @param mixed $atts
 *
 */
function wcusage_couponusage(  $atts  ) {
    if ( function_exists( 'is_product' ) ) {
        // Don't show shortcode in certain cases
        if ( isset( $_GET['action'] ) && $_GET['action'] == "elementor" ) {
            return '[couponaffiliates]';
        }
        if ( !is_admin() && !is_product() && is_page() ) {
            ob_start();
            global $has_run_couponusage;
            if ( $has_run_couponusage ) {
                return '';
            }
            $has_run_my_shortcode = true;
            if ( !wp_script_is( 'woo-coupon-usage', 'enqueued' ) ) {
                if ( wp_script_is( 'woo-coupon-usage', 'registered' ) ) {
                    wp_enqueue_script( 'woo-coupon-usage' );
                } else {
                    wp_enqueue_script(
                        'woo-coupon-usage',
                        WCUSAGE_UNIQUE_PLUGIN_URL . 'js/woo-coupon-usage.js',
                        array('jquery'),
                        '5.8.0',
                        false
                    );
                }
            }
            $wcusage_field_show_graphs = wcusage_get_setting_value( 'wcusage_field_show_graphs', 1 );
            ?>

		<link rel="stylesheet" href="<?php 
            echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
            ?>" crossorigin="anonymous">

		<?php 
            ?>

    	<?php 
            do_action( 'wcusage_hook_custom_styles' );
            // Custom Styles
            ?>

      	<?php 
            // Get Language
            $language = wcusage_get_language_code();
            $options = get_option( 'wcusage_options' );
            $urlid = "";
            $coupon_code = "";
            $couponvisible = 0;
            $wcusage_show_tabs = 1;
            $wcusage_page_load = 0;
            $singlecoupon = "";
            if ( isset( $atts['coupon'] ) ) {
                $singlecoupon = strtolower( $atts['coupon'] );
                $singlecoupon = str_replace( "%20", " ", $singlecoupon );
            } else {
                $singlecoupon = "";
            }
            $wcusage_justcoupon = wcusage_get_setting_value( 'wcusage_field_justcoupon', '1' );
            $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '0' );
            $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
            $wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
            if ( wcusage_check_admin_access() ) {
                $wcusage_urlprivate = 0;
            }
            $wcusage_field_which_toggle = wcusage_get_setting_value( 'wcusage_field_which_toggle', '1' );
            $wcusage_show_refresh = wcusage_get_setting_value( 'wcusage_field_show_refresh', '0' );
            $couponnotassigned = false;
            // Check if admin is previewing another user's dashboard
            $currentuserid = get_current_user_id();
            $preview_user_id = $currentuserid;
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
            if ( isset( $_GET['couponid'] ) ) {
                $urlid = strtolower( $_GET['couponid'] );
            }
            // Get coupon name
            $show_coupon = "";
            if ( $singlecoupon ) {
                $show_coupon = strtolower( $singlecoupon );
            } else {
                if ( $urlid ) {
                    $show_coupon = strtolower( $urlid );
                }
            }
            // Remove everything after last dash ("-") if it is numbers after the dash
            $show_coupon = preg_replace( '/-\\d+$/', '', $show_coupon );
            // Replace %20 with space
            $show_coupon = str_replace( "%20", " ", $show_coupon );
            if ( $show_coupon ) {
                // Get ID of coupon with name $show_coupon
                $the_coupon_id = wcusage_get_coupon_id( $show_coupon );
                if ( $the_coupon_id ) {
                    $args = array(
                        'post_type' => 'shop_coupon',
                        'p'         => $the_coupon_id,
                    );
                } else {
                    $args = array(
                        'post_type'      => 'shop_coupon',
                        'posts_per_page' => -1,
                        'cache_results'  => false,
                    );
                }
                $the_query = new WP_Query($args);
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $postid = get_the_ID();
                    $currentuserid = get_current_user_id();
                    $coupon = strtolower( get_the_title() );
                    $secretid = strtolower( $coupon . $postid );
                    $secretid2 = strtolower( $coupon . "-" . $postid );
                    if ( $wcusage_justcoupon ) {
                        $secretid3 = strtolower( $coupon );
                    } else {
                        $secretid3 = "-";
                    }
                    $getthetitle = strtolower( get_the_title() );
                    if ( ($secretid == $urlid || $secretid2 == $urlid || $secretid3 == $urlid || $getthetitle == $singlecoupon) && ($coupon && $urlid || $coupon && !empty( $atts['coupon'] )) ) {
                        $coupon_user_id = get_post_meta( $postid, 'wcu_select_coupon_user', true );
                        $thecurrentuser = get_userdata( $coupon_user_id );
                        if ( $thecurrentuser ) {
                            $username = $thecurrentuser->user_login;
                        } else {
                            $username = "";
                        }
                        // For admin preview, use the preview user's data
                        if ( $is_admin_preview ) {
                            $preview_user_data = get_userdata( $preview_user_id );
                            if ( $preview_user_data ) {
                                $username = $preview_user_data->user_login;
                            }
                        }
                        global $woocommerce;
                        $c = new WC_Coupon($coupon);
                        $the_coupon_usage = $c->get_usage_count();
                        $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', 1 );
                        $wcusage_field_load_ajax_per_page = wcusage_get_setting_value( 'wcusage_field_load_ajax_per_page', 1 );
                        if ( !$wcusage_field_load_ajax ) {
                            $wcusage_field_load_ajax_per_page = 0;
                        }
                        if ( !$wcusage_field_load_ajax ) {
                            $wcusage_page_load = wcusage_get_setting_value( 'wcusage_field_page_load', '0' );
                            if ( $the_coupon_usage > 5000 ) {
                                $wcusage_page_load = 1;
                            }
                        } else {
                            $wcusage_page_load = "0";
                        }
                        $couponinfo = wcusage_get_coupon_info_by_id( $postid );
                        $couponuser = $couponinfo[1];
                        // Check if user is parent affiliate
                        $is_mla_parent = "";
                        if ( function_exists( 'wcusage_network_check_sub_affiliate' ) ) {
                            $is_mla_parent = wcusage_network_check_sub_affiliate( $currentuserid, $couponuser );
                            if ( $is_mla_parent ) {
                                echo "<style>#tab-page-payouts, #tab-page-settings { display: none; }</style>";
                            }
                        }
                        // Show Content
                        if ( ($is_mla_parent || $couponuser && $couponuser == $preview_user_id || wcusage_check_admin_access() || $coupon_user_id == "" && !$wcusage_urlprivate || $is_admin_preview) && ($urlid || $couponuser == $preview_user_id || !empty( $atts['coupon'] )) ) {
                            ?>

				<div class="wcu-dash-coupon-area">

				<style>.wcu-user-coupon-title, .wcu-user-coupon-linebreak { display: none; }</style>

				<?php 
                            // Coupon Dashboard Title
                            $dashboard_title = get_the_title();
                            // Hidden input field with title
                            echo '<input type="hidden" id="wcu-coupon-title" value="' . esc_attr( $dashboard_title ) . '">';
                            // Filter to customise title
                            $dashboard_title = apply_filters( 'wcusage_hook_dashboard_title', $dashboard_title, $postid );
                            $dashboard_title = "<span class='wcu-coupon-title'>" . $dashboard_title . "</span>";
                            $wcusage_before_title = wcusage_get_setting_value( 'wcusage_before_title', '' );
                            $wcusage_before_title = "<span class='wcu-coupon-title-prefix'>" . $wcusage_before_title . "</span>";
                            if ( $wcusage_before_title ) {
                                $dashboard_title = $wcusage_before_title . " " . $dashboard_title;
                            }
                            echo '<h2 class="coupon-title"><i class="fas fa-tag" style="font-size: 0.8em;"></i> ' . wp_kses_post( $dashboard_title );
                            if ( $wcusage_field_load_ajax ) {
                                ?><a class="wcusage-refresh-data" href="javascript:void(0);" style="visibility: hidden;">
						<i class="fas fa-sync" style="font-size: 16px;" title="<?php 
                                echo esc_html__( "Refresh stats...", "woo-coupon-usage" );
                                ?>"></i>
					</a>
					<?php 
                            }
                            echo '<span class="wcusage-dash-top-links">';
                            // Dark Mode Toggle (only if enabled and not portal)
                            $wcusage_field_dark_mode_enable = wcusage_get_setting_value( 'wcusage_field_dark_mode_enable', 0 );
                            $wcusage_field_dark_mode_toggle = wcusage_get_setting_value( 'wcusage_field_dark_mode_toggle', 1 );
                            if ( $wcusage_field_dark_mode_enable && $wcusage_field_dark_mode_toggle ) {
                                ?>
				<span class="wcusage-dash-darkmode-toggle" style="float: right; text-align: right; margin-right: 20px;">
					<button type="button" class="wcu-dark-mode-toggle" id="wcu-dark-mode-toggle" aria-label="<?php 
                                echo esc_attr__( 'Toggle Dark Mode', 'woo-coupon-usage' );
                                ?>" title="<?php 
                                echo esc_attr__( 'Toggle Dark Mode', 'woo-coupon-usage' );
                                ?>">
						<i class="fas fa-moon"></i>
					</button>
				</span>
				<?php 
                            }
                            // Logout Link
                            $wcusage_field_show_logout_link = wcusage_get_setting_value( 'wcusage_field_show_logout_link', '1' );
                            if ( is_user_logged_in() && $wcusage_field_show_logout_link && !$is_admin_preview ) {
                                $thecurrentuser = get_userdata( $currentuserid );
                                $display_name = $thecurrentuser->display_name;
                                $logoutredirectpage = get_page_link( wcusage_get_coupon_shortcode_page_id() );
                                echo "<span class='wcusage-dash-logout' style='float: right; text-align: right;'><a href='" . esc_url( wp_logout_url( $logoutredirectpage ) ) . "' style='font-size: 12px;'>" . esc_html__( 'Logout', 'woo-coupon-usage' ) . " <i class='fas fa-sign-out-alt'></i></a></span>";
                            }
                            $wcusage_field_show_username = wcusage_get_setting_value( 'wcusage_field_show_username', '1' );
                            if ( is_user_logged_in() && $wcusage_field_show_username ) {
                                $display_username = $username;
                                if ( $is_admin_preview ) {
                                    $preview_user_data = get_userdata( $preview_user_id );
                                    if ( $preview_user_data ) {
                                        $display_username = $preview_user_data->user_login;
                                    }
                                }
                                echo "<span class='wcusage-dash-logout wcusage-dash-username' style='float: right; text-align: right; margin-right: 20px;'><i class='fas fa-user'></i> " . esc_html( $display_username ) . "</span>";
                            }
                            echo '</span>';
                            echo '</h2>';
                            ?>

				<?php 
                            if ( $wcusage_field_load_ajax ) {
                                ?>
				<!-- Check if jQuery loaded, if not show message -->
				<p class="wcusage-jquery-error" style="display: none; color: red; font-weight: bold; margin-bottom: 20px;">
				<?php 
                                echo sprintf( wp_kses_post( __( "jQuery is required to load the affiliate dashboard. It looks like you have a performance tool that is disabling jQuery from loading. Please exclude this page from your optimisations, or <a href='%s' target='_blank'>see here for other solutions</a>.", "woo-coupon-usage" ) ), "https://couponaffiliates.com/docs/affiliate-dashboard-is-not-showing/?utm_campaign=plugin&utm_source=plugin-dashboard&utm_medium=jquery-error" );
                                ?>
				</p>
				<script>
				if (typeof jQuery == 'undefined') {
					document.querySelector('.wcusage-jquery-error').style.display = "block";
				}
				</script>
				<?php 
                            }
                            ?>

				<?php 
                            $coupon_code = "";
                            if ( $singlecoupon ) {
                                $coupon_code = $singlecoupon;
                            } else {
                                $urlid = str_replace( "-" . $postid, "", $urlid );
                                $urlid = preg_replace( '/' . preg_quote( $postid, '/' ) . '$/', '', $urlid );
                                if ( $urlid ) {
                                    $coupon_code = $urlid;
                                }
                            }
                            $coupon_code = sanitize_text_field( $coupon_code );
                            $get_options = get_option( 'wcusage_options' );
                            $discount_type_original = get_post_meta( $postid, 'discount_type', true );
                            $discount_type = get_post_meta( $postid, 'discount_type', true );
                            if ( $discount_type == "fixed_cart" ) {
                                $discount_type = esc_html__( "Fixed amount on cart.", "woo-coupon-usage" );
                            }
                            if ( $discount_type == "percent" ) {
                                $discount_type = esc_html__( "Percentage discount on cart.", "woo-coupon-usage" );
                            }
                            if ( $discount_type == "recurring_fee" ) {
                                $discount_type = esc_html__( "Recurring fixed discount on subscription fee.", "woo-coupon-usage" );
                            }
                            if ( $discount_type == "recurring_percent" ) {
                                $discount_type = esc_html__( "Recurring percentage discount on subscription fee.", "woo-coupon-usage" );
                            }
                            if ( $discount_type == "signup_fixed" ) {
                                $discount_type = esc_html__( "Fixed discount on subscription signup.", "woo-coupon-usage" );
                            }
                            // Total Orders To Show
                            $option_coupon_orders = wcusage_get_setting_value( 'wcusage_field_orders', '10' );
                            $combined_commission = wcusage_commission_message( $postid );
                            $current_commission_message = get_post_meta( $postid, 'wcu_commission_message', true );
                            /*** Error if ajax fails ***/
                            $ajaxerrormessage = wcusage_ajax_error();
                            /*** Show Tabs ***/
                            $wcusage_show_tabs = wcusage_get_setting_value( 'wcusage_field_show_tabs', '1' );
                            /*** REFRESH STATS? ***/
                            $force_refresh_stats = wcusage_check_if_refresh_needed( $postid );
                            // Check if force refresh needed
                            if ( $force_refresh_stats ) {
                                ?>
					<?php 
                                if ( $wcusage_field_load_ajax ) {
                                    ?>
					<script>
					jQuery(document).ready(function() {
					jQuery('#tab-page-monthly, #tab-page-orders').css("opacity", "0.5");
					jQuery('#tab-page-monthly, #tab-page-orders').css("pointer-events", "none");
					});
					</script>
					<?php 
                                }
                                ?>
					<?php 
                            }
                            ?>

				<?php 
                            // Check if batch refresh enabled
                            $wcusage_field_enable_coupon_all_stats_batch = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_batch', '1' );
                            // Refresh the stats via ajax in batches
                            if ( $wcusage_field_load_ajax && $wcusage_field_enable_coupon_all_stats_batch && $force_refresh_stats ) {
                                $force_refresh_stats = 0;
                                ?>

					<style>
					.wcutablinks {
						opacity: 0.5;
						pointer-events: none;
					}
					</style>

					<?php 
                                do_action( 'wcusage_hook_before_dashboard', $coupon_code );
                                // Custom Hook
                                ?>

					<div style="clear: both;"></div>
					
					<?php 
                                do_action( 'wcusage_hook_dashboard_normal_tabs', $wcusage_page_load );
                                ?>

					<?php 
                                do_action( 'wcusage_hook_update_all_stats_batch_ajax', $coupon_code, $the_coupon_usage );
                                ?>

				<?php 
                            } else {
                                // Loader
                                if ( $wcusage_show_tabs == '1' || $wcusage_show_tabs == '' ) {
                                    ?>

				<div style="height: 0;">

				<script>
				function wcusage_update_complete_loading() {
					jQuery(".wcu-loading-image").hide();
					jQuery('.stuck-loading-message').hide();
					jQuery(".wcu-loading-hide").css({"visibility": "visible", "height": "auto"});
					jQuery('.wcusage-refresh-data i').removeClass('fa-spin wcusage-loading');
					jQuery(".wcusagechart").css("visibility", "visible");
					jQuery("#wcusagechartmonth path").click();
					jQuery('#generate-short-url').css('opacity', '1');
					jQuery('#generate-short-url').prop('disabled', false);
				}
				<?php 
                                    if ( $wcusage_field_load_ajax ) {
                                        ?>
				jQuery(document).on({
					ajaxStart: function(){
						jQuery(".wcu-loading-image").show();
						jQuery('.wcusage-refresh-data i').addClass('fa-spin wcusage-loading');
					},
					ajaxStop: function(){
					<?php 
                                    } else {
                                        ?>
					jQuery( document ).ready(function() {
					<?php 
                                    }
                                    ?>
						wcusage_update_complete_loading();
					<?php 
                                    if ( $wcusage_field_load_ajax ) {
                                        ?>
					}
					<?php 
                                    }
                                    ?>
				});
				</script>
				<?php 
                                }
                                ?>

				<script>
				function wcuOpenTab(evt, tabName) {
				jQuery(".wcutabcontent").css("display", "none");
				jQuery(".wcutabcontent").removeClass( "active" );
				jQuery("#" + tabName).css("display", "block");
				jQuery("#" + tabName).addClass( "active" );
				}
				</script>

				<script>
				<?php 
                                if ( !$wcusage_page_load ) {
                                    ?>
				if (jQuery('.wcutabfirst').length > 0) {
				document.querySelector('.wcutabfirst').click();
				}
				<?php 
                                }
                                ?>
				</script>

				</div>

				<?php 
                                do_action( 'wcusage_hook_before_dashboard', $coupon_code );
                                // Custom Hook
                                ?>

				<div style="clear: both;"></div>

				<?php 
                                do_action( 'wcusage_hook_dashboard_normal_tabs', $wcusage_page_load );
                                ?>
				
				<?php 
                                // Get Statistics tab content
                                do_action(
                                    'wcusage_hook_dashboard_tab_content_statistics',
                                    $postid,
                                    $coupon_code,
                                    $combined_commission,
                                    $wcusage_page_load,
                                    $force_refresh_stats
                                );
                                ?>

				<?php 
                                ?>

				<?php 
                                // Get Latest Orders tab content
                                do_action(
                                    'wcusage_hook_dashboard_tab_content_latest_orders',
                                    $postid,
                                    $coupon_code,
                                    $combined_commission,
                                    $wcusage_page_load
                                );
                                ?>

				<?php 
                                // Referral URL Links Section
                                do_action(
                                    'wcusage_hook_dashboard_tab_content_referral_url_stats',
                                    $postid,
                                    $coupon_code,
                                    $combined_commission,
                                    $wcusage_page_load
                                );
                                ?>

				<?php 
                                ?>

				<?php 
                                ?>

				<?php 
                                ?>

				<?php 
                                // Bonuses Section
                                $wcusage_field_bonuses_tab_enable = wcusage_get_setting_value( 'wcusage_field_bonuses_tab_enable', '1' );
                                if ( $wcusage_field_bonuses_tab_enable ) {
                                    ?>
					<?php 
                                    ?>
				<?php 
                                }
                                ?>

				<?php 
                                // Settings Section
                                $wcusage_field_show_settings_tab_show = wcusage_get_setting_value( 'wcusage_field_show_settings_tab_show', '1' );
                                if ( $wcusage_field_show_settings_tab_show ) {
                                    ?>
					<?php 
                                    if ( !$is_mla_parent || wcusage_check_admin_access() ) {
                                        ?>
						<?php 
                                        do_action(
                                            'wcusage_hook_dashboard_tab_content_settings',
                                            $postid,
                                            $coupon_code,
                                            $combined_commission,
                                            $wcusage_page_load,
                                            $coupon_user_id,
                                            ''
                                        );
                                        ?>
					<?php 
                                    }
                                    ?>
				<?php 
                                }
                                ?>
						
				<?php 
                                ?>

				<?php 
                            }
                            ?>

				<?php 
                        } else {
                            // Show message if coupon not assigned to user
                            $couponnotassigned = true;
                            echo "<p class='wcusage-full-not-assigned'>" . esc_html__( "Sorry, this coupon is not assigned to you.", "woo-coupon-usage" ) . "</p>";
                        }
                        ?>
				
				<?php 
                    }
                }
            }
            if ( !isset( $couponnotassigned ) ) {
                $couponnotassigned = false;
            }
            if ( !isset( $urlid ) ) {
                $urlid = "";
            }
            if ( !isset( $singlecoupon ) ) {
                $singlecoupon = "";
            }
            // If unique URL but no coupon/page found show message
            if ( !$coupon_code && !$couponnotassigned && $urlid ) {
                echo esc_html__( "No affiliate dashboard found.", "woo-coupon-usage" );
            }
            ?>

      	<?php 
            $get_options = get_option( 'wcusage_options' );
            if ( !$singlecoupon && !isset( $_GET['couponid'] ) ) {
                echo do_shortcode( '[couponaffiliates-user]' );
            } else {
                if ( !$urlid && !$coupon_code ) {
                    ?>
      		<br/><br/>
      		<div style="clear: both;"></div>
      		<p>
            <?php 
                    echo esc_html__( "No coupon ID has been selected.", "woo-coupon-usage" );
                    ?>
      		</p>
      		<?php 
                }
            }
            ?>

        <div style="clear: both; margin-bottom: 50px;"></div>

        <?php 
            do_action( 'wcusage_hook_after_dashboard', $coupon_code );
            // Custom Hook
            ?>

    	   <?php 
            $thecontent = ob_get_contents();
            ob_end_clean();
            wp_reset_postdata();
            // Return content removing white spaces
            $thecontent = trim( preg_replace( '/\\s+/', ' ', $thecontent ) );
            return $thecontent;
        }
    }
}

add_shortcode( 'couponusage', 'wcusage_couponusage' );
add_shortcode( 'couponaffiliates', 'wcusage_couponusage' );
//add_filter('the_content', 'wpautop', 12);