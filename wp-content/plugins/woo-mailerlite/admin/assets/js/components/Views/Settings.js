import CustomSelect from '../Forms/CustomSelect.js';
import SyncFields from '../Forms/SyncFields.js';
import eventBus from '../eventBus.js';
import ApiMixin from '../Api/ApiMixin.js';
import {loadStart, loadEnd} from "../Plugins/Loader.js";

const template = `
<div v-if="syncInProgress || asyncSyncInProgress" class="woo-ml-sync-loading-container">
    <strong>Sync in progress...</strong>
    <p>This will continue as long as you stay on this page.
       You can always come back, and it will start again.</p>
    <div class="woo-ml-sync-progress-bar"></div>
</div>
<form method="post" id="updateSettingsForm">
        <div class="settings-block">
            <div class="settings-block-fixed">

                <h2 class="settings-block-header">Synchronization settings</h2>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label mb-3-ml">Subscriber group</label>
                    <label class="input-mailerlite mb-2-ml" style="display: flex;">
                        <custom-select 
                            id="wooMlSubGroup" 
                            class="wc-enhanced-select" 
                            name="subscriber-group" 
                            :options="groups"
                            v-model="selectedGroup"
                            modelValue="selectedGroup"
                            :selectedGroup="selectedGroup"
                            placeholder="Select group"
                        ></custom-select>
                        <button @click="createGroup" id="createGroupModal" type="button" class="btn-secondary-ml" style="margin-left: 0.5rem; white-space: nowrap;">Create group</button>
                    </label>
                    <label class="settings-label-small">Subscribers from WooCommerce will join this MailerLite
                        subscriber group. </label>
                </div>

                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml">Ignore products list
                        <div class="tooltip-ml">
                            <span class="tooltiptext-ml">This field lists products that are configured to be ignored in MailerLite e-commerce automation. To add or remove a product from this list, go to the WordPress product list and click "Quick Edit" on the relevant item.</span>
                        </div>
                    </label>
                    <label v-if="ignoredProducts.length !== 0" class="input-mailerlite" style="cursor: default;">
                        <div multiple class="wc-enhanced-select" name="ignore_product_list"
                             style="min-height: 26px; padding-left: 8px; display: flex; flex-direction: row; overflow: hidden; flex-wrap: wrap; padding: 4px; padding-top: 0; border: 1px solid #d1d5db; border-radius: 0.25rem;">
                            <option v-for="(item, index) in ignoredProducts" :key="index" style="background-color: #e5e7eb; padding: 2px 5px; border-radius: 2px; font-size: 13px; margin-right: 4px; margin-top: 4px;">{{ item }}</option>
                        </div>
                    </label>
                    <a v-else :href="productsUrl">
                        <button type="button" class="btn btn-secondary-ml flex-start-ml">Go to your products</button>
                    </a>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml">Add language
                        field
                        <div class="tooltip-ml">
                            <span class="tooltiptext-ml">Collect subscriber languages in a hidden field stored in MailerLite.</span>
                        </div>
                    </label>
                    <div class="checkbox-text-ml">
                        <input type="checkbox"
                               class="woo-ml-form-checkbox" v-model="settings.languageField"
                               name="additional_sub_fields"
                               value="yes"
                               id="language_field_checkbox"
                        />
                        <label for="language_field_checkbox" class="settings-label-medium">Collect subscriber language data.</label>
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
                        data-placeholder="Click to select fields you want to sync" 
                        class="wc-enhanced-select" 
                        style="width: 100%;"
                        :options="syncFields"
                        v-model="selectedSyncFields"
                        />
                    </label>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml">Synchronize
                        store
                        <div class="tooltip-ml">
                            <span class="tooltiptext-ml">Synchronize categories, products and customer data that hasn't been submitted to MailerLite.</span>
                        </div>
                    </label>
                    <p v-if="!accountConnected" class="description">
                        Plugin not connected to MailerLite yet.
                    </p>
                    <button @click.prevent="resetSync" v-if="!totalUntrackedResources && !syncInProgress" type="button" class="btn btn-secondary-ml flex-start-ml"
                            data-woo-ml-reset-resources-sync="true" ref="resetSyncBtn"><span class="woo-ml-button-text">Reset synchronized resources</span>
                    </button>
                    <button @click.prevent="startSync" v-if="totalUntrackedResources && !syncInProgress" type="button" class="btn btn-secondary-ml flex-start-ml"
                            data-woo-ml-reset-resources-sync="true" ref="startSync"><span class="woo-ml-button-text">Synchronize {{ totalUntrackedResources }} untracked resources</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-block">
            <div class="settings-block-fixed">
                <h2 class="settings-block-header">Checkout settings</h2>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label mb-3-ml">Subscribe on checkout</label>
                    <div class="checkbox-text-ml">
                        <input type="checkbox"
                               class="woo-ml-form-checkbox" v-model="settings.subscribeOnCheckout"
                               name="checkout"
                               value="yes"
                               id="subscribe_checkout_checkbox"
                        />
                        <label for="subscribe_checkout_checkbox" class="settings-label-medium">Enable list subscription via checkout page.</label>
                    </div>
                </div>
                <div class="checkout-settings-group" :style="{ display: !settings.subscribeOnCheckout ? 'none' : 'block' }">
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label mb-3-ml"> Resubscribe</label>
                        <div class="checkbox-text-ml">
                            <input type="checkbox"
                                   class="woo-ml-form-checkbox" v-model="settings.resubscribe"
                                   name="resubscribe"
                                   value="yes"
                                   id="resubscribe_checkbox"
                            />
                            <label for="resubscribe_checkbox" class="settings-label-medium"> Allow unsubscribers to rejoin the email list if they
                                resubscribe via the checkout page.</label>
                        </div>
                    </div>
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label mb-3-ml">Subscribe checkbox position</label>
                        <label class="input-mailerlite">
                            <select 
                              class="wc-enhanced-select" 
                              name="checkout_position" 
                              :disabled="!settings.subscribeOnCheckout"
                              v-model="settings.selectedCheckoutPosition"
                            >
                              <option 
                                v-for="(checkoutPosition, key) in checkoutPositions" 
                                :key="key" 
                                :value="key"
                              >
                                {{ checkoutPosition }}
                              </option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label mb-3-ml">Pre-select subscribe checkbox</label>
                        <div class="checkbox-text-ml">
                            <input type="checkbox"
                                   class="woo-ml-form-checkbox" v-model="settings.checkoutPreselect"
                                   name="checkout_preselect"
                                   value="yes"
                                   id="preselect_subscribe_checkbox"
                                   :disabled="!settings.subscribeOnCheckout"
                            />
                            <label for="preselect_subscribe_checkbox" class="settings-label-medium">Pre-select the signup checkbox by default.</label>
                        </div>
                    </div>
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label mb-3-ml">Hide subscribe checkbox</label>
                        <div class="checkbox-text-ml">
                            <input type="checkbox"
                                   class="woo-ml-form-checkbox" v-model="settings.checkoutHidden"
                                   name="checkout_hide"
                                   value="yes"
                                   id="hide_subscriber_checkbox"
                                   :disabled="!settings.subscribeOnCheckout"
                            />
                            <label for="hide_subscriber_checkbox" class="settings-label-medium">Check to hide the checkbox. All customers will be
                                subscribed automatically.</label>
                        </div>
                    </div>
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label mb-3-ml">Subscribe checkbox label</label>
                        <label class="input-mailerlite">
                            <input type="text"
                                   class="woo-ml-form-checkbox text-input flex-start-ml mb-3-ml"
                                   v-model="settings.checkoutLabel"
                                   name="checkout_label"
                                   placeholder="I.e. I want to receive your newsletter."
                                   :disabled="!settings.subscribeOnCheckout"
                            />
                        </label>
                        <label class="settings-label-small">This text will be displayed next to the signup
                            checkbox. </label>
                    </div>
                    <div class="form-group-ml vertical">
                        <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml">Add subscribers after checkout
                            <div class="tooltip-ml">
                                <span class="tooltiptext-ml">Only customers that have completed the checkout process will be added to your MailerLite account as subscribers. Enabling this option disables abandoned cart functionality.</span>
                            </div>
                        </label>
                        <div class="checkbox-text-ml">
                            <input type="checkbox"
                                   class="woo-ml-form-checkbox" v-model="settings.syncAfterCheckout"
                                   name="disable_checkout_sync"
                                   value="yes"
                                   id="synchronize_after_checkout_checkbox"
                                   :disabled="!settings.subscribeOnCheckout"
                            />
                            <label for="synchronize_after_checkout_checkbox" class="settings-label-medium">Add subscribers to your MailerLite account after the checkout process has been completed.</label>
                        </div>
                        <div class="woo-ml-alert-warning-small" :style="{ display: !settings.subscribeOnCheckout ? 'flex' : 'none' }"> align-items: center;">
                            <svg width="12" height="12" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10,0 C15.5228475,0 20,4.4771525 20,10 C20,15.5228475 15.5228475,20 10,20 C4.4771525,20 0,15.5228475 0,10 C0,4.4771525 4.4771525,0 10,0 Z M10,2 C5.581722,2 2,5.581722 2,10 C2,14.418278 5.581722,18 10,18 C14.418278,18 18,14.418278 18,10 C18,5.581722 14.418278,2 10,2 Z M10,12 C10.5522847,12 11,12.4477153 11,13 C11,13.5522847 10.5522847,14 10,14 C9.44771525,14 9,13.5522847 9,13 C9,12.4477153 9.44771525,12 10,12 Z M10,5 C10.5522847,5 11,5.44771525 11,6 L11,10 C11,10.5522847 10.5522847,11 10,11 C9.44771525,11 9,10.5522847 9,10 L9,6 C9,5.44771525 9.44771525,5 10,5 Z" fill-rule="nonzero"></path></svg>
                            Enabling this option disables abandoned cart functionality.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-block">
            <div class="settings-block-fixed">
                <h2 class="settings-block-header">General settings</h2>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label flex-ml align-items-center-ml mb-3-ml">Double opt-in
                        <div class="tooltip-ml">
                            <span class="tooltiptext-ml">Changing this setting will automatically update your double opt-in setting for your MailerLite account.</span>
                        </div>
                    </label>
                    <div class="checkbox-text-ml">
                        <input type="checkbox"
                               class="woo-ml-form-checkbox" v-model="settings.doubleOptIn"
                               name="double_optin"
                               value="yes"
                               id="double_optin_checkbox"
                        />
                        <label for="double_optin_checkbox" class="settings-label-medium">Check to enforce email confirmation before being added
                            to your list.</label>
                    </div>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label mb-3-ml"> MailerLite pop-ups</label>
                    <div class="checkbox-text-ml">
                        <input type="checkbox"
                               class="woo-ml-form-checkbox" v-model="settings.popUps"
                               name="popups"
                               value="yes"
                               id="mailerlite_popups_checkbox"
                        />
                        <label for="mailerlite_popups_checkbox" class="settings-label-medium">Enable MailerLite pop-up forms. <a href="https://www.mailerlite.com/features/popups" target="_blank">Learn more.</a></label>
                    </div>
                </div>
                <div class="form-group-ml vertical">
                    <label for="wooMlSubGroup" class="settings-label mb-3-ml">Auto updates</label>
                    <div class="checkbox-text-ml">
                        <input type="checkbox"
                               class="woo-ml-form-checkbox" v-model="settings.autoUpdatePlugin"
                               name="auto_update_plugin"
                               value="yes"
                               id="autoupdate_plugin_checkbox"
                        />
                        <label for="autoupdate_plugin_checkbox" class="settings-label-medium">Receive automatic plugin updates.</label>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
            <button @click.prevent="updateSettings" :class="{ 'woo-ml-button-loading': isLoading }" :disabled="isLoading" type="submit" class="btn-primary-ml" style="margin-top: 2rem;" id="updateSettingsBtn"><span class="woo-ml-button-text">Save changes</span></button>
        </div>
     <div class="settings-block">
    <div class="woo-ml-wizard card-between">
        <div class="flex-start-ml">
            <h2 class="settings-block-header" style="margin-top:0; margin-bottom: 8px;">Debug logs</h2>
            <label class="settings-label-medium">This is an advanced troubleshooting method which gives a deeper insight and helps our support team to identify problems.</label>
            <div class="mt-6-ml">
                <button type="button" @click.prevent="openDebugLog" ref="openDebugLog" class="btn-secondary-ml mr-2-ml"><span class="woo-ml-button-text">Open debug logs</span></button>
                <button v-if="platform == 1" type="button" id="enableDebugMode" ref="enableDebugMode" @click.prevent="enableDebugMode" :class="debugMode ? 'btn-danger-ml' : 'btn-primary-ml'"><span class="woo-ml-button-text">{{debugMode ? 'Disable debug mode' : 'Enable debug mode'}}</span></button>
            </div>
        </div>


    </div>
    <div class="woo-ml-wizard card-between">
        <div>
            <h2 class="settings-block-header" style="margin-top:0; margin-bottom: 8px;">Reset integration</h2>
            <label class="settings-label-medium">Once you click on the "Reset integration" button this action will reset
                all integration configurations and the process can not be reverted.</label>
        </div>
        <button type="button" ref="resetIntegration" @click.prevent="resetIntegration" class="btn-danger-ml"><span class="woo-ml-button-text">Reset integration</span></button>
    </div>
    </div>
    </form>
`;

