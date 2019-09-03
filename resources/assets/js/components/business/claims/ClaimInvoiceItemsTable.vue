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
        >
            <claim-invoice-item-form ref="item-form" @close="hideModal()" :item="current" />
        </b-modal>
    </div>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import ClaimInvoiceItemForm from "./ClaimInvoiceItemForm";
    import { mapGetters } from 'vuex';

    export default {
        mixins: [ FormatsDates, FormatsNumbers ],
        components: { ClaimInvoiceItemForm },

        computed: {
            ...mapGetters({
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
                sortBy: 'date',
                sortDesc: false,
                fields: {
                    type: { sortable: true },
                    summary: { sortable: true },
                    date: { sortable: true, formatter: x => this.formatDateFromUTC(x) },
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
            },

            edit(item) {
                this.$store.commit('claims/setItem', item);
                this.showEditModal = true;
            },

            hideModal() {
                this.showEditModal = false;
                this.$store.commit('claims/setItem', {});
            },
        },
    }
</script>
