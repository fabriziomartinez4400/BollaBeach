<?php
if(!defined('ABSPATH')) {
    exit;
}

// Register the admin menu page
function affiliate_page_generator_menu() {
    $capability = function_exists( 'wcusage_get_admin_menu_capability' ) ? wcusage_get_admin_menu_capability() : 'manage_options';
    add_submenu_page(
        'wcusage_tools',
        __('Affiliate Program - Signup Promo Page Generator', 'woo-coupon-usage'),
        __('Affiliate Program - Signup Promo Page Generator', 'woo-coupon-usage'),
        $capability,
        'signup-page-generator',
        'affiliate_page_generator_admin_page'
    );
}
add_action('admin_menu', 'affiliate_page_generator_menu');

// Create the admin page content
function affiliate_page_generator_admin_page() {
    // Check user capabilities
    if ( ! wcusage_check_admin_access() ) {
        return;
    }

    // Get existing options
    $wcusage_options = get_option('wcusage_options', array());
    $existing_page_id = isset($wcusage_options['wcusage_signup_landing_page']) ? $wcusage_options['wcusage_signup_landing_page'] : 0;
    $page_exists = $existing_page_id && (get_post_status($existing_page_id) !== "trash");
    $wcusage_registration_page = wcusage_get_setting_value('wcusage_registration_page', '');
    // Check $wcusage_registration_page page exists
    if ($wcusage_registration_page) {
        $page_exists_reg = get_post_status($wcusage_registration_page);
        if(!$page_exists_reg) {
            $wcusage_registration_page = '';
        }
    }

    // Handle registration page update
    if (isset($_POST['set_registration_page']) && check_admin_referer('set_registration_page_action')) {
        $wcusage_options['wcusage_registration_page'] = $existing_page_id;
        update_option('wcusage_options', $wcusage_options);
        $wcusage_registration_page = $existing_page_id;
    }

    // If page does not exist, delete the option
    if (!$page_exists) {
        $wcusage_options = get_option('wcusage_options', array());
        $wcusage_options['wcusage_signup_landing_page'] = "";
        update_option('wcusage_options', $wcusage_options);
        $wcusage_options = get_option('wcusage_options', array());
    }

    // Get default commission rate from original logic
    $commission_structure_default = '';
    $wcusage_field_affiliate = wcusage_get_setting_value('wcusage_field_affiliate', '');
    $wcusage_field_affiliate_fixed_order = wcusage_get_setting_value('wcusage_field_affiliate_fixed_order', '');
    $wcusage_field_affiliate_fixed_product = wcusage_get_setting_value('wcusage_field_affiliate_fixed_product', '');
    if ($wcusage_field_affiliate) {
        $commission_structure_default .= $wcusage_field_affiliate . esc_html__('% on each sale referred, ', 'woo-coupon-usage');
    }
    if ($wcusage_field_affiliate_fixed_order) {
        $commission_structure_default .= wc_price($wcusage_field_affiliate_fixed_order) . esc_html__(' on each sale referred, ', 'woo-coupon-usage');
    }
    if ($wcusage_field_affiliate_fixed_product) {
        $commission_structure_default .= wc_price($wcusage_field_affiliate_fixed_product) . esc_html__(' on each product sold, ', 'woo-coupon-usage');
    }
    $commission_structure_default = rtrim($commission_structure_default, ', ');
    $commission_structure_default = strip_tags($commission_structure_default);
    $default_commission_rate = $commission_structure_default ? $commission_structure_default : '25%';

    // Get default payment methods from original logic
    $default_payment_methods = '';
    $wcusage_field_paypal_enable = wcusage_get_setting_value('wcusage_field_paypal_enable', '');
    $wcusage_field_tr_payouts_paypal_only = wcusage_get_setting_value('wcusage_field_tr_payouts_paypal_only', '');
    if($wcusage_field_paypal_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_paypal_only ? $wcusage_field_tr_payouts_paypal_only : 'PayPal';
        $default_payment_methods .= ', ';
    }
    $wcusage_field_paypal2_enable = wcusage_get_setting_value('wcusage_field_paypal2_enable', '');
    $wcusage_field_tr_payouts_paypal2_only = wcusage_get_setting_value('wcusage_field_tr_payouts_paypal2_only', '');
    if($wcusage_field_paypal2_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_paypal2_only ? $wcusage_field_tr_payouts_paypal2_only : 'PayPal';
        $default_payment_methods .= ', ';
    }
    $wcusage_field_banktransfer_enable = wcusage_get_setting_value('wcusage_field_banktransfer_enable', '');
    $wcusage_field_tr_payouts_banktransfer_only = wcusage_get_setting_value('wcusage_field_tr_payouts_banktransfer_only', '');
    if($wcusage_field_banktransfer_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_banktransfer_only ? $wcusage_field_tr_payouts_banktransfer_only : 'Bank Transfer';
        $default_payment_methods .= ', ';
    }
    $wcusage_field_paypalapi_enable = wcusage_get_setting_value('wcusage_field_paypalapi_enable', '');
    $wcusage_field_tr_payouts_paypalapi_only = wcusage_get_setting_value('wcusage_field_tr_payouts_paypalapi_only', '');
    if($wcusage_field_paypalapi_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_paypalapi_only ? $wcusage_field_tr_payouts_paypalapi_only : 'PayPal API';
        $default_payment_methods .= ', ';
    }
    $wcusage_field_stripeapi_enable = wcusage_get_setting_value('wcusage_field_stripeapi_enable', '');
    $wcusage_field_tr_payouts_stripeapi_only = wcusage_get_setting_value('wcusage_field_tr_payouts_stripeapi_only', '');
    if($wcusage_field_stripeapi_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_stripeapi_only ? $wcusage_field_tr_payouts_stripeapi_only : 'Stripe';
        $default_payment_methods .= ', ';
    }
    $wcusage_field_storecredit_enable = wcusage_get_setting_value('wcusage_field_storecredit_enable', '');
    $wcusage_field_tr_payouts_storecredit_only = wcusage_get_setting_value('wcusage_field_tr_payouts_storecredit_only', '');
    if($wcusage_field_storecredit_enable) {
        $default_payment_methods .= $wcusage_field_tr_payouts_storecredit_only ? $wcusage_field_tr_payouts_storecredit_only : 'Store Credit';
        $default_payment_methods .= ', ';
    }
    $default_payment_methods = rtrim($default_payment_methods, ', ');
    $last_comma_pos = strrpos($default_payment_methods, ',');
    if ($last_comma_pos !== false) {
        $default_payment_methods = substr_replace($default_payment_methods, ' or', $last_comma_pos, 1);
    }
    if(empty($default_payment_methods)) {
        $default_payment_methods = 'PayPal';
    }

    // Get default payment threshold from original logic
    $default_payment_threshold = wcusage_get_setting_value('wcusage_field_payout_threshold', 0);
    if($default_payment_threshold) {
        $default_payment_threshold = wc_price($default_payment_threshold);
    } else {
        $default_payment_threshold = wc_price(5.00);
    }
    $default_payment_threshold = strip_tags($default_payment_threshold);
    $default_payment_threshold = html_entity_decode($default_payment_threshold, ENT_QUOTES, 'UTF-8');

    // Get default cookie duration from original logic
    $wcusage_urls_cookie_days = wcusage_get_setting_value('wcusage_urls_cookie_days', 30);
    $default_cookie_duration = $wcusage_urls_cookie_days;

    // Default intro text from original content
    $default_intro_text = 'Share referral links with auto-applied coupons, earn 20% on each sale referred, and give your audience great discounts!';

    // Default page title and slug
    $default_page_title = 'Affiliate Program';
    $default_page_slug = 'affiliate-program';

    // Get existing page title and slug if page exists
    $existing_page_title = $page_exists ? get_the_title($existing_page_id) : $default_page_title;
    $existing_page_slug = $page_exists ? get_post_field('post_name', $existing_page_id) : $default_page_slug;

    // Check if form is submitted
    if (isset($_POST['affiliate_page_submit']) && check_admin_referer('affiliate_page_generator_action')) {
        $program_name = get_bloginfo('name');

        // Get form values or use defaults
        $commission_rate = !empty($_POST['commission_rate']) ? sanitize_text_field($_POST['commission_rate']) : $default_commission_rate;
        $payment_methods = !empty($_POST['payment_methods']) ? sanitize_text_field($_POST['payment_methods']) : $default_payment_methods;
        $payment_threshold = !empty($_POST['payment_threshold']) ? sanitize_text_field($_POST['payment_threshold']) : $default_payment_threshold;
        $cookie_duration = !empty($_POST['cookie_duration']) ? absint($_POST['cookie_duration']) : $default_cookie_duration;
        $intro_text = !empty($_POST['intro_text']) ? sanitize_textarea_field($_POST['intro_text']) : $default_intro_text;
        $page_title = !empty($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : $default_page_title;
        $page_slug = !empty($_POST['page_slug']) ? sanitize_title($_POST['page_slug']) : $default_page_slug;

// Create the page content with corrected Gutenberg blocks
$page_content = '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group"><!-- wp:cover {"dimRatio":70,"customOverlayColor":"#333333","isUserOverlayColor":true,"minHeight":300,"className":"has-background-dim-70 has-background-dim","style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}}} -->
<div class="wp-block-cover has-background-dim-70 has-background-dim" style="padding-top:40px;padding-bottom:40px;min-height:300px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim" style="background-color:#333333"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":1,"className":"has-text-color","style":{"typography":{"fontSize":"36px"},"color":{"text":"#ffffff"}}} -->
<h1 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff;font-size:36px">Join the ' . esc_html($program_name) . ' Affiliate Program</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"20px"},"color":{"text":"#ffffff"}}} -->
<p class="has-text-align-center has-text-color" style="color:#ffffff;font-size:20px">' . esc_html($intro_text) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#join">Join Now</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:40px;padding-bottom:40px"><!-- wp:paragraph -->
<p>Welcome to the ' . esc_html($program_name) . ' Affiliate Program!</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Earn commissions by sharing unique referral links that automatically apply your exclusive coupon codes to customers’ carts. When they shop using your link, they get fantastic discounts, and you earn ' . esc_html($commission_rate) . ' on each purchase - a win-win for everyone!</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We provide you with personalized referral links, detailed tracking tools, promotional materials, and a dedicated support team. Whether you’re a blogger, influencer, or affiliate marketer, our program is designed to help you succeed while offering unbeatable value to your audience.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"40px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:40px">
<!-- wp:heading {"level":2,"className":"wp-block-heading"} -->
<h2 class="wp-block-heading">Why join our affiliate program?</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Generous Commissions</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Earn a lucrative ' . esc_html($commission_rate) . ' through your coupon code and referral links, with no caps on your total earning potential.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Exclusive Coupons</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Get unique coupon codes tied to your referral links that auto-apply at checkout, exclusive to your audience for standout promotions.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Real-Time Tracking</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Track your referral link performance instantly with updates on clicks, conversions, and commissions via our intuitive dashboard.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"20px","left":"20px"},"margin":{"top":"20px"}}}} -->
<div class="wp-block-columns" style="margin-top:20px">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Timely Payments</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Receive prompt monthly payouts via ' . esc_html($payment_methods) . ' once you hit the ' . esc_html($payment_threshold) . ' threshold.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Marketing Tools</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Access free banners, email templates, and social media graphics to enhance your referral link and coupon promotions.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"benefit-box","style":{"border":{"radius":"5px","width":"1px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
<div class="wp-block-group benefit-box" style="border-width:1px;border-radius:5px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">
<!-- wp:heading {"textAlign":"center","level":3,"className":"wp-block-heading"} -->
<h3 class="wp-block-heading has-text-align-center">Dedicated Support</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Our team is here to assist with any questions or challenges, ensuring your success with our coupon affiliate program.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"25px"} -->
<div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"40px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:40px">
<!-- wp:heading {"level":2,"className":"wp-block-heading"} -->
<h2 class="wp-block-heading">How It Works</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4,"className":"wp-block-heading"} -->
<h4 class="wp-block-heading">1. Join the Program</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Sign up below to get your affiliate coupon and a referral link that auto-applies your exclusive discount.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4,"className":"wp-block-heading"} -->
<h4 class="wp-block-heading">2. Share Your Link</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Share your referral link via your blog, social media, or email. It auto-applies your coupon at checkout and tracks referrals.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4,"className":"wp-block-heading"} -->
<h4 class="wp-block-heading">3. Earn Rewards</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Earn ' . esc_html($commission_rate) . ' when customers use your coupon or link, with the referral cookie stored for ' . esc_html($cookie_duration) . ' days.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"5px"} -->
<div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"40px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-bottom:40px">
<!-- wp:heading {"level":2,"className":"wp-block-heading"} -->
<h2 class="wp-block-heading" id="join">Join Now</h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"border":{"radius":"0px","width":"0px"},"spacing":{"padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px"},"margin":{"top":"20px"}},"backgroundColor":"light-gray"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="border-width:0px;border-radius:0px;margin-top:20px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:shortcode -->
[couponaffiliates-register]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';

        // Page arguments
        $page_args = array(
            'post_title'    => $page_title,
            'post_content'  => $page_content,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => $page_slug
        );

        // Check if page already exists using stored ID
        if ($page_exists) {
            $page_args['ID'] = $existing_page_id;
            $page_id = wp_update_post($page_args);
        } else {
            $page_id = wp_insert_post($page_args);
            // Save the new page ID to wcusage_options
            $wcusage_options['wcusage_signup_landing_page'] = $page_id;
            update_option('wcusage_options', $wcusage_options);
        }
    }
    ?>

    <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

    <?php do_action('wcusage_hook_dashboard_page_header', ''); ?>

    <div class="wrap affiliate-page-generator-wrap">
        
        <h1><?php esc_html_e('Affiliate Program - Signup Promo Page Generator', 'woo-coupon-usage'); ?></h1>

        <p style="font-size:10px; color:rgb(150, 150, 150);">(Currently only available in English.)</p>

        <p>
            <?php esc_html_e('This tool help you create a simple but effective custom signup promo page for your affiliate program.', 'woo-coupon-usage'); ?> <a href="https://www.couponaffiliates.com/signup-promo-page/" target="_blank"><?php esc_html_e('Learn more.', 'woo-coupon-usage'); ?></a>
        </p>

        <p>
            <?php esc_html_e('The page is built with the default "Gutenberg" page builder, and will include important information about your affiliate program like commission rates, along with the registration form for users to sign up for your affiliate program. You can also set this page as the default registration page.', 'woo-coupon-usage'); ?>
        </p>

        <?php
        // If site does not use Gutenberg, show a warning message
        if (!function_exists('register_block_type')) {
            ?>
            <div class="error">
                <p><?php esc_html_e('This tool requires WordPress 5.0 or higher with Gutenberg support.', 'woo-coupon-usage'); ?></p>
            </div>
            <?php
        }
        // If disable gutenberg plugin is enabled, show a warning message
        if (is_plugin_active('disable-gutenberg/disable-gutenberg.php')) {
            ?>
            <div class="error">
                <p><?php esc_html_e('This tool requires Gutenberg to be enabled. Please disable the "Disable Gutenberg" plugin or any other plugin that disables Gutenberg.', 'woo-coupon-usage'); ?></p>
            </div>
            <?php
        }

        if (isset($_POST['affiliate_page_submit']) && !$page_exists) {
            $view_url = get_permalink($page_id);
            $edit_url = get_edit_post_link($page_id, '');
            ?>
            <div class="updated">
                <p>Affiliate program promo page has been created successfully at: <a href="<?php echo esc_url($view_url); ?>" target="_blank"><?php echo esc_url($view_url); ?></a></p>
                <p>
                    <a href="<?php echo esc_url($view_url); ?>" class="button" target="_blank"><?php esc_html_e('View Page', 'woo-coupon-usage'); ?></a>
                    <a href="<?php echo esc_url($edit_url); ?>" class="button" target="_blank"><?php esc_html_e('Edit Page', 'woo-coupon-usage'); ?></a>
                </p>
            </div>
            <?php
        }

        // Display existing page info if it exists
        if ($existing_page_id && $page_exists) {
            $view_url = get_permalink($existing_page_id);
            $edit_url = get_edit_post_link($existing_page_id, '');
            ?>
            <br/>
            <h2><?php esc_html_e('Signup Promo Page', 'woo-coupon-usage'); ?></h2>
            <p><?php esc_html_e('Your Affiliate Program signup promo page has been created. You can update it or view it below.', 'woo-coupon-usage'); ?></p>
            <p><a href="<?php echo esc_url($view_url); ?>" target="_blank"><?php echo esc_url($view_url); ?></a></p>
            <p>
                <a href="<?php echo esc_url($view_url); ?>" class="button" target="_blank"><?php esc_html_e('View Page', 'woo-coupon-usage'); ?></a>
                <a href="<?php echo esc_url($edit_url); ?>" class="button" target="_blank"><?php esc_html_e('Edit Page', 'woo-coupon-usage'); ?></a>
            </p>
            <?php
            if (isset($_POST['affiliate_page_submit'])) {
                ?>
                <div class="updated">
                    <p><?php esc_html_e('Affiliate Program page has been updated successfully.', 'woo-coupon-usage'); ?></p>
                </div>
                <?php
            }
        }
        ?>

        <?php if (($existing_page_id && $page_exists) || isset($_POST['affiliate_page_submit'])) { ?>
        <br/>
        <h2><?php esc_html_e('Update Registration Page?', 'woo-coupon-usage'); ?></h2>
        <?php
        if ($existing_page_id == $wcusage_registration_page) {
            ?>
            <div>
                <p><?php esc_html_e('✓ This page is currently set as the default registration Page.', 'woo-coupon-usage'); ?></p>
                <p>
                    <?php esc_html_e('You can change the Registration Page in "Registration Settings" tab:', 'woo-coupon-usage'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_settings&section=tab-registration')); ?>" target="_blank"><?php esc_html_e('Go to settings', 'woo-coupon-usage'); ?></a>
                </p>
            </div>
            <?php
        } else {
            ?>
            <p><?php esc_html_e('This page is not currently set as the Registration Page.', 'woo-coupon-usage'); ?></p>
            <?php if($wcusage_registration_page) { ?>
            <p>
                <?php esc_html_e('The registration page is currently set to:', 'woo-coupon-usage'); ?> <a href="<?php echo esc_url(get_permalink($wcusage_registration_page)); ?>" target="_blank"><?php echo esc_url(get_permalink($wcusage_registration_page)); ?></a>
            </p>
            <?php } ?>
            <p><?php esc_html_e('Would you like to set the new signup promo page as the default registration page?', 'woo-coupon-usage'); ?></p>
            <p><?php esc_html_e('This will replace it with this page:', 'woo-coupon-usage'); ?> <a href="<?php echo esc_url($view_url); ?>" target="_blank"><?php echo esc_html($view_url); ?></a></p>
            <form method="post" action="">
                <?php wp_nonce_field('set_registration_page_action'); ?>
                <?php submit_button('Yes, set as Registration Page', 'primary', 'set_registration_page'); ?>
            </form>
            <?php
        }
        ?>
        <?php } ?>

        <?php if ($existing_page_id && $page_exists || isset($_POST['affiliate_page_submit'])) { ?>
        <br/>
    
        <h2><?php esc_html_e('Generate New Promo Page', 'woo-coupon-usage'); ?></h2>
        <?php
        }
        ?>

        <p>
            <?php esc_html_e('You can customise the content of the generated page template by modifying the fields below.', 'woo-coupon-usage'); ?>
        </p>
        
        <p>
            <?php esc_html_e('You can then customise the the page further after it is generated, by editing the page itself.', 'woo-coupon-usage'); ?>
        </p>

        <form method="post" action="" onsubmit="return <?php echo $page_exists ? 'confirm(\'Warning: This will replace the existing Signup Promo Page content. Are you sure?\')' : 'true'; ?>;">
            <?php wp_nonce_field('affiliate_page_generator_action'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="page_title"><?php esc_html_e('Page Title', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="text" name="page_title" id="page_title" value="<?php echo esc_attr($existing_page_title); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter the title for the affiliate program page', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="page_slug"><?php esc_html_e('Page Slug', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="text" name="page_slug" id="page_slug" value="<?php echo esc_attr($existing_page_slug); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter the URL slug for the affiliate program page (e.g., "affiliate-program")', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="intro_text"><?php esc_html_e('Intro Text', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <textarea name="intro_text" id="intro_text" rows="3" class="large-text"><?php echo esc_textarea($default_intro_text); ?></textarea>
                        <p class="description"><?php esc_html_e('Text to display in the header section', 'woo two-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="commission_rate"><?php esc_html_e('Commission Rate', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="text" name="commission_rate" id="commission_rate" value="<?php echo esc_attr($commission_structure_default); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter the commission rate (e.g., "25%" or "$10 per sale")', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_methods"><?php esc_html_e('Payment Methods', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="text" name="payment_methods" id="payment_methods" value="<?php echo esc_attr($default_payment_methods); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter payment methods (e.g., "PayPal, Bank Transfer, or Stripe")', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_threshold"><?php esc_html_e('Payment Threshold', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="text" name="payment_threshold" id="payment_threshold" value="<?php echo esc_attr($default_payment_threshold); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Enter minimum payout amount (e.g., "$5.00")', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="cookie_duration"><?php esc_html_e('Cookie Duration', 'woo-coupon-usage'); ?></label></th>
                    <td>
                        <input type="number" name="cookie_duration" id="cookie_duration" value="<?php echo esc_attr($default_cookie_duration); ?>" class="small-text"> days
                        <p class="description"><?php esc_html_e('Number of days the referral cookie is stored', 'woo-coupon-usage'); ?></p>
                    </td>
                </tr>
            </table>

            <?php if($existing_page_id && $page_exists) { ?>
            <?php submit_button($page_exists ? 'Generate New (Replace Existing Page Content)' : 'Generate Affiliate Page', 'primary', 'affiliate_page_submit'); ?>
            <?php } else { ?>
            <?php submit_button('Generate Signup Promo Page', 'primary', 'affiliate_page_submit'); ?>
            <?php } ?>
        </form>
    </div>

    <?php
}

// Add some basic CSS to style the generated page
function affiliate_page_styles() {
    $wcusage_options = get_option('wcusage_options', array());
    $page_id = isset($wcusage_options['wcusage_signup_landing_page']) ? $wcusage_options['wcusage_signup_landing_page'] : 0;
    if ($page_id && is_page($page_id)) {
        ?>
        <style type="text/css">
            .benefit-box {
                border: 1px solid #ddd;
                background: #f9f9f9;
                transition: all 0.3s ease;
            }
            .benefit-box:hover {
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .registration-box {
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
            }
            
            @media (max-width: 960px) {
                .wp-block-columns.has-6-columns {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            
            @media (max-width: 600px) {
                .wp-block-columns {
                    flex-direction: column;
                }
                .wp-block-column {
                    margin-bottom: 20px;
                }
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'affiliate_page_styles');

