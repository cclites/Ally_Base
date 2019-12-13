<template>

    <b-card>

        <b-row class="mb-2">

            <b-col lg="12">

                <div class="d-flex flex-column justify-content-center">

                    <div class="d-flex my-1">

                        <b-form-group label="Location" class="f-1 mx-1">

                            <business-location-select v-model="filters.business" :allow-all="true" :hideable="false"></business-location-select>
                        </b-form-group>

                        <b-form-group label="User Type" class="f-1 mx-1">

                            <b-form-select v-model="filters.userType">

                                <option value="client">Clients</option>
                                <option value="caregiver">Caregivers</option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="User Status" class="f-1 mx-1">

                            <b-form-select v-model="filters.status">

                                <option value="">All {{ capitalize( filters.userType ) }}s</option>
                                <option value="active">Active {{ capitalize( filters.userType ) }}s</option>
                                <option value="inactive">Inactive {{ capitalize( filters.userType ) }}s</option>
                                <option v-for=" status in statusEntity " :key=" status.id " :value=" status.id" >

                                    {{ status.name }}
                                </option>
                            </b-form-select>
                        </b-form-group>
                    </div>

                    <div class="d-flex my-1">

                        <b-form-group label="Client Type" class="f-1 mx-1">

                            <client-type-dropdown
                                :disabled=" filters.userType === 'caregiver' "
                                v-model="filters.client_type"
                            />
                        </b-form-group>

                        <b-form-group label="Service Coordinator" class="f-1 mx-1">

                            <b-form-select v-model="filters.caseManager" :disabled=" filters.userType === 'caregiver' ">
                                <template slot="first">
                                    <!-- this slot appears above the options from 'options' prop -->
                                <option value="">All Service Coordinators</option>
                                </template>
                                <option :value="cm.id" v-for="cm in filteredCaseManagers" :key="cm.id">{{ cm.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>

                        <b-form-group label="Days Since Last Shift" class="f-1 mx-1">

                            <b-form-input v-model="filters.daysPassed" placeholder="Has had a shift in the last x days" type="number"/>
                        </b-form-group>
                    </div>

                    <b-form-group label="General Search">

                        <b-form-input v-model="filters.search" placeholder="Type to Search" class="f-1 mx-1" />
                    </b-form-group>
                </div>
            </b-col>
        </b-row>
        <b-row class="mb-2">

            <b-col sm="12" class="my-1 d-sm-flex d-block justify-content-end">


                <b-button @click=" loadUsers() " variant="info" class="mr-3">Generate Report</b-button>
                <b-link href="#" @click=" averyModal = true " class="btn btn-primary">Generate Avery 5160 Labels</b-link>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="users"
                :fields="fields"
                :per-page="perPage"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :busy="loading"
                ref="table"
            >
                <template slot="primaryPhone" scope="row">

                    {{ getPhone(row.item.caregiver) }}
                </template>
                <template slot="city" scope="row">

                    {{ row.item.city ? row.item.city : '-' }}
                </template>
                <template slot="zipcode" scope="row">

                    {{ row.item.zipcode ? row.item.zipcode : '-' }}
                </template>
                <template slot="location" scope="data">

                    <div v-if=" filters.userType == 'caregiver' ">

                        <div v-for="( business, index ) in getBusinessNames( data.item.caregiver )" :key="index">

                            {{ business }}
                        </div>
                    </div>
                    <div v-else> {{ data.item.county }} </div>
                </template>
                <template slot="actions" scope="row">

                    <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                    <b-btn size="sm" :href="`/business/${ filters.userType }s/${ row.item.id }`">
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

        <avery-modal v-model=" averyModal " :callback=" averyLabels "></avery-modal>
    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import BusinessLocationSelect from "../BusinessLocationSelect";
    import FormatsListData from "../../../mixins/FormatsListData";
    import LocalStorage from "../../../mixins/LocalStorage";
    import Constants from '../../../mixins/Constants';

    export default {
        mixins: [FormatsListData, Constants, LocalStorage],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        props: {},

        data() {
            return {

                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                users: [],
                caseManagers: [],
                loading: false,
                statuses: {caregiver: [], client: []},
                filters: new Form({
                    business: '',
                    status: '',
                    search: '',
                    client_type: '',
                    caseManager: '',
                    userType: 'caregiver',
                    daysPassed : 1
                }),
                averyModal : false,
            }
        },

        async mounted() {

            await this.fetchStatusAliases();
            this.loadOfficeUsers();
        },

        computed: {

            localStoragePrefix(){

                return `${ this.filters.userType }_list_`;
            },

            fields(){

                if( this.filters.userType == 'client' ){

                    return [
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
                    ];
                }

                return [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        label: 'Email Address',
                        sortable: true,
                        formatter: this.formatEmail,
                    },
                    {
                        key: 'primaryPhone',
                        label: 'Primary Phone',
                        sortable: false,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                    },
                    {
                        key: 'zipcode',
                        label: 'Zip Code',
                        sortable: true,
                    },
                    {
                        key: 'location',
                        label: 'Location',
                        sortable: false,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ];
            },

            averyEndpoint(){

                return `/business/${ this.filters.userType }s/avery-labels?userType=${ this.filters.userType }`;
            },
            paginatedEndpoint(){

                return `/business/${ this.filters.userType }s/paginate?json=1`;
            },
            filteredCaseManagers() {
                return (!this.filters.business_id)
                    ? this.caseManagers
                    : this.caseManagers.filter(x => x.business_ids.includes(this.filters.business_id));
            },
            statusEntity(){

                if( this.userType == 'client' ) return this.statuses.client;
                else return this.statuses.caregiver;
            },
            listFilters() {

                let active = '';
                let aliasId = '';

                this.loadFiltersFromStorage();

                if (this.filters.status === '') {

                    active = '';
                } else if (this.filters.status === 'active') {

                    active = 1;
                } else if (this.filters.status === 'inactive') {

                    active = 0;
                } else {

                    aliasId = this.filters.status;
                    let alias = this.statuses.caregiver.find(x => x.id == this.filters.status);
                    if ( alias ) {

                        aliasId = alias.id;
                        active = alias.active;
                    }
                }

                let query = `&search=${this.filters.search}&active=${active}&status=${aliasId}&businesses=${this.filters.business}&daysPassed=${this.filters.daysPassed}`;

                if( this.filters.userType == 'client' ){

                    query += '&client_type=' + this.filters.client_type;
                    query += '&case_manager_id=' + this.filters.caseManager;
                }

                return query;
            },
            paginationControls(){

                return '&page=' + this.currentPage + '&perPage=' + this.perPage + '&sort=' + this.sortBy + '&sortDirection=' + ( this.sortDesc ? 'desc' : 'asc' );
            }
        },

        methods: {

            async loadOfficeUsers() {

                const response = await axios.get(`/business/office-users`);
                this.caseManagers = response.data;
            },
            capitalize( string ){
                // this can be abstracted into a lodash utility mixin.. not sure if one exists yet havent looked

                return _.capitalize( string );
            },

            averyLabels( data ){

                window.open( this.averyEndpoint + this.listFilters + '&leftmargin=' + data.leftmargin + '&topmargin=' + data.topmargin );
            },

            loadUsers() {

                if( this.filters.daysPassed < 0 || this.filters.daysPassed > 999 ){

                    alert( 'please enter a number of days above 0 and below 999' );
                    return false;
                }

                this.users = [];
                this.loading = true;

                axios.get( this.paginatedEndpoint + this.listFilters + this.paginationControls )
                    .then( ({ data }) => {

                        console.log( 'response: ', data );
                        this.totalRows = data.total;

                        if( this.filters.userType == 'client' ){

                            this.users = data.clients.map( client => {

                                client.county = client.address ? client.address.county : '';
                                client.case_manager_name = client.case_manager ? client.case_manager.name : null;
                                return client;
                            }) || [];
                        } else this.users = data.results || [];
                    })
                    .catch(() => {

                        return [];
                    })
                    .finally(() => {

                        this.loading = false;
                    });
            },

            getAddress(caregiver)
            {
                if (caregiver.address) {
                    return caregiver.address;
                }
                return { city: '-', zip: '-' };
            },

            getPhone(caregiver)
            {
                if (caregiver.phone_number) {
                    return caregiver.phone_number.number;
                }
                return '-';
            },

            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
//                this.$root.$emit('bv::show::modal','caregiverEditModal', button);
                this.editModalVisible = true;
            },

            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },

            getBusinessNames( caregiver ) {

                return caregiver.businesses.map(x => x.name);
            },

            async fetchStatusAliases() {
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.caregiver) {
                            this.statuses = data;
                        } else {
                            this.statuses = {caregiver: [], client: []};
                        }
                    })
                    .catch(() => {})
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

            paginationControls( oldVal, newVal ) {

                if( newVal != oldVal ){

                    this.updateSavedFormFilters();
                    this.loadUsers();
                }
            },
            // 'filters.daysPassed'( newVal, oldVal ) {

            //     if( newVal < 0 || newVal > 999 ){

            //         alert( 'value must be within 0 and 999 days' );
            //     } else {

            //         // debounce the reloading of the table to prevent
            //         // unnecessary calls.
            //         _.debounce(() => {
            //             this.updateSavedFormFilters();
            //             this.loadUsers();
            //         }, 350)();
            //     }
            // },
            // 'filters.caseManager'( newVal, oldVal ) {

            //     if ( newVal != oldVal ) {

            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }
            // },
            // 'filters.userType'( newVal, oldVal ) {

            //     if ( newVal != oldVal ) {

            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }
            // },

            // 'filters.client_type'( newVal, oldVal ) {

            //     if ( newVal != oldVal ) {

            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }
            // },
            // 'filters.status'(newVal, oldVal) {
            //     if (newVal != oldVal) {
            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }
            // },

            // 'filters.business'(newVal, oldVal) {
            //     if (newVal != oldVal) {
            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }
            // },
            // 'filters.search'(newVal, oldVal) {
            //     // debounce the reloading of the table to prevent
            //     // unnecessary calls.
            //     _.debounce(() => {
            //         this.updateSavedFormFilters();
            //         this.loadUsers();
            //     }, 350)();
            // },

            sortBy() {

                this.updateSortOrder();
            }
        },
    }
</script>
