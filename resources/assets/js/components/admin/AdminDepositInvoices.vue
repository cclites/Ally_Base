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
                        <b-form-select
                                id="paid"
                                name="paid"
                                v-model="paid"
                        >
                            <option value="">All Invoices</option>
                            <option :value="0">Unpaid Invoices</option>
                            <option :value="1">Paid Invoices</option>
                        </b-form-select>
                        <b-form-select
                                id="chain_id"
                                name="chain_id"
                                v-model="chain_id"
                        >
                            <option value="">All Business Chains</option>
                            <option v-for="chain in chains" :value="chain.id" :key="chain.id">{{ chain.name }}</option>
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
                     :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :filter="filter"
            >
                <template slot="name" scope="row">
                    <a :href="invoiceUrl(row.item)" target="_blank">{{ row.value }}</a>
                </template>
                <template slot="status" scope="row">
                    <span v-if="row.item.amount == row.item.amount_paid">Paid</span>
                    <span v-else>Unpaid</span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="selectedInvoice = row.item">Edit Notes</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal title="Edit Invoice Notes" v-model="showModal" size="lg">
            <b-container fluid>

                    <b-form-group label="Notes" label-for="notes">
                        <b-form-textarea :rows="4" v-model="form.notes"></b-form-textarea>
                        <input-help :form="form" field="notes" text=""></input-help>
                    </b-form-group>

                    <div v-if=" selectedInvoice && selectedInvoice.caregiver_on_hold ">

                        <p>On Hold Notes:</p>
                        <p>{{ selectedInvoice.payment_hold_notes }}</p>
                    </div>
            </b-container>

            <div slot="modal-footer">
                <b-button variant="info" @click="updateSelectedInvoice()">Save</b-button>
                <b-btn variant="default" @click="selectedInvoice = null">Cancel</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {

        mixins: [FormatsDates, FormatsNumbers],

        props: {
            'chains': {
                type: Array,
            }
        },

        data() {
            return {
                sortBy: 'created_at',
                sortDesc: true,
                filter: null,
                loaded: -1,
                start_date: '01/01/2018',
                end_date: moment().format('MM/DD/YYYY'),
                chain_id: "",
                paid: 0,
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
                        key: 'recipient',
                        sortable: true,
                    },
                    {
                        key: 'chain_name',
                        label: "Business Chain",
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
                        key: 'notes',
                    },
                    {
                        key: 'flags',
                    },
                    'actions'
                ],
                form : new Form({}),
                selectedInvoice : null
            }
        },

        mounted() {

        },

        computed: {
            showModal: {
                get() {
                    return !!this.selectedInvoice;
                },
                set(val) {

                    if (!val) this.selectedInvoice = null;
                }
            },
        },

        methods: {

            async loadItems() {
                this.loaded = 0;
                let url = '/admin/invoices/deposits?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date +
                    '&chain_id=' + this.chain_id + '&paid=' + this.paid;
                const response = await axios.get(url);
                this.items = response.data.data.map(item => {
                    let chain;
                    if (item.caregiver) {
                        chain = item.caregiver.business_chains.length ? item.caregiver.business_chains[0] : null;
                    }
                    if (item.business) {
                        chain = item.business.chain;
                    }
                    item.chain_name = chain ? chain.name : "";

                    let flags = [];
                    if (item.caregiver_on_hold) flags.push("On Hold");
                    if (item.business_on_hold) flags.push("On Hold");
                    if (item.no_bank_account) flags.push("No Bank Account");

                    item.flags = flags.join(' | ');
                    return item;
                });
                this.loaded = 1;
            },

            invoiceUrl(invoice, view="") {
                const type = (invoice.invoice_type === 'business_invoices') ?  'businesses' : 'caregivers';
                return `/admin/invoices/${type}/${invoice.invoice_id}/${view}`;
            },

            async updateSelectedInvoice() {

                await this.form.patch(`/admin/invoices/deposits/${this.selectedInvoice.invoice_id}/${this.selectedInvoice.invoice_type}`);
                this.selectedInvoice.notes = this.form.notes;
                this.selectedInvoice = null;
            }
        },

        watch: {

            selectedInvoice( val ) {

                this.form = new Form({

                    notes: val ? val.notes : ""
                });
            }
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
