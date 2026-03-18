import ApiMixin from '../Api/ApiMixin.js';
import eventBus from "../eventBus.js";
const template = `
    <select ref="wooMlSubGroup" class="wc-enhanced-select">
        <option value="" disabled >{{ placeholder }}</option>
    </select>
`;

const CustomSelect = {
    props: {
        options: {
            type: Array,
            required: true,
        },
        placeholder: {
            type: String,
            default: '',
        },
        modelValue: {
            type: [String, Number, Array],
            default: null,
        },
        selectedGroup: {
            type: Object,
            default: null,
        },
    },
    data() {
      return {
          pagination: 0,
      }
    },
    template,
    mixins: [ApiMixin],
    mounted() {
        this.initSelect2(this.selectedGroup);
        eventBus.on('trigger-group-created', (group) => {
            this.initSelect2(group);
        });
    },
    methods: {
        initSelect2(group = null) {
            // Initialize select2 with the provided options and value
            const vm = this;
            jQuery(this.$el)
                .select2({
                    ajax: {
                        url: woo_mailerlite_admin_data.ajaxUrl,
                        method: "post",
                        data: function (params) {
                            return {
                                filter: params.term,
                                page: params.page || 1,
                                action: "woo_mailerlite_get_groups",
                                nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce,
                            }
                        },
                        // delay: 1000,
                        dataType: 'json',
                        processResults: function (data, params) {
                            let groupSelect = [];
                            if (data.data) {
                                for (let [key, value] of Object.entries(data.data)) {
                                    groupSelect.push({id : value.id, text: value.name});
                                }
                            }
                            if (data.pagination?.next === 'classic') {
                                data.pagination = !!data.data.length;
                            }
                            return {
                                results: groupSelect,
                                pagination: {
                                    more: data.pagination
                                }
                            };
                        },
                        // cache: true,
                        placeholder: 'Search for a group',
                    },
                    theme: 'mailerlite'
                })
                .val(this.value) // Set initial value
                .trigger('change')
                .on('change', function () {
                    vm.$emit('update:modelValue', jQuery(this).val());
                });
            if (group) {
                jQuery(this.$el).append(jQuery('<option>', {
                    value: group.id,
                    text: group.name
                }));
                jQuery(this.$el).val(group.id).trigger('change');
                // jQuery(this.$el).trigger({
                //     type: 'select2:select',
                //     params: {id: group.id, text: group.name}
                // });
            }
        },
    },
    watch: {
        value(newValue) {
            jQuery(this.$el).val(newValue).trigger('change');
        },
        options(newOptions) {
            jQuery(this.$el).select2('destroy');
            this.initSelect2();
        },
    },
    destroyed() {
        jQuery(this.$el).select2('destroy');
    },
};

export default CustomSelect;
