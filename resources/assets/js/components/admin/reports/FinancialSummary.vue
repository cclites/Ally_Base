<template>
    <b-row>
        <b-col>
            <b-card title="Filter">
                <b-button-toolbar aria-label="Toolbar with button groups and input groups">
                    <date-picker
                            class="mb-1"
                            v-model="filters.start_date"
                            placeholder="Start Date">
                    </date-picker> &nbsp;to&nbsp;
                    <date-picker
                            class="mb-1"
                            v-model="filters.end_date"
                            placeholder="End Date">
                    </date-picker>
                    <b-input-group class="mx-1">
                        <b-form-select v-model="filters.provider">
                            <option value="">All Providers</option>
                            <option v-for="provider in providers" :value="provider.id" :key="provider.id">{{ provider.name }}</option>
                        </b-form-select>
                    </b-input-group>
                    <div>
                        <b-btn @click="fetchData()" variant="info" :disabled="loading">
                            Search
                            <i class="fa fa-spin fa-spinner" v-show="loading"></i>
                        </b-btn>
                    </div>
                </b-button-toolbar>
            </b-card>
            <b-card title="Payment Types">

                <loading-card v-show="loading"></loading-card>
                
                <b-table v-show="! loading" :items="items" :fields="fields" foot-clone>
                    <template slot="FOOT_name" scope="data"></template>
                    <template slot="FOOT_total_charges" scope="data">
                        {{ moneyFormat(total) }}
                    </template>
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
        props: ['providers'],

        mixins: [FormatsNumbers],
        
        data() {
            return{
                filters: {
                    provider: '',
                    start_date: '',
                    end_date: ''
                },
                loading: false,
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
                        key: 'total_charges',
                        formatter: (value) => { return this.moneyFormat(value) }
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

        methods: {
            fetchData() {
                this.loading = true;
                axios.post('/admin/reports/finances', this.filters)
                    .then(response => {
                        this.items = response.data.stats;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
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