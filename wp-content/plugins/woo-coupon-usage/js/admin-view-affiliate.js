/**
 * Admin View Affiliate Page JavaScript
 */

jQuery(document).ready(function($) {
    // Mobile tab dropdown — inject <select> before the tab bar
    (function() {
        var $tabs = $('.wcusage-tabs');
        if (!$tabs.length) { return; }

        // Build the select element from the existing nav-tab links
        var $select = $('<select class="wcusage-tab-select" aria-label="Navigate tabs"></select>');
        $tabs.find('.nav-tab').each(function() {
            var $tab  = $(this);
            var href  = $tab.attr('href');
            var label = $tab.text().trim();
            var $opt  = $('<option></option>').val(href).text(label);
            if ($tab.hasClass('nav-tab-active')) { $opt.prop('selected', true); }
            if ($tab.hasClass('wcusage-tab-disabled')) { $opt.prop('disabled', true); }
            $select.append($opt);
        });

        var $wrapper = $('<div class="wcusage-tab-select-wrapper"></div>').append($select);
        $tabs.before($wrapper);

        $select.on('change', function() {
            var href  = $(this).val();
            var $link = $tabs.find('.nav-tab[href="' + href + '"]');
            if ($link.length) { $link.trigger('click'); }
        });
    })();

    // Tab switching functionality
    $('.wcusage-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();

        // Remove active class from all tabs
        $('.wcusage-tabs .nav-tab').removeClass('nav-tab-active');
        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');

        // Hide all tab content
        $('.wcusage-tab-content > div').removeClass('active');

        // Get tab ID from href
        var tabId = $(this).attr('href').replace('#', '');
        // Show the corresponding tab content
        $('#' + tabId).addClass('active');

        // If MLA tab, draw chart on demand
        if (tabId === 'tab-mla' && typeof window.WCU_MLA_draw === 'function') {
            try { window.WCU_MLA_draw(); } catch(e) {}
        }

        // Sync mobile dropdown
        $('.wcusage-tab-select').val('#' + tabId);

        // Update URL without page reload
        var tab = tabId.replace('tab-', '');
        var newUrl = window.location.pathname + window.location.search.replace(/([&?]tab=)[^&]*/, '$1' + tab);
        if (window.location.search.indexOf('tab=') === -1) {
            newUrl += (window.location.search ? '&' : '?') + 'tab=' + tab;
        }
        history.pushState(null, null, newUrl);
    });

    // Handle browser back/forward buttons
    $(window).on('popstate', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var tab = urlParams.get('tab') || 'overview';
        $('.wcusage-tabs .nav-tab').removeClass('nav-tab-active');
        $('.wcusage-tabs .nav-tab[href="#tab-' + tab + '"]').addClass('nav-tab-active');
        $('.wcusage-tab-content > div').removeClass('active');
        $('#tab-' + tab).addClass('active');

        // Sync mobile dropdown
        $('.wcusage-tab-select').val('#tab-' + tab);

        // If MLA tab is now active, ensure chart draws
        if (tab === 'mla' && typeof window.WCU_MLA_draw === 'function') {
            try { window.WCU_MLA_draw(); } catch(e) {}
        }
    });

    // On initial load, if MLA tab is active from URL, draw immediately
    (function(){
        var urlParams = new URLSearchParams(window.location.search);
        var initTab = urlParams.get('tab') || 'overview';
        if (initTab === 'mla' && typeof window.WCU_MLA_draw === 'function') {
            try { window.WCU_MLA_draw(); } catch(e) {}
        }
        // Or if markup already marks MLA tab active
        if ($('#tab-mla').hasClass('active') && typeof window.WCU_MLA_draw === 'function') {
            try { window.WCU_MLA_draw(); } catch(e) {}
        }
    })();

    // Helpers
    function getDateVal(selector) {
        var v = $(selector).val();
        return v ? v : '';
    }

    function ajaxLoad(type, page) {
        var actionMap = {
            referrals: { action: 'wcusage_get_affiliate_referrals', nonce: WCUAdminAffiliateView.nonce_referrals, container: '#wcusage-referrals-table-container', start: '#referrals-start-date', end: '#referrals-end-date' },
            visits: { action: 'wcusage_get_affiliate_visits', nonce: WCUAdminAffiliateView.nonce_visits, container: '#wcusage-visits-table-container', start: '#visits-start-date', end: '#visits-end-date' },
            payouts: { action: 'wcusage_get_affiliate_payouts', nonce: WCUAdminAffiliateView.nonce_payouts, container: '#wcusage-payouts-table-container', start: '#payouts-start-date', end: '#payouts-end-date' },
            activity: { action: 'wcusage_get_affiliate_activity', nonce: WCUAdminAffiliateView.nonce_activity, container: '#wcusage-activity-table-container', start: '#activity-start-date', end: '#activity-end-date' }
        };
        var cfg = actionMap[type];
        if (!cfg) return;
        if (typeof WCUAdminAffiliateView === 'undefined') {
            console.error('WCUAdminAffiliateView not available');
            return;
        }
        var data = {
            action: cfg.action,
            _wpnonce: cfg.nonce,
            user_id: WCUAdminAffiliateView.user_id,
            page: page || 1,
            per_page: WCUAdminAffiliateView.per_page,
            start_date: getDateVal(cfg.start),
            end_date: getDateVal(cfg.end),
            _ts: Date.now()
        };
        var $container = $(cfg.container);
        var $btns = $(cfg.container + ' .pagination-links a.button');
        $btns.prop('disabled', true);
        $container.addClass('wcusage-loading');
        $.ajax({
            url: WCUAdminAffiliateView.ajax_url,
            method: 'POST',
            data: data,
            dataType: 'html'
        }).done(function(html){
            $container.html(html);
        }).fail(function(jqXHR){
            console.error('AJAX failed', jqXHR.status, jqXHR.responseText);
            $container.html('<div class="notice notice-error"><p>Failed to load data. Please reload the page.</p></div>');
        }).always(function(){
            $container.removeClass('wcusage-loading');
            $(cfg.container + ' .pagination-links a.button').prop('disabled', false);
        });
    }

    // Apply filter buttons
    $('#referrals-apply-filters').on('click', function(e){ e.preventDefault(); ajaxLoad('referrals', 1); });
    $('#visits-apply-filters').on('click', function(e){ e.preventDefault(); ajaxLoad('visits', 1); });
    $('#payouts-apply-filters').on('click', function(e){ e.preventDefault(); ajaxLoad('payouts', 1); });
    $('#activity-apply-filters').on('click', function(e){ e.preventDefault(); ajaxLoad('activity', 1); });

    // Pagination link clicks (delegated)
    $('#wcusage-referrals-table-container').on('click', '.pagination-links a.button', function(e){
        e.preventDefault();
        if ($(this).attr('aria-disabled') === 'true' || $(this).prop('disabled')) return;
        var page = parseInt($(this).data('page'), 10) || 1;
        ajaxLoad('referrals', page);
    });
    $('#wcusage-visits-table-container').on('click', '.pagination-links a.button', function(e){
        e.preventDefault();
        if ($(this).attr('aria-disabled') === 'true' || $(this).prop('disabled')) return;
        var page = parseInt($(this).data('page'), 10) || 1;
        ajaxLoad('visits', page);
    });
    $('#wcusage-payouts-table-container').on('click', '.pagination-links a.button', function(e){
        e.preventDefault();
        if ($(this).attr('aria-disabled') === 'true' || $(this).prop('disabled')) return;
        var page = parseInt($(this).data('page'), 10) || 1;
        ajaxLoad('payouts', page);
    });
    $('#wcusage-activity-table-container').on('click', '.pagination-links a.button', function(e){
        e.preventDefault();
        if ($(this).attr('aria-disabled') === 'true' || $(this).prop('disabled')) return;
        var page = parseInt($(this).data('page'), 10) || 1;
        ajaxLoad('activity', page);
    });

    // Direct page input (Enter key)
    function bindPageInput(containerSel, type) {
        $(containerSel).on('keydown', '.paging-input .current-page', function(e){
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                var val = parseInt($(this).val(), 10) || 1;
                ajaxLoad(type, val);
            }
        });
    }
    bindPageInput('#wcusage-referrals-table-container', 'referrals');
    bindPageInput('#wcusage-visits-table-container', 'visits');
    bindPageInput('#wcusage-payouts-table-container', 'payouts');
    bindPageInput('#wcusage-activity-table-container', 'activity');

    // Copy referral link in Affiliate Coupons list (works even if input is hidden)
    $(document).on('click', '.wcusage-copy-link-button', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        var $input = $(this).siblings('.wcusage-copy-link-text');
        if (!$input.length) return;
        var text = $input.val();

        function fallbackCopy(t) {
            var ta = document.createElement('textarea');
            ta.value = t;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); } catch(e) { console.warn('Copy failed', e); }
            document.body.removeChild(ta);
        }

    if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).catch(function(){ fallbackCopy(text); });
        } else {
            fallbackCopy(text);
        }
    });

    // Quick Edit: toggle
    $(document).on('click', '.quick-edit-coupon', function(e) {
        e.preventDefault();
        var id = $(this).data('coupon-id');
        $('#quick-edit-' + id).toggle();
    });

    // Quick Edit: cancel
    $(document).on('click', '.cancel-quick-edit', function(e) {
        e.preventDefault();
        $(this).closest('.quick-edit-row').hide();
    });

    // Init user autocomplete inputs created in the DOM
    function initUserAutocomplete($input) {
        try {
            $input.autocomplete({
                source: function(request, response) {
                    $.post(WCUAdminAffiliateView.ajax_url, {
                        action: 'wcusage_search_users',
                        nonce: WCUAdminAffiliateView.coupon_nonce,
                        search: request.term,
                        label: 'username'
                    }).done(function(data){
                        if (data && data.success) response(data.data); else response([]);
                    }).fail(function(){ response([]); });
                },
                minLength: 2
            }).autocomplete('instance')._renderItem = function(ul, item) {
                return $('<li>').append('<div>' + item.label + '</div>').appendTo(ul);
            };
        } catch(e) {}
    }
    $(document).on('focus', '.wcu-autocomplete-user', function(){
        if (!$(this).data('ui-autocomplete')) initUserAutocomplete($(this));
    });

    // Quick Edit: save
    $(document).on('click', '.save-quick-edit', function(e) {
        e.preventDefault();
        var id = $(this).data('coupon-id');
        var $row = $('#quick-edit-' + id);
        var $spinner = $row.find('.spinner');
        $spinner.addClass('is-active');

        function val(sel){ return $row.find(sel).val(); }
        function checked(sel){ return $row.find(sel).is(':checked') ? 'yes' : 'no'; }

        var payload = {
            action: 'wcusage_save_coupon_data',
            nonce: WCUAdminAffiliateView.coupon_nonce,
            coupon_id: id,
            post_title: val('#coupon_code_' + id),
            post_excerpt: val('#coupon_description_' + id),
            discount_type: val('#discount_type_' + id),
            coupon_amount: val('#coupon_amount_' + id),
            free_shipping: checked('#free_shipping_' + id),
            expiry_date: val('#expiry_date_' + id),
            minimum_amount: val('#minimum_amount_' + id),
            maximum_amount: val('#maximum_amount_' + id),
            individual_use: checked('#individual_use_' + id),
            exclude_sale_items: checked('#exclude_sale_items_' + id),
            usage_limit_per_user: val('#usage_limit_per_user_' + id),
            wcu_enable_first_order_only: checked('#wcu_enable_first_order_only_' + id),
            wcu_select_coupon_user: val('#wcu_select_coupon_user_' + id),
            wcu_text_coupon_commission: val('#wcu_text_coupon_commission_' + id),
            wcu_text_coupon_commission_fixed_order: val('#wcu_text_coupon_commission_fixed_order_' + id),
            wcu_text_coupon_commission_fixed_product: val('#wcu_text_coupon_commission_fixed_product_' + id),
            wcu_text_unpaid_commission: val('#wcu_text_unpaid_commission_' + id),
            wcu_text_pending_payment_commission: val('#wcu_text_pending_payment_commission_' + id)
        };

        $.ajax({ url: WCUAdminAffiliateView.ajax_url, method: 'POST', data: payload })
        .done(function(resp){
            if (resp && resp.success) {
                // update the main row values we can safely adjust
                var $tr = $('#coupon-row-' + id);
                // Coupon code
                $tr.find('td').eq(0).text(payload.post_title);
                // We won't recompute stats here; they update on next refresh
                $row.hide();
            } else {
                alert('Save failed');
            }
        })
        .fail(function(){ alert('Save failed'); })
        .always(function(){ $spinner.removeClass('is-active'); });
    });
});

