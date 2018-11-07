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
                                    >Include CG Wages as COGS</b-form-checkbox>
                                </b-form-checkbox-group>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3" class="form-checkbox">
                            <b-form-group>
                                <b-form-checkbox-group>
                                    <b-form-checkbox v-model="form.compare_to_prior">Compare to previous period</b-form-checkbox>
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
                    <div v-if="dataIsReady && ! loading">
                        <b-row class="space-above">
                            <b-col lg="6" class="text-section">
                                <div>
                                    <span class="display-4 text-info">{{calculateTotalOf('revenue')}}</span> <span>Total revenue</span>
                                </div>
                                <div>
                                    <span class="display-6 text-danger">{{calculateTotalOf('wages')}}</span>
                                    <span>Total CG Wages as contractors</span>
                                </div>
                                <div>
                                    <span class="display-6 text-success">{{calculateTotalOf('profit')}}</span>
                                    <span>Total profit</span>
                                </div>
                                <hr/>
                                <div v-if="priorTableData.length > 0">
                                    <h3>Prior Period</h3>
                                    <div>
                                        <span class="display-4 text-info">{{calculateTotalOf('revenue', 'prior')}}</span> <span>Total revenue</span>
                                    </div>
                                    <div>
                                        <span class="display-6 text-danger">{{calculateTotalOf('wages', 'prior')}}</span>
                                        <span>Total CG Wages as contractors</span>
                                    </div>
                                    <div>
                                        <span class="display-6 text-success">{{calculateTotalOf('profit', 'prior')}}</span>
                                        <span>Total profit</span>
                                    </div>
                                    <hr/>
                                    <h3>Comparison to prior period</h3>
                                    <b-row class="space-above">
                                        <b-col lg="4">
                                            <b>{{calculateGrowth('revenue')}}%</b> Sales Growth
                                            <b-progress :value="Math.abs(revenue)"
                                                        :variant="progressVariant(revenue)"
                                                        key="revenue"
                                            ></b-progress>
                                        </b-col>
                                        <b-col lg="4">
                                            <b>{{calculateGrowth('wages')}}%</b> CG Wages Growth
                                            <b-progress :value="Math.abs(wages)"
                                                        :variant="progressVariant(wages)"
                                                        key="wages"
                                            ></b-progress>
                                        </b-col>
                                        <b-col lg="4">
                                            <b>{{calculateGrowth('profit')}}%</b> Profit Growth
                                            <b-progress :value="Math.abs(profit)"
                                                        :variant="progressVariant(profit)"
                                                        key="profit"
                                            ></b-progress>
                                        </b-col>
                                    </b-row>
                                </div>
                            </b-col>
                            <b-col lg="6">
                                <line-chart :chart-data="chartData" :options="{}"></line-chart>
                            </b-col>
                        </b-row>
                        <hr/>
                        <b-row>
                            <b-col lg="6">
                                <h2>Primary date range</h2>
                                <p>This is the table for each day as selected in the primary date range.</p>
                                <br/><br/>
                                <b-table striped :fields="tableFields" :items="currentTableData">
                                    <template
                                        v-for="key in ['revenue', 'wages', 'profit']"
                                        :slot="key"
                                        scope="data"
                                    >
                                        {{formatPrice(data.value)}}
                                    </template>
                                </b-table>
                                <h3>Total</h3>
                                <b-table
                                    :fields="tableFields"
                                    :items="[{
                                        date: '00/00/0000',
                                        revenue: calculateTotalOf('revenue'),
                                        wages: calculateTotalOf('wages'),
                                        profit: calculateTotalOf('profit'),
                                    }]"
                                >
                                    <template slot="date" scope="data">
                                        <i class="hidden">{{data.value}}</i>
                                    </template>
                                </b-table>
                                <!--b-btn class="btn-center">Export to Excel</b-btn-->
                            </b-col>

                            <b-col lg="6" v-if="priorTableData.length > 0">
                                <h2>Prior date comparaison</h2>
                                <p>This is the data for date period prior to the one currently selected.</p>
                                <br/><br/>
                                <b-table striped :fields="tableFields" :items="priorTableData">
                                    <template
                                        v-for="key in ['revenue', 'wages', 'profit']"
                                        :slot="key"
                                        scope="data"
                                    >
                                        {{formatPrice(data.value)}}
                                    </template>
                                </b-table>
                                <h3>Total</h3>
                                <b-table
                                    :fields="tableFields"
                                    :items="[{
                                        date: '00/00/0000',
                                        revenue: calculateTotalOf('revenue', 'prior'),
                                        wages: calculateTotalOf('wages', 'prior'),
                                        profit: calculateTotalOf('profit', 'prior'),
                                    }]"
                                >
                                    <template slot="date" scope="data">
                                        <i class="hidden">{{data.value}}</i>
                                    </template>
                                </b-table>
                                <!--b-btn class="btn-center">Export to Excel</b-btn-->
                            </b-col>
                        </b-row>
                    </div>
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
            tableFields: [
                {key: 'date', label: 'Date'},
                'revenue',
                {key: 'wages', label: 'CG wages'},
                'profit',
            ],
        };
    },
    computed: {
        revenue() {
            return this.calculateGrowth('revenue');
        },
        wages() {
            return this.calculateGrowth('wages');
        },
        profit() {
            return this.calculateGrowth('profit');
        },
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
        currentTableData() {
            const inArray = [];
            Object.keys(this.data.current).forEach(date => {
                const obj = {
                    ...this.data.current[date],
                    date,
                };

                inArray.push(obj);
                });
            inArray.sort((a, b) => new Date(a) - new Date(b));

            return inArray;
        },
        priorTableData() {
            const inArray = [];
            Object.keys(this.data.prior).forEach(date => {
                const obj = {
                    ...this.data.prior[date],
                    date,
                };

                inArray.push(obj);
                });
            inArray.sort((a, b) => new Date(a) - new Date(b));

            return inArray;
        },
    },
    methods: {
        progressVariant(value) {
            return (value > 0) ? 'success' : 'danger';
        },
        fetchData() {
            const {start_date, end_date, compare_to_prior} = this.form;
            const compare = compare_to_prior[0] ? 1 : 0;
            this.loading = true;

            this.form.post(`/business/reports/revenue?start_date=${start_date}&end_date=${end_date}&compare_to_prior=${compare}`)
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
        calculateTotalOf(metric, period = 'current') {
            const {dataIsReady, data} = this;

            if(dataIsReady) {
                const total = Object.keys(data[period]).map(date => data[period][date][metric]).reduce((total, value) => total + value, 0);
                return this.formatPrice(total);
            }

            return '$0.00';
        },
        calculateGrowth(metric) {
            const fromStringToNumber = (string) => Number(string.replace('$', '').replace(',', ''));
            const currentTotal = fromStringToNumber(this.calculateTotalOf(metric));
            const priorTotal = fromStringToNumber(this.calculateTotalOf(metric, 'prior'));

            return (((currentTotal - priorTotal) / priorTotal)  * 100).toFixed(0);
        },
        formatPrice(value) {
            return new Intl.NumberFormat('us-US', {style: 'currency', currency: 'USD'}).format(value);
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
.btn-center {
    display: block;
    margin: 0 auto;
}
.text-section {
    padding: 30px 60px;
}
.text-section p {
    margin-bottom: 22px;
    font-size: 18px;
}
.hidden {
    opacity: 0;
}
</style>
