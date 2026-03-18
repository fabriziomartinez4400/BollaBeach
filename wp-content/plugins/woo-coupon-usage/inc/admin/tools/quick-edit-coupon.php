<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wcusage_render_quick_edit_row' ) ) {
    /**
     * Render the Quick Edit row for a coupon ID.
     * Shared between Coupons list and Affiliate view to avoid duplication.
     *
     * @param int $coupon_id
     * @param int $colspan
     */
    function wcusage_render_quick_edit_row( $coupon_id, $colspan ) {
        try {
            $coupon = new WC_Coupon( $coupon_id );
        } catch ( Exception $e ) {
            return; // Invalid coupon
        }

        $currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
        $info             = wcusage_get_coupon_info_by_id( $coupon_id );
        $coupon_user_id   = is_array( $info ) && isset( $info[1] ) ? $info[1] : 0;
        $user_info        = $coupon_user_id ? get_userdata( $coupon_user_id ) : false;

        ?>
        <tr class="quick-edit-row" id="quick-edit-<?php echo esc_attr( $coupon_id ); ?>" style="display: none;">
            <td colspan="<?php echo intval( $colspan ); ?>">
                <div class="quick-edit-form">
                    <div class="quick-edit-fields" data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>">
                        <div class="section-left">
                            <h3 class="section-heading"><?php esc_html_e( 'Coupon Details', 'woo-coupon-usage' ); ?></h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="coupon_code_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Coupon Code', 'woo-coupon-usage' ); ?></label>
                                    <input type="text" id="coupon_code_<?php echo esc_attr( $coupon_id ); ?>" value="<?php echo esc_attr( $coupon->get_code() ); ?>">
                                </div>
                                <div class="form-field">
                                    <label for="coupon_description_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Description', 'woo-coupon-usage' ); ?></label>
                                    <input type="text" id="coupon_description_<?php echo esc_attr( $coupon_id ); ?>" value="<?php echo esc_attr( $coupon->get_description() ); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="discount_type_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Discount Type', 'woo-coupon-usage' ); ?></label>
                                    <select id="discount_type_<?php echo esc_attr( $coupon_id ); ?>">
                                        <option value="fixed_cart" <?php selected( $coupon->get_discount_type(), 'fixed_cart' ); ?>><?php esc_html_e( 'Fixed cart discount', 'woo-coupon-usage' ); ?></option>
                                        <option value="percent" <?php selected( $coupon->get_discount_type(), 'percent' ); ?>><?php esc_html_e( 'Percentage discount', 'woo-coupon-usage' ); ?></option>
                                        <option value="fixed_product" <?php selected( $coupon->get_discount_type(), 'fixed_product' ); ?>><?php esc_html_e( 'Fixed product discount', 'woo-coupon-usage' ); ?></option>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="coupon_amount_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Discount Amount', 'woo-coupon-usage' ); ?></label>
                                    <input type="number" id="coupon_amount_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( $coupon->get_amount() ); ?>">
                                </div>
                            </div>
                            <h3 class="section-heading"><?php esc_html_e( 'Spend Limits', 'woo-coupon-usage' ); ?></h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="minimum_amount_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Minimum Spend', 'woo-coupon-usage' ); ?></label>
                                    <input type="number" id="minimum_amount_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( $coupon->get_minimum_amount() ); ?>">
                                </div>
                                <div class="form-field">
                                    <label for="maximum_amount_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Maximum Spend', 'woo-coupon-usage' ); ?></label>
                                    <input type="number" id="maximum_amount_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( $coupon->get_maximum_amount() ); ?>">
                                </div>
                            </div>
                            <h3 class="section-heading"><?php esc_html_e( 'Usage Limits', 'woo-coupon-usage' ); ?></h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="expiry_date_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Expiry Date', 'woo-coupon-usage' ); ?></label>
                                    <input type="date" id="expiry_date_<?php echo esc_attr( $coupon_id ); ?>" value="<?php echo $coupon->get_date_expires() ? esc_attr( $coupon->get_date_expires()->date( 'Y-m-d' ) ) : ''; ?>">
                                </div>
                                <div class="form-field">
                                    <label for="usage_limit_per_user_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Limit Per User', 'woo-coupon-usage' ); ?></label>
                                    <input type="number" id="usage_limit_per_user_<?php echo esc_attr( $coupon_id ); ?>" min="0" value="<?php echo esc_attr( $coupon->get_usage_limit_per_user() ?: '' ); ?>">
                                </div>
                            </div>
                            <h3 class="section-heading"><?php esc_html_e( 'Other Settings', 'woo-coupon-usage' ); ?></h3>
                            <div class="form-field checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="free_shipping_<?php echo esc_attr( $coupon_id ); ?>" <?php checked( $coupon->get_free_shipping() ); ?>>
                                    <?php esc_html_e( 'Free Shipping', 'woo-coupon-usage' ); ?>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="exclude_sale_items_<?php echo esc_attr( $coupon_id ); ?>" <?php checked( $coupon->get_exclude_sale_items() ); ?>>
                                    <?php esc_html_e( 'Exclude Sale Items', 'woo-coupon-usage' ); ?>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="individual_use_<?php echo esc_attr( $coupon_id ); ?>" <?php checked( $coupon->get_individual_use() ); ?>>
                                    <?php esc_html_e( 'Individual Use Only', 'woo-coupon-usage' ); ?>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="wcu_enable_first_order_only_<?php echo esc_attr( $coupon_id ); ?>" <?php checked( get_post_meta( $coupon_id, 'wcu_enable_first_order_only', true ), 'yes' ); ?>>
                                    <?php esc_html_e( 'New Customers Only', 'woo-coupon-usage' ); ?>
                                </label>
                            </div>
                        </div>
                        <div class="section-right">
                            <h3 class="section-heading"><?php esc_html_e( 'Coupon Affiliates', 'woo-coupon-usage' ); ?></h3>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="wcu_select_coupon_user_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Affiliate User', 'woo-coupon-usage' ); ?></label>
                                    <input type="text"
                                        id="wcu_select_coupon_user_<?php echo esc_attr( $coupon_id ); ?>"
                                        class="wcu-autocomplete-user"
                                        value="<?php echo $user_info ? esc_attr( $user_info->user_login ) : ''; ?>"
                                        placeholder="<?php esc_html_e( 'Search for a user...', 'woo-coupon-usage' ); ?>">
                                </div>
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_coupon_commission_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Commission (%) Per Order', 'woo-coupon-usage' ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_coupon_commission_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( get_post_meta( $coupon_id, 'wcu_text_coupon_commission', true ) ); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_coupon_commission_fixed_order_<?php echo esc_attr( $coupon_id ); ?>"><?php printf( esc_html__( 'Commission (%s) Per Order', 'woo-coupon-usage' ), esc_html( $currency_symbol ) ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_coupon_commission_fixed_order_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( get_post_meta( $coupon_id, 'wcu_text_coupon_commission_fixed_order', true ) ); ?>">
                                </div>
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_coupon_commission_fixed_product_<?php echo esc_attr( $coupon_id ); ?>"><?php printf( esc_html__( 'Commission (%s) Per Product', 'woo-coupon-usage' ), esc_html( $currency_symbol ) ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_coupon_commission_fixed_product_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( get_post_meta( $coupon_id, 'wcu_text_coupon_commission_fixed_product', true ) ); ?>">
                                </div>
                            </div>

                            <h3 class="section-heading"><?php esc_html_e( 'Commission', 'woo-coupon-usage' ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></h3>
                            <div class="form-row">
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_unpaid_commission_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Unpaid Commission', 'woo-coupon-usage' ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_unpaid_commission_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( get_post_meta( $coupon_id, 'wcu_text_unpaid_commission', true ) ); ?>">
                                </div>
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_pending_payment_commission_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Pending Payout', 'woo-coupon-usage' ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_pending_payment_commission_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( get_post_meta( $coupon_id, 'wcu_text_pending_payment_commission', true ) ); ?>">
                                </div>
                            </div>
                            <?php $wcu_processing_commission = (float) get_post_meta( $coupon_id, 'wcu_text_pending_order_commission', true ); ?>
                            <?php if ( $wcu_processing_commission > 0 ) : ?>
                            <div class="form-row">
                                <div class="form-field" <?php if ( ! wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                                    <label for="wcu_text_pending_order_commission_<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Processing Commission', 'woo-coupon-usage' ); ?><?php if ( ! wcu_fs()->can_use_premium_code() ) { ?> (PRO)<?php } ?></label>
                                    <input type="number" id="wcu_text_pending_order_commission_<?php echo esc_attr( $coupon_id ); ?>" step="0.01" value="<?php echo esc_attr( $wcu_processing_commission ); ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="submit inline-edit-save">
                        <button class="button button-primary save-quick-edit" data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>"><?php esc_html_e( 'Save Changes', 'woo-coupon-usage' ); ?></button>
                        <button class="button cancel-quick-edit"><?php esc_html_e( 'Cancel', 'woo-coupon-usage' ); ?></button>
                        <span class="spinner"></span>
                    </p>
                </div>
            </td>
        </tr>
        <?php
    }
}
