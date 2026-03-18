import eventBus from '../eventBus.js';

const template = `
    <div v-if="'error' in alerts" class="woo-ml-alert">
        <span @click="clearAlerts" class="woo-ml-closebtn">&times;</span>
        {{ alerts['error'] }}
    </div>

    <div v-if="'success' in alerts" class="woo-ml-alert-success">
        <span @click="clearAlerts" class="woo-ml-closebtn-success">&times;</span>
        {{ alerts['success'] }}
    </div>
`;

const Alerts = {
    props: {
        alerts: {
            type: Array,
            default: [],
        },
    },
    template,
    methods: {
        clearAlerts() {
            eventBus.emit('alert-event', {});
        }
    }
};

export default Alerts;
