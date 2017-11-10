<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadData()">
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
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-card
                        header="Client Charges for Date Range"
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
                            <td>{{ item.name }}</td>
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
                                <td><strong>Total</strong></td>
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
                        header="Caregiver Payments for Date Range"
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
                            <td>{{ item.name }}</td>
                            <td>{{ item.hours }}</td>
                            <td>{{ item.amount }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td>{{ caregiverTotals.hours }}</td>
                            <td>{{ caregiverTotals.amount }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Provider Payment For Date Range:</strong></td>
                            <td>{{ clientTotals.provider_total }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Processing Fee For Date Range:</strong></td>
                            <td>{{ clientTotals.ally_total }}</td>
                        </tr>
                    </table>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Actual Shifts"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-row>
                        <b-col sm="6">
                            <b-btn href="/business/shifts/create" variant="info">Add a Shift</b-btn>
                        </b-col>
                        <b-col sm="6">
                            <b-row>
                                <b-col cols="6">
                                    <b-form-select v-model="filterCaregiverId">
                                        <option value="">All Caregivers</option>
                                        <option v-for="item in caregivers" :value="item.id">{{ item.nameLastFirst }}</option>
                                    </b-form-select>
                                </b-col>
                                <b-col cols="6">
                                    <b-form-select v-model="filterClientId">
                                        <option value="">All Clients</option>
                                        <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                                    </b-form-select>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-table bordered striped hover show-empty
                             :fields="fields"
                             :items="shiftHistoryItems"
                             :sort-by.sync="sortBy"
                             :sort-desc.sync="sortDesc"
                             class="shift-table"
                    >
                        <template slot="Day" scope="data">
                            {{ dayFormat(data.value) }}
                        </template>
                        <template slot="actions" scope="row">
                            <b-btn size="sm" :href="'/business/shifts/' + row.item.id">Edit</b-btn>
                            <b-btn size="sm" @click.stop="details(row.item)">View</b-btn>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>

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
                        {{ selectedItem.checked_out_time }}
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
                <b-btn variant="info" @click="verifySelected()" v-if="!selectedItem.verified">Mark Verified</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
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
                clients: [],
                caregivers: [],
                filterCaregiverId: "",
                filterClientId: "",
                sortBy: null,
                sortDesc: null,
                detailsModal: false,
                selectedItem: {
                    client: {}
                },
            }
        },

        mounted() {
            this.loadData();
            this.loadFiltersData();
        },

        computed: {
            fields() {
                let fields = [];
                let item;
                if (item = this.shiftHistoryItems[0]) {
                    for (let key of Object.keys(item)) {
                        if (key === 'id') continue;
                        fields.push({
                            'key': key,
                            'sortable': true,
                        });
                    }
                }
                fields.push('actions');
                return fields;
            },
            shiftHistoryItems() {
                let component = this;
                let items = this.items.shifts;
                if (component.filterCaregiverId || component.filterClientId) {
                    items = items.filter(function(item) {
                        if (component.filterCaregiverId && component.filterCaregiverId != item.caregiver_id) return false;
                        if (component.filterClientId && component.filterClientId != item.client_id) return false;
                        return true;
                    });
                }
                return items.map(function(item) {
                    return {
                        'id': item.id,
                        'Day': item.checked_in_time, // filtered in template
                        'Time': moment(item.checked_in_time).format('h:mm A') + ' - ' + ((item.checked_out_time) ? moment(item.checked_out_time).format('h:mm A') : ''),
                        'Hours': item.roundedShiftLength,
                        'Client': item.client.nameLastFirst,
                        'Caregiver': item.caregiver.nameLastFirst,
                        'CG Rate': item.caregiver_rate,
                        'Reg Rate': item.provider_fee,
                        'Ally Fee': item.ally_fee,
                        'Total Hourly': item.hourly_total,
                        'Shift Total': item.shift_total,
                        'Designation': item.hours_type,
                    }
                });
            },
            clientTotals() {
                let component = this;
                return this.items.clientCharges.reduce(function(totals, item) {
                    console.log(totals, item);
                    return {
                        hours: (component.parseFloat(totals.hours) + component.parseFloat(item.hours)).toFixed(2),
                        total: (component.parseFloat(totals.total) + component.parseFloat(item.total)).toFixed(2),
                        caregiver_total: (component.parseFloat(totals.caregiver_total) + component.parseFloat(item.caregiver_total)).toFixed(2),
                        provider_total: (component.parseFloat(totals.provider_total) + component.parseFloat(item.provider_total)).toFixed(2),
                        ally_total: (component.parseFloat(totals.ally_total) + component.parseFloat(item.ally_total)).toFixed(2),
                    }
                })
            },
            caregiverTotals() {
                let component = this;
                return this.items.caregiverPayments.reduce(function(totals, item) {
                    console.log(totals, item);
                    return {
                        amount: (component.parseFloat(totals.amount) + component.parseFloat(item.amount)).toFixed(2),
                        hours: (component.parseFloat(totals.hours) + component.parseFloat(item.hours)).toFixed(2),
                    }
                })
            }
        },

        methods: {
            loadData() {
                let component = this;
                let prefix = '/business/reports/data/';
                axios.get(prefix + 'caregiver_payments?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(function(response) {
                        component.items.caregiverPayments = response.data;
                    });
                axios.get(prefix + 'client_charges?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(function(response) {
                        component.items.clientCharges = response.data;
                    });
                axios.get(prefix + 'shifts?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(function(response) {
                        component.items.shifts = response.data;
                    });
            },

            loadFiltersData() {
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },

            dayFormat(date) {
                return moment(date).format('ddd MMM D');
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

            parseFloat(float) {
                if (typeof(float) === 'string') {
                    float = float.replace(',', '');
                }
                return parseFloat(float);
            },


        },
    }
</script>

<style>
    table {
        font-size: 14px;
    }
</style>