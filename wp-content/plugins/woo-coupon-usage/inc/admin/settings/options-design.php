<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcusage_field_cb_design( $args )
{
    $options = get_option( 'wcusage_options' );
    ?>

	<div id="design-settings" class="settings-area">

	<h1><?php echo esc_html__( 'Design & Layout Customisation', 'woo-coupon-usage' ); ?></h1>

  <hr/>

  <!-- Custom "Affiliate" terminology. -->
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Custom Terminology', 'woo-coupon-usage' ); ?></h3>

  <p>
    <div class="wcu-terminology">
    <?php wcusage_setting_text_option('wcusage_field_custom_affiliate_text', '', esc_html__( 'Custom "Affiliate" terminology:', 'woo-coupon-usage' ), '0px'); ?>
    </div>
    <div class="wcu-terminology">
    <?php wcusage_setting_text_option('wcusage_field_custom_affiliates_text', '', esc_html__( 'Custom "Affiliates" terminology:', 'woo-coupon-usage' ), '0px'); ?>
    </div>
    <script>
    /* Only enable the "affiliates" text field if the "affiliate" text field is not empty */
    jQuery(document).ready(function($) {
      var affiliateText = $(this).val();
      if (affiliateText !== '') {
        $('#wcusage_field_custom_affiliates_text').prop('disabled', false);
      } else {
        $('#wcusage_field_custom_affiliates_text').prop('disabled', true);
      }
      $('#wcusage_field_custom_affiliate_text').on('input', function() {
        var affiliateText = $(this).val();
        if (affiliateText !== '') {
          $('#wcusage_field_custom_affiliates_text').prop('disabled', false);
        } else {
          $('#wcusage_field_custom_affiliates_text').prop('disabled', true);
        }
      });
    });
    </script>
    <div style="clear: both;"></div>
    <i><?php echo esc_html__( 'If you want to change the word "Affiliate" to something else, enter it here. This will change it everywhere in the plugin except the settings page.', 'woo-coupon-usage' ); ?></i><br/>
    <i><?php echo esc_html__( 'For example, if you want to change it to "Partner", you would enter "Partner" here. It will then change things like "Affiliate Dashboard" to "Partner Dashboard", "Affiliate Registration" to "Partner Registration", etc.', 'woo-coupon-usage' ); ?></i>
  </p>
  <script>
  /* Set a placeholder if empty */
  jQuery(document).ready(function($) {
    var customAffiliateText = $('#wcusage_field_custom_affiliate_text').val();
    if (customAffiliateText === '') {
      $('#wcusage_field_custom_affiliate_text').attr('placeholder', '<?php echo esc_html__( 'Affiliate', 'woo-coupon-usage' ); ?>');
    }
    var customAffiliatesText = $('#wcusage_field_custom_affiliates_text').val();
    if (customAffiliatesText === '') {
      $('#wcusage_field_custom_affiliates_text').attr('placeholder', '<?php echo esc_html__( 'Affiliates', 'woo-coupon-usage' ); ?>');
    }
  });
  </script>

  <br/><hr/>

  <?php $wcusage_field_show_tabs = wcusage_get_setting_value('wcusage_field_show_tabs', '1');
  if(!$wcusage_field_show_tabs) { ?>
  <!-- Enable "tabbed" layout - Discontinued but option hidden if turned off -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_tabs', 1, esc_html__( 'Enable "tabbed" layout (recommended).', 'woo-coupon-usage' ), '0px'); ?>
	<br/>
  <?php } ?>

  <div class="affiliate-dashboard-page-settings">

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Affiliate Dashboard Tabs', 'woo-coupon-usage' ); ?></h3>

  <!-- Tabs Style -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">
  <p>
    <?php $wcusage_field_tabs_style = wcusage_get_setting_value('wcusage_field_tabs_style', '2'); ?>
    <input type="hidden" value="0" id="wcusage_field_tabs_style" data-custom="custom" name="wcusage_options[wcusage_field_tabs_style]" >
    <strong><label for="scales"><?php echo esc_html__( 'Tabs Style', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <select name="wcusage_options[wcusage_field_tabs_style]" id="wcusage_field_tabs_style" class="wcusage_field_tabs_style">
      <option value="1" <?php if($wcusage_field_tabs_style == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'Style #1 - Basic (Legacy)', 'woo-coupon-usage' ); ?></option>
      <option value="2" <?php if($wcusage_field_tabs_style == "2") { ?>selected<?php } ?>><?php echo esc_html__( 'Style #2 - Full Width (Modern)', 'woo-coupon-usage' ); ?></option>
    </select>
  </p>
  </div>

  <!-- Border Radius -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">
  <p>
    <?php $wcusage_field_tabs_border = wcusage_get_setting_value('wcusage_field_tabs_border', '1'); ?>
    <input type="hidden" value="0" id="wcusage_field_tabs_border" data-custom="custom" name="wcusage_options[wcusage_field_tabs_border]" >
    <strong><label for="scales"><?php echo esc_html__( 'Tabs Border Radius', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <select name="wcusage_options[wcusage_field_tabs_border]" id="wcusage_field_tabs_border" class="wcusage_field_tabs_border">
      <option value="1" <?php if($wcusage_field_tabs_border == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'Curved (5px)', 'woo-coupon-usage' ); ?></option>
      <option value="2" <?php if($wcusage_field_tabs_border == "2") { ?>selected<?php } ?>><?php echo esc_html__( 'Rounded (25px)', 'woo-coupon-usage' ); ?></option>
      <option value="3" <?php if($wcusage_field_tabs_border == "3") { ?>selected<?php } ?>><?php echo esc_html__( 'Square (0px)', 'woo-coupon-usage' ); ?></option>
    </select>
  </p>
  </div>

  <!-- Padding -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">
  <p>
    <?php $wcusage_field_tabs_padding = wcusage_get_setting_value('wcusage_field_tabs_padding', '1'); ?>
    <input type="hidden" value="0" id="wcusage_field_tabs_padding" data-custom="custom" name="wcusage_options[wcusage_field_tabs_padding]" >
    <strong><label for="scales"><?php echo esc_html__( 'Tabs Size / Padding', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <select name="wcusage_options[wcusage_field_tabs_padding]" id="wcusage_field_tabs_padding" class="wcusage_field_tabs_padding">
      <option value="1" <?php if($wcusage_field_tabs_padding == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'Small (8px)', 'woo-coupon-usage' ); ?></option>
      <option value="2" <?php if($wcusage_field_tabs_padding == "2") { ?>selected<?php } ?>><?php echo esc_html__( 'Medium (10px)', 'woo-coupon-usage' ); ?></option>
      <option value="3" <?php if($wcusage_field_tabs_padding == "3") { ?>selected<?php } ?>><?php echo esc_html__( 'Large (12px)', 'woo-coupon-usage' ); ?></option>
    </select>
  </p>
  </div>

  <!-- Font Size -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">
  <p>
    <?php $wcusage_field_tabs_font_size = wcusage_get_setting_value('wcusage_field_tabs_font_size', ''); ?>
    <input type="hidden" value="0" id="wcusage_field_tabs_font_size" data-custom="custom" name="wcusage_options[wcusage_field_tabs_font_size]" >
    <strong><label for="scales"><?php echo esc_html__( 'Tabs Font Size', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <input type="number" name="wcusage_options[wcusage_field_tabs_font_size]" id="wcusage_field_tabs_font_size"
    value="<?php echo esc_html($wcusage_field_tabs_font_size); ?>" placeholder="Default"
    min="10" max="30"
    class="wcusage_field_tabs_font_size" />
  </p>
  </div>

  <div style="clear: both;"></div>

  <br/>

  </div>
  
  <span class="affiliate-dashboard-page-settings">
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Tab Colours', 'woo-coupon-usage' ); ?></h3>
  </span>

  <span class="wcu-field-section-portal">
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Portal Colours', 'woo-coupon-usage' ); ?></h3>
  </span>

  <!-- Tabs -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">
    <span class="affiliate-dashboard-page-settings">
    <h3><?php echo esc_html__( 'Tabs', 'woo-coupon-usage' ); ?></h3>
    </span>

    <span class="wcu-field-section-portal">
    <h3><?php echo esc_html__( 'Sidebar Background', 'woo-coupon-usage' ); ?></h3>
    </span>

    <!-- Background -->
    <?php wcusage_setting_color_option('wcusage_field_color_tab', '#1b3e47', esc_html__( 'Background', 'woo-coupon-usage' ), '0px'); ?>

    <!-- Text -->
    <?php wcusage_setting_color_option('wcusage_field_color_tab_font', '#ffffff', esc_html__( 'Text', 'woo-coupon-usage' ), '0px'); ?>

  </div>

  <!-- Tabs Hover -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <span class="affiliate-dashboard-page-settings">
    <h3><?php echo esc_html__( 'Tabs Hover', 'woo-coupon-usage' ); ?></h3>
    </span>

    <span class="wcu-field-section-portal">
    <h3><?php echo esc_html__( 'Sidebar Link Hover', 'woo-coupon-usage' ); ?></h3>
    </span>

    <!-- Background -->
    <?php wcusage_setting_color_option('wcusage_field_color_tab_hover', '#005d75', esc_html__( 'Background', 'woo-coupon-usage' ), '0px'); ?>

    <!-- Text -->
    <?php wcusage_setting_color_option('wcusage_field_color_tab_hover_font', '#ffffff', esc_html__( 'Text', 'woo-coupon-usage' ), '0px'); ?>

  </div>
  <div style="clear: both;"></div>

  <br/><hr/>

  <?php do_action('wcusage_hook_setting_section_colours'); ?>

	<br/><hr/>

  <span class="wcu-field-section-portal">
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Portal Fonts', 'woo-coupon-usage' ); ?></h3>
  </span>  

  <!-- Portal Font (own section) -->
  <div class="wcusage-settings-style-colors wcu-field-section-portal" style="margin-bottom: 0;" id="affiliate-portal-font">
    <?php
      $saved_font = wcusage_get_setting_value('wcusage_portal_font_family', '');

      // WordPress Font Library fonts
      $wp_font_options = array();
      if ( function_exists('wp_get_global_settings') ) {
          $typography_fonts = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
          if ( is_array( $typography_fonts ) ) {
              foreach ( $typography_fonts as $group_fonts ) {
                  if ( is_array( $group_fonts ) ) {
                      foreach ( $group_fonts as $font ) {
                          $name = isset($font['name']) ? $font['name'] : ( isset($font['slug']) ? $font['slug'] : '' );
                          $family = isset($font['fontFamily']) ? ( is_array($font['fontFamily']) ? implode(', ', $font['fontFamily']) : $font['fontFamily'] ) : '';
                          if ( $name && $family ) {
                              $wp_font_options[$name] = $family;
                          }
                      }
                  }
              }
          }
      }

      // Built-in safe stacks
      $builtin_font_options = array(
        esc_html__( 'System Default', 'woo-coupon-usage' ) => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif',
        'Inter' => '"Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'Roboto' => '"Roboto", "Helvetica Neue", Arial, sans-serif',
        'Open Sans' => '"Open Sans", Arial, sans-serif',
        'Lato' => '"Lato", Arial, sans-serif',
        'Montserrat' => '"Montserrat", Arial, sans-serif',
        'Poppins' => '"Poppins", Arial, sans-serif',
        'Nunito' => '"Nunito", Arial, sans-serif',
        'Source Sans Pro' => '"Source Sans Pro", Arial, sans-serif',
        'Work Sans' => '"Work Sans", Arial, sans-serif',
        'Helvetica Neue' => '"Helvetica Neue", Arial, sans-serif',
        'Arial' => 'Arial, "Helvetica Neue", sans-serif',
        'Georgia' => 'Georgia, "Times New Roman", Times, serif',
        'Merriweather' => '"Merriweather", Georgia, serif',
      );
    ?>
    <h3><?php echo esc_html__( 'Primary Font', 'woo-coupon-usage' ); ?></h3>
    <p>
      <select id="wcusage_portal_font_family" name="wcusage_options['wcusage_portal_font_family']" style="min-width: 100%;max-width: 100%;">
        <option value="" <?php selected( $saved_font, '' ); ?>><?php echo esc_html__( 'Default (inherit browser/system)', 'woo-coupon-usage' ); ?></option>
        <?php if ( !empty( $wp_font_options ) ) : ?>
        <optgroup label="<?php echo esc_attr__( 'WordPress Font Library', 'woo-coupon-usage' ); ?>">
          <?php foreach ( $wp_font_options as $label => $value ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $saved_font, $value ); ?>><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </optgroup>
        <?php endif; ?>
        <optgroup label="<?php echo esc_attr__( 'Built-in Safe Fonts', 'woo-coupon-usage' ); ?>">
          <?php foreach ( $builtin_font_options as $label => $value ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $saved_font, $value ); ?>><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </optgroup>
      </select>
      <i><?php echo wp_kses_post( sprintf( __( 'Applies to all text in the Affiliate Portal. Fonts available from the <a href="%s" target="_blank">WordPress Font Library</a>.', 'woo-coupon-usage' ), esc_url( admin_url( 'customize.php?autofocus[section]=wcusage_font_options' ) ) ) ); ?></i>
    </p>

  </div>

  <div style="clear: both;"></div>

  <div class="affiliate-dashboard-page-settings">
  
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Dark Mode', 'woo-coupon-usage' ); ?></h3>

  <!-- Enable Dark Mode -->
  <?php wcusage_setting_toggle_option('wcusage_field_dark_mode_enable', 0, esc_html__( 'Enable Dark Mode', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'When enabled, dark mode styling will be available for the affiliate dashboard.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <div id="wcusage_field_dark_mode_options" style="display: none;">
    
    <!-- Display Dark Mode Toggle -->
    <?php wcusage_setting_toggle_option('wcusage_field_dark_mode_toggle', 1, esc_html__( 'Display Dark Mode Toggle', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'Show a toggle switch on the affiliate dashboard that allows users to switch between light and dark mode.', 'woo-coupon-usage' ); ?></i><br/>

    <br/>

    <!-- Enable Dark Mode as Default -->
    <?php wcusage_setting_toggle_option('wcusage_field_dark_mode_default', 0, esc_html__( 'Enable Dark Mode as Default', 'woo-coupon-usage' ), '0px'); ?>
    <i><?php echo esc_html__( 'When enabled, dark mode will be the default theme. Users can still toggle to light mode if the toggle is enabled.', 'woo-coupon-usage' ); ?></i><br/>

  </div>

  <script>
  jQuery(document).ready(function($) {
    // Show/hide dark mode options based on enable toggle
    function toggleDarkModeOptions() {
      if ($('.wcusage_field_dark_mode_enable').is(':checked')) {
        $('#wcusage_field_dark_mode_options').slideDown();
      } else {
        $('#wcusage_field_dark_mode_options').slideUp();
      }
    }
    
    toggleDarkModeOptions();
    $('.wcusage_field_dark_mode_enable').on('change', toggleDarkModeOptions);
  });
  </script>

  </div>
  
  <br/><hr/>
  
  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Registration & Login Form', 'woo-coupon-usage' ); ?></h3>

  <p>
    <?php $wcusage_field_form_style = wcusage_get_setting_value('wcusage_field_form_style', '1'); ?>
    <input type="hidden" value="0" id="wcusage_field_form_style" data-custom="custom" name="wcusage_options[wcusage_field_form_style]" >
    <strong><label for="scales"><?php echo esc_html__( 'Form Style', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <select name="wcusage_options[wcusage_field_form_style]" id="wcusage_field_form_style" class="wcusage_field_form_style">
      <option value="1" <?php if($wcusage_field_form_style == "1") { ?>selected<?php } ?>><?php echo esc_html__( 'Style #1 - Default', 'woo-coupon-usage' ); ?></option>
      <option value="2" <?php if($wcusage_field_form_style == "2") { ?>selected<?php } ?>><?php echo esc_html__( 'Style #2 - Modern (Bold)', 'woo-coupon-usage' ); ?></option>
      <option value="3" <?php if($wcusage_field_form_style == "3") { ?>selected<?php } ?>><?php echo esc_html__( 'Style #3 - Modern (Compact)', 'woo-coupon-usage' ); ?></option>
    </select>
  </p>

  <br/>

  <!-- Use the email address as username. -->
  <?php wcusage_setting_toggle_option('wcusage_field_form_style_columns', 1, esc_html__( 'Enable 2 Column Layout', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'With this enabled, some of the fields on the form will be displayed in 2 columns, such as first and last name.', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <!-- Form Title -->
  <?php wcusage_setting_text_option('wcusage_field_registration_form_title', '', esc_html__( 'Custom Registration Form Title', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'Default', 'woo-coupon-usage' ); ?>: <?php echo esc_html__( 'Register New Affiliate Account', 'woo-coupon-usage' ); ?></i><br/>

  <br/>

  <!-- Submit button text field label -->
  <?php wcusage_setting_text_option('wcusage_field_registration_submit_button_text', '', esc_html__( 'Custom Submit Button Text', 'woo-coupon-usage' ), '0px'); ?>
  <i><?php echo esc_html__( 'Default', 'woo-coupon-usage' ); ?>: <?php echo esc_html__( 'Submit Application', 'woo-coupon-usage' ); ?></i><br/>

	<br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Mobile Menu', 'woo-coupon-usage' ); ?></h3>

  <p>
    <?php $wcusage_field_mobile_menu = wcusage_get_setting_value('wcusage_field_mobile_menu', 'dropdown'); ?>
    <input type="hidden" value="0" id="wcusage_field_mobile_menu" data-custom="custom" name="wcusage_options[wcusage_field_mobile_menu]" >
    <strong><label for="scales"><?php echo esc_html__( 'Mobile Menu Style', 'woo-coupon-usage' ); ?>:</label></strong><br/>
    <select name="wcusage_options[wcusage_field_mobile_menu]" id="wcusage_field_mobile_menu" class="wcusage_field_mobile_menu">
      <option value="dropdown" <?php if($wcusage_field_mobile_menu == "dropdown") { ?>selected<?php } ?>><?php echo esc_html__( 'Dropdown', 'woo-coupon-usage' ); ?></option>
      <option value="tabs" <?php if($wcusage_field_mobile_menu == "tabs") { ?>selected<?php } ?>><?php echo esc_html__( 'Tabs', 'woo-coupon-usage' ); ?></option>
    </select>
  </p>

  <br/>
  <hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Custom CSS', 'woo-coupon-usage' ); ?></h3>

  <?php wcusage_setting_textarea_option('wcusage_field_custom_dashboard_css', '', esc_html__( 'Custom Dashboard CSS', 'woo-coupon-usage' ), '0px'); ?>
  
  <?php
  // Load WP code editor (CodeMirror) assets and initialize on this textarea
  if ( function_exists('wp_enqueue_code_editor') ) {
      $wcu_css_editor_settings = wp_enqueue_code_editor( array(
        'type' => 'text/css',
        'codemirror' => array(
          'mode' => 'css',
          'lint' => true,
          'gutters' => array('CodeMirror-lint-markers'),
          'lineNumbers' => true,
          'styleActiveLine' => true,
          'matchBrackets' => true
        )
      ) );
  }
  ?>
  <style>
    /* Make the textarea/editor reasonably sized */
    #wcusage_field_custom_dashboard_css { width: 100%; min-height: 220px; }
    .CodeMirror { border: 1px solid #ccd0d4; min-height: 220px; }
  </style>
  <script>
  jQuery(function(){
    if ( window.wp && wp.codeEditor && document.getElementById('wcusage_field_custom_dashboard_css') ) {
      try {
        var settings = <?php echo isset($wcu_css_editor_settings) && $wcu_css_editor_settings ? wp_json_encode( $wcu_css_editor_settings ) : '{}'; ?>;
        wp.codeEditor.initialize( 'wcusage_field_custom_dashboard_css', settings );
      } catch(e) {
        // Fallback silently to plain textarea
      }
    }
  });
  </script>

	</div>

 <?php
}

/**
 * Settings Section: Colours
 *
 */
add_action( 'wcusage_hook_setting_section_colours', 'wcusage_setting_section_colours', 10, 1 );
if( !function_exists( 'wcusage_setting_section_colours' ) ) {
  function wcusage_setting_section_colours() {

  $options = get_option( 'wcusage_options' );
  ?>

  <style>
  .wcusage-settings-style-colors {
      width: calc(50% - 20px);
      max-width: 290px;
      float: left;
      margin-right: 20px;
      margin-bottom: 40px;
  }
  .wcusage-settings-style-colors h3 {
    margin-top: 0;
    margin-bottom: 10px;
  }
  </style>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Other Colours', 'woo-coupon-usage' ); ?></h3>

  <!-- Table Header & Footer -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <h3><?php echo esc_html__( 'Table Header & Footer', 'woo-coupon-usage' ); ?></h3>

    <!-- Background -->
    <?php wcusage_setting_color_option('wcusage_field_color_table', '#f4f4f4', esc_html__( 'Background', 'woo-coupon-usage' ), '0px'); ?>

    <!-- Text -->
    <?php wcusage_setting_color_option('wcusage_field_color_table_font', '#0a0a0a', esc_html__( 'Text', 'woo-coupon-usage' ), '0px'); ?>

  </div>

  <!-- Buttons -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <h3><?php echo esc_html__( 'Buttons', 'woo-coupon-usage' ); ?></h3>

    <!-- Background -->
    <?php wcusage_setting_color_option('wcusage_field_color_button', '#005d75', esc_html__( 'Background', 'woo-coupon-usage' ), '0px'); ?>

    <!-- Text -->
    <?php wcusage_setting_color_option('wcusage_field_color_button_font', '#ffffff', esc_html__( 'Text', 'woo-coupon-usage' ), '0px'); ?>

  </div>

  <!-- Buttons Hover -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <h3><?php echo esc_html__( 'Buttons Hover', 'woo-coupon-usage' ); ?></h3>

    <!-- Background -->
    <?php wcusage_setting_color_option('wcusage_field_color_button_hover', '#1b3e47', esc_html__( 'Background', 'woo-coupon-usage' ), '0px'); ?>

    <!-- Text -->
    <?php wcusage_setting_color_option('wcusage_field_color_button_font_hover', '#ffffff', esc_html__( 'Text', 'woo-coupon-usage' ), '0px'); ?>

  </div>

  <div style="clear: both;"></div>

  <br/><hr/>

  <h3><span class="dashicons dashicons-admin-generic" style="margin-top: 2px;"></span> <?php echo esc_html__( 'Icons', 'woo-coupon-usage' ); ?></h3>

  <!-- Show icons on affiliate dashboard tabs -->
  <?php wcusage_setting_toggle_option('wcusage_field_show_tabs_icons', 1, esc_html__( 'Show icons on the affiliate dashboard tabs.', 'woo-coupon-usage' ), '0px'); ?>

  <div style="clear: both;"></div>

  <br/>

  <!-- Stats Icons -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <h3><?php echo esc_html__( 'Icon Colour', 'woo-coupon-usage' ); ?></h3>

    <!-- Main -->
    <?php wcusage_setting_color_option('wcusage_field_color_stats_icon', '#bebebe', '', '0px'); ?>

  </div>

  <div style="clear: both;"></div>

  <?php if( wcu_fs()->can_use_premium_code() ) { ?>
  <br/>
  <!-- Line Graph -->
  <div class="wcusage-settings-style-colors" style="margin-bottom: 0;">

    <span <?php if( !wcu_fs()->can_use_premium_code() ) { ?>style="opacity: 0.4 !important; display: block; pointer-events: none;" class="wcu-settings-pro-only"<?php } ?>>

      <h3><?php echo esc_html__( 'Line Graph', 'woo-coupon-usage' ); ?> (PRO)</h3>

      <!-- Main -->
      <?php wcusage_setting_color_option('wcusage_field_color_line_graph', '#008000', '', '0px'); ?>

    </span>

  </div>
  <div style="clear: both;"></div>
  <?php } ?>

  <?php
  }
}
