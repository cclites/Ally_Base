<template>
    <b-card>
        <b-row class="mb-4">
            <b-col>
                <div class="form-inline">
                    <b-form-select v-model="client" class="mr-3">
                        <option value="">All Clients</option>
                        <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>

                    <date-picker v-model="start_date" placeholder="Start Date" />
                    &nbsp;to&nbsp;
                    <date-picker v-model="end_date" placeholder="End Date" class="mr-3" />
                            
                    <b-form-radio-group v-model="showFilter" name="status" label="Status">
                        <b-form-radio value="unpaid">Show only unpaid dates of service</b-form-radio>
                        <b-form-radio value="all">Show all dates of service</b-form-radio>
                    </b-form-radio-group>
                </div>
            </b-col>
        </b-row>

        <loading-card v-show="loading" />

        <div v-show="! loading">
            <div class="table-responsive mb-3">
                <b-table bordered striped hover show-empty
                    :fields="fields"
                    :items="items"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    class="shift-table"
                >
                    <template slot="date" scope="row">
                        {{ formatDateFromUTC(row.item.checked_in_time) }}
                    </template>
                    <template slot="amount" scope="row">
                        <input type="text" class="form-control" value="0.00" />
                    </template>
                    <template slot="paid" scope="row">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="no_email" value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description"></span>
                            </label>
                        </div>
                    </template>
                    <template slot="partial_payment" scope="row">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="no_email" value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description"></span>
                            </label>
                        </div>
                    </template>
                </b-table>
            </div>

            <div class="mb-3">
                <b-row>
                    <b-col lg="4" class="mb-2">
                        <label for="check_total">Total Check Amount: $</label>
                        <input id="check_total" type="text" class="form-control" value="0.00" style="width: auto!important" />
                    </b-col>
                    <b-col lg="4" class="mb-2">
                        <input type="text" class="form-control" value="" placeholder="Check ID" />
                        <!-- <h3>Total Check Amount: $2,350.20</h3> -->
                    </b-col>
                    <b-col lg="4">
                        <b-btn variant="success">Apply Payment</b-btn>
                    </b-col>
                </b-row>
            </div>

            <div class="mb-3">
                <b-btn variant="primary">Previous Payments</b-btn>
            </div>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        mixins: [ FormatsNumbers, FormatsDates ],

        props: [ 'clients' ],

        data() {
            return {
                loading: true,
                client: '',
                showFilter: 'unpaid',
                start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                items: [],
                fields: [
                    {
                        key: 'id',
                        sortable: true,
                    },
                    {
                        key: 'date',
                        label: 'Date of Service',
                        sortable: true,
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Clock In',
                        formatter: (val) => this.formatTimeFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'checked_out_time',
                        label: 'Clock Out',
                        formatter: (val) => this.formatTimeFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
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
                        key: 'paid',
                        label: 'Paid in Full',
                        sortable: true,
                    },
                    {
                        key: 'partial_payment',
                        label: 'Partial Payment',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        sortable: false,
                    }
                ],
                sortBy: 'shift_time',
                sortDesc: false,
            }
        },

        computed: {
        },

        methods: {
            fetch() {
                axios.get(`/business/accounting/apply-payment`)
                    .then( ({ data }) => {
                        this.loading = false;
                        this.items = data;
                    });
            },
        },

        mounted() {
            this.fetch();
        },

        watch: {
            showFilter(newVal) {
                this.fetch();
            },
        },
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
    .fa-check-square-o { color: green; }
    .fa-times-rectangle-o { color: darkred; }
</style>
