<template>
    <b-card title="Payments History Statement">
        <b-table :items="items" :fields="fields">
            <template slot="for_care_week" scope="data">
                {{ formatDate(data.item.week.start) }} - {{ formatDate(data.item.week.end) }}
            </template>
            <template slot="actions" scope="data">
                <a :href="'/business/clients/' + data.item.id + '/statements'" class="btn btn-secondary">
                    View Details
                </a>
            </template>
        </b-table>
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