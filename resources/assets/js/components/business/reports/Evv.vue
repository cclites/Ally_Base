<template>

    <b-card class="mt-5">

        <b-row>

            <b-col lg="12" class="mt-3">

                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()" class="d-flex flex-column align-items-stretch">

                        <b-row>

                            <b-col sm="12" md="6" class="d-flex my-1 align-items-center">

                                <date-picker
                                        style="flex:1"
                                        v-model="start_date"
                                        placeholder="Start Date"
                                >
                                </date-picker>

                                    &nbsp;to&nbsp;

                                <date-picker
                                        style="flex:1"
                                        v-model="end_date"
                                        placeholder="End Date"
                                >
                                </date-picker>
                            </b-col>

                            <b-col class="d-flex my-1" sm="6" md="3">

                                <business-location-select v-model="business_id" :allow-all="true" :hideable="false" style="flex:1"></business-location-select>
                            </b-col>

                            <b-col class="d-flex my-1" sm="6" md="3">

                                <b-form-select
                                        id="caregiver_id"
                                        name="caregiver_id"
                                        v-model="caregiver_id"
                                >
                                    <option value="">--Select a Caregiver--</option>
                                    <option value="">All Caregivers</option>
                                    <option v-for="caregiver in caregivers" :value="caregiver.id" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                </b-form-select>
                            </b-col>
                            <b-col class="d-flex my-1" sm="6" md="3">

                                <b-form-select
                                        id="client_id"
                                        name="client_id"
                                        v-model="client_id"
                                        style="flex:1"
                                >
                                    <option value="">--Select a Client--</option>
                                    <option value="">All Clients</option>
                                    <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nameLastFirst }}</option>
                                </b-form-select>
                            </b-col>
                            <b-col class="d-flex my-1" sm="6" md="3">

                                <b-form-select
                                        id="method"
                                        name="method"
                                        v-model="filter_method"
                                        style="flex:1"
                                >
                                    <option value="">--Filter by Method--</option>
                                    <option value="">ANY</option>
                                    <option value="geolocation">Geolocation</option>
                                    <option value="telephony">Telephony</option>
                                </b-form-select>
                            </b-col>
                            <b-col class="d-flex my-1" sm="6" md="3">

                                <b-form-select
                                        id="method"
                                        name="method"
                                        v-model="filter_verified"
                                        style="flex:1"
                                >
                                    <option value="">--Filter by Verified--</option>
                                    <option value="">ANY</option>
                                    <option value="0">Unverified</option>
                                    <option value="1">Verified</option>
                                </b-form-select>
                            </b-col>
                        </b-row>
                        <b-row class="mt-4">

                            <b-col class="d-flex align-items-center justify-content-end">

                                <b-button type="submit" variant="info" :disabled="loaded === 0">Generate Report</b-button>
                                <b-button type="button" @click=" showHideSummary() " variant="primary" class="ml-2" v-show=" loaded > 0 ">{{ summaryButtonText }}</b-button>
                            </b-col>
                        </b-row>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show=" showSummary && loadingSummaries "></loading-card>

        <evv-summaries v-show=" showSummary && !loadingSummaries"
            :summary=" summary "
        />

        <b-row>

            <b-col lg="12" class="text-right">

                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-if="loaded == 0 "></loading-card>

        <b-row v-if="loaded < 0">

            <b-col lg="12">

                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>
        <div class="table-responsive" v-if="loaded > 0">
            <b-table
                bordered
                striped
                hover
                show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDistance from "../../../mixins/FormatsDistance"
    import FormatsDates from '../../../mixins/FormatsDates'
    import BusinessLocationSelect from "../BusinessLocationSelect";
    import EvvSummaries from './EvvSummaries';

    export default {
        mixins: [
            FormatsDistance,
            FormatsDates
        ],

        components: { BusinessLocationSelect, EvvSummaries },

        data() {

            return {

                showSummary : false,
                loadingSummaries : false,
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                start_date: moment().subtract(1, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                filter_method: "",
                filter_verified: "",
                business_id: "",
                caregiver_id: "",
                caregivers: [],
                client_id: "",
                clients: [],
                items: [],
                summary : {},
                fields: [
                    {
                        key: 'date',
                        formatter: (val) => this.formatDateFromUTC(val, 'ddd MMM D'),
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'business_name',
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Clock In',
                        formatter: (val) => this.formatTimeFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'checked_in_method',
                        label: 'Method',
                        sortable: true,
                    },
                    {
                        key: 'checked_in_verified',
                        label: 'EVV',
                        formatter: this.yesNo,
                        sortable: true,
                    },
                    {
                        key: 'checked_in_distance',
                        label: 'Distance (mi)',
                        formatter: this.distanceFormat,
                        sortable: true,
                    },
                    {
                        key: 'checked_out_time',
                        label: 'Clock Out',
                        formatter: (val) => !val ? '-' : this.formatTimeFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'checked_out_method',
                        label: 'Method',
                        sortable: true,
                    },
                    {
                        key: 'checked_out_verified',
                        label: 'EVV',
                        formatter: this.yesNo,
                        sortable: true,
                    },
                    {
                        key: 'checked_out_distance',
                        label: 'Distance (mi)',
                        formatter: this.distanceFormat,
                        sortable: true,
                    },
                    {
                        key: 'os',
                        label: 'OS',
                        sortable: true,
                    },
                    {
                        key: 'browser',
                        sortable: true,
                    }
                ]
            }
        },

        computed: {

            summaryButtonText() {

                return ( this.showSummary ) ? 'Hide Summary' : 'Show Summary';
            },
            queryString(){

                return '?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date +
                    '&businesses=' + this.business_id + '&caregiver_id=' + this.caregiver_id + '&client_id=' + this.client_id +
                    '&method=' + this.filter_method + '&verified=' + this.filter_verified + '&summarize=' + ( this.loadingSummaries ? 1 : 0 );
            }
        },

        mounted() {
            this.loadFilters();
        },

        methods: {

            loadFilters() {
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
                axios.get('/business/clients').then(response => this.clients = response.data);
            },

            async showHideSummary() {

                this.showSummary = !this.showSummary;
                if ( this.showSummary ) {

                    await this.loadSummaries();
                }
            },
            async loadSummaries() {

                this.loadingSummaries = true;

                await axios.get( '/business/reports/evv' + this.queryString )
                    .then( res => {

                        console.log( 'response: ', res );
                        this.summary = res.data;
                    });

                this.loadingSummaries = false;
            },

            loadItems() {
                this.loaded = 0;
                let url = '/business/reports/evv' + this.queryString;
                axios.get(url)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            item.date = item.checked_in_time;
                            item.business_name = item.business.name;
                            item.caregiver_name = item.caregiver.nameLastFirst;
                            item.client_name = item.client.nameLastFirst;
                            item.checked_in_method = item.checked_in_number ? 'Telephony' : 'Geolocation';
                            item.checked_out_method = item.checked_out_number ? 'Telephony' : 'Geolocation';
                            item.os = (item.user_agent.os) ? item.user_agent.os.family + (item.user_agent.os.major ? ' ' + item.user_agent.os.major : '') : '';
                            item.browser = (item.user_agent.ua) ? item.user_agent.ua.family + (item.user_agent.ua.major ? ' ' + item.user_agent.ua.major : '') : '';

                            // Replace null distances with blocked for geolocation
                            if (item.checked_in_method === 'Geolocation' && item.checked_in_distance === null) {
                                item.checked_in_distance = 'Blocked';
                                if (item.checked_in_latitude) {
                                    item.checked_in_distance = 'Unknown'; // Usually an address issue
                                }
                            }
                            if (item.checked_out_method === 'Geolocation' && item.checked_out_distance === null) {
                                item.checked_out_distance = 'Blocked';
                                if (item.checked_out_latitude) {
                                    item.checked_out_distance = 'Unknown'; // Usually an address issue
                                }
                            }

                            return item;
                        });
                        this.loaded = 1;
                    })
                    .catch(error => this.loaded = -1);
                
                if ( this.showSummary ) {
                    this.loadSummaries();
                }
            },

            dayFormat(date) {
                return moment(date).local().format('ddd MMM D');
            },

            yesNo(val) {
                return val ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-times-rectangle-o"></i>';
            },

            distanceFormat(val) {
                if (val === 0) {
                    return '<1';
                }

                if (! val) {
                    return 'No EVV Data';
                }

                if (isNaN(val)) {
                    return val;
                }

                return this.convertToMiles(val);
            }
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
    .fa-check-square-o { color: green; }
    .fa-times-rectangle-o { color: darkred; }
</style>
