<template>
    <b-card title="Payment History">
        <b-table hover
                :items="items"
                :fields="fields">
            <template slot="created_at" scope="data">
                {{ formatDate(data.item.created_at) }}
            </template>
            <template slot="week" scope="data">
                {{ formatDate(data.item.week.start) }} - {{ formatDate(data.item.week.end) }}
            </template>
            <template slot="actions" scope="data">
                <a :href="'/payment-history/' + data.item.id" class="btn btn-secondary">
                    View Details
                </a>
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    export default {
        props: ['client'],

        mixins: [FormatsDates],

        data() {
            return{
                items: this.client.payments,
                fields: [
                    { key: 'created_at', label: 'Date Paid' },
                    { key: 'week', label: 'Week' },
                    { key: 'amount', label: 'Amount' },
                    { key: 'method', label: 'Type' },
                    'actions'
                ]
            }
        }
    }
</script>