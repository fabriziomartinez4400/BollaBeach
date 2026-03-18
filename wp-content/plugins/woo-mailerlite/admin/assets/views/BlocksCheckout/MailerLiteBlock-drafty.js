(function ({ registerCheckoutBlock, CheckboxControl }, { createElement: el, useEffect, useState }, settings, wpData) {
    const innerBlock = 'mailerlite-block/woo-mailerlite';
    const {
        MailerLiteWooActive,
        MailerLiteWooLabel,
        MailerLiteWooPreselect,
        MailerLiteWooHidden,
    } = settings.getSetting('woo-mailerlite_data', '');

    const WooMailerLiteBlock = () => {
        const [checked, setChecked] = useState(MailerLiteWooPreselect);
        const useShipping = wpData.select('wc/store/checkout').getUseShippingAsBilling();

        useEffect(() => {
            setupListeners();
            document.addEventListener('focusout', reinitListenersIfMissing);
        }, []);

        const getNameFields = () => {
            const prefix = useShipping ? 'shipping' : 'billing';
            return {
                firstName: document.querySelector(`#${prefix}-first_name`)?.value || '',
                lastName: document.querySelector(`#${prefix}-last_name`)?.value || '',
            };
        };

        const getEmail = () => document.querySelector('#email')?.value || '';

        const updateChecked = (value) => {
            if (checked !== value) {
                setChecked(value);
                validateAndSend();
            }
        };

        const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        const ensureCookie = () => {
            if (!getCookie('mailerlite_checkout_token')) {
                const expires = new Date(Date.now() + 48 * 3600 * 1000).toUTCString();
                document.cookie = `mailerlite_checkout_token=${+new Date()}; expires=${expires}; path=/`;
            }
        };

        const validateAndSend = () => {
            const email = getEmail();
            if (!isValidEmail(email)) return;

            ensureCookie();
            const { firstName, lastName } = getNameFields();

            jQuery.post(woo_ml_public_post.ajax_url, {
                action: 'post_woo_ml_email_cookie',
                email,
                signup: document.querySelector('#woo_ml_subscribe')?.checked,
                language: settings.LOCALE.siteLocale,
                first_name: firstName,
                last_name: lastName,
                cookie_mailerlite_checkout_token: getCookie('mailerlite_checkout_token'),
            });
        };

        const setupListeners = () => {
            const email = document.querySelector('#email');
            email?.addEventListener('focusout', validateAndSend);

            const { firstName, lastName } = getNameFields();
            ['#billing-first_name', '#billing-last_name', '#shipping-first_name', '#shipping-last_name'].forEach(selector => {
                const el = document.querySelector(selector);
                if (el && !el.getAttribute('listener')) {
                    el.addEventListener('focusout', validateAndSend);
                    el.setAttribute('listener', 'true');
                }
            });
        };

        const reinitListenersIfMissing = () => {
            ['#billing-first_name', '#shipping-first_name'].forEach(selector => {
                if (!document.querySelector(selector)?.getAttribute('listener')) {
                    setupListeners();
                }
            });
        };

        const Checkbox = () => el('div', {}, el(CheckboxControl, {
            id: 'woo_ml_subscribe',
            name: 'woo_ml_subscribe',
            label: MailerLiteWooLabel,
            checked,
            onChange: updateChecked,
        }));

        const HiddenInput = () => el('input', {
            type: 'hidden',
            id: 'woo_ml_subscribe',
            name: 'woo_ml_subscribe',
            value: '1',
            readOnly: true,
        });

        if (!MailerLiteWooActive) return el('div');
        return MailerLiteWooHidden ? HiddenInput() : Checkbox();
    };

    registerCheckoutBlock({
        metadata: {
            name: innerBlock,
            parent: [
                'woocommerce/checkout-totals-block',
                'woocommerce/checkout-fields-block',
                'woocommerce/checkout-contact-information-block',
                'woocommerce/checkout-shipping-address-block',
                'woocommerce/checkout-billing-address-block',
                'woocommerce/checkout-shipping-methods-block',
                'woocommerce/checkout-payment-methods-block',
            ],
            supports: {
                multiple: false,
                reusable: false,
            },
        },
        component: WooMailerLiteBlock,
    });
})(window.wc.blocksCheckout, window.wp.element, window.wc.wcSettings, window.wp.data);