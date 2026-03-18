jQuery(document).ready(function() {

	/* Tab Switching */
	window.wcuOpenTab = function(evt, tabName) {
		jQuery(".wcutabcontent").css("display", "none");
		jQuery(".wcutabcontent").removeClass( "active" );
		jQuery("#" + tabName).css("display", "block");
		jQuery("#" + tabName).addClass( "active" );
	}

	jQuery(document).ready(function(){
		jQuery('.wcutablinks').on('click', function(e){
			jQuery('.wcutablinks').removeClass('wcutab-active');
			jQuery(this).addClass('wcutab-active');
			
			var tabName = jQuery(this).data('content');
			if(tabName) {
				wcuOpenTab(e, tabName);
			}
		});
	});

	jQuery('.coupon-box').hide();
	jQuery(document).on('click', '#search-coupon', function(){
		jQuery('.coupon-box').hide();
		var txt = jQuery('#search-criteria').val();
		jQuery('.coupon-box table:contains("'+txt+'")').show().parent().show();
	});
    jQuery(document).on('keyup', '#search-criteria', function(event) {
		jQuery('.coupon-box').hide();
		var txt = jQuery('#search-criteria').val();
		jQuery('.coupon-box table:contains("'+txt+'")').show().parent().show();
    });
	jQuery(document).on('click', '#search-coupon-all', function(){
		jQuery('.coupon-box').toggle();
	});

	jQuery(document).on('click', '#wcusage-last-days7', function(){
		jQuery('.wcusage-show-last-7').show();
		jQuery('#wcusage-last-days7').css("color", "#333");
		jQuery('#wcusage-last-days7').addClass('wcutab-active');
		jQuery('.wcusage-show-last-30').hide();
		jQuery('#wcusage-last-days30').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days30').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-all').hide();
		jQuery('#wcusage-last-days-all').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days-all').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-all-30').hide();
	});

	jQuery(document).on('click', '#wcusage-last-days30', function(){
		jQuery('.wcusage-show-last-7').hide();
		jQuery('#wcusage-last-days7').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days7').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-30').show();
		jQuery('#wcusage-last-days30').css("color", "#333");
		jQuery('#wcusage-last-days30').addClass('wcutab-active');
		jQuery('.wcusage-show-last-all').hide();
		jQuery('#wcusage-last-days-all').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days-all').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-all-30').show();
	});

	jQuery(document).on('click', '#wcusage-last-days-all', function(){
		jQuery('.wcusage-show-last-7').hide();
		jQuery('#wcusage-last-days7').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days7').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-30').hide();
		jQuery('#wcusage-last-days30').css("color", "#a6a6a6");
		jQuery('#wcusage-last-days30').removeClass('wcutab-active');
		jQuery('.wcusage-show-last-all').show();
		jQuery('#wcusage-last-days-all').css("color", "#333");
		jQuery('#wcusage-last-days-all').addClass('wcutab-active');
		jQuery('.wcusage-show-last-all-30').show();
	});

	if( jQuery( '.wcu-dash-coupon-area' ).width() < 850 ) {
		jQuery('head').append('<style>.wcusage-info-box{padding:25px 35px 25px 85px;}.wcusage-info-box::before{font-size:29px;width:29px;}</style>');
	}

	if( jQuery( '.wcu-dash-coupon-area' ).width() < 1150 ) {
		jQuery(".wcusage-num-pos, .wcusage-num-neg").css("display", "block");
		jQuery(".wcusage-num-pos, .wcusage-num-neg").css("top", "0");
		jQuery(".wcusage-num-pos, .wcusage-num-neg").css("right", "7px");
	}

	/* Password Toggle */
	jQuery( document ).ready(function() {
		jQuery(document).on('click', '.wcu-toggle-password', function() {
			jQuery(this).toggleClass("fa-eye fa-eye-slash");
			var input = jQuery(jQuery(this).attr("toggle"));
			if (input.attr("type") == "password") {
				input.attr("type", "text");
			} else {
				input.attr("type", "password");
			}
		});
	});

});

/* Copy Button */
function wcusage_copyToClipboard(elementId, elementId2) {
	var aux = document.createElement("input");
	aux.setAttribute("value", document.getElementById(elementId).innerText);
	if(elementId2) {
		if(document.getElementById(elementId2).innerText) {
			aux.setAttribute("value", document.getElementById(elementId2).innerText);
		}
	}
	document.body.appendChild(aux);
	aux.select();
	document.execCommand("copy");
	document.body.removeChild(aux);
	jQuery("#wcu-copy-" + elementId).removeClass("fa-copy");
	jQuery("#wcu-copy-" + elementId).addClass("fa-check-circle");
	setTimeout(function() { wcu_reset_copy(elementId); }, 1000);
}
function wcu_reset_copy(elementId) {
	jQuery("#wcu-copy-" + elementId).removeClass("fa-check-circle");
	jQuery("#wcu-copy-" + elementId).addClass("fa-copy");
}

