<?php
if (!defined('ABSPATH')) {
    exit;
}

function wcusage_bulk_product_fields() {
    global $wpdb;

    $products = $wpdb->get_results("
        SELECT ID, post_title
        FROM {$wpdb->prefix}posts
        WHERE post_type = 'product'
        AND post_status = 'publish'
        ORDER BY ID DESC
    ");

    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_name = $product->post_title;

        $commission_percent = get_post_meta($product_id, 'wcu_product_commission_percent', true);
        $commission_fixed = get_post_meta($product_id, 'wcu_product_commission_fixed', true);
        ?>
        <tr data-product-id="<?php echo esc_attr($product_id); ?>">
            <td><?php echo esc_html($product_id); ?></td>
            <td><?php echo esc_html($product_name); ?></td>
            <td><input type="number" name="commission_percent[<?php echo esc_attr($product_id); ?>]" value="<?php echo esc_attr($commission_percent); ?>"></td>
            <td><input type="number" name="commission_fixed[<?php echo esc_attr($product_id); ?>]" value="<?php echo esc_attr($commission_fixed); ?>"></td>
        </tr>
        <?php
    }
    $total_products = count($products);
    echo '<input type="hidden" id="total-products" value="' . esc_html($total_products) . '">';
}

function wcusage_bulk_product_page() {

    // Check if user is administrator
    if ( ! wcusage_check_admin_access() ) {
        wp_die('Error: Permission denied.');
    }
    
    // Nonce field for security
    $nonce = wp_create_nonce('bulk_product_update');
    ?>

    <link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

    <div class="wrap wcusage-admin-page">
        <?php do_action('wcusage_hook_dashboard_page_header', ''); ?>
    </div>

    <div class="wrap wcusage-bulk-edit-products wcusage-tools">

        <h2><?php echo esc_html__('Bulk Edit: Product Settings', 'woo-coupon-usage'); ?></h2>
        <p><?php echo esc_html__('Use this tool to bulk edit your per-product commission settings. The username must exist or it will not be updated.', 'woo-coupon-usage'); ?></p>
        <p><?php echo esc_html__('Currently "Per-Affiliate Product Commission Rates" can only be edited by viewing/editing the individual product.', 'woo-coupon-usage'); ?></p>
        <br/>
        <button id="import-csv" class="button">Import CSV</button>
        <button id="export-csv" class="button">Export CSV</button>
        <br/><br/>
        <form id="bulk-product-form" method="POST">
            <input type="hidden" name="action" value="update_products">
            <input type="hidden" name="_wpnonce" value="<?php echo esc_html($nonce); ?>">
            <div class="wcu-scrollable-table">
                <table id="wcusage-tools-rows">
                    <tr>
                        <th><?php echo esc_html__('Product ID', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Product Name', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Commission Percent', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Commission Fixed', 'woo-coupon-usage'); ?></th>
                    </tr>
                    <?php wcusage_bulk_product_fields(); ?>
                </table>
            </div>
            <br/>
            <p><span id="spinner" style="display: none; font-size: 20px; color: green;"><i class="fas fa-spinner fa-spin"></i> Updating... <span id="progress">0/0</span></span></p>
            <p><input type="button" value="Update Products" id="update-products-button" class="button button-primary" style="margin-bottom: 20px;"></p>
            <br/><br/>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_tools')); ?>">Go back to tools ></a></p>
        </form>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Import CSV button click event
            $('#import-csv').on('click', function() {
                var csvFileInput = $('<input type="file" accept=".csv" style="display:none">');
                csvFileInput.change(function(e) {
                    var file = e.target.files[0];
                    if (!file) return;
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var csv = e.target.result;
                        var lines = csv.split('\n');
                        var headers = lines[0].split(',');

                        for (var i = 1; i < lines.length; i++) {
                            var data = lines[i].split(',');
                            if (data.length >= 4) {
                                var productId = data[0].trim();
                                var commissionPercent = data[2].trim();
                                var commissionFixed = data[3].trim();
                                // sanitize
                                productId = productId.replace(/[^0-9]/g, '');
                                commissionPercent = commissionPercent.replace(/[^0-9.]/g, '');
                                commissionFixed = commissionFixed.replace(/[^0-9.]/g, '');
                                // update input fields
                                $('input[name="commission_percent[' + productId + ']"]').val(commissionPercent);
                                $('input[name="commission_fixed[' + productId + ']"]').val(commissionFixed);
                            }
                        }
                    };
                    reader.readAsText(file);
                });
                csvFileInput.click();
            });

            // Export CSV button click event
            $('#export-csv').on('click', function() {
                var csvContent = "data:text/csv;charset=utf-8,";

                // Add table headers to CSV content
                var tableHeaders = [];
                $('.wrap table th').each(function() {
                    tableHeaders.push($(this).text());
                });
                csvContent += tableHeaders.join(',') + "\n";

                // Add table data to CSV content
                $('.wrap table tr').each(function() {
                    var rowData = [];
                    $(this).find('td').each(function() {
                        rowData.push($(this).text());
                    });

                    // Include input field values in CSV content
                    var productId = $(this).find('td:first-child').text().trim();
                    var commissionPercent = $('input[name="commission_percent[' + productId + ']"]').val();
                    var commissionFixed = $('input[name="commission_fixed[' + productId + ']"]').val();

                    // Insert input field values at the correct positions
                    rowData.splice(2, 0, commissionPercent); // Insert Commission Percent value at index 2
                    rowData.splice(3, 0, commissionFixed); // Insert Commission Fixed value at index 3

                    csvContent += rowData.join(',') + "\n";
                });

                // Create a temporary link and trigger the download
                var encodedUri = encodeURI(csvContent);
                var link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "table_data.csv");
                document.body.appendChild(link); // Required for Firefox
                link.click();
                document.body.removeChild(link);
            });

            // Handle form submission
            $('#update-products-button').on('click', function(e) {
                e.preventDefault();
                $('.wcusage-message').remove();
                $(this).prop('disabled', true); // Disable the button
                $('#spinner').show(); // Show the spinner icon
                $('#progress').text('0/' + $('#total-products').val()); // Initialize progress
                updateProduct(0);
            });

            function updateProduct(index) {
                var productRow = $('#wcusage-tools-rows tr').eq(index + 1);
                var productId = productRow.data('product-id');
                var commissionPercent = $('input[name="commission_percent[' + productId + ']"]').val();
                var commissionFixed = $('input[name="commission_fixed[' + productId + ']"]').val();
                var nextIndex = index + 1;
                $('#progress').text(nextIndex + '/' + $('#total-products').val()); // Update progress

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'update_product',
                        product_id: productId,
                        commission_percent: commissionPercent,
                        commission_fixed: commissionFixed,
                        _wpnonce: '<?php echo esc_html($nonce); ?>'
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('.button-primary').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.success != 'pass') {
                            productRow.addClass('updated');
                            $('<div class="wcusage-message updated"><p>Product ' + productId + ' updated successfully!</p></div>').insertAfter('#update-products-button');
                            }
                        } else {
                            productRow.addClass('error');
                            var errorMessage = '<p style="font-weight: bold;">Error updating product: <span style="color: red;">' + response.error_message + '</span></p>';
                            errorMessage += '<p>Product ID: ' + productId + '</p>';
                            productRow.after('<tr class="wcusage-message error"><td colspan="4">' + errorMessage + '</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        productRow.addClass('error');
                        productRow.after('<tr class="wcusage-message error"><td colspan="4"><p>An error occurred. Please try again.</p></td></tr>');
                    },
                    complete: function() {
                        var nextIndex = index + 1;
                        if (nextIndex < $('#wcusage-tools-rows tr').length - 1) {
                            updateProduct(nextIndex);
                        } else {
                            $('<div class="wcusage-message updated"><p>All products updated successfully!</p></div>').insertAfter('#update-products-button');
                            $('.button-primary').prop('disabled', false);
                            $('#spinner').hide(); // Hide the spinner icon
                        }
                    }
                });
            }
        });
    </script>
    <?php
}

