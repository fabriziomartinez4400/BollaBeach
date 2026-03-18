<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_admin_list_page_html() {
// check user capabilities
if ( ! wcusage_check_admin_access() ) {
return;
}
$coupon_shortcode_page = wcusage_get_coupon_shortcode_page('0');
$registration_shortcode_page = wcusage_get_registration_shortcode_page('0');
?>

<!--- Font Awesome -->
<link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

<style>
.wcusage-admin-page-help-col { width: calc(50% - 80px); margin: 10px; padding: 15px 30px; background: #fff; float: left; border: 2px solid #e3e3e3; }
.wcusage-admin-page-help-col2 { width: calc(50% - 80px); float: left; }
.wcusage-admin-page-help-col3 { width: 100%; margin: 10px; padding: 15px 30px; background: #fff; float: left; border: 2px solid #e3e3e3; }
@media screen and (max-width: 1040px) { .wcusage-admin-page-help-col, .wcusage-admin-page-help-col2 { width: calc(100% - 76px); } }
strong { color: green; font-size: 16px; }
.wcusage-quicklink {
	display: block;
	width: calc(50% - 12px);
	padding: 20px 0;
	margin: 5px;
	background: #fafafa;
	float: left;
	border-radius: 10px;
	text-align: center;
	color: #333;
	font-size: 26px;
	font-weight: bold;
	text-decoration: none;
	border: 1px solid #333;
}
@media screen and (max-width: 1690px) {
	.wcusage-quicklink {
		font-size: 20px;
	}
}
@media screen and (max-width: 1260px) {
	.wcusage-quicklink {
		width: calc(100% - 12px);
	}
}
h2 { font-size: 22px; }

/* Modern styling for affiliate view */
.wcusage-tabs {
    margin-bottom: 30px;
    border-bottom: 2px solid #e1e5e9;
    background: #f8f9fa;
    padding: 0 20px;
    border-radius: 8px 8px 0 0;
}
.wcusage-tabs .nav-tab {
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 15px 25px;
    margin-right: 5px;
    text-decoration: none;
    color: #6c757d;
    border-radius: 0;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 14px;
}
.wcusage-tabs .nav-tab:hover {
    background: rgba(0, 123, 255, 0.1);
    color: #0056b3;
}
.wcusage-tabs .nav-tab-active {
    background: #fff;
    border-bottom: 3px solid #007cba;
    color: #007cba;
    margin-bottom: -1px;
    font-weight: 600;
}
.wcusage-tab-content {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-top: none;
    padding: 30px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
.tab-content h3 {
    margin-top: 0;
    margin-bottom: 25px;
    color: #2c3e50;
    font-size: 1.5em;
    font-weight: 600;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 10px;
}
.wcusage-coupon-dropdown {
    margin-bottom: 25px;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}
.wcusage-coupon-dropdown label {
    font-weight: 600;
    margin-right: 15px;
    color: #495057;
    font-size: 14px;
}
.wcusage-coupon-dropdown select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background: #fff;
    font-size: 14px;
    min-width: 200px;
    transition: border-color 0.3s ease;
}
.wcusage-coupon-dropdown select:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
}

/* Modern table styling */
.wp-list-table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 25px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}
.wp-list-table th,
.wp-list-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}
.wp-list-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
}
.wp-list-table tr:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}
.wp-list-table .striped > tbody > tr:nth-child(odd) {
    background: #fafbfc;
}
.wp-list-table .striped > tbody > tr:nth-child(odd):hover {
    background: #f1f3f4;
}

/* Enhanced visits table styling */
.wcusage-visits-table .dashicons {
    vertical-align: middle;
    margin-right: 8px;
}
.wcusage-visits-table code {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 12px;
    color: #495057;
    border: 1px solid #ced4da;
}
.wcusage-visits-table .button-small {
    padding: 6px 12px;
    font-size: 12px;
    line-height: 1.4;
    border-radius: 4px;
    border: 1px solid #007cba;
    background: #007cba;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}
.wcusage-visits-table .button-small:hover {
    background: #0056b3;
    border-color: #0056b3;
    text-decoration: none;
}
.wcusage-visits-table em {
    color: #6c757d;
    font-style: italic;
}
.wcusage-visits-table a {
    text-decoration: none;
    color: #007cba;
    transition: color 0.3s ease;
}
.wcusage-visits-table a:hover {
    text-decoration: underline;
    color: #0056b3;
}

/* Action buttons styling */
.wcusage-visits-table .payout-action-blacklistip {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
}
.wcusage-visits-table .payout-action-blacklistip:hover {
    background: #c82333;
    border-color: #bd2130;
}

/* Notice styling */
.notice {
    border-radius: 8px;
    border-left: 4px solid #007cba;
    background: #d1ecf1;
    color: #0c5460;
    padding: 15px 20px;
    margin: 20px 0;
}
.notice-success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}
.notice-error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

