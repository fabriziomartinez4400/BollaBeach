<?php

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Initialize variables as in the shortcode
$urlid = "";
$postid = "";
$coupon_code = "";
$couponvisible = 0;
$wcusage_show_tabs = 1;
$wcusage_page_load = 0;
$singlecoupon = "";
$force_refresh_stats = 0;
$wcusage_field_load_ajax = 0;
$combined_commission = 0;
$user_no_coupons = 0;
$options = get_option( 'wcusage_options' );
$wcusage_urlprivate = wcusage_get_setting_value( 'wcusage_field_urlprivate', '1' );
// Check if user is logged in
$current_user_id = get_current_user_id();
// Check if admin is previewing another user's dashboard
$preview_user_id = $current_user_id;
$is_admin_preview = false;
if ( isset( $_GET['userid'] ) && isset( $_GET['preview_nonce'] ) && wcusage_check_admin_access() ) {
    $preview_user_id_param = intval( $_GET['userid'] );
    $preview_nonce = sanitize_text_field( $_GET['preview_nonce'] );
    // Verify the nonce
    if ( wp_verify_nonce( $preview_nonce, 'wcusage_preview_affiliate_' . $preview_user_id_param ) ) {
        $preview_user_id = $preview_user_id_param;
        $is_admin_preview = true;
    }
}
if ( isset( $_GET['couponid'] ) ) {
    $coupon_code = strtolower( $_GET['couponid'] );
    $coupon_code = preg_replace( '/-\\d+$/', '', $coupon_code );
    $coupon_code = str_replace( "%20", " ", $coupon_code );
    // Get the coupon ID
    $the_coupon_id = wcusage_get_coupon_id( $coupon_code );
    // Get the coupon post
    $args = array(
        'post_type' => 'shop_coupon',
        'p'         => $the_coupon_id,
    );
    $the_query = new WP_Query($args);
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $postid = get_the_ID();
        $coupon_code = get_the_title();
        $couponvisible = 1;
    }
    $coupons = get_posts( array(
        'post_type'  => 'shop_coupon',
        'meta_key'   => 'wcu_select_coupon_user',
        'meta_value' => $preview_user_id,
    ) );
    wp_reset_postdata();
} else {
    $coupons = get_posts( array(
        'post_type'   => 'shop_coupon',
        'meta_key'    => 'wcu_select_coupon_user',
        'meta_value'  => $preview_user_id,
        'numberposts' => 1,
    ) );
    if ( !empty( $coupons ) ) {
        $coupon_post = $coupons[0];
        $postid = $coupon_post->ID;
    } else {
        $user_no_coupons = 1;
    }
    if ( !empty( $coupons ) ) {
        $coupon_code = $coupon_post->post_title;
    } else {
        $coupon_code = '';
    }
}
$coupons_total = get_posts( array(
    'post_type'  => 'shop_coupon',
    'meta_key'   => 'wcu_select_coupon_user',
    'meta_value' => $preview_user_id,
    'fields'     => 'ids',
) );
$other_view = 0;
$user_info = get_userdata( $preview_user_id );
if ( isset( $_GET['couponid'] ) ) {
    $other_view = 1;
    $couponinfo = wcusage_get_coupon_info( $_GET['couponid'] );
    $couponuser = $couponinfo[1];
    $user_info = get_userdata( $couponuser );
} elseif ( $is_admin_preview ) {
    $other_view = 1;
} else {
    $couponinfo = wcusage_get_coupon_info( $coupon_code );
    if ( isset( $couponinfo[1] ) ) {
        $couponuser = $couponinfo[1];
    } else {
        $couponuser = '';
    }
}
$userlogin = ( $user_info ? $user_info->user_login : '' );
$username = ( $user_info ? $user_info->display_name : '' );
if ( $postid ) {
    // Prepare variables for dashboard, including force_refresh_stats
    $combined_commission = wcusage_commission_message( $postid );
    $current_commission_message = get_post_meta( $postid, 'wcu_commission_message', true );
    $wcusage_field_page_load = wcusage_get_setting_value( 'wcusage_field_page_load', '0' );
    $wcusage_field_load_ajax = wcusage_get_setting_value( 'wcusage_field_load_ajax', 1 );
    $wcusage_field_load_ajax_per_page = wcusage_get_setting_value( 'wcusage_field_load_ajax_per_page', 1 );
    if ( !$wcusage_field_load_ajax ) {
        $wcusage_field_load_ajax_per_page = 0;
    }
    $c = new WC_Coupon($postid);
    $the_coupon_usage = $c->get_usage_count();
} else {
    $postid = 0;
    $the_coupon_usage = 0;
}
if ( !$wcusage_field_load_ajax ) {
    $wcusage_page_load = wcusage_get_setting_value( 'wcusage_field_page_load', '0' );
    if ( $the_coupon_usage > 5000 ) {
        $wcusage_page_load = 1;
    }
} else {
    $wcusage_page_load = "0";
}
$is_mla_parent = '';
if ( $postid ) {
    // Check if user is a parent affiliate (for MLA)
    if ( function_exists( 'wcusage_network_check_sub_affiliate' ) ) {
        $is_mla_parent = wcusage_network_check_sub_affiliate( $current_user_id, get_post_meta( $postid, 'wcu_select_coupon_user', true ) );
    }
    // If GET set and not user's coupon, or not MLA parent, or not admin, show error message
    $couponinfo = wcusage_get_coupon_info_by_id( $postid );
    $couponuser = $couponinfo[1];
    // Check if user is parent affiliate
    if ( function_exists( 'wcusage_network_check_sub_affiliate' ) ) {
        $is_mla_parent = wcusage_network_check_sub_affiliate( $current_user_id, $couponuser );
        if ( $is_mla_parent ) {
            echo "<style>#tab-page-payouts, #tab-page-settings { display: none; }</style>";
        }
    }
    // If not user's coupon, or not MLA parent, or not admin, redirect to affiliate registration page
    if ( $preview_user_id != get_post_meta( $postid, 'wcu_select_coupon_user', true ) && !$is_mla_parent && !wcusage_check_admin_access( $couponuser ) && !$is_admin_preview ) {
        $registration_page = ( isset( $options['wcusage_registration_page'] ) ? $options['wcusage_registration_page'] : '' );
        if ( $registration_page ) {
            wp_safe_redirect( get_permalink( $registration_page ) );
            exit;
        }
    }
}
// Enqueue necessary styles and scripts
if ( !wp_script_is( 'woo-coupon-usage', 'enqueued' ) ) {
    if ( wp_script_is( 'woo-coupon-usage', 'registered' ) ) {
        wp_enqueue_script( 'woo-coupon-usage' );
    } else {
        wp_enqueue_script(
            'woo-coupon-usage',
            WCUSAGE_UNIQUE_PLUGIN_URL . 'js/woo-coupon-usage.js',
            array('jquery'),
            '5.8.0',
            false
        );
    }
}
wp_enqueue_script(
    'wcusage-portal',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'js/portal.js',
    array('jquery', 'woo-coupon-usage'),
    '6.3.7',
    false
);
wp_enqueue_style(
    'wcusage-portal-font-awesome',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'fonts/font-awesome/css/all.min.css',
    array(),
    '5.15.3'
);
wp_enqueue_style(
    'wcusage-portal-css',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'inc/portal/style.css',
    array(),
    '6.3.7'
);
do_action( 'wcusage_hook_custom_styles' );
// Enqueue custom scripts and styles for the registration form
wp_enqueue_script(
    'wcusage-register-ajax',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'js/register-ajax.js',
    array('jquery'),
    '1.0',
    true
);
wp_localize_script( 'wcusage-register-ajax', 'wcusage_ajax_object', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'wcusage_verify_submit_registration_form1' ),
) );
// Enqueue custom settings script
wp_enqueue_script(
    'wcusage-tab-settings',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'js/tab-settings.js',
    array('jquery'),
    '1.0.3',
    true
);
// Enqueue custom settings styles
wp_enqueue_style(
    'wcusage-tab-settings',
    WCUSAGE_UNIQUE_PLUGIN_URL . 'css/tab-settings.css',
    array(),
    '1.0.3'
);
// Localize custom settingsscript with necessary data
wp_localize_script( 'wcusage-tab-settings', 'wcusage_ajax', array(
    'ajax_url'    => admin_url( 'admin-ajax.php' ),
    'saving_text' => __( 'Saving...', 'woo-coupon-usage' ),
    'save_text'   => __( 'Save changes', 'woo-coupon-usage' ),
) );
/*** REFRESH STATS? ***/
$force_refresh_stats = wcusage_check_if_refresh_needed( $postid );
// Check if force refresh needed
if ( $force_refresh_stats ) {
    ?>
    <?php 
    if ( $wcusage_field_load_ajax ) {
        ?>
    <script>
    jQuery(document).ready(function() {
    jQuery('#tab-page-monthly, #tab-page-orders').css("opacity", "0.5");
    jQuery('#tab-page-monthly, #tab-page-orders').css("pointer-events", "none");
    });
    </script>
    <?php 
    }
    ?>
    <?php 
}
// Check if batch refresh enabled
$wcusage_field_enable_coupon_all_stats_batch = wcusage_get_setting_value( 'wcusage_field_enable_coupon_all_stats_batch', '1' );
// Get tab colors
$tab_color = wcusage_get_setting_value( 'wcusage_field_color_tab', '#2c3e50' );
$tab_font_color = wcusage_get_setting_value( 'wcusage_field_color_tab_font', 'white' );
$tab_hover_color = wcusage_get_setting_value( 'wcusage_field_color_tab_hover', '#34495e' );
$tab_hover_font_color = wcusage_get_setting_value( 'wcusage_field_color_tab_hover_font', 'white' );
// Get portal title and footer text
$wcusage_portal_title = wcusage_get_setting_value( 'wcusage_portal_title', __( 'Affiliate Portal', 'woo-coupon-usage' ) );
$portal_footer_text = wcusage_get_setting_value( 'wcusage_portal_footer_text', '' );
// Convert to html entities
$portal_footer_text = htmlspecialchars_decode( $portal_footer_text );
// Show login and registration forms
$register_loggedin = wcusage_get_setting_value( 'wcusage_field_registration_enable_register_loggedin', '1' );
$wcusage_portal_login_enabled = wcusage_get_setting_value( 'wcusage_field_loginform', '1' );
$wcusage_portal_registration_enabled = wcusage_get_setting_value( 'wcusage_field_enable_portal_registration', '1' );
?>

