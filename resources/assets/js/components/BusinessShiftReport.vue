<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Select Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="reloadData()">
                        <date-picker
                                class="mb-1"
                                v-model="start_date"
                                placeholder="Start Date">
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                class="mb-1"
                                v-model="end_date"
                                placeholder="End Date">
                        </date-picker>
                        <b-form-select v-model="caregiver_id" class="mx-1 mb-1">
                            <option value="">All Caregivers</option>
                            <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="client_id" class="mr-1 mb-1">
                            <option value="">All Clients</option>
                            <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="payment_method" class="mb-1">
                            <option value="">All Payment Methods</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="bank_account">Bank Account</option>
                            <option value="business">Provider Payment</option>
                        </b-form-select>
                        <b-form-select v-if="admin" v-model="import_id" class="mb-1">
                            <option value="">--Filter by Import--</option>
                            <option v-for="item in imports" :value="item.id" :key="item.id">{{ item.name }} ({{ item.created_at }})</option>
                        </b-form-select>
                        <b-form-select v-model="charge_status" class="mb-1">
                            <option value="">All Statuses</option>
                            <option value="charged">Charged</option>
                            <option value="uncharged">Un-Charged</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info" class="mb-1">Generate Report</b-button>
                        &nbsp;&nbsp;<b-button type="button" @click="showHideSummary()" variant="primary" class="mb-1">{{ summaryButtonText }}</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loaded >= 0 && loaded < 3"></loading-card>

        <shift-history-summaries v-show="showSummary && loaded >= 3"
                                 :client-charges="items.clientCharges"
                                 :caregiver-payments="items.caregiverPayments"
                                 :admin="admin"
        />

        <b-row v-show="loaded == -1">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>

        <b-row v-show="loaded >= 3">
            <b-col lg="12">
                <b-card
                        header="Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                        title="Confirmed Shifts will be charged &amp; paid, Unconfirmed Shifts will NOT"
                >
                    <b-row class="mb-2">
                        <b-col sm="6">
                            <b-btn @click="addShiftModal = true" variant="info">Add a Shift</b-btn>
                            <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                        </b-col>
                        <b-col sm="6" class="text-right">
                            <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>
                    <shift-history-table :fields="fields" :items="shiftHistoryItems">
                        <template slot="actions" scope="row">
                            <b-btn size="sm" :href="'/business/shifts/' + row.item.id" variant="info" v-b-tooltip.hover title="Edit"><i class="fa fa-edit"></i></b-btn>
                            <b-btn size="sm" @click.stop="details(row.item)" v-b-tooltip.hover title="View"><i class="fa fa-eye"></i></b-btn>
                            <span>
                                <b-btn size="sm" @click.stop="unconfirmShift(row.item.id)" variant="primary" v-b-tooltip.hover title="Unconfirm" v-if="row.item.Confirmed"><i class="fa fa-calendar-times-o"></i></b-btn>
                                <b-btn size="sm" @click.stop="confirmShift(row.item.id)" variant="primary" v-b-tooltip.hover title="Confirm" v-else-if="row.item.status !== 'Clocked In'"><i class="fa fa-calendar-check-o"></i></b-btn>
                            </span>
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
        <shift-details-modal v-model="detailsModal" :selected-item="selectedItem">
            <template slot="buttons" scope="row">
                <b-btn variant="default" @click="downloadSelected()"><i class="fa fa-file-pdf-o"></i> Download PDF</b-btn>
                <b-btn variant="primary" @click="printSelected()"><i class="fa fa-print"></i> Print</b-btn>
                <b-btn variant="info" @click="confirmSelected()" v-if="row.item.status === 'WAITING_FOR_CONFIRMATION'">Confirm Shift</b-btn>
                <b-btn variant="info" @click="unconfirmSelected()" v-else-if="row.item.status !== 'CLOCKED_IN'">Unconfirm Shift</b-btn>
                <b-btn variant="primary" :href="'/business/shifts/' + row.item.id + '/duplicate'">Duplicate</b-btn>
                <b-btn variant="default" @click="detailsModal=false">Close</b-btn>
            </template>
        </shift-details-modal>

        <add-shift-modal 
            v-model="addShiftModal" 
            :caregiver="caregiver_id" 
            :client="client_id"
            :no-close-on-backdrop="true"
            @shiftCreated="onShiftCreated()"
        ></add-shift-modal>
    </div>
