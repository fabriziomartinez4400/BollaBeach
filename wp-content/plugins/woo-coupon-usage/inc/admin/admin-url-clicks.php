<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* 
* Create Clicks Admin Page
*/
function wcusage_admin_clicks_page_html() {
if ( ! wcusage_check_admin_access() ) {
  return;
}
?>

<link rel="stylesheet" href="<?php echo esc_url(WCUSAGE_UNIQUE_PLUGIN_URL) .'fonts/font-awesome/css/all.min.css'; ?>" crossorigin="anonymous">

<!-- Check Website Field Enabled -->
<?php
$wcusage_click_enable_website = wcusage_get_setting_value('wcusage_field_click_enable_website', '0');
$wcusage_field_track_click_ip = wcusage_get_setting_value('wcusage_field_track_click_ip', '1');
?>

<?php if(!$wcusage_click_enable_website) { ?>
<style>.column-website { display: none; }</style>
<?php } ?>

<!-- Delete Click Entry When Click Delete Button -->
<?php
if(isset($_POST['_wpnonce'])) {
  $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
  if(isset($_POST['wcu-status-delete']) && wp_verify_nonce( $nonce, 'delete_url' )  ){
  	$postid = sanitize_text_field( $_POST['wcu-id'] );
  	$delete = wcusage_delete_click_entry($postid);
  }
}
?>

<!-- Add BlackList IP/ID When When Click Button -->
<?php
if(isset($_POST['_wpnonce'])) {
  $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
  if(isset($_POST['wcu-blacklist-ipaddress']) && wp_verify_nonce( $nonce, 'blacklist_url' )  ){
    $option_group = get_option('wcusage_options');
    $wcusage_field_fraud_block_ips = wc_sanitize_textarea($option_group['wcusage_field_fraud_block_ips'] . "\n" . $_POST['wcu-blacklist-ipaddress']);
    $wcusage_field_fraud_block_ips = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $wcusage_field_fraud_block_ips); /* Remove Empty Lines */
    $option_group['wcusage_field_fraud_block_ips'] = $wcusage_field_fraud_block_ips;
    update_option( 'wcusage_options', $option_group );
  }
}
?>

<!-- Remove BlackList IP/ID When When Click Button -->
<?php
if(isset($_POST['_wpnonce'])) {
  $nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
  if(isset($_POST['wcu-blacklist-ipaddress-remove']) && wp_verify_nonce( $nonce, 'blacklist_url' )  ){
    $option_group = get_option('wcusage_options');
    $wcusage_field_fraud_block_ips = wc_sanitize_textarea( str_replace( $_POST['wcu-blacklist-ipaddress-remove'], '', $option_group['wcusage_field_fraud_block_ips']) );
    $wcusage_field_fraud_block_ips = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $wcusage_field_fraud_block_ips); /* Remove Empty Lines */
    $option_group['wcusage_field_fraud_block_ips'] = $wcusage_field_fraud_block_ips;
    update_option( 'wcusage_options', $option_group );
  }
}
?>

<!-- Styling -->
<style type="text/css">
.column-id { width: 50px; }
<?php if( !wcu_fs()->can_use_premium_code() ) { ?>
.column-campaign { display: none; }
<?php } ?>
</style>

<!-- Output Page -->
<div class="wrap plugin-settings">

  <?php do_action( 'wcusage_hook_dashboard_page_header', ''); ?>

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <?php
  $input_coupon = (isset($_REQUEST['coupon'])) ? $_REQUEST['coupon'] : "";
  $input_campaign = (isset($_REQUEST['campaign'])) ? $_REQUEST['campaign'] : "";
  $input_referrer = (isset($_REQUEST['referrer'])) ? $_REQUEST['referrer'] : "";
  $input_converted = (isset($_REQUEST['converted'])) ? $_REQUEST['converted'] : "";
  ?>

  <form method="get" style="margin-top: 10px;">
    <label>Search:</label>
    <input type="hidden" name="page" value="wcusage_clicks">
    <input type="search" id="user-search-input1" name="coupon" value="<?php esc_attr($input_coupon) ?>" placeholder="<?php echo esc_html__( 'Coupon Code', 'woo-coupon-usage' ); ?>..." title="<?php echo esc_html__( 'Coupon Code', 'woo-coupon-usage' ); ?>" style="max-width: 140px;">
    <?php if( wcu_fs()->can_use_premium_code() ) { ?>
    <input type="search" id="user-search-input2" name="campaign" value="<?php esc_attr($input_campaign) ?>" placeholder="<?php echo esc_html__( 'Campaign', 'woo-coupon-usage' ); ?>..." title="<?php echo esc_html__( 'Campaign', 'woo-coupon-usage' ); ?>" style="max-width: 140px;">
    <?php } ?>
    <input type="search" id="user-search-input3" name="referrer" value="<?php esc_attr($input_referrer) ?>" placeholder="<?php echo esc_html__( 'Referrer URL', 'woo-coupon-usage' ); ?>..." title="<?php echo esc_html__( 'Referrer URL', 'woo-coupon-usage' ); ?>" style="max-width: 140px;">
    <select id="user-search-input4" name="converted" value="<?php esc_attr($input_converted) ?>"
      placeholder="<?php echo esc_html__( 'Converted', 'woo-coupon-usage' ); ?>..."
      title="<?php echo esc_html__( 'Converted', 'woo-coupon-usage' ); ?>" style="max-width: 140px; margin-top: -4px;">
      <option value="">Converted...</option>
      <option value="1" <?php if($input_converted == "1") { echo "selected"; } ?>>Yes</option>
      <option value="0" <?php if($input_converted == "0") { echo "selected"; } ?>>No</option>
    </select>
    <input type="submit" id="search-submit" class="button" value="Search Visits">
	</form>

	<?php
  $ListTable = new WCUsage_Clicks_List_Table();
  $ListTable->prepare_items();
	?>
	<div style="margin-top: -30px;">
		<input type="hidden" name="page" value="<?php echo esc_html( $_GET['page'] ) ?>" />
		<?php $ListTable->display() ?>
	</div>
</div>

<?php
}

/***** Function to Delete Click Entry *****/
function wcusage_delete_click_entry($id) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'wcusage_clicks';
  $query = $wpdb->prepare("DELETE FROM $table_name WHERE id = %d", $id); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
  return $wpdb->query($query); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
}