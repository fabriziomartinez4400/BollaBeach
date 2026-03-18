<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_urls( $args )
{
    $options = get_option( 'wcusage_options' );

    $wcusage_urls_prefix = wcusage_get_setting_value('wcusage_field_urls_prefix', 'coupon');
    $wcusage_src_prefix = wcusage_get_setting_value('wcusage_field_src_prefix', 'src');
    $wcusage_field_default_ref_url = wcusage_get_setting_value('wcusage_field_default_ref_url', esc_url(home_url()));
    $wcusage_field_default_ref_url = rtrim($wcusage_field_default_ref_url, '/');
    ?>

	<div id="urls-settings" class="settings-area">

	<h1><?php echo esc_html__( 'Referral Links', 'woo-coupon-usage' ); ?></h1>

  <hr/>

    <!-- Enable Referral Links -->
    <?php wcusage_setting_toggle_option('wcusage_field_urls_enable', 1, esc_html__( 'Enable Referral URL Generation & Click Tracking', 'woo-coupon-usage' ), '0px'); ?>

    <br/>

    <!-- FAQ: How do referral URLs work? -->
    <div class="wcu-admin-faq">

      <?php echo wcusage_admin_faq_toggle(
      "wcu_show_section_qna_urls",
      "wcu_qna_urls",
      "FAQ: How do referral URLs work?");
      ?>

      <div class="wcu-admin-faq-content wcu_qna_urls" id="wcu_qna_urls" style="display: none;">

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'This will enable the Referral URL section on the affiliate dashboard which displays the referral URL generator, along with click statistics, conversion rates, and more.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'On the dashboard, the affiliate can generate their own custom links for specific pages or products on your website.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If enabled below, the referral link will automatically apply the coupon code to their cart.', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If you want conversions to be tracked even if the coupon is not used, enable the setting below under "URL Conversion Tracking".', 'woo-coupon-usage' ); ?><br/>

        <span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can also show the logged in affiliates default link anywhere using the shortcode: [couponaffiliates-referral-url]', 'woo-coupon-usage' ); ?><br/>

        <a href="https://couponaffiliates.com/docs/referral-urls" target="_blank" class="button button-primary" style="margin-top: 10px;"><?php echo esc_html__( 'View Documentation', 'woo-coupon-usage' ); ?> <span class="fas fa-external-link-alt"></span></a>

        <br/>

      </div>

    </div>
    
    <?php wcusage_setting_toggle('.wcusage_field_urls_enable', '.wcu-field-section-referral-url-settings'); // Show or Hide ?>
    <span class="wcu-field-section-referral-url-settings">

      <!-- ********** Auto Apply Coupon ********** -->
      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Auto Apply Coupon', 'woo-coupon-usage' ); ?>:</h3>

      <!-- Automatically apply coupon -->
      <?php wcusage_setting_toggle_option('wcusage_field_apply_enable', 1, esc_html__( 'Automatically apply coupon to cart/checkout if referral URL is used.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'This will automatically apply the coupon every time at checkout for that visitor, until the cookie expires.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'Please note: A coupon code needs to be applied for the affiliate commission to be tracked/earned (unless you have enabled the URL tracking without coupon option).', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <?php wcusage_setting_toggle_option('wcusage_field_apply_instant_enable', 1, esc_html__( 'Attempt to apply coupon instantly on first page visited.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'If enabled, the plugin will attempt to apply the code on the first page they visit. If disabled, it will only apply the code once they visit the cart/checkout pages.', 'woo-coupon-usage' ); ?></i><br/>

      <!-- ********** Referral URL Tab ********** -->
      <br/><hr/>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( '"Referral URL" Tab', 'woo-coupon-usage' ); ?>:</h3>

      <!-- Enable Referral Links -->
      <?php wcusage_setting_toggle_option('wcusage_field_urls_tab_enable', 1, esc_html__( 'Enable "Referral URL" tab on the affiliate dashboard.', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'Recommended. This will allow them to view and generate their own referrral URLs, click history, campaigns, etc.', 'woo-coupon-usage' ); ?></i><br/>

      <?php wcusage_setting_toggle('.wcusage_field_urls_tab_enable', '.wcu-field-section-referral-url-tab-settings'); // Show or Hide ?>
      <span class="wcu-field-section-referral-url-tab-settings">

        <br/>

        <!-- Enable Referral URL Generator -->
        <?php wcusage_setting_toggle_option('wcusage_field_urls_generator_enable', 1, esc_html__( 'Enable "Referral Link" Generator Section', 'woo-coupon-usage' ), '30px'); ?>
        <i style="margin-left: 30px;"><?php echo esc_html__( 'Recommended. This will allow the user to see their referral link, and generate a new one by entering their own page URL or selecting a campaign.', 'woo-coupon-usage' ); ?></i>

        <br/><br/>

        <!-- Enable Referral URL Generator -->
        <?php wcusage_setting_toggle_option('wcusage_field_urls_statistics_enable', 1, esc_html__( 'Enable "Referral Statistics" Section', 'woo-coupon-usage' ), '30px'); ?>
        <i style="margin-left: 30px;"><?php echo esc_html__( 'Recommended. This will show the clicks, conversions, and conversion rate statistics for referral URLs.', 'woo-coupon-usage' ); ?></i>

        <br/><br/>

        <!-- Custom Text -->
        <?php wcusage_setting_textarea_option('wcusage_field_text_urls', '', esc_html__( 'Custom Text', 'woo-coupon-usage' ), '30px'); ?>
    		<i style="margin-left: 30px;"><?php echo esc_html__( 'Displayed at top of the "referral URL" section on the coupon affiliate dashboard page. HTML tags enabled.', 'woo-coupon-usage' ); ?></i>

      <!-- ********** Referral URL Tab ********** -->
      <br/><hr/>

      </span>

      <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Referral Link Format', 'woo-coupon-usage' ); ?>:</h3>

      <p>
        <?php echo esc_html__( 'These settings let you customise the format of the referral links that your affiliates can generate on their affiliate dashboard.', 'woo-coupon-usage' ); ?>
      </p>

      <br/>

      <script>
      jQuery(function() {
        jQuery('#wcusage_field_urls_prefix').on('input', function(){
          var source_name = jQuery(this).attr('name');
          jQuery('.link-output-prefix').text( jQuery(this).val() );
        });
        jQuery('#wcusage_field_default_ref_url').on('input', function(){
          var source_name = jQuery(this).attr('name');
          $defaultURL = jQuery(this).val();
          if( $defaultURL.substr(-1) === '/' ) {
            $defaultURL = $defaultURL.slice(0, -1);
          }
          jQuery('.link-outputurl').text( $defaultURL );
        });
      });
      </script>

      <!-- Referral URL variable -->
      <?php wcusage_setting_text_option('wcusage_field_urls_prefix', 'coupon', esc_html__( 'Referral URL Variable', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'Set the referral URL variable that will be used in your site URL to identify the referrer and to automatically apply that coupon, and track clicks.', 'woo-coupon-usage' ); ?></i><br/>

      <br/>

      <!-- Default URL -->
      <?php wcusage_setting_text_option('wcusage_field_default_ref_url', esc_url(home_url()), esc_html__( 'Default Referral URL Page', 'woo-coupon-usage' ), '0px'); ?>
      <i><?php echo esc_html__( 'The default "Page URL" when affiliates generate referral links in the dashboard. Note: This needs to be a valid link/page on this website only.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'If the affiliate does not enter a custom URL, this will be used as the default landing page for the link.', 'woo-coupon-usage' ); ?></i><br/>
      <i><?php echo esc_html__( 'This is set to your home page by default, but can be any page on your website.', 'woo-coupon-usage' ); ?></i><br/>
      
      <br/>

      <p>
        <?php echo esc_html__( 'Example default referral URL with coupon code "example" would be', 'woo-coupon-usage' ); ?>:<br/>
        <span style="font-weight: bold;">
        <span class="link-output-url"><?php echo esc_url($wcusage_field_default_ref_url); ?></span>/?<span class="link-output-prefix"><?php echo esc_html($wcusage_urls_prefix); ?></span>=example
        </span>
      </p>

      <script>
          jQuery(document).ready(function() {

              var defaultURL = "<?php echo esc_url(home_url()); ?>";

              jQuery('#wcusage_field_default_ref_url').on('change', function() {
                  var url = jQuery(this).val();

                  // Add https:// if not already
                  if( url.indexOf('http://') === -1 && url.indexOf('https://') === -1 ) {
                      url = 'https://' + url;
                  }

                  // Regex pattern for URL validation
                  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
                      '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name and extension
                      '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
                      '(\\:\\d+)?'+ // port
                      '(\\/[-a-z\\d%_.~+]*)*'+ // path
                      '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
                      '(\\#[-a-z\\d_]*)?$','i'); // fragment locator

                  if(!pattern.test(url)) {
                      jQuery(this).val(defaultURL);
                      return;
                  }

                  // Should contain the sites domain
                  if( url.indexOf(defaultURL) === -1 ) {
                      alert('The URL must be on this website only.');
                      jQuery(this).val(defaultURL);
                      return;
                  }

                  // Create an anchor tag to easily parse the URL
                  var a = document.createElement('a');
                  a.href = url;

                  // Remove URL parameters if any
                  url = a.protocol + "//" + a.hostname + a.pathname;
                  jQuery(this).val(url);

                  // Remove slash if at the end
                  if( url.substr(-1) === '/' ) {
                      url = url.slice(0, -1);
                  }

                  // Update the example URL
                  var source_name = jQuery(this).attr('name');
                  jQuery('.link-outputurl').text( url );

              });
          });
      </script>

        <!-- ********** URL Conversion Tracking ********** -->
        <br/><hr/>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'URL Conversion Tracking', 'woo-coupon-usage' ); ?>:</h3>

        <?php wcusage_setting_toggle_option('wcusage_field_url_referrals', 0, esc_html__( 'Track conversions via referral URL even if coupon was not used.', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'If enabled, if someone visits the site via the referral URL and places an order without using the coupon code, it will still be tracked and award the affiliate.', 'woo-coupon-usage' ); ?></i><br/>
        <i><?php echo esc_html__( 'If disabled, by default the referral will only be tracked if the customer applys the affiliates coupon when placing their order.', 'woo-coupon-usage' ); ?></i><br/>

        <!-- ********** Referral Cookie Settings ********** -->
        <br/><hr/>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Referral Cookie Settings', 'woo-coupon-usage' ); ?>:</h3>

        <p><?php echo esc_html__( 'If preferred you can disable cookies completely in the', 'woo-coupon-usage' ); ?>
          <a href="#" onclick="wcusage_go_to_settings('#tab-debug', '#wcusage_field_store_cookies_p');">
            <?php echo esc_html__( 'debug settings tab', 'woo-coupon-usage' ); ?></a>.
        </p><br/>

        <!-- Cloudflare APO Detection and Warning -->
        <?php
        $cf_ap_enabled = false;
        if (isset($_SERVER['HTTP_CF_EDGE_CACHE']) || isset($_SERVER['HTTP_CF_CACHE_STATUS'])) {
            if (isset($_SERVER['HTTP_CF_EDGE_CACHE']) && strpos($_SERVER['HTTP_CF_EDGE_CACHE'], 'on') !== false) {
                $cf_ap_enabled = true;
            }
        }
        if ($cf_ap_enabled) {
            echo '<br/><div style="display: inline-block; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; margin-bottom: 20px;">'
            . esc_html__('Warning: Cloudflare Automatic Platform Optimization (APO) appears to be enabled. This may cause issues with referral cookies and coupon auto-apply features.', 'woo-coupon-usage')
                . '<br/>'
                . esc_html__('Please review your Cloudflare APO settings and consider disabling APO for this site if you experience problems.', 'woo-coupon-usage')
                . '</div>';
        }
        ?>

        <!-- LiteSpeed Cookie Stripping Detection and Warning -->
        <?php
        $litespeed_cookie_stripping = false;
        if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
            $litespeed_cookie_stripping = true;
        }
        if ($litespeed_cookie_stripping) {
            echo '<div style="display: inline-block; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; margin-bottom: 20px;">'
            . esc_html__('Warning: LiteSpeed web server detected. Cookie stripping may be enabled, which can cause issues with referral cookies and coupon auto-apply features.', 'woo-coupon-usage')
                . '<br/>'
                . esc_html__('This applies to hosts like Rocket.net, Hostinger, Namecheap, and others using LiteSpeed.', 'woo-coupon-usage')
                . '<br/>'
                . esc_html__('Please test your referral links, and if you experience problems, contact your hosting provider to disable cookie stripping.', 'woo-coupon-usage')
                . '</div>';
        }
        ?>

        <div class="wcu-referral-cookies">

          <!-- DESC -->
          <?php wcusage_setting_number_option('wcusage_urls_cookie_days', '30', esc_html__( 'Store cookie for how many days?', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'This is how many days after someone clicks on a referral link, that the cookie will be stored to automatically apply the coupon.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'If for some reason the user deletes/removes the coupon manually from their cart, the cookie will also be removed.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <?php wcusage_setting_toggle_option('wcusage_remove_cookies', 0, esc_html__( 'Remove all tracking cookies when customer places an order.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'If enabled, the tracking cookies will be deleted from the customers browser, once the order is completed.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'They would need to click the referral link again for coupons to be automatically applied again, or future orders to be awarded to the affiliate.', 'woo-coupon-usage' ); ?></i><br/>

        </div>

        <!-- ********** Click Attribution ********** -->
        <br/><hr/>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Click Attribution', 'woo-coupon-usage' ); ?>:</h3>

        <?php
          // Single source of truth: 0 = Last-Click (default), 1 = First-Click
          wcusage_setting_select_option(
            'wcusage_field_click_attribution_first',
            '0',
            esc_html__( 'Attribution Model', 'woo-coupon-usage' ),
            '0px',
            array(
              '0' => esc_html__( 'Last-Click Attribution (Default)', 'woo-coupon-usage' ),
              '1' => esc_html__( 'First-Click Attribution', 'woo-coupon-usage' )
            )
          );
        ?>

        <p style="margin-top:6px;">
          <i>- <?php echo esc_html__( 'Last-Click: New referral links overwrite referral cookies and auto-applied coupon with the latest affiliate.', 'woo-coupon-usage' ); ?></i><br/>
          <i>- <?php echo esc_html__( 'First-Click: Once a referral cookie exists, later referral links won’t replace it or the cart coupon automatically; customers can still manually apply a different coupon.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'Bare in mind: In both cases the referral link cookie will take priority over the cart coupon in terms of awarding the affiliate.', 'woo-coupon-usage' ); ?></i>
        </p>

        <!-- ********** Click Log ********** -->
        <br/><hr/>

        <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Click / Visit History', 'woo-coupon-usage' ); ?>:</h3>

        <?php wcusage_setting_toggle_option('wcusage_field_show_click_history', 1, esc_html__( 'Enable "Click History" Logs', 'woo-coupon-usage' ), '0px'); ?>
        <i><?php echo esc_html__( 'This will show a table with a log of the latest referral URL clicks/visits for the affiliate coupons referral URL (or selected campaign).', 'woo-coupon-usage' ); ?></i><br/>

        <?php wcusage_setting_toggle('.wcusage_field_show_click_history', '.wcu-field-section-click-history'); // Show or Hide ?>
        <span class="wcu-field-section-click-history">
        <br/>

        <!-- How many recent clicks should be shown? -->
        <?php wcusage_setting_number_option('wcusage_field_show_click_history_amount', '10', esc_html__( 'Clicks Per Page', 'woo-coupon-usage' ), '30px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_load_ajax', '.wcu-field-section-click-history-ajax'); // Show or Hide ?>
          <span class="wcu-field-section-click-history-ajax">

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_show_click_history_pagination', 1, esc_html__( 'Enable Pagination', 'woo-coupon-usage' ), '30px'); ?>
            <i style="margin-left: 30px;"><?php echo esc_html__( 'This will allow affiliates to filter through their whole click history with pagination.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

            <?php wcusage_setting_toggle_option('wcusage_field_show_click_history_converted', 1, 'Enable "Converted Only" Toggle', '30px'); ?>
            <i style="margin-left: 30px;"><?php echo esc_html__( 'This will allow affiliates to click a toggle to only show converted clicks.', 'woo-coupon-usage' ); ?></i><br/>

            <br/>

          </span>
          
          <?php wcusage_setting_toggle_option('wcusage_field_track_click_ip', 1, esc_html__( 'Store visitors "IP Address" for referral clicks, instead of a random ID.', 'woo-coupon-usage' ), '30px'); ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'The IP address will be stored in the "clicks" database table. The IP address is only used to check if a click has already been tracked for that visitor.', 'woo-coupon-usage' ); ?></i><br/>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'If disabled, it will instead store an extra random ID as a cookie for new referral clicks ("wcusage_referral_id") which will then work in the same way.', 'woo-coupon-usage' ); ?></i><br/>

      		<br/>

          <?php wcusage_setting_toggle_option('wcusage_field_track_all_clicks', 1, esc_html__( 'Track all new referral URL clicks from the same visitor/user.', 'woo-coupon-usage' ), '30px'); ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'If enabled, all new referral URL clicks from the same user will be tracked (and increases total clicks + visit logged in click history). Only the latest click will be converted if they make a purchase.', 'woo-coupon-usage' ); ?></i><br/>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'If disabled, only the first click from the visitor will be tracked (until the cookie expires).', 'woo-coupon-usage' ); ?></i><br/>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'Note: If enabled, any new clicks (from the same visitor) within the same minute as another will not be logged, and will keep the same ID as the initial click (to prevent spamming the logs).', 'woo-coupon-usage' ); ?></i><br/>

        </span>

        <div <?php if( !wcu_fs()->can_use_premium_code() || !wcu_fs()->is_premium() ) { ?>style="opacity: 0.4; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

          <!-- ********** Referral Campaigns ********** -->
          <br/><hr/>
          <h3 id="wcu-setting-header-referral-campaigns">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Referral Campaigns', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

      		<?php wcusage_setting_toggle_option('wcusage_field_show_campaigns', 1, esc_html__( 'Enable Referral Campaign Features', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, in the "referral URL" section your affiliates will be able to create "campaigns", to generate custom URLs, track clicks, sales, conversation rate for specific referral campaigns.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <!-- Campaign URL variable -->

          <script>
          jQuery(function() {
            jQuery('#wcusage_field_src_prefix').on('input', function(){
            var source_name = jQuery(this).attr('name');
            jQuery('.link-outputsrc').text( jQuery(this).val() );
            });
          });
          </script>

          <?php
          $wcusage_src_prefix = wcusage_get_setting_value('wcusage_field_src_prefix', 'src');
          wcusage_setting_text_option('wcusage_field_src_prefix', 'src', esc_html__( 'Campaign URL variable', 'woo-coupon-usage' ), '30px');
          ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'Set the referral URL variable that will be used to identify the campaign.', 'woo-coupon-usage' ); ?></i><br/>
          
          <br/>
          
          <p style="margin-left: 30px;"><?php echo esc_html__( 'Example URL with campaign "twitter" would be', 'woo-coupon-usage' ); ?>:<br/>
          <span style="font-weight: bold;">
            <span class="link-output-url"><?php echo esc_url($wcusage_field_default_ref_url); ?></span>/?<span class="link-output-prefix"><?php echo esc_html($wcusage_urls_prefix); ?></span>=example&<span class="link-output-src"><?php echo esc_html($wcusage_src_prefix); ?></span>=twitter
          </span>
          </p>

          <!-- ********** QR Codes ********** -->

          <br/><hr/>
          <h3 id="wcu-setting-header-referral-qr">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'QR Code Generator', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

      		<?php wcusage_setting_toggle_option('wcusage_field_show_qrcodes', 0, esc_html__( 'Enable QR Code Generator', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, affiliate users can click a button to generate a QR code for their referral link.', 'woo-coupon-usage' ); ?></i><br/>

          <?php wcusage_setting_toggle('.wcusage_field_show_qrcodes', '.wcu-field-section-qr-codes'); // Show or Hide ?>
          <span class="wcu-field-section-qr-codes">

          <br/>

          <?php wcusage_setting_toggle_option('wcusage_field_qr_logo_enable', 0, esc_html__( 'Include Logo in QR Codes', 'woo-coupon-usage' ), '20px'); ?>
          <i style="margin-left: 20px;">
            <?php echo esc_html__( 'Add a square logo image in the center of generated QR codes.', 'woo-coupon-usage' ); ?>
          </i><br/>

          <?php wcusage_setting_toggle('.wcusage_field_qr_logo_enable', '.wcu-field-section-qr-logo'); // Show or Hide ?>
          <span class="wcu-field-section-qr-logo">

            <br/>

            <script type="text/javascript">
            jQuery(document).ready(function($) {
              $('#wcusage_qr_logo_upload').click(function(e) {
                e.preventDefault();
                var image = wp.media({
                  title: '<?php echo esc_js(__('Select QR Code Logo', 'woo-coupon-usage')); ?>',
                  multiple: false
                }).open().on('select', function(e) {
                  var uploaded_image = image.state().get('selection').first();
                  var image_url = uploaded_image.toJSON().url;
                  $('#wcusage_field_qr_logo_url').val(image_url);
                  $('#wcusage_field_qr_logo_url').change();
                  // Explicitly trigger AJAX save for the QR logo URL field
                  if (typeof window.wcu_ajax_update_the_options === 'function') {
                    try {
                      window.wcu_ajax_update_the_options(jQuery('#wcusage_field_qr_logo_url'), 'id', 'wcu-update-text', 1, '', '');
                    } catch (ex) {}
                  }
                });
              });
            });
            </script>
            
            <p style="margin-left: 20px; margin-top: 10px;">
              <strong><?php echo esc_html__( 'QR Code Logo Image:', 'woo-coupon-usage' ); ?></strong><br/>
              <input type="text" id="wcusage_field_qr_logo_url" name="wcusage_options[wcusage_field_qr_logo_url]" value="<?php echo esc_attr(wcusage_get_setting_value('wcusage_field_qr_logo_url', '')); ?>" style="width: 400px;" />
              <button type="button" class="button" id="wcusage_qr_logo_upload"><?php echo esc_html__( 'Upload/Select Logo', 'woo-coupon-usage' ); ?></button>
              <br/><i><?php echo esc_html__( 'Upload a square logo image (recommended size: 100x100px or smaller). PNG with transparent background works best.', 'woo-coupon-usage' ); ?></i>
            </p>
          </span>

          </span>

          <!-- ********** Direct Links ********** -->

          <br/><hr/>
          <h3 id="wcu-setting-header-referral-directlinks">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Direct Link Tracking', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

          <?php
          $wcusage_field_store_cookies_domains = wcusage_get_setting_value('wcusage_field_store_cookies_domains', '1');
          if(!$wcusage_field_store_cookies_domains) { ?>
            <p><strong><?php echo esc_html__( 'Note:', 'woo-coupon-usage' ); ?></strong> <?php echo esc_html__( 'This feature is disabled because you have disabled the cookie storage.', 'woo-coupon-usage' ); ?></p>
          <?php } ?>

      		<?php wcusage_setting_toggle_option('wcusage_field_enable_directlinks', 0, esc_html__( 'Enable Direct Link Tracking', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, affiliate users can link their website domain to their coupon.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-direct-link-tracking" target="_blank">Learn More</a>.</i><br/>
          <i><?php echo esc_html__( 'Upon approval, they can then directly link to your site, directly from theirs, without needing to use a referral URL, and it will still be tracked.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'When an affiliate adds a new domain to their account, an email will be sent to your admin email, with a link to approve or deny the domain.', 'woo-coupon-usage' ); ?></i><br/>
          <i><?php echo esc_html__( 'Important: There are some cases where direct link tracking may not be detected properly.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-direct-link-tracking" target="_blank">Learn More</a>.</i><br/>

          <?php wcusage_setting_toggle('.wcusage_field_enable_directlinks', '.wcu-field-section-directlinks'); // Show or Hide ?>
          <span class="wcu-field-section-directlinks">

          <br/>

          <!-- Payment Method Info -->
          <?php wcusage_setting_text_option('wcusage_field_directlinks_text', '', esc_html__( 'Custom Text / Description', 'woo-coupon-usage' ), '30px'); ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'Custom information/text shown in the "Direct Link Tracking" section on the affiliate dashboard.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <?php wcusage_setting_toggle_option('wcusage_field_enable_directlinks_campaigns', 1, 'Enable "Campaigns" for Direct Link Tracking', '30px'); ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'With this enabled, when adding a new domain, affiliates will be able to select a "campaign" to assign that domains referrals to.', 'woo-coupon-usage' ); ?></i><br/>

          <br/>

          <p style="margin-left: 30px;"><strong><u><?php echo esc_html__( 'Email Notifications', 'woo-coupon-usage' ); ?></u></strong></p>

          <p style="margin-left: 30px;"><?php echo esc_html__( 'To manage/customise the email notifications for new direct link tracking domain requests, go to the "Emails" settings tab.', 'woo-coupon-usage' ); ?></p>

          <br/>

          <p style="margin-left: 30px;"><strong><u><?php echo esc_html__( 'Strict Fraud Prevention', 'woo-coupon-usage' ); ?></u></strong></p>

          <p style="margin-left: 30px;"><?php echo esc_html__( 'Important: This is only recommended if you experience high levels of affiliate fraud, and/or want a strict way to help stop unauthorized referrals.', 'woo-coupon-usage' ); ?></p>

          <br/>

          <?php wcusage_setting_toggle_option('wcusage_field_enable_directlinks_protection', 0, 'Only allow affiliate coupons to be applied when directly linked by an approved domain.', '30px'); ?>
          <i style="margin-left: 30px;"><?php echo esc_html__( 'Enabling this option will prevent ALL affiliate coupons and referral links from working UNLESS the customer was directly linked by the approved domain that is assigned to that coupon.', 'woo-coupon-usage' ); ?></i><br/>

          </span>

          <!-- ********** Short URL ********** -->

          <?php
          // Trim URL
          $input2 = get_site_url();
          $input2 = trim($input2, '/');
          if (!preg_match('#^http(s)?://#', $input2)) {
              $input2 = 'http://' . esc_html($input2);
          }
            $urlParts2 = wp_parse_url($input2);
          $domain2 = preg_replace('/^www\./', '', $urlParts2['host']);
          ?>

          <br/><hr/>
          <h3 id="wcu-setting-header-referral-short">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Short URLs', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

      		<?php wcusage_setting_toggle_option('wcusage_field_show_shortlink', 0, esc_html__( 'Enable Short URL Generator', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'With this enabled, affiliate users can click a button to automatically generate a short URL for their referral link.', 'woo-coupon-usage' ); ?></i><br/>
                    <i><?php echo esc_html__( 'A shortlink will only be created once for the same URL. A shortlink can also only be created if the URL is pointing to this website', 'woo-coupon-usage' ); ?> (<?php echo esc_html($domain2); ?>).</i><br/>
          <i><?php echo esc_html__( 'Shortlinks are stored as a custom post type and can be viewed in "Short URLs" menu link under "Coupon Affiliates" in the admin area (visible when enabled).', 'woo-coupon-usage' ); ?></i><br/>

          <script>
          function wcusage_get_field_shortlink_slug() {
            if(jQuery('#wcusage_field_show_shortlink_slug').val()) {
              var defaultslug = jQuery('#wcusage_field_show_shortlink_slug').val();
            } else {
              var defaultslug = "link";
            }
            return defaultslug;
          }
      		jQuery(function() {
            jQuery('#wcusage_shortlink_slug_example').text( wcusage_get_field_shortlink_slug() );
      		  jQuery('#wcusage_field_show_shortlink_slug').on('input', function(){
      			  jQuery('#wcusage_shortlink_slug_example').text( wcusage_get_field_shortlink_slug() );
      		  });
      		});
      		</script>

          <?php wcusage_setting_toggle('.wcusage_field_show_shortlink', '.wcu-field-section-shortlink'); // Show or Hide ?>
          <span class="wcu-field-section-shortlink">
          <br/>

            <!-- Shortlink Slug -->
            <?php
            $wcusage_field_show_shortlink_slug = wcusage_get_setting_value('wcusage_field_show_shortlink_slug', 'link');
            wcusage_setting_text_option('wcusage_field_show_shortlink_slug', 'link', esc_html__( 'Shortlink Slug', 'woo-coupon-usage' ), '30px');
            ?>
            <i style="margin-left: 30px;"><?php echo esc_html__( 'This is the slug for the shortlinks, for example:', 'woo-coupon-usage' ); ?> https://<?php echo esc_html($domain2); ?>/<span id="wcusage_shortlink_slug_example"><?php echo esc_html($wcusage_field_show_shortlink_slug); ?></span>/fnhj7rdk</i><br/>
            <i style="margin-left: 30px;"><?php echo esc_html__( 'Note: If you edit this, any previous short URLs created by your affiliates (with the old slug) will stop working.', 'woo-coupon-usage' ); ?></i><br/>
            <i style="margin-left: 30px;"><?php echo esc_html__( 'Important: If your short URLs show a 404 page after changing this, you may need to go to the "Settings > Permalinks" page and click "Save Changes" to refresh your permalinks.', 'woo-coupon-usage' ); ?></i><br/>

          </span>

          <!-- ********** Social Sharing Buttons ********** -->
          <br/><hr/>
          <h3 id="wcu-setting-header-referral-social">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Social Sharing Buttons', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

      		<?php wcusage_setting_toggle_option('wcusage_field_show_social', 1, 'Enable Social Sharing Buttons', '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_show_social', '.wcu-field-section-social-icons'); // Show or Hide ?>
          <span class="wcu-field-section-social-icons">

      			<?php
            // Facebook
            wcusage_setting_toggle_option('wcusage_field_show_social_facebook', 1, 'Facebook', '30px');

            // Twitter
            wcusage_setting_toggle_option('wcusage_field_show_social_twitter', 1, 'X (Twitter)', '30px');

            // WhatsApp
            wcusage_setting_toggle_option('wcusage_field_show_social_whatsapp', 0, 'WhatsApp (Mobile Only)', '30px');

            // Tumblr
            wcusage_setting_toggle_option('wcusage_field_show_social_tumblr', 0, 'Tumblr', '30px');

            // Telegram
            wcusage_setting_toggle_option('wcusage_field_show_social_telegram', 0, 'Telegram', '30px');

            // Reddit
            wcusage_setting_toggle_option('wcusage_field_show_social_reddit', 0, 'Reddit', '30px');

            // Email
            wcusage_setting_toggle_option('wcusage_field_show_social_email', 1, 'Email', '30px');
            ?>

            <br/>

            <?php wcusage_setting_toggle('.wcusage_field_show_social_twitter', '.wcu-field-section-social-icons-twitter'); // Show or Hide ?>
            <span class="wcu-field-section-social-icons-twitter">

              <!-- Twitter Text -->
              <?php wcusage_setting_text_option('wcusage_field_show_social_twitter_text', get_bloginfo( 'name' ), esc_html__( 'Twitter Text', 'woo-coupon-usage' ), '30px'); ?>

              <br/>

            </span>

            <?php wcusage_setting_toggle('.wcusage_field_show_social_whatsapp', '.wcu-field-section-social-icons-whatsapp'); // Show or Hide ?>
            <span class="wcu-field-section-social-icons-whatsapp">

              <!-- WhatsApp Text -->
              <?php wcusage_setting_text_option('wcusage_field_show_social_whatsapp_text', '', esc_html__( 'WhatsApp Text', 'woo-coupon-usage' ), '30px'); ?>

              <br/>

            </span>

            <?php wcusage_setting_toggle('.wcusage_field_show_social_email', '.wcu-field-section-social-icons-email'); // Show or Hide ?>
            <span class="wcu-field-section-social-icons-email">

              <!-- Email Subject -->
              <?php $emailsubject = "Check this out on " . get_bloginfo( 'name' ); ?>
              <?php wcusage_setting_text_option('wcusage_field_show_social_email_subject', $emailsubject, esc_html__( 'Email Subject', 'woo-coupon-usage' ), '30px'); ?>

              <br/>

              <!-- Email Message -->
              <?php $emailmessage = "I saw this and thought you would like it:"; ?>
              <?php wcusage_setting_text_option('wcusage_field_show_social_email_message', $emailmessage, esc_html__( 'Email Message', 'woo-coupon-usage' ), '30px'); ?>

            </span>

          </span>

          <!-- ********** Creatives Message ********** -->
          <br/><hr/>
          <h3 id="wcu-setting-header-referral-social">
            <span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Creatives', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:
          </h3>

          <p>You can enable "Creatives" features in the "PRO modules" section or via the button below. A new settings tab ("Creatives") will then appear on this page for setup and customisation.</p>

          <br/>

          <!-- Enable Referral Links -->
          <?php wcusage_setting_toggle_option('wcusage_field_creatives_enable', 1, esc_html__( 'Enable "Creatives" Features', 'woo-coupon-usage' ), '0px'); ?>

          <?php wcusage_setting_toggle('.wcusage_field_creatives_enable', '.wcu-field-section-referral-url-creatives-settings'); // Show or Hide ?>
          <span class="wcu-field-section-referral-url-creatives-settings">

            <br/>

            <a href="#" onclick="wcusage_go_to_settings('#tab-creatives', '');"
              class="wcu-addons-box-view-details" style="margin-left: 0px;">
              <?php echo esc_html__( 'View "Creatives" Settings', 'woo-coupon-usage' ); ?>
            </a>

            <br/>

          </span>

          <br/><hr/>

          <h3 id="wcu-setting-header-landing-pages"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Landing Pages', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:</h3>

          <!-- Enable "affiliate landing pages" features. -->
          <?php wcusage_setting_toggle_option('wcusage_field_landing_pages', 0, esc_html__( 'Enable "affiliate landing pages" features.', 'woo-coupon-usage' ), '0px'); ?>
          <i><?php echo esc_html__( 'This option will enable the "affiliate landing page" metabox on pages, for you to assign a page as a landing page for an affiliate coupon.', 'woo-coupon-usage' ); ?></i>
          <br/><i><?php echo esc_html__( 'Assigning a landing page to a coupon will act in the same way as a referral URL (click tracking and auto assign coupon).', 'woo-coupon-usage' ); ?></i>

          <?php wcusage_setting_toggle('.wcusage_field_landing_pages', '.wcu-field-section-landing-pages'); // Show or Hide ?>
          <span class="wcu-field-section-landing-pages">

            <br/><br/>

            <!-- Show "Landing Pages" section on coupon affiliate dashboard. -->
            <?php wcusage_setting_toggle_option('wcusage_field_landing_pages_show', 0, esc_html__( 'Show "Landing Pages" section on coupon affiliate dashboard.', 'woo-coupon-usage' ), '0px'); ?>
            <i><?php echo esc_html__( 'This will show a "Landing Pages" section on the affiliate dashboard in the "Referral URL" tab, with all the assigned landing pages for the affiliate coupon.', 'woo-coupon-usage' ); ?></i>
            <br/><i><?php echo esc_html__( 'If there are no landing pages assigned to the coupon, then this will be hidden.', 'woo-coupon-usage' ); ?></i>

            <?php wcusage_setting_toggle('.wcusage_field_landing_pages_show', '.wcu-field-section-landing-pages-text'); // Show or Hide ?>
            <span class="wcu-field-section-landing-pages-text">

              <br/><br/>

              <!-- Landing Pages Text -->
              <?php wcusage_setting_text_option('wcusage_field_landing_pages_text', '', esc_html__( 'Landing Pages Text', 'woo-coupon-usage' ), '0px'); ?>
              <i><?php echo esc_html__( 'Display a custom message above the list of landing pages on the affiliate dashboard.', 'woo-coupon-usage' ); ?></i>

            </span>

            <br/>

          </span>

          <br/><hr/>

          <h3 id="wcu-setting-header-referral-popup"><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Referral Popup', 'woo-coupon-usage' ); ?><?php if( !wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?>:</h3>

          <p><?php echo esc_html__( 'Display a custom modal popup, bar notice, or small widget to customers when they visit your site via an affiliate referral link. This feature helps highlight the discount and encourage conversions.', 'woo-coupon-usage' ); ?></p>

          <!-- Enable Referral Popup -->
          <?php wcusage_setting_toggle_option('wcusage_field_referral_popup_enable', 0, esc_html__( 'Enable Referral Welcome Popups', 'woo-coupon-usage' ), '0px'); ?>

          <br/>

          <?php wcusage_setting_toggle('.wcusage_field_referral_popup_enable', '.wcu-field-section-referral-popup-settings'); // Show or Hide ?>
          <span class="wcu-field-section-referral-popup-settings">

            <br/>

            <a href="#" onclick="wcusage_go_to_settings('#tab-referral-popup', '');"
              class="wcu-addons-box-view-details" style="margin-left: 0px;">
              <?php echo esc_html__( 'View "Referral Popup" Settings', 'woo-coupon-usage' ); ?>
            </a>

            <br/>

          </span>

        </div>

    </span>

	</div>

 <?php
}
