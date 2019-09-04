<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Filter Claims"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent=" loadItems() ">
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
                            class="mr-1 mt-1"
                        >
                            <option value="">All Invoices</option>
                            <option value="unpaid">Unpaid Invoices</option>
                            <option value="paid">Paid Invoices</option>
                            <option value="overpaid">Overpaid Invoices</option>
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
                <a href="/business/reports/claims-ar-aging" target="_blank">View Aging Report</a>
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
        <div class="table-responsive" v-if=" loaded > 0 ">

            <b-table bordered striped hover show-empty
                :items="filteredItems"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="name" scope="row">
                    <a :href=" invoiceUrl( row.item ) " target="_blank">{{ row.value }}</a>
                </template>
                <template slot="client" scope="row">
                    <a :href="`/business/clients/${row.item.client.id}`" target="_blank">{{ ( row.item.claim ? row.item.client_name : row.item.client.name ) }}</a>
                </template>
                <template slot="claim_status" scope="row">
                    {{ row.item.claim ? row.item.claim_status : '-' }}
                </template>
                <template slot="claim" scope="row">
                    <a v-if=" row.item.claim " :href="`/business/claims/${row.item.claim.id}/`" target="_blank">{{ row.item.claim.name }}</a>
                    <span v-else> - </span>
                </template>
                <template slot="payer" scope="row">
                    <span v-if="row.item.claim">
                        {{ row.item.claim.payer_name }}
                    </span>
                    <span v-else>
                        {{ row.item.payer ? row.item.payer.name : 'N/A' }}
                    </span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn v-if=" !row.item.claim " variant="success" class="flex-1 my-1" @click=" createClaim( row.item ) " :disabled=" busy " size="sm">
                        <i v-if="row.item.id === creatingId" class="fa fa-spin fa-spinner"></i>
                        <span>Create Claim</span>
                    </b-btn>
                    <div v-else-if=" row.item.claim && row.item.claim.status == 'CREATED' " class="d-flex">
<!--                        <b-btn variant="info" style="flex:1" class="m-1" @click.stop=" editClaimModal( row.item.claim ) " :disabled=" busy " size="sm">-->
<!--                            <i class="fa fa-edit"></i>-->
<!--                        </b-btn>-->
                        <b-btn variant="info" class="my-1" :href="`/business/claims/${row.item.claim.id}/edit`" size="sm">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn variant="danger" class="my-1" @click="deleteClaimModal(row.item)" :disabled="busy" size="sm">
                            <i v-if="row.item.id === deletingId" class="fa fa-spin fa-spinner"></i>
                            <i v-else class="fa fa-times"></i>
                        </b-btn>
                    </div>
                    <!--
                    <b-btn v-else-if=" row.item.claim.status == 'CREATED' " variant="primary" class="flex-1 my-1" @click=" transmitClaim( row.item ) " :disabled="busy">
                        <i v-if="row.item.id === transmittingId" class="fa fa-spin fa-spinner"></i>
                        <span>Transmit Claim</span>
                    </b-btn>
                    <b-btn v-else-if="( row.item.claim && row.item.claim.status != 'CREATED' ) && isAdmin " variant="primary" class="flex-1 my-1" @click=" transmitClaim( row.item ) " :disabled="busy">
                        <i v-if="row.item.id === transmittingId" class="fa fa-spin fa-spinner"></i>
                        <span>Re-Transmit Claim</span>
                    </b-btn>
                    <b-btn v-if="row.item.claim && row.item.claim.status != 'CREATED'" variant="success" class="flex-1 my-1" @click=" showPaymentModal( row.item )">Apply Payment</b-btn>
                    <b-btn v-if="row.item.claim" variant="secondary" class="flex-1 my-1" :href="claimInvoiceUrl(row.item)" target="_blank">View Claim Invoice</b-btn>
                    <b-btn v-if="row.item.claim" variant="secondary" class="flex-1 my-1" :href="claimInvoiceUrl(row.item, 'pdf')" target="_blank">Download Claim Invoice</b-btn>
                    -->
                </template>
            </b-table>
        </div>

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

        <!-- Details modal -->
        <edit-claim-modal
            v-model="editClaimModalOpen"
            :claim="editingClaim"
            :transmitUpdate="updateClaim"
            :transmitDelete="deleteClaimItem"
            :transmitEditItem="editClaimItem"
        />

        <confirm-modal title="Delete Claim" ref="confirmDeleteClaim" yesButton="Delete">
            <p>Are you sure you want to delete this claim?</p>
        </confirm-modal>

        <a href="#" target="_blank" ref="open_test_link" class="d-none"></a>
    </b-card>
