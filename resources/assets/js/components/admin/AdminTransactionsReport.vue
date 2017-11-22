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
                        key: 'id',
                        label: 'Internal ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_id',
                        label: 'Gateway ID',
                        sortable: true,
                    },
                    {
                        key: 'transaction_type',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'response_text',
                        sortable: true,
                    },
                ]
            }
        },

        mounted() {
            this.loadItems();
        },

        methods: {
            loadItems() {
                axios.get('/admin/transactions/report?start_date=' + this.start_date + '&end_date=' + this.end_date)
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
