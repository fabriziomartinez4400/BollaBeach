<?php
if (!defined('ABSPATH')) {
  exit;
}

?>

<div id="nsc_bar_sidebar">
  <?php if (!empty($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_tipps)) { ?>
    <div class="nsc_bar_info_box">
      <h3 class="fs-5">Tipps</h3>
      <div class="nsc_bar_inside_text">
        <?php echo $objSettings->setting_page_fields->tabs[$active_tab_index]->tab_tipps ?>
      </div>
    </div>
  <?php } ?>
  <a href="https://beautiful-cookie-banner.com/articles/cookie-banner-test-monitor/" target="_blank"><img class="mb-2"
      width="280px" src="<?php echo NSC_BAR_PLUGIN_URL . 'admin/img/banner3.png' ?>" /></a>
  <div class="nsc_bar_info_box">
    <h3 class="fs-5">Share some love</h3>
    <div class="nsc_bar_inside_text">
      <div>
        🤟
        <a target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post">Review
          this plugin
        </a>
      </div>
      <div>
        You enjoy this free plugin? Really happy to hear. Please leave a nice review <a target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/reviews/#new-post">here.
        </a> Thanks a lot.
      </div>
      <br />
      <div>
        🤨
        <a target="_blank" href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/">Don't
          like this plugin?</a>
      </div>
      <div>
        Or you have discovered a bug? Please tell us. It really helps us improving this plugin. Use the
        link above or
        <a target="_blank" href="https://beautiful-cookie-banner.com/contact-form/">this formular.</a>
      </div>
      <br />
      <div>
        🖖
        <a target="_blank" href="https://beautiful-cookie-banner.com/">More Features?</a>
      </div>
      <div>
        Or you are able to support the development with money? Have a look at the
        <a target="_blank" href="https://beautiful-cookie-banner.com/">premium add-on.</a>
      </div>
    </div>
  </div>

  <div class="nsc_bar_info_box">
    <h3 class="fs-5">Getting Help</h3>
    <div class="nsc_bar_inside_text">
      <div>
        <a target="_blank" href="https://beautiful-cookie-banner.com/documentation/">Official Documentation</a>
      </div>
      <div>Here you can find some documentation for this plugin.</div>
      <br />
      <div>
        <a target="_blank"
          href="https://wordpress.org/support/plugin/beautiful-and-responsive-cookie-consent/">Wordpress Support
          Forum</a>
      </div>
      <div>
        If the documentation is not helping please have a look at the Forum, maybe your question is
        already answered.
      </div>
    </div>
  </div>
  <div class="nsc_bar_info_box">
    <h3 class="fs-5">Description & Credits</h3>
    <div class="nsc_bar_inside_text">
      This plugin leverages the excellent open-source cookie consent banner solution from Osano (<a
        href="https://github.com/osano/cookieconsent/tree/master" target="_blank">Github link</a>) as a
      foundation. However, the WordPress plugin version includes custom adjustments tailored for this platform.
      If you have any feature requests or encounter issues, feel free to reach out!
      </p>
    </div>
  </div>
</div>