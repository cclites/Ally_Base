<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="2">
                <a href="/business/clients/create" class="btn btn-info">Add Client</a>
            </b-col>
            <b-col lg="3">
                <business-location-form-group :label="null" v-model="filters.business_id" :allow-all="true" />
            </b-col>
            <b-col lg="2">
                <b-form-select v-model="filters.active">
                    <option :value="null">All Clients</option>
                    <option :value="1">Active Clients</option>
                    <option :value="0">Inactive Clients</option>
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
            <b-col lg="2" class="text-right">
                <b-form-input v-model.trim="filters.search" placeholder="Type to Search" />
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
    import fuzzysearch from 'fuzzysearch';

    export default {
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsListData],

        props: {},

        data() {
            return {
                filters: {
                    active: 1,
                    client_type: '',
                    business_id: '',
                    search: null,
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
            }
        },

        mounted() {
            this.loadClients();
        },

        computed: {
            listUrl() {
                const {business_id, active} = this.filters;
                const activeValue = (active !== null) ? active : '';

                return `/business/clients?json=1&address=1&active=${activeValue}&businesses[]=${business_id}`;
            },

            items() {
                const {search, client_type, active, business_id} = this.filters;
                let results = this.clients;
                
                if(client_type) {
                    results = results.filter((client) => client.client_type == client_type);
                }
                
                if(active === 1 || active === 0) {
                    results = results.filter((client) => client.active == active);
                }

                if(search) {
                    results = results.filter(({firstname, lastname}) => fuzzysearch(search, firstname) || fuzzysearch(search, lastname));
                }

                if(business_id) {
                    results = results.filter((client) => client.business_id == business_id);
                }

                return results;
            },
        },

        methods: {
            async loadClients() {
                this.loading = true;
                const response = await axios.get(this.listUrl);
                this.clients = response.data.map(client => {
                    client.county = client.address ? client.address.county : '';
                    return client;
                });

                this.loading = false;
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
            }
        },

        watch: {
            listUrl() {
                this.loadClients();
            }
        }
    }
</script>
