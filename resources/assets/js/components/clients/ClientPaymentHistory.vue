<template>
    <b-card title="Payment History">
        <div class="table-responsive">
            <b-table hover
                     sort-by="created_at"
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
                    <a :href="'/payment-history/' + data.item.id" class="btn btn-secondary">
                        View Statement
                    </a>
                    <a :href="'/payment-history/' + data.item.id + '/print'" class="btn btn-secondary">
                        Download Statement
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
        props: ['client'],

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                items: this.client.payments,
                fields: [
                    { key: 'created_at', label: 'Date Paid' },
                    { key: 'week', label: 'Week' },
                    { key: 'success', label: 'Payment Status' },
                    {
                        key: 'amount',
                        label: 'Amount',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'                        
                    }
                ]
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
