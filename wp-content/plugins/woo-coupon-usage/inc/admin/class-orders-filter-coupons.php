<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// fire it up!
add_action( 'plugins_loaded', 'wcusage_class_orders_filters_coupons' );

/**
 * Adds custom filtering to the orders screen to allow filtering by coupon used.
 */
 class wcusage_class_orders_filters_coupons {

	const VERSION = '1.1.0';

	/** @var wcusage_class_orders_filters_coupons single instance of this plugin */
	protected static $instance;

	/**
	 * WC_Filter_Orders constructor.
	 */
	public function __construct() {

		// load translations
		//add_action( 'init', array( $this, 'load_translation' ) );

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

			// adds the coupon filtering dropdown to the orders page
			add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'filter_orders_by_coupon_used' ) );

			// makes coupons filterable
			add_filter( 'posts_join',  array( $this, 'add_order_items_join' ) );
			add_filter( 'posts_where', array( $this, 'add_filterable_where' ) );

			// HPOS-compatible filtering (WooCommerce Custom Orders Table)
			add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'add_orders_table_query_clauses' ), 10, 2 );

		}

	}

	/**
	 * Adds the coupon filtering dropdown to the orders list
	 */
	public function filter_orders_by_coupon_used() {
	?>

		<input placeholder="Filter by coupon code..." type="text" name="wcu_coupons" id="dropdown_coupons_used"
		value="<?php echo esc_attr( isset( $_GET['wcu_coupons'] ) ? $_GET['wcu_coupons'] : '' ); ?>"></input>

		<?php if( isset($_GET['wcu_coupons']) ) { ?>
			<p style="position: absolute; top: 10px; display: flex; color: green; font-weight: bold;">
			<?php if($_GET['wcu_coupons'] == "ALL") { ?>
			<span class="dashicons dashicons-info-outline"></span>&nbsp; Filter: Only showing orders that used ANY coupon code.
			<?php } elseif($_GET['wcu_coupons'] != "") { ?>
			<span class="dashicons dashicons-info-outline"></span>&nbsp; Filter: Only showing orders that used coupon code: <?php echo esc_html( $_GET['wcu_coupons'] ); ?>
			<?php } ?>
			</p>
		<?php } ?>

	<?php
	}

	/**
	 * Modify SQL JOIN for filtering the orders by any coupons used
	 *
	 * @param string $join JOIN part of the sql query
	 * @return string $join modified JOIN part of sql query
	 */
	public function add_order_items_join( $join ) {
		global $typenow, $wpdb;
	
		// Check if we're dealing with shop orders and the wcu_coupons query parameter is set
		if ( 'shop_order' === $typenow && isset( $_GET['wcu_coupons'] ) && ! empty( $_GET['wcu_coupons'] ) ) {
			// Check if HPOS is enabled
			if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items woi ON {$wpdb->prefix}wc_orders.id = woi.order_id AND woi.order_item_type='coupon'";
			} else {
				$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items woi ON {$wpdb->posts}.ID = woi.order_id AND woi.order_item_type='coupon'";
			}
		}
	
		return $join;
	}

	/**
	 * Modify SQL WHERE for filtering the orders by any coupons used
	 *
	 * @param string $where WHERE part of the sql query
	 * @return string $where modified WHERE part of sql query
	 */
	public function add_filterable_where( $where ) {
		global $typenow, $wpdb;

		if ( 'shop_order' === $typenow && isset( $_GET['wcu_coupons'] ) && ! empty( $_GET['wcu_coupons'] ) ) {

			$coupon = isset( $_GET['wcu_coupons'] ) ? wc_clean( wp_unslash( $_GET['wcu_coupons'] ) ) : '';

			// Main WHERE query part (JOIN already limited to coupon items)
			if ( $coupon === 'ALL' ) {
				$where .= " AND (woi.order_item_name IS NOT NULL AND woi.order_item_name <> '')";
			} elseif ( $coupon === 'NONE' ) {
				// No coupon used: ensure there are no matching coupon order items
				$where .= " AND woi.order_item_id IS NULL";
			} else {
				$where .= $wpdb->prepare( " AND woi.order_item_name = %s", $coupon );
			}

		}

		return $where;
	}

	/**
	 * HPOS-compatible: modify orders table query clauses to filter by coupon code.
	 *
	 * @param array $clauses Query clauses: select, from, join, where, group_by, order_by, limits
	 * @param mixed $args Optional args (varies by Woo versions)
	 * @return array Modified clauses
	 */
	public function add_orders_table_query_clauses( $clauses, $args = null ) {
		// Only run in admin with our filter present
		if ( ! is_admin() || ! isset( $_GET['wcu_coupons'] ) || '' === $_GET['wcu_coupons'] ) {
			return $clauses;
		}

		// Ensure HPOS/custom orders table actually in use
		if ( ! class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' ) || ! \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return $clauses;
		}

		global $wpdb;
		$coupon = wc_clean( wp_unslash( $_GET['wcu_coupons'] ) );

		// LEFT JOIN coupon order items (limit join to coupon items only)
		$join_snippet = " LEFT JOIN {$wpdb->prefix}woocommerce_order_items woi ON {$wpdb->prefix}wc_orders.id = woi.order_id AND woi.order_item_type='coupon'";
		if ( ! isset( $clauses['join'] ) ) {
			$clauses['join'] = '';
		}
		if ( strpos( $clauses['join'], 'woocommerce_order_items woi' ) === false ) {
			$clauses['join'] .= $join_snippet;
		}

		// WHERE fragment based on coupon parameter
		$where_fragment = '';
		if ( $coupon === 'ALL' ) {
			$where_fragment = " AND (woi.order_item_name IS NOT NULL AND woi.order_item_name <> '')";
		} elseif ( $coupon === 'NONE' ) {
			$where_fragment = " AND woi.order_item_id IS NULL";
		} else {
			$where_fragment = $wpdb->prepare( " AND woi.order_item_name = %s", $coupon );
		}
		if ( ! isset( $clauses['where'] ) ) {
			$clauses['where'] = '';
		}
		$clauses['where'] .= $where_fragment;

		// Prevent duplicates when multiple coupons exist on one order
		if ( empty( $clauses['group_by'] ) ) {
			$clauses['group_by'] = "{$wpdb->prefix}wc_orders.id";
		}

		return $clauses;
	}

	/**
	 * Main wcusage_class_orders_filters_coupons Instance, ensures only one instance is/can be loaded
	 *
	 * @see wcusage_class_orders_filters_coupons()
	 * @return wcusage_class_orders_filters_coupons
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
		 	self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 */
	public function __clone() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'woo-coupon-usage' ), 'Filter WC Orders by Coupon' ), '1.1.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'woo-coupon-usage' ), 'Filter WC Orders by Coupon' ), '1.1.0' );
	}


}

/**
 * Returns the One True Instance of wcusage_class_orders_filters_coupons
 *
 * @return \wcusage_class_orders_filters_coupons
 */
function wcusage_class_orders_filters_coupons() {
	return wcusage_class_orders_filters_coupons::instance();
}
