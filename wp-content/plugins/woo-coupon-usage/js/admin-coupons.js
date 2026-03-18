jQuery(document).ready(function($) {
    // For debugging - remove in production
    // alert('WooCommerce Usage Coupons JS Loaded!');

    // Initialize autocomplete for user search
    $('.wcu-autocomplete-user').each(function() {
        var $input = $(this);
        // Determine label preference (username-only vs default)
        var labelPref = $input.data('label') || '';
        // Resolve AJAX URL (fallback to global ajaxurl if localization missing)
        var ajaxUrl = (typeof wcusage_coupons_vars !== 'undefined' && wcusage_coupons_vars.ajax_url) ? wcusage_coupons_vars.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
        // If jQuery UI Autocomplete is unavailable, skip binding to avoid errors
        if (!$.ui || !$.ui.autocomplete || !ajaxUrl) {
            return;
        }
        
        $input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'wcusage_search_users',
                        nonce: (wcusage_coupons_vars && wcusage_coupons_vars.nonce) ? wcusage_coupons_vars.nonce : '',
                        search: request.term,
                        label: labelPref
                    },
                    success: function(data) {
                        if (data.success) {
                            response(data.data);
                        } else {
                            response([]);
                        }
                    },
                    error: function() {
                        response([]);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $input.val(ui.item.value); // Display username
                return false;
            },
            focus: function(event, ui) {
                return false; // Prevent value from being inserted on focus
            },
            close: function() {
                // Optional: Clear if no valid selection (though server will handle invalid usernames)
                var currentValue = $input.val();
                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'wcusage_search_users',
                        nonce: (wcusage_coupons_vars && wcusage_coupons_vars.nonce) ? wcusage_coupons_vars.nonce : '',
                        search: currentValue,
                        label: labelPref
                    },
                    success: function(data) {
                        if (data.success && Array.isArray(data.data) && !data.data.some(function(item){ return item.value === currentValue; })) {
                            $input.val(''); // Clear if not a valid username
                        }
                    }
                });
            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>')
                .append('<div>' + item.label + '</div>')
                .appendTo(ul);
        };

        // Clear field if no valid selection on blur (optional client-side validation)
        $input.on('blur', function() {
            setTimeout(function() {
                var currentValue = $input.val();
                if (currentValue) {
                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        data: {
                            action: 'wcusage_search_users',
                            nonce: (wcusage_coupons_vars && wcusage_coupons_vars.nonce) ? wcusage_coupons_vars.nonce : '',
                            search: currentValue,
                            label: labelPref
                        },
                        success: function(data) {
                            if (data.success && Array.isArray(data.data) && !data.data.some(function(item){ return item.value === currentValue; })) {
                                $input.val('');
                            }
                        }
                    });
                }
            }, 200); // Delay to allow select event to complete
        });
    });

    // Show/hide quick edit form
    $('.quick-edit-coupon').on('click', function(e) {
        e.preventDefault();
        var couponId = $(this).data('coupon-id');
        var $row = $('#quick-edit-' + couponId);
        $row.toggle();
    });

    // Cancel quick edit
    $('.cancel-quick-edit').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.quick-edit-row').hide();
    });

    // Save quick edit
    $('.save-quick-edit').on('click', function(e) {
        e.preventDefault();
        var couponId = $(this).data('coupon-id');
        var $row = $('#quick-edit-' + couponId);
        var $spinner = $row.find('.spinner');
        $spinner.addClass('is-active');

        var formData = {
            action: 'wcusage_save_coupon_data',
            coupon_id: couponId,
            nonce: wcusage_coupons_vars.nonce,
            post_title: $row.find('#coupon_code_' + couponId).val(),
            post_excerpt: $row.find('#coupon_description_' + couponId).val(),
            discount_type: $row.find('#discount_type_' + couponId).val(),
            coupon_amount: $row.find('#coupon_amount_' + couponId).val(),
            free_shipping: $row.find('#free_shipping_' + couponId).is(':checked') ? 'yes' : 'no',
            expiry_date: $row.find('#expiry_date_' + couponId).val(),
            minimum_amount: $row.find('#minimum_amount_' + couponId).val(),
            maximum_amount: $row.find('#maximum_amount_' + couponId).val(),
            individual_use: $row.find('#individual_use_' + couponId).is(':checked') ? 'yes' : 'no',
            exclude_sale_items: $row.find('#exclude_sale_items_' + couponId).is(':checked') ? 'yes' : 'no',
            usage_limit_per_user: $row.find('#usage_limit_per_user_' + couponId).val(),
            wcu_enable_first_order_only: $row.find('#wcu_enable_first_order_only_' + couponId).is(':checked') ? 'yes' : 'no',
            wcu_select_coupon_user: $row.find('#wcu_select_coupon_user_' + couponId).val(), // Use username directly
            wcu_text_coupon_commission: $row.find('#wcu_text_coupon_commission_' + couponId).val(),
            wcu_text_coupon_commission_fixed_order: $row.find('#wcu_text_coupon_commission_fixed_order_' + couponId).val(),
            wcu_text_coupon_commission_fixed_product: $row.find('#wcu_text_coupon_commission_fixed_product_' + couponId).val(),
            wcu_text_unpaid_commission: $row.find('#wcu_text_unpaid_commission_' + couponId).val(),
            wcu_text_pending_payment_commission: $row.find('#wcu_text_pending_payment_commission_' + couponId).val(),
            wcu_text_pending_order_commission: $row.find('#wcu_text_pending_order_commission_' + couponId).val() || '0'
        };

        $.ajax({
            url: wcusage_coupons_vars.ajax_url,
            method: 'POST',
            data: formData,
            success: function(response) {
                $spinner.removeClass('is-active');
                if (response.success) {
                    var $tableRow = $('#coupon-row-' + couponId);
                    
                    // Update Coupon Code
                    $tableRow.find('.column-post_title a').text(formData.post_title);
                    
                    // Update Coupon Type
                    var discountType = formData.discount_type;
                    var amount = formData.coupon_amount;
                    var types = {
                        'percent': wcusage_coupons_vars.types.percent,
                        'fixed_cart': wcusage_coupons_vars.types.fixed_cart,
                        'fixed_product': wcusage_coupons_vars.types.fixed_product,
                        'percent_product': wcusage_coupons_vars.types.percent_product
                    };
                    var display = types[discountType] || discountType;
                    var formattedAmount = amount ? (discountType === 'percent' ? amount + '%' : wcusage_coupons_vars.currency_symbol + amount) : '';
                    $tableRow.find('.column-coupon_type').text(display + (formattedAmount ? ' (' + formattedAmount + ')' : ''));
                    
                    // Update Affiliate User
                    var username = formData.wcu_select_coupon_user;
                    var $affiliateCell = $tableRow.find('.column-affiliate');
                    if (username) {
                        // Show username only; link will be correct on full reload when server renders user ID.
                        $affiliateCell.text(username);
                    } else {
                        $affiliateCell.text('-');
                    }
                    
                    // Update Unpaid Commission
                    var unpaidCommission = formData.wcu_text_unpaid_commission;
                    if (unpaidCommission) {
                        unpaidCommission = parseFloat(unpaidCommission).toFixed(2);
                    }
                    $tableRow.find('.column-unpaid_commission').text(unpaidCommission ? wcusage_coupons_vars.currency_symbol + unpaidCommission : '-');
                    
                    $row.hide();
                } else {
                    alert('Error saving coupon: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                alert('Error saving coupon');
            }
        });
    });

    // Copy referral link functionality (if needed)
    $('.wcusage-copy-link-button').on('click', function() {
        var $input = $(this).siblings('.wcusage-copy-link-text');
        $input.select();
        document.execCommand('copy');
    });

    // Sorting controls: apply selection next to Bulk actions
    function wcusageApplySort() {
        try {
            var url = new URL(window.location.href);
            // Always ensure we're on the coupons page param
            url.searchParams.set('page', 'wcusage_coupons');

            var orderby = $('select.wcusage-orderby-coupon').val() || 'id';
            url.searchParams.set('orderby_coupon', orderby);

            // Pull current filter values from the visible inputs if present
            var aff = $('input[name="affiliate_user"]').val();
            if (typeof aff !== 'undefined') {
                if (aff) { url.searchParams.set('affiliate_user', aff); } else { url.searchParams.delete('affiliate_user'); }
            }
            var code = $('input[name="coupon_code"]').val();
            if (typeof code !== 'undefined') {
                if (code) { url.searchParams.set('coupon_code', code); } else { url.searchParams.delete('coupon_code'); }
            }
            var stat = $('select[name="coupon_status"]').val();
            if (typeof stat !== 'undefined') {
                if (stat) { url.searchParams.set('coupon_status', stat); } else { url.searchParams.delete('coupon_status'); }
            }

            // Preserve or set affiliate_only based on dropdown if present; otherwise keep existing URL value
            var aonly = $('select[name="affiliate_only"]').val();
            if (typeof aonly !== 'undefined') {
                if (aonly) { url.searchParams.set('affiliate_only', aonly); } else { url.searchParams.delete('affiliate_only'); }
            } else {
                var currentParams = new URLSearchParams(window.location.search);
                if (currentParams.get('affiliate_only')) {
                    url.searchParams.set('affiliate_only', currentParams.get('affiliate_only'));
                }
            }

            // Reset pagination when changing sort
            url.searchParams.delete('paged');

            window.location.href = url.toString();
        } catch (e) {
            // Fallback: simple GET to current page with just orderby
            var href = window.location.href.split('?')[0] + '?page=wcusage_coupons&orderby_coupon=' + encodeURIComponent(($('select.wcusage-orderby-coupon').val() || 'id'));
            window.location.href = href;
        }
    }

    // Click handler for Apply button
    $(document).on('click', '.wcusage-apply-sort', function(e) {
        e.preventDefault();
        wcusageApplySort();
    });

    // Optional: apply on change
    $(document).on('change', 'select.wcusage-orderby-coupon', function() {
        // Uncomment to auto-apply on change instead of requiring button click
        // wcusageApplySort();
    });

    // Bulk action confirmation prompts (moved from inline PHP)
    function wcusageConfirmFor(action) {
        if (typeof wcusage_coupons_vars !== 'undefined' && wcusage_coupons_vars.bulk_confirm) {
            if (action === 'bulk-unassign' && wcusage_coupons_vars.bulk_confirm.bulk_unassign) {
                return wcusage_coupons_vars.bulk_confirm.bulk_unassign;
            }
            if (action === 'bulk-delete-coupons' && wcusage_coupons_vars.bulk_confirm.bulk_delete_coupons) {
                return wcusage_coupons_vars.bulk_confirm.bulk_delete_coupons;
            }
            if (action === 'bulk-delete-coupons-and-user' && wcusage_coupons_vars.bulk_confirm.bulk_delete_coupons_and_user) {
                return wcusage_coupons_vars.bulk_confirm.bulk_delete_coupons_and_user;
            }
        }
        return '';
    }

    $(document).on('click', '#doaction, #doaction2', function(e){
        var $select = $(this).siblings('select');
        if (!$select.length) return;
        var action = $select.val();
        if (!action) return;
        var msg = wcusageConfirmFor(action);
        if (msg && !window.confirm(msg)) {
            e.preventDefault();
            return false;
        }
    });

    // Handle Enter key on filter inputs to trigger filter instead of bulk action
    $(document).on('keypress', '.wcusage-admin-title-filters input[name="affiliate_user"], .wcusage-admin-title-filters input[name="coupon_code"]', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            // Find and click the filter button
            $(this).closest('.wcusage-admin-title-filters').find('button[type="submit"]').trigger('click');
            return false;
        }
    });

    // Handle Enter key on filter dropdowns to trigger filter instead of bulk action
    $(document).on('keypress', '.wcusage-admin-title-filters select[name="coupon_status"], .wcusage-admin-title-filters select[name="affiliate_only"]', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            // Find and click the filter button
            $(this).closest('.wcusage-admin-title-filters').find('button[type="submit"]').trigger('click');
            return false;
        }
    });
});