<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <a href="/business/caregivers/create" class="btn btn-info">Add Caregiver</a>
            </b-col>
            <b-col lg="3">
                <!--<business-location-select v-model="businessFilter" :allow-all="true" :hideable="false"></business-location-select>-->
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="statusFilter">
                    <option value="">All Caregivers</option>
                    <option value="active">Active Caregivers</option>
                    <option value="inactive">Inactive Caregivers</option>
                    <option v-for="status in statuses.caregiver" :key="status.id" :value="status.id">
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
                         :items="caregivers"
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
                        <b-btn size="sm" :href="'/business/caregivers/' + row.item.id">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                    </template>
                    <template slot="location" scope="data">
                        <div v-for="(business, index) in getBusinessNames(data.item)" :key="index">
                            {{ business }}
                        </div>
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
    import BusinessLocationSelect from "./business/BusinessLocationSelect";
    import FormatsListData from "../mixins/FormatsListData";

    export default {
        mixins: [FormatsListData],
        components: {BusinessLocationSelect},

        props: {},

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                location: 'all',
                caregivers: [],
                fields: [
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
                        key: 'primaryphone',
                        label: 'Primary Phone',
                        sortable: true,
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
                        sortable: true,
                        // class: 'location d-none'
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                loading: false,
                businessFilter: '',
                statuses: {caregiver: [], client: []},
                statusFilter: 'active',
            }
        },

        async mounted() {
            await this.fetchStatusAliases();
            this.loadCaregivers();
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
                    let alias = this.statuses.caregiver.find(x => x.id == this.statusFilter);
                    if (alias) {
                        aliasId = alias.id;
                        active = alias.active;
                    }
                }

                return `/business/caregivers?json=1&address=1&phone_number=1&active=${active}&status=${aliasId}&location=${this.businessFilter}`;
            },
        },

        methods: {
            async loadCaregivers() {
                this.loading = true;
                const response = await axios.get(this.listUrl);
                this.caregivers = response.data.map(caregiver => {
                    caregiver.primaryphone = this.getPhone(caregiver).number;
                    caregiver.zipcode = this.getAddress(caregiver).zip;
                    caregiver.city = this.getAddress(caregiver).city;
                    return caregiver;
                });
                this.loading = false;
            },
            getAddress(caregiver)
            {
                if (caregiver.address) {
                    return caregiver.address;
                }
                return {};
            },
            getPhone(caregiver)
            {
                if (caregiver.phone_number) {
                    return caregiver.phone_number;
                }
                return {};
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
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            getBusinessNames(caregiver) {
                if (! caregiver || ! caregiver.clients ) {
                    return 'None';
                }

                let businesses = caregiver.clients.map(x => x.business);
                businesses = businesses.filter((item, index) => {
                    return businesses.findIndex(x => x.id == item.id) === index;
                })

                return businesses.map(x => x.name);
            },

            async fetchStatusAliases() {
                this.loading = true;
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.caregiver) {
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
                this.loadCaregivers();
            }
        }
    }
</script>
