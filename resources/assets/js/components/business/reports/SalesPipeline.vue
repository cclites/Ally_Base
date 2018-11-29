<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="3">
                            <b-form-group label="Start Date">
                                <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    v-model="form.start_date"
                                    placeholder="Start Date"
                                ></date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="3">
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
                            <b-form-group label="Office Location">
                                <business-location-select v-model="form.businesses[0]" :allow-all="true"></business-location-select>
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
                        <hr/>
                        <b-row class="space-above space-evenly">
                            <span class="display-6">Total Prospects: {{totalProspects}}</span>
                            <span class="display-6">Closed Won: {{pipeline.closed_win}}</span>
                            <span class="display-6">Closed Lost: {{pipeline.closed_loss}}</span>
                        </b-row>
                        <hr/>
                        <b-row>
                            <b-col lg="6">
                                <h1>Prospects Funnel</h1>
                                <e-charts ref="funnel" :options="chartOptions" class="chart" auto-resize></e-charts>
                            </b-col>
                            <b-col lg="6">
                                <h1>Prospects by Referral Source</h1>
                                <e-charts ref="bar" :options="barOptions" class="chart" auto-resize></e-charts>
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
import ECharts from 'vue-echarts';
import moment from 'moment';
import BusinessLocationSelect from "../BusinessLocationSelect";

