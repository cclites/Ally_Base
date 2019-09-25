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

                    <b-form-select v-model="form.client_id" class="mr-1 mt-1" :disabled="loading">
                        <option value="">-- All Clients --</option>
                        <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                        </option>
                    </b-form-select>

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
                form: new Form({
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    businesses: '',
                    client_id: '',
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

            async loadClients() {
                this.clients = [];
                await axios.get('/business/clients?json=1')
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(() => {});
            },

            download() {
                window.location = this.form.toQueryString('/business/reports/claims/transmissions?export=1');
            },
        },

        async mounted() {
            this.loading = true;
            await this.loadClients();
            this.loading = false;
        },
    }
</script>

<style scoped>
    tfoot tr th strong {
        margin-right: 12px;
    }
</style>
