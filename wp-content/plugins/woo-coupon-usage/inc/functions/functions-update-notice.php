<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Update option to show notice when plugin updated to suggest clear cache
 *
 */
 function wcusage_updated_cache_dismissed() {

     $current_notice_version = "5.2.1"; // Change this when should show the notice again for sites that update the plugin.

     $notice_dismissed_version = get_option( 'wcusage_update_notice_dismissed_version' );

     if($notice_dismissed_version == "") {

       // If new install don't show notice and update values to current
       update_option( 'wcusage_update_notice_dismissed', '1' );
       update_option( 'wcusage_update_notice_dismissed_version', $current_notice_version );

     } else {

       // If dismissed version not equal to new version, then reset option to reshow notice and save new version
       if($notice_dismissed_version != $current_notice_version) {
         update_option( 'wcusage_update_notice_dismissed', '' );
         update_option( 'wcusage_update_notice_dismissed_version', $current_notice_version );
       }

    }

    // If pressed dismiss button then update option to hide it
    if ( isset( $_POST['wcusage-update-notice-dismissed'] ) ) {
      update_option( 'wcusage_update_notice_dismissed', '1' );
    }

    //echo "<center>" . get_option( 'wcusage_update_notice_dismissed' ) . "-" . get_option( 'wcusage_update_notice_dismissed_version' ) . "</center>";

 }
 add_action( 'admin_init', 'wcusage_updated_cache_dismissed' );

/**
  * Get notice when plugin updated to suggest clear cache
  *
  */
function wcusage_updated_cache_notice() {
    $notice_dismissed = get_option( 'wcusage_update_notice_dismissed' );
    if ( !$notice_dismissed ) {
      ?>

      <div class="notice notice-success"><form style="all: unset !important;" action="" method="post">
      <input type="text" id="wcusage-update-notice-dismissed" name="wcusage-update-notice-dismissed" value="1" style="display: none;">
      <p>
      Coupon Affiliates <?php echo esc_html__( "was updated", "woo-coupon-usage" ); ?>! (<a href="https://roadmap.couponaffiliates.com/updates" target="_blank"><?php echo esc_html__( "View Changelog", "woo-coupon-usage" ); ?></a>) - <strong><?php echo esc_html__( "Please CLEAR YOUR CACHE to ensure frontend changes are applied.", "woo-coupon-usage" ); ?></strong>
      <input type="submit" value="<?php echo esc_html__( "Dismiss Notice", "woo-coupon-usage" ); ?>" style="cursor: pointer;">
      </p>
      </form>
      </div>

      <?php
    }
}
add_action( 'admin_notices', 'wcusage_updated_cache_notice' );
