jQuery(document).ready(function($) {
    
    // Handle tooltip positioning for scrollable coupon lists
    $('.wcusage-affiliate-coupons-scrollable .custom-tooltip').each(function() {
        var $tooltip = $(this);
        var $tooltipTrigger = $tooltip.find('.wcusage-tooltip-trigger');
        var $tooltipContent = $tooltip.find('.tooltip-content');
        var $scrollContainer = $tooltip.closest('.wcusage-affiliate-coupons-scrollable');
        var $wrapper = $tooltip.closest('.wcusage-affiliate-coupons-wrapper');
        
        // Move tooltip content to wrapper level to escape overflow clipping
        if ($wrapper.length && $scrollContainer.length) {
            // Unbind all default tooltip handlers
            $tooltip.off('mouseenter mouseleave');
            $tooltipTrigger.off('mouseenter mouseleave');
            $tooltipContent.off('mouseenter mouseleave');
            
            // Only trigger on the actual link text, not the container
            $tooltipTrigger.on('mouseenter', function(e) {
                e.stopPropagation(); // Prevent bubbling to parent elements
                
                // Prevent the default tooltip from showing
                $tooltipContent.hide();
                
                // Get trigger position relative to the document
                var triggerOffset = $tooltipTrigger.offset();
                var wrapperOffset = $wrapper.offset();
                var triggerWidth = $tooltipTrigger.outerWidth();
                
                // Calculate position
                var leftPosition = triggerOffset.left - wrapperOffset.left + (triggerWidth / 2);
                var topPosition = triggerOffset.top - wrapperOffset.top - $tooltipContent.outerHeight() - 5;
                
                // Clone and show the tooltip at wrapper level
                var $clone = $tooltipContent.clone()
                    .addClass('wcusage-tooltip-clone')
                    .css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible',
                        'position': 'absolute',
                        'left': leftPosition + 'px',
                        'top': topPosition + 'px',
                        'transform': 'translateX(-50%)',
                        'bottom': 'auto'
                    })
                    .appendTo($wrapper);
                
                // Store reference for cleanup
                $tooltipTrigger.data('tooltip-clone', $clone);
            });
            
            $tooltipTrigger.on('mouseleave', function(e) {
                e.stopPropagation();
                var $clone = $tooltipTrigger.data('tooltip-clone');
                if ($clone) {
                    setTimeout(function() {
                        if (!$clone.is(':hover')) {
                            $clone.remove();
                        }
                    }, 100);
                }
            });
            
            // Prevent tooltip container from triggering anything
            $tooltip.on('mouseenter mouseleave', function(e) {
                e.stopPropagation();
                return false;
            });
            
            // Handle hover on cloned tooltip
            $wrapper.on('mouseenter', '.wcusage-tooltip-clone', function() {
                $(this).data('hovering', true);
            });
            
            $wrapper.on('mouseleave', '.wcusage-tooltip-clone', function() {
                $(this).remove();
            });
        }
    });
});
