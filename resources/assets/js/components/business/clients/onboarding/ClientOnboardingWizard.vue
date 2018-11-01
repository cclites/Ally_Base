<template>
    <b-card title="Onboarding">
        <onboarding-step-one v-if="step === 1" :client-data="clientData" :activities="activities"></onboarding-step-one>
    </b-card>
</template>

<script>
    import OnboardingStepOne from './OnboardingStepOne'

    export default {
        props: ['clientData', 'activities'],

        mixins: [],

        components: {
            OnboardingStepOne
        },

        data() {
            return {
                step: 1
            }
        },

        created() {

        },

        mounted() {
        },

        computed: {

        },

        methods: {

        }
    }
</script>

<style lang="scss">
</style>
