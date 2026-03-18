(function($) {

	"use strict";

	/* Vertical Tab */
	$( document ).on( "click", ".wcpscwc-vtab-nav a", function() {
		$(".wcpscwc-vtab-nav").removeClass('wcpscwc-active-vtab');
		$(this).parent('.wcpscwc-vtab-nav').addClass("wcpscwc-active-vtab");

		var selected_tab = $(this).attr("href");
		$('.wcpscwc-vtab-cnt').hide();

		/* Show the selected tab content */
		$(selected_tab).show();

		/* Pass selected tab */
		$('.wcpscwc-selected-tab').val(selected_tab);
		return false;
	});

	/* Remain selected tab for user */
	if( $('.wcpscwc-selected-tab').length > 0 ) {
		
		var sel_tab = $('.wcpscwc-selected-tab').val();

		if( typeof(sel_tab) !== 'undefined' && sel_tab != '' && $(sel_tab).length > 0 ) {
			$('.wcpscwc-vtab-nav [href="'+sel_tab+'"]').click();
		} else {
			$('.wcpscwc-vtab-nav:first-child a').click();
		}
	}

	/* Click to Copy the Text */
	$(document).on('click', '.wpos-copy-clipboard', function() {
		var copyText = $(this);
		copyText.select();
		document.execCommand("copy");
	});

	/* Drag widget event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.preview-rendered', wcpscwc_fl_render_preview );

	/* Save widget event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.layout-rendered', wcpscwc_fl_render_preview );

	/* Publish button event to render layout for Beaver Builder */
	$('.fl-builder-content').on( 'fl-builder.didSaveNodeSettings', wcpscwc_fl_render_preview );

})( jQuery );

/* Function to render shortcode preview for Beaver Builder */
function wcpscwc_fl_render_preview() {
	wcpscwc_product_slider_init();
}