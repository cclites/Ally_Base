<template>
    <div>
        <b-row>
            <b-col>
                <business-location-form-group
                    v-model="business_id"
                    label="Choose Office Location"
                />
            </b-col>
        </b-row>
        <b-card header="Quickbooks Invoice Queue"
            header-text-variant="white"
            header-bg-variant="info"
        >
            <b-row v-if="loadingConnection">
                <b-col>
                    <loading-card />
                </b-col>
            </b-row>
            <div v-else> <!-- Not initializing -->
                <b-row v-if="! hasConnectionConfigured">
                    <b-col>
                        <b-alert show variant="danger">
                            You must configure your <a href="/business/quickbooks">Quickbooks Connection</a> for this office location before you can transfer invoices.
                        </b-alert>
                    </b-col>
                </b-row>
                <div v-else> <!-- Connection is configured -->
                    <b-row>
                        <b-col>
                            <b-alert show variant="info">This search does not include Offline Client invoices.</b-alert>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col class="mb-4">
                            <b-form inline @submit.prevent="fetch()">
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

                                <payer-dropdown v-model="payerFilter" class="mr-1 mt-1" empty-text="-- Select a Payer --" :show-offline="true" />

                                &nbsp;<br /><b-button type="submit" variant="info" class="mt-1" :disabled="loadingTable">Generate</b-button>
                            </b-form>
                        </b-col>
                    </b-row>
                    <b-row v-if="waitingToGenerate">
                        <b-col>
                            <b-card class="text-center text-muted">
                                Select filters and press Generate
                            </b-card>
                        </b-col>
                    </b-row>
                    <b-row v-else>
                        <b-col>
                            <b-form-input v-model="filter" placeholder="Type to Search" class="mb-2" />

                            <div class="table-responsive">
                                <b-table bordered striped hover show-empty
                                    :items="items"
                                    :fields="fields"
                                    :sort-by.sync="sortBy"
                                    :sort-desc.sync="sortDesc"
                                    :filter="filter"
                                    :busy="loadingTable"
                                >
                                    <template slot="name" scope="row">
                                        <a :href="`/business/client/invoices/${row.item.id}`" target="_blank">{{ row.item.name }}</a>
                                    </template>
                                    <template slot="client_name" scope="row">
                                        <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                                    </template>
                                    <template slot="actions" scope="row">
                                        <div v-if="connection.is_desktop">
                                            <!-- DESKTOP ACTIONS -->
                                            <div v-if="row.item.status == QUICKBOOKS_INVOICE_STATUS.READY">
                                                <b-btn variant="primary" class="mr-2" @click="enqueue(row.item)" :disabled="busy">
                                                    <i v-if="row.item.id === queueingId" class="fa fa-spin fa-spinner"></i>
                                                    <span>Add to Queue</span>
                                                </b-btn>
                                            </div>
                                            <div v-else-if="row.item.status == QUICKBOOKS_INVOICE_STATUS.QUEUED">
                                                <b-btn variant="warning" class="mr-2" @click="dequeue(row.item)" :disabled="busy">
                                                    <i v-if="row.item.id === dequeueingId" class="fa fa-spin fa-spinner"></i>
                                                    <span>Remove from Queue</span>
                                                </b-btn>
                                            </div>
                                            <div v-else-if="row.item.status == QUICKBOOKS_INVOICE_STATUS.ERRORED">
                                                <b-btn variant="secondary" class="mr-2" @click="viewErrors(row.item)" :disabled="busy">
                                                    <span>View Errors</span>
                                                </b-btn>
                                                <b-btn variant="primary" class="mr-2" @click="enqueue(row.item)" :disabled="busy">
                                                    <i v-if="row.item.id === queueingId" class="fa fa-spin fa-spinner"></i>
                                                    <span>Re-Queue</span>
                                                </b-btn>
                                            </div>
                                            <div v-else-if="row.item.status == QUICKBOOKS_INVOICE_STATUS.PROCESSING">
                                                <b-btn variant="primary" class="mr-2" @click="enqueue(row.item)" :disabled="busy">
                                                    <i v-if="row.item.id === queueingId" class="fa fa-spin fa-spinner"></i>
                                                    <span>Re-Queue</span>
                                                </b-btn>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <!-- ONLINE ACTIONS -->
                                            <div v-if="row.item.status != QUICKBOOKS_INVOICE_STATUS.TRANSFERRED">
                                                <b-btn variant="primary" class="mr-2" @click="transferOnline(row.item)" :disabled="busy">
                                                    <i v-if="row.item.id === transferringId" class="fa fa-spin fa-spinner"></i>
                                                    <span>Transfer to Quickbooks</span>
                                                </b-btn>
                                            </div>
                                            <div v-else>
                                                -
                                            </div>
                                        </div>
                                    </template>
                                </b-table>
                            </div>
                        </b-col>
                    </b-row>
                </div>
            </div>
        </b-card>
        <b-modal :title="`Processing Invoice #${currentInvoice.name} Failed`" v-model="showErrorsModal" size="xl">
            <b-container fluid>
                <div><strong>Errors:</strong></div>
                <div>{{ currentInvoice.errors }}</div>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="showErrorsModal = false">Close</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import Constants from "../../../mixins/Constants";
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],
        components: { BusinessLocationFormGroup },

        data() {
            return {
                loadingConnection: true,
                loadingTable: false,
                waitingToGenerate: true,
                loadingClients: false,
                clients: [],
                clientFilter: '',
                payers: [],
                payerFilter: '',
                loadingPayers: false,
                selectedInvoice: {},
                busy: false,
                transferringId: null,
                queueingId: null,
                dequeueingId: null,
                currentInvoice: {},
                showErrorsModal: false,

                connection: {},
                business_id: null,
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                invoiceType: "",
                items: [],
                fields: [
                    {
                        key: 'date',
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
                        key: 'client_name',
                        sortable: true,
                    },
                    {
                        key: 'payer_name',
                        sortable: true,
                        formatter: x => x ? x : '(None)',
                    },
                    {
                        key: 'total',
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
                        key: 'status',
                        sortable: true,
                        formatter: (x) => x ? this.resolveOption(x, this.quickbooksInvoiceStatusOptions) : '-',
                    },
                    {
                        key: 'last_status_update',
                        label: 'Last Update',
                        formatter: x => x ? this.formatDateTimeFromUTC(x) : '-',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        label: 'Actions',
                        sortable: false,
                    },
                ],
            }
        },

        mounted() {
            if (this.business_id) {
                this.fetchConnection();
            }
        },

        computed: {
            mode() {
                if (! this.hasConnectionConfigured) {
                    return null;
                }
                return this.connection.is_desktop ? 'offline' : 'online';
            },

            hasConnectionConfigured() {
                return this.connection && this.connection.is_authenticated;
            },
        },

        methods: {
            enqueue(invoice) {
                this.busy = true;
                this.queueingId = invoice.id;
                let form = new Form({});
                form.post(`/business/quickbooks-queue/${invoice.id}/enqueue`)
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
                        this.queueingId = null;
                    });
            },

            dequeue(invoice) {
                this.busy = true;
                this.dequeueingId = invoice.id;
                let form = new Form({});
                form.post(`/business/quickbooks-queue/${invoice.id}/dequeue`)
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
                        this.dequeueingId = null;
                    });
            },

            viewErrors(invoice) {
                this.currentInvoice = invoice;
                this.showErrorsModal = true;
            },

            transferOnline(invoice) {
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

            async loadClients() {
                this.clients = [];
                this.loadingClients = true;
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
                this.loadingClients = false;
            },

            async fetch() {
                this.waitingToGenerate = false;
                this.loadingTable = true;
                let url = `/business/quickbooks-queue?json=1&businesses=${this.business_id}&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                        this.items = [];
                    })
                    .finally(() => {
                        this.loadingTable = false;
                    });
            },

            fetchConnection() {
                this.loadingConnection = true;
                axios.get(`/business/quickbooks?business_id=${this.business_id}&json=1`)
                    .then( ({ data }) => {
                        this.connection = data.connection;
                        this.clients = data.clients;
                    })
                    .catch(() => {
                        this.connect = {};
                        this.clients = [];
                    })
                    .finally(() => {
                        this.loadingConnection = false;
                    })
            },
        },

        watch: {
            business_id(newValue, oldValue) {
                if (newValue) {
                    this.fetchConnection();
                    this.fetch();
                }
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
