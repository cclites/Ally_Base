<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <a href="/business/clients/create" class="btn btn-info">Add Client</a>
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="active">
                    <option value="all">All Clients</option>
                    <option value="active">Active Clients</option>
                    <option value="inactive">Inactive Clients</option>
                </b-form-select>
            </b-col>
            <b-col lg="3" v-if="multi_location.multiLocationRegistry == 'yes'">
                <b-form-select v-model="location" class="mb-1">
                    <option value="all">All Locations</option>
                    <option :value="multi_location.name">{{ multi_location.name }}</option>
                </b-form-select>
            </b-col>
            <b-col :lg="multi_location.multiLocationRegistry == 'yes' ? '3' : '6'" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

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
    </b-card>
</template>

<script>
    export default {
        props: {
            'clients': Array,
            'multi_location': Object,
        },

        data() {
            return {
                active: 'active',
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
                        sortable: true
                    },
                    {
                        key: 'county',
                        sortable: true
                    },
                    {
                        key: 'client_type',
                        label: 'Type',
                        sortable: true
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
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
            if(this.multi_location.multiLocationRegistry == 'yes') {
                document.querySelectorAll('.location').forEach(elem => {
                    elem.classList.remove('d-none');
                })
            }
        },

        computed: {
            items() {
                let component = this;
                let clients = this.clients.map(function(client) {
                    return {
                        id: client.id,
                        firstname: client.user.firstname,
                        lastname: client.user.lastname,
                        email: client.user.email,
                        client_type: _.upperFirst(_.replace(client.client_type, '_', ' ')),
                        active: client.user.active,
                        location: component.multi_location.name,
                        county: client.county
                    }
                });

                return _.filter(clients, (client) => {
                    switch (this.active) {
                        case 'all':
                            return true;
                        case 'active':
                            return client.active;
                        case 'inactive':
                            return !client.active;
                    }
                })
            },
        },

        methods: {
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
        }
    }
</script>
