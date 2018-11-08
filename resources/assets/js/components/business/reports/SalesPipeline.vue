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

                        <b-col lg="2">
                            <b-form-group label="&nbsp;">
                                <b-button variant="info" @click="fetchData()">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <loading-card v-show="loading"></loading-card>
                    <div v-if="dataIsReady && ! loading">
                        <b-row class="space-above">
                            <b-col lg="6">
                                <h1>Prospects Funnel</h1>
                                <e-charts ref="funnel" :options="chartOptions" class="funnel-chart" auto-resize></e-charts>
                            </b-col>
                            <b-col lg="6">
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

export default {
    components: {
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
            }),
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
            chart: {
                min: 0,
                max: 80,
            },
            hotOptions: {
                width: '80%',
            },
        };
    },
    computed: {
        chartOptions() {
            let pipelineTotal = 0;
            const calculatePercentage = (status) => {
                const result = (this.pipeline[status] / pipelineTotal * 100).toFixed(2)
                console.log('total', pipelineTotal)
                console.log('value', this.pipeline[status])
                console.log('result', result)
                return result
                };
            Object.keys(this.pipeline).forEach(status => pipelineTotal += this.pipeline[status]);

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
                        min: this.chart.min,
                        max: this.chart.max,
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
                        ...this.hotOptions,
                    }
                ]
            };
        },
    },
    methods: {
        fetchData() {
            this.loading = true;
            this.dataIsReady = false;

            this.form.post(`/business/reports/sales-pipeline`)
                .then(({data}) => {
                    this.loading = false;
                    this.dataIsReady = true;
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
        },
    }
}
</script>

<style scoped>
.funnel-chart {
    width: 100%;
}

@media only screen and (min-width: 2000px) {
    .funnel-chart {
        height: 600px;
    }
}
</style>
