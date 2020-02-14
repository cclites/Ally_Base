<template>
    <b-card>
        <div class="mb-4">
            <b-btn variant="info" class="mb-3" href="/notes/create">Add Note</b-btn>
        </div>
        <b-form @submit.prevent="filter" class="mb-2">
            <b-row>
                <b-col lg="2">
                    <business-location-select v-model="searchForm.businesses" :allow-all="true" :hideable="false" name="businesses" />
                </b-col>
                <b-col lg="2">
                    <date-picker class="mb-2" v-model="searchForm.start_date"  placeholder="Start Date" />
                </b-col>
                <b-col lg="2">
                    <date-picker class="mb-2" v-model="searchForm.end_date"  placeholder="End Date" />
                </b-col>
                <b-col lg="2">
                    <b-form-select v-model="searchForm.type" class="mb-2">
                        <option :value="null">-- Type --</option>
                        <option :value="type.value" v-for="type in types" :key="type.value">{{ type.text }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.client" class="mb-2">
                        <option :value="null">-- Client --</option>
                        <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.nameLastFirst }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.caregiver" class="mb-2">
                        <option :value="null">-- Caregiver --</option>
                        <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.prospect" class="mb-2">
                        <option :value="null">-- Prospect --</option>
                        <option :value="prospect.id" v-for="prospect in prospects" :key="prospect.id">{{ prospect.nameLastFirst }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.referral_source" class="mb-2">
                        <option :value="null">-- Referral Source --</option>
                        <option :value="rs.id" v-for="rs in referral_sources" :key="rs.id">{{ rs.organization }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.user" class="mb-2">
                        <option :value="null">-- Created by --</option>
                        <option :value="user.id" v-for="user in users" :key="user.id">{{ user.name }}</option>
                    </b-form-select>
                </b-col>
                <b-col lg="3">
                    <b-form-input v-model="searchForm.free_form"
                                  class="mb-2"
                                  placeholder="Enter search term"
                    >
                    </b-form-input>
                </b-col>
                <b-col lg="3">
                    <b-form-select v-model="searchForm.template_id" class="mb-2">
                        <option :value="null">-- Template --</option>
                        <option :value="template.id" v-for="template in templates" :key="template.id">{{ template.short_name }}</option>
                    </b-form-select>
                </b-col>
            </b-row>
            <div>
                <b-btn @click="print" variant="primary" class="float-right"><i class="fa fa-print"></i> Print</b-btn>
                <b-button variant="info" type="submit" class="mb-2 mr-2 float-right">
                    Generate List
                </b-button>
            </div>
        </b-form>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading">

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        @filtered="onFiltered"
                >
                    <template slot="caregiver" scope="data">
                        <span v-if="data.item.caregiver">{{ data.item.caregiver.nameLastFirst }}</span>
                    </template>
                    <template slot="client" scope="data">
                        <span v-if="data.item.client">{{ data.item.client.name }}</span>
                    </template>
                    <template slot="prospect" scope="data">
                        <span v-if="data.item.prospect">{{ data.item.prospect.name }}</span>
                    </template>
                    <template slot="referral_source" scope="data">
                        <span v-if="data.item.referral_source">{{ data.item.referral_source.organization }}</span>
                    </template>
                    <template slot="action" scope="data">
                        <b-btn variant="secondary" @click="edit(data.item)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>

        <b-modal id="noteModal" :title="noteModalTitle" v-model="noteModal" size="lg">
            <note-form :caregiver="{}" :client="{}" :prospect="{}" :referralSource="{}" :note="note" :modal="1" ref="noteForm" />

            <div slot="modal-footer">
               <b-btn variant="default" @click="noteModal=false">Close</b-btn>
               <b-btn variant="danger" @click="destroy()" v-if="note.id">Delete</b-btn>
               <b-btn variant="info" @click="save()">Save</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsStrings from "../../mixins/FormatsStrings";
    import BusinessLocationSelect from "../business/BusinessLocationSelect";

    export default {      
        mixins: [ FormatsDates, FormatsStrings ],
        components: { BusinessLocationSelect },
        props: {
        },

        data() {
            return {
                note: {},
                noteModal: false,
                users: [],
                templates: [],
                caregivers: [],
                clients: [],
                prospects: [],
                referral_sources: [],
                items: [],
                searchForm: new Form({
                    businesses: '',
                    start_date: moment().utc().subtract(1, 'days').format('MM/DD/YYYY'), // todo, make this local, but backend needs to know what the local timezone is
                    end_date: moment.utc().format('MM/DD/YYYY'),
                    caregiver: null,
                    client: null,
                    prospect: null,
                    referral_source: null,
                    user: null,
                    type: null,
                    tags: '',
                    free_form: '',
                    template_id: null
                }),
                types: [
                    { text: 'Phone', value: 'phone' },
                    { text: 'Other', value: 'other' },
                ],
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'created_at',
                sortDesc: true,
                loading: false,
                fields: [
                    {
                        key: 'created_at',
                        label: 'Note Date',
                        sortable: true,
                        formatter: d => { return this.formatDateFromUTC(d) },
                    },
                    {
                        key: 'type',
                        label: 'Type',
                        sortable: true,
                        formatter: d => d == 'other' ? 'Note' : 'Phone Call'
                    },
                    {
                        key: 'caregiver',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'prospect',
                        label: 'Prospect',
                        sortable: true,
                    },
                    {
                        key: 'referral_source',
                        label: 'Referral Source',
                        sortable: true,
                    },
                    {
                        key: 'tags',
                        label: 'Tags',
                        sortable: true,
                    },
                    {
                        key: 'body',
                        label: 'Preview',
                        sortable: false,
                        formatter: val => this.stringLimit(val, 70),
                    },
                    'action'
                ]
            }
        },

        mounted() {
            this.loadClients();
            this.loadCaregivers();
            this.loadProspects();
            this.loadReferralSources();
            this.loadUsers();
            this.loadTemplates();
            this.filter();
        },

        computed: {
            noteModalTitle() {
                return this.note.id ? 'Edit Note' : 'Add Note';
            },
        },

        watch: {
           
        },

        methods: {
            async loadTemplates(){
                const response = await axios.get('/note-templates?json=1');
                this.templates = response.data;
            },
            async loadClients() {
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
            },

            async loadCaregivers() {
                const response = await axios.get('/business/caregivers?json=1');
                this.caregivers = response.data;
            },

            async loadProspects() {
                const response = await axios.get('/business/prospects?json=1');
                this.prospects = response.data;
            },

            async loadReferralSources() {
                const response = await axios.get('/business/referral-sources?json=1');
                this.referral_sources = response.data;
            },

            async loadUsers(){
                axios.get(`/business/notes/creators?json=1`)
                .then( ({ data }) => {
                    this.users = data;
                })
                .catch(() => {
                });
            },

            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            
            filter() {
                this.loading = true;
                axios.post('/notes/search', this.searchForm)
                    .then(response => {
                        this.items = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                    });
            },

            create() {
                this.note = {
                    caregiver_id: this.searchForm.caregiver ? this.searchForm.caregiver : '',
                    client_id: this.searchForm.client ? this.searchForm.client : '',
                    prospect_id: this.searchForm.prospect ? this.searchForm.prospect : '',
                    referral_source_id: this.searchForm.referral_source ? this.searchForm.referral_source : '',
                };
                this.noteModal = true;
            },

            edit(note) {
                this.note = note;
                this.noteModal = true;
            },
            
            save() {
                this.$refs.noteForm.submit()
                    .then(note => {
                        if (this.note.id) {
                            this.items = this.items.filter(obj => obj.id != this.note.id);
                        }
                        this.items.push(note);
                        this.note = {update: Math.random()};
                        this.noteModal = false;
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },

            destroy() {
                let f = new Form({});

                f.submit('delete', '/notes/' + this.note.id)
                    .then( ({ data }) => {
                        this.items = this.items.filter(obj => obj.id != this.note.id);
                        this.note = {};
                        this.noteModal = false;
                    })
            },
            print(){

                axios.post('/notes/search?print=1',
                            this.searchForm,
                            {
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/pdf'
                                },
                                responseType: 'blob'
                            },
                    )
                    .then(response => {
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                        var fileLink = document.createElement('a');
                        fileLink.href = fileURL;
                        fileLink.setAttribute('download', 'Notes.pdf');
                        document.body.appendChild(fileLink);
                        fileLink.click();
                        fileLink.remove();

                    })
                    .catch(error => {
                        console.error(error.response);
                    });
            }
        }
    }
</script>

<style>
</style>