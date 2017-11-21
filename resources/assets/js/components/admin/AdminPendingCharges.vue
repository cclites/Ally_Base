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
                <template slot="charge" scope="row">
                    <charge-payment-button :item.sync="row.item" :start-date="start_date" :end-date="end_date" :key="row.item.client_id"></charge-payment-button>
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
                        key: 'client_id',
                        label: 'Client ID',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client Name',
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
                        key: 'payment_type',
                        label: 'Type',
                        sortable: true,
                    },
                    {
                        key: 'total_shifts',
                        label: 'Total Shifts',
                        sortable: true,
                    },
                    {
                        key: 'unauthorized_shifts',
                        label: 'Unauthorized',
                        sortable: true,
                    },
                    'charge'
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
                axios.get('/admin/charges/pending_payments?start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.items = response.data;
                    });
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