// Enqueue scripts for admin page
add_action('admin_enqueue_scripts', 'wcusage_enqueue_admin_scripts_product');
function wcusage_enqueue_admin_scripts_product() {
    if (isset($_GET['page']) && $_GET['page'] === 'wcusage-bulk-coupon-update') {
        wp_enqueue_script('jquery');
    }
}

// Handle form submission
add_action('wp_ajax_update_product', 'wcusage_update_product');
function wcusage_update_product() {

    $response = array();

        if ( ! wcusage_check_admin_access() || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_product_update')) {
        $response['success'] = false;
        $response['error_message'] = 'Security check failed.';
        wp_send_json($response);
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $commission_percent = sanitize_text_field($_POST['commission_percent']);
    $commission_fixed = sanitize_text_field($_POST['commission_fixed']);

    $current_commission_percent = get_post_meta($product_id, 'wcu_product_commission_percent', true);
    $current_commission_fixed = get_post_meta($product_id, 'wcu_product_commission_fixed', true);

    // check if $commission_percent and $commission_fixed are already set to current values
    if ($current_commission_percent === $commission_percent && $current_commission_fixed === $commission_fixed) {
        $response['success'] = 'pass';
        wp_send_json($response);
        exit;
    }

    // check if $commission_percent and $commission_fixed are valid numbers or empty
    if ((!empty($commission_percent) && !is_numeric($commission_percent)) || (!empty($commission_fixed) && !is_numeric($commission_fixed))) {
        $response['success'] = false;
        $response['error_message'] = 'Commission Percent must be a number.';
        wp_send_json($response);
        exit;
    }

    update_post_meta($product_id, 'wcu_product_commission_percent', $commission_percent);
    update_post_meta($product_id, 'wcu_product_commission_fixed', $commission_fixed);

    $response['success'] = true;
    wp_send_json($response);
    exit;
}
