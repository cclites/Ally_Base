<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select a User or Provider"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline>
                        <b-form-select
                                id="client_id"
                                name="client_id"
                                v-model="client_id"
                                required
                        >
                            <option value="">--Select a Client--</option>
                            <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }} ({{ client.id }})</option>
                        </b-form-select>
                        <b-form-select
                                id="caregiver_id"
                                name="caregiver_id"
                                v-model="caregiver_id"
                                required
                        >
                            <option value="">--Select a Caregiver--</option>
                            <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }} ({{ caregiver.id }})</option>
                        </b-form-select>
                        <b-form-select
                                id="business_id"
                                name="business_id"
                                v-model="business_id"
                                required
                        >
                            <option value="">--Select a Provider--</option>
                            <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
                        </b-form-select>
                        &nbsp;&nbsp;<b-button @click="loadTransactions()" variant="info">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

        <loading-card v-show="! neverLoaded && loading"></loading-card>

        <b-row v-show="neverLoaded">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>

        <div v-show="! neverLoaded && ! loading">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="transactions"
                        :fields="fields"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                >
                </b-table>
            </div>
            <div class="text-right">
                <h4>Total: ${{ numberFormat(totalAmount) }}</h4>
            </div>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        mixins: [FormatsNumbers],

        props: {
            clientId: {},
            caregiverId: {},
            businessId: {},
        },

        data() {
            return {
                sortBy: null,
                sortDesc: false,
                business_id: this.businessId ? this.businessId : "",
                caregiver_id: this.caregiverId ? this.caregiverId : "",
                client_id: this.clientId ? this.clientId : "",
                businesses: [],
                caregivers: [],
                clients: [],
                processing: false,
                transactions: [],
                loading: false,
                neverLoaded: true,
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'transaction_type',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                    {
                        key: 'last_action',
                        sortable: true,
                    },
                    {
                        key: 'last_status',
                        sortable: true,
                    },
                    {
                        key: 'net_amount',
                        sortable: true,
                        formatter: this.numberFormat
                    },
                ]
            }
        },

        mounted() {
            this.loadFiltersData();
            this.loadTransactions();
        },

        computed: {
            totalAmount() {
                return this.transactions.reduce((previous, current) => {
                    return previous + parseFloat(current.net_amount);
                }, 0);
            }
        },

        methods: {
            loadFiltersData() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
                axios.get('/admin/clients').then(response => this.clients = response.data);
                axios.get('/admin/caregivers').then(response => this.caregivers = response.data);
            },
            loadTransactions() {
                this.loading = true;
                let url = '/admin/reports/reconciliation/';
                if (this.client_id) {
                    url = url + 'client/' + this.client_id;
                } else if (this.caregiver_id) {
                    url = url + 'caregiver/' + this.caregiver_id;
                } else if (this.business_id) {
                    url = url + 'business/' + this.business_id;
                }
                else {
                    this.loading = false;
                    this.transactions = [];
                    return;
                }
                axios.get(url)
                    .then(response => {
                        this.transactions = response.data.map(transaction => {
                            transaction.last_action = (transaction.last_history) ? transaction.last_history.action : "";
                            transaction.last_status = (transaction.last_history) ? transaction.last_history.status : "";
                            return transaction;
                        });
                        this.loading = false;
                        this.neverLoaded = false;
                    })
                    .catch(e => {
                        this.loading = false;
                        this.neverLoaded = false;
                    });
            },
        },

        watch: {
            client_id(val) {
                if (val) {
                    this.caregiver_id = "";
                    this.business_id = "";
                }
            },
            caregiver_id(val) {
                if (val) {
                    this.client_id = "";
                    this.business_id = "";
                }
            },
            business_id(val) {
                if (val) {
                    this.caregiver_id = "";
                    this.client_id = "";
                }
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
