<?php

namespace QuadLayers\WOOCCM;

/**
 * Upload Class
 */
class Upload {

	protected static $_instance;

	public function __construct() {
		add_action( 'wp_ajax_wooccm_order_attachment_update', array( $this, 'ajax_delete_attachment' ) );

		// Checkout
		// -----------------------------------------------------------------------.
		add_action( 'wp_ajax_wooccm_checkout_attachment_upload', array( $this, 'ajax_checkout_attachment_upload' ) );
		add_action( 'wp_ajax_nopriv_wooccm_checkout_attachment_upload', array( $this, 'ajax_checkout_attachment_upload' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_attachment_ids' ), 99 );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected function process_uploads( $files, $key, $post_id = 0 ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Security Fix: CVE-2025-12500 - Add upload limits
		$max_files_per_upload = apply_filters( 'wooccm_max_files_per_upload', 10 );
		$file_count           = is_array( $files['name'] ) ? count( $files['name'] ) : 0;

		if ( $file_count > $max_files_per_upload ) {
			wc_add_notice(
				sprintf(
					/* translators: %d: maximum number of files */
					esc_html__( 'You can only upload a maximum of %d files at once.', 'woocommerce-checkout-manager' ),
					$max_files_per_upload
				),
				'error'
			);
			return array();
		}

		$attachment_ids = array();

		add_filter(
			'upload_dir',
			function ( $param ) {
				$param['path'] = sprintf( '%s/wooccm_uploads', $param['basedir'] );
				$param['url']  = sprintf( '%s/wooccm_uploads', $param['baseurl'] );
				return $param;
			},
			10
		);

		foreach ( $files['name'] as $id => $value ) {

			if ( $files['name'][ $id ] ) {

				$_FILES[ $key ] = array(
					'name'     => $files['name'][ $id ],
					'type'     => $files['type'][ $id ],
					'tmp_name' => $files['tmp_name'][ $id ],
					'error'    => $files['error'][ $id ],
					'size'     => $files['size'][ $id ],
				);

				$attachment_id = media_handle_upload( $key, $post_id );

				if ( ! is_wp_error( $attachment_id ) ) {
					$attachment_ids[] = $attachment_id;
				} else {
					wc_add_notice( $attachment_id->get_error_message(), 'error' );
					// wp_send_json_error( $attachment_id->get_error_message() );
				}
			}
		}

		return $attachment_ids;
	}

	public function ajax_delete_attachment() {
		if ( ! empty( $_REQUEST ) && check_admin_referer( 'wooccm_upload', 'nonce' ) ) {

			$array1 = explode( ',', sanitize_text_field( isset( $_REQUEST['all_attachments_ids'] ) ? wp_unslash( $_REQUEST['all_attachments_ids'] ) : '' ) );
			$array2 = explode( ',', sanitize_text_field( isset( $_REQUEST['delete_attachments_ids'] ) ? wp_unslash( $_REQUEST['delete_attachments_ids'] ) : '' ) );

			if ( empty( $array1 ) || empty( $array2 ) ) {
				wp_send_json_error( esc_html__( 'No attachment selected.', 'woocommerce-checkout-manager' ) );
			}

			$attachment_ids = array_diff( $array1, $array2 );

			if ( ! empty( $attachment_ids ) ) {

				foreach ( $attachment_ids as $key => $attachtoremove ) {

					// Check the Attachment exists...
					if ( get_post_status( $attachtoremove ) == false ) {
						continue;
					}

					// Check the Attachment is associated with an Order
					$post_parent = get_post_field( 'post_parent', $attachtoremove );

					if ( empty( $post_parent ) ) {
						continue;
					} else {
						// if ( get_post_type( $post_parent ) <> 'shop_order' && get_post_type( $post_parent ) <> 'shop_order_placehold' ) {
						if ( ! in_array( get_post_type( $post_parent ), array( 'shop_order', 'shop_order_placehold' ) ) ) {
							continue;
						}
					}

					$order = wc_get_order( $post_parent );

					$current_user = wp_get_current_user();

					$session_handler = WC()->session;

					// Security Fix: CVE-2025-13930 - Fixed inverted login check
					$is_user_logged = 0 !== $current_user->ID;

					// For guest orders, require order key validation
					if ( ! $is_user_logged ) {
						// Validate order key for guest orders
						$order_key = isset( $_REQUEST['order_key'] ) ? wc_clean( wp_unslash( $_REQUEST['order_key'] ) ) : '';

						if ( empty( $order_key ) || ! hash_equals( $order->get_order_key(), $order_key ) ) {
							wp_send_json_error( esc_html__( 'Invalid order key.', 'woocommerce-checkout-manager' ) );
						}

						// Verify session email matches order email
						$session_customer       = $session_handler ? $session_handler->get( 'customer' ) : array();
						$session_customer_email = isset( $session_customer['email'] ) ? $session_customer['email'] : '';
						$order_email            = $order->get_billing_email();

						if ( empty( $session_customer_email ) || $order_email !== $session_customer_email ) {
							wp_send_json_error( esc_html__( 'Email mismatch.', 'woocommerce-checkout-manager' ) );
						}
					} else {
						// For logged-in users, verify ownership or capabilities
						$order_user_id         = $order->get_user_id();
						$user_has_capabilities = current_user_can( 'administrator' )
							|| current_user_can( 'edit_others_shop_orders' )
							|| current_user_can( 'delete_others_shop_orders' );

						if ( ! $user_has_capabilities && $current_user->ID !== $order_user_id ) {
							wp_send_json_error( esc_html__( 'This is not your order.', 'woocommerce-checkout-manager' ) );
						}
					}

					wp_delete_attachment( $attachtoremove );
				}
			}

			wp_send_json_success( 'Deleted successfully.', 'woocommerce-checkout-manager' );
		}
	}

	public function ajax_checkout_attachment_upload() {
		// Security Fix: CVE-2025-12500 - Added proper authorization checks

		// Step 1: Verify nonce for CSRF protection
		if ( ! check_admin_referer( 'wooccm_upload', 'nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'woocommerce-checkout-manager' ) ) );
		}

		// Step 2: Verify files are present
		if ( ! isset( $_FILES['wooccm_checkout_attachment_upload'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No files provided.', 'woocommerce-checkout-manager' ) ) );
		}

		// Step 3: Verify WooCommerce is available and ensure it's loaded
		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'WooCommerce is not available.', 'woocommerce-checkout-manager' ) ) );
		}

		// Ensure WooCommerce is initialized
		$wc = WC();
		if ( ! $wc ) {
			wp_send_json_error( array( 'message' => esc_html__( 'WooCommerce session not initialized.', 'woocommerce-checkout-manager' ) ) );
		}

		// Step 4: Verify user is in checkout process (applies to ALL users - logged in and guests).
		// This ensures that any user (including subscribers, customers, etc.) must be actively
		// in the checkout process before they can upload files, preventing arbitrary file uploads.
		$is_in_checkout_process = false;

		// Check 1: Verify cart has items.
		$cart_count = ( $wc->cart ) ? $wc->cart->get_cart_contents_count() : 0;
		if ( $wc->cart && $cart_count > 0 ) {
			$is_in_checkout_process = true;
		}

		// Check 2: Verify WooCommerce session exists with customer data.
		if ( ! $is_in_checkout_process && $wc->session ) {
			$customer = $wc->session->get( 'customer' );
			// Customer data exists and has at least one field populated.
			if ( ! empty( $customer ) && is_array( $customer ) && count( array_filter( $customer ) ) > 0 ) {
				$is_in_checkout_process = true;
			}
		}

		// Check 3: For logged-in users, check if they have WooCommerce customer role or cart session.
		if ( ! $is_in_checkout_process && is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			// Allow WooCommerce customers, shop managers, and administrators.
			if ( in_array( 'customer', $current_user->roles, true ) ||
				in_array( 'shop_manager', $current_user->roles, true ) ||
				in_array( 'administrator', $current_user->roles, true ) ) {
				// Verify they have an active WooCommerce session.
				if ( $wc->session && $wc->session->get_customer_id() ) {
					$is_in_checkout_process = true;
				}
			}
		}

		if ( ! $is_in_checkout_process ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Please start checkout process before uploading files.', 'woocommerce-checkout-manager' ) ) );
		}

		// It cannot be wp_unslash becouse it has images paths.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$files = wc_clean( $_FILES['wooccm_checkout_attachment_upload'] );

		if ( empty( $files ) ) {
			wc_add_notice( esc_html__( 'No uploads were recognized. Files were not uploaded.', 'woocommerce-checkout-manager' ), 'error' );
			wp_send_json_error( array( 'message' => esc_html__( 'No uploads were recognized. Files were not uploaded.', 'woocommerce-checkout-manager' ) ) );
		}

		$attachment_ids = $this->process_uploads( $files, 'wooccm_checkout_attachment_upload' );

		if ( count( $attachment_ids ) ) {
			wp_send_json_success( $attachment_ids );
		}

		wc_add_notice( esc_html__( 'Unknown error.', 'woocommerce-checkout-manager' ), 'error' );
		wp_send_json_error( array( 'message' => esc_html__( 'Unknown error.', 'woocommerce-checkout-manager' ) ) );
	}

	public function update_attachment_ids( $order_id = 0 ) {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$checkout = WC()->checkout->get_checkout_fields();

		if ( count( $checkout ) ) {

			foreach ( $checkout as $field_type => $fields ) {

				foreach ( $fields as $key => $field ) {

					if ( isset( $field['type'] ) && 'file' === $field['type'] ) {

						$order = wc_get_order( $order_id );
						$key   = sprintf( '_%s', $field['key'] );

						$attachments = $order->get_meta( $key, true );

						if ( $attachments ) {

							$attachments = (array) explode( ',', $attachments );

							if ( $attachments ) {

								foreach ( $attachments as $image_id ) {

									wp_update_post(
										array(
											'ID'          => $image_id,
											'post_parent' => $order_id,
										)
									);

									wp_update_attachment_metadata( $image_id, wp_generate_attachment_metadata( $image_id, get_attached_file( $image_id ) ) );
								}
							}
						}
					}
				}
			}
		}
	}
}
