<template>
    <b-card header="Claim Adjustment History"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="max-width: 900px">
                <tbody>
                    <tr>
                        <td><strong>Claim #</strong></td>
                        <td><a :href="`/business/claims/${claim.id}/print`" target="_blank">{{ claim.name }}</a></td>
                        <td><strong>Related Invoice</strong></td>
                        <td><a :href="`/business/client/invoices/${claim.client_invoice_id}`" target="_blank">#{{ claim.client_invoice.name }}</a></td>
                    </tr><tr>
                        <td><strong>Client</strong></td>
                        <td><a :href="`/business/clients/${claim.client_id}`" target="_blank">{{ claim.client.name }}</a></td>
                        <td><strong>Payer</strong></td>
                        <td>{{ claim.payer.name }}</td>
                    </tr><tr>
                        <td><strong>Status</strong></td>
                        <td>{{ snakeToTitleCase(claim.status) }}</td>
                        <td><strong></strong></td>
                        <td></td>
                    </tr><tr>
                        <td><strong>Amount</strong></td>
                        <td>{{ moneyFormat(claim.amount) }}</td>
                        <td><strong>Amount Due</strong></td>
                        <td>{{ moneyFormat(claim.amount_due) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-responsive claims-table">
            <b-table bordered striped hover show-empty
                :items="adjustments"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="claim_remit_id" scope="row">
                    <span v-if="row.item.claim_remit_id">
                        <a :href="`/business/claim-remits/${row.item.claim_remit_id}`">#{{ row.item.claim_remit_id }}</a>
                    </span>
                    <span v-else>-</span>
                </template>
                <template slot="item_total" scope="row">
                    {{ moneyFormat(row.item.item_total) }}
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ FormatsDates, FormatsStrings, Constants, FormatsNumbers ],
        props: {
            init: {
                type: Object,
                required: true,
                default: () => {},
            },
        },

        computed: {
            ...mapGetters({
                claim: 'claims/claim',
            }),
        },

        data() {
            return {
                adjustments: [],
                filter: '',
                sortBy: 'created_at',
                sortDesc: true,
                fields: {
                    item: { label: 'item', sortable: true },
                    item_total: { label: 'Total Cost', sortable: true },
                    amount_applied: { label: 'Amount Applied', sortable: true, formatter: x => this.moneyFormat(x) },
                    claim_remit_id: { label: 'Remit', sortable: true },
                    adjustment_type: { label: 'Type', sortable: true, formatter: x => this.resolveOption(x, this.claimAdjustmentTypeOptions) },
                    note: { label: 'Notes', sortable: true },
                    created_at: { label: 'Date', sortable: true, formatter: x => this.formatDateTimeFromUTC(x) },
                },
            }
        },

        created() {
            this.$store.commit('claims/setClaim', this.init.claim);
            this.adjustments = this.init.adjustments;
        },
    }
</script>
