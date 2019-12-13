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
                        <td>{{ formatDate(remit.date) }}</td>
                        <td><strong>Reference #</strong></td>
                        <td>{{ remit.reference }}</td>
                    </tr><tr>
                        <td><strong>Total Amount</strong></td>
                        <td>{{ moneyFormat(remit.amount) }}</td>
                        <td><strong>Amount Applied</strong></td>
                        <td>
                            <a :href="`/business/claim-remits/${remit.id}`" target="_blank">{{ moneyFormat(remit.amount_applied) }}</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex">
            <h2 class="f-1">
                <strong>Amount Applied: </strong>
                ${{ numberFormat(amountApplied.toFixed(2)) }}
            </h2>
            <h2 class="ml-auto">
                <strong>Available to Apply: </strong>
                ${{ numberFormat(amountAvailable.toFixed(2)) }}
            </h2>
        </div>

        <div>
            <b-form-radio-group v-model="filters.date_type">
                <b-radio value="service">Search by Date of Service</b-radio>
                <b-radio value="invoice">Search by Invoiced Date</b-radio>
            </b-form-radio-group>
        </div>

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

            <b-form-select v-model="filters.claim_type" class="mr-1 mt-1" :options="claimInvoiceTypeOptions">
                <template slot="first">
                    <option value="">-- Claim Type --</option>
                </template>
            </b-form-select>

            <b-form-select v-model="filters.claim_status" class="mr-1 mt-1">
                <option value="">-- All Claims --</option>
                <option value="unpaid">Unpaid Claims</option>
            </b-form-select>

            <business-location-form-group
                v-model="filters.businesses"
                :label="null"
                class="mr-1 mt-1"
                :allow-all="true"
            />

            <payer-dropdown v-model="filters.payer_id" class="mr-1 mt-1" empty-text="-- All Payers --" :show-offline="true" />

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

            <b-input
                v-model="filters.invoice_id"
                placeholder="Invoice #"
                class="mr-1 mt-1"
            />

            <b-btn variant="info" class="mr-1 mt-1" @click.prevent="fetch()" :disabled="filters.busy">Generate</b-btn>
        </b-form>

        <loading-card v-if="filters.busy" />
        <div v-else>
            <b-row>
                <b-col md="3">
                    <b-form-group label="Interest" label-for="interest">
                        <number-input id="interest"
                            v-model="interest"
                            name="interest"
                            :disabled="form.busy"
                        />
                        <input-help :form="form" field="interest" text="The amount to apply towards interest."></input-help>
                    </b-form-group>
                </b-col>
                <b-col md="3">
                    <b-form-group label="&nbsp;" label-for="interest">
                    <b-form-input
                        v-model="interest_note"
                        id="interest_note"
                        name="interest_note"
                        type="text"
                        :disabled="form.busy"
                        placeholder="Note..."
                        maxlength="255"
                    />
                    <input-help :form="form" field="interest" text="" />
                </b-form-group>
                </b-col>
            </b-row>

            <div class="table-responsive claims-table">
                <b-table bordered striped hover show-empty
                    class="fit-more"
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
                        <b-form-checkbox v-model="row.item.selected" @change="selectMaster(row.item)" />
                    </template>
                    <template slot="id" scope="row">
                        <a :href="`/business/claims/${row.item.id}/edit`" target="_blank">{{ row.item.name }}</a>
                    </template>
                    <template slot="payer_name" scope="row">
                        {{ row.item.payer_name }}
                    </template>
                    <template slot="client_name" scope="row">
                        <a v-if="row.item.client_id" :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                        <span v-else>(Grouped)</span>
                    </template>
                    <template slot="client_invoice_name" scope="row">
                        <a v-if="row.item.client_invoice_name" :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice_name }}</a>
                        <span v-else>-</span>
                    </template>
                    <template slot="amount_due" scope="row">
                        ${{ getMasterAmountDue(row.item) }}
                    </template>
                    <template slot="amount_applied" scope="row">
                        <div class="d-flex">
                            <number-input
                                class="mr-1"
                                v-model="row.item.amount_applied"
                                name="amount_applied"
                                :disabled="true"
                            />
                            <b-select name="adjustment_type"
                                v-model="row.item.adjustment_type"
                                :options="claimRemitAdjustmentTypeOptions"
                                :disabled="form.busy || !row.item.selected"
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
                            <b-form-checkbox v-model="row.item.selected"
                                :disabled="row.item.disabled"
                                @change="selectSub(row.item)"/>
                        </template>
                            <template slot="client_invoice_name" scope="row">
                                <a v-if="row.item.client_invoice_name" :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice_name }}</a>
                                <span v-else>-</span>
                            </template>
                        <template slot="start_time" scope="row">
                            <span v-if="row.item.start_time">
                                {{ formatTimeFromUTC(row.item.start_time) }} - {{ formatTimeFromUTC(row.item.end_time) }}
                            </span>
                            <span v-else>-</span>
                        </template>
                        <template slot="amount_applied" scope="row">
                            <div class="d-flex">
                                <number-input
                                    class="mr-1"
                                    v-model="row.item.amount_applied"
                                    name="amount_applied"
                                    :disabled="form.busy || row.item.disabled"
                                    @change="x => subAmountChanged(row.item, x)"
                                />
                                <b-select name="adjustment_type"
                                    v-model="row.item.adjustment_type"
                                    :options="claimRemitAdjustmentTypeOptions"
                                    :disabled="form.busy || row.item.disabled"
                                    @change="x => subTypeChanged(row.item, x)"
                                >
                                    <template slot="first">
                                        <option value="">-- Select Type --</option>
                                    </template>
                                </b-select>
                                <b-form-input
                                    v-model="row.item.note"
                                    placeholder="Note..."
                                    name="note"
                                    maxlength="255"
                                    type="text"
                                    :disabled="form.busy"
                                    style="max-width: none!important;"
                                />
                            </div>
                        </template>
                      </b-table>
                      <!---------- /END SUB TABLE --------------->
                    </b-card>
                    </template>
                </b-table>
            </div>
            <div>
                <b-btn variant="info" @click="submit()" :disabled="form.busy || !canSubmit">Apply Remit to Claim(s)</b-btn>
            </div>
        </div>
        <div v-if="isScrolling" id="floating-amount">
            <strong>Available to Apply: </strong>
            ${{ numberFormat(amountAvailable.toFixed(2)) }}
        </div>
        <div v-if="isScrolling" id="floating-amount-applied">
            <strong>Amount Applied: </strong>
            ${{ numberFormat(amountApplied.toFixed(2)) }}
        </div>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import LocalStorage from "../../../mixins/LocalStorage";
    import Constants from "../../../mixins/Constants";
    import { Decimal } from 'decimal.js';
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ FormatsDates, FormatsStrings, Constants, FormatsNumbers, LocalStorage ],
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

            /**
             * Get the text for an empty claims table.
             *
             * @return {string}
             */
            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            },

            /**
             * Get the total amount available to apply for the current Remit.
             *
             * @return {Decimal}
             */
            amountAvailable() {
                return this.decimalOrZero(this.remit.amount_available).sub(this.amountApplied);
            },

            /**
             * Get the total amount applied from the claims table.
             *
             * @return {Decimal}
             */
            amountApplied() {
                let total = this.claims.reduce((carry, claim) => {
                    return carry.add(
                        claim.items.reduce((itemTotal, item) => {
                            if (item.amount_applied == '' || isNaN(item.amount_applied)) {
                                return itemTotal;
                            }
                            return itemTotal.add(this.decimalOrZero(item.amount_applied));
                        }, this.decimalOrZero(0.00))
                    );
                }, this.decimalOrZero(0.00));

                return total.add(this.decimalOrZero(this.interest));
            },

            /**
             * Determines if the form can be submitted.
             *
             * @return {boolean}
             */
            canSubmit() {
                // We should always be able to submit now because there are no restrictions on amounts
                return true;
            },

            /**
             * Get the prefix for saving filters to local storage.  This is
             * based on the current remit ID.
             *
             * @return {string}
             */
            localStoragePrefix() {
                return this.remit ? 'apply_remit_' + this.remit.id : 'apply_remit_default';
            },
        },

        data() {
            return {
                // Filter data
                filters: new Form({
                    date_type: 'service',
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    businesses: '',
                    payer_id: '',
                    client_id: '',
                    claim_status: '',
                    client_type: '',
                    invoice_id: '',
                    inactive: 0,
                    claim_type: '',
                    json: 1,
                }),
                clients: [],
                loadingClients: false,
                isScrolling: false,
                interest: '',
                interest_note: '',
                form: new Form({
                    applications: [],
                }),

                // Table data
                claims: [],
                filter: '',
                sortBy: 'created_at',
                sortDesc: false,
                fields: {
                    expand: { label: ' ', sortable: false, },
                    selected: { label: ' ', sortable: false, },
                    id: { label: 'Claim #', sortable: true, thClass: 'text-nowrap' },
                    created_at: { label: 'Claim Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    client_invoice_name: { label: 'Inv #', sortable: true, tdClass: 'text-nowrap' },
                    client_invoice_date: { label: 'Inv Date', sortable: true, formatter: x => x ? this.formatDateFromUTC(x) : '-' },
                    client_name: { label: 'Client', sortable: true },
                    payer_name: { label: 'Payer', sortable: true },
                    amount: { label: 'Claim Total', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Claim Balance', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount to Apply', sortable: false },
                },
                subFields: {
                    selected: { label: ' ', sortable: false, },
                    date: { label: 'Date of Service', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    start_time: { label: 'Time', sortable: true },
                    type: { label: 'Type', sortable: true },
                    summary: { label: 'Summary', sortable: true },
                    client_invoice_name: { label: 'Inv #', sortable: true, tdClass: 'text-nowrap' },
                    client_invoice_date: { label: 'Inv Date', sortable: true, formatter: x => x ? this.formatDateFromUTC(x) : '-' },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Due', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount to Apply', sortable: false },
                },
            }
        },

        methods: {
            /**
             * Fetch transmitted claims using the filters form.
             */
            fetch() {
                this.filters.get(`/business/claims`)
                    .then( ({ data }) => {
                        this.claims = data.data.map(claim => {
                            claim.selected = false;
                            claim.adjustment_type = '';
                            claim.amount_applied = '';
                            claim.items = claim.items.map(item => {
                                item.selected = false;
                                item.disabled = false;
                                item.adjustment_type = '';
                                item.amount_applied = '';
                                item.note = '';
                                return item;
                            });
                            return claim;
                        });
                    })
                    .catch(() => {
                        this.claims = [];
                    })
                    .finally(() => {
                        this.saveFilters();
                    });
            },

            /**
             * Submit the main apply remit form.
             */
            submit() {
                this.populateFormFromTable();

                this.form.post(`/business/claim-remit-applications/${this.remit.id}`)
                    .then( ({ data }) => {
                    })
                    .catch(() => {
                    });
            },

            /**
             * Loop through the table data and update the form
             * to contain proper ClaimAdjustment objects.
             */
            populateFormFromTable() {
                this.form.applications = this.claims.map(claim => {
                    return claim.items.map(item => {
                        if (! item.selected || item.amount_applied == '') {
                            return null;
                        }

                        // Do not allow 0 if no note is present
                        if (! item.note && parseFloat(item.amount_applied) === parseFloat('0')) {
                            return null;
                        }

                        return {
                            claim_invoice_id: claim.id,
                            claim_invoice_item_id: item.id,
                            adjustment_type: item.adjustment_type,
                            note: item.note,
                            amount_applied: item.amount_applied,
                            is_interest: false,
                        };
                    })
                })
                .flat(1)
                .filter(x => x != null);

                // Add the interest field.
                if (this.interest != '' && !isNaN(this.interest)) {
                    this.form.applications.push({
                        adjustment_type: this.CLAIM_ADJUSTMENT_TYPES.INTEREST,
                        amount_applied: this.interest,
                        is_interest: true,
                        note: this.interest_note,
                    });
                }
            },

            /**
             * Handle toggle of selection to master items.
             *
             * @param {Object} claim
             */
            selectMaster(claim)  {
                if (claim.selected) {
                    // When the claim record is selected, all sub items
                    // should be set to the full amount and disabled.
                    this.$set(claim, 'items', claim.items.map(item => {
                        item.amount_applied = item.amount_due;
                        item.adjustment_type = this.CLAIM_ADJUSTMENT_TYPES.PAYMENT;
                        item.note = '';
                        item.selected = true;
                        item.disabled = true;
                        return item;
                    }));

                    this.$set(claim, 'amount_applied', claim.amount_due);
                    this.$set(claim, 'adjustment_type', this.CLAIM_ADJUSTMENT_TYPES.PAYMENT);

                    // Force view of details (sub-items)
                    this.$set(claim, '_showDetails', true);
                } else {
                    // When the claim record is un-selected, we should clear
                    // all progress from it and it's sub items.
                    this.$set(claim, 'items', claim.items.map(item => {
                        // item.amount_applied = '';
                        // item.adjustment_type = '';
                        // item.note = '';
                        // item.selected = false;
                        item.disabled = false;
                        return item;
                    }));
                    this.$set(claim, 'amount_applied', '');
                    this.$set(claim, 'adjustment_type', '');
                    this.forceRowUpdate(claim.id);
                }
            },

            /**
             * Handle change to adjustment_type for master items.
             *
             * @param claim Object
             * @param value String
             */
            changeMasterType(claim, value) {
                if (! claim.selected) {
                    return;
                }

                this.$set(claim, 'items', claim.items.map(item => {
                    item.adjustment_type = value;
                    return item;
                }));
            },

            /**
             * Handle selection of sub (claim) items.
             *
             * @param {Object} claimItem
             */
            selectSub(claimItem) {
                if (claimItem.selected) {
                    // Claim items should always have a numeric value when selected.
                    if (claimItem.amount_applied == '') {
                        this.$set(claimItem, 'amount_applied', claimItem.amount_due);
                        this.$set(claimItem, 'adjustment_type', this.CLAIM_ADJUSTMENT_TYPES.PAYMENT);
                        this.$set(claimItem, 'note', '');
                    }
                } else {
                    // Claim items that are not selected should always be empty.
                    this.$set(claimItem, 'amount_applied', '');
                    this.$set(claimItem, 'adjustment_type', '');
                    this.$set(claimItem, 'note', '');
                }

                this.forceRowUpdate(claimItem.claim_invoice_id);
            },

            /**
             * Handle change of claim item amount applied.
             *
             * @param {Object} claimItem
             * @param {number} value
             */
            subAmountChanged(claimItem, value) {
                if (isNaN(value) || value == '') {
                    // Clear the value / selection if invalid value.
                    this.$set(claimItem, 'amount_applied', '');
                    this.$set(claimItem, 'selected', false);
                } else {
                    // Make sure item is selected when it has an amount.
                    this.$set(claimItem, 'selected', true);
                }

                this.forceRowUpdate(claimItem.claim_invoice_id);
            },

            /**
             * Handle sub (claim) item changed adjustment type dropdown.
             *
             * @param {Object} claimItem
             * @param {string} value
             */
            subTypeChanged(claimItem, value) {
                if (value == '') {
                    return;
                }

                // Make sure item is selected and has a numeric amount set.
                this.$set(claimItem, 'selected', true);
                if (claimItem.amount_applied == '') {
                    this.$set(claimItem, 'amount_applied', '0.00');
                }

                this.forceRowUpdate(claimItem.claim_invoice_id);
            },

            /**
             * Force the BootstrapVue table to update the claim row.
             *
             * @param {number} claimId
             */
            forceRowUpdate(claimId) {
                // This was implemented because BootstrapVue was having issues knowing
                // that it should update the table rows unless we $set the claim row
                // explicitly.  Doing this after the nextTick() will ensure the row
                // triggers an update after we have updated any values prior
                // to this method call.
                this.$nextTick(x => {
                    let index = this.claims.findIndex(x => x.id == claimId);
                    // Set the claim item to itself to force and update but not change values.
                    this.$set(this.claims, index, JSON.parse(JSON.stringify(this.claims[index])));
                });
            },

            /**
             * Fetch client list for the dropdown filter.
             * @returns {Promise<void>}
             */
            async fetchClients() {
                this.form.client_id = '';
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

            /**
             * Calculate the amount due on a master (claim) item based on
             * it's current balance and the amount being applied to it's sub items.
             *
             * @returns string
             */
            getMasterAmountDue(claim) {
                return this.decimalOrZero(claim.amount_due).sub(claim.items.reduce((carry, item) => {
                    if (item.amount_applied == '') {
                        return carry;
                    }
                    return carry.add(this.decimalOrZero(item.amount_applied));
                }, this.decimalOrZero(0.00))).toFixed(2);
            },

            /**
             * Handle tracking of window scroll event
             */
            handleScroll() {
              this.isScrolling = window.scrollY > 0;
            },

            /**
             * Save filters to local storage.
             */
            saveFilters() {
                for (let filter of Object.keys(this.filters.data())) {
                    if (['invoice_id', 'json'].includes(filter)) {
                        continue;
                    }
                    this.setLocalStorage(filter, this.filters[filter]);
                }
                this.setLocalStorage('sortBy', this.sortBy);
                this.setLocalStorage('sortDesc', this.sortDesc);
            },

            /**
             * Load filters from local storage.
             */
            loadFilters() {
                if (typeof(Storage) !== "undefined") {
                    // Saved filters
                    for (let filter of Object.keys(this.filters)) {
                        let value = this.getLocalStorage(filter);
                        if (value !== null) {
                            this.filters[filter] = value;
                        }
                    }

                    // Sorting/show UI
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) {
                        this.sortBy = sortBy;
                    }
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) {
                        this.sortDesc = sortDesc;
                    }
                }
            },

            decimalOrZero(number) {
                try {
                    return new Decimal(number);
                } catch (e) {
                    return new Decimal(0.00);
                }
            },
        },

        async mounted() {
            await this.fetchClients();

            // Set default filters
            this.filters.claim_status = 'unpaid';
            this.filters.businesses = this.remit.business_id;
            this.filters.payer_id = this.remit.payer_id === 0 || this.remit.payer_id ? ''+this.remit.payer_id : '';
            this.loadFilters();

            this.fetch();
        },

        created() {
            this.$store.commit('claims/setRemit', this.init.remit);
            window.addEventListener('scroll', this.handleScroll);
        },

        destroyed() {
            window.removeEventListener('scroll', this.handleScroll);
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
    #floating-amount-applied {
        position: fixed;
        left: 25px;
        bottom: 25px;
        display: block;
        background-color: #fff;
        padding: 1rem;
        text-align: center;
        font-size: 24px;
        border: 1px solid darkgrey;
        z-index: 25;
    }
</style>