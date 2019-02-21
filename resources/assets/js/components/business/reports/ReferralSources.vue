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
                        <b-col md="2" class="float-left">
                            <b-form-select v-model="filter" class="mb-3">
                                <option :value="null">-- All Referral Sources --</option>
                                <option :value="report.organization" v-for="report in reports" :key="report.id">{{ report.organization }}</option>
                            </b-form-select>
                        </b-col>
                        <b-col md="3" class="float-right">
                            <b-button>
                                <b-form-checkbox @change="show=!show" v-model="checked" class="mb-0">
                                    <span v-if="false">Hide Summary Graphs </span>
                                    <span v-else>Show Summary Graphs </span>
                                </b-form-checkbox>
                            </b-button>
                        </b-col>
                    </b-row>
                    <b-row class="mb-5" v-show="show">
                        <b-col md="4">
                            <div id="bar-chart">
                                <canvas ref="barchart" height="300vh" width="600vw"></canvas>
                            </div>
                        </b-col>
                        <b-col md="4">
                            <div id="doughnut-chart">
                                <canvas ref="doughnutchart" height="300vh" width="600vw"></canvas>
                            </div>
                        </b-col>
                        <b-col md="4">
                            <div id="revenue-chart">
                                <canvas ref="revenuechart" height="300vh" width="600vw"></canvas>
                            </div>
                        </b-col>
                    </b-row>
                    <b-table striped hover
                             :items="items"
                             :fields="fields"
                             :filter="filter">
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
    import FormatsListData from "../../../mixins/FormatsListData";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsListData, FormatsNumbers],

        props: ['reports', 'sourceType'],

        data() {
            return {
                show: true,
                checked: true,
                selected: null,
                filter: null
            }
        },

        mounted() {
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

        },

        computed: {
            items() {
               let items =  this.reports.map(function(report) {
                    return {
                        organization: report.organization,
                        name: report.contact_name,
                        phone: report.phone,
                        prospects_count: report.prospects_count | 0,
                        clients_count: report.clients_count | 0,
                        caregivers_count: report.caregivers_count | 0,
                        revenue: report.shift_total,
                        id: report.id,
                    }
                });

               return  items;
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
                    columns.push({
                        key: 'prospects_count',
                        label: 'Number of Prospects',
                        sortable: true
                    });
                    columns.push({
                        key: 'clients_count',
                        label: 'Number of Clients',
                        sortable: true
                    });
                } else {
                    columns.push({
                        key: 'caregivers_count',
                        label: 'Number of Caregivers',
                        sortable: true
                    });
                }

                columns.push({
                    key: 'revenue',
                    label: 'Revenue',
                    sortable: true
                })

                return columns;
            },
        },

        methods: {
        }
    }
</script>
