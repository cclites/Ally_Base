<template>
    <b-modal id="newEntryDetail" v-model="openModal" title="New Entry Detail" ok-title="Save" @ok="validateBeforeSubmit" @cancel="clearModalData">

        <b-form-group label="DFI Account Number *" label-size="md">
            <b-form-input v-model="modalData.ppded_DFI_account_number" v-validate="'required'" name="ppded_DFI_account_number"></b-form-input>
            <input-help :form="modalData" text="The account number of the consumer or corporate entity receiving the ACH entry" v-if="!errors.has('ppded_DFI_account_number')"></input-help>
            <p class="text-danger" v-if="errors.has('ppded_DFI_account_number')">
                {{ (errors.first('ppded_DFI_account_number')).replace('ppded_DFI_account_number', 'DFI Account Number') }}
            </p>
        </b-form-group>

        <b-form-group label="Amount *" label-size="md">
            <b-form-input v-model="modalData.ppded_amount" v-validate="'required'" name="ppded_amount"></b-form-input>
            <input-help :form="modalData" text="The dollar amount of the entry" v-if="!errors.has('ppded_amount')"></input-help>
            <p class="text-danger" v-if="errors.has('ppded_amount')">
                {{ (errors.first('ppded_amount')).replace('ppded_amount', 'Amount') }}
            </p>
        </b-form-group>

        <b-form-group label="Individual Identification Number *" label-size="md">
            <b-form-input v-model="modalData.ppded_individual_identification_number" v-validate="'required'" name="ppded_individual_identification_number"></b-form-input>
            <input-help :form="modalData" text="Invoice#, employee #, etc." v-if="!errors.has('ppded_individual_identification_number')"></input-help>
            <p class="text-danger" v-if="errors.has('ppded_individual_identification_number')">
                {{ (errors.first('ppded_individual_identification_number')).replace('ppded_individual_identification_number', 'Individual Identification Number') }}
            </p>
        </b-form-group>

        <b-form-group label="Individual Name *" label-size="md">
            <b-form-input v-model="modalData.ppded_individual_name" v-validate="'required'" name="ppded_individual_name"></b-form-input>
            <input-help :form="modalData" text="The name of the entry recipient" v-if="!errors.has('ppded_individual_name')"></input-help>
            <p class="text-danger" v-if="errors.has('ppded_individual_name')">
                {{ (errors.first('ppded_individual_name')).replace('ppded_individual_name', 'Individual Name') }}
            </p>
        </b-form-group>

    </b-modal>
</template>

<script>

    export default {

        data() {
            return {
                modalData: new Form({
                    ppded_DFI_account_number: '',
                    ppded_amount: '',
                    ppded_individual_identification_number: '',
                    ppded_individual_name: '',
                }),
                openModal: false
            }
        },

        methods: {
            validateBeforeSubmit(evt) {
                evt.preventDefault()
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.addEntryDetail();
                    }
                });
            },

            addEntryDetail() {
                let data = new Form();
                for(let i in this.modalData) {
                    data[i] = this.modalData[i];
                }

                this.$emit('addNewEntryDetail', data);
                this.openModal = false;
                this.clearModalData();
            },

            clearModalData() {
                for(let i in this.modalData) {
                    this.modalData[i] = '';
                }

                this.$validator.reset();
            },
        }
    }
</script>

<style lang="scss">

</style>