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
                        <b-col lg="3">
                            <business-location-form-group v-model="form.business_id"
                                                          :form="form"
                                                          field="business_id"
                                                          :allow-all="true">
                            </business-location-form-group>
                        </b-col>
                        <b-col lg="3" class="form-checkbox">
                            <b-form-group>
                                    <b-form-checkbox
                                        v-model="includeCaregiverWages"
                                    >Include CG Wages as COGS
                                    </b-form-checkbox>
                            </b-form-group>
                            <!--b-form-group v-if="includeCaregiverWages">
                                <b-form-checkbox-group>
                                    <b-form-checkbox v-model="form.compare_to_prior">Compare to previous period
                                    </b-form-checkbox>
                                </b-form-checkbox-group>
                            </b-form-group-->
                        </b-col>
                        <b-col lg="2">
                            <b-button variant="info" @click="fetchData()" class="mb-2">Generate</b-button>
                            <b-button v-if="dataIsReady && ! loading" @click="print" class="mb-2">Print</b-button>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="3">
                            <b-form-group label="Client">
                                <b-form-select v-model="form.client_id">
                                    <option value="">All</option>
                                    <option v-for="client in clientOptions" :value="client.id">{{ client.name }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Caregiver">
                                <b-form-select v-model="form.caregiver_id">
                                    <option value="">All</option>
                                    <option v-for="caregiver in caregiverOptions" :value="caregiver.id">{{
                                        caregiver.name }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Client Type">
                                <b-form-select v-model="form.client_type">
                                    <option value="">All</option>
                                    <option v-for="type in clientTypes" :value="type.id">{{ type.name }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="3">
                            <b-form-group label="Service Code">
                                <b-form-select v-model="form.service_code">
                                    <option value="">All</option>
                                    <option v-for="service in serviceCodes" :value="service.id">{{ service.code }}
                                    </option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <loading-card v-show="loading"></loading-card>
                    <div v-if="dataIsReady && ! loading" id="revenue_report">
                        <b-row class="space-above">
                            <b-col lg="6">
                                <div class="d-flex align-items-end justify-content-between">
                                    <span>Total revenue</span>

                                    <span class="display-5 text-info mr-2"
                                          :style="`color: ${revenueColor}`">{{this.revenue.total.current}}</span>
                                </div>
                                <div class="d-flex align-items-end justify-content-between" v-if="includeCaregiverWages">
                                    <span>Total CG Wages as contractors</span>

                                    <span class="display-6 text-danger mr-2"
                                          :style="`color: ${wagesColor}`">{{this.wages.total.current}}</span>
                                </div>
                                <div class="d-flex align-items-end justify-content-between">
                                    <span>Total profit</span>

                                    <span class="display-6 text-success mr-2"
                                          :style="`color: ${profitColor}`">{{displayedProfitCurrent}}</span>
                                </div>
                                <hr/>
                                <div v-if="priorTableData.length > 0">
                                    <h1>Prior Period</h1>
                                    <div class="space-above"></div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <span>Total revenue</span>
                                        <span class="display-5 text-info" :style="`color: ${revenueColor}`">{{this.revenue.total.prior}}</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between" v-if="includeCaregiverWages">
                                        <span>Total CG Wages as contractors</span>
                                        <span class="display-6 text-danger" :style="`color: ${wagesColor}`">{{this.wages.total.prior}}</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <span>Total profit</span>
                                        <span class="display-6 text-success" :style="`color: ${profitColor}`">{{displayedProfitPrior}}</span>
                                    </div>
                                    <hr/>
                                    <h2>Comparison to prior period</h2>
                                    <b-row class="space-above text-container">
                                        <b-col v-for="(stat, i) in growthStats" :key="i" lg="4">
                                            <span><b>{{stat.value}}%</b> {{stat.label}}</span>
                                            <b-progress :value="Math.abs(stat.value)"
                                                        :variant="progressVariant(stat.value)"
                                                        :key="stat.key"
                                            ></b-progress>
                                        </b-col>
                                    </b-row>
                                </div>
                            </b-col>
                            <b-col lg="6">
                                <line-chart :chart-data="chartData" :options="chartOptions"></line-chart>
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
                                        revenue: revenue.total.current,
                                        wages: wages.total.current,
                                        profit: profit.total.current,
                                    }]"
                                >
                                    <template slot="date" scope="data">
                                        <i class="hidden">{{data.value}}</i>
                                    </template>
                                </b-table>
                                <!--b-btn class="btn-center">Export to Excel</b-btn-->
                            </b-col>

                            <b-col lg="6" v-if="priorTableData.length > 0">
                                <h2>Prior date comparison</h2>
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
                                        revenue: revenue.total.prior,
                                        wages: wages.total.prior,
                                        profit: profit.total.prior,
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
    import LineChart from './analytics/LineChart';
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

    export default {
        props: {
            clientOptions: {
                type: Array,
                required: true
            },
            caregiverOptions: {
                type: Array,
                required: true
            },
            clientTypes: {
                type: Array,
                required: true
            },
            serviceCodes: {
                type: Array,
                required: true
            }
        },

        components: {
            BusinessLocationFormGroup,
            LineChart,
        },

        data () {
            return {
                loading: false,
                dataIsReady: false,
                form: new Form ({
                    start_date: moment().subtract(1, 'months').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    compare_to_prior: 0,
                    wages_as_cogs: 1,
                    business_id: '',
                    client_id: '',
                    caregiver_id: '',
                    client_type: '',
                    service_code: ''
                }),
                data: {
                    current: {},
                    prior: {},
                },
                revenue: {
                    growth: null,
                    total: {
                        current: null,
                        prior: null,
                    },
                },
                wages: {
                    growth: null,
                    total: {
                        current: null,
                        prior: null,
                    },
                },
                profit: {
                    growth: null,
                    total: {
                        current: null,
                        prior: null,
                    },
                },
                tableFields: [
                    {key: 'date', label: 'Date'},
                    'revenue',
                    {key: 'wages', label: 'CG wages'},
                    'profit',
                ],
                profitColor: '#00cde3',
                revenueColor: '#795bcb',
                wagesColor: '#f07730',
                includeCaregiverWages: true,
                chartOptions: {
                    scales: {
                        xAxes: [{
                            type: 'time',
                            time: {
                                unit: 'week'
                            }
                        }]
                    }
                }
            };
        },

        computed: {
            chartData () {
                let date = Object.keys (this.data.current);
                const currentProfit = [];
                const currentSales = [];

                date.sort ((a, b) => new Date (a) - new Date (b)).forEach (date => {
                    const dayStats = this.data.current[date];
                    currentProfit.push (dayStats.profit)
                    currentSales.push (dayStats.revenue)
                });

                return {
                    labels: date,
                    datasets: [
                        {
                            label: 'Profit',
                            borderColor: this.profitColor,
                            backgroundColor: this.profitColor,
                            data: currentProfit
                        },
                        {
                            label: 'Revenue',
                            borderColor: this.revenueColor,
                            backgroundColor: this.revenueColor,
                            data: currentSales,
                        },
                    ],
                };
            },

            currentTableData () {
                const inArray = [];
                Object.keys (this.data.current).forEach (date => {
                    const obj = {
                        ...this.data.current[date],
                        date,
                    };

                    inArray.push (obj);
                });
                inArray.sort ((a, b) => new Date (a) - new Date (b));

                return inArray;
            },

            priorTableData () {
                const inArray = [];
                Object.keys (this.data.prior).forEach (date => {
                    const obj = {
                        ...this.data.prior[date],
                        date,
                    };

                    inArray.push (obj);
                });
                inArray.sort ((a, b) => new Date (a) - new Date (b));

                return inArray;
            },

            growthStats () {
                return [
                    {label: 'Sales Growth', value: this.revenue.growth, key: 'revenue'},
                    {label: 'CG Wages Growth', value: this.wages.growth, key: 'wages'},
                    {label: 'Profit Growth', value: this.profit.growth, key: 'profit'},
                ];
            },

            displayedProfitCurrent() {
                if(this.includeCaregiverWages){
                    return this['profit'].total.current;
                }else{
                    const profitTotal =  Number (this['profit'].total.current .replace ('$', '').replace (',', ''));
                    const wagesTotal = Number (this['wages'].total.current .replace ('$', '').replace (',', ''));
                    return this.formatPrice(profitTotal + wagesTotal);
                }
            },

            displayedProfitPrior() {
                return this['profit'].total.prior;
            }
        },
        methods: {

            progressVariant (value) {
                return (value > 0) ? 'success' : 'danger';
            },

            fetchData () {
                const {start_date, end_date, compare_to_prior, business_id} = this.form;
                const compare = compare_to_prior[0] ? 1 : 0;
                this.loading = true;

                this.form.post (`/business/reports/revenue?start_date=${start_date}&end_date=${end_date}&compare_to_prior=${compare}&businesses[]=${business_id}`)
                    .then (({data}) => {
                        this.data = data;
                        this.loading = false;
                        this.dataIsReady = true;

                        ['revenue', 'wages', 'profit'].forEach (prop => {
                            this[prop].total.current = this.calculateTotalOf (prop);

                            if (compare) {
                                this[prop].total.prior = this.calculateTotalOf (prop, 'prior');
                                this[prop].growth = this.calculateGrowth (prop);
                            }
                        });
                    })
                    .catch ((err) => {
                        console.error (err);
                        this.loading = false;
                    })


            },

            calculateTotalOf (metric, period = 'current') {
                const {dataIsReady, data} = this;

                if (dataIsReady) {
                    const total = Object.keys (data[period]).map (date => data[period][date][metric]).reduce ((total, value) => total + value, 0);
                    return this.formatPrice (total);
                }

                return '$0.00';
            },

            calculateGrowth (metric) {
                const fromStringToNumber = (string) => Number (string.replace ('$', '').replace (',', ''));
                const currentTotal = fromStringToNumber (this.calculateTotalOf (metric));
                const priorTotal = fromStringToNumber (this.calculateTotalOf (metric, 'prior'));

                return (((currentTotal - priorTotal) / priorTotal) * 100).toFixed (0);
            },

            formatPrice (value) {
                return new Intl.NumberFormat ('us-US', {style: 'currency', currency: 'USD'}).format (value);
            },

            print () {
                $ ('#revenue_report').print ()
            }
        },
    }
</script>

<style scoped>
    .space-above {
        margin-top: 2.5rem;
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

    .text-container p {
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }

    .hidden {
        opacity: 0;
    }

    @media only screen and (min-width: 2000px) {
        .text-section hr {
            margin: 3em 0;
        }

        .text-container p {
            margin-bottom: 3.5rem;
            font-size: 2.3rem;
        }

        .space-above {
            margin-top: 6.5rem;
        }

        .text-container h1, .text-container h2 {
            font-size: 4rem;
        }
    }
</style>
