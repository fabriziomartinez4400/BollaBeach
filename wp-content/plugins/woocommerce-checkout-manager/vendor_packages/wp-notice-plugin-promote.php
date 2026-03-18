<?php

if ( class_exists( 'QuadLayers\\WP_Notice_Plugin_Promote\\Load' ) ) {
	add_action('init', function() {
		/**
		 *  Promote constants
		 */
		define( 'WOOCCM_PROMOTE_LOGO_SRC', plugins_url( '/assets/backend/img/woocommerce-checkout-manager.jpg', WOOCCM_PLUGIN_FILE ) );
		/**
		 * Notice review
		 */
		define( 'WOOCCM_PROMOTE_REVIEW_URL', 'https://wordpress.org/support/plugin/woocommerce-checkout-manager/reviews/?filter=5#new-post' );
		/**
		 * Notice premium sell
		 */
		define( 'WOOCCM_PROMOTE_PREMIUM_SELL_SLUG', 'woocommerce-checkout-manager-pro' );
		define( 'WOOCCM_PROMOTE_PREMIUM_SELL_NAME', 'WooCommerce Checkout Manager PRO' );
		define( 'WOOCCM_PROMOTE_PREMIUM_SELL_URL', 'https://quadlayers.com/products/woocommerce-checkout-manager/?utm_source=wooccm_plugin&utm_medium=dashboard_notice&utm_campaign=premium_upgrade&utm_content=premium_link' );
		define( 'WOOCCM_PROMOTE_PREMIUM_INSTALL_URL', 'https://quadlayers.com/products/woocommerce-checkout-manager/?utm_source=wooccm_plugin&utm_medium=dashboard_notice&utm_campaign=premium_upgrade&utm_content=premium_install_button' );
		/**
		 * Notice cross sell 1
		 */
		define('WOOCCM_PROMOTE_CROSS_INSTALL_1_SLUG', 'wp-whatsapp-chat');
		define('WOOCCM_PROMOTE_CROSS_INSTALL_1_NAME', 'Social Chat');
		define(
			'WOOCCM_PROMOTE_CROSS_INSTALL_1_TITLE',
			wp_kses(
				sprintf(
					'<h3 style="margin:0">%s</h3>',
					esc_html__('Turn more visitors into customers.', 'woocommerce-checkout-manager')
				),
				array(
					'h3' => array(
						'style' => array()
					)
				)
			)
		);

		define(
			'WOOCCM_PROMOTE_CROSS_INSTALL_1_DESCRIPTION',
			esc_html__('Social Chat lets users contact you on WhatsApp with one click — faster support and higher conversions.', 'woocommerce-checkout-manager')
		);

		define('WOOCCM_PROMOTE_CROSS_INSTALL_1_URL', 'https://quadlayers.com/products/whatsapp-chat/?utm_source=wooccm_plugin&utm_medium=dashboard_notice&utm_campaign=cross_sell&utm_content=social_chat_link');
		define('WOOCCM_PROMOTE_CROSS_INSTALL_1_LOGO_SRC', plugins_url('/assets/backend/img/wp-whatsapp-chat.jpeg', WOOCCM_PLUGIN_FILE));
		/**
		 * Notice cross sell 2
		 */
		define( 'WOOCCM_PROMOTE_CROSS_INSTALL_2_SLUG', 'woocommerce-direct-checkout' );
		define( 'WOOCCM_PROMOTE_CROSS_INSTALL_2_NAME', 'Direct Checkout' );
		define(
			'WOOCCM_PROMOTE_CROSS_INSTALL_2_TITLE',
			wp_kses(
				sprintf(
					'<h3 style="margin:0">%s</h3>',
					esc_html__( 'Speed up your checkout process.', 'woocommerce-checkout-manager' )
				),
				array(
					'h3' => array(
						'style' => array()
					)
				)
			)
		);
		define( 'WOOCCM_PROMOTE_CROSS_INSTALL_2_DESCRIPTION', esc_html__( 'Reduce checkout steps by skipping the cart page. Faster purchases mean happier customers and fewer abandoned carts.', 'woocommerce-checkout-manager' ) );
		define( 'WOOCCM_PROMOTE_CROSS_INSTALL_2_URL', 'https://quadlayers.com/products/woocommerce-direct-checkout/?utm_source=wooccm_plugin&utm_medium=dashboard_notice&utm_campaign=cross_sell&utm_content=direct_checkout_link' );
		define( 'WOOCCM_PROMOTE_CROSS_INSTALL_2_LOGO_SRC', plugins_url( '/assets/backend/img/woocommerce-direct-checkout.jpg', WOOCCM_PLUGIN_FILE ) );

		new \QuadLayers\WP_Notice_Plugin_Promote\Load(
			WOOCCM_PLUGIN_FILE,
			array(
				array(
					'type'               => 'ranking',
					'notice_delay'       => 0,
					'notice_logo'        => WOOCCM_PROMOTE_LOGO_SRC,
					'notice_description' => sprintf(
									esc_html__( 'Hello! %2$s We\'ve spent countless hours developing this free plugin for you and would really appreciate it if you could drop us a quick rating. Your feedback is extremely valuable to us. %3$s It helps us to get better. Thanks for using %1$s.', 'woocommerce-checkout-manager' ),
									'<b>'.WOOCCM_PLUGIN_NAME.'</b>',
									'<span style="font-size: 16px;">🙂</span>',
									'<br>'
					),
					'notice_link'        => WOOCCM_PROMOTE_REVIEW_URL,
					'notice_more_link'   => 'https://quadlayers.com/account/support/?utm_source=wooccm_plugin&utm_medium=dashboard_notice&utm_campaign=support&utm_content=report_bug_button',
					'notice_more_label'  => esc_html__(
						'Report a bug',
						'woocommerce-checkout-manager'
					),
				),
				array(
					'plugin_slug'        => WOOCCM_PROMOTE_PREMIUM_SELL_SLUG,
					'plugin_install_link'   => WOOCCM_PROMOTE_PREMIUM_INSTALL_URL,
					'plugin_install_label'  => esc_html__(
						'Purchase Now',
						'woocommerce-checkout-manager'
					),
					'notice_delay'       => WEEK_IN_SECONDS,
					'notice_logo'        => WOOCCM_PROMOTE_LOGO_SRC,
					'notice_title'       => esc_html__(
						'Hello! We have a special gift!',
						'woocommerce-checkout-manager'
					),
					'notice_description' => sprintf(
						esc_html__(
							'Today we have a special gift for you. Use the coupon code %1$s within the next 48 hours to receive a %2$s discount on the premium version of the %3$s plugin.',
							'woocommerce-checkout-manager'
						),
						'ADMINPANEL20%',
						'20%',
						WOOCCM_PROMOTE_PREMIUM_SELL_NAME
					),
					'notice_more_link'   => WOOCCM_PROMOTE_PREMIUM_SELL_URL,
				),
				array(
					'plugin_slug'        => WOOCCM_PROMOTE_CROSS_INSTALL_1_SLUG,
					'notice_delay'       => MONTH_IN_SECONDS * 3,
					'notice_logo'        => WOOCCM_PROMOTE_CROSS_INSTALL_1_LOGO_SRC,
					'notice_title'       => WOOCCM_PROMOTE_CROSS_INSTALL_1_TITLE,
					'notice_description' => WOOCCM_PROMOTE_CROSS_INSTALL_1_DESCRIPTION,
					'notice_more_link'   => WOOCCM_PROMOTE_CROSS_INSTALL_1_URL
				),
				array(
					'plugin_slug'        => WOOCCM_PROMOTE_CROSS_INSTALL_2_SLUG,
					'notice_delay'       => MONTH_IN_SECONDS * 6,
					'notice_logo'        => WOOCCM_PROMOTE_CROSS_INSTALL_2_LOGO_SRC,
					'notice_title'       => WOOCCM_PROMOTE_CROSS_INSTALL_2_TITLE,
					'notice_description' => WOOCCM_PROMOTE_CROSS_INSTALL_2_DESCRIPTION,
					'notice_more_link'   => WOOCCM_PROMOTE_CROSS_INSTALL_2_URL
				),
			)
		);
	});
}
