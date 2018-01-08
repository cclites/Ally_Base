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
                            <option v-for="item in caregivers" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="client_id" class="mr-1 mb-1">
                            <option value="">All Clients</option>
                            <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="payment_method" class="mb-1">
                            <option value="">All Payment Methods</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="bank_account">Bank Account</option>
                            <option value="business">Provider Payment</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info" class="mb-1">Generate Report</b-button>
                        &nbsp;&nbsp;<b-button type="button" @click="showHideSummary()" variant="primary" class="mb-1">{{ summaryButtonText }}</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loading < 2"></loading-card>

        <b-row v-show="showSummary && loading >= 2">
            <b-col lg="6">
                <b-card
                        header="Client Charges for Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Client</th>
                            <th>Hours</th>
                            <th>Total</th>
                            <!--<th>Caregiver</th>-->
                            <!--<th>Registry</th>-->
                            <!--<th>Ally</th>-->
                            <th>Type</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in items.clientCharges">
                            <td><a :href="'/business/clients/' + item.id">{{ item.name }}</a></td>
                            <td>{{ item.hours }}</td>
                            <td>{{ moneyFormat(item.total) }}</td>
                            <!--<td>{{ item.caregiver_total }}</td>-->
                            <!--<td>{{ item.provider_total }}</td>-->
                            <!--<td>{{ item.ally_total }}</td>-->
                            <td>{{ item.payment_type }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total for Confirmed Shifts</strong></td>
                                <td>{{ clientTotals.hours }}</td>
                                <td>{{ moneyFormat(clientTotals.total) }}</td>
                                <td></td>
                                <!--<td>{{ clientTotals.caregiver_total }}</td>-->
                                <!--<td>{{ clientTotals.provider_total }}</td>-->
                                <!--<td>{{ clientTotals.ally_total }}</td>-->
                            </tr>
                        </tfoot>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card
                        header="Caregiver Payments for Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Caregiver</th>
                            <th>Hours</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in items.caregiverPayments">
                            <td><a :href="'/business/caregivers/' + item.id">{{ item.name }}</a></td>
                            <td>{{ item.hours }}</td>
                            <td>{{ moneyFormat(item.amount) }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td><strong>Total for Confirmed Shifts</strong></td>
                            <td>{{ caregiverTotals.hours }}</td>
                            <td>{{ moneyFormat(caregiverTotals.amount) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-show="showSummary && loading >= 2">
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Provider Payment For Date Range &amp; Filters:</strong></td>
                            <td>{{ moneyFormat(clientTotals.provider_total) }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Processing Fee For Date Range &amp; Filters:</strong></td>
                            <td>{{ moneyFormat(clientTotals.ally_total) }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-show="loading >= 2">
            <b-col lg="12">
                <b-card
                        header="Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                        title="Confirmed Shifts will be charged &amp; paid, Unconfirmed Shifts will NOT"
                >
                    <b-row class="mb-2">
                        <b-col sm="6">
                            <b-btn href="/business/shifts/create" variant="info">Add a Shift</b-btn>
                            <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                        </b-col>
                        <b-col sm="6" class="text-right">
                            <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn @click="printTable()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
                        </b-col>
                    </b-row>
                    <div class="table-responsive">
                        <b-table bordered striped hover show-empty
                                 :fields="fields"
                                 :items="shiftHistoryItems"
                                 :sort-by.sync="sortBy"
                                 :sort-desc.sync="sortDesc"
                                 class="shift-table"
                        >
                            <template slot="Day" scope="data">
                                {{ data.value !== 'Total' ? dayFormat(data.value) : data.value }}
                            </template>
                            <template slot="Client" scope="row">
                                <a :href="'/business/clients/' + row.item.client_id">{{ row.item.Client }}</a>
                            </template>
                            <template slot="Caregiver" scope="row">
                                <a :href="'/business/caregivers/' + row.item.caregiver_id">{{ row.item.Caregiver }}</a>
                            </template>
                            <template slot="EVV" scope="data">
                                <span v-if="data.value" style="color: green">
                                    <i class="fa fa-check-square-o"></i>
                                </span>
                                    <span v-else-if="data.value === undefined"></span>
                                    <span v-else style="color: darkred">
                                    <i class="fa fa-times-rectangle-o"></i>
                                </span>
                            </template>
                            <template slot="Confirmed" scope="data">
                                {{ (data.value) ? 'Yes' : (data.value === undefined) ? '' : 'No' }}
                            </template>
                            <template slot="actions" scope="row">
                            <span v-if="row.item.id">
                                <b-btn size="sm" :href="'/business/shifts/' + row.item.id" variant="info" v-b-tooltip.hover title="Edit"><i class="fa fa-edit"></i></b-btn>
                                <b-btn size="sm" @click.stop="details(row.item)" v-b-tooltip.hover title="View"><i class="fa fa-eye"></i></b-btn>
                                <span>
                                    <b-btn size="sm" @click.stop="unconfirmShift(row.item.id)" variant="primary" v-b-tooltip.hover title="Unconfirm" v-if="row.item.Confirmed"><i class="fa fa-calendar-times-o"></i></b-btn>
                                    <b-btn size="sm" @click.stop="confirmShift(row.item.id)" variant="primary" v-b-tooltip.hover title="Confirm" v-else><i class="fa fa-calendar-check-o"></i></b-btn>
                                </span>
                                <b-btn size="sm" @click.stop="deleteShift(row.item)" variant="danger" v-b-tooltip.hover title="Delete"><i class="fa fa-times"></i></b-btn>
                            </span>
                            </template>
                        </b-table>
                    </div>
                </b-card>
            </b-col>
        </b-row>

        <!-- Filter columns modal -->
        <b-modal id="filterColumnsModal" title="Show or Hide Columns" v-model="columnsModal">
            <b-container fluid>
                <b-row>
                    <div class="form-check row">
                        <div class="col-sm-auto" v-for="field in availableFields">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" v-model="filteredFields" :value="field">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ field }}</span>
                            </label>
                        </div>
                    </div>
               </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="columnsModal=false">Close</b-btn>
            </div>
        </b-modal>

        <!-- Details modal -->
        <b-modal id="detailsModal" title="Shift Details" v-model="detailsModal" size="lg">
            <b-container fluid>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Client</strong>
                        <br />
                        {{ selectedItem.client_name }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Caregiver</strong><br />
                        {{ selectedItem.caregiver_name }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <strong>Clocked In Time</strong><br />
                        {{ selectedItem.checked_in_time }}
                    </b-col>
                    <b-col sm="6">
                        <strong>Clocked Out Time</strong><br />
                        {{ selectedItem.checked_out_time }}<br />
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="6" class="with-padding-bottom">
                        <strong>Shift Type</strong><br>
                        {{ hoursType(selectedItem)}}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom" v-if="selectedItem.schedule && selectedItem.schedule.notes">
                    <b-col sm="12">
                        <strong>Schedule Notes</strong><br />
                        {{ selectedItem.schedule.notes }}
                    </b-col>
                </b-row>
                <b-row class="with-padding-bottom">
                    <b-col sm="12">
                        <strong>Caregiver Comments</strong><br />
                        {{ selectedItem.caregiver_comments ? selectedItem.caregiver_comments : 'No comments recorded' }}
                    </b-col>
                </b-row>
                
                <strong>Issues on Shift</strong>
                <b-row>
                    <b-col sm="12">
                        <p v-if="!selectedItem.issues || !selectedItem.issues.length">
                            No issues reported
                        </p>
                        <p else v-for="issue in selectedItem.issues">
                            <strong v-if="issue.caregiver_injury">The caregiver reported an injury to themselves.<br /></strong>
                            {{ issue.comments }}
                        </p>
                    </b-col>
                </b-row>
                
                <b-row class="with-padding-bottom" v-if="selectedItem.client.client_type == 'LTCI' && selectedItem.signature != null">
                    <b-col>
                        <strong>Client Signature</strong>
                        <div v-html="selectedItem.signature.content" class="signature"></div>
                    </b-col>
                </b-row>
                
                <strong>Activities Performed</strong>
                <b-row>
                    <b-col sm="12">
                        <p v-if="!selectedItem.activities || !selectedItem.activities.length">
                            No activities recorded
                        </p>
                        <table class="table table-sm" v-else>
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="activity in selectedItem.activities">
                                <td>{{ activity.code }}</td>
                                <td>{{ activity.name }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                
                <strong>Was this Shift Electronically Verified?</strong>
                <b-row class="with-padding-bottom">
                    <b-col sm="6">
                        <span v-if="selectedItem.checked_in_latitude || selectedItem.checked_in_longitude">Yes</span>
                        <span v-else>No</span>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="6">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th colspan="2">Clock In</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_in_latitude || selectedItem.checked_in_longitude">
                                <tr>
                                    <th>Geocode</th>
                                    <td>{{ selectedItem.checked_in_latitude.slice(0,8) }}, {{ selectedItem.checked_in_longitude.slice(0,8) }}</td>
                                </tr>
                                <tr>
                                    <th>Distance</th>
                                    <td>{{ selectedItem.checked_in_distance }}m</td>
                                </tr>
                            </tbody>
                            <tbody v-else-if="selectedItem.checked_in_number">
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ selectedItem.checked_in_number }}</td>
                            </tr>
                            </tbody>
                            <tbody v-else>
                            <tr>
                                <td colspan="2">No EVV data</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                    <b-col sm="6">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th colspan="2">Clock Out</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_out_latitude || selectedItem.checked_out_longitude">
                                <tr>
                                    <th>Geocode</th>
                                    <td>{{ selectedItem.checked_out_latitude.slice(0,8) }}, {{ selectedItem.checked_out_longitude.slice(0,8) }}</td>
                                </tr> 
                                
                                <tr>
                                    <th>Distance</th>
                                    <td>{{ selectedItem.checked_out_distance }}m</td>
                                </tr>
                            </tbody>
                            <tbody v-else-if="selectedItem.checked_out_number">
                                <tr>
                                    <th>Phone Number</th>
                                    <td>{{ selectedItem.checked_out_number }}</td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="2">No EVV data</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="primary" @click="printSelected(selectedItem.id, 'pdf')"><i class="fa fa-file-pdf-o"></i> Download PDF</b-btn>
                <b-btn variant="primary" @click="printSelected(selectedItem.id)"><i class="fa fa-print"></i> Print</b-btn>
                <b-btn variant="info" @click="confirmSelected()" v-if="selectedItem.status === 'WAITING_FOR_CONFIRMATION'">Confirm Shift</b-btn>
                <b-btn variant="info" @click="unconfirmSelected()" v-else>Unconfirm Shift</b-btn>
                <b-btn variant="primary" :href="'/business/shifts/' + selectedItem.id + '/duplicate'">Duplicate</b-btn>
                <b-btn variant="default" @click="detailsModal=false" class="d-inline d-sm-none" style="margin:10px 0;">Close</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import FormatsNumbers from "../mixins/FormatsNumbers";
    import FormatsDates from "../mixins/FormatsDates";
    import BusinessSettings from "../mixins/BusinessSettings";

    export default {
        mixins: [FormatsDates, FormatsNumbers, BusinessSettings],

        props: {},

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
                payment_method: "",
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
                urlPrefix: '/business/reports/data/',
                loading: 0,
            }
        },

        mounted() {
            this.setInitialFields();
            this.loadFiltersData();
            this.loadData();
            console.log(this.businessSettings());
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
                    'Hours': this.shiftTotals.hours,
                    'Mileage': this.shiftTotals.mileage,
                    'CG Total': this.shiftTotals.caregiver_total,
                    'Reg Total': this.shiftTotals.provider_total,
                    'Ally Total': this.shiftTotals.ally_total,
                    'Mileage Costs': this.shiftTotals.mileage_costs,
                    'Other Expenses': this.shiftTotals.other_expenses,
                    'Shift Total': this.shiftTotals.shift_total,
                })
                return items;
            },
            clientTotals() {
                if (this.items.clientCharges.length === 0) return {};
                return this.items.clientCharges.reduce((totals, item) => {
                    return {
                        hours: (this.parseFloat(totals.hours) + this.parseFloat(item.hours)).toFixed(2),
                        total: (this.parseFloat(totals.total) + this.parseFloat(item.total)).toFixed(2),
                        caregiver_total: (this.parseFloat(totals.caregiver_total) + this.parseFloat(item.caregiver_total)).toFixed(2),
                        provider_total: (this.parseFloat(totals.provider_total) + this.parseFloat(item.provider_total)).toFixed(2),
                        ally_total: (this.parseFloat(totals.ally_total) + this.parseFloat(item.ally_total)).toFixed(2),
                    }
                })
            },
            caregiverTotals() {
                if (this.items.caregiverPayments.length === 0) return {};
                return this.items.caregiverPayments.reduce((totals, item) => {
                    return {
                        amount: (this.parseFloat(totals.amount) + this.parseFloat(item.amount)).toFixed(2),
                        hours: (this.parseFloat(totals.hours) + this.parseFloat(item.hours)).toFixed(2),
                    }
                })
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
                return '?start_date=' + this.start_date + '&end_date=' + this.end_date + '&caregiver_id=' + this.caregiver_id + '&client_id=' + this.client_id + '&payment_method=' + this.payment_method;
            }
        },

        methods: {
            reloadData() {
                this.setLocalStorage('sortBy', 'Day');
                this.setLocalStorage('sortDesc', 'false');
                return this.loadData();
            },
            loadData() {
                // Attempt to load local storage information first
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

            loadFiltersData() {
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },

            dayFormat(date) {
                return moment.utc(date).local().format('ddd MMM D');
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

            printSelected(shiftId, type = '') {
                let url = '/business/shifts/' + shiftId + '/print';
                if (type === 'pdf') {
                    url += '?type=pdf';
                }
                window.location = url;
                //$("#detailsModal .container-fluid").print();
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

            hoursType(item) {
                switch (item.hours_type) {
                    case 'default':
                        return 'Regular';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            },

            showHideSummary() {
                this.showSummary = !this.showSummary;
            }
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