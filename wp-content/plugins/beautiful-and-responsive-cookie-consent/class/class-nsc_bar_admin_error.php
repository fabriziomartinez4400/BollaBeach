<?php
if (!defined('ABSPATH')) {
    exit;
}

class nsc_bar_admin_error
{
    public $errors;
    private $global_message_warning;
    private $global_message_warning_slug;
    private $global_message_warning_version;

    public function __construct()
    {
        $this->errors = array();
    }

    public function nsc_bar_set_global_warning_message($message, $slug, $version)
    {
        $this->global_message_warning = $message;
        $this->global_message_warning_slug = $slug;
        $this->global_message_warning_version = $version;
    }

    public function nsc_bar_display_errors()
    {
        if (!empty($this->errors)) {
            add_action('admin_notices', array($this, 'nsc_bar_add_admin_errors'));
        }
    }

    public function nsc_bar_set_admin_error($error, $type = "error")
    {
        $this->errors[$type][] = $error;
    }

    public function nsc_bar_add_admin_errors()
    {
        $uniqueErrors = array_unique($this->errors);
        foreach ($uniqueErrors as $error_type => $type) {
            $uniqueErrorTypes = array_unique($type);
            foreach ($uniqueErrorTypes as $error_message) {
                echo '<div class="notice notice-error">
                       <p>' . __($error_message, "nsc_bar_cookie_consent") . '</p>
                    </div>';
            }
        }
    }

    public function nsc_bar_global_admin_warning()
    {
        if (empty($this->global_message_warning)) return;
        if (! current_user_can("activate_plugins")) return;
        $user_id = get_current_user_id();
        if (! $user_id) return;

        $userMetaKey = $this->get_global_warning_user_meta_key();
        if (get_user_meta($user_id, $userMetaKey, true)) return;

        $post_target = is_network_admin() ? network_admin_url('admin-post.php') : admin_url('admin-post.php');


        echo '<div class="notice notice-warning is-dismissible" style="position:relative; padding-bottom:1em;">';
        echo wp_kses_post('<p>' . $this->global_message_warning . '</p>');
        echo '<form method="post" action="' . esc_url($post_target) . '" style="display:inline-block;margin-top:6px">';
        echo '<input type="hidden" name="action" value="' . esc_attr($this->global_message_warning_slug) . '">';
        wp_nonce_field(esc_attr($this->global_message_warning_slug) . "_" . esc_attr($user_id));
        echo '<a class="button button-secondary" href="/wp-admin/options-general.php?page=nsc_bar-cookie-consent&tab=license&nsc_bara_language_selector=xx">Check Licence Key</a>';
        echo '<button type="submit" style="border: 0;background: transparent;text-decoration:underline;padding-left:0.8em; padding-top:0.4em">I accept the risks (hide)</button>';
        echo '</form>';
        echo '</div>';
    }

    public function nsc_bar_handle_dismiss()
    {
        if (!is_user_logged_in()) auth_redirect();

        $user_id = get_current_user_id();
        check_admin_referer($this->global_message_warning_slug . '_' . $user_id);

        $userMetaKey = $this->get_global_warning_user_meta_key();
        update_user_meta($user_id, $userMetaKey, time());

        $redirect = wp_get_referer();
        if (! $redirect) {
            $redirect = is_network_admin() ? network_admin_url() : admin_url();
        }
        wp_safe_redirect($redirect);
        exit;
    }

    private function get_global_warning_user_meta_key()
    {
        return $this->global_message_warning_slug . "_v" .   $this->global_message_warning_version;
    }
}
