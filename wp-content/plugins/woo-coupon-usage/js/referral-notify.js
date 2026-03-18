jQuery(document).ready(function($){
    // On page load, fetch referral notification via AJAX
    $.post(wcusageReferralNotify.ajax_url, {
        action: 'wcusage_referral_notify',
        nonce: wcusageReferralNotify.nonce
    }, function(response){
        var referralCount = response.count;
        var bellCount = $('.wcusage-admin-bell-count');
        var referralSection = $('#wcusage-admin-bell-referral-section');
        var referralMsg = $('#wcusage-admin-bell-referral-message');
        var currentTotal = parseInt(bellCount.text()) || 0;
        if (referralCount > 0) {
            bellCount.text(currentTotal + 1);
            referralSection.show();
            referralMsg.find('a').text(response.message);
        } else {
            referralSection.hide();
        }
    });

    // Track if referral notification has already been cleared
    var referralCleared = false;
    $('#wcusage-admin-bell').on('click', function(e){
        e.preventDefault();
        if (referralCleared) return;
        $.post(wcusageReferralNotify.ajax_url, {
            action: 'wcusage_referral_notify',
            nonce: wcusageReferralNotify.nonce,
            update_date: 1
        }, function(response){
            var referralCount = response.count;
            var bellCount = $('.wcusage-admin-bell-count');
            var currentTotal = parseInt(bellCount.text()) || 0;
            // Only decrease by 1 if there was at least 1 new referral tracked
            if (referralCount > 0 && currentTotal > 0) {
                bellCount.text(currentTotal - 1);
                referralCleared = true;
            }
        });
    });
});
