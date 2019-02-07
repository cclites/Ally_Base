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
            <template slot="week" scope="data">
                {{ start_end(data) }}
            </template>
            <template slot="success" scope="data">
                <span style="color: green;" v-if="data.value">Complete</span>
                <span style="color: darkred;" v-else>Failed</span>
            </template>
            <template slot="actions" scope="data">
                <slot name="actions" :item="data.item">
                    <a :href="'/client/payments/' + data.item.id" class="btn btn-secondary" target="_blank">
                        <i class="fa fa-external-link"></i> View
                    </a>
                    <a :href="'/client/payments/' + data.item.id + '/pdf'" class="btn btn-secondary">
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
        props: ['client', 'payments'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.payments,
                fields: [
                    { key: 'created_at', label: 'Date Paid', sortable: true },
                    { key: 'week', label: 'Week' },
                    { key: 'success', label: 'Payment Status' },
                    {
                        key: 'amount',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true,
                    },
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
            start_end(data) {
                if (data.item.week) {
                    if ('start' in data.item.week) {
                        return `${this.formatDate(data.item.week.start)} - ${this.formatDate(data.item.week.end)}`;
                    }
                }
                return 'Shift N/A';
            }
        }
    }
</script>
