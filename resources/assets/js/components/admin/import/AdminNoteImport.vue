<template>
    <div>
        <b-card>
            <admin-note-import-form :businesses="businesses"
                :name.sync="name"
                @imported="loadImportedData"
                v-show="imported.length === 0"
            ></admin-note-import-form>

            <div v-if="imported.length > 0">
                <div class="row">
                    <div class="col form-inline">
                        <select v-model="filters.ByMatch" class="form-control">
                            <option value="">--Show All (Match)--</option>
                            <option value="unmatched">Show Only Unmatched</option>
                            <option value="matched">Show Only Matched</option>
                        </select>

                        <select v-model="filters.ByType" class="form-control">
                            <option value="">--Show All (Type)--</option>
                            <option value="phone">Show By Phone</option>
                            <option value="other">Show By Other</option>
                        </select>

                        <input v-model="filters.ByName" class="form-control" placeholder="Filter by Name" />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" :key="">
                        <thead>
                        <tr>
                            <th>Row #</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th colspan="2">Client</th>
                            <th colspan="2">Caregiver</th>
                            <th>Body</th>
                            <th>Tags</th>
                            <th>User ID Created By</th>
                        </tr>
                        </thead>
                        <tbody>
                        <admin-note-import-id-row v-for=" row in paginated "
                            :clients="clients"
                            :caregivers="caregivers"
                            :note.sync="row.note"
                            :identifiers="row.identifiers"
                            :key="row.note.rowNo"
                            :index="row.note.rowNo"
                            @swapIdentifiers=" swapIdentifiers "
                            @mappedIdentifier="mapIdentifier"
                            @createClient="loadCreateClient"
                            @createCaregiver="loadCreateCaregiver"
                            @removeRow="removeRow"
                        ></admin-note-import-id-row>
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col">
                        <nav aria-label="..." class="pull-left">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="page-link" href="#" @click="page=1">First</a>
                                </li>
                                <li class="page-item" v-if="page > 2">
                                    <a class="page-link" href="#" @click="page-=2">{{ page-2 }}</a>
                                </li>
                                <li class="page-item" v-if="page > 1">
                                    <a class="page-link" href="#" @click="page-=1">{{ page-1 }}</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">{{ page }} <span class="sr-only">(current)</span></a>
                                </li>
                                <li class="page-item" v-if="page+1 <= lastPage">
                                    <a class="page-link" href="#" @click="page+=1">{{ page+1 }}</a>
                                </li>
                                <li class="page-item" v-if="page+2 <= lastPage">
                                    <a class="page-link" href="#" @click="page+=2">{{ page+2 }}</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#" @click="page=lastPage">Last</a>
                                </li>
                            </ul>
                        </nav>
                        <select v-model="itemsPerPage" class="form-control pull-left" style="max-width: 150px;">
                            <option value="25">25 Per Page</option>
                            <option value="50">50 Per Page</option>
                            <option value="100">100 Per Page</option>
                            <option value="250">250 Per Page</option>
                            <option value="999">999 Per Page</option>
                        </select>
                    </div>
                </div>

                <div class="pull-right">
                    <b-btn @click="saveDraft()" variant="primary"><i class="fa fa-save"></i> Save Draft</b-btn>
                    <b-btn @click="savenotes()" variant="info" :disabled="submitting">
                        <i class="fa fa-spin fa-spinner" v-if="submitting"></i>
                        <i class="fa fa-upload" v-else></i> Save Notes
                    </b-btn>
                    <b-btn @click="deleteDraft()" variant="danger"><i class="fa fa-times"></i> Delete &amp; Cancel</b-btn>
                </div>
            </div>

        </b-card>

        <b-modal title="Create Client" v-model="createClientModal" size="lg" @ok="saveCreateClient()">
            <b-container fluid v-if="createClientModal" :key="createClientName">
                <form @keydown="createClientForm.clearError($event.target.name)">
                    <client-create-form v-model="createClientForm"></client-create-form>
                </form>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="info" @click="saveCreateClient()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    <i class="fa fa-save" v-show="!submitting"></i>
                    Create Client
                </b-btn>
                <b-btn variant="default" @click="createClientModal=false">Close</b-btn>
            </div>
        </b-modal>

        <b-modal title="Create Caregiver" v-model="createCaregiverModal" size="lg">
            <b-container fluid v-if="createCaregiverModal" :key="createCaregiverName">
                <form @keydown="createCaregiverForm.clearError($event.target.name)">
                    <caregiver-create-form v-model="createCaregiverForm"></caregiver-create-form>
                </form>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="info" @click="saveCreateCaregiver()" :disabled="submitting">
                    <i class="fa fa-spinner fa-spin" v-show="submitting"></i>
                    <i class="fa fa-save" v-show="!submitting"></i>
                    Create Caregiver
                </b-btn>
                <b-btn variant="default" @click="createCaregiverModal=false">Close</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import ClientCreateForm from "../../forms/ClientCreateForm";
    import CaregiverCreateForm from "../../forms/CaregiverCreateForm";
    import axios from "axios";

    export default {

        components: {
            CaregiverCreateForm,
            ClientCreateForm,
            'admin-note-import-form': require('./AdminNoteImportForm'),
            'admin-note-import-id-row': require('./AdminNoteImportIdRow'),
        },

        props: {},

        data() {
            return {
                'name': '',
                'businesses': [],
                'caregivers': [],
                'clients': [],
                'imported': [],
                'draft': false,
                'submitting': false,
                'filters': {
                    'ByName': '',
                    'ByMatch': '',
                    'ByType': '',
                },
                'page': 1,
                'itemsPerPage': 50,
                'filtered': [],
                'createClientModal': false,
                'createClientForm': new Form(),
                'createClientName': '',
                'createCaregiverModal': false,
                'createCaregiverForm': new Form(),
                'createCaregiverName': '',
            }
        },

        computed: {
            paginated() {
                let start = (this.itemsPerPage * this.page) - this.itemsPerPage;
                let end = start + this.itemsPerPage;

                return this.filtered.slice(start, end);
            },

            lastPage() {
                if (!this.filtered.length) return 1;
                return Math.ceil(this.filtered.length / this.itemsPerPage);
            }
        },

        async mounted() {
            this.loadBusinesses();
            this.imported = this.loadDraft();
        },

        methods: {

            removeRow( rowNo ){

                console.log( rowNo, this.imported.findIndex( data => data.note.rowNo == rowNo ) );
                if( !confirm( 'remove this record?' ) ) return;
                const index = this.imported.findIndex( data => data.note.rowNo == rowNo );
                this.imported.splice( index, 1 );
                this.loadFiltered();
            },
            swapIdentifiers( rowNo ){

                let found = this.imported.find( data => data.note.rowNo == rowNo );
                const tmp = found.identifiers.caregiver_name;
                found.identifiers.caregiver_name = found.identifiers.client_name;
                found.identifiers.client_name = tmp;
            },
            loadFiltered()
            {
                let filtered = this.imported.filter( i => !i.dontImport ).slice(0);

                filtered = this.imported.map((item, index) => {
                    item.index = index;
                    return item;
                });

                if (this.filters.ByType) {
                    filtered = filtered.filter(item => {
                        return item.note.type === this.filters.ByType;
                    })
                }

                if (this.filters.ByMatch) {
                    filtered = filtered.filter(item => {
                        if (this.filters.ByMatch === 'unmatched') {
                            return !item.note.client_id || !item.note.caregiver_id;
                        }
                        else if (this.filters.ByMatch === 'matched') {
                            return item.note.client_id && item.note.caregiver_id;
                        }
                    })
                }

                if (this.filters.ByName) {
                    const pattern = new RegExp(this.filters.ByName, 'i');
                    filtered = filtered.filter(item => {
                        return item.identifiers.caregiver_name.search(pattern) > -1
                            || item.identifiers.client_name.search(pattern) > -1;
                    });
                }

                // Reset the page when filters are changed
                setTimeout(() => this.page = 1, 50);

                this.filtered = filtered;
            },

            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadClients() {
                if (this.imported.length > 0) {
                    let business_id = this.imported[0].note.business_id;
                    axios.get('/admin/businesses/' + business_id + '/clients?json=1').then(response => this.clients = response.data);
                }
            },

            loadCaregivers() {
                if (this.imported.length > 0) {
                    let business_id = this.imported[0].note.business_id;
                    axios.get('/admin/businesses/' + business_id + '/caregivers?json=1').then(response => this.caregivers = response.data);
                }
            },

            loadImportedData(data) {
                this.imported = data;
            },

            mapIdentifier(type, name, id) {
                this.imported.map(item => {
                    if (item.identifiers[`${type}_name`] === name) {
                        item.note[`${type}_id`] = id;
                    }
                })
            },

            loadDraft() {
                let data = JSON.parse(localStorage.getItem('admin_note_import_draft'));
                if (Array.isArray(data)) {
                    this.draft = true;
                    this.name = localStorage.getItem('admin_note_import_draft_name');
                    return data;
                }
                return [];
            },

            saveDraft() {
                localStorage.setItem('admin_note_import_draft', JSON.stringify(this.imported));
                localStorage.setItem('admin_note_import_draft_name', this.name);
                this.draft = true;
                alerts.addMessage('success', 'This import data has been saved to your browser.');
            },

            async savenotes() {
                this.submitting = true;
                const form = new Form({
                    name: this.name,
                    notes: this.imported.map(item => item.note)
                });
                try {
                    const response = await form.post('/admin/note-import/save');
                    this.deleteDraft(false);
                    this.submitting = false;
                }
                catch(err) {
                    this.submitting = false;
                }
            },

            deleteDraft(ask = true) {
                if (!ask || confirm('Are you sure you wish to delete this import data?')) {
                    localStorage.setItem('admin_note_import_draft', JSON.stringify([]));
                    this.draft = false;
                    this.imported = [];
                }
            },

            loadCreateClient(name)
            {
                this.createClientName = name;
                let business_id = this.getBusinessId();
                this.createClientForm = new Form({
                    'firstname': name.split(',')[1],
                    'lastname': name.split(',')[0],
                    'no_email': 1,
                    'username': moment().unix(),
                    'business_id': business_id
                });
                this.createClientModal = true;
            },

            loadCreateCaregiver(name)
            {
                this.createCaregiverName = name;
                let password = _(12).range().map(_.partial(_.random, 33, 126, false)).map(_.ary(String.fromCharCode)).join('');
                let business_id = this.getBusinessId();
                this.createCaregiverForm = new Form({
                    'firstname': name.split(',')[1],
                    'lastname': name.split(',')[0],
                    'no_email': 1,
                    'username': moment().unix(),
                    'password': password,
                    'password_confirmation': password,
                    'business_id': business_id,
                });
                this.createCaregiverModal = true;
            },

            getBusinessId() {
                let first = this.imported[0];
                if (!first || !first.note || !first.note.business_id) {
                    alert('Unable to determine the business ID from the import data.');
                    return false;
                }
                return first.note.business_id;
            },

            async saveCreateClient() {
                this.submitting = true;

                let business_id = this.getBusinessId();
                if (!business_id) return;

                try {
                    // Ensure  the active business matches the import business
                    await axios.post('/admin/businesses/active_business', {business_id});

                    // Store the client
                    const response = await this.createClientForm.post('/business/clients');
                    const id = response.data.data.id;
                    const name = this.createClientName;
                    if (id) {
                        // Save the mapping
                        axios.post('/admin/note-import/map/client', {id, name});
                        this.mapIdentifier('client', name, id);

                        // Close the modal
                        this.createClientModal = false;
                    }

                    // Reload the client list
                    this.loadClients();
                }
                catch(error) {
                    console.log(error);
                }
                this.submitting = false;
            },

            async saveCreateCaregiver()
            {
                this.submitting = true;

                let business_id = this.getBusinessId();
                if (!business_id) return;

                try {
                    // Ensure  the active business matches the import business
                    await axios.post('/admin/businesses/active_business', {business_id});

                    // Store the client
                    const response = await this.createCaregiverForm.post('/business/caregivers');
                    const id = response.data.data.id;
                    const name = this.createCaregiverName;
                    if (id) {
                        // Save the mapping
                        axios.post('/admin/note-import/map/caregiver', {id, name});
                        this.mapIdentifier('caregiver', name, id);

                        // Close the modal
                        this.createCaregiverModal = false;
                    }

                    // Reload the caregiver list
                    this.loadCaregivers();
                }
                catch(error) {
                    console.log(error);
                }
                this.submitting = false;
            },
        },

        watch: {
            imported() {
                this.loadClients();
                this.loadCaregivers();
                this.loadFiltered();
            },

            filters: {
                handler: function() {
                    this.loadFiltered();
                },
                deep: true
            },
        }
    }
</script>