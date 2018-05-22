<template>
    <b-card title="Client Payment Statements">
        <div class="table-responsive">
            <b-table :items="items" :fields="fields">
                <template slot="for_care_week" scope="data">
                    {{ weekStart(data.item) }} - {{ weekEnd(data.item) }}
                </template>
                <template slot="success" scope="data">
                    <span style="color: green;" v-if="data.value">Complete</span>
                    <span style="color: darkred;" v-else>Failed</span>
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
                        key: 'success',
                        label: 'Payment Status',
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
            weekStart(item) {
                if (!item.week) return '';
                return this.formatDate(item.week.start);
            },

            weekEnd(item) {
                if (!item.week) return '';
                return this.formatDate(item.week.end);
            }
        },
        
        computed: {

        }
    }
</script>
