<template>
    <b-container fluid>
        <b-row>
            <b-col>
                <b-card header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-alert variant="warning" show>
                        Please enter the start and end dates of service.  Only confirmed visits from the Shift History will pull into this payroll report.
                    </b-alert>
                    <div class="form-inline">
                        <business-location-form-group v-model="form.business_id"
                            :label="null"
                            class="mb-2 mr-2"
                            :allow-all="true"
                            :form="form"
                            field="business_id" />

                        <date-picker v-model="form.start"
                            placeholder="Start Date"
                            weekStart="1"
                            class="mb-2"
                        >
                        </date-picker>
                        &nbsp;to&nbsp;
                        <date-picker v-model="form.end" placeholder="End Date" class="mb-2"></date-picker>
                        &nbsp;&nbsp;

                        <b-select v-model="form.output_format" class="mr-2 mb-2">
                            <option value="">-- Select Format --</option>
                            <option value="ADP">ADP</option>
                            <option value="BCN">BCN</option>
                            <option value="PAYCHEX">Paychex</option>
                        </b-select>
                        <b-button @click="fetch()" variant="info" :disabled="busy || form.output_format == ''" class="mr-2 mb-2">
                            <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                            Generate Report
                        </b-button>
                        <b-button @click="exportReport()" variant="success" :disabled="busy || form.output_format == ''" class="mb-2">
                            <i class="fa fa-file-excel-o"></i> Export to Excel
                        </b-button>
                    </div>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card v-if="busy">
                    <loading-card></loading-card>
                </b-card>
                <b-card v-else>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                            :busy="busy"
                            :items="items"
                            :fields="fields"
                            :current-page="currentPage"
                            :per-page="perPage"
                            :sort-by.sync="sortBy"
                            :sort-desc.sync="sortDesc"
                            :empty-text="emptyText">
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

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsNumbers, FormatsDates],

        computed: {
            emptyText() {
                if (! this.hasRun) {
                    return 'Select a date range and format and press Generate Report';
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
                    output_format: '',
                    json: 1,
                    export: 0,
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'caregiver_last_name',
                sortDesc: false,
                fields: [],
                fieldsADP: [
                    { key: 'caregiver_id', label: 'Caregiver ID', sortable: true, },
                    { key: 'caregiver_last_name', label: 'Last Name', sortable: true, },
                    { key: 'caregiver_first_name', label: 'First Name', sortable: true, },
                    { key: 'paycode', label: 'Shift Type', sortable: true, formatter: x => x == 'OVT' ? 'OT' : x },
                    { key: 'pay_rate', label: 'Pay Rate', sortable: true, formatter: x => x == '-' ? 'N/A' : this.moneyFormat(x) },
                    { key: 'hours', label: 'Hours', sortable: true, },
                    { key: 'amount', label: 'Amount', sortable: true, formatter: x => this.moneyFormat(x) },
                ],
                fieldsBCN: [
                    { key: 'caregiver_id', label: 'Caregiver ID', sortable: true, },
                    { key: 'caregiver_last_name', label: 'Last Name', sortable: true, },
                    { key: 'caregiver_first_name', label: 'First Name', sortable: true, },
                    { key: 'paycode', label: 'Shift Type', sortable: true, formatter: x => x == 'OVT' ? 'OT' : x },
                    { key: 'pay_rate', label: 'Pay Rate', sortable: true, formatter: x => x == '-' ? 'N/A' : this.moneyFormat(x) },
                    { key: 'location', label: 'Client Zip', sortable: true, },
                    { key: 'hours', label: 'Hours', sortable: true },
                    { key: 'amount', label: 'Amount', sortable: true, formatter: x => this.moneyFormat(x) },
                ],
                items: this.shifts,
                hasRun: false,
            }
        },

        methods: {
            fetch() {
                if (! this.form.output_format) {
                    return;
                }

                this.busy = true;
                this.form.get('/business/reports/payroll-export')
                    .then( ({ data }) => {
                        this.fields = this.form.output_format == 'BCN' ? this.fieldsBCN : this.fieldsADP;
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            exportReport() {
                this.form.export = 1;
                window.location = this.form.toQueryString('/business/reports/payroll-export');
                this.form.export = 0;
            },
        },

        mounted() {
        },
    }
</script>
