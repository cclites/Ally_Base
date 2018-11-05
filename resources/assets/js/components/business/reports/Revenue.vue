<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Date Range &amp; Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="2">
                            <b-form-group label="Start Date">
                                <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    v-model="form.start_date"
                                    placeholder="Start Date"
                                ></date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="End Date">
                                <date-picker
                                    class="mb-1"
                                    v-model="form.end_date"
                                    name="end_date"
                                    placeholder="End Date"
                                ></date-picker>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3" class="form-checkbox">
                            <b-form-group>
                                <b-form-checkbox-group :checked="true" disabled>
                                    <b-form-checkbox
                                        :checked="true"
                                        disabled
                                    >Include CG Waves as COGS</b-form-checkbox>
                                </b-form-checkbox-group>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3" class="form-checkbox">
                            <b-form-group>
                                <b-form-checkbox-group>
                                    <b-form-checkbox
                                        v-model="form.compare_to_prior"
                                        :value="1"
                                        :unchecked-value="0"
                                    >Compare to previous period</b-form-checkbox>
                                </b-form-checkbox-group>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="&nbsp;">
                                <b-button variant="info" @click="fetchData()">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <loading-card v-show="loading"></loading-card>

                    <b-row v-if="dataIsReady && ! loading" class="space-above">
                        <b-col lg="6">
                            <p>Total revenue: {{calculateTotalOf('revenue')}}</p>
                            <p>Total CG Wages as contractors: {{calculateTotalOf('wages')}}</p>
                            <p>Total profits: {{calculateTotalOf('profit')}}</p>
                        </b-col>
                        <b-col lg="6">
                            <line-chart :chart-data="chartData" :options="{}"></line-chart>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import axios from 'axios';
import LineChart from './analytics/LineChart';

export default {
    components: {
        LineChart,
    },
    data() {
        return {
            loading: false,
            dataIsReady: false,
            form: new Form({
                start_date: '09/01/2018',
                end_date: '11/01/2018',
                compare_to_prior: 0,
                wages_as_cogs: 1,
            }),
            data: {
                current: {},
                prior: {},
            },
        };
    },
    computed: {
        chartData() {
            let date = Object.keys(this.data.current);
            const currentProfit = [];
            const currentSales = [];

            date.sort((a, b) => new Date(a) - new Date(b)).forEach(date => {
                const dayStats = this.data.current[date];
                currentProfit.push(dayStats.profit)
                currentSales.push(dayStats.revenue)
            });
            
            return {
                labels: date,
                datasets: [
                    {
                    label: 'Profit',
                    borderColor: '#00cde3',
                    backgroundColor: '#00cde3',
                    data: currentProfit
                    },
                    {
                    label: 'Revenue',
                    borderColor: '#795bcb',
                    backgroundColor: '#795bcb',
                    data: currentSales,
                    },
                ],
            };
        },
    },
    methods: {
        fetchData() {
            const {start_date, end_date} = this.form;
            this.loading = true;

            this.form.post(`/business/reports/revenue?start_date=${start_date}&end_date=${end_date}`)
                .then(({data}) => {
                    this.data = data;
                    this.loading = false;
                    this.dataIsReady = true;
                    console.log(data)
                })
                .catch((err) => {
                    console.error(err);
                    this.loading = false;
                })
        },
        calculateTotalOf(metric) {
            const {dataIsReady, data: {current}} = this;

            if(dataIsReady) {
                const total = Object.keys(current).map(date => current[date][metric]).reduce((total, value) => total + value, 0);
                return new Intl.NumberFormat('us-US', {style: 'currency', currency: 'USD'}).format(total);
            }

            return '$0.00';
        }
    }
}
</script>

<style scoped>
.space-above {
    margin-top: 40px;
}
.form-checkbox {
    align-self: flex-end;
}
</style>
