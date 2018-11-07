<template>
    <div class="d-flex flex-column">
        <object class="w-100" style="height: 40rem;" :data="`/business/clients/referral-service-agreement/${referralAgreementData.id}/agreement-pdf`"></object>
        <div class="mt-3">
            <b-btn class="mr-2" variant="secondary" @click="nextStep" :disabled="state === 'updating'">Next Step</b-btn>
            <i class="fa fa-spin fa-spinner" v-show="state === 'updating'"></i>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['onboardingData'],

        data() {
            return {
                referralAgreementData: this.onboardingData.client.referral_service_agreement,
                form: new Form({
                    onboarding_step: 6
                }),
                state: ''
            }
        },

        methods: {
            async nextStep() {
                this.state = 'updating';
                let response = await this.form.put(`/business/clients/onboarding/${this.onboardingData.id}`);
                this.state = '';
                window.location = response.data.data.url;
            }
        }
    }
</script>
