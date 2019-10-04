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
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                        &nbsp;&nbsp;<b-button @click="authorizeAll()" variant="primary">Authorize All Shifts</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>

        <div v-if="! loading" class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="shift_time" scope="data">
                    {{ dayFormat(data.value) }}
                </template>
                <template slot="verified" scope="data">
                    <span v-if="data.value" style="color: green">
                        <i class="fa fa-check-square-o"></i>
                    </span>
                    <span v-else style="color: darkred">
                        <i class="fa fa-times-rectangle-o"></i>
                    </span>
                </template>
                <template slot="authorized" scope="row">
                    <authorized-payment-checkbox :item.sync="row.item" :key="row.item.shift_id"></authorized-payment-checkbox>
                </template>
                <template slot="start_time" scope="data">
                    {{ formatTimeFromUTC(data.item.shift_time) }}
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        mixins: [FormatsDates],

        props: {},

        data() {
            return {
                sortBy: 'shift_time',
                sortDesc: false,
                start_date: '08/01/2017',
                end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                items: [],
                loading: false,
                fields: [
                    {
                        key: 'shift_id',
                        label: 'Shift Id',
                        sortable: true,
                    },
                    {
                        key: 'shift_time',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'start_time',
                        label: 'Clock In Time',
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
                        key: 'shift_hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'mileage_costs',
                        label: 'Mileage Cost',
                        sortable: true,
                    },
                    {
                        key: 'total_payment',
                        label: 'Amount',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_allotment',
                        label: 'Caregiver Allotment',
                        sortable: true,
                    },
                    {
                        key: 'business_allotment',
                        label: 'Provider Allotment',
                        sortable: true,
                    },
                    {
                        key: 'ally_allotment',
                        label: 'Ally Allotment',
                        sortable: true,
                    },
                    {
                        key: 'ally_pct',
                        label: 'Ally %',
                        sortable: true,
                    },
                    {
                        key: 'payment_type',
                        label: 'Type',
                        sortable: true,
                    },
                    {
                        key: 'verified',
                        label: 'Verified',
                        sortable: true,
                    },
                    'authorized'
                ]
            }
        },

        mounted() {
            this.loadBusinesses();
        },

        computed: {

        },

        methods: {
            authorizeAll() {
                let form = new Form({
                   start_date: this.start_date,
                   end_date: this.end_date,
                   business_id: this.business_id,
                   authorized: 1
                });
                form.post('/admin/charges/pending_shifts');
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadItems() {
                this.loading = true;
                let url = '/admin/charges/pending_shifts?start_date=' + this.start_date + '&end_date=' + this.end_date;
                if (this.business_id) url = url + '&business_id=' + this.business_id;
                axios.get(url)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            item.client_name = (item.client) ? item.client.name : '';
                            item.caregiver_name = (item.caregiver) ? item.caregiver.name : '';
                            item.authorized = (item.status === 'WAITING_FOR_INVOICE');
                            return item;
                        });
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },

            dayFormat(date) {
                return moment(date).local().format('ddd MMM D');
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
