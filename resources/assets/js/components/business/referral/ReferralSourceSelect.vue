<template>
    <b-row>
        <b-col md="8">
            <b-form-group label="Referred By" label-for="referred_by">
                <b-form-select id="referral"
                               v-model="selectedValue"
                >
                    <option value="">No Referral Source</option>
                    <option v-for="source in filteredSources" :value="source.id" :key="source.id">{{ source.organization }} - {{ source.contact_name }}</option>
                </b-form-select>
            </b-form-group>
        </b-col>
        <b-col md="4" class="mt-1">
            <b-form-group label="Add">
                <b-btn  @click="showReferralModal = true">Add Referral Source</b-btn>
            </b-form-group>
        </b-col>

        <business-referral-source-modal
            @saved="newReferralSource"
            v-model="showReferralModal" 
            :source="{}"
            :source-type="sourceType"
        ></business-referral-source-modal>
    </b-row>
</template>

<script>
    export default {
        name: "ReferralSourceSelect",

        props: ['value', 'sourceType', 'businessId'],

        data() {

            return {

                showReferralModal : false,
                referralSources   : null,
                business_id       : this.businessId || null
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
                return this.referralSources;
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
                const response = await axios(`/business/referral-sources?type=${this.sourceType}&business_id=${this.business_id}`);
                this.referralSources = response.data || null;
            }
        },

        created() {
            this.loadReferralSources();
        }
    }
</script>

<style scoped>

</style>