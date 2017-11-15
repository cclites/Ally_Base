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
                        &nbsp;&nbsp;<b-button type="submit" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <div class="table-responsive">
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
                    <authorized-payment-checkbox :item.sync="row.item"></authorized-payment-checkbox>
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
                sortBy: null,
                sortDesc: false,
                start_date: moment().startOf('isoweek').subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').subtract(1, 'days').format('MM/DD/YYYY'),
                items: [],
                fields: [
                    {
                        key: 'shift_time',
                        label: 'Date',
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
                        label: 'Business Allotment',
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
            this.loadItems();
        },

        computed: {

        },

        methods: {
            loadItems() {
                axios.get('/admin/charges/pending_shifts?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.items = response.data.map(function (item) {
                            item.client_name = (item.client) ? item.client.name : '';
                            item.caregiver_name = (item.caregiver) ? item.caregiver.name : '';
                            item.verified = (item.status !== 'WAITING_FOR_APPROVAL');
                            item.authorized = (item.status === 'WAITING_FOR_CHARGE');
                            return item;
                        });
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
