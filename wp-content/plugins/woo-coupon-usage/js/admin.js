jQuery(document).ready(function($) {
    // Hide the View Affiliate submenu item on all pages
    $('#toplevel_page_wcusage .wp-submenu a[href*="page=wcusage_view_affiliate"]').parent('li').hide();

    // Event delegation for copy link button
    $(document).on('click', '.wcusage-copy-link-button', function() {

        // Find the input element associated with the clicked button
        var $linkInput = $(this).prev('input[type="text"]');

        // Disable copy button for 1 second
        $(this).prop('disabled', true);

        // Store the original text
        var $originalText = $linkInput.val();

        // Save the original text to the clipboard
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($originalText).select();
        document.execCommand("copy");
        $temp.remove();

        // Highlight the text in the input field for 1 second
        $linkInput.select();
        var $button = $(this);
        setTimeout(function() {
            $button.prop('disabled', false);
            // Deselect the text
            $linkInput.blur();
            // Restore the original text
            $linkInput.val($originalText);
        }
        , 1000);

        try {
            // Copy the text to the clipboard
            var successful = document.execCommand('copy');
        } catch (err) {
            console.log('Oops, unable to copy');
        }

    });
    
    // Show tooltip content when hovering over the tooltip icon
    $('.custom-tooltip').hover(function() {
        $(this).find('.tooltip-content').show();
    }, function() {
        $(this).find('.tooltip-content').hide();
    });

    // Keep the tooltip content open when hovering over it
    $('.custom-tooltip .tooltip-content').hover(function() {
        $(this).show();
    }, function() {
        $(this).hide();
    });

    // Function to position the tooltip
    function positionTooltip(tooltip) {
        var tooltipContent = tooltip.find('.tooltip-content');
        var tooltipWidth = tooltipContent.outerWidth();
        var tooltipHeight = tooltipContent.outerHeight();

        var windowWidth = $(window).width();
        var windowHeight = $(window).height();

        var tooltipOffset = tooltip.offset();
        var tooltipLeft = tooltipOffset.left;
        var tooltipTop = tooltipOffset.top;

        // Adjust horizontal position if needed
        if (tooltipLeft + tooltipWidth > windowWidth) {
            tooltipLeft = windowWidth - tooltipWidth;
        }

        // Adjust vertical position if needed
        if (tooltipTop + tooltipHeight > windowHeight) {
            tooltipTop = windowHeight - tooltipHeight;
        }

        // Only adjust if necessary, don't override CSS positioning
        // Since CSS handles positioning, this function may not be needed
    }

    // Delete dropdown functionality
    $(document).on('click', '.wcusage-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close any other open menus
        $('.wcusage-delete-menu').hide();
        
        // Toggle this menu
        $(this).next('.wcusage-delete-menu').toggle();
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        // Don't hide delete menu if clicking in admin menu
        if ($(e.target).closest('#adminmenu').length) return;
        if (!$(e.target).closest('.wcusage-delete-dropdown').length) {
            $('.wcusage-delete-menu').hide();
        }
    });

    // Handle delete option clicks
    $(document).on('click', '.wcusage-delete-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var action = $(this).data('action');
        var userId = $(this).data('user-id');
        var nonce = $(this).data('nonce');
        
        // Prevent multiple clicks
        if ($(this).hasClass('processing')) {
            return false;
        }
        
        $(this).addClass('processing');
        
        var actionText = action.replace(/_/g, ' ');
        var confirmMessage = 'Are you sure you want to ' + actionText + ' for this user? This action cannot be undone.';
        
        if (confirm(confirmMessage)) {
            // Create form and submit
            var form = $('<form>', {
                'method': 'POST',
                'action': window.location.href
            });
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'wcusage_delete_action',
                'value': action
            }));
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'wcusage_user_id',
                'value': userId
            }));
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_wpnonce',
                'value': nonce
            }));
            
            $('body').append(form);
            form.submit();
        } else {
            // Remove processing class if user cancels
            $(this).removeClass('processing');
        }
    });

});