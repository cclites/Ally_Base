<template>
        <b-row>
            <b-col>
                <b-card header="Available Shifts Report"
                                 header-text-variant="white"
                                 header-bg-variant="info"
                >
                    <b-row>
                        <business-location-form-group
                                v-model="form.businesses"
                                label="Office Location"
                                class="mr-2"
                                :allow-all="false"
                        />
                        <b-form-group label="Start Date" class="mr-2">
                            <date-picker v-model="form.start" name="start_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="End Date"class="mr-2">
                            <date-picker v-model="form.end" name="end_date"></date-picker>
                        </b-form-group>
                        <b-form-group label="Client" class="mr-2">
                            <b-form-select v-model="form.client_id" :disabled="loadingClients">
                                <option value="">All</option>
                                <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="City" class="mr-2">
                            <b-form-select v-model="form.city" :disabled="loadingCities">
                                <option value="">All</option>
                                <option v-for="item in cities" :value="item">{{ item }}</option>
                            </b-form-select>
                        </b-form-group>
                        <b-form-group label="Service" class="mr-2">
                            <b-form-select v-model="form.service" :disabled="loadingServices">
                                <option value="">All</option>
                                <option v-for="item in services" :value="item.id">{{ item.name }}</option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="&nbsp;">
                            <b-btn @click="fetchReportData()" variant="info" :disabled="busy">
                                <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                                Generate Report
                            </b-btn>
                            <b-btn @click="print()" :disabled="busy"><i class="fa fa-print mr-1"></i>Print</b-btn>
                        </b-form-group>
                    </b-row>

                    <b-table
                            class="table"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :empty-text="emptyText"
                            :busy="busy"
                            :current-page="currentPage"
                            :per-page="perPage"
                    >
                        <template slot="client_services" scope="row">
                            <div v-for="(service, index) in row.item.client_services" :key="index">
                                {{ service[0].service_name }}
                            </div>
                        </template>
                        <template slot="day" scope="row">
                            <div v-for="(service, index) in row.item.client_services" :key="index">
                                {{ service[0].day }}
                            </div>
                        </template>
                        <template slot="date" scope="row">
                            <div v-for="(service, index) in row.item.client_services" :key="index">
                                {{ service[0].date }}
                            </div>
                        </template>
                        <template slot="start_time" scope="row">
                            <div v-for="(service, index) in row.item.client_services" :key="index">
                                {{ service[0].start_time }}
                            </div>
                        </template>
                        <template slot="end_time" scope="row">
                            <div v-for="(service, index) in row.item.client_services" :key="index">
                                {{ service[0].end_time }}
                            </div>
                        </template>
                    </b-table>

                    <b-row v-if="this.items.length > 0">
                        <b-col lg="6" >
                            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                        </b-col>
                        <b-col lg="6" class="text-right">
                            Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                        </b-col>
                    </b-row>
                </b-card>
            </b-col>
        </b-row>
</template>

<script>

    import BusinessLocationFormGroup from '../BusinessLocationFormGroup';

    export default {

        props: {

        },

        components: {BusinessLocationFormGroup},

        mixins: [],

        data() {
            return {
                form: new Form({
                    businesses: '',
                    start: moment().format('MM/DD/YYYY'),
                    end:  moment().add(7, "days").format('MM/DD/YYYY'),
                    client_id: '',
                    city: '',
                    service: '',
                    inactive: false,
                }),
                fields: [
                    { key: 'client_name', label: 'Client', sortable: true, },
                    { key: 'client_city', label: 'City', sortable: true, },
                    { key: 'case_manager', label: 'Case Manager', sortable: true, },
                    { key: 'client_services', label: 'Services', sortable: false, },
                    { key: 'day', label: 'Day', sortable: false, },
                    { key: 'date', label: 'Date', sortable: false, },
                    { key: 'start_time', label: 'Start Time', sortable: false, },
                    { key: 'end_time', label: 'End Time', sortable: false, },
                ],
                clients:'',
                cities:'',
                services: '',
                loadingClients: false,
                loadingCities: false,
                loadingServices: false,
                fetchData: false,
                fetchPdf: false,
                items: [],
                busy: false,
                emptyText: "No records to display",
                perPage: 25,
                totalRows: 0,
                currentPage: 1,
                sortBy: 'client_name',
            }
        },

        mounted() {
            this.fetchClients();
            this.fetchServices();
            this.fetchCities();
        },

        computed: {

        },

        methods: {

            async fetchClients()
            {
                this.loadingClients = true;

                await axios.get(`/business/dropdown/clients?businesses=${this.form.businesses}&inactive=${this.form.inactive}`)
                    .then( ({ data }) => {
                        this.clients = data;
                    })
                    .catch(() => {
                    })
                    .finally(() => {
                        this.loadingClients = false;
                    });
            },

            async fetchServices()
            {
                this.loadingServices = true;

                await axios.get(`/business/dropdown/services`)
                    .then( ({ data }) => {
                        this.services = data;
                    })
                    .catch(() => {
                    })
                    .finally(() => {
                        this.loadingServices = false;
                    });
            },

            async fetchCities()
            {
                this.loadingCities = true;

                await axios.get(`/business/dropdown/cities?businesses=${this.form.businesses}`)
                    .then( ({ data }) => {
                        this.cities = data;
                    })
                    .catch(() => {
                    })
                    .finally(() => {
                        this.loadingCities = false;
                    });
            },

            fetchReportData(){
                let url = 'available-shifts?json=1';

                this.fetchData = true;

                this.form.get(url)
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(() => {
                    })
                    .finally(() => {
                        this.fetchData = false;
                    });
            },

            print(){
                window.location = this.form.toQueryString('available-shifts?export=1');
            }

        },

        watch: {},
    }
</script>

<style scoped>
    .table{
        width: 100%;
    }
</style>