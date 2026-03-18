<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>
<div id="wcpscwc_basic_tabs" class="wcpscwc-vtab-cnt wcpscwc_basic_tabs wcpscwc-clearfix">
	<!-- <h3 style="text-align:center">Compare <span class="wcpscwc-blue">"Woo Product Slider"</span> Free VS Pro</h3> -->

	<!-- <div class="wcpscwc-deal-offer-wrap">
		<div class="wcpscwc-deal-offer"> 
			<div class="wcpscwc-inn-deal-offer">
				<h3 class="wcpscwc-inn-deal-hedding"><span>Buy WP News and Scrolling Widgets Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wcpscwc-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wcpscwc-inn-deal-offer-btn">
				<div class="wcpscwc-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php // echo esc_url(WCPSCWC_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wcpscwc-sf-btn wcpscwc-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wcpscwc-deal-offer-wrap">
		<div class="wcpscwc-deal-offer"> 
			<div class="wcpscwc-inn-deal-offer">
				<h3 class="wcpscwc-inn-deal-hedding"><span>Try Woo Product Slider</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wcpscwc-deal-free-offer">
				<a href="<?php // echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wcpscwc-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

	<!-- <div class="wcpscwc-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WCPSCWC_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<div class="wcpscwc-black-friday-banner-wrp" style="background:#e1ecc8;padding: 20px 20px 40px; border-radius:5px; text-align:center;margin-bottom: 40px;">
		<h2 style="font-size:30px; margin-bottom:10px;"><span style="color:#0055fb;">Product Slider and Carousel with Category for WooCommerce</span> is included in <span style="color:#0055fb;">Essential Plugin Bundle</span> </h2> 
		<h4 style="font-size: 18px;margin-top: 0px;color: #ff5d52;margin-bottom: 24px;">Now get Designs, Optimization, Security, Backup, Migration Solutions @ one stop. </h4>

		<div class="wcpscwc-black-friday-feature">

			<div class="wcpscwc-inner-deal-class" style="width:40%;">
				<div class="wcpscwc-inner-Bonus-class">Bonus</div>
				<div class="wcpscwc-image-logo" style="font-weight: bold;font-size: 26px;color: #222;"><img style="width: 34px; height:34px;vertical-align: middle;margin-right: 5px;" class="wcpscwc-img-logo" src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/essential-logo-small.png" alt="essential-logo" /><span class="wcpscwc-esstial-name" style="color:#0055fb;">Essential </span>Plugin</div>
				<div class="wcpscwc-sub-heading" style="font-size: 16px;text-align: left;font-weight: bold;color: #222;margin-bottom: 10px;">Includes All premium plugins at no extra cost.</div>
				<a class="wcpscwc-sf-btn" href="<?php echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
			</div>

			<div class="wcpscwc-main-list-class" style="width:60%;">
				<div class="wcpscwc-inner-list-class">
					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/img-slider.png" alt="essential-logo" /> Image Slider</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/advertising.png" alt="essential-logo" /> Publication</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/marketing.png" alt="essential-logo" /> Marketing</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/photo-album.png" alt="essential-logo" /> Photo album</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/showcase.png" alt="essential-logo" /> Showcase</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/shopping-bag.png" alt="essential-logo" /> WooCommerce</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/performance.png" alt="essential-logo" /> Performance</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/security.png" alt="essential-logo" /> Security</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/forms.png" alt="essential-logo" /> Pro Forms</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/seo.png" alt="essential-logo" /> SEO</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/backup.png" alt="essential-logo" /> Backups</li></div>

					<div class="wcpscwc-list-img-class"><img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/White-labeling.png" alt="essential-logo" /> Migration</li></div>
				</div>
			</div>
		</div>
		<div class="wcpscwc-main-feature-item">
			<div class="wcpscwc-inner-feature-item">
				<div class="wcpscwc-list-feature-item">
					<img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/layers.png" alt="layer" />
					<h5>Site management</h5>
					<p>Manage, update, secure & optimize unlimited sites.</p>
				</div>
				<div class="wcpscwc-list-feature-item">
					<img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/risk.png" alt="backup" />
					<h5>Backup storage</h5>
					<p>Secure sites with auto backups and easy restore.</p>
				</div>
				<div class="wcpscwc-list-feature-item">
					<img src="<?php echo esc_url( WCPSCWC_URL ); ?>assets/images/logo-image/support.png" alt="support" />
					<h5>Support</h5>
					<p>Get answers on everything WordPress at anytime.</p>
				</div>
			</div>
		</div>
		<a class="wcpscwc-sf-btn" href="<?php echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
	</div>

	<h3 style="text-align:center">Compare <span class="wcpscwc-blue">"Woo Product Slider"</span> Free VS Pro</h3>

	<table class="wpos-plugin-pricing-table">
		<colgroup></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th></th>
				<th>
					<h2><?php esc_html_e('Free', 'woo-product-slider-and-carousel-with-category'); ?></h2>
				</th>
				<th>
					<h2 class="wpos-epb"><?php esc_html_e('Premium', 'woo-product-slider-and-carousel-with-category'); ?></h2>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<th><?php esc_html_e('Designs', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Designs that make your website better', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
			<td>1</td>
			<td>15+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcodes', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Shortcode provide output to the front-end side', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><?php esc_html_e('1 (Grid)', 'woo-product-slider-and-carousel-with-category'); ?></td>
				<td><?php esc_html_e('2 (Grid, Slider)', 'woo-product-slider-and-carousel-with-category'); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Add extra power to the shortcode', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td>15+</td>
				<td>30+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Generator', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required!!', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('WP Templating Features', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Gutenberg Block Supports', 'woo-product-slider-and-carousel-with-category'); ?><span><?php esc_html_e('Use this plugin with Gutenberg easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Elementor Page Builder Support', 'woo-product-slider-and-carousel-with-category'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Elementor easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Beaver Builder Support', 'woo-product-slider-and-carousel-with-category'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Beaver Builder easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('SiteOrigin Page Builder Support', 'woo-product-slider-and-carousel-with-category'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with SiteOrigin easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Divi Page Builder Native Support', 'woo-product-slider-and-carousel-with-category'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Divi Builder easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Fusion (Avada) Page Builder Native Support', 'woo-product-slider-and-carousel-with-category'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Fusion Builder easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('WPBakery Page Builder Support', 'woo-product-slider-and-carousel-with-category'); ?><span><?php esc_html_e('Use this plugin with Visual Composer easily', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			
			<tr>
			<th><?php esc_html_e('Display Product for Particular Categories', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Display only the product with particular category', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-yes"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			
			<tr>
			<th><?php esc_html_e('Out Of Stock', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Enable/Diseble Slider Out Of Stock Product.', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('product Order / Order By Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Display product according to name, title and etc', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-yes"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('Product Center Mode Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Display product Centermode', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('Product Center Mode Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Display product Centermode', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('Slider Adaptive Height', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Display Slider Adaptive Height.', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('Hover && Focus Pause Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Slider parameters like Hover Pause, Focus Pause.', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
			<th><?php esc_html_e('Multiple Slider Parameters', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Slider parameters like autoplay, number of slide, sider dots and etc.', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
				<th><?php esc_html_e('Automatic Update', 'woo-product-slider-and-carousel-with-category'); ?><span><?php esc_html_e('Get automatic plugin updates', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><?php esc_html_e('Lifetime', 'woo-product-slider-and-carousel-with-category'); ?></td>
				<td><?php esc_html_e('Lifetime', 'woo-product-slider-and-carousel-with-category'); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Support', 'woo-product-slider-and-carousel-with-category'); ?><span class="subtext"><?php esc_html_e('Get support for plugin', 'woo-product-slider-and-carousel-with-category'); ?></span></th>
				<td><?php esc_html_e('Limited', 'woo-product-slider-and-carousel-with-category'); ?></td>
				<td><?php esc_html_e('1 Year', 'woo-product-slider-and-carousel-with-category'); ?></td>
			</tr>
		</tbody>
	</table>

	<!-- <div class="wcpscwc-deal-offer-wrap">
		<div class="wcpscwc-deal-offer"> 
			<div class="wcpscwc-inn-deal-offer">
				<h3 class="wcpscwc-inn-deal-hedding"><span>Buy WP News and Scrolling Widgets Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wcpscwc-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wcpscwc-inn-deal-offer-btn">
				<div class="wcpscwc-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php // echo esc_url(WCPSCWC_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="wcpscwc-sf-btn wcpscwc-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div> -->

	<!-- <div class="wcpscwc-deal-offer-wrap">
		<div class="wcpscwc-deal-offer"> 
			<div class="wcpscwc-inn-deal-offer">
				<h3 class="wcpscwc-inn-deal-hedding"><span>Try Woo Product Slider</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="wcpscwc-deal-free-offer">
				<a href="<?php //echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="wcpscwc-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div> -->

	<!-- <div class="wcpscwc-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WCPSCWC_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%" src="<?php // echo esc_url( WCPSCWC_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

</div>