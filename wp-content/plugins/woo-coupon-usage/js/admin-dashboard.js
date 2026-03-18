// JS migrated from inline scripts in admin-dashboard.php
jQuery(document).ready(function($) {
    // Changelog modal logic
    $('#close-changelog-modal').on('click', function() {
        $('#changelog-modal').hide();
    });
    // Dropdown centering
    $('.wcusage-admin-menu-dropdown-list').each(function() {
        var parentWidth = $(this).outerWidth();
        $(this).css('--parent-link-width', parentWidth + 'px');
    });
    // Dropdown toggles with portal logic
    $('.wcusage-admin-menu-dropdown > a').on('click', function(e) {
        e.preventDefault();
        var $dropdown = $(this).next('.wcusage-admin-menu-dropdown-list');
        var $parent = $(this).parent();
        $('.wcusage-admin-menu-dropdown-list:visible, #wcusage-admin-bell-dropdown:visible').each(function() {
            $(this).hide();
            if ($(this).data('portal')) {
                $(this).appendTo($(this).data('originalParent'));
                $(this).removeData('portal').removeData('originalParent');
            }
        });
        if ($dropdown.is(':visible')) {
            return;
        }
        var offset = $(this).offset();
        var height = $(this).outerHeight();
        var parentWidth = $(this).outerWidth();
        $dropdown.data('originalParent', $parent);
        $dropdown.appendTo('body').css({
            display: 'block',
            position: 'absolute',
            left: offset.left + parentWidth / 2,
            top: offset.top + height,
            minWidth: parentWidth,
            zIndex: 9999
        }).data('portal', true);
    });
    // Bell dropdown portal logic
    $('#wcusage-admin-bell').on('click', function(e) {
        e.preventDefault();
        var $dropdown = $('#wcusage-admin-bell-dropdown');
        var $parent = $(this).parent();
        if ($dropdown.is(':visible')) {
            $dropdown.hide();
            if ($dropdown.data('portal')) {
                $dropdown.appendTo($dropdown.data('originalParent'));
                $dropdown.removeData('portal').removeData('originalParent');
            }
            return;
        }
        var offset = $(this).offset();
        var height = $(this).outerHeight();
        var parentWidth = $(this).outerWidth();
        $dropdown.data('originalParent', $parent);
        $dropdown.appendTo('body').css({
            display: 'block',
            position: 'absolute',
            left: offset.left + parentWidth / 2,
            top: offset.top + height,
            minWidth: parentWidth,
            zIndex: 99999
        }).data('portal', true);
    });
    // Hide dropdowns on outside click
    $(document).on('mousedown', function(e) {
        $('.wcusage-admin-menu-dropdown-list:visible, #wcusage-admin-bell-dropdown:visible').each(function() {
            if (!$(e.target).closest(this).length && !$(e.target).closest('.wcusage-admin-menu-dropdown > a, #wcusage-admin-bell').length) {
                $(this).hide();
                if ($(this).data('portal')) {
                    $(this).appendTo($(this).data('originalParent'));
                    $(this).removeData('portal').removeData('originalParent');
                }
            }
        });
    });

    // Dashboard sections drag & drop ordering
    var $sectionsContainer = $('.wcusage-admin-page-col-section');
    if ($sectionsContainer.length && typeof $.fn.sortable === 'function' && window.WCUsageDashboard) {
        $sectionsContainer.sortable({
            items: '.wcusage-dashboard-section-item',
            handle: '.wcusage-drag-handle',
            tolerance: 'pointer',
            placeholder: 'wcusage-dashboard-section-placeholder',
            forcePlaceholderSize: true,
            cancel: 'input,textarea,button,a,select',
            update: function() {
                var order = [];
                $sectionsContainer.find('.wcusage-dashboard-section-item').each(function() {
                    var key = $(this).data('section-key');
                    if (key) { order.push(String(key)); }
                });
                if (order.length) {
                    $.post(WCUsageDashboard.ajaxUrl, {
                        action: 'wcusage_save_dashboard_order',
                        nonce: WCUsageDashboard.nonce,
                        order: order
                    });
                }
            }
        });
    }

    // AJAX pagination for dashboard section tables
    function updatePaginationUI($pager, data) {
        var page = parseInt(data.page, 10) || 1;
        $pager.attr('data-page', page);
        $pager.find('.wcusage-page-indicator').text('Page ' + page);
        $pager.find('.wcusage-page-prev').prop('disabled', !data.has_prev);
        $pager.find('.wcusage-page-next').prop('disabled', !data.has_next);
    }

    function loadDashboardPage($pager, targetPage) {
        if (!window.WCUsageDashboard) return;
        var section = $pager.data('section');
        var perPage = parseInt($pager.data('per-page'), 10) || 5;
    var $scope = $pager.closest('.wcusage-admin-page-col, .wcusage-affiliates-sidebar, .wcusage-affiliates-section');
    var $tbody = $scope.find('#wcusage-tbody-' + section + ', #wcusage-list-' + section);

        // Prevent overlapping requests per pager
        if ($pager.data('loading')) return;
        $pager.data('loading', true);
        // Temporarily disable buttons during load
        $pager.find('button').prop('disabled', true);

        $.post(WCUsageDashboard.ajaxUrl, {
            action: 'wcusage_dashboard_paginate',
            nonce: WCUsageDashboard.paginationNonce,
            section: section,
            page: targetPage,
            per_page: perPage
        }).done(function(resp){
            if (resp && resp.success) {
                if ($tbody.length) {
                    $tbody.html(resp.data.html || '');
                }
                // Persist total for future fallback UI if needed
                if (resp.data && typeof resp.data.total !== 'undefined') {
                    $pager.attr('data-total', String(resp.data.total));
                    // If latest affiliates refreshed, sync the header total number
                    if (section === 'affiliates_latest') {
                        $('.wcusage-affiliates-total-number').text(resp.data.total);
                    }
                }
                updatePaginationUI($pager, resp.data);
            }
        }).fail(function(){
            // Fallback: re-enable controls based on current page/per_page/total
            var curr = parseInt($pager.attr('data-page'), 10) || 1;
            var pp = parseInt($pager.attr('data-per-page'), 10) || 5;
            var total = parseInt($pager.attr('data-total'), 10) || 0;
            var hasPrev = curr > 1;
            var hasNext = (curr * pp) < total;
            $pager.find('.wcusage-page-prev').prop('disabled', !hasPrev);
            $pager.find('.wcusage-page-next').prop('disabled', !hasNext);
            $pager.find('.wcusage-page-indicator').text('Page ' + curr);
        }).always(function(){
            // Clear loading flag
            $pager.data('loading', false);
        });
    }

    $(document).on('click', '.wcusage-pagination .wcusage-page-prev', function(){
        var $pager = $(this).closest('.wcusage-pagination');
        var current = parseInt($pager.attr('data-page'), 10) || 1;
        if (current > 1) {
            loadDashboardPage($pager, current - 1);
        }
    });

    $(document).on('click', '.wcusage-pagination .wcusage-page-next', function(){
        var $pager = $(this).closest('.wcusage-pagination');
        var current = parseInt($pager.attr('data-page'), 10) || 1;
        loadDashboardPage($pager, current + 1);
    });

    // Clear cache button
    $('#wcusage-clear-cache-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalHtml = $btn.html();
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<span class="fa-solid fa-spinner fa-spin"></span> ' + 'Clearing...');
        
        $.ajax({
            url: WCUsageDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wcusage_clear_dashboard_caches',
                nonce: WCUsageDashboard.clearCacheNonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.html('<span class="fa-solid fa-check"></span> ' + 'Cleared!');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert(response.data.message || 'Failed to clear cache.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                alert('Error clearing cache. Please try again.');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Note: Removed auto-refresh functionality (previously 20s interval) per request
});
