( function ( blocks, element, i18n, blocksCheckout, blockEditor, components,settings, ajax, wpData ) {

    const { CheckboxControl } = blocksCheckout
    const { useState } = element
    const { useBlockProps, InspectorControls} = blockEditor || {}
    const { PanelBody, ToggleControl } = components || {}
    const { getSetting } = settings
    const innerBlock = 'mailerlite-block/woo-mailerlite'

    const icon = element.createElement('svg',
        {
            width: 21,
            height: 21,
            viewBox: '0 0 21 21'
        },
        element.createElement( 'g',
            {
                id: 'Page-1',
                stroke: 'none',
                strokeWidth: 1,
                fill: 'none',
                fillRule: 'evenodd'
            },
            element.createElement( 'g',
                {
                    id: 'mailerlitelogo',
                    transform: 'translate(0.198319, 0.325455)',
                    fill: '#09C269',
                    fillRule: 'nonzero'
                },
                element.createElement( 'path',
                    {
                        id: 'Shape-path',
                        d: "M17.2807581,0.115646258 L2.78853487,0.115646258 C1.28807741,0.115646258 0.0437956203,1.34864717 0.0437956203,2.8355012 L0.0437956203,11.9016843 L0.0437956203,13.6786562 L0.0437956203,20.1156463 L3.83153579,16.3985111 L17.2990564,16.3985111 C18.7995138,16.3985111 20.0437956,15.1655103 20.0437956,13.6786562 L20.0437956,2.8355012 C20.0254974,1.3305148 18.7995138,0.115646258 17.2807581,0.115646258 Z"
                    }
                )
            )
        )
    );

    const {
        __
    } = i18n;

    const el = element.createElement;

    const {
        MailerLiteWooActive,
        MailerLiteWooLabel,
        MailerLiteWooPreselect,
        MailerLiteWooHidden,
        MailerLiteNonce,
        MailerLiteAdminURL,
    } = getSetting( 'woo-mailerlite_data', '' );

    blocks.registerBlockType( innerBlock, {

        title: __('MailerLite – WooCommerce integration', 'woo-mailerlite'),
        icon: icon,
        description: 'Allows checkout integration with MailerLite.',
        supports: {
            multiple: false,
            reusable: false
        },
        category: "woocommerce",
        parent: [
            'woocommerce/checkout-totals-block',
            'woocommerce/checkout-fields-block',
            'woocommerce/checkout-contact-information-block',
            'woocommerce/checkout-shipping-address-block',
            'woocommerce/checkout-billing-address-block',
            'woocommerce/checkout-shipping-methods-block',
            'woocommerce/checkout-payment-methods-block',
        ],

        attributes: {
            preSelect: {
                type: 'bool',
                default: MailerLiteWooPreselect,
            },
            blockLabel: {
                type: 'string',
                default: MailerLiteWooLabel,
            },
            blockActive: {
                type: 'bool',
                default: MailerLiteWooActive,
            },
            blockHidden: {
                type: 'bool',
                default: MailerLiteWooHidden,
            }
        },

        edit: function ( props ) {

            const [ checked, setChecked ] = useState( props.attributes.preSelect )
            const [ checkedHidden, setHiddenChecked ] = useState( props.attributes.blockHidden )
            const blockProps = useBlockProps()

            function updateChecked( isChecked ) {

                ajax.post(
                    'woo_ml_admin_ajax_update_preselect_checkbox',
                    {
                        preselect: isChecked,
                        _wpnonce: MailerLiteNonce,
                    }
                ).done(function(response) {
                    // success
                    setChecked(isChecked)

                    props.setAttributes({
                        'preSelect': isChecked
                    })
                }).fail(function(xhr, status, err) {

                    wpData.dispatch("core/notices").createNotice(
                        'error',
                        'Could not update settings.',
                        {
                            isDismissible: true
                        }
                    )
                })
            }

            function updateHiddenChecked( isChecked ) {

                ajax.post(
                    'woo_ml_admin_ajax_update_hide_checkbox',
                    {
                        hidden: isChecked,
                        _wpnonce: MailerLiteNonce,
                    }
                ).done(function(response) {
                    // success
                    setHiddenChecked(isChecked)

                    props.setAttributes({
                        'blockHidden': isChecked
                    })
                }).fail(function(xhr, status, err) {

                    wpData.dispatch("core/notices").createNotice(
                        'error',
                        'Could not update settings.',
                        {
                            isDismissible: true
                        }
                    )
                })
            }

            function MailerLiteCheckbox() {
                return el(
                    CheckboxControl,
                    {
                        label: props.attributes.blockLabel,
                        checked: props.attributes.preSelect,
                        disabled: true,
                        readOnly: true,
                    }
                )
            }

            function MailerliteHiddenCheckbox() {
                return el(
                    'p',
                    {},
                    __('[ Placeholder for the hidden MailerLite checkbox ]', 'woo-mailerlite')
                )
            }

            return(el('div', { ...blockProps },
                    el(
                        InspectorControls,
                        {},
                        el(
                            PanelBody,
                            {
                                title: __('Settings', 'woo-mailerlite'),
                            },
                            el(
                                ToggleControl,
                                {
                                    label: 'Preselect checkbox',
                                    help: 'Check to preselect the signup checkbox by default',
                                    checked: checked,
                                    onChange: updateChecked,
                                }
                            ),
                            el(
                                ToggleControl,
                                {
                                    label: 'Hide checkbox',
                                    help: 'Check to hide the checkbox. All customers will be subscribed automatically',
                                    checked: checkedHidden,
                                    onChange: updateHiddenChecked,
                                }
                            ),
                            el(
                                'p',
                                {},
                                'For additional settings click ',
                                el(
                                    'a',
                                    {
                                        href: MailerLiteAdminURL,
                                    },
                                    'here'
                                )
                            )
                        ),
                    ),
                    checkedHidden ? MailerliteHiddenCheckbox() : MailerLiteCheckbox(),
                )
            );
        },

        save: function ( props ) {

            return(
                el('div', { ...useBlockProps.save() })
            );
        },
    } );
} )( window.wp.blocks, window.wp.element, window.wp.i18n, window.wc.blocksCheckout, window.wp.blockEditor, window.wp.components, window.wc.wcSettings, window.wp.ajax, window.wp.data );