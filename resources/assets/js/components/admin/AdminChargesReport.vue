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
                            <option value="">--All Providers--</option>
                            <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
                        </b-form-select>
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
                <template slot="actions" scope="row">
                    <b-btn :href="'/admin/transactions/' + row.item.transaction_id"  v-if="row.item.transaction_id">View Transaction</b-btn>
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
                start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                business_id: "",
                businesses: [],
                items: [],
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'business_allotment',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_allotment',
                        sortable: true,
                    },
                    {
                        key: 'system_allotment',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Total Amount',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'gateway_id',
                        label: 'Transaction ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_response',
                        label: 'Trans. Response',
                        sortable: true,
                    },
                    'actions'
                ]
            }
        },

        mounted() {
            this.loadBusinesses();
            this.loadItems();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                axios.get('/admin/charges?json=1&business_id=' + this.business_id + '&start_date=' + this.start_date + '&end_date=' + this.end_date)
                    .then(response => {
                        this.items = response.data.map(function(item) {
                            item.name = (item.client) ? item.client.nameLastFirst : item.business.name;
                            item.transaction_response = (item.transaction) ? item.transaction.response_text : '';
                            item.gateway_id = (item.transaction) ? item.transaction.transaction_id : '';
                            return item;
                        });
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
