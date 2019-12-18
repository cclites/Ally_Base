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
                    <option value="2019">2019</option>
                </b-form-select>
            </b-form-group>

            <business-location-form-group
                    v-model="form.business_id"
                    :allow-all="true"
                    class="mr-2 location_select"
                    label="Location"
            />

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

            <b-form-group label="Payer" label-for="payer" class="mr-2">
                <b-form-select id="payer"
                               v-model="form.payer"
                >
                    <option value="">All</option>
<!--                    <option value="no">No</option>-->
                    <option value="client">Client</option>
                    <option value="ally">Ally</option>

                </b-form-select>
            </b-form-group>

            <b-form-group label="1099 Status" label-for="created_status" class="mr-2">
                <b-form-select id="created_status" v-model="form.created">
                    <option value="">Any</option>
                    <option value="1">Created</option>
                    <option value="0">Not Yet Created</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate</b-btn>
            </b-form-group>

        </b-row>

        <loading-card v-if="form.busy"></loading-card>
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                        :items="items"
                        :fields="fields"
                        :sort-by="sortBy"
                        :empty-text="emptyText"
                        :busy="busy"
                        :current-page="currentPage"
                        :per-page="perPage"
                    >
                        <template slot="actions" scope="row">
                            <b-btn @click="create(row)"
                                   class="btn btn-secondary"
                                   title="Create 1099"
                                   v-if="! row.item.caregiver_1099_id && ! row.item.errors.length"
                            >
                                <i class="fa fa-plus mr-2"></i>
                            </b-btn>

                            <b-btn @click="showErrors(row)"
                                   class="btn btn-danger"
                                   title="Show Errors"
                                   v-if="row.item.errors.length"
                            >
                                <i class="fa fa-exclamation-triangle mr-2"></i>
                            </b-btn>

                            <b-btn v-if="row.item.caregiver_1099_id"
                                   @click="edit(row.item.caregiver_1099_id)"
                                   class="btn btn-secondary"
                                   title="Edit 1099"
                            >
                                <i class="fa fa-edit mr-2"></i>
                            </b-btn>
                            <b-btn v-if="row.item.caregiver_1099_id"
                                   @click="downloadPdf(row.item.caregiver_1099_id)"
                                   class="btn btn-secondary"
                                   title="Download PDF"
                            >
                                <i class="fa fa-print mr-2"></i>
                            </b-btn>
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

        <b-modal
                v-model="caregiver1099Edit"
                @ok.prevent="save()"
                @cancel="hideEditModal()"
                ok-variant="info"
                size="lg"
        >
            <caregiver-1099-edit-modal :caregiver1099="caregiver1099"></caregiver-1099-edit-modal>
        </b-modal>

        <b-modal
                v-model="showErrorModal"
                @cancel="hideEditModal()"
                ok-variant="info"
                size="md"
                title="Caregiver 1099 Errors"
        >
            <label class="mb-2">Caregiver 1099 is missing:</label>

            <b-row v-for="item in errorItems" :key="item" class="mb-3 pl-4">
                {{ item }}
            </b-row>

            <hr>

            <a :href="'/business/clients/' + selected.client_id">Edit Client</a>
            <br>
            <a :href="'/business/caregivers/' + selected.caregiver_id">Edit Caregiver</a>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "Admin1099PreviewReport",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsNumbers, FormatsDates],
        props: {},
        data() {
            return {
                form: new Form({
                        business_id: '',
                        client_id: '',
                        caregiver_id: '',
                        year: '2019',
                        payer: '',
                        created: '',
                        transmitted: '',
                        json: 1,
                }),
                transmitSelected: [],
                caregivers: [],
                clients: [],
                items: [],
                caregiver1099: [],
                caregiver1099Edit: false,
                showErrorModal: false,
                errorItems: [],
                busy: false,
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'client_last_name',
                sortDesc: false,
                emptyText: "No records to display",
                selected: '',
                firstRun: true,
                fields: [
                    {key: 'client_first_name', label: 'Client First Name', sortable: true,},
                    {key: 'client_last_name', label: 'Client Last Name', sortable: true,},
                    {key: 'caregiver_first_name', label: 'Caregiver First Name', sortable: true,},
                    {key: 'caregiver_last_name', label: 'Caregiver Last Name', sortable: true,},
                    {key: 'caregiver_1099', label: 'Payer', sortable: true, formatter: x => { return _.startCase(x) }},
                    {key: 'business_name', label: 'Office Location', sortable: true,},
                    {key: 'payment_total', label: 'Total Year Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                    'actions',
                ],

            }
        },
        methods: {
            generate(){
                this.items = [];
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
                    })
            },

            create(record){

                let index = record.index;

                let data = new Form({
                    'year': this.form.year,
                    'business_id': this.form.business_id,
                    'client_id' : record.item.client_id,
                    'caregiver_id' : record.item.caregiver_id,
                    'payment_total' : record.item.payment_total,
                });

                data.post('/admin/business-1099/create')
                    .then(response => {
                        this.generate();
                    })
                    .catch( e => {
                    })
                    .finally(() => {
                    });
            },

            edit(id){
                axios.get('/admin/business-1099/edit/' + id)
                .then(response => {
                    this.caregiver1099 = response.data;
                    this.caregiver1099Edit = true;
                })
                .catch( e => {})
                .finally(() => {});
            },

            /* Unused at this time */
            transmit(){
                let url = '/admin/business-1099/transmit?transmitSelected=' + this.transmitSelected;

                axios.get(url)
                    .then(response => {
                        let csv = response.data;

                        var hiddenElement = document.createElement('a');
                        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
                        hiddenElement.target = '_blank';
                        hiddenElement.download = 'Transmission_Report.csv';
                        hiddenElement.click();

                        this.transmitSelected = [];
                        this.generate();
                    })
                    .catch( e => {})
                    .finally(() => {
                    });
            },

            save(){
                let data = new Form( this.caregiver1099 );
                data.patch('/admin/business-1099/' + this.caregiver1099.id)
                .then(response => {
                    this.generate();
                })
                .catch( e => {})
                .finally(() => {
                    this.caregiver1099Edit = false;
                });
            },

            downloadPdf(id){
                window.location = '/admin/business-1099/download/' + id;
            },

            showErrors(row){
                this.selected = row.item;
                this.errorItems = row.item.errors;
                this.showErrorModal = true;
            },

            hideEditModal(){
                this.errorItems = [];
                this.showErrorModal = false;
            }
        },
        watch: {
            'form.business_id'(newVal, oldVal){

                if(this.form.business_id !== ''){
                    if(newVal !== oldVal) {
                        axios.get('/admin/clients?json=1&id=' + this.form.business_id + '&all=1').then(response => this.clients = response.data);
                        axios.get('/admin/caregivers?json=1&id=' + this.form.business_id + '&all=1').then(response => this.caregivers = response.data);
                        this.client_id = '';
                        this.caregiver_id = '';
                    }
                }
            },
        },
        computed: {
            disableGenerate(){
                if(this.caregivers.length){
                    return false;
                }
                return true;
            }
        },
    }
</script>

<style scoped>
    i.fa.fa-edit,
    i.fa.fa-print,
    i.fa.fa-plus,
    i.fa.fa-exclamation-triangle{
        position: relative;
        left: 4px;
    }

    i.fa.fa-exclamation-triangle{
        color: #ffffff;
    }

    button.btn.btn-secondary{
        padding: 2px;
    }

    .location_select{
        margin-top: 5px;
    }
</style>