<!DOCTYPE html>
<html lang="<?php 
echo esc_attr( get_locale() );
?>">
<?php 
do_action( 'wcusage_portal_hook_before_head' );
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
echo esc_html( $wcusage_portal_title );
?></title>
    <?php 
wp_head();
// Include necessary WordPress head scripts
?>

    <?php 
// Apply selected primary font to the portal via CSS variable
$wcusage_portal_font_family = wcusage_get_setting_value( 'wcusage_portal_font_family', '' );
if ( !empty( $wcusage_portal_font_family ) ) {
    // Allow only safe characters for CSS font-family lists
    $safe_font_family = preg_replace( '/[^a-zA-Z0-9\\s,\\-"\']/', '', $wcusage_portal_font_family );
    // Remove "quot" text
    $safe_font_family = str_replace( array("'", '"', 'quot'), '', $safe_font_family );
    if ( !empty( $safe_font_family ) ) {
        echo '<style id="wcusage-portal-font">:root{--primary-font: ' . esc_html( $safe_font_family ) . ';}</style>';
    }
}
// Unenqueue any stylesheets from the sites theme
$theme = wp_get_theme();
$theme_name = $theme->get( 'Name' );
$theme_name = strtolower( $theme_name );
// Get all enqueued styles
global $wp_styles;
$styles = $wp_styles->queue;
foreach ( $styles as $style ) {
    $style_obj = $wp_styles->registered[$style];
    $style_handle = $style_obj->handle;
    $style_src = $style_obj->src;
    $style_src = strtolower( $style_src );
    // Don't dequeue our Font Awesome or portal styles
    if ( $style_handle === 'wcusage-portal-font-awesome' || $style_handle === 'wcusage-portal-css' || $style_handle === 'woo-coupon-usage-style' ) {
        continue;
    }
    if ( strpos( $style_src, $theme_name ) !== false || strpos( $style_src, 'woocommerce' ) !== false || strpos( $style_src, 'wc-' ) !== false || strpos( $style_src, 'global-styles' ) !== false || strpos( $style_src, 'global' ) !== false ) {
        wp_dequeue_style( $style_handle );
    }
}
?>
</head>
<?php 
do_action( 'wcusage_portal_hook_before_body' );
?>
<body>
    <div class="affiliate-portal-container">
        <!-- Left Sidebar with Tabs -->
        <div class="sidebar<?php 
