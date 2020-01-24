<template>
    <b-card
        header="1099 Caregivers Not Elected Report"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-alert show variant="info">All of the Caregivers that appear here made meet the criteria to receive a 1099 but worked for clients that requested not to send 1099s.</b-alert>
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
                    <option value="client">Client</option>
                    <option value="ally">Ally</option>

                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="form.busy">Generate</b-btn>
            </b-form-group>
        </b-row>

        <loading-card v-if="form.busy" />
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                        bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :sort-by="sortBy"
                        :empty-text="emptyText"
                        :busy="form.busy"
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
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'client_last_name',
                sortDesc: false,
                emptyText: "No records to display",
                selected: '',
                firstRun: true,
                fields: [
                    {key: 'business_name', label: 'Office Location', sortable: true,},
                    {key: 'caregiver_name', label: 'Caregiver', sortable: true,},
                    {key: 'caregiver_phone', label: 'Caregiver Phone', sortable: true,},
                    {key: 'caregiver_email', label: 'Caregiver Email', sortable: true,},
                    {key: 'client_name', label: 'Client', sortable: true,},
                    // {key: 'caregiver_1099', label: 'Payer', sortable: true, formatter: x => { return _.startCase(x) }},
                    {key: 'earnings', label: 'Total Year Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],

            }
        },

        methods: {
            generate() {
                this.items = [];
                this.totalRows = 0;
                this.form.get('/admin/reports/1099-not-elected')
                    .then(({data}) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                    });
            },
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
    }
</script>

<style scoped>
    .location_select{
        margin-top: 5px;
    }
</style>