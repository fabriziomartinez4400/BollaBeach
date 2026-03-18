<?php
/**
 * Plugin Name: Woo Delivery Notice Configurable
 * Description: Aviso de entrega configurable por días de margen, días sin reparto y fechas bloqueadas. Muestra aviso en tienda, producto, carrito y checkout.
 * Version: 1.0.0
 * Author: Tu Equipo
 */

if (!defined('ABSPATH')) exit;

class Woo_Delivery_Notice_Configurable {
    private $option_group = 'wdnc_settings_group';
    private $option_name  = 'wdnc_settings';

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);

        // Mostrar aviso en varias vistas
        add_action('woocommerce_before_shop_loop',        [$this, 'show_notice']);
        add_action('woocommerce_single_product_summary',  [$this, 'show_notice'], 3);
        add_action('woocommerce_before_cart',             [$this, 'show_notice']);
        add_action('woocommerce_before_checkout_form',    [$this, 'show_notice']);
    }

    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            'Entrega',
            'Entrega',
            'manage_woocommerce',
            'wdnc-settings',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings() {
        register_setting($this->option_group, $this->option_name, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => [
                'lead_time' => 2,
                'off_weekdays' => [], // 0=Dom,1=Lun,...6=Sáb
                'blackout_dates' => '',
                'message' => 'Puedes comprar hoy; entregamos a partir del {date}.',
                'enabled' => 1,
            ],
        ]);

        add_settings_section('wdnc_main', 'Configuración de entrega', function(){}, $this->option_name);

        add_settings_field('enabled', 'Mostrar aviso', function(){
            $opts = get_option($this->option_name);
            echo '<input type="checkbox" name="'.$this->option_name.'[enabled]" value="1" '.checked(1, isset($opts['enabled']) ? $opts['enabled'] : 0, false).'> Activar';
        }, $this->option_name, 'wdnc_main');

        add_settings_field('lead_time', 'Días de margen (lead time)', function(){
            $opts = get_option($this->option_name);
            $v = isset($opts['lead_time']) ? intval($opts['lead_time']) : 2;
            echo '<input type="number" min="0" name="'.$this->option_name.'[lead_time]" value="'.esc_attr($v).'" style="width:100px;">';
            echo '<p class="description">Cantidad mínima de días antes de la primera entrega posible.</p>';
        }, $this->option_name, 'wdnc_main');

        add_settings_field('off_weekdays', 'Días sin reparto (semanales)', function(){
            $opts = get_option($this->option_name);
            $sel = isset($opts['off_weekdays']) && is_array($opts['off_weekdays']) ? $opts['off_weekdays'] : [];
            $days = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            foreach ($days as $i => $label) {
                $checked = in_array($i, $sel) ? 'checked' : '';
                echo '<label style="display:inline-block;margin-right:10px;"><input type="checkbox" name="'.$this->option_name.'[off_weekdays][]" value="'.$i.'" '.$checked.'> '.$label.'</label>';
            }
            echo '<p class="description">Marca los días en que <strong>no</strong> se realizarán entregas.</p>';
        }, $this->option_name, 'wdnc_main');

        add_settings_field('blackout_dates', 'Fechas bloqueadas (puntuales)', function(){
            $opts = get_option($this->option_name);
            $v = isset($opts['blackout_dates']) ? $opts['blackout_dates'] : '';
            echo '<textarea name="'.$this->option_name.'[blackout_dates]" rows="4" style="width:100%;">'.esc_textarea($v).'</textarea>';
            echo '<p class="description">Ingresa fechas sin reparto separadas por comas, formato <code>YYYY-MM-DD</code>. Ej: <code>2025-08-20, 2025-08-28</code></p>';
        }, $this->option_name, 'wdnc_main');

        add_settings_field('message', 'Mensaje a mostrar', function(){
            $opts = get_option($this->option_name);
            $v = isset($opts['message']) ? $opts['message'] : 'Puedes comprar hoy; entregamos a partir del {date}.';
            echo '<input type="text" name="'.$this->option_name.'[message]" value="'.esc_attr($v).'" style="width:100%;">';
            echo '<p class="description">Placeholders: <code>{days}</code> = días de espera, <code>{date}</code> = próxima fecha disponible.</p>';
        }, $this->option_name, 'wdnc_main');
    }

    public function sanitize_settings($input) {
        $out = [];
        $out['enabled'] = isset($input['enabled']) ? 1 : 0;
        $out['lead_time'] = isset($input['lead_time']) ? max(0, intval($input['lead_time'])) : 0;

        $out['off_weekdays'] = [];
        if (!empty($input['off_weekdays']) && is_array($input['off_weekdays'])) {
            foreach ($input['off_weekdays'] as $d) {
                $d = intval($d);
                if ($d >= 0 && $d <= 6) $out['off_weekdays'][] = $d;
            }
        }

        $out['blackout_dates'] = isset($input['blackout_dates']) ? sanitize_text_field($input['blackout_dates']) : '';
        $out['message'] = isset($input['message']) ? sanitize_text_field($input['message']) : 'Puedes comprar hoy; entregamos a partir del {date}.';
        return $out;
    }

    public function settings_page_html() {
        if (!current_user_can('manage_woocommerce')) return;
        ?>
        <div class="wrap">
            <h1>Entrega — Aviso de disponibilidad</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_group);
                do_settings_sections($this->option_name);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function show_notice() {
        if (!function_exists('wc_print_notice')) return;

        $opts = get_option($this->option_name);
        if (empty($opts) || empty($opts['enabled'])) return;

        $lead_days     = isset($opts['lead_time']) ? intval($opts['lead_time']) : 0;
        $off_weekdays  = isset($opts['off_weekdays']) && is_array($opts['off_weekdays']) ? array_map('intval', $opts['off_weekdays']) : [];
        $blackout_raw  = isset($opts['blackout_dates']) ? $opts['blackout_dates'] : '';
        $message_tpl   = !empty($opts['message']) ? $opts['message'] : 'Puedes comprar hoy; entregamos a partir del {date}.';

        $blackouts = [];
        if (!empty($blackout_raw)) {
            $parts = array_filter(array_map('trim', explode(',', $blackout_raw)));
            foreach ($parts as $p) {
                // Validar formato YYYY-MM-DD
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $p)) $blackouts[] = $p;
            }
        }

        // Calcular próxima fecha disponible
        $next_ts = $this->get_next_available_timestamp($lead_days, $off_weekdays, $blackouts);
        if (!$next_ts) return;

        // Construir mensaje
        $date_str = date_i18n(get_option('date_format', 'j \d\e F, Y'), $next_ts);
        $days_wait = $this->business_days_between(time(), $next_ts, $off_weekdays, $blackouts);

        $msg = str_replace(
            ['{days}', '{date}'],
            [max(0, $days_wait), esc_html($date_str)],
            $message_tpl
        );

        wc_print_notice(wp_kses_post($msg), 'notice');
    }

    private function get_next_available_timestamp($lead_days, $off_weekdays, $blackouts) {
        // Usar horario WP
        $ts = current_time('timestamp');

        // Avanzar lead time en días hábiles (saltando off y blackouts)
        $days_added = 0;
        while ($days_added < $lead_days) {
            $ts = strtotime('+1 day', $ts);
            if ($this->is_working_day($ts, $off_weekdays, $blackouts)) {
                $days_added++;
            }
        }

        // Si el día resultante cae en off/blackout, seguir avanzando hasta el próximo hábil
        while (!$this->is_working_day($ts, $off_weekdays, $blackouts)) {
            $ts = strtotime('+1 day', $ts);
        }
        return $ts;
    }

    private function is_working_day($ts, $off_weekdays, $blackouts) {
        $w = intval(date('w', $ts)); // 0=Dom .. 6=Sáb
        if (in_array($w, $off_weekdays, true)) return false;
        $ymd = date('Y-m-d', $ts);
        if (in_array($ymd, $blackouts, true)) return false;
        return true;
    }

    private function business_days_between($start_ts, $end_ts, $off_weekdays, $blackouts) {
        // Cuenta días hábiles entre dos timestamps (excluye start, incluye end si hábil)
        $count = 0;
        $ts = $start_ts;
        while (true) {
            $ts = strtotime('+1 day', $ts);
            if ($ts > $end_ts) break;
            if ($this->is_working_day($ts, $off_weekdays, $blackouts)) $count++;
        }
        return $count;
    }
}

new Woo_Delivery_Notice_Configurable();
