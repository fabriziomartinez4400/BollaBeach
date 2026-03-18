jQuery(document).ready(function($) {
    $(document).on('submit', '#product_search_form', function(e) {
        e.preventDefault();
        var searchTerm = $(this).find('input[name="search"]').val();
        loadData(searchTerm, 1);
    });

    $(document).on('click', '.rates-pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('paged');
        var searchTerm = $('#product_search_form input[name="search"]').val();
        loadData(searchTerm, page);
    });

    function loadData(searchTerm, page) {
        $.post(wcusage_product_rates_ajax.ajax_url, {
            action: 'wcusage_rates_pagination',
            search: searchTerm,
            coupon: $('#product_search_form input[name="coupon"]').val(),
            paged: page,
        }, function(response) {
            $('.wcusage-product-rates').html(response);
        });
    }
});