/* Responsive improvements */
@media screen and (max-width: 768px) {
    .wcusage-tab-content {
        padding: 20px 15px;
    }
    .wp-list-table th,
    .wp-list-table td {
        padding: 10px 12px;
        font-size: 14px;
    }
    .wcusage-tabs {
        padding: 0 10px;
    }
    .wcusage-tabs .nav-tab {
        padding: 12px 15px;
        font-size: 13px;
    }
}
</style>

<div class="wrap plugin-settings">
	
	<div class="wcusage-admin-page-help-col" style="width: calc(100% - 76px);">
	<img src="<?php echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL . 'images/coupon-affiliates-logo.png' ); ?>" alt="Coupon Affiliates" style="display: block; width: 100%; max-width: 550px; text-align: center; margin: 10px auto 20px auto;">

	<p style="text-align: center; font-weight: bold;"><?php echo esc_html__( "Create a coupon based affiliate program for your WooCommerce website, and view coupon usage statistics.", "woo-coupon-usage" ); ?></p>

	<p style="text-align: center; margin: 0; margin-bottom: 10px;">Website: <a href="https://couponaffiliates.com" target="_blank">www.couponaffiliates.com</a> | Follow us on Twitter <a href="https://twitter.com/CouponAffs" target="_blank">@CouponAffs</a> to keep up to date with new plugin features.</p>

	</div>

	<div style="clear: both;"></div>

	<div class="wcusage-admin-page-help-col">

		<h2><?php echo esc_html__( "Getting Started", "woo-coupon-usage" ); ?></h2>

    <p style="font-weight: bold; font-size: 15px;">Need help with setup? Follow our step-by-step setup guide (<a href="https://couponaffiliates.com/docs/setup-guide-free?utm_source=dashboard-link&amp;utm_medium=getting-started" target="_blank">click here</a>), or see a list of instructions below.</p>

    <hr/><br/>

		<?php if(!$coupon_shortcode_page) {

			do_action('wcusage_hook_getting_started_create');
			do_action('wcusage_hook_getting_started2');

		} else { ?>

		<?php echo esc_html__( "Affiliate dashboard page", "woo-coupon-usage" ); ?>:<br/><a href="<?php echo esc_url($coupon_shortcode_page); ?>" target="_blank"><?php echo esc_url($coupon_shortcode_page); ?></a>
		<br/><br/>

  		<?php echo esc_html__( "Affiliate registration page", "woo-coupon-usage" ); ?>:<br/><a href="<?php echo esc_url($registration_shortcode_page); ?>" target="_blank"><?php echo esc_url($registration_shortcode_page); ?></a>
  		<br/>

		<?php } ?>

    <br/>

    <h1><?php echo esc_html__( 'Instructions & Plugin Details', 'woo-coupon-usage' ); ?></h1>

    <hr/>

		<?php echo wp_kses_post(wcusage_how_to_use_content()); ?>

	</div>

	<div class="wcusage-admin-page-help-col2">

		<div class="wcusage-admin-page-help-col3">

		<div style="clear: both;"></div>

		<h2><?php echo esc_html__( "Admin Quicklinks", "woo-coupon-usage" ); ?></h2>

		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings')); ?>" class="wcusage-quicklink">
			<?php echo esc_html__( "Plugin Settings", "woo-coupon-usage" ); ?>
		</a>

		<a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" class="wcusage-quicklink">
			<?php echo esc_html__( "Coupons List", "woo-coupon-usage" ); ?>
		</a>

		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_payouts')); ?>" class="wcusage-quicklink"
			<?php if ( !wcu_fs()->can_use_premium_code() ) { ?>
				style="opacity: 0.5; pointer-events: none;"
			<?php } ?>
			>
			<?php echo esc_html__( "Commission Payouts", "woo-coupon-usage" ); ?> <?php if ( !wcu_fs()->can_use_premium_code() ) { ?>(Pro)<?php } ?>
		</a>

		<a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" class="wcusage-quicklink">
			<?php echo esc_html__( "Affiliate Dashboard URLs", "woo-coupon-usage" ); ?>
		</a>

		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_registrations')); ?>" class="wcusage-quicklink"
			<?php if ( !wcu_fs()->can_use_premium_code() ) { ?>
				style="opacity: 0.5; pointer-events: none;"
			<?php } ?>
			>
			<?php echo esc_html__( "Affiliate Registrations", "woo-coupon-usage" ); ?> <?php if ( !wcu_fs()->can_use_premium_code() ) { ?>(Pro)<?php } ?>
		</a>

		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_admin_reports')); ?>" class="wcusage-quicklink">
			<?php echo esc_html__( "Reports & Analytics", "woo-coupon-usage" ); ?>
		</a>

		<div style="clear: both;"></div>
		<br/>

		</div>

		<div class="wcusage-admin-page-help-col3">

		<h2><?php echo esc_html__( "Other Useful Links", "woo-coupon-usage" ); ?></h2>

		<?php if ( wcu_fs()->can_use_premium_code() ) { ?>
			<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-contact')); ?>" class="wcusage-quicklink" target="_blank">
		<?php } else { ?>
			<a href="https://wordpress.org/support/plugin/woo-coupon-usage/#new-topic-0" class="wcusage-quicklink" target="_blank">
		<?php } ?>
			<?php echo esc_html__( "Create Support Ticket", "woo-coupon-usage" ); ?>
		</a>

		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-account')); ?>" class="wcusage-quicklink" target="_blank">
			<?php echo esc_html__( "Your Account", "woo-coupon-usage" ); ?>
		</a>

		<a href="https://couponaffiliates.com/docs" target="_blank" class="wcusage-quicklink" target="_blank">
			<?php echo esc_html__( "Help Documentation", "woo-coupon-usage" ); ?>
		</a>

		<a href="https://twitter.com/CouponAffs" target="_blank" class="wcusage-quicklink" target="_blank">
			Twitter/X @CouponAffs
		</a>

		<?php if ( !wcu_fs()->can_use_premium_code() ) { ?>
		<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-pricing&trial=true')); ?>" target="_blank" class="wcusage-quicklink" style="width: calc(100% - 12px);">
			Try PRO free for 7 days!
		</a>
		<?php } ?>

		<div style="clear: both;"></div>
		<br/>

		</div>

		<div class="wcusage-admin-page-help-col3">

		<h2><?php echo esc_html__( "Latest News & Updates", "woo-coupon-usage" ); ?></h2>

		<?php
			global $text, $maxchar, $end;
			function substrwords($text, $maxchar, $end='...') {
				if (strlen($text) > $maxchar || $text == '') {
					$words = preg_split('/\s/', $text);
					$output = '';
					$i      = 0;
					while (1) {
						$length = strlen($output)+strlen($words[$i]);
						if ($length > $maxchar) {
							break;
						} else {
							$output .= " " . $words[$i];
							++$i;
						}
					}
					$output .= $end;
				} else {
					$output = $text;
				}
				return $output;
			}

			$rss = new DOMDocument();
			$rss->load('https://couponaffiliates.com/feed/');
			$feed = array();
			foreach ($rss->getElementsByTagName('item') as $node) {
				$item = array (
					'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
					'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
					'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
					'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				);
				array_push($feed, $item);
			}

			$limit = 8;
			for ($x=0; $x<$limit; $x++) {
				$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
				$link = $feed[$x]['link'] . "?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=dashboard-news";
				$description = $feed[$x]['desc'];
				$description = substrwords($description, 100);
				$date = date_i18n('l F d, Y', strtotime($feed[$x]['date']));
				echo '<p><strong><a href="'.esc_url($link).'" title="'.esc_attr($title).'">'.esc_html($title).'</a></strong><br />';
				echo '<small><em>Posted on '.esc_html($date).'</em></small>';
				echo '<br/>'.esc_html($description).'</p>';
			}
		?>

		<br/>
		Follow us on Twitter <a href="https://twitter.com/CouponAffs" target="_blank">@CouponAffs</a> to keep up to date with the latest news, and new features.

		</div>

</div>

<?php
}

