<template>
    <div>
        <object class="w-100" style="height: 40rem;" :data="`/business/clients/onboarding/${onboardingData.id}/intake-pdf`"></object>
    </div>
</template>

<script>
    export default {
        props: ['onboardingData'],

        mixins: [],

        components: {},

        data() {
            return {}
        },

        created() {
        },

        mounted() {
        },

        computed: {},

        methods: {

        }
    }
</script>

<style lang="scss">
</style>
