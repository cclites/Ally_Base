<template>
    <b-card header="Search Client Invoices"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-form inline @submit.prevent="fetch()" class="mb-4">
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
                id="invoice_type"
                v-model="filters.invoice_type"
                class="mr-1 mt-1"
            >
                <option value="">-- Invoice Status --</option>
                <option value="unpaid">Unpaid Invoices</option>
                <option value="paid">Paid Invoices</option>
                <option value="has_claim">Has Claim</option>
                <option value="no_claim">Does Not Have Claim</option>
            </b-form-select>

            <b-form-select v-model="filters.client_id" ref="clientFilter" class="mr-1 mt-1">
                <option value="">-- All Clients --</option>
                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
            </b-form-select>

            <b-form-checkbox v-model="filters.inactive" :value="1" :unchecked-value="0" class="mr-1 mt-1">
                Show Inactive Clients
            </b-form-checkbox>

            <b-input
                v-model="filters.invoice_id"
                placeholder="Invoice #"
                class="mr-1 mt-1"
            />

            <b-button type="submit" variant="info" class="mt-1" :disabled="filters.busy">Generate Report</b-button>
        </b-form>

        <loading-card v-if="filters.busy"></loading-card>

        <b-row v-if="!filters.hasBeenSubmitted">
            <b-col lg="12">
                <b-card class="text-center text-muted">
                    Select filters and press Generate Report
                </b-card>
            </b-col>
        </b-row>

        <div class="table-responsive" v-if="!filters.busy && filters.hasBeenSubmitted" style="min-height: 250px">
            <b-row class="mb-2">
                <b-col md="12">
                    <div class="form-inline">
                        <b-form-checkbox v-model="selectAll" class="mr-3">Select All</b-form-checkbox>
                        <b-form-select v-model="withSelected" class="mr-1">
                            <option value="">-- With Selected --</option>
                            <option value="group">Create a Grouped Claim</option>
<!--                            <option value="single">Create Claim For Each</option>-->
                        </b-form-select>
                        <b-btn variant="primary" class="mr-1" @click="processSelected()" :disabled="withSelected != 'group'">Process</b-btn>
                    </div>
                </b-col>
            </b-row>
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
            >
                <template slot="selector" scope="row">
                    <label class="custom-control custom-checkbox" style="padding-right: 25px">
                        <input type="checkbox" class="custom-control-input" v-model="row.item.selected" :value="true">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description"></span>
                    </label>
                </template>
                <template slot="name" scope="row">
                    <a :href="`/business/client/invoices/${row.item.id}/`" target="_blank">{{ row.value }}</a>
                </template>
                <template slot="client_name" scope="row">
                    <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                </template>
                <template slot="claim_name" scope="row">
                    <div class="text-nowrap">
                        <a v-if="row.item.claim" :href="`/business/claims/${row.item.claim_id}/print`" target="_blank">{{ row.item.claim_name }}</a>
                        <span v-else> - </span>
                    </div>
                </template>
                <template slot="actions" scope="row" class="text-nowrap">
                    <!-- CREATE BUTTON -->
                    <div v-if="! row.item.claim">
                        <b-btn variant="success" class="mr-1" @click="createClaim(row.item)" :disabled="filters.busy || creatingIds.length > 0" size="sm">
                            <i v-if="creatingIds.includes(row.item.id)" class="fa fa-spin fa-spinner" />&nbsp;Create Claim
                        </b-btn>
                    </div>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from '../../../mixins/Constants';
    import {mapGetters} from "vuex";

    export default {
        components: {BusinessLocationFormGroup},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                filters: new Form({
                    businesses: '',
                    start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    invoice_type: '',
                    client_id: '',
                    payer_id: '',
                    client_type: '',
                    invoice_id: '',
                    inactive: 0,
                    json: 1,
                }),
                creatingIds: [],
                selectAll: false,
                withSelected: '',
                // Table data:
                items: [],
                sortBy: 'created_at',
                sortDesc: false,
                fields: {
                    selector: { label: ' ', sortable: false },
                    created_at: { label: 'Invoice Date', sortable: true, formatter: (val) => this.formatDateFromUTC(val) },
                    name: { label: 'Invoice #', sortable: true },
                    client_name: { sortable: true },
                    payer_name: { sortable: true },
                    amount: { label: 'Invoiced Amt', sortable: true, formatter: (val) => this.moneyFormat(val, '$', true) },
                    is_paid: { label: 'Paid', sortable: true },
                    claim_name: { label: 'Claim #', sortable: false },
                    actions: { tdClass: 'actions-column', sortable: false },
                },
            }
        },

        computed: {
            ...mapGetters({
                clientList: 'filters/clientList',
            }),
            clients() {
                return this.clientList.filter(x => {
                    if (this.filters.client_type) {
                        if (x.client_type != this.filters.client_type) {
                            return false;
                        };
                    }

                    if (! this.filters.inactive) {
                        return x.active == 1;
                    }

                    return true;
                });
            },
        },

        methods: {
            processSelected() {
                // only option for selected is group claim
                let selected_ids = this.items.map(item => {
                    return item.selected ? item.id : null; 
                }).filter(x => x != null);

                this.creatingIds = selected_ids;

                let form = new Form({selected_ids});
                form.post(`/business/grouped-claims`)
                    .then( ({ data }) => {
                        console.log('response:', data);
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.creatingIds = [];
                    });
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
                this.creatingIds = [invoice.id];
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
                        this.creatingIds = [];
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
             * Fetch Client Invoice and Claim records for the Queue.
             * @returns {Promise<void>}
             */
            async fetch() {
                this.selectedAll = false;
                this.items = [];
                this.filters.get(`/business/claims-create`)
                    .then(({data}) => {
                        this.items = data.data;
                    })
                    .catch(e => {
                        this.items = [];
                    })
            },
        },

        async mounted() {
            this.$store.commit('filters/setBusiness', this.filters.businesses);
            await this.$store.dispatch('filters/fetchResources', ['clients']);
        },

        watch: {
            'form.businesses'(newValue, oldValue) {
                this.$store.commit('filters/setBusiness', newValue);
            },
            selectAll(newVal) {
                if (newVal) {
                    this.items = this.items.map(item => {
                        item.selected = true;
                        return item;
                    });
                } else {
                    this.items = this.items.map(item => {
                        item.selected = false;
                        return item;
                    });
                }
            },
        },
    }
</script>
