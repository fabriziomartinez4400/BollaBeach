<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get custom styles for affiliate dashboard
 *
 * @return mixed
 */
if( !function_exists( 'wcusage_custom_styles' ) ) {
  function wcusage_custom_styles() {

    $options = get_option( 'wcusage_options' );

  	$wcusage_color_tab = wcusage_get_setting_value('wcusage_field_color_tab', '#1b3e47');
  	$wcusage_color_tab_font = wcusage_get_setting_value('wcusage_field_color_tab_font', '#fff');
  	$wcusage_color_tab_hover = wcusage_get_setting_value('wcusage_field_color_tab_hover', '#005d75');
  	$wcusage_color_tab_hover_font = wcusage_get_setting_value('wcusage_field_color_tab_hover_font', '#fff');
  	$wcusage_color_table = wcusage_get_setting_value('wcusage_field_color_table', '#f4f4f4');
  	$wcusage_color_table_font = wcusage_get_setting_value('wcusage_field_color_table_font', '#0a0a0a');
  	$wcusage_color_button = wcusage_get_setting_value('wcusage_field_color_button', '#005d75');
  	$wcusage_color_button_font = wcusage_get_setting_value('wcusage_field_color_button_font', '#fff');
    $wcusage_color_button_hover = wcusage_get_setting_value('wcusage_field_color_button_hover', '#1b3e47');
  	$wcusage_color_button_font_hover = wcusage_get_setting_value('wcusage_field_color_button_font_hover', '#fff');
  	$wcusage_color_stats_icon = wcusage_get_setting_value('wcusage_field_color_stats_icon', '#bebebe');
  	?>

  	<style>
  		<?php if($wcusage_color_table) { ?>
  			.wcuTableHead, .wcuTableFoot  {
  				background: <?php echo esc_html($wcusage_color_table); ?> !important;
  				color: <?php echo esc_html($wcusage_color_table_font); ?> !important;
  			}
        	.wcuTableHead span, .wcuTableFoot span {
  				color: <?php echo esc_html($wcusage_color_table_font); ?> !important;
  			}
  		<?php } ?>
  		<?php if($wcusage_color_tab) { ?>
  			.wcutab button  {
  				background: <?php echo esc_html($wcusage_color_tab); ?>;
  				color: <?php echo esc_html($wcusage_color_tab_font); ?>;
  			}
  		<?php } ?>
  		<?php if($wcusage_color_tab_hover) { ?>
  			.wcutab button:hover, .wcutab button.active  {
  				background: <?php echo esc_html($wcusage_color_tab_hover); ?> !important;
  				color: <?php echo esc_html($wcusage_color_tab_hover_font); ?> !important;
  			}
			.wcu-dash-coupon-area .wcutab .wcutab-active {
				background: <?php echo esc_html($wcusage_color_tab_hover); ?> !important;
  				color: <?php echo esc_html($wcusage_color_tab_hover_font); ?> !important;
			}
  		<?php } ?>
  		<?php if($wcusage_color_button) { ?>
  			.wcu-button-export, #wcu-monthly-orders-button, #wcu-orders-button, #wcu-summary-button, #wcusage_copylink,
        #wcu-paypal-button, #submitpayoutno, .wcu-paypal-button,
        #wcu6 .woocommerce-EditAccountForm .woocommerce-Button, #ml-wcu4 .woocommerce-EditAccountForm .woocommerce-Button,
        #wcu-add-campaign-button, #wcu-add-directlink-button, #wcu-add-mlainvite-button,
        .wcu-save-settings-button, #wcu6 button, #wcu-register-button, .wcusage-login-form-col .woocommerce-button,
        .wcusage_copylink, .wcusage_creativelink, .wcu-coupon-list-button, .product-rates-search, .product-rates-copy, #wcu-download-qr {
  				background: <?php echo esc_html($wcusage_color_button); ?> !important;
  				color: <?php echo esc_html($wcusage_color_button_font); ?> !important;
  				text-shadow: 0 0 2px #000;
				padding: 5px 10px !important;
				font-size: 16px !important;
  			}
		.login-registration-container .woocommerce-form-login__submit {
  			background: <?php echo esc_html($wcusage_color_button); ?> !important;
  			color: <?php echo esc_html($wcusage_color_button_font); ?> !important;
		}
		#wcu-add-campaign-button {
			max-width: 100px;
		}
        .wcusage-social-icon {
  				color: <?php echo esc_html($wcusage_color_button); ?> !important;
  			}
  		<?php } ?>
      <?php if($wcusage_color_button_hover) { ?>
  			.wcu-button-export:hover, #wcu-monthly-orders-button:hover, #wcu-orders-button:hover, #wcu-summary-button:hover, #wcusage_copylink:hover,
        #wcu-paypal-button:hover, #submitpayoutno:hover, .wcu-paypal-button:hover,
        #wcu6 .woocommerce-EditAccountForm .woocommerce-Button:hover, #ml-wcu4 .woocommerce-EditAccountForm .woocommerce-Button:hover,
        #wcu-add-campaign-button:hover, #wcu-add-directlink-button:hover, #wcu-add-mlainvite-button:hover,
        .wcu-save-settings-button:hover, #wcu6 button:hover, #wcu-register-button:hover, .wcusage-login-form-col .woocommerce-button:hover,
        .wcusage_copylink:hover, .wcusage_creativelink:hover, .wcu-coupon-list-button:hover, .product-rates-search:hover, .product-rates-copy:hover, #wcu-download-qr:hover,
		.login-registration-container .woocommerce-form-login__submit:hover {
  				background: <?php echo esc_html($wcusage_color_button_hover); ?> !important;
  				color: <?php echo esc_html($wcusage_color_button_font_hover); ?> !important;
  			}
        .wcusage-social-icon:hover {
  				color: <?php echo esc_html($wcusage_color_button_hover); ?> !important;
  			}
  		<?php } ?>
  		<?php if($wcusage_color_stats_icon) { ?>
  			.wcusage-info-box::before {
  				color: <?php echo esc_html($wcusage_color_stats_icon); ?> !important;
  			}
  		<?php } ?>

		@media screen and (max-width: 768px) {
			.wcu-dash-coupon-area {
				padding: 1px;
			}
			.wcusage-info-box {
				margin-bottom: 10px;
			}
		}

		/* Tabs style */
		<?php $wcusage_field_tabs_style = wcusage_get_setting_value('wcusage_field_tabs_style', '2'); ?>
		<?php if($wcusage_field_tabs_style == '2') { ?>
		@media screen and (min-width: 768px) {
			.wcutab {
				display: flex;
				flex-wrap: wrap;
				width: 100%;
			}
			.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
				flex-grow: 1;
				text-align: center;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				line-height: normal;
				margin-right: 4px;
			}
			.wcutab .wcutablinks:last-child, .wcutab .ml_wcutablinks:last-child {
				margin-right: 0;
			}
		}
		<?php } ?>

		<?php $wcusage_field_mobile_menu = wcusage_get_setting_value('wcusage_field_mobile_menu', 'dropdown'); ?>
		<?php if($wcusage_field_mobile_menu == 'dropdown') { ?>
		@media screen and (max-width: 1000px) {
			.wcutab {
				display: none;
			}
		}
		<?php } ?>

		/* Tabs border radius */
		<?php $wcusage_field_tabs_border = wcusage_get_setting_value('wcusage_field_tabs_border', '1'); ?>
		<?php if($wcusage_field_tabs_border == '1') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			border-radius: 5px;
		}
		<?php } ?>
		<?php if($wcusage_field_tabs_border == '2') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			border-radius: 25px;
		}
		<?php } ?>
		<?php if($wcusage_field_tabs_border == '3') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			border-radius: 0;
		}
		<?php } ?>

		/* Tabs Padding */
		<?php $wcusage_field_tabs_padding = wcusage_get_setting_value('wcusage_field_tabs_padding', '2'); ?>
		<?php if($wcusage_field_tabs_padding == '1') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			padding: 8px 10px;
		}
		<?php } ?>
		<?php if($wcusage_field_tabs_padding == '2') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			padding: 10px 12px;
		}
		<?php } ?>
		<?php if($wcusage_field_tabs_padding == '3') { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			padding: 12px 14px;
		}
		<?php } ?>

		/* Tabs responsive */
		@media screen and (max-width: 768px) {
			.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
				padding: 7px 12px 8px 12px;
				font-size: 14px;
			}
		}

		/* Tabs Font Size */
		<?php $wcusage_field_tabs_font_size = wcusage_get_setting_value('wcusage_field_tabs_font_size', ''); ?>
		<?php if($wcusage_field_tabs_font_size) { ?>
		.wcutab .wcutablinks, .wcutab .ml_wcutablinks {
			font-size: <?php echo esc_html($wcusage_field_tabs_font_size); ?>px !important;
		}
		<?php } ?>

		<?php
		// Custom Dashboard CSS (Design settings)
		$wcusage_custom_dashboard_css = wcusage_get_setting_value('wcusage_field_custom_dashboard_css', '');
		if ( ! empty( $wcusage_custom_dashboard_css ) ) {
			// Strip HTML tags for safety, but keep CSS characters intact
			$wcusage_custom_dashboard_css = strip_tags( $wcusage_custom_dashboard_css );
			?>
			/* Custom Dashboard CSS */
			<?php echo wp_strip_all_tags($wcusage_custom_dashboard_css); ?>
			<?php
		}
		?>

  	</style>

  <?php
  }
}
add_action('wcusage_hook_custom_styles', 'wcusage_custom_styles', 10, 0);