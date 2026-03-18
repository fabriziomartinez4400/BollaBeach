const template = `
    <select v-model="setSyncFields"/>
`;

const SyncFields = {
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
    },
    template,
    mounted() {
        this.defaultValues = this.options
            .filter((opt) => opt.default)
            .map((opt) => opt.value);
        this.selectedSyncFields = [...new Set([...this.selectedSyncFields, ...this.defaultValues])];
        if (this.modelValue) {
            this.selectedSyncFields = [...new Set([...this.selectedSyncFields, ...this.modelValue])];
        }
        this.initSelect2();
    },
    computed: {
        setSyncFields: {
            set(value) {
                this.$emit('update:modelValue', value);
            },
        },
    },
    data() {
        return {
            selectedSyncFields: [],
            defaultValues: [],
        }
    },
    methods: {

        initSelect2() {

            // Initialize select2 with the provided options and value
            const vm = this;

            jQuery(this.$el)
                .select2({
                    theme: 'mailerlite',
                    closeOnSelect : false,
                    allowHtml: true,
                    allowClear: true,
                    tags: false,
                    data: this.options
                        .filter(opt => opt && opt.value && opt.text)
                        .map(opt => ({
                            id: opt.value,
                            text: opt.text,
                            default: opt.default || false
                        })),
                    placeholder: this.placeholder,
                    templateSelection : function (tag, container) {
                        let is_default = jQuery(tag.element).attr('ml-default');
                        if (typeof is_default !== 'undefined' && is_default !== false){
                            jQuery(container).addClass('ml-default-choice');
                            tag['ml-default'] = true;
                        }
                        return tag.text;
                    },
                }).on('select2:selecting', function (e) {
                    if (e.params.args.data.element.value) {
                        if (!vm.selectedSyncFields.includes(e.params.args.data.element.value)) {
                            vm.selectedSyncFields.push(e.params.args.data.element.value)
                            vm.setSyncFields = vm.selectedSyncFields
                        }
                    }
                }).on('select2:unselecting', function(e){
                const unselectedOption = e.params.args.data;
                const isDefault = vm.options.some(
                    (opt) => opt.value === unselectedOption.id && opt.default
                );
                if (isDefault) {
                    e.preventDefault();
                }
                vm.selectedSyncFields = vm.selectedSyncFields.filter(item => item !== e.params.args.data.element.value)
                vm.setSyncFields = vm.selectedSyncFields
            });
            jQuery(this.$el).val(this.selectedSyncFields);
            jQuery(this.$el).trigger('change');
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

export default SyncFields;
