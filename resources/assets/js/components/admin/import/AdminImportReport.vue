<template>
    <div>
        <b-card v-if="!selectedImport.id">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :fields="importFields"
                         :items="imports"
                >
                    <template slot="actions" scope="row">
                        <b-btn variant="info" size="sm" @click="selectedImport = row.item">View Shifts</b-btn>
                        <b-btn variant="danger" size="sm" @click="deleteImport(row.item.id)"><i class="fa fa-times"></i> Delete Import</b-btn>
                    </template>
                </b-table>
            </div>
        </b-card>
        <div v-if="selectedImport.id">
            <b-card
                    header="Imported Shifts Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
            >
                <div class="pull-right">
                    <b-btn variant="primary" @click="returnToList()">Return to Import List</b-btn>
                </div>

                <b-form inline @submit.prevent="loadData()">
                    <select2 v-model="caregiver_id" class="form-control mx-1 mb-1">
                        <option value="">All Caregivers</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                    </select2>
                    <select2 v-model="client_id" class="form-control mr-1 mb-1">
                        <option value="">All Clients</option>
                        <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                    </select2>
                    &nbsp;&nbsp;<b-button type="submit" variant="info" class="mb-1">Generate Report</b-button>
                    &nbsp;&nbsp;<b-button type="button" @click="showHideSummary()" variant="primary" class="mb-1">{{ summaryButtonText }}</b-button>
                </b-form>
            </b-card>

            <loading-card v-show="loading < 2"></loading-card>

            <shift-history-summaries v-show="showSummary && loading >= 2"
                                     :client-charges="items.clientCharges"
                                     :caregiver-payments="items.caregiverPayments"
                                     :admin="1"
            />

            <b-row v-show="showSummary && loading >= 2">
                <b-col>
                    <b-card>
                        <b>Total Mileage:</b> {{ shiftTotals.mileage }} (${{ shiftTotals.mileage_costs }})
                    </b-card>
                </b-col>
                <b-col>
                    <b-card>
                        <b>Total Other Expenses:</b> {{ shiftTotals.other_expenses }}
                    </b-card>
                </b-col>
            </b-row>


            <b-row v-show="loading >= 2">
                <b-col lg="12">
                    <b-card header="Shifts"
                            header-text-variant="white"
                            header-bg-variant="info"
                            title="Confirmed Shifts will be charged &amp; paid, Unconfirmed Shifts will NOT"
                    >
                        <b-row class="mb-2">
                            <b-col sm="6">
                                <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                            </b-col>
                            <b-col sm="6" class="text-right">
                                <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                                <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                            </b-col>
                        </b-row>
                        <shift-history-table :fields="fields" :items="shiftHistoryItems">
                            <template slot="actions" scope="row">
                                <b-btn size="sm" :href="'/business/shifts/' + row.item.id" target="_blank" variant="info" v-b-tooltip.hover title="Edit"><i class="fa fa-edit"></i></b-btn>
                                <b-btn size="sm" @click.stop="details(row.item)" v-b-tooltip.hover title="View"><i class="fa fa-eye"></i></b-btn>
                                <b-btn size="sm" @click.stop="deleteShift(row.item)" variant="danger" v-b-tooltip.hover title="Delete"><i class="fa fa-times"></i></b-btn>
                            </template>
                        </shift-history-table>
                    </b-card>
                </b-col>
            </b-row>

            <!-- Filter columns modal -->
            <filter-columns-modal v-model="columnsModal"
                                  :available-fields="availableFields"
                                  :fields.sync="filteredFields"
            />

            <!-- Details modal -->
        <shift-details-modal v-model="detailsModal" :shift="selectedItem"></shift-details-modal>
        </div>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import ShiftHistoryTable from "../../shifts/ShiftHistoryTable";
    import FilterColumnsModal from "../../modals/FilterColumnsModal";
    import ShiftDetailsModal from "../../modals/ShiftDetailsModal";
    import ShiftHistorySummaries from "../../shifts/ShiftHistorySummaries";

    export default {
        components: {
            ShiftHistorySummaries,
            ShiftDetailsModal,
            FilterColumnsModal,
            ShiftHistoryTable
        },

        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        props: {

        },

        data() {
            return {
                imports: [],
                importFields: [
                        'name',
                        'created_at',
                        'actions'
                ],
                selectedImport: {},
                items: {
                    shifts: [],
                    clientCharges: [],
                    caregiverPayments: [],
                },
                start_date: '01/01/2017',
                end_date: '12/31/2021',
                caregiver_id: "",
                client_id: "",
                clients: [],
                caregivers: [],
                showSummary: false,
                sortBy: 'Day',
                sortDesc: false,
                detailsModal: false,
                selectedItem: {
                    client: {}
                },
                columnsModal: false,
                availableFields: [
                    'Day',
                    'Time',
                    'Hours',
                    'Client',
                    'Caregiver',
                    'EVV',
                    'CG Rate',
                    'Reg Rate',
                    'Ally Fee',
                    'Total Hourly',
                    'Mileage',
                    'CG Total',
                    'Reg Total',
                    'Ally Total',
                    'Mileage Costs',
                    'Other Expenses',
                    'Shift Total',
                    'Type',
                    'Confirmed',
                ],
                filteredFields: [],
                urlPrefix: '/business/reports/',
                loading: 0,
            }
        },

        mounted() {
            this.loadImports();
            this.loadFiltersData();
            this.setInitialFields();
        },

        computed: {
            fields() {
                let fields = [];
                for (let field of this.availableFields) {
                    if (this.filteredFields.indexOf(field) !== -1) {
                        fields.push({
                            'key': field,
                            'sortable': true,
                        });
                    }
                }
                fields.push({
                    key: 'actions',
                    class: 'hidden-print'
                });
                return fields;
            },
            shiftHistoryItems() {
                let items = this.items.shifts;
                items = items.map((item) => {
                    return {
                        'id': item.id,
                        'client_id': item.client_id,
                        'caregiver_id': item.caregiver_id,
                        'Day': item.checked_in_time, // filtered in template
                        'Time': moment.utc(item.checked_in_time).local().format('h:mm A') + ' - ' + ((item.checked_out_time) ? moment.utc(item.checked_out_time).local().format('h:mm A') : ''),
                        'Hours': item.hours,
                        'Client': item.client_name,
                        'Caregiver': item.caregiver_name,
                        'EVV': item.EVV,
                        'CG Rate': this.moneyFormat(item.caregiver_rate),
                        'Reg Rate': this.moneyFormat(item.provider_fee),
                        'Ally Fee': this.moneyFormat(item.ally_fee),
                        'Total Hourly': this.moneyFormat(item.hourly_total),
                        'Mileage': item.mileage,
                        'CG Total': this.moneyFormat(item.caregiver_total),
                        'Reg Total': this.moneyFormat(item.provider_total),
                        'Ally Total': this.moneyFormat(item.ally_total),
                        'Mileage Costs': this.moneyFormat(item.mileage_costs),
                        'Other Expenses': this.moneyFormat(item.other_expenses),
                        'Shift Total': this.moneyFormat(item.shift_total),
                        'Type': item.hours_type,
                        'Confirmed': item.confirmed,
                        '_rowVariant': (item.confirmed) ? null : 'warning'
                    }
                });
                items.push({
                    '_rowVariant': 'info',
                    'Day': 'Total',
                    'Time': '',
                    'Hours': this.shiftTotals.hours,
                    'Client': '',
                    'Caregiver': '',
                    'CG Rate': '',
                    'Reg Rate': '',
                    'Ally Fee': '',
                    'Total Hourly': '',
                    'Mileage': this.shiftTotals.mileage,
                    'CG Total': this.shiftTotals.caregiver_total,
                    'Reg Total': this.shiftTotals.provider_total,
                    'Ally Total': this.shiftTotals.ally_total,
                    'Mileage Costs': this.shiftTotals.mileage_costs,
                    'Other Expenses': this.shiftTotals.other_expenses,
                    'Shift Total': this.shiftTotals.shift_total,
                    'Type': '',
                    // Skip EVV and Confirmed since the template scope ignores undefined
                });
                return items;
            },
            shiftTotals() {
                if (this.items.shifts.length === 0) return {};
                return this.items.shifts.reduce((totals, item) => {
                    return {
                        hours: (this.parseFloat(totals.hours) + this.parseFloat(item.hours)).toFixed(2),
                        caregiver_total: (this.parseFloat(totals.caregiver_total) + this.parseFloat(item.caregiver_total)).toFixed(2),
                        provider_total: (this.parseFloat(totals.provider_total) + this.parseFloat(item.provider_total)).toFixed(2),
                        ally_total: (this.parseFloat(totals.ally_total) + this.parseFloat(item.ally_total)).toFixed(2),
                        shift_total: (this.parseFloat(totals.shift_total) + this.parseFloat(item.shift_total)).toFixed(2),
                        mileage: (this.parseFloat(totals.mileage) + this.parseFloat(item.mileage)).toFixed(2),
                        mileage_costs: (this.parseFloat(totals.mileage_costs) + this.parseFloat(item.mileage_costs)).toFixed(2),
                        other_expenses: (this.parseFloat(totals.other_expenses) + this.parseFloat(item.other_expenses)).toFixed(2),
                    }
                })
            },
            summaryButtonText() {
                return (this.showSummary) ? 'Hide Summary' : 'Show Summary';
            },
            queryString() {
                return '?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date + '&caregiver_id=' + this.caregiver_id
                        + '&client_id=' + this.client_id + '&payment_method='
                        + '&import_id=' + this.selectedImport.id;
            },
        },

        methods: {
            async loadImports() {
                const response =  await axios.get('/admin/imports?json=1');
                this.imports = response.data;
            },

            async deleteImport(id) {
                if (confirm('Are you sure you wish to delete this import?  This will delete all associated shifts.')) {
                    let form = new Form();
                    await form.submit('delete', '/admin/imports/' + id);
                    this.loadImports();
                }
            },

            loadFiltersData() {
                axios.get('/admin/clients?json=1').then(response => this.clients = response.data);
                axios.get('/admin/caregivers?json=1').then(response => this.caregivers = response.data);
            },

            returnToList() {
                // Unset selected import
                this.selectedImport = {};
                // Unset filter data
                this.caregiver_id = '';
                this.client_id = '';
            },

            details(item) {
                axios.get('/business/shifts/' + item.id)
                        .then(response => {
                            let shift = response.data;
                            shift.checked_in_time = moment.utc(shift.checked_in_time).local().format('L LT');
                            shift.checked_out_time = moment.utc(shift.checked_out_time).local().format('L LT');
                            this.selectedItem = shift;
                            this.detailsModal = true;
                        })
                        .catch(function(error) {
                            alert('Error loading shift details');
                        });
            },

            loadData() {

                this.loading = 0;

                axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                        .then(response => {
                            if (Array.isArray(response.data)) {
                                this.items.caregiverPayments = response.data;
                            }
                            else {
                                this.items.caregiverPayments = [];
                            }
                            this.loading++;
                        });
                axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                        .then(response => {
                            if (Array.isArray(response.data)) {
                                this.items.clientCharges = response.data;
                            }
                            else {
                                this.items.clientCharges = [];
                            }
                            this.loading++;
                        });
                axios.get(this.urlPrefix + 'shifts' + this.queryString)
                        .then(response => {
                            if (Array.isArray(response.data)) {
                                this.items.shifts = response.data;
                            }
                            else {
                                this.items.shifts = [];
                            }
                            this.loading++;
                        });
            },

            deleteShift(item) {
                let message = 'Are you sure you wish to delete the ' + item.Hours + ' hour shift for ' + item.Caregiver + ' on ' + this.formatDate(item.Day) + '?';
                if (confirm(message)) {
                    let form = new Form();
                    form.submit('delete', '/business/shifts/' + item.id)
                            .then(response => {
                                this.items.shifts = this.items.shifts.filter(function(shift) {
                                    return (shift.id !== item.id);
                                });
                            })
                }
            },

            printSelected() {
                $("#detailsModal .container-fluid").print();
            },

            downloadSelected() {
                let url = '/business/shifts/' + this.selectedItem.id + '/print?type=pdf';
                window.location = url;
            },

            printTable() {
                $(".shift-table").print();
            },

            parseFloat(float) {
                if (typeof(float) === 'string') {
                    float = float.replace(',', '');
                }
                return parseFloat(float);
            },

            getLocalStorage(item) {
                let val = localStorage.getItem('shift_report_' + item);
                if (typeof(val) === 'string') {
                    if (val.toLowerCase() === 'null' || val.toLowerCase() === '') return null;
                    if (val.toLowerCase() === 'false') return false;
                    if (val.toLowerCase() === 'true') return true;
                }
                return val;
            },

            setLocalStorage(item, value) {
                if (typeof(Storage) !== "undefined") {
                    localStorage.setItem('shift_report_' + item, value);
                }
            },

            setInitialFields() {
                if (this.getLocalStorage('fields')) {
                    let fields = JSON.parse(this.getLocalStorage('fields'));
                    if (fields[0] && typeof(fields[0]) !== 'object') {
                        // Temporarily Force 'Confirmed'
                        if (fields.indexOf('Confirmed') === -1) {
                            fields.push('Confirmed');
                        }
                        this.filteredFields = fields;
                        return;
                    }
                }
                this.filteredFields = this.availableFields.slice();
            },

            showHideSummary() {
                this.showSummary = !this.showSummary;
            },
        },

        watch: {
            selectedImport(val) {
                if (val) {
                    this.loadData();
                }
            }
        }
    }
</script>
