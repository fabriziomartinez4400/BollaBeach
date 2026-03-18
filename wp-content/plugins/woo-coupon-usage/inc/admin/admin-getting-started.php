<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wcusage_hook_getting_started_create', 'wcusage_getting_started_create' );
function wcusage_getting_started_create() {

    if ( isset( $_POST['submitnewpage'] ) || isset( $_POST['submitnewpage2'] ) || isset( $_GET['create_new_dashboard'] ) ) {

    $current_user_id = get_current_user_id();

    global $wpdb;
		$table_name = $wpdb->prefix . 'posts';
		$wpdb->insert(
			$table_name,
			array(
        'post_title'     => 'Affiliate Dashboard',
        'post_type'      => 'page',
        'post_name'      => 'affiliates',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_content'   => '[couponaffiliates]',
        'post_status'    => 'publish',
        'post_author'    => $current_user_id,
			)
		);
		$new_page_id = $wpdb->insert_id;

    if( isset( $_POST['submitnewpage'] ) ) {
      echo "<form>";

      echo "<strong style='font-size: 1.3em;'>Affiliate dashboard page created. (ID #" . esc_html($new_page_id) . ")</strong>";

  		echo "<br/><br/>- You can now view a full list of affiliate dashboard URL's for each coupon on the <a href='".esc_url(admin_url("admin.php?page=wcusage_coupons"))."' target='_blank'>coupons list</a> page.";

      echo "<br/><br/>- You can assign users to coupons by going to the <a href='".esc_url(admin_url("admin.php?page=wcusage_coupons"))."' target='_blank'>coupons list</a> page, edit a coupon, and go to the 'coupon affiliates' tab.";

      echo "<br/><br/>- Make sure to customise the plugin, and set your commission rates etc, in the <a href='".esc_url(get_admin_url())."admin.php?page=wcusage_settings'>plugin settings</a>.";

      echo "<br/><br/>If you need help with anything at all, please <a href='".esc_url(admin_url("admin.php?page=wcusage-contact"))."' target='_blank'>contact us</a> or check out our <a href='https://couponaffiliates.com/docs/setup-guide-free' target='_blank'>setup guide</a>";

      echo "<style>.wcusage-get-started { display: none; }</style>";

      echo "</form>";
    } else {

      echo "<p style='color: green;'><strong>Affiliate dashboard page created. (ID #" . esc_html($new_page_id) . ")</strong></p><br/>";

    }

    $option_group = get_option('wcusage_options');
    $option_group['wcusage_dashboard_page'] = $new_page_id;
    update_option( 'wcusage_options', $option_group );

    }

    // Remove GET variable from URL
    if(isset($_GET['create_new_dashboard'])) {
      $url = remove_query_arg('create_new_dashboard');
      wp_safe_redirect($url);
      exit;
    }

}

add_action( 'wcusage_hook_getting_started_registration_post', 'wcusage_getting_started_registration' );
function wcusage_getting_started_registration() {

    $registration_shortcode_page = wcusage_get_registration_shortcode_page_id();
    if(!$registration_shortcode_page) {

      if ( isset( $_GET['action'] ) ) {

        $current_user_id = get_current_user_id();

        global $wpdb;
    		$table_name = $wpdb->prefix . 'posts';
    		$wpdb->insert(
    			$table_name,
    			array(
            'post_title'     => 'Affiliate Registration',
            'post_type'      => 'page',
            'post_name'      => 'affiliate-registration',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_content'   => '[couponaffiliates-register]',
            'post_status'    => 'publish',
            'post_author'    => $current_user_id,
    			)
    		);
    		$new_page_id = $wpdb->insert_id;

        if(isset($_GET['action']) && $_GET['action'] == "generate" && $new_page_id) {

          echo "<p style='color: green;'><strong>Registration form page created. (ID #" . esc_html($new_page_id) . ")</strong></p>";

        }

        $option_group = get_option('wcusage_options');
        $option_group['wcusage_registration_page'] = $new_page_id;
        update_option( 'wcusage_options', $option_group );

      }

    }

}

