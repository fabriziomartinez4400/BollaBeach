import eventBus from '../eventBus.js';
import CustomSelect from '../Forms/CustomSelect.js';
import SyncFields from '../Forms/SyncFields.js';
import ApiMixin from '../Api/ApiMixin.js';
import {validate} from "../Plugins/Validation.js";

const template = `
<div v-if="currentStep === 0" class="header-title-ml">
    <h1>Connect MailerLite with WooCommerce</h1>
    <p>Build custom segments, send automations, and track purchase activity in MailerLite. Enter your MailerLite account's API key to start using the integration.</p>
</div>

<div v-if="currentStep === 1">
    <button @click="backToApi" type="button" id="woo_ml_wizard_back_step_1" class="btn btn-link-ml flex-start-ml flex-ml align-items-center-ml" style="margin-bottom: 1rem;">
        <svg width="14" height="14" fill="none" style="margin-right: 0.25rem;" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to API key</button>
    <div class="header-title-ml">
        <h1 v-if="mlPlatform === 'rewrite'">Import your customers to MailerLite</h1>
        <h1 v-else>Select subscriber group</h1>
        <p>Subscribers from WooCommerce will join this MailerLite subscriber group.</p>
    </div>
</div>

<!-- Wizard content -->
<div class="woo-ml-wizard-content">
    <div v-if="currentStep === 0" id="woo-ml-wizard-step-one">
        <div class="input-block-ml">
            <label for="wooMlApiKey" class="settings-label mb-3-ml">API key</label>
            <div class="api-key-input">
                <input ref="wooMlApiKey" type="text" name="api-key" placeholder="Enter your MailerLite API key" v-model="wooMlApiKey" :disabled="isLoading">
                <button @click="connectAccount" :class="{ 'woo-ml-button-loading': isLoading }" :disabled="isLoading" type="button" id="wooMlWizardApiKeyBtn" class="btn-primary-ml"><span class="woo-ml-button-text">Connect account</span></button>
            </div>
            <div class="signup-link-ml">
                <p>Don't you have a MailerLite account yet? Click to <a href="https://www.mailerlite.com/signup?utm_source=referral&utm_medium=woocommerce&utm_campaign=integration" target="_blank">Sign up</a></p>
            </div>
        </div>

        <div class="border-top-ml">
            <div class="header-title-ml" style="margin-bottom: 0;">
                <h2>Having trouble? Follow the instructions:</h2>
                <ol class="instructions-list">
                    <li>Access your MailerLite account and go to the <a href="https://dashboard.mailerlite.com/integrations" target="_blank" class="body-link-ml">Integrations tab.</a></li>
                    <li>Locate the MailerLite API section and click Use and then Generate new token.</li>
                    <li>Enter a name for the token such as "WooCommerce" and click Generate token.</li>
                    <li>Copy the token and return to the Integrations page in WooCommerce. Paste your key in the API key box and click Connect account.
                    </li>
                </ol>
            </div>
            <div style="display: none;">
                <h3>Not a client of MailerLite? Click Sign up to create your account</h3>
                <a class="btn btn-secondary-ml" href="https://www.mailerlite.com/signup?utm_source=referral&utm_medium=woocommerce&utm_campaign=integration" target="_blank">Create account</a>
            </div>
        </div>
        <div class="border-top-ml">
            <label class="settings-label-medium" style="margin:0;">Still having trouble connecting to your account? <a @click="openDebugLog" id="openDebugLog">Click here</a> for an advanced troubleshooting. </label>
        </div>
    </div>

    <div v-if="currentStep === 1">
        <div id="woo-ml-wizard-step-two">
            <label for="wooMlSubGroup" class="settings-label mb-3-ml">Group</label>
            <label class="input-mailerlite mb-2-ml" style="display: flex;">
                <custom-select 
                    ref="wooMlSubGroupComponent" 
                    class="wc-enhanced-select" 
                    name="subscriber-group" 
                    style="width: 100%;"
                    :options="groups"
                    v-model="selectedGroup"
                    placeholder="Select group"
                ></custom-select>
                <button @click="createGroup" id="createGroupModal" type="button" class="btn-secondary-ml" style="margin-left: 0.5rem; white-space: nowrap;">Create group</button>
            </label>
            
        </div>
        <label class="settings-label-small mt-6-ml">{{ accountInfo }}</label>
        <div v-if="mlPlatform === 'classic'" class="border-top-ml">
            <div id="woo-ml-wizard-step-two">
                <div class="header-title-ml">
                    <h1>Consumer details</h1>
                    <p>The Consumer key and Consumer secret are required for e-commerce automations to work in MailerLite Classic.</p>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml mt-0-ml">Consumer key
                    </label>
                    <label class="input-mailerlite mb-2-ml mt-0-ml">
                        <input ref="consumerKeyRef" type="text" name="consumer_key" class="woo-ml-form-checkbox text-input flex-start-ml"
                               style="width: 100%;" v-model="consumerKey">
                    </label>
                    <label class="settings-label-small mt-0-ml mb-0-ml">Find out how to generate secret <a href="https://docs.woocommerce.com/document/woocommerce-rest-api/" target="_blank">here.</a></label>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml mt-0-ml">Consumer secret
                    </label>
                    <label class="input-mailerlite mb-2-ml mt-0-ml">
                        <input ref="consumerSecretRef" type="text" name="consumer_secret" class="woo-ml-form-checkbox text-input flex-start-ml"
                               style="width: 100%;" v-model="consumerSecret">
                    </label>
                    <label class="settings-label-small mt-0-ml">Find out how to generate secret <a href="https://docs.woocommerce.com/document/woocommerce-rest-api/" target="_blank">here.</a></label>
                </div>
            </div>
        </div>
        
        <div class="form-group-ml vertical">
            <label for="sync_fields" class="settings-label flex-ml align-items-center-ml mb-3-ml">
                Synced fields
                <div class="tooltip-ml">
                    <span class="tooltiptext-ml">Select which fields you would like to sync. Please note that Email and Name fields are mandatory.</span>
                </div>
            </label>
            <label class="input-mailerlite">
                <sync-fields 
                    id="sync_fields" 
                    multiple="multiple" 
                    v-model="selectedSyncFields" 
                    placeholder="Click to select fields you want to sync" 
                    class="wc-enhanced-select" style="width: 100%;"
                    :options="syncFields"
                    :model-value="selectedSyncFields"
                />
            </label>
        </div>

        <div class="settings-block" style="display: flex; justify-content: space-between; padding-top: 2rem;">
            <button @click="startImport" :class="{ 'woo-ml-button-loading': isLoading }" :disabled="isLoading" id="startImport" type="button" class="btn-primary-ml"><span class="woo-ml-button-text">Next</span></button>
        </div>
    </div>
</div>
`;