</template>

<script>
    import FormatsNumbers from "../mixins/FormatsNumbers";
    import FormatsDates from "../mixins/FormatsDates";
    import BusinessSettings from "../mixins/BusinessSettings";
    import ShiftHistoryTable from "./shifts/ShiftHistoryTable";
    import FilterColumnsModal from "./modals/FilterColumnsModal";
    import ShiftDetailsModal from "./modals/ShiftDetailsModal";
    import AddShiftModal from "./modals/AddShiftModal";
    import ShiftHistorySummaries from "./shifts/ShiftHistorySummaries";
    import LocalStorage from "../mixins/LocalStorage";

    export default {
        components: {
            ShiftHistorySummaries,
            ShiftDetailsModal,
            FilterColumnsModal,
            AddShiftModal,
            ShiftHistoryTable
        },

        mixins: [FormatsDates, FormatsNumbers, BusinessSettings, LocalStorage],

        props: {
            admin: Number,
            autoload: Number,
            imports: Array
        },

        data() {
            return {
                items: {
                    shifts: [],
                    clientCharges: [],
                    caregiverPayments: [],
                },
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                caregiver_id: "",
                client_id: "",
                import_id: "",
                payment_method: "",
                clients: [],
                caregivers: [],
                showSummary: false,
                sortBy: 'Day',
                sortDesc: false,
                addShiftModal: false,
                detailsModal: false,
                selectedItem: {
                    client: {}
                },
                columnsModal: false,
                filteredFields: [],
                urlPrefix: '/business/reports/data/',
                loaded: -1,
                charge_status: '',
                localStoragePrefix: 'shift_report_',
            }
        },

        mounted() {
            this.loadFiltersFromStorage();
            this.setInitialFields();
            this.loadFiltersData();
            if (this.autoload) {
                this.loadData();
            }
        },

        computed: {
            availableFields() {
                let fields = [
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
                    'Charged',
                ];

                // remove certain fields completely based on business settings
                if (! this.businessSettings().co_mileage) {
                    fields.splice(fields.indexOf('Mileage'), 1);
                    fields.splice(fields.indexOf('Mileage Costs'), 1);
                }
                if (! this.businessSettings().co_expenses) {
                    fields.splice(fields.indexOf('Other Expenses'), 1);
                }
                
                return fields;
            },

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
                        'Type': item.hours_type == 'default' ? 'Reg' : item.hours_type,
                        'Confirmed': item.confirmed,
                        'confirmed_at': item.confirmed_at,
                        'Charged': item.charged,
                        'charged_at': item.charged_at,
                        'status': item.status,
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
                return '?start_date=' + this.start_date + '&end_date=' + this.end_date + '&caregiver_id=' + this.caregiver_id
                        + '&client_id=' + this.client_id + '&payment_method=' + this.payment_method
                        + '&import_id=' + this.import_id + '&status=' + this.charge_status;
            }
        },

        methods: {
            loadFiltersFromStorage() {
                if (typeof(Storage) !== "undefined") {
                    let startDate = this.getLocalStorage('startDate');
                    if (startDate) this.start_date = startDate;
                    let endDate = this.getLocalStorage('endDate');
                    if (endDate) this.end_date = endDate;
                    let filterCaregiverId = this.getLocalStorage('filterCaregiverId');
                    if (filterCaregiverId) this.caregiver_id = filterCaregiverId;
                    let filterClientId = this.getLocalStorage('filterClientId');
                    if (filterClientId) this.client_id = filterClientId;
                    let filterPaymentMethod = this.getLocalStorage('filterPaymentMethod');
                    if (filterPaymentMethod) this.payment_method = filterPaymentMethod;
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) this.sortBy = sortBy;
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) this.sortDesc = sortDesc;
                    let showSummary = this.getLocalStorage('showSummary');
                    if (showSummary === false || showSummary === true) this.showSummary = showSummary;
                }
            },
            reloadData() {
                this.setLocalStorage('sortBy', 'Day');
                this.setLocalStorage('sortDesc', 'false');
                return this.loadData();
            },
            loadData() {
                this.loaded = 0;

                axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.caregiverPayments = response.data;
                        }
                        else {
                            this.items.caregiverPayments = [];
                        }
                        this.loaded++;
                    });
                axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.clientCharges = response.data;
                        }
                        else {
                            this.items.clientCharges = [];
                        }
                        this.loaded++;
                    });
                axios.get(this.urlPrefix + 'shifts' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.shifts = response.data;
                        }
                        else {
                            this.items.shifts = [];
                        }
                        this.loaded++;
                    });
            },

            loadFiltersData() {
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },

            details(item) {
                let component = this;
                axios.get('/business/shifts/' + item.id)
                    .then(function(response) {
                        let shift = response.data;
                        shift.checked_in_time = moment.utc(shift.checked_in_time).local().format('L LT');
                        shift.checked_out_time = moment.utc(shift.checked_out_time).local().format('L LT');
                        component.selectedItem = shift;
                        component.detailsModal = true;
                        console.log(component.selectedItem);
                    })
                    .catch(function(error) {
                        alert('Error loading shift details');
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

            confirmShift(id) {
                if (this.businessSettings().ask_on_confirm === undefined || this.businessSettings().ask_on_confirm == 1) {
                    if (!confirm('Are you sure you wish to confirm this shift?')) {
                        return;
                    }
                }

                let form = new Form();
                form.post('/business/shifts/' + id + '/confirm')
                    .then(response => {
                        this.detailsModal = false;
                        this.items.shifts.map(shift => {
                            if (shift.id === id) {
                                shift.status = response.data.data.status;
                                shift.confirmed = true;
                            }
                            return shift;
                        });
                    });
            },

            unconfirmShift(id) {
                if (this.businessSettings().ask_on_confirm === undefined || this.businessSettings().ask_on_confirm == 1) {
                    if (!confirm('Are you sure you wish to un-confirm this shift?')) {
                        return;
                    }
                }

                let form = new Form();
                form.post('/business/shifts/' + id + '/unconfirm')
                    .then(response => {
                        this.detailsModal = false;
                        this.items.shifts.map(shift => {
                            if (shift.id === id) {
                                shift.status = response.data.data.status;
                                shift.confirmed = false;
                            }
                            return shift;
                        });
                    });
            },

            confirmSelected() {
                return this.confirmShift(this.selectedItem.id);
            },

            unconfirmSelected() {
                return this.unconfirmShift(this.selectedItem.id);
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

            setInitialFields() {
                if (this.getLocalStorage('fields')) {
                    let fields = JSON.parse(this.getLocalStorage('fields'));
                    if (fields[0] && typeof(fields[0]) !== 'object') {
                        // Temporarily Force 'Confirmed'
                        if (fields.indexOf('Confirmed') === -1) {
                            fields.push('Confirmed');
                        }
                        if (fields.indexOf('Charged') === -1) {
                            fields.push('Charged');
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

            onShiftCreated() {
                this.addShiftModal = false;
                this.reloadData();
            },
        },

        watch: {
            caregiver_id(val) {
                this.setLocalStorage('filterCaregiverId', val);
            },
            client_id(val) {
                this.setLocalStorage('filterClientId', val);
                if (val) this.payment_method = ""; // Set payment method filter back to all if client is selected
            },
            payment_method(val) {
                this.setLocalStorage('filterPaymentMethod', val);
                if (val) this.client_id = ""; // Set client filter back to all if payment method is selected
            },
            start_date(val) {
                this.setLocalStorage('startDate', val);
            },
            end_date(val) {
                this.setLocalStorage('endDate', val);
            },
            sortBy(val) {
                this.setLocalStorage('sortBy', val);
            },
            sortDesc(val) {
                this.setLocalStorage('sortDesc', val);
            },
            filteredFields(val) {
                this.setLocalStorage('fields', JSON.stringify(val));
            },
            showSummary(val) {
                this.setLocalStorage('showSummary', JSON.stringify(val));
            },
        }
    }
</script>

<style>
    table {
        font-size: 14px;
    }
    .table-info, .table-info>td, .table-info>th {
        font-weight: bold;
        font-size: 13px;
        background-color: #ecf7f9;
    }
    .table-sm td,
    .table-sm th {
        padding: 0.2rem 0;
    }
    .signature > svg {
        margin: -25px 0;
        width: 100%;
        height: auto;
        max-width: 400px;
    }
</style>