function wcusage_how_to_use_content() {
?>

<p>

    <strong><?php echo esc_html__( 'Setup Guide', 'woo-coupon-usage' ); ?></strong><br/>

    <br/><span class="dashicons dashicons-arrow-right"></span>  Follow our step-by-step setup guide: <a href="https://couponaffiliates.com/docs/setup-guide-free?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=getting-started" style="text-decoration: none;" target="_blank">Click Here<span class="dashicons dashicons-external"></span></a>

    <br/>

    <br/><span class="dashicons dashicons-arrow-right"></span>  Watch our 5 minute setup video: <a href="https://couponaffiliates.com/docs/setup-guide-free?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=getting-started" style="text-decoration: none;" target="_blank">Click Here<span class="dashicons dashicons-external"></span></a>

    <br/>

		<br/><strong><?php echo esc_html__( 'Settings & Customization', 'woo-coupon-usage' ); ?></strong><br/>

		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can customize the plugin to meet your requirements on ', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings')); ?>" target="_blank"><?php echo esc_html__( 'settings page', 'woo-coupon-usage' ); ?></a>.<br/>

    <?php
    if ( function_exists('wc_coupons_enabled') ) {
      if ( !wc_coupons_enabled() ) {
        update_option( 'woocommerce_enable_coupons', 'yes' );
        ?>

        <br/><strong><?php echo esc_html__( 'Enable Coupon Codes', 'woo-coupon-usage' ); ?></strong><br/>

    		<br/><span class="dashicons dashicons-arrow-right"></span> Note: For this plugin to work, coupons need to be enabled in WooCommerce. This has been enabled automatically for you, in your "WooCommerce > Settings > General".<br/>

        <?php
      }
    }
    ?>

		<br/><strong><?php echo esc_html__( 'Creating Affiliates & Coupons', 'woo-coupon-usage' ); ?></strong><br/>

		<br/><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url(admin_url('post-new.php?post_type=shop_coupon')); ?>" target="_blank"><?php echo esc_html__( 'Create a coupon code', 'woo-coupon-usage' ); ?></a> <?php echo esc_html__( 'as normal in WooCommerce. You can then assign affiliate users to these coupons (see below) to make them an affiliate.', 'woo-coupon-usage' ); ?>

		<br/><br/>

		<strong><?php echo esc_html__( 'Assign Affiliates to Coupons', 'woo-coupon-usage' ); ?></strong><br/>

		
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To assign users to a specific coupon, go to the', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>"><?php echo esc_html__( 'coupons management page', 'woo-coupon-usage' ); ?></a>, <?php echo esc_html__( 'edit a coupon and assign users under the "coupon affiliates" tab', 'woo-coupon-usage' ); ?>. (<a href="https://couponaffiliates.com/docs/how-do-i-assign-users-to-coupons" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>)
		
		<br/><br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The affiliate user can then visit the "affiliate dashboard page" to view their affiliate statistics, commissions, referral URLs, etc, for the coupon(s) they are assigned to.', 'woo-coupon-usage' ); ?>
		
		<br/><br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Alternatively, you can allow affiliates to register as an affiliate. When accepted, this will then automatically create the coupon and assign them to it.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/pro-affiliate-registration" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>

		<br/>

		<br/><strong><?php echo esc_html__( 'Affiliate Dashboard', 'woo-coupon-usage' ); ?></strong><br/>

		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To display the affiliate dashboard for the coupon(s) assigned to the logged in user use shortcode:', 'woo-coupon-usage' ); ?> <span style="font-weight: bold; color: blue;">[couponaffiliates]</span>
		
		<br/>
		
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Affiliate users will then just need to visit this page to see their affiliate dashboard.', 'woo-coupon-usage' ); ?>
		
		<br/>
		
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If you are getting the "Failed to load ajax request" error on the affiliate dashboard', 'woo-coupon-usage' ); ?>, <a href="https://couponaffiliates.com/docs/error-ajax-request" target="_blank"><?php echo esc_html__( 'click here', 'woo-coupon-usage' ); ?></a> <?php echo esc_html__( 'for a solution.', 'woo-coupon-usage' ); ?>

		<br/><br/>

		<strong><?php echo esc_html__( 'Shortcodes', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To view a list of all the different shortcodes available to use with this plugin', 'woo-coupon-usage' ); ?>, <a href="https://couponaffiliates.com/docs/shortcodes?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=textlink" target="_blank"><?php echo esc_html__( 'click here', 'woo-coupon-usage' ); ?></a>.

		<br/>

		<br/><strong><?php echo esc_html__( 'Affiliate Login Form', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'A login form will be displayed on the affiliate dashboard for users to login to their account, and directly access their dashboard without needing to use the unique link.', 'woo-coupon-usage' ); ?>

		<br/><br/><strong><?php echo esc_html__( 'Set Your Commission Rates', 'woo-coupon-usage' ); ?></strong><br/>

		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can set your custom commission rates in the "commission" tab of the', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings')); ?>" target="_blank"><?php echo esc_html__( 'settings page', 'woo-coupon-usage' ); ?></a>.
		<br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can set fixed commission amounts (either per order, or per product), alongside percentage of the total order. You can set all 3 of these for a combined total if required.', 'woo-coupon-usage' ); ?>
    	<br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'With the PRO version can also set commission rates per each individual affiliate/coupon, or product.', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs/flexible-commission-settings/" target="_blank"><?php echo esc_html__( 'Learn More.', 'woo-coupon-usage' ); ?></a>

		<br/>

		<br/><strong><?php echo esc_html__( 'View Coupons List & Unique Links', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can also view a full list of the coupons, with the assigned user, pending payments, and unique affiliate dashboard links for each coupon (to view yourself) on the WooCommerce', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" target="_blank"><?php echo esc_html__( 'coupon list page', 'woo-coupon-usage' ); ?>.</a>

		<br/>

		<br/><strong><?php echo esc_html__( 'Referral Links', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can enable referral URLs, and customise the settings for these, in the "Referral Links" tab on the settings tab.', 'woo-coupon-usage' ); ?></br>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Referral Links can be generated by the affiliate on the affiliate dashboard page.', 'woo-coupon-usage' ); ?></br>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'If the referral URL is clicked, the coupon code will automatically be applied to the users checkout. The coupon must be used for commission to be tracked.', 'woo-coupon-usage' ); ?></br>

		<br/>

		<strong><?php echo esc_html__( 'Basic Admin Reports', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The free version gives access to admin reports for the past 4 weeks. Reports allow you to see the overall statistics for all affiliates/coupons, and each individual affiliate/coupon, on a single page', 'woo-coupon-usage' ); ?>: <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_admin_reports')); ?>"><?php echo esc_html__( 'View Reports', 'woo-coupon-usage' ); ?></a>

		<br/><br/>

		<strong><?php echo esc_html__( 'Multi-Currency Support', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To get started with Multi-Currency support, simply enable "multi-currency settings" under the "General" settings tab. A new tab will the appear labeled "currencies" to customise your currencies and conversion rates.', 'woo-coupon-usage' ); ?>
		
		<br/><br/>

		<strong><?php echo esc_html__( 'Display Affiliate Registration Form', 'woo-coupon-usage' ); ?></strong><br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'The affiliate registration form will be shown as default for logged out users on the affiliate dashboard page, next to the login form.', 'woo-coupon-usage' ); ?>
		<br/>
		<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To display the affiliate application form on a custom page, use the shortcode: ', 'woo-coupon-usage' ); ?> <span style="font-weight: bold; color: blue;">[couponaffiliates-register]</span>

	</p>

	<div <?php
    if ( !wcu_fs()->can_use_premium_code() ) {
        ?>title="Available with Pro version." style="opacity: 0.6;"<?php
    }
    ?>>

		<br/><hr/>

		<h1><?php echo esc_html__( 'Pro Features', 'woo-coupon-usage' ); ?> <?php
    if ( !wcu_fs()->can_use_premium_code() ) {
		?><strong style="color: green;"><a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-pricing&trial=true')); ?>"><?php echo esc_html__( 'UPGRADE', 'woo-coupon-usage' ); ?></a></strong><?php
    }
    ?></h1>

	<br/>

		<p>

			<strong><?php echo esc_html__( 'Advanced Admin Reports', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'With the Pro version get access to unlimited date range on the admin reports, export to CSV, and access to date comparison features, to compare analytics between 2 sets of dates', 'woo-coupon-usage' ); ?>: <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_admin_reports')); ?>"><?php echo esc_html__( 'View Reports', 'woo-coupon-usage' ); ?></a>

      <br/><br/>

      <strong><?php echo esc_html__( 'Manage/Track Payouts', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Affiliates can request payouts for coupons they are assigned to in the "Payouts" tab on the coupon affiliate dashboard page.', 'woo-coupon-usage' ); ?>
			<br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can track and manage payouts on the', 'woo-coupon-usage' ); ?> "<a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_payouts')); ?>"><?php echo esc_html__( 'Commission Payouts', 'woo-coupon-usage' ); ?></a>" <?php echo esc_html__( 'admin page', 'woo-coupon-usage' ); ?>.
			<br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'For detailed information regarding the payouts features, and setting up one-click payout methods such as Stripe and PayPal,', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs-category/features-commission" target="_blank"><?php echo esc_html__( 'click here', 'woo-coupon-usage' ); ?>.</a>

			<br/><br/>

		<strong><?php echo esc_html__( 'Edit Unpaid Commission', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To manually edit/change "unpaid commission" for a coupon, go to the', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>"><?php echo esc_html__( 'coupons management page', 'woo-coupon-usage' ); ?></a>, <?php echo esc_html__( 'click "edit" on the coupon, then go to "Coupon Affiliates & Commission" data settings tab.', 'woo-coupon-usage' ); ?>

			<br/><br/>

      <strong><?php echo esc_html__( 'Creatives', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To get started with creatives, first go to the "Creatives" settings to to enable and customise the settings.', 'woo-coupon-usage' ); ?>
			<br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo sprintf(esc_html__( 'Next, simply <a href="%s">add new creatives</a> and they will be displayed in the new "Creatives" tab on the affiliate dashboard.', 'woo-coupon-usage' ), esc_url(admin_url('edit.php?post_type=wcu-creatives')) ); ?>

			<br/><br/>

      <strong><?php echo esc_html__( 'Affiliate Email Reports', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To get started with affiliate email reports, first enable it in the "PRO Modules" section of the settings, then setup and customise the settings in the new "Reports" settings tab that will appear.', 'woo-coupon-usage' ); ?>

			<br/><br/>

      <strong><?php echo esc_html__( 'Flexible Commission - Per Product or Coupon', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Flexible commission settings can also be set at product level, as well as coupon level. To do this, simply edit a product, or coupon, and visit the "Coupon Affiliates & Commission" data settings tab.', 'woo-coupon-usage' ); ?>

			<br/><br/>

      <strong><?php echo esc_html__( 'Lifetime Commission', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Give your affiliates lifetime commission for ALL future purchases from all their referred users.', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'To do this go to "Pro Settings" and toggle on "Enable lifetime commission features." option.', 'woo-coupon-usage' ); ?>

			<br/><br/>

      <strong><?php echo esc_html__( 'Affiliate Landing Pages', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'Ability to link a landing page to an affiliate coupon, which will then work the same as a referral URL.', 'woo-coupon-usage' ); ?> <?php echo esc_html__( 'To get started, simply enable the "Affiliate Landing Pages" option in "Pro Settings" then you will see a new meta box when editing pages, to assign an affiliate coupon to that page.', 'woo-coupon-usage' ); ?>

			<br/><br/>

			<strong><?php echo esc_html__( 'Commission Line Graphs', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'You can enable line graphs in the "Pro Settings" tab. This will show some nice line graphs on the statistics tab of the affiliate dashboard.', 'woo-coupon-usage' ); ?>

      <br/><br/>

			<strong><?php echo esc_html__( 'Other PRO Modules', 'woo-coupon-usage' ); ?></strong><br/>
			<br/><span class="dashicons dashicons-arrow-right"></span> <?php echo esc_html__( 'To view all available PRO modules, simply visit the "Pro Modules" tab on the settings page, and enable the modules you want to use.', 'woo-coupon-usage' ); ?>

		</p>

	</div>

	<br/>

	<h1><?php echo esc_html__( 'Other Information', 'woo-coupon-usage' ); ?></h1>

	<hr/>

	<?php echo esc_html__( 'To view all features available with PRO, visit our website:', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com" target="_blank">https://couponaffiliates.com</a>
	
	<br/>
	
	<h2>Documentation</h2>

	<?php echo esc_html__( 'For more in-depth tutorials and guides, see our plugin documentation:', 'woo-coupon-usage' ); ?> <a href="https://couponaffiliates.com/docs" target="_blank">https://couponaffiliates.com/docs</a>

<?php
}

/**
 * View Affiliate Admin Page
 */
function wcusage_view_affiliate_page() {
    // Include the affiliate view page from separate file
    require_once plugin_dir_path(__FILE__) . 'admin-view-affiliate.php';
}

/**
 * MLA Users Admin Page
 */
function wcusage_mla_users_page_html() {
    require_once plugin_dir_path(__FILE__) . 'class-mla-users-table.php';
    wcusage_mla_users_page();
}



