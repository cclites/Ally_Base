<template>
    <b-card header="Select Date Range &amp; Filters"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col>
                <b-alert show variant="info">This report only shows projections based on scheduled shifts with assigned caregivers.</b-alert>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="3">
                <b-row>
                    <b-col>
                        <b-form-group label="Start Date">
                            <date-picker v-model="filters.dates.start" name="start_date"
                                         @input="filterDates($event, 'start')"></date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group label="End Date">
                            <date-picker :value="filters.dates.end" name="end_date" @input="filterDates($event, 'end')"></date-picker>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="2">
                <b-form-group label="Client">
                    <b-form-select v-model="filters.client" @change="filterItems($event, 'client')">
                        <option value="">All</option>
                        <option v-for="item in clientOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="Client Type">
                    <b-form-select v-model="filters.clientType" @change="filterItems($event, 'clientType')">
                        <option value="">All</option>
                        <option v-for="item in clientTypeOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="filters.caregiver" @change="filterItems($event, 'caregiver')">
                        <option value="">All</option>
                        <option v-for="item in caregiverOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="&nbsp;">
                    <b-button-group>
                        <b-button @click="generatePdf()"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                        <b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button>
                    </b-button-group>
                </b-form-group>
            </b-col>
        </b-row>
        <div class="d-flex justify-content-center" v-if="loading">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
        <div id="projected_billing_report" v-else>
            <b-row>
                <b-col>
                    <div class="h4 my-3">Total hours scheduled: {{ numberFormat(stats.total_hours) }}</div>
                    <div class="h4 mb-3">Projected Billing: {{ moneyFormat(stats.projected_total) }}</div>
                    <hr>
                    <div v-for="item in clientTypeStats" class="d-flex justify-content-between mb-1">
                        <div><i class="fa fa-chevron-right mr-1"></i>{{ startCase(item.name) }}:</div>
                        <div>{{ moneyFormat(item.projected_billing) }}</div>
                    </div>
                </b-col>
                <b-col>
                    <div class="h4 my-3">Total clients scheduled: {{ stats.total_clients }}</div>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-table :items="clientStats" :fields="fields"></b-table>
                </b-col>
            </b-row>
        </div>

    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers'
    export default {
        props: {
            clientOptions: {
                type: Array,
                required: true
            },
            clientTypeOptions: {
                type: [Array, Object],
                required: true,
                default: () => { return []; },
            },
            caregiverOptions: {
                type: Array,
                required: true
            }
        },

        mixins: [FormatsNumbers],

        data () {
            return {
                filters: {
                    caregiver: '',
                    client: '',
                    clientType: '',
                    dates: {
                        start: moment ().format ('MM/DD/YYYY'),
                        end: moment ().add(30, 'day').format ('MM/DD/YYYY')
                    },
                },
                clientStats: [],
                clientTypeStats: [],
                stats: {},
                fields: [
                    {
                        key: 'name',
                        label: 'Client Name',
                    },
                    {
                        key: 'hours',
                        label: 'Hours',
                        formatter: (val) => this.numberFormat(val)
                    },
                    {
                        key: 'projected_billing',
                        formatter: (val) => this.moneyFormat(val)

                    }
                ],
                typeFields: [
                    {
                        key: 'name',
                        label: 'Name',
                        formatter: (val) => _.startCase(val)
                    },
                    {
                        key: 'projected_billing',
                        formatter: (val) => this.moneyFormat(val)
                    }
                ],
                loading: false
            }
        },

        created () {
            this.fetchData()
        },

        methods: {
            filterDates(value, key) {
                this.filters.dates[key] = value;
                this.fetchData()
            },

            filterItems(value, key) {
                this.filters[key] = value;
                this.fetchData();
            },

            async fetchData() {
                this.loading = true;
                let form = new Form(this.filters);
                let response = await form.post('/business/reports/projected-billing').catch(err => console.error(err))
                this.stats = response.data.stats;
                this.clientStats = response.data.clientStats;
                this.clientTypeStats = response.data.clientTypeStats;
                this.loading = false;
            },

            startCase(text) {
                return _.startCase(text)
            },

            generatePdf() {
                this.filters.print = true;
                let url = `/business/reports/projected-billing/print?dates[start]=${this.filters.dates.start}` +
                    `&dates[end]=${this.filters.dates.end}` +
                    `&caregiver=${this.filters.caregiver}` +
                    `&client=${this.filters.client}` +
                    `&clientType=${this.filters.clientType}`;
                console.log(url);
                window.location = url;
            },

            print() {
                $('#projected_billing_report').print();
            }
        }
    }
</script>
