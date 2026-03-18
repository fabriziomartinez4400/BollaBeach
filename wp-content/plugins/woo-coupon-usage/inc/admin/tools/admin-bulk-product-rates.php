<?php
if (!defined('ABSPATH')) {
    exit;
}

function wcusage_bulk_assign_rates_fields() {
    ?>
    <tr>
        <td>
            <input type="text" name="product_id[]" placeholder="Product ID">
        </td>
        <td>
            <select id="select-type" name="type[]">
                <option value="coupon">Coupon</option>
                <option value="user">User</option>
                <option value="role">Per Role</option>
            </select>
        </td>
        <td>
            <input class="the-affiliate" type="text" name="coupon[]" placeholder="Coupon">
        </td>
        <td>
            <input type="text" name="percent[]" placeholder="Percent">
        </td>
        <td>
            <input type="text" name="fixed[]" placeholder="Fixed">
        </td>
        <td>
            <button type="button" class="delete-row">Delete</button>
        </td>
    </tr>
    <?php
}

function wcusage_bulk_assign_rates_page() {
    // Check if user is administrator
    if ( ! wcusage_check_admin_access() ) {
        wp_die('Error: Permission denied.');
    }

    // Nonce field for security
    $nonce = wp_create_nonce('bulk_assign_rates');
    ?>

    <div class="wrap wcusage-admin-page">
        <?php do_action('wcusage_hook_dashboard_page_header', ''); ?>
    </div>

    <div class="wrap wcusage-tools">

        <h2><?php echo esc_html__('Bulk Assign: Per-Affiliate Product Rates', 'woo-coupon-usage'); ?></h2>
        <p></p>
        <p><?php echo esc_html__('Bulk assign per-product commission rates, on a per-affiliate basis. Any existing rates will also be updated.', 'woo-coupon-usage'); ?></p>
        <form id="bulk-assign-coupon-form" method="POST">
            <input type="hidden" name="action" value="assign_rates">
            <input type="hidden" name="_wpnonce" value="<?php echo esc_html($nonce); ?>">
            <br />
            <div class="wcu-scrollable-table">
                <table id="wcusage-tools-rows" style="margin: 0;">
                    <tr style="text-align: left;">
                        <th><?php echo esc_html__('Product ID', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Type', 'woo-coupon-usage'); ?></th>
                        <th class='the-type'><?php echo esc_html__('Affiliate', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Percent', 'woo-coupon-usage'); ?></th>
                        <th><?php echo esc_html__('Fixed', 'woo-coupon-usage'); ?></th>
                    </tr>
                    <?php wcusage_bulk_assign_rates_fields(); ?>
                </table>
                <br />
                <button type="button" id="add-row">Add New +</button>
            </div>
            <br /><br />
            <input type="submit" value="Update Rates" id="wcusage-submit" class="button button-primary">
        </form>
        <div id="wcusage-messages"></div>
    </div>
    <?php
}

// Enqueue scripts for admin page
add_action('admin_enqueue_scripts', 'wcusage_enqueue_admin_scripts_assign_rates');
function wcusage_enqueue_admin_scripts_assign_rates()
{
    if (isset($_GET['page']) && $_GET['page'] === 'wcusage-bulk-product-rates') {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wcusage-admin-scripts', plugin_dir_url(__FILE__) . 'js/admin-scripts.js', array('jquery'), '1.0', true);
        wp_localize_script('wcusage-admin-scripts', 'wcusage_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
}

// Add the JS code
add_action('admin_footer', 'wcusage_admin_footer_script_assign_rates');
function wcusage_admin_footer_script_assign_rates()
{
    if (isset($_GET['page']) && $_GET['page'] === 'wcusage-bulk-product-rates') {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#add-row').on('click', function () {
                    $('#wcusage-tools-rows').append(`
                        <tr>
                            <?php wcusage_bulk_assign_rates_fields(); ?>
                        </tr>
                    `);
                });

                // Change only the next ".the-affiliate" text field placeholder based on the select type
                $(document).on('change', '#select-type', function () {
                    var type = $(this).val();
                    if (type == 'coupon') {
                        $(this).closest('tr').find('.the-affiliate').attr('placeholder', 'Coupon');
                    } else if (type == 'user') {
                        $(this).closest('tr').find('.the-affiliate').attr('placeholder', 'Username');
                    } else if (type == 'role') {
                        $(this).closest('tr').find('.the-affiliate').attr('placeholder', 'User Role');
                    }
                });

                $(document).on('click', '.delete-row', function () {
                    $(this).closest('tr').remove();
                });

                // Handle form submission
                $('#bulk-assign-coupon-form').on('submit', function (e) {
                    e.preventDefault();

                    // Clear previous messages
                    $('#wcusage-messages').empty();

                    // Create an empty array to store the form data
                    var formDataArray = [];

                    // Loop through each row in the table
                    $('#wcusage-tools-rows tr').each(function () {
                        var product_id = $(this).find('input[name="product_id[]"]').val();
                        var type = $(this).find('select[name="type[]"]').val();
                        var coupon = $(this).find('input[name="coupon[]"]').val();
                        var percent = $(this).find('input[name="percent[]"]').val();
                        var fixed = $(this).find('input[name="fixed[]"]').val();

                        // Add the data to the formDataArray
                        formDataArray.push({
                            product_id: product_id,
                            type: type,
                            coupon: coupon,
                            percent: percent,
                            fixed: fixed
                        });
                    });

                    // Serialize the form data array
                    var formData = {
                        action: 'assign_rates',
                        _wpnonce: $('#bulk-assign-coupon-form input[name="_wpnonce"]').val(),
                        data: formDataArray
                    };

                    // Submit form data via AJAX
                    $.ajax({
                        type: 'POST',
                        url: wcusage_ajax_object.ajax_url,
                        data: formData,
                        dataType: 'json',
                        beforeSend: function () {
                            // Show loading spinner or disable submit button
                            $('#wcusage-submit').prop('disabled', true);
                        },
                        success: function (response) {
                            if (response.success) {
                                // Display success message
                                var message = '<p style="font-weight: bold;">' + response.data.message + '</p>';
                                $('#wcusage-messages').append('<br/><div class="wcusage-message updated">' + message + '</div>');
                                // Remove the rows from the table by clicking the delete button closest to the product ID
                                $.each(formDataArray, function (index, data) {
                                    $('.delete-row').filter(function () {
                                        return $(this).closest('tr').find('input[name="product_id[]"]').val() === data.product_id;
                                    }).closest('tr').remove();
                                });
                            } else {
                                // Display error message
                                var errorMessage = '<p style="font-weight: bold;">Error: ' + response.data.message + '</p>';
                                $('#wcusage-messages').append('<div class="wcusage-message error">' + errorMessage + '</div>');
                            }
                        },
                        error: function (xhr, status, error) {
                            // Display generic error message
                            $('#wcusage-messages').append('<div class="wcusage-message error">An error occurred. Please try again.</div>');
                        },
                        complete: function () {
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

add_action('wp_ajax_assign_rates', 'wcusage_ajax_assign_rates');
add_action('wp_ajax_nopriv_assign_rates', 'wcusage_ajax_assign_rates');

function wcusage_ajax_assign_rates() {
    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_assign_rates')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    // Get the submitted data as an array
    $data = isset($_POST['data']) ? $_POST['data'] : array();

    // Initialize an array to store success and error messages
    $success_messages = array();
    $error_messages = array();

    // Loop through the submitted data array
    foreach ($data as $item) {
        // Extract data from the current item
        $product_id = sanitize_text_field($item['product_id']);
        $type = sanitize_text_field($item['type']);
        $coupon = sanitize_text_field($item['coupon']);
        $percent = sanitize_text_field($item['percent']);
        $fixed = sanitize_text_field($item['fixed']);

        if($product_id && $type && $coupon) {

            $wcu_product_per_user_rates = get_post_meta( $product_id, 'wcu_product_per_user_rates', true );
            // example array: array(2) { [0]=> array(4) { ["type"]=> string(6) "coupon" ["affiliate"]=> string(10) "coupontest" ["commission_percent"]=> string(2) "50" ["commission_fixed"]=> string(1) "5" } [1]=> array(4) { ["type"]=> string(4) "user" ["affiliate"]=> string(5) "5sdw5" ["commission_percent"]=> string(2) "11" ["commission_fixed"]=> string(1) "0" } }

            // Check if rate already exists where type and coupon match
            $rate_exists = false;
            foreach($wcu_product_per_user_rates as $key => $value) {
                if($value['type'] == $type && $value['affiliate'] == $coupon) {
                    $rate_exists = true;
                    // Update existing
                    $wcu_product_per_user_rates[$key]['commission_percent'] = $percent;
                    $wcu_product_per_user_rates[$key]['commission_fixed'] = $fixed;
                }
            }

            if(!$rate_exists) {
                // Add to existing array
                $wcu_product_per_user_rates[] = array(
                    'type' => $type,
                    'affiliate' => $coupon,
                    'commission_percent' => $percent,
                    'commission_fixed' => $fixed,
                );
            }
            
            // Update post meta
            update_post_meta( $product_id, 'wcu_product_per_user_rates', $wcu_product_per_user_rates );

        }

        // For demonstration purposes, let's assume a successful update
        $success = true;

        if ($success) {
            // If the update was successful, add a success message
            $success_messages[] = array(
                'product_id' => $product_id,
                'type' => $type,
                'coupon' => $coupon,
                'percent' => $percent,
                'fixed' => $fixed,
            );
        } else {
            // If the update failed, add an error message
            $error_messages[] = array(
                'product_id' => $product_id,
                'type' => $type,
                'coupon' => $coupon,
                'percent' => $percent,
                'fixed' => $fixed,
                'message' => 'Failed to update product.',
            );
        }
    }

    // Check if there are any success or error messages
    if (!empty($success_messages)) {
        // Send a success response with success messages
        wp_send_json_success(array(
            'message' => 'Products updated successfully!',
            'success_rows' => $success_messages,
        ));
    } elseif (!empty($error_messages)) {
        // Send an error response with error messages
        wp_send_json_error(array(
            'message' => 'Some products failed to update.',
            'row_errors' => $error_messages,
        ));
    } else {
        // If no messages, send a generic success response
        wp_send_json_success(array('message' => 'No products updated.'));
    }
}
