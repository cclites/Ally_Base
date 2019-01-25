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
                            <business-location-form-group v-model="form.business_id"
                                                          :form="form"
                                                          field="business_id"
                                                          :allow-all="true">
                            </business-location-form-group>
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
                        <b-col lg="2">
                            <b-button variant="primary" class="mb-3" @click="showGraph = !showGraph">Show/Hide graphs</b-button>
                        </b-col>
                        <b-row v-show="showGraph">
                            <b-col lg="6">
                                <h1 class="text-center">Prospects Funnel</h1>
                                <e-charts ref="funnel" :options="chartOptions" class="chart" auto-resize></e-charts>
                            </b-col>
                            <b-col lg="6">
                                <h1 class="text-center">Prospects by Referral Source</h1>
                                <e-charts ref="bar" :options="barOptions" class="chart" auto-resize></e-charts>
                            </b-col>
                        </b-row>
                        <b-row v-if="dataIsReady && ! loading">
                            <hr/>
                            <div class="table-responsive">
                                <b-table bordered striped hover show-empty
                                    :items="table.items"
                                    :fields="table.fields"
                                    :current-page="table.currentPage"
                                    :per-page="table.perPage"
                                    empty-text="No related prospects data found for the generated date range."
                                    @filtered="onFiltered"
                                >
                                    <template slot="name" scope="row">
                                        <a :href="`/business/prospects/${row.item.id}`">{{ row.item.name }}</a>
                                    </template>
                                    <template slot="status" scope="row">
                                        {{ findStatus(row.item) }}
                                    </template>
                                    <template slot="referral" scope="row">
                                        {{ row.item.referral_source ? row.item.referral_source.organization : '-' }}
                                    </template>
                                </b-table>
                            </div>
                            <b-row style="width: 100%">
                                <b-col lg="6">
                                    <b-pagination :total-rows="table.totalRows" :per-page="table.perPage" v-model="table.currentPage"/>
                                </b-col>
                                <b-col lg="6" class="text-right">
                                    Showing {{ table.perPage < table.totalRows ? table.perPage : table.totalRows }} of {{ table.totalRows }} results
                                </b-col>
                            </b-row>
                        </b-row>
                    </div>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import ECharts from 'vue-echarts';
import moment from 'moment';
import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

export default {
    components: {
        BusinessLocationFormGroup,
        ECharts,
    },
    mounted() {
        if(this.$refs.funnel) this.$refs.funnel.resize();
    },
    data() {
        return {
            loading: false,
            dataIsReady: false,
            showGraph: true,
            form: new Form({
                start_date: '09/01/2018',
                end_date: '12/01/2018',
                businesses: [''],
            }),
            totalProspects: 0,
            prospect_status_label: {
                general: 'General',
                had_assessment_scheduled: 'Assessment Scheduled', 
                had_assessment_performed: 'Assessment Performed', 
                needs_contract: 'Needs Contract', 
                expecting_client_signature: 'Expecting Signature', 
                needs_payment_info: 'Collected Payment Info', 
                ready_to_schedule:'Ready to Schedule', 
                closed_loss: 'Closed - Loss',
                closed_win: 'Closed - Win',
            },
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
            table: {
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                fields: [
                    {
                        key: 'name',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date entered',
                        sortable: true,
                        formatter: (value) => moment(value).format('MM/DD/YYYY'),
                    },
                    {
                        key: 'status',
                        sortable: true,
                    },
                    {
                        key: 'referral',
                        label: 'Related referral source',
                        sortable: true,
                    },
                ],
                items: [],
            },
            referralData: {},
        };
    },
    computed: {
        chartOptions() {
            let data = Object.keys(this.prospect_status_label).reverse().map((key, i) => ({
                    value: i * 10,
                    count: this.pipeline[key],
                    name: this.prospect_status_label[key],
                    percentage: this.calculatePercentage(key),
            }));
            data = data.filter(({name}) => !name.match(/close/i));

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

                    this.table.items = data;
                    this.crunchDataForBar(data);
                    this.crunchDataForFunnel(data);
                })
                .catch((err) => {
                    console.error(err);
                    this.loading = false;
                });
        },
        calculatePercentage(category) {
            return this.totalProspects != 0 
                ? (this.pipeline[category] / this.totalProspects * 100).toFixed(2)
                : 0;
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
        findStatus(prospect) {
            const statuses = Object.keys(this.prospect_status_label);
            statuses.shift();

            if(prospect.closed_loss) {
                return this.prospect_status_label.closed_loss;
            }

            if(prospect.closed_win) {
                return this.prospect_status_label.closed_win;
            }

            const hasNoStatus = statuses.every(status => !prospect[status]);
            if(hasNoStatus) {
                return this.prospect_status_label.general;
            }

            let latestStatus = '';
            statuses.forEach(status => {
                if(prospect[status]) latestStatus = status;
            });
            debugger; // Debug why some prospects dont have a status
            return this.prospect_status_label[latestStatus];
        },
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.table.totalRows = filteredItems.length;
            this.table.currentPage = 1;
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
