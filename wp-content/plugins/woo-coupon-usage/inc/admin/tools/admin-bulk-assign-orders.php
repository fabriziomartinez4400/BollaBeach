<?php
if (!defined('ABSPATH')) {
    exit;
}

function wcusage_bulk_assign_coupons_fields()
{
?>
    <tr>
        <td>
            <input type="text" name="order_id[]" placeholder="Order ID">
        </td>
        <td>
            <input type="text" name="coupon_code[]" placeholder="Coupon Code">
        </td>
        <td>
            <button type="button" class="delete-row">Delete</button>
        </td>
    </tr>
<?php
}

function wcusage_bulk_assign_coupons_page() {

    // Check if user is administrator
    if ( ! wcusage_check_admin_access() ) {
        wp_die('Error: Permission denied.');
    }

    // Nonce field for security
    $nonce = wp_create_nonce('bulk_assign_coupons');
    ?>

    <div class="wrap wcusage-admin-page">
        <?php do_action( 'wcusage_hook_dashboard_page_header', ''); ?>
    </div>

    <div class="wrap wcusage-tools">
        <h2><?php echo esc_html__('Bulk Assign: Coupons to Orders', 'woo-coupon-usage'); ?></h2>
        <p></p>
        <p><?php echo esc_html__('Bulk assign affiliate coupons to orders. Enter the order ID and the corresponding coupon code to assign the coupon to the order.', 'woo-coupon-usage'); ?></p>
        <p><?php echo esc_html__('This will not apply the coupon discount to the order, it simply links the coupon/affiliate to that order for them to earn commission for it.', 'woo-coupon-usage'); ?></p>
        <form id="bulk-assign-coupon-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="assign_coupons">
            <input type="hidden" name="_wpnonce" value="<?php echo esc_html($nonce); ?>">
            <br />
            <strong>Import (CSV):</strong> <input type="file" id="csv-upload" accept=".csv">
            <br /><br />
            <div class="wcu-scrollable-table">
                <table id="wcusage-tools-rows" style="margin: 0;">
                    <tr style="text-align: left;">
                        <th><?php echo esc_html__('Order ID', 'woo-coupon-usage'); ?></th>
                        <th>Coupon Code</th>
                    </tr>
                    <?php wcusage_bulk_assign_coupons_fields(); ?>
                </table>
                <br/>
                <button type="button" id="add-row">Add New +</button>
            </div>
            <br /><br />
            <input type="submit" value="Assign Coupons" id="wcusage-submit" class="button button-primary">
            <br/><br/>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_tools')); ?>">Go back to tools ></a></p>
        </form>
        <div id="wcusage-messages"></div>
    </div>
<?php
}

// Enqueue scripts for admin page
add_action('admin_enqueue_scripts', 'wcusage_enqueue_admin_scripts_assign_coupons');
function wcusage_enqueue_admin_scripts_assign_coupons()
{
    if (isset($_GET['page']) && $_GET['page'] === 'wcusage-bulk-assign-coupons') {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wcusage-admin-scripts', plugin_dir_url(__FILE__) . 'js/admin-scripts.js', array('jquery'), '1.0', true);
        wp_localize_script('wcusage-admin-scripts', 'wcusage_ajax_object', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
        ));
    }
}

