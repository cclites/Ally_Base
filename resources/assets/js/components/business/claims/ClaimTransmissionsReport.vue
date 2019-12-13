<template>
    <b-row>
        <b-col>
            <b-card header="Claim Transmissions Report"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <b-alert show variant="info">
                    This report shows total amounts grouped by payer for claims transmitted during the selected date range.
                </b-alert>
                <div class="form-inline mb-3">
                    <business-location-form-group
                        v-model="form.businesses"
                        :label="null"
                        class="mr-1 mt-1"
                        :allow-all="true"
                    />

                    <date-picker
                        v-model="form.start_date"
                        placeholder="Start Date"
                        class="mt-1"
                    />
                        &nbsp;to&nbsp;
                    <date-picker
                        v-model="form.end_date"
                        placeholder="End Date"
                        class="mr-1 mt-1"
                    />

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

                    <b-button @click="download()" v-if="!!items" variant="success" class="mr-1 mt-1">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </b-button>
                </div>

                <b-row>
                    <b-col>
                        <loading-card v-if="busy" />

                        <div v-else class="table-responsive">
                            <b-table bordered striped hover show-empty
                                class="fit-more"
                                :busy="busy"
                                :items="items"
                                :fields="fields"
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

                                <template slot="FOOT_payer" scope="row">
                                    &nbsp;
                                </template>
                                <template slot="FOOT_amount" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(total_amount) }}
                                </template>
                                <template slot="FOOT_amount_due" scope="row">
                                    <strong>Total:</strong>{{ moneyFormat(total_due) }}
                                </template>
                            </b-table>
                        </div>
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
                loadingClients: false,
                form: new Form({
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    businesses: '',
                    client_id: '',
                    client_type: '',
                    inactive: 1,
                    json: 1,
                }),
                busy: false,
                sortBy: 'payer_name',
                sortDesc: false,
                fields: {
                    payer_name: { sortable: true, label: 'Payer' },
                    amount: { sortable: true, label: 'Total Amount Transmitted', formatter: x => this.moneyFormat(x) },
                    amount_due: { sortable: true, label: 'Total Amount Due from Transmissions', formatter: x => this.moneyFormat(x) },
                },
                items: [],
                total_amount: 0.00,
                total_due: 0.00,
                hasRun: false,
                footClone: true,
            }
        },

        methods: {
            fetch() {
                this.busy = true;
                this.form.get('/business/reports/claims/transmissions')
                    .then( ({ data }) => {
                        this.items = data.results;
                        this.total_amount = data.total_amount;
                        this.total_due = data.total_due;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            download() {
                window.location = this.form.toQueryString('/business/reports/claims/transmissions?export=1');
            },

            /**
             * Fetch client list for the dropdown filter.
             * @returns {Promise<void>}
             */
            async fetchClients() {
                this.form.client_id = '';
                this.loadingClients = true;
                this.clients = [];
                await axios.get(`/business/dropdown/clients?inactive=${this.form.inactive}&client_type=${this.form.client_type}&businesses=${this.form.businesses}`)
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
