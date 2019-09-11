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
            ${{ amountAvailable }}
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
            <b-row v-if="applications['interest']">
                <b-col md="3">
                    <b-form-group label="Interest" label-for="interest">
                        <b-form-input
                            v-model="applications['interest'].amount_applied"
                            id="interest"
                            name="interest"
                            type="number"
                            step="0.01"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="interest" text="The amount to apply towards interest."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>

            <div class="table-responsive claims-table">
                <b-table bordered striped hover show-empty
                    :items="claims"
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
                    <template slot="selected" scope="row">
                        <b-form-checkbox v-model="applications[row.item.id].selected" @change="selectMaster(row.item)" />
                    </template>
                    <template slot="id" scope="row">
                        <a :href="`/business/claims/${row.item.id}/edit`" target="_blank">{{ row.item.name }}</a>
                    </template>
                    <template slot="client_invoice_id" scope="row">
                        <a :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice.name }}</a>
                    </template>
                    <template slot="amount_applied" scope="row">
                        <div class="d-flex">
                        <b-form-input
                            class="mr-1"
                            v-model="applications[row.item.id].amount_applied"
                            name="amount_applied"
                            type="number"
                            step="0.01"
                            :disabled="true"
                        />
                        <b-select name="application_type"
                            v-model="applications[row.item.id].application_type"
                            :options="claimRemitPaymentTypeOptions"
                            :disabled="form.busy || !applications[row.item.id].selected"
                            @change="(val) => changeMasterType(row.item, val)"
                        >
                            <template slot="first">
                                <option value="">-- Select Type --</option>
                            </template>
                        </b-select>
                        </div>
                    </template>
                    <template slot="row-details" scope="row">
                    <b-card>
                        <!---------- SUB TABLE --------------->
                        <b-table bordered striped show-empty
                          :items="row.item.items"
                          :fields="subFields"
                        >
                        <template slot="selected" scope="row">
                            <b-form-checkbox v-model="applications[row.item.claim_invoice_id+'_'+row.item.id].selected"
                                :disabled="applications[row.item.claim_invoice_id].selected"
                                @change="selectSub(row.item)"/>
                        </template>
                        <template slot="start_time" scope="row">
                            <span v-if="row.item.start_time">
                                {{ formatTimeFromUTC(row.item.start_time) }} - {{ formatTimeFromUTC(row.item.end_time) }}
                            </span>
                            <span v-else>-</span>
                        </template>
                        <template slot="amount_applied" scope="row">
                            <div class="d-flex">
                                <b-form-input
                                    class="mr-1"
                                    v-model="applications[row.item.claim_invoice_id+'_'+row.item.id].amount_applied"
                                    name="amount_applied"
                                    type="number"
                                    step="0.01"
                                    :disabled="form.busy || applications[row.item.claim_invoice_id].selected"
                                    @change="x => subAmountChanged(row.item, x)"
                                />
                                <b-select name="application_type"
                                    v-model="applications[row.item.claim_invoice_id+'_'+row.item.id].application_type"
                                    :options="claimRemitPaymentTypeOptions"
                                    :disabled="form.busy || applications[row.item.claim_invoice_id].selected"
                                    @change="x => subTypeChanged(row.item, x)"
                                >
                                    <template slot="first">
                                        <option value="">-- Select Type --</option>
                                    </template>
                                </b-select>
                            </div>
                        </template>
                      </b-table>
                      <!---------- /END SUB TABLE --------------->
                    </b-card>
                    </template>
                </b-table>
            </div>
            <div>
                <b-btn variant="info" @click="submit()">Apply Remit to Claim(s)</b-btn>
            </div>
        </div>
        <div v-if="isScrolling" id="floating-amount">
            <strong>Available to Apply: </strong>
            ${{ amountAvailable }}
        </div>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import Constants from "../../../mixins/Constants";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import { mapGetters } from 'vuex';
    import { Decimal } from 'decimal.js';

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
            }),

            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            },

            amountAvailable() {
                console.log('amountAvailable triggered');
                let amount = new Decimal(this.remit.amount_available);
                return this.numberFormat(amount.sub(this.amountApplied).toFixed(2));
            },

            amountApplied() {
                console.log('amountApplied triggered');
                return Object.values(this.applications)
                    // .filter(item => {
                    //     // Filter out the master claim records.
                    //     return item.is_interest || !!item.claim_invoice_item_id;
                    // })
                    .reduce((carry, item) => {
                        if (item.amount_applied == '' || isNaN(item.amount_applied)) {
                            return carry;
                        }
                        return carry.add(new Decimal(item.amount_applied));
                    }, new Decimal(0.00));
            },

            submit() {
                this.form.post(`/business/claim-remit-applications/${this.remit.id}`)
                    .then( ({ data }) => {

                    })
                    .catch(() => {

                    });
            },
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
                isScrolling: false,
                applications: {},

                // Form data
                form: new Form({
                    applications: {},
                }),

                // Table data
                claims: [],
                filter: '',
                sortBy: 'client_invoice_date',
                sortDesc: false,
                fields: {
                    expand: { label: ' ', sortable: false, },
                    selected: { label: ' ', sortable: false, },
                    client_invoice_id: { label: 'Invoice #', sortable: true },
                    id: { label: 'Claim #', sortable: true },
                    client_invoice_date: { label: 'Invoice Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    client: { sortable: true, formatter: x => x.name },
                    payer: { sortable: true, formatter: x => x ? x.name : '-' },
                    amount: { label: 'Claim Total', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Claim Balance', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount to Apply', sortable: false },
                },
                subFields: {
                    selected: { label: ' ', sortable: false, },
                    type: { label: 'Type', sortable: true },
                    summary: { label: 'Summary', sortable: true },
                    date: { label: 'Service Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    start_time: { label: 'Time', sortable: true },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount to Apply', sortable: false },
                },
            }
        },

        methods: {
            /**
             * Initialize the form from the current claims objects.
             */
            initApplications() {
                this.applications = {
                    interest: {
                        selected: false,
                        claim_remit_id: this.remit.id,
                        claim_invoice_id: null,
                        claim_invoice_item_id: null,
                        application_type: this.CLAIM_REMIT_PAYMENT_TYPES.INTEREST,
                        amount_applied: '',
                        is_interest: true,
                    },
                };

                this.claims.forEach(claim => {
                    this.applications[claim.id] = {
                        selected: false,
                        claim_remit_id: this.remit.id,
                        claim_invoice_id: claim.id,
                        claim_invoice_item_id: null, // <- this creates an invalid item to get posted
                        application_type: '',
                        amount_applied: '',
                        is_interest: false,
                    };

                    claim.items.forEach(item => {
                        this.applications[claim.id+'_'+item.id] = {
                            selected: false,
                            claim_remit_id: this.remit.id,
                            claim_invoice_id: claim.id,
                            claim_invoice_item_id: item.id,
                            application_type: '',
                            amount_applied: '',
                            is_interest: false,
                        };
                    });
                })
            },

            selectSub(claimItem) {
                console.log('change select for claim item: ', claimItem);

                let claimId = claimItem.claim_invoice_id;
                if (this.applications[claimId+'_'+claimItem.id].selected) {
                    if (this.applications[claimId+'_'+claimItem.id].amount_applied == '') {
                        this.applications[claimId+'_'+claimItem.id].amount_applied = '0.00';
                    }
                } else {
                    this.applications[claimId+'_'+claimItem.id].amount_applied = '';
                }

                this.forceRowUpdate(claimId);
            },

            /**
             * Handle change of claim item amount applied.
             *
             * @param {Object} claimItem
             * @param {number} value
             */
            subAmountChanged(claimItem, value) {
                console.log('sub item amount changed:', claimItem, value);

                let claimId = claimItem.claim_invoice_id;
                if (isNaN(value) || value == '') {
                    // Clear the value / selection if invalid value.
                    this.applications[claimId+'_'+claimItem.id].selected = false;
                    this.applications[claimId+'_'+claimItem.id].amount_applied = '';
                } else {
                    // Make sure item is selected.
                    this.applications[claimId+'_'+claimItem.id].selected = true;
                }

                this.forceRowUpdate(claimId);
            },

            subTypeChanged(claimItem, value) {
                console.log('sub item type changed:', claimItem, value);

                let claimId = claimItem.claim_invoice_id;
                if (value == '') {
                    return;
                }

                // Make sure item is selected.
                this.applications[claimId+'_'+claimItem.id].selected = true;

                // Make sure there is a numeric amount set.
                if (this.applications[claimId+'_'+claimItem.id].amount_applied == '') {
                    this.applications[claimId+'_'+claimItem.id].amount_applied = '0.00';
                }

                this.forceRowUpdate(claimId);
            },

            /**
             * Force the BootstrapVue table to update the claim row.
             *
             * @param {number} claimId
             */
            forceRowUpdate(claimId) {
                // This was implemented because BootstrapVue was having issues knowing
                // that it should update the table rows because we are not modifying the
                // row items, we are modifying the applications object.  Doing this after
                // the nextTick() will ensure the row triggers an update after we have
                // updated any values prior to this method call.
                this.$nextTick(x => {
                    let index = this.claims.findIndex(x => x.id == claimId);
                    // Set the claim item to itself to force and update but not change values.
                    this.$set(this.claims, index, this.claims[index]);
                });
            },

            /**
             * Handle toggle of selection to master items.
             *
             * @param claim Object
             */
            selectMaster(claim) {
                console.log('change select for claim: ', claim, this.applications[claim.id].selected);
                if (this.applications[claim.id].selected) {
                    // When the claim record is selected, all sub items
                    // should be set to the full amount and disabled.
                    claim.items.forEach(item => {
                        this.applications[claim.id+'_'+item.id].amount_applied = item.amount_due;
                        this.applications[claim.id+'_'+item.id].application_type = '';
                        this.applications[claim.id+'_'+item.id].selected = true;
                    });
                    this.applications[claim.id].amount_applied = claim.amount_due;
                    // Force view of details (sub-items)
                    this.$set(claim, '_showDetails', true);
                } else {
                    // When the claim record is un-selected, we should clear
                    // all progress from it and it's sub items.
                    claim.items.forEach(item => {
                        this.applications[claim.id+'_'+item.id].amount_applied = '';
                        this.applications[claim.id+'_'+item.id].application_type = '';
                        this.applications[claim.id+'_'+item.id].selected = false;
                    });
                    this.applications[claim.id].amount_applied = '';
                    this.applications[claim.id].application_type = '';
                }

                this.forceRowUpdate(claim.id);
            },

            /**
             * Handle change to application_type for master items.
             *
             * @param claim Object
             * @param value String
             */
            changeMasterType(claim, value) {
                console.log('changed application type', value);
                if (! this.applications[claim.id].selected) {
                    return;
                }

                claim.items.forEach(item => {
                    this.applications[claim.id+'_'+item.id].application_type = value;
                });

                this.forceRowUpdate(claim.id);
            },

            fetch() {
                this.filters.get(`/business/claims`)
                    .then( ({ data }) => {
                        this.claims = data.data;
                    })
                    .catch(() => {
                        this.claims = [];
                    })
                    .finally(() => {
                        this.initApplications();
                    });
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

            /**
             * Handle tracking of window scroll event
             */
            handleScroll () {
              this.isScrolling = window.scrollY > 0;
            },
        },

        async mounted() {
            await this.fetchPayers();
            await this.fetchClients();
            this.fetch();
        },

        created() {
            this.$store.commit('claims/setRemit', this.init.remit);
            window.addEventListener('scroll', this.handleScroll);
        },

        destroyed() {
            window.removeEventListener('scroll', this.handleScroll);
        },
    }
</script>

<style>
    .claims-table input.form-control { max-width: 135px; }
    #floating-amount {
        position: fixed;
        right: 25px;
        bottom: 25px;
        display: block;
        background-color: #fff;
        padding: 1rem;
        text-align: center;
        font-size: 24px;
        border: 1px solid darkgrey;
        z-index: 2;
    }
</style>