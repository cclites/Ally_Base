<template>
    <b-card header="Invoice Summary by Client Report"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-alert show variant="info">This report shows invoice or claim totals summarized by client.  The date range on this report is for the invoice date (not date of service).  If you select Claim Amounts, this will only include invoices that have transmitted claims.</b-alert>
        <b-row>
            <b-col lg="12">
                <b-form inline class="mb-4">
                    <business-location-form-group
                        v-model="filters.businesses"
                        :label="null"
                        class="mr-1 mt-1"
                        :allow-all="false"
                    />
                    <date-picker
                        v-model="filters.start_date"
                        placeholder="Start Date"
                        class="mt-1"
                    />
                        &nbsp;to&nbsp;
                    <date-picker
                        v-model="filters.end_date"
                        placeholder="End Date"
                        class="mr-1 mt-1"
                    />

                    <b-form-select
                        v-model="filters.mode"
                        class="mr-1 mt-1"
                    >
                        <option value="invoice">Show Invoice Amounts</option>
                        <option value="claim">Show Claim Amounts</option>
                    </b-form-select>

                    <payer-dropdown v-model="filters.payer_id" class="mr-1 mt-1" empty-text="-- All Payers --" />

                    <client-type-dropdown v-model="filters.client_type" class="mr-1 mt-1" empty-text="-- All Client Types --" />

                    <b-form-select v-model="filters.client_id" class="mr-1 mt-1" :disabled="loadingClients">
                        <option v-if="loadingClients" selected value="">Loading Clients...</option>
                        <option v-else value="">-- All Clients --</option>
                        <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                        </option>
                    </b-form-select>

                    <b-form-checkbox v-model="filters.inactive" :value="1" :unchecked-value="0" class="mr-1 mt-1">
                        Show Inactive Clients
                    </b-form-checkbox>

                    <b-btn variant="info" class="mt-1" :disabled="filters.busy" @click.prevent="fetch()">Generate</b-btn>
                </b-form>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col lg="6">
            </b-col>
            <b-col lg="6" class="d-flex">
                <b-button @click="download()" v-if="!filters.busy && !!items" variant="success" class="ml-auto">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </b-button>
            </b-col>
        </b-row>

        <loading-card v-if="filters.busy" />
        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
                :empty-text="emptyText"
            >
                <template slot="expand" scope="row">
                    <b-btn variant="secondary" size="sm" @click.stop="row.toggleDetails">
                        <i v-if="row.detailsShowing" class="fa fa-caret-down" />
                        <i v-else class="fa fa-caret-right" />
                    </b-btn>
                </template>
                <template slot="row-details" scope="row">
                        <!---------- SUB TABLE --------------->
                        <b-table bordered striped show-empty
                            :items="row.item.invoices"
                            :fields="subFields"
                            sort-by="date"
                        >
                            <template slot="invoice_name" scope="row">
                                <a :href="`/business/client/invoices/${row.item.invoice_id}`" target="_blank">#{{ row.item.invoice_name }}</a>
                            </template>
                        </b-table>
                      <!---------- /END SUB TABLE --------------->
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../BusinessLocationFormGroup';
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                items: [],
                sortBy: 'client_name',
                sortDesc: false,
                filter: '',
                clients: [],
                fields: {
                    expand: { label: ' ', sortable: false, },
                    client_name: { label: 'Client', sortable: true },
                    invoice_count: { label: 'No of Invoices', sortable: true },
                    hours: { sortable: true, formatter: x => this.numberFormat(x) },
                    hourly_charges: { sortable: true, formatter: x => this.moneyFormat(x) },
                    total_charges: { sortable: true, formatter: x => this.moneyFormat(x) },
                },
                subFields: {
                    invoice_name: { sortable: true, label: 'Invoice' },
                    invoice_date: { sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    payer_name: { sortable: true, label: 'Payer' },
                    date_range: { sortable: true, label: 'Dates of Service' },
                    hours: { sortable: true, formatter: x => this.numberFormat(x) },
                    hourly_charges: { sortable: true, formatter: x => this.moneyFormat(x) },
                    total_charges: { sortable: true, formatter: x => this.moneyFormat(x) },
                },
                filters: new Form({
                    mode: 'invoice',
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    businesses: '',
                    client_type: '',
                    payer_id: '',
                    client_id: '',
                    inactive: 0,
                    json: 1,
                }),
                loadingClients: false,
            }
        },

        computed: {
            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            }
        },

        methods: {
            async fetch() {
                this.filters.get(`/business/reports/invoice-summary-by-client`)
                    .then( ({ data }) => {
                        this.items = data.results;
                    })
                    .catch(() => {
                        this.items = [];
                    });
            },

            download() {
                window.location = this.filters.toQueryString('/business/reports/invoice-summary-by-client?export=1');
            },

            /**
             * Fetch client list for the dropdown filter.
             * @returns {Promise<void>}
             */
            async fetchClients() {
                this.filters.client_id = '';
                this.loadingClients = true;
                this.clients = [];
                await axios.get(`/business/dropdown/clients?inactive=${this.filters.inactive}&client_type=${this.filters.client_type}&payer_id=${this.filters.payer_id}&businesses=${this.filters.businesses}`)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(() => {
                        this.clients = [];
                    })
                    .finally(() => {
                        this.loadingClients = false;
                    });
            },
        },

        async mounted() {
            await this.fetchClients();
        },

        watch: {
            'filters.businesses'(newValue, oldValue) {
                this.fetchClients();
            },
            'filters.client_type'(newValue, oldValue) {
                this.fetchClients();
            },
            'filters.payer_id'(newValue, oldValue) {
                this.fetchClients();
            },
            'filters.inactive'(newValue, oldValue) {
                this.fetchClients();
            },
        },
    }
</script>
