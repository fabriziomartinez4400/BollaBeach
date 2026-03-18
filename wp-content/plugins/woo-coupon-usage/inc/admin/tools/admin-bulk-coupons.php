<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
function wcusage_bulk_coupon_creator_fields() {
    ?>
    <tr>
        <td>
            <input type="text" name="username[]" placeholder="Username">
        </td>
        <td>
            <input type="email" name="email[]" placeholder="Email Address">
        </td>
        <td>
            <input type="text" name="first_name[]" placeholder="First Name">
        </td>
        <td>
            <input type="text" name="coupon_code[]" placeholder="Coupon Code">
        </td>
        <?php 
    ?>
        <td>
            <button type="button" class="delete-row">Delete</button>
        </td>
    </tr>
<?php 
}

add_action( 'admin_init', 'wcusage_check_for_csv_download' );
function wcusage_check_for_csv_download() {
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wcusage-bulk-coupon-creator' && isset( $_GET['download'] ) ) {
        wcusage_bulk_coupon_creator_page_downloadCSV();
    }
}

// Create Page
function wcusage_bulk_coupon_creator_page() {
    // Check if user is administrator
    if ( !wcusage_check_admin_access() ) {
        wp_die( 'Error: Permission denied.' );
    }
    // Nonce field for security
    $nonce = wp_create_nonce( 'create_bulk_coupons' );
    // Check if multiple templates is enabled
    $wcusage_coupon_multiple = wcusage_get_setting_value( 'wcusage_field_registration_multiple_template', '0' );
    ?>

    <div class="wrap wcusage-admin-page">
        <?php 
    do_action( 'wcusage_hook_dashboard_page_header', '' );
    ?>
    </div>

    <link rel="stylesheet" href="<?php 
    echo esc_url( WCUSAGE_UNIQUE_PLUGIN_URL ) . 'fonts/font-awesome/css/all.min.css';
    ?>" crossorigin="anonymous">

    <div class="wrap wcusage-tools wcusage-page">

        <h2><?php 
    echo esc_html__( 'Bulk Create: Affiliate Coupons', 'woo-coupon-usage' );
    ?></h2>
        <p></p>
        <p><?php 
    echo esc_html__( 'Bulk create or import a list of affiliate coupons (and users) to be automatically created. This will automatically create the coupon, and assign the user to it. If the user does not exist, that will also be created.', 'woo-coupon-usage' );
    ?> <a href="https://couponaffiliates.com/docs/bulk-coupon-importer"><?php 
    echo esc_html__( 'Learn More', 'woo-coupon-usage' );
    ?> </a></p>
        <form id="bulk-coupon-creator-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_coupons">
            <input type="hidden" name="_wpnonce" value="<?php 
    echo esc_html( $nonce );
    ?>">
            <br />
            <strong>Import (CSV):</strong> <input type="file" id="csv-upload" accept=".csv">
            <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage-bulk-coupon-creator&download=1' ) );
    ?>">Download CSV Template</a>
            <br /><br/>
            <div class="wcu-scrollable-table">
                <table id="wcusage-tools-rows" style="margin: 0;">
                    <tr style="text-align: left;">
                        <th><?php 
    echo esc_html__( 'Username', 'woo-coupon-usage' );
    ?></th>
                        <th>Email Address</th>
                        <th>First Name</th>
                        <th>Coupon Code</th>
                        <?php 
    if ( $wcusage_coupon_multiple && wcu_fs()->can_use_premium_code__premium_only() ) {
        ?>
                        <th>Coupon Template</th>
                        <?php 
    }
    ?>
                    </tr>
                    <?php 
    wcusage_bulk_coupon_creator_fields();
    ?>
                </table>
                <br/>
                <button type="button" id="add-row">Add New +</button>
            </div>
            <br /><br />
            <input type="submit" value="Create Coupons" id="wcusage-submit" class="button button-primary">
            <br/><br/>
            <p><a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wcusage_tools' ) );
    ?>">Go back to tools ></a></p>
        </form>
        <div id="wcusage-messages"></div>
    </div>
<?php 
}

// Download CSV Template6
function wcusage_bulk_coupon_creator_page_downloadCSV() {
    // Clear any existing output buffer
    while ( ob_get_level() ) {
        ob_end_clean();
    }
    // Start a new output buffer
    ob_start();
    $data = "Username,Email Address,First Name,Coupon Code\n";
    // Header
    $data .= "example,example@example.com,John,example10\n";
    // Sample row
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=bulk-coupon-import.csv' );
    echo esc_html( trim( $data ) );
    // Trim will remove any leading or trailing whitespace or newlines
    // End the output buffer and send the content
    ob_end_flush();
    exit;
}

