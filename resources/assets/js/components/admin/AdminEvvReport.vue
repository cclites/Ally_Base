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
                        label: 'Distance',
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
                        label: 'Distance',
                        sortable: true,
                    },
                ]
            }
        },

        mounted() {
            this.loadBusinesses();
            // this.loadItems();
        },

        computed: {

        },

        methods: {

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadItems() {
                let url = '/admin/reports/evv?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date;
                if (this.business_id) url = url + '&business_id=' + this.business_id;
                axios.get(url)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            item.date = item.checked_in_time.split(' ')[0];
                            item.business_name = item.business.name;
                            item.caregiver_name = item.caregiver.nameLastFirst;
                            item.client_name = item.client.nameLastFirst;
                            item.checked_in_method = item.checked_in_number ? 'Telephony' : 'Geolocation';
                            item.checked_out_method = item.checked_out_number ? 'Telephony' : 'Geolocation';
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
