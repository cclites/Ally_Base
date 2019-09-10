<template>
    <b-card header="Apply Remit"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="max-width: 900px">
                <tbody>
                    <tr>
                        <td><strong>ID #</strong></td>
                        <td>{{ remit.id }}</td>
                        <td><strong>Type</strong></td>
                        <td>{{ resolveOption(remit.payment_type, this.claimRemitTypeOptions) }} #{{ remit.id }}</td>
                    </tr><tr>
                        <td><strong>Office Location</strong></td>
                        <td>{{ remit.office_location }}</td>
                        <td><strong>Payer</strong></td>
                        <td>{{ remit.payer_name ? remit.payer_name : 'N/A' }}</td>
                    </tr><tr>
                        <td><strong>Notes</strong></td>
                        <td colspan="3">{{ remit.notes }}</td>
                    </tr><tr>
                        <td><strong>Date</strong></td>
                        <td>{{ formatDateFromUTC(remit.date) }}</td>
                        <td><strong>Reference #</strong></td>
                        <td>{{ remit.reference }}</td>
                    </tr><tr>
                        <td><strong>Total Amount</strong></td>
                        <td>{{ moneyFormat(remit.amount) }}</td>
                        <td><strong>Amount Applied</strong></td>
                        <td>{{ moneyFormat(remit.amount_applied) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2>
            <strong>Available to Apply: </strong>
            {{ moneyFormat(remit.amount_available) }}
        </h2>

        <b-form inline class="mb-4">
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
            <business-location-form-group
                v-model="filters.businesses"
                :label="null"
                class="mr-1 mt-1"
                :allow-all="true"
            />
            <b-form-select v-model="filters.payer_id" class="mr-1 mt-1">
                <option value="">-- Any Payer --</option>
                <option value="0">(Client)</option>
                <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                </option>
            </b-form-select>
            <b-form-select v-model="filters.client_id" class="mr-1 mt-1">
                <option value="">-- All Clients --</option>
                <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                </option>
            </b-form-select>
            <b-form-select v-model="filters.claim_status" class="mr-1 mt-1">
                <option value="">-- All Claims --</option>
                <option value="unpaid">Unpaid Claims</option>
            </b-form-select>
            <b-btn variant="info" class="mr-1 mt-1" @click.prevent="fetch()" :disabled="filters.busy">Generate</b-btn>
        </b-form>

        <loading-card v-if="filters.busy" />
        <div v-else>
            <b-row>
                <b-col md="3">
                    <b-form-group label="Interest" label-for="interest">
                        <b-form-input
                            v-model="form.interest"
                            id="interest"
                            name="interest"
                            type="text"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="interest" text="The amount to apply towards interest."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="claims"
                    :fields="fields"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :filter="filter"
                    :empty-text="emptyText"
                >
                    <template slot="id" scope="row">
                        <a :href="`/business/claims/${row.item.id}/edit`" target="_blank">{{ row.item.name }}</a>
                    </template>
                    <template slot="client_invoice_id" scope="row">
                        <a :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice.name }}</a>
                    </template>
                    <template slot="apply_amount" scope="row">
                        <b-form-input
                            v-model="form.amount_to_apply"
                            id="amount_to_apply"
                            name="amount_to_apply"
                            type="text"
                            :disabled="form.busy"
                        />
                    </template>
                    <template slot="apply_type" scope="row">
                        <b-select name="application_type" id="application_type" v-model="form.application_type" :options="claimRemitPaymentTypeOptions">
                            <template slot="first">
                                <option value="">-- Type --</option>
                            </template>
                        </b-select>
                    </template>
                    <template slot="row-details" scope="row">
                      <b-card>
                          <b-table bordered striped show-empty
                              :items="row.item.items"
                              :fields="subFields"
                          >
                              <template slot="start_time" scope="row">
                                  <span v-if="row.item.start_time">
                                    {{ formatTimeFromUTC(row.item.start_time) }} - {{ formatTimeFromUTC(row.item.end_time) }}
                                  </span>
                                  <span v-else>-</span>
                              </template>
                              <template slot="apply_amount" scope="row">
                                  <b-form-input
                                      v-model="form.amount_to_apply"
                                      id="amount_to_apply"
                                      name="amount_to_apply"
                                      type="text"
                                      :disabled="form.busy"
                                  />
                              </template>
                              <template slot="apply_type" scope="row">
                                  <b-select name="application_type" id="application_type" v-model="form.application_type" :options="claimRemitPaymentTypeOptions">
                                      <template slot="first">
                                          <option value="">-- Type --</option>
                                      </template>
                                  </b-select>
                              </template>
                          </b-table>
                      </b-card>
                    </template>
                </b-table>
            </div>

