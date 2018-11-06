<template>
    <div class="d-flex flex-column">
        <object class="w-100" style="height: 40rem;" :data="`/business/clients/onboarding/${onboardingData.id}/intake-pdf`"></object>
        <div class="mt-3">
            <b-btn variant="secondary" @click="nextStep">Next Step</b-btn>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['onboardingData'],

        methods: {
            nextStep() {
                console.log(this.onboardingData);
                this.$emit('next', {data: { onboarding: this.onboardingData }});
            }
        }
    }
</script>
