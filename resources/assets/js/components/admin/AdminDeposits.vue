<template>
    <b-card title="Admin Pending Deposits">
        <b-form-group>
            <b-form-select v-model="chainId">
                <option value="">--Select a Chain--</option>
                <option v-for="chain in chains" :value="chain.id">{{ chain.name }}</option>
            </b-form-select>
            <b-btn variant="primary" v-if="chainLoaded" @click="generateInvoices()">Generate Invoices (1st)</b-btn>
            <b-btn variant="info" v-if="chainLoaded && invoices.length > 0" @click="deposit()">Process Deposits (2nd)</b-btn>
        </b-form-group>

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
                                <li v-for="invoice in row.item.invoices">
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
                        <tr v-for="error in invoiceErrors">
                            <td>{{ error.recipient }}</td>
                            <td>{{ error.exception }}</td>
                            <td>{{ error.message }}</td>
                        </tr>
                    </table>
                </div>
            </div>

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
                this.invoices = response.data.data;
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