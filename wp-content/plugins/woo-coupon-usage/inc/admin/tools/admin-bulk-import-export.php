<?php
if (!defined('ABSPATH')) {
    exit;
}

function wcusage_data_import_export_tables() {
    if (wcu_fs()->can_use_premium_code()) {
        $tables = [
            'Activity' => 'wcusage_activity',
            'Campaigns' => 'wcusage_campaigns',
            'Clicks' => 'wcusage_clicks',
            'Direct Links' => 'wcusage_directlinks',
            'MLA Invites' => 'wcusage_mlainvites',
            'Payouts' => 'wcusage_payouts',
        ];
    } else {
        $tables = [
            'Activity' => 'wcusage_activity',
            'Clicks' => 'wcusage_clicks',
        ];
    }
    return $tables;
}

function wcusage_data_import_export_page() {

    // Check if user is administrator
    if ( ! wcusage_check_admin_access() ) {
        wp_die('Error: Permission denied.');
    }

    global $wpdb;

    $tables = wcusage_data_import_export_tables();
    
    echo '<div class="wrap wcusage-tools">';
    echo '<h1>Import/Export Database Tables (Beta)</h1>';
    echo '<p>Use this tool to import or export the custom database tables for the plugin.</p>';
    echo '<p>When importing the CSV file, this will overwrite the whole database table with the new data.</p>';
    echo '<p>Please be cautious and <strong>make sure to take backups</strong> before importing.</p>';

    foreach ($tables as $key => $table) {
        echo '<div class="import-export-container">';
        echo '<h2 style="margin: 0;">' . esc_html($key) . ' (' . esc_html($table) . ')';
        echo ' <button class="button button-secondary toggle-content" style="margin-left: 10px; margin-top: -5px; float: right;">Show</button>';
        echo '</h2>';
        echo '<div class="content" style="display: none;">'; // Initial display is set to none to hide the content

        // Export
        $nonce_export = wp_create_nonce('export-nonce-' . $table);
        echo '<p style="font-weight: bold;">Export:</p><a href="' . esc_url(add_query_arg(['table' => $table, 'export' => '1', 'nonce' => $nonce_export], esc_url(admin_url()))) . '" class="button button-primary">Export CSV</a>';

        // Import
        echo '<p style="font-weight: bold;">Import:</p><form method="post" enctype="multipart/form-data" class="import-form">';
        echo '<input type="file" name="import_file" id="import_file" />';
        echo '<input type="hidden" name="table" value="' . esc_attr($table) . '">';
        wp_nonce_field('import-nonce-' . $table, 'import_nonce');
        echo '<p style="margin-bottom: 0;"><input type="submit" name="submit" id="submit" class="button button-primary" value="Import CSV"></p>';
        echo '</form>';
        echo '</div>'; // .content
        echo '</div>'; // .import-export-container
    }
    
    echo '</div>'; // .wrap
    ?>

    <br/><br/>
    
    <p><a href="<?php echo esc_url(admin_url('admin.php?page=wcusage_tools')); ?>">Go back to tools ></a></p>

    <?php
    // Add some basic inline CSS for the admin page
    echo "
    <style>
        .import-export-container {
            max-width: 400px;
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .import-form {
            margin-top: 10px;
        }
        .import-form input[type='file'] {
            margin-right: 20px;
        }
    </style>
    ";

    // Add jQuery code to handle show/hide of content
    echo "
    <script>
    jQuery(document).ready(function($) {
        $('.toggle-content').on('click', function(e) {
            e.preventDefault();
            $(this).parent().next('.content').slideToggle();
            $(this).text(function(i, text){
                return text === 'Show' ? 'Hide' : 'Show';
            })
        });
    });
    </script>
    ";
}

add_action('admin_init', 'wcusage_handle_export_import');
function wcusage_handle_export_import() {

    global $wpdb;

    $tables = wcusage_data_import_export_tables();
    
    if (isset($_GET['export'], $_GET['table'], $_GET['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'export-nonce-' . $_GET['table'])) {

        // Check if user is administrator
        if ( ! wcusage_check_admin_access() ) {
            wp_die('Error: Permission denied. Failed to export data.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // Check if table in array
        if (isset($_GET['table'])) {
            $table = sanitize_text_field($_GET['table']);
            if (!in_array($table, $tables)) {
                wp_die('Error: Failed to find the table ' . esc_html($table), 'Error',  array( 'response' => 500, 'back_link' => true ));
            }
        } else {
            wp_die('Error: Failed to find the table.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // Check File
        $table = sanitize_text_field($_GET['table']);
        $filename = $table . '.csv';

        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$wpdb->prefix$table`"), ARRAY_A); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

        if ($data) {

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . $filename);

            $fp = fopen('php://output', 'w');
            fputcsv($fp, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($fp, $row);
            }

            fclose($fp);
            exit;

        } else {
            wp_die('Error: Failed to export data.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }
    }

    if (isset($_POST['table'], $_FILES['import_file']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['import_nonce'] ) ), 'import-nonce-' . $_POST['table'])) {
        
        // Check if user is administrator
        if ( ! wcusage_check_admin_access() ) {
            wp_die('Error: Permission denied. Failed to export data.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // Check if table in array
        if (isset($_POST['table'])) {
            $table = sanitize_text_field($_POST['table']);
            if (!in_array($table, $tables)) {
                wp_die(sprintf(esc_html__('Error: Failed to find the table %s', 'woo-coupon-usage'), esc_html($table)), 'Error',  array( 'response' => 500, 'back_link' => true ));
            }
        }

        // File
        $file = $_FILES['import_file'];

        if(empty($file['tmp_name'])) {
            wp_die('Error: Please upload a file.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // Check file
        $allowed_file_types = array('csv'); // Allowed file extensions
        $allowed_mime_types = array('text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values'); // Allowed MIME types for CSV
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_mime_type = mime_content_type($file['tmp_name']);
        if (!in_array(strtolower($file_extension), $allowed_file_types) || !in_array($file_mime_type, $allowed_mime_types)) {
            wp_die('Error: Invalid file type. Only CSV files are allowed.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // File Content Checks
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $headers = fgetcsv($handle, 1000, ",");
            // get headers from the current table in database
            $table = sanitize_text_field($_POST['table']);
            $expected_headers = $wpdb->get_col("SHOW COLUMNS FROM " . $wpdb->prefix . $table); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            if ($headers !== $expected_headers) {
                wp_die('Error: The CSV file does not have the expected headers.', 'Error',  array( 'response' => 500, 'back_link' => true ));
            }
            fclose($handle);
        } else {
            wp_die('Error: Failed to open the file.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        if ($file['error'] > 0) {
            wp_die('Error: ' . esc_html($file['error']), 'Error',  array( 'response' => 500, 'back_link' => true ));
        }

        // Check if table exists, if not, create it
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . $table));
        if (!$table_exists) {
            // Replace the SQL query below with your actual table schema
            $wpdb->query("CREATE TABLE IF NOT EXISTS `$wpdb->prefix$table` (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                data text NOT NULL,
                PRIMARY KEY (id)
            );"); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
        }

        $handle = fopen($file['tmp_name'], 'r');

        if ($handle) {
            
            $headers = fgetcsv($handle, 1000, ",");

            // Get the actual column names from the table
            $table = sanitize_text_field($_POST['table']);
            $table_columns = $wpdb->get_col($wpdb->prepare("DESCRIBE `$wpdb->prefix$table`")); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

            // Check if CSV column headers match the table columns
            if ($headers !== $table_columns) {
                wp_die('Error: The CSV column headers do not match the database table columns.', 'Error',  array( 'response' => 500, 'back_link' => true ));
            }

            if(in_array($table, $tables)) {
                $wpdb->query("TRUNCATE TABLE `$wpdb->prefix$table`"); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            }

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $insert_data = array_combine($headers, $data);

                // Sanitize each column before inserting
                foreach ($insert_data as $column => $value) {
                    $sanitized_value = sanitize_text_field($value);
                    $insert_data[$column] = $sanitized_value;
                }

                $datetime_columns = ['date', 'datepaid', 'dateaccepted', 'datecreated']; // Replace these with your datetime column names
                foreach ($datetime_columns as $datetime_column) {
                    if (isset($insert_data[$datetime_column])) {
                        $formatted_date = date_create_from_format('d/m/Y H:i', trim($insert_data[$datetime_column])); // Note the change in format
                        if($formatted_date) {
                            $insert_data[$datetime_column] = $formatted_date->format('Y-m-d H:i:s');
                        } else {
                            error_log('Error: Incorrect date format for ' . $datetime_column . '. The date was: ' . $insert_data[$datetime_column]);
                        }
                    }
                }

                $result = $wpdb->insert($wpdb->prefix . $table, $insert_data);
                if ($result === false) {
                    error_log('DB Insert Error for table ' . $wpdb->prefix . esc_html($table) . ': ' . $wpdb->last_error);
                }
            }

            echo '<div class="notice notice-success is-dismissible"><p>Import completed for table: '.esc_html($table).'</p></div>';

            fclose($handle);
        } else {
            wp_die('Error: Failed to open file.', 'Error',  array( 'response' => 500, 'back_link' => true ));
        }
    }
}
