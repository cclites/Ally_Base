<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
                        <business-location-form-group
                            v-model="businesses"
                            :label="null"
                            class="mr-1 mt-1"
                            :allow-all="true"
                        />

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
                        <b-form-select
                            id="invoiceType"
                            name="invoiceType"
                            v-model="invoiceType"
                            class="mt-1"
                        >
                            <option value="">All Invoices</option>
                            <option value="unpaid">Unpaid Invoices</option>
                            <option value="paid">Paid Invoices</option>
                            <option value="overpaid">Overpaid Invoices</option>
                        </b-form-select>
                        &nbsp;<br /><b-button type="submit" variant="info" class="mt-1" :disabled="loaded === 0">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6" class="text-right">
                <a href="/business/reports/offline-ar-aging" target="_blank">View Aging Report</a>
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
                    <b-btn variant="success" class="mr-2" @click="showPaymentModal(row.item)" v-if="row.item.balance != 0">Apply Payment</b-btn>
                </template>
            </b-table>
        </div>
        <b-modal id="applyPaymentModal"
                 :title="`Apply Payment to Offline Invoice #${selectedInvoice.name}`"
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

            <b-form-group label="Payment Description">
                <b-form-select
                        name="type"
                        v-model="form.description"
                        class="mt-1"
                        :disabled="form.busy"
                >
                    <option value="payment_applied">Payment Applied</option>
                    <option value="partial_payment_applied">Partial Payment Applied</option>
                    <option value="overpayment">Overpayment/Surplus</option>
                    <option value="write_off">Write Off/Uncollectable</option>
                    <option value="denial">Denial</option>
                    <option value="supplier_contribution">Supplier Contribution</option>
                    <option value="interest">Interest</option>
                    <option value="discount">Discount</option>
                </b-form-select>
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
            <b-form-group label="Payment Amount Towards Invoice">
                <b-form-input
                    name="amount"
                    type="number"
                    v-model="form.amount"
                    step="0.01"
                    required
                    :disabled="form.busy || payFullBalance"
                />
                <input-help :form="form" field="amount" text="" />
            </b-form-group>
            <b-form-group label="Notes">
                <b-form-textarea
                        id="notes"
                        name="notes"
                        :rows="4"
                        v-model="form.notes"
                ></b-form-textarea>
            </b-form-group>
            <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" v-model="payFullBalance" @change="updateFullBalance()" />
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Pay Full Balance</span>
            </label>
            <div slot="modal-footer">
                <b-btn variant="default" @click="cancelPayment()" :disabled="form.busy">Cancel</b-btn>
                <b-btn variant="info" @click="applyPayment()" :disabled="form.busy">Apply Payment</b-btn>
            </div>
        </b-modal>

        <confirm-modal title="Offline Transmission" ref="confirmManualTransmission" yesButton="Okay">
            <p>Based on the transmission type for this Invoice, this will assume you have sent in via E-Mail/Fax.</p>
        </confirm-modal>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../components/business/BusinessLocationFormGroup';
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import Constants from '../../mixins/Constants';

    export default {
        components: { BusinessLocationFormGroup },
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
                        key: 'amount',
                        label: 'Inv Total',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'balance',
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
                paymentModal: false,
                businesses: '',
                form: new Form({
                    type: '',
                    payment_date: moment().format('MM/DD/YYYY'),
                    amount: 0.00,
                    reference: '',
                    description: 'payment_applied',
                    notes: '',
                }),
                selectedInvoice: {},
                busy: false,
                transmittingId: null,
                selectedTransmissionMethod: '',
                payFullBalance: false,
            }
        },

        mounted() {
            this.loadClients();
        },

        computed: {
            filteredItems() {
                return this.items.map(item => {
                    return item;
                });
            },
        },

        methods: {
            async loadClients() {
                this.clients = [];
                this.loadingClients = true;
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
                this.loadingClients = false;
            },

            showPaymentModal(invoice) {
                this.payFullBalance = false;
                this.selectedInvoice = invoice;
                this.paymentModal = true;
            },

            cancelPayment() {
                this.paymentModal = false;
                this.form.reset(true);
            },

            applyPayment() {
                if (! confirm('Are you sure you wish to apply payment to this invoice?')) {
                    return ;
                }

                this.form.busy = true;
                this.form.post(`/business/offline-invoice-ar/${this.selectedInvoice.id}/pay`)
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
                let url = `/business/offline-invoice-ar?json=1&businesses=${this.businesses}&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}`;
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

            updateFullBalance() {
                if (this.payFullBalance) {
                    this.form.amount = this.selectedInvoice.balance;
                }
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