// Add the JS code
add_action('admin_footer', 'wcusage_admin_footer_script_assign_coupons');
function wcusage_admin_footer_script_assign_coupons()
{
    if (isset($_GET['page']) && $_GET['page'] === 'wcusage-bulk-assign-coupons') {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#add-row').on('click', function() {
                    $('#wcusage-tools-rows').append(`
                        <tr>
                            <?php wcusage_bulk_assign_coupons_fields(); ?>
                        </tr>
                    `);
                });

                $(document).on('click', '.delete-row', function() {
                    $(this).closest('tr').remove();
                });

                // Listen for a change on the file input
                $('#csv-upload').change(function(e) {
                    var file = e.target.files[0];
                    if (!file) return;

                    // Read the file contents
                    var reader = new FileReader();
                    reader.readAsText(file);
                    reader.onload = function(e) {
                        var csv = e.target.result;

                        // Parse the CSV data
                        var lines = csv.split('\n');
                        for (var i = 1; i < lines.length; i++) {

                            // Skip if line is empty
                            if (!lines[i]) continue;

                            var data = lines[i].split(',');
                            if (!data[0] && !data[1]) continue;

                            // Sanitize the data
                            for(var j = 0; j < data.length; j++){
                                data[j] = data[j].replace(/[^a-zA-Z0-9 _-]/g, '');
                            }

                            // Add a new row for each line of data
                            $('#wcusage-tools-rows').append(`
                                <tr class="row" style="margin-top: 20px;">
                                    <td>
                                        <input type="text" name="order_id[]" placeholder="Order ID" value="${data[0]}">
                                    </td>
                                    <td>
                                        <input type="text" name="coupon_code[]" placeholder="Coupon Code" value="${data[1]}">
                                    </td>
                                    <td>
                                        <button type="button" class="delete-row">Delete</button>
                                    </td>
                                </tr>
                            `);

                            // Remove any empty rows
                            $('#wcusage-tools-rows tr').each(function() {
                                var row = $(this);
                                if (row.find('input[name="order_id[]"]').val() === '' && row.find('input[name="coupon_code[]"]').val() === '') {
                                    row.remove();
                                }
                            });

                            // Reset the file input
                            $('#csv-upload').val('');
                        }
                    };
                });

                // Handle form submission
                $('#bulk-assign-coupon-form').on('submit', function(e) {
                    e.preventDefault();

                    // Clear previous messages
                    $('#wcusage-messages').empty();

                    // Serialize form data
                    var formData = $(this).serialize();

                    // Submit form data via AJAX
                    $.ajax({
                        type: 'POST',
                        url: wcusage_ajax_object.ajax_url,
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            // Show loading spinner or disable submit button
                            $('#wcusage-submit').prop('disabled', true);
                        },
                        success: function(response) {
                            if (response.success) {
                                // Display success messages for each row without errors
                                response.success_rows.forEach(function(row) {
                                    var message = '<p style="font-weight: bold;">Coupon assigned to order successfully!</p>';
                                    message += '<p>Order ID: ' + row.data.order_id + '<br/>';
                                    message += 'Coupon Code: ' + row.data.coupon_code + '</p>';
                                    $('#wcusage-messages').append('<div class="wcusage-message updated">' + message + '</div>');
                                    // Remove the row from the table by clicking the delete button closest to the coupon code
                                    $('.delete-row').filter(function() {
                                        return $(this).closest('tr').find('input[name="coupon_code[]"]').val() === row.data.coupon_code;
                                    }).closest('tr').remove();
                                });
                            }
                            // Display error messages
                            if (response.row_errors && response.row_errors.length > 0) {
                                response.row_errors.forEach(function(error) {
                                    var message = '<p style="font-weight: bold;">Error assigning coupon to order: <span style="color: red;">' + error.message + '</span></p>';
                                    message += '<p>Order ID: ' + error.data.order_id + '<br/>';
                                    message += 'Coupon Code: ' + error.data.coupon_code + '</p>';
                                    $('#wcusage-messages').append('<div class="wcusage-message error">' + message + '</div>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Display generic error message
                            $('#wcusage-messages').append('<div class="wcusage-message error">An error occurred. Please try again.</div>');
                        },
                        complete: function() {
                            // Hide loading spinner or enable submit button
                            $('#wcusage-submit').prop('disabled', false);
                            // Click .add-row
                            $('#add-row').trigger('click');
                        }
                    });
                });
            });
        </script>
<?php
    }
}

