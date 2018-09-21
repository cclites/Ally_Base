<template>
    <div>
        <b-card header="Batch Header" header-text-variant="white" header-bg-variant="success">
            <b-row>
                <b-col md="12">
                    <b-button v-b-modal.newBatchHeader :variant="'warning'">New Batch Header</b-button>
                </b-col>
            </b-row>

            <div v-for="(batch, index) in batchData" class="mt-4">
                <b-card>
                    <b-row>
                        <b-col md="3">
                            <b-form-group label="Service Class Code *" label-size="md">
                                <b-form-select v-model="batch.bh_service_class_code" v-validate="'required'" :name="'bh_service_class_code_' + index">
                                    <option value="">Please Select</option>
                                    <option value="200">ACH mixed Debits and Credits</option>
                                    <option value="220">ACH Credits Only</option>
                                    <option value="225">ACH Debits Only</option>
                                </b-form-select>
                                <input-help :form="batch" text="ACH Debits/ACH Credits" v-if="!errors.has('bh_service_class_code_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('bh_service_class_code_' + index)">
                                    {{ (errors.first('bh_service_class_code_' + index)).replace('bh_service_class_code_' + index, 'Service Class Code') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="2">
                            <b-form-group label="Originating DFI ID *" label-size="md">
                                <b-form-input v-model="batch.bh_originating_DFI_ID" v-validate="'required'" :name="'bh_originating_DFI_ID_' + index"></b-form-input>
                                <input-help :form="batch" text="Originating DFI ID" v-if="!errors.has('bh_originating_DFI_ID_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('bh_originating_DFI_ID_' + index)">
                                    {{ (errors.first('bh_originating_DFI_ID_' + index)).replace('bh_originating_DFI_ID_' + index, 'Originating DFI ID') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="Company Name *" label-size="md">
                                <b-form-input v-model="batch.bh_company_name" v-validate="'required'" :name="'bh_company_name_' + index"></b-form-input>
                                <input-help :form="batch" text="The Company Originating Entries in the batch" v-if="!errors.has('bh_company_name_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('bh_company_name_' + index)">
                                    {{ (errors.first('bh_company_name_' + index)).replace('bh_company_name_' + index, 'Company Name') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="Company Entry Description *" label-size="md">
                                <b-form-input v-model="batch.bh_company_entry_description" v-validate="'required'" :name="'bh_company_entry_description_' + index"></b-form-input>
                                <input-help :form="batch" text="A description of the entries contained  in the batch" v-if="!errors.has('bh_company_entry_description_' + index)"></input-help>
                                <p class="text-danger" v-if="errors.has('bh_company_entry_description_' + index)">
                                    {{ (errors.first('bh_company_entry_description_' + index)).replace('bh_company_entry_description_' + index, 'Company Entry Description') }}
                                </p>
                            </b-form-group>
                        </b-col>

                        <b-col md="1">
                            <span class="mdi mdi-minus remove" @click="removeBatch(index)"></span>
                        </b-col>
                    </b-row>

                    <admin-nachaach-entry-details
                        :collapse="batch.showEntryDetails"
                        :entryDetailsData="batch.entry_details"
                        :parentIndex="index"
                        @setParentIndex="setParentIndex">
                    </admin-nachaach-entry-details>
                </b-card>
            </div>
        </b-card>

        <admin-nachaach-batch-modal
            @addNewBatch="addBatch">
        </admin-nachaach-batch-modal>

        <admin-nachaach-entry-detail-modal
            @addNewEntryDetail="addEntryDetail">
        </admin-nachaach-entry-detail-modal>

    </div>
</template>
<script>

    export default {

        data() {
            return {
                batchData: [],
                modal: false,
                detailParentIndex: 0,
            }
        },

        methods: {
            validateBeforeSubmit(type) {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.addBatch();
                    }
                });
            },

            addBatch(data) {
                this.batchData.push(data);
                this.batchDataToParent();
            },

            addEntryDetail(data) {
                this.batchData[this.detailParentIndex]['entry_details'].push(data);
                this.batchDataToParent();
            },

            removeBatch(index) {
                if(this.batchData[index]) {
                    this.batchData.splice(index, 1);
                    this.batchDataToParent();
                }
            },

            setParentIndex(index) {
                this.detailParentIndex = index;
            },

            batchDataToParent() {
                this.$emit('sendBatchData', this.batchData);
            }
        }
    }
</script>

<style lang="scss">

</style>