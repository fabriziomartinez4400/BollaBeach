jQuery(document).ready(function($){
    // Checkbox enable/disable based on selected bulk action
    function updateCheckboxStates(){
        var action = $('#bulk-action-selector-top').val();
        var action2 = $('#bulk-action-selector-bottom').val();
        var selectedAction = action !== '-1' ? action : action2;
        if (selectedAction === 'update_unpaid_commission') {
            $('input[name="bulk-delete[]"]').each(function(){
                var can = $(this).data('can-update-commission');
                if (can === 1 || can === '1') {
                    $(this).prop('disabled', false).parent().removeClass('checkbox-disabled');
                } else {
                    $(this).prop('disabled', true).prop('checked', false).parent().addClass('checkbox-disabled');
                }
            });
        } else {
            $('input[name="bulk-delete[]"]').prop('disabled', false).parent().removeClass('checkbox-disabled');
        }
    }
    $(document).on('change', '#bulk-action-selector-top, #bulk-action-selector-bottom', updateCheckboxStates);
    updateCheckboxStates();

    // Guard for localization
    var ajaxUrl = (typeof wcusage_referrals_vars !== 'undefined' && wcusage_referrals_vars.ajax_url) ? wcusage_referrals_vars.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
    var nonce  = (typeof wcusage_referrals_vars !== 'undefined' && wcusage_referrals_vars.nonce) ? wcusage_referrals_vars.nonce : '';
    var texts  = (typeof wcusage_referrals_vars !== 'undefined' && wcusage_referrals_vars.texts) ? wcusage_referrals_vars.texts : {};

    // Confirm on submit when updating unpaid commission
    $(document).on('submit', '#referrals-table', function(e){
        var action = $('#bulk-action-selector-top').val();
        var action2 = $('#bulk-action-selector-bottom').val();
        var selectedAction = action !== '-1' ? action : action2;
        if (selectedAction === 'update_unpaid_commission') {
            var checkedBoxes = $('input[name="bulk-delete[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert(texts.please_select_at_least_one_order || 'Please select at least one order.');
                e.preventDefault();
                return false;
            }
            var msg = (texts.update_unpaid_confirm_header || 'Update unpaid commission for the selected orders?');
            if (texts.update_unpaid_confirm_line) {
                msg += '\n\n' + texts.update_unpaid_confirm_line;
            }
            if (texts.selected_orders) {
                msg += '\n\n' + texts.selected_orders + ' ' + checkedBoxes.length;
            }
            if (!window.confirm(msg)) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Lightweight username autocomplete for Affiliate User filter on this page
    (function initUserAutocomplete(){
        var $input = $('.wcu-autocomplete-user');
        if(!$input.length) return;
        if (!$.ui || !$.ui.autocomplete || !ajaxUrl) return;
        var labelPref = $input.data('label') || '';
        $input.autocomplete({
            source: function(request, response){
                $.post(ajaxUrl, {
                    action: 'wcusage_search_users',
                    nonce: nonce,
                    search: request.term,
                    label: labelPref
                }).done(function(res){
                    if(res && res.success && res.data){ response(res.data); } else { response([]); }
                }).fail(function(){ response([]); });
            },
            minLength: 2,
            select: function(e, ui){ $(this).val(ui.item.value); return false; }
        }).autocomplete('instance')._renderItem = function(ul, item){
            return $('<li>').append('<div>'+ item.label +'</div>').appendTo(ul);
        };
    })();

    // Handle Enter key on filter inputs to trigger filter instead of bulk action
    $(document).on('keypress', '.wcusage-admin-title-filters input[name="affiliate_user"], .wcusage-admin-title-filters input[name="coupon_code"], .wcusage-admin-title-filters input[name="date_from"], .wcusage-admin-title-filters input[name="date_to"]', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            // Find and click the filter button
            $(this).closest('.wcusage-admin-title-filters').find('button[type="submit"]').trigger('click');
            return false;
        }
    });

    // Handle Enter key on filter dropdowns to trigger filter instead of bulk action
    $(document).on('keypress', '.wcusage-admin-title-filters select[name="affiliate_group"], .wcusage-admin-title-filters select[name="order_status"]', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            // Find and click the filter button
            $(this).closest('.wcusage-admin-title-filters').find('button[type="submit"]').trigger('click');
            return false;
        }
    });
});
