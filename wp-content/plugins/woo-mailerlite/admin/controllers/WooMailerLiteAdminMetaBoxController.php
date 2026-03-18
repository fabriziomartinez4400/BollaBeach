<?php

class WooMailerLiteAdminMetaBoxController extends WooMailerLiteController
{
    public function addMetaBoxes()
    {
        $screen = 'shop_order';
        if (class_exists('CustomOrdersTableController') && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()) {
            $screen = wc_get_page_screen_id( 'shop-order' );
        }
        // if hpos is enabled
        if (get_current_screen()->id == 'woocommerce_page_wc-orders') {
            $screen = 'woocommerce_page_wc-orders';
        }

        add_meta_box(
            'woo-ml-order',
            '<span class="woo-ml-icon"></span>&nbsp;&nbsp;' . __('MailerLite', 'woo-mailerlite'),
            [$this, 'metaBoxOutput'],
            $screen,
            'side',
            'low'
        );
    }

    public function metaBoxOutput($post)
    {
        wp_nonce_field(basename(__FILE__), 'woo_ml_order_meta_box_nonce');

        $order = ($post instanceof WP_Post) ? wc_get_order($post->ID) : $post;

        $orderId = $order->get_id();
        $iconYes = '<span class="dashicons dashicons-yes" style="color: #00A153;right: 7px;position: absolute;"></span>';
        $iconNo  = '<span class="dashicons dashicons-no-alt" style="color: #a00;right: 7px;position: absolute;"></span>';
        $iconNA = '<span class="dashicons dashicons-minus" style="color: #ffb900; right: 7px; position: absolute;"></span>';
        ?>
        <?php $subscribe = $order->get_meta('_woo_ml_subscribe'); ?>
        <p>
            <?php echo _e('Signed up for emails',
                    'woo-mailerlite') . wc_help_tip("Customer ticked the Subscribe box at the checkout stage to receive newsletters."); ?><?php echo ($subscribe) ? $iconYes : $iconNo; ?>
        </p>
        <?php $subscribed = $order->get_meta('_woo_ml_subscribed'); ?>
        <?php $already_subscribed = $order->get_meta('_woo_ml_already_subscribed'); ?>
        <?php $subscriber_updated = $order->get_meta('_woo_ml_subscriber_updated'); ?>
        <p>
            <?php echo _e('Subscribed to email list',
                    'woo-mailerlite') . wc_help_tip("Customer was successfully added to the subscriber list in MailerLite."); ?><?php echo ($subscribed) ? $iconYes : ($already_subscribed && $subscriber_updated ? $iconNA : $iconNo); ?>
        </p>
        <p>
            <?php echo _e('Previously subscribed',
                    'woo-mailerlite') . wc_help_tip("Customer was already an existing subscriber."); ?><?php echo ($already_subscribed) ? $iconYes : ($subscribe && $subscribed ? $iconNA : $iconNo); ?>
        </p>
        <p>
            <?php echo _e('Updated subscriber data',
                    'woo-mailerlite') . wc_help_tip("Checkout data including purchases and contact information was successfully updated in MailerLite."); ?><?php echo ($subscriber_updated) ? $iconYes : ($subscribe && $subscribed ? $iconNA : $iconNo); ?>
        </p>
        <?php $order_data_submitted = $order->get_meta('_woo_ml_order_data_submitted'); ?>
        <p>
            <?php echo _e('Order data submitted',
                    'woo-mailerlite') . wc_help_tip("Order data is uploaded to MailerLite once the payment is processed."); ?><?php echo ($order_data_submitted) ? $iconYes : $iconNo; ?>
        </p>
        <?php $order_tracking_completed = $order->get_meta('_woo_ml_order_tracked'); ?>
        <p>
            <?php echo _e('Order tracking completed',
                    'woo-mailerlite') . wc_help_tip("All stages of the tracking process on this order have been completed."); ?><?php echo ($order_tracking_completed) ? $iconYes : $iconNo; ?>
        </p>
        <?php
    }

    /**
     * Saves the custom meta input
     */
    function woo_ml_order_meta_box_save($post_id)
    {

        // Checks save status
        $is_autosave    = wp_is_post_autosave($post_id);
        $is_revision    = wp_is_post_revision($post_id);
        $is_valid_nonce = (isset($_POST['woo_ml_order_meta_box_nonce']) && wp_verify_nonce($_POST['woo_ml_order_meta_box_nonce'],
                basename(__FILE__))) ? 'true' : 'false';
    }


}