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
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
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
                        'Time': moment(item.checked_in_time).format('h:mm A') + ' - ' + moment(item.checked_out_time).format('h:mm A'),
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