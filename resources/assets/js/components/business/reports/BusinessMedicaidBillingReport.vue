<template>
    <b-container fluid>
        <b-row>
            <b-col>
                <b-card header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <div class="form-inline">
                        <date-picker v-model="form.start"
                            placeholder="Start Date"
                            weekStart="1"
                            class="mb-2"
                        >
                        </date-picker>
                        &nbsp;to&nbsp;
                        <date-picker v-model="form.end" placeholder="End Date" class="mb-2 mr-2"></date-picker>

                        <client-type-dropdown v-model="form.client_type" class="mb-2 mr-2" name="client_id" />

                        <b-select v-model="form.client" class="mb-2 mr-2">
                            <option value="">All Clients</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                        </b-select>

                        <b-select v-model="form.caregiver" class="mb-2 mr-2">
                            <option value="">All Caregivers</option>
                            <option v-for="caregiver in caregivers" :key="caregiver.id" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                        </b-select>

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
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import Constants from '../../../mixins/Constants';

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsNumbers, FormatsDates, Constants],
        props: {
            clients: {
                type: [Array, Object],
                default: () => { return []; },
            },
            caregivers: {
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
                    business_id: '',
                    start: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end: moment().format('MM/DD/YYYY'),
                    client_type: '',
                    client: '',
                    caregiver: '',
                    json: 1,
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: [
                    { key: 'client_name', label: 'Client', sortable: true, },
                    { key: 'caregiver_name', label: 'Caregiver', sortable: true, },
                    { key: 'service', label: 'Service Code & Type', sortable: true },
                    { key: 'service_auth', label: 'Authorization Number', sortable: true, formatter: x => x ? x : '-' },
                    { key: 'date', label: 'Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    { key: 'start', label: 'Start', sortable: true, formatter: x => this.formatTimeFromUTC(x) },
                    { key: 'end', label: 'End', sortable: true, formatter: x => this.formatTimeFromUTC(x) },
                    { key: 'units', label: 'Units', sortable: true },
                    { key: 'hours', label: 'Hours', sortable: true },
                    { key: 'rate', label: 'Cost/Hour', sortable: true, formatter: x => this.moneyFormat(x) },
                    { key: 'evv', label: 'EVV', sortable: true },
                    { key: 'billable', label: 'Total Billable', sortable: true, formatter: x => this.moneyFormat(x) },
                ],
                items: [],
                hasRun: false,
            }
        },

        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/medicaid-billing')
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