/* Search MLA Summary Field */
jQuery( document ).ready(function($) {
jQuery(document).on("keyup", "#wcu-summary-search", function() {
	var value = this.value.toLowerCase().trim();
	jQuery(".wcu-table-mla-summary tr").each(function(index) {
		jQuery(this).find("td").each(function () {
				jQuery(this).closest('tr').removeClass("excludeThisClass");
				var id = $(this).text().toLowerCase().trim();
				var not_found = (id.indexOf(value) == -1);
				jQuery(this).closest('tr').toggle(!not_found);
				if(not_found) {
					jQuery(this).closest('tr').addClass("excludeThisClass");
				}
				return not_found;
		});
	});
});
});

/* Creative Copy Embed Code */
function wcu_copy_embed_code(codeid, iconid) {
	var copyText = document.getElementById(codeid);
	copyText.select();
	copyText.setSelectionRange(0, 99999); /* For mobile devices */
	document.execCommand("copy");
	jQuery(iconid).removeClass("fa-code");
	jQuery(iconid).addClass("fa-check-circle");
	setTimeout(function() {
		jQuery(iconid).removeClass("fa-check-circle");
		jQuery(iconid).addClass("fa-code");
		copyText.blur();
	}, 1000);
}

function wcuCopyToClipboard(elementId, iconid) {
	// Create a "hidden" input
	var aux = document.createElement("input");
	// Assign it the value of the specified element
	aux.setAttribute("value", document.getElementById(elementId).innerHTML);
	// Append it to the body
	document.body.appendChild(aux);
	// Highlight its content
	aux.select();
	// Copy the highlighted text
	document.execCommand("copy");
	// Remove it from the body
	document.body.removeChild(aux);
	// Icon
	jQuery(iconid).removeClass("fa-code");
	jQuery(iconid).addClass("fa-check-circle");
	setTimeout(function() {
		jQuery(iconid).removeClass("fa-check-circle");
		jQuery(iconid).addClass("fa-code");
		copyText.blur();
	}, 1000);
}

/* Make all fields required if bank transfer is selected */
jQuery( document ).ready(function() {
	jQuery(document).on('change', '#wcu-payout-type', function() {
		var currentpayout = jQuery('#wcu-payout-type').find(":selected").val();
		if(currentpayout == "banktransfer") {
			if(jQuery('#wcu-bank-input1').length > 0) {
				jQuery('#wcu-bank-input1').prop('required',true);
			}
			if(jQuery('#wcu-bank-input2').length > 0) {
				jQuery('#wcu-bank-input2').prop('required',true);
			}
			if(jQuery('#wcu-bank-input3').length > 0) {
				jQuery('#wcu-bank-input3').prop('required',true);
			}
			if(jQuery('#wcu-bank-input4').length > 0) {
				jQuery('#wcu-bank-input4').prop('required',true);
			}
			if(jQuery('#wcu-bank-input5').length > 0) {
				jQuery('#wcu-bank-input5').prop('required',true);
			}
			if(jQuery('#wcu-bank-input6').length > 0) {
				jQuery('#wcu-bank-input6').prop('required',true);
			}
			if(jQuery('#wcu-bank-input7').length > 0) {
				jQuery('#wcu-bank-input7').prop('required',true);
			}
		} else {
			if(jQuery('#wcu-bank-input1').length > 0) {
				jQuery('#wcu-bank-input1').prop('required',false);
			}
			if(jQuery('#wcu-bank-input2').length > 0) {
				jQuery('#wcu-bank-input2').prop('required',false);
			}
			if(jQuery('#wcu-bank-input3').length > 0) {
				jQuery('#wcu-bank-input3').prop('required',false);
			}
			if(jQuery('#wcu-bank-input4').length > 0) {
				jQuery('#wcu-bank-input4').prop('required',false);
			}
			if(jQuery('#wcu-bank-input5').length > 0) {
				jQuery('#wcu-bank-input5').prop('required',false);
			}
			if(jQuery('#wcu-bank-input6').length > 0) {
				jQuery('#wcu-bank-input6').prop('required',false);
			}
			if(jQuery('#wcu-bank-input7').length > 0) {
				jQuery('#wcu-bank-input7').prop('required',false);
			}
		}
	});
});
jQuery( document ).ready(function() {
	jQuery( "#wcu-payout-type" ).change();
});

/* 2nd row tabs normal size */
jQuery(document).ready(function($) {

	if( $('.wcutablinks').length > 5 ) {		

		var $buttons = $('.wcutablinks');
		var firstRowTop = $buttons.first().offset().top;

		// Reset flex-grow for all buttons
		$buttons.css('flex-grow', 0);

		// Apply flex-grow only to buttons in the first row
		$buttons.each(function() {
			if ($(this).offset().top === firstRowTop) {
				$(this).css('flex-grow', 1);
			}
		});

		// Handle window resize
		$(window).resize(function() {
			var firstRowTop = $buttons.first().offset().top;

			// Reset flex-grow for all buttons
			$buttons.css('flex-grow', 0);

			// Re-apply flex-grow to buttons in the first row
			$buttons.each(function() {
				if ($(this).offset().top === firstRowTop) {
					$(this).css('flex-grow', 1);
				}
			});
		});

	}

});