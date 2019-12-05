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

        <div class="table-responsive mb-4">
            <h3>Claim Applications</h3>
            <b-table bordered striped hover show-empty
                :items="adjustments['applications']"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
            >
                <template slot="expand" scope="row">
                    <b-btn variant="secondary" size="sm" @click.stop="row.toggleDetails">
                        <i v-if="row.detailsShowing" class="fa fa-caret-down" />
                        <i v-else class="fa fa-caret-right" />
                    </b-btn>
                </template>
                <template slot="name" scope="row">
                    <a :href="`/business/claims/${row.item.id}/print`" target="_blank">{{ row.item.name }}</a>
                </template>
                <template slot="client_name" scope="row">
                    <a v-if="row.item.client_id" :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                    <span v-else>(Grouped)</span>
                </template>
                <template slot="client_invoice_id" scope="row">
                    <a v-if="row.item.client_invoice_id" :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice_name }}</a>
                    <span v-else>-</span>
                </template>
                <template slot="row-details" scope="row">
                <b-card>
                    <!---------- SUB TABLE --------------->
                    <b-table bordered striped show-empty
                        :items="row.item.items"
                        :fields="subFields"
                        sort-by="created_at"
                        :sort-desc="true"
                    >
                        <template slot="client_name" scope="row">
                            <a :href="`/business/clients/${row.item.client_id}`" target="_blank">{{ row.item.client_name }}</a>
                        </template>
                        <template slot="client_invoice_id" scope="row">
                            <a :href="`/business/client/invoices/${row.item.client_invoice_id}`" target="_blank">{{ row.item.client_invoice_name }}</a>
                        </template>
                    </b-table>
                  <!---------- /END SUB TABLE --------------->
                </b-card>
                </template>
            </b-table>
        </div>

        <div class="table-responsive mb-4">
            <h3>Applied Interest</h3>
            <b-table bordered striped hover show-empty
                :items="adjustments['interest']"
                :fields="interestFields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
            >
            </b-table>
        </div>

        <div class="table-responsive mb-4">
            <h3>Remit Adjustments</h3>
            <b-table bordered striped hover show-empty
                :items="adjustments['adjustments']"
                :fields="interestFields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
            >
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
                return this.adjustments['interest'].reduce((carry, item) => {
                    return carry.add(new Decimal(item.amount_applied));
                }, new Decimal(0.00));
            }
        },

        data() {
            return {
                invoices: [],
                adjustments: [],
                sortBy: 'client_invoice_date',
                sortDesc: false,
                fields: {
                    expand: { label: ' ', sortable: false, },
                    name: { label: 'Claim #', sortable: true },
                    created_at: { label: 'Claim Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    client_invoice_id: { label: 'Inv #', sortable: true },
                    client_invoice_date: { label: 'Inv Date', sortable: true, formatter: x => x ? this.formatDateFromUTC(x) : '-' },
                    client_name: { label: 'Client', sortable: true },
                    payer: { sortable: true, formatter: x => x ? x.name : '-' },
                    amount: { label: 'Claim Total', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Claim Balance', sortable: true, formatter: x => this.moneyFormat(x) },
                },
                subFields: {
                    client_invoice_id: { label: 'Inv #', sortable: true },
                    client_invoice_date: { label: 'Inv Date', sortable: true, formatter: x => x ? this.formatDateFromUTC(x) : '-' },
                    item: { label: 'Item', sortable: true },
                    client_name: { label: 'Client', sortable: true },
                    item_total: { label: 'Total Cost', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount Applied', sortable: true, formatter: x => this.moneyFormat(x) },
                    adjustment_type: { label: 'Type', sortable: true, formatter: x => this.resolveOption(x, this.claimAdjustmentTypeOptions) },
                    note: { label: 'Notes', sortable: true },
                    created_at: { label: 'Date', sortable: true, formatter: x => this.formatDateTimeFromUTC(x) },
                },
                interestFields: {
                    amount_applied: { label: 'Amount Applied', sortable: true, formatter: x => this.moneyFormat(x) },
                    adjustment_type: { label: 'Type', sortable: true, formatter: x => this.resolveOption(x, this.claimAdjustmentTypeOptions) },
                    note: { label: 'Notes', sortable: true },
                    created_at: { label: 'Date', sortable: true, formatter: x => this.formatDateTimeFromUTC(x) },
                },
            }
        },

        created() {
            this.$store.commit('claims/setRemit', this.init.remit);
            this.adjustments = this.init.adjustments;

            // this.invoices = _.groupBy(this.adjustments, x => {
            //     if (! x.claim_invoice_name && x.is_interest) {
            //         return 'Interest';
            //     } else if (! x.claim_invoice_id) {
            //         return 'Adjustments';
            //     }
            //     return x.claim_invoice_name;
            // });
            //
            // this.invoices = invoices..map((index, items) => {
            //     console.log('index: ', index, 'items:', items);
            // });
        },
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 14px;
    }
</style>