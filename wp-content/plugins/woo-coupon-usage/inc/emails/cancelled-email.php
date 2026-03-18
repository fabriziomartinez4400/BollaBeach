<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * If refunded, cancelled, or failed, send email to affiliate
 */
function wcusage_send_order_refund_email( $order_id, $old_status, $new_status ) {

    $order = wc_get_order( $order_id );

    // If refunded, cancelled, or failed and old status was completed
    if( $new_status != 'refunded' && $new_status != 'cancelled' && $new_status != 'failed' ) {
        return;
    }
    // If old status was refunded, cancelled, failed, or completed
    if( $old_status != 'completed' ) {
        return;
    }

    $lifetimeaffiliate = wcusage_order_meta($order_id,'lifetime_affiliate_coupon_referrer');
    $affiliatereferrer = wcusage_order_meta($order_id,'wcusage_referrer_coupon');

    if($lifetimeaffiliate) {
        
        wcusage_order_refund_email($order_id, $lifetimeaffiliate, $new_status);

    } elseif($affiliatereferrer) {

        wcusage_order_refund_email($order_id, $affiliatereferrer, $new_status);

    } else {

        foreach( $order->get_coupon_codes() as $coupon_code ) {

        wcusage_order_refund_email($order_id, $coupon_code, $new_status);

        }

    }

}
add_action( 'woocommerce_order_status_changed', 'wcusage_send_order_refund_email', 10, 3 );

/*
* Send email to affiliate
*/
function wcusage_order_refund_email($order_id, $coupon_code, $status) {

    $coupon = new WC_Coupon($coupon_code);
    $id = $coupon->get_id();

    $valueuser = get_post_meta( $id, "wcu_select_coupon_user", true );

    $calculateorder = wcusage_calculate_order_data( $order_id, $coupon_code, 0, 1 );
    $totalcommission = isset($calculateorder['totalcommission']) ? $calculateorder['totalcommission'] : 0;

    // Fallback to get saved commission if 0 (e.g. if order status changed to cancelled/refunded)
    if( empty($totalcommission) ) {
        $totalcommission = wcusage_order_meta( $order_id, 'wcusage_total_commission', true );
    }

    // Fallback to wcusage_stats if still empty
    if( empty($totalcommission) ) {
        $stats = wcusage_order_meta( $order_id, 'wcusage_stats', true );
        if( is_array($stats) && isset($stats['commission']) ) {
            $totalcommission = $stats['commission'];
        }
    }

    $valuecommission = wcusage_format_price( number_format((float)$totalcommission, 2, '.', '') );

    $wcu_enable_notifications = get_post_meta( $id, 'wcu_enable_notifications', true );
    if($wcu_enable_notifications == "") { $wcu_enable_notifications = 1; }

    if ( wcu_fs()->can_use_premium_code() ) {
        $wcu_notifications_extra = sanitize_text_field( get_post_meta( $id, 'wcu_notifications_extra', true ) );
    } else {
        $wcu_notifications_extra = "";
    }

    if( $wcu_enable_notifications && $valueuser && $order_id ) {

        $order = wc_get_order( $order_id );

    }

    $user_info = get_userdata($valueuser);

    $user_name = $user_info->display_name;
    $user_email = $user_info->user_email;

    $from = wcusage_get_from_email();

    $wcusage_cancel_email_subject = wcusage_get_setting_value('wcusage_field_cancel_email_subject', 'Your referred order #{id} has been cancelled.');

    $wcusage_cancel_email_message = html_entity_decode( wcusage_get_setting_value('wcusage_field_cancel_email_message', '') );

    if(!$wcusage_cancel_email_message) {

        $wcusage_cancel_email_message = "Hi {name},
        <br/><br/>
        We're sorry to inform you that one of your referred orders has been {status}.
        <br/><br/>
        Order ID: {id}
        <br/><br/>
        The following commission has been removed from your account: {commission}
        <br/><br/>
        Thank you for your continued support.";

    }

    $wcusage_cancel_email_subject = str_replace("{name}", $user_name, $wcusage_cancel_email_subject);
    $wcusage_cancel_email_subject = str_replace("{coupon}", $coupon_code, $wcusage_cancel_email_subject);
    $wcusage_cancel_email_subject = str_replace("{commission}", $valuecommission, $wcusage_cancel_email_subject);
    $wcusage_cancel_email_subject = str_replace("{id}", $order_id, $wcusage_cancel_email_subject);
    $wcusage_cancel_email_subject = str_replace("{status}", $status, $wcusage_cancel_email_subject);
    $wcusage_cancel_email_subject = strip_tags($wcusage_cancel_email_subject);

    $wcusage_cancel_email_message = str_replace("{name}", $user_name, $wcusage_cancel_email_message);
    $wcusage_cancel_email_message = str_replace("{coupon}", $coupon_code, $wcusage_cancel_email_message);
    $wcusage_cancel_email_message = str_replace("{commission}", $valuecommission, $wcusage_cancel_email_message);
    $wcusage_cancel_email_message = str_replace("{id}", $order_id, $wcusage_cancel_email_message);
    $wcusage_cancel_email_message = str_replace("{status}", $status, $wcusage_cancel_email_message);

    $to = $user_email . "," . $wcu_notifications_extra;
    $subject = $wcusage_cancel_email_subject;
    $body = $wcusage_cancel_email_message;
    $headers = array( 'Content-Type: text/html; charset=UTF-8;', $from );

    // Get woocommerce mailer from instance
    $mailer = WC()->mailer();

    // Wrap message using woocommerce html email template
    $wrapped_message = $mailer->wrap_message($subject, $body);

    // Create new WC_Email instance
    $wc_email = new WC_Email;

    // Style the wrapped message with woocommerce inline styles
    $html_message = $wc_email->style_inline($wrapped_message);

    wp_mail( $to, $subject, $html_message, $headers );

}