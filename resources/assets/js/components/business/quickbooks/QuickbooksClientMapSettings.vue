<template>
    <div>
        <loading-card v-if="loading" text="Loading..." />

        <div v-else class="">
            <b-row class="mb-2" align-h="end">
                <b-col md="6">
                    <b-btn variant="info" @click="save()" :disabled="busy">Save Changes</b-btn>
                </b-col>
                <b-col md="6" class="text-right">
                    <b-btn v-if="connection.is_desktop" variant="success" href="https://jtrsolutions.atlassian.net/wiki/spaces/AKB/pages/20316176/Setting+up+Ally+for+Quickbooks+Desktop" target="_blank" :disabled="busy">
                        How to Sync Quickbooks Customers
                    </b-btn>
                    <b-btn v-else variant="success" @click="refreshCustomers()" :disabled="busy">
                        Sync Quickbooks Customers
                    </b-btn>
                </b-col>
            </b-row>
            <b-row class="mb-2" align-h="end">
                <b-col md="4" class="text-right">
                    <b-form-input v-model="filter" placeholder="Type to Search" />
                </b-col>
            </b-row>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="items"
                         :current-page="currentPage"
                         :per-page="perPage"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc"
                         :fields="fields"
                         :filter="filter"
                >
                    <template slot="nameLastFirst" scope="row">
                        <a :href="`/business/clients/${row.item.id}`">{{ row.item.nameLastFirst }}</a>
                    </template>
                    <template slot="quickbooks_customer_id" scope="row">
                        <div class="d-flex">
                            <b-form-select v-model="row.item.quickbooks_customer_id" :disabled="busy" class="f-1 mr-2">
                                <option value="">Do No Match</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }} ({{ customer.customer_id }})</option>
                            </b-form-select>

                            <b-btn v-if="!connection.is_desktop && !row.item.quickbooks_customer_id" variant="primary" @click="createCustomer(row.item)" :disabled="busy">Create Customer</b-btn>
                        </div>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6" >
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            connection: {
                type: [Array, Object],
                default: () => { return {}; },
            },
            clients: {
                type: Array,
                default: [],
            },
            businessId: {
                type: [String, Number],
                default: '',
            },
        },

        data() {
            return {
                items: [],
                filter: '',
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'nameLastFirst',
                sortDesc: false,
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Ally Client',
                        sortable: true
                    },
                    {
                        key: 'quickbooks_customer_id',
                        label: 'QuickBooks Customer',
                        sortable: false
                    },
                ],

                customers: [],
                loading: false,
                busy: false,
            }
        },

        mounted() {
            this.loading = true;
            this.items = this.clients;
            this.totalRows = this.items.length;
            this.fetchCustomers();
        },

        computed: {
        },

        methods: {
            fetchCustomers() {
                if (! this.businessId) {
                    return;
                }

                this.loading = true;
                axios.get(`/business/quickbooks/${this.businessId}/customers`)
                    .then( ({ data }) => {
                        this.customers = data;
                        this.loading = false;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },

            refreshCustomers() {
                if (! this.businessId) {
                    return;
                }
                this.busy = true;

                let form = new Form({});
                form.post(`/business/quickbooks/${this.businessId}/customers/sync`)
                    .then( ({ data }) => {
                        this.customers = data.data;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    });
            },

            save() {
                if (! this.businessId) {
                    return;
                }

                this.busy = true;
                let form = new Form({ clients: this.items });
                form.patch(`/business/quickbooks/${this.businessId}/customers`)
                    .then( ({ data }) => {
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    });
            },

            createCustomer(item) {
                if (! this.businessId) {
                    return;
                }

                this.busy = true;
                let form = new Form({ client_id: item.id });
                form.post(`/business/quickbooks/${this.businessId}/customer`)
                    .then( ({ data }) => {
                        let client = this.items.find(x => x.id === data.data.client.id);
                        client.quickbooks_customer_id = data.data.client.quickbooks_customer_id;
                        this.customers = data.data.customers;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.busy = false;
                    });
            },
        },

        watch: {
            businessId(newValue, oldValue) {
                this.fetchCustomers();
            }
        },
    }
</script>

<style>

</style>