<!--            <div class="table-responsive mb-3">-->
<!--                <table class="table table-bordered table-striped">-->
<!--                    <thead>-->
<!--                        <tr>-->
<!--                            <th>Invoice #</th>-->
<!--                            <th>Claim #</th>-->
<!--                            <th>Invoice Date</th>-->
<!--                            <th>Client</th>-->
<!--                            <th>Payer</th>-->
<!--                            <th>Claim Total</th>-->
<!--                            <th>Claim Balance</th>-->
<!--                            <th>Amount to Apply</th>-->
<!--                            <th>Type</th>-->
<!--                        </tr>-->
<!--                    </thead>-->
<!--                    <tbody>-->
<!--                        <tr v-for="claim in claims" :key="claim.id">-->
<!--                            <td><a :href="`/business/client/invoices/${claim.client_invoice_id}`" target="_blank">{{ claim.client_invoice.name }}</a></td>-->
<!--                            <td><a :href="`/business/claims/${claim.id}/edit`" target="_blank">{{ claim.name }}</a></td>-->
<!--                            <td>{{ formatDateFromUTC(claim.client_invoice.date) }}</td>-->
<!--                            <td>{{ claim.client_first_name + ' ' + claim.client_last_name }}</td>-->
<!--                            <td>{{ claim.payer ? claim.payer.name : '-' }}</td>-->
<!--                            <td>{{ moneyFormat(claim.amount) }}</td>-->
<!--                            <td>{{ moneyFormat(claim.amount_due) }}</td>-->
<!--                            <td>-->
<!--                                <b-form-input-->
<!--                                    v-model="form.amount_to_apply"-->
<!--                                    id="amount_to_apply"-->
<!--                                    name="amount_to_apply"-->
<!--                                    type="text"-->
<!--                                    :disabled="form.busy"-->
<!--                                />-->
<!--                            </td>-->
<!--                            <td>-->
<!--                                <b-select name="application_type" id="application_type" v-model="form.application_type" :options="claimRemitPaymentTypeOptions">-->
<!--                                    <template slot="first">-->
<!--                                        <option value="">&#45;&#45; Type &#45;&#45;</option>-->
<!--                                    </template>-->
<!--                                </b-select>-->
<!--                            </td>-->
<!--                        </tr>-->
<!--                    </tbody>-->
<!--                </table>-->
<!--            </div>-->
            <div>
                <b-btn variant="info">Apply Remit</b-btn>
            </div>
        </div>
<!--        <div v-else class="table-responsive">-->
<!--            <b-table bordered striped hover show-empty-->
<!--                :items="remits"-->
<!--                :fields="fields"-->
<!--                :sort-by.sync="sortBy"-->
<!--                :sort-desc.sync="sortDesc"-->
<!--                :filter="filter"-->
<!--                :empty-text="emptyText"-->
<!--            >-->
<!--                <template slot="actions" scope="row">-->
<!--                    <b-btn variant="success" size="sm" :href="`/business/claim-remits/${row.item.id}`">Apply</b-btn>-->
<!--                    <b-btn variant="secondary" size="sm" @click="edit(row.item)"><i class="fa fa-edit" /></b-btn>-->
<!--                </template>-->
<!--            </b-table>-->
<!--        </div>-->

    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import Constants from "../../../mixins/Constants";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import { mapGetters } from 'vuex';
    export default {
        mixins: [ FormatsDates, FormatsStrings, Constants, FormatsNumbers ],
        components: { BusinessLocationFormGroup },
        props: {
            init: {
                type: Object,
                required: true,
                default: () => {},
            },
        },

        computed: {
            ...mapGetters({
                remit: 'claims/remit',
                claims: 'claims/claims',
            }),

            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            }
        },

        data() {
            return {
                // Filter data
                filters: new Form({
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    businesses: '',
                    payer_id: '',
                    client_id: '',
                    claim_status: 'unpaid',
                    json: 1,
                }),
                payers: [],
                clients: [],

                // Form data
                form: new Form({
                    interest: 0.00,
                }),

                // Table data
                filter: '',
                sortBy: 'client_invoice_date',
                sortDesc: false,
                fields: {
                    client_invoice_id: { label: 'Invoice #', sortable: true },
                    id: { label: 'Claim #', sortable: true },
                    client_invoice_date: { label: 'Invoice Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    client: { sortable: true, formatter: x => x.name },
                    payer: { sortable: true, formatter: x => x ? x.name : '-' },
                    amount: { label: 'Claim Total', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Claim Balance', sortable: true, formatter: x => this.moneyFormat(x) },
                    apply_amount: { label: 'Amount to Apply', sortable: true },
                    apply_type: { label: 'Type', sortable: true },
                },
                subFields: {
                    type: { label: 'Type', sortable: true },
                    summary: { label: 'Summary', sortable: true },
                    date: { label: 'Service Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    start_time: { label: 'Time', sortable: true },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { sortable: true, formatter: x => this.moneyFormat(x) },
                    apply_amount: { label: 'Amount to Apply', sortable: true },
                    apply_type: { label: 'Type', sortable: true },
                },
            }
        },

        methods: {
            fetch() {
                this.filters.get(`/business/claims`)
                    .then( ({ data }) => {
                        this.$store.commit('claims/setClaims', data.data.map(x => {
                            x['_showDetails'] = true;
                            return x;
                        }));
                    })
                    .catch(() => {
                        this.$store.commit('claims/setClaims', []);
                    })
            },

            async fetchClients() {
                await axios.get(`/business/dropdown/clients?businesses=${this.filters.businesses}`)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(() => {
                        this.clients = [];
                    });
            },

            async fetchPayers() {
                await axios.get(`/business/dropdown/payers`)
                    .then( ({ data }) => {
                        this.payers = data;
                    })
                    .catch(() => {
                        this.payers = [];
                    });
            },

        },

        async mounted() {
            await this.fetchPayers();
            await this.fetchClients();
        },

        created() {
            this.$store.commit('claims/setRemit', this.init.remit);
        },
    }
</script>
