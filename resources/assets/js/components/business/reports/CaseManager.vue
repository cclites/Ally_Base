<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="2">
                            <b-form-group label="Case Manager">
                                <b-form-select v-model="filters.case_manager_id" class="mr-1 mb-1" name="case_manager_id">
                                    <option value="">All Case Manager</option>
                                    <option v-for="item in caseManagers" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                    
                        <b-col lg="2">
                            <b-form-group label="Client">
                                <label v-if="clients.length === 0">Client</label>
                                <b-form-select v-else v-model="filters.client_id" class="mr-1 mb-1" name="client_id">
                                    <option value="">All Clients</option>
                                    <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>
                        
                        <b-col lg="2">
                            <b-form-group label="Client Status">
                                <b-form-select v-model="filters.client_status" class="mr-1 mb-1" name="client_status">
                                    <option value="">All Clients</option>
                                    <option :value="1">Active</option>
                                    <option :value="0">Inactive</option>
                                </b-form-select>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="Days since">
                                <b-form-input v-model="filters.days_since_contact" type="number" placeholder="Last contact" />
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <hr/>
                    <div>
                        <div class="table-responsive">
                            <b-table 
                                bordered striped hover show-empty
                                :items="items"
                                :fields="fields"
                                :current-page="currentPage"
                                :per-page="perPage"
                                @filtered="onFiltered"
                            >
                                <template slot="case_manager" scope="row">
                                    {{ row.item.case_manager.user.nameLastFirst }}
                                </template>
                                <template slot="days_since_contact" scope="row">
                                    -
                                </template>
                                <template slot="actions" scope="row">
                                    <b-btn size="sm" :href="`/business/clients/${row.item.id}`" target="_blank">
                                        View client profile
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
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates, FormatsNumbers],

        props: {
            caseManagers: {
                type: Array,
                required: true,
            },
            clients: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                loading: false,
                filters: {
                    case_manager_id: '',
                    client_id: '',
                    client_status: '',
                    days_since_contact: '',
                },
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                fields: [
                    {
                        key: 'case_manager',
                        sortable: true,
                    },
                    {
                        key: 'nameLastFirst',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'days_since_contact',
                        label: 'Days since last contact (from call center)',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print',
                        sortable: false,
                    },
                ],
            };
        },

        computed: {
            items() {
                let result = [ ...this.clients ];

                if(this.filters.case_manager_id) {
                    result = result.filter(client => client.case_manager_id == this.filters.case_manager_id);
                }

                if(this.filters.client_status !== '') {
                    result = result.filter(client => client.user.active === this.filters.client_status);
                }

                if(this.filters.client_id) {
                    result = result.filter(client => client.id == this.filters.client_id);
                }

                // TODO: filter for days since contact

                return result;
            },
        },

        methods: {
            async loadClients() {
                this.filters.client_id = '';
                this.selectedClient = false;
                this.clients = [];
                this.loadingClients = true;

                try { 
                    const response = await axios.get('/business/clients?json=1&client_type=' + this.clientType);
                } catch(e) {
                    console.error(e);
                }
                this.clients = response.data;
                this.loadingClients = false;
            },

            fetchPreview() {
                this.loading = true;
                this.form.post('/business/reports/claims-report')
                    .then(response => {
                        this.items = response.data.summary;
                        this.selectedClient = response.data.client;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
            calculateDaysSince(date) {
                return moment(date);
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        },
    }
</script>

<style>
    table {
        font-size: 14px;
    }

    .table-info,
    .table-info > td,
    .table-info > th {
        font-weight: bold;
        font-size: 13px;
        background-color: #ecf7f9;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.2rem 0;
    }

    .signature > svg {
        margin: -25px 0;
        width: 100%;
        height: auto;
        max-width: 400px;
    }
</style>