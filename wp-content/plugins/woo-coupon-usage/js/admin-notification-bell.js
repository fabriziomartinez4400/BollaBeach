jQuery(document).ready(function($){
    var bellShakeInterval;
    var bellCheckInterval;
    var originalTitle = document.title;
    var currentNotificationCount = 0;

    // Load bell data on page load
    loadBellData();

    // Start periodic checking for new notifications
    startPeriodicCheck();

    // Listen for tab visibility changes
    $(document).on('visibilitychange', handleVisibilityChange);

    // Handle bell click to show/hide dropdown
    var bellDisabled = false;
    $('#wcusage-admin-bell').on('click', function(e){
        if (bellDisabled) {
            e.preventDefault();
            return;
        }
        e.preventDefault();
        bellDisabled = true;
        // Visually disable bell and prevent pointer events
        $('#wcusage-admin-bell').css({'pointer-events':'none'});
        setTimeout(function(){
            bellDisabled = false;
            $('#wcusage-admin-bell').css({'pointer-events':'auto'});
        }, 2000);
        // Always reload dropdown data and show it, even if notifications are disabled
        // Ensure placeholder exists
        if ($('#wcusage-admin-bell-dropdown-placeholder').length === 0) {
            $('.wcusage-admin-bell-container').append('<div id="wcusage-admin-bell-dropdown-placeholder"></div>');
        }
        loadBellData(true, true, function() {
            if (!bellDisabled) {
                $('#wcusage-admin-bell-dropdown').show();
            }
        });
    });

    // Handle toggle notifications click
    $(document).on('click', '#wcusage-toggle-notifications', function(e){
        e.preventDefault();
        $('#wcusage-admin-bell-dropdown').remove();
        // Ensure placeholder exists
        if ($('#wcusage-admin-bell-dropdown-placeholder').length === 0) {
            $('.wcusage-admin-bell-container').append('<div id="wcusage-admin-bell-dropdown-placeholder"></div>');
        } else {
            $('#wcusage-admin-bell-dropdown-placeholder').empty();
        }
        $.post(wcusageAdminBell.ajax_url, {
            action: 'wcusage_toggle_admin_notifications',
            nonce: wcusageAdminBell.nonce
        }, function(response){
            loadBellData(true, true, function() {
                $('#wcusage-admin-bell-dropdown').show();
            });
        });
    });

    function loadBellData(updateDate, updateDropdown, callback) {
        var data = {
            action: 'wcusage_admin_bell_data',
            nonce: wcusageAdminBell.nonce
        };
        if (updateDate) {
            data.update_date = '1';
        }
        $.post(wcusageAdminBell.ajax_url, data, function(response){
            currentNotificationCount = response.count;
            if (response.count > 0) {
                $('.wcusage-admin-bell-count').text(response.count).show();
                startBellShake();
            } else {
                $('.wcusage-admin-bell-count').hide();
                stopBellShake();
            }
            if (updateDropdown !== false) {
                if (response.dropdown_html && response.dropdown_html.trim() !== '') {
                    $('#wcusage-admin-bell-dropdown-placeholder').html(response.dropdown_html);
                } else {
                    $('#wcusage-admin-bell-dropdown').remove();
                }
            }
            $('#wcusage-admin-bell').css('opacity', response.enabled == '1' ? '1' : '0.5');
            updateTabTitle();
            if (callback) callback();
        });
    }

    function startPeriodicCheck() {
        bellCheckInterval = setInterval(function(){
            loadBellData(false, false); // Don't update date, don't update dropdown
        }, 10000); // Every 10 seconds
    }

    function stopPeriodicCheck() {
        if (bellCheckInterval) {
            clearInterval(bellCheckInterval);
            bellCheckInterval = null;
        }
    }

    function startBellShake() {
        if (bellShakeInterval) return; // Already shaking
        $('.fa-bell').addClass('wcusage-bell-shake');
        bellShakeInterval = setInterval(function(){
            $('.fa-bell').addClass('wcusage-bell-shake');
            setTimeout(function(){
                $('.fa-bell').removeClass('wcusage-bell-shake');
            }, 500); // Animation duration
        }, 2000); // Every 5 seconds
    }

    function stopBellShake() {
        if (bellShakeInterval) {
            clearInterval(bellShakeInterval);
            bellShakeInterval = null;
        }
        $('.fa-bell').removeClass('wcusage-bell-shake');
    }

    function handleVisibilityChange() {
        if (document.hidden) {
            // Tab is now hidden
            updateTabTitle();
        } else {
            // Tab is now visible
            restoreTabTitle();
        }
    }

    function updateTabTitle() {
        if (document.hidden && currentNotificationCount > 0) {
            document.title = '(' + currentNotificationCount + ') ' + originalTitle;
        }
    }

    function restoreTabTitle() {
        document.title = originalTitle;
    }
});