// Enqueue scripts for admin page
add_action( 'admin_enqueue_scripts', 'wcusage_enqueue_admin_scripts_coupon_creator' );
function wcusage_enqueue_admin_scripts_coupon_creator() {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage-bulk-coupon-creator' ) {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script(
            'wcusage-admin-scripts',
            plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js',
            array('jquery'),
            '1.0',
            true
        );
        wp_localize_script( 'wcusage-admin-scripts', 'wcusage_ajax_object', array(
            'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
        ) );
    }
}

// Add the JS code
add_action( 'admin_footer', 'wcusage_admin_footer_script_coupon_creator' );
function wcusage_admin_footer_script_coupon_creator() {
    $wcusage_coupon_multiple = wcusage_get_setting_value( 'wcusage_field_registration_multiple_template', '0' );
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'wcusage-bulk-coupon-creator' ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#add-row').on('click', function() {
                    $('#wcusage-tools-rows').append(`
                        <tr>
                            <?php 
        wcusage_bulk_coupon_creator_fields();
        ?>
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
                            if ( !lines[i] ) continue;

                            var data = lines[i].split(',');
                            if( !data[0] && !data[1] && !data[2] && !data[3] && !data[4] ) continue;

                            // Sanitize the data
                            for(var j = 0; j < data.length; j++){
                                if(j != 1){
                                    data[j] = data[j].replace(/[^a-zA-Z0-9 _-]/g, '');
                                }else{
                                    data[j] = data[j].replace(/[^a-zA-Z0-9@._-]/g, '');
                                }
                            }
                            
                            // Add a new row for each line of data
                            $('#wcusage-tools-rows').append(`
                                <tr class="row" style="margin-top: 20px;">
                                    <td>
                                        <input type="text" name="username[]" placeholder="Username" value="${data[0]}">
                                    </td>
                                    <td>
                                        <input type="email" name="email[]" placeholder="Email Address" value="${data[1]}">
                                    </td>
                                    <td>
                                        <input type="text" name="first_name[]" placeholder="First Name" value="${data[2]}">
                                    </td>
                                    <td>
                                        <input type="text" name="coupon_code[]" placeholder="Coupon Code" value="${data[3]}">
                                    </td>
                                    <?php 
        if ( $wcusage_coupon_multiple && wcu_fs()->can_use_premium_code__premium_only() ) {
            ?>
                                    <td>
                                        <input type="text" name="coupon_type[]" value="${data[4]}">
                                    </td>
                                    <?php 
        }
        ?>
                                    <td>
                                        <button type="button" class="delete-row">Delete</button>
                                    </td>
                                </tr>
                            `);

                            // Remove any empty rows
                            $('#wcusage-tools-rows tr').each(function() {
                                var row = $(this);
                                if (row.find('input[name="username[]"]').val() === '' && row.find('input[name="email[]"]').val() === '' && row.find('input[name="first_name[]"]').val() === '' && row.find('input[name="coupon_code[]"]').val() === '') {
                                    row.remove();
                                }
                            });

                            // Reset the file input
                            $('#csv-upload').val('');

                        }
                    };
                });

                // Handle form submission
                $('#bulk-coupon-creator-form').on('submit', function(e) {
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
                                    var message = '<p style="font-weight: bold;">Coupon created successfully!</p>';
                                    if (row.new) message += '<p>(New user was created.)</p>';
                                    message += '<p>Username: ' + row.data.username + '<br/>';
                                    message += 'Email: ' + row.data.email + '<br/>';
                                    message += 'First Name: ' + row.data.first_name + '<br/>';
                                    message += 'Coupon Code: ' + row.data.coupon_code + '</p>';
                                    $('#wcusage-messages').append('<div class="wcusage-message updated">' + message + '</div>');
                                    // Remove the row from the table by clicking the delete button closest to the coupon code
                                    $('.delete-row').filter(function() {
                                        return $(this).closest('tr').find('input[name="coupon_code[]"]').val() === row.data.coupon_code;
                                    }).closest('tr').remove();
                                    $('#add-row').trigger('click');
                                });
                            }
                            // Display error messages
                            if (response.row_errors && response.row_errors.length > 0) {
                                response.row_errors.forEach(function(error) {
                                    var message = '<p style="font-weight: bold;">Error creating coupon: <span style="color: red;">' + error.message + '</span></p>';
                                    message += '<p>Username: ' + error.data.username + '<br/>';
                                    message += 'Email: ' + error.data.email + '<br/>';
                                    message += 'First Name: ' + error.data.first_name + '<br/>';
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
                        }
                    });
                });
            });
        </script>
<?php 
    }
}

// Handle form submission
add_action( 'wp_ajax_create_coupons', 'wcusage_bulk_create_coupons' );
add_action( 'wp_ajax_nopriv_create_coupons', 'wcusage_bulk_create_coupons' );
function wcusage_bulk_create_coupons() {
    $response = array();
    if ( $_POST['coupon_code'] ) {
        // Check nonce
        if ( !wcusage_check_admin_access() || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'create_bulk_coupons' ) ) {
            $response['errors'][] = 'Security check failed';
            wp_send_json( $response );
            exit;
        }
        $row_errors = array();
        // Store errors for each row/item
        $success_rows = array();
        // Store coupon codes for rows without errors
        foreach ( $_POST['coupon_code'] as $i => $coupon_code ) {
            $coupon_code = sanitize_text_field( $coupon_code );
            $error = "";
            if ( isset( $_POST['coupon_type'][$i] ) ) {
                $template = sanitize_text_field( $_POST['coupon_type'][$i] );
            } else {
                $template = wcusage_get_setting_value( 'wcusage_field_registration_coupon_template', '' );
            }
            // Get the template coupon id
            $coupon_info = wcusage_get_coupon_info( $template );
            $template_coupon_id = $coupon_info[2];
            // Get the template coupon meta
            $template_meta = get_post_meta( $template_coupon_id );
            $username = '';
            $email = '';
            $first_name = '';
            if ( isset( $_POST['username'][$i] ) ) {
                $username = sanitize_text_field( $_POST['username'][$i] );
            }
            if ( isset( $_POST['email'][$i] ) ) {
                $email = sanitize_email( $_POST['email'][$i] );
            }
            if ( isset( $_POST['first_name'][$i] ) ) {
                $first_name = sanitize_text_field( $_POST['first_name'][$i] );
            }
            // If all empty, skip
            if ( empty( $username ) && empty( $email ) && empty( $first_name ) && empty( $coupon_code ) ) {
                continue;
            }
            $row_data = array(
                'username'    => $username,
                'email'       => $email,
                'first_name'  => $first_name,
                'coupon_code' => $coupon_code,
            );
            // Username is required
            if ( empty( $username ) ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Username is required.',
                );
                continue;
            }
            // Coupon Code is required
            if ( empty( $coupon_code ) ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Coupon Code is required.',
                );
                continue;
            }
            // Coupon code exists
            if ( wc_get_coupon_id_by_code( $coupon_code ) ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Coupon Code already exists.',
                );
                continue;
            }
            // Coupon Template is required
            if ( empty( $template ) ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Coupon Template is required.',
                );
                continue;
            }
            // Coupon Template is invalid
            if ( !$template_coupon_id ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Coupon Template is invalid.',
                );
                continue;
            }
            // Get or create the user
            $new_user = false;
            $user = get_user_by( 'login', $username );
            if ( !$user ) {
                // Email is required
                if ( empty( $email ) ) {
                    $row_errors[] = array(
                        'data'    => $row_data,
                        'message' => 'User does not exist. Email is required.',
                    );
                    continue;
                }
                // First Name is required
                if ( empty( $first_name ) ) {
                    $row_errors[] = array(
                        'data'    => $row_data,
                        'message' => 'User does not exist. First Name is required.',
                    );
                    continue;
                }
                $user_id = wp_create_user( $username, wp_generate_password(), $email );
                if ( $user_id ) {
                    wp_update_user( [
                        'ID'         => $user_id,
                        'first_name' => $first_name,
                    ] );
                    // Send New Account Email
                    $wcusage_email_registration_new_enable = wcusage_get_setting_value( 'wcusage_field_email_registration_new_enable', '1' );
                    if ( $user_id && $wcusage_email_registration_new_enable ) {
                        wcusage_email_affiliate_register_new(
                            $email,
                            $coupon_code,
                            $first_name,
                            $username,
                            $user_id
                        );
                    }
                    $new_user = true;
                }
            } else {
                $user_id = $user->ID;
                $new_user = false;
            }
            // Create the coupon
            $coupon_id = wp_insert_post( [
                'post_title'   => $coupon_code,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'shop_coupon',
            ] );
            if ( !$coupon_id ) {
                $row_errors[] = array(
                    'data'    => $row_data,
                    'message' => 'Failed to create coupon.',
                );
                continue;
            }
            update_post_meta( $coupon_id, 'wcu_select_coupon_user', $user_id );
            // Set the coupon meta from template
            foreach ( $template_meta as $key => $value ) {
                if ( $key != 'wcu_select_coupon_user' ) {
                    update_post_meta( $coupon_id, $key, maybe_unserialize( $value[0] ) );
                }
            }
            // Store coupon code for rows without errors
            $success_rows[] = array(
                'data' => $row_data,
                'new'  => $new_user,
            );
        }
        if ( !empty( $success_rows ) ) {
            $response['success'] = true;
            $response['success_rows'] = $success_rows;
        }
        if ( !empty( $row_errors ) ) {
            $response['row_errors'] = $row_errors;
        }
    }
    wp_send_json( $response );
    exit;
}
