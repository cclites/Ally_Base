<template>
    <b-row>
        <b-col md="8">
            <b-form-group label="Referred By" label-for="referred_by">
                <b-form-select id="referral"
                               v-model="selectedValue"
                >
                    <option value="">No Referral Source</option>
                    <option v-for="source in filteredSources" :value="source.id" :key="source.id">{{ source.organization }}</option>
                </b-form-select>
            </b-form-group>
        </b-col>
        <b-col md="4" class="pad-top">
            <b-form-group label="Add">
                <b-btn  @click="showReferralModal = true">Add Referral Source</b-btn>
            </b-form-group>
        </b-col>

        <client-referral-modal @saved="newReferralSource" v-model="showReferralModal" :source="{}"></client-referral-modal>
    </b-row>
</template>

<script>
    export default {
        name: "ReferralSourceSelect",

        props: ['value', 'businessId'],

        data() {
            return {
                showReferralModal: false,
                referralSources: [],
            }
        },

        computed: {
            selectedValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            filteredSources() {
                return (this.businessId === undefined) ? this.referralSources
                    : this.referralSources.filter(source => source.business_id === this.businessId);
            }
        },

        methods: {
            newReferralSource(data) {
                if(data) {
                    this.showReferralModal = false;
                    this.referralSources.push(data);
                    this.$emit('input', data.id);
                }
            },
            async loadReferralSources() {
                const response = await axios('/business/referral-sources');
                this.referralSources = response.data || [];
            }
        },

        created() {
            this.loadReferralSources();
        }
    }
</script>

<style scoped>

</style>