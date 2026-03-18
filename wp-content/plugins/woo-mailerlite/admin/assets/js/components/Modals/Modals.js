import eventBus from "../eventBus.js";
import ApiMixin from '../Api/ApiMixin.js';
import {validate} from "../Plugins/Validation.js";
import {loadStart, loadEnd} from "../Plugins/Loader.js";
const template = `
<!-- Create Group Modal -->
<div v-if="openCreateGroupModal" class="woo-ml-wizard-modal" id="wooMlWizardCreateGroupModal" role="dialog">
    <div class="woo-ml-wizard-modal-parent">
        <div class="woo-ml-wizard-modal-container">
            <div class="woo-ml-wizard-modal-content">
                <div class="woo-ml-wizard-modal-header">
                    <h2>Create new group</h2>
                    <span @click="openCreateGroupModal = false" class="close"></span>
                </div>
                <div class="woo-ml-wizard-modal-body">
                    <div class="create-group-input">
                        <input ref="wooMlCreateGroup" type="text" name="createGroup" placeholder="Enter group name" v-model="createGroupName" class="">
                    </div>
                    <div class="modal-button-ml">
                        <button @click="openCreateGroupModal = false" type="button" class="btn-secondary-ml woo-ml-close" style="margin-right: 12px;">Close</button>
                        <button @click="createGroup" ref="createGroup" type="button" class="btn-primary-ml"><span class="woo-ml-button-text">Create group</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create Group Modal -->

<!-- Sync Resources Modal -->
<div v-if="openSyncModal" class="woo-ml-wizard-modal" id="wooMlSyncModal" role="dialog">
    <div class="woo-ml-wizard-modal-parent">
        <div class="woo-ml-wizard-modal-container">
            <div class="woo-ml-wizard-modal-content woo-ml-text-center">
                <div class="woo-ml-wizard-modal-header">
                    <h2>Synchronizing subscribers</h2>
                </div>
                <p style="line-height: 1.75rem; font-size: 14px; text-align: left; margin-bottom: 24px;">
                    This initial import will send product details, product categories, and customer data to MailerLite. It will also enable real-time synchronization on future updates. Please note that only subscribers who opt-in to receive email marketing will be added.
                </p>
                <div class="progress-box">
                    <div class="progress">
                        <div id="wooMlWizardProgress" :style="{ width: progressPercentage + '%' }"></div>
                    </div>
                    <div style=" text-align: center; "><span id="progressPercentage">{{ progressPercentage }}%</span></div>
                </div>
                <h4>Total resources to sync: {{ remainingUntrackedResources }}</h4>
                <div class="woo-ml-wizard-modal-body">
                    <div class="modal-button-ml">
                        <button @click="openSyncModal = false" type="button" class="btn-secondary-ml">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Sync Resources Modal -->

<!-- Cancel Sync Resources Modal -->
<div v-if="openCancelSyncModal" class="woo-ml-wizard-modal" id="wooMlCancelSyncModal" role="dialog">
    <div class="woo-ml-wizard-modal-parent">
        <div class="woo-ml-wizard-modal-container">
            <div class="woo-ml-wizard-modal-content woo-ml-text-center">
                <div class="woo-ml-wizard-modal-header">
                    <h2>Terminate import</h2>
                </div>
                <p style="line-height: 1.75rem; font-size: 14px; text-align: left; margin-bottom: 0;">
                    Are you sure you want to stop the import process? The import will be reset, and the progress made so far will be lost. Click 'Terminate' if you wish to continue.
                </p>
                <div class="woo-ml-wizard-modal-body">
                    <div class="modal-button-ml">
                        <button @click="openCancelSyncModal = false" type="button" class="btn-secondary-ml" style="margin-right: 12px;">Close</button>
                        <button id="confirmCancelSync" type="button" class="btn-danger-ml" style="margin-right: 12px;"><span class="woo-ml-button-text">Terminate</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Cancel Sync Resources Modal -->

<!-- Reset Integration Modal -->
<div v-if="openResetModal" class="woo-ml-wizard-modal" id="wooMlResetIntegrationModal" role="dialog">
    <div class="woo-ml-wizard-modal-parent">
        <div class="woo-ml-wizard-modal-container">
            <div class="woo-ml-wizard-modal-content woo-ml-text-center">
                <div class="woo-ml-wizard-modal-header">
                    <h2>Reset integration</h2>
                </div>
                <p style="line-height: 1.75rem; font-size: 14px; text-align: left; margin-bottom: 0;">
                    Are you sure you want to reset the integration? Click 'Reset' if you wish to continue.
                </p>
                <div class="woo-ml-wizard-modal-body">
                    <div class="modal-button-ml">
                        <form id="resetIntegration" method="post">
                            <!-- wp_nonce_field('ml_reset_integration'); -->
                            <input type="hidden" name="resetIntegration"/>
                            <button @click="openResetModal = false" type="button" class="btn-secondary-ml" style="margin-right: 12px;">Close</button>
                            <button @click.prevent="resetIntegration" ref="resetIntegration" id="resetIntegrationBtn" class="btn-danger-ml" style="margin-right: 12px;"><span class="woo-ml-button-text">Reset</span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Reset Integration Modal -->

<!-- Reset Integration Modal -->
<div v-if="openDebugLogModal" class="woo-ml-wizard-modal" id="openDebugLogModal" role="dialog">
    <div class="woo-ml-wizard-modal-parent">
        <div class="woo-ml-wizard-modal-container">
            <div class="woo-ml-wizard-modal-content woo-ml-text-center">
                <div class="woo-ml-wizard-modal-header">
                    <h2>Debug logs</h2>
                    <p style="line-height: 1.75rem; font-size: 14px; text-align: left; margin-bottom: 0;">
                        In case you have problems with our application's performance, please click 'Copy to clipboard' and share the logs with our support team using <a href="https://www.mailerlite.com/contact-us?category=integrations-api" target="_blank" class="settings-label-medium">this contact form</a>.
                    </p>
                </div>
                <div class="woo-ml-wizard-modal-body">
                <input type="text" v-model="searchLogs" @input="highlightSearch" placeholder="Search logs..." />
                    <pre class="woo-mailerlite-log-container" ref="debugLogLines">
                        <div v-for="(line, index) in filteredLogs" :key="index" v-html="highlightSearch(line)"></div>
                    </pre>
                    <div class="modal-button-ml">
                        <button @click="openDebugLogModal = false" type="button" class="btn-secondary-ml" style="margin-right: 12px;">Close</button>
                        <button @click="copyDebugLogToClipboard" type="button" class="btn-primary-ml no-icon-tooltip-ml" style="margin-right: 12px;"><span class="no-icon-tooltip-ml-text">Copied</span><span class="woo-ml-button-text">{{this.copyMessage !== '' ? this.copyMessage : 'Copy to clipboard'}}</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Reset Integration Modal -->
`;

