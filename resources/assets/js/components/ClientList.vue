<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <a href="/business/clients/create" class="btn btn-info">Add Client</a>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-form-select v-model="filters.caseManager" class="mr-2 mb-2">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option value="">-- Case Manager --</option>
                    </template>
                    <option :value="cm.id" v-for="cm in filteredCaseManagers" :key="cm.id">{{ cm.name }}</option>
                </b-form-select>
            </b-col>
            <b-col lg="3">
                <business-location-form-group :label="null" v-model="filters.business_id" :allow-all="true" />
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="filters.status">
                    <option value="">All Clients</option>
                    <option value="active">Active Clients</option>
                    <option value="inactive">Inactive Clients</option>
                    <option v-for="status in statuses.client" :key="status.id" :value="status.id">
                        {{ status.name }}
                    </option>
                </b-form-select>
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="filters.client_type">
                    <option value="">--Select--</option>
                    <option value="private_pay">Private Pay</option>
                    <option value="medicaid">Medicaid</option>
                    <option value="VA">VA</option>
                    <option value="LTCI">LTC Insurance</option>
                </b-form-select>
            </b-col>
            <b-col lg="3" class="text-right">
                <b-form-input v-model="filters.search" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        <div v-if="!loading">
            <div class="table-responsive">
                <b-table 
                    bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :filter="filters.search"
                    @filtered="onFiltered"
                >
                    <template slot="payment_type" scope="row">
                        {{ paymentTypes.find(type => type.value == row.item.payment_type).text }}
                    </template>
                    <template slot="actions" scope="row">
                        <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                        <b-btn size="sm" :href="'/business/clients/' + row.item.id">
                            <i class="fa fa-edit" />
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

        data() {
            return {
                filters: {
                    status: '',
                    client_type: '',
                    business_id: '',
                    search: null,
                    caseManager: '',
                },
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                clients: [],
                caseManagers: [],
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
            }
        },

        async mounted() {
            await this.fetchStatusAliases();
            this.loadClients();
            this.loadOfficeUsers();
        },

        computed: {
            listUrl() {
                const {business_id, status, caseManager} = this.filters;
                const activeValue = (active !== null) ? active : '';

                let active = '';
                let aliasId = '';
                if (status === '') {
                    active = '';
                } else if (status === 'active') {
                    active = 1;
                } else if (status === 'inactive') {
                    active = 0;
                } else {
                    aliasId = status;
                    let alias = this.statuses.client.find(x => x.id == this.filters.status);
                    if (alias) {
                        aliasId = alias.id;
                        active = alias.active;
                    }
                }

                return `/business/clients?json=1&address=1&businesses[]=${this.business_id}&active=${active}&status=${aliasId}`;
            },

            items() {
                const {search, active, caseManager} = this.filters;
                let simpleMatches = ['client_type', 'business_id'];
                let results = this.clients;
                
                simpleMatches = simpleMatches.filter(key => !!this.filters[key]);
                results = results.filter((client) => {
                    const val = simpleMatches.every(key => client[key] == this.filters[key])
                    return val;
                });
                
                if(active === 1 || active === 0) {
                    results = results.filter((client) => client.active == active);
                } 

                if(caseManager) {
                    results = results.filter((client) => client.case_manager_id === caseManager);
                }

                return results;
            },

            filteredCaseManagers() {
                return (!this.filters.business_id)
                    ? this.caseManagers
                    : this.caseManagers.filter(x => x.business_ids.includes(this.filters.business_id));
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
                this.loading = false;
            },
            async loadOfficeUsers() {
                const response = await axios.get(`/business/office-users`);
                this.caseManagers = response.data;
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
        }
    }
</script>
