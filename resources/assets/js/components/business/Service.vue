<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <a @click="onNewService()" class="btn btn-info">Add Service</a>
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
                        <b-btn size="sm" @click="onEditService(row.item.id)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn size="sm" @click="onDeleteService(row.item.id)">
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
            @saved="newService"
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
            newService(data) {
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item.name = data.name;
                    item.default = data.default;
                } else {
                    this.items.push(data);
                }
            },
            onNewService() {
                this.service = {};
                this.showServiceModal = true;
            },
            onEditService(id) {
                this.service = this.items.find(x => x.id == id);
                this.showServiceModal = true;
            },
            onDeleteService(id) {
                let form = new Form();
                form.submit('delete', `/business/service/${id}`)
                    .then( ({ data }) => {
                        this.items = this.items.filter(x => x.id !== id);
                    });
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
        }
    }
</script>