// Handle form submission
add_action('wp_ajax_assign_coupons', 'wcusage_bulk_assign_coupons');
add_action('wp_ajax_nopriv_assign_coupons', 'wcusage_bulk_assign_coupons');
function wcusage_bulk_assign_coupons()
{
    $response = array();

    if ($_POST['coupon_code']) {

        // Check nonce
        if (!wp_verify_nonce(sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_assign_coupons')) {
            $response['errors'][] = 'Security check failed';
            wp_send_json($response);
            exit;
        }

        $row_errors = array(); // Store errors for each row/item
        $success_rows = array(); // Store coupon codes for rows without errors

        foreach ($_POST['coupon_code'] as $i => $coupon_code) {

            $coupon_code = sanitize_text_field($coupon_code);

            $error = "";

            $order_id = '';

            if(isset($_POST['order_id'][$i])) $order_id = sanitize_text_field($_POST['order_id'][$i]);

            // If all empty, skip
            if (empty($order_id) && empty($coupon_code)) {
                continue;
            }

            $row_data = array(
                'order_id' => $order_id,
                'coupon_code' => $coupon_code,
            );

            // Order ID is required
            if (empty($order_id)) {
                $row_errors[] = array(
                    'data' => $row_data,
                    'message' => 'Order ID is required.'
                );
                continue;
            }
            // Orders status is set to completed
            $order_status = get_post_status($order_id);
            if ($order_status == 'wc-completed') {
                $row_errors[] = array(
                    'data' => $row_data,
                    'message' => 'Order status is completed. Affiliate coupon cannot be assigned to completed orders.'
                );
                continue;
            }
            // Coupon Code is required
            if (empty($coupon_code)) {
                $row_errors[] = array(
                    'data' => $row_data,
                    'message' => 'Coupon Code is required.'
                );
                continue;
            }
            // Check if the order exists
            $order = wc_get_order($order_id);
            if (!$order) {
                $row_errors[] = array(
                    'data' => $row_data,
                    'message' => 'Order does not exist.'
                );
                continue;
            }

            // Update all time stats
            $wcusage_referrer_coupon_old = get_post_meta( $order_id, 'wcusage_referrer_coupon', true );
            $wcusage_referrer_coupon = $coupon_code;
            if ( version_compare( WC_VERSION, 3.7, ">=" ) ) {
                $coupons_array = $order->get_coupon_codes();
            } else {
                $coupons_array = $order->get_used_coupons();
            }
            if($wcusage_referrer_coupon_old != $wcusage_referrer_coupon) {
                // Remove
                if($wcusage_referrer_coupon_old) {
                    // Remove from previous coupon
                    do_action( 'wcusage_hook_update_all_stats_single', $wcusage_referrer_coupon_old, $order_id, 0, 1 ); // Remove
                } else {
                    // Remove to all other coupons
                    foreach( $coupons_array as $this_coupon_code ) {
                        do_action( 'wcusage_hook_update_all_stats_single', $this_coupon_code, $order_id, 0, 1 ); // Remove
                    }
                }
                wcusage_update_order_meta( $order_id, 'wcusage_referrer_coupon', $wcusage_referrer_coupon );
                wcusage_delete_order_meta( $order_id, 'wcusage_total_commission' );
                $order_data = wcusage_calculate_order_data( $order_id, $coupon_code, 1, 0, 1 );
                // Add
                if($wcusage_referrer_coupon) {
                    // Add to new coupon
                    do_action( 'wcusage_hook_update_all_stats_single', $wcusage_referrer_coupon, $order_id, 1, 1 ); // Add
                } else {
                    // Add to all other coupons
                    foreach( $coupons_array as $this_coupon_code ) {
                        do_action( 'wcusage_hook_update_all_stats_single', $this_coupon_code, $order_id, 1, 1 ); // Add
                    }
                }
            }

            // Assign the coupon code as order meta
            wcusage_update_order_meta($order_id, 'wcusage_referrer_coupon', $coupon_code);

            // Store coupon code for rows without errors
            $success_rows[] = array(
                'data' => $row_data,
            );
        }

        if (!empty($success_rows)) {
            $response['success'] = true;
            $response['success_rows'] = $success_rows;
        }

        if (!empty($row_errors)) {
            $response['row_errors'] = $row_errors;
        }
        
    }

    wp_send_json($response);
    exit;
}
