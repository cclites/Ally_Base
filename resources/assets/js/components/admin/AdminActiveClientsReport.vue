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
                                required
                        >
                            <option value="">--Select a Provider--</option>
                            <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                        </b-form-select>
                        <b-button type="submit" variant="info" class="ml-2">Generate Report</b-button>
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
                    <b-btn size="sm" :href="'/admin/transactions/' + row.item.id">View Transaction Details</b-btn>
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
                // items: [],
                // fields: [
                //     {
                //         key: 'id',
                //         label: 'Internal ID',
                //         sortable: true,
                //     },
                //     {
                //         key: 'transaction_id',
                //         label: 'Gateway ID',
                //         sortable: true,
                //     },
                //     {
                //         key: 'transaction_type',
                //         sortable: true,
                //     },
                //     {
                //         key: 'amount',
                //         sortable: true,
                //     },
                //     {
                //         key: 'created_at',
                //         label: 'Date',
                //         sortable: true,
                //     },
                //     {
                //         key: 'response_text',
                //         sortable: true,
                //     },
                //     {
                //         key: 'actions',
                //         class: 'hidden-print'
                //     }
                // ]
            }
        },

        mounted() {
            this.loadFiltersData();
            this.loadItems();
        },

        methods: {
            loadFiltersData() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },
            loadItems() {
                axios.post(`/admin/reports/active-clients?start_date=${this.start_date}&end_date=${this.end_date}&business_id=${this.business_id}`)
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
