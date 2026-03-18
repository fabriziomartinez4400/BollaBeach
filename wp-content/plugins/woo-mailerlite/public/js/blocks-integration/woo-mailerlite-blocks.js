( function ( blocksCheckout, element, settings, wpData ) {

    const innerBlock = 'mailerlite-block/woo-mailerlite'
    const { CheckboxControl, registerCheckoutBlock } = blocksCheckout
    const { getSetting } = settings

    const {
        MailerLiteWooActive,
        MailerLiteWooLabel,
        MailerLiteWooPreselect,
        MailerLiteWooHidden,
    } = getSetting( 'woo-mailerlite_data', '' );

    const WooMLBlock = () => {
        const el = element.createElement
        const { useState, useEffect } = element

        if (!MailerLiteWooActive) {
            return el(
                'div',
            );
        }

        const { LOCALE } = settings
        const [ checked, setChecked ] = useState(MailerLiteWooPreselect);
        const useShipping = wpData.select( 'wc/store/checkout' ).getUseShippingAsBilling()

        useEffect( () => {
            document.addEventListener('focusout', function () {
                if (!(document.querySelector('#billing-first_name')?.getAttribute('listener')) || !(document.querySelector('#shipping-first_name')?.getAttribute('listener'))) {
                    setupListeners()
                }
            });
            setupListeners()
        }, [] )

        function MailerLiteCheckbox() {
            return el(
                'div',
                { },
                el(
                    CheckboxControl,
                    {
                        id: 'woo_ml_subscribe',
                        name: 'woo_ml_subscribe',
                        label: MailerLiteWooLabel,
                        checked: checked,
                        onChange: updateChecked,
                    }
                ),
            )
        }

        function MailerLiteHiddenCheckbox() {
            return el(
                'input',
                {
                    id : 'woo_ml_subscribe',
                    name: 'woo_ml_subscribe',
                    type: 'hidden',
                    value: '1',
                    checked: true,
                    readOnly: true,
                },
            )
        }

        function checkoutMLSub( data ) {
            jQuery.ajax({
                url: wooMailerLitePublicData.ajaxUrl,
                type: "post",
                data: data
            })
        }

        function updateChecked( isChecked ) {
            if (checked !== isChecked) {
                setChecked(isChecked)
            }
            validateMLSub()
        }

        function validEmail(email) {
            const validFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

            return validFormat.test(email)
        }

        function validateMLSub() {
            if (window.mailerlitePublicJsCaptured) {
                return
            }
            if (!getCookie('mailerlite_checkout_token')) {
                var now = new Date();
                now.setTime(now.getTime() + 48 * 3600 * 1000);
                document.cookie = `mailerlite_checkout_token=${(+new Date).toString()}; expires=${now.toUTCString()}; path=/`;
            }
            const email = document.querySelector('#email')

            if (email && validEmail(email.value)) {
                let first_name = '';
                let last_name = '';

                if (useShipping && document.querySelector('#shipping-first_name')) {
                    const first_name_field = document.querySelector('#shipping-first_name')
                    const last_name_field = document.querySelector('#shipping-last_name')

                    if (first_name_field !== null) {
                        first_name = first_name_field.value;
                    }

                    if (last_name_field !== null) {
                        last_name = last_name_field.value;
                    }
                } else {
                    const first_name_field = document.querySelector('#billing-first_name')
                    const last_name_field = document.querySelector('#billing-last_name')

                    if (first_name_field !== null) {
                        first_name = first_name_field.value;
                    }

                    if (last_name_field !== null) {
                        last_name = last_name_field.value;
                    }
                }
                checkoutMLSub({
                    action: "woo_mailerlite_set_cart_email",
                    nonce: wooMailerLitePublicData.nonce,
                    email: email.value,
                    signup: document.querySelector('#woo_ml_subscribe').checked,
                    language: LOCALE.siteLocale,
                    name: first_name,
                    last_name: last_name,
                    cookie_mailerlite_checkout_token:getCookie('mailerlite_checkout_token')
                });
            }
        }

        function setupListeners() {
            const email = document.querySelector('#email')

            if (email) {
                email.addEventListener('focusout', validateMLSub)
            }

            if (useShipping) {
                const first_name_field = document.querySelector('#shipping-first_name')
                const last_name_field = document.querySelector('#shipping-last_name')

                if (first_name_field) {
                    first_name_field.addEventListener('focusout', validateMLSub)
                    first_name_field.setAttribute('listener', 'true');
                }

                if (last_name_field) {
                    last_name_field.addEventListener('focusout', validateMLSub)
                    last_name_field.setAttribute('listener', 'true');
                }
            } else {
                const first_name_field = document.querySelector('#billing-first_name')
                const last_name_field = document.querySelector('#billing-last_name')

                if (first_name_field) {
                    first_name_field.addEventListener('focusout', validateMLSub)
                    first_name_field.setAttribute('listener', 'true');
                }

                if (last_name_field) {
                    last_name_field.addEventListener('focusout', validateMLSub)
                    last_name_field.setAttribute('listener', 'true');
                }
            }
        }

        return el(
            'div',
            {
                'data-block-name': 'mailerlite-block/woo-mailerlite',
            },
            MailerLiteWooHidden ? MailerLiteHiddenCheckbox() : MailerLiteCheckbox()
        );
    };

    const checkoutOptions = {
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
                reusable: false
            },
        },
        component: () => {
            return WooMLBlock()
        },
    }

    registerCheckoutBlock(checkoutOptions)
} )( window.wc.blocksCheckout, window.wp.element, window.wc.wcSettings, window.wp.data );
