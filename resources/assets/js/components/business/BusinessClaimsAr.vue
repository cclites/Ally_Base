<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select Date Range"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadItems()">
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
                        <b-form-select v-model="payerFilter" class="mr-1 mt-1">
                            <option v-if="loadingPayers" selected>Loading...</option>
                            <option v-else value="">-- Select a Payer --</option>
                            <option value="0">(Client)</option>
                            <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                            </option>
                        </b-form-select>
                        <b-form-select
                            id="invoiceType"
                            name="invoiceType"
                            v-model="invoiceType"
                            class="mt-1"
                        >
                            <option value="">All Invoices</option>
                            <option value="unpaid">Unpaid Invoices</option>
                            <option value="paid">Paid Invoices</option>
                            <option value="has_claim">Has Claim</option>
                            <option value="no_claim">Does Not Have Claim</option>
                            <option value="has_balance">Has Claim Balance</option>
                            <option value="no_balance">Does Not Have Claim Balance</option>
                        </b-form-select>
                        &nbsp;<br /><b-button type="submit" variant="info" class="mt-1" :disabled="loaded === 0">Generate Report</b-button>
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
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6" class="text-right">
                <a href="/business/reports/claims-ar-aging" target="_blank">View Aging Report</a>
            </b-col>
        </b-row>
        <loading-card v-if="loaded == 0"></loading-card>
        <b-row v-if="loaded < 0">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>
        <div class="table-responsive" v-if="loaded > 0">
            <b-table bordered striped hover show-empty
                     :items="filteredItems"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :filter="filter"
            >
                <template slot="name" scope="row">
                    <a :href="invoiceUrl(row.item)" target="_blank">{{ row.value }}</a>
                </template>
                <template slot="client" scope="row">
                    <a :href="`/business/clients/${row.item.client.id}`">{{ row.item.client.name }}</a>
                </template>
                <template slot="actions" scope="row">
                    <b-btn v-if="!row.item.claim || row.item.claim.status == 'CREATED'" variant="primary" class="mr-2" @click="transmitClaim(row.item)" :disabled="busy">
                        <i v-if="row.item.id === transmittingId" class="fa fa-spin fa-spinner"></i>
                        <span>Transmit Claim</span>
                    </b-btn>
                    <b-btn v-if="row.item.claim && row.item.claim.status != 'CREATED'" variant="success" class="mr-2" @click="showPaymentModal(row.item)">Apply Payment</b-btn>
                    <b-btn v-if="row.item.claim" variant="secondary" class="mr-2" :href="claimInvoiceUrl(row.item)" target="_blank">View Claim Invoice</b-btn>
                    <b-btn v-if="row.item.claim" variant="secondary" class="mr-2" :href="claimInvoiceUrl(row.item, 'pdf')" target="_blank">Download Claim Invoice</b-btn>
                </template>
            </b-table>
        </div>
        <b-modal id="applyPaymentModal"
                 :title="`Apply Payment to Claim #${selectedInvoice.name}`"
                 v-model="paymentModal"
                 no-close-on-backdrop
        >
            <b-form-group label="Payment Date">
                <date-picker v-model="form.payment_date" placeholder="Payment Date" :disabled="form.busy"></date-picker>
                <input-help :form="form" field="payment_date" text="" />
            </b-form-group>
            <b-form-group label="Payment Type">
                <b-form-input
                    name="type"
                    type="text"
                    v-model="form.type"
                    max="255"
                    :disabled="form.busy"
                />
                <input-help :form="form" field="type" text="" />
            </b-form-group>
            <b-form-group label="Reference #">
                <b-form-input
                    name="reference"
                    type="text"
                    v-model="form.reference"
                    max="255"
                    :disabled="form.busy"
                />
                <input-help :form="form" field="reference" text="" />
            </b-form-group>
            <b-form-group label="Claim Balance">
                <b-form-input
                    name="claim_balance"
                    v-model="selectedInvoice.claim_balance"
                    :disabled="true"
                />
                <input-help :form="form" field="claim_balance" text="" />
            </b-form-group>
            <b-form-group label="Payment Amount Towards Claim">
                <b-form-input
                    name="amount"
                    type="number"
                    v-model="form.amount"
                    step="0.01"
                    required
                    :disabled="form.busy || payFullBalance"
                />
                <input-help :form="form" field="amount" text="" />
            </b-form-group>
            <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" v-model="payFullBalance" @change="updateFullBalance()" />
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Pay Full Balance</span>
            </label>

            <div slot="modal-footer">
                <b-btn variant="default" @click="cancelPayment()" :disabled="form.busy">Cancel</b-btn>
                <b-btn variant="info" @click="applyPayment()" :disabled="form.busy">Apply Payment</b-btn>
            </div>
        </b-modal>

        <confirm-modal
            title="Select Transmission Method"
            ref="confirmTransmissionMethod"
            yesButton="Transmit"
            :yes-disabled="!selectedTransmissionMethod"
        >
            <div v-if="transmissionPrivate">
                <p>Private and Offline Payer types do not have a default transmission method.</p>
                <p>Please select the method would you like to use to submit this invoice.</p>
            </div>
            <div v-else>
                <p>A transmission method has not been set for this payer.  We recommend you go into the Payer record and assign a transmission method.</p>
                <p>For now, please choose how you would like to send:</p>
            </div>
            <b-form-group label="Transmission Method" label-for="selectedTransmissionMethod" label-class="required">
                <b-select v-model="selectedTransmissionMethod">
                    <option value="">-- Select Transmission Method --</option>
                    <option value="-" disabled>Direct Transmission:</option>
                    <option :value="CLAIM_SERVICE.HHA">{{ serviceLabel(CLAIM_SERVICE.HHA) }}</option>
                    <option :value="CLAIM_SERVICE.TELLUS">{{ serviceLabel(CLAIM_SERVICE.TELLUS) }}</option>
