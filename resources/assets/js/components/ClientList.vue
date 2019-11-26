<template>
    <b-card>
        <b-row class="mb-2">
            <b-col sm="6" class="my-1">
                <a href="/business/clients/create" class="btn btn-info">Add Client</a>
            </b-col>
            <b-col sm="6" class="my-1 d-sm-flex d-block justify-content-end">
                <a href="JavaScript:Void(0)" @click=" averyLabels() " class="btn btn-info">Avery 5160 PDF</a>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="12">
                <div class="d-flex flex-md-row flex-sm-column justify-content-between align-items-start">
                    <b-form-select v-model="filters.caseManager" class="f-1 mr-2">
                        <template slot="first">
                            <!-- this slot appears above the options from 'options' prop -->
                        <option value="">All Service Coordinators</option>
                        </template>
                        <option :value="cm.id" v-for="cm in filteredCaseManagers" :key="cm.id">{{ cm.nameLastFirst }}</option>
                    </b-form-select>

                    <business-location-form-group :label="null" v-model="filters.business_id" :allow-all="true" class="f-1 mr-2" />

                    <b-form-select v-model="filters.status" class="f-1 mr-2">
                        <option value="">All Clients</option>
                        <option value="active">Active Clients</option>
                        <option value="inactive">Inactive Clients</option>
                        <option v-for="status in statuses.client" :key="status.id" :value="status.id">
                            {{ status.name }}
                        </option>
                    </b-form-select>

                    <client-type-dropdown
                        v-model="filters.client_type"
                        class="f-1 mr-2"
                    />
                    
                    <b-form-input v-model="filters.search" placeholder="Type to Search" class="f-1" />
                </div>
            </b-col>
        </b-row>

            <div class="table-responsive">
                <b-table 
                    bordered striped hover show-empty
                    :items="clients"
                    :fields="fields"
                    :per-page="perPage"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
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
    </b-card>
</template>

<script>
    import FormatsListData from "../mixins/FormatsListData";
    import BusinessLocationSelect from "./business/BusinessLocationSelect";
    import business from "../store/modules/business";
    import BusinessLocationFormGroup from "./business/BusinessLocationFormGroup";
    import Constants from '../mixins/Constants';
    import LocalStorage from "../mixins/LocalStorage";

    export default {
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsListData, Constants, LocalStorage],

        data() {
            return {
                filters: {
                    status: '',
                    client_type: '',
                    business_id: '',
                    search: '',
                    caseManager: '',
                },
                sortBy: 'lastname',
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
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
                        label: 'Service Coordinator',
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
                localStoragePrefix: 'client_list_',
                paginatedEndpoint : '/business/clients/paginate?json=1',
                averyEndpoint : '/business/clients/averyLabels?userType=client'
            }
        },

        async mounted() {
            this.loadFiltersFromStorage();
            await this.fetchStatusAliases();
            this.loadOfficeUsers();
            await this.loadClients();
        },

        computed: {

            filteredCaseManagers() {
                return (!this.filters.business_id)
                    ? this.caseManagers
                    : this.caseManagers.filter(x => x.business_ids.includes(this.filters.business_id));
            },

            listFilters() {

                // &page=${ctx.currentPage}&perpage=${ctx.perPage}&sort=${sort}

                let query = '&address=1&case_managers=1'; // this seems wierd that it is hard-coded.. but it was here when I got here

                // pagination controls
                query += '&page=' + this.currentPage;
                query += '&perPage=' + this.perPage;
                query += '&sort=' + this.sortBy;
                query += '&sortDirection=' + ( this.sortDesc ? 'desc' : 'asc' );

                let active = this.filters.status;
                let aliasId = '';
                switch( active ){

                    case '':

                        active = '';
                        break;
                    case 'active':

                        active = 1;
                        break;
                    case 'inactive':

                        active = 0;
                        break;
                    default:

                        aliasId = this.filters.status;
                        let alias = this.statuses.client.find( x => x.id == this.filters.status );
                        if ( alias ) {

                            aliasId = alias.id;
                            active  = alias.active;
                        }
                        break;
                }

                query += '&active=' + active;
                query += '&status=' + aliasId;

                query += '&client_type=' + this.filters.client_type;
                query += '&case_manager_id=' + this.filters.caseManager;
                query += '&businesses[]=' + this.filters.business_id;
                query += '&search=' + this.filters.search;

                return query;
            },
        },

        methods: {

            async loadClients() {

                this.loading = true;

                axios.get( this.paginatedEndpoint + this.listFilters )
                    .then( res => {

                        console.log( 'response: ', res );
                        this.totalRows = res.data[ 'total' ];

                        console.log( 'total rows: ', this.totalRows );

                        this.clients = res.data[ 'clients' ].map( client => {

                            client.county = client.address ? client.address.county : '';
                            client.case_manager_name = client.case_manager ? client.case_manager.name : null;
                            return client;
                        });

                        this.updateSavedFormFilters();
                    })
                    .catch( err => {

                        console.error( err );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
            averyLabels(){

                if( confirm( 'FYI: This will skip those without an address on file.' ) ) window.open( this.averyEndpoint + this.listFilters );
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
            loadFiltersFromStorage() {
                if (typeof(Storage) !== "undefined") {
                    // Saved filters
                    for (let filter of Object.keys(this.filters)) {
                        let value = this.getLocalStorage(filter);
                        if (value) this.filters[filter] = value;
                    }
                    // Sorting/show UI
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) this.sortBy = sortBy;
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) this.sortDesc = sortDesc;
                }
            },
            updateSavedFormFilters() {
                for (let filter of Object.keys(this.filters)) {
                    this.setLocalStorage(filter, this.filters[filter]);
                }
            },

            updateSortOrder(){
                this.setLocalStorage('sortBy', this.sortBy);
            }
        },

        watch: {

            async listFilters() {

                if( !this.loading ){

                    await this.loadClients();
                }
            },

            sortBy() {
                this.updateSortOrder();
            }
        }
    }
</script>
