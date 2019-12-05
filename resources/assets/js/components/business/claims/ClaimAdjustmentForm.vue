<template>
    <div>
        <h2>Claim #{{ claim.name }}</h2>
        <div class="table-responsive claims-table">
            <b-table bordered striped show-empty
                class="fit-more"
                :items="items"
                :fields="fields"
                empty-text="This claim has no items."
            >
            <template slot="selected" scope="row">
                <b-form-checkbox v-model="row.item.selected"
                    :disabled="form.busy"
                    @change="selectItem(row.item.id)"/>
            </template>
            <template slot="start_time" scope="row">
                <span v-if="row.item.start_time">
                    {{ formatTimeFromUTC(row.item.start_time) }} - {{ formatTimeFromUTC(row.item.end_time) }}
                </span>
                <span v-else>-</span>
            </template>
            <template slot="amount_applied" scope="row">
                <div class="d-flex">
                    <b-form-input
                        class="mr-1"
                        v-model="row.item.amount_applied"
                        name="amount_applied"
                        type="number"
                        step="0.01"
                        :disabled="form.busy || !row.item.selected"
                        @change="x => itemAmountChanged(row.item, x)"
                    />
                    <b-select name="adjustment_type"
                        class="mr-1"
                        v-model="row.item.adjustment_type"
                        :options="claimAdjustmentTypeOptions"
                        :disabled="form.busy || !row.item.selected"
                        @change="x => itemTypeChanged(row.item, x)"
                    >
                        <template slot="first">
                            <option value="">-- Type --</option>
                        </template>
                    </b-select>
                    <b-form-input
                        name="note"
                        v-model="row.item.note"
                        type="text"
                        :disabled="form.busy || !row.item.selected"
                        maxlength="255"
                        style="max-width: none!important;"
                    />
                </div>
            </template>
          </b-table>
        </div>

        <hr />
        <div class="d-flex">
            <div class="ml-auto">
                <b-btn variant="success" @click="submit()" :disabled="form.busy">
                    <span v-if="form.busy"><i class="fa fa-spin fa-spinner"></i></span>
                    <span v-else>Save Adjustment</span>
                </b-btn>
                <b-btn variant="default" @click="cancel()" :disabled="form.busy">Cancel</b-btn>
            </div>
        </div>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import FormatsDates from "../../../mixins/FormatsDates";
    import Constants from "../../../mixins/Constants";
    import { Decimal } from 'decimal.js';
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ Constants, FormatsDates, FormatsNumbers, FormatsStrings ],

        data() {
            return {
                form: new Form({
                    adjustments: [],
                }),
                items: [],
                fields: {
                    selected: { label: ' ', sortable: false, },
                    type: { label: 'Type', sortable: true },
                    summary: { label: 'Summary', sortable: true },
                    date: { label: 'Date', sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    start_time: { label: 'Time', sortable: true },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { label: 'Due', sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_applied: { label: 'Amount to Adjust', sortable: false },
                },
            };
        },

        computed: {
            ...mapGetters({
                claim: 'claims/claim',
            }),
        },

        methods: {
            /**
             * Map items with extra fields required.
             */
            initItems(claim) {
                if (! claim || ! claim.items) {
                    return [];
                }

                this.items = claim.items.map(item => {
                    item.selected = false;
                    item.adjustment_type = '';
                    item.amount_applied = '';
                    item.note = '';
                    return item;
                });
            },

            /**
             * Loop through the table data and update the form
             * to contain proper ClaimAdjustment objects.
             */
            populateFormFromTable() {
                this.form.adjustments = this.items.map(item => {
                    if (! item.selected || parseFloat(item.amount_applied) === parseFloat('0')) {
                        return null;
                    }

                    return {
                        claim_invoice_item_id: item.id,
                        adjustment_type: item.adjustment_type,
                        amount_applied: item.amount_applied,
                        note: item.note,
                    };
                })
                .filter(x => x != null);
            },

            /**
             * Submit the adjustments form.
             */
            submit() {
                this.populateFormFromTable();

                this.form.post(`/business/claim-adjustments/${this.claim.id}`)
                    .then( ({ data }) => {
                        this.$emit('update', data.data);
                        this.$emit('close');
                    })
                    .catch(() => {});
            },

            /**
             * Close the modal.
             */
            cancel() {
                this.$emit('close');
            },

            /**
             * Handle selection of items.
             *
             * @param {number} id
             */
            selectItem(id) {
                let claimItem = this.items.find(x => x.id == id);

                if (claimItem.selected) {
                    // Claim items should always have a numeric value when selected.
                    if (claimItem.amount_applied == '') {
                        this.$set(claimItem, 'amount_applied', (new Decimal(-1)).times(new Decimal(claimItem.amount_due)).toFixed(2));
                        this.$set(claimItem, 'adjustment_type', '');
                    }
                } else {
                    // Claim items that are not selected should always be empty.
                    this.$set(claimItem, 'amount_applied', '');
                    this.$set(claimItem, 'adjustment_type', '');
                }
                this.$set(claimItem, 'note', '');

                this.forceRowUpdate(claimItem);
            },

            /**
             * Handle change of claim item amount applied.
             *
             * @param {Object} claimItem
             * @param {number} value
             */
            itemAmountChanged(claimItem, value) {
                if (isNaN(value) || value == '') {
                    // Clear the value / selection if invalid value.
                    this.$set(claimItem, 'amount_applied', '');
                } else {
                    // Make sure item is selected when it has an amount.
                    this.$set(claimItem, 'selected', true);
                }

                this.forceRowUpdate(claimItem);
            },

            /**
             * Handle claim item changed adjustment type dropdown.
             *
             * @param {Object} claimItem
             * @param {string} value
             */
            itemTypeChanged(claimItem, value) {
                if (value == '') {
                    return;
                }

                // Make sure item is selected and has a numeric amount set.
                this.$set(claimItem, 'selected', true);
                if (claimItem.amount_applied == '') {
                    this.$set(claimItem, 'amount_applied', '0.00');
                }

                this.forceRowUpdate(claimItem);
            },

            /**
             * Force the BootstrapVue table to update the claim item row.
             *
             * @param {Object} claimItem
             */
            forceRowUpdate(claimItem) {
                // This was implemented because BootstrapVue was having issues knowing
                // that it should update the table rows unless we $set the claim row
                // explicitly.  Doing this after the nextTick() will ensure the row
                // triggers an update after we have updated any values prior
                // to this method call.
                this.$nextTick(x => {
                    let index = this.items.findIndex(x => x.id == claimItem.id);
                    // Set the claim item to itself to force and update but not change values.
                    this.$set(this.items, index, JSON.parse(JSON.stringify(this.items[index])));
                });
            },
        },

        created() {
            this.$store.commit('claims/setClaim', {});
        },

        mounted() {
            this.initItems(this.claim);
        },

        watch: {
            claim(newValue, oldValue) {
                this.initItems(newValue);
            },
        },
    }
</script>

<style>
    .claims-table input.form-control { max-width: 135px; }
</style>