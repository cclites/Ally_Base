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
                                        v-for="source in sources"
                                        :value="source.id"
                                        :key="source.id"
                                    >{{ source.organization }}</option>
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
                                <b-button variant="info" @click="fetchData()" :disabled="busy">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12">
                <loading-card v-if="busy" text="Loading Report..."></loading-card>
                <b-card
                    v-else
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
                            <e-charts ref="revenueDonut" class="chart" :options="revenueDonutOptions" auto-resize />
                        </b-col>
                    </b-row>
                    <hr/>
                    <b-table 
                        striped hover
                        :items="items"
                        :fields="fields"
                        :filter="tableFilter"
                        :show-empty="true"
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
    import ECharts from 'vue-echarts';
    import FormatsListData from "../../../mixins/FormatsListData";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsListData, FormatsNumbers],

        components: {
            ECharts,
        },

        props: {
            sourceType: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
                busy: false,
                showGraph: true,
                checked: true,
                selected: null,
                data: [],
                sources: [],
                filters: {
                    referral_source: null,
                    start_date: moment().subtract(30, 'days').format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
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

            if(this.$refs.revenueDonut) {
                this.$refs.revenueDonut.resize();
            }

            this.fetchReferralSources();
        },

        computed: {
            fetchReferralSources() {
                axios.get(`/business/referral-sources?type=${this.sourceType}`)
                    .then( ({ data }) => {
                        this.sources = data;
                    })
                    .catch(() => {});
            },

            referralSourceData() {
                return this.items.map(stats => ({
                    organization: stats.organization,
                    total: this.sourceType == 'client' 
                        ? stats.clients_count + stats.prospects_count
                        : stats.caregivers_count,
                    revenue: stats.revenue,
                }));
            },

            barOptions() {
                const data = this.referralSourceData;

                return {
                    title: {
                        text: 'Referral by Sources',
                        subtext: this.sourceType == 'client' ? 'For clients and prospects' : 'For caregivers',
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
                        data: data.map(data => data.organization),
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
                            data: data.map(data => ({name: data.organization, value: data.total})),
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
                            data: data.map(data => ({name: data.organization, value: data.revenue})),
                        },
                    ],
                };
            },

            items() {
                return this.data.map((report) => ({
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

            tableFilter() {
                const report = this.data.find(report => report.id == this.filters.referral_source);
                return report ? report.organization : null;
            },
        },

        methods: {
            async fetchData() {
                this.data = [];
                this.busy = true;
                try {
                    const form = new Form(this.filters);
                    const {data} = await form.post(`/business/reports/${this.sourceType}-referral-sources`);
                    this.data = data;
                } catch(e) {
                    console.error(e);
                } finally {
                    this.busy = false;
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
