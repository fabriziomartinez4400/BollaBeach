<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Displays the referral URL tab content on affiliate dashboard
 *
 * @param int $postid
 * @param string $coupon_code
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_tab_referral_url',
    'wcusage_tab_referral_url',
    10,
    2
);
if ( !function_exists( 'wcusage_tab_referral_url' ) ) {
    function wcusage_tab_referral_url(  $postid, $coupon_code  ) {
        $options = get_option( 'wcusage_options' );
        $option_text_urls = wcusage_get_setting_value( 'wcusage_field_text_urls', '' );
        $wcusage_urls_prefix = wcusage_get_setting_value( 'wcusage_field_urls_prefix', 'coupon' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '' );
        $wcusage_src_prefix = wcusage_get_setting_value( 'wcusage_field_src_prefix', 'src' );
        $wcusage_field_show_campaigns = wcusage_get_setting_value( 'wcusage_field_show_campaigns', 1 );
        $wcusage_field_show_qrcodes = wcusage_get_setting_value( 'wcusage_field_show_qrcodes', 0 );
        $wcusage_field_show_shortlink = wcusage_get_setting_value( 'wcusage_field_show_shortlink', 0 );
        $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
        $urls_generator_enable = wcusage_get_setting_value( 'wcusage_field_urls_generator_enable', 1 );
        $urls_statistics_enable = wcusage_get_setting_value( 'wcusage_field_urls_statistics_enable', 1 );
        ?>

		<?php 
        do_action( 'wcusage_hook_scripts_tab_referral_url_stats' );
        // Get Referral Tab Scripts
        ?>

    <?php 
        if ( $urls_generator_enable ) {
            ?>
    <div id="wcu-referral-generator">

    <?php 
            echo "<p class='wcu-tab-title' style='font-size: 22px; margin-bottom: 20px;'>" . esc_html__( "Referral URL", "woo-coupon-usage" ) . ":</p>";
            // Title
            if ( $option_text_urls ) {
                echo '<p><span class="wcusage-info-box-title">' . esc_html( $option_text_urls ) . '</p><br/>';
            }
            // Custom Description
            ?>

		<!-- Select Page URL -->
		<?php 
            if ( $wcusage_field_show_campaigns ) {
                ?>
  		<div class="wcu-campaigns-col1">
  		<?php 
            }
            ?>
  			<p>
  				<strong><?php 
            echo esc_html__( "Page URL", "woo-coupon-usage" );
            ?>:</strong><br/>
  				<input type="url" class="wcusage_custom_ref_url" placeholder="<?php 
            echo esc_attr( $wcusage_field_default_ref_url );
            ?>" style="width: 300px; max-width: 100%;">
  			</p>
  			<br/>
  		<?php 
            if ( $wcusage_field_show_campaigns ) {
                ?>
  		</div>
  		<?php 
            }
            ?>

  		<?php 
            ?>

  		<div style="clear: both;"></div>

  		<div class="referral-url-box">

  			<!-- Ref Link -->
  			<p style="margin-top: 8px; margin-bottom: 10px;">
  				<strong><?php 
            echo esc_html__( "Referral Link", "woo-coupon-usage" );
            ?>:</strong> <button id="wcusage_copylink"
          onclick="wcusage_copyToClipboard('p1', '<?php 
            if ( $wcusage_field_show_shortlink ) {
                ?>p1short<?php 
            }
            ?>')"><?php 
            echo esc_html( ucfirst( __( "Copy", "woo-coupon-usage" ) ) );
            ?> <i id="wcu-copy-p1" class="far fa-copy"></i> <i id="wcu-copied-p1" style="display: none;" class="fas fa-check-circle"></i></button> <?php 
            if ( $wcusage_field_show_shortlink ) {
                do_action( 'wcusage_hook_url_shorten_form' );
            }
            ?><br/>
  				<code style="margin: 10px 0 2px 0; padding: 5px; display: inline-block; background: rgba(0,0,0,0.04); color: #1a1a1a;" id="p1" class="wcu-urllink">
            <span id="output-custom-url"><?php 
            echo esc_html( $wcusage_field_default_ref_url );
            ?></span><span id="output-custom-url-sep">?</span><?php 
            echo esc_html( $wcusage_urls_prefix );
            ?>=<?php 
            echo esc_html( rawurlencode( $coupon_code ) );
            ?><span id="output-custom-campaign"></span>
  				</code>
  				<?php 
            if ( $wcusage_field_show_shortlink ) {
                ?>
  				<code style="margin: 10px 0 2px 0; padding: 5px; display: inline-block; background: rgba(0,0,0,0.04); color: #1a1a1a; display: none;" id="p1short" class="wcu-urllink"></code>
  				<?php 
            }
            ?>
  			</p>

  			<!-- Social sharing -->
  			<?php 
            ?>

  			<?php 
            ?>

  			<div style="clear: both;"></div>

  		</div>

  		<br/>

      </div>
    <?php 
        }
        ?>

	<?php 
    }

}
/**
 * Gets the stats for referral URL
 *
 * @param int $postid
 * @param string $coupon_code
 * @param string $campaign
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_tab_referral_url_stats',
    'wcusage_tab_referral_url_stats',
    10,
    5
);
if ( !function_exists( 'wcusage_tab_referral_url_stats' ) ) {
    function wcusage_tab_referral_url_stats(
        $postid,
        $coupon_code,
        $campaign,
        $page = 0,
        $converted = 0
    ) {
        // Show Stats Boxes
        do_action(
            'wcusage_hook_get_referral_url_stats',
            $postid,
            $coupon_code,
            $campaign
        );
        // diplay clicks / visits table
        $wcusage_field_show_click_history = wcusage_get_setting_value( 'wcusage_field_show_click_history', 1 );
        if ( $wcusage_field_show_click_history ) {
            do_action(
                'wcusage_hook_display_coupon_url_clicks',
                $postid,
                $campaign,
                $page,
                $converted
            );
        }
    }

}
/**
 * Gets the scripts for referral URL tab
 *
 */
