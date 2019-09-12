<template>
    <b-card header="Claim Remits"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row>
            <b-col lg="12">
                <b-form-checkbox v-model="filters.all">
                    Show all Remits with amount available to apply
                </b-form-checkbox>
                <b-form v-show="! filters.all" inline class="mb-4">
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
                    />
                        &nbsp;to&nbsp;
                    <date-picker
                        v-model="filters.end_date"
                        placeholder="End Date"
                        class="mr-1 mt-1"
                    />
                    <b-form-select
                        v-model="filters.type"
                        :options="claimRemitTypeOptions"
                        class="mr-1 mt-1"
                    >
                        <template slot="first">
                            <option value="">-- Any Payment Type --</option>
                        </template>
                    </b-form-select>
                    <b-form-select
                        v-model="filters.status"
                        :options="claimRemitStatusOptions"
                        class="mr-1 mt-1"
                    >
                        <template slot="first">
                            <option value="">-- Any Status --</option>
                        </template>
                    </b-form-select>
                    <b-form-select v-model="filters.payer_id" class="mr-1 mt-1">
                        <option value="">-- Any Payer --</option>
                        <option value="0">(Client)</option>
                        <option v-for="item in payers" :key="item.id" :value="item.id">{{ item.name }}
                        </option>
                    </b-form-select>
                    <b-input
                        v-model="filters.reference"
                        placeholder="Reference #"
                        class="mr-1 mt-1"
                    />

                    <b-btn variant="info" class="mt-1" :disabled="filters.busy" @click.prevent="fetch()">Generate</b-btn>
                </b-form>
                <div v-show="filters.all" class="mb-3">
                    <b-btn variant="info" class="mt-1" :disabled="filters.busy" @click.prevent="fetch()">Refresh</b-btn>
                </div>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6">
                <div class="d-flex">
                    <b-btn variant="info" class="ml-auto" @click="add()">
                        <i class="fa fa-plus" /> Add Remit
                    </b-btn>
                </div>
            </b-col>
        </b-row>

        <loading-card v-if="filters.busy" />
        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="remits"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
                :empty-text="emptyText"
            >
                <template slot="actions" scope="row">
                    <b-btn variant="success" size="sm" :href="`/business/claim-remits/${row.item.id}`">Apply</b-btn>
                    <b-btn variant="secondary" size="sm" @click="edit(row.item)"><i class="fa fa-edit" /></b-btn>
                </template>
            </b-table>
        </div>

<!--        <confirm-modal title="Delete Claim" ref="confirmDeleteClaim" yesButton="Delete">-->
<!--            <p>Are you sure you want to delete this claim?</p>-->
<!--        </confirm-modal>-->

        <b-modal id="editRemitModal"
            :title="modalTitle"
            v-model="showEditModal"
            :no-close-on-backdrop="true"
            hide-footer
            size="lg"
        >
            <claim-remit-form
                ref="remit-form"
                @close="hideEdit()"
                :remit="remit"
                :payers="payers"
                @added="fetch()"
                @updated="fetch()"
            />
        </b-modal>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import Constants from '../../../mixins/Constants';
    import { mapGetters } from 'vuex';
    import ClaimRemitForm from "./ClaimRemitForm";
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        components: {BusinessLocationFormGroup, ClaimRemitForm},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                sortBy: 'date',
                sortDesc: false,
                filter: '',
                fields: {
                    id: { sortable: true, label: 'ID' },
                    office_location: { sortable: true },
                    date: { sortable: true, label: 'Payment Date', formatter: x => this.formatDateFromUTC(x) },
                    payment_type: { sortable: true, formatter: x => this.resolveOption(x, this.claimRemitTypeOptions) },
                    payer_name: { label: 'Payer', sortable: true, formatter: x => x ? x : '-' },
                    reference: { sortable: true, label: 'Reference #' },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_available: { sortable: true, formatter: x => this.moneyFormat(x) },
                    status: { sortable: true, formatter: x => this.resolveOption(x, this.claimRemitStatusOptions) },
                    created_at: { sortable: true, label: 'Date Added', formatter: x => this.formatDateFromUTC(x) },
                    actions: { sortable: false },
                },
                filters: new Form({
                    all: true,
                    type: '',
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    reference: '',
                    payer_id: '',
                    businesses: '',
                    status: '',
                    json: 1,
                }),
                payers: [],
                showEditModal: false,
            }
        },

        computed: {
            ...mapGetters({
                remits: 'claims/remits',
                remit: 'claims/remit',
            }),

            modalTitle() {
                return this.remit.id ? 'Edit Remit' : 'Add Remit';
            },

            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            }
        },

        methods: {
            async fetch() {
                if (this.filters.all) {
                    // Ensure business dropdown is set to all locations.
                    this.filters.businesses = '';
                }

                this.filters.get(`/business/claim-remits`)
                    .then( ({ data }) => {
                        this.$store.commit('claims/setRemits', data.data);
                    })
                    .catch(() => {});
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

            edit(item) {
                this.$store.commit('claims/setRemit', item);
                this.showEditModal = true;
            },

            hideEdit() {
                this.showEditModal = false;
                this.$store.commit('claims/setItem', {});
            },

            add() {
                this.edit({
                    id: null,
                    type: this.CLAIM_REMIT_TYPES.REMIT,
                });
            },

            handleAddedRemit(item) {

            },

            handleUpdatedRemit(item) {

            }
        },

        async mounted() {
            this.loading = true;
            await this.fetchPayers();
            this.loading = false;

            this.filters.businesses = '';
            this.fetch();
        }
    }
</script>
