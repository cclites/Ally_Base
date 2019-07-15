<template>
    <b-container fluid>
        <b-row>
            <b-col>
                <b-card header="Third party payer report for invoiced shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                >

                    <div class="form-inline">

                        <business-location-form-group
                                v-model="form.business"
                                :allow-all="true"
                                class="mb-2 mr-2"
                                :label="null"
                        />

                        <date-picker v-model="form.start"
                                     placeholder="Start Date"
                                     weekStart="1"
                                     class="mb-2 mr-2 col-md-2"
                        >
                        </date-picker>
                        &nbsp;to&nbsp;
                        <date-picker v-model="form.end"
                                     placeholder="End Date"
                                     class="mb-2 mr-2 col-md-2">
                        </date-picker>

                        <b-form-select v-model="form.type" class="mb-2 mr-2" name="client_id">
                            <option v-for="item in clientTypes" :key="item.value" :value="item.value">{{ item.text }}</option>
                        </b-form-select>

                        <b-select v-model="form.client" class="mb-2 mr-2">
                            <option value="">All Clients</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                        </b-select>

                        <b-form-select v-model="form.payer" class="mb-2 mr-2" name="payer">
                            <option value="">All Payers</option>
                            <option :value="PRIVATE_PAY_ID">PRIVATE PAY</option>
                            <option :value="OFFLINE_PAY_ID">OFFLINE</option>
                            <option v-for="p in payers" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </b-form-select>

                        <b-button @click="fetch()" variant="info" :disabled="busy || form.output_format == ''" class="mr-2 mb-2">
                            <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                            Generate Report
                        </b-button>
                    </div>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card>
                    <div class="d-flex mb-2">
                        <b-btn class="ml-auto" variant="success" @click="printTable()">
                            <i class="fa fa-print"></i> Print
                        </b-btn>
                    </div>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                                 :busy="busy"
                                 :items="items"
                                 :fields="fields"
                                 :current-page="currentPage"
                                 :per-page="perPage"
                                 :sort-by.sync="sortBy"
                                 :sort-desc.sync="sortDesc"
                                 :empty-text="emptyText"
                                 class="report-table"
                        >
                            <template slot="invoice_name" scope="row">
                                <a :href="`/business/client/invoices/${row.item.invoice_id}`">{{ row.item.invoice_name }}</a>
                            </template>
                            <template slot="client_name" scope="row">
                                <a :href="`/business/clients/${row.item.client_id}`">{{ row.item.client_name }}</a>
                            </template>
                            <template slot="caregiver_name" scope="row">
                                <a :href="`/business/caregivers/${row.item.caregiver_id}`">{{ row.item.caregiver_name }}</a>
                            </template>
                            <template slot="evv" scope="data">
                                <span v-if="data.value" style="color: green">
                                    <i class="fa fa-check-square-o"></i>
                                </span>
                                <span v-else-if="data.value === undefined"></span>
                                <span v-else style="color: darkred">
                                    <i class="fa fa-times-rectangle-o"></i>
                                </span>
                            </template>
                        </b-table>
                    </div>
                    <b-row>
                        <b-col lg="6">
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import BusinessLocationSelect from '../../business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import Constants from '../../../mixins/Constants';

    export default {
        components: { BusinessLocationFormGroup, BusinessLocationSelect },
        mixins: [FormatsNumbers, FormatsDates, Constants],
        props: {
            clients: {
                type: [Array, Object],
                default: () => { return []; },
            },
            payers: {
                type: [Array, Object],
                default: () => { return []; },
            },
        },

        computed: {
            emptyText() {
                if (! this.hasRun) {
                    return 'Select a date range and press Generate Report';
                }
                return 'No records for ' + this.formatDate(this.form.start) + ' through ' + this.formatDate(this.form.end);
            }
        },

        data() {
            return {
                form: new Form({
                    business: '',
                    start: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                    type: '',
                    client: '',
                    payer:'',
                    json: 1
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: [
                    { key: 'client_name', label: 'Client', sortable: true, },
                    { key: 'invoice_name', label: 'Invoice', sortable: true, },
                    { key: 'hic', label: 'HIC#', sortable: true, },
                    { key: 'dob', label: 'Client DOB', sortable: true, },
                    { key: 'code', label: 'Diagnosis Code', sortable: true, },
                    { key: 'caregiver', label: 'Caregiver', sortable: true, },
                    { key: 'payer', label: 'Payer', sortable: true, },
                    { key: 'service', label: 'Service Code & Type', sortable: true },
                    { key: 'service_auth', label: 'Authorization Number', sortable: true, formatter: x => x ? x : '-' },
                    { key: 'date', label: 'Date', sortable: true, formatter: x => this.formatDate(x) },
                    { key: 'start', label: 'Start', sortable: true, formatter: x => this.formatTimeFromUTC(x) },
                    { key: 'end', label: 'End', sortable: true, formatter: x => this.formatTimeFromUTC(x) },
                    { key: 'units', label: 'Units', sortable: true },
                    { key: 'hours', label: 'Hours', sortable: true },
                    { key: 'rate', label: 'Cost/Hour', sortable: true, formatter: x => this.moneyFormat(x) },
                    { key: 'evv', label: 'EVV', sortable: true },
                    { key: 'billable', label: 'Total Billable', sortable: true, formatter: x => this.moneyFormat(x) },
                ],

                items: [],
                item:'',
                hasRun: false,
                businesses: [],
            }
        },

        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/third-party-payer')
                    .then( ({ data }) => {

                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            printTable() {
                $(".report-table").print();
            },
        },

        mounted() {
        },
    }
</script>
