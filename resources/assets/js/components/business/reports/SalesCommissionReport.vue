<template>
    <b-card header="Select Date Range &amp; Filters"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col>
                <b-alert show variant="info">This report shows the total number of clients created within the date range selected for each salesperson.</b-alert>
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
                <b-form-group label="Salesperson">
                    <b-form-select v-model="filters.salesperson" @change="filterItems($event, 'salesperson')">
                        <option value="">All</option>
                        <option v-for="item in salespersonOptions" :value="item.id">{{ item.name }}</option>
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
        <div id="salesperson_commission_report" v-else>
            <b-row>
                <b-col>
                    <b-table :items="salespersonStats" :fields="fields"></b-table>
                </b-col>
            </b-row>
        </div>

    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers'
    export default {
        props: {
            salespersonOptions: {
                type: Array,
                required: true
            }
        },

        mixins: [FormatsNumbers],

        data () {
            return {
                filters: {
                    salesperson: '',
                    dates: {
                        start: moment ().format ('MM/DD/YYYY'),
                        end: moment ().add(30, 'day').format ('MM/DD/YYYY')
                    },
                },
                salespersonStats: [],
                stats: {},
                fields: [
                    {
                        key: 'name',
                        label: 'Salesperson',
                    },
                    {
                        key: 'hours',
                        label: 'Total Number of Clients',
                        formatter: (val) => this.numberFormat(val)
                    },
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
                this.salespersonStats = response.data.clientStats;
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
                $('#salesperson_commission_report').print();
            }
        }
    }
</script>

<style scoped>

</style>