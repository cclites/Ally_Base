<template>
    <b-card title="Invoice History">
        <div class="table-responsive">
            <b-table hover
                     sort-by="created_at"
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
                    <a :href="'/client/invoices/' + data.item.id" class="btn btn-secondary" target="_blank">
                        <i class="fa fa-external-link"></i> View
                    </a>
                    <a :href="'/client/invoices/' + data.item.id + '/pdf'" class="btn btn-secondary">
                        <i class="fa fa-file-pdf-o"></i> Download
                    </a>
                </template>
            </b-table>
        </div>
    </b-card>
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
                    { key: 'created_at', label: 'Invoice Date' },
                    {
                        key: 'amount',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {   key: 'amount_paid', label: "Status" },
                    {
                        key: 'actions',
                        class: 'hidden-print'                        
                    }
                ]
            }
        },
        methods: {

        }
    }
</script>
