<template>

    <b-row>
        <b-col>
            <b-card title="Breakdown">
                <b-table :items="breakdown" :fields="breakdownFields"></b-table>
            </b-card>
            <b-card title="Payment Types">
                <b-table :items="items" :fields="fields" foot-clone>
                    <template slot="FOOT_name" scope="data"></template>
                    <template slot="FOOT_caregiver" scope="data">
                        {{ moneyFormat(caregiverTotal) }}
                    </template>
                    <template slot="FOOT_business" scope="data">
                        {{ moneyFormat(businessTotal) }}
                    </template>
                    <template slot="FOOT_system" scope="data">
                        {{ moneyFormat(systemTotal) }}
                    </template>
                    <template slot="FOOT_total" scope="data">
                        {{ moneyFormat(total) }}
                    </template>
                </b-table>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers';

    export default {
        mixins: [FormatsNumbers],
        
        data() {
            return{
                search: {

                },
                breakdown: [],
                breakdownFields: [
                    'label',
                    {
                        key: 'total',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'percentage',
                        formatter: (value) => { return numeral(value).format('0.00%') }
                    }
                ],
                items: [],
                fields: [
                    {
                        key: 'name',
                        label: 'Type'
                    },
                    {
                        key: 'caregiver',
                        label: 'Total CG Deposits',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'business',
                        label: 'Total Provider Deposits',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'system',
                        label: 'Total Ally Fee',
                        formatter: (value) => { return this.moneyFormat(value) }
                    },
                    {
                        key: 'total',
                        label: 'Total Payouts',
                        formatter: (value) => { return this.moneyFormat(value) }
                    }
                ]
            }
        },
        
        created() {
            this.fetchData();
        },
        
        mounted() {
        
        },
        
        methods: {
            fetchData() {
                axios.post('/admin/reports/finances', this.search)
                    .then(response => {
                        this.items = response.data.stats;
                        this.breakdown = response.data.breakdown;
                    })
            }
        },
        
        computed: {
            caregiverTotal() {
                return _.sumBy(this.items, 'caregiver');
            },

            businessTotal() {
                return _.sumBy(this.items, 'business');
            },

            systemTotal() {
                return _.sumBy(this.items, 'system');
            },

            total() {
                return _.sumBy(this.items, 'total');
            }
        }
    }
</script>