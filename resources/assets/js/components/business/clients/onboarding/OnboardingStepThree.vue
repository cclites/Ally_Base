<template>
    <div class="d-flex flex-column">
        <object class="w-100" style="height: 40rem;" :data="`/business/clients/onboarding/${onboardingData.id}/intake-pdf`"></object>
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
                form: new Form({
                    onboarding_step: 4
                }),
                state: ''
            }
        },

        methods: {
            async nextStep() {
                this.state = 'updating';
                await this.form.put(`/business/clients/onboarding/${this.onboardingData.id}`);
                this.$emit('next', {data: { onboarding: this.onboardingData }});
                this.state = '';
            }
        }
    }
</script>