echo ( !$current_user_id || $user_no_coupons ? ' logged-out' : '' );
?>" style="background: <?php 
echo esc_attr( $tab_color );
?>;">
            <div class="sidebar-logo">
                <?php 
$portal_slug = wcusage_get_setting_value( 'wcusage_portal_slug', 'affiliate-portal' );
$portal_logo = wcusage_get_setting_value( 'wcusage_portal_logo', '' );
$portal_logo = apply_filters( 'wcusage_hook_portal_logo', $portal_logo );
if ( $portal_logo ) {
    echo '<a href="' . esc_url( home_url( $portal_slug ) ) . '">';
    echo '<img src="' . esc_url( $portal_logo ) . '" alt="Portal Logo">';
    echo '</a>';
}
?>
                <h2 style="color: <?php 
echo esc_attr( $tab_font_color );
?>; font-size: 18px; font-weight: bold; margin-top: 10px; margin-bottom: 10px; text-align: center;">
                    <?php 
echo esc_html( $wcusage_portal_title );
?>
                </h2>
            </div>
            <div class="portal-tabs">
                <?php 
wcusage_portal_tabs(
    $postid,
    $coupon_code,
    $wcusage_page_load,
    $is_mla_parent,
    $force_refresh_stats
);
?>
            </div>
            <?php 
do_action( 'wcusage_portal_hook_sidebar_bottom' );
?>

            <div class="wcu-mobile-menu-close-wrap">
                <button type="button" class="wcu-mobile-menu-close">
                    <?php 
echo esc_html__( 'Close Menu', 'woo-coupon-usage' );
?>
                </button>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="content">
            <?php 