const Wizard = {
    components: {
        CustomSelect,
        SyncFields
    },
    template,
    mixins: [ApiMixin],
    mounted() {
        const defaultValues = this.syncFields
            .filter((opt) => opt.default)
            .map((opt) => opt.value);
        this.selectedSyncFields = [...this.selectedSyncFields, ...defaultValues];
    },
    data() {
        return {
            wooMlApiKey: '',
            isLoading: false,
            mlPlatform: woo_mailerlite_admin_data.account.platform ?? 'rewrite',
            totalUntrackedResources: 0,
            accountName: woo_mailerlite_admin_data.account.accountName ?? '',
            consumerKey: '',
            consumerSecret: '',
            selectedSyncFields: [],
            currentStep: parseInt(woo_mailerlite_admin_data.currentStep) ?? 0,
            groups: [
                {
                    value: 1,
                    text: 'Group 1'
                },
                {
                    value: 2,
                    text: 'Group 2'
                },
                {
                    value: 3,
                    text: 'Group 3'
                },
            ],
            selectedGroup: null,
            syncFields: [
                {
                    value: 'name',
                    text: 'Name',
                    default: true
                },
                {
                    value: 'email',
                    text: 'Email',
                    default: true
                },
                {
                    value: 'company',
                    text: 'Company'
                },
                {
                    value: 'city',
                    text: 'City'
                },
                {
                    value: 'zip',
                    text: 'ZIP'
                },
                {
                    value: 'state',
                    text: 'State'
                },
                {
                    value: 'country',
                    text: 'Country'
                },
                {
                    value: 'phone',
                    text: 'Phone'
                },
            ],
        }
    },
    computed: {
        accountInfo() {
            return this.accountName ? `Account: ${this.accountName}` : '';
        },
    },
    methods: {

        backToApi() {
            this.currentStep = 0
            eventBus.emit('wizard-step-event', 0)
        },
        createGroup() {
            eventBus.emit('show-create-group-modal', this.selectedGroup)
        },
        async connectAccount() {
            const apiKey = validate(this.$refs.wooMlApiKey, ['required'], 'API key is required');
            if (!apiKey) {
                return
            }
            this.isLoading = true

            try {
                const data = await this.validateKey(this.wooMlApiKey);

                this.isLoading = false

                if (data.success) {
                    this.currentStep = 1;
                    this.mlPlatform = data.data.mlPlatform
                    this.accountName = data.data.name

                    eventBus.emit('wizard-step-event', this.currentStep)
                } else {
                    eventBus.emit('alert-event', { error: data.data.message });
                }
            } catch (err) {
                this.isLoading = false
                eventBus.emit('alert-event', { error: err.toString() });
            }
        },
        keyExists(key) {
            return this.syncFields.includes(key);
        },
        async startImport() {
            try {
                this.isLoading = true
                const group = validate(this.$refs.wooMlSubGroupComponent.$refs.wooMlSubGroup, ['required'], 'Please select a group!')
                let consumerKey = true;
                let consumerSecret = true;
                if (this.$refs.consumerKeyRef) {
                    consumerKey = validate(this.$refs.consumerKeyRef, ['required','startsWith:ck_'], {'required' : 'Please enter a consumer key!' , 'startsWith' : 'Please enter a valid consumer key'})
                    consumerSecret = validate(this.$refs.consumerSecretRef, ['required', 'startsWith:cs_'], {'required' : 'Please enter a consumer secret!' , 'startsWith' : 'Please enter a valid consumer secret'})
                }

                if (!group || !consumerKey || !consumerSecret) {
                    this.isLoading = false
                    return
                }
                const response = await this.setupShop(this.selectedGroup, JSON.stringify(this.selectedSyncFields), this.consumerKey, this.consumerSecret);
                if (response.success) {
                    eventBus.emit('group-saved', {'group': {'name' : response.data.group.name, 'id': response.data.group.id}, 'fields': this.selectedSyncFields})
                    eventBus.emit('wizard-step-event', 2)
                }
                this.isLoading = false
            } catch (err) {
                window.scrollTo(0,0);
                this.isLoading = false
                eventBus.emit('alert-event', { error: err.toString() });
            }


        },
        openDebugLog() {
            eventBus.emit('show-debug-modal');
        }
    },
};

export default Wizard;
