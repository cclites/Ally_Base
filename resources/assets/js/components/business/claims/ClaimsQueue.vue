<template>
    <b-card header="Search Claims"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <div class="filters mb-4">
            <div>
                <b-form-radio-group v-model="filters.date_type">
                    <b-radio value="service">Search by Date of Service</b-radio>
                    <b-radio value="invoice">Search by Invoice Dates</b-radio>
                </b-form-radio-group>
            </div>

            <b-form inline @submit.prevent="fetch()">
                <business-location-form-group
                    v-model="filters.businesses"
                    :label="null"
                    class="mr-1 mt-1"
                    :allow-all="true"
                />
                <date-picker
                    v-model="filters.start_date"
                    placeholder="Start Date"
                    class="mt-1"
                >
                </date-picker> &nbsp;to&nbsp;
                <date-picker
                    v-model="filters.end_date"
                    placeholder="End Date"
                    class="mr-1 mt-1"
                >
                </date-picker>

                <payer-dropdown v-model="filters.payer_id" class="mr-1 mt-1" empty-text="-- All Payers --" />

                <client-type-dropdown v-model="filters.client_type" class="mr-1 mt-1" empty-text="-- All Client Types --" />

                <b-form-select
                    v-model="filters.balance"
                    class="mr-1 mt-1"
                >
                    <option value="">-- Balance --</option>
                    <option value="has_balance">Has Balance</option>
                    <option value="no_balance">No Balance</option>
                </b-form-select>

                <b-form-select v-model="filters.claim_type" class="mr-1 mt-1" :options="claimInvoiceTypeOptions">
                    <template slot="first">
                        <option value="">-- Claim Type --</option>
                    </template>
                </b-form-select>

                <b-form-select
                    id="claim_status"
                    name="claim_status"
                    v-model="filters.claim_status"
                    class="mr-1 mt-1"
                >
                    <option value="">-- Claim Status --</option>
                    <option value="CREATED">Created</option>
                    <option value="TRANSMITTED">Transmitted</option>
                </b-form-select>

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

                <b-button type="submit" variant="info" class="mt-1" :disabled="loaded === 0">Generate</b-button>
            </b-form>
        </div>

        <b-row>
            <b-col>
                <b-alert show variant="info">
                    Once claims are submitted, you will need to follow up with the Payer or your Claims Portal for updates.
                </b-alert>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-input v-model=" filter " placeholder="Type to Search" />
            </b-col>
            <b-col lg="6" class="text-right">
                <a href="/business/reports/claims/ar-aging" target="_blank">View Aging Report</a>
            </b-col>
        </b-row>

        <loading-card v-if="loaded == 0"></loading-card>

        <b-row v-if=" loaded < 0 ">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate
                </b-card>
            </b-col>
        </b-row>
        <div class="table-responsive" v-if="loaded > 0" style="min-height: 250px">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="expand" scope="row">
                    <b-btn v-if="row.item.invoice_id == '-'" variant="secondary" size="sm" @click.stop="row.toggleDetails">
                        <i v-if="row.detailsShowing" class="fa fa-caret-down" />
                        <i v-else class="fa fa-caret-right" />
                    </b-btn>
                </template>
                <template slot="invoice_name" scope="row">
                    <span v-if="row.item.invoice_id == '-'">
                        {{ row.item.invoice_name }}
                    </span>
                    <a v-else :href="`/business/client/invoices/${row.item.invoice_id}/`" target="_blank">{{ row.item.invoice_name }}</a>
                </template>
                <template slot="client_name" scope="row">
                    <span v-if="row.item.type == CLAIM_INVOICE_TYPES.PAYER">
                        (Grouped)
                    </span>
                    <a v-else :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                </template>
                <template slot="name" scope="row">
                    <div class="text-nowrap">
                        <a :href="`/business/claims/${row.item.id}/print`" target="_blank">{{ row.item.name }}</a>
                        <span v-if="row.item.modified_at">
                            <i class="fa fa-code-fork text-danger" :id="`modified_icon_${row.item.id}`" />
                            <b-tooltip :target="`modified_icon_${row.item.id}`" triggers="hover">
                                Claim has been modified.
                            </b-tooltip>
                        </span>
                    </div>
                </template>
                <template slot="payer_name" scope="row">
                    {{ row.item.payer_name }}
                </template>
                <template slot="amount" scope="row">
                    <div class="text-nowrap">
                        <span>{{ moneyFormat(row.item.amount, '$', true) }}</span>
                        <span v-if="row.item.amount_mismatch">
                            <i class="fa fa-warning ml-1 text-danger" :id="`mismatch_icon_${row.item.id}`" />
                            <b-tooltip :target="`mismatch_icon_${row.item.id}`" triggers="hover">
                                Claim amount does not match invoice amount.
                            </b-tooltip>
                        </span>
                    </div>
                </template>
                <template slot="status" scope="row">
                    {{ resolveOption(row.item.status, claimStatusOptions) }}
                    <span v-if="row.item.transmission_method == 'HHA' && row.item.status == CLAIM_STATUSES.REJECTED">
                        <i class="ml-1 text-danger fa fa-lg fa-exclamation-circle" @click="showHhaResults(row.item)"></i>
                    </span>
                </template>
                <template slot="actions" scope="row" class="text-nowrap">
                    <div class="text-nowrap">
                        <!-- EDIT BUTTON -->
                        <b-btn
                               variant="info"
                               class="mr-1"
                               :href="`/business/claims/${row.item.id}/edit`"
                               size="sm"
                        >
                            <i class="fa fa-edit" />
                        </b-btn>
                        <b-dropdown right size="sm" text="..." class="claim-dropdown" :disabled="busy || [transmittingId, deletingId].includes(row.item.id)">
                            <b-dropdown-item :href="`/business/claims/${row.item.id}/print?download=1`">
                                <i class="fa fa-download mr-1" />Download PDF
                            </b-dropdown-item>
                            <b-dropdown-item v-if="row.item.status == 'CREATED'" @click="transmit(row.item)">
                                <i class="fa fa-send-o mr-1" />Transmit Claim
                            </b-dropdown-item>
                            <b-dropdown-item v-if="row.item.status != 'CREATED'" @click="transmit(row.item)">
                                <i class="fa fa-send-o mr-1" />Re-transmit Claim
                            </b-dropdown-item>
                            <b-dropdown-item @click="adjust(row.item)">
                                <i class="fa fa-usd mr-1" />Adjust Claim
                            </b-dropdown-item>
                            <b-dropdown-item :href="`/business/claim-adjustments/${row.item.id}`">
                                <i class="fa fa-history mr-1" />Adjustment History
                            </b-dropdown-item>
                            <b-dropdown-divider />
                            <b-dropdown-item @click="deleteClaim(row.item)" variant="danger">
                                <i class="fa fa-times mr-1" />Delete Claim
                            </b-dropdown-item>
                        </b-dropdown>
                    </div>
                </template>
                <template slot="row-details" scope="row">
                    <b-card>
                        <!---------- SUB TABLE --------------->
                        <b-table bordered striped show-empty
                            :items="row.item.invoices"
                            :fields="subFields"
                        >
                            <template slot="name" scope="row">
                                <a :href="`/business/client/invoices/${row.item.id}/`" target="_blank">{{ row.item.name }}</a>
                            </template>
                            <template slot="client_name" scope="row">
                                <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                            </template>
                        </b-table>
                        <!---------- /END SUB TABLE --------------->
                    </b-card>
                </template>
            </b-table>
        </div>

        <confirm-modal
            title="Select Transmission Method"
            ref="confirmTransmissionMethod"
            yesButton="Transmit"
            :yes-disabled="!selectedTransmissionMethod"
        >
            <div>
                <p>A transmission method could not be inferred for this Claim.  Please check the method you would like to use:</p>
            </div>
            <b-form-group label="Transmission Method" label-for="selectedTransmissionMethod" label-class="required">
                <transmission-method-dropdown v-model="selectedTransmissionMethod" />
            </b-form-group>
        </confirm-modal>

        <confirm-modal title="Offline Transmission" ref="confirmManualTransmission" yesButton="Okay">
            <p>Based on the transmission type for this Claim, this will assume you have sent in via E-Mail/Fax/Direct Mail.</p>
        </confirm-modal>

        <confirm-modal title="Delete Claim" ref="confirmDeleteClaim" yesButton="Delete" yesVariant="danger">
            <p>Are you sure you want to delete this claim?</p>
        </confirm-modal>

        <b-modal id="adjustmentModal"
            title="Adjust Claim Balance"
            v-model="showAdjustmentModal"
            :no-close-on-backdrop="true"
            hide-footer
            size="lg"
        >
            <claim-adjustment-form @close="hideAdjustmentModal()" @update="updateRecord" />
        </b-modal>

        <b-modal id="missingFieldsModal"
            title="Missing Data Requirements"
            v-model="missingFieldsModal"
        >
            <div v-for="(item, index) in missingFieldErrors" :key="index">
                <span class="mr-1">{{ item.message }}</span>
                <span>(<a :href="item.url">Fix</a>)</span>
            </div>
            <div slot="modal-footer">
                <b-btn variant="default" @click="missingFieldsModal = false">Close</b-btn>
            </div>
        </b-modal>

        <b-modal id="tellusErrorsModal"
             title="Tellus Validation Issues"
             v-model="tellusErrorsModal"
             size="lg"
        >
            <b-alert variant="danger" show>Tellus is very strict on what values they allow in their submissions.  Please address the following issues and try to transmit again.</b-alert>
            <div v-for="(error, index) in tellusErrors" :key="index" class="mb-1">
                <span class="mr-2"><strong>{{ error.field }}</strong></span>
                <span>{{ error.error }}</span>
                <span v-if="error.url" class="ml-2">(<a :href="`${error.url}`" target="_blank">Go Fix</a>)</span>
            </div>
            <div slot="modal-footer">
                <b-btn variant="default" @click="tellusErrorsModal = false">Dismiss</b-btn>
            </div>
        </b-modal>

        <b-modal id="hhaResultsModal"
             size="lg"
             :title="`HHA Results for Claim #${selectedClaim.name}`"
             v-model="hhaResultsModal"
        >
            <b-row>
                <b-table bordered striped hover show-empty
                         :items="hhaResults"
                         :fields="hhaFields"
                         sort-by="service_date"
                >
                </b-table>
            </b-row>
        </b-modal>

        <a href="#" target="_blank" ref="open_test_link" class="d-none"></a>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import TransmissionMethodDropdown from "./TransmissionMethodDropdown";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import ClaimAdjustmentForm from "./ClaimAdjustmentForm";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';

    export default {
        components: {BusinessLocationFormGroup, ClaimAdjustmentForm, TransmissionMethodDropdown},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                filters: new Form({
                    businesses: '',
                    start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    balance: '',
                    claim_status: '',
                    client_id: '',
                    payer_id: '',
                    client_type: '',
                    invoice_id: '',
                    inactive: 0,
                    date_type: 'service',
                    claim_type: '',
                    json: 1,
                }),
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                items: [],
                fields: {
                    expand: { label: ' ', sortable: false, },
                    name: {label: 'Claim #', sortable: true },
                    // type: { sortable: true, formatter: x => this.resolveOption(x, this.claimInvoiceTypeOptions) },
                    created_at: { label: 'Date', formatter: (val) => this.formatDateFromUTC(val), sortable: true, },
                    client_name: { sortable: true, },
                    payer_name: { label: 'Payer', sortable: true,},
                    invoice_name: { label: 'Invoice #',sortable: true,},
                    invoice_amount: { label: 'Total Invoiced', formatter: (val) => this.moneyFormat(val, '$', true), sortable: true, },
                    amount: { label: 'Claim Amt', formatter: (val) => this.moneyFormat(val, '$', true), sortable: true },
                    paid: { label: 'Amt Paid', formatter: (val) => this.moneyFormat(val, '$', true), sortable: true },
                    balance: { label: 'Balance', formatter: (val) => this.moneyFormat(val, '$', true), sortable: true, },
                    status: { sortable: true, },
                    actions: { tdClass: 'actions-column', sortable: false, },
                },
                subFields: {
                    name: {label: 'Invoice #', sortable: true },
                    created_at: { label: 'Date', formatter: (val) => this.formatDateFromUTC(val), sortable: true, },
                    client_name: { label: 'Client', sortable: true, },
                    amount: { label: 'Invoiced Amt', formatter: (val) => this.moneyFormat(val, '$', true), sortable: true },
                },
                loadingClients: false,
                clients: [],
                busy: false,
                transmittingId: null,
                creatingId: null,
                deletingId: null,
                selectedTransmissionMethod: '',
                showAdjustmentModal: false,
                missingFieldErrors: [],
                missingFieldsModal: false,
                tellusErrorsModal: false,
                tellusErrors: [],
                hhaResultsModal: false,
                hhaResults: [],
                hhaFields: {
                    service_date: { sortable: true, label: 'Date', formatter: x => this.formatDateTime(x) },
                    service_code: { sortable: true, label: 'Service Code' },
                    status_code: { sortable: true, label: 'Status Code' },
                    import_status: { sortable: true, label: 'Import Status' },
                },
                selectedClaim: {},
            }
        },

        methods: {
            /**
             * Show the Claim adjustment modal.
             * @param {Object} claim
             */
            adjust(claim) {
                axios.get(`/business/claims/${claim.id}`)
                    .then( ({ data }) => {
                        this.$store.commit('claims/setClaim', data);
                        this.showAdjustmentModal = true;
                    })
                    .catch(() => {});
            },

            /**
             * Hide the Claim adjustment modal.
             */
            hideAdjustmentModal() {
                this.showAdjustmentModal = false;
                this.$store.commit('claims/setClaim', {});
            },

            /**
             * Handle updating the table item after it is successfully adjusted.
             * @param {Object} invoice
             */
            updateRecord(claim) {
                let index = this.items.findIndex(x => x.id == claim.id);
                if (index >= 0) {
                    this.items.splice(index, 1, claim);
                }
            },

            /**
             * Handle deleting the Claim Invoice and update the record.
             * @param {Object} claim
             */
            deleteClaim(claim) {
                this.$refs.confirmDeleteClaim.confirm(() => {
                    this.deletingId = claim.id;
                    let form = new Form({});
                    form.submit('delete', `/business/claims/${claim.id}`)
                        .then( ({ data }) => {
                            let index = this.items.findIndex(x => x.id == claim.id);
                            if (index >= 0) {
                                this.items.splice(index, 1, data.data);
                            }
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.deletingId = null;
                        })
                });
            },

            /**
             * Transmit a Claim.
             * @param {Object} claim
             * @param {Boolean} skipAlert
             */
            transmit(claim, skipAlert = false) {
                if (! skipAlert) {
                    if (! claim.transmission_method) {
                        this.selectedTransmissionMethod = '';
                        this.$refs.confirmTransmissionMethod.confirm(() => {
                            this.transmit(claim, true);
                        });
                        return;
                    }

                    let offlineMethods = [
                        this.CLAIM_SERVICE.EMAIL,
                        this.CLAIM_SERVICE.FAX,
                        this.CLAIM_SERVICE.DIRECT_MAIL
                    ];

                    if (offlineMethods.includes(claim.transmission_method)) {
                        this.$refs.confirmManualTransmission.confirm(() => {
                            this.transmit(claim, true);
                        });
                        return;
                    }
                }

                this.busy = true;
                this.transmittingId = claim.id;
                let form = new Form({
                    method: this.selectedTransmissionMethod,
                });

                form.post(`/business/claims/${claim.id}/transmit`)
                    .then( ({ data }) => {
                        // success
                        if (data.data.test_result) {
                            // test mode
                            this.$refs.open_test_link.href = data.data.test_result;
                            this.$refs.open_test_link.click();
                        }
                        let index = this.items.findIndex(x => x.id == claim.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data.claim);
                        }
                    })
                    .catch(e => {
                        if (e.response.status == 412) {
                            // Required fields are missing.
                            console.log('wtf');
                            this.showMissingFieldsModal(e.response.data.data, claim);
                        } else if (e.response.status == 420) {
                            // Tellus Validation Errors
                            console.log('tellus');
                            this.tellusErrors = e.response.data.data.tellus_errors;
                            this.tellusErrorsModal = true;
                        }
                    })
                    .finally(() => {
                        this.busy = false;
                        this.transmittingId = null;
                    });
            },

            /**
             * Show the missing fields modal with the given errors
             * @param {array} errors
             * @param {Object} claim
             */
            showMissingFieldsModal(errors, claim) {
                this.missingFieldErrors = errors;
                this.missingFieldsModal = true;
            },

            /**
             * Fetch Client Invoice and Claim records for the Queue.
             * @returns {Promise<void>}
             */
            async fetch() {
                this.loaded = 0;
                this.filters.get(`/business/claims-queue`)
                    .then(({data}) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                        this.items = [];
                    })
                    .finally(() => {
                        this.loaded = 1;
                    });
            },

            /**
             * Fetch client list for the dropdown filter.
             * @returns {Promise<void>}
             */
            async fetchClients() {
                this.filters.client_id = '';
                this.loadingClients = true;
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
             * Fetch and show the HHA file results from the transmission.
             * @param claim
             */
            showHhaResults(claim) {
                this.selectedClaim = claim;

                axios.get(`/business/claims/${claim.id}/results`)
                    .then( ({ data }) => {
                        this.hhaResults = data;
                    })
                    .catch(() => {

                    });
                // claims-ar/hha-results
                this.hhaResultsModal = true;
            },
        },

        async mounted() {
            await this.fetchClients();

            // load filters from query
            let autoLoad = false;
            var urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('start_date')) {
                this.start_date = urlParams.get('start_date');
                autoLoad = true;
            }

            if (urlParams.has('end_date')) {
                this.end_date = urlParams.get('end_date');
                autoLoad = true;
            }

            if (urlParams.has('filter')) {
                this.filter = urlParams.get('filter');
            }

            if (autoLoad) {
                this.fetch();
            }
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
    table:not(.form-check) {
        font-size: 14px;
    }
    .claim-dropdown button {
        font-weight: 700;
        letter-spacing: 3px;
    }
    .claim-dropdown button::after {
        border: none;
        margin: 0;
    }
</style>