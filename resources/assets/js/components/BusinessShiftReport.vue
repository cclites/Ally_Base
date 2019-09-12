<template>
    <div>
        <b-card body-class="pb-2">
            <b-row>
                <b-container fluid id="filtersContainer">
                    <b-row>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Date Range" class="form-inline">
                                <date-picker ref="startDate"
                                             style="max-width: 8rem;"
                                             v-model="filters.start_date"
                                             placeholder="Start Date">
                                </date-picker> &nbsp;to&nbsp;
                                <date-picker ref="endDate"
                                             style="max-width: 8rem;"
                                             v-model="filters.end_date"
                                             placeholder="End Date">
                                </date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Caregiver" class="form-inline">
                                <b-form-select v-model="filters.caregiver_id" ref="caregiverFilter">
                                    <option value="">All Caregivers</option>
                                    <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Client" class="form-inline">
                                <b-form-select v-model="filters.client_id" ref="clientFilter">
                                    <option value="">All Clients</option>
                                    <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Payment Method" class="form-inline">
                                <b-form-select v-model="filters.payment_method" ref="paymentFilter">
                                    <option value="">All Payment Methods</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_account">Bank Account</option>
                                    <option value="business">Provider Payment</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
<!--                            <b-form-group label="Charge Status" class="form-inline">-->
<!--                                <b-form-select v-model="filters.charge_status" ref="chargeFilter">-->
<!--                                    <option value="">All Statuses</option>-->
<!--                                    <option value="charged">Charged</option>-->
<!--                                    <option value="uncharged">Un-Charged</option>-->
<!--                                </b-form-select>-->
<!--                            </b-form-group>-->
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Confirmed Status" class="form-inline">
                                <b-form-select v-model="filters.confirmed_status" ref="confirmedFilter">
                                    <option value="">All Statuses</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="unconfirmed">Unconfirmed</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
                            <!-- Business Location is not shown on single business registries -->
                            <business-location-form-group class="form-inline" v-model="filters.business_id" :allow-all="true" />
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Service" class="form-inline">
                                <b-form-select v-model="filters.service_id">
                                    <option value="">All Services</option>
                                    <option v-for="item in services" :value="item.id" :key="item.id">
                                        {{ item.name }} {{ item.code ? `(${item.code})` : '' }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col xl="4" lg="6">
                            <b-form-group label="Client Type" class="form-inline">
                                <b-form-select v-model="filters.client_type" ref="clientTypeFilter">
                                    <option v-for="item in clientTypes" :key="item.value" :value="item.value">{{ item.text }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row v-if="admin"> <!-- ADMIN ONLY DROPDOWN -->
                        <b-col>
                            <b-form-group label="Admin Imports" class="form-inline">
                                <b-form-select v-model="filters.import_id">
                                    <option value="">--Filter by Import--</option>
                                    <option v-for="item in imports" :value="item.id" :key="item.id">{{ item.name }} ({{ item.created_at }})</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12">
                            <div class="card mb-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title">Filter by Flags</h6>
                                    <b-form-radio-group v-model="filters.flag_type" @change="updateFilterFlags(true)">
                                        <b-radio value="any">Include All Shifts - Flagged or Not</b-radio><br />
                                        <b-radio value="none">Has No Flags</b-radio><br />
                                        <b-radio value="selected">Has Any of the Selected Flags:</b-radio>
                                    </b-form-radio-group>
                                    <b-col lg="12">
                                        <b-form-checkbox v-model="includeAllFlags" @change="updateFilterFlags" :disabled="filters.flag_type !== 'selected'">All Flags</b-form-checkbox>
                                        <b-form-checkbox v-for="(display, value) in flagTypes"
                                                         v-model="filters.flags"
                                                         :value="value"
                                                         :key="value"
                                                         class="flag-checkbox"
                                                         :disabled="filters.flag_type !== 'selected'"
                                                         @change="includeAllFlags = false"
                                        >
                                            {{ display }}
                                        </b-form-checkbox>
                                    </b-col>
                                </div>
                            </div>
                        </b-col>
                    </b-row>
                </b-container>

                <b-col lg="12" class="text-right">
                    <b-btn variant="info" @click="reloadData()" :disabled="generateReportDisabled">Generate Report</b-btn>
                    <b-button type="button" @click="showHideSummary()" variant="primary" class="ml-2" v-show="shiftsLoaded">{{ summaryButtonText }}</b-button>
                </b-col>
            </b-row>

            <div class="text-center text-muted" v-show="! shiftsLoaded">
                Update filters and press Generate Report
            </div>

        </b-card>

        <loading-card v-show="showSummary && loadingSummaries"></loading-card>

        <shift-history-summaries v-show="showSummary && ! loadingSummaries"
                                 :client-charges="items.clientCharges"
                                 :caregiver-payments="items.caregiverPayments"
                                 :admin="admin"
        />

        <loading-card v-show="loadingShifts"></loading-card>

        <b-card
                header="Shift List for Date Range &amp; Filters"
                header-text-variant="white"
                header-bg-variant="info"
                title="Confirmed Shifts will be charged &amp; paid, Unconfirmed Shifts will NOT"
                v-show="shiftsLoaded && ! loadingShifts"
                ref="SHRCard"
        >
            <b-row class="mb-2">
                <b-col sm="6">
                    <b-btn @click="addShiftModal = true" variant="info">Add a Shift</b-btn>
                    <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                </b-col>
                <b-col sm="6" class="text-right">
                    <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                    <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                    <b-btn @click="fullscreenToggle()"><i class="fa fa-arrows-alt"></i></b-btn>
                </b-col>
            </b-row>
            <shift-history-table :fields="fields" :items="shiftHistoryItems">
                <template slot="actions" scope="row">
                    <span class="text-nowrap" v-b-tooltip.hover title="Shift ID for Admins Only" v-if="admin && row.item.id">ID: {{ row.item.id }}</span>
                    <div v-if="row.item.id">
                        <b-btn size="sm" @click="editingShiftId = row.item.id; editShiftModal = true" variant="info" v-b-tooltip.hover title="Edit"><i class="fa fa-edit"></i></b-btn>
                        <b-btn size="sm" @click.stop="details(row.item)" v-b-tooltip.hover title="View"><i class="fa fa-eye"></i></b-btn>
                        <span>
                                <b-btn size="sm" @click.stop="unconfirmShift(row.item)" variant="primary" v-b-tooltip.hover title="Unconfirm" v-if="row.item.Confirmed"><i class="fa fa-calendar-times-o"></i></b-btn>
                                <b-btn size="sm" @click.stop="confirmShift(row.item)" variant="primary" v-b-tooltip.hover title="Confirm" v-else-if="row.item.status !== 'Clocked In'"><i class="fa fa-calendar-check-o"></i></b-btn>
                            </span>
                        <b-btn size="sm" @click.stop="deleteShift(row.item)" variant="danger" v-b-tooltip.hover title="Delete"><i class="fa fa-times"></i></b-btn>
                    </div>
                </template>
            </shift-history-table>
        </b-card>

        <!-- Filter columns modal -->
        <filter-columns-modal v-model="columnsModal"
                              :available-fields="availableFields"
                              :fields.sync="filteredFields"
        />

        <!-- Details modal -->
        <shift-details-modal v-model="detailsModal" :shift="selectedItem">
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
            :caregiver="{id: filters.caregiver_id}"
            :client="{id: filters.client_id}"
            @shift-created="onShiftCreate"
        ></add-shift-modal>

        <edit-shift-modal
            v-model="editShiftModal"
            :shift_id="editingShiftId"
            :activities="activities"
            @shift-updated="onShiftUpdate"
            @shift-deleted="onShiftDelete"
            @closed="editingShiftId = null"
        />
    </div>
</template>

<script>
    import FormatsNumbers from "../mixins/FormatsNumbers";
    import FormatsDates from "../mixins/FormatsDates";
    import ShiftHistoryTable from "./shifts/ShiftHistoryTable";
    import FilterColumnsModal from "./modals/FilterColumnsModal";
    import ShiftDetailsModal from "./modals/ShiftDetailsModal";
    import AddShiftModal from "./modals/AddShiftModal";
    import EditShiftModal from "./modals/EditShiftModal";
    import ShiftHistorySummaries from "./shifts/ShiftHistorySummaries";
    import LocalStorage from "../mixins/LocalStorage";
    import BusinessLocationFormGroup from "./business/BusinessLocationFormGroup";
    import ShiftFlags from "../mixins/ShiftFlags";
    import Constants from '../mixins/Constants';

    export default {
        components: {
            BusinessLocationFormGroup,
            ShiftHistorySummaries,
            ShiftDetailsModal,
            FilterColumnsModal,
            AddShiftModal,
            EditShiftModal,
            ShiftHistoryTable
        },

        mixins: [FormatsDates, FormatsNumbers, LocalStorage, ShiftFlags, Constants],

        props: {
            admin: Number,
            autoload: Number,
            imports: Array,
            multi_location: Object,
            activities: {
                type: Array,
                default: [],
            },
        },

        data() {
            return {
                items: {
                    shifts: [],
                    clientCharges: [],
                    caregiverPayments: [],
                },
                filters: {
                    start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                    business_id: "",
                    caregiver_id: "",
                    client_id: "",
                    import_id: "",
                    payment_method: "",
                    charge_status: "",
                    confirmed_status: "",
                    flag_type: "any",
                    flags: [],
                    client_type: '',
                    service_id: '',
                },
                includeAllFlags: false,
                clients: [],
                caregivers: [],
                showSummary: false,
                sortBy: 'Day',
                sortDesc: false,
                addShiftModal: false,
                editShiftModal: false,
                detailsModal: false,
                editingShiftId: null,
                selectedItem: {
                    client: {}
                },
                columnsModal: false,
                filteredFields: [],
                urlPrefix: '/business/reports/',
                shiftsLoaded: false,
                summaryLoaded: false,
                loadingSummaries: false,
                loadingShifts: false,
                localStoragePrefix: 'shift_report_',
                location: 'all',
                services: [],
            }
        },

        async mounted() {
            this.loadFiltersFromStorage();
            this.setInitialFields();
            await this.loadFiltersData();
            if (this.autoload) {
                this.loadData();
            }
        },

        computed: {
            availableFields() {
                let fields = [
                    'Flags',
                    'Day',
                    'Time',
                    'Hours',
                    'Services',
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
                    // 'Charged',
                    'Invoiced',
                ];

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
                        'Flags': item.flags,
                        'Day': item.checked_in_time, // filtered in template
                        'Time': moment.utc(item.checked_in_time).local().format('h:mm A') + ' - ' + ((item.checked_out_time) ? moment.utc(item.checked_out_time).local().format('h:mm A') : ''),
                        'Hours': item.hours,
                        'Client': item.client_name,
                        'Caregiver': item.caregiver_name,
                        'EVV': item.EVV,
                        'CG Rate': this.hourlyFormat(item, item.caregiver_rate, 'caregiver'),
                        'Reg Rate': this.hourlyFormat(item, item.provider_fee, null),
                        'Ally Fee': this.hourlyFormat(item, item.ally_fee, null),
                        'Total Hourly': this.hourlyFormat(item, item.hourly_total, null),
                        'Mileage': item.mileage,
                        'CG Total': this.moneyFormat(item.caregiver_total),
                        'Reg Total': this.moneyFormat(item.provider_total),
                        'Ally Total': this.moneyFormat(item.ally_total),
                        'Mileage Costs': this.moneyFormat(item.mileage_costs),
                        'Other Expenses': this.moneyFormat(item.other_expenses),
                        'Shift Total': this.moneyFormat(item.shift_total),
                        'Type': item.hours_type == 'overtime' ? 'OT' : item.hours_type == 'holiday' ? 'HOL' : 'Reg',
                        'Confirmed': item.confirmed,
                        'confirmed_at': item.confirmed_at,
                        'client_confirmed': item.client_confirmed,
                        'Invoiced': item.invoiced,
                        // 'Charged': item.charged,
                        // 'charged_at': item.charged_at,
                        'Services': item.services,
                        'status': item.status,
                        'business_id': item.business_id,
                        '_rowVariant': this.getRowVariant(item),
                    };
                });
                items.push({
                    '_rowVariant': 'info',
                    'Flags': '',
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
                const filters = this.filters;
                return '?json=1&start_date=' + filters.start_date + '&end_date=' + filters.end_date + '&caregiver_id=' + filters.caregiver_id
                        + '&client_id=' + filters.client_id + '&payment_method=' + filters.payment_method
                        + '&import_id=' + filters.import_id + '&status=' + filters.charge_status + '&confirmed=' + filters.confirmed_status
                        + '&client_type=' + filters.client_type + '&service_id=' + filters.service_id
                        + '&businesses[]=' + filters.business_id + '&flag_type=' + filters.flag_type + '&' + jQuery.param({'flags': filters.flags});
            },
            generateReportDisabled(){
                if( moment(this.filters.start_date).isSameOrBefore(moment(this.filters.end_date))){
                    return false;
                }
                return true;
            },
        },

        methods: {
            getRowVariant(item) {
                if (item.flags && item.flags.includes('duration_mismatch')) {
                    return 'danger';
                }
                return (item.confirmed) ? null : 'warning';
            },

            fullscreenToggle() {
                $(this.$refs.SHRCard).toggleClass('fullscreen-shr');
                $('.left-sidebar').toggle();
                $('.footer').toggle();
                window.scrollTo(0, 0);
            },

            loadFiltersFromStorage() {
                if (typeof(Storage) !== "undefined") {
                    // Saved filters
                    for (let filter of Object.keys(this.filters)) {
                        let value = this.getLocalStorage(filter);
                        if (value) this.filters[filter] = value;
                    }
                    // Sorting/show UI
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) this.sortBy = sortBy;
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) this.sortDesc = sortDesc;
                }
            },

            reloadShift(id) {
                console.log(`Reloading shift #${id}`);
                axios.get(this.urlPrefix + `shifts/reload/${id}`)
                    .then( ({ data }) => {
                        let index = this.items.shifts.findIndex(x => x.id === id);
                        if (index >= 0) {
                            this.items.shifts.splice(index, 1, data.data);
                        } else {
                            console.log(`Could not reload shift #${id}`, data);
                        }
                        this.loadSummaries();
                    })
                    .catch(e => {})
            },

            async loadSummaries() {
                this.loadingSummaries = true;
                await axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.caregiverPayments = response.data;
                        }
                        else {
                            this.items.caregiverPayments = [];
                        }
                    });
                await axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.clientCharges = response.data;
                        }
                        else {
                            this.items.clientCharges = [];
                        }
                    });
                this.loadingSummaries = false;
            },

            reloadData() {
                this.updateSavedFormFilters();
                this.setLocalStorage('sortBy', 'Day');
                this.setLocalStorage('sortDesc', 'false');
                return this.loadData();
            },

            loadData() {
                this.loadingShifts = true;

                axios.get(this.urlPrefix + 'shifts' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.shifts = response.data;
                        }
                        else {
                            this.items.shifts = [];
                        }
                    })
                    .catch(error => {
                        if (error.response.data && error.response.data.message) {
                            alerts.addMessage('error', error.response.data.message);
                        }
                    })
                    .finally(() => {
                        this.shiftsLoaded = true;
                        this.loadingShifts = false;
                    });

                if (this.showSummary) {
                    this.loadSummaries();
                }
            },

            async loadFiltersData() {
                await axios.get('/business/clients').then(response => this.clients = response.data);
                await axios.get('/business/caregivers').then(response => this.caregivers = response.data);
                await axios.get('/business/services?json=1').then(response => this.services = response.data);
            },

            details(item) {
                let component = this;
                axios.get('/business/shifts/' + item.id)
                    .then(function(response) {
                        let shift = response.data;
                        shift.checked_in_time = moment.utc(shift.checked_in_time).local().format('L LT');
                        shift.checked_out_time = shift.checked_out_time ? moment.utc(shift.checked_out_time).local().format('L LT') : '(Still clocked in)';
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
                            this.onShiftDelete(item.id);
                        })
                }
            },

            confirmShift(shift) {
                let id = shift.id;
                let business = this.$store.getters.getBusiness(shift.business_id) || {};

                if (business.ask_on_confirm === undefined || business.ask_on_confirm == 1) {
                    if (!confirm('Are you sure you wish to confirm this shift?')) {
                        return;
                    }
                }

                let form = new Form();
                form.post('/business/shifts/' + id + '/confirm')
                    .then(response => {
                        this.detailsModal = false;
                        this.reloadShift(id);
                    });
            },

            unconfirmShift(shift) {
                let id = shift.id;
                let business = this.$store.getters.getBusiness(shift.business_id) || {};

                if (business.ask_on_confirm === undefined || business.ask_on_confirm == 1) {
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
                return this.confirmShift(this.selectedItem);
            },

            unconfirmSelected() {
                return this.unconfirmShift(this.selectedItem);
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
                let fields = this.getLocalStorage('fields');
                if (fields && fields[0] && typeof(fields[0]) !== 'object') {
                    if (fields[0] && typeof(fields[0]) !== 'object') {
                        // Temporarily Force 'Flags'
                        if (fields.indexOf('Flags') === -1) {
                            fields.push('Flags');
                        }
                        this.filteredFields = fields;
                        return;
                    }
                }
                this.filteredFields = this.availableFields.slice();
            },

            async showHideSummary() {
                this.showSummary = !this.showSummary;
                if (this.showSummary) {
                    await this.loadSummaries();
                }
            },

            onShiftUpdate(id) {
                console.log('Updating shift ' + id);
                this.editShiftModal = false;
                this.addShiftModal = false;
                this.reloadShift(id, false);
            },

            onShiftCreate() {
                this.editShiftModal = false;
                this.addShiftModal = false;
                this.reloadData();
            },

            hourlyFormat(item, amount, type) {
                if(item.services && item.services.length > 1 && type == 'caregiver'){
                    return 'M';
                }

                return (item.fixed_rates) ? '---' : this.moneyFormat(amount);
            },

            onShiftDelete(id) {
                console.log('Deleting shift ' + id);
                this.editShiftModal = false;
                this.addShiftModal = false;
                this.items.shifts = this.items.shifts.filter(shift => shift.id !== id);
                if (this.showSummary) {
                    this.loadSummaries();
                }
            },

            updateSavedFormFilters() {
                for (let filter of Object.keys(this.filters)) {
                    this.setLocalStorage(filter, this.filters[filter]);
                }
            },

            updateFilterFlags(changedType = false) {
                if (changedType) {
                    this.includeAllFlags = (this.filters.flag_type === 'selected');
                }
                this.filters.flags = this.includeAllFlags && this.filters.flag_type === 'selected' ? this.shiftFlags : [];
            },
        },

        watch: {
            sortBy(val) {
                this.setLocalStorage('sortBy', val);
            },
            sortDesc(val) {
                this.setLocalStorage('sortDesc', val);
            },
            filteredFields(val) {
                this.setLocalStorage('fields', val);
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
    .shift-table td > .fa {
        font-size: 16px;
    }
    #filtersContainer .form-group {
        margin-bottom: 0.5rem;
    }
    .fullscreen-shr {
        background-color: white;
        z-index: 101;
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        border: 0 !important;
        box-shadow: none !important;
    }
</style>
