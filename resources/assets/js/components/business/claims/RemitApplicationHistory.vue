<template>
    <b-card header="Remit Application History"
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
                        <td>{{ formatDateFromUTC(remit.date) }}</td>
                        <td><strong>Reference #</strong></td>
                        <td>{{ remit.reference }}</td>
                    </tr><tr>
                        <td><strong>Total Amount</strong></td>
                        <td>{{ moneyFormat(remit.amount) }}</td>
                        <td><strong>Amount Available</strong></td>
                        <td>{{ moneyFormat(remit.amount_available) }}</td>
                    </tr><tr>
                        <td><strong>Amount Applied Towards Interest</strong></td>
                        <td>{{ moneyFormat(totalInterest) }}</td>
                        <td><strong>Amount Applied</strong></td>
                        <td>{{ moneyFormat(remit.amount_applied) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-responsive claims-table">
            <b-table bordered striped hover show-empty
                :items="claim_applications"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :filter="filter"
            >
                <template slot="client_name" scope="row">
                    <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                </template>
                <template slot="claim_invoice_name" scope="row">
                    <a :href="`/business/claims/${row.item.claim_invoice_id}/print`" target="_blank">{{ row.item.claim_invoice_name }}</a>
                </template>
                <template slot="item_total" scope="row">
                    <span v-if="row.item.is_interest">-</span>
                    <span v-else>{{ moneyFormat(row.item.item_total) }}</span>
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
    import { Decimal } from 'decimal.js';
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
                remit: 'claims/remit',
            }),

            totalInterest() {
                return this.claim_applications.reduce((carry, item) => {
                    if (! item.is_interest) {
                        return carry;
                    }

                    return carry.add(new Decimal(item.amount_applied));
                }, new Decimal(0.00));
            }
        },

        data() {
            return {
                claim_applications: [],
                filter: '',
                sortBy: 'created_at',
                sortDesc: true,
                fields: {
                    claim_invoice_name: { label: 'Claim #', sortable: true },
                    client_name: { label: 'Client', sortable: true },
                    item: { label: 'item', sortable: true },
                    item_total: { label: 'Total Cost', sortable: true },
                    amount_applied: { label: 'Amount Applied', sortable: true, formatter: x => this.moneyFormat(x) },
                    adjustment_type: { label: 'Type', sortable: true, formatter: x => this.resolveOption(x, this.claimAdjustmentTypeOptions) },
                    note: { label: 'Notes', sortable: true },
                    created_at: { label: 'Date', sortable: true, formatter: x => this.formatDateTimeFromUTC(x) },
                },
            }
        },

        created() {
            this.$store.commit('claims/setRemit', this.init.remit);
            this.claim_applications = this.init.claim_applications;
        },
    }
</script>