<!--                    <option :value="CLAIM_SERVICE.CLEARINGHOUSE">{{ serviceLabel(CLAIM_SERVICE.CLEARINGHOUSE) }}</option>-->
                    <option value="-" disabled>-</option>
                    <option value="-" disabled>Offline:</option>
                    <option :value="CLAIM_SERVICE.EMAIL">{{ serviceLabel(CLAIM_SERVICE.EMAIL) }}</option>
                    <option :value="CLAIM_SERVICE.FAX">{{ serviceLabel(CLAIM_SERVICE.FAX) }}</option>
                </b-select>
            </b-form-group>
        </confirm-modal>

        <confirm-modal title="Offline Transmission" ref="confirmManualTransmission" yesButton="Okay">
            <p>Based on the transmission type for this Invoice, this will assume you have sent in via E-Mail/Fax.</p>
        </confirm-modal>

        <b-modal id="missingFieldsModal"
                 title="Missing Data Requirements"
                 v-model="missingFieldsModal"
                 no-close-on-backdrop
        >
            <div v-if="missingFieldGroup(['business'])" class="mb-4">
                <h4>Business Settings</h4>
                <b-form-group v-if="missingField('business_ein')" label="Business EIN">
                    <b-form-input
                        name="business_ein"
                        type="text"
                        v-model="missingForm.business_ein"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="business_ein" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('business_zip')" label="Business Zipcode">
                    <b-form-input
                        name="business_zip"
                        type="text"
                        v-model="missingForm.business_zip"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="business_zip" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('business_medicaid_id')" label="Medicaid ID">
                    <b-form-input
                        name="business_medicaid_id"
                        type="text"
                        v-model="missingForm.business_medicaid_id"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="business_medicaid_id" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('business_medicaid_npi_number')" label="Medicaid NPI Number">
                    <b-form-input
                        name="business_medicaid_npi_number"
                        type="text"
                        v-model="missingForm.business_medicaid_npi_number"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="business_medicaid_npi_number" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('business_medicaid_npi_taxonomy')" label="Medicaid NPI Taxonomy">
                    <b-form-input
                        name="business_medicaid_npi_taxonomy"
                        type="text"
                        v-model="missingForm.business_medicaid_npi_taxonomy"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="business_medicaid_npi_taxonomy" text="" />
                </b-form-group>
            </div>

            <div v-if="missingFieldGroup(['client'])" class="mb-4">
                <h4>Client Settings</h4>
                <b-form-group v-if="missingField('client_date_of_birth')" label="Date of Birth">
                    <b-form-input
                        name="client_date_of_birth"
                        type="text"
                        v-model="missingForm.client_date_of_birth"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="client_date_of_birth" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('client_medicaid_id')" label="Medicaid ID">
                    <b-form-input
                        name="client_medicaid_id"
                        type="text"
                        v-model="missingForm.client_medicaid_id"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="client_medicaid_id" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('client_medicaid_payer_id')" :label="`MCO / Payer Identifier (<a href='https://s3.amazonaws.com/hhaxsupport/SupportDocs/EDI+Guides/EDI+Code+Table+Guides/EDI+Code+Table+Guide_Florida.pdf' target='_blank'>Code Guides: HHA</a>)`">
                    <b-form-input
                        name="client_medicaid_payer_id"
                        type="text"
                        v-model="missingForm.client_medicaid_payer_id"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="client_medicaid_payer_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)" />
                </b-form-group>
                <b-form-group v-if="missingField('client_medicaid_plan_id')" label="Plan Identifier">
                    <b-form-input
                        name="client_medicaid_plan_id"
                        type="text"
                        v-model="missingForm.client_medicaid_plan_id"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="client_medicaid_plan_id" text="For use only if you are submitting a private pay claim for reimbursement (not common)" />
                </b-form-group>
                <b-form-group v-if="missingField('client_medicaid_diagnosis_codes')" label="Medicaid Diagnosis Codes">
                    <b-form-input
                        name="client_medicaid_diagnosis_codes"
                        type="text"
                        v-model="missingForm.client_medicaid_diagnosis_codes"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="client_medicaid_diagnosis_codes" text="Note: Supports multiple using commas" />
                </b-form-group>
            </div>

            <div v-if="missingFieldGroup(['payer'])" class="mb-4">
                <h4>Payer Settings</h4>
                <b-form-group v-if="missingField('payer_payer_code')" :label="`MCO / Payer Identifier (<a href='https://s3.amazonaws.com/hhaxsupport/SupportDocs/EDI+Guides/EDI+Code+Table+Guides/EDI+Code+Table+Guide_Florida.pdf' target='_blank'>Code Guides: HHA</a>)`">
                    <b-form-input
                        name="payer_payer_code"
                        type="text"
                        v-model="missingForm.payer_payer_code"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="payer_payer_code" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('payer_plan_code')" label="Plan Identifier">
                    <b-form-input
                        name="payer_plan_code"
                        type="text"
                        v-model="missingForm.payer_plan_code"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="payer_plan_code" text="" />
                </b-form-group>
            </div>

            <div v-if="missingFieldGroup(['credentials'])" class="mb-4">
                <h4>Transmission Credentials</h4>
                <b-form-group v-if="missingField('credentials_hha_username')" label="HHA Username">
                    <b-form-input
                        name="credentials_hha_username"
                        type="text"
                        v-model="missingForm.credentials_hha_username"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="credentials_hha_username" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('credentials_hha_password')" label="HHA Password">
                    <b-form-input
                        name="credentials_hha_password"
                        type="text"
                        v-model="missingForm.credentials_hha_password"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="credentials_hha_password" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('credentials_tellus_username')" label="Tellus Username">
                    <b-form-input
                        name="credentials_tellus_username"
                        type="text"
                        v-model="missingForm.credentials_tellus_username"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="credentials_tellus_username" text="" />
                </b-form-group>
                <b-form-group v-if="missingField('credentials_tellus_password')" label="Tellus Password">
                    <b-form-input
                        name="credentials_tellus_password"
                        type="text"
                        v-model="missingForm.credentials_tellus_password"
                        max="255"
                        :disabled="missingForm.busy"
                    />
                    <input-help :form="missingForm" field="credentials_tellus_password" text="" />
                </b-form-group>
            </div>

            <div slot="modal-footer">
                <b-btn variant="default" @click="missingFieldsModal = false" :disabled="missingForm.busy">Cancel</b-btn>
                <b-btn variant="info" @click="updateMissingFields()" :disabled="missingForm.busy">Save Changes</b-btn>
            </div>
        </b-modal>

        <a href="#" target="_blank" ref="open_test_link" class="d-none"></a>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../components/business/BusinessLocationFormGroup';
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import Constants from '../../mixins/Constants';

    export default {
        components: { BusinessLocationFormGroup },
        mixins: [FormatsDates, FormatsNumbers, Constants],

        data() {
            return {
                sortBy: 'shift_time',
                sortDesc: false,
                filter: null,
                loaded: -1,
                start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                invoiceType: "",
                items: [],
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
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
                        formatter: (val) => val ? val.name : 'None',
                        sortable: true,
                    },
                    {
                        key: 'amount',
                        label: 'Claim Total',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    // {
                    //     key: 'balance',
                    //     label: 'Invoice Balance',
                    //     formatter: (val) => this.moneyFormat(val),
                    //     sortable: true,
                    // },
                    {
                        key: 'claim_status',
                        formatter: (x) => _.capitalize(_.startCase(x)),
                        sortable: true,
                    },
                    {
                        key: 'claim_service',
                        label: 'Claim Service',
                        formatter: (x) => this.serviceLabel(x),
                        sortable: true,
                    },
                    {
                        key: 'claim_balance',
                        label: 'Claim Balance',
                        formatter: (val) => this.moneyFormat(val),
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        sortable: false,
                    },
                ],
                loadingClients: false,
                clients: [],
                clientFilter: '',
                payers: [],
                payerFilter: '',
                businesses: '',
                loadingPayers: false,
                paymentModal: false,
                form: new Form({
                    type: '',
                    payment_date: moment().format('MM/DD/YYYY'),
                    amount: 0.00,
                    reference: '',
                }),
                selectedInvoice: {},
                busy: false,
                transmittingId: null,
                selectedTransmissionMethod: '',
                payFullBalance: false,
                transmissionPrivate: false,
                missingFieldsModal: false,
                missingForm: new Form({}),
            }
        },

        mounted() {
            this.loadClients();
            this.fetchPayers();
        },

        computed: {
            filteredItems() {
                return this.items.map(item => {
                    return item;
                });
            },
        },

        methods: {
            missingFieldGroup(group) {
                for (let field of Object.keys(this.missingForm.originalData)) {
                    if (field.startsWith(group + '_')) {
                        return true;
                    }
                }
                return false;
            },

            missingField(field) {
                return this.missingForm.originalData.hasOwnProperty(field)
            },

            showMissingFieldsModal(errors, invoice) {
                let fields = {invoice: invoice.id};

                if (errors.hasOwnProperty('business')) {
                    for (let field of errors.business) {
                        fields['business_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('client')) {
                    for (let field of errors.client) {
                        fields['client_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('payer')) {
                    for (let field of errors.payer) {
                        fields['payer_' + field] = '';
                    }
                }

                if (errors.hasOwnProperty('credentials')) {
                    for (let field of errors.credentials) {
                        fields['credentials_' + field] = '';
                    }
                }

                this.missingForm = new Form(fields);
                this.missingFieldsModal = true;
            },

            updateMissingFields() {
                this.missingForm.patch(`claims-ar/${this.missingForm.invoice}/update-missing-fields`)
                    .then( ({ data }) => {
                        this.missingFieldsModal = false;
                    })
                    .catch(e => {})
                    .finally(() => {});
            },

            serviceLabel(serviceValue) {
                switch (serviceValue) {
                    case this.CLAIM_SERVICE.HHA: return 'HHAeXchange';
                    case this.CLAIM_SERVICE.TELLUS: return 'Tellus';
                    case this.CLAIM_SERVICE.CLEARINGHOUSE: return 'CareExchange LTC Clearinghouse';
                    case this.CLAIM_SERVICE.EMAIL: return 'E-Mail';
                    case this.CLAIM_SERVICE.FAX: return 'Fax';
                    default:
                        return '-';
                }
            },

            transmitClaim(invoice, skipAlert = false) {
                if (! skipAlert) {
                    if (invoice.payer && [this.PRIVATE_PAY_ID, this.OFFLINE_PAY_ID].includes(invoice.payer.id)) {
                        // offline and private pay Payer objects have no transmission method set
                        // so we allow the user to select which method they would like to use
                        this.selectedTransmissionMethod = '';
                        this.transmissionPrivate = true;
                        this.$refs.confirmTransmissionMethod.confirm(() => {
                            this.transmitClaim(invoice, true);
                        });
                        return;
                    }

                    if (invoice.payer && ! invoice.payer.transmission_method) {
                        // if no transmission method set up for the payer, allow them to choose
                        this.selectedTransmissionMethod = '';
                        this.transmissionPrivate = false;
                        this.$refs.confirmTransmissionMethod.confirm(() => {
                            this.transmitClaim(invoice, true);
                        });
                        return;
                    }

                    if (invoice.payer && [this.CLAIM_SERVICE.EMAIL, this.CLAIM_SERVICE.FAX].includes(invoice.payer.transmission_method)) {
                        this.$refs.confirmManualTransmission.confirm(() => {
                            this.transmitClaim(invoice, true);
                        });
                        return;
                    }
                }

                this.busy = true;
                this.transmittingId = invoice.id;
                let form = new Form({
                    method: this.selectedTransmissionMethod,
                });
                form.post(`/business/claims-ar/${invoice.id}/transmit`)
                    .then( ({ data }) => {
                        // success
                        if (data.data.test_result) {
                            // test mode
                            this.$refs.open_test_link.href = data.data.test_result;
                            this.$refs.open_test_link.click();
                        }
                        let index = this.items.findIndex(x => x.id == invoice.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data.claim);
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

            async fetchPayers() {
                this.payers = [];
                this.loadingPayers = true;
                let response = await axios.get('/business/payers?json=1');
                if (Array.isArray(response.data)) {
                    this.payers = response.data;
                } else {
                    this.payers = [];
                }
                this.loadingPayers = false;
            },

            async loadClients() {
                this.clients = [];
                this.loadingClients = true;
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
                this.loadingClients = false;
            },

            showPaymentModal(invoice) {
                this.payFullBalance = false;
                this.selectedInvoice = invoice;
                this.paymentModal = true;
            },

            cancelPayment() {
                this.paymentModal = false;
                this.form.reset(true);
            },

            applyPayment() {
                if (! confirm('Are you sure you wish to apply payment to this claim?')) {
                    return ;
                }

                this.form.busy = true;
                this.form.post(`/business/claims-ar/${this.selectedInvoice.id}/pay`)
                    .then( ({ data }) => {
                        console.log('payment response', data);
                        let index = this.items.findIndex(x => x.id == this.selectedInvoice.id);
                        if (index >= 0) {
                            this.items.splice(index, 1, data.data);
                        }
                        this.paymentModal = false;
                        this.form.reset(true);
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.form.busy = false;
                    });
            },

            async loadItems() {
                this.loaded = 0;
                let url = `/business/claims-ar?json=1&businesses=${this.businesses}&start_date=${this.start_date}&end_date=${this.end_date}&invoiceType=${this.invoiceType}&client_id=${this.clientFilter}&payer_id=${this.payerFilter}`;
                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                        this.items = [];
                    })
                    .finally(() => {
                        this.loaded = 1;
                    });
            },

            invoiceUrl(invoice, view="") {
                return `/business/client/invoices/${invoice.id}/${view}`;
            },

            claimInvoiceUrl(invoice, view="") {
                if (! invoice.claim) {
                    return;
                }

                return `/business/claims-ar/invoices/${invoice.claim.id}/${view}`;
            },

            updateFullBalance() {
                if (this.payFullBalance) {
                    this.form.amount = this.selectedInvoice.claim_balance;
                }
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
    .fa-check-square-o { color: green; }
    .fa-times-rectangle-o { color: darkred; }
</style>
