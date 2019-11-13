<template>
    <b-card
            header="1099 Preview Report"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-form-group label="Year" label-for="year" class="mr-2">
                <b-form-select id="year"
                               v-model="form.year"
                >
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="Business" label-for="business_id" class="mr-2">
                <b-form-select id="business_id"
                               v-model="form.business_id"
                >
                    <option value="">All Businesses</option>
                    <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="Caregivers" label-for="caregiver_id" class="mr-2">
                <b-form-select
                        id="caregiver_id"
                        name="caregiver_id"
                        v-model="form.caregiver_id"
                >
                    <option value="">All Caregivers</option>
                    <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="Clients" label-for="client_id" class="mr-2">
                <b-form-select
                        id="client_id"
                        name="client_id"
                        v-model="form.client_id"
                >
                    <option value="">All Clients</option>
                    <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="Caregiver 1099" label-for="caregiver_1099" class="mr-2">
                <b-form-select id="caregiver_1099"
                               v-model="form.caregiver_1099"
                >
                    <option value="">All</option>
                    <option value="no">No</option>
                    <option value="client">Client</option>
                    <option value="ally">Ally</option>

                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate Preview</b-btn>
            </b-form-group>

        </b-row>

        <div class="d-flex justify-content-center" v-if="busy">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                            class="payers-summary-table"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :empty-text="emptyText"
                            :busy="busy"
                            :current-page="currentPage"
                            :per-page="perPage"
                    >
                    </b-table>
                </b-col>
            </b-row>
        </div>

        <b-row v-if="this.items.length > 0">
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
        <b-row v-else>
            <b-col class="text-center">{{ emptyText }}</b-col>
        </b-row>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        name: "Admin1099PreviewReport",
        mixins: [FormatsNumbers],
        props: {},
        data() {
            return {
                start_date: 2017, //arbitrary start year
                end_date: moment().year(),
                form: new Form({
                        business_id: '',
                        client_id: '',
                        caregiver_id: '',
                        year: '',
                        caregiver_1099: '',
                        json: 1,
                }),
                businesses: [],
                caregivers: [],
                clients: [],
                items: [],
                busy: false,
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'client_lname',
                sortDesc: false,
                emptyText: "No records to display",
                fields: [
                    {key: 'client_fname', label: 'Client First Name', sortable: true,},
                    {key: 'client_lname', label: 'Client Last Name', sortable: true,},
                    {key: 'caregiver_fname', label: 'Caregiver First Name', sortable: true,},
                    {key: 'caregiver_lname', label: 'Caregiver Last Name', sortable: true,},
                    {key: 'caregiver_1099', label: '1099 Status', sortable: true, formatter: x => { return _.startCase(x) }},
                    {key: 'location', label: 'Location', sortable: true,},
                    {key: 'total', label: 'Total Year Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],
            }
        },
        methods: {
            async loadFilters() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            generate(){
                this.busy = true;
                this.form.get('/admin/preview-1099-report')
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                        this.footClone = true;
                    })
            },
        },
        watch: {
            'form.business_id'(newVal, oldVal){

                if(newVal !== oldVal){
                    axios.get('/admin/clients?json=1&id=' + this.form.business_id).then(response => this.clients = response.data);
                    axios.get('/admin/caregivers?json=1&id=' + this.form.business_id).then(response => this.caregivers = response.data);
                    this.client_id='';
                    this.caregiver_id='';
                }

            },

            'form.caregiver_id'(newVal, oldVal){},

            'form.client_id'(newVal, oldVal){},
        },
        computed: {
            years(){
                let x = [];
                let i = this.start_date;
                while( i <= this.end_date){
                    x.push(i++);
                };

                this.form.year = this.start_date;
                return x;
            },

            disableGenerate(){
                if(this.businesses.length && this.clients.length && this.caregivers.length){
                    return false;
                }
                return true;
            }
        },
        async mounted(){
            this.loadFilters();
        }
    }
</script>

<style scoped>

</style>