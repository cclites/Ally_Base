<template>
    <b-card header="Remit Application Report"
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
                        :allow-all="false"
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
                            <option value="">-- All Payment Types --</option>
                        </template>
                    </b-form-select>

                    <payer-dropdown v-model="filters.payer_id" class="mr-1 mt-1" empty-text="-- All Payers --" />

                    <b-btn variant="info" class="mt-1" :disabled="filters.busy" @click.prevent="fetch()">Generate</b-btn>
                </b-form>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col lg="6">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
            <b-col lg="6">
            </b-col>
        </b-row>

        <loading-card v-if="filters.busy" />
        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
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
                <template slot="row-details" scope="row">
                        <!---------- SUB TABLE --------------->
                        <b-table bordered striped show-empty
                            :items="row.item.remits"
                            :fields="subFields"
                        >
                        </b-table>
                      <!---------- /END SUB TABLE --------------->
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from '../../BusinessLocationFormGroup';
    import FormatsStrings from "../../../../mixins/FormatsStrings";
    import FormatsNumbers from "../../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../../mixins/FormatsDates";
    import Constants from '../../../../mixins/Constants';
    import ClaimRemitForm from "../ClaimRemitForm";
    import ClaimRemitAdjustmentForm from "../ClaimRemitAdjustmentForm";

    export default {
        components: {BusinessLocationFormGroup, ClaimRemitForm, ClaimRemitAdjustmentForm},
        mixins: [FormatsDates, FormatsNumbers, Constants, FormatsStrings],

        data() {
            return {
                items: [],
                sortBy: 'payer_name',
                sortDesc: false,
                filter: '',
                fields: {
                    expand: { label: ' ', sortable: false, },
                    payer_name: { label: 'Payer', sortable: true, formatter: x => x ? x : '(No Payer)' },
                    remit_count: { label: 'Total Payments', sortable: true, },
                    // applied: { sortable: true },
                    amount: { label: 'Total Amount', sortable: true, formatter: x => this.moneyFormat(x) },
                    available: { label: 'Total Amount Available', sortable: true, formatter: x => this.moneyFormat(x) },
                },
                subFields: {
                    id: { sortable: true, label: 'ID' },
                    payment_type: { sortable: true, formatter: x => this.resolveOption(x, this.claimRemitTypeOptions) },
                    date: { sortable: true, label: 'Payment Date', formatter: x => this.formatDateFromUTC(x) },
                    reference: { sortable: true, label: 'Reference #' },
                    status: { sortable: true, formatter: x => this.resolveOption(x, this.claimRemitStatusOptions) },
                    amount_applied: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    // amount_available: { sortable: true, formatter: x => this.moneyFormat(x) },
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
                showEditModal: false,
                showAdjustmentModal: false,
                deletingId: null,
            }
        },

        computed: {
            emptyText() {
                return this.filters.hasBeenSubmitted ? 'There are no results to display.' : 'Select filters and press Generate.';
            }
        },

        methods: {
            async fetch() {
                this.filters.get(`/business/reports/claims/remit-application`)
                    .then( ({ data }) => {
                        this.items = data.results.map(item => {
                            // item._showDetails = true;
                            return item;
                        });
                    })
                    .catch(() => {
                        this.items = [];
                    });
            },
        },

        async mounted() {
            this.fetch();
        }
    }
</script>
