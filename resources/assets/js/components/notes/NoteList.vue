<template>
    <b-card>
        <b-form inline @submit.prevent="filter" class="mb-4">
            <b-form-input
                    type="text"
                    id="start-date"
                    class="datepicker mr-2 mb-2"
                    v-model="searchForm.start_date"
                    placeholder="Start Date"
                    @change="filter"
            >
            </b-form-input>

            <b-form-input
                    type="text"
                    id="end-date"
                    class="datepicker mr-2 mb-2"
                    v-model="searchForm.end_date"
                    placeholder="End Date"
            >
            </b-form-input>

            <b-form-select v-model="searchForm.type" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Type --</option>
                </template>
                <option :value="type.value" v-for="type in types" :key="type.value">{{ type.text }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.caregiver" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Caregiver --</option>
                </template>
                <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.nameLastFirst }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.client" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Client --</option>
                </template>
                <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.nameLastFirst }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.prospect" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Prospect --</option>
                </template>
                <option :value="prospect.id" v-for="prospect in prospects" :key="prospect.id">{{ prospect.nameLastFirst }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.referral_source" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Referral Source --</option>
                </template>
                <option :value="rs.id" v-for="rs in referral_sources" :key="rs.id">{{ rs.organization }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.user" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- User --</option>
                </template>
                <option :value="user.id" v-for="user in users" :key="user.id">{{ user.nameLastFirst }}</option>
            </b-form-select>

            <!--<b-form-input
                type="text"
                id="tags"
                v-model="searchForm.tags"
                class="mr-2 mb-2"
                placeholder="Tags">
            </b-form-input>-->

            <b-button variant="info" type="submit" class="mb-2">
                Filter
            </b-button>
        </b-form>

        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading">
            <b-btn variant="info" class="mb-3" href="/notes/create">Add Note</b-btn>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :sort-by.sync="sortBy"
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
    export default {      
        mixins: [ FormatsDates ],

        props: {
            'notes': Array,
        },

        data() {
            return {
                note: {},
                noteModal: false,
                users: [],
                caregivers: [],
                clients: [],
                prospects: [],
                referral_sources: [],
                items: this.notes,
                searchForm: {
                    caregiver: null,
                    client: null,
                    prospect: null,
                    referral_source: null,
                    user: null,
                    type: null,
                    tags: ''
                },
                types: [
                    { text: 'Phone', value: 'phone' },
                    { text: 'Other', value: 'other' },
                ],
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'created_at',
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
                        formatter: d => d && d.substr(0, 1).toUpperCase() + d.substr(1),
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
                    // {
                    //     key: 'tags',
                    //     label: 'Tags',
                    //     sortable: true,
                    // },
                    {
                        key: 'body',
                        label: 'Preview',
                        sortable: false
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
            this.totalRows = this.items.length;
            let startDate = jQuery('#start-date');
            let endDate = jQuery('#end-date');
            let component = this;
            startDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.start_date = startDate.val();
            });
            endDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.end_date = endDate.val();
            });

        },

        computed: {
            noteModalTitle() {
                return this.note.id ? 'Edit Note' : 'Add Note';
            },
        },

        watch: {
            notes(val) {
                val.forEach(note => {
                    if (!this.users.find(x => x.id === note.created_by)) {
                        this.users.push(note.creator);
                    }
                });
            },
        },

        methods: {
            async loadClients() {
                console.log('loadClients called');
                const response = await axios.get('/business/clients?json=1');
                this.clients = response.data;
            },

            async loadCaregivers() {
                console.log('loadCaregivers called');
                const response = await axios.get('/business/caregivers?json=1');
                this.caregivers = response.data;
            },

            async loadProspects() {
                console.log('loadProspects called');
                const response = await axios.get('/business/prospects?json=1');
                this.prospects = response.data;
            },

            async loadReferralSources() {
                console.log('loadReferralSources called');
                const response = await axios.get('/business/referral-sources?json=1');
                this.referral_sources = response.data;
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
        }
    }
</script>

<style>
.datepicker { z-index: 1000!important };
</style>