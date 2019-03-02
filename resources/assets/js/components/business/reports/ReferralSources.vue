<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    :header="title"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col md="3">
                            <b-form-group label="Start Date">
                                <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    v-model="filters.start_date"
                                    placeholder="Start Date"
                                />
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="End Date">
                                <date-picker
                                    class="mb-1"
                                    v-model="filters.end_date"
                                    name="end_date"
                                    placeholder="End Date"
                                />
                            </b-form-group>
                        </b-col>

                        <b-col md="3">
                            <b-form-group label="." label-class="hidden-label">
                                <b-form-select v-model="filters.referral_source" class="mb-3">
                                    <option :value="null">-- All Referral Sources --</option>
                                    <option 
                                        v-for="report in reports" 
                                        :value="report.organization" 
                                        :key="report.id"
                                    >{{ report.organization }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        
                        <b-col md="3">
                            <b-form-group label="." label-class="hidden-label">
                                <b-button>
                                    <b-form-checkbox @change="showGraph=!showGraph" v-model="checked" class="mb-0">
                                        <span v-if="false">Hide Summary Graphs </span>
                                        <span v-else>Show Summary Graphs </span>
                                    </b-form-checkbox>
                                </b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button variant="info" @click="fetchData()">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Report"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row class="mb-5" v-show="showGraph">
                        <b-col md="4">
                            <e-charts ref="bar" class="chart" :options="barOptions" auto-resize />
                        </b-col>
                        <b-col md="4">
                            <e-charts ref="referralDonut" class="chart" :options="referralDonutOptions" auto-resize />
                        </b-col>
                        <b-col md="4">
                            <div id="revenue-chart">
                                <canvas ref="revenuechart" height="300vh" width="600vw"></canvas>
                            </div>
                        </b-col>
                    </b-row>
                    <hr/>
                    <b-table 
                        striped hover
                        :items="items"
                        :fields="fields"
                        :filter="filters.referral_source"
                    >
                        <template slot="revenue"  scope="row">
                            {{ moneyFormat(row.item.revenue) }}
                        </template>
                        <template slot="actions"  scope="row">
                            <b-btn size="sm" :href="'#/' + row.item.id">
                                View Shifts
                            </b-btn>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import Chart from 'chart.js';
    import ECharts from 'vue-echarts';
    import FormatsListData from "../../../mixins/FormatsListData";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsListData, FormatsNumbers],

        components: {
            ECharts,
        },

        props: {
            reports: {
                type: Array,
                required: true,
            },
            sourceType: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
                showGraph: true,
                checked: true,
                selected: null,
                data: [],
                filters: {
                    referral_source: null,
                    start_date: '',
                    end_date: '',
                },
                donutOptions: {
                    tooltip: {
                        trigger: 'item',
                        formatter: "{a} <br/>{b}: {c} ({d}%)"
                    },
                    legend: {
                        orient: 'horizontal',
                        x: 'center',
                        data: [],
                    },
                    series: {
                        name: 'Referrals',
                        type:'pie',
                        radius: ['50%', '70%'],
                        avoidLabelOverlap: false,
                        label: {
                            normal: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                show: true,
                                textStyle: {
                                    fontSize: '30',
                                    fontWeight: 'bold'
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data: null,
                    }
                },
            };
        },

        mounted() {
            if(this.$refs.bar) {
                this.$refs.bar.resize();
            }

            if(this.$refs.referralDonut) {
                this.$refs.referralDonut.resize();
            }

            /*
            var  labels = [], datasets=[], revenue=[], graphColors = [], allCount=0, userCount=[];
            this.items.forEach(item => {
                if (this.sourceType == 'client') {
                    allCount +=(item.clients_count + item.prospects_count);
                } else {
                    allCount += item.caregivers_count;
                }
            });

            this.items.forEach(item => {
                labels.push(item.organization);
                revenue.push(item.revenue);
                if (this.sourceType == 'client') {
                    userCount.push(Math.round(100/allCount*(item.clients_count + item.prospects_count)));
                    datasets.push(item.clients_count + item.prospects_count);
                } else {
                    userCount.push(Math.round(100 / (allCount * (item.caregivers_count))));
                    datasets.push(item.caregivers_count);
                }
                var randomR = Math.floor((Math.random() * 200) + 100);
                var randomG = Math.floor((Math.random() * 200) + 100);
                var randomB = Math.floor((Math.random() * 100) + 100);
                var graphBackground = "rgb("
                    + randomR + ", "
                    + randomG + ", "
                    + randomB + ")";
                graphColors.push(graphBackground);
            });

            var barchart = this.$refs.barchart;
            var barctx = barchart.getContext("2d");
            var myChart = new Chart(barctx, {
                type: 'bar',
                data: {
                    labels:  labels,
                    datasets: [{
                        label: 'Referred Count',
                        data: datasets,
                        backgroundColor: graphColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                stepSize: 1
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'Referrals By Source'
                    }
                }
            });

            var doughnutchart = this.$refs.doughnutchart;
            var doughnutctx = doughnutchart.getContext("2d");
            var myChart = new Chart(doughnutctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: userCount,
                        backgroundColor: graphColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                stepSize: 1
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'Referrals By Source'
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItems, data) {
                                var label = data.labels[tooltipItems.index];
                                var dataset = data.datasets[0].data[tooltipItems.index];
                                return  label + ' : ' + dataset + "%";
                            }
                        }
                    },
                }
            });

            var doughnutchart = this.$refs.revenuechart;
            var doughnutctx = doughnutchart.getContext("2d");
            var myChart = new Chart(doughnutctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: revenue,
                        backgroundColor: graphColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                stepSize: 1
                            }
                        }]
                    },
                    title: {
                        display: true,
                        text: 'Revenue By Referral Source'
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: (tooltipItems, data) => {
                                var label = data.labels[tooltipItems.index];
                                var dataset = data.datasets[0].data[tooltipItems.index];
                                return  label + ' : ' + this.moneyFormat(dataset);
                            }
                        }
                    }
                }
            });
            */
        },

        computed: {
            referralSourceData() {
                return this.items.map(stats => ({
                    org: stats.organization,
                    total: stats.clients_count + stats.prospects_count,
                    revenue: stats.shift_total,
                }));
            },

            barOptions() {
                const data = this.referralSourceData;

                return {
                    title: {
                        text: 'Referral by Sources',
                        subtext: 'For clients and prospects',
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['ref']
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        boundaryGap: [0, 0.01]
                    },
                    yAxis: {
                        type: 'category',
                        data: data.map(data => data.org),
                    },
                    series: [
                        {
                            name: 'Referrals',
                            type: 'bar',
                            data: data.map(data => data.total),
                        },
                    ]
                };
            },

            referralDonutOptions() {
                const data = this.referralSourceData;

                return {
                    ...this.donutOptions,
                    title: {
                        text: 'Referral by Sources',
                    },
                    series: [
                        {
                            ...this.donutOptions.series,
                            data: data.map(data => ({name: data.org, value: data.total})),
                        },
                    ],
                };
            },

            revenueDonutOptions() {
                const data = this.referralSourceData;

                return {
                    ...this.donutOptions,
                    title: {
                        text: 'Revenue by Sources',
                    },
                    series: [
                        {
                            ...this.donutOptions.series,
                            data: data.map(data => ({name: data.org, value: data.revenue})),
                        },
                    ],
                };
            },

            items() {
               return this.reports.map((report) => ({
                        organization: report.organization,
                        name: report.contact_name,
                        phone: report.phone,
                        prospects_count: report.prospects_count || 0,
                        clients_count: report.clients_count || 0,
                        caregivers_count: report.caregivers_count || 0,
                        revenue: report.shift_total,
                        id: report.id,
                }));
            },

            title() {
                return _.upperFirst(this.sourceType) + ' Referral Sources'; 
            },

            fields() {
                let columns = [
                    {
                        key: 'organization',
                        label: 'Organization',
                        sortable: true
                    },
                    {
                        key: 'name',
                        label: 'Contact Name',
                        sortable: true
                    },
                    {
                        key: 'phone',
                        label: 'Phone',
                        sortable: true
                    },
                    {
                        key: 'business_id',
                        label: 'Location',
                        sortable: true,
                        formatter: this.showBusinessName,
                    },
                ];

                if (this.sourceType == 'client') {
                    const newItems = [
                        {
                            key: 'prospects_count',
                            label: 'Number of Prospects',
                            sortable: true
                        },
                        {
                            key: 'clients_count',
                            label: 'Number of Clients',
                            sortable: true
                        }
                    ];
                    
                    columns.push(...newItems);
                } else {
                    columns.push({
                        key: 'caregivers_count',
                        label: 'Number of Caregivers',
                        sortable: true
                    });
                }

                // Add to last position
                columns.push({
                    key: 'revenue',
                    label: 'Revenue',
                    sortable: true
                });

                return columns;
            },
        },

        methods: {
            async fetchData() {
                try {
                    const form = new Form;
                    const {data} = await form.post('/business/');
                } catch(e) {
                    console.error(e);
                }
            }
        }
    }
</script>

<style scoped>
.chart {
    width: 100%;
}
.hidden-label {
    opacity: 0;
}
</style>
