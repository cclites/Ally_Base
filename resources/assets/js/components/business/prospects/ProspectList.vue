<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <a href="/business/prospects/create" class="btn btn-info">Add Prospect</a>
            </b-col>
        </b-row>

        <loading-card v-show="loading"></loading-card>
        <div class="table-responsive" v-if="!loading">
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
                    <b-btn size="sm" :href="'/business/prospects/' + row.item.id">
                        <i class="fa fa-edit"></i> Edit
                    </b-btn>
                    <b-btn size="sm" @click="convert(row.item)">
                        <i class="fa fa-arrow-right"></i> Convert to client
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
                items: [],
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
                        key: 'phone',
                        sortable: true
                    },
                    {
                        key: 'city',
                        sortable: true
                    },
                    {
                        key: 'zip',
                        sortable: true
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
            this.loadProspects();
        },

        computed: {

        },

        methods: {
            async loadProspects() {
                this.loading = true;
                const response = await axios.get('/business/prospects?json=1');
                this.items = response.data;
                this.totalRows = this.items.length;
                this.loading = false;
            },
            convert(item) {
                if (!confirm(`Are you sure you wish to convert ${item.firstname} ${item.lastname} to a client?`)) return;
                let form = new Form({});
                form.post(`/business/prospects/${item.id}/convert`);
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
