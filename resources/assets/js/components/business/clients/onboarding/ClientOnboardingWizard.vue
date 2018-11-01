<template>
    <b-card title="Onboarding">
        <onboarding-step-one v-if="step === 1 || !step" :client-data="clientData" :activities="activities" @next="nextStep($event, 2)"></onboarding-step-one>
        <onboarding-step-two v-if="step === 2" :client-data="clientData" :onboarding="onboarding" @previous="previousStep"></onboarding-step-two>
    </b-card>
</template>

<script>
    import OnboardingStepOne from './OnboardingStepOne'
    import OnboardingStepTwo from './OnboardingStepTwo'

    export default {
        props: ['clientData', 'activities', 'onboardingData'],

        mixins: [],

        components: {
            OnboardingStepOne,
            OnboardingStepTwo
        },

        data() {
            return {
                step: (this.clientData.onboarding_step) ? this.clientData.onboarding_step : 1,
                onboarding: this.onboardingData
            }
        },

        created() {

        },

        mounted() {

        },

        computed: {

        },

        methods: {
            nextStep(event, step) {
                this.onboarding = event.data.onboarding;
                this.step = step;
            },

            previousStep() {
                this.step--;
            }
        }
    }
</script>

<style lang="scss">
</style>
