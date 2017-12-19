<template>
    <b-card title="Payments History Statement">
        <b-table :items="items" :fields="fields">
            <template slot="for_care_week" scope="data">
                {{ formatDate(data.item.week.start) }} - {{ formatDate(data.item.week.end) }}
            </template>
            <template slot="actions" scope="data">
                <b-btn :href="'/business/clients/payments/'+data.item.id">View Details</b-btn>
            </template>
        </b-table>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {
        props: ['payments'],
        
        mixins: [FormatsDates],
        
        components: {
        
        },
        
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
                        formatter: (value) => { return numeral(value).format('$0,0.00') }
                    },
                    'actions'
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