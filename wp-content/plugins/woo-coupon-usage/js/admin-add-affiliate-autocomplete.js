/* Username autocomplete for Add New Affiliate page */
(function($){
	if(window.console){ console.debug('[WCUsage] Username autocomplete script loaded'); }
	if( typeof WCUsageAffiliateAutocomplete === 'undefined') { return; }
	var cfg = WCUsageAffiliateAutocomplete;
	var $input = $('#wcu-input-username');
	if(!$input.length){ return; }

	var $wrap = $('<div class="wcu-username-autocomplete-wrapper" style="position:relative;display:inline-block;width:100%;"></div>');
	$input.after($wrap);
	$wrap.append($input);
	var $list = $('<ul class="wcu-username-autocomplete" role="listbox" aria-label="User suggestions" style="position:absolute;z-index:9999;top:100%;left:0;right:0;max-height:230px;overflow:auto;margin:2px 0 0;padding:0;list-style:none;background:#fff;border:1px solid #ccd0d4;box-shadow:0 2px 5px rgba(0,0,0,.15);display:none;"></ul>');
	$wrap.append($list);

	var debounceTimer = null;
	var activeIndex = -1;
	var suggestions = [];

	function render(){
		$list.empty();
		if(!suggestions.length){
			if($input.val().length >= cfg.minChars){
				$('<li role="option" aria-disabled="true" style="padding:6px 10px;color:#666;font-style:italic;">'+ cfg.noResults +'</li>').appendTo($list);
				$list.show();
			} else {
				$list.hide();
			}
			return;
		}
		suggestions.forEach(function(s, i){
			var $li = $('<li role="option" tabindex="-1" style="padding:6px 10px;cursor:pointer;"></li>');
			$li.text(s.login + (s.email ? ' ('+ s.email +')' : ''));
			$li.data('value', s.login);
			if(i === activeIndex){ $li.css({'background':'#2271b1','color':'#fff'}); }
			$li.on('mousedown', function(e){ // mousedown so it fires before input blur
				e.preventDefault();
				choose($(this).data('value'));
			});
			$list.append($li);
		});
		$list.show();
	}

	function choose(val){
		$input.val(val);
		hide();
		// Trigger change so existing logic (username existence check) runs
		$input.trigger('change');
	}

	function hide(){
		$list.hide();
		activeIndex = -1;
	}

	function request(){
		var term = $input.val().trim();
		if(term.length < cfg.minChars){ suggestions = []; render(); return; }
		$.ajax({
			type:'POST',
			url: cfg.ajaxUrl,
			data:{ action: 'wcusage_search_usernames', nonce: cfg.nonce, term: term },
			dataType:'json'
		}).done(function(res){
			if(res && res.success){
				suggestions = res.data.results || [];
				activeIndex = -1;
				render();
			}
		});
	}

	$input.on('input', function(){
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(request, 220);
	});

	$input.on('keydown', function(e){
		if(!$list.is(':visible')) { return; }
		var max = suggestions.length - 1;
		if(e.key === 'ArrowDown'){
			e.preventDefault();
			if(!suggestions.length){ return; }
			activeIndex = (activeIndex + 1) > max ? 0 : activeIndex + 1;
			render();
		} else if(e.key === 'ArrowUp') {
			e.preventDefault();
			if(!suggestions.length){ return; }
			activeIndex = (activeIndex - 1) < 0 ? max : activeIndex - 1;
			render();
		} else if(e.key === 'Enter') {
			if(activeIndex > -1 && suggestions[activeIndex]){
				e.preventDefault();
				choose(suggestions[activeIndex].login);
			} else {
				hide();
			}
		} else if(e.key === 'Escape') {
			hide();
		}
	});

	$(document).on('click', function(e){
		if(!$(e.target).closest('.wcu-username-autocomplete-wrapper').length){ hide(); }
	});
})(jQuery);
