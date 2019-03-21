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
                        <b-form-select v-model="clientFilter" class="mr-1 mb-1">
                            <option v-if="loadingClients" selected>Loading...</option>
                            <option v-else value="">-- Select a Client --</option>
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                            </option>
                        </b-form-select>
                        <b-form-select v-model="payerFilter" class="mr-1 mb-1">
                            <option v-if="loadingPayers" selected>Loading...</option>
                            <option v-else value="">-- Select a Payer --</option>
                            <option value="0">(Client)</option>
                            <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                            </option>
                        </b-form-select>
                        <b-form-select
                            id="paid"
                            name="paid"
                            v-model="paid"
                        >
                            <option value="">All Invoices</option>
                            <option value="0">Unpaid Invoices</option>
                            <option value="1">Paid Invoices</option>
                            <option value="2">Has Claim</option>
                            <option value="3">Does Not Have Claim</option>
                        </b-form-select>
                        &nbsp;<br /><b-button type="submit" variant="info" :disabled="loaded === 0">Generate Report</b-button>
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
                <template slot="actions" scope="row">
                    <b-btn variant="success" class="mr-2" @click="showPaymentModal(row.item)">Apply Payment</b-btn>
                    <b-btn variant="secondary" class="mr-2" :href="invoiceUrl(row.item)">View Invoice</b-btn>
                    <b-btn variant="primary" class="mr-2" :disabled="true">Transmit Claim</b-btn>
                </template>
            </b-table>
        </div>
        <b-modal id="applyPaymentModal" :title="`Apply Payment to Invoice #${selectedInvoice.name}`" v-model="paymentModal">
            <b-form-group label="Payment Date">
                <date-picker v-model="form.payment_date" placeholder="Payment Date"></date-picker>
                <input-help :form="form" field="payment_date" text="" />
            </b-form-group>
            <b-form-group label="Payment Type">
                <b-form-input
                    name="payment_type"
                    type="text"
                    v-model="form.payment_type"
                    max="255"
                />
                <input-help :form="form" field="payment_type" text="" />
            </b-form-group>
            <b-form-group label="Reference #">
                <b-form-input
                    name="reference_no"
                    type="text"
                    v-model="form.reference_no"
                    max="255"
                />
                <input-help :form="form" field="reference_no" text="" />
            </b-form-group>
            <b-form-group label="Invoice Balance">
                <b-form-input
                    name="balance"
                    v-model="selectedInvoice.balance"
                    :disabled="true"
                />
                <input-help :form="form" field="balance" text="" />
            </b-form-group>
            <b-form-group label="Payment Amount Towards Invoice">
                <b-form-input
                    name="amount"
                    type="number"
                    v-model="form.amount"
                    step="0.01"
                    required
                />
                <input-help :form="form" field="amount" text="" />
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
                    name="claim_amount"
                    type="number"
                    v-model="form.claim_amount"
                    step="0.01"
                    required
                />
                <input-help :form="form" field="claim_amount" text="" />
            </b-form-group>
            <div slot="modal-footer">
                <b-btn variant="default" @click="paymentModal=false">Cancel</b-btn>
                <b-btn variant="info" @click="applyPayment()">Apply Payment</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import {Decimal} from 'decimal.js';

    export default {

        mixins: [FormatsDates, FormatsNumbers],

        props: {
        },

        data() {
            return {
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                paid: "",
                items: [],
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
                        formatter: (val) => this.formatDateFromUTC(val),
                    },
                    {
                        key: 'name',
                        label: 'Invoice #',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        formatter: (val) => val.name,
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        formatter: (val) => val.name,
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Inv Total',
                        formatter: (val) => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'balance',
                        label: 'Invoice Balance',
                        formatter: (val) => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'claim_status',
                        formatter: (x) => 'Not Sent',
                    },
                    {
                        key: 'claim_balance',
                        label: 'Claim Balance',
                        formatter: (val) => this.numberFormat(val),
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
                    payment_type: '',
                    payment_date: moment().format('MM/DD/YYYY'),
                    amount: 0.00,
                    claim_amount: 0.00,
                    reference_no: '',
                }),
                selectedInvoice: {},
            }
        },

        mounted() {
            this.loadClients();
            this.fetchPayers();
        },

        computed: {
            filteredItems() {
                return this.items.map(item => {
                    let amount = new Decimal(item.amount);
                    let amount_paid = new Decimal(item.amount_paid);
                    item.balance = amount.minus(amount_paid).toFixed(2);
                    item.claim_balance = (item.claim_balance).toFixed(2);
                    return item;
                });
            },
        },

        methods: {
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

            applyPayment() {
                if (! confirm('Are you sure you wish to apply payment to this invoice?')) {
                    return ;
                }

                alerts.addMessage('success', `Payment successfully applied to invoice #${this.selectedInvoice.name}`);
                this.paymentModal = false;
            },

            async loadItems() {
                this.loaded = 0;
                let url = `/business/claims-ar?json=1&start_date=${this.start_date}&end_date=${this.end_date}&paid=${this.paid}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
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
