jQuery(document).ready(function($) {
    // Store the current coupon title
    var originalTitle = couponTitleData.currentTitle;
    // Listen for the form submission
    $('#post').on('submit', function(e) {
        // Get the new coupon title
        var newTitle = $('#title').val();
        // Check if the title has been changed
        if (newTitle !== originalTitle) {
            // Show the confirmation popup
            if (!confirm(couponTitleData.warningMessage)) {
                // If the user clicks "Cancel", prevent the form from submitting
                e.preventDefault();
                return false;
            }
        }
    });
});