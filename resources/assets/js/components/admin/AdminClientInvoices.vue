<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">

                        <b-form-group label="Business Chains" class="mb-2 mr-2">
                            <b-form-select
                                    id="chain_id"
                                    name="chain_id"
                                    v-model="chain_id"
                            >
                                <option value="">All Business Chains</option>
                                <option v-for="chain in chains" :value="chain.id">{{ chain.name }}</option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="Start Date" class="mb-2 mr-2">
                            <date-picker
                                    v-model="start_date"
                            >
                            </date-picker>
                        </b-form-group>

                        <b-form-group label="End Date" class="mb-2 mr-2">
                            <date-picker
                                    v-model="end_date"
                            >
                            </date-picker>
                        </b-form-group>

                        <b-form-group label="Status" class="mb-2 mr-2">
                            <b-form-select
                                    id="paid"
                                    name="paid"
                                    v-model="paid"
                            >
                                <option value="">All Invoices</option>
                                <option :value="0">Unpaid Invoices</option>
                                <option :value="1">Paid Invoices</option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="Clients" class="mb-2 mr-2">
                            <b-form-select
                                    name="client_id"
                                    v-model="client_id"
                            >
                                <option value="">All Clients</option>
                                <option v-for="row in clients" :value="row.id" :key="row.id" :text="row.name">{{ row.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>

                        <b-button type="submit" variant="info" :disabled="loaded === 0" class="mt-3 mr-2">Generate Report</b-button>
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
            </b-table>
        </div>
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
                clients: '',
                client_id: '',
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
                        key: 'client',
                        formatter: (val) => val ? val.name : '-',
                        sortable: true,
                    },
                    {
                        key: 'chain_name',
                        label: "Business Chain",
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        formatter: (val) => val ? val.name : '-',
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
                    }
                ]
            }
        },

        mounted() {

        },

        computed: {

        },

        methods: {

            async loadItems() {
                this.loaded = 0;
                let url = '/admin/invoices/clients?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date +
                    '&chain_id=' + this.chain_id + '&paid=' + this.paid + '&client_id=' + this.client_id;
                const response = await axios.get(url);
                this.items = response.data.data.map(item => {
                    item.chain_name = (item.client && item.client.business && item.client.business.chain) ? item.client.business.chain.name : "";

                    let flags = [];
                    if (item.client_on_hold) flags.push("On Hold");
                    if (!item.payer_payment_type) flags.push("No Payment Method");

                    item.flags = flags.join(' | ');
                    return item;
                });
                this.loaded = 1;
            },

            getClients(){


                axios.get('/business/dropdown/clients-for-chain?chain=' + this.chain_id)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },

            invoiceUrl(invoice, view="") {
                return `/admin/invoices/clients/${invoice.id}/${view}`;
            },
        },

        watch: {
            async 'chain_id'(newValue, oldValue) {
                this.clients = [];
                this.client = '';
                if (newValue !== ''){
                    this.getClients();
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
