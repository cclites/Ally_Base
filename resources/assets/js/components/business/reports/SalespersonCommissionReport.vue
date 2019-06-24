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
                <business-location-form-group
                    v-model="filters.business"
                    label="Office Location"
                    :allow-all="true"
            />
            </b-col>
            <b-col md="2">
                <b-form-group label="Salesperson">
                    <b-form-select v-model="filters.salesperson" @change="filterItems($event, 'salesperson')">
                        <option value="">All</option>
                        <option v-for="item in allSalespersons" :value="item.value">{{ item.text }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>

            <b-col md="2">
                <b-form-group label="&nbsp;">
                    <b-button-group>
                        <b-button @click="generateReport()" variant="info" :disabled="loading"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
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
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                        class="sales-commission-table"
                        :items="salespersons"
                        :fields="fields"
                        sort-by="name"
                        empty-text="No Results"
                        :busy="loading"
                    />
                </b-col>
            </b-row>
        </div>

    </b-card>
</template>

<script>
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';
    import FormatsListData from "../../../mixins/FormatsListData";
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {

        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsNumbers, FormatsDates, FormatsListData],

        props: {
        },

        data () {
            return {
                filters: {
                    salesperson: '',
                    dates: {
                        start: moment ().format ('MM/DD/YYYY'),
                        end: moment ().add(30, 'day').format ('MM/DD/YYYY')
                    },
                    business: '',
                },
                salespersons: [],
                allSalespersons: [],
                stats: {},
                fields: [
                    {
                        key: 'name',
                        label: 'Salesperson',
                        sortable: true,
                    },
                    {
                        key: 'clients',
                        label: 'Total Number of Clients',
                        sortable: true,
                    },
                ],
                loading: false,
            }
        },

        methods: {
            filterDates(value, key) {
                this.filters.dates[key] = value;
            },

            filterItems(value, key) {
                this.filters[key] = value;
            },

            //default load
            async fetchSalespersons() {
                let response = await axios.get (`/business/reports/sales-people-commission/sales-people`);
                this.allSalespersons = response.data;
            },

            async generateReport() {
                let url = `/business/reports/sales-people-commission/generate?dates[start]=${this.filters.dates.start}` +
                    `&dates[end]=${this.filters.dates.end}` +
                    `&salesperson=${this.filters.salesperson}` +
                    `&business=${this.filters.business}` +
                    `&json=1`;

                this.loading = true;
                axios.get(url)
                    .then( ({ data }) => {
                        this.salespersons = data;
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },

            startCase(text) {
                return _.startCase(text)
            },

            print(){
                $('.sales-commission-table').print();
            },
        },

        async mounted() {
            await this.fetchSalespersons();
        },
    }

</script>
