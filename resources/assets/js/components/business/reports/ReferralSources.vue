<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Referral Sources"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col md="2" class="float-left">
                            <b-form-select v-model="filter" class="mb-3">
                                <option :value="null">-- All Referral Sources --</option>
                                <option :value="report.organization" v-for="report in reports">{{ report.organization }}</option>
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
                            ${{ row.item.revenue }}
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

    export default {
        props: ['reports'],

        data() {
            return {
                fields: [
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
                        key: 'prospectscount',
                        label: 'Number of Prospects',
                        sortable: true
                    },
                    {
                        key: 'clientscount',
                        label: 'Number of Clients',
                        sortable: true
                    },
                    {
                        key: 'revenue',
                        label: 'Revenue',
                        sortable: true
                    },
                ],
                show: true,
                checked: true,
                selected: null,
                filter: null
            }
        },

        mounted() {
            var  labels = [], datasets=[], revenue=[], graphColors = [], allCount=0, userCount=[];
            this.items.forEach(function(item){
                allCount +=(item.clientscount + item.prospectscount);
            });

            this.items.forEach(function(item){
                labels.push(item.organization);
                revenue.push(item.revenue);
                userCount.push(Math.round(100/allCount*(item.clientscount + item.prospectscount)));
                datasets.push(item.clientscount + item.prospectscount);

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
                            label: function(tooltipItems, data) {
                                var label = data.labels[tooltipItems.index];
                                var dataset = data.datasets[0].data[tooltipItems.index];
                                return  label + ' : $' + dataset;
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
                        prospectscount: report.prospect_count,
                        clientscount: report.client_count,
                        revenue: report.shift_total,
                        id: report.id,
                    }
                });

               return  items;
            },
        },

        methods: {

        }
    }
</script>
