<template>
    <b-card title="Admin Pending Charges">
        <b-form-group>
            <b-form-select v-model="chainId">
                <option value="">--Select a Chain--</option>
                <option v-for="chain in chains" :value="chain.id">{{ chain.name }} ({{ chain.id }})</option>
            </b-form-select>
            <b-btn variant="primary" v-if="chainLoaded" @click="generateInvoices()">Generate Invoices (1st)</b-btn>
            <b-btn variant="info" v-if="chainLoaded && invoices.length > 0" @click="charge()">Process Payments (2nd)</b-btn>
        </b-form-group>

        <loading-card v-if="chainId && !chainLoaded"></loading-card>

        <div v-if="chainLoaded">

            <div v-if="payments.length > 0">
                <h4>Payments</h4>
                <div class="table-responsive">
                    <b-table bordered striped hover show-empty
                             :items="payments"
                             :fields="paymentFields">
                        <template slot="success" scope="row">
                            <i class="fa fa-check" style="color: green" v-if="row.value"></i>
                            <i class="fa fa-times" style="color: darkred" v-else></i>
                        </template>
                        <template slot="invoices" scope="row">
                            <ul>
                                <li v-for="invoice in row.item.invoices">
                                    <a :href="invoiceUrl(invoice.id)" target="_blank">{{ invoice.name }}</a>: {{ invoice.pivot ? invoice.pivot.amount_applied : '' }}
                                </li>
                            </ul>
                        </template>
                        <template slot="actions" scope="row">
                            <a :href="paymentUrl(row.item.payment_id)" target="_blank" v-if="row.item.payment_id">View Statement</a>
                        </template>
                    </b-table>
                </div>
            </div>

            <div v-if="invoiceErrors.length > 0">
                <h4 style="color: darkred;">Errors</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th>Client</th>
                            <th>Error Type</th>
                            <th>Message</th>
                        </tr>
                        <tr v-for="error in invoiceErrors">
                            <td>{{ error.client ? error.client.name : 'Unknown' }}</td>
                            <td>{{ error.exception }}</td>
                            <td>{{ error.message }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="table table-bordered" v-if="invoices.length">
                <thead>
                <th>Total Invoices</th>
                <th>Total Amount</th>
                <th>CC Amount</th>
                <th>ACH Amount</th>
                </thead>
                <tbody>
                <td>{{ invoices.length }}</td>
                <td>{{ numberFormat(totalAmountDue) }}</td>
                <td>{{ numberFormat(totalCCDue) }}</td>
                <td>{{ numberFormat(totalACHDue) }}</td>
                </tbody>
            </table>

            <table class="table table-bordered" v-if="invoices.length">
                <thead>
                <th>Estimates</th>
                <th>Reg Total</th>
                <th>CG Total</th>
                <th>Client Pre-Ally</th>
                <th>Ally Total</th>
                <th>Client Total</th>
                </thead>
                <tbody>
                <td></td>
                <td>{{ numberFormat(totalEstimates.provider_total) }}</td>
                <td>{{ numberFormat(totalEstimates.caregiver_total) }}</td>
                <td>{{ numberFormat(totalAmountDue - totalEstimates.ally_total) }}</td>
                <td>{{ numberFormat(totalEstimates.ally_total) }}</td>
                <td>{{ numberFormat(totalAmountDue) }}</td>
                </tbody>
            </table>

            <h4>Invoices</h4>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="invoices"
                         :fields="invoiceFields"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc">
                    <template slot="name" scope="row">
                        <a :href="invoiceUrl(row.item.id)" target="_blank">{{ row.value }}</a>
                        <i v-if="row.item.was_split" title="Invoice has payment with split charges" class="fa fa-code-fork text-danger ml-2"></i>
                        <i v-if="row.item.has_partial_payment" title="Invoice has partial payment" class="fa fa-money text-danger ml-2"></i>
                    </template>
                    <template slot="status" scope="row">
                        <span v-if="row.item.amount == row.item.amount_paid">Paid</span>
                        <span v-else>Unpaid</span>
                        <span v-if="row.item.client_on_hold">- On Hold</span>
                    </template>
                    <template slot="actions" scope="row">
                        <b-button @click="uninvoice(row.item.id)" variant="danger" v-if="!row.item.has_claim">Uninvoice</b-button>
                        <b-button v-if="! row.item.client.user.payment_hold" @click="addHold(row.item)" variant="danger">Add Hold</b-button>
                        <b-button v-if="row.item.client.user.payment_hold" @click="removeHold(row.item)" variant="primary">Remove Hold</b-button>
                    </template>
                </b-table>
            </div>
        </div>

    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {
        name: "AdminPayments",

        mixins: [FormatsDates, FormatsNumbers],

        props: {
            chains: Array,
        },

        data() {
            return {
                chainId: "",
                chainLoaded: false,
                invoices: [],
                invoiceErrors: [],
                invoiceFields: [
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
                        key: 'location',
                        label: 'Office Location',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        formatter: (val, key, item) => `${val ? val.name : '-'} (${item.payer_payment_type})`,
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        formatter: (val) => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'amount_paid',
                        formatter: (val) => this.numberFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'status',
                    },
                    {
                        key: 'actions',
                    }
                ],
                sortBy: null,
                sortDesc: false,
                payments: [],
                paymentFields: [
                    {
                        key: 'payment_method',
                    },
                    {
                        key: 'amount',
                        formatter: (val) => this.numberFormat(val),
                    },
                    {
                        key: 'success',
                    },
                    {
                        key: 'exception',
                    },
                    {
                        key: 'error_message',
                        label: 'Error Message'
                    },
                    {
                        key: 'invoices',
                    },
                    {
                        key: 'actions',
                    },
                ],
            }
        },

        computed: {
            totalAmountDue() {
                return this.invoices.reduce((carry, invoice) => carry + parseFloat(invoice.amount) - parseFloat(invoice.amount_paid), 0);
            },
            totalCCDue() {
                return this.invoices.reduce((carry, invoice) => carry + (['CC', 'AMEX'].includes(invoice.payer_payment_type) ? parseFloat(invoice.amount) - parseFloat(invoice.amount_paid) : 0), 0);
            },
            totalACHDue() {
                return this.invoices.reduce((carry, invoice) => carry + (['ACH', 'ACH-P'].includes(invoice.payer_payment_type) ? parseFloat(invoice.amount) - parseFloat(invoice.amount_paid) : 0), 0);
            },
            totalEstimates() {
                return this.invoices.reduce((carry, invoice) => ({
                    caregiver_total: parseFloat(carry.caregiver_total || 0) + parseFloat(invoice.estimates ? invoice.estimates.caregiver_total : 0),
                    ally_total: parseFloat(carry.ally_total || 0) + parseFloat(invoice.estimates ? invoice.estimates.ally_total : 0),
                    provider_total: parseFloat(carry.provider_total || 0) + parseFloat(invoice.estimates ? invoice.estimates.provider_total : 0),
                }), {});
            },
        },

        methods: {
            addHold(invoice) {
                let form = new Form();
                form.submit('post', '/admin/users/' + invoice.client_id + '/hold')
                    .then(response => {
                        invoice.client.user.payment_hold = true;
                    })
                    .catch(e => {});
            },

            removeHold(invoice) {
                let form = new Form();
                form.submit('delete', '/admin/users/' + invoice.client_id + '/hold')
                    .then(response => {
                        invoice.client.user.payment_hold = null;
                    })
                    .catch(e => {});
            },

            async generateInvoices() {
                if (this.chainLoaded && this.chainId) {
                    let form = new Form({chain_id: this.chainId});
                    this.chainLoaded = false;
                    const response = await form.post(`/admin/invoices/clients`);
                    this.invoiceErrors = response.data.data ? response.data.data.errors : [];
                    this.loadInvoices();
                }
            },

            async loadInvoices() {
                this.chainLoaded = false;
                const response = await axios.get(`/admin/invoices/clients?json=1&paid=0&chain_id=${this.chainId}`);
                this.invoices = response.data.data;
                this.chainLoaded = true;
            },

            async charge() {
                if (!confirm('Are you sure you want to charge all the open invoices in this business chain?')) {
                    return;
                }
                if (this.chainLoaded && this.chainId) {
                    try {
                        this.chainLoaded = false;
                        const form = new Form({});
                        const response = await form.post(`/admin/charges/charge/${this.chainId}`);
                        this.payments = response.data.data;
                        await this.updatePaidInvoices();
                        this.chainLoaded = true;
                    }
                    catch (e) {
                        this.chainLoaded = false;
                    }
                }
            },

            async updatePaidInvoices()
            {
                const response = await axios.get(`/admin/invoices/clients?json=1&paid=0&chain_id=${this.chainId}`);
                let data = response.data.data;
                this.invoices = this.invoices.map(invoice => {
                    if (!data.find(item => item.id === invoice.id)) {
                        invoice.amount_paid = invoice.amount;
                    }
                    return invoice;
                })
            },

            invoiceUrl(id, view="") {
                return `/admin/invoices/clients/${id}/${view}`;
            },


            paymentUrl(id, view="") {
                return `/admin/charges/${id}/${view}`;
            },

            async uninvoice(id) {
                let form = new Form({});
                await form.submit("delete", "/admin/invoices/clients/" + id);
                this.invoices = this.invoices.filter(i => i.id != id);
            }
        },

        watch: {
            chainId(val) {
                if (val) this.loadInvoices();
            }
        }
    }
</script>

<style scoped>

</style>