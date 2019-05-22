<template>
    <b-card
        header="Office Locations"
        header-text-variant="white"
        header-bg-variant="info"
    >

        <b-form-checkbox-group stacked v-model="form.businesses" name="businesses" class="mb-4">
            <b-form-checkbox v-for="business in businesses" :key="business.id" :value="business.id">{{ business.name }}</b-form-checkbox>
        </b-form-checkbox-group>

        <b-btn variant="success" :disabled="form.busy" @click="submit">Save Changes</b-btn>
    </b-card>
</template>

<script>
    import { mapState } from 'vuex';

    export default {
        props: {
            caregiver: {
                type: Object,
                default: () => { return {}; },
            },
        },

        data() {
            return {
                form: new Form({
                    businesses: [],
                }),
            };
        },

        mounted() {
            this.form.businesses = this.caregiver.businesses.map(x => { return x.id });
        },
        
        computed: {
            ...mapState({
                businesses: state => state.business.businesses
            }),
        },
        
        methods: {
            submit() {
                this.form.patch(`/business/caregivers/${this.caregiver.id}/office-locations`)
                    .then( ({ data }) => {

                    })
                    .catch(() => {});
            },
        },
    }
</script>

<style lang="scss">
</style>
