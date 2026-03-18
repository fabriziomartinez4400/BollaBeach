<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb( $args ) {
    $options = get_option( 'wcusage_options' );

    $ispro = ( wcu_fs()->can_use_premium_code() ? 1 : 0 );
    $probrackets = ( $ispro ? "" : " (PRO)" );
    ?>

<div id="general-settings" class="settings-area">

	<h1><?php echo esc_html__( "General Settings", "woo-coupon-usage" ); ?></h1>

  <?php
  if ( function_exists('wc_coupons_enabled') ) {
    if ( !wc_coupons_enabled() ) {
      echo "Notice: Coupons have been automatically enabled in your WooCommerce settings.";
      update_option( 'woocommerce_enable_coupons', 'yes' );
    }
  }
  ?>

  <hr/>

  <!-- Dashboard Page -->
  <h3 class="affiliate-dashboard-page-settings"><span class="dashicons dashicons-admin-generic " style="margin-top: 2px;"></span> <?php echo esc_html__( 'Dashboard Page', 'woo-coupon-usage' ); ?>:</h3>
  <?php do_action( 'wcusage_hook_setting_section_dashboard_page' ); ?>

  <br/><hr/>

  <!-- FAQ: How to create new affiliates & coupons? -->
  <div class="wcu-admin-faq">

    <?php wcusage_admin_faq_toggle(
    "wcu_show_section_qna_create_affiliates",
    "wcu_qna_create_affiliates",
    "FAQ: How do I create new affiliates & coupons?");
    ?>

    <div class="wcu-admin-faq-content wcu_qna_create_affiliates" id="wcu_qna_create_affiliates" style="display: none;">

      <?php echo esc_html__( 'To add new affiliates and assign them to a specific coupon, you can do any of the following 3 options:', 'woo-coupon-usage' ); ?>
      
      <br/>
      
      <ul>
        <li style="margin-left: 5px; margin-bottom: 10px;">
        &bull; Option 1 - <strong>Edit Coupons Manually</strong>: <?php echo esc_html__( 'Go to the', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" target="_blank"><?php echo esc_html__( 'coupons management page', 'woo-coupon-usage' ); ?></a>, <?php echo esc_html__( 'and add or edit a coupon, then assign users under the "coupon affiliates" tab', 'woo-coupon-usage' ); ?>. (<a href="https://couponaffiliates.com/docs/how-do-i-assign-users-to-coupons" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>)
        </li>
        <li style="margin-left: 5px; margin-bottom: 10px;">
        &bull; Option 2 - <strong>Add New Affiliates</strong>: <?php echo sprintf(wp_kses_post(__( 'Go to the <a href="%s" target="_blank">Add New Affiliate</a> page to add new affiliates here, which will automatically generate the coupon code for them.', 'woo-coupon-usage' )), esc_url(admin_url('admin.php?page=wcusage_add_affiliate'))); ?> (<a href="https://couponaffiliates.com/docs/manual-affiliate-registrations/" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>)
        </li>
        <li style="margin-left: 5px; margin-bottom: 10px;">
        &bull; Option 3 - <strong>Registration Form</strong>: <?php echo sprintf(wp_kses_post(__( 'Direct users to the <a href="%s" target="_blank">Affiliate Registration</a> page to allow them to register themselves. When accepted, this will then automatically create the coupon and assign them to it.', 'woo-coupon-usage' )), esc_url(admin_url('admin.php?page=wcusage_registrations'))); ?> (<a href="https://couponaffiliates.com/docs/pro-affiliate-registration" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>)
        </li>
      </ul>

      <?php echo esc_html__( 'The affiliate user can then visit the "affiliate dashboard page" to view their affiliate statistics, commissions, referral URLs, etc, for the coupons they are assigned to.', 'woo-coupon-usage' ); ?>

    </div>

  </div>

  <br/><hr style="margin-top: 14px;"/>

  <!-- Order/Sales Tracking -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Order/Sales Tracking', 'woo-coupon-usage' ); ?>:</h3>
  <?php do_action('wcusage_hook_setting_section_ordersalestracking'); ?>

  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px; margin-bottom: 0;"></span>
    <?php echo esc_html__( 'Affiliate Dashboard Customisation', 'woo-coupon-usage' ); ?>
  </h3>

  <p style="font-weight: bold;">
    <?php echo esc_html__( 'Customise the affiliate dashboard page to include exactly what you need:', 'woo-coupon-usage' ); ?>
  </p>

  <p>
    &bull; <?php echo esc_html__( 'Click "show settings" to customise each specific tab.', 'woo-coupon-usage' ); ?>
  </p>

  <p>
    &bull; <?php echo esc_html__( 'Drag and drop to customise the order of tabs shown on the dashboard menu.', 'woo-coupon-usage' ); ?>
  </p>

  <p>
    &bull; <?php echo esc_html__( 'Toggle the visibility of each tab to show/hide it on the dashboard.', 'woo-coupon-usage' ); ?>
  </p>

  <p>
    <?php echo esc_html__( 'By default the tabs settings are all already set to recommended options.', 'woo-coupon-usage' ); ?>
  </p>

  <div>

      <br/>
      <style>
      .wcusage-sortable-placeholder {
          height: 60px !important;
          margin: 10px 0 !important;
          background: #f0f8ff !important;
          border: 2px dashed #007cba !important;
          border-radius: 4px !important;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
          position: relative !important;
      }
      .wcusage-sortable-placeholder:before {
          content: "Drop here";
          color: #007cba;
          font-weight: 500;
          font-size: 14px;
      }
      .wcusage-tab-item.ui-sortable-helper {
          transform: rotate(1deg);
          box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
          z-index: 1000 !important;
      }
      </style>
      <script>
      jQuery(document).ready(function($){
          $('#wcusage-dashboard-tabs-order').sortable({
              placeholder: 'wcusage-sortable-placeholder',
              items: '.wcusage-tab-item',
              handle: '.dashicons-move',
              tolerance: 'pointer',
              cursor: 'move',
              opacity: 0.8,
              helper: 'clone',
              start: function(e, ui) {
                  ui.placeholder.height(ui.item.outerHeight());
              },
              update: function(){
                  var order = $('#wcusage-dashboard-tabs-order .wcusage-tab-item').map(function() {
                      return this.id;
                  }).get().join(',');
                  // Update hidden input holding the layout order
                  var $layoutInput = $('#wcusage_dashboard_tabs_layout');
                  $layoutInput.val(order);
                  // Only auto-save via AJAX if legacy (manual save) mode is NOT enabled
                  var legacyEnabled = jQuery('#wcusage_field_settings_legacy').is(':checked');
                  if (!legacyEnabled) {
                      if (typeof wcu_ajax_update_the_options === 'function') {
                          wcu_ajax_update_the_options($layoutInput, 'id', 'wcu-update-text', 1, '', 'textarea, input[type=text], input[type=number], input[type=password], input[type=radio], input[type=color], select');
                      } else {
                          // Fallback: still trigger change in case implementation changes later.
                          $layoutInput.trigger('change');
                      }
                  }
              }
          });
          $('#wcusage-dashboard-tabs-order').disableSelection();
      });
      </script>
      <?php
      $options = get_option('wcusage_options');
      $stored_tabs_order = isset($options['wcusage_dashboard_tabs_layout']) ? $options['wcusage_dashboard_tabs_layout'] : '';

      // Build dynamic list of potential tabs (keys align with button IDs in functions-dashboard & portal template for consistency)
      $candidate_tabs = array();
      $candidate_tabs['tab-page-stats'] = esc_html__('Statistics', 'woo-coupon-usage');
      // Monthly Summary (Pro + setting)
        if( wcu_fs()->can_use_premium_code() ) {
          $candidate_tabs['tab-page-monthly'] = esc_html__('Monthly Summary', 'woo-coupon-usage');
        }
      // Recent Orders
      $candidate_tabs['tab-page-orders'] = esc_html__('Recent Orders', 'woo-coupon-usage');
      // Referral URL
      $candidate_tabs['tab-page-links'] = esc_html__('Referral URL', 'woo-coupon-usage');
      // Creatives (Pro)
        if( wcu_fs()->can_use_premium_code() ) {
          $candidate_tabs['tab-page-creatives'] = esc_html__('Creatives', 'woo-coupon-usage');
        }
      // Rates (Pro)
        if( wcu_fs()->can_use_premium_code() ) {
          $candidate_tabs['tab-page-rates'] = esc_html__('Rates', 'woo-coupon-usage');
        }
      // Payouts (Pro)
        if( wcu_fs()->can_use_premium_code() ) {
          $candidate_tabs['tab-page-payouts'] = esc_html__('Payouts', 'woo-coupon-usage');
        }
      // Bonuses (Pro)
        if( wcu_fs()->can_use_premium_code() ) {
          $candidate_tabs['tab-page-bonuses'] = esc_html__('Bonuses', 'woo-coupon-usage');
        }
      // Settings (only for logged in affiliates, but include so order persists)
      $candidate_tabs['tab-page-settings'] = esc_html__('Settings', 'woo-coupon-usage');

      // Custom Tabs (Pro) - include placeholders (actual visibility handled elsewhere) for ordering
        if( wcu_fs()->can_use_premium_code() ) {
          $tabsnumber = wcusage_get_setting_value('wcusage_field_custom_tabs_number', '2');
          for ($i = 1; $i <= $tabsnumber; $i++) {
            if(isset($options['wcusage_field_custom_tabs'][$i]['name']) && $options['wcusage_field_custom_tabs'][$i]['name']) {
              $candidate_tabs['tab-custom-'.$i] = "Custom Tab $i: ".$options['wcusage_field_custom_tabs'][$i]['name'];
            }
          }
        }

      // If stored tab order is missing any candidate tabs, reset it
      $candidate_tab_keys = array_keys($candidate_tabs);
      $stored_tabs_array = $stored_tabs_order ? explode(',', $stored_tabs_order) : array();
      $missing_tabs = array_diff($candidate_tab_keys, $stored_tabs_array);
      if(!$stored_tabs_order || !empty($missing_tabs)) {
        $stored_tabs_order = implode(',', $candidate_tab_keys);
        update_option('wcusage_dashboard_tabs_layout', $stored_tabs_order);
        $stored_tabs_array = $candidate_tab_keys;
      }

      // Helper to render toggle using existing function without its <p> wrapper
      if(!function_exists('wcusage_render_inline_toggle')) {
        function wcusage_render_inline_toggle($name, $default, $label='') {
          ob_start();
          wcusage_setting_toggle_option($name, $default, $label, '0px');
          $html = ob_get_clean();
          // Strip outer <p ...>...</p>
          if(preg_match('/<p[^>]*>(.*)<\/p>/sU', $html, $m)) {
            $html = $m[1];
          }
          return $html;
        }
      }

      // Helper functions to get settings content for each tab
      if(!function_exists('wcusage_get_statistics_tab_settings')) {
        function wcusage_get_statistics_tab_settings() {
          ob_start();
          ?>
          <div style="display: block; float: right; width: 500px;">
            <p><strong style="font-size: 18px;"><?php echo esc_html__( 'Section Layout', 'woo-coupon-usage' ); ?>:</strong></p>
            <p><?php echo esc_html__( 'Customise the order of sections displayed on the "Statistics" tab.', 'woo-coupon-usage' ); ?></p>
            <br/>
            <style>
            .wcusage-section-sortable-placeholder {
                height: 50px !important;
                margin: 5px 0 !important;
                background: #f0f8ff !important;
                border: 2px dashed #007cba !important;
                border-radius: 4px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                list-style: none !important;
            }
            .wcusage-section-sortable-placeholder:before {
                content: "Drop section here";
                color: #007cba;
                font-weight: 500;
                font-size: 12px;
            }
            #wcusage-section-order li.ui-sortable-helper {
                transform: rotate(3deg);
                box-shadow: 0 5px 15px rgba(0,0,0,0.15) !important;
                z-index: 1000 !important;
            }
          /* Ensure list items stay on top and are fully draggable */
          #wcusage-section-order li { position: relative; z-index: 1; }
            </style>
            <script>
            (function($){
              function wcusage_init_stats_sortable(){
                var $list = $('#wcusage-section-order');
                if(!$list.length){ return; }
                // Recreate to avoid stale init after DOM changes
                if($list.data('ui-sortable')){ try { $list.sortable('destroy'); } catch(e) {}
                }
                $list.sortable({
                  placeholder: 'wcusage-section-sortable-placeholder',
                  tolerance: 'pointer',
                  cursor: 'move',
                  axis: 'y',
                  opacity: 0.8,
                  start: function(e, ui){
                    ui.placeholder.height(ui.item.outerHeight());
                  },
                  update: function(){
                    var sectionOrder = $list.sortable('toArray').join(',');
                    var $layoutInput = jQuery('#wcusage_statistics_layout');
                    $layoutInput.val(sectionOrder);
                    var legacyEnabled = jQuery('#wcusage_field_settings_legacy').is(':checked');
                    if (!legacyEnabled) {
                      if (typeof wcu_ajax_update_the_options === 'function') {
                        wcu_ajax_update_the_options($layoutInput, 'id', 'wcu-update-text', 1, '', 'textarea, input[type=text], input[type=number], input[type=password], input[type=radio], input[type=color], select');
                      } else {
                        $layoutInput.trigger('change');
                      }
                    }
                  }
                });
                $list.disableSelection();
              }
              window.wcusage_init_stats_sortable = wcusage_init_stats_sortable;
              $(function(){ wcusage_init_stats_sortable(); });
            })(jQuery);
            </script>
            <?php
            $options = get_option('wcusage_options');
            $section_order = isset($options['wcusage_statistics_layout']) ? $options['wcusage_statistics_layout'] : '';
            $sections = array(
                'section_couponinfo' => esc_html__('Coupon Info', 'woo-coupon-usage'),
                'section_commissionamounts' => esc_html__('Commission Earnings', 'woo-coupon-usage'),
                'section_commissiongraphs' => esc_html__('Commission Graph', 'woo-coupon-usage'),
                'section_latestreferrals' => esc_html__('Latest Referrals', 'woo-coupon-usage'),
                'section_commissionpayouts' => esc_html__('Commission Payouts', 'woo-coupon-usage'),
            );
            if(!$section_order) {
              $section_order = implode(',', array_keys($sections));
            }
            $section_order_array = explode(',', $section_order);
            echo '<ul id="wcusage-section-order" class="wcusage-sortable">';
            foreach ($section_order_array as $section_key) {
                if (array_key_exists($section_key, $sections)) {
                    echo '<li id="' . esc_attr($section_key) . '">';
                    echo '<span class="dashicons dashicons-move" style="cursor:move;opacity:0.7;"></span>';
                    echo ' <span>' . esc_html($sections[$section_key]) . '</span>';
                    echo '</li>';
                }
            }
            echo '</ul>';
            ?>
            <div style="display: none">
              <?php wcusage_setting_text_option("wcusage_statistics_layout", "", "", "0px"); ?>
            </div>
            <script>
            (function($){
              function toggleStatsSaveButton(){
                var legacyEnabled = jQuery('#wcusage_field_settings_legacy').is(':checked');
                jQuery('#wcusage_statistics_layout_save_btn').toggle(!!legacyEnabled);
              }
              $(function(){
                toggleStatsSaveButton();
                jQuery(document).on('change', '#wcusage_field_settings_legacy', toggleStatsSaveButton);
                jQuery('#wcusage_statistics_layout_save_btn').on('click', function(){
                  var $list = jQuery('#wcusage-section-order');
                  var sectionOrder = $list.sortable ? $list.sortable('toArray').join(',') : jQuery('#wcusage_statistics_layout').val();
                  var $layoutInput = jQuery('#wcusage_statistics_layout');
                  $layoutInput.val(sectionOrder);
                  if (typeof wcu_ajax_update_the_options === 'function') {
                    wcu_ajax_update_the_options($layoutInput, 'id', 'wcu-update-text', 1, '', 'textarea, input[type=text], input[type=number], input[type=password], input[type=radio], input[type=color], select');
                    // Show a tiny saved hint (the global handler also shows messages)
                    var $msg = jQuery('#wcusage_statistics_layout_saved_msg');
                    $msg.stop(true,true).fadeIn(100, function(){
                      var self = this; setTimeout(function(){ jQuery(self).fadeOut(200); }, 1200);
                    });
                  } else {
                    // Fallback: submit change event (may be ignored by guard, but harmless)
                    $layoutInput.trigger('change');
                  }
                });
              });
            })(jQuery);
            </script>
          </div>
          
          <div style="display: block; float: left; width: 50%;">
            
            <!-- Show Coupon Info -->
            <?php wcusage_setting_toggle_option('wcusage_field_statistics_couponinfo', 1, esc_html__( 'Show "Coupon Info" summary.', 'woo-coupon-usage' ), '0px'); ?>
            
            <br/>

            <?php wcusage_setting_text_option("wcusage_field_text", "", esc_html__( 'Custom Text / Information', 'woo-coupon-usage' ), "0px"); ?>
            <i><?php echo esc_html__( 'Displayed at top the "statistics" section on the coupon affiliate dashboard page. HTML tags enabled.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Show Commission Earnings-->
            <?php wcusage_setting_toggle_option('wcusage_field_statistics_commissionearnings', 1, esc_html__( 'Show "Commission Earnings" summary with toggles.', 'woo-coupon-usage' ), '0px'); ?>

            <?php wcusage_setting_toggle('.wcusage_field_statistics_commissionearnings', '.wcu-field-statistics-commissionearnings'); ?>
            <span class="wcu-field-statistics-commissionearnings">

              <br/>

              <!-- Show Total Sales-->
              <?php wcusage_setting_toggle_option('wcusage_field_statistics_commissionearnings_total', 1, esc_html__( 'Show "Total Sales" box.', 'woo-coupon-usage' ), '40px'); ?>

              <br/>

              <!-- Show Total Discounts -->
              <?php wcusage_setting_toggle_option('wcusage_field_statistics_commissionearnings_discounts', 1, esc_html__( 'Show "Total Discounts" box.', 'woo-coupon-usage' ), '40px'); ?>

              <br/>

              <!-- Show Total Commission -->
              <?php wcusage_setting_toggle_option('wcusage_field_statistics_commissionearnings_commission', 1, esc_html__( 'Show "Total Commission" box.', 'woo-coupon-usage' ), '40px'); ?>

            </span>

            <br/>

            <!-- Toggle Between Stats Types -->
            <p style="margin-bottom: -5px; margin-left: 0px;">
              <?php
            $wcusage_field_which_toggle = wcusage_get_setting_value('wcusage_field_which_toggle', '1');
            $checked1 = ( $wcusage_field_which_toggle == '0' ? ' checked="checked"' : '' );
            $checked2 = ( $wcusage_field_which_toggle == '1' || $wcusage_field_which_toggle == '' ? ' checked="checked"' : '' );
            ?>
            <strong><label for="scales"><?php echo esc_html__( 'What toggles should be shown for statistics and line graphs?', 'woo-coupon-usage' ); ?></label></strong>
              <br/>
              <label class="switch">
                  <input type="radio" value="0" id="wcusage_field_which_toggle" data-custom="custom" name="wcusage_options[wcusage_field_which_toggle]" <?php echo esc_html($checked1); ?>>
              <span class="slider round">
                <span class="on"><span class="fa-solid fa-check"></span></span>
                <span class="off"></span>
              </span>
              </label>
              <strong style="display: inline-block;"><label for="scales"><?php echo esc_html__( 'All-time', 'woo-coupon-usage' ); ?> | <?php echo esc_html__( 'Last 30 Days', 'woo-coupon-usage' ); ?> | <?php echo esc_html__( 'Last 7 Days', 'woo-coupon-usage' ); ?></label></strong>
              <br/>
              <label class="switch">
                  <input type="radio" value="1" id="wcusage_field_which_toggle" data-custom="custom" name="wcusage_options[wcusage_field_which_toggle]" <?php echo esc_html($checked2); ?>>
              <span class="slider round">
                <span class="on"><span class="fa-solid fa-check"></span></span>
                <span class="off"></span>
              </span>
              </label>
              <strong style="display: inline-block;"><label for="scales"><?php echo esc_html__( 'All-time', 'woo-coupon-usage' ); ?> | <?php echo esc_html__( 'This Month', 'woo-coupon-usage' ); ?> | <?php echo esc_html__( 'Last Month', 'woo-coupon-usage' ); ?></label></strong>
            </p>

            <br/>

            <!-- Show Latest Referrals -->
            <?php wcusage_setting_toggle_option('wcusage_field_statistics_latest', 1, esc_html__( 'Show "Latest Referrals" summary.', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'Show a summary of the 5 latest orders/referrals on the "Statistics" tab.', 'woo-coupon-usage' ); ?></i><br/>
            
            <script>
            jQuery(document).ready(function($) {
              if ( $('#wcusage_field_statistics_latest').is(':checked') ) {
                $('#section_latestreferrals').css('display', 'block');
              } else {
                $('#section_latestreferrals').css('display', 'none');
              }
              $('#wcusage_field_statistics_latest').change(function() {
                if ( $(this).is(':checked') ) {
                  $('#section_latestreferrals').css('display', 'block');
                } else {
                  $('#section_latestreferrals').css('display', 'none');
                }
              });
            });
            </script>

            <br/>

            <div <?php if ( !wcu_fs()->can_use_premium_code() ) {?>class="pro-settings-hidden" title="Available with Pro version."<?php } ?>>

              <!-- Show Commission Payouts -->
              <?php $probrackets = ( wcu_fs()->can_use_premium_code() ? "" : " (PRO)" ); ?>
              <?php wcusage_setting_toggle_option('wcusage_field_statistics_commissionpayouts', 1, esc_html__( 'Show "Commission Payouts" summary.', 'woo-coupon-usage' ) . esc_html($probrackets), '0px'); ?>
              <i><?php echo esc_html__( 'Show a payouts summary of the "Unpaid Commission", "Pending Payments", "Completed Payments".', 'woo-coupon-usage' ); ?></i><br/>
              
              <script>
              jQuery(document).ready(function($) {
                if ( $('#wcusage_field_statistics_commissionpayouts').is(':checked') ) {
                  $('#section_commissionpayouts').css('display', 'block');
                } else {
                  $('#section_commissionpayouts').css('display', 'none');
                }
                $('#wcusage_field_statistics_commissionpayouts').change(function() {
                  if ( $(this).is(':checked') ) {
                    $('#section_commissionpayouts').css('display', 'block');
                  } else {
                    $('#section_commissionpayouts').css('display', 'none');
                  }
                });
                <?php if( !wcu_fs()->can_use_premium_code() ) {?>
                  $('#section_commissionpayouts').css('display', 'none');
                <?php } ?>
              });
              </script>
              <br/>

              <!-- Show Commission Graphs -->
              <?php wcusage_setting_toggle_option('wcusage_field_show_graphs', 1, esc_html__( 'Show "Commission Graphs".', 'woo-coupon-usage' ) . esc_html($probrackets), '0px'); ?>
              <i><?php echo esc_html__( 'These are line graphs that show the commission earnings for every day in the past 90 days, 30 days or 7 days.', 'woo-coupon-usage' ); ?></i><br/>
              
              <script>
              jQuery(document).ready(function($) {
                if ( $('#wcusage_field_show_graphs').is(':checked') ) {
                  $('#section_commissiongraphs').css('display', 'block');
                } else {
                  $('#section_commissiongraphs').css('display', 'none');
                }
                $('#wcusage_field_show_graphs').change(function() {
                  if ( $(this).is(':checked') ) {
                    $('#section_commissiongraphs').css('display', 'block');
                  } else {
                    $('#section_commissiongraphs').css('display', 'none');
                  }
                });
                <?php if( !wcu_fs()->can_use_premium_code() ) {?>
                  $('#section_commissiongraphs').css('display', 'none');
                <?php } ?>
              });
              </script>

            </div>
            
          </div>
          <div style="clear:both;"></div>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_orders_tab_settings')) {
        function wcusage_get_orders_tab_settings() {
          ob_start();
          ?>
          <!-- Recent Orders Number -->
          <?php wcusage_setting_number_option('wcusage_field_orders', '10', esc_html__( 'Default amount of "latest orders" to show:', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'Amount of orders to show on the affiliate dashboard by default.', 'woo-coupon-usage' ); ?></i>

          <br/><br/>

          <!-- Max Orders Number -->
          <?php wcusage_setting_number_option('wcusage_field_max_orders', '250', esc_html__( 'Maximum amount of "latest orders" to show at once:', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'The maximum number of orders to show when filtered by date. Too many could make it take significantly longer to load.', 'woo-coupon-usage' ); ?></i>

          <br/><br/>

          <!-- Show order ID. -->
          <?php wcusage_setting_toggle_option('wcusage_field_orderid', 0, esc_html__( 'Show order "ID".', 'woo-coupon-usage' ), '0px'); ?>
            
          <?php wcusage_setting_toggle('.wcusage_field_orderid', '.wcu-field-orders-id-show'); ?>
          <span class="wcu-field-orders-id-show">
            <?php wcusage_setting_toggle_option('wcusage_field_orderid_click', 0, esc_html__( 'Make the order "ID" clickable for admins.', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'If the user is an admin, then the ID will also be clickable to open the order page in the backend.', 'woo-coupon-usage' ); ?></i><br/>
          </span>

          <br/>

          <!-- Show order "date". -->
          <?php wcusage_setting_toggle_option('wcusage_field_date', 1, esc_html__( 'Show order "date".', 'woo-coupon-usage' ), '0px'); ?>

          <!-- Show order "time". -->
          <?php wcusage_setting_toggle_option('wcusage_field_time', 0, esc_html__( 'Show order "time".', 'woo-coupon-usage' ), '0px'); ?>

          <!-- Show order "status". -->
          <?php wcusage_setting_toggle_option('wcusage_field_status', 1, esc_html__( 'Show order "status".', 'woo-coupon-usage' ), '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_status', '.wcu-field-status-tables'); ?>
          <span class="wcu-field-status-tables">
            <!-- Show "Status" totals. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_orders_table_status_totals', 1, esc_html__( 'Show order status totals below the table.', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'When selected, below the orders table it will show the total number of orders for each status. The "Status" column needs to be enabled.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <!-- Show "Status" filter. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_orders_table_filter_status', 1, esc_html__( 'Show "Status" dropdown filter.', 'woo-coupon-usage' ), '40px'); ?>
            <i style="margin-left: 40px;"><?php echo esc_html__( 'When selected, a "Status" dropdown will be shown as an option when filtering by date range. Will only show if you have more than 1 status enabled.', 'woo-coupon-usage' ); ?></i><br/>
          </span>

          <br/>

          <!-- Show order "total". -->
          <?php wcusage_setting_toggle_option('wcusage_field_amount', 1, esc_html__( 'Show order "total".', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show order "discount". -->
          <?php wcusage_setting_toggle_option('wcusage_field_amount_saved', 1, esc_html__( 'Show order "discount".', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show order "country". -->
          <?php wcusage_setting_toggle_option('wcusage_field_ordercountry', 0, esc_html__( 'Show customer "country".', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show order "city". -->
          <?php wcusage_setting_toggle_option('wcusage_field_ordercity', 0, esc_html__( 'Show customer "city".', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show customer "first name". -->
          <?php wcusage_setting_toggle_option('wcusage_field_ordername', 0, esc_html__( 'Show customer "first name".', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show customer "last name". -->
          <?php wcusage_setting_toggle_option('wcusage_field_ordernamelast', 0, esc_html__( 'Show customer "last name".', 'woo-coupon-usage' ), '0px'); ?>

          <i>
          <?php echo esc_html__( 'Beware of privacy issues when showing customer names. This is not recommended.', 'woo-coupon-usage' ); ?>
          </i><br/>

          <br/>

          <!-- Show shipping costs. -->
          <?php wcusage_setting_toggle_option('wcusage_field_show_shipping', 0, esc_html__( 'Show "shipping" costs column.', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show tax costs. -->
          <?php wcusage_setting_toggle_option('wcusage_field_show_order_tax', 0, esc_html__( 'Show order "tax" column.', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show list of products for orders. -->
          <?php wcusage_setting_toggle_option('wcusage_field_list_products', 1, esc_html__( 'Show products summary/list for orders ("MORE" column).', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <!-- Show the combined totals for all orders within the selected date range. -->
          <?php wcusage_setting_toggle_option('wcusage_field_show_orders_table_totals', 1, esc_html__( 'Show the combined totals for all orders within the selected date range.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'When selected, the totals for all orders within the selected date range will be shown in a new row at the bottom of the recent orders and monthly summary table.', 'woo-coupon-usage' ); ?></i><br/>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_referral_urls_tab_settings')) {
        function wcusage_get_referral_urls_tab_settings() {
          ob_start();
          ?>
          <!-- Enable Referral Links -->
          <?php wcusage_setting_toggle_option('wcusage_field_urls_enable', 1, esc_html__( 'Enable Referral Links & Click Tracking', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <p>
            <?php echo esc_html__( 'Referral URL settings are configured in the "Referral Links" settings tab.', 'woo-coupon-usage' ); ?>
          </p>

          <br/>

          <p>
            <button class="button" onclick="wcusage_go_to_settings('#tab-urls', '');"><?php esc_html_e( 'Go to Referral Links Settings', 'woo-coupon-usage' ); ?></button>
          </p>

          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_settings_tab_settings')) {
        function wcusage_get_settings_tab_settings() {
          ob_start();
          ?>
          <!-- Show "Account Details" section in the "Settings" tab. -->
          <?php wcusage_setting_toggle_option('wcusage_field_show_settings_tab_account', 1, esc_html__( 'Show "Account Details" section in the "Settings" tab.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'This will show the WooCommerce "Account Details" fields directly in the "settings" tab on the affiliate dashboard, along with a logout link.', 'woo-coupon-usage' ); ?></i>

          <br/><br/>

          <?php wcusage_setting_toggle_option('wcusage_field_show_settings_tab_gravatar', 1, esc_html__( 'Show Gravatar in the "Settings" tab.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'This will show the Gravatar image and link to edit their gravatar in the "Settings" tab on the affiliate dashboard.', 'woo-coupon-usage' ); ?></i>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_monthly_tab_settings')) {
        function wcusage_get_monthly_tab_settings() {
          ob_start();
          ?>
          <!-- Default number of months to show -->
          <?php wcusage_setting_number_option('wcusage_field_months_table_total', '6', esc_html__( 'Default number of months to show', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'How many months to show on the "monthly summary" table by default.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Monthly Table Column Settings -->
          <?php if ( wcu_fs()->can_use_premium_code() ) { ?>

            <!-- Show "Month" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_date', 1, esc_html__( 'Show "Month" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "Order Count" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_order_count', 1, esc_html__( 'Show "Order Count" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "Total Sales" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_order', 1, esc_html__( 'Show "Total Sales" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "Discounts" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_discount', 1, esc_html__( 'Show "Discounts" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "Total" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_totalwithdiscount', 1, esc_html__( 'Show "Total" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "Commission" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_commission', 1, esc_html__( 'Show "Commission" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "% Change" Column. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_change', 1, esc_html__( 'Show "% Change" Column.', 'woo-coupon-usage' ), '0px'); ?>

            <!-- Show "More" column to show/hide "List of products purchased" section. -->
            <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_col_more', 1, esc_html__( 'Show "More" Column (Toggle for products summary/list).', 'woo-coupon-usage' ), '0px'); ?>

          <?php } ?>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_creatives_tab_settings')) {
        function wcusage_get_creatives_tab_settings() {
          ob_start();
          ?>
          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_creatives_enable', 1, 'Enable "creatives" features.', '0px'); ?>
            <i><?php echo esc_html__( 'This will enable "Creatives" in the admin menu, where you can upload your own banners (creatives) for affiliates to use.', 'woo-coupon-usage' ); ?></i><br/>
            <i><?php echo esc_html__( 'A new "creatives" tab will be shown in the affiliate dashboard displaying these creatives, including a HTML code for them to copy and paste, to show the banner on their own site (with the referral link).', 'woo-coupon-usage' ); ?></i><br/>
          </p>

          <?php wcusage_setting_toggle('.wcusage_field_creatives_enable', '.wcu-field-section-creatives'); ?>
          <span class="wcu-field-section-creatives">

            <br/>

            <p><?php echo esc_html__( 'To customise the "Creatives" tab, please go to the creatives settings:', 'woo-coupon-usage' ); ?> <a href="#" onclick="wcusage_go_to_settings('#tab-creatives', '#affiliate-reports-settings');">Click Here</a></p>

          </span>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_rates_tab_settings')) {
        function wcusage_get_rates_tab_settings() {
          ob_start();
          ?>
          <p>
            <?php wcusage_setting_text_option("wcusage_field_rates_name", "", esc_html__( 'Custom Tab Name', 'woo-coupon-usage' ) . " ('Rates')", "0px"); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_text_option("wcusage_field_rates_header", "", esc_html__( 'Custom Tab Header', 'woo-coupon-usage' ) . " ('Product Commission Rates')", "0px"); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_text_option("wcusage_field_rates_text", "", esc_html__( 'Custom Text / Information', 'woo-coupon-usage' ), "0px"); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_number_option("wcusage_field_rates_per_page", "20", esc_html__( 'Products Per Page', 'woo-coupon-usage' ), "0px"); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_all_variations', 0, esc_html__( 'Show All Product Variations', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'If enabled, all variations of a product will be shown in the table as seperate rows.', 'woo-coupon-usage' ); ?></i>
            <br/>
            <i><?php echo esc_html__( 'If disabled, only the parent product will be shown - and variations that have per-variation commission rates set different to the parent.', 'woo-coupon-usage' ); ?></i>
          </p>

          <?php wcusage_setting_toggle('.wcusage_field_rates_show_all_variations', '.wcu-field-rates-show-all-variations'); ?>
          <span class="wcu-field-rates-show-all-variations" style="padding-left: 40px; display: block;">

            <br/>

            <p>
              <?php wcusage_setting_toggle_option('wcusage_field_rates_hide_variations_parent', 0, esc_html__( 'Hide Parent Product', 'woo-coupon-usage' ), '40px'); ?>
              <i style="margin-left: 40px;"><?php echo esc_html__( 'If enabled, the parent product will be hidden from the table if at-least 1 variation.', 'woo-coupon-usage' ); ?></i>
              <br/>
              <i style="margin-left: 40px;"><?php echo esc_html__( 'If disabled, the parent product will be shown in the table.', 'woo-coupon-usage' ); ?></i>
            </p>

          </span>

          <br/>

          <!-- Product category -->
          <p>
            <strong><label for="wcusage_field_rates_category"><?php echo esc_html__( 'Product Category', 'woo-coupon-usage' ); ?>:</label></strong>
            <br/>
            <?php
            // Fetch product categories with error handling
            $args = array(
              'taxonomy'   => 'product_cat',
              'hide_empty' => false,
            );
            $product_categories = get_terms($args);
            ?>
            <select id="wcusage_field_rates_category" name="wcusage_options[wcusage_field_rates_category]">
              <option value=""><?php echo esc_html__( 'All Categories', 'woo-coupon-usage' ); ?></option>
              <?php
              // Check if categories were retrieved successfully
              if (!is_wp_error($product_categories) && !empty($product_categories)) {
                foreach ($product_categories as $category) {
                  // Safely retrieve the saved setting value, defaulting to empty string if undefined
                  $selected_category = function_exists('wcusage_get_setting_value') ? wcusage_get_setting_value('wcusage_field_rates_category', '') : '';
                  ?>
                  <option value="<?php echo esc_attr($category->term_id); ?>" 
                          <?php selected($selected_category, $category->term_id, true); ?>>
                    <?php echo esc_html($category->name); ?>
                  </option>
                  <?php
                }
              } else {
                // Fallback if no categories are found or an error occurs
                ?>
                <option value=""><?php echo esc_html__( 'No categories found', 'woo-coupon-usage' ); ?></option>
                <?php
              }
              ?>
            </select>
            <br/>
            <i><?php echo esc_html__( 'Select a product category to filter the products shown in the table.', 'woo-coupon-usage' ); ?></i>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_search', 1, esc_html__( 'Show Search Field', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_id', 1, esc_html__( 'Show "ID" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_image', 1, esc_html__( 'Show "Image" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_product', 1, esc_html__( 'Show "Product" Column', 'woo-coupon-usage' ), '0px'); ?>  
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_rate', 1, esc_html__( 'Show "Commission Rate" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_price', 1, esc_html__( 'Show "Product Price" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php
            // Product price display type (full vs discounted)
            wcusage_setting_select_option(
              'wcusage_field_rates_price_display',
              'full',
              esc_html__( 'Product Price Display', 'woo-coupon-usage' ),
              '0px',
              array(
                'full' => esc_html__( 'Show full price (before discount)', 'woo-coupon-usage' ),
                'discounted' => esc_html__( 'Show discounted price (with coupon)', 'woo-coupon-usage' )
              )
            );
            ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_commission', 1, esc_html__( 'Show "Commission Per Product" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>

          <br/>

          <p>
            <?php wcusage_setting_toggle_option('wcusage_field_rates_show_link', 1, esc_html__( 'Show "Referral Link" Column', 'woo-coupon-usage' ), '0px'); ?>
          </p>
          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_bonuses_tab_settings')) {
        function wcusage_get_bonuses_tab_settings() {
          ob_start();
          ?>
          <!-- Enable Referral bonuses -->
          <?php wcusage_setting_toggle_option('wcusage_field_bonuses_enable', 0, esc_html__( 'Enable Performance Bonuses', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <p><?php echo esc_html__( 'Performance bonus settings are configured in the "Bonuses" settings tab.', 'woo-coupon-usage' ); ?></p>
          <br/>

          <p>
            <button class="button" onclick="wcusage_go_to_settings('#tab-bonuses', '');"><?php esc_html_e( 'Go to Bonuses Settings', 'woo-coupon-usage' ); ?></button>
          </p>

          <?php
          return ob_get_clean();
        }
      }

      if(!function_exists('wcusage_get_payouts_tab_settings')) {
        function wcusage_get_payouts_tab_settings() {
          ob_start();
          ?>
          <p><?php echo esc_html__( 'Payout settings are configured in the "Payouts" settings tab.', 'woo-coupon-usage' ); ?></p>
          <br/>
          <p>
            <button class="button" onclick="wcusage_go_to_settings('#tab-payouts', '');"><?php esc_html_e( 'Go to Payouts Settings', 'woo-coupon-usage' ); ?></button>
          </p>
          <?php
          return ob_get_clean();
        }
      }

      // Map tab IDs to their existing core enable options (for active toggles)
      $tab_option_map = array(
        'tab-page-stats'    => 'wcusage_field_show_statistics_tab', // now toggleable
        'tab-page-monthly'  => 'wcusage_field_show_months_table',
        'tab-page-orders'   => 'wcusage_field_show_order_tab',
        'tab-page-links'    => 'wcusage_field_urls_tab_enable',
        'tab-page-creatives'=> 'wcusage_field_creatives_enable',
        'tab-page-rates'    => 'wcusage_field_rates_enable',
        'tab-page-payouts'  => 'wcusage_field_payouts_enable',
        'tab-page-bonuses'  => 'wcusage_field_bonuses_tab_enable',
        'tab-page-settings' => 'wcusage_field_show_settings_tab_show'
      );

      echo '<div id="wcusage-dashboard-tabs-order" class="wcusage-sortable wcusage-tabs-order-list" style="max-width:100%;">';
      foreach($stored_tabs_array as $tab_key) {
        if(!isset($candidate_tabs[$tab_key])) { continue; }
        $label = $candidate_tabs[$tab_key];
        $linked_option = isset($tab_option_map[$tab_key]) ? $tab_option_map[$tab_key] : null;
        
        // Check if tab is enabled
        $is_enabled = true;
        if($linked_option) {
          $current_value = wcusage_get_setting_value($linked_option, '1');
          $is_enabled = ($current_value == '1');
        }
        
        // Generate unique IDs for each tab's settings section
        $settings_section_id = 'wcu_tab_settings_' . str_replace('-', '_', $tab_key);
        
        // Add styling for disabled tabs
        $box_style = 'border:1px solid #ddd;margin:10px 0;background:#fff;';
        $header_style = 'display:flex;align-items:center;gap:10px;padding:15px;background:#f9f9f9;border-bottom:1px solid #ddd;';
        if(!$is_enabled) {
          $box_style = 'border:1px solid #ddd;margin:10px 0;background:#f5f5f5;opacity:0.6;';
          $header_style = 'display:flex;align-items:center;gap:10px;padding:15px;background:#e9e9e9;border-bottom:1px solid #ddd;';
        }
        
        echo '<div id="'.esc_attr($tab_key).'" class="wcusage-tab-item" style="'.$box_style.'">';
        
        // Header with drag handle, toggle, label, and show/hide button
        echo '<div style="'.$header_style.'">';
        echo '<span class="dashicons dashicons-move" style="cursor:move;opacity:0.7;" title="'.esc_html__('Drag to reorder','woo-coupon-usage').'"></span>';

        if(strpos($tab_key,'tab-custom-') === 0) {
          echo '<input type="checkbox" checked disabled style="margin:0;" />';
          echo '<strong style="flex:1;font-size:15px;">'.esc_html($label).'</strong>';
          echo '<span style="font-size:11px;opacity:0.7;margin-right:auto;">('.esc_html__('custom','woo-coupon-usage').')</span>';
          $settings_button_disabled = !$is_enabled ? 'disabled' : '';
          echo '<button type="button" class="wcu-showhide-button" onclick="wcusage_toggle_settings(\''.esc_attr($settings_section_id).'\')" style="font-size:14px;padding:6px 12px;" '.$settings_button_disabled.'>'.esc_html__('Show Settings','woo-coupon-usage').' <span class="fa-solid fa-arrow-down"></span></button>';
        } elseif(!$linked_option) {
          echo '<input type="checkbox" checked disabled style="margin:0;" />';
          echo '<strong style="flex:1;font-size:15px;">'.esc_html($label).'</strong>';
          echo '<span style="font-size:11px;opacity:0.7;margin-right:auto;">('.esc_html__('always on','woo-coupon-usage').')</span>';
          echo '<button type="button" class="wcu-showhide-button" onclick="wcusage_toggle_settings(\''.esc_attr($settings_section_id).'\')" style="font-size:14px;padding:6px 12px;">'.esc_html__('Show Settings','woo-coupon-usage').' <span class="fa-solid fa-arrow-down"></span></button>';
        } else {
          $toggle_html = wcusage_render_inline_toggle($linked_option, '1', '');
          // Add onchange event to handle toggle changes
          $toggle_html = str_replace('<input', '<input onchange="wcusage_handle_tab_toggle(this, \''.esc_attr($tab_key).'\')"', $toggle_html);
          echo '<div style="display:flex;align-items:center;gap:6px;">'.$toggle_html.'</div>';
          echo '<strong style="flex:1;font-size:15px;">'.esc_html($label).'</strong>';
          $settings_button_disabled = !$is_enabled ? 'disabled' : '';
          echo '<button type="button" class="wcu-showhide-button" onclick="wcusage_toggle_settings(\''.esc_attr($settings_section_id).'\')" style="font-size:14px;padding:6px 12px;" '.$settings_button_disabled.'>'.esc_html__('Show Settings','woo-coupon-usage').' <span class="fa-solid fa-arrow-down"></span></button>';
        }
        echo '</div>';
        
        // Settings content for each tab
        echo '<div id="'.esc_attr($settings_section_id).'" class="wcu_section_settings" style="display:none;padding:20px;">';
        
        // Tab-specific settings content
        switch($tab_key) {
          case 'tab-page-stats':
            echo wcusage_get_statistics_tab_settings();
            break;
          case 'tab-page-orders':
            echo wcusage_get_orders_tab_settings();
            break;
          case 'tab-page-links':
            echo wcusage_get_referral_urls_tab_settings();
            break;
          case 'tab-page-settings':
            echo wcusage_get_settings_tab_settings();
            break;
          case 'tab-page-monthly':
            if(wcu_fs()->can_use_premium_code()) {
              echo wcusage_get_monthly_tab_settings();
            }
            break;
          case 'tab-page-creatives':
            if(wcu_fs()->can_use_premium_code()) {
              echo wcusage_get_creatives_tab_settings();
            }
            break;
          case 'tab-page-rates':
            if(wcu_fs()->can_use_premium_code()) {
              echo wcusage_get_rates_tab_settings();
            }
            break;
          case 'tab-page-bonuses':
            if(wcu_fs()->can_use_premium_code()) {
              echo wcusage_get_bonuses_tab_settings();
            }
            break;
          case 'tab-page-payouts':
            if(wcu_fs()->can_use_premium_code()) {
              echo wcusage_get_payouts_tab_settings();
            }
            break;
          default:
            if(strpos($tab_key,'tab-custom-') === 0) {
              echo '<p>'.esc_html__('Custom tab settings are configured in the "Tabs" settings section.','woo-coupon-usage').'</p>';
              echo '<br/><p><a href="#" onclick="wcusage_go_to_settings(\'#tab-custom-tabs\', \'\');" class="button button-primary">'.esc_html__('Go to Tabs Settings','woo-coupon-usage').'</a></p>';
            }
            break;
        }
        
        echo '</div>';
        echo '</div>';
      }
      echo '</div>';
      
      // Add JavaScript for toggle functionality
      echo '<script>
      function wcusage_toggle_settings(sectionId) {
        var section = document.getElementById(sectionId);
        var button = event.target.closest("button");
        var arrow = button.querySelector(".fa-solid");
        
        if (section.style.display === "none" || !section.style.display) {
          section.style.display = "block";
          button.innerHTML = "'.esc_js(__('Hide Settings','woo-coupon-usage')).' <span class=\"fa-solid fa-arrow-up\"></span>";
          // If this section contains the Statistics layout list, (re)initialize sortable
          try {
            if (typeof window.wcusage_init_stats_sortable === "function" && section.querySelector("#wcusage-section-order")) {
              window.wcusage_init_stats_sortable();
            }
          } catch(e) {}
        } else {
          section.style.display = "none";
          button.innerHTML = "'.esc_js(__('Show Settings','woo-coupon-usage')).' <span class=\"fa-solid fa-arrow-down\"></span>";
        }
      }

      // Function to handle tab toggle changes
      function wcusage_handle_tab_toggle(checkbox, tab_id) {
        var tabItem = document.getElementById(tab_id);
        var showButton = tabItem.querySelector(".wcu-showhide-button");
        var settingsSection = tabItem.querySelector(".wcu_section_settings");
        
        if (checkbox.checked) {
          // Enable the tab
          tabItem.style.opacity = "1";
          tabItem.style.background = "#fff";
          tabItem.querySelector("div").style.background = "#f9f9f9";
          if (showButton) showButton.disabled = false;
        } else {
          // Disable the tab
          tabItem.style.opacity = "0.6";
          tabItem.style.background = "#f5f5f5";
          tabItem.querySelector("div").style.background = "#e9e9e9";
          if (showButton) showButton.disabled = true;
          if (settingsSection && settingsSection.style.display === "block") {
            settingsSection.style.display = "none";
            if (showButton) showButton.innerHTML = "'.esc_js(__('Show Settings','woo-coupon-usage')).' <span class=\"fa-solid fa-arrow-down\"></span>";
          }
        }
      }

      function wcusage_go_to_settings(tab, section) {
        // Click on the custom tab
        jQuery(tab).click();
        // Wait a moment then scroll to the section
        setTimeout(function() {
          jQuery("html, body").animate({
            scrollTop: jQuery(section).offset().top - 100
          }, 500);
        }, 100);
      }
      </script>';
      ?>
      <div style="display:none;">
        <?php wcusage_setting_text_option('wcusage_dashboard_tabs_layout', '', '', '0px'); ?>
      </div>
      <br/>
    </div>
    
  <hr/>

  <?php echo wcu_admin_settings_showhide_toggle("wcu_show_section_other_tab", "wcu_section_other_tab", "Show", "Hide"); ?>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px; margin-bottom: 0;"></span>
    <?php echo esc_html__( 'Other Dashboard Settings', 'woo-coupon-usage' ); ?>
  </h3>

  <p>
    <?php echo esc_html__( 'These settings do not belong to a specific tab, but are other customisation options for the affiliate dashboard.', 'woo-coupon-usage' ); ?>
  </p>

  <br/>
  
  <button style="font-size: 14px; font-weight: normal;" class="wcu-showhide-button"
    type="button" onclick="wcusage_toggle_settings('wcu_section_other_tab')">
    <?php echo esc_html__('Show Settings', 'woo-coupon-usage'); ?> <span class='fa-solid fa-arrow-down'></span>
  </button>

  <br/><br/>

  <div class="wcu_section_settings" id="wcu_section_other_tab" style="display: none;">

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - Header', 'woo-coupon-usage' ); ?>:</h3>

    <?php wcusage_setting_text_option("wcusage_before_title", "", esc_html__( 'Coupon Title Prefix', 'woo-coupon-usage' ), "0px"); ?>
    <i><?php echo esc_html__( 'This will be shown before the coupon code shown in the header of the affiliate dashboard page, for example you could set it to "Coupon code:".', 'woo-coupon-usage' ); ?></i>

    <br/><br/><hr/>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - Login Form', 'woo-coupon-usage' ); ?>:</h3>

    <?php wcusage_setting_toggle_option('wcusage_field_loginform', 1, esc_html__( 'Show WooCommerce login form on affiliate dashboard page when users are logged out.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This will allow affiliate users to login to the dashboard if they visit the base dashboard URL.', 'woo-coupon-usage' ); ?></i><br/>

    <br/><hr/>

    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard - Profile', 'woo-coupon-usage' ); ?>:</h3>

    <!-- Show logout link on affiliate dashboard (top right). -->
    <?php wcusage_setting_toggle_option('wcusage_field_show_logout_link', 1, esc_html__( 'Show logout link on affiliate dashboard (top right).', 'woo-coupon-usage' ), '0px'); ?>

    <br/>

    <!-- Show username on affiliate dashboard (top right). -->
    <?php wcusage_setting_toggle_option('wcusage_field_show_username', 1, esc_html__( 'Show username on affiliate dashboard (top right).', 'woo-coupon-usage' ), '0px'); ?>

    <br/><hr/>

    <div <?php if ( !wcu_fs()->can_use_premium_code() ) {?>class="pro-settings-hidden" title="Available with Pro version."<?php } ?>>

      <h3 id="wcu-setting-header-export"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Export to Excel Buttons', 'woo-coupon-usage' ); ?><?php echo esc_html($probrackets); ?>:</h3>

      <!-- Enable button to export an Excel file of "monthly summary" table. -->
      <?php wcusage_setting_toggle_option('wcusage_field_show_months_table_export', 1, esc_html__( 'Enable button to export an Excel file of "monthly summary" table.', 'woo-coupon-usage' ), '0px'); ?>

      <br/>

      <!-- Enable button to export an Excel file of "recent orders" table. -->
      <?php wcusage_setting_toggle_option('wcusage_field_show_orders_table_export', 1, esc_html__( 'Enable button to export an Excel file of "recent orders" table.', 'woo-coupon-usage' ), '0px'); ?>

    </div>

    <br/><hr/>

    <!-- Assign Affiliates to Coupons -->
    <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( '"My Account" Menu Link', 'woo-coupon-usage' ); ?>:</h3>

    <?php wcusage_setting_toggle_option('wcusage_field_account_tab', 1, esc_html__( 'Add an "Affiliate" menu link to the "My Account" page.', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'With this enabled, a new "Affiliate" link will appear on the users "My Account" page menu. This will take them to the affiliate dashboard page selected above.', 'woo-coupon-usage' ); ?></i>

    <?php wcusage_setting_toggle('.wcusage_field_account_tab', '.wcu-field-section-show-tab'); // Show or Hide ?>
    <span class="wcu-field-section-show-tab">

      <br/><br/>

      <?php wcusage_setting_toggle_option('wcusage_field_account_tab_affonly', 0, esc_html__( 'Hide link for non-affiliate users.', 'woo-coupon-usage' ), '30px'); ?>
      <i style="margin-left: 30px;"><?php echo esc_html__( 'With this enabled, the link will be hidden for users that are not assigned to a coupon.', 'woo-coupon-usage' ); ?></i>
      
      <br/><br/>

      <?php wcusage_setting_toggle_option('wcusage_field_account_tab_create', 0, esc_html__( 'Display the affiliate dashboard as a page within the "My Account" section.', 'woo-coupon-usage' ), '30px'); ?>
      <i style="margin-left: 30px;"><?php echo esc_html__( 'With this enabled, when the "Affiliate" tab is clicked, instead of redirecting to the normal affiliate dashboard page, it will show the affiliate dashboard as a page/section within "My Account".', 'woo-coupon-usage' ); ?></i>

    </span>

    <br/>

  </div>

</div>

 <?php
}

/**
 * Settings Section: Dashboard Page
 *
 */
add_action( 'wcusage_hook_setting_section_dashboard_page', 'wcusage_setting_section_dashboard_page' );
if( !function_exists( 'wcusage_setting_section_dashboard_page' ) ) {
  function wcusage_setting_section_dashboard_page() {

    $options = get_option( 'wcusage_options' );
    ?>

    <div class="affiliate-dashboard-page-settings">

    <?php if (!class_exists('SitePress') || isset($options['wcusage_dashboard_page'])) { ?>

      <!-- Dashboard Page Dropdown -->
      <p><strong><?php echo esc_html__( 'Affiliate Dashboard Page:', 'woo-coupon-usage' ); ?><?php if ( !$options['wcusage_dashboard_page'] ) { ?> <span class="dashicons dashicons-warning" title="Important" style="color: red;"></span><?php } ?></strong></p>
      <?php
      $dashboardpage = "";
      if ( isset($options['wcusage_dashboard_page']) ) {
          $dashboardpage = $options['wcusage_dashboard_page'];
      }
      // Check this page contains the [couponaffiliates] shortcode
      if ( $dashboardpage ) {
        $page = get_post($dashboardpage);
        if ( $page && !has_shortcode($page->post_content, 'couponaffiliates') ) {
          // Don't update on GET request - just display warning
          if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
            $options_update = get_option('wcusage_options');
            if ( ! is_array( $options_update ) ) {
              $options_update = array();
            }
            $options_update['wcusage_dashboard_page'] = "";
            update_option('wcusage_options', $options_update);
            $dashboardpage = $options_update['wcusage_dashboard_page'];
          }
        }
      }
      // If the page is not set, try to find it
      if ( !isset($options['wcusage_dashboard_page']) || !$dashboardpage ) {
        $dashboardpage = wcusage_get_coupon_shortcode_page_id();
        if($dashboardpage && $_SERVER['REQUEST_METHOD'] !== 'GET') {
          $options_update = get_option('wcusage_options');
          if ( ! is_array( $options_update ) ) {
            $options_update = array();
          }
          $options_update['wcusage_dashboard_page'] = $dashboardpage;
          update_option('wcusage_options', $options_update);
        }
      }
      // Show the dropdown
      $dropdown_args = array(
          'post_type'        => 'page',
          'selected'         => esc_html($dashboardpage),
          'name'             => 'wcusage_options[wcusage_dashboard_page]',
          'id'               => 'wcusage_dashboard_page',
          'value_field'      => 'wcusage_dashboard_page',
          'show_option_none' => '-',
      );
      foreach ( $dropdown_args as $key => $value ) {
        if ( is_string( $value ) ) {
            $dropdown_args[ $key ] = esc_attr( $value );
        }
      }
      wp_dropdown_pages( $dropdown_args );

      echo "<br/>";
      
      if($dashboardpage) {
        // Show the link
        echo "<a style='margin-top: 5px; display: inline-block;' id='dashboard_link' href='".esc_url(get_permalink($dashboardpage))."' target='_blank'>".esc_url(get_permalink($dashboardpage))."</a><br/>";
      }
      ?>

      <script type="text/javascript">
      // jQuery is assumed to be loaded in WordPress by default
      jQuery(document).ready(function($){
          $('#wcusage_dashboard_page').on('change', function(){
              var pageID = $(this).val();
              // Get the URL of the selected page using WordPress AJAX (Assuming you have an AJAX handler that returns the permalink of a page given its ID)
              jQuery.post(
                  '<?php echo esc_url(admin_url("admin-ajax.php")); ?>', 
                  {
                      'action': 'wcusage_get_permalink',
                      'page_id': pageID,
                      'nonce': '<?php echo wp_create_nonce("wcusage_get_permalink_nonce"); ?>'
                  }
              )
              .done(function(response){
                  if(!response) {
                    $('#dashboard_link').hide();
                  } else {
                    $('#dashboard_link').show();
                  }
                  $('#dashboard_link').attr('href', response);
                  $('#dashboard_link').text(response); 
              })
              .fail(function() {
                  alert('AJAX request failed');  // debugging line
              });
          });
      });
      </script>
      
    <?php } else { ?>

      <!-- Showing number input if WPML installed -->
      <?php wcusage_setting_number_option('wcusage_dashboard_page', '', esc_html__( 'Affiliate Dashboard Page (ID):', 'woo-coupon-usage' ), '0px'); ?>

    <?php } ?>

    <i><?php echo esc_html__( '(The page that has the [couponaffiliates] shortcode on.)', 'woo-coupon-usage' ); ?></i>

    <br/>

    <div class="setup-hide">

      <div class="dashboard_shortcode_check" style="margin-bottom: 0px; font-size: 12px; margin-top: 20px; color: red; display: none;">

      <?php
      $dashboardpage = wcusage_get_setting_value('wcusage_dashboard_page', '');
      if($dashboardpage) {
      ?>
      <?php echo esc_html__( '(ERROR) This page does not contain the shortcode:', 'woo-coupon-usage' ); ?> <strong>[couponaffiliates]</strong><br/>
      <?php echo esc_html__( 'Please add the shortcode to a new page, and select it from the dropdown above.', 'woo-coupon-usage' ); ?><br/>

      <?php echo esc_html__('Or you can click the button below to automatically generate the page for you:', 'woo-coupon-usage'); ?>

      <br/><br/>
      <?php } ?>

      <!-- Link to GET create_new_dashboard as 1 -->
      <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings&create_new_dashboard=1')); ?>"
      style="margin: 5px 0; display: inline-block;"
        <button type="button" name="submitnewpage" class="button button-secondary">
          <strong><?php echo esc_html__( "Generate Dashboard Page", "woo-coupon-usage" ); ?> <span class="fa-solid fa-circle-arrow-right"></span></strong>
        </button>
      </a>

      </div>

    </div>

    <br/>

    </div>
    <script>
    // If affiliate portal is enabled, hide the dashboard page settings
    jQuery(document).ready(function($) {
      if ( $('.wcusage_field_portal_enable').is(':checked') ) {
        $('.affiliate-dashboard-page-settings').hide();
      }
      $('.wcusage_field_portal_enable').change(function() {
        if ( $(this).is(':checked') ) {
          $('.affiliate-dashboard-page-settings').hide();
        } else {
          $('.affiliate-dashboard-page-settings').show();
        }
      });
      // Check if the selected page contains the shortcode
      function check_dashboard_page_shortcode() {
          var pageID = $('#wcusage_dashboard_page').val();
          if (!pageID) {
              $('.dashboard_shortcode_check').show();
              return;
          }
          $.post(
              '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
              {
                  'action': 'wcusage_check_dashboard_shortcode',
                  'page_id': pageID,
                  'nonce': '<?php echo wp_create_nonce("wcusage_check_dashboard_shortcode_nonce"); ?>'
              },
              function(response) {
                  if (response == 1) {
                      $('.dashboard_shortcode_check').hide();
                  } else {
                      $('.dashboard_shortcode_check').show();
                  }
              }
          );
      }
      // On change of the dropdown, check if the page contains the shortcode
      $('#wcusage_dashboard_page').on('change', function() {
          check_dashboard_page_shortcode();
      });
      // Generate a new dashboard page on button click
      $('#wcu-generate-dashboard-page').on('click', function() {
          // Disable button and change to spinner
          $(this).prop('disabled', true).html('<span class="spinner"></span> <?php echo esc_html__( 'Generating...', 'woo-coupon-usage' ); ?>');
          // Make the AJAX request to generate the page
          $.post(
              '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
              {
                  'action': 'wcusage_generate_dashboard_page'
              },
              function(response) {
                  if (response.success) {
                      // Add the new page to the dropdown
                      var newOption = $('<option></option>')
                          .val(response.data.page_id)
                          .text(response.data.page_title)
                          .prop('selected', true);
                      $('#wcusage_dashboard_page').append(newOption);
                      
                      // Update the link
                      $('#dashboard_link')
                          .attr('href', response.data.permalink)
                          .text(response.data.permalink);
                      
                      // Hide the error message since the new page has the shortcode
                      $('.dashboard_shortcode_check').hide();
                  } else {
                      alert('Error: ' + response.data.message);
                  }
                  // Re-enable the button and reset its text
                  $('#wcu-generate-dashboard-page').prop('disabled', false).html('<?php echo esc_html__( 'Generate Dashboard Page', 'woo-coupon-usage' ); ?> <span class="fa-solid fa-arrow-right"></span>');
              }
          );
      });

      // Initial check for shortcode
      $('.dashboard_shortcode_check').hide();
      check_dashboard_page_shortcode();
  });
  </script>

    <h3 style="margin-top: 20px;"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Portal', 'woo-coupon-usage' ); ?>:</h3>

    <p>
      <?php echo esc_html__( 'The "Affiliate Portal" is an alternative to the normal "affiliate dashboard" page.', 'woo-coupon-usage' ); ?>
    </p>
    <br class="setup-hide"/>
    <p>
      <?php echo esc_html__( 'Instead of being a shortcode displayed on a page within your theme, the affiliate portal is its own standalone full-screen page with a modern unique design.', 'woo-coupon-usage' ); ?>
    </p>
    <br class="setup-hide"/>
    <p>
      <?php echo esc_html__( 'If enabled, all dashboard links will direct to the portal page, instead of the regular dashboard page.', 'woo-coupon-usage' ); ?>
    </p>

    <br class="setup-hide"/>

    <!-- Enable Affiliate Portal -->
    <?php wcusage_setting_toggle_option('wcusage_field_portal_enable', 0, esc_html__( 'Enable Affiliate Portal', 'woo-coupon-usage' ), '0px'); ?>

    <?php if( function_exists('wcusage_check_affiliate_portal_rewrite_rule') && !wcusage_check_affiliate_portal_rewrite_rule() ) { ?>
      <p style="color: red; margin: 20px 0 5px 0;"><strong><?php echo esc_html__( 'The affiliate portal is enabled, but the URL rewrite rules are not work correctly.', 'woo-coupon-usage' ); ?></strong></p>
      <p style="color: red; margin: 5px 0;"><strong><?php echo sprintf(esc_html__( 'Please go to %sSettings > Permalinks%s and click "Save Changes" to refresh the rewrite rules, or %sclick here%s for more information.', 'woo-coupon-usage' ),
      '<a href="'.esc_url(admin_url('options-permalink.php')).'" target="_blank">', '</a>',
      '<a href="https://couponaffiliates.com/docs/affiliate-portal-not-working/" target="_blank">', '</a>'); ?></strong></p>
      <p style="color: red; margin: 5px 0;"><strong><?php echo esc_html__( 'The plugin will default to the normal dashboard page until the rewrite rule exists.', 'woo-coupon-usage' ); ?></strong></p>
      <script>
        jQuery(document).ready(function($) {
          $('.affiliate-dashboard-page-settings').show();
        });
      </script>
    <?php } ?>

    <?php
    wcusage_setting_toggle('.wcusage_field_portal_enable', '.wcu-field-section-portal'); // Show or Hide

    $wcusage_portal_enabled_on_load = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
    $wcusage_users_can_register = (int) get_option('users_can_register');
    // Default to enabled for existing portals, but keep disabled when the portal toggle is off and user registration is disabled
    $wcusage_portal_form_default = 1;
    if ('1' !== $wcusage_portal_enabled_on_load && !$wcusage_users_can_register) {
      $wcusage_portal_form_default = 0;
    }
    ?>
    <span class="wcu-field-section-portal">

    <br class="setup-hide"/>

    <?php $portal_slug = wcusage_get_setting_value('wcusage_portal_slug', 'affiliate-portal'); ?>

    <p class="setup-hide">
      <?php echo esc_html__( 'Affiliate Portal URL: ', 'woo-coupon-usage' ); ?>
      <a href="<?php echo esc_url(get_home_url()).'/'.$portal_slug.'/' ?>" target="_blank" class="affiliate-portal-url"><?php echo esc_url(get_home_url()).'/'.$portal_slug.'/' ?></a>
    </p>

    <?php if ( ! get_option( 'users_can_register' ) ) { ?>
      
      <p style="margin: 22px 0 0 0; color: #c44747ff; font-size: 12px;" class="registration-warning setup-hide">
        <?php echo sprintf( wp_kses_post( __( 'Warning: You have "<a href="%s" target="_blank">Anyone can register</a>" disabled in WordPress, which will be ignored for the registration form on the affiliate portal.', 'woo-coupon-usage' ) ), esc_url( admin_url( 'options-general.php' ) . '#users_can_register' ) ); ?>
      </p>

      <div class="setup-hide">

      <br class="setup-hide"/>

      <?php wcusage_setting_toggle_option('wcusage_field_loginform', 1, esc_html__( 'Show login form on affiliate portal.', 'woo-coupon-usage' ), '0px'); ?>

      <br class="setup-hide"/>

      <?php wcusage_setting_toggle_option('wcusage_field_enable_portal_registration', $wcusage_portal_form_default, esc_html__( 'Show registration form on affiliate portal.', 'woo-coupon-usage' ), '0px'); ?>

      </div>

    <?php } ?>

    <div>

    <br class="setup-hide"/>

    <p><strong><?php echo esc_html__( 'Customise Affiliate Portal Design:', 'woo-coupon-usage' ); ?></strong>
    <button type="button" class="wcu-showhide-button" id="wcu_show_section_portal_settings"><?php echo esc_html__('Show', 'woo-coupon-usage'); ?> <span class='fa-solid fa-arrow-down'></span></button>

    <?php echo wcu_admin_settings_showhide_toggle("wcu_show_section_portal_settings", "wcu_section_portal_settings", "Show", "Hide"); ?>
    </p>
    <div class="wcu_section_settings" id="wcu_section_portal_settings" style="display: none; margin-top: 10px;">

    <!-- Portal Page Title -->
    <?php wcusage_setting_text_option("wcusage_portal_title", "Affiliate Portal", esc_html__( 'Portal Page Title', 'woo-coupon-usage' ), "0px"); ?>

    <br/>

    <!-- Portal Page URL Slug -->
    <?php wcusage_setting_text_option("wcusage_portal_slug", "affiliate-portal", esc_html__( 'Portal Page URL Slug', 'woo-coupon-usage' ), "0px"); ?>
    <span class="affiliate-portal-url">
    <i><?php echo esc_html__( 'Your affiliate portal will be located at:', 'woo-coupon-usage' ); ?><br/><?php echo esc_url(get_home_url()).'/'.$portal_slug.'/' ?></span></i>
    
    <br/>
    <script>
      // Update the affiliate portal URL when the slug is changed
      jQuery(document).ready(function($) {
               $('#wcusage_portal_slug').on('change', function(){
                   var slug = $(this).val();
          $('.affiliate-portal-url').text('<?php echo esc_url(get_home_url()); ?>/' + slug + '/');
          $('.affiliate-portal-url').attr('href', '<?php echo esc_url(get_home_url()); ?>/' + slug + '/');
        });
      });
      // When affiliate portal enabled, set the portal slug to the slug of the old affiliate dashboard page if exists found in #dashboard_link
      jQuery(document).ready(function($) {
        $('.wcusage_field_portal_enable').on('change', function() {
          var portal_slug = $('#dashboard_link').text();
          portal_slug = portal_slug.replace('<?php echo esc_url(get_home_url()); ?>/', '');
          if (portal_slug.substr(-1) == '/') {
            portal_slug = portal_slug.substr(0, portal_slug.length - 1);
          }
          // If not empty, set the portal slug to the dashboard page slug
          if (portal_slug) {
            $('#wcusage_portal_slug').val(portal_slug);
            $('.affiliate-portal-url').text('<?php echo esc_url(get_home_url()); ?>/' + portal_slug + '/');
          } else {
            $('#wcusage_portal_slug').val('affiliate-portal');
            $('.affiliate-portal-url').text('<?php echo esc_url(get_home_url()); ?>/affiliate-portal/');
          }
          // Delay
          setTimeout(function() {
            $('#wcusage_portal_slug').trigger('change');
          }, 2500);
          // Flush permalinks via AJAX
          setTimeout(function() {
            $.ajax({
                url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wcusage_flush_permalinks',
                    nonce: '<?php echo wp_create_nonce("flush_permalinks_nonce"); ?>' // Add nonce here
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Permalinks flushed successfully!');
                    } else {
                        console.log('Error flushing permalinks: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error: ' + error);
                }
            });
          }, 5000);
        });
      });
    </script>
    
    <br/>

    <!-- IMAGE - Affiliate Portal Logo -->
    <script>
        jQuery(document).ready(function($) {
            $('.wcusage_portal_logo_upload').click(function(e) {
                e.preventDefault();
                var custom_uploader = wp.media({
                    title: 'Custom Image',
                    button: {
                        text: 'Upload Image'
                    },
                    multiple: false  // Set this to true to allow multiple files to be selected
                })
                .on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('.wcusage_portal_logo').attr('src', attachment.url);
                    $('.wcusage_portal_logo').val(attachment.url);
          $('.wcusage_portal_logo').change();
          // Explicitly trigger AJAX save (programmatic change events are ignored by the global handler)
          if (typeof window.wcu_ajax_update_the_options === 'function') {
            try {
              window.wcu_ajax_update_the_options(jQuery('#wcusage_portal_logo'), 'id', 'wcu-update-text', 1, '', '');
            } catch (e) {}
          }
                })
                .open();
            });
        });
    </script>
    <p>
      <?php $wcusage_portal_logo = wcusage_get_setting_value('wcusage_portal_logo', ''); ?>
      <strong><?php echo esc_html__( 'Affiliate Portal Logo', 'woo-coupon-usage' ); ?></strong><br/>
      <input class="wcusage_portal_logo" type="text"
      id="wcusage_portal_logo"
      name="wcusage_options['wcusage_portal_logo']"
      size="60" value="<?php echo esc_html($wcusage_portal_logo); ?>">
      <a href="#" class="wcusage_portal_logo_upload">Upload</a>
      <br/><i><?php echo esc_html__( 'This is shown at the very top left of the affiliate portal. Recommended size is 200px width.', 'woo-coupon-usage' ); ?></i><br/>
    </p>

    <br/><br/>

    <!-- Footer Text -->
    <?php wcusage_setting_tinymce_option("wcusage_portal_footer_text", "", esc_html__( 'Footer Text', 'woo-coupon-usage' ), "0px"); ?>

    <br/>

    <!-- Show Dark Mode Toggle -->
    <?php wcusage_setting_toggle_option('wcusage_portal_dark_mode', 1, esc_html__( 'Show Dark Mode Toggle', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'This will show a toggle switch at the top right of the portal to switch between light and dark mode.', 'woo-coupon-usage' ); ?></i>

    <br/><br/>

    <span class="setup-hide">

        <?php if ( get_option( 'users_can_register' ) ) { ?>

        <strong><?php echo esc_html__( 'Login & Registration', 'woo-coupon-usage' ); ?></strong>

        <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_loginform', 1, esc_html__( 'Show login form on affiliate portal', 'woo-coupon-usage' ), '0px'); ?>

        <br/>

        <?php wcusage_setting_toggle_option('wcusage_field_enable_portal_registration', $wcusage_portal_form_default, esc_html__( 'Show registration form on affiliate portal', 'woo-coupon-usage' ), '0px'); ?>
        
        <?php } ?>

        <br/>

        <strong><?php echo esc_html__( 'Portal Colors', 'woo-coupon-usage' ); ?></strong>

        <p>
            <?php echo sprintf( esc_html__( 'You can customise the colors of the affiliate portal in the %s.', 'woo-coupon-usage' ), '<a href="#" onclick="wcusage_go_to_settings(\'#tab-design\', \'#affiliate-dashboard-colors\');">design settings tab</a>' ); ?>
        </p>

        <br/>

        <strong><?php echo esc_html__( 'Portal Font', 'woo-coupon-usage' ); ?></strong>

        <p>
          <?php echo sprintf( esc_html__( 'You can change the primary font used in the affiliate portal in the %s.', 'woo-coupon-usage' ), '<a href="#" onclick="wcusage_go_to_settings(\'#tab-design\', \'#affiliate-portal-font\');">design settings tab</a>' ); ?>
        </p>

      </span>

      <?php if ( isset($_GET['page']) && $_GET['page'] == 'wcusage_setup' ) { ?>

        <p>
            <?php echo esc_html__( 'You can customise the affiliate portal more, including layout and colors, later on the settings page.', 'woo-coupon-usage' ); ?>
        </p>

      <?php } ?>

    </div>

    </div>

    </span>

  <?php
  }
}

/**
 * Get Permalink AJAX
 *
 */
function wcusage_get_permalink_ajax() {

  $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
  if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wcusage_get_permalink_nonce' ) ) {
    echo '';
    wp_die();
  }
  if ( ! current_user_can( 'manage_options' ) ) {
    echo '';
    wp_die();
  }
  $page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
  echo esc_url( get_permalink( $page_id ) );
  wp_die();
}
add_action('wp_ajax_wcusage_get_permalink', 'wcusage_get_permalink_ajax');

/**
 * Settings Section: Order/Sales Tracking
 *
 */
add_action( 'wcusage_hook_setting_section_ordersalestracking', 'wcusage_setting_section_ordersalestracking', 10, 1 );
if( !function_exists( 'wcusage_setting_section_ordersalestracking' ) ) {
  function wcusage_setting_section_ordersalestracking($type = "") {

  $options = get_option( 'wcusage_options' );
  if ( ! is_array( $options ) ) {
    $options = array();
  }
  if ( isset( $options['wcusage_field_order_type_custom'] ) && ! is_array( $options['wcusage_field_order_type_custom'] ) ) {
    $options['wcusage_field_order_type_custom'] = array();
  }
  ?>

    <p class="option_wcusage_field_order_type">
      <?php
      $wcusage_field_order_type = wcusage_get_setting_value('wcusage_field_order_type', '');
      $wcusage_field_order_type_custom = wcusage_get_setting_value('wcusage_field_order_type_custom', '');
      ?>

      <!-- Order Status Type Field -->
      <strong><label for="scales"><?php echo esc_html__( 'Required order status to show on affiliate dashboard:', 'woo-coupon-usage' ); ?></label></strong><br/>

        <?php
        if( function_exists('wc_get_order_statuses') ) {
          $orderstatuses = wc_get_order_statuses();
        } else {
          $orderstatuses = array(
            'wc-pending'    => esc_html__( 'Pending payment', 'woocommerce' ),
            'wc-processing' => esc_html__( 'Processing', 'woocommerce' ),
            'wc-on-hold'    => esc_html__( 'On hold', 'woocommerce' ),
            'wc-completed'  => esc_html__( 'Completed', 'woocommerce' ),
            'wc-cancelled'  => esc_html__( 'Cancelled', 'woocommerce' ),
            'wc-refunded'   => esc_html__( 'Refunded', 'woocommerce' ),
            'wc-failed'     => esc_html__( 'Failed', 'woocommerce' ),
          );
        }
        $i = 0;
        // Ensure the group key is present in POST even if all checkboxes are unchecked,
        // so sanitize callback can detect an intentional clear.
        if ( ! $type ) {
          echo '<input type="hidden" name="wcusage_options[wcusage_field_order_type_custom][__present]" value="1">';
        }
        $checkboxes_per_row = 4;
        $checkbox_count = 0;
        foreach( $orderstatuses as $key => $status ){
          if($status == "Refunded") {
            if(isset($options['wcusage_field_order_type_custom'][$key])) {
              $current = $options['wcusage_field_order_type_custom'][$key];
            }
            if( !isset($current) ) {
              continue;
            }
          }

          $i++;
          if($i == 1) { $thisid = "wcusage_field_order_type_custom"; }

          $checkedx = "";

          if($wcusage_field_order_type_custom) {
            if( isset($options['wcusage_field_order_type_custom'][$key]) ) {
              // Get Current Input Value
              $current = $options['wcusage_field_order_type_custom'][$key];
              // See if Checked
              if( isset($current) ) {
                $checkedx = "checked";
              }
            }
          }

          // MAKE COMPATIBLE WITH OLD SETTING
          if( ( !$wcusage_field_order_type_custom && $wcusage_field_order_type ) || ( !$wcusage_field_order_type_custom && !$wcusage_field_order_type ) ) {
            if($wcusage_field_order_type == "completed") {
              if($key == "wc-completed") {
                $checkedx = "checked";
              }
            } else {
              if($key == "wc-completed" || $key == "wc-processing") {
                $checkedx = "checked";
              }
            }
          }

          // Force completed to be checked
          if($key == "wc-completed") {
            if(!isset($options['wcusage_field_order_type_custom']['wc-completed']) || $checkedx) {
              // Only update on non-GET requests
              if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
                $option_group = get_option('wcusage_options');
                if ( ! is_array( $option_group ) ) {
                  $option_group = array();
                }
                if ( isset( $option_group['wcusage_field_order_type_custom'] ) && ! is_array( $option_group['wcusage_field_order_type_custom'] ) ) {
                  $option_group['wcusage_field_order_type_custom'] = array();
                }
                $option_group['wcusage_field_order_type_custom']['wc-completed'] = "on";
                update_option( 'wcusage_options', $option_group );
              }
              $checkedx = "checked";
            }
          }

          // Force processing to be checked on first time load settings
          if( !get_option('wcusage_field_order_type_custom_isset') && !isset($options['wcusage_field_load_ajax']) && $key == "wc-processing" ) {
            // Only update on non-GET requests
            if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
              $option_group = get_option('wcusage_options');
              if ( ! is_array( $option_group ) ) {
                $option_group = array();
              }
              if ( isset( $option_group['wcusage_field_order_type_custom'] ) && ! is_array( $option_group['wcusage_field_order_type_custom'] ) ) {
                $option_group['wcusage_field_order_type_custom'] = array();
              }
              $option_group['wcusage_field_order_type_custom']['wc-processing'] = "on";
              update_option( 'wcusage_options', $option_group );
            }
            $checkedx = "checked";
          }

          $extrastyles = "";
          if($key == "wc-completed" && $checkedx == "checked") {
            $extrastyles = ' pointer-events: none !important; opacity: 0.6;';
          }

          // Output Checkbox
          if(!$type) {
            $name = 'wcusage_options[wcusage_field_order_type_custom]['.$key.']';
          } else {
            $name = 'wcusage_field_order_type_custom['.$key.']';
          }

          // Start a new row if needed
          if ($checkbox_count % $checkboxes_per_row === 0) {
            if ($checkbox_count > 0) {
              echo '<br/>';
            }
          }

          echo '<span style="display: inline-block; margin: 10px 20px 10px 0;'.esc_attr($extrastyles).'" id="'.esc_attr($thisid).'">
          <input type="checkbox"
          style="'.esc_attr($extrastyles).'" checktype="multi"
          class="order-status-checkbox-'.esc_attr($key).'"
          checktypekey="'.esc_attr($key).'"
          customid="'.esc_attr($thisid).'"
          name="'.esc_attr($name).'"
          '.esc_attr($checkedx).'> '.esc_attr($status).'</span>';

          $checkbox_count++;
        }
        update_option( 'wcusage_field_order_type_custom_isset', 1 );
        ?>

        <br/><i><?php echo esc_html__( 'This will affect the coupon usage stats, orders list, commission, and monthly summary.', 'woo-coupon-usage' ); ?></i> <i><?php echo esc_html__( 'Affiliate stats will be automatically refreshed when changing these statuses.', 'woo-coupon-usage' ); ?></i>

        <br/><i><?php echo esc_html__( 'For "unpaid commission" to be granted (PRO), the order status must be "completed".', 'woo-coupon-usage' ); ?></i>
        
        <br/><i><?php echo esc_html__( 'Cancelled, Refunded, and Failed orders will show stats as "0.00".', 'woo-coupon-usage' ); ?></i>

      </p>

      <div class="setup-hide">

        <?php $wcusage_field_order_sort = wcusage_get_setting_value('wcusage_field_order_sort', 'paiddate'); ?>

        <?php if( $wcusage_field_order_sort != "completeddate" ) { ?>
        <br/>
        <p><strong><?php echo esc_html__( 'Advanced Orders Settings', 'woo-coupon-usage' ); ?>:</strong>
        <button type="button" class="wcu-showhide-button" id="wcu_show_orders_advanced">Show <span class="fa-solid fa-arrow-down"></span></button></p>

        <?php echo wcu_admin_settings_showhide_toggle("wcu_show_orders_advanced", "wcu_orders_advanced", "Show", "Hide"); ?>
        <div id="wcu_orders_advanced" style="display: none;">
        <?php } ?>

        <br/>

        <!-- How to sort orders -->
        <p>
          <input type="hidden" value="0" id="wcusage_field_order_sort" data-custom="custom" name="wcusage_options[wcusage_field_order_sort]" >

          <style>
          .order-status-checkbox-wc-completed {
            pointer-events: none !important;
          }
          </style>
          <script>
          jQuery( document ).ready(function() {
            check_order_sort_dropdown();
          });
          function check_order_sort_dropdown() {
            var value = jQuery('.wcusage_field_order_sort_option:selected').val();
            if (value === 'completeddate') {
              jQuery('.option_wcusage_field_order_type').css('opacity', '0.75');
            } else {
              jQuery('.option_wcusage_field_order_type').css('opacity', '1');
            }
            if ( jQuery('.wcusage_field_order_sort_option:selected').val() == "completeddate" ) {
              jQuery(".wcu-field-section-message-orders-sort-completed").show();
            } else {
              jQuery(".wcu-field-section-message-orders-sort-completed").hide();
            }
          }
          </script>
          <strong><label for="scales"><?php echo esc_html__( 'By which date should orders be sorted on the affiliate dashboard?', 'woo-coupon-usage' ); ?></label></strong><br/>
          <select name="wcusage_options[wcusage_field_order_sort]" id="wcusage_field_order_sort" onchange="check_order_sort_dropdown()">
            <option class="wcusage_field_order_sort_option" value="paiddate" <?php if($wcusage_field_order_sort == "paiddate") { ?>selected<?php } ?>><?php echo esc_html__( 'Created Date (Recommended)', 'woo-coupon-usage' ); ?></option>
            <option class="wcusage_field_order_sort_option" value="completeddate" <?php if($wcusage_field_order_sort == "completeddate") { ?>selected<?php } ?>><?php echo esc_html__( 'Completed Date', 'woo-coupon-usage' ); ?></option>
          </select>
          <br/><i><?php echo esc_html__( 'This will determine how the orders are sorted on the affiliate dashboard, either by the day they were paid for, or the day it was set to completed.', 'woo-coupon-usage' ); ?></i>
          <span class="wcu-field-section-message-orders-sort-completed" style="display: none;">
            <br/>
            <i style="color: red; font-size: 15px; font-weight: bold;">
              <?php echo esc_html__( 'NOTE: If set to "Completed Date", only orders that have been marked as "completed" (at-least once) can be displayed on the dashboard.', 'woo-coupon-usage' ); ?>
              <br/>
              <?php echo esc_html__( 'This may therefore disregard some of the order statuses that are checked above.', 'woo-coupon-usage' ); ?>
              <?php echo esc_html__( 'Ideally you should only enable "completed" order statuses above if you have "Completed Date" selected.', 'woo-coupon-usage' ); ?>
            </i>
          </span>

        <?php if( $wcusage_field_order_sort != "completeddate" ) { ?>
        </div>
        <?php } ?>

      </div>

  	</p>

  <?php
  }
}

add_action('wp_ajax_wcusage_flush_permalinks', 'wcusage_flush_permalinks_callback');
function wcusage_flush_permalinks_callback() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flush_permalinks_nonce')) {
        wp_send_json_error('Invalid nonce.');
        wp_die();
    }

    // Flush permalinks
    flush_rewrite_rules();

    // Send success response
    wp_send_json_success('Permalinks flushed successfully.');
    wp_die();
}

/*
* Function to check wcusage_check_dashboard_shortcode
*/
add_action( 'wp_ajax_wcusage_check_dashboard_shortcode', 'wcusage_check_dashboard_shortcode' );
function wcusage_check_dashboard_shortcode() {
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcusage_check_dashboard_shortcode_nonce')) {
      wp_die('Security check failed');
  }
  if (!current_user_can('manage_options')) {
      wp_die('Access denied');
  }
  $page_id = intval($_POST['page_id']);
  $page = get_post($page_id);
  if ($page) {
    $content = $page->post_content;
    if (strpos($content, '[couponaffiliates]') !== false) {
      echo 1; // Shortcode found
    } else {
      echo 0; // Shortcode not found
    }
  } else {
    echo 0; // Page not found
  }
  wp_die();
}