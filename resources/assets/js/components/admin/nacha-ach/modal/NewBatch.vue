<template>
    <b-modal id="newBatchHeader" v-model="openModal" title="New Batch Header" ok-title="Save" @ok="validateBeforeSubmit" @cancel="clearModalData">
        <b-form-group label="Service Class Code *" label-size="md">
            <b-form-select v-model="modalData.bh_service_class_code" v-validate="'required'" name="bh_service_class_code">
                <option value="">Please Select</option>
                <option value="200">ACH mixed Debits and Credits</option>
                <option value="220">ACH Credits Only</option>
                <option value="225">ACH Debits Only</option>
            </b-form-select>
            <input-help :form="modalData" text="ACH Debits/ACH Credits" v-if="!errors.has('bh_service_class_code')"></input-help>
            <p class="text-danger" v-if="errors.has('bh_service_class_code')">
                {{ (errors.first('bh_service_class_code')).replace('bh_service_class_code', 'Service Class Code') }}
            </p>
        </b-form-group>

        <b-form-group label="Originating DFI ID *" label-size="md">
            <b-form-input v-model="modalData.bh_originating_DFI_ID" v-validate="'required'" name="bh_originating_DFI_ID"></b-form-input>
            <input-help :form="modalData" text="Originating DFI ID" v-if="!errors.has('bh_originating_DFI_ID')"></input-help>
            <p class="text-danger" v-if="errors.has('bh_originating_DFI_ID')">
                {{ (errors.first('bh_originating_DFI_ID')).replace('bh_originating_DFI_ID', 'Originating DFI ID') }}
            </p>
        </b-form-group>

        <b-form-group label="Company Name *" label-size="md">
            <b-form-input v-model="modalData.bh_company_name" v-validate="'required'" name="bh_company_name"></b-form-input>
            <input-help :form="modalData" text="The Company Originating Entries in the batch" v-if="!errors.has('bh_company_name')"></input-help>
            <p class="text-danger" v-if="errors.has('bh_company_name')">
                {{ (errors.first('bh_company_name')).replace('bh_company_name', 'Company Name') }}
            </p>
        </b-form-group>

        <b-form-group label="Company Entry Description *" label-size="md">
            <b-form-input v-model="modalData.bh_company_entry_description" v-validate="'required'" name="bh_company_entry_description"></b-form-input>
            <input-help :form="modalData" text="A description of the entries contained  in the batch" v-if="!errors.has('bh_company_entry_description')"></input-help>
            <p class="text-danger" v-if="errors.has('bh_company_entry_description')">
                {{ (errors.first('bh_company_entry_description')).replace('bh_company_entry_description', 'Company Entry Description') }}
            </p>
        </b-form-group>
    </b-modal>
</template>

<script>

    export default {

        data() {
            return {
                modalData: new Form({
                    bh_service_class_code: '',
                    bh_originating_DFI_ID: '',
                    bh_company_name: '',
                    bh_company_entry_description: '',
                }),
                openModal: false
            }
        },

        methods: {
            validateBeforeSubmit(evt) {
                evt.preventDefault()
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.addBatch();
                    }
                });
            },

            addBatch() {
                let data = new Form();
                for(let i in this.modalData) {
                    data[i] = this.modalData[i];
                }

                data['entry_details'] = [];
                data['showEntryDetails'] = false;
                this.$emit('addNewBatch', data);
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