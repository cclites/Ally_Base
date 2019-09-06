<template>
    <b-card header="Claim Remits"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row>
            <b-col lg="12">
                <b-form inline class="mb-4">
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

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="remits"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="actions" scope="row">
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
        },

        methods: {
            async fetch() {
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
        },

        async mounted() {
            this.loading = true;
            await this.fetchPayers();
            this.loading = false;
        }
    }
</script>
