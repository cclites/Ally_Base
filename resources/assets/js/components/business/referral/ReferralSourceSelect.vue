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
            @saved="saved"
            v-model="showReferralModal" 
            :source="{}"
            :source-type="sourceType"
        ></business-referral-source-modal>
    </b-row>
</template>

<script>
    export default {
        name: "ReferralSourceSelect",

        props: ['value', 'sourceType', 'businessId', 'caregiver', 'showActiveOnly'],

        data() {

            return {
                showReferralModal : false,
                referralSources   : [],
                business_id       : this.businessId || '',
                show_active_only : this.showActiveOnly || 0,
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

                //TODO: figure out what to do if the referral source is not
                //      active, but linked to Client/CG

                if(this.show_active_only === 1){

                    let filtered = this.referralSources.filter( function(x){

                        if(x.active === 1){
                            return x;
                        }
                    });

                    return filtered;
                }else{
                    return this.referralSources;
                }
            }
        },

        methods: {
            saved(data) {

                if(data) {
                    this.showReferralModal = false;
                    this.referralSources.push(data);
                    this.$emit('input', data.id);
                }
            },
            async loadReferralSources() {
                let userType = 'client';
                if(this.$props.caregiver){
                    userType = 'caregiver';
                }
                const response = await axios(`/business/referral-sources?type=` + userType + `&json=1`);
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