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

            <b-form-group label="1099 Status" label-for="created_status" class="mr-2">
                <b-form-select id="created_status"
                               v-model="form.created"
                >
                    <option value="">Any</option>
                    <option value="1">Created</option>
                    <option value="0">Not Yet Created</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="Transmission Status" label-for="transmission_status" class="mr-2">
                <b-form-select id="transmission_status"
                               v-model="form.transmitted"
                >
                    <option value="">All</option>
                    <option value="1">Transmitted</option>
                    <option value="0">Not Transmitted</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate</b-btn>
                <b-btn variant="info" @click="transmitSelected()">Transmit Selected</b-btn>
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
                        <template slot="actions" scope="row">
                            <b-btn @click="create(row.item)" class="btn btn-secondary" title="Create 1099"><i class="fa fa-plus mr-2"></i></b-btn>
                            <b-btn v-if="row.item.caregiver_1099_id"
                                   @click="edit(row.item.caregiver_1099_id)"
                                   class="btn btn-secondary"
                                   title="Edit 1099"
                            >
                                <i class="fa fa-edit mr-2"></i></b-btn>
                        </template>

                        <template slot="transmit" scope="row">
                            <b-form-checkbox v-if="row.item.caregiver_1099_id"
                                             v-model="transmitSelected"
                                             :value="row.item.caregiver_1099_id"
                            >
                            </b-form-checkbox>
                        </template>
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
                        created: '',
                        transmitted: '',
                        json: 1,
                }),
                transmitSelected: [],
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
                    {key: 'caregiver_1099', label: 'Caregiver 1099', sortable: true, formatter: x => { return _.startCase(x) }},
                    {key: 'location', label: 'Location', sortable: true,},
                    {key: 'total', label: 'Total Year Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                    'actions',
                    'transmit'
                ],
            }
        },
        methods: {
            async loadFilters() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            generate(){
                this.busy = true;
                this.totalRows = 0;
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

            create(item){
                let data = new Form({item});
                data.item.year = this.form.year;
                data.item.business_id = this.form.business_id;
                data.post('/admin/business-1099/create');
            },

            edit(id){
                axios.get('/admin/business-1099/' + id);
            },

            transmit(){
                let data = new Form({transmitSelected});

                data.get('/admin/business-1099/transmit')
                    .then(response => {
                    })
                    .catch( e => {
                    })
                    .finally(() => {
                    });
            },
        },
        watch: {
            'form.business_id'(newVal, oldVal){
                if(newVal !== oldVal){
                    axios.get('/admin/clients?json=1&id=' + this.form.business_id + '&active=1').then(response => this.clients = response.data);
                    axios.get('/admin/caregivers?json=1&id=' + this.form.business_id + '&active=1').then(response => this.caregivers = response.data);
                    this.client_id='';
                    this.caregiver_id='';
                }
            },
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
    i.fa.fa-edit,
    i.fa.fa-plus{
        position: relative;
        left: 4px;
    }

    button.btn.btn-secondary{
        padding: 2px;
    }
</style>