<template>
    <div>
        <b-card>
            <admin-import-form :businesses="businesses"
                               @imported="loadImportedData"
                               v-show="imported.length === 0"
            ></admin-import-form>

            <table v-if="imported.length > 0" class="table table-bordered">
                <thead>
                <tr>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Duration</th>
                    <th colspan="2">Client</th>
                    <th colspan="2">Caregiver</th>
                    <th>CG Rate</th>
                    <th>Reg. Fee</th>
                    <th>Mileage</th>
                    <th>Other Expenses</th>
                </tr>
                </thead>
                <tbody>
                    <admin-import-id-row v-for="(row, index) in imported"
                                         :clients="clients"
                                         :caregivers="caregivers"
                                         :shift.sync="row.shift"
                                         :identifiers="row.identifiers"
                                         :key="index"
                                         :index="index"
                    ></admin-import-id-row>
                </tbody>
            </table>

            <div class="pull-right" v-if="imported.length > 0">
                <b-btn @click="saveDraft()" variant="primary"><i class="fa fa-save"></i> Save Draft</b-btn>
                <b-btn @click="saveShifts()" variant="info" :disabled="submitting">
                    <i class="fa fa-spin fa-spinner" v-if="submitting"></i>
                    <i class="fa fa-upload" v-else></i> Save Shifts
                </b-btn>
                <b-btn @click="deleteDraft()" variant="danger"><i class="fa fa-times"></i> Delete &amp; Cancel</b-btn>
            </div>
        </b-card>
    </div>
</template>

<script>
    export default {

        components: {
            'admin-import-form': require('./AdminImportForm'),
            'admin-import-id-row': require('./AdminImportIdRow'),
        },

        props: {},

        data() {
            return {
                'businesses': [],
                'caregivers': [],
                'clients': [],
                'imported': [],
                'draft': false,
                'submitting': false,
            }
        },

        async mounted() {
            this.loadBusinesses();
            this.imported = this.loadDraft();
        },

        methods: {
            loadBusinesses() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
            },

            loadClients() {
                if (this.imported.length > 0) {
                    let business_id = this.imported[0].shift.business_id;
                    axios.get('/admin/businesses/' + business_id + '/clients?json=1').then(response => this.clients = response.data);
                }
            },

            loadCaregivers() {
                if (this.imported.length > 0) {
                    let business_id = this.imported[0].shift.business_id;
                    axios.get('/admin/businesses/' + business_id + '/caregivers?json=1').then(response => this.caregivers = response.data);
                }
            },

            loadImportedData(data) {
                this.imported = data;
            },

            loadDraft() {
                let data = JSON.parse(localStorage.getItem('admin_import_draft'));
                if (Array.isArray(data)) {
                    this.draft = true;
                    return data;
                }
                return [];
            },

            saveDraft() {
                localStorage.setItem('admin_import_draft', JSON.stringify(this.imported));
                this.draft = true;
                alerts.addMessage('success', 'This import data has been saved to your browser.');
            },

            async saveShifts() {
                this.submitting = true;
                const form = new Form({
                    shifts: this.imported.map(item => item.shift)
                });
                try {
                    const response = await form.post('/admin/import/save');
                    this.deleteDraft(false);
                    this.submitting = false;
                }
                catch(err) {
                    this.submitting = false;
                }
            },

            deleteDraft(ask = true) {
                if (!ask || confirm('Are you sure you wish to delete this import data?')) {
                    localStorage.setItem('admin_import_draft', JSON.stringify([]));
                    this.draft = false;
                    this.imported = [];
                }
            }
        },

        watch: {
            imported() {
                this.loadClients();
                this.loadCaregivers();
            }
        }
    }
</script>
