<template>
    <b-card header="Payroll Summary"
            header-text-variant="white"
            header-bg-variant="info"
            class="mb-3"
    >
        <div class="form-inline">
            <business-location-form-group
                    v-model="form.business"
                    :allow-all="true"
                    class="mr-2"
                    label="Location"
            />
            <b-form-group label="Start Date" class="mr-2">
                <date-picker v-model="form.start" name="start_date"></date-picker>
            </b-form-group>
            <b-form-group label="End Date" class="mr-2">
                <date-picker v-model="form.end" name="end_date"></date-picker>
            </b-form-group>

            <b-form-group label="Caregiver"  class="mr-2">
                <b-select v-model="form.caregiver">
                    <option value="">All Caregivers</option>
                    <option v-for="caregiver in caregivers" :key="caregiver.id" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                </b-select>
            </b-form-group>


            <b-form-group label="Client Type" class="mr-2">
                <b-select v-model="form.client_type" >
                    <option v-for="type in clientTypes" :key="type.value" :value="type.value">{{ type.text }}</option>
                </b-select>
            </b-form-group>

            <b-col>
                <b-form-group label="&nbsp;">
                    <b-button-group>
                        <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                        <b-button @click="printTable()"><i class="fa fa-print mr-1"></i>Print</b-button>
                    </b-button-group>
                </b-form-group>
            </b-col>
        </div>

        <loading-card v-show="busy"></loading-card>

        <div class="table-responsive summary-table">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :footClone="footClone"
                     class="mt-2"
            >
                <template slot="details" scope="row">
                    &nbsp;<b-btn @click="showPaymentSummary(row.item)">Details</b-btn>
                </template>
                <template slot="FOOT_caregiver" scope="item" class="primary">
                    &nbsp;<strong>For Client Types: </strong> {{totals.type}}
                </template>
                <template slot="FOOT_amount" scope="item" class="primary">
                    &nbsp;<strong>Total:  </strong> {{ moneyFormat(totals.amount ) }}
                </template>
                <template slot="FOOT_details" scope="item" class="primary">
                    &nbsp;&nbsp;
                </template>

            </b-table>
        </div>

        <b-modal ref="paymentSummaryModal">
            <h4>Caregiver: {{ selectedItem.caregiver }}</h4>
            <table>
                <thead>
                  <tr>
                      <th>Date</th>
                      <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="deposit in selectedItem.deposits">
                      <td class="pr-5">{{ formatDate(deposit.created_at) }}</td>
                      <td>{{ moneyFormat(deposit.amount) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="pt-3"><strong>Total</strong></th>
                        <th class="pt-3">{{ depositTotal }}</th>
                    </tr>
                </tfoot>
            </table>
        </b-modal>

    </b-card>

</template>

<script>

    import BusinessLocationSelect from '../../business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import Constants from '../../../mixins/Constants';

    export default {
        name: "payroll-summary-report",
        components: { BusinessLocationFormGroup, BusinessLocationSelect },
        mixins: [FormatsNumbers, FormatsDates, Constants],
        props: {

        },
        data() {
            return {
                items: [],
                form: new Form({
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    client_type: '',
                    business: '',
                    caregiver: '',
                    json: 1,
                    }
                ),
                busy: false,
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'caregiver',
                sortDesc: false,
                caregivers: {
                    type: [Array, Object],
                    default: () => { return []; },
                },
                fields: [
                    { key: 'caregiver', label: 'Caregiver', sortable: true, },
                    { key: 'amount', label: 'Amount', sortable: true, formatter: x => this.moneyFormat(x) },
                    { key: 'details', label: 'Details', sortable: true, },
                ],
                totals: [],
                footClone: false,
                emptyText: "No Results",
                selectedItem: '',
                selectedDeposits: []
            }
        },
        methods: {

            fetch() {
                this.busy = true;
                this.form.get('/business/reports/payroll-summary-report')
                    .then( ({ data }) => {

                        this.items = data.data;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.footClone = true;
                    })
            },
            fetchCaregivers(){
                axios.get('/business/reports/payroll-summary-report/' + this.form.business)
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
                    })
            },
            printTable(){
                window.location = this.form.toQueryString(`/business/reports/payroll-summary-report?print=true`);
            },
            showPaymentSummary(selectedItem){

                this.selectedItem = selectedItem;

                this.$refs.paymentSummaryModal.show();

            },

        },

        computed: {
            depositTotal(){

                if(this.selectedItem){
                    return this.moneyFormat(this.selectedItem.deposits.reduce(function(a, c){return a + Number((c.amount) || 0)}, 0));
                }

            }
        },

        mounted(){
            this.fetchCaregivers();
        },
    }
</script>

<style>
    table.b-table tfoot tr th{
        padding-top: 40px;
    }
</style>