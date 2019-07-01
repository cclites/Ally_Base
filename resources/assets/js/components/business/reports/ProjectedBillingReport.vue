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
            <b-col md="2">
                <business-location-form-group
                    v-model="form.businesses"
                    label="For Office Location"
                    :allow-all="true"
                />
            </b-col>
            <b-col md="4">
                <b-row>
                    <b-col>
                        <b-form-group label="Start Date">
                            <date-picker v-model="form.start_date" name="start_date"></date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group label="End Date">
                            <date-picker :value="form.end_date" name="end_date"></date-picker>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="2">
                <b-form-group label="Client">
                    <b-form-select v-model="form.client" :disabled="loadingFilters">
                        <option value="">All</option>
                        <option v-for="item in clientOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="Client Type">
                    <b-form-select v-model="form.clientType" :disabled="loadingFilters">
                        <option value="">All</option>
                        <option v-for="item in clientTypeOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="form.caregiver" :disabled="loadingFilters">
                        <option value="">All</option>
                        <option v-for="item in caregiverOptions" :value="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col class="mb-3">
                <b-button-group>
                    <b-button @click="fetch()" variant="info" class="mr-2"><i class="fa mr-1"></i>Generate Report</b-button>
                    <b-button @click="print()"><i class="fa fa-print mr-1"></i>Print</b-button>
                </b-button-group>
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
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    export default {
        mixins: [FormatsNumbers],
        components: { BusinessLocationFormGroup },

        data () {
            return {
                clientOptions: [],
                clientTypeOptions: [],
                caregiverOptions: [],
                loadingFilters: false,
                form: new Form({
                    json: 1,
                    businesses: '',
                    caregiver: '',
                    client: '',
                    clientType: '',
                    start_date: moment ().format ('MM/DD/YYYY'),
                    end_date: moment ().add(30, 'day').format ('MM/DD/YYYY')
                }),
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
                loading: false,
            }
        },

        async created() {
            await this.fetchOptions();
        },

        methods: {
            async fetchOptions() {
                this.loadingFilters = true;
                await axios.get(`/business/reports/projected-billing/filters?businesses=${this.form.businesses}`)
                    .then( ({ data }) => {
                        this.caregiverOptions = data.caregivers;
                        this.clientOptions = data.clients;
                        this.clientTypeOptions = data.clientTypes;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loadingFilters = false;
                    })
            },

            async fetch() {
                this.loading = true;
                this.form.get('/business/reports/projected-billing')
                    .then( ({ data }) => {
                        this.stats = data.stats;
                        this.clientStats = data.clientStats;
                        this.clientTypeStats = data.clientTypeStats;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },

            startCase(text) {
                return _.startCase(text)
            },

            generate() {
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
        },

        watch: {
            async 'form.businesses'(newValue, oldValue) {
                if (newValue != oldValue) {
                    await this.fetchOptions();
                }
            }
        },
    }
</script>
