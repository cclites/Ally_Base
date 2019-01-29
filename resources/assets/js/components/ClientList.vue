<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <a href="/business/clients/create" class="btn btn-info">Add Client</a>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-form-select v-model="caseManager" class="mr-2 mb-2">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option :value="null">-- Case Manager --</option>
                    </template>
                    <option :value="cm.id" v-for="cm in filteredCaseManagers" :key="cm.id">{{ cm.name }}</option>
                </b-form-select>
            </b-col>
            <b-col lg="3">
                <business-location-form-group :label="null" v-model="business_id" :allow-all="true" />
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="statusFilter">
                    <option value="">All Clients</option>
                    <option value="active">Active Clients</option>
                    <option value="inactive">Inactive Clients</option>
                    <option v-for="status in statuses.client" :key="status.id" :value="status.id">
                        {{ status.name }}
                    </option>
                </b-form-select>
            </b-col>
            <b-col lg="3" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        <div v-if="!loading">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="filteredClients"
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
                        <b-btn size="sm" :href="'/business/clients/' + row.item.id">
                            <i class="fa fa-edit"></i>
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
</template>

<script>
    import FormatsListData from "../mixins/FormatsListData";
    import BusinessLocationSelect from "./business/BusinessLocationSelect";
    import business from "../store/modules/business";
    import BusinessLocationFormGroup from "./business/BusinessLocationFormGroup";

    export default {
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsListData],

        props: {},

        data() {
            return {
                active: 1,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                business_id: "",
                clients: [],
                caseManagers: [],
                caseManager: null,
                filteredCaseManagers: [],
                filteredClients: [],
                fields: [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true
                    },
                    {
                        key: 'email',
                        label: 'Email Address',
                        sortable: true,
                        formatter: this.formatEmail,
                    },
                    {
                        key: 'county',
                        sortable: true
                    },
                    {
                        key: 'client_type',
                        label: 'Type',
                        sortable: true,
                        formatter: this.formatUppercase,
                    },
                    {
                        key: 'case_manager_name',
                        label: 'Case Manager',
                        sortable: true,
                    },
                    {
                        key: 'location',
                        label: 'Location',
                        sortable: true,
                        class: 'location d-none'
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                loading: false,
                statuses: {caregiver: [], client: []},
                statusFilter: '',
            }
        },

        async mounted() {
            await this.fetchStatusAliases();
            this.loadClients();
            this.loadOfficeUsers();
        },

        computed: {
            listUrl() {
                let active = '';
                let aliasId = '';
                if (this.statusFilter === '') {
                    active = '';
                } else if (this.statusFilter === 'active') {
                    active = 1;
                } else if (this.statusFilter === 'inactive') {
                    active = 0;    
                } else {
                    aliasId = this.statusFilter;
                    let alias = this.statuses.client.find(x => x.id == this.statusFilter);
                    if (alias) {
                        aliasId = alias.id;
                        active = alias.active;
                    }
                }

                return `/business/clients?json=1&address=1&businesses[]=${this.business_id}&active=${active}&status=${aliasId}`;
            }
        },

        methods: {
            async loadClients() {
                this.loading = true;
                const response = await axios.get(this.listUrl);
                this.clients = response.data.map(client => {
                    client.county = client.address ? client.address.county : '';
                    client.case_manager_name = client.case_manager ? client.case_manager.name : null;
                    return client;
                });
                this.filterClients();
                this.loading = false;
            },
            async loadOfficeUsers() {
                const response = await axios.get(`/business/office-users`);
                this.caseManagers = response.data;
                this.filterCaseManagers();
            },
            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
//                this.$root.$emit('bv::show::modal','clientEditModal', button);
                this.editModalVisible = true;
            },
            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            filterClients() {
                if (! this.caseManager) {
                    this.filteredClients = this.clients;
                } else {
                    this.filteredClients = this.clients.filter(x => x.case_manager_id === this.caseManager);
                }
            },
            filterCaseManagers() {
                if (this.business_id == '') {
                    this.filteredCaseManagers = this.caseManagers;
                } else {
                    this.filteredCaseManagers = this.caseManagers.filter(x => x.business_ids.includes(this.business_id))
                }
            },
            async fetchStatusAliases() {
                this.loading = true;
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.client) {
                            this.statuses = data;
                        } else {
                            this.statuses = {caregiver: [], client: []};
                        }
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.loading = false;
                    })
            },
        },

        watch: {
            listUrl() {
                this.loadClients();
            },
            caseManager(value) {
                this.filterClients();
            },
            business_id(value) {
                this.filterCaseManagers();
                this.filterClients();
            }
        }
    }
</script>
