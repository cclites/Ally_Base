<template>
    <div class="d-flex flex-column">
        <object class="w-100" style="height: 40rem;" :data="`/business/clients/referral-service-agreement/${referralAgreementData.id}/agreement-pdf`"></object>
        <div class="mt-3">
            <b-btn variant="secondary" @click="nextStep">Next Step</b-btn>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['referralAgreementData'],

        data() {
            return {
                form: new Form({
                    onboarding_step: 6
                })
            }
        },

        methods: {
            async nextStep() {
                await this.form.put(`/business/clients/onboarding/${this.onboardingData.id}`);
                this.$emit('next', {data: { onboarding: this.onboardingData }});
            }
        }
    }
</script>
