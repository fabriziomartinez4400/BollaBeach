<span id="nsc_settings_content">
  <?php if ($displayReview === true) { ?>
    <div id="nsc_bar_notice_please_rate" class="nsc_bar_notice_below_tabs">
      <table>
        <tr>
          <td>
            <img id="nsc_bar_notice_please_rate_image" width="150px"
              src="<?php echo NSC_BAR_PLUGIN_URL ?>/admin/img/rating-stars.png" />
          </td>
          <td>
            <p>Hi, I noticed you use this plugin for a while now. That's awesome! Could you please do me a big favor and
              give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.</p>
            <p target="_blank" class="nsc_bar_notice_links">
              <a id="nsc_bar_notice_link_one" target="_blank" class="button button-primary"
                href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post">Ok,
                you deserve it</a>
              <a id="nsc_bar_notice_reviewedAlready" class="nsc_bar_notice_link_two" href="#">I already did</a>
            </p>
            <p>If you think we do not deserve a 5-Star rating please contact us to give us a chance to improve, before
              rating.</p>
          </td>
        </tr>
      </table>
    </div>
  <?php }
  if ($newBannerEnabled === false) {
  ?>
    <table class="form-table nsc_bar_language">
      <tbody>
        <tr id="tr_content_language_setter">
          <th scope="row">Language</th>
          <td>
            <fieldset>
              <label><?php echo $form_fields->nsc_bar_get_language_dropdown() ?></label>
              <p class="description"><?php echo wp_kses($objSettings->addon_lang_description, $allowed_html) ?></p>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <hr>
  <?php } ?>

  <?php echo "<div class='tab_description'>" . wp_kses($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_description, $allowed_html) . "  </div>" ?>

  <?php
  if ($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_slug === "revoke_settings_tab" && defined("NSC_BARA_PLUGIN_VERSION") && version_compare(NSC_BARA_PLUGIN_VERSION, "4.0.0", "<")) {
    // introduced in v4.8.0
    echo '<div class="nsc_bar_notice_error px-3 pt-3 mt-2">
                       <p>To use this feature for Banner 2 please update to at least version 4.0.0 of the premium plugin.</p>
                    </div>';
  }

  ?>

  <form action="" method="post">
    <?php wp_nonce_field("save_cookie_settings_" . $objSettings->plugin_slug . "--" . $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_slug, 'nsc_bar_nonce'); ?>
    <input type="hidden" name="action" value="nsc_bar_cookie_settings_save" />
    <input type="hidden" name="option_page"
      value="<?php echo $objSettings->plugin_slug . $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_slug ?>" />
    <?php
    $activeInternalTab = 0;
    $display = '';
    submit_button();
    $dnone = "";
    ?>

    <?php if (empty($objSettings->setting_page_fields->tabs[$active_tab_index]->internal_tabs) === false) {
      $dnone = "d-none";
      $tabs = '<ul class="nav nav-underline">';
      foreach ($objSettings->setting_page_fields->tabs[$active_tab_index]->internal_tabs as $key => $internal_tab) {
        $active = $key === $activeInternalTab ? 'active' : '';
        $tabs .= '<li id="' . $internal_tab->id . '_tab" data-includedids="' . esc_html(implode(",", $internal_tab->fields)) . '" class="nav-item nsc_bar_internal_tab">
          <a data-includedids="' . esc_html(implode(",", $internal_tab->fields)) . '" href="#' . $internal_tab->id . '" id="' . $internal_tab->id . '_tab_link" class="nav-link ' . $active . '" aria-current="page">' . $internal_tab->name . '</a>
        </li>';
      }
      $tabs .= '</ul>';
      echo $tabs;
    }
    ?>
    <table class="form-table">
      <?php foreach ($objSettings->setting_page_fields->tabs[$active_tab_index]->tabfields as $field_configs) {
        if ($newBannerEnabled === true && isset($field_configs->newBanner) && $field_configs->newBanner === false) {
          continue;
        }

        if ($newBannerEnabled === false && isset($field_configs->newBanner) && $field_configs->newBanner === true) {
          continue;
        }
      ?>
        <?php
        $tabbedRow = "nsc_bar_non_tabbed_row";
        if (empty($objSettings->setting_page_fields->tabs[$active_tab_index]->internal_tabs) === false) {
          foreach ($objSettings->setting_page_fields->tabs[$active_tab_index]->internal_tabs as $intTab) {
            $tabbedRow = in_array($field_configs->field_slug, $intTab->fields) ? 'nsc_bar_tabbed_row' : $tabbedRow;
          }
        }
        ?>
        <tr id="tr_<?php echo esc_attr($field_configs->field_slug) ?>" class="<?php echo $tabbedRow . " " . $dnone; ?>">
          <?php $xcolspan = ' colspan="2" class="nsc_bar_show_text"';
          if ($field_configs->type !== "showtext") {
            $xcolspan = "" ?>
            <th scope="row">
              <?php echo esc_html($field_configs->name) ?>
            </th>
          <?php } ?>
          <td<?php echo $xcolspan ?>>
            <fieldset>
              <?php echo $form_fields->nsc_bar_return_form_field($field_configs, $objSettings->plugin_prefix); ?>
              <?php
              if (empty($field_configs->custom_component) === false && empty($field_configs->plugin_url) === true) {
                // introduced in v3.8.4
                echo '<div class="nsc_bar_notice_error py-4 px-3 mt-2">
                       <p>All good - your current configuration is still working smoothly!
                        However, to make any changes, you\'ll need to update to the latest version of the add-on.
                        Unfortunately, older versions of the add-on aren\'t compatible with the newest version of the main plugin.</p>
                    </div>';
              }

              if (empty($field_configs->custom_component) === false && empty($field_configs->plugin_url) === false) {
                echo '
                    <iframe width="100%"
                      id="nsc_bar_cc_' . esc_attr($field_configs->field_slug) . '"
                      src="' . $field_configs->custom_component . '">
                    </iframe>
                    <script>
                      addEventListener("load", (event) => {iFrameResize({ log: false, minHeight: 500 }, "#nsc_bar_cc_' . esc_attr($field_configs->field_slug) . '");});
                      (function(){
                          const iframe = document.getElementById("nsc_bar_cc_' . esc_attr($field_configs->field_slug) . '");
                          iframe.addEventListener("load", ()=>{
                              const config = {
                                  source: "beautiful-cookie-banner",
                                  pluginUrl: "' . esc_url($field_configs->plugin_url) . '",
                                  mainPluginUrl: "' . esc_url(NSC_BAR_PLUGIN_URL) . '",
                                  restURL:   "' . esc_url(get_rest_url()) . '",
                                  nonce:     "' . esc_js(wp_create_nonce('wp_rest')) . '"
                              };
                              if(localStorage.getItem("nscDebugLog") === "true") {
                                  console.log("iFrame loaded, sending config",config);
                              }
                              iframe.contentWindow.postMessage(config, window.location.origin);
                          });
                      })();
                  </script>';
              }
              ?>
              <p class="description"><?php echo wp_kses($field_configs->helpertext, $allowed_html) ?></p>
            </fieldset>
            </td>
        </tr>
      <?php } ?>
    </table>
  </form>
</span>
<?php require 'sidebar.php'; ?>