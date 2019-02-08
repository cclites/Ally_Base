<template>
    <div>
        <b-row class="mb-2">
            <b-col lg="12">
                <b-btn variant="info" @click="addAuth()">Add Authorization</b-btn>
            </b-col>
        </b-row>
        <div>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="items"
                         :fields="fields"
                         :current-page="currentPage"
                         :per-page="perPage"
                         :filter="filter"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc"
                         @filtered="onFiltered"
                >
                    <template slot="actions" scope="row">
                        <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                        <b-btn size="sm" @click="editAuth(row.item.id)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn size="sm" @click="deleteAuth(row.item.id)">
                            <i class="fa fa-trash"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6" >
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>

        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-modal id="filterColumnsModal" :title="title" v-model="showAuthModal">
                <b-container fluid>
                    <b-row>
                        <b-col lg="6">
                            <b-form-group label="Service Code" label-class="required">
                                <b-form-select v-model="form.service_id" class="mr-1 mb-1" name="report_type">
                                    <option :value="null">--Select--</option>
                                    <option v-for="s in services" :value="s.id" :key="s.id">{{ s.code}} {{ s.name }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Payer">
                                <b-form-select v-model="form.payer_id" class="mr-1 mb-1" name="report_type">
                                    <option :value="null">(Any Payer)</option>
                                    <option v-for="p in payers" :value="p.id" :key="p.id">{{ p.name }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Effective Start" label-class="required">
                                <mask-input v-model="form.effective_start" type="date" class="date-input"></mask-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Effective End" label-class="required">
                                <mask-input v-model="form.effective_end" type="date" class="date-input"></mask-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Units" label-class="required">
                                <b-form-input v-model="form.units"></b-form-input>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Unit Type" label-class="required">
                                <b-form-select v-model="form.unit_type" class="mr-1 mb-1" name="report_type">
                                    <option value="hourly">Hourly</option>
                                    <option value="fixed">Fixed</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col lg="6">
                            <b-form-group label="Period" label-class="required">
                                <b-form-select v-model="form.period" class="mr-1 mb-1" name="report_type">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Notes">
                                <b-form-textarea :rows="2" v-model="form.notes"></b-form-textarea>
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-container>
                <div slot="modal-footer">
                    <b-button variant="success"
                            type="submit"
                            :disabled="loading"
                    >
                        {{ buttonText }}
                    </b-button>
                    <b-btn variant="default" @click="showAuthModal=false">Close</b-btn>
                </div>
            </b-modal>
        </form>
    </div>
</template>

<script>
    export default {
        props: ['auths', 'payers', 'services', 'clientId'],

        data() {
            return {
                items: [],
                auth: {},
                showAuthModal: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'payer',
                        label: 'Payer',
                        sortable: true,
                        formatter: (val) => {if (val) return val.name;}
                    },
                    {
                        key: 'service',
                        label: 'Service',
                        sortable: true,
                        formatter: (val) => {if (val) return val.name;}
                    },
                    {
                        key: 'effective_start',
                        label: 'Start',
                        sortable: true,
                    },
                    {
                        key: 'effective_end',
                        label: 'End',
                        sortable: true,
                    },
                    {
                        key: 'units',
                        label: 'Units',
                        sortable: true,
                    },
                    {
                        key: 'unit_type',
                        label: 'Unit Type',
                        sortable: true,
                        formatter: (val) => val.substr(0, 1).toUpperCase() + val.substr(1)
                    },
                    {
                        key: 'period',
                        label: 'Period',
                        sortable: true,
                        formatter: (val) => val.substr(0, 1).toUpperCase() + val.substr(1)
                    },
                    {
                        key: 'notes',
                        label: 'Notes',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                form: this.makeForm(this.auth),
                loading: false
            }
        },

        mounted() {
            this.items = Object.keys(this.auths).map(x => this.auths[x]);
        },

        computed: {
            title() {
                return (this.auth.id) ? 'Edit Authorization' : 'Add New Authorization';
            },
            buttonText() {
                return (this.auth.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            authSaved(data) {
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item = Object.assign(item, data);
                } else {
                    this.items.push(data);
                }
            },
            addAuth() {
                this.auth = {};
                this.form = this.makeForm(this.auth);
                this.showAuthModal = true;
            },
            editAuth(id) {
                this.auth = this.items.find(x => x.id == id);
                this.form = this.makeForm(this.auth);
                this.showAuthModal = true;
            },
            deleteAuth(id) {
                if (confirm("Are you sure you wish to delete this authorization?")) {
                    let form = new Form();
                    form.submit('delete', `/business/authorization/${id}`)
                        .then( ({ data }) => {
                            this.items = this.items.filter(x => x.id !== id);
                        });
                }
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            makeForm(defaults = {}) {
                return new Form({
                    client_id: this.clientId,
                    service_id: defaults.service_id || null,
                    payer_id: defaults.payer_id || null,
                    effective_start: defaults.effective_start || moment().format('MM/DD/YYYY'),
                    effective_end: defaults.effective_end || "12/31/9999",
                    units: defaults.units || "",
                    unit_type: defaults.unit_type || "hourly",
                    period: defaults.period || "weekly",
                    notes: defaults.notes || "",
                });
            },
            submitForm() {
                this.loading = true;
                let method = this.auth.id ? 'patch' : 'post';
                let url = this.auth.id ? `/business/authorization/${this.auth.id}` : '/business/authorization';
                this.form.submit(method, url)
                    .then(response => {
                        this.authSaved(response.data.data);
                        this.showAuthModal = false;
                    })
                    .finally(() => this.loading = false)
            },
        }
    }
</script>


<style lang="scss" scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }
</style>