<template>
    <b-card title="Client Payment Statements">
        <div class="table-responsive">
            <b-table :items="items" :fields="fields">
                <template slot="for_care_week" scope="data">
                    {{ formatDate(data.item.week.start) }} - {{ formatDate(data.item.week.end) }}
                </template>
                <template slot="actions" scope="data">
                    <b-btn :href="'/business/clients/payments/'+data.item.id">View Details</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';

    export default {
        props: ['payments'],
        
        mixins: [FormatsDates, FormatsNumbers],
        
        data() {
            return{
                items: this.payments,
                fields: [
                    {
                        key: 'created_at',
                        label: 'Paid',
                        formatter: (value) => { return this.formatDate(value) }
                    },
                    'for_care_week',
                    {
                        key: 'amount',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },
        
        created() {
        
        },
        
        mounted() {
        
        },
        
        methods: {
        
        },
        
        computed: {
        
        }
    }
</script>
