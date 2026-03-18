import eventBus from '../eventBus.js';

const template = `
<div class="woo-ml-wizard-breadcrumbs">
    <ul class="woo-ml-wizard-ul">
        <li :class="{ 'woo-ml-wizard-text-medium': currentStep === 0 }">API key</li>
        <span class="chevron-ml"></span>
        <li :class="{ 'woo-ml-wizard-text-medium': currentStep === 1 }">Groups and subscribers</li>
        <span class="chevron-ml"></span>
        <li :class="{ 'woo-ml-wizard-text-medium': currentStep === 2 }">Additional settings</li>
    </ul>
</div>
`;

const WizardBreadCrumb = {
    template,
    data() {
        return {
            currentStep: 0,
        };
    },
    mounted() {
        eventBus.on('wizard-step-event', (step) => {
            this.currentStep = step
        });
    },
};

export default WizardBreadCrumb;
