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
                                v-model="start_date"
                                placeholder="Start Date"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date"
                                placeholder="End Date"
                        >
                        </date-picker>
                        <b-form-select v-model="caregiver_id">
                            <option value="">All Caregivers</option>
                            <option v-for="item in caregivers" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="client_id">
                            <option value="">All Clients</option>
                            <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                        &nbsp;&nbsp;<b-button type="button" @click="showHideSummary()" variant="primary">{{ summaryButtonText }}</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <b-row v-show="showSummary">
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
                            <td>{{ item.total }}</td>
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
                                <td>{{ clientTotals.total }}</td>
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
                            <td>{{ item.amount }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td><strong>Total for Confirmed Shifts</strong></td>
                            <td>{{ caregiverTotals.hours }}</td>
                            <td>{{ caregiverTotals.amount }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-show="showSummary">
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Provider Payment For Date Range &amp; Filters:</strong></td>
                            <td>{{ clientTotals.provider_total }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Processing Fee For Date Range &amp; Filters:</strong></td>
                            <td>{{ clientTotals.ally_total }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                        title="Confirmed Shifts will be charged &amp; paid, Unconfirmed Shifts will NOT"
                >
                    <b-row>
                        <b-col sm="6">
                            <b-btn href="/business/shifts/create" variant="info">Add a Shift</b-btn>
                            <b-btn @click="columnsModal = true" variant="primary">Show or Hide Columns</b-btn>
                        </b-col>
                        <b-col sm="6" class="text-right">
                            <b-btn :href="urlPrefix + 'shifts' + queryString + '&export=1'" variant="success"><i class="fa fa-file-excel-o"></i> Export to Excel</b-btn>
                            <b-btn href="javascript:print()" variant="primary"><i class="fa fa-print"></i> Print</b-btn>
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
                <b-row class="with-padding-bottom" v-if="selectedItem.client.client_type == 'LTCI' && selectedItem.signature != null">
                    <b-col>
                        <strong>Client Signature</strong>
                        <br />
                        <span class="signature">{{ selectedItem.client_name }}</span>
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
                        <strong>Special Designation</strong><br>
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
                <h4>Issues on Shift</h4>
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
                <h4>Activities Performed</h4>
                <b-row>
                    <b-col sm="12">
                        <p v-if="!selectedItem.activities || !selectedItem.activities.length">
                            No activities recorded
                        </p>
                        <table class="table" v-else>
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
                <h4>EVV</h4>
                <b-row>
                    <b-col sm="6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock In</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_in_latitude || selectedItem.checked_in_longitude">
                            <!-- <tr>
                                <th>Geocode</th>
                                <td>{{ selectedItem.checked_in_latitude.slice(0,8) }},<br />{{ selectedItem.checked_in_longitude.slice(0,8) }}</td>
                            </tr> -->
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
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock Out</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_out_latitude || selectedItem.checked_out_longitude">
                            <!-- <tr>
                                 <th>Geocode</th>
                                 <td>{{ selectedItem.checked_out_latitude.slice(0,8) }},<br />{{ selectedItem.checked_out_longitude.slice(0,8) }}</td>
                             </tr> -->
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
                <b-btn variant="default" @click="detailsModal=false">Close</b-btn>
                <b-btn variant="info" @click="confirmSelected()" v-if="selectedItem.status === 'UNCONFIRMED'">Confirm Shift</b-btn>
                <b-btn variant="info" @click="unconfirmSelected()" v-else>Unconfirm Shift</b-btn>
                <b-btn variant="primary" :href="'/business/shifts/' + selectedItem.id + '/duplicate'">Duplicate to a New Shift</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import FormatsDates from "../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates],

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
            }
        },

        mounted() {
            this.setInitialFields();
            this.loadFiltersData();
            this.loadData();
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
                fields.push('actions');
                return fields;
            },
            shiftHistoryItems() {
                let items = this.items.shifts;
                items = items.map(function(item) {
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
                        'CG Rate': item.caregiver_rate,
                        'Reg Rate': item.provider_fee,
                        'Ally Fee': item.ally_fee,
                        'Total Hourly': item.hourly_total,
                        'Mileage': item.mileage,
                        'CG Total': item.caregiver_total,
                        'Reg Total': item.provider_total,
                        'Ally Total': item.ally_total,
                        'Mileage Costs': item.mileage_costs,
                        'Other Expenses': item.other_expenses,
                        'Shift Total': item.shift_total,
                        'Type': item.hours_type,
                        'Confirmed': item.confirmed,
                        '_rowVariant': (item.confirmed) ? null : 'warning'
                    }
                });
                items.push({
                    '_rowVariant': 'info',
                    'Day': 'Total',
                    'Hours': this.shiftTotals.duration,
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
                        duration: (this.parseFloat(totals.duration) + this.parseFloat(item.duration)).toFixed(2),
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
                return '?start_date=' + this.start_date + '&end_date=' + this.end_date + '&caregiver_id=' + this.caregiver_id + '&client_id=' + this.client_id;
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
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) this.sortBy = sortBy;
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) this.sortDesc = sortDesc;
                    let showSummary = this.getLocalStorage('showSummary');
                    if (showSummary === false || showSummary === true) this.showSummary = showSummary;
                }

                axios.get(this.urlPrefix + 'caregiver_payments' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.caregiverPayments = response.data;
                        }
                        else {
                            this.items.caregiverPayments = [];
                        }
                    });
                axios.get(this.urlPrefix + 'client_charges' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.clientCharges = response.data;
                        }
                        else {
                            this.items.clientCharges = [];
                        }
                    });
                axios.get(this.urlPrefix + 'shifts' + this.queryString)
                    .then(response => {
                        if (Array.isArray(response.data)) {
                            this.items.shifts = response.data;
                        }
                        else {
                            this.items.shifts = [];
                        }
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
                let message = 'Are you sure you wish to delete the ' + item.Hours + ' hour shift for ' + item.Caregiver + ' on ' + this.formatDate(this.Day) + '?';
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

            confirmSelected() {
                let form = new Form();
                form.post('/business/shifts/' + this.selectedItem.id + '/confirm')
                    .then(response => {
                        this.detailsModal = false;
                        this.items.shifts.map(shift => {
                            if (shift.id === this.selectedItem.id) {
                                shift.status = response.data.data.status;
                            }
                            return shift;
                        });
                    });
            },

            unconfirmSelected() {
                let form = new Form();
                form.post('/business/shifts/' + this.selectedItem.id + '/unconfirm')
                    .then(response => {
                        this.detailsModal = false;
                        this.items.shifts.map(shift => {
                            if (shift.id === this.selectedItem.id) {
                                shift.status = response.data.data.status;
                            }
                            return shift;
                        });
                    });
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
                        return 'None';
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

    @media print {
        body * {
            visibility: hidden;
        }
        .shift-table, .shift-table * {
            visibility: visible;
        }
        .shift-table {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>