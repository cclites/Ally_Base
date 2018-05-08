<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
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
                                id="business_id"
                                name="business_id"
                                v-model="business_id"
                        >
                            <option value="">--Select a Provider--</option>
                            <option value="">All Providers</option>
                            <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
                        </b-form-select>
                        <b-form-select
                                id="caregiver_id"
                                name="caregiver_id"
                                v-model="caregiver_id"
                        >
                            <option value="">--Select a Caregiver--</option>
                            <option value="">All Caregivers</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="client_id"
                        >
                            <option value="">--Select a Client--</option>
                            <option value="">All Clients</option>
                            <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
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
    import FormatsDates from "../../mixins/FormatsDates";

    export default {

        mixins: [FormatsDates],

        props: {},

        data() {
            return {
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                caregiver_id: "",
                caregivers: [],
                client_id: "",
                clients: [],
                items: [],
                fields: [
                    {
                        key: 'date',
                        formatter: this.dayFormat,
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
                        label: 'Distance (m)',
                        sortable: true,
                    },
                    {
                        key: 'checked_out_time',
                        label: 'Clock Out',
                        formatter: (val) => this.formatTimeFromUTC(val),
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
                        label: 'Distance (m)',
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

        mounted() {
            this.loadFilters();
            // this.loadItems();
        },

        computed: {

        },

        methods: {

            loadFilters() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
                axios.get('/admin/caregivers').then(response => this.caregivers = response.data);
                axios.get('/admin/clients').then(response => this.clients = response.data);
            },

            loadItems() {
                let url = '/admin/reports/evv?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date +
                    '&business_id=' + this.business_id + '&caregiver_id=' + this.caregiver_id +  '&client_id=' + this.client_id;
                axios.get(url)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            item.date = item.checked_in_time.split(' ')[0];
                            item.business_name = item.business.name;
                            item.caregiver_name = item.caregiver.nameLastFirst;
                            item.client_name = item.client.nameLastFirst;
                            item.checked_in_method = item.checked_in_number ? 'Telephony' : 'Geolocation';
                            item.checked_out_method = item.checked_out_number ? 'Telephony' : 'Geolocation';
                            item.os = (item.user_agent.os) ? item.user_agent.os.family + (item.user_agent.os.major ? ' ' + item.user_agent.os.major : '') : '';
                            item.browser = (item.user_agent.ua) ? item.user_agent.ua.family + (item.user_agent.ua.major ? ' ' + item.user_agent.ua.major : '') : '';
                            return item;
                        });
                    });
            },

            dayFormat(date) {
                return moment(date).local().format('ddd MMM D');
            },

            yesNo(val) {
                return val ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-times-rectangle-o"></i>';
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
