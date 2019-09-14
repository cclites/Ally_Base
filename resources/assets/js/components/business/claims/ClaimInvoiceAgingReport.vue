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

                    <b-form-select v-model="form.client_id" class="mr-1 mt-1" :disabled="loading">
                        <option value="">-- All Clients --</option>
                        <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                        </option>
                    </b-form-select>

                    <b-form-select v-model="form.payer_id" class="mr-1 mt-1" :disabled="loading">
                        <option value="">-- All Payers --</option>
                        <option value="0">(Client)</option>
                        <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                        </option>
                    </b-form-select>

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
                                    <a :href="`/business/claims/${row.item.claim_id}`" target="_blank">{{ row.item.claim_name }}</a>
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

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsNumbers, FormatsDates],

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
                payers: [],
                form: new Form({
                    businesses: '',
                    client_id: '',
                    payer_id: '',
                    json: 1,
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'client_name',
                sortDesc: false,
                fields: {
                    claim_name: { sortable: true, label: "Claim" },
                    invoice_name: { sortable: true, label: "Invoice" },
                    client_name: { sortable: true, label: "Client" },
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

            async fetchPayers() {
                this.payers = [];
                await axios.get('/business/payers?json=1')
                    .then( ({ data }) => {
                        if (Array.isArray(data)) {
                            this.payers = data;
                        }
                    })
                    .catch(() => {});
            },

            async loadClients() {
                this.clients = [];
                await axios.get('/business/clients?json=1')
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(() => {});
            },

            download() {
                window.location = this.form.toQueryString('/business/reports/claims/ar-aging?export=1');
            },
        },

        async mounted() {
            this.loading = true;
            await this.loadClients();
            await this.fetchPayers();
            this.loading = false;
        },
    }
</script>

<style scoped>
    tfoot tr th strong {
        margin-right: 12px;
    }
</style>
