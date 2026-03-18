<?php
if (!defined('ABSPATH')) {
    exit;
}

$allowed_html = array(
    "strong" => array(),
    "i" => array(),
    "a" => array(
        "href" => array(),
        "id" => array(),
        "title" => array(),
        "target" => array(),
    ),
    "div" => array(
        "class" => array(),
        "id" => array()
    ),
    "p" => array("class" => array(), "id" => array()),
    "br" => array("class" => array(), "id" => array()),
    "ul" => array("class" => array(), "id" => array()),
    "ol" => array("class" => array(), "id" => array()),
    "li" => array("class" => array(), "id" => array()),
    "h1" => array("class" => array(), "id" => array()),
    "h2" => array("class" => array(), "id" => array()),
    "h3" => array("class" => array(), "id" => array()),
    "h4" => array("class" => array(), "id" => array()),
    "h5" => array("class" => array(), "id" => array()),
    "h6" => array("class" => array(), "id" => array()),
    "hr" => array("class" => array(), "id" => array()),
);
?>
<div class="wrap">

    <script>
        const nscBarConsentType = `<?php echo $exposeJSConsentType ?>`;
        const nscBarCookieTypes = <?php echo $exposeJSCookieTypes ?>
    </script>

    <div id="nsc_bar_upper_area">
        <h1 id="nsc_bar_admin_title"><?php echo esc_html($objSettings->settings_page_configs->page_title) ?></h1>
        <p><?php echo wp_kses($objSettings->settings_page_configs->description, $allowed_html) ?></p>
    </div>
    <div class="nsc-bar-selector-new-banner">
        <label for="nsc_bar_new_banner_selector" id="nsc_bar_new_banner_selector-label">
            <span class="nsc_bar_new_banner_selector-text">Banner in use is: </span>
        </label>
        <select name="nsc_bar_new_banner_selector" id="nsc_bar_new_banner_selector">
            <option <?php echo $newBannerEnabled === true ? '' : 'selected' ?> value="banner-1">Banner 1</option>
            <option <?php echo $newBannerEnabled === true ? 'selected' : '' ?> value="banner-2">Banner 2</option>
        </select>
    </div>

    <h2 class="nav-tab-wrapper">
        <?php
        //tabs are created
        foreach ($objSettings->setting_page_fields->tabs as $tab) {
            // not display if new banner is false
            if ($newBannerEnabled === true && isset($tab->newBanner) && $tab->newBanner === false) {
                continue;
            }

            // not display if newBanner is true, but banner is disabled.
            if ($newBannerEnabled === false && isset($tab->newBanner) && $tab->newBanner === true) {
                continue;
            }

            // here all tabs go with newBanner not set at all

            $activeTab = "";
            if ($tab->active === true) {
                $activeTab = 'nav-tab-active';
            }
            echo '<a href="?page=' . esc_attr($objSettings->plugin_slug) . '&tab=' . esc_attr($tab->tab_slug) . '&' . esc_attr($objSettings->additional_tab_link_parameter) . '" class="nav-tab ' . esc_attr($activeTab) . '" >' . esc_html($tab->tabname) . '</a>';
        }
        $active_tab_index = $objSettings->setting_page_fields->active_tab_index;
        ?>
    </h2>
    <?php
    if (empty($_GET["tab"]) || $_GET["tab"] !== "new_banner") {
        require NSC_BAR_PLUGIN_DIR . "/admin/tpl/legacy.php";
    }

    if (empty($_GET["tab"]) === false && $_GET["tab"] === "new_banner") {
        $nonce = wp_create_nonce('wp_rest');
        echo wp_kses($objSettings->setting_page_fields->tabs[$active_tab_index]->tab_description, $allowed_html);
        if ($premiumAddonInstalled === false) {
            echo '<script>localStorage.setItem("nscBaraCookieBannerState",JSON.stringify(' . $fallbackStateNewBanner . '));</script>';
        }
        $loadCustomBuildCss = 0;
        echo '<script>addEventListener("load", (event) => {iFrameResize({ log: false, minHeight: 500 }, "#nsc_bar_new_banner");}); 
        </script><iframe width="100%"
             id="nsc_bar_new_banner"
             src="' . NSC_BAR_PLUGIN_URL . 'admin/new-banner/index.html?cb=' . NSC_BAR_PLUGIN_VERSION . '">
        </iframe>
        <script>
            (function(){
                const iframe = document.getElementById("nsc_bar_new_banner");
                iframe.addEventListener("load", ()=>{
                    const config = {
                        source: "beautiful-cookie-banner",
                        pluginUrl: "' . esc_url(NSC_BAR_PLUGIN_URL) . '",
                        restURL:   "' . esc_url(get_rest_url()) . '",
                        nonce:     "' . esc_js($nonce) . '",
                        content_url: "' . esc_url(content_url()) . '",
                        customBuildCss: ' . $loadCustomBuildCss . ',
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