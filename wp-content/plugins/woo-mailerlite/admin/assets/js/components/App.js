import eventBus from './eventBus.js';
import Header from './Ui/Header.js'
import Alerts from './Ui/Alerts.js'
import Wizard from './Views/Wizard.js'
import Settings from './Views/Settings.js'
import Modals from './Modals/Modals.js'

const template = `
<div class="woo-ml-wizard">
    <Header></Header>
    <Alerts :alerts="alerts"></Alerts>
    <Wizard v-if="currentStep < 2"></Wizard>
    <Settings v-else :group="selectedGroup" :fields="selectedFields"></Settings>
</div>
<Modals></Modals>
`

const App = {
    template,
    components: {
        Header,
        Alerts,
        Wizard,
        Settings,
        Modals,
    },
    data() {
        return {
            alerts: [],
            currentStep: 0,
            selectedGroup: null,
            selectedFields: [],
            startSync: false,
            manualSync: false,
        }
    },
    methods: {
      async syncProcess() {
          const response = await fetch(woo_mailerlite_admin_data.ajaxUrl, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: new URLSearchParams({
                  action: 'woo_mailerlite_sync_handler',
                  nonce: woo_mailerlite_admin_data.woo_mailerlite_admin_nonce,
                  async: !this.manualSync
              }),
          });

          if ([200, 203].includes(response.status)) {
              eventBus.emit('sync-completed', true);
              clearInterval(this.interval);
          }
      }
    },
    mounted() {
        if (woo_mailerlite_admin_data.falseApi && woo_mailerlite_admin_data.currentStep > 0) {
            window.location.reload()
        }
        if (woo_mailerlite_admin_data.asyncSync || this.startSync || ((parseInt(woo_mailerlite_admin_data.currentStep) === 2) && woo_mailerlite_admin_data.sync.syncInProgress)) {
            this.syncProcess();
            if (!woo_mailerlite_admin_data.asyncSync) {
                this.interval = setInterval(() => {
                    woo_mailerlite_admin_data.sync.syncInProgress = true
                    this.syncProcess();
                }, 8000);
            }
        }

        eventBus.on('alert-event', (data) => {
            this.alerts = data
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        eventBus.on('start-sync', (data) => {
            this.manualSync = true;
            this.syncProcess();
            this.interval = setInterval(() => {
                woo_mailerlite_admin_data.sync.syncInProgress = true
                this.syncProcess();
            }, 8000);
        });

        eventBus.on('wizard-step-event', (step) => {
            if (this.currentStep > 0 && step === 0) {
                this.currentStep = 0
                return
            }
            this.alerts = []
            this.currentStep = step
        });
        eventBus.on('group-saved', (data) => {
            this.selectedGroup = data.group
            this.selectedFields = data.fields
        })
        eventBus.emit('wizard-step-event', parseInt(woo_mailerlite_admin_data.currentStep))
    },
    beforeDestroy() {
        clearInterval(this.interval);
    }
}

export default App
