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
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                            </option>
                        </b-form-select>
                        <b-form-select v-model="payerFilter" class="mr-1 mt-1">
                            <option v-if="loadingPayers" selected>Loading...</option>
                            <option v-else value="">-- Select a Payer --</option>
                            <option value="0">(Client)</option>
                            <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                            </option>
                        </b-form-select>
                        <b-form-select
                            id="invoiceType"
                            name="invoiceType"
                            v-model="invoiceType"
                            class="mt-1"
                        >
                            <option value="">All Invoices</option>
                            <option value="unpaid">Unpaid Invoices</option>
                            <option value="paid">Paid Invoices</option>
                            <option value="has_claim">Has Claim</option>
                            <option value="no_claim">Does Not Have Claim</option>
                            <option value="has_balance">Has Claim Balance</option>
                            <option value="no_balance">Does Not Have Claim Balance</option>
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
                    <b-btn v-if="row.item.claim" variant="success" class="mr-2" @click="showPaymentModal(row.item)">Apply Payment</b-btn>
                    <b-btn variant="secondary" class="mr-2" :href="invoiceUrl(row.item)" target="_blank">View Invoice</b-btn>
                    <b-btn v-if="!row.item.claim" variant="primary" class="mr-2" @click="transmitClaim(row.item)" :disabled="busy">
                        <i v-if="row.item.id === transmittingId" class="fa fa-spin fa-spinner"></i>
                        <span>Transmit Claim</span>
                    </b-btn>
                </template>
            </b-table>
        </div>
        <b-modal id="applyPaymentModal"
                 :title="`Apply Payment to Claim #${selectedInvoice.name}`"
                 v-model="paymentModal"
                 no-close-on-backdrop
        >
            <b-form-group label="Payment Date">
                <date-picker v-model="form.payment_date" placeholder="Payment Date" :disabled="form.busy"></date-picker>
                <input-help :form="form" field="payment_date" text="" />
            </b-form-group>
            <b-form-group label="Payment Type">
                <b-form-input
                    name="type"
                    type="text"
                    v-model="form.type"
                    max="255"
                    :disabled="form.busy"
                />
                <input-help :form="form" field="type" text="" />
            </b-form-group>
            <b-form-group label="Reference #">
                <b-form-input
                    name="reference"
                    type="text"
                    v-model="form.reference"
                    max="255"
                    :disabled="form.busy"
                />
                <input-help :form="form" field="reference" text="" />
            </b-form-group>
            <b-form-group label="Claim Balance">
                <b-form-input
                    name="claim_balance"
                    v-model="selectedInvoice.claim_balance"
                    :disabled="true"
                />
                <input-help :form="form" field="claim_balance" text="" />
            </b-form-group>
            <b-form-group label="Payment Amount Towards Claim">
                <b-form-input
                    name="amount"
                    type="number"
                    v-model="form.amount"
                    step="0.01"
                    required
                    :disabled="form.busy"
                />
                <input-help :form="form" field="amount" text="" />
            </b-form-group>
            <div slot="modal-footer">
                <b-btn variant="default" @click="cancelPayment()" :disabled="form.busy">Cancel</b-btn>
                <b-btn variant="info" @click="applyPayment()" :disabled="form.busy">Apply Payment</b-btn>
            </div>
        </b-modal>

        <confirm-modal
            title="Select Transmission Method"
            ref="confirmTransmissionMethod"
            yesButton="Transmit"
            :yes-disabled="!selectedTransmissionMethod"
        >
            <p>Private and Offline Payer types do not have a default transmission method.</p>
            <p>Please select the method would you like to use to submit this invoice.</p>
            <b-form-group label="Transmission Method" label-for="selectedTransmissionMethod" label-class="required">
                <b-select v-model="selectedTransmissionMethod">
                    <option value="">-- Select Transmission Method --</option>
                    <option value="-" disabled>Direct Transmission:</option>
                    <option :value="CLAIM_SERVICE.HHA">{{ serviceLabel(CLAIM_SERVICE.HHA) }}</option>
                    <option :value="CLAIM_SERVICE.TELLUS">{{ serviceLabel(CLAIM_SERVICE.TELLUS) }}</option>
                    <option :value="CLAIM_SERVICE.CLEARINGHOUSE">{{ serviceLabel(CLAIM_SERVICE.CLEARINGHOUSE) }}</option>
                    <option value="-" disabled>-</option>
                    <option value="-" disabled>Offline:</option>
                    <option :value="CLAIM_SERVICE.EMAIL">{{ serviceLabel(CLAIM_SERVICE.EMAIL) }}</option>
                    <option :value="CLAIM_SERVICE.FAX">{{ serviceLabel(CLAIM_SERVICE.FAX) }}</option>
                </b-select>
            </b-form-group>
        </confirm-modal>

        <confirm-modal title="Offline Transmission" ref="confirmManualTransmission" yesButton="Okay">
            <p>Based on the transmission type for this Invoice, this will assume you have sent in via E-Mail/Fax.</p>
        </confirm-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import Constants from '../../mixins/Constants';

    export default {
        mixins: [FormatsDates, FormatsNumbers, Constants],

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
                        key: 'claim_status',
                        formatter: (x) => _.capitalize(_.startCase(x)),
                        sortable: true,
                    },
                    {
                        key: 'claim_service',
                        label: 'Claim Service',
                        formatter: (x) => this.serviceLabel(x),
                        sortable: true,
                    },
                    {
                        key: 'claim_balance',
                        label: 'Claim Balance',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        sortable: false,
                    },
                ],
                loadingClients: false,
                clients: [],
                clientFilter: '',
                payers: [],
                payerFilter: '',
                loadingPayers: false,
                paymentModal: false,
                form: new Form({
                    type: '',
                    payment_date: moment().format('MM/DD/YYYY'),
                    amount: 0.00,
                    reference: '',
                }),
                selectedInvoice: {},
                busy: false,
                transmittingId: null,
                selectedTransmissionMethod: '',
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
            serviceLabel(serviceValue) {
                switch (serviceValue) {
                    case this.CLAIM_SERVICE.HHA: return 'HHAeXchange';
                    case this.CLAIM_SERVICE.TELLUS: return 'Tellus';
                    case this.CLAIM_SERVICE.CLEARINGHOUSE: return 'CareExchange LTC Clearinghouse';
                    case this.CLAIM_SERVICE.EMAIL: return 'E-Mail';
                    case this.CLAIM_SERVICE.FAX: return 'Fax';
                    default:
                        return '-';
                }
            },

            transmitClaim(invoice, skipAlert = false) {
                if (! skipAlert) {
                    if (invoice.payer && [this.PRIVATE_PAY_ID, this.OFFLINE_PAY_ID].includes(invoice.payer.id)) {
                        // offline and private pay Payer objects have no transmission method set
                        // so we allow the user to select which method they would like to use
                        this.selectedTransmissionMethod = '';
                        this.$refs.confirmTransmissionMethod.confirm(() => {
                            this.transmitClaim(invoice, true);
                        });
                        return;
                    }

                    if (invoice.payer && [this.CLAIM_SERVICE.EMAIL, this.CLAIM_SERVICE.FAX].includes(invoice.payer.transmission_method)) {
                        this.$refs.confirmManualTransmission.confirm(() => {
                            this.transmitClaim(invoice, true);
                        });
                        return;
                    }
                }

                this.busy = true;
                this.transmittingId = invoice.id;
                let form = new Form({
                    method: this.selectedTransmissionMethod,
                });
                form.post(`/business/claims-ar/${invoice.id}/transmit`)
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
                        this.transmittingId = null;
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

            showPaymentModal(invoice) {
                this.selectedInvoice = invoice;
                this.paymentModal = true;
            },

            cancelPayment() {
                this.paymentModal = false;
                this.form.reset(true);
            },

            applyPayment() {
                if (! confirm('Are you sure you wish to apply payment to this claim?')) {
                    return ;
                }

                this.form.busy = true;
                this.form.post(`/business/claims-ar/${this.selectedInvoice.id}/pay`)
                    .then( ({ data }) => {
                        console.log('payment response', data);
                        let index = this.items.findIndex(x => x.id == this.selectedInvoice.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data);
                        }
                        this.paymentModal = false;
                        this.form.reset(true);
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.form.busy = false;
                    });
            },

            async loadItems() {
                this.loaded = 0;
                let url = `/business/claims-ar?json=1&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
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
