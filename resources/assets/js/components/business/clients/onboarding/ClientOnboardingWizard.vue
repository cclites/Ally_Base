<template>
    <b-card title="Onboarding">
        <onboarding-step-one v-if="step === 1 || !step" :client-data="clientData" :activities="activities" @next="nextStep($event, 2)"></onboarding-step-one>
        <onboarding-step-two v-if="step === 2" :client-data="clientData" :onboarding="onboarding" @previous="previousStep" @next="nextStep($event, 3)"></onboarding-step-two>
        <onboarding-step-three v-if="step === 3" :client-data="clientData" :onboarding-data="onboarding" @previous="previousStep" @next="nextStep($event, 4)"></onboarding-step-three>
        <onboarding-step-four v-if="step === 4" :client-data="clientData" :onboarding-data="onboarding" @previous="previousStep" @next="nextStep($event, 5)"></onboarding-step-four>
    </b-card>
</template>

<script>
    import OnboardingStepOne from './OnboardingStepOne'
    import OnboardingStepTwo from './OnboardingStepTwo'
    import OnboardingStepThree from './OnboardingStepThree'
    import OnboardingStepFour from './OnboardingStepFour'

    export default {
        props: ['clientData', 'activities', 'onboardingData'],

        mixins: [],

        components: {
            OnboardingStepOne,
            OnboardingStepTwo,
            OnboardingStepThree,
            OnboardingStepFour
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
