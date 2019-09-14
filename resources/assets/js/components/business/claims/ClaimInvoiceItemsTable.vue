<template>
    <div class="table-responsive claim-items">
        <div class="d-flex mb-2">
            <b-btn class="ml-auto" variant="info" @click="edit({})"><i class="fa fa-plus" /> Add Item</b-btn>
        </div>
        <b-table bordered striped hover show-empty
            :items="items"
            :fields="fields"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
        >
            <template slot="start_time" scope="row">
                <span v-if="row.item.start_time">
                    {{ formatTimeFromUTC(row.item.start_time) }} - {{ formatTimeFromUTC(row.item.end_time) }}
                </span>
                <span v-else>-</span>
            </template>
            <template slot="related_shift_id" scope="row">
                <a v-if="row.item.related_shift_id" :href="`/business/shifts/${row.item.related_shift_id}`" target="_blank">{{ row.item.related_shift_id }}</a>
                <span v-else>-</span>
            </template>
            <template slot="actions" scope="row">
                <b-btn variant="info" @click="edit(row.item)" class="my-1" size="sm">
                    <i class="fa fa-edit"></i>
                </b-btn>
                <b-btn variant="danger" @click="destroy(row.item)" class="my-1" size="sm" :disabled="row.item.id === deletingId">
                    <i v-if="row.item.id === deletingId" class="fa fa-spin fa-spinner"></i>
                    <i v-else class="fa fa-times"></i>
                </b-btn>
            </template>
        </b-table>

        <b-modal id="editItemModal"
            :title="modalTitle"
            v-model="showEditModal"
            size="lg"
            :no-close-on-backdrop="true"
            hide-footer
            class="modal-fit-more"
        >
            <claim-invoice-item-form ref="item-form" @close="hideModal()" :item="current" />
        </b-modal>

        <confirm-modal title="Delete Item" ref="confirmDeleteItem" yesButton="Delete" yesVariant="danger">
            <p>Are you sure you want to delete this item from the Claim?  This is a permanent action and cannot be undone.</p>
        </confirm-modal>
    </div>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import ClaimInvoiceItemForm from "./ClaimInvoiceItemForm";
    import { mapGetters } from 'vuex';
    import Constants from "../../../mixins/Constants";

    export default {
        mixins: [ FormatsDates, FormatsNumbers, Constants ],
        components: { ClaimInvoiceItemForm },

        computed: {
            ...mapGetters({
                claim: 'claims/claim',
                items: 'claims/claimItems',
                current: 'claims/item',
            }),

            modalTitle() {
                return this.current.id ? 'Edit Claimable Item' : 'Create Claimable Item';
            },
        },

        data() {
            return {
                deletingId: null,
                showEditModal: false,
                // Table data:
                sortBy: '',
                sortDesc: false,
                fields: {
                    type: { sortable: true },
                    summary: { sortable: true },
                    date: { sortable: true, formatter: x => this.formatDateFromUTC(x) },
                    start_time: { label: 'Time', sortable: true },
                    related_shift_id: { sortable: true, label: 'Related Shift' },
                    rate: { sortable: true, formatter: x => this.moneyFormat(x) },
                    units: { sortable: true },
                    amount: { sortable: true, formatter: x => this.moneyFormat(x) },
                    amount_due: { sortable: true, formatter: x => this.moneyFormat(x) },
                    actions: { sortable: false },
                },
            }
        },

        methods: {
            destroy(item) {
                this.$refs.confirmDeleteItem.confirm(() => {
                    this.deletingId = item.id;

                    let form = new Form({});
                    form.submit('DELETE', `/business/claims/${this.claim.id}/item/${item.id}`)
                        .then( ({ data }) => {
                            this.$store.commit('claims/setClaim', data.data);
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.deletingId = null;
                        });
                });
            },

            edit(item) {
                this.$store.commit('claims/setItem', item);
                this.showEditModal = true;
            },

            hideModal() {
                this.showEditModal = false;
                this.$store.commit('claims/setItem', {});
            },

            add() {
                this.edit({
                    id: null,
                    claimable_type: this.CLAIMABLE_TYPES.SERVICE,
                });
            },
        },
    }
</script>