const Settings = {
    components: {
        CustomSelect,
        SyncFields
    },
    template,
    mixins: [ApiMixin],
    props: {
      group: {
            type: Object,
            required: false
        },
        fields: {
            type: Array,
            required: false
        },
    },
    data() {
      return {
          ignoredProducts: woo_mailerlite_admin_data.ignoredProducts,
          productsUrl: woo_mailerlite_admin_data.productsUrl,
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
          selectedGroup: woo_mailerlite_admin_data.selectedGroup ?? null,
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
          accountConnected: true,
          totalTrackedResources: 0,
          totalUntrackedResources: woo_mailerlite_admin_data.sync.totalUntrackedResources ?? 0,
          syncInProgress: woo_mailerlite_admin_data.sync.syncInProgress,
          lastCustomerSync: 1,
          newMLSync: 0,
          settings: {
              languageField: false,
              subscribeOnCheckout: false,
              resubscribe: false,
              selectedCheckoutPosition: woo_mailerlite_admin_data.selectedCheckoutPosition ?? 'checkout_billing',
              checkoutPreselect: false,
              checkoutHidden: false,
              syncAfterCheckout: false,
              checkoutLabel: 'Yes, I want to receive your newsletter.',
              doubleOptIn: false,
              popUps: false,
              autoUpdatePlugin: false,
          },
          debugMode: woo_mailerlite_admin_data?.debugMode ?? false,
          platform: woo_mailerlite_admin_data?.account?.platform === 'rewrite' ? 1 : 2,
          checkoutPositions: {
              'checkout_billing'                :'After billing details',
              'checkout_billing_email'          : 'After billing email address',
              'checkout_shipping'               :'After shipping details',
              'checkout_after_customer_details' :'After customer details',
              'review_order_before_submit'      : 'Before submit button',
          },
          isLoading: false,
          selectedSyncFields: [],
          asyncSyncInProgress: woo_mailerlite_admin_data.asyncSync,
      }
    },
    mounted() {
        eventBus.on('sync-completed', (sync) => {
            this.syncInProgress = false
            this.totalUntrackedResources = 0
        });
        eventBus.on('reset-completed', (untracked) => {
            this.totalUntrackedResources = untracked
        });

        eventBus.on('reset-integration', (untracked) => {
                this.resetPlugin()
        });

    },
    created() {
        if (woo_mailerlite_admin_data && woo_mailerlite_admin_data.settings) {
            this.settings = {
                ...this.settings,
                ...woo_mailerlite_admin_data.settings,
            };
        }
        const defaultValues = this.syncFields
            .filter((opt) => opt.default)
            .map((opt) => opt.value);
        this.selectedSyncFields = [...new Set([...this.selectedSyncFields, ...defaultValues])];
        this.selectedSyncFields = [...new Set([...this.selectedSyncFields, ...woo_mailerlite_admin_data.syncFields ?? []])];

        if (this.fields) {
            this.selectedSyncFields = [...new Set([...this.selectedSyncFields, ...this.fields])];
        }
        if (this.group) {
            this.selectedGroup = this.group;
        }
    },
    computed: {
        syncMessage() {
            const count = this.totalUntrackedResources;
            return count === 1
                ? `Synchronize ${count} untracked resource`
                : `Synchronize ${count} untracked resources`;
        },
        syncFieldsComputed() {

            return this.fields.length ? this.fields : (woo_mailerlite_admin_data.syncFields ?? []) ?? [];
        },
    },
    methods: {
        async updateSettings() {
            this.isLoading = true

            try {
                this.settings.group = this.selectedGroup;
                this.syncFields = this.selectedSyncFields;

                this.settings.syncFields = this.syncFields
                const data = await this.saveSettings(this.settings);

                this.isLoading = false
                if (data.success) {
                    eventBus.emit('alert-event', { success: data.message ?? "" });
                } else {
                    eventBus.emit('alert-event', { error: data.message ?? "" });
                }
            } catch (err) {
                this.isLoading = false
                let error = 'An error occurred'
                if (err.match('401 - Unauthorized')) {
                    error = 'Please reset the plugin and setup again!'
                }
                eventBus.emit('alert-event', { error: error });
            }
        },
        async resetSync() {
            loadStart(this.$refs.resetSyncBtn)
            const resetData = await this.resetResources()
            loadEnd(this.$refs.resetSyncBtn)
            eventBus.emit('reset-completed', resetData.data?.totalUntrackedResources ?? 0 );
        },
        syncResources() {
            this.syncResources()
        },
        openDebugLog() {
            eventBus.emit('show-debug-modal', this.$refs.openDebugLog);
        },
        async resetIntegration() {
            eventBus.emit('show-reset-modal', this.$refs.resetIntegration);
        },
        async resetPlugin() {
            // reset-integration
            loadStart(this.$refs.resetIntegration)
            const response = await this.resetIntegrationSettings();
            if (response.success) {
                loadEnd(this.$refs.resetIntegration)
                window.location.reload()
                eventBus.emit('wizard-step-event', 0)
            }
        },
        createGroup() {
            eventBus.emit('show-create-group-modal', this.selectedGroup)
        },
        startSync() {
            this.syncInProgress = true
            eventBus.emit('start-sync')
        },
        async downgradePlugin() {
            await this.downgrade()
        },

        async enableDebugMode() {
            loadStart(this.$refs.enableDebugMode)
            const response = await this.debugModeEnable()
            this.debugMode = response
            loadEnd(this.$refs.enableDebugMode)
        }
    }
};

export default Settings;
