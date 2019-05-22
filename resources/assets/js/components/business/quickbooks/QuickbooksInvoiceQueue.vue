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
                                class="mt-1"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date"
                                placeholder="End Date"
                                class="mr-1 mt-1"
                        >
                        </date-picker>
                        <b-form-select v-model="clientFilter" class="mr-1 mt-1">
                            <option v-if="loadingClients" selected>Loading...</option>
                            <option v-else value="">-- Select a Client --</option>
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                        <b-form-select v-model="payerFilter" class="mr-1 mt-1">
                            <option v-if="loadingPayers" selected>Loading...</option>
                            <option v-else value="">-- Select a Payer --</option>
                            <option value="0">(Client)</option>
                            <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </b-form-select>
                        &nbsp;<br /><b-button type="submit" variant="info" class="mt-1" :disabled="loaded === 0">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>
        <loading-card v-if="loaded == 0"></loading-card>
        <b-row v-if="loaded < 0">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>
        <div class="table-responsive" v-if="loaded > 0">
            <b-table bordered striped hover show-empty
                     :items="filteredItems"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :filter="filter"
            >
                <template slot="name" scope="row">
                    <a :href="invoiceUrl(row.item)" target="_blank">{{ row.value }}</a>
                </template>
                <template slot="client" scope="row">
                    <a :href="`/business/clients/${row.item.client.id}`">{{ row.item.client.name }}</a>
                </template>
                <template slot="actions" scope="row">
                    <div v-if="row.item.quickbooksInvoice && row.item.quickbooksInvoice.length">
                        Transferred on {{ formatDateFromUTC(row.item.quickbooksInvoice[0].created_at) }}
                    </div>
                    <b-btn v-else variant="primary" class="mr-2" @click="transfer(row.item)" :disabled="busy">
                        <i v-if="row.item.id === transferringId" class="fa fa-spin fa-spinner"></i>
                        <span>Transfer to Quickbooks</span>
                    </b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                invoiceType: "",
                items: [],
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
                        formatter: (val) => this.formatDateFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Invoice #',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        formatter: (val) => val ? val.name : 'None',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Inv Total',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'balance',
                        label: 'Invoice Balance',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        label: 'Transfer',
                        sortable: false,
                    },
                ],
                loadingClients: false,
                clients: [],
                clientFilter: '',
                payers: [],
                payerFilter: '',
                loadingPayers: false,
                selectedInvoice: {},
                busy: false,
                transferringId: null,
            }
        },

        mounted() {
            this.loadClients();
            this.fetchPayers();
        },

        computed: {
            filteredItems() {
                return this.items.map(item => {
                    return item;
                });
            },
        },

        methods: {
            transfer(invoice) {
                this.busy = true;
                this.transferringId = invoice.id;
                let form = new Form({});
                form.post(`/business/quickbooks-queue/${invoice.id}/transfer`)
                    .then( ({ data }) => {
                        // success
                        let index = this.items.findIndex(x => x.id == invoice.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data);
                        }
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.transferringId = null;
                    });
            },

            async fetchPayers() {
                this.payers = [];
                this.loadingPayers = true;
                let response = await axios.get('/business/payers?json=1');
                if (Array.isArray(response.data)) {
                    this.payers = response.data;
                } else {
                    this.payers = [];
                }
                this.loadingPayers = false;
            },

            async loadClients() {
                this.clients = [];
                this.loadingClients = true;
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
                this.loadingClients = false;
            },

            async loadItems() {
                this.loaded = 0;
                let url = `/business/quickbooks-queue?json=1&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                        this.items = [];
                    })
                    .finally(() => {
                        this.loaded = 1;
                    });
            },

            invoiceUrl(invoice, view="") {
                return `/business/client/invoices/${invoice.id}/${view}`;
            },
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