const Modals = {
    template,
    mixins: [ApiMixin],
    data() {
        return {
            totalTrackedResources: 0,
            totalUntrackedResources: 100,
            openCreateGroupModal: false,
            openSyncModal: false,
            openCancelSyncModal: false,
            openResetModal: false,
            openDebugLogModal: false,
            progressPercentage: 0,
            intervalId: null,
            createGroupName: "",
            filteredLogs: [],
            searchLogs: "",
            copyMessage: "",
            resetSyncButtonRef: null,
        }
    },
    computed: {
        remainingUntrackedResources() {
            return this.totalUntrackedResources - this.totalTrackedResources;
        },
    },
    mounted() {
        eventBus.on('show-create-group-modal', (data) => {
            this.openCreateGroupModal = true
        });
        eventBus.on('show-sync-modal', (data) => {
            this.openSyncModal = true

            // TODO - send sync to background and continue setup
            this.simProgress()
        });
        eventBus.on('show-cancel-sync-modal', (data) => {
            this.openCancelSyncModal = true
        });
        eventBus.on('show-reset-modal', (ref) => {
            this.openResetModal = true
        });
        eventBus.on('show-debug-modal', async (ref) => {
            try {
                loadStart(ref);
                const data = await this.getDebugLog()
                if (data.success) {
                    this.debugLogs = data.log ? data.log.split('\n') : "No Logs Found"
                    this.filteredLogs = [...this.debugLogs];
                    this.openDebugLogModal = true
                } else {
                    eventBus.emit('alert-event', { error: 'An error occurred' });
                }
                loadEnd(ref);
            } catch (err) {
                this.isLoading = false
                eventBus.emit('alert-event', { error: 'An error occurred' });
                loadEnd(ref);
            }
        });
    },
    methods: {
        simProgress() {
            if (this.intervalId) return;
            this.progressPercentage = 0;
            this.totalTrackedResources = 0;

            this.intervalId = setInterval(() => {
                if (this.totalTrackedResources < this.totalUntrackedResources) {
                    this.totalTrackedResources += 10;

                    this.progressPercentage = Math.round(
                        (this.totalTrackedResources / this.totalUntrackedResources) * 100
                    );
                } else {
                    clearInterval(this.intervalId); // stop once it reaches 100
                    this.intervalId = null;

                    // move to settings and close modal
                    eventBus.emit('wizard-step-event', 2)
                    this.openSyncModal = false
                }
            }, 500);
        },
        async createGroup() {
            loadStart(this.$refs.createGroup);
            const groupNameValid = validate(this.$refs.wooMlCreateGroup, ['required', 'minLength:3'], {
                required: 'Group name is required.',
            });
            if (groupNameValid) {
                const response = await this.createGroupApi(this.createGroupName)
                if (response.success) {
                    eventBus.emit('trigger-group-created', response.data)
                }
            }
            loadEnd(this.$refs.createGroup);
            this.openCreateGroupModal = false
        },
        resetIntegration() {
            eventBus.emit('reset-integration');
        },
        filterLogs() {
            const term = this.searchLogs.toLowerCase();
            this.filteredLogs = this.debugLogs.filter(line =>
                line.toLowerCase().includes(term)
            );
        },
        // Highlight search term in logs
        highlightSearch(line) {
            if (!this.searchLogs) return line;
            if (typeof line !== 'string') return line;
            const term = this.searchLogs.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&'); // Escape special characters
            const regex = new RegExp(`(${term})`, 'gi');
            return line.replace(regex, '<span class="woo-mailerlite-log-highlight">$1</span>');
        },
        copyDebugLogToClipboard() {
            if (navigator.clipboard && window.isSecureContext) {
                // Modern method (HTTPS only)
                navigator.clipboard.writeText(this.filteredLogs.join('\n')).then(() => {
                    this.copyMessage = 'Copied!';
                    setTimeout(() => {
                        this.copyMessage = "";
                    }, 2000);
                }).catch(err => {
                });
            } else {
                const textarea = document.createElement("textarea");
                textarea.value = this.filteredLogs.join('\n');
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand("copy");
                document.body.removeChild(textarea);
                this.copyMessage = 'Copied!';
                setTimeout(() => {
                    this.copyMessage = "";
                }, 2000);
            }
        },
    }
};

export default Modals;
