<template>
    <b-row>
        <b-col>
            <b-card header="Claims AR Aging Report"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <div class="form-inline mb-3">
                    <business-location-form-group
                        v-model="form.businesses"
                        :label="null"
                        class="mr-1 mt-1"
                        :allow-all="true"
                    />

                    <payer-dropdown v-model="form.payer_id" class="mr-1 mt-1" empty-text="-- All Payers --" :disabled="loading" />

                    <client-type-dropdown v-model="form.client_type" class="mr-1 mt-1" empty-text="-- All Client Types --" />

                    <b-form-select v-model="form.client_id" class="mr-1 mt-1" :disabled="loadingClients">
                        <option v-if="loadingClients" selected value="">Loading Clients...</option>
                        <option v-else value="">-- All Clients --</option>
                        <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                        </option>
                    </b-form-select>

                    <b-form-checkbox v-model="form.inactive" :value="1" :unchecked-value="0" class="mr-1 mt-1">
                        Show Inactive Clients
                    </b-form-checkbox>

                    <b-button @click="fetch()" variant="info" :disabled="busy" class="mr-1 mt-1">
                        <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                        Generate Report
                    </b-button>

                    <b-button @click="download()" v-if="totalRows" variant="success" class="mr-1 mt-1">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </b-button>
                </div>

                <b-row>
                    <b-col>
                        <loading-card v-if="busy" />

                        <div v-else class="table-responsive">
                            <b-table bordered striped hover show-empty
                                :busy="busy"
                                :items="items"
                                :fields="fields"
                                :current-page="currentPage"
                                :per-page="perPage"
                                :sort-by.sync="sortBy"
                                :sort-desc.sync="sortDesc"
                                :empty-text="emptyText"
                                :footClone="footClone"
                            >
                                <template slot="claim_name" scope="row">
                                    <a :href="`/business/claims/${row.item.claim_id}/print`" target="_blank">#{{ row.item.claim_name }}</a>
                                    <span v-if="row.item.has_notes">
                                        <a :href="`/business/claim-adjustments/${row.item.claim_id}`" target="_blank"><i class="fa fa-comment text-warning ml-1" /></a>
                                    </span>
                                </template>
                                <template slot="invoice_name" scope="row">
                                    <a :href="`/business/client/invoices/${row.item.invoice_id}`" target="_blank">{{ row.item.invoice_name }}</a>
                                </template>
                                <template slot="client_name" scope="row">
                                    <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                                </template>

                                <template slot="FOOT_claim_name" scope="row">
                                    &nbsp;
                                </template>
                                <template slot="FOOT_invoice_name" scope="row">
                                    &nbsp;
                                </template>
                                <template slot="FOOT_client_name" scope="row">
                                    &nbsp;
                                </template>
                                <template slot="FOOT_payer" scope="row">
                                    &nbsp;
                                </template>
                                <template slot="FOOT_current" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(totals.current) }}
                                </template>
                                <template slot="FOOT_period_30_45" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(totals.period_30_45) }}
                                </template>
                                <template slot="FOOT_period_46_60" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(totals.period_46_60) }}
                                </template>
                                <template slot="FOOT_period_61_75" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(totals.period_61_75) }}
                                </template>
                                <template slot="FOOT_period_75_plus" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(totals.period_75_plus) }}
                                </template>

                            </b-table>
                        </div>
                        <b-row>
                            <b-col lg="6">
                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                            </b-col>
                            <b-col lg="6" class="text-right">
                                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import Constants from '../../../mixins/Constants';

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsNumbers, FormatsDates, Constants],

        computed: {
            emptyText() {
                if (! this.hasRun) {
                    return 'Press Generate Report';
                }
                return 'No matching records available.';
            },
        },

        data() {
            return {
                loading: false,
                clients: [],
                form: new Form({
                    businesses: '',
                    client_id: '',
                    payer_id: '',
                    client_type: '',
                    inactive: 0,
                    json: 1,
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: {
                    claim_name: { sortable: true, label: "Claim", class: 'text-nowrap' },
                    invoice_name: { sortable: true, label: "Invoice", class: 'text-nowrap' },
                    client_name: { sortable: true, label: "Client", class: 'text-nowrap' },
                    payer: {sortable: true, label: "Payers"},
                    current: { sortable: true, label: 'Current', formatter: x => this.moneyFormat(x) },
                    period_30_45: { sortable: true, label: '30-45', formatter: x => this.moneyFormat(x) },
                    period_46_60: { sortable: true, label: '46-60', formatter: x => this.moneyFormat(x) },
                    period_61_75: { sortable: true, label: '61-75', formatter: x => this.moneyFormat(x) },
                    period_75_plus: { sortable: true, label: '75+', formatter: x => this.moneyFormat(x) },
                },
                items: [],
                hasRun: false,
                footClone: true,
                totals: {},
                loadingClients: false,
            }
        },

        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/claims/ar-aging')
                    .then( ({ data }) => {
                        this.items = data.results;
                        this.totals = data.totals;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            download() {
                window.location = this.form.toQueryString('/business/reports/claims/ar-aging?export=1');
            },

            /**
             * Fetch client list for the dropdown filter.
             * @returns {Promise<void>}
             */
            async fetchClients() {
                this.form.client_id = '';
                this.loadingClients = true;
                this.clients = [];
                await axios.get(`/business/dropdown/clients?inactive=${this.form.inactive}&client_type=${this.form.client_type}&payer_id=${this.form.payer_id}&businesses=${this.form.businesses}`)
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
            this.loading = true;
            await this.fetchClients();
            this.loading = false;
        },

        watch: {
            'form.businesses'(newValue, oldValue) {
                this.fetchClients();
            },
            'form.client_type'(newValue, oldValue) {
                this.fetchClients();
            },
            'form.payer_id'(newValue, oldValue) {
                this.fetchClients();
            },
            'form.inactive'(newValue, oldValue) {
                this.fetchClients();
            },
        },
    }
</script>

<style scoped>
    tfoot tr th strong {
        margin-right: 12px;
    }
</style>