export default {
    components: {
        BusinessLocationSelect,
        ECharts,
    },
    mounted() {
        if(this.$refs.funnel) this.$refs.funnel.resize();
    },
    data() {
        return {
            loading: false,
            dataIsReady: false,
            form: new Form({
                start_date: '09/01/2018',
                end_date: '12/01/2018',
                businesses: [""],
            }),
            totalProspects: 0,
            pipeline: {
                closed_loss: 0,
                closed_win: 0,
                expecting_client_signature: 0,
                had_assessment_performed: 0,
                had_assessment_scheduled: 0,
                needs_contract: 0,
                needs_payment_info: 0,
                ready_to_schedule: 0,
                general: 0,
            },
            hotOptions: {
                width: '80%',
            },
            referralData: {},
        };
    },
    computed: {
        chartOptions() {
            const calculatePercentage = (status) => (this.pipeline[status] / this.totalProspects * 100).toFixed(2);
            const data = [
                {name: 'General', value: 70, count: this.pipeline.general, percentage: calculatePercentage('general') },
                {name: 'Assessment Scheduled', value: 60, count: this.pipeline.had_assessment_scheduled, percentage: calculatePercentage('had_assessment_scheduled') },
                {name: 'Assessment Performed', value: 50, count: this.pipeline.had_assessment_performed, percentage: calculatePercentage('had_assessment_performed') },
                {name: 'Needs Contract', value: 40, count: this.pipeline.needs_contract, percentage: calculatePercentage('needs_contract') },
                {name: 'Expecting Signature', value: 30, count: this.pipeline.expecting_client_signature, percentage: calculatePercentage('expecting_client_signature') },
                {name: 'Collected Payment Info', value: 20, count: this.pipeline.needs_payment_info, percentage: calculatePercentage('needs_payment_info') },
                {name: 'Ready to Schedule', value: 10, count: this.pipeline.ready_to_schedule, percentage: calculatePercentage('ready_to_schedule') },
            ];

            return {
                title: {
                    text: '',
                    subtext: '',
                },
                tooltip: {
                    trigger: 'item',
                    formatter: ({seriesName, data}, ticket, cb) => `${seriesName} <br/> ${data.name}: ${data.count}`,
                },
                toolbox: { show: false },
                legend: {
                    data: data.map(data => data.name),
                },
                calculable: true,
                series: [
                    {
                        data,
                        name:'Prospect',
                        type:'funnel',
                        left: '10%',
                        gap: 5,
                        top: 60,
                        //x2: 80,
                        bottom: 60,
                        width: '80%',
                        // height: {totalHeight} - y - y2,
                        min: 0,
                        max: 80,
                        minSize: '0%',
                        maxSize: '100%',
                        sort: 'descending',
                        label: {
                            normal: {
                                formatter: ({data}) => `${data.name} ${data.percentage}%`,
                                show: true,
                                position: 'inside'
                            },
                            emphasis: {
                                textStyle: {
                                    fontSize: 20
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                length: 10,
                                lineStyle: {
                                    width: 1,
                                    type: 'solid'
                                }
                            }
                        },
                        itemStyle: {
                            normal: {
                                borderColor: '#fff',
                                borderWidth: 1
                            }
                        },
                    }
                ]
            };
        },
        barOptions() {
            let yAxisLabels = [];
            const series = Object.keys(this.referralData).map((date, i) => {
                const data = this.referralData[date].map(item => item.count).reverse();
                const name = moment(date, 'MM/YYYY').format('MMM');

                if(i == 0) {
                    yAxisLabels = this.referralData[date].map(item => item.name).reverse();
                }
                
                return {
                    name,
                    data,
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: { show: true, position: 'insideRight' },
                    },
                };
            });
            
            return {
                series,
                tooltip : {
                    trigger: 'axis',
                    axisPointer : {        
                        type : 'shadow'        // 'line' | 'shadow'
                    }
                },
                legend: {
                    data: this.monthLabels,
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis:  {
                    type: 'value'
                },
                yAxis: {
                    type: 'category',
                    data: yAxisLabels,
                },
                ...this.hotOptions,
            };
        },
    },
    methods: {
        fetchData() {
            this.loading = true;
            this.dataIsReady = false;
            this.totalProspects = 0;
            this.monthLabels = [];
            this.referralData = {};
            Object.keys(this.pipeline).forEach(field => this.pipeline[field] = 0);

            this.form.get(`/business/reports/sales-pipeline`)
                .then(({data}) => {
                    const {start_date, end_date} = this.form;
                    this.loading = false;
                    this.dataIsReady = true;
                    
                    // Create months label array for the bar chart
                    const monthGap = moment(end_date).diff((start_date), 'month');
                    for (let i = 0; i < Math.abs(monthGap); i++) {
                        const month = moment(start_date, 'MM/DD/YYYY').add({months: i});
                        this.monthLabels.push(month.format('MMM'));
                    }

                    this.crunchDataForBar(data);
                    this.crunchDataForFunnel(data);
                })
                .catch((err) => {
                    console.error(err);
                    this.loading = false;
                });
        },
        crunchDataForFunnel(data) {
            data.forEach(prospect => {
                let hasFoundCategory = false;

                Object.keys(this.pipeline).some(status => {
                    if(prospect[status]) {
                        hasFoundCategory = true;
                        this.pipeline[status]++;
                        return true;
                    }
                });

                if(!hasFoundCategory) this.pipeline.general++;
            });        
            
            let total = 0;
            Object.keys(this.pipeline).forEach(status => total += this.pipeline[status]);
            this.totalProspects = total;
        },
        crunchDataForBar(data) {
            const groupedByDate= {};
            data.forEach((prospect) => {
                if(!prospect.referral_source) return;
                const date = moment(prospect.created_at).format('M/YYYY');

                groupedByDate[date] 
                    ? groupedByDate[date].push(prospect)
                    : groupedByDate[date] = [prospect];
            });

            const result = {};

            // Combine prospects by their referral source
            Object.keys(groupedByDate).forEach(date => {
                const sortBySource = {};
                groupedByDate[date].forEach(({referral_source: source}) => {
                    const newSource = { id: source.id, name: source.organization, count: 1 };
                    sortBySource[source.id]
                        ? sortBySource[source.id].count++
                        : sortBySource[source.id] = newSource;
                });

                result[date] = Object.keys(sortBySource).map(sourceId => sortBySource[sourceId]);
            });

            this.referralData = result;
        },
    }
}
</script>

<style scoped>
.chart {
    width: 100%;
}
.space-evenly {
    justify-content: space-evenly;
}
@media only screen and (min-width: 2000px) {
    .chart {
        height: 600px;
    }
}
</style>
