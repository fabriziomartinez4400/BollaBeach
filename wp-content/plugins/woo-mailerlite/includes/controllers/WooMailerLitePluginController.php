<?php

class WooMailerLitePluginController extends WooMailerLiteController
{
    protected $position = '';
    protected $label = '';
    protected $preselect = '';
    protected $hidden = '';
    protected $pluginEnabled = false;
    protected $checkoutEnabled = '';
    protected $group = false;

    public function __construct()
    {
        $this->position = WooMailerLiteOptions::get('settings.selectedCheckoutPosition', 'checkout_billing');
        $this->label = strip_tags(stripslashes(WooMailerLiteOptions::get('settings.checkoutLabel')));
        $this->hidden = WooMailerLiteOptions::get('settings.checkoutHidden');
        $this->preselect = WooMailerLiteOptions::get('settings.checkoutPreselect');
        $this->pluginEnabled = WooMailerLiteOptions::get('enabled');
        $this->checkoutEnabled = WooMailerLiteOptions::get('settings.subscribeOnCheckout');
        $this->group = WooMailerLiteOptions::get('group' , false);
    }
    public function addMlSubscribeCheckbox()
    {
        if (!$this->pluginEnabled || !$this->checkoutEnabled || ($this->position == 'checkout_billing_email') || !$this->group) {
            return;
        }

        if ($this->hidden) {
            ?>
            <input name="woo_ml_subscribe" type="hidden" id="woo_ml_subscribe" value="1" checked="checked"/>
            <?php
        } else {
            woocommerce_form_field('woo_ml_subscribe', array(
                'type'  => 'checkbox',
                'label' => __($this->label, 'woo-mailerlite'),
                'checked' => $this->preselect ? 'checked' : ''
            ), (bool) $this->preselect);
        }
    }

    public function addBillingCheckoutFields($fields)
    {
        if (!$this->pluginEnabled || !$this->checkoutEnabled || !$this->group || ($this->position !== 'checkout_billing_email')) {
            return $fields;
        }
        $new_billing_fields = [];

        foreach ($fields['billing'] as $key => $field) {
            $new_billing_fields[$key] = $field;
            if ($key === 'billing_email') {

                $new_billing_fields['woo_ml_subscribe'] = [
                    'type' => (!$this->hidden) ? 'checkbox' : 'hidden',
                    'default' => $this->preselect,
                    'required' => false,
                ];

                if (!$this->hidden) {
                    $new_billing_fields['woo_ml_subscribe']['label'] = __($this->label, 'woo-mailerlite');
                } else {
                    $new_billing_fields['woo_ml_subscribe']['custom_attributes'] = [
                        'checked' => 'checked',
                    ];
                }
            }
        }

        $fields['billing'] = $new_billing_fields;

        return $fields;
    }

    public function reloadCheckout()
    {
        try {
            if (!function_exists('WC') || !is_object(WC()->session)) {
                return false;
            }

            if ($this->requestHas('ml_checkout') && (ctype_digit($this->request['ml_checkout']) || wp_is_uuid($this->request['ml_checkout']))) {
                $escaped = db()->esc_like($this->request['ml_checkout']);
                $escaped = '%checkout_id":"' . addcslashes($escaped, '%_') . '"%';
                $cart = WooMailerLiteCart::where('data', 'like', $escaped)->first();
                if ($cart && $cart->exists()) {
                    $cartData = $cart->data;
                    unset($cartData['checkout_id']);
                    WC()->session->set('cart', $cartData);
                }
            }
        } catch (Throwable $e) {
            WooMailerLiteLog()->error('Error restoring cart from checkout ID: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        return true;
    }
}
