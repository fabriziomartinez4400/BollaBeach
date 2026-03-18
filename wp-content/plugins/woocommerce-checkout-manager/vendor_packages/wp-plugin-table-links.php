<?php

if ( class_exists( 'QuadLayers\\WP_Plugin_Table_Links\\Load' ) ) {
	add_action('init', function() {
		new \QuadLayers\WP_Plugin_Table_Links\Load(
			WOOCCM_PLUGIN_FILE,
			array(
				array(
					'text' => esc_html__( 'Settings', 'woocommerce-checkout-manager' ),
					'url'  => admin_url( 'admin.php?page=wc-settings&tab=wooccm' ),
					'target' => '_self',
				),
				array(
					'text' => esc_html__( 'Premium', 'woocommerce-checkout-manager' ),
					'url'  => 'https://quadlayers.com/products/woocommerce-checkout-manager/?utm_source=wooccm_plugin&utm_medium=plugin_table&utm_campaign=premium_upgrade&utm_content=premium_link',
					'color' => 'green',
					'target' => '_blank',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Support', 'woocommerce-checkout-manager' ),
					'url'   => 'https://quadlayers.com/account/support/?utm_source=wooccm_plugin&utm_medium=plugin_table&utm_campaign=support&utm_content=support_link',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Documentation', 'woocommerce-checkout-manager' ),
					'url'   => 'https://quadlayers.com/documentation/woocommerce-checkout-manager/?utm_source=wooccm_plugin&utm_medium=plugin_table&utm_campaign=documentation&utm_content=documentation_link',
				),
			)
		);
	});

}
