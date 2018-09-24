<template>
    <div>
        <div class="mt-4"><a href="javascript:;" @click="collapseToggle = !collapseToggle">
            Entry details <span class="mdi" :class="[collapseToggle ? 'mdi-minus' : 'mdi-plus']"></span></a>
        </div>

        <b-collapse class="mt-2" v-model="collapseToggle" :id="'entry_details_' + parentIndex">
            <b-card class="mb-1">
                <b-row>
                    <b-col md="12">
                        <b-button v-b-modal.newEntryDetail @click="returnParentIndex" :variant="'primary'">New Entry Detail</b-button>
                    </b-col>
                </b-row>

                <div v-for="(entryDetail, index) in entryDetailsData" class="mt-4">
                    <b-row>
                        <b-col md="3">
                            <b-form-group label="DFI Account Number *" label-size="md">
                                <b-form-input v-model="entryDetail.ppded_DFI_account_number" v-validate="'required'" :name="'ppded_DFI_account_number_' + index"></b-form-input>
                                <input-help :form="entryDetail" text="The account number of the consumer or corporate entity receiving the ACH entry" v-if="!errors.has('ppded_DFI_account_number_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('ppded_DFI_account_number_' + index)">
                                    {{ (errors.first('ppded_DFI_account_number_' + index)).replace('ppded_DFI_account_number_' + index, 'DFI Account Number') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="2">
                            <b-form-group label="Amount *" label-size="md">
                                <b-form-input v-model="entryDetail.ppded_amount" v-validate="'required'" :name="'ppded_amount_' + index"></b-form-input>
                                <input-help :form="entryDetail" text="The dollar amount of the entry" v-if="!errors.has('ppded_amount_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('ppded_amount_' + index)">
                                    {{ (errors.first('ppded_amount_' + index)).replace('ppded_amount_' + index, 'Amount') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="Individual Identification Number *" label-size="md">
                                <b-form-input v-model="entryDetail.ppded_individual_identification_number" v-validate="'required'" :name="'ppded_individual_identification_number_' + index"></b-form-input>
                                <input-help :form="entryDetail" text="Invoice#, employee #, etc." v-if="!errors.has('ppded_individual_identification_number_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('ppded_individual_identification_number_' + index)">
                                    {{ (errors.first('ppded_individual_identification_number_' + index)).replace('ppded_individual_identification_number_' + index, 'Individual Identification Number') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="Individual Name *" label-size="md">
                                <b-form-input v-model="entryDetail.ppded_individual_name" v-validate="'required'" :name="'ppded_individual_name_' + index"></b-form-input>
                                <input-help :form="entryDetail" text="The name of the entry recipient" v-if="!errors.has('ppded_individual_name_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('ppded_individual_name_' + index)">
                                    {{ (errors.first('ppded_individual_name_' + index)).replace('ppded_individual_name_' + index, 'Individual Name') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="1">
                            <span class="mdi mdi-minus remove" @click="removeEntryDetail(index)"></span>
                        </b-col>
                    </b-row>
                </div>

            </b-card>
        </b-collapse>
    </div>
</template>
<script>

    export default {

        props: {
            collapse: Boolean,
            parentIndex: Number,
            entryDetailsData: Array
        },

        data() {
            return {
                collapseToggle: false,
            }
        },

        methods: {
            removeEntryDetail(index) {
                if(this.entryDetailsData[index]) {
                    this.entryDetailsData.splice(index, 1);
                }
            },

            returnParentIndex() {
                this.$emit('setParentIndex', this.parentIndex);
            }
        }
    }
</script>

<style lang="scss">

</style>