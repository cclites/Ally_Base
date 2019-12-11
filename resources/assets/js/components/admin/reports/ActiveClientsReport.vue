<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select a Provider and Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
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
                        <b-form-select
                                class="ml-2"
                                id="business_id"
                                name="business_id"
                                v-model="business_id"
                        >
                            <option value="">All Providers</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        <b-button type="submit" variant="info" class="ml-2">Generate Report</b-button>
                    </b-form>
    
                    <div v-show="! loading" class="table-responsive mt-4">
                        <b-table bordered striped hover show-empty
                                :items="items"
                                :fields="fields"
                        >
                        </b-table>
                    </div>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-card header="Select a Date Range to Compare"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline>
                        <date-picker
                                v-model="start_date2"
                                placeholder="Start Date"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date2"
                                placeholder="End Date"
                        >
                        </date-picker>
                    </b-form>
    
                    <div v-show="! loading" class="table-responsive mt-4">
                        <b-table bordered striped hover show-empty
                                :items="compareItems"
                                :fields="fields"
                        >
                        </b-table>
                    </div>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="compareItems"
                     :fields="fields"
            >
                <template slot="active_clients" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.active_clients_diff)">{{ differences.active_clients_diff }}</span>
                    <span style="color: green" v-else>+ {{ differences.active_clients_diff }}</span>
                    (<span style="color: red" v-if="isNegative(differences.active_clients_percent)">{{ differences.active_clients_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.active_clients_percent }}%</span>)
                </template>
                <template slot="active_caregivers" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.active_caregivers_diff)">{{ differences.active_caregivers_diff }}</span>
                    <span style="color: green" v-else>+ {{ differences.active_caregivers_diff }}</span>
                    (<span style="color: red" v-if="isNegative(differences.active_caregivers_percent)">{{ differences.active_caregivers_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.active_caregivers_percent }}%</span>)
                </template>
                <template slot="total_hours_billed" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.total_hours_billed_diff)">{{ differences.total_hours_billed_diff }}</span>
                    <span style="color: green" v-else>+ {{ differences.total_hours_billed_diff }}</span>
                    (<span style="color: red" v-if="isNegative(differences.total_hours_billed_percent)">{{ differences.total_hours_billed_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.total_hours_billed_percent }}%</span>)
                </template>
                <template slot="total_charges" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.total_charges_diff)">${{ differences.total_charges_diff }}</span>
                    <span style="color: green" v-else>+ ${{ differences.total_charges_diff }}</span>
                    (<span style="color: red" v-if="isNegative(differences.total_charges_percent)">{{ differences.total_charges_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.total_charges_percent }}%</span>)
                </template>
                <template slot="total_shifts" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.total_shifts_diff)">{{ differences.total_shifts_diff }}</span>
                    <span style="color: green" v-else>+ {{ differences.total_shifts_diff }}</span>
                    (<span style="color: red" v-if="isNegative(differences.total_shifts_percent)">{{ differences.total_shifts_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.total_shifts_percent }}%</span>)
                </template>
                <template slot="verified_shifts" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.verified_shifts_diff)">{{ differences.verified_shifts_diff }}%</span>
                    <span style="color: green" v-else>+ {{ differences.verified_shifts_diff }}%</span>
                    (<span style="color: red" v-if="isNegative(differences.verified_shifts_percent)">{{ differences.verified_shifts_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.verified_shifts_percent }}%</span>)
                </template>
                <template slot="telephony" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.telephony_diff)">{{ differences.telephony_diff }}%</span>
                    <span style="color: green" v-else>+ {{ differences.telephony_diff }}%</span>
                    (<span style="color: red" v-if="isNegative(differences.telephony_percent)">{{ differences.telephony_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.telephony_percent }}%</span>)
                </template>
                <template slot="mobile_app" scope="data">
                    <!-- {{ data.value }} -->
                    <span style="color: red" v-if="isNegative(differences.mobile_app_diff)">{{ differences.mobile_app_diff }}%</span>
                    <span style="color: green" v-else>+ {{ differences.mobile_app_diff }}%</span>
                    (<span style="color: red" v-if="isNegative(differences.mobile_app_percent)">{{ differences.mobile_app_percent }}%</span>
                    <span style="color: green" v-else>{{ differences.mobile_app_percent }}%</span>)
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    export default {

        props: {},

        data() {
            return {
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                start_date2: moment().startOf('isoweek').add(-7, 'days').format('MM/DD/YYYY'),
                end_date2: moment().startOf('isoweek').add(-1, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                items: [],
                loading: false,
                compareItems: [],
                differences: {},
                fields: [
                    { key: 'active_clients' },
                    { key: 'active_caregivers' },
                    { key: 'total_hours_billed' },
                    { key: 'total_charges', formatter: val => { return '$' + val } },
                    { key: 'total_shifts' },
                    { key: 'verified_shifts', label: '% of Shifts Verified', formatter: val => { return val + '%' } },
                    { key: 'telephony', formatter: val => { return val + '%' } },
                    { key: 'mobile_app', formatter: val => { return val + '%' } },
                ]
            }
        },

        mounted() {
            this.loadFiltersData();
        },

        methods: {
            loadFiltersData() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadItems() {
                this.loading = true;
                axios.get(`/admin/reports/active-clients?start_date=${this.start_date}&end_date=${this.end_date}&start_date2=${this.start_date2}&end_date2=${this.end_date2}&business_id=${this.business_id}`)
                    .then(response => {
                        this.items = [response.data.report1];
                        this.compareItems = [response.data.report2];
                        this.differences = response.data.report3;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            isNegative(str) {
                if (! str) {
                    return false;
                }
                return String(str).startsWith('-');
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
