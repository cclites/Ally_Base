<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Filter Claims"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="fetch()">
                        <business-location-form-group
                            v-model="businesses"
                            :label="null"
                            class="mr-1 mt-1"
                            :allow-all="true"
                        />
                        <date-picker
                                v-model="start_date"
                                placeholder="Start Date"
                                class="mt-1"
                        >
                        </date-picker> &nbsp;to&nbsp;
                        <date-picker
                                v-model="end_date"
                                placeholder="End Date"
                                class="mr-1 mt-1"
                        >
                        </date-picker>
                        <b-form-select v-model="clientFilter" class="mr-1 mt-1">
                            <option v-if="loadingClients" selected>Loading...</option>
                            <option v-else value="">-- Select a Client --</option>
                            <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                            </option>
                        </b-form-select>

                        <payer-dropdown v-model="payerFilter" class="mr-1 mt-1" />

                        <b-form-select
                            id="invoiceType"
                            name="invoiceType"
                            v-model="invoiceType"
                            class="mr-1 mt-1"
                        >
                            <option value="">All Invoices</option>
                            <option value="unpaid">Unpaid Invoices</option>
                            <option value="paid">Paid Invoices</option>
                            <option value="has_claim">Has Claim</option>
                            <option value="no_claim">Does Not Have Claim</option>
                            <option value="has_balance">Has Claim Balance</option>
                            <option value="no_balance">Does Not Have Claim Balance</option>
                        </b-form-select>

                        <b-button type="submit" variant="info" class="mt-1" :disabled="loaded === 0">Generate Report</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>

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
                    <a :href="`/business/client/invoices/${row.item.id}/`" target="_blank">{{ row.value }}</a>
                </template>
                <template slot="client" scope="row">
                    <a :href="`/business/clients/${row.item.client.id}`" target="_blank">{{ ( row.item.claim ? row.item.client_name : row.item.client.name ) }}</a>
                </template>
                <template slot="claim" scope="row">
                    <a v-if="row.item.claim" :href="`/business/claims/${row.item.claim.id}/print`" target="_blank">{{ row.item.claim.name }}</a>
                    <span v-else> - </span>
                    <i v-if="row.item.claim && row.item.claim.modified_at" class="fa fa-code-fork text-danger"></i>
                </template>
                <template slot="payer" scope="row">
                    <span v-if="row.item.claim">
                        {{ row.item.claim.payer_name }}
                    </span>
                    <span v-else>
                        {{ row.item.payer ? row.item.payer.name : 'N/A' }}
                    </span>
                </template>
                <template slot="actions" scope="row" class="text-nowrap">
                    <!-- CREATE BUTTON -->
                    <div v-if="! row.item.claim">
                        <b-btn variant="success" class="mr-1" @click="createClaim(row.item)" :disabled="busy || creatingId != null" size="sm">
                            <i v-if="row.item.id === creatingId" class="fa fa-spin fa-spinner" />&nbsp;Create Claim
                        </b-btn>
                    </div>
                    <div class="text-nowrap" v-else>
                        <!-- EDIT BUTTON -->
                        <b-btn
                               variant="info"
                               class="mr-1"
                               :href="`/business/claims/${row.item.claim.id}/edit`"
                               size="sm"
                        >
                            <i class="fa fa-edit" />
                        </b-btn>
                        <b-dropdown right size="sm" text="..." class="claim-dropdown" :disabled="busy || [transmittingId, deletingId].includes(row.item.id)">
                            <b-dropdown-item :href="`/business/claims/${row.item.claim.id}/print?download=1`">
                                <i class="fa fa-download" />&nbsp;Download PDF
                            </b-dropdown-item>
                            <b-dropdown-item v-if="row.item.claim.status == 'CREATED'" @click="transmit(row.item)">
                                <i class="fa fa-send-o" />&nbsp;Transmit Claim
                            </b-dropdown-item>
                            <b-dropdown-item v-if="row.item.claim.status != 'CREATED'" @click="transmit(row.item)">
                                <i class="fa fa-send-o" />&nbsp;Re-transmit Claim
                            </b-dropdown-item>
                            <b-dropdown-item @click="adjust(row.item)">
                                <i class="fa fa-usd" />&nbsp;Adjust Claim
                            </b-dropdown-item>
                            <b-dropdown-divider />
                            <b-dropdown-item @click="deleteClaim(row.item)" variant="danger">
                                <i class="fa fa-times" />&nbsp;Delete Claim
                            </b-dropdown-item>
                        </b-dropdown>
                    </div>
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
            <p>Based on the transmission type for this Claim, this will assume you have sent in via E-Mail/Fax.</p>
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
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                invoiceType: "",
                items: [],
                fields: [
                    {
                        key: 'created_at',
                        label: 'Inv Date',
                        formatter: (val) => this.formatDateFromUTC(val),
                        sortable: true,
                    },
                    {
                        key: 'name',
                        label: 'Invoice #',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        sortable: true,
                    },
                    {
                        key: 'payer',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Invoiced Amt',
                        formatter: (val) => this.moneyFormat(val, '$', true),
                        sortable: true,
                    },
                    {
                        key: 'claim',
                        label: 'Claim',
                        sortable: false
                    },
                    {
                        key: 'claim_date',
                        formatter: (val) => this.formatDateFromUTC(val, 'MM/DD/YYYY h:mm a', null, true),
                        label: 'Claim Date',
                        sortable: true
                    },
                    {
                        key: 'claim_total',
                        label: 'Claim Amt',
                        formatter: (val) => this.moneyFormat(val, '$', true),
                        sortable: true
                    },
                    {
                        key: 'claim_paid',
                        label: 'Amt Paid',
                        formatter: (val) => this.moneyFormat(val, '$', true),
                        sortable: true
                    },
                    {
                        key: 'claim_balance',
                        label: 'Claim Balance',
                        formatter: (val) => this.moneyFormat(val, '$', true),
                        sortable: true,
                    },
                    {
                        key: 'claim_status',
                        formatter: (x) => this.resolveOption(x, this.claimStatusOptions),
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        tdClass: 'actions-column',
                        sortable: false,
                    },
                ],
                loadingClients: false,
                clients: [],
                clientFilter: '',
                payerFilter: '',
                businesses: '',
                paymentModal: false,
                form: new Form({
                    type: '',
                    payment_date: moment().format('MM/DD/YYYY'),
                    amount: 0.00,
                    reference: '',
                    notes: '',
                    description: 'payment_applied',
                }),
                selectedInvoice: {},
                busy: false,
                transmittingId: null,
                creatingId: null,
                deletingId: null,
                selectedTransmissionMethod: '',
                payFullBalance: false,
                editingClaim: {},
                showAdjustmentModal: false,
            }
        },

        methods: {
            /**
             * Show the Claim adjustment modal.
             * @param {Object} invoice
             */
            adjust(invoice) {
                axios.get(`/business/claims/${invoice.claim.id}`)
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
            updateRecord(invoice) {
                let index = this.items.findIndex(x => x.id == invoice.id);
                if (index >= 0) {
                    this.items.splice(index, 1, invoice);
                }
            },

            /**
             * Handle creation of the Claim invoice and update the record.
             * @param {Object} invoice
             */
            createClaim(invoice) {
                this.creatingId = invoice.id;
                let form = new Form({client_invoice_id: invoice.id});
                form.post(`/business/claims`)
                    .then( ({ data }) => {
                        let index = this.items.findIndex(x => x.id == data.data.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data);
                        }
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.creatingId = null;
                    })
            },

            /**
             * Handle deleting the Claim Invoice and update the record.
             * @param {Object} invoice
             */
            deleteClaim(invoice) {
                if (! invoice.claim) {
                    return;
                }

                this.$refs.confirmDeleteClaim.confirm(() => {
                    this.deletingId = invoice.id;
                    let form = new Form({});
                    form.submit('delete', `/business/claims/${invoice.claim.id}`)
                        .then( ({ data }) => {
                            let index = this.items.findIndex(x => x.id == invoice.id);
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
             * @param {Object} invoice
             * @param {Boolean} skipAlert
             */
            transmit(invoice, skipAlert = false) {
                if (! skipAlert) {
                    if (! invoice.claim.transmission_method) {
                        this.selectedTransmissionMethod = '';
                        this.$refs.confirmTransmissionMethod.confirm(() => {
                            this.transmit(invoice, true);
                        });
                        return;
                    }

                    if ([this.CLAIM_SERVICE.EMAIL, this.CLAIM_SERVICE.FAX].includes(invoice.claim.transmission_method)) {
                        this.$refs.confirmManualTransmission.confirm(() => {
                            this.transmit(invoice, true);
                        });
                        return;
                    }
                }

                this.busy = true;
                this.transmittingId = invoice.claim.id;
                let form = new Form({
                    method: this.selectedTransmissionMethod,
                });

                form.post(`/business/claims/${invoice.claim.id}/transmit`)
                    .then( ({ data }) => {
                        // success
                        if (data.data.test_result) {
                            // test mode
                            this.$refs.open_test_link.href = data.data.test_result;
                            this.$refs.open_test_link.click();
                        }
                        let index = this.items.findIndex(x => x.id == invoice.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data.invoice);
                        }
                    })
                    .catch(e => {
                        if (e.response.status == 412) {
                            // Required fields are missing.
                            this.showMissingFieldsModal(e.response.data.data, invoice);
                        }
                    })
                    .finally(() => {
                        this.busy = false;
                        this.transmittingId = null;
                    });
            },

            /**
             * Fetch Client Invoice and Claim records for the Queue.
             * @returns {Promise<void>}
             */
            async fetch() {
                this.loaded = 0;
                let url = `/business/claims-queue?json=1&businesses=${this.businesses}&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
                axios.get(url)
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
                this.loadingClients = true;
                await axios.get(`/business/dropdown/clients`)
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
        }
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