<template>
    <b-card title="Admin Pending Deposits">

        <b-form-group>

            <div class="d-flex align-items-center mb-2" style="flex-wrap: wrap">

                <b-form-select v-model="chainId" class="my-1 f-1" style="min-width: 430px">

                    <option value="">--Select a Chain--</option>
                    <option v-for=" ( chain, k ) in chains" :value="chain.id" :key="k">{{ chain.name }} ({{ chain.id }})</option>
                </b-form-select>

                <b-btn class="my-1 mx-0 mx-sm-2" variant="secondary" :disabled="!chainId" @click="loadInvoices()">Refresh</b-btn>
            </div>

            <b-row>

                <b-col>

                    <b-btn variant="primary" v-if="chainLoaded" @click="generateInvoices()">Generate Invoices (1st)</b-btn>
                    <b-btn variant="info" v-if="chainLoaded && invoices.length > 0" @click="deposit()">Process Deposits (2nd)</b-btn>
                </b-col>
            </b-row>
        </b-form-group>

        <b-col class="my-2 text-right">

            A limit of 2500 deposits can be returned
        </b-col>

        <loading-card v-if="chainId && !chainLoaded"></loading-card>

        <div v-if="chainLoaded">

            <div v-if="deposits.length > 0">
                <h4>Deposits</h4>
                <div class="table-responsive">
                    <b-table bordered striped hover show-empty
                             :items="deposits"
                             :fields="depositFields">
                        <template slot="success" scope="row">
                            <i class="fa fa-check" style="color: green" v-if="row.value"></i>
                            <i class="fa fa-times" style="color: darkred" v-else></i>
                        </template>
                        <template slot="invoices" scope="row">
                            <ul>
                                <li v-for="( invoice, i ) in row.item.invoices" :key="i">
                                    <a :href="invoiceUrl(invoice)" target="_blank">{{ invoice.name }}</a>: {{ invoice.pivot ? invoice.pivot.amount_applied : '' }}
                                </li>
                            </ul>
                        </template>
                        <template slot="actions" scope="row">
                            <a :href="depositUrl(row.item.deposit_id)" target="_blank" v-if="row.item.deposit_id">View Statement</a>
                        </template>
                    </b-table>
                </div>
            </div>

            <div v-if="invoiceErrors.length > 0">
                <h4 style="color: darkred;">Errors</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th>Recipient</th>
                            <th>Error Type</th>
                            <th>Message</th>
                        </tr>
                        <tr v-for="( error, e ) in invoiceErrors" :key=" e ">
                            <td>{{ error.recipient }}</td>
                            <td>{{ error.exception }}</td>
                            <td>{{ error.message }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <p v-if="invoices.length">
                <strong>There are {{ invoices.length }} invoices listed for a total amount of {{ numberFormat(totalAmountDue) }}.</strong>
            </p>

            <h4>Invoices</h4>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="invoices"
                         :fields="invoiceFields"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc">
                    <template slot="name" scope="row">
                        <a :href="invoiceUrl(row.item)" target="_blank">{{ row.value }}</a>
                    </template>
                    <template slot="status" scope="row">
                        <span v-if="row.item.amount == row.item.amount_paid">Paid</span>
                        <span v-else>Unpaid</span>
                        <span v-if="row.item.caregiver_on_hold">- On Hold</span>
                        <span v-if="row.item.business_on_hold">- On Hold</span>
                    </template>
                    <template slot="actions" scope="row">
                        <b-button @click="uninvoice(row.item)" variant="danger">Uninvoice</b-button>
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
        name: "AdminDeposits",

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
                        key: 'recipient',
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
                        key: 'flags',
                    },
                    {
                        key: 'actions',
                    }
                ],
                sortBy: null,
                sortDesc: false,
                deposits: [],
                depositFields: [
                    {
                        key: 'recipient',
                    },
                    {
                        key: 'payment_method',
                    },
                    {
                        key: 'amount',
                        formatter: (val) => this.numberFormat(val),
                    },
                    {
                        key: 'amount_due',
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
            }
        },

        methods: {
            async generateInvoices() {
                if (this.chainLoaded && this.chainId) {
                    let form = new Form({chain_id: this.chainId});
                    this.chainLoaded = false;
                    const response = await form.post(`/admin/invoices/deposits`);
                    this.invoiceErrors = response.data.data ? response.data.data.errors : [];
                    this.loadInvoices();
                }
            },

            async loadInvoices() {
                this.chainLoaded = false;
                const response = await axios.get(`/admin/invoices/deposits?json=1&paid=0&chain_id=${this.chainId}`);
                this.invoices = response.data.data.map(item => {
                    let flags = [];
                    if (item.caregiver_on_hold) flags.push("On Hold");
                    if (item.business_on_hold) flags.push("On Hold");
                    if (item.no_bank_account) flags.push("No Bank Account");

                    item.flags = flags.join(' | ');
                    return item;
                });
                this.chainLoaded = true;
            },

            async deposit() {
                if (!confirm('Are you sure you want to deposit all the open invoices in this business chain?')) {
                    return;
                }
                if (this.chainLoaded && this.chainId) {
                    try {
                        this.chainLoaded = false;
                        const form = new Form({});
                        const response = await form.post(`/admin/deposits/deposit/${this.chainId}`);
                        this.deposits = response.data.data;
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
                const response = await axios.get(`/admin/invoices/deposits?json=1&paid=0&chain_id=${this.chainId}`);
                let data = response.data.data;
                this.invoices = this.invoices.map(invoice => {
                    if (!data.find(item => item.name === invoice.name)) {
                        invoice.amount_paid = invoice.amount;
                    }
                    return invoice;
                })
            },

            invoiceUrl(invoice, view="") {
                const type = (invoice.invoice_type === 'business_invoices') ?  'businesses' : 'caregivers';
                return `/admin/invoices/${type}/${invoice.invoice_id}/${view}`;
            },


            depositUrl(id, view="") {
                return `/admin/deposits/${id}/${view}`;
            },

            async uninvoice(invoice) {
                console.log(invoice);
                const type = (invoice.invoice_type === 'business_invoices') ?  'businesses' : 'caregivers';
                let form = new Form({});
                await form.submit("delete", `/admin/invoices/${type}/${invoice.invoice_id}`);
                this.invoices = this.invoices.filter(i => invoice.invoice_id != i.invoice_id || invoice.invoice_type != i.invoice_type);
            },
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