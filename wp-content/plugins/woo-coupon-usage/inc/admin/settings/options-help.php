<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_help( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

<style>
@media screen and (max-width: 782px) {
  #help-area {
    width: 100%;
    float: none;
    padding-right: 0px;
  }
  #help-area-videos {
    width: 100%;
    float: none;
  }
  #help-area-videos h1 {
    margin-top: 40px;
  }
}
@media screen and (min-width: 782px) and (max-width: 1240px) {
  #help-area {
    width: calc(55% - 40px);
    float: left;
    padding-right: 40px;
  }
  #help-area-videos {
    width: 45%;
    float: right;
  }
}
@media screen and (min-width: 1240px) {
  #help-area {
    width: calc(75% - 40px);
    float: left;
    padding-right: 40px;
  }
  #help-area-videos {
    width: 25%;
    float: right;
  }
}
</style>

	<div id="help-area" class="help-area">

  <h1><?php echo esc_html__( 'Support', 'woo-coupon-usage' ); ?></h1>

  <hr/>

  <?php echo esc_html__( 'Need help? Have a suggestion?', 'woo-coupon-usage' ); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage-contact')); ?>"><?php echo esc_html__( "Create a new support ticket", "woo-coupon-usage" ); ?> <span class='fas fa-arrow-circle-right'></span></a>

  <br/><br/>

  <div id="couponaffiliates-support-widget" style="margin-top: 15px; padding: 10px 20px 10px 20px; background: #fff; border: 1px solid #ddd; border-radius: 5px;">
      <h2><?php echo esc_html__( 'Support Documentation', 'woo-coupon-usage' ); ?></h2>
      <p style="font-size: 16px; margin-bottom: 10px;"><?php echo esc_html__( 'Search our documentation for help with common questions and issues.', 'woo-coupon-usage' ); ?></p>
      <form id="couponaffiliates-docs-search-form" autocomplete="off" style="margin-bottom: 20px;">
          <input class="docs-search-input"
          placeholder="Search documentation..."
          style="width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
      </form>
      <br/>
      <a href="https://couponaffiliates.com/docs/?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=documentation"
      target="_blank" style="text-decoration: none; color: #0073aa; margin-top: 10px; display: inline-block;">
          <strong style="font-size: 12px !important;"><?php echo esc_html__( 'View All Documentation', 'woo-coupon-usage' ); ?></strong> <span class="dashicons dashicons-external"
          style="font-size: 12px; margin-top: 4px; height: 12px; width: 12px;"></span>
      </a>
      
      <div id="docs-search-results" style="margin-top: 20px;"></div>

      <br/>
      
      <!-- Default Categories Display -->
      <div id="docs-categories">

  <?php
  $sections = array(
      array(
          'title' => esc_html__( 'Setup', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Setup Guide – How to get started!', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/setup-guide-free/'),
              array('text' => esc_html__( 'How to assign affiliate users to coupons', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-do-i-assign-users-to-coupons/'),
              array('text' => esc_html__( 'Shortcodes', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/shortcodes/'),
              array('text' => esc_html__( 'How do I get support?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-do-i-get-support/'),
          )
      ),
      array(
          'title' => esc_html__( 'Commission + Payouts', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Flexible Commission Settings', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/flexible-commission-settings/'),
              array('text' => esc_html__( 'Set custom commission per product.', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/custom-product-level-commission/'),
              array('text' => esc_html__( 'Set custom commission per affiliate / coupon.', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/custom-commission-per-affiliate-coupon/'),
              array('text' => esc_html__( 'Set a custom referrer for orders.', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/custom-referrer-for-orders/'),
              array('text' => esc_html__( '(PRO) Commission Tracking and Payouts', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/commission-tracking-and-payouts/'),
              array('text' => esc_html__( '(PRO) Invoices', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-invoices/'),
              array('text' => esc_html__( '(PRO) Statements', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-statements/'),
              array('text' => esc_html__( '(PRO) Scheduled Payout Requests', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-scheduled-payouts/'),
              array('text' => esc_html__( '(PRO) Automatic Payouts', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-automatic-payouts/'),
              array('text' => esc_html__( '(PRO) Payouts – How to Pay Affiliates', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-payouts/'),
              array('text' => esc_html__( '(PRO) Lifetime Commission', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-lifetime-commission/'),
              array('text' => esc_html__( '(PRO) User Registrations as Lifetime Referral', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/track-user-registrations-as-lifetime-referral/'),
              array('text' => esc_html__( '(PRO) User-Role Specific Payout Methods', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-user-role-specific-payout-methods/')
          )
      ),
      array(
          'title' => esc_html__( 'Affiliate Dashboard', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Affiliate Dashboard Statistics', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/affiliate-dashboard-statistics/'),
              array('text' => esc_html__( 'Recent Orders Table', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/recent-orders-table/'),
              array('text' => esc_html__( '(PRO) Monthly Summary Table', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-monthly-summary-table/'),
              array('text' => esc_html__( '(PRO) Export to Excel', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/export-to-excel/'),
              array('text' => esc_html__( '(PRO) Line Graphs', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/line-graphs/'),
              array('text' => esc_html__( '(PRO) Custom Tabs', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-custom-tabs/')
          )
      ),
      array(
          'title' => esc_html__( 'Referral Links / URLs', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Referral URLs – Overview', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/referral-urls/'),
              array('text' => esc_html__( 'Referral URL Click History (Visits)', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/referral-url-clicks/'),
              array('text' => esc_html__( '(PRO) Referral URL Campaigns', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-campaigns/'),
              array('text' => esc_html__( '(PRO) Creatives', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-creatives/'),
              array('text' => esc_html__( '(PRO) Affiliate Landing Pages', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-affiliate-landing-pages/'),
              array('text' => esc_html__( '(PRO) Social Sharing for Referral URLs', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-social-sharing/'),
              array('text' => esc_html__( '(PRO) Short URL Generator for Referral URLs', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-short-url/'),
              array('text' => esc_html__( '(PRO) QR Code Generator for Referral URLs', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-qr-codes/'),
              array('text' => esc_html__( '(PRO) Direct Link Tracking', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-direct-link-tracking/'),
              array('text' => esc_html__( 'Tracking URL Conversions Without Coupon', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/tracking-conversions-via-referral-url-without-coupons/')
          )
      ),
      array(
          'title' => esc_html__( 'Affiliate Registration', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Affiliate Registration – Overview', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/affiliate-registration/'),
              array('text' => esc_html__( 'Create Affiliate Registrations Manually', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/manual-affiliate-registrations/'),
              array('text' => esc_html__( 'Template Coupon Code', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/template-coupon-code/'),
              array('text' => esc_html__( 'Registration Form CAPTCHA (Spam Prevention)', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/registration-captcha/'),
              array('text' => esc_html__( '(PRO) Dynamic Coupon Generation', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/auto-coupon-generation/'),
              array('text' => esc_html__( '(PRO) Multiple Template Coupons', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-multiple-template-coupons/'),
              array('text' => esc_html__( '(PRO) Custom Fields for Affiliate Registration', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/custom-fields-affiliate-registration/'),
              array('text' => esc_html__( '(PRO) Auto Affiliate Registration for New Users', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/auto-affiliate-registration-new-users/'),
              array('text' => esc_html__( '(PRO) Affiliate Registration on Checkout', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/affiliate-registration-on-checkout/')
          )
      ),
      array(
          'title' => esc_html__( 'Reporting', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Admin Reports & Analytics', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/admin-reports-analytics/'),
              array('text' => esc_html__( '(PRO) Affiliate Email Reports', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-affiliate-reports/')
          )
      ),
      array(
          'title' => esc_html__( 'Multi-Level Affiliates', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( '(PRO) Multi-Level Affiliates – Overview', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-multi-level-affiliates/'),
              array('text' => esc_html__( '(PRO) MLA – How to edit a user’s parents', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-mla-edit-users-parents/'),
              array('text' => esc_html__( '(PRO) MLA – How to edit the MLA “unpaid commission” for a parent', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-mla-edit-commission/'),
              array('text' => esc_html__( '(PRO) MLA – Make MLA invite links a normal referral link.', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-mla-mla-invite-normal-referral-link/'),
              array('text' => esc_html__( '(PRO) MLA – Make MLA Dashboard “Invite Only”', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-mla-invite-only/'),
              array('text' => esc_html__( '(PRO) MLA – Auto-assign referred customers as an MLA sub-affiliate.', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-mla-auto-assign-referred-customers-sub-affiliate/')
          )
      ),
      array(
          'title' => esc_html__( 'Integrations', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( '(PRO) Automated Conversion Rates', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-automated-conversion-rates/'),
              array('text' => esc_html__( 'WooCommerce – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/woocommerce-integration/'),
              array('text' => esc_html__( 'LifterLMS – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/lifterlms-integration/'),
              array('text' => esc_html__( 'LearnDash – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/learndash-integration/'),
              array('text' => esc_html__( 'TutorLMS – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/tutorlms-integration/'),
              array('text' => esc_html__( 'MemberPress – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/memberpress-integration/'),
              array('text' => esc_html__( 'Paid Memberships Pro – Integration Guide', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/paid-memberships-pro-integration/'),
              array('text' => esc_html__( '(PRO) Store Credit Payouts – Integrations', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/pro-store-credit-integrations/')
          )
      ),
      array(
          'title' => esc_html__( 'Other Features', 'woo-coupon-usage' ),
          'links' => array(
              array('text' => esc_html__( 'Affiliate Fraud Prevention Features', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/affiliate-fraud-prevention/'),
              array('text' => esc_html__( 'Email Notifications', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/email-notifications/'),
              array('text' => esc_html__( 'Multi-Currency Support &amp; Setup', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/multi-currency-support/'),
              array('text' => esc_html__( 'Admin – List of all ‘Affiliate Dashboard’ Links', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/admin-affiliates-dashboard-urls-list/'),
              array('text' => esc_html__( 'Admin – Assign affiliate users to coupons', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/assign-users-to-coupons/'),
              array('text' => esc_html__( 'Limit coupons to new customers only (first order)', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/new-customers-only/'),
              array('text' => esc_html__( 'Subscriptions (Recurring Referrals)', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/subscriptions/'),
              array('text' => esc_html__( 'Tax/VAT Calculations', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/tax-calculations/')
          )
      ),
      array(
        'title' => esc_html__( 'General Questions', 'woo-coupon-usage' ),
        'links' => array(
            array('text' => esc_html__( 'Using One License for Live & Staging Sites', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/license-utilization/'),
            array('text' => esc_html__( 'How do I manually connect an affiliate to an existing customer as a lifetime affiliate?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/manually-connect-affiliate-to-existing-customer-lifetime-affiliate/'),
            array('text' => esc_html__( 'How to fix “ERROR: Failed to load ajax request.”', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/error-ajax-request/'),
            array('text' => esc_html__( 'How do I only show “completed” orders on affiliate dashboard?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-do-i-only-show-completed-orders-on-affiliate-dashboard/'),
            array('text' => esc_html__( 'How do I change commission rates for new orders only?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/change-commission-rates-for-new-orders-only/'),
            array('text' => esc_html__( 'My PRO features and settings are not showing. How do I fix this?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/my-pro-features-and-settings-are-not-showing-how-do-i-fix-this/'),
            array('text' => esc_html__( 'My email notifications are not working. How do I fix this?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/my-email-notifications-are-not-working-how-do-i-fix-this/'),
            array('text' => esc_html__( 'The stats/content isn’t loading on my dashboard. How can I fix this?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/stats-content-not-loading/'),
            array('text' => esc_html__( 'How do I apply custom tax adjustments to stats and commission?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/custom-tax-adjustments/'),
            array('text' => esc_html__( 'How do I include or exclude tax/VAT from stats and commission?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/include-exclude-tax-vat-from-stats/'),
            array('text' => esc_html__( 'What happens when an order is refunded or partially refunded?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/order-refunded/'),
            array('text' => esc_html__( 'Are shipping costs excluded from stats and calculations?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/shipping-costs-excluded-from-stats/'),
            array('text' => esc_html__( 'Can I assign affiliate users to multiple coupons?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/assign-users-multiple-coupons/'),
            array('text' => esc_html__( 'How to use the Coupon Usage Tracker plugin?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-use-the-coupon-usage-tracker-plugin/'),
            array('text' => esc_html__( 'How to track coupon usage in WooCommerce?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-track-coupon-usage-in-woocommerce/'),
            array('text' => esc_html__( 'How to exclude specific products from commission?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-exclude-specific-products-from-commission/'),
            array('text' => esc_html__( 'How to track affiliate sales and commissions in WooCommerce?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-track-affiliate-sales-and-commissions-in-woocommerce/'),
            array('text' => esc_html__( 'How to customize the affiliate registration form?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-customize-the-affiliate-registration-form/'),
            array('text' => esc_html__( 'How to set up tiered affiliate commissions?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-set-up-tiered-affiliate-commissions/'),
            array('text' => esc_html__( 'How to export affiliate reports in WooCommerce?', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/how-to-export-affiliate-reports-in-woocommerce/')
        )
      ),
      array(
        'title' => esc_html__( 'Admin Tools', 'woo-coupon-usage' ),
        'links' => array(
          array('text' => esc_html__( 'Bulk Edit: Coupon Settings', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/bulk-edit-coupon-settings/'),
          array('text' => esc_html__( 'Bulk Edit: Product Settings', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/bulk-edit-product-settings/'),
          array('text' => esc_html__( 'Bulk Assign: Coupons to Orders', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/bulk-assign-coupons-to-orders/'),
          array('text' => esc_html__( 'Bulk Create: Affiliate Coupons', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/bulk-create-affiliate-coupons/'),
          array('text' => esc_html__( 'Import/Export Custom Tables', 'woo-coupon-usage' ), 'url' => 'https://couponaffiliates.com/docs/import-export-custom-tables/')
        )
      )
  );
  ?>

  <style>
  .helpaccordion {
    border: 1px solid #ccc;
    margin-bottom: 20px;
  }

  .helpaccordion-header {
    font-size: 20px;
    background-color: #f9f9f9;
    padding: 10px;
    cursor: pointer;
  }

  .helpaccordion-content {
    display: none;
    padding: 10px 20px;
  }

  .helpaccordion.active .helpaccordion-content {
    display: block;
  }
  </style>

  <?php foreach ($sections as $index => $section) { ?>
    <div class="helpaccordion">
      <div class="helpaccordion-header"><?php echo esc_html($section['title']); ?></div>
      <div class="helpaccordion-content">
        <ul>
          <?php foreach ($section['links'] as $link) { ?>
            <li style="font-size: 18px; margin: 20px 0; display: block;">
              <a href="<?php echo esc_url($link['url']); ?>?utm_campaign=plugin&utm_source=dashboard-link&utm_medium=documentation" target="_blank"><?php echo esc_html( $link['text'] ); ?></a>
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

  <script>
    const helpaccordions = document.querySelectorAll('.helpaccordion');

    helpaccordions.forEach((helpaccordion) => {
      const header = helpaccordion.querySelector('.helpaccordion-header');
      const content = helpaccordion.querySelector('.helpaccordion-content');

      header.addEventListener('click', () => {
        helpaccordion.classList.toggle('active');

        if (helpaccordion.classList.contains('active')) {
          content.style.display = 'block';
        } else {
          content.style.display = 'none';
        }
      });
    });
  </script>

	</div>

  </div>
  </div>

  <div id="help-area-videos" class="help-area-videos">

    <h1>Videos</h1>

    <hr/>

  <h2><?php echo esc_html__( 'Setup Guide', 'woo-coupon-usage' ); ?></h2>
  <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/705963280?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
    
    <br/><hr/>

  <h2><?php echo esc_html__( 'Registration System', 'woo-coupon-usage' ); ?></h2>
  <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/713487822?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
    
    <br/><hr/>

  <h2><?php echo esc_html__( 'Payouts (PRO)', 'woo-coupon-usage' ); ?></h2>
  <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/837140385?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
    
      <br/><hr/>

  <h2><?php echo esc_html__( 'Multi-Level Affiliates (PRO)', 'woo-coupon-usage' ); ?></h2>
  <?php echo wcusage_admin_vimeo_embed( 'https://player.vimeo.com/video/705963220?badge=0&autopause=0&player_id=0&app_id=58479/embed' ); ?>
    
  </div>

 <?php
}

// Enqueue scripts and localize AJAX data
add_action('admin_enqueue_scripts', 'couponaffiliates_enqueue_admin_scripts');
function couponaffiliates_enqueue_admin_scripts($hook) {

  wp_enqueue_script('jquery');
  
  // Add custom CSS
  $css = "
      .docs-result-box {
          background: #f9f9f9;
          border: 1px solid #ddd;
          border-radius: 5px;
          padding: 15px;
          margin-bottom: 15px;
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          transition: transform 0.2s;
      }
      .docs-result-box:hover {
          transform: translateY(-2px);
          box-shadow: 0 3px 6px rgba(0,0,0,0.15);
      }
      .docs-result-box h4 {
          margin: 0 0 10px 0;
          color: #0073aa;
      }
      .docs-result-box p {
          margin: 0;
          color: #555;
          font-size: 14px;
      }
      .docs-result-box a {
          text-decoration: none;
      }
  ";
  wp_add_inline_style('wp-admin', $css);

  // Inline JavaScript
  $nonce = wp_create_nonce('couponaffiliates_docs_search');
  $ajax_url = admin_url('admin-ajax.php');
  $script = "
      jQuery(document).ready(function($) {
          var searchTimeout;
          $('.docs-search-input').on('keyup', function() {
              clearTimeout(searchTimeout);
              var query = $(this).val();
              if (query.length < 2) {
                  $('#docs-search-results').html('');
                  $('#docs-categories').show();
                  return;
              }
              $('#docs-search-results').html('<p>Loading...</p>');
              $('#docs-categories').hide();
              searchTimeout = setTimeout(function() {
                  $.ajax({
                      url: '{$ajax_url}',
                      type: 'POST',
                      data: {
                          action: 'couponaffiliates_search_docs',
                          nonce: '{$nonce}',
                          query: query
                      },
                      success: function(response) {
                          if (response.success) {
                              $('#docs-search-results').html(response.data.html);
                          } else {
                              $('#docs-search-results').html('<p>No results found.</p>');
                          }
                      },
                      error: function() {
                          $('#docs-search-results').html('<p>Error fetching docs. Please try again.</p>');
                      }
                  });
              }, 300);
          });
      });
  ";
  wp_add_inline_script('jquery', $script);
}

// AJAX handler to fetch and return docs
add_action('wp_ajax_couponaffiliates_search_docs', 'couponaffiliates_search_docs_callback');
function couponaffiliates_search_docs_callback() {
  check_ajax_referer('couponaffiliates_docs_search', 'nonce');

  $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

  $response = wp_remote_get(
      add_query_arg(
          [
              'search' => $query,
              'per_page' => 10,
          ],
          'https://couponaffiliates.com/wp-json/wp/v2/docs'
      ),
      ['timeout' => 15]
  );

  if (is_wp_error($response)) {
      wp_send_json_error(['message' => 'Failed to fetch docs.']);
  }

  $docs = json_decode(wp_remote_retrieve_body($response), true);
  if (empty($docs)) {
      wp_send_json_success(['html' => '<p>No results found.</p>']);
  }

  // Build fancy boxed HTML output
  $html = '';
  foreach ($docs as $doc) {
      $title = esc_html($doc['title']['rendered']);
      $link = esc_url($doc['link']);
      $excerpt = wp_trim_words(wp_strip_all_tags($doc['excerpt']['rendered']), 20, '...');
      $html .= "
          <div class='docs-result-box'>
              <a href='$link' target='_blank'>
                  <h4>$title</h4>
                  <p>$excerpt</p>
              </a>
          </div>
      ";
  }

  wp_send_json_success(['html' => $html]);
}