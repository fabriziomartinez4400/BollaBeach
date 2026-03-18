jQuery(document).ready(function(a) {
    const allowedInputs = [
        'billing_email',
        'billing_first_name',
        'email',
        'billing_last_name',
        'woo_ml_subscribe',
        'billing-first_name',
        'billing-last_name',
        'shipping-first_name',
        'shipping-last_name'
    ];
    let email = null;
    let firstName = null;
    let lastName = null;
    let foundEmail = false;
    let checkboxAdded = false;
    let mailerLiteCheckoutBlockActive = null;
    let listeningEvents = false;
    let iteratorInterrupt = 0;

    // temporarily forcefully picking this js not blocks one
    window.mailerlitePublicJsCaptured = true;

    if (wooMailerLitePublicData.checkboxSettings.enabled && document.body.classList.contains('woocommerce-checkout')) {
        triggerAddEvents();
    }

    var execute;

    if (wooMailerLitePublicData.checkboxSettings.preselect) {
        jQuery('#woo_ml_subscribe').prop('checked', true);
    }

    if (wooMailerLitePublicData?.checkboxSettings?.enabled) {
        let retryCount = 0;
        const maxRetries = 20;

        const interval = setInterval(() => {
            const inputFound = allowedInputs.some(id => document.getElementById(id));

            if (inputFound) {
                triggerAddEvents();
                clearInterval(interval);
            } else if (++retryCount >= maxRetries) {
                clearInterval(interval);
            }
        }, 500);
    }

    function triggerAddEvents() {
        iteratorInterrupt++;
        if (iteratorInterrupt >= 5) {
            return false;
        }
        const mailerLiteCheckoutBlockWrapper = document.querySelector('[data-block-name="mailerlite-block/woo-mailerlite"]');

        if (mailerLiteCheckoutBlockWrapper && mailerLiteCheckoutBlockWrapper.querySelector('#woo_ml_subscribe')) {
            mailerLiteCheckoutBlockActive = true;
        }

        if (listeningEvents && (checkboxAdded || (mailerLiteCheckoutBlockActive !== null && mailerLiteCheckoutBlockActive))) {
            return false;
        }

        allowedInputs.forEach((val) => {
            if (!foundEmail && val.match('email')) {
                email = document.querySelector('#' + val)
                if (email) {
                    foundEmail = true;
                }
            }

            if (val.match('first_name')) {
                if (document.querySelector('#' + val)) {
                    firstName = document.querySelector('#' + val);
                }
            }
            if (val.match('last_name')) {
                if (document.querySelector('#' + val)) {
                    lastName = document.querySelector('#' + val);
                }
            }
        });

        let signup = document.getElementById('woo_ml_subscribe');

        if (email !== null && !signup) {
            const checkboxWrapper = document.createElement('div');
            checkboxWrapper.className = 'woo-ml-subscribe-wrapper';
            checkboxWrapper.style.marginTop = '1rem';
            const wooMlCheckoutCheckbox = document.createElement('input');
            wooMlCheckoutCheckbox.setAttribute('id', 'woo_ml_subscribe');
            wooMlCheckoutCheckbox.setAttribute('type', 'checkbox');
            wooMlCheckoutCheckbox.setAttribute('name', 'woo_ml_subscribe');
            wooMlCheckoutCheckbox.setAttribute('value', wooMailerLitePublicData.checkboxSettings.preselect ? 1 : 0);
            if (wooMailerLitePublicData.checkboxSettings.preselect) {
                wooMlCheckoutCheckbox.setAttribute('checked', 'checked');
            }

            const label = document.createElement('label');
            label.style.cursor = 'pointer';
            label.style.display = 'inline-flex';
            label.style.alignItems = 'center';
            label.style.gap = '0.5rem';
            label.htmlFor = 'woo_ml_subscribe';
            if (wooMailerLitePublicData.checkboxSettings.hidden) {
                label.style.display = 'none';
            }

            const labelText = document.createElement('span');
            labelText.textContent = wooMailerLitePublicData.checkboxSettings.label ?? 'Yes, I want to receive your newsletter.';

            label.appendChild(wooMlCheckoutCheckbox);
            label.appendChild(labelText);
            checkboxWrapper.appendChild(label);
            if (wooMailerLitePublicData.checkboxSettings.hidden) {
                wooMlCheckoutCheckbox.setAttribute('type', 'hidden');
            }

            const wrapper = email.closest('div') ?? email;
            wrapper.parentNode.insertBefore(checkboxWrapper, wrapper.nextSibling);
            signup = document.getElementById('woo_ml_subscribe');
            checkboxAdded = true;
        }

        if (email !== null && !listeningEvents) {
            listeningEvents = true;

            document.addEventListener('change', (event) => {
                if (event.target && event.target.matches('input[name="billing_email"]')) {
                    validateMLSub(event);
                }
            });

            email.addEventListener('change', (event) => {
                validateMLSub(event);
            });

            if (firstName !== null) {
                firstName.addEventListener('change', (event) => {
                    if (firstName.value.length > 0) {
                        validateMLSub(event);
                    }
                });
            }

            if (lastName !== null) {
                lastName.addEventListener('change', (event) => {
                    if (lastName.value.length > 0) {
                        validateMLSub(event);
                    }
                });
            }

            if (signup !== null) {
                a(document).on('change', signup, function(event) {
                    if (event.target.id === 'woo_ml_subscribe') {
                        validateMLSub(event);
                    }
                });
            }
        }
    }

    function validateMLSub(e) {
        if (email !== null && email.value.length > 0) {
            checkoutMLSub(e);
        }
    }

    function checkoutMLSub(e) {
        clearTimeout(execute);
        execute = setTimeout(() => {
            if (!allowedInputs.includes(e.target.id)) {
                return false;
            }

            if (!getCookie('mailerlite_checkout_token')) {
                var now = new Date();
                now.setTime(now.getTime() + 48 * 3600 * 1000);
                document.cookie = `mailerlite_checkout_token=${(+new Date).toString()}; expires=${now.toUTCString()}; path=/`;
            }

            const accept_marketing = document.querySelector('#woo_ml_subscribe').checked;

            jQuery.ajax({
                url: wooMailerLitePublicData.ajaxUrl,
                type: "post",
                data: {
                    action: "woo_mailerlite_set_cart_email",
                    nonce: wooMailerLitePublicData.nonce,
                    email: email.value ?? null,
                    signup: accept_marketing,
                    language: wooMailerLitePublicData.language,
                    name: firstName?.value ?? '',
                    last_name: lastName?.value ?? '',
                    cookie_mailerlite_checkout_token: getCookie('mailerlite_checkout_token')
                }
            });
        }, 2);
    }
});

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift()
    }
    return null;
}
