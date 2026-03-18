=== Beautiful Cookie Consent Banner ===
Tags: cookie consent, cookie banner, cookie consent banner, gdpr, google consent mode v2
Requires at least: 4.0
Donate link: https://www.paypal.me/nikelschubert/6.00EUR
Tested up to: 6.9
Requires PHP: 7.0.0
License: GPLv3
Stable tag: 4.9.2

Free and beautiful Cookie Consent Banner to make your website compliant. Highly customizable and not loading any files from 3rd party servers.

== Description ==
A free and beautiful way to get a Cookie Banner without loading any external resources from 3rd parties. Customize it to match your compliance requirements and website layout. This Banner is super responsive and highly customizable. You want to make sure your banner always works? See here: [banner-test](https://beautiful-cookie-banner.com/articles/cookie-banner-test-monitor/). With paid add-on you get Google Consent Mode v2 support.

= Key Features =

- fully customizable texts, colors, fonts and position of the banner and buttons.
- support WP Consent API and other consent APIs.
- Google Tag Manager dataLayer pushes
- choose between different compliance types: Just Inform, Opt-in, Opt-out, Differentiated. Cookies will not be stored by default. With differentiated you can define cookie groups, and for each group a user can give a consent.
- show the Banner until user accepts all cookies.
- prevent user setting cookie from automatic deletion by many browsers (e.g. ITP => 2.1).
- no resources loaded from third parties: **everything is hosted on your domain**.
- developer friendly: filter hook to change cookie message: nsc_bar_cookie_bar_message

= Additional Features with paid add-on =

- Google Consent Mode v2, including new ad_personalization and ad_user_data, see here: [Google Consent Mode v2 for WordPress](https://beautiful-cookie-banner.com/documentation/cookie-consent-premium-add-on-documentation/google-consent-mode-v2-implementation-example/)
- Microsoft UET consent mode support. [Microsoft UET consent mode for WordPress](https://beautiful-cookie-banner.com/documentation/cookie-consent-premium-add-on-documentation/microsoft-uet-consent-mode-for-wordpress/)
- Microsoft Clarity Consent API support. [Microsoft Clarity API support for WordPress](https://beautiful-cookie-banner.com/documentation/cookie-consent-premium-add-on-documentation/microsoft-clarity-consent-api-support-wordpress/)
- Auto block scripts and requests
- Multilanguage support
- Full Google Tag Manager integration: load GTM through this plugin.
- API for Settings
- Consent statistics and logs

See the list of all features and get the add-on: [beautiful-cookie-banner.com](https://beautiful-cookie-banner.com/)


= These cookies are set by this plugin =

See the list cookies and localStorage used: [Cookies used by Cookie Banner](https://beautiful-cookie-banner.com/documentation/cookies-used-by-cookie-banner-plugin/)


= Features =

1. Fully responsive.
2. Make consent cookie a backend cookie to prevent automatic deletion which many browsers do for javascript/frontend cookies.
3. reload after acceptance
4. Works with liteSpeed and other caching plugin.
5. Easily export your settings to another installation: just copy and paste the json string.
6. Show banner until user accepts cookies
7. Google Tag Manager supported: Push consent to dataLayer for easy configuration
8. Users can easily change cookie settings: choose between an extra tab or with a shortcode.
9. Fully customizable colors and text.
10. It is for all kind of compliance: opt-out, opt-in, info only, differentiated
11. choose between different themes
12. choose the position on your website
13. choose cookie name, duration, domain.
14. cookie setting management via shortcode: [cc_show_cookie_banner_nsc_bar]
15. preview banner in backend.
16. auto dismiss
    ... and there are a lot more.

See the full list of all features: [beautiful-cookie-banner.com](https://beautiful-cookie-banner.com/)

The plugin helps you preparing your website for a lot cookie laws and regulations, for example:

- GDPR: The General Data Protection Regulation (European Union)
- CCPA: The California Consumer Privacy Act (California, United States)
- PIPEDA: The Personal Information Protection and Electronic Documents Act (Canada)
- LGPD: The Brazilian General Data Protection Law (Brazil)
- OAIC: Australia’s Privacy Principles (Australia)

= Credits =
This really beautiful plugin wraps the solution provided by osano (https://www.osano.com/cookieconsent/download/) into a wordpress plugin.
This Version uses 3.1.0 from osano as basis, but it has a lot modifications to the original source code. It is optimized for performance and for a low database impact.

= NOTE: Using this plugin alone does not make your site automatically compliant. As each website and country is different you must make sure you have the right configurations in place. Google tag manager can help you with that for free. =

== Frequently Asked Questions ==

= Why backend cookies? (ITP >= 2.1) =
Most Cookie Banner plugins set javascript cookies. In Safari and Firefox these cookies have a short lifetime, even if the cookie is set with an very long expiration date.
**Consequence:** Your user have to opt in every seven days again. And sees the banner every seven days. Which is kind of annoying.
With this Plugin the consent cookie stays save and is not limited in lifetime. If you use the option.

= How to remove tab at the bottom? =
Just go to Settings > Cookie Consent Banner > Consent Management and uncheck the checkbox "Show 'Cookie Settings' tab"

= Does this plugin have a setting for blocking scripts or cookies until acceptance? =
Yes. With the premium add-on ([beautiful-cookie-banner.com](https://beautiful-cookie-banner.com/)) you can enable autoblocking. But be aware: there is no 100% guarantee that this will always work. As this is heavily dependent from your wordpress installation.

= How should I organize my trackingscripts and cookies? =
The technical recommandation is to use the google tag manager. It is an awesome and free tagmanager. Since version 2.2 this plugin natively supports it. But as always: check if it is legal to use it in your jurisdication☝️.

= I have a custom template and want to modify the cookie message. How? =
You can use the filter hook "nsc_bar_cookie_bar_message" in your plugin or theme.

== Screenshots ==

1. Cookie Banner Mobile Example - two buttons.
2. Cookie Banner admin settings area with banner example.
3. Set the colors and general appearence of the Cookie Banner.
4. Set the text and customize the links of the Cookie Banner.
5. How should the Cookie Banner behave? Two Buttons, one button? Consent categories? Everything is possible.
6. Define how user can interact with the Cookie Baner after they closed it? Only per shortcode link? Or a tab?
7. Block scripts which set cookies automatically with the premium add-on of this cookie banner.
8. Basic cookie settings. The consents are stored in a cookie.
9. You can easily import and export most of the settings of your cookie banner. Very handy to apply it to multiple pages.
10. Mobile Example of the cookie banner.
11. Mobile Example of the cookie banner.
12. Desktop Example of the cookie banner.
13. Desktop Example of the cookie banner with top bar.

== Installation ==
Just install this plugin and go to Settings > Cookie Consent Banner to change the default config and to activate the banner.

== Changelog ==

= 4.9.2 =

- FIX: Banner 1 - Resolved an accessibility issue where the Cookie Settings tab could not be accessed using keyboard navigation only.

= 4.9.1 =

- FIX: Resolved PHP warnings that appeared in the admin area for certain installations.

= 4.9.0 =
- SECURITY FIX: Fixed potential stored Cross-Site Scripting vulnerability.
- NEW: Banner 1 - Cookie domain has now the same logic as Banner 2. Domain is set to ".example.com" and not "example.com" anymore.
- NEW: Added a global admin warning for site administrators when the premium add-on is missing critical updates. We do not like global notices, but in this case we think they are justified to highlight potential security risks from outdated versions.
- REFACTOR (Premium): MS UET consent mode, now back in Google Consent mode tab, to make clear, that they are coupled. A decoupling will happen in future releases.
- NEW (Premium): Setting for disabling the public API.
- FIX (Premium): Sometimes backend cookie conversion lead to problems updating the consent storage cookie.

= 4.8.0 =
- NEW: Blur the Background of Banner 1.
- NEW: Added support for "Cookie Settings Tab" for Banner 2.
- NEW: More Themes for Banner 2.

= 4.7.0 =
- FIX: the banner JavaScript was polluting global scope
- NEW: Added support for a "blocking placeholder" feature, available for Premium users.
- IMPROVED: Refactored parts of the codebase for better performance and future maintainability.

= 4.6.3 =
- Added Plugin URI

= 4.6.2 =
- FIX: Fix potential XSS Vulnerability

= 4.6.1 =
- FIX: made validation more forgiving: in some misconfigured cases there was a fatal error.

= 4.6.0 =
- NEW: added support for [WP Consent API](https://wordpress.org/plugins/wp-consent-api/)
- NEW: added support for [Pixel Manager for WooCommerce](https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/) consent API
- UI: Renamed Consent Mode Tab 

= 4.5.6 =
- FIX: International characters or now supported in link fields.
- FIX: Error messages in admin are back.
- FIX: hide admin setting for custom order of buttons if not needed.
- FIX: in some rare situations it was not possible to import a json string.

= 4.5.5 =
- REFACTOR: Better error messages in admin for WordPress API Errors.

= 4.5.4 =
- FIX: Single quotes in banner text works again.
- REFACTOR: deleted some unused variables.

= 4.5.3 =
- FIX: Specfic HTML is allowed in description again.

= 4.5.2 =
- SECURITY FIX: Admins where able to start a XSS attack.

= 4.5.1 =
- REFACTOR: Removed automatic LiteSpeed cache purge upon saving.
- NEW: Blocklist for banner 2 now.

= 4.5.0 =
- NEW: You can now freely arrange the order of the consent buttons when using the "Differentiated consent - 3 Buttons" option.
- FIX: "Learn More" Link Styling: The margins of the "Learn More" link in the banner have been corrected.
- FIX: Resolved a conflict where page CSS would interfere with button styling, preventing buttons from displaying properly (e.g., underlined buttons).

= 4.4.0 =

- NEW: premium add-on support for Microsoft clarity.
- REFACTOR: removed obsolete constant NSC_BAR_VERSION
- REFACTOR: renamed admin js file
- REFACTOR: added return filter class
- REFACTOR: Admin UI slightly nicer

= 4.3.1 =

- Fix: Resolved an issue where initial differentiated consent occasionally failed to produce the correct results for consent mode.
- Refactor: Introduced groundwork to further decouple the premium plugin from the standard plugin, enhancing modularity.

= 4.3.0 =

- Improvements for premium addon: consent mode update dataLayer push on first page view & microsoft consent mode support.

= 4.2.1 =

- FIX: Prevent premature cookie deletion now handles json cookie content correctly. Only affecte "Banner 2"

= 4.2.0 =

- BREAKING CHANGE: removed deprecated shortcode **nsc_bar_shortcode_revoke_settings_link**. Was marked for over two years as deprecated.
- REFACTOR: remove wp_localize_script
- NEW: added more triggers to load banner

= 4.1.0 =
**IMPORTANT** The premium add on < 3.2.0 is not fully compatible anymore with this version. If you want to update the main plugin it is recommended to update the premium add-on to at least 3.2.
If you are not using the premium add-on you can ignore this hint.

- REFACTOR: Preparations to support multiple banner.
- REFACTOR: DataLayer init script improved.
- FIX: Notice in db upgrader

= 4.0.1 =

- FIX: PHP Parse Error in class-nsc_bar_save_form_fields.php on line 75

= 4.0.0 =

- NEW: The "text for banner" field accepts now the following HTML Tags: strong, i, a, div, span, p, br, ul, ol, li, h1, h2, h3, h4, h5, h6, hr
- NEW: Support for masking fields in admin
- FIX: Special chars are now not broken anymore. All json encodes uses now: JSON_UNESCAPED_UNICODE.
- REFACTOR: added getCookieName Method
- REFACTOR: Preparations to support multiple banner.

= 3.3.2 =

- FIX: Google Consent mode: with compliance type "detailed" changes on the same page were not always pushed.

= 3.3.1 =

- IMPROVMENT: new compliance type "Differentiated Consent - Deny" needed three buttons. Added button to save settings.

= 3.3.0 =

- NEW: new compliance type: Differentiated Consent - Deny

= 3.2.4 =

- FIX: Some changes lead to banner disapear. Reverted some cache plugin optimisations.

= 3.2.3 =

- IMPROVEMENT: added improvements for WP Rocket

= 3.2.2 =

- FIX: improved loading of static assets in settings area.
- IMPROVEMENT: Works now better with caching plugins like liteSpeed.

= 3.2.1 =

- FIX: Google Consent Mode v2 update crashed, if dataLayername was not defined
- IMPROVEMENT: Consent Mode help text improvements.

= 3.2.0 =

- Google Consent Mode v2 improvements
- Review banner text improvements

= 3.1.0 =

- NEW: Google Consent Mode v2 support
- FIX: For new languages banner settings were not inherited
- IMPROVEMENT: Ability to reset banner configs now: [How to reset the cookie banner](https://beautiful-cookie-banner.com/documentation/troubleshooting/#reset-banner).

= 3.0.0 =

- Fix & breaking change: renamed element ids of banner switches to make them unique and avoid collisions. Only relevant if you add custom logic or styles to the banner.
- Fix: after allow all the switches are now displayed correctly, even without a page load. Before the fix a reload of the page was necessary. Was only a "display problem".
- FIX: Differentiated consent: if a switch was disabled AND unchecked, so that the user was not able to change it, it was still set to allow, if user clicked "allow all". Now it stays "denied".
- Increased required PHP version to 7.0.0
- New: added humble request for a review
- New: added Google Consent Mode support for premium addon

= 2.15.0 =

- New: added centered position for banner. You can position the banner now in the center of the screen.
- New: adjust the position of the "Cookie Settings" tab independently from the cookie pop up.
- Improvement: Admin settings tab renamed "Consent Management" to "Cookie Settings tab"

= 2.14.1 =

- Improvements: preparation for settings API of premium plugin

= 2.14.0 =

- Improvements: further web accessibility improvements
- NEW: added support for icons for cookie settings tab for premium add-on

= 2.13.5 =

- FIX: Web Accessibility: Labels were missing

= 2.13.4 =

- FIX: Fixed missed deprecation warnings with PHP Version > 8.2

= 2.13.3 =

- FIX: Deprecation warnings with PHP Version > 8.2

= 2.13.2 =

- FIX: Target of 'Learn more' Link sometimes not saved correctly. Especially in multilanguage context.

= 2.13.1 =

- FIX: relative URLs could not be saved anymore.

= 2.13.0 =

- IMPROVEMENT: Preperations for premium addon 2.2.0

= 2.12.2 =

- FIX: Some caching plugins prevent the correct calculation of the dataLayer value. Works now with all. Reading the cookie values now directly in JS.
- IMPROVEMENT: for better convenience purging litespeed cache, after changes to the banner setting.

= 2.12.1 =

- Added banner test hint in admin area

= 2.12.0 =

- REFACTOR: changed loading logic of dataLayer event beautiful_cookie_consent_initialized.
- IMPROVEMENT: New Tab: Google Tag Manager to have all GTM configs in one place.
- NEW FEATURE: With the premium add-on this plugin can manage now the loading of the GTM, based on user consent.

= 2.11.1 =

- REFACTOR: Added compatibility for upcoming addon version.

= 2.11.0 =

- PLEASE REVIEW BEFORE UPDATING: change the standard permission to edit the banner from edit_published_posts to manage_options. If you do not want that, you can change it back again in the admin area.
- IMPROVEMENT: added additional security safeguard as recommended from the external security audit.
- IMPROVEMENT: added better validation for color inputs

= 2.10.2 =

- Improvement: If the in verison 2.10.1 fixed vulnerabulity was exploited on your site, this update is going to disable the malicious code.
- Refactor: Improved validation

= 2.10.1 =

- Fix: Authentication issues.
- Fix: XXS vulnerabulity fixed.

= 2.10.0 =

- IMPROVEMENT: performance - js for dataLayer push is not inline anymore.
- IMPROVEMENT: preparations for premium add-on version 1.7.0

= 2.9.2 =

- IMPROVEMENT: Minified some JS
- IMPROVEMENT: Support for show banner with delay of premium add-on

= 2.9.1 =

- FIX: XSS vulnerabulity in admin area. Was only possible for admins to exploit.

= 2.9.0 =

- Feature: added option to customize the text for closing.
- Refactor: made the admin js a little bit nicer.

= 2.8.2 =

- Bugfix: sometimes when using elementor preloader, the banner did not disapear. Now it works.

= 2.8.1 =

- Improvement: the "x" is now within the banner and not outside.

= 2.8.0 =

- NEW: added possibility to close banner with an "x"
- IMPROVEMENT: increased the z-index of the banner to max integer. So the banner and the blocking screen will be always on top of everything.

= 2.7.3 =

- Refactor: removed unnecessary admin check.

= 2.7.2 =

- Improvement: filter changes for premium plugin: nsc_bar_plugin_settings_as_an_object
- Bugfix: Removed duplicate input sanitation, which lead in some cases to errors.

= 2.7.1 =

- Bugfix: If the [cc_show_cookie_banner_nsc_bar] shortcode was used more then once per page it only worked for the first link.
- Refactor: changes to JS library for an upcoming statistics module

= 2.7.0 =

- Feature: Disable the banner, if the page is loaded in an iFrame.
- Refactor: Preparation for a statistic module.
- Refactor: dataLayer push more separated in code, to be able to load it on wp_loaded.
- Bugfix: Now it works with the weglot language plugin.
- Improvement: adding filter: nsc_bar_filter_data_layer_values.

= 2.6.7 =

- Improvement: Cookie samesite attribute is now fully set
- Improvement: You can now interact with the buttons by pushing "return"

= 2.6.6 =

- Bug fix: in WP 5.8 Banner showed up in new widget area.

= 2.6.5 =

- Refactor: added filter for field validation
- Improvements: added warning to "improve speed" setting.
- Improvements: 'Learn more' Link and customLink can now be configured, if they should open in a new tab/window or not.

= 2.6.4 =

- Improvements: added setting to improve the banner loading speed.

= 2.6.3 =

- Improvements: adding more filter: nsc_bar_filter_json_config_string_before_js, nsc_bar_filter_banner_config_array_init, nsc_bar_filter_banner_is_active

= 2.6.2 =

- Improvements: added filter for the final config json as valid JS object: nsc_bar_filter_json_config_string_with_js

= 2.6.1 =

- Improvements: no new features, only the admin area is now nicer and cleaner.

= 2.6.0 =

- FEATURE: you can now block scripts that set cookies with the premium add-on, see here for more information: [beautiful-cookie-banner.com](https://beautiful-cookie-banner.com/)

= 2.5.3 =

- BUG FIX: Some issues with caching plugins.

= 2.5.2 =

- BUG FIX: since 2.5.1 the dataLayer push for "banner initialized" was fired pretty late. Now it is fired earlier again.

= 2.5.1 =

- IMPROVEMENT: Filter for the cookie message. Now you can easily change the cookie message in your theme or template.
- REFACTOR: some changes regarding how the scripts are included in the page.

= 2.5.0 =

- ADDED: Possibility to place a second link freely in the cookie banner text.

= 2.4.0 =

- ADDED: Support for multilanguage add-on, see here for more information: [beautiful-cookie-banner.com](https://beautiful-cookie-banner.com/)

= 2.3.1 =

- IMPROVEMENT: Colour fields now supprt rgb as well, e.g.: rgba(200, 54, 54, 0.5)

= 2.3 =

- FEATURE: Adjust the position of the Banner in HTML source code of the page. Per default it is directly beneath the <body>. Now you can use any other selector to change that.

= 2.2.1 =

- IMPROVEMENT: Default Setting name of "cookie settings"
- IMPROVEMENT: When adding cookie types the added form is now in focus.
- FIX: length of "cookie_suffix" not correct validated.

= 2.2 =

- ADDED: Google Tag Manager support: dataLayer pushes - events: beautiful_cookie_consent_initialized and beautiful_cookie_consent_updated
- ADDED: setting for font customisation.
- ADDED: setting to make buttons look equal.
- ADDED: automatic consent: after time or scroll.

= 2.1.1 =

- IMPROVEMENT: changed way scripts are loaded. Now there should be a better support of caching plugins.

= 2.1 =

- ADDED: "differentiated consent" with two buttons: "save all" and "save settings"
- ADDED: Option to block screen, when banner is shown.

= 2.0 =

- ADDED: "differentiated consent" as compliance type: users can now give consent to only a special type of cookie.
- ADDED: a shortcode for showing banner again, for managing consent: [cc_show_cookie_banner_nsc_bar].
- ADDED: more positions for the banner
- CHANGED: changed revoke tab handling: now possible to use for all banner types.
- DEPRECATED: shortcode: [cc_revoke_settings_link_nsc_bar]
- REFACTOR: a lot refactorings, for better feature development in future. A lot.
- REFACTOR: not using unchanged banner JS from osano anymore.

= 1.7.1 =

- bug fixes

= 1.7 =

- added feature "Ask until acceptance": show banner until user consents.

= 1.6 =

- added "reload after banner close" for more accurate tracking after tracking opt in.

= 1.5 =

- added a field to configure the text of "Cookie Policy" tab.
- "Cookie Policy" tab can now be deactivated
- users can manage their cookie settings now independently from "Cookie Policy" tab: you can add the link easily easily by shortcode: [cc_revoke_settings_link_nsc_bar].
- bit of clean up of admin area

= 1.4.1 =

- Bug Fix: sometimes the Banner was behind the content.
- Bug Fix: minor Performance Issue.

= 1.4 =

- New Feature: cookie banner is now configurable with a form and not only with a json string. If you want to, you can still use the json.
- a lot stuff under the hood.

= 1.3 =

- New Feature: Added prevention for ITP 2.1 cookie deletion. Safari and Firefox limit the lifetime of a cookie which is set by javascript to seven days.
  Most cookie banner plugins set javascript cookies, that means that your returning user will see every seven days your banner and have to opt in again.
- Improvement: Cookie in preview modus only deleted if you visit the settings page of that plugin, not admin in general.

= 1.2 =

- Improvement: When visiting an admin page, consent cookie is only deleted, if "preview banner" setting is activated.

= 1.1.2 =

- minor bug fixes.

= 1.1.1 =

- fixes for PHP 5.4

= 1.1 =

New Features

- you can activate or deactivate the banner now.
- you see a preview in the plugin settings area now.

= 1.0.5 =

- Improvement: Updated to cookie consent library 3.1. Now dismissOn... functions are working.

= 1.0.4 =

- FIX: Now Really: with an update of this plugin the configuration of the cookie banner is not overwritten anymore.

= 1.0.3 =

- FIX: small fixes and improvements, e.g. admin errors will only be displayed on admin page of this plugin.
- Improvement: added Icon for this plugin.

= 1.0.2 =

- FIX: with an update of this plugin the configuration of the cookie banner is not overwritten anymore.

= 1.0.1 =

- Improvement: added compatibility in readme.txt

= 1.0 =

- First Version of this lightweight Plugin. More to come!