add_action(
    'wcusage_hook_scripts_tab_referral_url_stats',
    'wcusage_scripts_tab_referral_url_stats',
    10,
    0
);
if ( !function_exists( 'wcusage_scripts_tab_referral_url_stats' ) ) {
    function wcusage_scripts_tab_referral_url_stats() {
        $options = get_option( 'wcusage_options' );
        $wcusage_field_default_ref_url = wcusage_get_default_ref_url();
        if ( isset( $options['wcusage_field_page_load'] ) ) {
            $wcusage_page_load = $options['wcusage_field_page_load'];
        } else {
            $wcusage_page_load = "";
        }
        ?>
		<script>
		jQuery(document).ready(function(){

			jQuery( "#wcu-referral-campaign" ).change(function(){
				jQuery("#p1short").text("");
				jQuery("#p1short").hide();
				jQuery("#p1").css("display", "inline-block");
				jQuery('#generate-short-url').css('opacity', '1');
				jQuery('#generate-short-url').prop('disabled', false);
			});

			jQuery('.wcusage_custom_ref_url').on('input', function(){
				jQuery("#p1short").text("");
				jQuery("#p1short").hide();
				jQuery("#p1").css("display", "inline-block");
			});

		});
    jQuery(function() {
      jQuery('.wcusage_custom_ref_url').on('input', function(){
          var source_name = jQuery(this).attr('name');
          var url = jQuery(this).val();
          <?php 
        if ( get_option( 'permalink_structure' ) ) {
            ?>
          <?php 
            $include_slash = apply_filters( 'wcusage_url_include_slash', true );
            if ( $include_slash ) {
                ?>
            if (url.length > 0 && !url.endsWith('/')) {
              url += '/';
            }
          <?php 
            }
            ?>
          <?php 
        } else {
            ?>
          jQuery('#output-custom-url-sep').text("&");
          <?php 
        }
        ?>
          jQuery('#output-custom-url').text(url);
          if (url.length == 0) {
            jQuery('#output-custom-url-sep').text("?");
            jQuery('#output-custom-url').text("<?php 
        echo esc_html( $wcusage_field_default_ref_url );
        ?>");
          }
      });
    });

		</script>
	<?php 
    }

}
/**
 * Gets referral URL tab for shortcode page
 *
 * @param int $postid
 * @param string $coupon_code
 * @param int $combined_commission
 *
 * @return mixed
 *
 */
add_action(
    'wcusage_hook_dashboard_tab_content_referral_url_stats',
    'wcusage_dashboard_tab_content_referral_url_stats',
    10,
    4
);
if ( !function_exists( 'wcusage_dashboard_tab_content_referral_url_stats' ) ) {
    function wcusage_dashboard_tab_content_referral_url_stats(
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
        $wcusage_field_show_click_history = wcusage_get_setting_value( 'wcusage_field_show_click_history', '1' );
        $wcusage_field_show_click_history_amount = wcusage_get_setting_value( 'wcusage_field_show_click_history_amount', '15' );
        $wcusage_field_show_click_history_pagination = wcusage_get_setting_value( 'wcusage_field_show_click_history_pagination', '1' );
        $wcusage_justcoupon = wcusage_get_setting_value( 'wcusage_field_justcoupon', '1' );
        $wcusage_show_tax = wcusage_get_setting_value( 'wcusage_field_show_tax', '0' );
        $wcusage_hide_all_time = wcusage_get_setting_value( 'wcusage_field_hide_all_time', '0' );
        $wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
        if ( wcusage_check_admin_access() ) {
            $wcusage_urlprivate = 0;
        }
        $ajaxerrormessage = wcusage_ajax_error();
        // Custom
        $wcusage_field_urls_enable = wcusage_get_setting_value( 'wcusage_field_urls_enable', '1' );
        $wcusage_field_urls_tab_enable = wcusage_get_setting_value( 'wcusage_field_urls_tab_enable', '1' );
        // *** DISPLAY CONTENT *** //
        ?>

  <?php 
        if ( isset( $_POST['page-links'] ) || !isset( $_POST['load-page'] ) || $wcusage_page_load == false ) {
            ?>

    <?php 
            $wcusage_src_prefix = wcusage_get_setting_value( 'wcusage_field_src_prefix', 'src' );
            ?>    <?php 
            if ( isset( $_POST['page-links'] ) ) {
                ?>
    <style>#wcu4 { display: block; }</style>
    <?php 
            }
            ?>

    <?php 
            if ( $wcusage_field_urls_enable && $wcusage_field_urls_tab_enable ) {
                ?>
    <div id="wcu4" <?php 
                if ( $wcusage_show_tabs == '1' || $wcusage_show_tabs == '' ) {
                    ?>class="wcutabcontent"<?php 
                }
                ?>>

      <script>
      jQuery(document).ready(function(){

        jQuery('.show_referrals').html('');
        jQuery('#wcu-add-campaign-delete').hide();
        jQuery('.wcu-clicks-pagination').hide();

        jQuery(document).on('change', '#wcu-referral-campaign, #wcu-checkbox-clicks-converted', function() {
          jQuery("#wcu-clicks-page").val("0");
          wcusage_run_tab_page_links();
        });

        /* Pagination Next */
        jQuery( ".wcusage-clicks-next-page" ).click(function() {
          var nextpage = Number(jQuery("#wcu-clicks-page").val()) + 1;
          jQuery('.wcusage-clicks-next-page').css({"opacity": "0.5", "pointer-events": "none"});
          jQuery('.wcusage-clicks-prev-page').css({"opacity": "1", "pointer-events": "auto"});
          jQuery('#wcu-clicks-page').val( nextpage );
          wcusage_run_tab_page_links();
        });

        /* Pagination Previous */
        jQuery( ".wcusage-clicks-prev-page" ).click(function() {
          var prevpage = Number(jQuery("#wcu-clicks-page").val()) - 1;
          jQuery( ".wcusage-clicks-next-page" ).css({"opacity": "1", "pointer-events": "auto"});
          jQuery('.wcusage-clicks-prev-page').css({"opacity": "0.5", "pointer-events": "none"});
          jQuery('#wcu-clicks-page').val( prevpage );
          wcusage_run_tab_page_links();
        });

      });

      jQuery( document ).ready(function() {
      
      <?php 
                if ( $wcusage_field_load_ajax_per_page ) {
                    ?>
      jQuery( "#tab-page-links" ).one('click', wcusage_run_tab_page_links);
      <?php 
                }
                ?>
      jQuery( ".wcusage-refresh-data" ).on('click', wcusage_run_tab_page_links);

      jQuery( ".wcusage-refresh-data" ).click(function() { jQuery( "#wcu-referral-campaign" ).change(); });
      
      });

      function wcusage_run_tab_page_links() {

        var isconverted = 0;
        if (jQuery('#wcu-checkbox-clicks-converted').is(":checked")) {
          isconverted = 1;
        }

        var data = {
          action: 'wcusage_load_referral_url_stats',
          _ajax_nonce: '<?php 
                echo esc_html( wp_create_nonce( 'wcusage_dashboard_ajax_nonce' ) );
                ?>',
          postid: '<?php 
                echo esc_html( $postid );
                ?>',
          couponcode: '<?php 
                echo esc_html( $coupon_code );
                ?>',
          campaign: jQuery('#wcu-referral-campaign').val(),
          page: jQuery('#wcu-clicks-page').val(),
          converted: isconverted,
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
            success: function(data){

              /* Show Content */
              jQuery('.show_referrals').html(data);

              /* Pagination */
              jQuery('.wcu-clicks-pagination').css('display', 'block');

              jQuery('.wcusage-clicks-next-page').css({"opacity": "1", "pointer-events": "auto"});
              jQuery('.wcusage-clicks-prev-page').css({"opacity": "1", "pointer-events": "auto"});

              if(jQuery("#wcu-clicks-page").val() <= 0) {
                jQuery('.wcusage-clicks-prev-page').css({"opacity": "0.5", "pointer-events": "none"});
                jQuery("#wcu-clicks-page").val("0");
              }

              var totalpages = Number(jQuery("#wcu-total-usage-clicks-url-num").text() / <?php 
                echo esc_html( $wcusage_field_show_click_history_amount );
                ?>);
              var totalpages = Math.round(totalpages);

              var totalpagesconverted = Number(jQuery("#wcu-total-usage-number-url-num").text() / <?php 
                echo esc_html( $wcusage_field_show_click_history_amount );
                ?>);
              var totalpagesconverted = Math.round(totalpagesconverted);

              /* Converted Toggle */
              jQuery( '#wcu-clicks-pages-second-half' ).show();
              if(isconverted) {
                jQuery('#wcu-clicks-pages-last').text(Number(totalpagesconverted));
                jQuery( "#wcu-checkbox-clicks-converted" ).prop( "checked", true );
              } else {
                jQuery('#wcu-clicks-pages-last').text(Number(totalpages + 1));
              }

              jQuery('#wcu-clicks-pages-current').text(Number(jQuery('#wcu-clicks-page').val()) + 1);
              if(Number(jQuery('#wcu-clicks-pages-current').text()) >= Number(jQuery('#wcu-clicks-pages-last').text())) {
                jQuery( ".wcusage-clicks-next-page" ).css({"opacity": "0.5", "pointer-events": "none"});
              }

            },
            error: function(data){ jQuery('.show_referrals').html('<?php 
                echo wp_kses_post( $ajaxerrormessage );
                ?>'); }
        });

        if(jQuery('#wcu-referral-campaign').val()) {
          jQuery('#wcu-add-campaign-delete').show();
        } else {
          jQuery('#wcu-add-campaign-delete').hide();
        }

        if(jQuery('#wcu-referral-campaign').val()) {
          jQuery('#output-custom-campaign').text( "&<?php 
                echo esc_html( $wcusage_src_prefix );
                ?>=" + encodeURIComponent(jQuery('#wcu-referral-campaign').val()) );
        } else {
          jQuery('#output-custom-campaign').text( "" );
        }

      }
      </script>

      <?php 
                do_action( 'wcusage_hook_tab_referral_url', $postid, $coupon_code );
                ?>

      <?php 
                if ( $wcusage_field_load_ajax ) {
                    ?>

        <!-- Display Content -->
        <div class="show_referrals"></div>

        <!-- Pagination -->
        <div class="wcu-clicks-pagination" style="font-size: 12px; margin-top: 0; display: none;">

          <input type="text" id="wcu-clicks-page" name="wcu-clicks-page" value="0" style="display: none;">

          <?php 
                    if ( $wcusage_field_show_click_history_pagination && $wcusage_field_show_click_history ) {
                        ?>

          <p style="float: left; font-size: 12px; margin: 0;">Page <span id="wcu-clicks-pages-current">1</span><span id="wcu-clicks-pages-second-half"> / <span id="wcu-clicks-pages-last"></span></p>

          <p style="float: right; font-size: 12px; margin: 0;">
            <a class="wcusage-clicks-prev-page" href="javascript:void(0);">
              <i class="fas fa-arrow-left" style="font-size: 12px;" title="<?php 
                        echo esc_html( ucfirst( __( "Previous Page", "woo-coupon-usage" ) ) );
                        ?>"></i> <?php 
                        echo esc_html( ucfirst( __( "Previous", "woo-coupon-usage" ) ) );
                        ?>
            </a>
            &nbsp;&nbsp;
            <a class="wcusage-clicks-next-page" href="javascript:void(0);">
              <?php 
                        echo esc_html( ucfirst( __( "Next", "woo-coupon-usage" ) ) );
                        ?> <i class="fas fa-arrow-right" style="font-size: 12px;" title="<?php 
                        echo esc_html( ucfirst( __( "Next Page", "woo-coupon-usage" ) ) );
                        ?>"></i>
            </a>
          </p>

          <?php 
                    }
                    ?>

        </div>

        <div style="clear: both;"></div>

        <!-- Loading -->
        <div class="wcu-loading-image wcu-loading-referral">
          <div class="wcu-loading-loader">
            <div class="loader"></div>
          </div>
          <p class="wcu-loading-loader-text"><br/><?php 
                    echo esc_html__( "Loading", "woo-coupon-usage" );
                    ?>...</p><br/>
        </div>

      <?php 
                } else {
                    ?>

        <?php 
                    do_action(
                        'wcusage_hook_tab_referral_url_stats',
                        $postid,
                        $coupon_code,
                        '',
                        0
                    );
                    ?>

      <?php 
                }
                ?>

      <?php 
                ?>

    </div>
    <div style="width: 100%; clear: both;"></div>
    <?php 
            }
            ?>

  <?php 
        }
        ?>

  <?php 
    }

}