</template>

<script>

    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import Constants from '../../../mixins/Constants';
    import EditClaimModal from "../modals/EditClaimModal";

    export default {
        components: {BusinessLocationFormGroup, EditClaimModal},
        mixins: [FormatsDates, FormatsNumbers, Constants],

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
                        formatter: (x) => _.capitalize(_.startCase(x)),
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
                transmissionPrivate: false,

                editClaimModalOpen: false,
                editingClaim: {},
            }
        },

        computed: {
            filteredItems() {
                return this.items.map(item => {
                    return item;
                });
            },
        },

        methods: {
            serviceLabel(serviceValue) {
                switch (serviceValue) {
                    case this.CLAIM_SERVICE.HHA:
                        return 'HHAeXchange';
                    case this.CLAIM_SERVICE.TELLUS:
                        return 'Tellus';
                    case this.CLAIM_SERVICE.CLEARINGHOUSE:
                        return 'CareExchange LTC Clearinghouse';
                    case this.CLAIM_SERVICE.EMAIL:
                        return 'E-Mail';
                    case this.CLAIM_SERVICE.FAX:
                        return 'Fax';
                    default:
                        return '-';
                }
            },

            createClaim(invoice) {

                this.creatingId = invoice.id;
                let form = new Form({client_invoice_id: invoice.id});
                form.post(`/business/claims`)
                    .then(({data}) => {
                        let claim = data.data.claim;
                        console.log('created claim: ', claim);

                        let item = this.items.find(item => item.id == claim.client_invoice_id);
                        // manually set the attributes that the claim-resource does..
                        item.claim_total = this.moneyFormat(claim.amount, '$', true);
                        item.claim_paid = this.moneyFormat(claim.amount - claim.amount_due, '$', true);
                        item.claim_balance = this.moneyFormat(claim.amount_due, '$', true);
                        item.claim_status = claim.status;
                        item.claim_status = claim.status;
                        item.claim_date = this.formatDateFromUTC(claim.created_at, 'MM/DD/YYYY h:mm a', null, true);
                        item.claim = claim;
                        item.client_name = _.upperFirst(claim.client_first_name) + ' ' + _.upperFirst(claim.client_last_name);
                    })
                    .catch(() => {

                    })
                    .finally(() => {

                        this.creatingId = null;
                    })
            },

            deleteClaimModal(invoice) {
                this.$refs.confirmDeleteClaim.confirm(() => {
                    this.deleteClaim(invoice);
                });
            },

            deleteClaim(invoice) {
                if (invoice.claim && invoice.claim.status == 'CREATED') {
                    this.deletingId = invoice.id;
                    let form = new Form({});
                    form.submit('delete', `/business/claims/${invoice.claim.id}`)
                        .then( ({ data }) => {
                            let item = this.items.find(item => item.id == invoice.id);
                            item.claim_total = null;
                            item.claim_paid = null;
                            item.claim_balance = null;
                            item.claim_status = null;
                            item.client_name = null;
                            item.claim_date = null;
                            item.claim = null;
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.deletingId = null;
                        })
                }
            },

            editClaimModal(claim) {
                this.busy = true;
                axios.get('/business/claims/' + claim.id + '/edit')
                    .then(res => {
                        let claim = res.data;
                        claim.items.forEach(item => {
                            item.removing = false;
                            item.editing = false;
                        });
                        this.editingClaim = claim;
                        this.editClaimModalOpen = true;
                    })
                    .catch(err => {
                        console.err(err);
                        alert('Problem loading claim details..');
                    }).finally(() => this.busy = false);
            },

            updateClaim(newData) {
                // for when claim-info is edited
                let invoice = this.items.find(invoice => {
                    return invoice.claim && invoice.claim.id == newData.id;
                });

                invoice.claim = {
                    ...invoice.claim,
                    ...newData
                };

                invoice.client_name = _.upperFirst(newData.client_first_name) + ' ' + _.upperFirst(newData.client_last_name);
            },

            editClaimItem(data) {
                // for when a claim item is edited, contains the changed amount of the item
                console.log('transmitted edit item..', data);
                let invoice = this.items.find(client_invoice => client_invoice.claim && client_invoice.claim.id == data.claim_invoice_id);

                // // the value in the claim is always an int, not formatted
                let current_claim_total = invoice.claim.amount;
                let current_claim_balance = invoice.claim.amount_due;

                current_claim_total -= parseFloat(data.changed_amount);
                current_claim_balance -= parseFloat(data.changed_amount);

                invoice.claim_balance = this.moneyFormat(current_claim_balance, '$', true);
                invoice.claim_total = this.moneyFormat(current_claim_total, '$', true);
            },

            deleteClaimItem(item) {
                // for when a claim item is deleted from a claim
                let invoice = this.items.find(client_invoice => client_invoice.claim && client_invoice.claim.id == item.claim_invoice_id);

                // the value in the claim is always an int, not formatted
                let current_claim_total = invoice.claim.amount;
                let current_claim_balance = invoice.claim.amount_due;

                current_claim_total -= parseFloat(item.amount);
                current_claim_balance -= parseFloat(item.amount_due);
                let current_claim_paid = current_claim_total - current_claim_balance;

                invoice.claim.amount = current_claim_total;
                invoice.claim.amount_due = current_claim_balance;

                invoice.claim_balance = this.moneyFormat(current_claim_balance, '$', true);
                invoice.claim_total = this.moneyFormat(current_claim_total, '$', true);
                invoice.claim_paid = this.moneyFormat(current_claim_paid, '$', true);
            },

            transmitClaim(invoice, skipAlert = false) {
                if (true) return;

                if (!skipAlert) {

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

                    if (invoice.payer && !invoice.payer.transmission_method) {
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
                    .then(({data}) => {
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

            async loadItems() {
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

            invoiceUrl(invoice, view = "") {
                return `/business/client/invoices/${invoice.id}/${view}`;
            },

            claimInvoiceUrl(invoice, view = "") {
                if (!invoice.claim) {

                    return;
                }

                return `/business/claims-ar/invoices/${invoice.claim.id}/${view}`;
            },

            updateFullBalance() {
            },
        },

        watch: {
            // editClaimModalOpen: function( newVal, oldVal ){
            //     // when the modal closes make sure the editing cliam is up-to-date

            //     if( newVal === false ){

            //         let claim = this.items.find( claim => claim.id == this.editingClaim.id );
            //         claim = {

            //             ...claim,
            //             ...this.editingClaim,
            //             client_name : _.upperFirst( this.editingClaim.client_first_name ) + ' ' + _.upperFirst( this.editingClaim.client_last_name )
            //         };

            //         console.log( 'editing: ', this.editingClaim );
            //         console.log( 'claim: ', claim );
            //     }
            // }
        },

        async mounted() {
            await this.loadClients();
            await this.fetchPayers();

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
                this.loadItems();
            }
        }
    }
</script>

<style>
    td.actions-column {
        display: flex;
        flex-direction: column;
    }
    table:not(.form-check) {
        font-size: 14px;
    }
    .fa-check-square-o {
        color: green;
    }
    .fa-times-rectangle-o {
        color: darkred;
    }
</style>
