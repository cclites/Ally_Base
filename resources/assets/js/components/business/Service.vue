<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
                <b-btn variant="info" @click="addService()">Add Service Code</b-btn>
            </b-col>
            <b-col lg="6">
                <b-form-input v-model="filter" placeholder="Type to Search" class="f-1" />
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
                        <b-btn size="sm" @click="editService(row.item.id)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn size="sm" @click="deleteService(row.item.id)">
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
        <business-service-modal 
            @saved="serviceSaved"
            v-model="showServiceModal" 
            :source="service">
        </business-service-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            services: {},
        },

        data() {
            return {
                items: [],
                service: {},
                showServiceModal: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true
                    },
                    {
                        key: 'code',
                        label: 'HCPCS Code',
                        sortable: true
                    },
                    {
                        key: 'mod1',
                        label: 'Modifier One',
                        sortable: true
                    },
                    {
                        key: 'mod2',
                        label: 'Modifier Two',
                        sortable: true
                    },
                    {
                        key: 'default',
                        label: 'Default',
                        sortable: true,
                        formatter: (val) => val ? 'Yes' : '',
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        mounted() {
            this.items = Object.keys(this.services).map(x => this.services[x]);
        },

        computed: {
        },

        methods: {
            serviceSaved(data) {
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item.name = data.name;
                    item.code = data.code;
                    item.mod1 = data.mod1;
                    item.mod2 = data.mod2;
                    item.default = data.default;
                } else {
                    this.items.push(data);
                }

                if (data.default) {
                    this.items.map(item => {
                        item.default = item.id === data.id;
                        return item;
                    });
                }
            },
            addService() {
                this.service = {};
                this.showServiceModal = true;
            },
            editService(id) {
                this.service = this.items.find(x => x.id == id);
                this.showServiceModal = true;
            },
            deleteService(id) {
                if (confirm("Are you sure you wish to delete this service?")) {
                    let form = new Form();
                    form.submit('delete', `/business/services/${id}`)
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
        }
    }
</script>