<template>
    <div class="table-responsive claim-items">
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
    </div>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    export default {
        mixins: [ FormatsDates, FormatsNumbers ],

        props: {
            claim: {
                type: Object,
                default: () => {},
                required: true,
            },
            items: {
                type: Array,
                default: () => [],
                required: true,
            },
        },

        data() {
            return {
                deletingId: null,
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
                        this.$emit('update:claim', data.data);
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.deletingId = null;
                    });
            },
        },
    }
</script>

<style>
</style>