if ( !$current_user_id ) {
    ?>
                <!-- Logged-out User: Login and Registration Forms -->
                <div class="content-header">
                    <i class="fas fa-bars hamburger-menu"></i>
                    <div class="welcome-header">
                        <h1 style="font-size: 24px; margin: 0; color: #2c3e50; font-weight: bold;">
                            <?php 
    echo esc_html( $wcusage_portal_title );
    ?>
                        </h1>
                    </div>
                    <?php 
    $wcusage_portal_dark_mode = wcusage_get_setting_value( 'wcusage_portal_dark_mode', '1' );
    if ( $wcusage_portal_dark_mode ) {
        ?>
                    <i class="fas fa-sun dark-mode-toggle" id="dark-mode-toggle" title="Toggle Dark Mode"></i>
                    <?php 
    }
    ?>
                </div>
                <?php 
    do_action( 'wcusage_portal_hook_after_header' );
    ?>
                <?php 
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    $wcusage_field_registration_enable_logout = wcusage_get_setting_value( 'wcusage_field_registration_enable_logout', '1' );
    $wcusage_field_registration_enable_login = wcusage_get_setting_value( 'wcusage_field_registration_enable_login', '1' );
    $wcusage_should_show_registration = $wcusage_portal_registration_enabled && $wcusage_field_registration_enable && $wcusage_field_registration_enable_logout && $wcusage_field_registration_enable_login;
    if ( $wcusage_portal_login_enabled || $wcusage_should_show_registration ) {
        ?>
                <div class="login-registration-container">
                    <?php 
        if ( $wcusage_portal_login_enabled ) {
            ?>
                    <div class="login-form">
                        <h2 class="wcusage-login-form-title"><?php 
            esc_html_e( 'Login', 'woo-coupon-usage' );
            ?></h2>
                        <?php 
            if ( function_exists( 'wc_print_notices' ) ) {
                woocommerce_output_all_notices();
            }
            if ( function_exists( 'woocommerce_login_form' ) ) {
                woocommerce_login_form();
            }
            ?>
                        <?php 
            do_action( 'wcusage_portal_hook_after_login_form' );
            ?>
                    </div>
                    <?php 
        }
        ?>
                    <?php 
        if ( $wcusage_should_show_registration ) {
            ?>
                    <div class="registration-form">
                        <?php 
            // Display couponaffiliates-register shortcode
            echo do_shortcode( '[couponaffiliates-register]' );
            ?>
                        <?php 
            do_action( 'wcusage_portal_hook_after_registration_form' );
            ?>
                    </div>
                    <?php 
        }
        ?>
                </div>
                <?php 
    } else {
        ?>
                    <p><?php 
        esc_html_e( 'You do not have permission to access the affiliate portal.', 'woo-coupon-usage' );
        ?></p>
                <?php 
    }
    ?>
                <?php 
    if ( $portal_footer_text ) {
        ?>
                <div class="content-footer">
                    <p><?php 
        echo wp_kses_post( $portal_footer_text );
        ?></p>
                </div>
                <?php 
    }
    ?>
            <?php 
} elseif ( $user_no_coupons ) {
    ?>
                <!-- Logged-out User: Login and Registration Forms -->
                <div class="content-header">
                    <i class="fas fa-bars hamburger-menu"></i>
                    <div class="welcome-header">
                        <h1 style="font-size: 24px; margin: 0; color: #2c3e50; font-weight: bold;">
                            <?php 
    echo esc_html( $wcusage_portal_title );
    ?>
                        </h1>
                    </div>
                    <?php 
    $wcusage_portal_dark_mode = wcusage_get_setting_value( 'wcusage_portal_dark_mode', '1' );
    if ( $wcusage_portal_dark_mode ) {
        ?>
                    <i class="fas fa-sun dark-mode-toggle" id="dark-mode-toggle" title="Toggle Dark Mode"></i>
                    <?php 
    }
    ?>
                    <div class="profile-dropdown">
                        <?php 
    $wcusage_field_show_username = wcusage_get_setting_value( 'wcusage_field_show_username', '1' );
    if ( is_user_logged_in() ) {
        $user_email = $user_info->user_email;
        $avatar_url = get_avatar_url( $user_email, array(
            'size' => 40,
        ) );
        ?>
                            <?php 
        if ( $wcusage_field_show_username ) {
            ?>
                            <div class="profile-trigger">
                                <span class="username-in-header"><?php 
            if ( $other_view && $username ) {
                esc_html_e( 'Viewing as', 'woo-coupon-usage' );
                ?>: <?php 
            }
            echo esc_html( $username );
            ?></span><img src="<?php 
            echo esc_url( $avatar_url );
            ?>" alt="<?php 
            echo esc_attr( $username );
            ?>" class="profile-image">
                                <i class="fas fa-caret-down dropdown-arrow"></i>
                            </div>
                            <?php 
        }
        ?>
                            <div class="dropdown-content">
                                <?php 
        $currentuserid = get_current_user_id();
        if ( $currentuserid == $couponuser ) {
            echo '<a href="javascript:void(0);" onclick="wcusage_portal_open_tab(event, \'tab-page-settings\', \'wcu6\', \'' . esc_js( $postid ) . '\', \'' . esc_js( $coupon_code ) . '\', \'' . esc_js( $force_refresh_stats ) . '\');">' . esc_html__( 'Settings', 'woo-coupon-usage' ) . '</a>';
        }
        $wcusage_field_show_logout_link = wcusage_get_setting_value( 'wcusage_field_show_logout_link', '1' );
        if ( $wcusage_field_show_logout_link ) {
            $logoutredirectpage = get_page_link( wcusage_get_coupon_shortcode_page_id() );
            $wcusage_field_portal_enable = wcusage_get_setting_value( 'wcusage_field_portal_enable', '0' );
            $portal_slug = wcusage_get_setting_value( 'wcusage_portal_slug', 'affiliate-portal' );
            if ( $wcusage_field_portal_enable && $portal_slug ) {
                $logoutredirectpage = home_url( $portal_slug );
            }
            echo '<a href="' . esc_url( wp_logout_url( $logoutredirectpage ) ) . '">' . esc_html__( 'Logout', 'woo-coupon-usage' ) . '</a>';
        }
        ?>
                            </div>
                            <?php 
    }
    ?>
                    </div>
                    <?php 
    do_action( 'wcusage_portal_hook_after_profile_dropdown' );
    ?>
                </div>
                <?php 
    do_action( 'wcusage_portal_hook_after_header' );
    ?>
                <div class="login-registration-container">
                    <div class="registration-form">
                        <?php 
    $wcusage_field_registration_enable = wcusage_get_setting_value( 'wcusage_field_registration_enable', '1' );
    if ( $wcusage_field_registration_enable ) {
        if ( $register_loggedin && is_user_logged_in() ) {
            echo do_shortcode( '[couponaffiliates-register]' );
            do_action( 'wcusage_portal_hook_after_registration_form' );
        } else {
            echo '<p>' . esc_html__( 'No affiliate coupons are assigned to your account.', 'woo-coupon-usage' ) . '</p>';
        }
        if ( !is_user_logged_in() ) {
            $wcusage_field_registration_enable_logout = wcusage_get_setting_value( 'wcusage_field_registration_enable_logout', '1' );
            $wcusage_field_registration_enable_login = wcusage_get_setting_value( 'wcusage_field_registration_enable_login', '1' );
            if ( $wcusage_field_registration_enable_logout && $wcusage_field_registration_enable_login ) {
                echo do_shortcode( '[couponaffiliates-register]' );
                do_action( 'wcusage_portal_hook_after_registration_form' );
            }
        }
    } else {
        echo '<p>' . esc_html__( 'No affiliate coupons are assigned to your account.', 'woo-coupon-usage' ) . '</p>';
    }
    ?>
                    </div>
                </div>
                <?php 
    if ( $portal_footer_text ) {
        ?>
                <div class="content-footer">
                    <p><?php 
        echo wp_kses_post( $portal_footer_text );
        ?></p>
                </div>
                <?php 
    }
    ?>
            <?php 
} else {
    ?>
                <div class="content-header">
                    <i class="fas fa-bars hamburger-menu"></i>
                    <div class="welcome-header">
                        <?php 
    if ( $coupons_total && count( $coupons_total ) > 0 || isset( $_GET['couponid'] ) ) {
        // If multiple coupons, show dropdown with current coupon selected
        if ( $coupons_total && count( $coupons_total ) > 1 && !isset( $_GET['couponid'] ) ) {
            if ( isset( $_GET['couponid'] ) ) {
                $wcusage_before_title = wcusage_get_setting_value( 'wcusage_before_title', '' );
                $wcusage_before_title = "<span class='wcu-coupon-title-prefix'>" . esc_html( $wcusage_before_title ) . "</span>";
                if ( $wcusage_before_title ) {
                    echo wp_kses_post( $wcusage_before_title );
                }
                // Dropdown with all coupons, clicking one opens that coupon's dashboard, icon to right
                echo '<select id="wcu-coupon-select" style="margin-left: 0px; font-size: 24px; width: 250px;
                                    border: 0; background: #f0f0f0; color: #2c3e50; cursor: pointer;">';
                foreach ( $coupons as $coupon ) {
                    $coupon_id = $coupon->ID;
                    $coupon_title = $coupon->post_title;
                    $selected = ( $coupon_title == $coupon_code ? 'selected' : '' );
                    echo '<option value="' . esc_attr( $coupon_title ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $coupon_title ) . '</option>';
                }
                echo '</select>';
                // Open selected coupon dashboard
                $wcusage_portal_slug = wcusage_get_setting_value( 'wcusage_portal_slug', 'affiliate-portal' );
                ?>
                                    <script>
                                    jQuery(document).ready(function() {
                                        jQuery('#wcu-coupon-select').on('change', function() {
                                            var couponid = jQuery(this).val();
                                            var current_page_url = '<?php 
                echo esc_js( $wcusage_portal_slug );
                ?>';
                                            window.location.href = '<?php 
                echo esc_url( home_url() );
                ?>/' + current_page_url + '?couponid=' + couponid;
                                        });
                                    });
                                    </script>

                                    <?php 
                // Hidden input field with title
                echo '<input type="hidden" id="wcu-coupon-title" value="' . esc_attr( $coupon_code ) . '">';
            } else {
                ?>

                                <style>
                                .portal-tabs .portal-tablink {
                                    display: none;
                                }
                                .portal-tabs #tab-page-back {
                                    opacity: 1;
                                    pointer-events: auto;
                                    display: block !important;
                                }
                                </style>
                                
                                <?php 
            }
        } else {
            // Coupon Dashboard Title
            $dashboard_title = get_the_title( $postid );
            // Hidden input field with title
            echo '<input type="hidden" id="wcu-coupon-title" value="' . esc_attr( $dashboard_title ) . '">';
            // Filter to customize title
            $dashboard_title = apply_filters( 'wcusage_hook_dashboard_title', $dashboard_title, $postid );
            echo '<i class="fas fa-tag" style="font-size: 0.8em;"></i> ';
            $dashboard_title = "<span class='wcu-coupon-title'>" . $dashboard_title . "</span>";
            $wcusage_before_title = wcusage_get_setting_value( 'wcusage_before_title', '' );
            $wcusage_before_title = "<span class='wcu-coupon-title-prefix'>" . $wcusage_before_title . "</span>";
            if ( $wcusage_before_title ) {
                $dashboard_title = $wcusage_before_title . " " . $dashboard_title;
            }
            echo wp_kses_post( $dashboard_title );
        }
    }
    ?>
                    </div>
                    <?php 
    do_action( 'wcusage_portal_hook_before_header_buttons' );
    ?>
                    <?php 
    $wcusage_portal_dark_mode = wcusage_get_setting_value( 'wcusage_portal_dark_mode', '1' );
    if ( $wcusage_portal_dark_mode ) {
        ?>
                    <i class="fas fa-sun dark-mode-toggle" id="dark-mode-toggle" title="Toggle Dark Mode"></i>
                    <?php 
    }
    ?>
                    <div class="profile-dropdown">
                        <?php 
    $wcusage_field_show_username = wcusage_get_setting_value( 'wcusage_field_show_username', '1' );
    if ( is_user_logged_in() && $wcusage_field_show_username ) {
        if ( !$user_info ) {
            $user_info = get_userdata( $current_user_id );
        }
        $user_email = $user_info->user_email;
        $avatar_url = get_avatar_url( $user_email, array(
            'size' => 40,
        ) );
        ?>
                            <div class="profile-trigger">
                                <span class="username-in-header"><?php 
        if ( $other_view && $username ) {
            esc_html_e( 'Viewing as', 'woo-coupon-usage' );
            ?>: <?php 
        }
        echo esc_html( $username );
        ?></span><img src="<?php 
        echo esc_url( $avatar_url );
        ?>" alt="<?php 
        echo esc_attr( $username );
        ?>" class="profile-image">
                                <i class="fas fa-caret-down dropdown-arrow"></i>
                            </div>
                            <div class="dropdown-content">
                                <?php 
        $currentuserid = get_current_user_id();
        if ( $currentuserid == $couponuser ) {
            echo '<a href="javascript:void(0);" onclick="wcusage_portal_open_tab(event, \'tab-page-settings\', \'wcu6\', \'' . esc_js( $postid ) . '\', \'' . esc_js( $coupon_code ) . '\', \'' . esc_js( $force_refresh_stats ) . '\');">' . esc_html__( 'Settings', 'woo-coupon-usage' ) . '</a>';
        }
        $logoutredirectpage = get_page_link( wcusage_get_coupon_shortcode_page_id() );
        $wcusage_field_portal_enable = wcusage_get_setting_value( 'wcusage_field_portal_enable', '0' );
        $portal_slug = wcusage_get_setting_value( 'wcusage_portal_slug', 'affiliate-portal' );
        if ( $wcusage_field_portal_enable && $portal_slug ) {
            $logoutredirectpage = home_url( $portal_slug );
        }
        echo '<a href="' . esc_url( wp_logout_url( $logoutredirectpage ) ) . '">' . esc_html__( 'Logout', 'woo-coupon-usage' ) . '</a>';
        ?>
                            </div>
                            <?php 
    }
    ?>
                    </div>
                    <?php 
    do_action( 'wcusage_portal_hook_after_profile_dropdown' );
    ?>
                </div>
                <?php 
    do_action( 'wcusage_portal_hook_after_header' );
    ?>
                <div class="content-body">
                    <?php 
    // Refresh the stats via ajax in batches
    if ( $wcusage_field_load_ajax && $wcusage_field_enable_coupon_all_stats_batch && $force_refresh_stats ) {
        $force_refresh_stats = 0;
        ?>

                        <style>
                        .portal-tabs {
                            opacity: 0.5;
                            pointer-events: none;
                        }
                        </style>

                        <?php 
        do_action( 'wcusage_hook_before_dashboard', $coupon_code );
        // Custom Hook
        ?>

                        <div style="clear: both;"></div>
                        
                        <?php 
        do_action( 'wcusage_hook_update_all_stats_batch_ajax', $coupon_code, $the_coupon_usage );
        ?>

                        <?php 
    } elseif ( $coupons_total && count( $coupons_total ) > 1 && !isset( $_GET['couponid'] ) ) {
        echo do_shortcode( '[couponaffiliates-user]' );
    } else {
        wcusage_portal_tab_content(
            $postid,
            $coupon_code,
            $combined_commission,
            $wcusage_page_load,
            $force_refresh_stats,
            $is_mla_parent
        );
    }
    ?>
                </div>
                <?php 
    do_action( 'wcusage_portal_hook_after_body' );
    ?>
                <?php 
    if ( $portal_footer_text ) {
        ?>
                <div class="content-footer">
                    <p><?php 
        echo wp_kses_post( $portal_footer_text );
        ?></p>
                </div>
                <?php 
    }
    ?>
                <?php 
    do_action( 'wcusage_portal_hook_after_footer' );
    ?>
            <?php 
}
?>
        </div>
    </div>

    <script>
    function wcusage_update_complete_loading() {
        jQuery(".wcu-loading-image").hide();
        jQuery('.stuck-loading-message').hide();
        jQuery(".wcu-loading-hide").css({"visibility": "visible", "height": "auto"});
        jQuery('.wcusage-refresh-data i').removeClass('fa-spin wcusage-loading');
        jQuery(".wcusagechart").css("visibility", "visible");
        jQuery("#wcusagechartmonth path").click();
        jQuery('#generate-short-url').css('opacity', '1');
        jQuery('#generate-short-url').prop('disabled', false);
    }
    <?php 
