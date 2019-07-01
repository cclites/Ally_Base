<template>
    <div class="table-responsive">
        <b-table hover
                 :sort-by="sortBy"
                 :sort-desc="sortDesc"
                 :items="items"
                 :fields="fields">
            <template slot="created_at" scope="data">
                {{ formatDate(data.item.created_at) }}
            </template>
            <template slot="amount_paid" scope="data">
                <span style="color: darkred;" v-if="data.value < data.item.amount">Unpaid</span>
                <span style="color: green;" v-else>Paid</span>
            </template>
            <template slot="actions" scope="data">
                <slot name="actions" :item="data.item">
                    <a :href="'/client/invoices/' + data.item.id" class="btn btn-secondary" target="_blank">
                        <i class="fa fa-external-link"></i> View
                    </a>
                    <a :href="'/client/invoices/' + data.item.id + '/pdf'" class="btn btn-secondary">
                        <i class="fa fa-file-pdf-o"></i> Download
                    </a>
                </slot>
            </template>
        </b-table>
    </div>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: ['client', 'invoices'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.invoices,
                fields: [
                    { key: 'created_at', label: 'Invoice Date', sortable: true },
                    { key: 'name', label: 'Invoice #', sortable: true },
                    { key: 'payer', label: 'Payers', sortable: true },
                    {
                        key: 'amount',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {   key: 'amount_paid', label: "Status" },
                    {
                        key: 'actions',
                        class: 'hidden-print'                        
                    }
                ],
                sortBy: 'created_at',
                sortDesc: true,
            }
        },
        methods: {

        }
    }
</script>