add_action( 'wcusage_hook_getting_started', 'wcusage_getting_started' );
function wcusage_getting_started() {
?>
	<form method="post" class="wcusage-get-started" action="" style="margin-top: 35px;">

    <div style="height: 220px; width: 55%; display: inline-block;">
  		<strong style="font-size: 1.3em;">Getting Started</strong>
  		<p>
  		<?php echo esc_html__( "(1) Add the shortcode [couponaffiliates] to a frontend page (click the button below to generate automatically).", "woo-coupon-usage" ); ?>
  		</p>
  		<p>
  		<?php echo esc_html__( "(2) As an admin, you will see a full list of affiliate coupons and dashboard links on the", "woo-coupon-usage" ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" target="_blank"><?php echo esc_html__( "WooCommerce coupons list", "woo-coupon-usage" ); ?></a>.
      </p>
      <p>
      <?php echo esc_html__( "(3) You can assign/link users to coupons so they can access their coupons affiliate dashboard directly, without needing the unique link.", "woo-coupon-usage" ); ?> (<a href="https://couponaffiliates.com/docs/assign-users-to-coupons" target="_blank"><?php echo esc_html__( "Learn More", "woo-coupon-usage" ); ?></a>)
  		</p>
  		<p>
  		<?php echo esc_html__( "(4) Customise the settings on this page below, and view the 'How To Use' tab for more help on getting started.", "woo-coupon-usage" ); ?>
  		</p>
      <p>
  		<?php echo esc_html__( "(5) To view our step-by-step setup guide", "woo-coupon-usage" ); ?> <a href='https://couponaffiliates.com/docs/setup-guide-free' target='_blank'><?php echo esc_html__( "click here", "woo-coupon-usage" ); ?></a>.
  		</p>

      <p style="margin-bottom: -15px;">
      <button type="submit" name="submitnewpage" class="button button-secondary" style="display: inline-block;">
        <?php echo esc_html__( "Generate Dashboard Page", "woo-coupon-usage" ); ?> <span class="fa-solid fa-circle-arrow-right"></span>
      </button>
      </p>
      <br/>
      <div style="clear: both;"></div>

    </div>

	</form>
<?php
}

add_action( 'wcusage_hook_getting_started2', 'wcusage_getting_started2' );
function wcusage_getting_started2() {
?>

<h2 style="font-size: 20px; margin: 8px 0 35px -12px;"> <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_setup')); ?>" class="wcusage-settings-button">Start Setup Wizard <span class="fa-solid fa-circle-arrow-right"></span></a></h2>

	<form method="post" class="wcusage-get-started" action="">

    <p><strong>Quick Guide:</strong></p>

		<p>
		<?php echo esc_html__( "(1) Add the shortcode [couponaffiliates] to a frontend page (click the button below to generate automatically).", "woo-coupon-usage" ); ?>
		</p>

		<p>
		<?php echo esc_html__( "(2) As an admin, you will see a full list of dashboard URLs to share with affiliates on the", "woo-coupon-usage" ); ?> <a href="<?php echo esc_url(admin_url("admin.php?page=wcusage_coupons")); ?>" target="_blank"><?php echo esc_html__( "WooCommerce coupons list", "woo-coupon-usage" ); ?></a>.
    </p>

    <p>
    <?php echo esc_html__( "(3) You can assign/link users to coupons so they can access their coupons affiliate dashboard directly, without needing the unique link.", "woo-coupon-usage" ); ?> (<a href="https://couponaffiliates.com/docs/assign-users-to-coupons" target="_blank"><?php echo esc_html__( "Learn More", "woo-coupon-usage" ); ?></a>)
		</p>

		<p>
		<?php echo esc_html__( "(4) Customise the plugin on the", "woo-coupon-usage" ); ?> <a href="<?php echo esc_url(get_admin_url("admin.php?page=wcusage_settings")); ?>" target="_blank"><?php echo esc_html__( "settings page", "woo-coupon-usage" ); ?></a>.
		</p>

    <p>
		<?php echo esc_html__( "(5) To view our plugin setup guide", "woo-coupon-usage" ); ?> <a href="https://couponaffiliates.com/docs/setup-guide-free" target="_blank"><?php echo esc_html__( "click here", "woo-coupon-usage" ); ?></a>.
		</p>

		<button type="button" name="submitnewpage" class="button button-secondary">
      <?php echo esc_html__( "Generate Dashboard Page", "woo-coupon-usage" ); ?> <span class="fa-solid fa-circle-arrow-right"></span>
    </button>

		<br/><br/>

	</form>
<?php
}

add_action( 'wcusage_hook_getting_started3', 'wcusage_getting_started3' );
function wcusage_getting_started3() {
?>
	<form method="post" action="" style="margin-bottom: 10px;">
		<button type="submit" name="submitnewpage2" class="submit-generate-page"><?php echo esc_html__( "Generate Dashboard Page", "woo-coupon-usage" ); ?> <span class="fa-solid fa-arrow-right"></span></button>
	</form>
<?php
}

add_action( 'wcusage_hook_getting_started_registration', 'wcusage_getting_started_registration_post' );
function wcusage_getting_started_registration_post() {
?>
  <a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_setup&step=2&action=generate')); ?>">
      <button type="button" class="submit-generate-page"><?php echo esc_html__( "Generate Registration Page", "woo-coupon-usage" ); ?> <span class="fa-solid fa-arrow-right"></span></button>
  </a>
  <br/>
<?php
}

add_action( 'wcusage_hook_checklist', 'wcusage_hook_checklist_function' );
function wcusage_hook_checklist_function() {
?>
  <div class="wcusage-checklist" style="display: none; border: 1px solid #ccc;
  min-width: 400px; padding: 5px 20px 10px 20px; background: #f9f9f9; border-radius: 5px; margin: 20px 0 5px 0;">
      <h2 style="font-size: 20px; margin-top: 15px;"><?php echo esc_html__( "Setup Checklist", "woo-coupon-usage" ); ?></h2>
      <?php
      $show = false;

      // 1 - Create a dashboard page
      $wcusage_dashboard_page = wcusage_get_setting_value('wcusage_dashboard_page', '');
      $wcusage_dashboard_page = get_post($wcusage_dashboard_page);
      if($wcusage_dashboard_page && $wcusage_dashboard_page->post_status == 'publish') {
        $wcusage_dashboard_page = $wcusage_dashboard_page->ID;
      } else {
        $wcusage_dashboard_page = false;
        $options_group = get_option('wcusage_options');
        if(isset($options_group['wcusage_dashboard_page'])) {
          $options_group['wcusage_dashboard_page'] = '';
          update_option( 'wcusage_options', $options_group );
        }
      }
      $wcusage_field_portal_enable = wcusage_get_setting_value('wcusage_field_portal_enable', '0');
      if(!$wcusage_dashboard_page && !$wcusage_field_portal_enable) {
        echo "<p style='margin-top: 20px;'>";
        echo "<span class='fa-solid fa-exclamation-circle' style='color: red;'></span> ";
        echo "<strong>" . esc_html__( "Create the affiliate dashboard page:", "woo-coupon-usage" ) . "</strong>";
        echo sprintf( " <a href='%s'>%s</a>", esc_url(admin_url('admin.php?page=wcusage_setup&step=1')), esc_html__( "Click here", "woo-coupon-usage" ) );
        echo "</p>";
        $show = true;
      } else {
        echo "<p style='margin-top: 20px;'>";
        echo "<span class='fa-solid fa-check-circle' style='color: green;'></span> ";
        echo "<strong>" . esc_html__( "Affiliate dashboard page created.", "woo-coupon-usage" ) . "</strong>";
        echo "</p>";
      }

      $wcusage_field_registration_enable = wcusage_get_setting_value('wcusage_field_registration_enable', '1');

      if($wcusage_field_registration_enable) {

        // 2 - Create a registration page
        $wcusage_registration_page = wcusage_get_setting_value('wcusage_registration_page', '');
        if(!$wcusage_registration_page) {
          echo "<p style='margin-top: 20px;' class='wcusage-checklist-registration'>";
          echo "<span class='fa-solid fa-exclamation-circle' style='color: red;'></span> ";
          echo "<strong>" . esc_html__( "Create the affiliate registration page:", "woo-coupon-usage" ) . "</strong>";
          echo sprintf( " <a href='%s'>%s</a>", esc_url(admin_url('admin.php?page=wcusage_setup&step=2')), esc_html__( "Click here", "woo-coupon-usage" ) );
          echo "</p>";
          $show = true;
        } else {
          echo "<p style='margin-top: 20px;' class='wcusage-checklist-registration'>";
          echo "<span class='fa-solid fa-check-circle' style='color: green;'></span> ";
          echo "<strong>" . esc_html__( "Affiliate registration page created.", "woo-coupon-usage" ) . "</strong>";
          echo "</p>";
        }

        // 3 - Create template coupon
        $coupon_template = wcusage_get_setting_value('wcusage_field_registration_coupon_template', '0');
        $couponid = wcusage_get_coupon_id($coupon_template);
        if(!$couponid) {
          echo "<p style='margin-top: 20px;'>";
          echo "<span class='fa-solid fa-exclamation-circle' style='color: red;'></span> ";
          echo "<strong>" . esc_html__( "Create the template coupon:", "woo-coupon-usage" ) . "</strong>";
          echo sprintf( " <a href='%s'>%s</a>", esc_url(admin_url('admin.php?page=wcusage_setup&step=2')), esc_html__( "Click here", "woo-coupon-usage" ) );
          echo "</p>";
          $show = true;
        } else {
          echo "<p style='margin-top: 20px;'>";
          echo "<span class='fa-solid fa-check-circle' style='color: green;'></span> ";
          echo "<strong>" . esc_html__( "Template coupon created.", "woo-coupon-usage" ) . "</strong>";
          echo "</p>";
        }

      }

      // Show?
      if($show) {
        echo "<style>.wcusage-checklist { display: inline-block !important; }</style>";
      }
      ?>
  </div>
<?php
}