if ( $wcusage_field_load_ajax ) {
    ?>
    jQuery(document).on({
        ajaxStart: function(){
            jQuery(".wcu-loading-image").show();
            jQuery('.wcusage-refresh-data i').addClass('fa-spin wcusage-loading');
        },
        ajaxStop: function(){
            wcusage_update_complete_loading();
        }
    });
    <?php 
} else {
    ?>
    jQuery( document ).ready(function() {
        wcusage_update_complete_loading();
    });
    <?php 
}
?>
    </script>

    <?php 
wp_footer();
// Include necessary WordPress footer scripts
?>
</body>
</html>

<?php 
// Define tab generation function
function wcusage_portal_tabs(
    $postid,
    $coupon_code,
    $wcusage_page_load,
    $is_mla_parent,
    $force_refresh_stats
) {
    $options = get_option( 'wcusage_options' );
    $custom_order = ( isset( $options['wcusage_dashboard_tabs_layout'] ) ? $options['wcusage_dashboard_tabs_layout'] : '' );
    $show_tabs_icons = wcusage_get_setting_value( 'wcusage_field_show_tabs_icons', '1' );
    $wcusage_field_show_statistics_tab = wcusage_get_setting_value( 'wcusage_field_show_statistics_tab', '1' );
    $wcusage_field_show_order_tab = wcusage_get_setting_value( 'wcusage_field_show_order_tab', '1' );
    $option_coupon_orders = wcusage_get_setting_value( 'wcusage_field_orders', '10' );
    $wcusage_field_urls_enable = wcusage_get_setting_value( 'wcusage_field_urls_enable', '1' );
    $wcusage_field_urls_tab_enable = wcusage_get_setting_value( 'wcusage_field_urls_tab_enable', '1' );
    $wcusage_field_creatives_enable = wcusage_get_setting_value( 'wcusage_field_creatives_enable', '1' );
    $wcusage_field_payouts_enable = wcusage_get_setting_value( 'wcusage_field_payouts_enable', '1' );
    $wcusage_field_rates_enable = wcusage_get_setting_value( 'wcusage_field_rates_enable', '0' );
    $wcusage_field_bonuses_enable = wcusage_get_setting_value( 'wcusage_field_bonuses_enable', '0' );
    $wcusage_field_bonuses_tab_enable = wcusage_get_setting_value( 'wcusage_field_bonuses_tab_enable', '1' );
    $wcusage_field_show_settings_tab_show = wcusage_get_setting_value( 'wcusage_field_show_settings_tab_show', '1' );
    $tab_color = wcusage_get_setting_value( 'wcusage_field_color_tab', '#2c3e50' );
    $tab_font_color = wcusage_get_setting_value( 'wcusage_field_color_tab_font', 'white' );
    $tab_hover_color = wcusage_get_setting_value( 'wcusage_field_color_tab_hover', '#34495e' );
    $tab_hover_font_color = wcusage_get_setting_value( 'wcusage_field_color_tab_hover_font', 'white' );
    $tabs = [
        [
            'tab-id'     => 'tab-page-stats',
            'content-id' => 'wcu1',
            'label'      => __( 'Statistics', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-chart-line',
            'condition'  => $wcusage_field_show_statistics_tab,
        ],
        [
            'tab-id'     => 'tab-page-monthly',
            'content-id' => 'wcu2',
            'label'      => __( 'Monthly Summary', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-calendar-alt',
            'condition'  => wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && wcusage_get_setting_value( 'wcusage_field_show_months_table', '1' ),
        ],
        [
            'tab-id'     => 'tab-page-orders',
            'content-id' => 'wcu3',
            'label'      => __( 'Recent Orders', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-shopping-cart',
            'condition'  => $wcusage_field_show_order_tab && ($option_coupon_orders > 0 || $option_coupon_orders == ''),
        ],
        [
            'tab-id'     => 'tab-page-links',
            'content-id' => 'wcu4',
            'label'      => __( 'Referral URL', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-link',
            'condition'  => $wcusage_field_urls_enable && $wcusage_field_urls_tab_enable,
        ],
        [
            'tab-id'     => 'tab-page-creatives',
            'content-id' => 'wcu7',
            'label'      => __( 'Creatives', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-photo-video',
            'condition'  => wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_creatives_enable && wp_count_posts( 'wcu-creatives' )->publish > 0,
        ],
        [
            'tab-id'     => 'tab-page-rates',
            'content-id' => 'wcu-rates',
            'label'      => __( 'Rates', 'woo-coupon-usage' ),
            'icon'       => 'fa-solid fa-percent',
            'condition'  => wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_rates_enable,
        ],
        [
            'tab-id'     => 'tab-page-payouts',
            'content-id' => 'wcu5',
            'label'      => __( 'Payouts', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-money-bill-wave',
            'condition'  => wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_payouts_enable && (!$is_mla_parent || wcusage_check_admin_access()),
        ],
        [
            'tab-id'     => 'tab-page-bonuses',
            'content-id' => 'wcubonuses',
            'label'      => __( 'Bonuses', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-gift',
            'condition'  => wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && $wcusage_field_bonuses_enable && $wcusage_field_bonuses_tab_enable,
        ],
        [
            'tab-id'     => 'tab-page-settings',
            'content-id' => 'wcu6',
            'label'      => __( 'Settings', 'woo-coupon-usage' ),
            'icon'       => 'fas fa-cog',
            'condition'  => is_user_logged_in() && $wcusage_field_show_settings_tab_show && (!$is_mla_parent || wcusage_check_admin_access()),
        ]
    ];
    // Custom Tabs (Premium Only)
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
        $tabsnumber = wcusage_get_setting_value( 'wcusage_field_custom_tabs_number', '2' );
        for ($i = 1; $i <= $tabsnumber; $i++) {
            $hide = 1;
            $thisid = 'wcusage_field_custom_tabs_roles_' . $i;
            if ( empty( $options[$thisid] ) ) {
                $hide = 0;
            } else {
                $roles = wp_roles()->roles;
                foreach ( $roles as $key => $role ) {
                    if ( isset( $options[$thisid][$key] ) && user_can( get_current_user_id(), $key ) ) {
                        $hide = 0;
                    }
                }
            }
            if ( isset( $options['wcusage_field_custom_tabs'][$i]['name'] ) ) {
                $custom_tab_name = $options['wcusage_field_custom_tabs'][$i]['name'];
                if ( !$hide && $custom_tab_name ) {
                    $custom_icon = $options['wcusage_field_custom_tabs_icon_' . $i];
                    $custom_icon = ( $custom_icon ? 'fas fa-' . $custom_icon : '' );
                    $legacy_external = ( isset( $options['wcusage_field_custom_tabs'][$i]['external'] ) ? $options['wcusage_field_custom_tabs'][$i]['external'] : '' );
                    $custom_external = wcusage_get_setting_value( 'wcusage_field_custom_tabs_external_' . $i, $legacy_external );
                    $legacy_external_url = ( isset( $options['wcusage_field_custom_tabs'][$i]['external_url'] ) ? $options['wcusage_field_custom_tabs'][$i]['external_url'] : '' );
                    $custom_external_url = wcusage_get_setting_value( 'wcusage_field_custom_tabs_external_url_' . $i, $legacy_external_url );
                    // If external + URL valid, mark with special content id 'external'
                    if ( $custom_external == '1' && $custom_external_url ) {
                        $tabs[] = [
                            'tab-id'       => 'tab-custom-' . $i,
                            'content-id'   => 'external',
                            'external_url' => esc_url( $custom_external_url ),
                            'label'        => $custom_tab_name,
                            'icon'         => $custom_icon,
                            'condition'    => true,
                        ];
                    } else {
                        $tabs[] = [
                            'tab-id'     => 'tab-custom-' . $i,
                            'content-id' => 'wcu0' . $i,
                            'label'      => $custom_tab_name,
                            'icon'       => $custom_icon,
                            'condition'  => true,
                        ];
                    }
                }
            }
        }
    }
    // Add Back to Site link at very bottom (always last regardless of order setting)
    $back_tab = [
        'tab-id'     => 'tab-page-back',
        'content-id' => 'wcu-back',
        'label'      => __( 'Back to site', 'woo-coupon-usage' ),
        'icon'       => 'fas fa-arrow-left',
        'condition'  => true,
    ];
    // Reorder according to custom order setting shared with dashboard
    if ( $custom_order ) {
        $order_keys = array_filter( array_map( 'trim', explode( ',', $custom_order ) ) );
        $reordered = [];
        foreach ( $order_keys as $key ) {
            foreach ( $tabs as $t ) {
                if ( $t['tab-id'] === $key ) {
                    $reordered[] = $t;
                    break;
                }
            }
        }
        // Append any tabs not captured (new ones)
        foreach ( $tabs as $t ) {
            $exists = false;
            foreach ( $reordered as $rt ) {
                if ( $rt['tab-id'] === $t['tab-id'] ) {
                    $exists = true;
                    break;
                }
            }
            if ( !$exists ) {
                $reordered[] = $t;
            }
        }
        $tabs = $reordered;
    }
    // Finally append back tab
    $tabs[] = $back_tab;
    // Filter out tabs disabled via new visibility toggles (wcusage_dashboard_tab_visible_<tab-id>)
    if ( is_array( $tabs ) ) {
        foreach ( $tabs as $idx => $t ) {
            $tid = ( isset( $t['tab-id'] ) ? $t['tab-id'] : $idx );
            $vis_opt_key = 'wcusage_dashboard_tab_visible_' . $tid;
            if ( isset( $options[$vis_opt_key] ) && (int) $options[$vis_opt_key] !== 1 ) {
                unset($tabs[$idx]);
            }
        }
    }
    // Determine first visible tab id to set active state dynamically
    $first_visible = '';
    foreach ( $tabs as $t ) {
        if ( $t['tab-id'] !== 'tab-page-back' && $t['condition'] ) {
            $first_visible = $t['tab-id'];
            break;
        }
    }
    // Track whether we auto-clicked first tab via JS (will inject script after list rendered)
    $portal_first_tab_id = $first_visible;
    foreach ( $tabs as $tab ) {
        $wcusage_field_tracking_enable = wcusage_get_setting_value( 'wcusage_field_tracking_enable', '1' );
        $wcusage_field_payouts_enable = wcusage_get_setting_value( 'wcusage_field_payouts_enable', '1' );
        if ( $tab['tab-id'] == 'tab-page-payouts' && (!$wcusage_field_tracking_enable || !$wcusage_field_payouts_enable) ) {
            continue;
        }
        if ( $tab['tab-id'] == 'tab-page-back' ) {
            ?>
            <a href="<?php 
            echo esc_url( home_url( '/' ) );
            ?>" id="<?php 
            echo esc_attr( $tab['tab-id'] );
            ?>"
            class="portal-tablink" style="margin-top: 75px; background: <?php 
            echo esc_attr( $tab_color );
            ?>; color: <?php 
            echo esc_attr( $tab_font_color );
            ?>; border: none; padding: 15px 20px; text-align: left; cursor: pointer; font-size: 16px; transition: background 0.3s, color 0.3s; border-left: 4px solid transparent; outline: none;">
                <?php 
            if ( $show_tabs_icons && $tab['icon'] ) {
                ?><i class="<?php 
                echo esc_attr( $tab['icon'] );
                ?> fa-xs"></i><?php 
            }
            ?>
                <?php 
            echo esc_html( $tab['label'] );
            ?>
            </a>
            <?php 
        } else {
            if ( $tab['condition'] ) {
                if ( isset( $tab['content-id'] ) && $tab['content-id'] === 'external' && isset( $tab['external_url'] ) ) {
                    ?>
                    <a id="<?php 
                    echo esc_attr( $tab['tab-id'] );
                    ?>" class="portal-tablink" href="<?php 
                    echo esc_url( $tab['external_url'] );
                    ?>" target="_blank" rel="noopener noreferrer" style="background: <?php 
                    echo esc_attr( $tab_color );
                    ?>; color: <?php 
                    echo esc_attr( $tab_font_color );
                    ?>;">
                        <?php 
                    if ( $show_tabs_icons && $tab['icon'] ) {
                        ?><i class="<?php 
                        echo esc_attr( $tab['icon'] );
                        ?> fa-xs"></i><?php 
                    }
                    ?>
                        <?php 
                    echo esc_html( $tab['label'] );
                    ?> <span class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></span>
                    </a>
                <?php 
                } else {
                    ?>
                    <button id="<?php 
                    echo esc_attr( $tab['tab-id'] );
                    ?>" class="portal-tablink <?php 
                    if ( $tab['tab-id'] == $first_visible ) {
                        echo 'active';
                    }
                    ?>"
                    data-content-id="<?php 
                    echo esc_attr( $tab['content-id'] );
                    ?>" onclick="wcusage_portal_open_tab(event, '<?php 
                    echo esc_attr( $tab['tab-id'] );
                    ?>', '<?php 
                    echo esc_attr( $tab['content-id'] );
                    ?>', '<?php 
                    echo esc_js( $postid );
                    ?>', '<?php 
                    echo esc_js( $coupon_code );
                    ?>', '<?php 
                    echo esc_js( $force_refresh_stats );
                    ?>')" style="background: <?php 
                    echo esc_attr( $tab_color );
                    ?>; color: <?php 
                    echo esc_attr( $tab_font_color );
                    ?>;">
                        <?php 
                    if ( $show_tabs_icons && $tab['icon'] ) {
                        ?><i class="<?php 
                        echo esc_attr( $tab['icon'] );
                        ?> fa-xs"></i><?php 
                    }
                    ?>
                        <?php 
                    echo esc_html( $tab['label'] );
                    ?>
                    </button>
                <?php 
                }
                ?>
                <script>
                (function(){
                  var el = document.getElementById('<?php 
                echo esc_js( $tab['tab-id'] );
                ?>');
                  if(!el) return;
                  el.addEventListener('mouseover', function() {
                      this.style.background = '<?php 
                echo esc_js( $tab_hover_color );
                ?>';
                      this.style.color = '<?php 
                echo esc_js( $tab_hover_font_color );
                ?>';
                  });
                  el.addEventListener('mouseout', function() {
                      this.style.background = '<?php 
                echo esc_js( $tab_color );
                ?>';
                      this.style.color = '<?php 
                echo esc_js( $tab_font_color );
                ?>';
                  });
                  el.addEventListener('click', function() {
                      this.style.background = '<?php 
                echo esc_js( $tab_hover_color );
                ?>';
                      this.style.color = '<?php 
                echo esc_js( $tab_hover_font_color );
                ?>';
                      this.classList.add('active');
                  });
                })();
                </script>
                <style>
                .portal-tablink.active {
                    background: <?php 
                echo esc_attr( $tab_hover_color );
                ?> !important;
                    color: <?php 
                echo esc_attr( $tab_hover_font_color );
                ?> !important;
                }
                </style>
                <?php 
            }
        }
    }
    // JS: ensure first visible tab triggers its click handler (loads content) if not statistics or if order changed
    if ( $portal_first_tab_id ) {
        echo '<script>document.addEventListener("DOMContentLoaded",function(){
        // Delay it by 100ms to ensure DOM ready
        setTimeout(function(){
            var el=document.getElementById("' . esc_js( $portal_first_tab_id ) . '");
            if(el && !el.classList.contains("portal-tab-init")){ el.classList.add("portal-tab-init"); el.click();
            }
        }, 100);
        });</script>';
    }
    do_action( 'wcusage_hook_after_normal_tabs', $wcusage_page_load );
    // Custom Hook
}

// Define tab content function
function wcusage_portal_tab_content(
    $postid,
    $coupon_code,
    $combined_commission,
    $wcusage_page_load,
    $force_refresh_stats,
    $is_mla_parent
) {
    ?>
    <div id="wcu1" class="portal-tabcontent">
        <?php 
    do_action(
        'wcusage_hook_dashboard_tab_content_statistics',
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load,
        $force_refresh_stats
    );
    ?>
    </div>
    <div id="wcu2" class="portal-tabcontent">
        <?php 
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
        do_action(
            'wcusage_hook_dashboard_tab_content_monthly_summary',
            $postid,
            $coupon_code,
            $combined_commission,
            $wcusage_page_load,
            $force_refresh_stats
        );
    }
    ?>
    </div>
    <div id="wcu3" class="portal-tabcontent">
        <?php 
    do_action(
        'wcusage_hook_dashboard_tab_content_latest_orders',
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load,
        $force_refresh_stats
    );
    ?>
    </div>
    <div id="wcu4" class="portal-tabcontent">
        <?php 
    do_action(
        'wcusage_hook_dashboard_tab_content_referral_url_stats',
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load,
        $force_refresh_stats
    );
    ?>
    </div>
    <div id="wcu7" class="portal-tabcontent">
        <?php 
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
        do_action(
            'wcusage_hook_dashboard_tab_content_creatives',
            $postid,
            $coupon_code,
            $combined_commission,
            $wcusage_page_load,
            $force_refresh_stats
        );
    }
    ?>
    </div>
    <div id="wcu5" class="portal-tabcontent">
        <?php 
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && (!$is_mla_parent || wcusage_check_admin_access()) ) {
        do_action(
            'wcusage_hook_dashboard_tab_content_payout',
            $postid,
            $coupon_code,
            $combined_commission,
            $wcusage_page_load,
            $force_refresh_stats,
            ''
        );
    }
    ?>
    </div>
    <div id="wcu-rates" class="portal-tabcontent">
        <?php 
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() ) {
        do_action(
            'wcusage_hook_dashboard_tab_content_rates',
            $postid,
            $coupon_code,
            $wcusage_page_load,
            $force_refresh_stats
        );
    }
    ?>
    </div>
    <div id="wcubonuses" class="portal-tabcontent">
        <?php 
    if ( wcu_fs()->is__premium_only() && wcu_fs()->can_use_premium_code() && wcusage_get_setting_value( 'wcusage_field_bonuses_tab_enable', '1' ) ) {
        do_action(
            'wcusage_hook_dashboard_tab_content_bonuses',
            $postid,
            $coupon_code,
            $wcusage_page_load,
            $force_refresh_stats
        );
    }
    ?>
    </div>
    <div id="wcu6" class="portal-tabcontent">
        <?php 
    if ( is_user_logged_in() && (!$is_mla_parent || wcusage_check_admin_access()) ) {
        $couponinfo = wcusage_get_coupon_info_by_id( $postid );
        $coupon_user_id = $couponinfo[1];
        do_action(
            'wcusage_hook_dashboard_tab_content_settings',
            $postid,
            $coupon_code,
            $combined_commission,
            $wcusage_page_load,
            $coupon_user_id,
            $force_refresh_stats,
            ''
        );
    }
    ?>
    </div>
    <?php 
    do_action(
        'wcusage_hook_dashboard_tab_content_custom',
        $postid,
        $coupon_code,
        $combined_commission,
        $wcusage_page_load,
        1
    );
}
